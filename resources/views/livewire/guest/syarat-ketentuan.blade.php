<?php

use Livewire\Attributes\Layout;
use Livewire\Volt\Component;

new #[Layout('layouts.app')] class extends Component
{
    //
}; ?>

<div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-10">
    {{-- Breadcrumb + tombol back --}}
    <div class="flex items-center justify-between mb-6">
        <nav class="flex items-center gap-2 text-sm text-gray-400">
            <a href="{{ route('home') }}" wire:navigate class="hover:text-[#3a6484] transition">Beranda</a>
            <span>/</span>
            <span class="text-gray-600">Syarat & Ketentuan</span>
        </nav>
        <button onclick="history.back()"
            class="flex items-center gap-1.5 text-sm text-[#3a6484] hover:text-[#2f5370] transition">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
            </svg>
            Kembali
        </button>
    </div>

    {{-- Hero Section --}}
    <div class="bg-gradient-to-r from-[#3a6484] to-[#2f5370] rounded-3xl p-8 sm:p-10 text-white mb-10 shadow-lg relative overflow-hidden">
        <div class="absolute right-0 top-0 translate-x-8 -translate-y-8 opacity-10 pointer-events-none">
            <svg class="w-80 h-80" fill="currentColor" viewBox="0 0 24 24">
                <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm1 15h-2v-6h2v6zm0-8h-2V7h2v2z"/>
            </svg>
        </div>
        <div class="relative z-10 max-w-2xl">
            <span class="inline-block px-3 py-1 bg-white/10 backdrop-blur-md rounded-full text-xs font-medium text-white/90 mb-4">
                Informasi & Kebijakan Resmi
            </span>
            <h1 class="text-3xl sm:text-4xl font-bold tracking-tight mb-3">Syarat & Ketentuan Pemesanan</h1>
            <p class="text-white/80 text-sm sm:text-base leading-relaxed">
                Selamat datang di Boboin Villa. Harap membaca kebijakan dan syarat ketentuan berikut secara saksama sebelum mengajukan pemesanan untuk kenyamanan dan keamanan bersama.
            </p>
            <p class="text-xs text-white/60 mt-4">Terakhir diperbarui: {{ date('d F Y') }}</p>
        </div>
    </div>

    {{-- Grid Ringkasan Aturan Utama --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-10">
        <div class="bg-white border border-gray-100 rounded-2xl p-5 shadow-sm hover:shadow-md transition">
            <div class="w-10 h-10 rounded-xl bg-blue-50 text-[#3a6484] flex items-center justify-center text-xl mb-3">
                🕒
            </div>
            <h3 class="font-semibold text-gray-800 text-sm mb-1">Waktu Check-in & Out</h3>
            <p class="text-xs text-gray-500 leading-relaxed">
                Check-in mulai pukul <strong>14.00 WIB</strong> dan Check-out maksimal pukul <strong>12.00 WIB</strong>.
            </p>
        </div>

        <div class="bg-white border border-gray-100 rounded-2xl p-5 shadow-sm hover:shadow-md transition">
            <div class="w-10 h-10 rounded-xl bg-green-50 text-green-600 flex items-center justify-center text-xl mb-3">
                💳
            </div>
            <h3 class="font-semibold text-gray-800 text-sm mb-1">Verifikasi Pembayaran</h3>
            <p class="text-xs text-gray-500 leading-relaxed">
                Bukti transfer harus diunggah setelah booking disetujui admin. Batas verifikasi 1×24 jam.
            </p>
        </div>

        <div class="bg-white border border-gray-100 rounded-2xl p-5 shadow-sm hover:shadow-md transition">
            <div class="w-10 h-10 rounded-xl bg-purple-50 text-purple-600 flex items-center justify-center text-xl mb-3">
                👥
            </div>
            <h3 class="font-semibold text-gray-800 text-sm mb-1">Batas Kapasitas</h3>
            <p class="text-xs text-gray-500 leading-relaxed">
                Jumlah tamu menginap wajib sesuai kapasitas maksimal unit yang tertera pada detail villa.
            </p>
        </div>

        <div class="bg-white border border-gray-100 rounded-2xl p-5 shadow-sm hover:shadow-md transition">
            <div class="w-10 h-10 rounded-xl bg-orange-50 text-orange-600 flex items-center justify-center text-xl mb-3">
                🔑
            </div>
            <h3 class="font-semibold text-gray-800 text-sm mb-1">Kode Booking Unik</h3>
            <p class="text-xs text-gray-500 leading-relaxed">
                Simpan kode booking (format BVL-XXXXXX) untuk mengecek status pesanan & mendownload invoice.
            </p>
        </div>
    </div>

    {{-- Detail Ketentuan Lengkap --}}
    <div class="space-y-6">
        {{-- Section 1 --}}
        <div class="bg-white border border-gray-100 rounded-2xl p-6 shadow-sm">
            <h2 class="text-lg font-semibold text-[#3a6484] mb-3 flex items-center gap-2">
                <span class="w-7 h-7 rounded-lg bg-[#3a6484]/10 flex items-center justify-center text-sm">1</span>
                Prosedur Pemesanan & Pembayaran
            </h2>
            <ul class="space-y-2 text-sm text-gray-600 list-disc list-inside leading-relaxed pl-1">
                <li>Pemesanan diajukan melalui sistem tanpa memerlukan login akun tamu.</li>
                <li>Setelah form diajukan, status pesanan awal adalah <strong class="text-orange-600">waiting_admin</strong>. Admin akan memeriksa ketersediaan villa.</li>
                <li>Setelah disetujui oleh admin (status <strong class="text-blue-600">approved</strong>), pemesan wajib melengkapi data diri dan mengunggah foto bukti transfer bank.</li>
                <li>Pembayaran yang telah terverifikasi oleh admin akan mengubah status menjadi <strong class="text-green-600">paid</strong> dan invoice resmi dapat diunduh langsung dari halaman Cek Pesanan.</li>
            </ul>
        </div>

        {{-- Section 2 --}}
        <div class="bg-white border border-gray-100 rounded-2xl p-6 shadow-sm">
            <h2 class="text-lg font-semibold text-[#3a6484] mb-3 flex items-center gap-2">
                <span class="w-7 h-7 rounded-lg bg-[#3a6484]/10 flex items-center justify-center text-sm">2</span>
                Ketentuan Hunian & Kapasitas Tamu
            </h2>
            <ul class="space-y-2 text-sm text-gray-600 list-disc list-inside leading-relaxed pl-1">
                <li>Jumlah total penghuni (dewasa + anak-anak) tidak boleh melebihi kapasitas maksimal unit villa.</li>
                <li>Tamu wajib menjaga ketertiban, kebersihan, serta fasilitas selama menginap. Kerusakan fasilitas akibat kelalaian tamu dapat dikenakan denda sesuai biaya perbaikan.</li>
                <li>Dilarang membawa barang-barang berbahaya, bahan mudah terbakar, serta benda ilegal menurut hukum Republik Indonesia.</li>
                <li>Penggunaan area villa untuk acara besar (seperti pernikahan atau pesta skala besar) wajib mengonfirmasi dan mengantongi izin tertulis dari manajemen Boboin Villa terlebih dahulu.</li>
            </ul>
        </div>

        {{-- Section 3 --}}
        <div class="bg-white border border-gray-100 rounded-2xl p-6 shadow-sm">
            <h2 class="text-lg font-semibold text-[#3a6484] mb-3 flex items-center gap-2">
                <span class="w-7 h-7 rounded-lg bg-[#3a6484]/10 flex items-center justify-center text-sm">3</span>
                Kebijakan Pembatalan & Pengembalian Dana (Refund)
            </h2>
            <ul class="space-y-2 text-sm text-gray-600 list-disc list-inside leading-relaxed pl-1">
                <li>Permintaan perubahan tanggal (*reschedule*) dapat dilakukan maksimal <strong>7 hari</strong> sebelum tanggal check-in (tergantung ketersediaan villa).</li>
                <li>Pembatalan pesanan yang telah dikonfirmasi/dibayar kurang dari 7 hari sebelum check-in dapat dikenakan potong uang muka/DP sesuai kebijakan manajemen.</li>
                <li>Apabila pesanan ditolak oleh admin pada tahap awal (`rejected`), tamu dapat mengajukan pemesanan ulang untuk tanggal atau unit villa lainnya.</li>
            </ul>
        </div>

        {{-- Section 4 --}}
        <div class="bg-white border border-gray-100 rounded-2xl p-6 shadow-sm">
            <h2 class="text-lg font-semibold text-[#3a6484] mb-3 flex items-center gap-2">
                <span class="w-7 h-7 rounded-lg bg-[#3a6484]/10 flex items-center justify-center text-sm">4</span>
                Privasi & Keamanan Data
            </h2>
            <p class="text-sm text-gray-600 leading-relaxed mb-2">
                Boboin Villa berkomitmen menjaga privasi data Anda. Data pribadi yang dikumpulkan (Nama, No. HP, Email, Kota Asal) hanya digunakan untuk keperluan administrasi pemesanan, verifikasi pembayaran, dan pengiriman invoice. Data Anda tidak akan diperjualbelikan kepada pihak ketiga.
            </p>
        </div>
    </div>

    {{-- Contact Banner --}}
    <div class="mt-10 bg-gradient-to-r from-blue-50 to-slate-50 border border-blue-100 rounded-2xl p-6 flex flex-col sm:flex-row items-center justify-between gap-4">
        <div>
            <h3 class="font-semibold text-gray-800 text-base mb-1">Ada Pertanyaan Pertanyaan Lain?</h3>
            <p class="text-xs sm:text-sm text-gray-500">Tim Customer Support Boboin Villa siap membantu Anda 24/7 via WhatsApp.</p>
        </div>
        <a href="https://wa.me/6282215433017?text=Halo%20Boboin%20Villa%2C%20saya%20ingin%20bertanya%20mengenai%20syarat%20dan%20ketentuan%20pemesanan"
           target="_blank"
           class="inline-flex items-center gap-2 px-5 py-2.5 bg-[#3a6484] text-white rounded-xl text-sm font-medium hover:bg-[#2f5370] transition shrink-0">
            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24">
                <path d="M.057 24l1.687-6.163c-1.041-1.804-1.588-3.849-1.587-5.946.003-6.556 5.338-11.891 11.893-11.891 3.181.001 6.167 1.24 8.413 3.488 2.245 2.248 3.481 5.236 3.48 8.414-.003 6.557-5.338 11.892-11.893 11.892-1.99-.001-3.951-.5-5.688-1.448l-6.305 1.654zm6.597-3.807c1.676.995 3.276 1.591 5.392 1.592 5.448 0 9.886-4.434 9.889-9.885.002-5.462-4.415-9.89-9.881-9.892-5.452 0-9.887 4.434-9.889 9.884-.001 2.225.651 3.891 1.746 5.634l-.999 3.648 3.742-.981z"/>
            </svg>
            Hubungi Hotline WhatsApp
        </a>
    </div>
</div>
