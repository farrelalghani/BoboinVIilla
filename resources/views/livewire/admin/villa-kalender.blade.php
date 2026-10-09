<?php

use App\Models\Booking;
use App\Models\Villa;
use App\Models\VillaBlockedDate;
use Carbon\Carbon;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;

new #[Layout('layouts.admin')] class extends Component
{
    public int    $villaId = 0;
    public int    $year;
    public int    $month;
    public string $blockNote = '';

    public function mount(): void
    {
        $this->year  = now()->year;
        $this->month = now()->month;
        $first = Villa::where('status', 'active')->first();
        if ($first) $this->villaId = $first->id;
    }

    public function prevMonth(): void
    {
        $d = Carbon::create($this->year, $this->month, 1)->subMonth();
        $this->year  = $d->year;
        $this->month = $d->month;
    }

    public function nextMonth(): void
    {
        $d = Carbon::create($this->year, $this->month, 1)->addMonth();
        $this->year  = $d->year;
        $this->month = $d->month;
    }

    public function toggleBlock(int $day): void
    {
        $villaId = (int) $this->villaId;
        $date    = sprintf('%04d-%02d-%02d', $this->year, $this->month, $day);

        if (!$villaId || $day < 1 || $day > 31) return;

        $existing = VillaBlockedDate::where('villa_id', $villaId)
            ->where('date', $date)
            ->first();

        if ($existing) {
            $existing->delete();
        } else {
            VillaBlockedDate::create([
                'villa_id' => $villaId,
                'date'     => $date,
                'note'     => null,
            ]);
        }
    }

    public function with(): array
    {
        $villas = Villa::orderBy('name')->get();

        if (!$this->villaId) {
            return ['villas' => $villas, 'calendarData' => [], 'villa' => null,
                    'firstDay' => 1, 'daysInMonth' => 0, 'monthLabel' => ''];
        }

        $villa     = Villa::find($this->villaId);
        $startDate = Carbon::create($this->year, $this->month, 1);
        $endDate   = $startDate->copy()->endOfMonth();

        // Booking yang overlap dengan bulan ini
        $bookings = Booking::where('villa_id', $this->villaId)
            ->whereIn('status', ['approved', 'pending_payment', 'paid', 'completed'])
            ->where('check_in', '<', $endDate)
            ->where('check_out', '>', $startDate)
            ->get(['check_in', 'check_out', 'status', 'booking_code', 'guest_name']);

        // Tanggal diblokir manual
        $blocked = VillaBlockedDate::where('villa_id', $this->villaId)
            ->whereBetween('date', [$startDate, $endDate])
            ->pluck('note', 'date')
            ->mapWithKeys(fn($note, $date) => [Carbon::parse($date)->format('Y-m-d') => $note]);

        // Build calendar data — satu entry per hari di bulan ini
        $calendarData = [];
        for ($day = 1; $day <= $endDate->day; $day++) {
            $date    = Carbon::create($this->year, $this->month, $day);
            $dateStr = $date->format('Y-m-d');
            $status  = 'available';
            $info    = null;

            foreach ($bookings as $b) {
                if ($date >= Carbon::parse($b->check_in) && $date < Carbon::parse($b->check_out)) {
                    $status = $b->status;
                    $info   = ($b->guest_name ?? 'Tamu') . ' · ' . $b->booking_code;
                    break;
                }
            }

            if ($blocked->has($dateStr)) {
                $status = 'blocked';
                $info   = $blocked[$dateStr] ?: 'Diblokir';
            }

            $calendarData[$day] = [
                'date'    => $dateStr,
                'status'  => $status,
                'info'    => $info,
                'isPast'  => $date->isPast() && !$date->isToday(),
                'isToday' => $date->isToday(),
            ];
        }

        return [
            'villas'       => $villas,
            'villa'        => $villa,
            'calendarData' => $calendarData,
            'firstDay'     => Carbon::create($this->year, $this->month, 1)->dayOfWeek, // 0=Sun
            'daysInMonth'  => $endDate->day,
            'monthLabel'   => $startDate->isoFormat('MMMM YYYY'),
        ];
    }
}; ?>

