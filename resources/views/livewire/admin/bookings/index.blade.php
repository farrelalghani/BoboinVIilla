<?php

use App\Models\Booking;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Url;
use Livewire\Volt\Component;

new #[Layout('layouts.admin')] class extends Component
{
    #[Url]
    public string $filterStatus = 'all';

    public bool   $showDetail    = false;
    public bool   $showReject    = false;
    public bool   $showCancel    = false;
    public ?int   $detailId      = null;
    public ?int   $rejectingId   = null;
    public ?int   $cancellingId  = null;
    public string $rejectionNote = '';

    public function approve(int $id): void
    {
        Booking::findOrFail($id)->update(['status' => 'approved']);
    }

    public function openReject(int $id): void
    {
        $this->rejectingId   = $id;
        $this->rejectionNote = '';
        $this->showReject    = true;
        $this->showDetail    = false;
    }

    public function confirmReject(): void
    {
        Booking::findOrFail($this->rejectingId)->update([
            'status'         => 'rejected',
            'rejection_note' => trim($this->rejectionNote) ?: null,
        ]);

        $this->showReject  = false;
        $this->rejectingId = null;
    }

    public function markCompleted(int $id): void
    {
        Booking::where('id', $id)->where('status', 'paid')->update(['status' => 'completed']);
        $this->showDetail = false;
    }

    public function openCancel(int $id): void
    {
        $this->cancellingId = $id;
        $this->showCancel   = true;
        $this->showDetail   = false;
    }

    public function confirmCancel(): void
    {
        Booking::whereIn('status', ['waiting_admin', 'approved', 'pending_payment'])
            ->where('id', $this->cancellingId)
            ->update(['status' => 'cancelled']);

        $this->showCancel   = false;
        $this->cancellingId = null;
    }

    public function openDetail(int $id): void
    {
        $this->detailId   = $id;
        $this->showDetail = true;
    }

    public function with(): array
    {
        $query = Booking::with(['villa'])->latest();

        if ($this->filterStatus !== 'all') {
            $query->where('status', $this->filterStatus);
        }

        return [
            'bookings'      => $query->get(),
            'countAll'      => Booking::count(),
            'countWaiting'  => Booking::where('status', 'waiting_admin')->count(),
            'countApproved' => Booking::where('status', 'approved')->count(),
            'countPaid'     => Booking::where('status', 'paid')->count(),
            'detailBooking' => $this->detailId ? Booking::with(['villa', 'payment'])->find($this->detailId) : null,
        ];
    }
}; ?>

