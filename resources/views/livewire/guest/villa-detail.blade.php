<?php

use App\Models\Booking;
use App\Models\Villa;
use App\Models\VillaBlockedDate;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Storage;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;

new #[Layout('layouts.app')] class extends Component
{
    public Villa $villa;

    public string $check_in  = '';
    public string $check_out = '';
    public int    $guests    = 1;

    public bool        $bookingSuccess = false;
    public string|null $bookingCode    = null;

    public function mount(string $slug): void
    {
        $this->villa = Villa::where('slug', $slug)->where('status', 'active')->firstOrFail();
    }

    public function getNightsProperty(): int
    {
        if (!$this->check_in || !$this->check_out) return 0;
        return max(0, Carbon::parse($this->check_in)->diffInDays(Carbon::parse($this->check_out)));
    }

    /**
     * Rincian harga per malam, dikelompokkan per label (Weekday/Weekend/nama peak season).
     */
    public function getPriceBreakdownProperty(): array
    {
        if ($this->nights === 0) return [];

        $groups = [];
        $cursor = Carbon::parse($this->check_in);
        $end    = Carbon::parse($this->check_out);

        while ($cursor->lt($end)) {
            $info = $this->villa->priceInfoForDate($cursor->copy());
            $key  = $info['label'] . '|' . $info['price'];

            if (!isset($groups[$key])) {
                $groups[$key] = ['label' => $info['label'], 'price' => $info['price'], 'nights' => 0];
            }
            $groups[$key]['nights']++;

            $cursor->addDay();
        }

        return array_values($groups);
    }

    public function getTotalPriceProperty(): float
    {
        return collect($this->priceBreakdown)->sum(fn ($g) => $g['price'] * $g['nights']);
    }

    public function ajukanPemesanan(): void
    {
        $this->validate([
            'check_in'  => ['required', 'date', 'after_or_equal:today'],
            'check_out' => ['required', 'date', 'after:check_in'],
            'guests'    => ['required', 'integer', 'min:1', 'max:' . $this->villa->capacity],
        ], [
            'check_in.required'       => 'Tanggal check-in wajib diisi.',
            'check_in.after_or_equal' => 'Tanggal check-in tidak boleh sebelum hari ini.',
            'check_out.required'      => 'Tanggal check-out wajib diisi.',
            'check_out.after'         => 'Tanggal check-out harus setelah check-in.',
            'guests.max'              => 'Jumlah tamu melebihi kapasitas villa (' . $this->villa->capacity . ' orang).',
        ]);

        $isBlocked = VillaBlockedDate::where('villa_id', $this->villa->id)
            ->where('date', '>=', $this->check_in)
            ->where('date', '<', $this->check_out)
            ->exists();

        if ($isBlocked) {
            $this->addError('check_in', 'Villa tidak tersedia pada tanggal yang dipilih karena sedang ditutup/dalam pemeliharaan.');
            return;
        }

        $conflict = Booking::where('villa_id', $this->villa->id)
            ->whereIn('status', ['approved', 'pending_payment', 'paid', 'completed'])
            ->where('check_in', '<', $this->check_out)
            ->where('check_out', '>', $this->check_in)
            ->exists();

        if ($conflict) {
            $this->addError('check_in', 'Tanggal tersebut sudah dipesan villa ini. Silakan pilih tanggal lain.');
            return;
        }

        $booking = Booking::create([
            'villa_id'    => $this->villa->id,
            'check_in'    => $this->check_in,
            'check_out'   => $this->check_out,
            'total_price' => $this->totalPrice,
            'status'      => 'waiting_admin',
        ]);

        $this->bookingCode    = $booking->booking_code;
        $this->bookingSuccess = true;
        $this->reset(['check_in', 'check_out', 'guests']);
    }
}; ?>

