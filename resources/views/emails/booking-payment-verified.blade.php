<x-mail::message>
# Pembayaran Anda Dikonfirmasi!

Halo **{{ $booking->guest_name }}**,

Kabar baik! Pembayaran untuk pemesanan villa Anda telah berhasil diverifikasi oleh tim Boboin Villa.

<x-mail::table>
| | |
|:---|:---|
| Kode Booking | **{{ $booking->booking_code }}** |
| Villa | {{ $booking->villa->name }} |
| Lokasi | {{ $booking->villa->city }} |
| Check-in | {{ $booking->check_in->translatedFormat('l, d F Y') }} |
| Check-out | {{ $booking->check_out->translatedFormat('l, d F Y') }} |
| Durasi | {{ $booking->check_in->diffInDays($booking->check_out) }} malam |
| Total Bayar | **Rp {{ number_format($booking->total_price, 0, ',', '.') }}** |
</x-mail::table>

Unduh invoice pemesanan Anda:

<x-mail::button :url="route('guest.invoice.download', $booking->booking_code)" color="success">
Unduh Invoice PDF
</x-mail::button>

Atau pantau status pesanan kapan saja:

<x-mail::button :url="route('guest.cek-pesanan') . '?kode=' . $booking->booking_code">
Cek Status Pesanan
</x-mail::button>

Terima kasih telah mempercayai **Boboin Villa** untuk perjalanan Anda. Selamat menikmati liburan!

Salam,
**Tim Boboin Villa**
</x-mail::message>
