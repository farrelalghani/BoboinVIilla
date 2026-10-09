<?php

namespace App\Contracts;

use App\Models\Booking;
use App\Models\Payment;

interface PaymentGatewayInterface
{
    /**
     * Buat record payment baru untuk sebuah booking.
     * Masing-masing implementasi menyimpan data spesifik (VA number, receipt path, dll).
     */
    public function createPayment(Booking $booking, array $data): Payment;

    /**
     * Konfirmasi/verifikasi pembayaran.
     * Manual: admin klik "Verifikasi". Midtrans: webhook dari server Midtrans.
     */
    public function verifyPayment(Payment $payment, array $data = []): bool;

    /**
     * Tolak/batalkan pembayaran.
     * Manual: admin klik "Tolak". Midtrans: refund / cancel order.
     */
    public function rejectPayment(Payment $payment, string $reason = ''): bool;

    /**
     * Identifier unik untuk implementasi ini.
     * Contoh: 'manual_transfer', 'midtrans', 'xendit'
     */
    public function getPaymentType(): string;
}
