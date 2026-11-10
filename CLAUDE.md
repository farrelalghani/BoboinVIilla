# Boboin Villa — Project Memory

Website pemesanan villa (villa booking) milik **Boboin Villa**. Dibangun dengan Laravel 11 + Livewire 3 Volt. Tidak ada sistem login untuk tamu — pemesanan menggunakan **kode booking** unik.

---

## Update Keamanan & Audit

### 2026-11-03
- Proteksi double-booking di admin approval `resources/views/livewire/admin/bookings/index.blade.php`: admin dicegah menyetujui pesanan jika tanggalnya bentrok dengan pesanan lain yang berstatus `approved`, `pending_payment`, atau `paid`.
- Proteksi tanggal blokir villa di `resources/views/livewire/guest/villa-detail.blade.php`: tamu dicegah memesan pada rentang tanggal yang ditutup/diblokir pada tabel `villa_blocked_dates`.
- Penguatan token webhook Xendit di `routes/web.php`: menggunakan perbandingan *timing-safe* `hash_equals` dan penolakan langsung jika secret token kosong.
- Pengetatan kontrol akses verifikasi pembayaran di `resources/views/livewire/admin/payments/index.blade.php`: verifikasi manual dibatasi hanya untuk jenis `manual_transfer` dengan status `uploaded`.
- Bersihkan sisa dead code & attack surface: hapus file artefak `subject('Test'))`, serta view login tamu lama yang tidak lagi dipakai (`my-bookings.blade.php` dan `register.blade.php`).
- Amankan seeder admin di `database/seeders/AdminSeeder.php` menggunakan env variable `ADMIN_INITIAL_PASSWORD`.
- Verifikasi keamanan deployment live di `https://boboinvilla.my.id`: akses ke `/.env` menghasilkan 403 Forbidden, `/composer.json` menghasilkan 404 Not Found, file log tidak dapat diakses, dan koneksi HTTPS SSL aktif.

