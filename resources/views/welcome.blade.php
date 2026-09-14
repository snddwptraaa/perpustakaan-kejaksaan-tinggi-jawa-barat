<!DOCTYPE html>
<html lang="id" class="h-full scroll-smooth">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Perpustakaan Kejati Jawa Barat</title>
    <meta name="description"
        content="Perpustakaan Kejaksaan Tinggi Jawa Barat — ruang referensi hukum dan pengetahuan.">
    <x-favicon />
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
</head>

<body class="landing-page min-h-full bg-kejati-canvas font-sans text-slate-900 antialiased">
    <a href="#main-content" class="skip-link">Lewati ke konten utama</a>
    <x-public-header />

    <main id="main-content" tabindex="-1">
        <section id="hero" class="landing-hero">
            <div class="landing-hero-grid">
                <div class="landing-hero-copy">
                    <p
                        class="landing-eyebrow">
                        <span class="h-px w-8 bg-kejati-gold-dark" aria-hidden="true"></span> Ruang pengetahuan Kejati Jabar
                    </p>
                    <h1 class="landing-hero-title">
                        Baca untuk <span>memahami.</span>
                    </h1>
                    <p class="mt-7 max-w-xl text-base leading-7 text-slate-600 sm:text-lg sm:leading-8">
                        Akses katalog referensi hukum dan koleksi pengetahuan Perpustakaan Kejaksaan Tinggi Jawa
                        Barat—teratur, terbuka, dan dekat dengan kebutuhan Anda.
                    </p>
                    <div class="mt-10 flex flex-col gap-3 sm:flex-row">
                        <a href="{{ route('kunjungan') }}"
                            class="landing-primary-link">
                            Isi buku tamu
                            <svg aria-hidden="true" class="h-4 w-4" fill="none" viewBox="0 0 24 24"
                                stroke="currentColor" stroke-width="2.25">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M5 12h14m-6-6 6 6-6 6" />
                            </svg>
                        </a>
                        <a href="#tentang"
                            class="landing-secondary-link">
                            Tentang perpustakaan
                        </a>
                    </div>
                    <p class="mt-4 text-xs leading-5 text-slate-600">Isi buku tamu terlebih dahulu untuk melanjutkan ke katalog.</p>
                </div>
                <figure class="landing-photo">
                    <img src="{{ asset('images/library-hero.webp') }}" alt="" width="1672" height="941"
                        fetchpriority="high" decoding="async">
                    <figcaption>
                        <span class="landing-photo-label">Perpustakaan Kejati Jawa Barat</span>
                        <span class="font-display text-2xl sm:text-3xl">Ruang untuk membaca.<br>Bekal untuk berkarya.</span>
                    </figcaption>
                </figure>
            </div>
            <div class="landing-visit-strip">
                <p><span>Referensi hukum</span>Perundangan, yurisprudensi, dan literatur umum.</p>
                <p><span>Layanan perpustakaan</span>Baca di tempat dan peminjaman bersama petugas.</p>
                <a href="#kontak">Rencanakan kunjungan <span aria-hidden="true">↗</span></a>
            </div>
        </section>

        <section id="layanan" class="landing-services scroll-mt-24 bg-white py-16 sm:py-20 lg:py-24">
            <div class="mx-auto max-w-7xl px-5 lg:px-8">
                <div class="grid gap-8 border-b border-kejati/15 pb-9 lg:grid-cols-[0.85fr_1.15fr] lg:items-end">
                    <div>
                        <p class="text-xs font-bold uppercase tracking-[0.2em] text-kejati-gold-dark">Fasilitas & akses</p>
                        <h2 class="mt-3 font-display text-4xl leading-tight tracking-tight text-kejati-dark sm:text-5xl">Layanan</h2>
                    </div>
                    <p class="max-w-2xl text-base leading-7 text-slate-600 lg:justify-self-end lg:pb-1">
                        Gunakan fasilitas perpustakaan untuk membaca, meminjam, menemukan referensi, dan menelusuri
                        data koleksi sesuai kebutuhan Anda.
                    </p>
                </div>

                <div class="mt-8 grid gap-6">
                    <article class="landing-service-panel">
                        <div class="flex flex-wrap items-center justify-between gap-3 border-b border-stone-200 px-6 py-5 sm:px-8">
                            <div>
                                <p class="text-[11px] font-bold uppercase tracking-[0.16em] text-kejati">Di perpustakaan</p>
                                <h3 class="mt-1 text-xl font-bold text-slate-950">Layanan saat Anda berkunjung</h3>
                            </div>
                            <span class="rounded-full bg-emerald-50 px-3 py-1.5 text-xs font-semibold text-kejati">
                                Bersama petugas
                            </span>
                        </div>

                        <ul class="landing-service-list" aria-label="Layanan di perpustakaan">
                            <li class="grid gap-4 px-6 py-6 sm:px-8">
                                <span class="flex h-12 w-12 items-center justify-center rounded-xl bg-kejati text-kejati-gold">
                                    <svg aria-hidden="true" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                                    </svg>
                                </span>
                                <div>
                                    <h4 class="font-bold text-slate-900">Baca di tempat</h4>
                                    <p class="mt-1 text-sm leading-6 text-slate-600">Gunakan koleksi dan ruang baca selama jam layanan perpustakaan.</p>
                                </div>
                            </li>
                            <li class="grid gap-4 px-6 py-6 sm:px-8">
                                <span class="flex h-12 w-12 items-center justify-center rounded-xl bg-kejati text-kejati-gold">
                                    <svg aria-hidden="true" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M7.5 21L3 16.5m0 0L7.5 12M3 16.5h13.5m0-13L21 8m0 0l-4.5 4.5M21 8H7.5" />
                                    </svg>
                                </span>
                                <div>
                                    <h4 class="font-bold text-slate-900">Peminjaman</h4>
                                    <p class="mt-1 text-sm leading-6 text-slate-600">Pilih buku melalui katalog, lalu proses peminjaman fisik bersama petugas.</p>
                                </div>
                            </li>
                            <li class="grid gap-4 px-6 py-6 sm:px-8">
                                <span class="flex h-12 w-12 items-center justify-center rounded-xl bg-kejati text-kejati-gold">
                                    <svg aria-hidden="true" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.501 20.118a7.5 7.5 0 0114.998 0A17.933 17.933 0 0112 21.75c-2.676 0-5.216-.584-7.499-1.632z" />
                                    </svg>
                                </span>
                                <div>
                                    <h4 class="font-bold text-slate-900">Referensi</h4>
                                    <p class="mt-1 text-sm leading-6 text-slate-600">Temukan literatur hukum dan pengetahuan pendukung untuk pekerjaan atau penelitian.</p>
                                </div>
                            </li>
                        </ul>
                    </article>

                    <article class="landing-digital-panel">
                        <div>
                        <div class="flex items-center justify-between gap-4">
                            <span class="rounded-full border border-white/15 bg-white/[0.07] px-3 py-1.5 text-[11px] font-bold uppercase tracking-[0.16em] text-kejati-gold">
                                Daring
                            </span>
                            <svg aria-hidden="true" class="h-7 w-7 text-kejati-gold" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.7">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 6.75h15m-15 5.25h15m-15 5.25h9" />
                            </svg>
                        </div>
                        <h3 class="mt-5 font-display text-3xl leading-tight text-white">Repositori digital</h3>
                        <p class="mt-4 text-sm leading-7 text-emerald-100">
                            Akses data koleksi secara daring untuk melihat judul, penulis, ketersediaan, dan lokasi rak.
                        </p>

                        </div>
                        <div class="landing-digital-access">
                        <p class="border-l-2 border-kejati-gold pl-4 text-sm leading-6 text-emerald-100">
                            Isi buku tamu terlebih dahulu untuk melanjutkan ke katalog.
                        </p>

                        <a href="{{ route('kunjungan') }}"
                            class="mt-8 inline-flex min-h-11 items-center gap-2 rounded-xl bg-kejati-gold px-5 py-3 text-sm font-bold text-kejati-dark transition hover:bg-yellow-200">
                            Isi buku tamu
                            <svg aria-hidden="true" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.25">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M5 12h14m-6-6 6 6-6 6" />
                            </svg>
                        </a>
                        </div>
                    </article>
                </div>
            </div>
        </section>

        <section id="tentang" class="landing-about scroll-mt-24 border-t border-stone-200 py-16 sm:py-20 lg:py-28">
            <div class="mx-auto grid max-w-7xl gap-12 px-5 lg:grid-cols-[0.92fr_1.08fr] lg:gap-20 lg:px-8">
                <div class="lg:sticky lg:top-28 lg:self-start">
                    <p class="text-xs font-bold uppercase tracking-[0.2em] text-kejati-gold-dark">Sekilas perpustakaan</p>
                    <h2 class="mt-4 font-display text-4xl leading-tight tracking-tight text-kejati-dark sm:text-5xl">
                        Pusat pengetahuan dan referensi hukum Kejaksaan Tinggi Jawa Barat.
                    </h2>
                    <div class="mt-7 space-y-5 text-base leading-7 text-slate-600">
                        <p>
                            Perpustakaan Kejaksaan Tinggi Jawa Barat didirikan sebagai fasilitas utama dalam mendukung
                            tugas dan fungsi penegakan hukum melalui penyediaan literatur hukum yang mutakhir, lengkap,
                            dan terpercaya.
                        </p>
                        <p>
                            Kami melayani Jaksa, ASN Kejaksaan, praktisi hukum, akademisi, serta masyarakat umum yang
                            memerlukan akses informasi perundang-undangan, yurisprudensi, dokumen hukum khusus, hingga
                            koleksi literatur umum.
                        </p>
                    </div>
                    <a href="#kontak"
                        class="mt-8 inline-flex min-h-11 items-center gap-2 rounded-xl border border-kejati/25 bg-white px-5 py-3 text-sm font-bold text-kejati transition hover:border-kejati hover:bg-emerald-50">
                        Selengkapnya
                        <svg aria-hidden="true" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.25">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M5 12h14m-6-6 6 6-6 6" />
                        </svg>
                    </a>
                </div>

                <div class="landing-reference-sheet">
                    <div class="grid grid-cols-[auto_1fr] border-b border-stone-200 bg-kejati-dark text-white">
                        <div class="flex w-16 items-center justify-center border-r border-white/10 bg-kejati-gold text-kejati-dark sm:w-20">
                            <svg aria-hidden="true" class="h-7 w-7" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 19.5h15m-13.5-3v-9l6-3 6 3v9m-9-6h.008v.008H9V10.5zm0 3h.008v.008H9V13.5zm3-3h.008v.008H12V10.5zm0 3h.008v.008H12V13.5zm3-3h.008v.008H15V10.5zm0 3h.008v.008H15V13.5z" />
                            </svg>
                        </div>
                        <div class="px-6 py-6 sm:px-8">
                            <p class="text-[11px] font-bold uppercase tracking-[0.16em] text-kejati-gold">Mengapa perpustakaan?</p>
                            <h3 class="mt-1 text-xl font-bold">Referensi yang membantu langkah Anda lebih pasti.</h3>
                        </div>
                    </div>

                    <dl class="divide-y divide-stone-200">
                        <div class="grid gap-3 px-6 py-6 sm:grid-cols-[9rem_1fr] sm:px-8">
                            <dt class="text-sm font-bold text-kejati">Koleksi terkurasi</dt>
                            <dd>
                                <p class="text-sm leading-6 text-slate-600">Referensi hukum, perundangan, dan pengetahuan umum untuk mendukung pekerjaan dan penelitian.</p>
                            </dd>
                        </div>
                        <div class="grid gap-3 px-6 py-6 sm:grid-cols-[9rem_1fr] sm:px-8">
                            <dt class="text-sm font-bold text-kejati">Akses yang jelas</dt>
                            <dd>
                                <p class="text-sm leading-6 text-slate-600">Cari judul, penulis, dan ketersediaan buku sebelum Anda datang ke rak.</p>
                            </dd>
                        </div>
                        <div class="grid gap-3 px-6 py-6 sm:grid-cols-[9rem_1fr] sm:px-8">
                            <dt class="text-sm font-bold text-kejati">Layanan tertib</dt>
                            <dd>
                                <p class="text-sm leading-6 text-slate-600">Setiap kunjungan dan sirkulasi buku tercatat untuk menjaga layanan tetap akuntabel.</p>
                            </dd>
                        </div>
                    </dl>

                    <div class="m-4 flex gap-4 rounded-xl border border-amber-200 bg-amber-50 px-5 py-4 sm:m-6">
                        <svg aria-hidden="true" class="mt-0.5 h-5 w-5 shrink-0 text-kejati" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M11.25 11.25l.041-.02a.75.75 0 011.063.852l-.708 2.836a.75.75 0 001.063.853l.041-.021M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-9-3.75h.008v.008H12V8.25z" />
                        </svg>
                        <p class="text-sm leading-6 text-slate-700">
                            <strong class="font-bold text-slate-900">Datang dan bertanya.</strong> Peminjaman dilakukan langsung melalui petugas di lokasi perpustakaan.
                        </p>
                    </div>
                </div>
            </div>
        </section>

        <section class="landing-closing">
            <div
                class="flex flex-col gap-6 px-5 mx-auto max-w-7xl py-14 sm:flex-row sm:items-center sm:justify-between lg:px-8">
                <div>
                    <p class="text-xs font-bold uppercase tracking-[0.2em] text-kejati-gold-dark">Siap mencari
                        referensi?</p>
                    <h2 class="mt-3 font-display text-3xl text-kejati-dark sm:text-4xl">Mulai dengan mengisi buku tamu.</h2>
                    <p class="mt-3 text-sm leading-6 text-slate-600">Setelah mengisi buku tamu, Anda dapat menelusuri katalog koleksi perpustakaan.</p>
                </div>
                <a href="{{ route('kunjungan') }}"
                    class="landing-primary-link shrink-0">
                    Isi buku tamu <span>→</span>
                </a>
            </div>
        </section>
    </main>

    <x-footer />
    @livewireScripts
</body>

</html>
