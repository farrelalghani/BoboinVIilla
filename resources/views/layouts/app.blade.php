<!DOCTYPE html>
<html lang="id">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <title>{{ $title ?? config('app.name', 'Boboin Villa') }}</title>
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans antialiased bg-white text-gray-800">

        {{-- Header --}}
        <header class="bg-white shadow-sm sticky top-0 z-50">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="relative flex items-center h-16">
                    {{-- Logo --}}
                    <a href="{{ route('home') }}" wire:navigate class="flex items-center gap-2 shrink-0 z-10">
                        <img src="{{ asset('images/logo.jpg') }}" alt="Boboin Villa" class="h-14 w-auto">
                        <span class="hidden md:inline text-[#3a6484] font-bold text-lg">Boboin Villa</span>
                    </a>
                    {{-- Teks tengah — hanya di mobile --}}
                    <a href="{{ route('home') }}" wire:navigate
                       class="md:hidden absolute left-1/2 -translate-x-1/2 text-[#3a6484] font-bold text-lg">
                        Boboin Villa
                    </a>

                    {{-- Nav desktop --}}
                    <nav class="hidden md:flex items-center gap-6 ml-auto">
                        <a href="{{ route('villas.index') }}" wire:navigate
                           class="text-[#3a6484] hover:text-[#2f5370] transition text-sm font-medium">
                            Cari Villa
                        </a>
                        <a href="{{ route('guest.cek-pesanan') }}" wire:navigate
                           class="text-[#3a6484] hover:text-[#2f5370] transition text-sm font-medium">
                            Cek Pesanan
                        </a>
                        @auth
                            @if(auth()->user()->hasRole('admin'))
                                <a href="{{ route('admin.dashboard') }}" wire:navigate
                                   class="px-4 py-2 bg-[#3a6484] text-white rounded-lg text-sm font-medium hover:bg-[#2f5370] transition">
                                    Dashboard Admin
                                </a>
                            @endif
                        @endauth
                    </nav>

                    {{-- Hamburger mobile --}}
                    <button id="mobile-menu-btn" class="md:hidden p-2 text-[#3a6484] ml-auto z-10">
                        <svg id="icon-open" class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
                        </svg>
                        <svg id="icon-close" class="w-6 h-6 hidden" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>

                {{-- Mobile menu --}}
                <div id="mobile-menu" class="hidden md:hidden border-t border-[#3a6484]/20 py-4">
                    <nav class="flex flex-col gap-3">
                        <a href="{{ route('villas.index') }}" wire:navigate class="text-[#3a6484] text-sm font-medium py-2">Cari Villa</a>
                        <a href="{{ route('guest.cek-pesanan') }}" wire:navigate class="text-[#3a6484] text-sm font-medium py-2">Cek Pesanan</a>
                        @auth
                            @if(auth()->user()->hasRole('admin'))
                                <a href="{{ route('admin.dashboard') }}" wire:navigate class="text-[#3a6484] text-sm font-medium py-2">Dashboard Admin</a>
                            @endif
                        @endauth
                    </nav>
                </div>
            </div>
        </header>

        {{-- Content --}}
        <main>
            {{ $slot }}
        </main>

        {{-- Footer --}}
        <footer class="bg-[#3a6484] text-white pt-12 pb-6 mt-16">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-8 mb-10">
                    <div>
                        <h3 class="text-lg font-semibold mb-3">Boboin Villa</h3>
                        <p class="text-white/70 text-sm leading-relaxed mb-4">
                            Temukan dan pesan villa terbaik di destinasi wisata favorit Anda dengan mudah dan terpercaya.
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
                        <h4 class="font-medium mb-3 text-sm">Layanan</h4>
                        <ul class="space-y-2 text-sm text-white/70">
                            <li><a href="{{ route('villas.index') }}" wire:navigate class="hover:text-white transition">Cari Villa</a></li>
                            <li><a href="{{ route('guest.cek-pesanan') }}" wire:navigate class="hover:text-white transition">Cek Pesanan</a></li>
                        </ul>
                    </div>
                    <div>
                        <h4 class="font-medium mb-3 text-sm">Bantuan</h4>
                        <ul class="space-y-2 text-sm text-white/70">
                            <li><a href="https://wa.me/6282215433017?text=Halo%20Boboin%20Villa%2C%20saya%20ingin%20bertanya" target="_blank" class="hover:text-white transition">Hubungi Kami</a></li>
                            <li><a href="{{ route('syarat-ketentuan') }}" wire:navigate class="hover:text-white transition">Syarat & Ketentuan</a></li>
                        </ul>
                    </div>
                </div>
                <div class="border-t border-white/20 pt-6 text-center text-xs text-white/50">
                    © {{ date('Y') }} Boboin Villa. All rights reserved.
                </div>
            </div>
        </footer>

        {{-- Tombol WhatsApp mengambang — ganti nomor di bawah jika berubah --}}
        <a href="https://wa.me/6282215433017?text=Halo%20Boboin%20Villa%2C%20saya%20ingin%20bertanya%20mengenai%20pemesanan%20villa."
           target="_blank" rel="noopener"
           title="Hubungi kami via WhatsApp"
           class="fixed bottom-6 right-6 z-50 flex items-center gap-2.5 bg-[#3a6484] text-white pl-3 pr-4 py-3 rounded-full shadow-lg hover:shadow-xl hover:bg-[#2f5370] transition-all duration-200 group">
            <svg class="w-6 h-6 shrink-0" viewBox="0 0 24 24" fill="currentColor">
                <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347z"/>
                <path d="M12 0C5.373 0 0 5.373 0 12c0 2.126.558 4.121 1.532 5.849L.057 23.516a.75.75 0 00.927.927l5.666-1.475A11.934 11.934 0 0012 24c6.627 0 12-5.373 12-12S18.627 0 12 0zm0 21.75a9.734 9.734 0 01-4.964-1.359l-.356-.212-3.693.96.982-3.591-.232-.369A9.733 9.733 0 012.25 12C2.25 6.615 6.615 2.25 12 2.25S21.75 6.615 21.75 12 17.385 21.75 12 21.75z"/>
            </svg>
            <span class="text-sm font-medium whitespace-nowrap">Hubungi Kami</span>
        </a>

        <script>
            function initMobileMenu() {
                const btn = document.getElementById('mobile-menu-btn');
                const menu = document.getElementById('mobile-menu');
                const iconOpen = document.getElementById('icon-open');
                const iconClose = document.getElementById('icon-close');
                if (!btn) return;
                btn.onclick = () => {
                    menu.classList.toggle('hidden');
                    iconOpen.classList.toggle('hidden');
                    iconClose.classList.toggle('hidden');
                };
            }
            initMobileMenu();
            document.addEventListener('livewire:navigated', initMobileMenu);
        </script>
    </body>
</html>
