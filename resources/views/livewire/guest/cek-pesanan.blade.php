<?php

use App\Models\Booking;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Url;
use Livewire\Volt\Component;

new #[Layout('layouts.app')] class extends Component
{
    #[Url(as: 'kode')]
    public string $kode = '';

    public ?Booking $booking = null;
    public bool     $searched = false;

    public function mount(): void
    {
        if ($this->kode) {
            $this->cariPesanan();
        }
    }

    public function cariPesanan(): void
    {
        $this->searched = true;
        $this->booking  = Booking::with(['villa', 'payment'])
            ->where('booking_code', strtoupper(trim($this->kode)))
            ->first();
    }

    public function checkStatus(): void
    {
        if (!$this->booking) return;

        $fresh = Booking::with(['villa', 'payment'])
            ->where('booking_code', $this->booking->booking_code)
            ->first();

        if (!$fresh) return;

        $this->booking = $fresh;

        if ($fresh->status === 'approved') {
            $this->redirect(route('guest.payment.upload', $fresh->booking_code));
        }
        // status paid: re-render saja — Livewire akan tampilkan UI invoice otomatis
    }
}; ?>

<div class="max-w-xl mx-auto px-4 sm:px-6 py-12">
    <div class="text-center mb-8">
        <h1 class="text-2xl font-medium text-[#3a6484] mb-1">Cek Status Pesanan</h1>
        <p class="text-gray-500 text-sm">Masukkan kode booking yang Anda terima saat memesan.</p>
    </div>

    {{-- Form input kode --}}
    <form wire:submit="cariPesanan" class="flex gap-2 mb-8">
        <input wire:model="kode" type="text"
            placeholder="Contoh: BVL-A3X9K2"
            class="flex-1 border border-gray-200 rounded-xl px-4 py-3 text-sm uppercase tracking-widest focus:outline-none focus:ring-2 focus:ring-[#3a6484]/30 focus:border-[#3a6484]">
        <button type="submit"
            class="px-5 py-3 bg-[#3a6484] text-white rounded-xl font-medium hover:bg-[#2f5370] transition text-sm shrink-0">
            Cek
        </button>
    </form>

    {{-- Hasil --}}
    @if($searched)
        @if(!$booking)
            <div class="text-center py-12 bg-white border border-gray-100 rounded-2xl shadow-sm">
                <svg class="w-12 h-12 mx-auto text-gray-300 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                <p class="font-medium text-gray-600">Kode booking tidak ditemukan.</p>
                <p class="text-sm text-gray-400 mt-1">Periksa kembali kode yang Anda masukkan.</p>
            </div>
        @else
            @php
                $statusConfig = [
                    'waiting_admin'   => ['label' => 'Menunggu Konfirmasi Admin', 'class' => 'bg-yellow-100 text-yellow-700', 'icon' => '⏳'],
                    'approved'        => ['label' => 'Disetujui — Silakan Bayar', 'class' => 'bg-blue-100 text-blue-700',   'icon' => '✅'],
                    'rejected'        => ['label' => 'Ditolak',                   'class' => 'bg-red-100 text-red-700',     'icon' => '❌'],
                    'pending_payment' => ['label' => 'Menunggu Verifikasi Bayar', 'class' => 'bg-orange-100 text-orange-700','icon' => '🕐'],
                    'paid'            => ['label' => 'Lunas',                     'class' => 'bg-green-100 text-green-700', 'icon' => '💚'],
                    'completed'       => ['label' => 'Selesai',                   'class' => 'bg-gray-100 text-gray-600',   'icon' => '🏁'],
                    'cancelled'       => ['label' => 'Dibatalkan',                'class' => 'bg-red-50 text-red-400',      'icon' => '🚫'],
                ];
                $st = $statusConfig[$booking->status] ?? ['label' => $booking->status, 'class' => 'bg-gray-100 text-gray-500', 'icon' => '•'];
            @endphp

            <div class="bg-white border border-gray-100 rounded-2xl shadow-sm overflow-hidden"
                 @if(in_array($booking->status, ['waiting_admin', 'pending_payment'])) wire:poll.3000ms="checkStatus" @endif>
                {{-- Header --}}
                <div class="px-6 py-4 border-b border-gray-100 flex items-center justify-between">
                    <div>
                        <p class="text-xs text-gray-400 mb-0.5">Kode Booking</p>
                        <p class="font-bold text-[#3a6484] tracking-widest text-lg">{{ $booking->booking_code }}</p>
                    </div>
                    <span class="text-xs font-medium px-3 py-1.5 rounded-full {{ $st['class'] }}">
                        {{ $st['icon'] }} {{ $st['label'] }}
                    </span>
                </div>

                <div class="p-6 space-y-4">
                    {{-- Info tamu --}}
                    @if($booking->guest_name)
                        <div class="bg-gray-50 rounded-xl p-4 text-sm space-y-1">
                            <p class="font-medium text-gray-800">{{ $booking->guest_name }}</p>
                            <p class="text-gray-500">{{ $booking->guest_phone }}</p>
                            <p class="text-gray-500">{{ $booking->guest_email }}</p>
                        </div>
                    @endif

                    {{-- Info villa --}}
                    <div class="grid grid-cols-2 gap-4 text-sm">
                        <div class="col-span-2">
                            <p class="text-xs text-gray-400 mb-0.5">Villa</p>
                            <p class="font-medium text-gray-800">{{ $booking->villa->name }}</p>
                            <p class="text-xs text-gray-500">{{ $booking->villa->city }}</p>
                        </div>
                        <div>
                            <p class="text-xs text-gray-400 mb-0.5">Check-in</p>
                            <p class="font-medium text-gray-800">{{ $booking->check_in->format('d M Y') }}</p>
                        </div>
                        <div>
                            <p class="text-xs text-gray-400 mb-0.5">Check-out</p>
                            <p class="font-medium text-gray-800">{{ $booking->check_out->format('d M Y') }}</p>
                        </div>
                        <div>
                            <p class="text-xs text-gray-400 mb-0.5">Durasi</p>
                            <p class="font-medium text-gray-800">{{ $booking->check_in->diffInDays($booking->check_out) }} malam</p>
                        </div>
                        <div>
                            <p class="text-xs text-gray-400 mb-0.5">Total</p>
                            <p class="font-semibold text-[#3a6484]">Rp {{ number_format($booking->total_price, 0, ',', '.') }}</p>
                        </div>
                    </div>

                    {{-- Alasan tolak --}}
                    @if($booking->status === 'rejected' && $booking->rejection_note)
                        <div class="flex items-start gap-2 bg-red-50 border border-red-100 rounded-xl px-4 py-3 text-sm">
                            <svg class="w-4 h-4 text-red-400 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            <div>
                                <p class="text-xs font-medium text-red-600 mb-0.5">Alasan Penolakan</p>
                                <p class="text-red-700">{{ $booking->rejection_note }}</p>
                            </div>
                        </div>
                    @endif

                    {{-- Aksi --}}
                    <div class="pt-2 space-y-2">
                        @if($booking->status === 'waiting_admin')
                            <div class="flex items-center gap-2.5 bg-yellow-50 border border-yellow-100 rounded-xl px-4 py-3 text-sm text-yellow-700">
                                <svg class="w-4 h-4 shrink-0 animate-spin text-yellow-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                                </svg>
                                <span>Memantau status secara otomatis... Halaman akan berpindah otomatis ketika admin menyetujui.</span>
                            </div>
                        @elseif($booking->status === 'approved')
                            <a href="{{ route('guest.payment.upload', $booking->booking_code) }}"
                               class="block w-full py-3 bg-[#3a6484] text-white rounded-xl font-medium hover:bg-[#2f5370] transition text-center text-sm">
                                Lanjut ke Pembayaran
                            </a>
                        @elseif($booking->status === 'pending_payment')
                            <div class="flex items-center gap-2.5 bg-orange-50 border border-orange-100 rounded-xl px-4 py-3 text-sm text-orange-700">
                                <svg class="w-4 h-4 shrink-0 animate-spin text-orange-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                                </svg>
                                <span>Menunggu konfirmasi pembayaran... Halaman akan diperbarui otomatis.</span>
                            </div>
                            @if($booking->payment?->xendit_invoice_url)
                                <a href="{{ $booking->payment->xendit_invoice_url }}" target="_blank"
                                   class="block w-full py-3 border border-[#3a6484] text-[#3a6484] rounded-xl font-medium hover:bg-[#3a6484] hover:text-white transition text-center text-sm">
                                    Lanjutkan Pembayaran →
                                </a>
                            @endif
                        @elseif($booking->status === 'paid')
                            <div class="flex items-center gap-2.5 bg-green-50 border border-green-200 rounded-xl px-4 py-3 text-sm text-green-700">
                                <svg class="w-5 h-5 shrink-0 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                                <span class="font-medium">Pembayaran berhasil! Sampai jumpa di villa.</span>
                            </div>
                            <a href="{{ route('guest.invoice.download', $booking->booking_code) }}"
                               class="block w-full py-3 bg-[#3a6484] text-white rounded-xl font-medium hover:bg-[#2f5370] transition text-center text-sm">
                                Unduh Invoice PDF
                            </a>
                        @elseif($booking->status === 'completed')
                            <div class="bg-gray-50 border border-gray-200 rounded-xl px-4 py-3 text-sm text-gray-600 text-center">
                                Terima kasih telah menginap di Boboin Villa!
                            </div>
                            <a href="{{ route('guest.invoice.download', $booking->booking_code) }}"
                               class="block w-full py-3 border border-[#3a6484] text-[#3a6484] rounded-xl font-medium hover:bg-[#3a6484] hover:text-white transition text-center text-sm">
                                Unduh Invoice PDF
                            </a>
                        @elseif($booking->status === 'rejected' || $booking->status === 'cancelled')
                            <a href="{{ route('villas.show', $booking->villa->slug) }}" wire:navigate
                               class="block w-full py-3 border border-[#3a6484] text-[#3a6484] rounded-xl font-medium hover:bg-[#3a6484] hover:text-white transition text-center text-sm">
                                Pesan Villa Ini Lagi
                            </a>
                        @endif

                        {{-- WhatsApp bantuan dengan kode booking --}}
                        @if(!in_array($booking->status, ['rejected', 'cancelled']))
                            @php
                                $waMsg = urlencode("Halo Boboin Villa, saya butuh bantuan untuk pesanan dengan kode booking {$booking->booking_code}.");
                            @endphp
                            <a href="https://wa.me/6282215433017?text={{ $waMsg }}" target="_blank"
                               class="flex items-center justify-center gap-2 w-full py-2.5 text-sm text-gray-500 hover:text-green-600 transition">
                                <svg class="w-4 h-4" viewBox="0 0 24 24" fill="currentColor">
                                    <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347z"/>
                                    <path d="M12 0C5.373 0 0 5.373 0 12c0 2.126.558 4.121 1.532 5.849L.057 23.516a.75.75 0 00.927.927l5.666-1.475A11.934 11.934 0 0012 24c6.627 0 12-5.373 12-12S18.627 0 12 0zm0 21.75a9.734 9.734 0 01-4.964-1.359l-.356-.212-3.693.96.982-3.591-.232-.369A9.733 9.733 0 012.25 12C2.25 6.615 6.615 2.25 12 2.25S21.75 6.615 21.75 12 17.385 21.75 12 21.75z"/>
                                </svg>
                                Butuh bantuan? Chat WhatsApp
                            </a>
                        @endif
                    </div>
                </div>
            </div>
        @endif
    @endif
</div>
