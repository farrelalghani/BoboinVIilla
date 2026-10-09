# Boboin Villa — Project Memory

Website pemesanan villa (villa booking) milik **Boboin Villa**. Dibangun dengan Laravel 11 + Livewire 3 Volt. Tidak ada sistem login untuk tamu — pemesanan menggunakan **kode booking** unik.

---

## Tech Stack

| Layer | Teknologi |
|---|---|
| Backend | Laravel 11 |
| Frontend | Livewire 3 Volt (single-file components) |
| Styling | Tailwind CSS |
| Database | MySQL via XAMPP |
| Auth | Laravel Breeze — hanya untuk admin |
| PDF | barryvdh/laravel-dompdf |
| Storage | `Storage::disk('public')` — `storage/app/public/` |
| Email | Gmail SMTP (App Password) |
| Roles | Spatie Permission — role: `admin` saja (guest login dihapus) |

## Cara Menjalankan Lokal

```bash
# Terminal 1 — Laravel
php artisan serve

# Terminal 2 — Vite (Tailwind HMR)
npm run dev

# Pastikan XAMPP (Apache + MySQL) aktif
```

---

## Alur Pemesanan (Booking Flow)

```
1. Tamu buka halaman villa → isi form (check_in, check_out, guests)
   → Booking dibuat dengan status: waiting_admin
   → Kode booking BVL-XXXXXX ditampilkan di layar (harus disimpan tamu)

2. Tamu buka /cek-pesanan?kode=BVL-XXXXXX
   → Halaman auto-poll tiap 2 detik (wire:poll.2000ms)

3. Admin login → Kelola Pesanan → Setujui atau Tolak

4a. Jika DITOLAK → status: rejected
    → Tamu lihat alasan di halaman Cek Pesanan

4b. Jika DISETUJUI → status: approved
    → Halaman Cek Pesanan auto-redirect ke /payment/BVL-XXXXXX

5. Tamu isi data diri (nama, email, HP, asal kota, dewasa, anak)
   + upload bukti transfer → status booking: pending_payment

6. Admin → Verifikasi Pembayaran → Verifikasi atau Tolak struk

7a. Jika TERVERIFIKASI → status: paid
    → Email konfirmasi + link invoice dikirim ke tamu

7b. Jika DITOLAK → status kembali ke approved
    → Email penolakan + link upload ulang dikirim ke tamu

8. Tamu download Invoice PDF dari /invoice/BVL-XXXXXX
```

---

## Database Schema

### Tabel `villas`
| Kolom | Tipe | Keterangan |
|---|---|---|
| id | bigint PK | |
| name | string | Nama villa |
| slug | string unique | URL-friendly name |
| description | text | |
| price_per_night | decimal(12,2) | |
| capacity | integer | Kapasitas maksimal tamu |
| city | string | Dropdown: Lembang Bandung / Dago Bandung / Bogor |
| address | text | |
| status | enum | active / inactive |
| image | string nullable | Path: `villas/filename.jpg` di disk public |
| facilities | json nullable | Array string, cast ke `'array'` di model |
| nearby_destinations | json nullable | Array of objects `{category, name, distance}` |
| house_rules | json nullable | Array string peraturan khusus villa, cast ke `'array'` di model |

### Tabel `bookings`
| Kolom | Tipe | Keterangan |
|---|---|---|
| id | bigint PK | |
| booking_code | string(20) unique nullable | Format: `BVL-XXXXXX`, auto-generate di model `booted()` |
| guest_id | bigint FK nullable | Nullable — login tamu sudah dihapus |
| villa_id | bigint FK | |
| check_in | date | |
| check_out | date | |
| total_price | decimal(12,2) | |
| status | enum | waiting_admin / approved / rejected / pending_payment / paid / completed / cancelled |
| rejection_note | text nullable | Alasan tolak dari admin |
| guest_name | string nullable | Diisi saat upload bukti bayar |
| guest_email | string nullable | Diisi saat upload bukti bayar |
| guest_phone | string(20) nullable | Diisi saat upload bukti bayar |
| guest_city | string(100) nullable | Asal kota tamu, diisi saat upload bukti bayar |
| adult_count | tinyint nullable | Jumlah tamu dewasa |
| child_count | tinyint nullable | Jumlah tamu anak-anak |

