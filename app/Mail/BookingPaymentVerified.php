<?php

namespace App\Mail;

use App\Models\Booking;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class BookingPaymentVerified extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public Booking $booking) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Pembayaran Dikonfirmasi — ' . $this->booking->booking_code . ' | Boboin Villa',
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'emails.booking-payment-verified',
            with: ['booking' => $this->booking],
        );
    }
}
