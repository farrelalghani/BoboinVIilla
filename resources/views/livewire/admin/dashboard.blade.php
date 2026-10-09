<?php

use App\Models\Booking;
use App\Models\Villa;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;

new #[Layout('layouts.admin')] class extends Component
{
    public int $selectedYear;

    public function mount(): void
    {
        $this->selectedYear = now()->year;
    }

    public function with(): array
    {
        $monthlyData = Booking::selectRaw('
                MONTH(created_at) as month,
                COUNT(*) as total_pesanan,
                SUM(total_price) as total_pendapatan
            ')
            ->whereYear('created_at', $this->selectedYear)
            ->whereIn('status', ['paid', 'completed'])
            ->groupByRaw('MONTH(created_at)')
            ->orderByRaw('MONTH(created_at)')
            ->get()
            ->keyBy('month');

        return [
            'totalVilla'         => Villa::count(),
            'pesananMasuk'       => Booking::where('status', 'waiting_admin')->count(),
            'pendapatanBulanIni' => Booking::whereIn('status', ['paid', 'completed'])
                                        ->whereYear('created_at', now()->year)
                                        ->whereMonth('created_at', now()->month)
                                        ->sum('total_price'),
            'recentBookings'     => Booking::with(['villa'])->latest()->take(6)->get(),
            'statusDistribution' => Booking::selectRaw('status, COUNT(*) as count')
                                        ->groupBy('status')
                                        ->pluck('count', 'status'),
            'monthlyData'        => $monthlyData,
            'availableYears'     => Booking::selectRaw('YEAR(created_at) as year')
                                        ->whereIn('status', ['paid', 'completed'])
                                        ->groupBy('year')
                                        ->orderByDesc('year')
                                        ->pluck('year'),
        ];
    }
}; ?>

<div>
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-1 mb-6 sm:mb-8">
        <h2 class="text-xl sm:text-2xl font-semibold text-gray-800">Dashboard</h2>
        <p class="text-sm text-gray-400">{{ now()->isoFormat('dddd, D MMMM Y') }}</p>
    </div>

    {{-- Metric Cards --}}
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 sm:gap-5 mb-6 sm:mb-8">
        <div class="bg-white rounded-xl border border-gray-200 p-4 sm:p-6 flex items-center gap-4 shadow-sm">
            <div class="w-12 h-12 rounded-xl bg-[#1e3a8a]/10 flex items-center justify-center shrink-0">
                <svg class="w-6 h-6 text-[#1e3a8a]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                </svg>
            </div>
            <div class="min-w-0">
                <p class="text-sm text-gray-500">Total Villa</p>
                <p class="text-2xl sm:text-3xl font-bold text-[#1e3a8a]">{{ $totalVilla }}</p>
            </div>
        </div>

        <div class="bg-white rounded-xl border border-gray-200 p-4 sm:p-6 flex items-center gap-4 shadow-sm">
            <div class="w-12 h-12 rounded-xl bg-yellow-100 flex items-center justify-center shrink-0">
                <svg class="w-6 h-6 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
            </div>
            <div class="min-w-0">
                <p class="text-sm text-gray-500">Menunggu Konfirmasi</p>
                <p class="text-2xl sm:text-3xl font-bold text-yellow-600">{{ $pesananMasuk }}</p>
            </div>
        </div>

        <div class="bg-white rounded-xl border border-gray-200 p-4 sm:p-6 flex items-center gap-4 shadow-sm">
            <div class="w-12 h-12 rounded-xl bg-green-100 flex items-center justify-center shrink-0">
                <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
            </div>
            <div class="min-w-0">
                <p class="text-sm text-gray-500">Pendapatan Bulan Ini</p>
                <p class="text-lg sm:text-xl font-bold text-green-600 truncate">Rp {{ number_format($pendapatanBulanIni, 0, ',', '.') }}</p>
            </div>
        </div>
    </div>

    {{-- Grafik --}}
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-5 mb-6 sm:mb-8">

        {{-- Bar chart: Pendapatan per bulan --}}
        <div class="lg:col-span-2 bg-white rounded-xl border border-gray-200 shadow-sm p-4 sm:p-6">
            <h3 class="font-semibold text-gray-800 mb-4">Pendapatan per Bulan — {{ $selectedYear }}</h3>
            <div class="relative h-64 sm:h-72">
                <canvas id="chartPendapatan"></canvas>
            </div>
        </div>

        {{-- Donut chart: Distribusi status --}}
        <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-4 sm:p-6">
            <h3 class="font-semibold text-gray-800 mb-4">Status Pesanan</h3>
            <div class="relative h-52 sm:h-56">
                <canvas id="chartStatus"></canvas>
            </div>
            <div class="mt-4 space-y-1.5" id="legendStatus"></div>
        </div>
    </div>

    @php
        $namaBulanPendek = ['Jan','Feb','Mar','Apr','Mei','Jun','Jul','Agt','Sep','Okt','Nov','Des'];
        $pendapatanArr   = [];
        $pesananArr      = [];
        for ($i = 1; $i <= 12; $i++) {
            $d = $monthlyData->get($i);
            $pendapatanArr[] = $d ? (float) $d->total_pendapatan : 0;
            $pesananArr[]    = $d ? (int) $d->total_pesanan : 0;
        }
        $statusLabels = [
            'waiting_admin'   => 'Menunggu',
            'approved'        => 'Disetujui',
            'pending_payment' => 'Belum Bayar',
            'paid'            => 'Lunas',
            'completed'       => 'Selesai',
            'rejected'        => 'Ditolak',
            'cancelled'       => 'Dibatalkan',
        ];
        $statusColors = [
            'waiting_admin'   => '#f59e0b',
            'approved'        => '#3b82f6',
            'pending_payment' => '#f97316',
            'paid'            => '#22c55e',
            'completed'       => '#6b7280',
            'rejected'        => '#ef4444',
            'cancelled'       => '#fca5a5',
        ];
        $donutLabels = [];
        $donutData   = [];
        $donutColors = [];
        foreach ($statusDistribution as $status => $count) {
            $donutLabels[] = $statusLabels[$status] ?? $status;
            $donutData[]   = (int) $count;
            $donutColors[] = $statusColors[$status] ?? '#d1d5db';
        }
    @endphp

    @script
    <script>
        const pendapatanData = @json($pendapatanArr);
        const pesananData    = @json($pesananArr);
        const bulanLabels    = @json($namaBulanPendek);
        const donutLabels    = @json($donutLabels);
        const donutData      = @json($donutData);
        const donutColors    = @json($donutColors);

        // Destroy chart lama jika ada (saat year ganti, component re-render)
        if (window._chartPendapatan) window._chartPendapatan.destroy();
        if (window._chartStatus)    window._chartStatus.destroy();

        // Bar chart pendapatan
        const ctxP = document.getElementById('chartPendapatan');
        if (ctxP) {
            window._chartPendapatan = new Chart(ctxP, {
                type: 'bar',
                data: {
                    labels: bulanLabels,
                    datasets: [{
                        label: 'Pendapatan (Rp)',
                        data: pendapatanData,
                        backgroundColor: 'rgba(30,58,138,0.15)',
                        borderColor: '#1e3a8a',
                        borderWidth: 2,
                        borderRadius: 6,
                        hoverBackgroundColor: 'rgba(30,58,138,0.3)',
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { display: false },
                        tooltip: {
                            callbacks: {
                                label: ctx => 'Rp ' + ctx.parsed.y.toLocaleString('id-ID')
                            }
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: {
                                callback: val => 'Rp ' + (val >= 1000000
                                    ? (val/1000000).toFixed(1) + 'jt'
                                    : val.toLocaleString('id-ID'))
                            },
                            grid: { color: '#f3f4f6' }
                        },
                        x: { grid: { display: false } }
                    }
                }
            });
        }

        // Donut chart status
        const ctxS = document.getElementById('chartStatus');
        if (ctxS) {
            window._chartStatus = new Chart(ctxS, {
                type: 'doughnut',
                data: {
                    labels: donutLabels,
                    datasets: [{
                        data: donutData,
                        backgroundColor: donutColors,
                        borderWidth: 2,
                        borderColor: '#fff',
                        hoverOffset: 6,
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    cutout: '65%',
                    plugins: {
                        legend: { display: false },
                        tooltip: {
                            callbacks: {
                                label: ctx => ctx.label + ': ' + ctx.parsed + ' pesanan'
                            }
                        }
                    }
                }
            });

            // Custom legend
            const legend = document.getElementById('legendStatus');
            if (legend) {
                legend.innerHTML = donutLabels.map((label, i) => `
                    <div style="display:flex;align-items:center;gap:8px;font-size:12px;color:#374151;">
                        <span style="display:inline-block;width:10px;height:10px;border-radius:3px;background:${donutColors[i]};flex-shrink:0;"></span>
                        <span>${label}</span>
                        <span style="margin-left:auto;font-weight:600;">${donutData[i]}</span>
                    </div>
                `).join('');
            }
        }
    </script>
    @endscript

    {{-- Riwayat Pesanan Per Bulan --}}
    <div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden mb-8">
        <div class="flex flex-wrap items-center justify-between gap-3 px-6 py-4 border-b border-gray-100">
            <h3 class="font-semibold text-gray-800">Riwayat Pesanan per Bulan</h3>
            <div class="flex items-center gap-3">
                {{-- Filter Tahun --}}
                <select wire:model.live="selectedYear"
                    class="text-sm border border-gray-200 rounded-lg px-3 py-1.5 focus:outline-none focus:ring-2 focus:ring-[#3a6484]/30">
                    @forelse($availableYears as $year)
                        <option value="{{ $year }}">{{ $year }}</option>
                    @empty
                        <option value="{{ now()->year }}">{{ now()->year }}</option>
                    @endforelse
                </select>

                {{-- Export Tahun Penuh --}}
                <a href="{{ route('admin.export.bookings', ['year' => $selectedYear]) }}"
                   class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-[#3a6484] text-white text-sm rounded-lg hover:bg-[#2f5370] transition">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                    </svg>
                    Export {{ $selectedYear }}
                </a>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-gray-50 text-gray-500 text-xs uppercase tracking-wide">
                    <tr>
                        <th class="px-5 py-3 text-left">Bulan</th>
                        <th class="px-5 py-3 text-center">Jumlah Pesanan</th>
                        <th class="px-5 py-3 text-right">Total Pendapatan</th>
                        <th class="px-5 py-3 text-center">Export</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    @php
                        $totalPesananTahunan   = 0;
                        $totalPendapatanTahunan = 0;
                        $namaBulan = [
                            1=>'Januari',2=>'Februari',3=>'Maret',4=>'April',
                            5=>'Mei',6=>'Juni',7=>'Juli',8=>'Agustus',
                            9=>'September',10=>'Oktober',11=>'November',12=>'Desember',
                        ];
                    @endphp

                    @for($m = 1; $m <= 12; $m++)
                        @php
                            $data       = $monthlyData->get($m);
                            $jumlah     = $data?->total_pesanan ?? 0;
                            $pendapatan = $data?->total_pendapatan ?? 0;
                            $totalPesananTahunan    += $jumlah;
                            $totalPendapatanTahunan += $pendapatan;
                            $isBulanIni = ($m == now()->month && $selectedYear == now()->year);
                        @endphp
                        <tr class="transition {{ $isBulanIni ? 'bg-blue-50/40' : 'hover:bg-gray-50' }}">
                            <td class="px-5 py-3 font-medium text-gray-800">
                                @if($jumlah > 0)
                                    <a href="{{ route('admin.laporan.bulanan', ['year' => $selectedYear, 'month' => $m]) }}"
                                       wire:navigate
                                       class="hover:text-[#3a6484] hover:underline transition flex items-center gap-1.5">
                                        {{ $namaBulan[$m] }}
                                        @if($isBulanIni)
                                            <span class="text-xs text-[#3a6484] font-normal">(bulan ini)</span>
                                        @endif
                                    </a>
                                @else
                                    <span class="text-gray-400">{{ $namaBulan[$m] }}</span>
                                    @if($isBulanIni)
                                        <span class="ml-1 text-xs text-[#3a6484] font-normal">(bulan ini)</span>
                                    @endif
                                @endif
                            </td>
                            <td class="px-5 py-3 text-center">
                                @if($jumlah > 0)
                                    <a href="{{ route('admin.laporan.bulanan', ['year' => $selectedYear, 'month' => $m]) }}" wire:navigate>
                                        <span class="px-2.5 py-1 bg-green-100 text-green-700 rounded-full text-xs font-medium hover:bg-green-200 transition">
                                            {{ $jumlah }} pesanan
                                        </span>
                                    </a>
                                @else
                                    <span class="text-gray-300">—</span>
                                @endif
                            </td>
                            <td class="px-5 py-3 text-right font-medium {{ $jumlah > 0 ? 'text-gray-800' : 'text-gray-300' }}">
                                @if($jumlah > 0)
                                    Rp {{ number_format($pendapatan, 0, ',', '.') }}
                                @else
                                    —
                                @endif
                            </td>
                            <td class="px-5 py-3 text-center">
                                @if($jumlah > 0)
                                    <a href="{{ route('admin.export.bookings', ['year' => $selectedYear, 'month' => $m]) }}"
                                       class="inline-flex items-center gap-1 text-xs text-[#3a6484] hover:underline">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                                        </svg>
                                        Excel
                                    </a>
                                @endif
                            </td>
                        </tr>
                    @endfor

                    {{-- Baris Total Tahunan --}}
                    <tr class="bg-gray-50 font-semibold border-t-2 border-gray-200">
                        <td class="px-5 py-3 text-gray-800">Total {{ $selectedYear }}</td>
                        <td class="px-5 py-3 text-center text-gray-800">{{ $totalPesananTahunan }} pesanan</td>
                        <td class="px-5 py-3 text-right text-[#1e3a8a]">
                            Rp {{ number_format($totalPendapatanTahunan, 0, ',', '.') }}
                        </td>
                        <td class="px-5 py-3 text-center">
                            @if($totalPesananTahunan > 0)
                                <a href="{{ route('admin.export.bookings', ['year' => $selectedYear]) }}"
                                   class="inline-flex items-center gap-1 text-xs font-medium text-[#3a6484] hover:underline">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                                    </svg>
                                    Semua
                                </a>
                            @endif
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>

    {{-- Pesanan Terbaru --}}
    <div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden">
        <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100">
            <h3 class="font-semibold text-gray-800">Pesanan Terbaru</h3>
            <a href="{{ route('admin.bookings.index') }}" wire:navigate
               class="text-sm text-[#1e3a8a] hover:underline">Lihat semua →</a>
        </div>

        @if($recentBookings->isEmpty())
            <div class="px-6 py-10 text-center text-gray-400 text-sm">Belum ada pesanan.</div>
        @else
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="bg-gray-50 text-gray-500 text-xs uppercase tracking-wide">
                        <tr>
                            <th class="px-6 py-3 text-left">Tamu</th>
                            <th class="px-6 py-3 text-left">Villa</th>
                            <th class="px-6 py-3 text-left">Check-in</th>
                            <th class="px-6 py-3 text-left">Total</th>
                            <th class="px-6 py-3 text-left">Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-50">
                        @foreach($recentBookings as $booking)
                            @php
                                $badge = [
                                    'waiting_admin'   => 'bg-yellow-100 text-yellow-700',
                                    'approved'        => 'bg-blue-100 text-blue-700',
                                    'rejected'        => 'bg-red-100 text-red-600',
                                    'pending_payment' => 'bg-orange-100 text-orange-700',
                                    'paid'            => 'bg-green-100 text-green-700',
                                    'completed'       => 'bg-gray-100 text-gray-600',
                                    'cancelled'       => 'bg-red-50 text-red-400',
                                ][$booking->status] ?? 'bg-gray-100 text-gray-500';
                                $label = [
                                    'waiting_admin'   => 'Menunggu',
                                    'approved'        => 'Disetujui',
                                    'rejected'        => 'Ditolak',
                                    'pending_payment' => 'Belum Bayar',
                                    'paid'            => 'Lunas',
                                    'completed'       => 'Selesai',
                                    'cancelled'       => 'Dibatalkan',
                                ][$booking->status] ?? $booking->status;
                            @endphp
                            <tr class="hover:bg-gray-50 transition">
                                <td class="px-6 py-3 font-medium text-gray-800">{{ $booking->guest_name ?? '-' }}</td>
                                <td class="px-6 py-3 text-gray-600">{{ $booking->villa->name }}</td>
                                <td class="px-6 py-3 text-gray-600">{{ $booking->check_in->format('d M Y') }}</td>
                                <td class="px-6 py-3 text-gray-800">Rp {{ number_format($booking->total_price, 0, ',', '.') }}</td>
                                <td class="px-6 py-3">
                                    <span class="px-2.5 py-1 rounded-full text-xs font-medium {{ $badge }}">{{ $label }}</span>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>
</div>
