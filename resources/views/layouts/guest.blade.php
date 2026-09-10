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
    <body class="min-h-full bg-kejati-canvas font-sans text-slate-900 antialiased">
        <a href="#main-content" class="skip-link">Lewati ke konten utama</a>
        @php($isAuthPage = request()->routeIs('login', 'password.*'))
        @php($isKioskPage = request()->routeIs('kunjungan'))
        @php($isCatalogPage = request()->routeIs('katalog', 'buku.detail'))

        @if ($isAuthPage)
            <header class="border-b border-stone-200 bg-white">
                <div class="mx-auto flex max-w-5xl items-center justify-between px-5 py-4 lg:px-8">
                    <a href="{{ route('home') }}" class="flex items-center gap-3" wire:navigate>
                        <img src="{{ asset('images/logo.svg') }}" alt="" width="32" height="36" class="h-9 w-8 object-contain">
                        <span class="text-sm font-bold text-kejati-dark">Perpustakaan Kejati Jawa Barat</span>
                    </a>
                    <a href="{{ route('home') }}" class="text-sm font-semibold text-kejati hover:text-kejati-dark" wire:navigate>Kembali ke beranda</a>
                </div>
            </header>
        @elseif (! $isKioskPage && ! $isCatalogPage)
            <x-public-header />
        @endif

        <main class="{{ $isKioskPage ? 'min-h-screen' : 'min-h-[60vh]' }}" id="main-content" tabindex="-1">
            {{ $slot }}
        </main>

        @if (! $isAuthPage && ! $isKioskPage)
            <x-footer compact />
        @endif
        @livewireScripts
    </body>
</html>