### 2026-10-19
- Hardened webhook Xendit di `routes/web.php`: validasi `x-callback-token`, `external_id`, dan status yang diizinkan dilakukan sebelum query ke database.
- Tambah proteksi agar status webhook tidak valid langsung menghasilkan `400 Unsupported Xendit status` tanpa lanjut ke proses pembayaran.
- Perkuat verifikasi webhook Xendit: callback sekarang harus menyertakan invoice ID yang cocok dengan payment lokal; sebelum mengubah status, aplikasi mengambil invoice dari API Xendit dan memeriksa ID, booking reference, nominal, mata uang, serta status invoice. Perubahan status dilakukan secara transaksional hanya dari keadaan `pending_payment`; invoice kedaluwarsa juga diverifikasi ke API Xendit. Tes webhook terarah lulus, tetapi integrasi API langsung tetap perlu diuji di staging dengan kredensial Xendit.
- Catatan keamanan: secret harus disimpan pada environment production dan tidak dibagikan ke repo; file `.env` harus dipindahkan ke server yang aman.
- Tambah middleware keamanan produksi di `app/Http/Middleware/SecurityHeaders.php` untuk mengirim header seperti HSTS, X-Content-Type-Options, X-Frame-Options, dan Referrer-Policy.
- Tambah proteksi browser cache pada halaman sensitif/admin dan halaman status/pembayaran publik di `app/Http/Middleware/SecurityHeaders.php` dengan `Cache-Control: no-store`, `Pragma: no-cache`, dan `Expires: 0` agar data pribadi/transaksi tidak tersimpan di browser.
- Batasi pencarian kode booking publik menjadi 10 percobaan per menit per alamat IP dan jangan tampilkan nomor telepon/email tamu pada halaman status publik di `resources/views/livewire/guest/cek-pesanan.blade.php`.
- Batasi unduhan invoice publik menjadi 10 percobaan per menit per alamat IP di `routes/web.php` untuk mengurangi enumerasi kode booking dan pengambilan PDF berulang; tes terarah `InvoiceDownloadSecurityTest` lulus.
- Batasi pembuatan invoice Xendit publik menjadi 5 percobaan per menit per alamat IP melalui middleware `throttle:5,1` di `routes/web.php`; tes route terarah memastikan batas tersebut terpasang.
- Perkuat validasi upload file di `routes/web.php`: MIME type asli file sekarang dicek, bukan cuma ekstensi nama file, agar bukti transfer dan foto villa lebih aman.
- Bukti transfer manual baru sekarang disimpan pada disk privat `local`, bukan disk `public`; unduhan hanya lewat route admin terautentikasi `admin.payments.receipt`. File struk lama yang sudah berada di disk public tidak otomatis dipindahkan dan perlu ditinjau saat migrasi data.
- Cookie sesi sekarang otomatis memakai flag `Secure` di production melalui default `SESSION_SECURE_COOKIE` pada `config/session.php`; nilai environment tetap dapat meng-override default ini. Mode lokal tetap non-secure agar `php artisan serve` melalui HTTP tetap berfungsi.
- Berhenti melacak `sec ky/backup_codes.csv` dan `sec ky/secret_api_key.csv` di Git, tetapi salinan lokal tetap dipertahankan dan folder sudah diabaikan oleh `.gitignore`. Jika file pernah dipush ke remote, rahasia di dalamnya harus dirotasi; penghapusan dari index tidak menghapus riwayat commit lama.
- Nonaktifkan pendaftaran akun publik di `routes/auth.php` dan hapus tautan daftar dari halaman login; tamu tetap memesan tanpa akun dan akses admin menggunakan akun yang disiapkan melalui seeder.
- Nonaktifkan route publik unggah bukti transfer manual `guest.payment.upload.post` sementara `ManualPaymentService` tetap dipertahankan sebagai fallback sampai alur Xendit teruji di staging. Route pembayaran Xendit tetap aktif; tes `PaymentMethodRoutesTest` memastikan kedua kondisi ini.
- Perluas aturan `.gitignore` menjadi `.env*` dengan pengecualian `!.env.example`, sehingga variasi file environment lokal seperti `.env.local` dan `.env.staging` tidak ikut terlacak. Audit nama file menunjukkan `.env` lokal tidak terlacak; hanya `.env.example` yang tercatat di Git. File kredensial lama masih muncul di riwayat commit lokal; bila pernah dipush, rotasi secret tetap diperlukan.
- Progress checklist: keamanan dasar untuk webhook + header produksi sudah selesai; tahap berikutnya fokus pada konfigurasi production, secret hygiene, dan akses admin/session dengan kontrol yang lebih ketat.

### Checklist Audit Keamanan untuk Handoff

Gunakan daftar ini untuk melanjutkan audit sebelum website menerima pemesanan dan pembayaran sungguhan. Periksa perubahan terbaru di kode sebelum menandai item selesai.

#### Sudah dikerjakan
- [x] Validasi token callback, booking reference, invoice ID, nilai, mata uang, dan status invoice Xendit sebelum mengubah status pembayaran; perubahan hanya dari status lokal yang diharapkan.
- [x] Gunakan verifikasi token webhook Xendit yang *timing-safe* (`hash_equals`).
- [x] Tambahkan proteksi double-booking saat admin menyetujui pesanan dan proteksi tanggal blokir villa (`VillaBlockedDate`).
- [x] Tambahkan security headers produksi dan larangan cache pada halaman admin, status pesanan, pembayaran, dan invoice.
- [x] Batasi login admin, pencarian kode booking, unduhan invoice, dan pembuatan invoice Xendit.
- [x] Hapus pendaftaran akun publik; route admin tetap dibatasi autentikasi dan role admin.
- [x] Nonaktifkan endpoint publik unggah bukti transfer manual sementara service manual disimpan sebagai cadangan.
- [x] Simpan bukti transfer manual baru di storage privat dan sediakan unduhan hanya untuk admin.
- [x] Validasi MIME upload dan batasi foto villa ke JPEG/PNG/WebP; bukti manual mendukung gambar/PDF dengan batas ukuran.
- [x] Pastikan cookie sesi Secure otomatis pada production dan abaikan file `.env*` lokal dari Git, kecuali `.env.example`.
- [x] Pastikan deployment hanya mengekspos folder Laravel `public`; verifikasi langsung bahwa `.env`, `vendor`, `storage`, dan source code tidak dapat diakses melalui web (terverifikasi 403 & 404).
- [x] Pastikan PHP website dan CLI memenuhi `composer.json` (`^8.2`), database MySQL berhasil dimigrasikan pada hosting live.
- [x] Verifikasi koneksi live HTTPS (SSL aktif pada `boboinvilla.my.id`).
- [x] Bersihkan file sampah dan dead code sisa fitur lama.

