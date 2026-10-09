<!DOCTYPE html>
<html lang="id">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <title>{{ config('app.name', 'Boboin Villa') }}</title>
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans antialiased">
        <div class="min-h-screen flex">
            {{-- Sisi kiri: branding (hanya tampil di md ke atas) --}}
            <div class="hidden md:flex md:w-1/2 bg-[#3a6484] flex-col justify-between p-12">
                <a href="/" class="text-white text-2xl font-semibold tracking-wide">
                    Boboin Villa
                </a>
                <div>
                    <h2 class="text-white text-3xl font-medium leading-snug mb-4">
                        Temukan Villa Impian Anda<br>di Destinasi Terbaik
                    </h2>
                    <p class="text-white/70 text-base">
                        Ribuan pilihan villa premium siap menyambut kedatangan Anda.
                    </p>
                </div>
                <p class="text-white/40 text-sm">© {{ date('Y') }} Boboin Villa. All rights reserved.</p>
            </div>

            {{-- Sisi kanan: form --}}
            <div class="w-full md:w-1/2 flex flex-col justify-center items-center px-6 py-12 bg-white">
                {{-- Logo mobile --}}
                <a href="/" class="md:hidden text-[#3a6484] text-2xl font-semibold mb-8">
                    Boboin Villa
                </a>

                <div class="w-full max-w-md">
                    {{ $slot }}
                </div>
            </div>
        </div>
    </body>
</html>
