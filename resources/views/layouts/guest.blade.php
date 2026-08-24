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
        <a href="#main-content" class="skip-link">Lewati ke konten utama</a>
        <header x-data="{ menuOpen: false }" class="sticky top-0 z-50 border-b border-white/10 bg-kejati/95 text-white shadow-md backdrop-blur-md">
            <div class="mx-auto flex max-w-7xl items-center justify-between gap-6 px-5 py-4 lg:px-8">
                <a href="{{ route('home') }}" class="group flex items-center gap-3" wire:navigate>
                    <img src="{{ asset('images/logo.svg') }}" alt="Logo Kejaksaan Tinggi Jawa Barat" class="h-10 w-auto shrink-0 object-contain drop-shadow">
                    <span>
                        <span class="block text-[10px] font-semibold uppercase tracking-[0.22em] text-kejati-gold">Kejaksaan Tinggi Jawa Barat</span>
                        <span class="block text-sm font-semibold tracking-tight sm:text-base">Perpustakaan Digital</span>
                    </span>
                </a>
                <nav class="hidden items-center gap-1 text-sm font-medium md:flex" aria-label="Navigasi utama">
                    <a href="{{ route('home') }}" class="rounded-lg px-2.5 py-1.5 sm:px-3 sm:py-2 text-white/80 transition hover:bg-white/10 hover:text-white" wire:navigate>Beranda</a>
                    <a href="{{ route('home') }}#tentang" class="rounded-lg px-2.5 py-1.5 sm:px-3 sm:py-2 text-white/80 transition hover:bg-white/10 hover:text-white">Tentang</a>
                    <a href="{{ route('home') }}#layanan" class="rounded-lg px-2.5 py-1.5 sm:px-3 sm:py-2 text-white/80 transition hover:bg-white/10 hover:text-white">Layanan</a>
                    <a href="#kontak" class="rounded-lg px-2.5 py-1.5 sm:px-3 sm:py-2 text-white/80 transition hover:bg-white/10 hover:text-white">Kontak</a>
                </nav>
                <button type="button" @click="menuOpen = !menuOpen" :aria-expanded="menuOpen"
                    class="inline-flex h-10 w-10 items-center justify-center rounded-xl border border-white/15 bg-white/10 text-white md:hidden"
                    aria-controls="mobile-navigation" aria-label="Buka menu navigasi">
                    <svg x-show="!menuOpen" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" d="M4 7h16M4 12h16M4 17h16" /></svg>
                    <svg x-cloak x-show="menuOpen" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" d="M6 6l12 12M18 6L6 18" /></svg>
                </button>
            </div>
            <nav id="mobile-navigation" x-cloak x-show="menuOpen" x-transition.origin.top
                class="border-t border-white/10 px-5 py-3 md:hidden" aria-label="Navigasi mobile">
                <div class="mx-auto grid max-w-7xl grid-cols-2 gap-2 text-sm font-semibold">
                    <a href="{{ route('home') }}" class="rounded-xl bg-white/10 px-4 py-3" wire:navigate>Beranda</a>
                    <a href="{{ route('kunjungan') }}" class="rounded-xl bg-kejati-gold px-4 py-3 text-kejati-dark" wire:navigate>Jelajahi katalog</a>
                    <a href="{{ route('home') }}#tentang" class="rounded-xl px-4 py-3 text-white/80">Tentang</a>
                    <a href="#kontak" class="rounded-xl px-4 py-3 text-white/80">Kontak</a>
                </div>
            </nav>
        </header>

        <main id="main-content" tabindex="-1">
            {{ $slot }}
        </main>

        <x-footer />
        @livewireScripts
    </body>
</html>
