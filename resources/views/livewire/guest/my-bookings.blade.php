<?php

use App\Models\Booking;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;

new #[Layout('layouts.app')] class extends Component
{
    public function with(): array
    {
        return [
            'bookings' => Booking::with('villa')
                ->where('guest_id', auth()->id())
                ->latest()
                ->get(),
        ];
    }
}; ?>

<div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-10">
    @if(session('upload_success'))
        <div class="mb-6 flex items-start gap-3 bg-green-50 border border-green-200 rounded-xl px-4 py-3 text-sm text-green-700">
            <svg class="w-5 h-5 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            {{ session('upload_success') }}
        </div>
    @endif

    <div class="mb-8">
        <h1 class="text-2xl font-medium text-[#3a6484] mb-1">Pesanan Saya</h1>
        <p class="text-gray-500 text-sm">Daftar semua pemesanan villa yang pernah Anda ajukan.</p>
    </div>

    @if($bookings->isEmpty())
        <div class="text-center py-20 bg-white rounded-2xl border border-gray-100 shadow-sm">
            <svg class="w-16 h-16 mx-auto text-gray-300 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                    d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
            </svg>
            <p class="text-gray-500 text-lg font-medium mb-2">Belum ada pesanan</p>
            <p class="text-gray-400 text-sm mb-6">Yuk mulai cari villa impian Anda!</p>
            <a href="{{ route('villas.index') }}" wire:navigate
               class="inline-block px-6 py-3 bg-[#3a6484] text-white rounded-xl font-medium hover:bg-[#2f5370] transition text-sm">
                Cari Villa Sekarang
            </a>
        </div>
    @else
        <div class="space-y-4">
            @foreach($bookings as $booking)
                @php
                    $statusConfig = [
                        'waiting_admin'   => ['label' => 'Menunggu Konfirmasi', 'class' => 'bg-yellow-100 text-yellow-700'],
                        'approved'        => ['label' => 'Disetujui', 'class' => 'bg-blue-100 text-blue-700'],
                        'rejected'        => ['label' => 'Ditolak', 'class' => 'bg-red-100 text-red-700'],
                        'pending_payment' => ['label' => 'Menunggu Pembayaran', 'class' => 'bg-orange-100 text-orange-700'],
                        'paid'            => ['label' => 'Lunas', 'class' => 'bg-green-100 text-green-700'],
                        'completed'       => ['label' => 'Selesai', 'class' => 'bg-gray-100 text-gray-700'],
                        'cancelled'       => ['label' => 'Dibatalkan', 'class' => 'bg-red-100 text-red-500'],
                    ];
                    $status = $statusConfig[$booking->status] ?? ['label' => $booking->status, 'class' => 'bg-gray-100 text-gray-600'];
                @endphp

                <div class="bg-white border border-gray-100 rounded-2xl p-5 sm:p-6 shadow-sm">
                    <div class="flex items-start justify-between gap-4 mb-4">
                        <div>
                            <h3 class="font-semibold text-gray-800 text-base mb-0.5">
                                {{ $booking->villa->name }}
                            </h3>
                            <p class="text-[#3a6484] text-sm flex items-center gap-1">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                                </svg>
                                {{ $booking->villa->city }}
                            </p>
                        </div>
                        <span class="shrink-0 text-xs font-medium px-3 py-1 rounded-full {{ $status['class'] }}">
                            {{ $status['label'] }}
                        </span>
                    </div>

                    <div class="grid grid-cols-2 sm:grid-cols-3 gap-4 text-sm mb-4">
                        <div>
                            <p class="text-gray-400 text-xs mb-0.5">Check-in</p>
                            <p class="font-medium text-gray-700">{{ $booking->check_in->format('d M Y') }}</p>
                        </div>
                        <div>
                            <p class="text-gray-400 text-xs mb-0.5">Check-out</p>
                            <p class="font-medium text-gray-700">{{ $booking->check_out->format('d M Y') }}</p>
                        </div>
                        <div>
                            <p class="text-gray-400 text-xs mb-0.5">Total</p>
                            <p class="font-semibold text-[#3a6484]">Rp {{ number_format($booking->total_price, 0, ',', '.') }}</p>
                        </div>
                    </div>

                    {{-- Alasan penolakan --}}
                    @if($booking->status === 'rejected' && $booking->rejection_note)
                        <div class="mb-3 flex items-start gap-2 bg-red-50 border border-red-100 rounded-xl px-3 py-2.5 text-sm">
                            <svg class="w-4 h-4 text-red-400 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            <div>
                                <p class="text-xs font-medium text-red-600 mb-0.5">Alasan Penolakan</p>
                                <p class="text-red-700">{{ $booking->rejection_note }}</p>
                            </div>
                        </div>
                    @endif

                    <div class="flex items-center justify-between pt-3 border-t border-gray-100">
                        <p class="text-xs text-gray-400">
                            Diajukan {{ $booking->created_at->diffForHumans() }}
                        </p>

                        @if($booking->status === 'approved')
                            <a href="{{ route('guest.payment.upload', $booking->id) }}" wire:navigate
                               class="text-xs font-medium text-[#3a6484] hover:underline">
                                Lanjut Pembayaran →
                            </a>
                        @elseif($booking->status === 'rejected' || $booking->status === 'cancelled')
                            <a href="{{ route('villas.show', $booking->villa->slug) }}" wire:navigate
                               class="text-xs font-medium text-[#3a6484] hover:underline flex items-center gap-1">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                                </svg>
                                Pesan Lagi →
                            </a>
                        @elseif($booking->status === 'paid')
                            <a href="{{ route('guest.invoice.download', $booking->id) }}"
                               class="text-xs font-medium text-[#3a6484] hover:underline">
                                Unduh Invoice →
                            </a>
                        @endif
                    </div>
                </div>
            @endforeach
        </div>
    @endif
</div>
