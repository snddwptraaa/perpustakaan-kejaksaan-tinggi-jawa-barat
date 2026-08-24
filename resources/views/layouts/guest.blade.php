<!DOCTYPE html>
<html lang="id" class="h-full scroll-smooth">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <title>{{ $title ?? 'Perpustakaan Kejati Jawa Barat' }}</title>
        <meta name="description" content="Katalog dan layanan kunjungan Perpustakaan Kejaksaan Tinggi Jawa Barat.">
        @vite(['resources/css/app.css', 'resources/js/app.js'])
        @livewireStyles
    </head>
    <body class="min-h-full bg-stone-50 font-sans text-slate-900 antialiased">
        <header class="sticky top-0 z-50 border-b border-white/10 bg-kejati/95 text-white shadow-md backdrop-blur-md">
            <div class="mx-auto flex max-w-7xl items-center justify-between gap-6 px-5 py-4 lg:px-8">
                <a href="{{ route('home') }}" class="group flex items-center gap-3" wire:navigate>
                    <img src="{{ asset('images/logo.svg') }}" alt="Logo Kejaksaan Tinggi Jawa Barat" class="h-10 w-auto shrink-0 object-contain drop-shadow">
                    <span>
                        <span class="block text-[10px] font-semibold uppercase tracking-[0.22em] text-kejati-gold">Kejaksaan Tinggi Jawa Barat</span>
                        <span class="block text-sm font-semibold tracking-tight sm:text-base">Perpustakaan Digital</span>
                    </span>
                </a>
                <nav class="flex items-center gap-1 sm:gap-2 text-xs sm:text-sm font-medium" aria-label="Navigasi utama">
                    <a href="{{ route('home') }}" class="rounded-lg px-2.5 py-1.5 sm:px-3 sm:py-2 text-white/80 transition hover:bg-white/10 hover:text-white" wire:navigate>Beranda</a>
                    <a href="{{ route('home') }}#tentang" class="rounded-lg px-2.5 py-1.5 sm:px-3 sm:py-2 text-white/80 transition hover:bg-white/10 hover:text-white">Tentang</a>
                    <a href="{{ route('home') }}#layanan" class="rounded-lg px-2.5 py-1.5 sm:px-3 sm:py-2 text-white/80 transition hover:bg-white/10 hover:text-white">Layanan</a>
                    <a href="#kontak" class="rounded-lg px-2.5 py-1.5 sm:px-3 sm:py-2 text-white/80 transition hover:bg-white/10 hover:text-white">Kontak</a>
                </nav>
            </div>
        </header>

        <main>
            {{ $slot }}
        </main>

        <x-footer />
        @livewireScripts
    </body>
</html>
