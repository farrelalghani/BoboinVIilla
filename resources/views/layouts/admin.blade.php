<!DOCTYPE html>
<html lang="id">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <title>Admin — {{ config('app.name', 'Boboin Villa') }}</title>
        @vite(['resources/css/app.css', 'resources/js/app.js'])
        <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
    </head>
    <body class="font-sans antialiased bg-gray-100">
        {{-- Topbar mobile --}}
        <header class="md:hidden flex items-center justify-between bg-[#1e3a8a] text-white px-4 py-3 sticky top-0 z-30">
            <div>
                <h1 class="text-lg font-semibold leading-tight">Boboin Villa</h1>
                <p class="text-blue-200 text-xs">Admin Dashboard</p>
            </div>
            <button id="admin-menu-btn" class="p-2 text-white">
                <svg id="admin-icon-open" class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
                </svg>
                <svg id="admin-icon-close" class="w-6 h-6 hidden" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </header>

        {{-- Overlay mobile --}}
        <div id="admin-sidebar-overlay" class="hidden md:hidden fixed inset-0 bg-black/50 z-40"></div>

        <div class="min-h-screen flex">
            {{-- Sidebar --}}
            <aside id="admin-sidebar"
                class="w-64 bg-[#1e3a8a] text-white min-h-screen flex flex-col shrink-0
                    fixed md:static inset-y-0 left-0 z-50
                    -translate-x-full md:translate-x-0 transition-transform duration-200 ease-in-out overflow-y-auto">
                <div class="p-6 border-b border-blue-700 flex items-center justify-between">
                    <div>
                        <h1 class="text-xl font-semibold">Boboin Villa</h1>
                        <p class="text-blue-200 text-xs mt-1">Admin Dashboard</p>
                    </div>
                    <button id="admin-sidebar-close" class="md:hidden text-blue-200 hover:text-white">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>

                <nav class="flex-1 p-4">
                    <ul class="space-y-1">
                        <li>
                            <a href="{{ route('admin.dashboard') }}" wire:navigate
                               class="flex items-center gap-3 px-4 py-3 rounded-lg transition-colors {{ request()->routeIs('admin.dashboard') ? 'bg-blue-600 text-white' : 'text-blue-100 hover:bg-blue-700' }}">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
                                </svg>
                                <span>Dashboard</span>
                            </a>
                        </li>
                        <li>
                            <a href="{{ route('admin.villas.index') }}" wire:navigate
                               class="flex items-center gap-3 px-4 py-3 rounded-lg transition-colors {{ request()->routeIs('admin.villas*') ? 'bg-blue-600 text-white' : 'text-blue-100 hover:bg-blue-700' }}">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                                </svg>
                                <span>Kelola Villa</span>
                            </a>
                        </li>
                        <li>
                            <a href="{{ route('admin.bookings.index') }}" wire:navigate
                               class="flex items-center gap-3 px-4 py-3 rounded-lg transition-colors {{ request()->routeIs('admin.bookings*') ? 'bg-blue-600 text-white' : 'text-blue-100 hover:bg-blue-700' }}">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                                </svg>
                                <span>Kelola Pesanan</span>
                            </a>
                        </li>
                        <li>
                            <a href="{{ route('admin.payments.index') }}" wire:navigate
                               class="flex items-center gap-3 px-4 py-3 rounded-lg transition-colors {{ request()->routeIs('admin.payments*') ? 'bg-blue-600 text-white' : 'text-blue-100 hover:bg-blue-700' }}">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/>
                                </svg>
                                <span>Verifikasi Bayar</span>
                            </a>
                        </li>
                        <li>
                            <a href="{{ route('admin.kalender') }}" wire:navigate
                               class="flex items-center gap-3 px-4 py-3 rounded-lg transition-colors {{ request()->routeIs('admin.kalender*') ? 'bg-blue-600 text-white' : 'text-blue-100 hover:bg-blue-700' }}">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                </svg>
                                <span>Kalender Villa</span>
                            </a>
                        </li>
                    </ul>
                </nav>

                <div class="p-4 border-t border-blue-700">
                    <div class="flex items-center gap-3 px-4 py-3">
                        <div class="w-9 h-9 rounded-full bg-blue-600 flex items-center justify-center shrink-0">
                            <span class="text-sm font-semibold">{{ strtoupper(substr(auth()->user()->name, 0, 2)) }}</span>
                        </div>
                        <div class="flex-1 min-w-0">
                            <p class="text-sm font-medium truncate">{{ auth()->user()->name }}</p>
                            <p class="text-blue-200 text-xs">Admin</p>
                        </div>
                    </div>
                    <form method="POST" action="{{ route('logout') }}" class="mt-2 px-4">
                        @csrf
                        <button type="submit" class="w-full text-left text-blue-200 hover:text-white text-sm transition">
                            Keluar
                        </button>
                    </form>
                </div>
            </aside>

            {{-- Main content --}}
            <main class="flex-1 p-4 sm:p-6 md:p-8 overflow-auto min-w-0">
                {{ $slot }}
            </main>
        </div>

        <script>
            function initAdminSidebar() {
                const btn      = document.getElementById('admin-menu-btn');
                const closeBtn = document.getElementById('admin-sidebar-close');
                const sidebar  = document.getElementById('admin-sidebar');
                const overlay  = document.getElementById('admin-sidebar-overlay');
                const iconOpen  = document.getElementById('admin-icon-open');
                const iconClose = document.getElementById('admin-icon-close');
                if (!btn || !sidebar || !overlay) return;

                const openSidebar = () => {
                    sidebar.classList.remove('-translate-x-full');
                    overlay.classList.remove('hidden');
                    iconOpen?.classList.add('hidden');
                    iconClose?.classList.remove('hidden');
                };
                const closeSidebar = () => {
                    sidebar.classList.add('-translate-x-full');
                    overlay.classList.add('hidden');
                    iconOpen?.classList.remove('hidden');
                    iconClose?.classList.add('hidden');
                };

                btn.onclick = () => {
                    sidebar.classList.contains('-translate-x-full') ? openSidebar() : closeSidebar();
                };
                closeBtn && (closeBtn.onclick = closeSidebar);
                overlay.onclick = closeSidebar;

                // Tutup sidebar otomatis setelah klik menu di mobile
                sidebar.querySelectorAll('a').forEach(link => {
                    link.addEventListener('click', () => {
                        if (window.innerWidth < 768) closeSidebar();
                    });
                });
            }
            initAdminSidebar();
            document.addEventListener('livewire:navigated', initAdminSidebar);
        </script>
    </body>
</html>