#### Wajib sebelum produksi / go-live penuh
- [ ] Tanyakan apakah file secret pernah di-push ke remote. Jika ya atau tidak yakin, cabut/rotasi kredensial terkait terlebih dahulu; pertimbangkan pembersihan riwayat Git hanya dengan rencana terkoordinasi dan backup.
- [ ] Pindai riwayat Git dan semua file terlacak untuk secret lain tanpa mencetak nilainya ke log; pastikan secret baru hanya disimpan di environment/secret manager hosting.
- [ ] Masukkan token Xendit live yang sesungguhnya di file `.env` hosting (`XENDIT_SECRET_KEY` dan `XENDIT_WEBHOOK_TOKEN`).
- [ ] Uji alur Xendit end-to-end di staging/live: invoice dibuat, pembayaran berhasil/gagal/kedaluwarsa, webhook tervalidasi, callback duplikat/replay tidak mengirim email atau mengubah status berulang, dan invoice hanya tersedia setelah status paid.
- [ ] Buat akun admin dengan password unik baru (ubah dari password bawaan seeder `password`).
- [ ] Buat backup database dan file penting secara berkala di cPanel.

Jangan menandai aplikasi siap produksi hanya berdasarkan tes lokal. Integrasi Xendit live, konfigurasi hosting, pemulihan backup, dan pemeriksaan kebocoran file harus diverifikasi pada staging/hosting.

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
/admin/pengaturan          → admin.pengaturan.index (kelola banner cover beranda & teks)
POST /admin/pengaturan/banner       → admin.pengaturan.banner (upload banner baru)
POST /admin/pengaturan/banner/reset → admin.pengaturan.banner.reset (kembali ke default)
POST /admin/villas/{id}/image          → admin.villas.image (single photo)
POST /admin/villas/{id}/images         → admin.villas.images (multi photo upload)
POST /admin/villas/{id}/images/primary → admin.villas.images.primary (jadikan cover)
DELETE /admin/villas/{id}/images       → admin.villas.images.delete (hapus foto galeri)
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
- `admin/pengaturan/index.blade.php` — Pengaturan tampilan web: kelola foto cover banner beranda, judul/subjudul, live preview, dan tombol reset ke default

### Models — `app/Models/`
- `SiteSetting.php` — key-value store tabel `site_settings` untuk konfigurasi website (`hero_image`, `hero_title`, `hero_subtitle`). Helper: `SiteSetting::get($key, $default)` & `SiteSetting::set($key, $value)`.
- `Villa.php` — fillable + cast `images`, `facilities`, `nearby_destinations`, & `house_rules` sebagai `'array'`. Memiliki accessor `all_images` yang menggabungkan foto utama (`image`) dan galeri (`images`) tanpa duplikasi.
- `VillaPeakSeason.php` — relasi `peakSeasons()` di Villa. Menyimpan periode harga khusus (`start_date`, `end_date`, `price`, `label`).
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
- `resources/views/layouts/app.blade.php` — layout tamu: navbar (logo + Cek Pesanan), footer, floating WhatsApp button (`wa.me/6285724821667`, no: +62 857-2482-1667, warna `#3a6484`)
- `resources/views/layouts/admin.blade.php` — layout admin sidebar

