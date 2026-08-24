<!DOCTYPE html>
<html lang="id" class="h-full">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title ?? 'Panel Admin — Perpustakaan Kejati Jawa Barat' }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
</head>

<body class="min-h-full bg-stone-100 font-sans text-slate-900 antialiased">
    <div x-data="{ open: false }" class="min-h-screen lg:flex">
        <div x-cloak x-show="open" x-transition.opacity class="fixed inset-0 z-30 bg-slate-950/50 lg:hidden"
            @click="open = false"></div>
        <aside
            class="fixed inset-y-0 left-0 z-40 flex w-72 -translate-x-full flex-col bg-kejati-dark text-white transition-transform duration-200 lg:static lg:translate-x-0"
            :class="{ 'translate-x-0': open }">
            <div class="flex h-20 items-center gap-3 border-b border-white/10 px-6">
                <img src="{{ asset('images/logo.svg') }}" alt="Logo Kejaksaan Tinggi Jawa Barat"
                    class="h-9 w-auto shrink-0 object-contain drop-shadow">
                <div>
                    <p class="text-[10px] font-semibold uppercase tracking-[0.2em] text-kejati-gold">Panel Admin</p>
                    <p class="font-semibold">Perpustakaan Jabar</p>
                </div>
                <button type="button"
                    class="ml-auto rounded-lg p-2 text-white/60 hover:bg-white/10 hover:text-white lg:hidden"
                    @click="open = false" aria-label="Tutup menu">×</button>
            </div>
            <nav class="flex-1 space-y-1 px-4 py-6" aria-label="Navigasi admin">
                @php
                    $links = [
                        ['route' => 'admin.dashboard', 'label' => 'Ringkasan', 'icon' => '▦'],
                        ['route' => 'admin.books', 'label' => 'Koleksi buku', 'icon' => '▤'],
                        ['route' => 'admin.categories', 'label' => 'Kategori', 'icon' => '◈'],
                        ['route' => 'admin.loans', 'label' => 'Peminjaman', 'icon' => '↗'],
                        ['route' => 'admin.visitors', 'label' => 'Pengunjung', 'icon' => '♙'],
                    ];

                    if (auth()->user()->isSuperadmin()) {
                        $links[] = ['route' => 'admin.users', 'label' => 'Pengguna admin', 'icon' => '♙'];
                    }
                @endphp
                @foreach ($links as $link)
                    <a href="{{ route($link['route']) }}"
                        class="flex items-center gap-3 rounded-xl px-4 py-3 text-sm font-medium transition {{ request()->routeIs($link['route']) ? 'bg-kejati-gold text-kejati-dark shadow-lg shadow-black/10' : 'text-white/70 hover:bg-white/10 hover:text-white' }}"
                        wire:navigate>
                        <span class="w-5 text-center text-lg leading-none"
                            aria-hidden="true">{{ $link['icon'] }}</span>{{ $link['label'] }}
                    </a>
                @endforeach
            </nav>
            <div class="border-t border-white/10 p-4">
                <div class="mb-3 rounded-xl bg-white/5 px-4 py-3">
                    <p class="truncate text-sm font-semibold">{{ auth()->user()->name }}</p>
                    <p class="text-xs text-white/50">{{ ucfirst(auth()->user()->role) }}</p>
                </div>
                <form method="POST" action="{{ route('logout') }}">@csrf<button
                        class="flex w-full items-center gap-3 rounded-xl px-4 py-3 text-sm font-medium text-white/65 transition hover:bg-white/10 hover:text-white"><span
                            aria-hidden="true">↪</span> Keluar</button></form>
            </div>
        </aside>
        <div class="min-w-0 flex-1">
            <header class="flex h-20 items-center justify-between border-b border-stone-200 bg-white px-5 lg:px-10">
                <button type="button" class="rounded-lg p-2 text-slate-600 hover:bg-stone-100 lg:hidden"
                    @click="open = true" aria-label="Buka menu">☰</button>
                <div class="hidden lg:block">
                    <p class="text-xs font-semibold uppercase tracking-[0.18em] text-kejati-gold-dark">Sistem informasi
                        perpustakaan</p>
                    <p class="mt-0.5 text-sm text-slate-500">Kejaksaan Tinggi Jawa Barat</p>
                </div>
                <a href="{{ route('home') }}"
                    class="ml-auto inline-flex items-center gap-2 rounded-lg px-3 py-2 text-sm font-semibold text-kejati transition hover:bg-kejati/5"
                    wire:navigate><span>←</span> Lihat situs publik</a>
            </header>
            <main class="p-5 lg:p-10">{{ $slot }}</main>
        </div>
    </div>
    <x-confirm-modal />
    @livewireScripts
</body>

</html>