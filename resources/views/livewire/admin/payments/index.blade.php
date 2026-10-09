<?php

use App\Models\Payment;
use App\Services\Payment\ManualPaymentService;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Url;
use Livewire\Volt\Component;

new #[Layout('layouts.admin')] class extends Component
{
    #[Url]
    public string $filterStatus = 'uploaded';

    public ?int  $detailId    = null;
    public bool  $showDetail  = false;
    public string $rejectNote = '';

    public function openDetail(int $id): void
    {
        $this->detailId   = $id;
        $this->showDetail = true;
        $this->rejectNote = '';
    }

    public function verify(int $id, ManualPaymentService $service): void
    {
        $payment = Payment::with('booking')->findOrFail($id);
        $service->verifyPayment($payment);
        $this->showDetail = false;
    }

    public function reject(int $id, ManualPaymentService $service): void
    {
        $payment = Payment::with('booking')->findOrFail($id);
        $service->rejectPayment($payment, $this->rejectNote);
        $this->showDetail = false;
    }

    public function with(): array
    {
        $query = Payment::with(['booking.guest', 'booking.villa'])->latest();

        if ($this->filterStatus !== 'all') {
            $query->where('payment_status', $this->filterStatus);
        }

        return [
            'payments'       => $query->get(),
            'countAll'       => Payment::count(),
            'countUploaded'  => Payment::where('payment_status', 'uploaded')->count(),
            'countVerified'  => Payment::where('payment_status', 'verified')->count(),
            'countRejected'  => Payment::where('payment_status', 'rejected')->count(),
            'detailPayment'  => $this->detailId
                ? Payment::with(['booking.guest', 'booking.villa'])->find($this->detailId)
                : null,
        ];
    }
}; ?>

