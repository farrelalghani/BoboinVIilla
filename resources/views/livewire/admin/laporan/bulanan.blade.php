<?php

use App\Models\Booking;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;

new #[Layout('layouts.admin')] class extends Component
{
    public int $year;
    public int $month;

    public function mount(int $year, int $month): void
    {
        $this->year  = $year;
        $this->month = $month;
    }

    public function with(): array
    {
        $bookings = Booking::with(['villa'])
            ->whereYear('created_at', $this->year)
            ->whereMonth('created_at', $this->month)
            ->whereIn('status', ['paid', 'completed'])
            ->orderBy('created_at')
            ->get();

        return [
            'bookings'        => $bookings,
            'totalPendapatan' => $bookings->sum('total_price'),
            'namaBulan'       => \Carbon\Carbon::create($this->year, $this->month)->translatedFormat('F'),
        ];
    }
}; ?>

<div>
    {{-- Header --}}
    <div class="flex items-center justify-between mb-6">
        <div class="flex items-center gap-3">
            <a href="{{ route('admin.dashboard') }}" wire:navigate
               class="p-2 text-gray-400 hover:text-gray-600 hover:bg-gray-100 rounded-lg transition">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                </svg>
            </a>
            <div>
                <h2 class="text-2xl font-semibold text-gray-800">Laporan {{ $namaBulan }} {{ $year }}</h2>
                <p class="text-sm text-gray-400 mt-0.5">{{ $bookings->count() }} pesanan lunas · Total Rp {{ number_format($totalPendapatan, 0, ',', '.') }}</p>
            </div>
        </div>
        <a href="{{ route('admin.export.bookings', ['year' => $year, 'month' => $month]) }}"
           class="inline-flex items-center gap-2 px-4 py-2 bg-[#3a6484] text-white rounded-xl text-sm font-medium hover:bg-[#2f5370] transition">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
            </svg>
            Export Excel
        </a>
    </div>

    {{-- Tabel rincian --}}
    <div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden">
        @if($bookings->isEmpty())
            <div class="px-6 py-16 text-center text-gray-400">
                <svg class="w-12 h-12 mx-auto mb-3 opacity-30" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                        d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                </svg>
                <p class="font-medium">Tidak ada pesanan lunas di bulan ini.</p>
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="bg-gray-50 text-gray-500 text-xs uppercase tracking-wide">
                        <tr>
                            <th class="px-5 py-3 text-left">No</th>
                            <th class="px-5 py-3 text-left">Kode Booking</th>
                            <th class="px-5 py-3 text-left">Tamu</th>
                            <th class="px-5 py-3 text-left">Villa</th>
                            <th class="px-5 py-3 text-left">Check-in</th>
                            <th class="px-5 py-3 text-left">Check-out</th>
                            <th class="px-5 py-3 text-center">Durasi</th>
                            <th class="px-5 py-3 text-center">Tamu</th>
                            <th class="px-5 py-3 text-right">Total</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-50">
                        @foreach($bookings as $i => $booking)
                            <tr class="hover:bg-gray-50 transition">
                                <td class="px-5 py-3.5 text-gray-400 text-xs">{{ $i + 1 }}</td>
                                <td class="px-5 py-3.5">
                                    <span class="font-mono text-[#3a6484] font-semibold text-xs">
                                        {{ $booking->booking_code }}
                                    </span>
                                </td>
                                <td class="px-5 py-3.5">
                                    <p class="font-medium text-gray-800">{{ $booking->guest_name ?? '-' }}</p>
                                    @if($booking->guest_email)
                                        <p class="text-xs text-gray-400">{{ $booking->guest_email }}</p>
                                    @endif
                                    @if($booking->guest_city)
                                        <p class="text-xs text-gray-400">{{ $booking->guest_city }}</p>
                                    @endif
                                </td>
                                <td class="px-5 py-3.5 text-gray-700">{{ $booking->villa->name }}</td>
                                <td class="px-5 py-3.5 text-gray-600">{{ $booking->check_in->format('d M Y') }}</td>
                                <td class="px-5 py-3.5 text-gray-600">{{ $booking->check_out->format('d M Y') }}</td>
                                <td class="px-5 py-3.5 text-center text-gray-600">
                                    {{ $booking->check_in->diffInDays($booking->check_out) }} malam
                                </td>
                                <td class="px-5 py-3.5 text-center text-gray-600 text-xs">
                                    @if($booking->adult_count !== null)
                                        {{ $booking->adult_count }} dewasa
                                        @if($booking->child_count)
                                            <br>{{ $booking->child_count }} anak
                                        @endif
                                    @else
                                        —
                                    @endif
                                </td>
                                <td class="px-5 py-3.5 text-right font-semibold text-gray-800">
                                    Rp {{ number_format($booking->total_price, 0, ',', '.') }}
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                    <tfoot class="bg-gray-50 border-t-2 border-gray-200">
                        <tr>
                            <td colspan="8" class="px-5 py-3 text-right font-semibold text-gray-700">
                                Total Pendapatan {{ $namaBulan }} {{ $year }}
                            </td>
                            <td class="px-5 py-3 text-right font-bold text-[#1e3a8a] text-base">
                                Rp {{ number_format($totalPendapatan, 0, ',', '.') }}
                            </td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        @endif
    </div>
</div>
