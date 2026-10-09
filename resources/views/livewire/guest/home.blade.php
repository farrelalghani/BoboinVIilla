<?php

use App\Models\Villa;
use Illuminate\Support\Facades\Storage;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;

new #[Layout('layouts.app')] class extends Component
{
    public string $check_in   = '';
    public string $check_out  = '';
    public string $guests     = '';

    public function search(): void
    {
        $this->redirect(route('villas.index', [
            'check_in'  => $this->check_in,
            'check_out' => $this->check_out,
            'guests'    => $this->guests,
        ]), navigate: true);
    }

    public function with(): array
    {
        return [
            'featuredVillas' => Villa::where('status', 'active')->latest()->take(6)->get(),
        ];
    }
}; ?>

<div>
    {{-- HERO --}}
    <section class="relative min-h-[520px] sm:min-h-[580px] overflow-hidden flex items-center">
        {{-- Background foto --}}
        <div class="absolute inset-0">
            <img src="/images/images.jpg" alt="Hero" class="w-full h-full object-cover">
            <div class="absolute inset-0 bg-[#1a3a52]/70"></div>
        </div>

        <div class="relative w-full max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-16 text-center">
            <h1 class="text-white text-3xl sm:text-4xl lg:text-5xl font-medium mb-4 leading-snug">
                Temukan Villa Impian Anda
            </h1>
            <p class="text-white/80 text-base sm:text-lg mb-10 max-w-xl mx-auto">
                Ribuan villa premium di destinasi wisata terbaik Indonesia siap menyambut Anda.
            </p>

            {{-- Search bar --}}
            <div class="w-full max-w-3xl mx-auto bg-white rounded-2xl shadow-2xl p-4 sm:p-6">
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                    {{-- Check-in --}}
                    <div class="flex items-center gap-3 bg-[#3a6484] rounded-xl px-4 py-3">
                        <svg class="w-5 h-5 text-white shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                        </svg>
                        <div class="flex-1 text-left">
                            <p class="text-white/70 text-xs mb-0.5">Check-in</p>
                            <input wire:model="check_in" type="date"
                                class="w-full bg-transparent text-white text-sm focus:outline-none [color-scheme:dark]" />
                        </div>
                    </div>

                    {{-- Check-out --}}
                    <div class="flex items-center gap-3 bg-[#3a6484] rounded-xl px-4 py-3">
                        <svg class="w-5 h-5 text-white shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                        </svg>
                        <div class="flex-1 text-left">
                            <p class="text-white/70 text-xs mb-0.5">Check-out</p>
                            <input wire:model="check_out" type="date"
                                class="w-full bg-transparent text-white text-sm focus:outline-none [color-scheme:dark]" />
                        </div>
                    </div>

                    {{-- Tamu --}}
                    <div class="flex items-center gap-3 bg-[#3a6484] rounded-xl px-4 py-3">
                        <svg class="w-5 h-5 text-white shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/>
                        </svg>
                        <div class="flex-1 text-left">
                            <p class="text-white/70 text-xs mb-0.5">Tamu</p>
                            <input wire:model="guests" type="number" min="1" placeholder="Berapa orang?"
                                class="w-full bg-transparent text-white placeholder-white/60 text-sm focus:outline-none" />
                        </div>
                    </div>
                </div>

                <button wire:click="search"
                    class="mt-4 w-full sm:w-auto px-8 py-3 bg-[#3a6484] text-white rounded-xl font-medium hover:bg-[#2f5370] transition flex items-center justify-center gap-2 mx-auto">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                    </svg>
                    Cari Villa
                </button>
            </div>
        </div>
    </section>

    {{-- FEATURED VILLAS --}}
    <section class="py-14 sm:py-20 bg-white">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-10">
                <h2 class="text-2xl sm:text-3xl font-medium text-[#3a6484] mb-3">Villa Pilihan</h2>
                <p class="text-gray-500 text-base max-w-xl mx-auto">
                    Pilihan villa terbaik yang siap membuat liburan Anda tak terlupakan.
                </p>
            </div>

            @if($featuredVillas->isEmpty())
                <div class="text-center py-16 text-gray-400">
                    <svg class="w-16 h-16 mx-auto mb-4 opacity-30" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16"/>
                    </svg>
                    <p>Belum ada villa tersedia.</p>
                </div>
            @else
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6 sm:gap-8">
                    @foreach($featuredVillas as $villa)
                        @php
                            $facilityIcons = [
                                'kolam_renang' => ['label' => 'Kolam Renang', 'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 15a4 4 0 004 4h9a5 5 0 10-.1-9.999 5.002 5.002 0 10-9.78 2.096A4.001 4.001 0 003 15z"/>'],
                                'wifi'         => ['label' => 'WiFi', 'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.111 16.404a5.5 5.5 0 017.778 0M12 20h.01m-7.08-7.071c3.904-3.905 10.236-3.905 14.141 0M1.394 9.393c5.857-5.857 15.355-5.857 21.213 0"/>'],
                                'ac'           => ['label' => 'AC', 'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>'],
                                'dapur'        => ['label' => 'Dapur', 'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/>'],
                                'parkir'       => ['label' => 'Parkir', 'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>'],
                                'bbq'          => ['label' => 'BBQ', 'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 18.657A8 8 0 016.343 7.343S7 9 9 10c0-2 .5-5 2.986-7C14 5 16.09 5.777 17.656 7.343A7.975 7.975 0 0120 13a7.975 7.975 0 01-2.343 5.657z"/>'],
                                'tv'           => ['label' => 'TV', 'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>'],
                                'karaoke'      => ['label' => 'Karaoke', 'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19V6l12-3v13M9 19c0 1.105-1.343 2-3 2s-3-.895-3-2 1.343-2 3-2 3 .895 3 2zm12-3c0 1.105-1.343 2-3 2s-3-.895-3-2 1.343-2 3-2 3 .895 3 2zM9 10l12-3"/>'],
                                'taman'        => ['label' => 'Taman', 'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z"/>'],
                                'mesin_cuci'   => ['label' => 'Mesin Cuci', 'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z"/>'],
                                'cctv'         => ['label' => 'CCTV', 'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.069A1 1 0 0121 8.82v6.36a1 1 0 01-1.447.894L15 14M3 8a2 2 0 012-2h8a2 2 0 012 2v8a2 2 0 01-2 2H5a2 2 0 01-2-2V8z"/>'],
                                'sarapan'      => ['label' => 'Sarapan', 'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>'],
                            ];
                        @endphp
                        <a href="{{ route('villas.show', $villa->slug) }}" wire:navigate
                           class="group bg-white rounded-2xl overflow-hidden shadow-sm hover:shadow-lg transition-all duration-300 border border-gray-100 flex flex-col">

                            {{-- Foto villa --}}
                            <div class="relative h-52 sm:h-56 overflow-hidden bg-gradient-to-br from-[#3a6484] to-[#2f5370] shrink-0">
                                @if($villa->image)
                                    <img src="{{ Storage::url($villa->image) }}"
                                        alt="{{ $villa->name }}"
                                        class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500">
                                @else
                                    <div class="absolute inset-0 flex items-center justify-center opacity-20">
                                        <svg class="w-24 h-24 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                                        </svg>
                                    </div>
                                @endif
                                {{-- Gradient overlay bawah --}}
                                <div class="absolute inset-x-0 bottom-0 h-20 bg-gradient-to-t from-black/60 to-transparent"></div>
                                {{-- Lokasi di atas foto --}}
                                <div class="absolute bottom-3 left-3 flex items-center gap-1.5 text-white text-xs font-medium">
                                    <svg class="w-3.5 h-3.5 shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M5.05 4.05a7 7 0 119.9 9.9L10 18.9l-4.95-4.95a7 7 0 010-9.9zM10 11a2 2 0 100-4 2 2 0 000 4z" clip-rule="evenodd"/>
                                    </svg>
                                    {{ $villa->city }}
                                </div>
                                {{-- Badge tersedia --}}
                            </div>

                            {{-- Konten card --}}
                            <div class="p-5 flex flex-col flex-1">
                                <h3 class="font-bold text-gray-800 group-hover:text-[#3a6484] transition text-base mb-3 leading-snug">
                                    {{ $villa->name }}
                                </h3>

                                {{-- Info kapasitas --}}
                                <div class="flex items-center gap-4 text-xs text-gray-500 mb-3">
                                    <span class="flex items-center gap-1">
                                        <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/>
                                        </svg>
                                        {{ $villa->capacity }} tamu
                                    </span>
                                </div>

                                {{-- Fasilitas pills --}}
                                @if($villa->facilities && count($villa->facilities) > 0)
                                    <div class="flex flex-wrap gap-1.5 mb-4">
                                        @foreach(array_slice($villa->facilities, 0, 3) as $f)
                                            <span class="inline-flex items-center gap-1 text-xs bg-gray-50 border border-gray-200 text-gray-600 px-2.5 py-1 rounded-full">
                                                <svg class="w-3 h-3 text-[#3a6484]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    {!! $facilityIcons[$f]['icon'] ?? '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>' !!}
                                                </svg>
                                                {{ $facilityIcons[$f]['label'] ?? $f }}
                                            </span>
                                        @endforeach
                                        @if(count($villa->facilities) > 3)
                                            <span class="text-xs text-gray-400 px-2 py-1">+{{ count($villa->facilities) - 3 }} lainnya</span>
                                        @endif
                                    </div>
                                @endif

                                {{-- Harga + tombol --}}
                                <div class="mt-auto pt-4 border-t border-gray-100 flex items-center justify-between gap-3">
                                    <div>
                                        <p class="text-xs text-gray-400 mb-0.5">Mulai dari</p>
                                        <p class="text-[#3a6484] font-bold text-lg leading-none">
                                            Rp {{ number_format($villa->starting_price, 0, ',', '.') }}
                                            <span class="text-gray-400 text-xs font-normal">/ malam</span>
                                        </p>
                                    </div>
                                    <span class="shrink-0 px-4 py-2 bg-[#3a6484] text-white text-xs font-semibold rounded-xl group-hover:bg-[#2f5370] transition">
                                        Lihat Detail
                                    </span>
                                </div>
                            </div>
                        </a>
                    @endforeach
                </div>

                <div class="text-center mt-10">
                    <a href="{{ route('villas.index') }}" wire:navigate
                       class="inline-block px-8 py-3 border-2 border-[#3a6484] text-[#3a6484] rounded-xl font-medium hover:bg-[#3a6484] hover:text-white transition">
                        Lihat Semua Villa
                    </a>
                </div>
            @endif
        </div>
    </section>
</div>
