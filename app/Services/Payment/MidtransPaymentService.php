<?php

namespace App\Services\Payment;

use App\Contracts\PaymentGatewayInterface;
use App\Models\Booking;
use App\Models\Payment;

/**
 * Placeholder untuk integrasi Midtrans (Fase 5+).
 * Uncomment & implementasi saat siap, tanpa menyentuh kode booking sama sekali.
 */
class MidtransPaymentService implements PaymentGatewayInterface
{
    public function getPaymentType(): string
    {
        return 'midtrans';
    }

    public function createPayment(Booking $booking, array $data): Payment
    {
        // TODO:
        // 1. Hit Midtrans Snap API untuk generate payment_url / VA number
        // 2. Simpan response (va_number, snap_token) ke tabel payments
        // 3. Return Payment model
        throw new \RuntimeException('MidtransPaymentService belum diimplementasi.');
    }

    public function verifyPayment(Payment $payment, array $data = []): bool
    {
        // TODO: Validasi signature dari webhook Midtrans
        // $data berisi payload POST dari Midtrans notification endpoint
        throw new \RuntimeException('MidtransPaymentService belum diimplementasi.');
    }

    public function rejectPayment(Payment $payment, string $reason = ''): bool
    {
        // TODO: Panggil Midtrans Cancel/Refund API
        throw new \RuntimeException('MidtransPaymentService belum diimplementasi.');
    }
}
