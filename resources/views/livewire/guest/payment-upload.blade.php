<?php

use App\Models\Booking;
use App\Services\Payment\ManualPaymentService;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;

new #[Layout('layouts.app')] class extends Component
{
    public string $bookingCode;

    public function mount(string $bookingCode): void
    {
        $booking = Booking::where('booking_code', strtoupper($bookingCode))
            ->whereIn('status', ['approved', 'pending_payment', 'paid', 'completed'])
            ->firstOrFail();

        $this->bookingCode = strtoupper($bookingCode);

        if (in_array($booking->status, ['paid', 'completed'], true) && ! session('success')) {
            $this->redirect(route('guest.cek-pesanan', ['kode' => $this->bookingCode]));
        }
    }

    public function refreshStatus(): void
    {
        $booking = Booking::where('booking_code', $this->bookingCode)->first();

        if ($booking && in_array($booking->status, ['paid', 'completed'], true)) {
            $this->redirect(route('guest.cek-pesanan', ['kode' => $this->bookingCode]));
        }
    }

    public function with(): array
    {
        $bankDetails = ManualPaymentService::bankDetails();

        return [
            'booking'       => Booking::with(['villa', 'payment'])->where('booking_code', $this->bookingCode)->firstOrFail(),
            'bankName'      => $bankDetails['bank'],
            'accountNumber' => $bankDetails['account_number'],
            'accountName'   => $bankDetails['account_name'],
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
            <h2 class="text-lg font-semibold text-green-700 mb-1">{{ __('Bukti berhasil dikirim!') }}</h2>
            <p class="text-sm text-green-600 mb-5">{{ __('Admin akan memverifikasi dalam 1×24 jam.') }}</p>
            <a href="{{ route('guest.cek-pesanan') }}?kode={{ $bookingCode }}"
               class="inline-block px-5 py-2.5 bg-[#3a6484] text-white rounded-xl text-sm font-medium hover:bg-[#2f5370] transition">
                {{ __('Kembali ke Status Pesanan') }}
            </a>
        </div>
    @elseif($booking->status === 'pending_payment')
        {{-- Bukti transfer sudah diunggah, menunggu verifikasi admin --}}
        <div wire:poll.5000ms="refreshStatus" class="bg-amber-50 border border-amber-200 rounded-2xl p-8 text-center">
            <svg class="w-12 h-12 text-amber-400 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            <h2 class="text-lg font-semibold text-amber-700 mb-1">{{ __('Menunggu Pembayaran') }}</h2>
            <p class="text-sm text-amber-600 mb-1">{{ __('Kode Booking') }}: <span class="font-mono font-bold">{{ $bookingCode }}</span></p>
            <p class="text-sm text-amber-700/80 mb-5">
                {{ __('Bukti pembayaran Anda sedang diverifikasi oleh Admin dalam 1×24 jam.') }}
            </p>
            @if($booking->payment)
                <div class="bg-white/80 border border-amber-200/70 rounded-xl p-3 text-xs text-gray-600 mb-5 inline-block text-left">
                    <p><span class="text-gray-400">{{ __('Bank') }}:</span> <span class="font-semibold">{{ $booking->payment->bank ?? $bankName }}</span></p>
                    <p><span class="text-gray-400">{{ __('Total Pembayaran') }}:</span> <span class="font-semibold text-[#3a6484]">Rp {{ number_format($booking->payment->gross_amount, 0, ',', '.') }}</span></p>
                </div>
            @endif
            <div class="flex flex-col sm:flex-row gap-3 justify-center">
                <a href="{{ route('guest.cek-pesanan') }}?kode={{ $bookingCode }}"
                   class="inline-block px-5 py-2.5 bg-[#3a6484] text-white rounded-xl text-sm font-medium hover:bg-[#2f5370] transition">
                    {{ __('Cek Status Pesanan') }}
                </a>
            </div>
        </div>
    @else
        <div class="mb-6">
            <h1 class="text-xl font-semibold text-[#3a6484] mb-0.5">{{ __('Lengkapi Data & Bayar') }}</h1>
            <p class="text-sm text-gray-500">
                {{ $booking->villa->name }} —
                <span class="font-semibold text-gray-700">Rp {{ number_format($booking->total_price, 0, ',', '.') }}</span>
            </p>
            <p class="text-xs text-gray-400 mt-1">{{ __('Kode Booking') }}: <span class="font-mono font-medium">{{ $bookingCode }}</span></p>
        </div>

        @if($booking->payment?->payment_status === 'rejected')
            <div class="mb-5 p-4 bg-red-50 border border-red-200 rounded-2xl text-sm text-red-700 flex items-start gap-2.5">
                <svg class="w-5 h-5 text-red-500 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                <p>{{ __('Bukti pembayaran sebelumnya ditolak. Silakan unggah ulang bukti transfer yang valid.') }}</p>
            </div>
        @endif

        {{-- Info Rekening Tujuan Transfer Manual --}}
        <div class="bg-blue-50/80 border border-blue-200/80 rounded-2xl p-5 mb-6 space-y-4">
            <div class="flex items-start gap-3">
                <div class="w-9 h-9 rounded-xl bg-[#3a6484] text-white flex items-center justify-center shrink-0">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/>
                    </svg>
                </div>
                <div>
                    <p class="text-sm font-semibold text-[#1e3a8a]">{{ __('Rekening Tujuan Transfer') }}</p>
                    <p class="text-xs text-gray-600 mt-0.5">{{ __('Silakan transfer sesuai nominal total tagihan, lalu unggah bukti transfer di bawah ini.') }}</p>
                </div>
            </div>

            <div class="bg-white rounded-xl border border-blue-100 p-4 space-y-3 text-sm">
                <div class="flex items-center justify-between">
                    <span class="text-xs text-gray-500">Bank</span>
                    <span class="font-bold text-gray-800">{{ $bankName }}</span>
                </div>
                <div class="flex items-center justify-between gap-2" x-data="{ copied: false }">
                    <span class="text-xs text-gray-500">No. Rekening</span>
                    <div class="flex items-center gap-2">
                        <span class="font-mono font-bold text-[#3a6484] text-base">{{ $accountNumber }}</span>
                        <button type="button"
                            @click="navigator.clipboard.writeText('{{ $accountNumber }}'); copied = true; setTimeout(() => copied = false, 2000)"
                            class="text-xs px-2.5 py-1 rounded-lg font-medium transition"
                            :class="copied ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-600 hover:bg-gray-200'">
                            <span x-text="copied ? '{{ __('Tersalin!') }}' : '{{ __('Salin') }}'"></span>
                        </button>
                    </div>
                </div>
                <div class="flex items-center justify-between">
                    <span class="text-xs text-gray-500">{{ __('Atas Nama') }}</span>
                    <span class="font-medium text-gray-800">{{ $accountName }}</span>
                </div>
                <div class="pt-2 border-t border-gray-100 flex items-center justify-between gap-2" x-data="{ copiedAmt: false }">
                    <span class="text-xs font-medium text-gray-600">{{ __('Total Pembayaran') }}</span>
                    <div class="flex items-center gap-2">
                        <span class="font-bold text-[#3a6484] text-base">Rp {{ number_format($booking->total_price, 0, ',', '.') }}</span>
                        <button type="button"
                            @click="navigator.clipboard.writeText('{{ (int) round((float) $booking->total_price) }}'); copiedAmt = true; setTimeout(() => copiedAmt = false, 2000)"
                            class="text-xs px-2.5 py-1 rounded-lg font-medium transition"
                            :class="copiedAmt ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-600 hover:bg-gray-200'">
                            <span x-text="copiedAmt ? '{{ __('Tersalin!') }}' : '{{ __('Salin') }}'"></span>
                        </button>
                    </div>
                </div>
            </div>
        </div>

        @if($errors->any())
            <div class="mb-4 p-3 bg-red-50 border border-red-200 rounded-xl text-sm text-red-600">
                {{ $errors->first() }}
            </div>
        @endif

        <form method="POST"
              action="{{ route('guest.payment.upload.post', $bookingCode) }}"
              enctype="multipart/form-data"
              x-data="{
                  submitting: false,
                  fileName: '',
                  fileSizeError: false,
                  previewUrl: null,
                  isPdf: false,
                  handleFile(e) {
                      const file = e.target.files[0];
                      this.fileSizeError = false;
                      this.previewUrl = null;
                      this.isPdf = false;
                      if (!file) {
                          this.fileName = '';
                          return;
                      }
                      this.fileName = file.name;
                      if (file.size > 5 * 1024 * 1024) {
                          this.fileSizeError = true;
                          return;
                      }
                      if (file.type === 'application/pdf') {
                          this.isPdf = true;
                      } else if (file.type.startsWith('image/')) {
                          this.previewUrl = URL.createObjectURL(file);
                      }
                  }
              }"
              @submit="if (!fileSizeError) submitting = true"
              class="space-y-4">
            @csrf

            {{-- Data diri --}}
            <div class="space-y-3">
                <h2 class="text-sm font-semibold text-gray-700">{{ __('Data Diri Pemesan') }}</h2>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('Nama Lengkap') }}</label>
                    <input type="text" name="guest_name" required maxlength="100"
                        value="{{ old('guest_name', $booking->guest_name) }}"
                        placeholder="{{ __('Nama Anda') }}"
                        class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-[#3a6484]/30 focus:border-[#3a6484]">
                    @error('guest_name') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('Nomor HP / WhatsApp') }}</label>
                    <input type="tel" name="guest_phone" required maxlength="20"
                        value="{{ old('guest_phone', $booking->guest_phone) }}"
                        placeholder="08xxxxxxxxxx"
                        class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-[#3a6484]/30 focus:border-[#3a6484]">
                    @error('guest_phone') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('Email') }}</label>
                    <input type="email" name="guest_email" required maxlength="150"
                        value="{{ old('guest_email', $booking->guest_email) }}"
                        placeholder="email@contoh.com"
                        class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-[#3a6484]/30 focus:border-[#3a6484]">
                    @error('guest_email') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('Asal Kota') }}</label>
                    <input type="text" name="guest_city" required maxlength="100"
                        value="{{ old('guest_city', $booking->guest_city) }}"
                        placeholder="{{ __('Contoh: Jakarta, Bandung, Surabaya') }}"
                        class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-[#3a6484]/30 focus:border-[#3a6484]">
                    @error('guest_city') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>
            </div>

            {{-- Jumlah tamu --}}
            <div class="space-y-3">
                <div class="flex items-center justify-between">
                    <h2 class="text-sm font-semibold text-gray-700">{{ __('Jumlah Tamu yang Menginap') }}</h2>
                    <span class="text-xs font-medium text-[#3a6484] bg-blue-50 px-2.5 py-1 rounded-full">
                        {{ __('Maks.') }} {{ $booking->villa->capacity }} {{ __('orang (dewasa + anak-anak)') }}
                    </span>
                </div>
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('Dewasa') }}</label>
                        <input type="number" name="adult_count" required min="1" max="{{ $booking->villa->capacity }}"
                            value="{{ old('adult_count', $booking->adult_count ?? 1) }}"
                            class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-[#3a6484]/30 focus:border-[#3a6484]">
                        @error('adult_count') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('Anak-anak') }}</label>
                        <input type="number" name="child_count" required min="0" max="{{ $booking->villa->capacity }}"
                            value="{{ old('child_count', $booking->child_count ?? 0) }}"
                            class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-[#3a6484]/30 focus:border-[#3a6484]">
                        @error('child_count') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>
                </div>
            </div>

            {{-- Upload Bukti Transfer --}}
            <div class="space-y-2 pt-2">
                <label class="block text-sm font-semibold text-gray-700">{{ __('Unggah Bukti Pembayaran') }}</label>
                <label class="flex flex-col items-center justify-center w-full border-2 border-dashed border-gray-300 hover:border-[#3a6484] rounded-2xl p-6 cursor-pointer bg-gray-50/70 hover:bg-blue-50/30 transition text-center">
                    <svg class="w-8 h-8 text-[#3a6484] mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/>
                    </svg>
                    <span class="text-sm font-medium text-gray-700" x-text="fileName || '{{ __('Pilih file bukti transfer (struk / screenshot)') }}'"></span>
                    <span class="text-xs text-gray-400 mt-1">{{ __('Format JPG, PNG, WebP, atau PDF (Maks. 5MB)') }}</span>
                    <input type="file" name="receipt" required class="hidden"
                           accept=".jpg,.jpeg,.png,.webp,.pdf,image/jpeg,image/png,image/webp,application/pdf"
                           @change="handleFile($event)">
                </label>

                <p x-show="fileSizeError" class="text-red-500 text-xs" style="display: none;">
                    {{ __('Ukuran file melebihi batas maksimal 5MB.') }}
                </p>

                <div x-show="previewUrl" class="mt-2 rounded-xl overflow-hidden border border-gray-200 bg-white p-2 max-w-xs mx-auto" style="display: none;">
                    <img :src="previewUrl" alt="Preview Bukti Transfer" class="w-full h-auto max-h-48 object-contain rounded-lg">
                </div>

                <div x-show="isPdf" class="mt-2 inline-flex items-center gap-2 px-3 py-1.5 bg-red-50 text-red-700 border border-red-200 rounded-lg text-xs font-medium" style="display: none;">
                    <span>📄</span>
                    <span x-text="fileName"></span>
                </div>

                @error('receipt') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
            </div>

            {{-- Ringkasan pembayaran --}}
            <div class="bg-gray-50 border border-gray-200 rounded-xl p-4 flex items-center justify-between">
                <div>
                    <p class="text-xs text-gray-400">{{ __('Total Pembayaran') }}</p>
                    <p class="text-[#3a6484] font-bold text-xl">Rp {{ number_format($booking->total_price, 0, ',', '.') }}</p>
                </div>
                <svg class="w-8 h-8 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/>
                </svg>
            </div>

            <button type="submit"
                :disabled="submitting || fileSizeError"
                class="w-full py-3.5 bg-[#3a6484] text-white rounded-xl font-semibold hover:bg-[#2f5370] transition flex items-center justify-center gap-2 disabled:opacity-50 cursor-pointer">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                </svg>
                <span x-text="submitting ? '{{ __('Mengirim...') }}' : '{{ __('Kirim Bukti Pembayaran') }}'"></span>
            </button>
            <p class="text-xs text-center text-gray-400">{{ __('Pembayaran diverifikasi manual oleh admin Boboin Villa dalam 1×24 jam.') }}</p>
        </form>
    @endif
</div>
