<x-mail::message>
# Bukti Pembayaran Ditolak

Halo **{{ $booking->guest_name }}**,

Mohon maaf, bukti pembayaran yang Anda kirimkan untuk pesanan berikut tidak dapat kami verifikasi:

<x-mail::table>
| | |
|:---|:---|
| Kode Booking | **{{ $booking->booking_code }}** |
| Villa | {{ $booking->villa->name }} |
| Check-in | {{ $booking->check_in->translatedFormat('l, d F Y') }} |
| Check-out | {{ $booking->check_out->translatedFormat('l, d F Y') }} |
| Total Bayar | **Rp {{ number_format($booking->total_price, 0, ',', '.') }}** |
</x-mail::table>

@if($reason)
**Alasan penolakan:**
{{ $reason }}

@endif
Silakan upload ulang bukti pembayaran yang valid melalui tautan berikut:

<x-mail::button :url="route('guest.payment.upload', $booking->booking_code)" color="error">
Upload Ulang Bukti Bayar
</x-mail::button>

Jika ada pertanyaan, hubungi kami melalui WhatsApp:

<x-mail::button :url="'https://wa.me/6282215433017'">
Hubungi Admin via WhatsApp
</x-mail::button>

Salam,
**Tim Boboin Villa**
</x-mail::message>
