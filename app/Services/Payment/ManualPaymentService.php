<?php

namespace App\Services\Payment;

use App\Contracts\PaymentGatewayInterface;
use App\Mail\BookingPaymentRejected;
use App\Mail\BookingPaymentVerified;
use App\Models\Booking;
use App\Models\Payment;
use App\Models\SiteSetting;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;

class ManualPaymentService implements PaymentGatewayInterface
{
    public function getPaymentType(): string
    {
        return 'manual_transfer';
    }

    /**
     * Ambil informasi rekening tujuan transfer resmi (prioritas SiteSetting admin, fallback ke config/payment.php).
     *
     * @return array{bank: string, account_number: string, account_name: string}
     */
    public static function bankDetails(): array
    {
        return [
            'bank'           => SiteSetting::get('payment_manual_bank') ?: config('payment.manual.bank', 'BCA'),
            'account_number' => SiteSetting::get('payment_manual_account_number') ?: config('payment.manual.account_number', '1234567890'),
            'account_name'   => SiteSetting::get('payment_manual_account_name') ?: config('payment.manual.account_name', 'Boboin Villa'),
        ];
    }

    /**
     * Buat atau perbarui record payment manual (anti duplikasi row & melindungi payment yang sudah verified).
     */
    public function createPayment(Booking $booking, array $data = []): Payment
    {
        $bankDetails = self::bankDetails();

        return DB::transaction(function () use ($booking, $data, $bankDetails) {
            $existing = Payment::query()->where('booking_id', $booking->id)->lockForUpdate()->first();

            if ($existing && $existing->payment_status === 'verified') {
                throw new \RuntimeException('Pembayaran untuk pesanan ini sudah terverifikasi.');
            }

            return Payment::updateOrCreate(
                ['booking_id' => $booking->id],
                [
                    'payment_type'       => $this->getPaymentType(),
                    'bank'               => $data['bank'] ?? $bankDetails['bank'],
                    'account_number'     => $data['account_number'] ?? $bankDetails['account_number'],
                    'gross_amount'       => $booking->total_price,
                    'payment_status'     => 'pending',
                    'xendit_invoice_id'  => null,
                    'xendit_invoice_url' => null,
                ]
            );
        });
    }

    /**
     * Proses atomik pengisian data diri tamu dan unggah bukti transfer manual.
     *
     * @param  array<string, mixed>  $guestData
     */
    public function submitTransfer(Booking $booking, array $guestData, UploadedFile $receipt): Payment
    {
        $newPath = $receipt->store('receipts', 'local');
        $bankDetails = self::bankDetails();
        $oldReceiptPath = null;

        try {
            $payment = DB::transaction(function () use ($booking, $guestData, $newPath, $bankDetails, &$oldReceiptPath) {
                $lockedBooking = Booking::query()
                    ->with('villa')
                    ->lockForUpdate()
                    ->findOrFail($booking->id);

                if ($lockedBooking->status !== 'approved') {
                    abort(409, 'Pesanan tidak dalam status siap dibayar.');
                }

                $maxCapacity = (int) ($lockedBooking->villa->capacity ?? 0);
                $totalGuests = (int) $guestData['adult_count'] + (int) $guestData['child_count'];
                if ($totalGuests > $maxCapacity) {
                    abort(422, 'Total tamu melebihi kapasitas villa.');
                }

                $existingPayment = Payment::query()
                    ->where('booking_id', $lockedBooking->id)
                    ->lockForUpdate()
                    ->first();

                if ($existingPayment && $existingPayment->payment_status === 'verified') {
                    abort(409, 'Pembayaran sudah terverifikasi.');
                }

                $oldReceiptPath = $existingPayment?->receipt_image;

                $lockedBooking->update([
                    'guest_name'  => $guestData['guest_name'],
                    'guest_phone' => $guestData['guest_phone'],
                    'guest_email' => $guestData['guest_email'],
                    'guest_city'  => $guestData['guest_city'],
                    'adult_count' => (int) $guestData['adult_count'],
                    'child_count' => (int) $guestData['child_count'],
                    'status'      => 'pending_payment',
                ]);

                return Payment::updateOrCreate(
                    ['booking_id' => $lockedBooking->id],
                    [
                        'payment_type'       => $this->getPaymentType(),
                        'bank'               => $bankDetails['bank'],
                        'account_number'     => $bankDetails['account_number'],
                        'receipt_image'      => $newPath,
                        'gross_amount'       => $lockedBooking->total_price,
                        'payment_status'     => 'uploaded',
                        'xendit_invoice_id'  => null,
                        'xendit_invoice_url' => null,
                    ]
                );
            });
        } catch (\Throwable $e) {
            Storage::disk('local')->delete($newPath);
            throw $e;
        }

        if ($oldReceiptPath && $oldReceiptPath !== $newPath) {
            Storage::disk('local')->delete($oldReceiptPath);
        }

        return $payment->fresh();
    }

