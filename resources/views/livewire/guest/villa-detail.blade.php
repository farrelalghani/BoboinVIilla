<?php

use App\Models\Booking;
use App\Models\Villa;
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

<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10">
    {{-- Breadcrumb --}}
    <nav class="flex items-center gap-2 text-sm text-gray-400 mb-6">
        <a href="{{ route('home') }}" wire:navigate class="hover:text-[#3a6484] transition">Beranda</a>
        <span>/</span>
        <a href="{{ route('villas.index') }}" wire:navigate class="hover:text-[#3a6484] transition">Villa</a>
        <span>/</span>
        <span class="text-gray-600">{{ $villa->name }}</span>
    </nav>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        {{-- Info Villa --}}
        <div class="lg:col-span-2 space-y-6">
            {{-- Foto Villa --}}
            <div class="w-full h-72 sm:h-96 rounded-2xl overflow-hidden bg-gradient-to-br from-[#3a6484] to-[#2f5370] relative">
                @if($villa->image)
                    <img src="{{ Storage::url($villa->image) }}"
                        alt="{{ $villa->name }}"
                        class="w-full h-full object-cover">
                @else
                    <div class="absolute inset-0 opacity-10 flex items-center justify-center">
                        <svg class="w-40 h-40 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="0.8" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                        </svg>
                    </div>
                    <div class="absolute inset-0 flex items-end p-5">
                        <p class="text-white/50 text-sm">Foto villa belum tersedia</p>
                    </div>
                @endif
            </div>

            {{-- Detail --}}
            <div class="bg-white border border-gray-100 rounded-2xl p-6 shadow-sm">
                <div class="flex items-start justify-between mb-3">
                    <h1 class="text-2xl font-semibold text-gray-800">{{ $villa->name }}</h1>
                </div>

                <p class="text-[#3a6484] text-sm flex items-center gap-1 mb-4">
                    <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                    </svg>
                    {{ $villa->city }} — {{ $villa->address }}
                </p>

                <div class="flex items-center gap-4 text-sm text-gray-500 pb-4 border-b border-gray-100 mb-4">
                    <span class="flex items-center gap-1">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/>
                        </svg>
                        Maks. {{ $villa->capacity }} tamu
                    </span>
                </div>

                <h2 class="font-medium text-gray-700 mb-2">Tentang Villa Ini</h2>
                <p class="text-gray-600 text-sm leading-relaxed">{{ $villa->description }}</p>
            </div>

            {{-- Fasilitas --}}
            @if($villa->facilities && count($villa->facilities) > 0)
                @php
                    $facilityLabels = [
                        'kolam_renang' => ['label' => 'Kolam Renang', 'icon' => '🏊'],
                        'wifi'         => ['label' => 'WiFi', 'icon' => '📶'],
                        'ac'           => ['label' => 'AC', 'icon' => '❄️'],
                        'dapur'        => ['label' => 'Dapur', 'icon' => '🍳'],
                        'parkir'       => ['label' => 'Parkir', 'icon' => '🚗'],
                        'bbq'          => ['label' => 'Area BBQ', 'icon' => '🔥'],
                        'tv'           => ['label' => 'TV', 'icon' => '📺'],
                        'mesin_cuci'   => ['label' => 'Mesin Cuci', 'icon' => '🫧'],
                        'karaoke'      => ['label' => 'Karaoke', 'icon' => '🎤'],
                        'taman'        => ['label' => 'Taman', 'icon' => '🌿'],
                        'cctv'         => ['label' => 'CCTV', 'icon' => '📹'],
                        'sarapan'      => ['label' => 'Sarapan', 'icon' => '🍽️'],
                    ];
                @endphp
                <div class="bg-white border border-gray-100 rounded-2xl p-6 shadow-sm">
                    <h2 class="font-medium text-gray-700 mb-4">Fasilitas</h2>
                    <div class="grid grid-cols-2 sm:grid-cols-3 gap-3">
                        @foreach($villa->facilities as $f)
                            @php $item = $facilityLabels[$f] ?? ['label' => $f, 'icon' => '✓']; @endphp
                            <div class="flex items-center gap-2.5 bg-[#3a6484]/5 rounded-xl px-3 py-2.5">
                                <span class="text-lg leading-none">{{ $item['icon'] }}</span>
                                <span class="text-sm text-gray-700">{{ $item['label'] }}</span>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif

            {{-- Destinasi Terdekat --}}
            @if($villa->nearby_destinations && count($villa->nearby_destinations) > 0)
                @php
                    $categoryMap = [
                        'pantai'       => ['label' => 'Pantai',        'icon' => '🏖️'],
                        'gunung'       => ['label' => 'Gunung',        'icon' => '⛰️'],
                        'air_terjun'   => ['label' => 'Air Terjun',    'icon' => '💧'],
                        'danau'        => ['label' => 'Danau',         'icon' => '🏞️'],
                        'kuliner'      => ['label' => 'Kuliner',       'icon' => '🍽️'],
                        'pasar'        => ['label' => 'Pasar/Belanja', 'icon' => '🛍️'],
                        'taman_wisata' => ['label' => 'Taman Wisata',  'icon' => '🌿'],
                        'lainnya'      => ['label' => 'Lainnya',       'icon' => '📍'],
                    ];
                    $sorted = collect($villa->nearby_destinations)->sortBy('distance')->values();
                @endphp
                <div class="bg-white border border-gray-100 rounded-2xl p-6 shadow-sm">
                    <h2 class="font-medium text-gray-700 mb-4">Destinasi Terdekat</h2>
                    <div class="divide-y divide-gray-50">
                        @foreach($sorted as $dest)
                            @php $cat = $categoryMap[$dest['category'] ?? 'lainnya'] ?? $categoryMap['lainnya']; @endphp
                            <div class="flex items-center justify-between py-3 first:pt-0 last:pb-0">
                                <div class="flex items-center gap-3">
                                    <span class="text-xl leading-none w-8 text-center">{{ $cat['icon'] }}</span>
                                    <div>
                                        <p class="text-sm font-medium text-gray-800">{{ $dest['name'] }}</p>
                                        <p class="text-xs text-gray-400">{{ $cat['label'] }}</p>
                                    </div>
                                </div>
                                @if(!empty($dest['distance']))
                                    <span class="text-sm font-medium text-[#3a6484] shrink-0">
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
                <div class="bg-amber-50/70 border border-amber-200/80 rounded-2xl p-6 shadow-sm">
                    <h2 class="font-semibold text-amber-900 mb-3 flex items-center justify-between">
                        <span class="flex items-center gap-2">
                            <span class="text-lg">📜</span>
                            Peraturan Khusus Villa Ini
                        </span>
                        <span class="text-xs font-normal text-amber-700 bg-amber-100/80 px-2.5 py-1 rounded-full">
                            Ketentuan Pemilik
                        </span>
                    </h2>
                    <div class="space-y-2.5">
                        @foreach($villa->house_rules as $rule)
                            <div class="flex items-start gap-2.5 text-xs sm:text-sm text-amber-900/90 bg-white/70 rounded-xl px-3.5 py-2.5 border border-amber-100">
                                <svg class="w-4 h-4 text-amber-600 mt-0.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                                </svg>
                                <span>{{ $rule }}</span>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif

            {{-- Syarat & Ketentuan Pemesanan --}}
            <div class="bg-white border border-gray-100 rounded-2xl p-6 shadow-sm">
                <h2 class="font-semibold text-gray-800 mb-4 flex items-center justify-between">
                    <span class="flex items-center gap-2">
                        <svg class="w-5 h-5 text-[#3a6484]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                        </svg>
                        Syarat & Ketentuan Pemesanan
                    </span>
                    <span class="text-xs font-normal text-gray-400">Kebijakan Menginap</span>
                </h2>

                <div class="space-y-4 text-sm text-gray-600">
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <div class="bg-blue-50/70 border border-blue-100/60 rounded-xl p-3.5 flex items-start gap-3">
                            <span class="text-xl leading-none">🕒</span>
                            <div>
                                <p class="text-xs font-semibold text-gray-800">Check-in & Check-out</p>
                                <p class="text-xs text-gray-600 mt-0.5">Check-in: 14.00 WIB | Check-out: 12.00 WIB</p>
                            </div>
                        </div>
                        <div class="bg-purple-50/70 border border-purple-100/60 rounded-xl p-3.5 flex items-start gap-3">
                            <span class="text-xl leading-none">👥</span>
                            <div>
                                <p class="text-xs font-semibold text-gray-800">Kapasitas Maksimal</p>
                                <p class="text-xs text-gray-600 mt-0.5">Maks. {{ $villa->capacity }} orang (dewasa + anak-anak)</p>
                            </div>
                        </div>
                    </div>

                    <ul class="space-y-2 text-xs text-gray-600 list-disc list-inside leading-relaxed">
                        <li>Pemesanan membutuhkan konfirmasi ketersediaan oleh admin terlebih dahulu (status <strong class="text-orange-600 font-medium">waiting_admin</strong>).</li>
                        <li>Setelah disetujui, bukti transfer bank wajib diunggah untuk verifikasi administrasi.</li>
                        <li>Permintaan perubahan tanggal (*reschedule*) dapat diajukan maksimal 7 hari sebelum check-in.</li>
                        <li>Tamu wajib menjaga kebersihan, ketertiban, serta fasilitas villa selama menginap.</li>
                    </ul>
                </div>
            </div>
        </div>

        {{-- Booking Widget --}}
        <div class="lg:col-span-1">
            <div class="bg-white border border-gray-200 rounded-2xl p-6 shadow-lg sticky top-24">
                <div class="mb-5">
                    <p class="text-xs text-gray-400 mb-0.5">Mulai dari</p>
                    <span class="text-2xl font-bold text-[#3a6484]">
                        Rp {{ number_format($villa->starting_price, 0, ',', '.') }}
                    </span>
                    <span class="text-gray-400 text-sm"> / malam</span>
                    <p class="text-xs text-gray-400 mt-1">
                        Weekday Rp {{ number_format($villa->weekday_price, 0, ',', '.') }} · Weekend (Jum-Sab) Rp {{ number_format($villa->weekend_price, 0, ',', '.') }}
                    </p>
                </div>

                {{-- Sukses --}}
                @if($bookingSuccess)
                    <div class="bg-green-50 border border-green-200 rounded-xl p-5 text-center space-y-3">
                        <svg class="w-10 h-10 text-green-500 mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        <p class="text-green-700 font-semibold text-sm">Pemesanan berhasil diajukan!</p>

                        <p class="text-xs text-gray-600">Silakan salin kode booking berikut dan simpan baik-baik:</p>

                        <div x-data="{ copied: false }" class="bg-white border border-green-200 rounded-xl py-3 px-4">
                            <p class="text-xs text-gray-400 mb-1">Kode Booking</p>
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
                                <span x-text="copied ? 'Tersalin!' : 'Salin Kode'"></span>
                            </button>
                        </div>

                        <p class="text-xs text-gray-500 leading-relaxed">
                            Buka halaman <span class="font-medium text-[#3a6484]">Cek Pesanan</span> dan masukkan kode ini untuk memantau status, upload bukti bayar, dan download invoice.
                        </p>
                    </div>
                @else
                    <form wire:submit="ajukanPemesanan" class="space-y-3">
                        <div class="grid grid-cols-2 gap-2">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Check-in</label>
                                <input wire:model.live="check_in" type="date" min="{{ date('Y-m-d') }}"
                                    class="w-full border border-gray-200 rounded-xl px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-[#3a6484]/30 focus:border-[#3a6484]" />
                                @error('check_in') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Check-out</label>
                                <input wire:model.live="check_out" type="date" min="{{ $check_in ?: date('Y-m-d') }}"
                                    class="w-full border border-gray-200 rounded-xl px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-[#3a6484]/30 focus:border-[#3a6484]" />
                                @error('check_out') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                            </div>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">
                                Jumlah Tamu (maks. {{ $villa->capacity }})
                            </label>
                            <input wire:model="guests" type="number" min="1" max="{{ $villa->capacity }}"
                                class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-[#3a6484]/30 focus:border-[#3a6484]" />
                            @error('guests') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                        </div>

                        @if($this->nights > 0)
                            <div class="bg-gray-50 rounded-xl p-4 space-y-2 text-sm">
                                @foreach($this->priceBreakdown as $group)
                                    <div class="flex justify-between text-gray-600">
                                        <span>Rp {{ number_format($group['price'], 0, ',', '.') }} ({{ $group['label'] }}) × {{ $group['nights'] }} malam</span>
                                        <span>Rp {{ number_format($group['price'] * $group['nights'], 0, ',', '.') }}</span>
                                    </div>
                                @endforeach
                                <div class="flex justify-between font-semibold text-gray-800 pt-2 border-t border-gray-200">
                                    <span>Total</span>
                                    <span class="text-[#3a6484]">Rp {{ number_format($this->totalPrice, 0, ',', '.') }}</span>
                                </div>
                            </div>
                        @endif

                        <button type="submit"
                            class="w-full py-3 bg-[#3a6484] text-white rounded-xl font-medium hover:bg-[#2f5370] transition">
                            Ajukan Pemesanan
                        </button>
                        <p class="text-center text-xs text-gray-400">
                            Pesanan akan dikonfirmasi oleh admin terlebih dahulu.<br>
                            Silahkan Cek <a href="{{ route('syarat-ketentuan') }}" wire:navigate class="underline text-[#3a6484] hover:text-[#2f5370]">Syarat & Ketentuan</a> kami sebelum pemesanan.
                        </p>
                    </form>
                @endif
            </div>
        </div>
    </div>
</div>
