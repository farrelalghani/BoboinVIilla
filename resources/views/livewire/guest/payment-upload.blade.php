<?php

use App\Models\Booking;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;

new #[Layout('layouts.app')] class extends Component
{
    public string $bookingCode;

    public function mount(string $bookingCode): void
    {
        Booking::where('booking_code', strtoupper($bookingCode))
            ->whereIn('status', ['approved', 'pending_payment'])
            ->firstOrFail();

        $this->bookingCode = strtoupper($bookingCode);
    }

    public function with(): array
    {
        return [
            'booking'       => Booking::with('villa')->where('booking_code', $this->bookingCode)->firstOrFail(),
            'bankName'      => config('payment.manual.bank', 'BCA'),
            'accountNumber' => config('payment.manual.account_number', '1234567890'),
            'accountName'   => config('payment.manual.account_name', 'Boboin Villa'),
            'success'       => session('success'),
        ];
    }
}; ?>

<div class="max-w-xl mx-auto px-4 py-10">
    @if($success)
        <div class="bg-green-50 border border-green-200 rounded-2xl p-8 text-center">
            <svg class="w-12 h-12 text-green-500 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            <h2 class="text-lg font-semibold text-green-700 mb-1">Bukti berhasil dikirim!</h2>
            <p class="text-sm text-green-600 mb-5">Admin akan memverifikasi dalam 1×24 jam.</p>
            <a href="{{ route('guest.cek-pesanan') }}?kode={{ $bookingCode }}"
               class="inline-block px-5 py-2.5 bg-[#3a6484] text-white rounded-xl text-sm font-medium hover:bg-[#2f5370] transition">
                Kembali ke Status Pesanan
            </a>
        </div>
    @elseif($booking->status === 'pending_payment')
        {{-- Invoice sudah dibuat, belum dibayar --}}
        <div class="bg-amber-50 border border-amber-200 rounded-2xl p-8 text-center">
            <svg class="w-12 h-12 text-amber-400 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            <h2 class="text-lg font-semibold text-amber-700 mb-1">Menunggu Pembayaran</h2>
            <p class="text-sm text-amber-600 mb-1">Kode Booking: <span class="font-mono font-bold">{{ $bookingCode }}</span></p>
            <p class="text-sm text-amber-600 mb-5">
                Invoice sudah dibuat. Selesaikan pembayaran Anda melalui Xendit.
            </p>
            <div class="flex flex-col sm:flex-row gap-3 justify-center">
                @if($booking->payment?->xendit_invoice_url)
                    <a href="{{ $booking->payment->xendit_invoice_url }}" target="_blank"
                       class="inline-block px-5 py-2.5 bg-[#3a6484] text-white rounded-xl text-sm font-medium hover:bg-[#2f5370] transition">
                        Lanjutkan Pembayaran →
                    </a>
                @endif
                <a href="{{ route('guest.cek-pesanan') }}?kode={{ $bookingCode }}"
                   class="inline-block px-5 py-2.5 border border-[#3a6484] text-[#3a6484] rounded-xl text-sm font-medium hover:bg-[#3a6484] hover:text-white transition">
                    Cek Status Pesanan
                </a>
            </div>
        </div>
    @else
        <div class="mb-6">
            <h1 class="text-xl font-semibold text-[#3a6484] mb-0.5">Lengkapi Data & Bayar</h1>
            <p class="text-sm text-gray-500">
                {{ $booking->villa->name }} —
                <span class="font-semibold text-gray-700">Rp {{ number_format($booking->total_price, 0, ',', '.') }}</span>
            </p>
            <p class="text-xs text-gray-400 mt-1">Kode Booking: <span class="font-mono font-medium">{{ $bookingCode }}</span></p>
        </div>

        {{-- Info pembayaran Xendit --}}
        <div class="bg-blue-50 border border-blue-100 rounded-2xl p-4 mb-6 flex items-start gap-3">
            <svg class="w-5 h-5 text-[#3a6484] mt-0.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/>
            </svg>
            <div>
                <p class="text-sm font-semibold text-[#1e3a8a] mb-0.5">Pembayaran via Xendit</p>
                <p class="text-xs text-gray-500">Setelah mengisi data diri, Anda akan diarahkan ke halaman pembayaran Xendit. Tersedia: Virtual Account, QRIS, E-wallet, Kartu Kredit.</p>
            </div>
        </div>

        @if($errors->any())
            <div class="mb-4 p-3 bg-red-50 border border-red-200 rounded-xl text-sm text-red-600">
                {{ $errors->first() }}
            </div>
        @endif

        @if(request('xendit_failed'))
            <div class="mb-4 p-3 bg-red-50 border border-red-200 rounded-xl text-sm text-red-600">
                Pembayaran dibatalkan atau gagal. Silakan coba lagi.
            </div>
        @endif

        <form method="POST"
              action="{{ route('guest.payment.xendit.post', $bookingCode) }}"
              class="space-y-4">
            @csrf

            {{-- Data diri --}}
            <div class="space-y-3">
                <h2 class="text-sm font-semibold text-gray-700">Data Diri Pemesan</h2>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Nama Lengkap</label>
                    <input type="text" name="guest_name"
                        value="{{ old('guest_name', $booking->guest_name) }}"
                        placeholder="Nama Anda"
                        class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-[#3a6484]/30 focus:border-[#3a6484]">
                    @error('guest_name') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Nomor HP / WhatsApp</label>
                    <input type="tel" name="guest_phone"
                        value="{{ old('guest_phone', $booking->guest_phone) }}"
                        placeholder="08xxxxxxxxxx"
                        class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-[#3a6484]/30 focus:border-[#3a6484]">
                    @error('guest_phone') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Email</label>
                    <input type="email" name="guest_email"
                        value="{{ old('guest_email', $booking->guest_email) }}"
                        placeholder="email@contoh.com"
                        class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-[#3a6484]/30 focus:border-[#3a6484]">
                    @error('guest_email') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Asal Kota</label>
                    <input type="text" name="guest_city"
                        value="{{ old('guest_city', $booking->guest_city) }}"
                        placeholder="Contoh: Jakarta, Bandung, Surabaya"
                        class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-[#3a6484]/30 focus:border-[#3a6484]">
                    @error('guest_city') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>
            </div>

            {{-- Jumlah tamu --}}
            <div class="space-y-3">
                <div class="flex items-center justify-between">
                    <h2 class="text-sm font-semibold text-gray-700">Jumlah Tamu yang Menginap</h2>
                    <span class="text-xs font-medium text-[#3a6484] bg-blue-50 px-2.5 py-1 rounded-full">
                        Maks. {{ $booking->villa->capacity }} orang
                    </span>
                </div>
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Dewasa</label>
                        <input type="number" name="adult_count" min="1" max="{{ $booking->villa->capacity }}"
                            value="{{ old('adult_count', $booking->adult_count ?? 1) }}"
                            class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-[#3a6484]/30 focus:border-[#3a6484]">
                        @error('adult_count') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Anak-anak</label>
                        <input type="number" name="child_count" min="0" max="{{ $booking->villa->capacity }}"
                            value="{{ old('child_count', $booking->child_count ?? 0) }}"
                            class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-[#3a6484]/30 focus:border-[#3a6484]">
                        @error('child_count') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>
                </div>
            </div>

            {{-- Ringkasan pembayaran --}}
            <div class="bg-gray-50 border border-gray-200 rounded-xl p-4 flex items-center justify-between">
                <div>
                    <p class="text-xs text-gray-400">Total Pembayaran</p>
                    <p class="text-[#3a6484] font-bold text-xl">Rp {{ number_format($booking->total_price, 0, ',', '.') }}</p>
                </div>
                <svg class="w-8 h-8 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/>
                </svg>
            </div>

            <button type="submit"
                class="w-full py-3.5 bg-[#3a6484] text-white rounded-xl font-semibold hover:bg-[#2f5370] transition flex items-center justify-center gap-2">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"/>
                </svg>
                Lanjut ke Pembayaran
            </button>
            <p class="text-xs text-center text-gray-400">Anda akan diarahkan ke halaman pembayaran Xendit yang aman</p>
        </form>
    @endif
</div>