---

## Konvensi & Pola Penting

### Upload File & Galeri Foto Villa
- Selalu gunakan **regular HTML POST form** (bukan Livewire `WithFileUploads`) untuk upload file demi reliabilitas file besar.
- Input file menggunakan attribute `multiple` dan `accept="image/jpeg,image/png,image/webp"`.
- **Dukungan Foto iPhone (HEIC):** Browser web standar tidak dapat me-render format `.heic` secara native. Dengan menetapkan `accept="image/jpeg,image/png,image/webp"`, browser iOS Safari/WebKit pada iPhone secara otomatis mengonversi foto HEIC menjadi JPEG berkualitas tinggi saat dipilih sebelum diunggah ke server.
- **Keamanan Upload:** Validasi backend ketat menggunakan `mimetypes:image/jpeg,image/png,image/webp` dengan batasan `max:5120` (5MB per file). Format file executable, script, atau SVG (yang rentan XSS) diblokir secara eksplisit.
- **Manajemen Galeri:** Admin dapat mengunggah banyak foto sekaligus, melihat thumbnail seluruh foto, menyetel salah satu foto sebagai cover utama (tombol "Jadikan Cover"), serta menghapus foto individual langsung dari storage.
- **Tampilan Tamu (Guest Detail):** Menggunakan galeri interaktif Alpine.js dengan foto utama, badge counter (contoh: 1 / 6 Foto), kontrol prev/next, thumbnail strip horizontal, dan lightbox full-screen saat foto diklik.
- **Navigasi & Geser (Swipe/Slide) Foto Diperbesar:** Modal lightbox menggunakan `x-teleport="body"` agar menutupi seluruh layar secara absolut (`z-[99999]`, background gelap `bg-black/95`). Mendukung:
  - **Tombol Tutup Eksplisit (✕ Tutup):** Pinned di pojok kanan atas (`fixed top-4 right-4 z-[100002]`) dengan background putih semi-transparan ber-blur agar 100% selalu terlihat di HP maupun laptop/PC.
  - **Skala Foto Proporsional (Anti Terlalu Besar/Ngezoom):** Foto di modal dibatasi `max-h-[60vh] sm:max-h-[68vh] object-contain` sehingga selalu terlihat utuh tanpa terpotong dan tidak memerlukan zoom-out browser.
  - **Swipe Geser Jari di HP/Tablet:** Menggunakan event listener `touchstart` & `touchend` (geser ke kiri untuk foto berikutnya, geser ke kanan untuk foto sebelumnya).
  - **Tombol Navigasi Panah Mengambang:** Tombol panah kiri dan kanan dipatok di pinggir layar (`fixed left-2 sm:left-6` dan `fixed right-2 sm:right-6`) menggunakan icon panah SVG tebal sehingga tidak pernah menumpuk di tengah foto.
  - **Navigasi Keyboard:** Tombol panah keyboard kanan/kiri (`ArrowRight` / `ArrowLeft`) dan tombol `Escape` untuk menutup.
  - **Thumbnail Strip Dalam Modal:** Deretan thumbnail di bawah foto dalam modal yang dapat digulir dan diklik langsung untuk berpindah foto.

### Responsivitas Mobile (Mobile Friendly)
- Kartu "Peraturan Khusus Villa Ini" dan "Syarat & Ketentuan Pemesanan" menggunakan tata letak flexbox adaptif (`flex-col sm:flex-row`) sehingga badge "Ketentuan Pemilik" dan "Kebijakan Menginap" tidak menabrak teks judul di layar HP sempit.
- Floating WhatsApp button diatur lebih ringkas di layar HP (`bottom-4 right-4 py-2.5 px-3.5`) dengan padding bawah halaman `pb-24 sm:pb-16` agar tidak menutupi isi kartu.

