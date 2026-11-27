<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <title>{{ $title ?? config('app.name', 'Boboin Villa') }}</title>

        {{-- Favicon Boboin Villa --}}
        <link rel="icon" type="image/x-icon" href="{{ asset('favicon.ico') }}">
        <link rel="icon" type="image/png" sizes="32x32" href="{{ asset('favicon-32x32.png') }}">
        <link rel="icon" type="image/png" sizes="192x192" href="{{ asset('favicon.png') }}">
        <link rel="apple-touch-icon" href="{{ asset('apple-touch-icon.png') }}">

        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans antialiased bg-white text-gray-800">

        {{-- Header Bar dengan Capsule Dock ("Navbar di dalam Navbar") khas Boboin Villa --}}
        <header id="main-header"
                class="fixed top-0 inset-x-0 z-50 transition-all duration-300 bg-white/95 backdrop-blur-xl border-b border-[#3a6484]/15 shadow-sm">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex items-center justify-between h-16">
                    {{-- Kiri: Brand Logo Asli & Nama Boboin Villa --}}
                    <a href="{{ route('home') }}" wire:navigate class="flex items-center gap-2.5 sm:gap-3 shrink-0 group">
                        <img src="{{ asset('images/logo.jpg') }}" alt="Boboin Villa" class="h-11 sm:h-12 w-auto rounded-lg object-contain transition-transform group-hover:scale-105">
                        <span class="text-[#3a6484] font-bold text-lg sm:text-xl tracking-tight">Boboin Villa</span>
                    </a>

                    {{-- Kanan: Capsule Dock Menu ("Navbar di dalam Navbar") & Kontrol --}}
                    <div class="hidden md:flex items-center gap-3 ml-auto">
                        {{-- Capsule Dock Menu --}}
                        <nav aria-label="Main Navigation" class="flex items-center gap-1 rounded-full bg-[#3a6484]/[0.08] border border-[#3a6484]/20 p-1 backdrop-blur-md">
                            {{-- Beranda --}}
                            <a href="{{ route('home') }}" wire:navigate
                               class="px-4 py-1.5 rounded-full text-xs font-semibold transition-all duration-200 {{ request()->routeIs('home') ? 'bg-[#3a6484] text-white shadow-sm shadow-[#3a6484]/25' : 'text-[#3a6484] hover:text-[#25435b] hover:bg-[#3a6484]/10' }}">
                                {{ __('Beranda') }}
                            </a>

                            {{-- Cari Villa --}}
                            <a href="{{ route('villas.index') }}" wire:navigate
                               class="px-4 py-1.5 rounded-full text-xs font-semibold transition-all duration-200 {{ request()->routeIs('villas.*') ? 'bg-[#3a6484] text-white shadow-sm shadow-[#3a6484]/25' : 'text-[#3a6484] hover:text-[#25435b] hover:bg-[#3a6484]/10' }}">
                                {{ __('Cari Villa') }}
                            </a>

                            {{-- Cek Pesanan --}}
                            <a href="{{ route('guest.cek-pesanan') }}" wire:navigate
                               class="px-4 py-1.5 rounded-full text-xs font-semibold transition-all duration-200 {{ request()->routeIs('guest.cek-pesanan') ? 'bg-[#3a6484] text-white shadow-sm shadow-[#3a6484]/25' : 'text-[#3a6484] hover:text-[#25435b] hover:bg-[#3a6484]/10' }}">
                                {{ __('Cek Pesanan') }}
                            </a>
                        </nav>

                        {{-- Admin Dashboard (jika login admin) --}}
                        @auth
                            @if(auth()->user()->hasRole('admin'))
                                <a href="{{ route('admin.dashboard') }}" wire:navigate
                                   class="px-4 py-1.5 bg-[#3a6484] text-white rounded-full text-xs font-semibold hover:bg-[#2f5370] transition shadow-sm">
                                    {{ __('Dashboard Admin') }}
                                </a>
                            @endif
                        @endauth

                        {{-- Language Switcher ID/EN --}}
                        <x-language-switch />
                    </div>

                    {{-- Kontrol Mobile --}}
                    <div class="md:hidden flex items-center gap-2 ml-auto">
                        <x-language-switch />
                        <button id="mobile-menu-btn"
                                type="button"
                                class="p-2 text-[#3a6484] hover:bg-[#3a6484]/10 rounded-lg transition focus:outline-none"
                                aria-label="Menu">
                            <svg id="icon-open" class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
                            </svg>
                            <svg id="icon-close" class="w-6 h-6 hidden" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                            </svg>
                        </button>
                    </div>
                </div>

                {{-- Mobile Dropdown Menu (Capsule Card Khas Boboin Villa) --}}
                <div id="mobile-menu" class="hidden md:hidden border-t border-[#3a6484]/15 pt-3 pb-4">
                    <nav class="flex flex-col gap-1.5 bg-[#3a6484]/[0.05] border border-[#3a6484]/15 rounded-2xl p-2 backdrop-blur-md">
                        {{-- Beranda --}}
                        <a href="{{ route('home') }}" wire:navigate
                           class="flex items-center justify-between px-3.5 py-2.5 rounded-xl text-sm font-semibold transition-all duration-200 {{ request()->routeIs('home') ? 'bg-[#3a6484] text-white shadow-sm shadow-[#3a6484]/25' : 'text-[#3a6484] hover:bg-[#3a6484]/10' }}">
                            <span>{{ __('Beranda') }}</span>
                            <svg class="w-4 h-4 opacity-75" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
                            </svg>
                        </a>

                        {{-- Cari Villa --}}
                        <a href="{{ route('villas.index') }}" wire:navigate
                           class="flex items-center justify-between px-3.5 py-2.5 rounded-xl text-sm font-semibold transition-all duration-200 {{ request()->routeIs('villas.*') ? 'bg-[#3a6484] text-white shadow-sm shadow-[#3a6484]/25' : 'text-[#3a6484] hover:bg-[#3a6484]/10' }}">
                            <span>{{ __('Cari Villa') }}</span>
                            <svg class="w-4 h-4 opacity-75" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                            </svg>
                        </a>

                        {{-- Cek Pesanan --}}
                        <a href="{{ route('guest.cek-pesanan') }}" wire:navigate
                           class="flex items-center justify-between px-3.5 py-2.5 rounded-xl text-sm font-semibold transition-all duration-200 {{ request()->routeIs('guest.cek-pesanan') ? 'bg-[#3a6484] text-white shadow-sm shadow-[#3a6484]/25' : 'text-[#3a6484] hover:bg-[#3a6484]/10' }}">
                            <span>{{ __('Cek Pesanan') }}</span>
                            <svg class="w-4 h-4 opacity-75" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/>
                            </svg>
                        </a>

                        {{-- Dashboard Admin --}}
                        @auth
                            @if(auth()->user()->hasRole('admin'))
                                <a href="{{ route('admin.dashboard') }}" wire:navigate
                                   class="flex items-center justify-between px-3.5 py-2.5 rounded-xl text-sm font-semibold bg-[#2f5370] text-white shadow-sm mt-1">
                                    <span>{{ __('Dashboard Admin') }}</span>
                                    <svg class="w-4 h-4 opacity-75" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"/>
                                    </svg>
                                </a>
                            @endif
                        @endauth
                    </nav>
                </div>
            </div>
        </header>

        {{-- Content --}}
        <main class="{{ request()->routeIs('home') ? '' : 'pt-20 sm:pt-24' }}">
            {{ $slot }}
        </main>

        {{-- Footer --}}
        <footer class="bg-[#3a6484] text-white pt-12 pb-6 mt-16">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-8 mb-10">
                    <div>
                        <h3 class="text-lg font-semibold mb-3">Boboin Villa</h3>
                        <p class="text-white/70 text-sm leading-relaxed mb-4">
                            {{ __('Temukan dan pesan villa terbaik di destinasi wisata favorit Anda dengan mudah dan terpercaya.') }}
                        </p>
                        <div class="flex items-center gap-3">
                            <a href="https://instagram.com/boboinvilla" target="_blank" rel="noopener"
                               title="Instagram Boboin Villa"
                               class="text-white/60 hover:text-white transition">
                                <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
                                    <path d="M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.849 0 3.205-.012 3.584-.069 4.849-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07-3.204 0-3.584-.012-4.849-.07-3.26-.149-4.771-1.699-4.919-4.92-.058-1.265-.07-1.644-.07-4.849 0-3.204.013-3.583.07-4.849.149-3.227 1.664-4.771 4.919-4.919 1.266-.057 1.645-.069 4.849-.069zm0-2.163c-3.259 0-3.667.014-4.947.072-4.358.2-6.78 2.618-6.98 6.98-.059 1.281-.073 1.689-.073 4.948 0 3.259.014 3.668.072 4.948.2 4.358 2.618 6.78 6.98 6.98 1.281.058 1.689.072 4.948.072 3.259 0 3.668-.014 4.948-.072 4.354-.2 6.782-2.618 6.979-6.98.059-1.28.073-1.689.073-4.948 0-3.259-.014-3.667-.072-4.947-.196-4.354-2.617-6.78-6.979-6.98-1.281-.059-1.69-.073-4.949-.073zm0 5.838c-3.403 0-6.162 2.759-6.162 6.162s2.759 6.163 6.162 6.163 6.162-2.759 6.162-6.163c0-3.403-2.759-6.162-6.162-6.162zm0 10.162c-2.209 0-4-1.79-4-4 0-2.209 1.791-4 4-4s4 1.791 4 4c0 2.21-1.791 4-4 4zm6.406-11.845c-.796 0-1.441.645-1.441 1.44s.645 1.44 1.441 1.44c.795 0 1.439-.645 1.439-1.44s-.644-1.44-1.439-1.44z"/>
                                </svg>
                            </a>
                            <a href="https://tiktok.com/@boboinvillaa" target="_blank" rel="noopener"
                               title="TikTok Boboin Villa"
                               class="text-white/60 hover:text-white transition">
                                <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
                                    <path d="M19.59 6.69a4.83 4.83 0 01-3.77-4.25V2h-3.45v13.67a2.89 2.89 0 01-2.88 2.5 2.89 2.89 0 01-2.89-2.89 2.89 2.89 0 012.89-2.89c.28 0 .54.04.79.1V9.01a6.32 6.32 0 00-.79-.05 6.34 6.34 0 00-6.34 6.34 6.34 6.34 0 006.34 6.34 6.34 6.34 0 006.33-6.34V8.69a8.18 8.18 0 004.78 1.52V6.75a4.85 4.85 0 01-1.01-.06z"/>
                                </svg>
                            </a>
                        </div>
                    </div>
                    <div>
                        <h4 class="font-medium mb-3 text-sm">{{ __('Layanan') }}</h4>
                        <ul class="space-y-2 text-sm text-white/70">
                            <li><a href="{{ route('villas.index') }}" wire:navigate class="hover:text-white transition">{{ __('Cari Villa') }}</a></li>
                            <li><a href="{{ route('guest.cek-pesanan') }}" wire:navigate class="hover:text-white transition">{{ __('Cek Pesanan') }}</a></li>
                        </ul>
                    </div>
                    <div>
                        <h4 class="font-medium mb-3 text-sm">{{ __('Bantuan') }}</h4>
                        <ul class="space-y-2 text-sm text-white/70">
                            <li><a href="https://wa.me/6285724821667?text=Halo%20Boboin%20Villa%2C%20saya%20ingin%20bertanya" target="_blank" class="hover:text-white transition">{{ __('Hubungi Kami') }}</a></li>
                            <li><a href="{{ route('syarat-ketentuan') }}" wire:navigate class="hover:text-white transition">{{ __('Syarat & Ketentuan') }}</a></li>
                        </ul>
                    </div>
                </div>
                <div class="border-t border-white/20 pt-6 text-center text-xs text-white/50">
                    © {{ date('Y') }} Boboin Villa. {{ __('Hak cipta dilindungi undang-undang.') }}
                </div>
            </div>
        </footer>

        {{-- Pusat Bantuan Terpadu Mengambang (AI Concierge & WhatsApp Admin) --}}
        <livewire:guest.ai-concierge-chat />

        <script>
            function initMobileMenu() {
                const btn = document.getElementById('mobile-menu-btn');
                const menu = document.getElementById('mobile-menu');
                const iconOpen = document.getElementById('icon-open');
                const iconClose = document.getElementById('icon-close');
                if (!btn || !menu) return;

                // Tutup menu otomatis setiap kali halaman berpindah
                menu.classList.add('hidden');
                iconOpen?.classList.remove('hidden');
                iconClose?.classList.add('hidden');

                btn.onclick = (e) => {
                    e.stopPropagation();
                    const isOpen = !menu.classList.contains('hidden');
                    if (isOpen) {
                        menu.classList.add('hidden');
                        iconOpen?.classList.remove('hidden');
                        iconClose?.classList.add('hidden');
                    } else {
                        menu.classList.remove('hidden');
                        iconOpen?.classList.add('hidden');
                        iconClose?.classList.remove('hidden');
                    }
                };

                document.addEventListener('click', (e) => {
                    if (!menu.contains(e.target) && !btn.contains(e.target)) {
                        menu.classList.add('hidden');
                        iconOpen?.classList.remove('hidden');
                        iconClose?.classList.add('hidden');
                    }
                });
            }
            initMobileMenu();
            document.addEventListener('livewire:navigated', initMobileMenu);
        </script>
    </body>
</html>