<div>
    <div class="flex items-center justify-between mb-6">
        <h2 class="text-2xl font-semibold text-gray-800">Kelola Pesanan</h2>
    </div>

    {{-- Filter Tab --}}
    <div class="flex gap-2 mb-6 overflow-x-auto pb-1">
        @foreach([
            ['all',           'Semua',    $countAll],
            ['waiting_admin', 'Menunggu', $countWaiting],
            ['approved',      'Disetujui',$countApproved],
            ['paid',          'Lunas',    $countPaid],
        ] as [$val, $label, $count])
            <button wire:click="$set('filterStatus', '{{ $val }}')"
                class="shrink-0 px-4 py-2 rounded-xl text-sm font-medium transition flex items-center gap-2
                    {{ $filterStatus === $val
                        ? 'bg-[#1e3a8a] text-white'
                        : 'bg-white border border-gray-200 text-gray-600 hover:border-[#1e3a8a]/50' }}">
                {{ $label }}
                <span class="text-xs {{ $filterStatus === $val ? 'bg-white/20' : 'bg-gray-100' }} px-1.5 py-0.5 rounded-full">
                    {{ $count }}
                </span>
            </button>
        @endforeach
    </div>

    {{-- Tabel --}}
    <div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden">
        @if($bookings->isEmpty())
            <div class="px-6 py-16 text-center text-gray-400">
                <svg class="w-12 h-12 mx-auto mb-3 opacity-30" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                        d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                </svg>
                <p class="font-medium">Tidak ada pesanan.</p>
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="bg-gray-50 text-gray-500 text-xs uppercase tracking-wide">
                        <tr>
                            <th class="px-5 py-3 text-left">Tamu</th>
                            <th class="px-5 py-3 text-left">Villa</th>
                            <th class="px-5 py-3 text-left">Check-in</th>
                            <th class="px-5 py-3 text-left">Check-out</th>
                            <th class="px-5 py-3 text-left">Total</th>
                            <th class="px-5 py-3 text-left">Status</th>
                            <th class="px-5 py-3 text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-50">
                        @foreach($bookings as $booking)
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
                                <td class="px-5 py-3.5">
                                    <p class="font-medium text-gray-800">{{ $booking->guest_name }}</p>
                                    <p class="text-xs text-gray-400">{{ $booking->guest_phone }}</p>
                                    <p class="text-xs text-gray-400 font-mono">{{ $booking->booking_code }}</p>
                                </td>
                                <td class="px-5 py-3.5 text-gray-600">{{ $booking->villa->name }}</td>
                                <td class="px-5 py-3.5 text-gray-600">{{ $booking->check_in->format('d M Y') }}</td>
                                <td class="px-5 py-3.5 text-gray-600">{{ $booking->check_out->format('d M Y') }}</td>
                                <td class="px-5 py-3.5 font-medium text-gray-800">
                                    Rp {{ number_format($booking->total_price, 0, ',', '.') }}
                                </td>
                                <td class="px-5 py-3.5">
                                    <span class="px-2.5 py-1 rounded-full text-xs font-medium {{ $badge }}">
                                        {{ $label }}
                                    </span>
                                </td>
                                <td class="px-5 py-3.5">
                                    <div class="flex items-center justify-center gap-1.5">
                                        <button wire:click="openDetail({{ $booking->id }})"
                                            title="Lihat Detail"
                                            class="p-1.5 text-gray-400 hover:text-[#1e3a8a] hover:bg-blue-50 rounded-lg transition">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                            </svg>
                                        </button>

                                        @if($booking->status === 'waiting_admin')
                                            <button wire:click="approve({{ $booking->id }})"
                                                title="Setujui"
                                                class="p-1.5 text-gray-400 hover:text-green-600 hover:bg-green-50 rounded-lg transition">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                                </svg>
                                            </button>
                                            <button wire:click="openReject({{ $booking->id }})"
                                                title="Tolak"
                                                class="p-1.5 text-gray-400 hover:text-red-600 hover:bg-red-50 rounded-lg transition">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                                </svg>
                                            </button>
                                        @endif
                                        @if($booking->status === 'paid')
                                            <button wire:click="markCompleted({{ $booking->id }})"
                                                title="Tandai Selesai"
                                                class="p-1.5 text-gray-400 hover:text-indigo-600 hover:bg-indigo-50 rounded-lg transition">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                                </svg>
                                            </button>
                                        @endif
                                        @if(in_array($booking->status, ['waiting_admin', 'approved', 'pending_payment']))
                                            <button wire:click="openCancel({{ $booking->id }})"
                                                title="Batalkan"
                                                class="p-1.5 text-gray-400 hover:text-red-500 hover:bg-red-50 rounded-lg transition">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                                </svg>
                                            </button>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>

    {{-- Modal Detail --}}
    @if($showDetail && $detailBooking)
        <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4">
            <div class="bg-white rounded-2xl shadow-2xl w-full max-w-lg">
                <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100">
                    <h3 class="font-semibold text-gray-800">Detail Pesanan — {{ $detailBooking->booking_code }}</h3>
                    <button wire:click="$set('showDetail', false)" class="text-gray-400 hover:text-gray-600">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>
                <div class="p-6 space-y-4 text-sm">
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <p class="text-gray-400 text-xs mb-1">Tamu</p>
                            @if($detailBooking->guest_name)
                                <p class="font-medium text-gray-800">{{ $detailBooking->guest_name }}</p>
                                <p class="text-gray-500 text-xs">{{ $detailBooking->guest_phone }}</p>
                                <p class="text-gray-500 text-xs">{{ $detailBooking->guest_email }}</p>
                                @if($detailBooking->guest_city)
                                    <p class="text-gray-500 text-xs">{{ $detailBooking->guest_city }}</p>
                                @endif
                            @else
                                <p class="text-gray-400 text-xs italic">Belum diisi</p>
                            @endif
                        </div>
                        <div>
                            <p class="text-gray-400 text-xs mb-1">Villa</p>
                            <p class="font-medium text-gray-800">{{ $detailBooking->villa->name }}</p>
                            <p class="text-gray-500 text-xs">{{ $detailBooking->villa->city }}</p>
                        </div>
                        <div>
                            <p class="text-gray-400 text-xs mb-1">Check-in</p>
                            <p class="font-medium text-gray-800">{{ $detailBooking->check_in->format('d M Y') }}</p>
                        </div>
                        <div>
                            <p class="text-gray-400 text-xs mb-1">Check-out</p>
                            <p class="font-medium text-gray-800">{{ $detailBooking->check_out->format('d M Y') }}</p>
                        </div>
                        <div>
                            <p class="text-gray-400 text-xs mb-1">Durasi</p>
                            <p class="font-medium text-gray-800">
                                {{ $detailBooking->check_in->diffInDays($detailBooking->check_out) }} malam
                            </p>
                        </div>
                        <div>
                            <p class="text-gray-400 text-xs mb-1">Total Harga</p>
                            <p class="font-semibold text-[#1e3a8a]">Rp {{ number_format($detailBooking->total_price, 0, ',', '.') }}</p>
                        </div>
                        @if($detailBooking->adult_count !== null)
                            <div class="col-span-2">
                                <p class="text-gray-400 text-xs mb-1">Jumlah Tamu</p>
                                <p class="font-medium text-gray-800">
                                    {{ $detailBooking->adult_count }} dewasa
                                    @if($detailBooking->child_count)
                                        · {{ $detailBooking->child_count }} anak
                                    @endif
                                </p>
                            </div>
                        @endif
                    </div>

                    {{-- Alasan penolakan jika sudah ditolak --}}
                    @if($detailBooking->status === 'rejected' && $detailBooking->rejection_note)
                        <div class="bg-red-50 border border-red-100 rounded-xl p-3">
                            <p class="text-xs font-medium text-red-600 mb-1">Alasan Penolakan</p>
                            <p class="text-sm text-red-700">{{ $detailBooking->rejection_note }}</p>
                        </div>
                    @endif

                    <div class="pt-3 border-t border-gray-100">
                        <p class="text-gray-400 text-xs mb-1">Status</p>
                        @php
                            $badge = [
                                'waiting_admin'   => 'bg-yellow-100 text-yellow-700',
                                'approved'        => 'bg-blue-100 text-blue-700',
                                'rejected'        => 'bg-red-100 text-red-600',
                                'pending_payment' => 'bg-orange-100 text-orange-700',
                                'paid'            => 'bg-green-100 text-green-700',
                                'completed'       => 'bg-gray-100 text-gray-600',
                                'cancelled'       => 'bg-red-50 text-red-400',
                            ][$detailBooking->status] ?? 'bg-gray-100 text-gray-500';
                            $statusLabel = [
                                'waiting_admin'   => 'Menunggu Konfirmasi Admin',
                                'approved'        => 'Disetujui — Menunggu Pembayaran',
                                'rejected'        => 'Ditolak',
                                'pending_payment' => 'Menunggu Pembayaran',
                                'paid'            => 'Pembayaran Lunas',
                                'completed'       => 'Selesai',
                                'cancelled'       => 'Dibatalkan',
                            ][$detailBooking->status] ?? $detailBooking->status;
                        @endphp
                        <span class="px-3 py-1.5 rounded-full text-xs font-medium {{ $badge }}">{{ $statusLabel }}</span>
                    </div>

                    @if($detailBooking->status === 'waiting_admin')
                        <div class="flex gap-3 pt-2">
                            <button wire:click="approve({{ $detailBooking->id }}); $set('showDetail', false)"
                                class="flex-1 py-2.5 bg-green-600 text-white rounded-xl font-medium hover:bg-green-700 transition text-sm">
                                Setujui Pesanan
                            </button>
                            <button wire:click="openReject({{ $detailBooking->id }})"
                                class="flex-1 py-2.5 bg-red-600 text-white rounded-xl font-medium hover:bg-red-700 transition text-sm">
                                Tolak Pesanan
                            </button>
                        </div>
                    @endif
                    @if($detailBooking->status === 'paid')
                        <button wire:click="markCompleted({{ $detailBooking->id }})"
                            class="w-full py-2.5 bg-indigo-600 text-white rounded-xl font-medium hover:bg-indigo-700 transition text-sm mt-2">
                            Tandai Selesai (Tamu Sudah Check-out)
                        </button>
                    @endif
                    @if(in_array($detailBooking->status, ['waiting_admin', 'approved', 'pending_payment']))
                        <button wire:click="openCancel({{ $detailBooking->id }})"
                            class="w-full py-2.5 border border-red-200 text-red-500 rounded-xl font-medium hover:bg-red-50 transition text-sm mt-1">
                            Batalkan Pesanan
                        </button>
                    @endif
                </div>
            </div>
        </div>
    @endif

    {{-- Modal Konfirmasi Batalkan --}}
    @if($showCancel)
        <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4">
            <div class="bg-white rounded-2xl shadow-2xl w-full max-w-sm">
                <div class="p-6 text-center">
                    <div class="w-12 h-12 bg-red-100 rounded-full flex items-center justify-center mx-auto mb-4">
                        <svg class="w-6 h-6 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                        </svg>
                    </div>
                    <h3 class="font-semibold text-gray-800 mb-1">Batalkan Pesanan?</h3>
                    <p class="text-sm text-gray-500 mb-6">Pesanan akan dibatalkan dan tidak dapat dikembalikan.</p>
                    <div class="flex gap-3">
                        <button wire:click="$set('showCancel', false)"
                            class="flex-1 py-2.5 border border-gray-200 text-gray-600 rounded-xl font-medium hover:bg-gray-50 transition text-sm">
                            Kembali
                        </button>
                        <button wire:click="confirmCancel"
                            class="flex-1 py-2.5 bg-red-600 text-white rounded-xl font-medium hover:bg-red-700 transition text-sm">
                            Ya, Batalkan
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif

    {{-- Modal Tolak + Alasan --}}
    @if($showReject)
        <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4">
            <div class="bg-white rounded-2xl shadow-2xl w-full max-w-md">
                <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100">
                    <h3 class="font-semibold text-gray-800">Tolak Pesanan</h3>
                    <button wire:click="$set('showReject', false)" class="text-gray-400 hover:text-gray-600">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>
                <div class="p-6 space-y-4">
                    <div class="flex items-start gap-3 bg-red-50 border border-red-100 rounded-xl p-3 text-sm text-red-700">
                        <svg class="w-5 h-5 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                        </svg>
                        <p>Pesanan akan ditolak dan tamu akan mendapat notifikasi. Pastikan Anda mengisi alasan yang jelas.</p>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1.5">
                            Alasan Penolakan
                            <span class="text-gray-400 font-normal">(akan ditampilkan ke tamu)</span>
                        </label>
                        <textarea wire:model="rejectionNote" rows="3"
                            placeholder="Contoh: Villa sudah terisi pada tanggal tersebut. Silakan pilih tanggal lain atau villa yang berbeda."
                            class="w-full text-sm rounded-xl border border-gray-200 px-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-red-300 resize-none">
                        </textarea>
                    </div>

                    <div class="flex gap-3 pt-1">
                        <button wire:click="$set('showReject', false)"
                            class="flex-1 py-2.5 border border-gray-200 text-gray-600 rounded-xl font-medium hover:bg-gray-50 transition text-sm">
                            Batal
                        </button>
                        <button wire:click="confirmReject"
                            class="flex-1 py-2.5 bg-red-600 text-white rounded-xl font-medium hover:bg-red-700 transition text-sm">
                            Ya, Tolak Pesanan
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>
