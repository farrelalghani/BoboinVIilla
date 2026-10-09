<?php

namespace App\Services\Payment;

use App\Contracts\PaymentGatewayInterface;
use App\Mail\BookingPaymentRejected;
use App\Mail\BookingPaymentVerified;
use App\Models\Booking;
use App\Models\Payment;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;

class ManualPaymentService implements PaymentGatewayInterface
{
    public function getPaymentType(): string
    {
        return 'manual_transfer';
    }

    /**
     * Buat record payment.
     *
     * $data yang diharapkan:
     *   - 'bank'           : string  (BCA, BRI, Mandiri, dst)
     *   - 'account_number' : string  (nomor rekening tujuan)
     */
    public function createPayment(Booking $booking, array $data): Payment
    {
        return Payment::create([
            'booking_id'     => $booking->id,
            'payment_type'   => $this->getPaymentType(),
            'bank'           => $data['bank'],
            'account_number' => $data['account_number'],
            'gross_amount'   => $booking->total_price,
            'payment_status' => 'pending',
        ]);
    }

    /**
     * Tamu mengupload bukti transfer.
     *
     * $data yang diharapkan:
     *   - 'receipt' : UploadedFile
     */
    public function uploadReceipt(Payment $payment, UploadedFile $receipt): Payment
    {
        if ($payment->receipt_image) {
            Storage::disk('public')->delete($payment->receipt_image);
        }

        $path = $receipt->store('receipts', 'public');

        $payment->update([
            'receipt_image'  => $path,
            'payment_status' => 'uploaded',
        ]);

        // Ubah status booking ke pending_payment supaya tamu tahu sudah diproses
        $payment->booking->update(['status' => 'pending_payment']);

        return $payment->fresh();
    }

    /**
     * Admin verifikasi — mutasi cocok, pembayaran sah.
     */
    public function verifyPayment(Payment $payment, array $data = []): bool
    {
        DB::transaction(function () use ($payment) {
            $payment->update(['payment_status' => 'verified']);
            $payment->booking->update(['status' => 'paid']);
        });

        $booking = $payment->booking->fresh(['villa']);
        if ($booking->guest_email) {
            Mail::to($booking->guest_email)->send(new BookingPaymentVerified($booking));
        }

        return true;
    }

    /**
     * Admin tolak — struk tidak valid atau nominal tidak sesuai.
     */
    public function rejectPayment(Payment $payment, string $reason = ''): bool
    {
        DB::transaction(function () use ($payment) {
            $payment->update(['payment_status' => 'rejected']);
            // Kembalikan booking ke approved agar tamu bisa upload ulang
            $payment->booking->update(['status' => 'approved']);
        });

        $booking = $payment->booking->fresh(['villa']);
        if ($booking->guest_email) {
            Mail::to($booking->guest_email)->send(
                new BookingPaymentRejected($booking, $reason ?: null)
            );
        }

        return true;
    }
}