### Banner Cover Beranda (Hero Banner)
- Foto latar belakang besar di halaman utama tamu (`/`) dapat diganti secara dinamis oleh admin melalui menu **"Pengaturan Web"** (`/admin/pengaturan`).
- Nilai disimpan di tabel `site_settings` melalui model `SiteSetting`.
- Admin dapat mengunggah foto baru (maks. 5MB, format JPG/PNG/WebP), mengubah judul serta subjudul banner, melihat **Pratinjau Langsung (Live Preview)** sebelum/sesudah disimpan, atau mengembalikan ke gambar bawaan (*Reset ke Bawaan*).
- Jika belum diatur atau direset, sistem menggunakan fallback foto bawaan `/images/images.jpg`.

### Fasilitas Villa (Standar & Custom)
- Disimpan sebagai JSON array di kolom `facilities` pada tabel `villas`.
- **Fasilitas Standar:** Disimpan sebagai string identifier (contoh: `'wifi'`, `'kolam_renang'`, `'ac'`).
- **Fasilitas Custom ("Tambahkan Sendiri"):** Admin dapat menambahkan fasilitas baru secara dinamis dengan memilih icon emoji (misal: 🎱 Billiard, 🎮 Game/PS, 🛁 Jacuzzi, 🪟 Balkon, 🔥 Api Unggun, dll.) serta menuliskan nama fasilitasnya.
- **Format Penyimpanan Custom:** Disimpan sebagai associative array di dalam JSON `facilities`:
  ```json
  [
    "wifi",
    "kolam_renang",
    {"name": "Meja Billiard", "icon": "🎱"},
    {"name": "PlayStation 5", "icon": "🎮"}
  ]
  ```
- **Tampilan Tamu:** Di halaman Beranda, Daftar Villa, dan Detail Villa, sistem mengecek `is_array($f)`. Jika custom, nama dan emoji dirender langsung; jika standar, icon SVG dan label standar dirender.

### Fitur Multi-Bahasa (Bilingual Indonesia & English)
- **Komponen Switcher:** Menggunakan komponen blade `resources/views/components/language-switch.blade.php` dengan desain *pill slider* hitam-putih (ID/EN) yang memiliki efek transisi geser mulus (*smooth sliding animation*) berbasis Alpine.js & Tailwind CSS.
- **Penempatan:** Disediakan pada Navbar Desktop (di sebelah menu Cek Pesanan) dan pada Top Bar Mobile (di sebelah tombol menu hamburger) serta dalam menu mobile.
- **Manajemen Sesi & Middleware:** 
  - Middleware `App\Http\Middleware\SetLocale` membaca pilihan bahasa dari session (`Session::get('locale')`), memvalidasi nilai (`id` atau `en`), lalu menetapkan bahasa via `App::setLocale()` dan `Carbon::setLocale()`.
  - Route `/lang/{locale}` menangani perubahan bahasa secara instan dan mengembalikan tamu ke halaman sebelumnya (`redirect()->back()`).
- **Penyimpanan Terjemahan:** Menggunakan format JSON resmi Laravel pada `lang/en.json` dan `lang/id.json`. Seluruh teks UI publik (Navigasi, Pencarian, Kartu Villa, Detail Villa, Cek Pesanan, Syarat & Ketentuan, dan Pembayaran) dibungkus helper `__('...')` dengan fallback aman ke bahasa Indonesia.

### Peak Season Multi-Periode
- Mendukung beberapa rentang tanggal peak season per villa (contoh: tanggal 15–20 dan tanggal 25–30 dalam bulan yang sama).
- Dikelola melalui modal "Kelola Peak Season" di dashboard admin villa.
- **Validasi Tumpang Tindih (Overlap Prevention):** Sistem memvalidasi tanggal mulai tidak boleh melebihi tanggal akhir, dan rentang tanggal baru tidak boleh bertabrakan/overlap dengan rentang peak season yang sudah aktif untuk villa tersebut.
- **Prioritas Harga:** Pada fungsi `Villa::priceInfoForDate()`, urutan prioritas harga adalah: Peak Season (sesuai tanggal) > Weekend (Jumat & Sabtu) > Weekday (Minggu s/d Kamis).

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