<div>
    <div class="flex items-center justify-between mb-6">
        <h2 class="text-2xl font-semibold text-gray-800">Verifikasi Pembayaran</h2>
    </div>

    {{-- Filter Tab --}}
    <div class="flex gap-2 mb-6 overflow-x-auto pb-1">
        @foreach([
            ['uploaded', 'Menunggu Verifikasi', $countUploaded],
            ['verified', 'Terverifikasi',        $countVerified],
            ['rejected', 'Ditolak',              $countRejected],
            ['all',      'Semua',                $countAll],
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
        @if($payments->isEmpty())
            <div class="px-6 py-16 text-center text-gray-400">
                <svg class="w-12 h-12 mx-auto mb-3 opacity-30" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                        d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                </svg>
                <p class="font-medium">Tidak ada data pembayaran.</p>
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="bg-gray-50 text-gray-500 text-xs uppercase tracking-wide">
                        <tr>
                            <th class="px-5 py-3 text-left">Tamu</th>
                            <th class="px-5 py-3 text-left">Villa</th>
                            <th class="px-5 py-3 text-left">Bank</th>
                            <th class="px-5 py-3 text-left">Jumlah</th>
                            <th class="px-5 py-3 text-left">Struk</th>
                            <th class="px-5 py-3 text-left">Status</th>
                            <th class="px-5 py-3 text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-50">
                        @foreach($payments as $payment)
                            @php
                                $badge = [
                                    'pending'  => 'bg-gray-100 text-gray-500',
                                    'uploaded' => 'bg-yellow-100 text-yellow-700',
                                    'verified' => 'bg-green-100 text-green-700',
                                    'rejected' => 'bg-red-100 text-red-600',
                                ][$payment->payment_status] ?? 'bg-gray-100 text-gray-500';
                                $label = [
                                    'pending'  => 'Menunggu Upload',
                                    'uploaded' => 'Struk Dikirim',
                                    'verified' => 'Terverifikasi',
                                    'rejected' => 'Ditolak',
                                ][$payment->payment_status] ?? $payment->payment_status;
                            @endphp
                            <tr class="hover:bg-gray-50 transition">
                                <td class="px-5 py-3.5">
                                    <p class="font-medium text-gray-800">{{ $payment->booking->guest_name ?? '-' }}</p>
                                    <p class="text-xs text-gray-400">{{ $payment->booking->guest_phone ?? '-' }}</p>
                                </td>
                                <td class="px-5 py-3.5 text-gray-600">{{ $payment->booking->villa->name }}</td>
                                <td class="px-5 py-3.5 text-gray-600">{{ $payment->bank ?? '-' }}</td>
                                <td class="px-5 py-3.5 font-medium text-gray-800">
                                    Rp {{ number_format($payment->gross_amount, 0, ',', '.') }}
                                </td>
                                <td class="px-5 py-3.5">
                                    @if($payment->receipt_image)
                                        <a href="{{ Storage::url($payment->receipt_image) }}" target="_blank"
                                            class="text-[#1e3a8a] text-xs font-medium hover:underline flex items-center gap-1">
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                            </svg>
                                            Lihat
                                        </a>
                                    @else
                                        <span class="text-xs text-gray-400">Belum ada</span>
                                    @endif
                                </td>
                                <td class="px-5 py-3.5">
                                    <span class="px-2.5 py-1 rounded-full text-xs font-medium {{ $badge }}">
                                        {{ $label }}
                                    </span>
                                </td>
                                <td class="px-5 py-3.5 text-center">
                                    <button wire:click="openDetail({{ $payment->id }})"
                                        class="p-1.5 text-gray-400 hover:text-[#1e3a8a] hover:bg-blue-50 rounded-lg transition"
                                        title="Detail & Aksi">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                        </svg>
                                    </button>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>

    {{-- Modal Detail & Aksi --}}
    @if($showDetail && $detailPayment)
        <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4">
            <div class="bg-white rounded-2xl shadow-2xl w-full max-w-lg max-h-[90vh] overflow-y-auto">
                <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100 sticky top-0 bg-white">
                    <h3 class="font-semibold text-gray-800">Detail Pembayaran #{{ $detailPayment->id }}</h3>
                    <button wire:click="$set('showDetail', false)" class="text-gray-400 hover:text-gray-600">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>

                <div class="p-6 space-y-5 text-sm">
                    {{-- Info Tamu & Villa --}}
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <p class="text-gray-400 text-xs mb-1">Tamu</p>
                            <p class="font-medium text-gray-800">{{ $detailPayment->booking->guest_name ?? '-' }}</p>
                            <p class="text-gray-500 text-xs">{{ $detailPayment->booking->guest_phone ?? '-' }}</p>
                            <p class="text-gray-500 text-xs">{{ $detailPayment->booking->guest_email ?? '-' }}</p>
                            @if($detailPayment->booking->guest_city)
                                <p class="text-gray-500 text-xs">{{ $detailPayment->booking->guest_city }}</p>
                            @endif
                            @if($detailPayment->booking->adult_count !== null)
                                <p class="text-gray-500 text-xs mt-1">
                                    {{ $detailPayment->booking->adult_count }} dewasa
                                    @if($detailPayment->booking->child_count)
                                        · {{ $detailPayment->booking->child_count }} anak
                                    @endif
                                </p>
                            @endif
                        </div>
                        <div>
                            <p class="text-gray-400 text-xs mb-1">Villa</p>
                            <p class="font-medium text-gray-800">{{ $detailPayment->booking->villa->name }}</p>
                        </div>
                        <div>
                            <p class="text-gray-400 text-xs mb-1">Bank Tujuan</p>
                            <p class="font-medium text-gray-800">{{ $detailPayment->bank ?? '-' }}</p>
                        </div>
                        <div>
                            <p class="text-gray-400 text-xs mb-1">No. Rekening</p>
                            <p class="font-medium text-gray-800 font-mono">{{ $detailPayment->account_number ?? '-' }}</p>
                        </div>
                        <div class="col-span-2">
                            <p class="text-gray-400 text-xs mb-1">Jumlah Transfer</p>
                            <p class="font-bold text-[#1e3a8a] text-base">Rp {{ number_format($detailPayment->gross_amount, 0, ',', '.') }}</p>
                        </div>
                    </div>

                    {{-- Bukti Transfer --}}
                    <div>
                        <p class="text-gray-400 text-xs mb-2">Bukti Transfer</p>
                        @if($detailPayment->receipt_image)
                            <a href="{{ Storage::url($detailPayment->receipt_image) }}" target="_blank">
                                <img src="{{ Storage::url($detailPayment->receipt_image) }}"
                                    class="w-full rounded-xl border border-gray-200 object-cover max-h-64 hover:opacity-90 transition cursor-zoom-in">
                            </a>
                            <p class="text-xs text-gray-400 mt-1">Klik gambar untuk membuka di tab baru</p>
                        @else
                            <div class="bg-gray-50 rounded-xl p-6 text-center text-gray-400 text-xs">
                                Tamu belum mengupload bukti transfer
                            </div>
                        @endif
                    </div>

                    {{-- Status Saat Ini --}}
                    <div>
                        <p class="text-gray-400 text-xs mb-1">Status</p>
                        @php
                            $badge = [
                                'pending'  => 'bg-gray-100 text-gray-500',
                                'uploaded' => 'bg-yellow-100 text-yellow-700',
                                'verified' => 'bg-green-100 text-green-700',
                                'rejected' => 'bg-red-100 text-red-600',
                            ][$detailPayment->payment_status] ?? 'bg-gray-100 text-gray-500';
                            $statusLabel = [
                                'pending'  => 'Menunggu Upload dari Tamu',
                                'uploaded' => 'Struk Dikirim — Menunggu Verifikasi Admin',
                                'verified' => 'Pembayaran Terverifikasi',
                                'rejected' => 'Struk Ditolak',
                            ][$detailPayment->payment_status] ?? $detailPayment->payment_status;
                        @endphp
                        <span class="px-3 py-1.5 rounded-full text-xs font-medium {{ $badge }}">{{ $statusLabel }}</span>
                    </div>

                    {{-- Tombol Aksi (hanya saat uploaded) --}}
                    @if($detailPayment->payment_status === 'uploaded')
                        <div class="border-t border-gray-100 pt-4 space-y-3">
                            <p class="text-xs font-medium text-gray-700">Tindakan Admin</p>
                            <button wire:click="verify({{ $detailPayment->id }})"
                                wire:confirm="Verifikasi pembayaran ini? Status booking akan berubah menjadi Lunas."
                                class="w-full py-2.5 bg-green-600 text-white rounded-xl font-medium hover:bg-green-700 transition text-sm">
                                Verifikasi — Pembayaran Sah
                            </button>
                            <div class="space-y-2">
                                <textarea wire:model="rejectNote" rows="2"
                                    placeholder="Alasan penolakan (opsional)..."
                                    class="w-full text-sm rounded-xl border border-gray-200 px-3 py-2 focus:outline-none focus:ring-2 focus:ring-red-300 resize-none"></textarea>
                                <button wire:click="reject({{ $detailPayment->id }})"
                                    wire:confirm="Tolak struk ini? Tamu akan diminta upload ulang."
                                    class="w-full py-2.5 bg-red-600 text-white rounded-xl font-medium hover:bg-red-700 transition text-sm">
                                    Tolak — Struk Tidak Valid
                                </button>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    @endif
</div>
