<!DOCTYPE html>
<html lang="id" class="h-full scroll-smooth">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Perpustakaan Kejati Jawa Barat</title>
    <meta name="description"
        content="Perpustakaan Kejaksaan Tinggi Jawa Barat — ruang referensi hukum dan pengetahuan.">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="min-h-full font-sans antialiased bg-stone-50 text-slate-900">
    <a href="#main-content" class="skip-link">Lewati ke konten utama</a>
    <header x-data="{ menuOpen: false }" class="sticky top-0 z-50 text-white border-b shadow-md border-white/10 bg-kejati/95 backdrop-blur-md">
        <div class="mx-auto flex max-w-7xl items-center justify-between gap-4 px-5 py-3.5 lg:px-8">
            <a href="{{ route('home') }}" class="flex items-center gap-3">
                <img src="{{ asset('images/logo.svg') }}" alt="Logo Kejaksaan Tinggi Jawa Barat"
                    class="object-contain w-auto h-10 shrink-0 drop-shadow">
                <span>
                    <span class="block text-[10px] font-semibold uppercase tracking-[0.22em] text-kejati-gold">Kejaksaan
                        Tinggi Jawa Barat</span>
                    <span class="block text-sm font-semibold">Perpustakaan Digital</span>
                </span>
            </a>

            <nav class="hidden items-center gap-1 text-sm font-medium md:flex" aria-label="Navigasi utama">
                <a href="#hero"
                    class="rounded-lg px-2.5 py-1.5 sm:px-3 sm:py-2 text-white/90 transition hover:bg-white/10 hover:text-white">Beranda</a>
                <a href="#tentang"
                    class="rounded-lg px-2.5 py-1.5 sm:px-3 sm:py-2 text-white/90 transition hover:bg-white/10 hover:text-white">Tentang</a>
                <a href="#layanan"
                    class="rounded-lg px-2.5 py-1.5 sm:px-3 sm:py-2 text-white/90 transition hover:bg-white/10 hover:text-white">Layanan</a>
                <a href="#kontak"
                    class="rounded-lg px-2.5 py-1.5 sm:px-3 sm:py-2 text-white/90 transition hover:bg-white/10 hover:text-white">Kontak</a>
            </nav>

            <a href="{{ route('login') }}"
                class="hidden px-4 py-2 text-sm font-semibold text-white transition border rounded-lg md:inline-flex border-white/25 hover:bg-white/10">Masuk
                petugas</a>
            <button type="button" @click="menuOpen = !menuOpen" :aria-expanded="menuOpen" aria-controls="home-mobile-navigation"
                class="inline-flex h-10 w-10 items-center justify-center rounded-xl border border-white/15 bg-white/10 md:hidden" aria-label="Buka menu">
                <svg x-show="!menuOpen" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" d="M4 7h16M4 12h16M4 17h16" /></svg>
                <svg x-cloak x-show="menuOpen" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" d="M6 6l12 12M18 6L6 18" /></svg>
            </button>
        </div>
        <nav id="home-mobile-navigation" x-cloak x-show="menuOpen" x-transition.origin.top class="border-t border-white/10 px-5 py-3 md:hidden" aria-label="Navigasi mobile">
            <div class="mx-auto grid max-w-7xl grid-cols-2 gap-2 text-sm font-semibold">
                <a href="#tentang" class="rounded-xl bg-white/10 px-4 py-3">Tentang</a>
                <a href="#layanan" class="rounded-xl bg-white/10 px-4 py-3">Layanan</a>
                <a href="{{ route('kunjungan') }}" class="rounded-xl bg-kejati-gold px-4 py-3 text-kejati-dark">Jelajahi katalog</a>
                <a href="{{ route('login') }}" class="rounded-xl px-4 py-3 text-white/80">Masuk petugas</a>
            </div>
        </nav>
    </header>

    <main id="main-content" tabindex="-1">
        <section id="hero" class="relative overflow-hidden text-white bg-kejati">
            <div class="absolute -right-40 -top-48 h-[38rem] w-[38rem] rounded-full border border-kejati-gold/15"></div>
            <div class="absolute bottom-0 right-0 h-40 w-2/3 bg-gradient-to-l from-[#064E3B]/60 to-transparent"></div>
            <div
                class="relative mx-auto grid min-h-[580px] max-w-7xl items-center gap-12 px-5 pb-20 pt-16 lg:grid-cols-[1fr_0.75fr] lg:px-8 lg:pt-20">
                <div class="max-w-2xl">
                    <p
                        class="mb-6 inline-flex items-center gap-3 text-xs font-bold uppercase tracking-[0.25em] text-kejati-gold">
                        <span class="w-10 h-px bg-kejati-gold"></span> Ruang pengetahuan Kejati Jabar
                    </p>
                    <h1 class="font-display text-5xl leading-[1.04] tracking-tight sm:text-6xl lg:text-7xl">
                        Baca untuk <span class="text-kejati-gold">memahami.</span>
                    </h1>
                    <p class="max-w-xl text-lg leading-8 mt-7 text-white/70">
                        Akses katalog referensi hukum dan koleksi pengetahuan Perpustakaan Kejaksaan Tinggi Jawa
                        Barat—teratur, terbuka, dan dekat dengan kebutuhan Anda.
                    </p>
                    <div class="flex flex-col gap-3 mt-10 sm:flex-row">
                        <a href="{{ route('kunjungan') }}"
                            class="inline-flex items-center justify-center gap-3 rounded-xl bg-kejati-gold px-6 py-3.5 text-sm font-bold text-kejati-dark shadow-xl shadow-black/10 transition hover:bg-yellow-200">
                            Jelajahi katalog <span class="text-lg">→</span>
                        </a>
                        <a href="#tentang"
                            class="inline-flex items-center justify-center rounded-xl border border-white/20 px-6 py-3.5 text-sm font-semibold text-white/80 transition hover:bg-white/10 hover:text-white">
                            Tentang perpustakaan
                        </a>
                    </div>
                </div>
                <div class="relative hidden lg:block">
                    <div
                        class="relative mx-auto aspect-[4/5] max-w-sm rotate-3 rounded-[2rem] border border-kejati-gold/30 bg-gradient-to-br from-white/15 to-white/5 p-5 shadow-2xl shadow-black/20">
                        <div
                            class="flex h-full flex-col justify-between rounded-[1.4rem] border border-white/10 bg-kejati-dark/80 p-7">
                            <div>
                                <p class="text-xs uppercase tracking-[0.22em] text-kejati-gold">Koleksi hukum</p>
                                <div class="h-px mt-8 bg-kejati-gold/50"></div>
                                <p class="mt-6 text-4xl font-semibold leading-tight">Pengetahuan<br><span
                                        class="text-kejati-gold">yang terjaga.</span></p>
                            </div>
                            <div>
                                <p class="text-sm leading-6 text-white/50">Perpustakaan Kejaksaan Tinggi Jawa Barat</p>
                                <div class="flex items-end justify-between mt-5">
                                    <span class="text-5xl font-light text-white/20">01</span>
                                    <span class="text-2xl text-kejati-gold">✦</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Section Layanan -->
        <section id="layanan" class="scroll-mt-16 border-b border-stone-200/60 bg-[#F4F7F5] py-14">
            <div class="px-5 mx-auto max-w-7xl lg:px-8">
                <div class="mb-8">
                    <p class="text-xs font-bold uppercase tracking-[0.2em] text-kejati-gold-dark">Fasilitas & Akses</p>
                    <h2 class="mt-1 text-2xl font-bold tracking-tight text-kejati-dark sm:text-3xl">Layanan</h2>
                </div>

                <div class="grid grid-cols-1 gap-5 sm:grid-cols-2 lg:grid-cols-4">
                    <!-- 1. Baca di tempat -->
                    <div
                        class="flex flex-col items-center justify-center text-center transition duration-300 bg-white border shadow-sm group rounded-2xl border-stone-200/80 p-7 hover:-translate-y-1 hover:border-kejati-gold/60 hover:shadow-md">
                        <div
                            class="flex items-center justify-center mb-4 transition duration-300 shadow-inner h-14 w-14 rounded-2xl bg-emerald-50 text-kejati group-hover:bg-kejati group-hover:text-kejati-gold">
                            <svg class="h-7 w-7" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                                stroke-width="1.8">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                            </svg>
                        </div>
                        <span
                            class="text-base font-semibold transition duration-200 text-slate-800 group-hover:text-kejati">Baca
                            di tempat</span>
                    </div>

                    <!-- 2. Peminjaman -->
                    <div
                        class="flex flex-col items-center justify-center text-center transition duration-300 bg-white border shadow-sm group rounded-2xl border-stone-200/80 p-7 hover:-translate-y-1 hover:border-kejati-gold/60 hover:shadow-md">
                        <div
                            class="flex items-center justify-center mb-4 transition duration-300 shadow-inner h-14 w-14 rounded-2xl bg-emerald-50 text-kejati group-hover:bg-kejati group-hover:text-kejati-gold">
                            <svg class="h-7 w-7" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                                stroke-width="1.8">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M7.5 21L3 16.5m0 0L7.5 12M3 16.5h13.5m0-13L21 8m0 0l-4.5 4.5M21 8H7.5" />
                            </svg>
                        </div>
                        <span
                            class="text-base font-semibold transition duration-200 text-slate-800 group-hover:text-kejati">Peminjaman</span>
                    </div>

                    <!-- 3. Referensi -->
                    <div
                        class="flex flex-col items-center justify-center text-center transition duration-300 bg-white border shadow-sm group rounded-2xl border-stone-200/80 p-7 hover:-translate-y-1 hover:border-kejati-gold/60 hover:shadow-md">
                        <div
                            class="flex items-center justify-center mb-4 transition duration-300 shadow-inner h-14 w-14 rounded-2xl bg-emerald-50 text-kejati group-hover:bg-kejati group-hover:text-kejati-gold">
                            <svg class="h-7 w-7" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                                stroke-width="1.8">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.501 20.118a7.5 7.5 0 0114.998 0A17.933 17.933 0 0112 21.75c-2.676 0-5.216-.584-7.499-1.632z" />
                            </svg>
                        </div>
                        <span
                            class="text-base font-semibold transition duration-200 text-slate-800 group-hover:text-kejati">Referensi</span>
                    </div>

                    <!-- 4. Repositori digital -->
                    <div
                        class="flex flex-col items-center justify-center text-center transition duration-300 bg-white border shadow-sm group rounded-2xl border-stone-200/80 p-7 hover:-translate-y-1 hover:border-kejati-gold/60 hover:shadow-md">
                        <div
                            class="flex items-center justify-center mb-4 transition duration-300 shadow-inner h-14 w-14 rounded-2xl bg-emerald-50 text-kejati group-hover:bg-kejati group-hover:text-kejati-gold">
                            <svg class="h-7 w-7" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                                stroke-width="1.8">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M20.25 6.375c0 2.278-3.694 4.125-8.25 4.125S3.75 8.653 3.75 6.375m16.5 0c0-2.278-3.694-4.125-8.25-4.125S3.75 4.097 3.75 6.375m16.5 0v11.25c0 2.278-3.694 4.125-8.25 4.125s-8.25-1.847-8.25-4.125V6.375m16.5 5.625c0 2.278-3.694 4.125-8.25 4.125s-8.25-1.847-8.25-4.125" />
                            </svg>
                        </div>
                        <span
                            class="text-base font-semibold transition duration-200 text-slate-800 group-hover:text-kejati">Repositori
                            digital</span>
                    </div>
                </div>
            </div>
        </section>

        <section class="px-5 py-20 mx-auto max-w-7xl lg:px-8 lg:py-28">
            <div class="grid gap-12 lg:grid-cols-[0.7fr_1fr] lg:items-start">
                <div>
                    <p class="text-xs font-bold uppercase tracking-[0.2em] text-kejati-gold-dark">Mengapa perpustakaan?
                    </p>
                    <h2 class="max-w-md mt-4 text-3xl font-semibold leading-tight tracking-tight sm:text-4xl">Referensi
                        yang membantu langkah Anda lebih pasti.</h2>
                </div>
                <div class="grid gap-8 sm:grid-cols-2">
                    <div class="pl-5 border-l-2 border-kejati-gold">
                        <p class="text-3xl font-semibold text-kejati">01</p>
                        <h3 class="mt-3 font-semibold">Koleksi terkurasi</h3>
                        <p class="mt-2 text-sm leading-6 text-slate-500">Referensi hukum, perundangan, dan pengetahuan
                            umum untuk mendukung pekerjaan dan penelitian.</p>
                    </div>
                    <div class="pl-5 border-l-2 border-kejati-gold">
                        <p class="text-3xl font-semibold text-kejati">02</p>
                        <h3 class="mt-3 font-semibold">Akses yang jelas</h3>
                        <p class="mt-2 text-sm leading-6 text-slate-500">Cari judul, penulis, dan ketersediaan buku
                            sebelum Anda datang ke rak.</p>
                    </div>
                    <div class="pl-5 border-l-2 border-kejati-gold">
                        <p class="text-3xl font-semibold text-kejati">03</p>
                        <h3 class="mt-3 font-semibold">Layanan tertib</h3>
                        <p class="mt-2 text-sm leading-6 text-slate-500">Setiap kunjungan dan sirkulasi buku tercatat
                            untuk menjaga layanan tetap akuntabel.</p>
                    </div>
                    <div class="pl-5 border-l-2 border-kejati-gold">
                        <p class="text-3xl font-semibold text-kejati">04</p>
                        <h3 class="mt-3 font-semibold">Datang dan bertanya</h3>
                        <p class="mt-2 text-sm leading-6 text-slate-500">Peminjaman dilakukan langsung melalui petugas
                            di lokasi perpustakaan.</p>
                    </div>
                </div>
            </div>
        </section>

        <!-- Ringkasan Profil Perpustakaan -->
        <section id="tentang" class="py-16 bg-white border-t scroll-mt-16 border-stone-200/80 lg:py-24">
            <div class="px-5 mx-auto max-w-7xl lg:px-8">
                <div class="grid gap-12 lg:grid-cols-12 lg:items-center">

                    <!-- Kolom Kiri: Teks Ringkasan -->
                    <div class="space-y-6 lg:col-span-7">
                        <div
                            class="inline-flex items-center gap-2.5 rounded-full border border-emerald-100 bg-emerald-50 px-3.5 py-1.5 text-xs font-semibold text-kejati">
                            <span class="w-2 h-2 rounded-full bg-kejati-gold"></span>
                            Sekilas Perpustakaan
                        </div>

                        <h2
                            class="text-2xl font-bold leading-tight tracking-tight text-kejati-dark sm:text-3xl lg:text-4xl">
                            Pusat Pengetahuan & Referensi Hukum Kejaksaan Tinggi Jawa Barat
                        </h2>

                        <p class="leading-relaxed text-slate-600 sm:text-base">
                            Perpustakaan Kejaksaan Tinggi Jawa Barat didirikan sebagai fasilitas utama dalam mendukung
                            tugas dan fungsi penegakan hukum melalui penyediaan literatur hukum yang mutakhir, lengkap,
                            dan terpercaya.
                        </p>

                        <p class="leading-relaxed text-slate-600 sm:text-base">
                            Kami melayani Jaksa, ASN Kejaksaan, praktisi hukum, akademisi, serta masyarakat umum yang
                            memerlukan akses informasi perundang-undangan, yurisprudensi, dokumen hukum khusus, hingga
                            koleksi literatur umum.
                        </p>

                        <!-- Highlight Poin Utama -->
                        <div class="grid gap-4 pt-2 sm:grid-cols-2">
                            <div
                                class="flex items-start gap-3 p-4 transition border rounded-xl border-stone-100 bg-stone-50/80 hover:bg-emerald-50/40">
                                <div
                                    class="flex items-center justify-center rounded-lg h-9 w-9 shrink-0 bg-kejati text-kejati-gold">
                                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                                        stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round"
                                            d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                </div>
                                <div>
                                    <h4 class="text-sm font-bold text-slate-900">Koleksi Hukum Terpadu</h4>
                                    <p class="mt-0.5 text-xs text-slate-500">Kitab undang-undang, naskah akademik, dan
                                        karya hukum nasional.</p>
                                </div>
                            </div>

                            <div
                                class="flex items-start gap-3 p-4 transition border rounded-xl border-stone-100 bg-stone-50/80 hover:bg-emerald-50/40">
                                <div
                                    class="flex items-center justify-center rounded-lg h-9 w-9 shrink-0 bg-kejati text-kejati-gold">
                                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                                        stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round"
                                            d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                </div>
                                <div>
                                    <h4 class="text-sm font-bold text-slate-900">Sistem Terintegrasi</h4>
                                    <p class="mt-0.5 text-xs text-slate-500">Pencatatan kunjungan digital dan
                                        katalogisasi teratur.</p>
                                </div>
                            </div>
                        </div>

                        <!-- Tombol Selengkapnya -->
                        <div class="pt-2">
                            <a href="#"
                                class="inline-flex items-center gap-2 px-5 py-3 text-sm font-bold text-white transition duration-200 shadow-md rounded-xl bg-kejati hover:bg-kejati-dark hover:shadow-lg">
                                <span>Selengkapnya</span>
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                                    stroke-width="2.5">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="M13.5 4.5L21 12m0 0l-7.5 7.5M21 12H3" />
                                </svg>
                            </a>
                        </div>
                    </div>

                    <!-- Kolom Kanan: Card Visual Motto Pelayanan -->
                    <div class="lg:col-span-5">
                        <div
                            class="relative p-8 overflow-hidden text-white border shadow-xl rounded-3xl border-kejati-gold/30 bg-gradient-to-br from-kejati-dark to-kejati shadow-kejati-dark/10">
                            <div class="absolute w-40 h-40 rounded-full -right-12 -top-12 bg-kejati-gold/10 blur-2xl">
                            </div>

                            <div class="relative z-10 space-y-6">
                                <div class="flex items-center justify-between">
                                    <span
                                        class="px-3 py-1 text-xs font-semibold tracking-wider uppercase border rounded-lg border-white/10 bg-white/10 text-kejati-gold">
                                        Motto Pelayanan
                                    </span>
                                    <span class="text-2xl text-kejati-gold">✦</span>
                                </div>

                                <blockquote class="text-lg font-semibold leading-relaxed text-white/95 sm:text-xl">
                                    "Mewujudkan pelayanan referensi hukum yang profesional, akuntabel, dan berintegritas
                                    untuk mendukung penegakan hukum berkeadilan."
                                </blockquote>

                                <div class="pt-5 text-xs border-t border-white/15 text-slate-300">
                                    <p class="font-medium text-white">Kejaksaan Tinggi Jawa Barat</p>
                                    <p class="text-slate-400">Jl. L. R. E. Martadinata No. 54, Bandung</p>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        </section>

        <section class="bg-amber-100">
            <div
                class="flex flex-col gap-6 px-5 mx-auto max-w-7xl py-14 sm:flex-row sm:items-center sm:justify-between lg:px-8">
                <div>
                    <p class="text-xs font-bold uppercase tracking-[0.2em] text-kejati-gold-dark">Siap mencari
                        referensi?</p>
                    <h2 class="mt-2 text-2xl font-semibold text-kejati-dark">Mulai dari katalog kami.</h2>
                </div>
                <a href="{{ route('kunjungan') }}"
                    class="inline-flex items-center justify-center gap-2 rounded-xl bg-kejati px-5 py-3.5 text-sm font-bold text-white transition hover:bg-kejati-dark">
                    Isi buku tamu <span>→</span>
                </a>
            </div>
        </section>
    </main>

    <x-footer />
</body>

</html>
