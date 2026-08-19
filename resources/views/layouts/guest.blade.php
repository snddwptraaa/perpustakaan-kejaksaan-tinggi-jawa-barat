<!DOCTYPE html>
<html lang="id" class="h-full">
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
        <header class="border-b border-white/10 bg-kejati text-white shadow-lg shadow-kejati/10">
            <div class="mx-auto flex max-w-7xl items-center justify-between gap-6 px-5 py-4 lg:px-8">
                <a href="{{ route('home') }}" class="group flex items-center gap-3" wire:navigate>
                    <span class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl border border-kejati-gold-dark/50 bg-white/10 text-kejati-gold transition group-hover:bg-white/20" aria-hidden="true">
                        <svg class="h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7"><path d="M4 5.5A2.5 2.5 0 0 1 6.5 3H20v15H6.5A2.5 2.5 0 0 0 4 20.5v-15Z"/><path d="M4 20.5A2.5 2.5 0 0 1 6.5 18H20M8 7h8M8 10h8"/></svg>
                    </span>
                    <span>
                        <span class="block text-[10px] font-semibold uppercase tracking-[0.22em] text-kejati-gold">Kejaksaan Tinggi</span>
                        <span class="block text-sm font-semibold tracking-tight sm:text-base">Perpustakaan Jawa Barat</span>
                    </span>
                </a>
                <nav class="flex items-center gap-2 text-sm font-medium" aria-label="Navigasi utama">
                    <a href="{{ route('home') }}" class="hidden rounded-lg px-3 py-2 text-white/80 transition hover:bg-white/10 hover:text-white sm:inline-flex" wire:navigate>Beranda</a>
                    @if (session('visitor_checked_in'))
                        <a href="{{ route('katalog') }}" class="rounded-lg bg-kejati-gold px-3 py-2 font-semibold text-kejati transition hover:bg-yellow-200" wire:navigate>Katalog buku</a>
                    @else
                        <a href="{{ route('kunjungan') }}" class="rounded-lg bg-kejati-gold px-3 py-2 font-semibold text-kejati transition hover:bg-yellow-200" wire:navigate>Isi kunjungan</a>
                    @endif
                </nav>
            </div>
        </header>

        <main>
            {{ $slot }}
        </main>

        <footer class="border-t border-stone-200 bg-white">
            <div class="mx-auto flex max-w-7xl flex-col gap-2 px-5 py-6 text-xs text-slate-500 sm:flex-row sm:items-center sm:justify-between lg:px-8">
                <span>© {{ now()->year }} Perpustakaan Kejaksaan Tinggi Jawa Barat</span>
                <span class="font-medium text-kejati">Melayani dengan tertib, terbuka, dan berintegritas.</span>
            </div>
        </footer>
        @livewireScripts
    </body>
</html>
