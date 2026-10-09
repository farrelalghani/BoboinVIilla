<?php

namespace App\Services\Payment;

use App\Contracts\PaymentGatewayInterface;
use App\Mail\BookingPaymentVerified;
use App\Models\Booking;
use App\Models\Payment;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Xendit\Configuration;
use Xendit\Invoice\InvoiceApi;
use Xendit\Invoice\CreateInvoiceRequest;

class XenditPaymentService implements PaymentGatewayInterface
{
    public function __construct()
    {
        Configuration::setXenditKey(config('services.xendit.secret_key'));
    }

    public function getPaymentType(): string
    {
        return 'xendit';
    }

    public function createPayment(Booking $booking, array $data = []): Payment
    {
        $apiInstance = new InvoiceApi();
        $nights      = $booking->check_in->diffInDays($booking->check_out);

        $phone = null;
        if ($booking->guest_phone) {
            $phone = '+62' . ltrim(preg_replace('/\D/', '', $booking->guest_phone), '0');
        }

        $request = new CreateInvoiceRequest([
            'external_id'          => $booking->booking_code,
            'amount'               => (float) $booking->total_price,
            'description'          => "Pembayaran {$booking->villa->name} ({$nights} malam) — {$booking->booking_code}",
            'payer_email'          => $booking->guest_email,
            'customer'             => [
                'given_names'   => $booking->guest_name,
                'email'         => $booking->guest_email,
                'mobile_number' => $phone,
            ],
            'success_redirect_url' => route('guest.cek-pesanan') . '?kode=' . $booking->booking_code,
            'failure_redirect_url' => route('guest.payment.upload', $booking->booking_code) . '?xendit_failed=1',
            'currency'             => 'IDR',
            'items'                => [[
                'name'     => "{$booking->villa->name} — {$nights} malam",
                'quantity' => 1,
                'price'    => (float) $booking->total_price,
            ]],
            'payment_methods'      => ['BCA', 'BNI', 'BRI', 'MANDIRI', 'PERMATA', 'BJB', 'BSI', 'OVO', 'DANA', 'SHOPEEPAY', 'QRIS'],
        ]);

        $invoice = $apiInstance->createInvoice($request);

        $payment = Payment::updateOrCreate(
            ['booking_id' => $booking->id],
            [
                'payment_type'       => $this->getPaymentType(),
                'gross_amount'       => $booking->total_price,
                'xendit_invoice_id'  => $invoice->getId(),
                'xendit_invoice_url' => $invoice->getInvoiceUrl(),
                'payment_status'     => 'pending',
            ]
        );

        $booking->update(['status' => 'pending_payment']);

        return $payment;
    }

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

    public function rejectPayment(Payment $payment, string $reason = ''): bool
    {
        DB::transaction(function () use ($payment) {
            $payment->update(['payment_status' => 'rejected']);
            $payment->booking->update(['status' => 'approved']);
        });

        return true;
    }
}