<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 sm:py-10 pb-24 sm:pb-16">
    {{-- Breadcrumb --}}
    <nav class="flex items-center gap-2 text-sm text-gray-400 mb-6">
        <a href="{{ route('home') }}" wire:navigate class="hover:text-[#3a6484] transition">Beranda</a>
        <span>/</span>
        <a href="{{ route('villas.index') }}" wire:navigate class="hover:text-[#3a6484] transition">Villa</a>
        <span>/</span>
        <span class="text-gray-600 truncate">{{ $villa->name }}</span>
    </nav>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        {{-- Info Villa --}}
        <div class="lg:col-span-2 space-y-6">
            @php
                $photos = $villa->all_images;
                $photoUrls = array_values(array_map(fn($p) => Storage::url($p), $photos));
            @endphp
            {{-- Foto Villa / Galeri --}}
            <div x-data="{
                    active: 0,
                    photos: {{ json_encode($photoUrls) }},
                    modalOpen: false,
                    touchStartX: 0,
                    touchEndX: 0,
                    next() { if (this.photos.length > 0) this.active = (this.active + 1) % this.photos.length; },
                    prev() { if (this.photos.length > 0) this.active = (this.active - 1 + this.photos.length) % this.photos.length; },
                    handleTouchStart(e) {
                        this.touchStartX = e.changedTouches[0].screenX;
                    },
                    handleTouchEnd(e) {
                        this.touchEndX = e.changedTouches[0].screenX;
                        let diff = this.touchStartX - this.touchEndX;
                        if (Math.abs(diff) > 40) {
                            if (diff > 0) { this.next(); }
                            else { this.prev(); }
                        }
                    }
                }"
                class="space-y-3">
                <div class="w-full h-64 sm:h-80 md:h-96 lg:h-[420px] rounded-2xl overflow-hidden bg-slate-900 relative group select-none shadow-sm"
                     @touchstart="handleTouchStart($event)"
                     @touchend="handleTouchEnd($event)">
                    <template x-if="photos.length > 0">
                        <div class="w-full h-full relative cursor-pointer" @click="modalOpen = true">
                            <img :src="photos[active]"
                                :alt="'{{ addslashes($villa->name) }} - Foto ' + (active + 1)"
                                class="w-full h-full object-cover transition-all duration-300">

                            {{-- Counter Badge (Bottom Right) --}}
                            <div class="absolute bottom-3 right-3 sm:bottom-4 sm:right-4 bg-black/60 backdrop-blur-sm text-white text-xs px-3 py-1.5 rounded-full font-medium flex items-center gap-1.5 shadow">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                </svg>
                                <span x-text="(active + 1) + ' / ' + photos.length"></span>
                            </div>

                            {{-- Tombol Klik Perbesar (Top Right) --}}
                            <div class="absolute top-3 right-3 sm:top-4 sm:right-4 bg-black/60 hover:bg-black/80 backdrop-blur-sm text-white px-3 py-1.5 rounded-full text-xs font-medium transition flex items-center gap-1.5 shadow">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 8V4m0 0h4M4 4l5 5m11-1V4m0 0h-4m4 0l-5 5M4 16v4m0 0h4m-4 0l5-5m11 5l-5-5m5 5v-4m0 4h-4"/>
                                </svg>
                                <span>Perbesar</span>
                            </div>
                        </div>
                    </template>

                    <template x-if="photos.length === 0">
                        <div class="w-full h-full relative">
                            <div class="absolute inset-0 opacity-10 flex items-center justify-center">
                                <svg class="w-40 h-40 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="0.8" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                                </svg>
                            </div>
                            <div class="absolute inset-0 flex items-end p-5">
                                <p class="text-white/50 text-sm">Foto villa belum tersedia</p>
                            </div>
                        </div>
                    </template>

                    {{-- Panah Navigasi Kiri & Kanan di Banner --}}
                    <template x-if="photos.length > 1">
                        <div>
                            <button type="button" @click.stop="prev()"
                                class="absolute left-3 top-1/2 -translate-y-1/2 w-9 h-9 sm:w-10 sm:h-10 rounded-full bg-black/50 hover:bg-black/80 text-white flex items-center justify-center transition shadow-lg z-10"
                                title="Foto Sebelumnya">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15 19l-7-7 7-7"/>
                                </svg>
                            </button>
                            <button type="button" @click.stop="next()"
                                class="absolute right-3 top-1/2 -translate-y-1/2 w-9 h-9 sm:w-10 sm:h-10 rounded-full bg-black/50 hover:bg-black/80 text-white flex items-center justify-center transition shadow-lg z-10"
                                title="Foto Berikutnya">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7"/>
                                </svg>
                            </button>
                        </div>
                    </template>
                </div>

                {{-- Thumbnail Strip di Halaman Detail --}}
                <template x-if="photos.length > 1">
                    <div class="flex items-center gap-2 overflow-x-auto pb-1.5 scrollbar-thin">
                        <template x-for="(p, idx) in photos" :key="idx">
                            <button type="button" @click="active = idx"
                                :class="active === idx ? 'ring-2 ring-[#3a6484] ring-offset-2 opacity-100 scale-[1.02]' : 'opacity-60 hover:opacity-100'"
                                class="relative shrink-0 w-20 h-14 rounded-xl overflow-hidden border border-gray-200 transition">
                                <img :src="p" class="w-full h-full object-cover">
                            </button>
                        </template>
                    </div>
                </template>

                {{-- Modal Lightbox (Teleported to body for 100% full screen & perfect responsiveness) --}}
                <template x-teleport="body">
                    <div x-show="modalOpen"
                        x-transition:enter="transition ease-out duration-200"
                        x-transition:enter-start="opacity-0"
                        x-transition:enter-end="opacity-100"
                        x-transition:leave="transition ease-in duration-150"
                        x-transition:leave-start="opacity-100"
                        x-transition:leave-end="opacity-0"
                        class="fixed inset-0 z-[99999] bg-black/95 backdrop-blur-md select-none"
                        @keydown.escape.window="modalOpen = false"
                        @keydown.right.window="if (modalOpen) next()"
                        @keydown.left.window="if (modalOpen) prev()"
                        style="display: none;">

                        {{-- Tombol Tutup Paling Jelas di Pojok Kanan Atas --}}
                        <button type="button" @click="modalOpen = false"
                            class="fixed top-4 right-4 sm:top-6 sm:right-6 z-[100002] inline-flex items-center gap-1.5 px-4 py-2 rounded-full bg-white/20 hover:bg-white/30 active:bg-white/40 text-white text-xs sm:text-sm font-semibold backdrop-blur-md border border-white/30 shadow-2xl transition cursor-pointer"
                            title="{{ __('Tutup Galeri (Esc)') }}">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"/>
                            </svg>
                            <span>{{ __('Tutup') }}</span>
                        </button>

                        {{-- Counter Badge di Pojok Kiri Atas --}}
                        <div class="fixed top-4 left-4 sm:top-6 sm:left-6 z-[100002] flex items-center gap-2">
                            <span class="text-xs sm:text-sm font-medium bg-black/70 text-white backdrop-blur-md border border-white/20 px-3.5 py-1.5 rounded-full shadow-lg font-mono">
                                <span class="font-bold text-white" x-text="active + 1"></span>
                                <span class="text-white/60"> / </span>
                                <span class="text-white/60" x-text="photos.length"></span>
                            </span>
                        </div>

                        {{-- Tombol Navigasi Panah Kiri (Tetap di Sisi Kiri Layar) --}}
                        <button type="button" @click.stop="prev()" x-show="photos.length > 1"
                            class="fixed left-2 sm:left-6 top-1/2 -translate-y-1/2 w-11 h-11 sm:w-14 sm:h-14 rounded-full bg-black/60 hover:bg-black/85 active:scale-95 text-white flex items-center justify-center shadow-2xl backdrop-blur-md border border-white/30 transition z-[100002]"
                            title="{{ __('Foto Sebelumnya (←)') }}">
                            <svg class="w-6 h-6 sm:w-7 sm:h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15 19l-7-7 7-7"/>
                            </svg>
                        </button>

                        {{-- Tombol Navigasi Panah Kanan (Tetap di Sisi Kanan Layar) --}}
                        <button type="button" @click.stop="next()" x-show="photos.length > 1"
                            class="fixed right-2 sm:right-6 top-1/2 -translate-y-1/2 w-11 h-11 sm:w-14 sm:h-14 rounded-full bg-black/60 hover:bg-black/85 active:scale-95 text-white flex items-center justify-center shadow-2xl backdrop-blur-md border border-white/30 transition z-[100002]"
                            title="{{ __('Foto Berikutnya (→)') }}">
                            <svg class="w-6 h-6 sm:w-7 sm:h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7"/>
                            </svg>
                        </button>

                        {{-- Wadah Foto Utama (Ukuran Pas di Layar Laptop & HP, Tidak Ngezoom/Terlalu Besar) --}}
                        <div class="fixed inset-0 z-[100001] flex items-center justify-center p-4 sm:p-12 pt-16 pb-24"
                             @click.self="modalOpen = false"
                             @touchstart="handleTouchStart($event)"
                             @touchend="handleTouchEnd($event)">
                            <img :src="photos[active]"
                                 :alt="'{{ addslashes($villa->name) }} - Foto ' + (active + 1)"
                                 class="max-w-[92vw] sm:max-w-4xl max-h-[60vh] sm:max-h-[68vh] w-auto h-auto object-contain rounded-xl sm:rounded-2xl shadow-2xl transition-all duration-200 cursor-pointer"
                                 @click="next()">
                        </div>

                        {{-- Thumbnail Strip di Bagian Bawah Modal --}}
                        <div class="fixed bottom-3 sm:bottom-4 inset-x-0 z-[100002] px-4 pointer-events-none" x-show="photos.length > 1">
                            <div class="max-w-xl mx-auto flex items-center justify-center gap-2 overflow-x-auto py-1.5 px-3 bg-black/75 backdrop-blur-md rounded-2xl border border-white/20 pointer-events-auto shadow-2xl scrollbar-thin">
                                <template x-for="(p, idx) in photos" :key="idx">
                                    <button type="button" @click="active = idx"
                                        :class="active === idx ? 'ring-2 ring-white scale-105 opacity-100' : 'opacity-40 hover:opacity-80'"
                                        class="relative shrink-0 w-12 h-9 sm:w-16 sm:h-11 rounded-lg overflow-hidden border border-white/20 transition-all duration-150">
                                        <img :src="p" class="w-full h-full object-cover">
                                    </button>
                                </template>
                            </div>
                        </div>
                    </div>
                </template>
            </div>

            {{-- Detail --}}
            <div class="bg-white border border-gray-100 rounded-2xl p-5 sm:p-6 shadow-sm">
                <div class="flex items-start justify-between mb-3">
                    <h1 class="text-xl sm:text-2xl font-semibold text-gray-800 break-words">{{ $villa->name }}</h1>
                </div>

                <p class="text-[#3a6484] text-xs sm:text-sm flex items-start gap-1 mb-4 leading-relaxed">
                    <svg class="w-4 h-4 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                    </svg>
                    <span>{{ $villa->city }} — {{ $villa->address }}</span>
                </p>

                <div class="flex items-center gap-4 text-xs sm:text-sm text-gray-500 pb-4 border-b border-gray-100 mb-4">
                    <span class="flex items-center gap-1.5">
                        <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/>
                        </svg>
                        Maks. {{ $villa->capacity }} {{ __('tamu') }}
                    </span>
                </div>

                <h2 class="font-medium text-gray-800 text-sm sm:text-base mb-2">{{ __('Tentang Villa Ini') }}</h2>
                <p class="text-gray-600 text-xs sm:text-sm leading-relaxed">{{ $villa->description }}</p>
            </div>

            {{-- Fasilitas --}}
            @if($villa->facilities && count($villa->facilities) > 0)
                @php
                    $facilityLabels = [
                        'kolam_renang' => ['label' => __('Kolam Renang'), 'icon' => '🏊'],
                        'wifi'         => ['label' => __('WiFi'), 'icon' => '📶'],
                        'ac'           => ['label' => __('AC'), 'icon' => '❄️'],
                        'dapur'        => ['label' => __('Dapur'), 'icon' => '🍳'],
                        'parkir'       => ['label' => __('Parkir'), 'icon' => '🚗'],
                        'bbq'          => ['label' => __('Area BBQ'), 'icon' => '🔥'],
                        'tv'           => ['label' => __('TV'), 'icon' => '📺'],
                        'mesin_cuci'   => ['label' => __('Mesin Cuci'), 'icon' => '🫧'],
                        'karaoke'      => ['label' => __('Karaoke'), 'icon' => '🎤'],
                        'taman'        => ['label' => __('Taman'), 'icon' => '🌿'],
                        'cctv'         => ['label' => __('CCTV'), 'icon' => '📹'],
                        'sarapan'      => ['label' => __('Sarapan'), 'icon' => '🍽️'],
                    ];
                @endphp
                <div class="bg-white border border-gray-100 rounded-2xl p-5 sm:p-6 shadow-sm">
                    <h2 class="font-semibold text-gray-800 text-sm sm:text-base mb-4">{{ __('Fasilitas') }}</h2>
                    <div class="grid grid-cols-2 sm:grid-cols-3 gap-2.5 sm:gap-3">
                        @foreach($villa->facilities as $f)
                            @php
                                if (is_array($f)) {
                                    $item = ['label' => $f['name'] ?? '', 'icon' => $f['icon'] ?? '⭐'];
                                } else {
                                    $item = $facilityLabels[$f] ?? ['label' => $f, 'icon' => '✓'];
                                }
                            @endphp
                            <div class="flex items-center gap-2.5 bg-[#3a6484]/5 rounded-xl px-3 py-2 sm:py-2.5 min-w-0">
                                <span class="text-base sm:text-lg leading-none shrink-0">{{ $item['icon'] }}</span>
                                <span class="text-xs sm:text-sm text-gray-700 truncate">{{ $item['label'] }}</span>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif

            {{-- Destinasi Terdekat --}}
            @if($villa->nearby_destinations && count($villa->nearby_destinations) > 0)
                @php
                    $categoryMap = [
                        'pantai'       => ['label' => __('Pantai'),        'icon' => '🏖️'],
                        'gunung'       => ['label' => __('Gunung'),        'icon' => '⛰️'],
                        'air_terjun'   => ['label' => __('Air Terjun'),    'icon' => '💧'],
                        'danau'        => ['label' => __('Danau'),         'icon' => '🏞️'],
                        'kuliner'      => ['label' => __('Kuliner'),       'icon' => '🍽️'],
                        'pasar'        => ['label' => __('Pasar/Belanja'), 'icon' => '🛍️'],
                        'taman_wisata' => ['label' => __('Taman Wisata'),  'icon' => '🌿'],
                        'lainnya'      => ['label' => __('Lainnya'),       'icon' => '📍'],
                    ];
                    $sorted = collect($villa->nearby_destinations)->sortBy('distance')->values();
                @endphp
                <div class="bg-white border border-gray-100 rounded-2xl p-5 sm:p-6 shadow-sm">
                    <h2 class="font-semibold text-gray-800 text-sm sm:text-base mb-4">{{ __('Destinasi Terdekat') }}</h2>
                    <div class="divide-y divide-gray-50">
                        @foreach($sorted as $dest)
                            @php $cat = $categoryMap[$dest['category'] ?? 'lainnya'] ?? $categoryMap['lainnya']; @endphp
                            <div class="flex items-center justify-between py-2.5 sm:py-3 first:pt-0 last:pb-0 gap-2">
                                <div class="flex items-center gap-2.5 min-w-0">
                                    <span class="text-lg sm:text-xl leading-none w-7 text-center shrink-0">{{ $cat['icon'] }}</span>
                                    <div class="min-w-0">
                                        <p class="text-xs sm:text-sm font-medium text-gray-800 truncate">{{ $dest['name'] }}</p>
                                        <p class="text-[11px] text-gray-400">{{ $cat['label'] }}</p>
                                    </div>
                                </div>
                                @if(!empty($dest['distance']))
                                    <span class="text-xs sm:text-sm font-medium text-[#3a6484] shrink-0 whitespace-nowrap bg-blue-50 px-2 py-0.5 rounded-md">
                                        {{ $dest['distance'] }} km
                                    </span>
                                @endif
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif

            {{-- Peraturan Khusus Villa --}}
            @if($villa->house_rules && count($villa->house_rules) > 0)
                <div class="bg-amber-50/70 border border-amber-200/80 rounded-2xl p-5 sm:p-6 shadow-sm">
                    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2 mb-3.5">
                        <div class="flex items-center gap-2">
                            <span class="text-lg sm:text-xl">📜</span>
                            <h2 class="font-semibold text-amber-900 text-sm sm:text-base">{{ __('Peraturan Khusus Villa Ini') }}</h2>
                        </div>
                        <span class="self-start sm:self-auto text-[11px] sm:text-xs font-medium text-amber-800 bg-amber-100/90 px-2.5 py-0.5 rounded-full border border-amber-200/70 shrink-0">
                            {{ __('Ketentuan Pemilik') }}
                        </span>
                    </div>
                    <div class="space-y-2 sm:space-y-2.5">
                        @foreach($villa->house_rules as $rule)
                            <div class="flex items-start gap-2.5 text-xs sm:text-sm text-amber-900/90 bg-white/80 rounded-xl px-3.5 py-2.5 border border-amber-100">
                                <svg class="w-4 h-4 text-amber-600 mt-0.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                                </svg>
                                <span class="leading-relaxed">{{ __($rule) }}</span>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif

            {{-- Syarat & Ketentuan Pemesanan --}}
            <div class="bg-white border border-gray-100 rounded-2xl p-5 sm:p-6 shadow-sm">
                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2 mb-4">
                    <div class="flex items-center gap-2">
                        <svg class="w-5 h-5 text-[#3a6484] shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                        </svg>
                        <h2 class="font-semibold text-gray-800 text-sm sm:text-base">{{ __('Syarat & Ketentuan Pemesanan') }}</h2>
                    </div>
                    <span class="self-start sm:self-auto text-[11px] sm:text-xs font-medium text-gray-500 bg-gray-100 px-2.5 py-0.5 rounded-full shrink-0">
                        {{ __('Kebijakan Menginap') }}
                    </span>
                </div>

                <div class="space-y-4 text-xs sm:text-sm text-gray-600">
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <div class="bg-blue-50/70 border border-blue-100/60 rounded-xl p-3.5 flex items-start gap-3">
                            <span class="text-xl leading-none">🕒</span>
                            <div>
                                <p class="text-xs font-semibold text-gray-800">{{ __('Check-in & Check-out') }}</p>
                                <p class="text-xs text-gray-600 mt-0.5">{{ __('Check-in: 14.00 WIB | Check-out: 12.00 WIB') }}</p>
                            </div>
                        </div>
                        <div class="bg-purple-50/70 border border-purple-100/60 rounded-xl p-3.5 flex items-start gap-3">
                            <span class="text-xl leading-none">👥</span>
                            <div>
                                <p class="text-xs font-semibold text-gray-800">{{ __('Kapasitas Maksimal') }}</p>
                                <p class="text-xs text-gray-600 mt-0.5">{{ __('Maks.') }} {{ $villa->capacity }} {{ __('orang (dewasa + anak-anak)') }}</p>
                            </div>
                        </div>
                    </div>

                    <ul class="space-y-2 text-xs text-gray-600 list-disc list-inside leading-relaxed pl-1">
                        <li>{{ __('Pemesanan membutuhkan konfirmasi ketersediaan oleh admin terlebih dahulu (status waiting approval).') }}</li>
                        <li>{{ __('Setelah disetujui, bukti transfer bank diunggah untuk verifikasi administrasi.') }}</li>
                        <li>{{ __('Permintaan perubahan tanggal (*reschedule*) dapat diajukan maksimal 7 hari sebelum check-in.') }}</li>
                        <li>{{ __('Tamu wajib menjaga kebersihan, ketertiban, serta fasilitas villa selama menginap.') }}</li>
                    </ul>
                </div>
            </div>
        </div>

        {{-- Booking Widget --}}
        <div class="lg:col-span-1">
            <div class="bg-white border border-gray-200 rounded-2xl p-6 shadow-lg sticky top-24">
                <div class="mb-5">
                    <p class="text-xs text-gray-400 mb-0.5">{{ __('Mulai dari') }}</p>
                    <span class="text-2xl font-bold text-[#3a6484]">
                        Rp {{ number_format($villa->starting_price, 0, ',', '.') }}
                    </span>
                    <span class="text-gray-400 text-sm"> {{ __('/ malam') }}</span>
                    <p class="text-xs text-gray-400 mt-1">
                        {{ __('Weekday') }} Rp {{ number_format($villa->weekday_price, 0, ',', '.') }} · {{ __('Weekend (Jum-Sab)') }} Rp {{ number_format($villa->weekend_price, 0, ',', '.') }}
                    </p>
                </div>

                {{-- Sukses --}}
                @if($bookingSuccess)
                    <div class="bg-green-50 border border-green-200 rounded-xl p-5 text-center space-y-3">
                        <svg class="w-10 h-10 text-green-500 mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        <p class="text-green-700 font-semibold text-sm">{{ __('Pemesanan berhasil diajukan!') }}</p>

                        <p class="text-xs text-gray-600">{{ __('Silakan salin kode booking berikut dan simpan baik-baik:') }}</p>

                        <div x-data="{ copied: false }" class="bg-white border border-green-200 rounded-xl py-3 px-4">
                            <p class="text-xs text-gray-400 mb-1">{{ __('Kode Booking') }}</p>
                            <p class="text-2xl font-bold text-[#3a6484] tracking-widest mb-3">{{ $bookingCode }}</p>
                            <button type="button"
                                @click="navigator.clipboard.writeText('{{ $bookingCode }}'); copied = true; setTimeout(() => copied = false, 2000)"
                                class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-medium transition"
                                :class="copied ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-600 hover:bg-gray-200'">
                                <svg x-show="!copied" class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"/>
                                </svg>
                                <svg x-show="copied" class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                </svg>
                                <span x-text="copied ? '{{ __('Tersalin!') }}' : '{{ __('Salin Kode') }}'"></span>
                            </button>
                        </div>

                        <p class="text-xs text-gray-500 leading-relaxed">
                            {{ __('Buka halaman') }} <span class="font-medium text-[#3a6484]">{{ __('Cek Pesanan') }}</span> {{ __('dan masukkan kode ini untuk memantau status, upload bukti bayar, dan download invoice.') }}
                        </p>
                    </div>
                @else
                    <form wire:submit="ajukanPemesanan" class="space-y-3">
                        <div class="grid grid-cols-2 gap-2">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('Check-in') }}</label>
                                <input wire:model.live="check_in" type="date" min="{{ date('Y-m-d') }}"
                                    class="w-full border border-gray-200 rounded-xl px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-[#3a6484]/30 focus:border-[#3a6484]" />
                                @error('check_in') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('Check-out') }}</label>
                                <input wire:model.live="check_out" type="date" min="{{ $check_in ?: date('Y-m-d') }}"
                                    class="w-full border border-gray-200 rounded-xl px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-[#3a6484]/30 focus:border-[#3a6484]" />
                                @error('check_out') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                            </div>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">
                                {{ __('Jumlah Tamu') }} ({{ __('maks.') }} {{ $villa->capacity }})
                            </label>
                            <input wire:model="guests" type="number" min="1" max="{{ $villa->capacity }}"
                                class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-[#3a6484]/30 focus:border-[#3a6484]" />
                            @error('guests') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                        </div>

                        @if($this->nights > 0)
                            <div class="bg-gray-50 rounded-xl p-4 space-y-2 text-sm">
                                @foreach($this->priceBreakdown as $group)
                                    <div class="flex justify-between text-gray-600">
                                        <span>Rp {{ number_format($group['price'], 0, ',', '.') }} ({{ __($group['label']) }}) × {{ $group['nights'] }} {{ __('malam') }}</span>
                                        <span>Rp {{ number_format($group['price'] * $group['nights'], 0, ',', '.') }}</span>
                                    </div>
                                @endforeach
                                <div class="flex justify-between font-semibold text-gray-800 pt-2 border-t border-gray-200">
                                    <span>{{ __('Total') }}</span>
                                    <span class="text-[#3a6484]">Rp {{ number_format($this->totalPrice, 0, ',', '.') }}</span>
                                </div>
                            </div>
                        @endif

                        <button type="submit"
                            class="w-full py-3 bg-[#3a6484] text-white rounded-xl font-medium hover:bg-[#2f5370] transition">
                            {{ __('Ajukan Pemesanan') }}
                        </button>
                        <p class="text-center text-xs text-gray-400">
                            {{ __('Pesanan akan dikonfirmasi oleh admin terlebih dahulu.') }}<br>
                            {{ __('Silahkan Cek') }} <a href="{{ route('syarat-ketentuan') }}" wire:navigate class="underline text-[#3a6484] hover:text-[#2f5370]">{{ __('Syarat & Ketentuan') }}</a> {{ __('kami sebelum pemesanan.') }}
                        </p>
                    </form>
                @endif
            </div>
        </div>
    </div>
</div>