### Tabel `payments`
| Kolom | Tipe | Keterangan |
|---|---|---|
| id | bigint PK | |
| booking_id | bigint FK | |
| payment_type | string nullable | `manual_transfer` |
| bank | string nullable | Nama bank tujuan |
| account_number | string nullable | Nomor rekening tujuan |
| receipt_image | string nullable | Path bukti transfer di disk public |
| gross_amount | decimal(12,2) | |
| payment_status | enum | pending / uploaded / verified / rejected |

---

## Struktur File Penting

### Routes — `routes/web.php`
```
/                          → guest.home
/villas                    → guest.villas-index
/villas/{slug}             → guest.villas.show
/syarat-ketentuan          → guest.syarat-ketentuan      (publik)
/cek-pesanan               → guest.cek-pesanan          (publik, tanpa login)
/payment/{bookingCode}     → guest.payment.upload        (publik)
POST /payment/{bookingCode}/upload → guest.payment.upload.post
GET /invoice/{bookingCode} → guest.invoice.download      (hanya status paid)
/admin/dashboard           → admin.dashboard             (middleware: auth + role:admin)
/admin/villas              → admin.villas.index
/admin/bookings            → admin.bookings.index
/admin/payments            → admin.payments.index
POST /admin/villas/{id}/image → admin.villas.image
```

### Livewire Volt Components — `resources/views/livewire/`

**Guest:**
- `guest/home.blade.php` — Halaman utama, hero image dari `public/images/images.jpg`, featured villas
- `guest/villas-index.blade.php` — Daftar villa dengan filter (city dropdown, check_in, check_out, guests)
- `guest/villa-detail.blade.php` — Detail villa + Fasilitas + Destinasi Terdekat + Peraturan Khusus Villa + Syarat & Ketentuan Pemesanan + form booking. Setelah submit: tampilkan kode booking + tombol salin (Alpine.js clipboard)
- `guest/syarat-ketentuan.blade.php` — Halaman informasi resmi syarat & ketentuan, kebijakan check-in/out, batas kapasitas, refund, dan privasi
- `guest/cek-pesanan.blade.php` — Cek status pesanan dengan kode. Auto-poll 2 detik saat status `waiting_admin`. Auto-redirect ke payment upload saat approved
- `guest/payment-upload.blade.php` — Upload bukti + isi data diri. Accessible saat status `approved` atau `pending_payment`

**Admin:**
- `admin/dashboard.blade.php` — Statistik + pesanan terbaru
- `admin/villas/index.blade.php` — CRUD villa: nama, harga, kota (dropdown), fasilitas (checkbox), destinasi terdekat (tambah/hapus dinamis), peraturan khusus villa (tambah/hapus dinamis), upload foto
- `admin/bookings/index.blade.php` — Kelola pesanan: approve / reject dengan alasan
- `admin/payments/index.blade.php` — Verifikasi bukti bayar: verify / reject

### Models — `app/Models/`
- `Villa.php` — fillable + cast `facilities`, `nearby_destinations`, & `house_rules` sebagai `'array'`
- `Booking.php` — auto-generate `booking_code` di `booted()`, cast `check_in`/`check_out` sebagai date
- `Payment.php`

### Services — `app/Services/Payment/`
- `ManualPaymentService.php` — `createPayment()`, `uploadReceipt()`, `verifyPayment()`, `rejectPayment()`
  - `verifyPayment()` → kirim `BookingPaymentVerified` mail jika ada `guest_email`
  - `rejectPayment()` → kirim `BookingPaymentRejected` mail jika ada `guest_email`

