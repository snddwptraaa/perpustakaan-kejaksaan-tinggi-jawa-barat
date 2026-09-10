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

<body class="min-h-full admin-shell bg-kejati-canvas font-sans text-slate-900 antialiased">
    <a href="#admin-content" class="skip-link">Lewati ke konten utama</a>
    <div wire:loading.delay class="fixed inset-x-0 top-0 z-[100] h-1 overflow-hidden bg-kejati-gold/30" role="status" aria-label="Memuat">
        <div class="h-full w-1/3 animate-pulse rounded-full bg-kejati-gold"></div>
    </div>
    <div x-data="{
        open: false,
        desktop: window.innerWidth >= 1024,
        restoreNavScroll() {
            const nav = this.$refs.adminNav;
            const saved = sessionStorage.getItem('admin-nav-scroll');
            if (nav && saved !== null) {
                nav.scrollTop = parseInt(saved, 10) || 0;
            }
        },
        saveNavScroll() {
            const nav = this.$refs.adminNav;
            if (nav) {
                sessionStorage.setItem('admin-nav-scroll', nav.scrollTop);
            }
        }
    }"
    x-init="
        $nextTick(() => restoreNavScroll());
        document.addEventListener('livewire:navigating', () => saveNavScroll());
        document.addEventListener('livewire:navigated', () => requestAnimationFrame(() => restoreNavScroll()));
    "
    @resize.window="desktop = window.innerWidth >= 1024; if (desktop) open = false" @keydown.escape.window="open = false" class="min-h-screen lg:flex">
        <div x-cloak x-show="open" x-transition.opacity class="fixed inset-0 z-30 bg-slate-950/50 lg:hidden"
            @click="open = false"></div>
        <aside id="admin-navigation" x-trap.inert.noscroll="open &amp;&amp; !desktop" :inert="!desktop &amp;&amp; !open"
            class="fixed inset-y-0 left-0 z-40 flex w-[17rem] -translate-x-full flex-col overflow-hidden bg-kejati-dark text-white shadow-2xl transition-transform duration-200 lg:sticky lg:top-0 lg:h-screen lg:translate-x-0 lg:shadow-none"
            :class="{ 'translate-x-0': open }">
            <div class="pointer-events-none absolute -right-24 top-24 h-64 w-64 rounded-full border border-white/5"></div>
            <div class="pointer-events-none absolute -bottom-32 -left-20 h-80 w-80 rounded-full bg-kejati/30 blur-3xl"></div>
            <div class="relative flex min-h-20 items-center gap-3 border-b border-white/10 px-6">
                <img src="{{ asset('images/logo.svg') }}" alt="Logo Kejaksaan Tinggi Jawa Barat"
                    class="h-9 w-8 shrink-0 object-contain">
                <div>
                    <p class="text-[10px] font-semibold uppercase tracking-[0.2em] text-kejati-gold">Panel Admin</p>
                    <p class="text-sm font-semibold">Perpustakaan Kejati Jabar</p>
                </div>
                <button type="button"
                    class="ml-auto flex h-11 w-11 shrink-0 items-center justify-center rounded-lg text-white/60 hover:bg-white/10 hover:text-white lg:hidden"
                    @click="open = false" aria-label="Tutup menu">×</button>
            </div>
            <nav x-ref="adminNav" @scroll.passive.debounce.150ms="saveNavScroll()" class="relative flex-1 space-y-1 overflow-y-auto px-4 py-6" aria-label="Navigasi admin">
                <p class="mb-3 px-3 text-[10px] font-bold uppercase tracking-[0.2em] text-white/60">Menu utama</p>
                @php
                    $links = [
                        ['route' => 'admin.dashboard', 'label' => 'Ringkasan', 'icon' => 'dashboard'],
                        ['route' => 'admin.books', 'label' => 'Koleksi buku', 'icon' => 'book'],
                        ['route' => 'admin.categories', 'label' => 'Kategori', 'icon' => 'tag'],
                        ['route' => 'admin.loans', 'label' => 'Peminjaman', 'icon' => 'swap'],
                        ['route' => 'admin.members', 'label' => 'Anggota', 'icon' => 'users'],
                        ['route' => 'admin.history', 'label' => 'Riwayat sirkulasi', 'icon' => 'swap'],
                        ['route' => 'admin.visitors', 'label' => 'Pengunjung', 'icon' => 'users'],
                    ];

                    if (auth()->user()->isSuperadmin()) {
                        $links[] = ['route' => 'admin.users', 'label' => 'Pengguna Admin', 'icon' => 'shield'];
                        $links[] = ['route' => 'admin.audit', 'label' => 'Audit log', 'icon' => 'shield'];
                    }
                @endphp
                @foreach ($links as $link)
                    <a href="{{ route($link['route']) }}"
                        @if(request()->routeIs($link['route'])) aria-current="page" @endif
                        class="group flex items-center gap-3 rounded-xl px-3.5 py-3 text-sm font-semibold transition {{ request()->routeIs($link['route']) ? 'bg-kejati-gold text-kejati-dark shadow-lg shadow-black/10' : 'text-white/80 hover:bg-white/10 hover:text-white' }}"
                        wire:navigate>
                        <span class="flex h-8 w-8 items-center justify-center rounded-lg {{ request()->routeIs($link['route']) ? 'bg-kejati-dark/10' : 'bg-white/5 group-hover:bg-white/10' }}" aria-hidden="true">
                            @switch($link['icon'])
                                @case('dashboard') <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M4 5a1 1 0 011-1h4a1 1 0 011 1v4a1 1 0 01-1 1H5a1 1 0 01-1-1V5zm10 0a1 1 0 011-1h4a1 1 0 011 1v4a1 1 0 01-1 1h-4a1 1 0 01-1-1V5zM4 15a1 1 0 011-1h4a1 1 0 011 1v4a1 1 0 01-1 1H5a1 1 0 01-1-1v-4zm10 0a1 1 0 011-1h4a1 1 0 011 1v4a1 1 0 01-1 1h-4a1 1 0 01-1-1v-4z" /></svg> @break
                                @case('book') <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6.25v13m0-13C10.8 5.45 9.25 5 7.5 5S4.2 5.45 3 6.25v13c1.2-.8 2.75-1.25 4.5-1.25s3.3.45 4.5 1.25m0-13C13.2 5.45 14.75 5 16.5 5S19.8 5.45 21 6.25v13c-1.2-.8-2.75-1.25-4.5-1.25s-3.3.45-4.5 1.25" /></svg> @break
                                @case('tag') <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M7 7h.01M3 5a2 2 0 012-2h4.17a2 2 0 011.42.59l9.82 9.82a2 2 0 010 2.82l-4.18 4.18a2 2 0 01-2.82 0L3.59 10.59A2 2 0 013 9.17V5z" /></svg> @break
                                @case('swap') <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M7 7h11m0 0l-3-3m3 3l-3 3M17 17H6m0 0l3 3m-3-3l3-3" /></svg> @break
                                @case('shield') <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 3l7 3v5c0 4.4-2.8 8.2-7 10-4.2-1.8-7-5.6-7-10V6l7-3zm-2 9l1.5 1.5L15 10" /></svg> @break
                                @default <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M16 21v-2a4 4 0 00-4-4H6a4 4 0 00-4 4v2m7-10a4 4 0 100-8 4 4 0 000 8zm13 10v-2a4 4 0 00-3-3.87m-2-11.96a4 4 0 010 7.75" /></svg>
                            @endswitch
                        </span>
                        <span>{{ $link['label'] }}</span>
                        @if (request()->routeIs($link['route'])) <span class="ml-auto h-1.5 w-1.5 rounded-full bg-kejati-dark" aria-hidden="true"></span> @endif
                    </a>
                @endforeach
            </nav>
            <div class="relative border-t border-white/10 p-4">
                <a href="{{ route('profile') }}" class="mb-3 block rounded-xl bg-white/5 px-4 py-3 transition hover:bg-white/10" wire:navigate>
                    <p class="truncate text-sm font-semibold">{{ auth()->user()->name }}</p>
                    <p class="text-xs text-white/70">{{ ucfirst(auth()->user()->role) }} · Pengaturan akun</p>
                </a>
                <form method="POST" action="{{ route('logout') }}">@csrf<button
                        class="flex w-full items-center gap-3 rounded-xl px-4 py-3 text-sm font-medium text-white/80 transition hover:bg-white/10 hover:text-white"><span
                            aria-hidden="true">↪︎</span> Keluar</button></form>
            </div>
        </aside>
        <div class="min-w-0 flex-1">
            <header class="sticky top-0 z-20 flex h-16 items-center justify-between border-b border-stone-200/80 bg-white/90 px-4 backdrop-blur-xl sm:px-6 lg:px-10">
                <button type="button" class="rounded-lg p-2 text-slate-600 hover:bg-stone-100 lg:hidden"
                    @click="open = true" :aria-expanded="open" aria-controls="admin-navigation" aria-label="Buka menu">☰</button>
                <div class="ml-auto flex items-center gap-2">
                    <span class="hidden text-sm text-slate-500 sm:inline">{{ now()->translatedFormat('d M Y') }}</span>
                    <a href="{{ route('home') }}"
                        class="inline-flex items-center gap-2 rounded-xl border border-stone-200 bg-white px-3 py-2 text-xs font-bold text-kejati shadow-sm transition hover:border-kejati/30 hover:bg-kejati/5 sm:text-sm"
                        aria-label="Lihat situs publik" wire:navigate><span aria-hidden="true">↗︎</span><span class="hidden sm:inline">Situs publik</span></a>
                </div>
            </header>
            <main id="admin-content" tabindex="-1" class="relative p-4 sm:p-6 lg:p-8 xl:p-10">
                <div class="relative mx-auto max-w-[1600px]">{{ $slot }}</div>
            </main>
        </div>
    </div>
    <x-confirm-modal />
    @livewireScripts
</body>

</html>