<div>
    <div class="flex items-center justify-between mb-6">
        <h2 class="text-2xl font-semibold text-gray-800">Kalender Villa</h2>
    </div>

    {{-- Pilih Villa --}}
    <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-4 mb-6 flex flex-wrap items-center gap-4">
        <label class="text-sm font-medium text-gray-700">Pilih Villa:</label>
        <select wire:model.live="villaId"
            class="border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-[#1e3a8a]/30 min-w-48">
            @foreach($villas as $v)
                <option value="{{ $v->id }}">{{ $v->name }}</option>
            @endforeach
        </select>

        {{-- Legenda --}}
        <div class="flex flex-wrap items-center gap-4 ml-auto text-xs text-gray-600">
            <span class="flex items-center gap-1.5"><span class="w-3 h-3 rounded bg-green-200 inline-block"></span>Tersedia</span>
            <span class="flex items-center gap-1.5"><span class="w-3 h-3 rounded bg-blue-200 inline-block"></span>Dipesan</span>
            <span class="flex items-center gap-1.5"><span class="w-3 h-3 rounded bg-emerald-500 inline-block"></span>Lunas</span>
            <span class="flex items-center gap-1.5"><span class="w-3 h-3 rounded bg-gray-300 inline-block"></span>Diblokir</span>
            <span class="flex items-center gap-1.5"><span class="w-3 h-3 rounded bg-orange-200 inline-block"></span>Menunggu Bayar</span>
        </div>
    </div>

    @if($villa)
    <div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden">
        {{-- Header bulan --}}
        <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100">
            <button wire:click="prevMonth"
                class="p-2 rounded-lg hover:bg-gray-100 transition text-gray-500">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                </svg>
            </button>
            <div class="text-center">
                <p class="font-semibold text-gray-800 text-lg">{{ $monthLabel }}</p>
                <p class="text-xs text-gray-400">{{ $villa->name }}</p>
            </div>
            <button wire:click="nextMonth"
                class="p-2 rounded-lg hover:bg-gray-100 transition text-gray-500">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                </svg>
            </button>
        </div>

        <div class="p-4 sm:p-6">
            {{-- Nama hari --}}
            <div style="display:grid;grid-template-columns:repeat(7,1fr);margin-bottom:8px;">
                @foreach(['Min','Sen','Sel','Rab','Kam','Jum','Sab'] as $hari)
                    <div style="text-align:center;font-size:11px;font-weight:600;color:#9ca3af;padding:8px 0;">{{ $hari }}</div>
                @endforeach
            </div>

            {{-- Grid kalender --}}
            <div style="display:grid;grid-template-columns:repeat(7,1fr);gap:4px;">
                {{-- Offset hari pertama --}}
                @for($i = 0; $i < $firstDay; $i++)
                    <div></div>
                @endfor

                {{-- Hari-hari --}}
                @foreach($calendarData as $day => $data)
                    @php
                        $bg = match($data['status']) {
                            'paid', 'completed'  => 'bg-emerald-500 text-white',
                            'approved'           => 'bg-blue-200 text-blue-800',
                            'pending_payment'    => 'bg-orange-200 text-orange-800',
                            'blocked'            => 'bg-gray-300 text-gray-600',
                            default              => $data['isPast'] ? 'bg-gray-50 text-gray-300' : 'bg-green-50 text-gray-700 hover:bg-green-100 cursor-pointer',
                        };
                        $isBookable = $data['status'] === 'available' && !$data['isPast'];
                        $isBlocked  = $data['status'] === 'blocked';
                        $canToggle  = ($isBookable || $isBlocked);
                        $ring = $data['isToday'] ? 'ring-2 ring-[#1e3a8a] ring-offset-1' : '';
                    @endphp

                    <div class="relative group">
                        @if($canToggle)
                            <button type="button"
                                wire:click="toggleBlock({{ $day }})"
                                class="w-full rounded-lg flex flex-col items-center justify-center text-sm font-medium transition {{ $bg }} {{ $ring }}"
                                style="aspect-ratio:1/1;cursor:pointer;">
                                {{ $day }}
                                @if($isBlocked)
                                    <span style="width:6px;height:6px;border-radius:50%;background:currentColor;opacity:.6;margin-top:2px;display:block;"></span>
                                @endif
                            </button>
                        @else
                            <div class="w-full rounded-lg flex flex-col items-center justify-center text-sm font-medium {{ $bg }} {{ $ring }}"
                                style="aspect-ratio:1/1;cursor:default;">
                                {{ $day }}
                                @if(!in_array($data['status'], ['available', 'blocked']))
                                    <span style="width:6px;height:6px;border-radius:50%;background:currentColor;opacity:.6;margin-top:2px;display:block;"></span>
                                @endif
                            </div>
                        @endif

                        {{-- Tooltip --}}
                        @if($data['info'])
                            <div class="absolute bottom-full left-1/2 -translate-x-1/2 mb-2 z-20 hidden group-hover:block w-max max-w-[180px]">
                                <div class="bg-gray-800 text-white text-xs rounded-lg px-3 py-2 shadow-lg">
                                    {{ $data['info'] }}
                                </div>
                            </div>
                        @elseif($isBookable)
                            <div class="absolute bottom-full left-1/2 -translate-x-1/2 mb-2 z-20 hidden group-hover:block">
                                <div class="bg-gray-700 text-white text-xs rounded-lg px-2 py-1 shadow-lg whitespace-nowrap">
                                    Klik untuk blokir
                                </div>
                            </div>
                        @endif
                    </div>
                @endforeach
            </div>
        </div>

        {{-- Info cara pakai --}}
        <div class="px-6 py-3 bg-gray-50 border-t border-gray-100 text-xs text-gray-400">
            Klik tanggal <span class="text-green-600 font-medium">hijau (tersedia)</span> untuk memblokirnya.
            Klik tanggal <span class="text-gray-600 font-medium">abu-abu (diblokir)</span> untuk membukanya kembali.
            Tanggal yang sudah dipesan tamu tidak bisa diubah dari sini.
        </div>
    </div>
    @endif
</div>