### Mail — `app/Mail/`
- `BookingPaymentVerified.php` — subject: "Pembayaran Dikonfirmasi — BVL-XXX"
- `BookingPaymentRejected.php` — subject: "Bukti Pembayaran Ditolak — BVL-XXX"
- Template: `resources/views/emails/booking-payment-verified.blade.php`
- Template: `resources/views/emails/booking-payment-rejected.blade.php`

### PDF — `resources/views/pdf/invoice.blade.php`
- Download via route `guest.invoice.download` — hanya booking berstatus `paid`
- Menggunakan barryvdh/laravel-dompdf

### Layout
- `resources/views/layouts/app.blade.php` — layout tamu: navbar (logo + Cek Pesanan), footer, floating WhatsApp button (`wa.me/6282215433017`, warna `#3a6484`)
- `resources/views/layouts/admin.blade.php` — layout admin sidebar

---

## Konvensi & Pola Penting

### Upload File
Selalu gunakan **regular HTML POST form** (bukan Livewire `WithFileUploads`) untuk upload file. Ini lebih reliable untuk file besar.

```html
<form method="POST" action="..." enctype="multipart/form-data">
```

### Identifikasi Tamu
Tidak ada sesi/login tamu. Semua halaman tamu menggunakan `booking_code` (string `BVL-XXXXXX`) sebagai identifier. Contoh: `/payment/BVL-A3X9K2`.

### Kode Booking
Auto-generate di `Booking::booted()`:
```php
do {
    $code = 'BVL-' . strtoupper(Str::random(6));
} while (static::where('booking_code', $code)->exists());
```

### Komponen Volt
Format single-file Livewire 3 Volt:
```php
new #[Layout('layouts.app')] class extends Component { ... }; ?>
<div>...</div>
```

### Fasilitas Villa
Disimpan sebagai JSON array di kolom `facilities`. Contoh: `["WiFi","Kolam Renang","AC","Dapur"]`. Di-cast `'array'` di model.

### Destinasi Terdekat
Disimpan sebagai JSON array of objects di kolom `nearby_destinations`. Contoh:
```json
[{"category":"Wisata","name":"Tangkuban Perahu","distance":"5 km"}]
```

### Validasi Kapasitas Villa
- Di halaman detail villa (`villa-detail.blade.php`), input `guests` divalidasi dengan `max:villa.capacity`.
- Di route POST `/payment/{bookingCode}/upload`, `adult_count` dan `child_count` divalidasi individual (`max:capacity`) dan totalnya `(adult_count + child_count <= villa.capacity)` dengan pesan error informatif jika melampaui batas.

---

## Konfigurasi Email (Gmail SMTP / Local Log)

Status: **Disimpan untuk nanti (Pending Real Credentials)**. Currently diset `MAIL_MAILER=log` di `.env` untuk kebutuhan pengembangan & testing lokal agar tidak menghambat flow pengujian. Email yang terkirim akan masuk ke `storage/logs/laravel.log`.

Jika sudah siap konfigurasi Gmail SMTP asli nantinya:
`.env`:
```env
MAIL_MAILER=smtp
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=emailanda@gmail.com
MAIL_PASSWORD="xxxx xxxx xxxx xxxx" # App Password 16 digit dari Google Account
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=emailanda@gmail.com
MAIL_FROM_NAME="Boboin Villa"
```

## Export Excel

Package: `maatwebsite/excel`. Export class: `app/Exports/BookingsExport.php`.

Route: `GET /admin/export/bookings?year=2026&month=7` → download `.xlsx` pesanan berstatus `paid`/`completed`.

Dashboard menampilkan tabel ringkasan per bulan dengan tombol export per bulan dan per tahun.

---

## Fitur yang Belum Dikerjakan (Backlog)

- Integrasi WhatsApp Hotline otomatis berisikan Kode Booking saat butuh bantuan
- Xendit Payment Gateway (menggantikan/melengkapi transfer manual)