    /**
     * Tamu mengupload bukti transfer ke storage privat.
     */
    public function uploadReceipt(Payment $payment, UploadedFile $receipt): Payment
    {
        $newPath = $receipt->store('receipts', 'local');
        $oldPath = null;

        try {
            $updated = DB::transaction(function () use ($payment, $newPath, &$oldPath) {
                $lockedPayment = Payment::query()->lockForUpdate()->findOrFail($payment->id);
                $lockedBooking = Booking::query()->lockForUpdate()->findOrFail($lockedPayment->booking_id);

                if ($lockedPayment->payment_status === 'verified') {
                    abort(409, 'Pembayaran sudah terverifikasi.');
                }

                $oldPath = $lockedPayment->receipt_image;

                $lockedPayment->update([
                    'receipt_image'  => $newPath,
                    'payment_status' => 'uploaded',
                ]);

                $lockedBooking->update(['status' => 'pending_payment']);

                return $lockedPayment->fresh();
            });
        } catch (\Throwable $e) {
            Storage::disk('local')->delete($newPath);
            throw $e;
        }

        if ($oldPath && $oldPath !== $newPath) {
            Storage::disk('local')->delete($oldPath);
        }

        return $updated;
    }

    /**
     * Admin verifikasi — mutasi cocok, pembayaran sah (transaksional + lockForUpdate anti double-verify).
     */
    public function verifyPayment(Payment $payment, array $data = []): bool
    {
        $wasVerified = DB::transaction(function () use ($payment) {
            $lockedPayment = Payment::query()->lockForUpdate()->findOrFail($payment->id);
            $lockedBooking = Booking::query()->lockForUpdate()->findOrFail($lockedPayment->booking_id);

            if ($lockedPayment->payment_type !== $this->getPaymentType()) {
                return false;
            }

            if ($lockedPayment->payment_status === 'verified' && $lockedBooking->status === 'paid') {
                return false;
            }

            if ($lockedPayment->payment_status !== 'uploaded' || $lockedBooking->status !== 'pending_payment') {
                return false;
            }

            $lockedPayment->update(['payment_status' => 'verified']);
            $lockedBooking->update(['status' => 'paid']);

            return true;
        });

        if (! $wasVerified) {
            return false;
        }

        $booking = $payment->booking()->with('villa')->first();
        if ($booking?->guest_email) {
            try {
                Mail::to($booking->guest_email)->send(new BookingPaymentVerified($booking));
            } catch (\Throwable $e) {
                Log::error('Gagal mengirim email verifikasi pembayaran: ' . $e->getMessage());
                session()->flash('mail_warning', 'Pembayaran berhasil diverifikasi, tetapi email notifikasi gagal terkirim ke ' . $booking->guest_email . '. Periksa konfigurasi SMTP di .env.');
            }
        }

        return true;
    }

    /**
     * Admin tolak — struk tidak valid atau nominal tidak sesuai (transaksional + lockForUpdate).
     */
    public function rejectPayment(Payment $payment, string $reason = ''): bool
    {
        $wasRejected = DB::transaction(function () use ($payment) {
            $lockedPayment = Payment::query()->lockForUpdate()->findOrFail($payment->id);
            $lockedBooking = Booking::query()->lockForUpdate()->findOrFail($lockedPayment->booking_id);

            if ($lockedPayment->payment_type !== $this->getPaymentType()) {
                return false;
            }

            if ($lockedPayment->payment_status !== 'uploaded') {
                return false;
            }

            $lockedPayment->update(['payment_status' => 'rejected']);
            // Kembalikan booking ke approved agar tamu bisa upload ulang
            $lockedBooking->update(['status' => 'approved']);

            return true;
        });

        if (! $wasRejected) {
            return false;
        }

        $booking = $payment->booking()->with('villa')->first();
        if ($booking?->guest_email) {
            try {
                Mail::to($booking->guest_email)->send(
                    new BookingPaymentRejected($booking, $reason ?: null)
                );
            } catch (\Throwable $e) {
                Log::error('Gagal mengirim email penolakan pembayaran: ' . $e->getMessage());
                session()->flash('mail_warning', 'Bukti transfer ditolak, tetapi email notifikasi gagal terkirim ke ' . $booking->guest_email . '. Periksa konfigurasi SMTP di .env.');
            }
        }

        return true;
    }
}
