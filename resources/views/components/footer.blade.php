<footer class="relative bg-gradient-to-b from-[#064E3B] to-[#04382B] text-white scroll-mt-16" id="kontak">
    <!-- Accent Line on Top -->
    <div class="h-1 w-full bg-gradient-to-r from-kejati-gold/60 via-kejati-gold to-kejati-gold/60"></div>

    <div class="mx-auto max-w-7xl px-6 py-12 lg:px-8 lg:py-14">
        <div class="grid grid-cols-1 gap-10 md:grid-cols-2 lg:grid-cols-4 lg:gap-8">
            <!-- Kolom 1: Brand & Alamat -->
            <div class="space-y-4 lg:border-r lg:border-white/10 lg:pr-8">
                <!-- Logo & Badge Container -->
                <div class="inline-flex items-center gap-3 rounded-full bg-white px-4 py-2 text-kejati shadow-sm">
                    <img src="{{ asset('images/logo.svg') }}" alt="Logo Kejaksaan Tinggi Jawa Barat"
                        class="h-9 w-auto shrink-0 object-contain">
                    <div class="text-left">
                        <span class="block text-[9px] font-bold uppercase tracking-widest text-slate-500">Perpustakaan
                            Digital</span>
                        <span class="block text-xs font-bold text-kejati-dark">Kejati Jawa Barat</span>
                    </div>
                </div>

                <div class="space-y-1.5 text-xs text-slate-300">
                    <h3 class="font-semibold text-white">Perpustakaan Kejati Jawa Barat</h3>
                    <p class="leading-relaxed text-slate-300/90">
                        Jl. L. R. E. Martadinata No. 54, Citarum, Kec. Bandung Wetan, Kota Bandung, Jawa Barat 40115
                    </p>
                    <p class="pt-1 text-slate-300">
                        <a href="mailto:perpustakaan@kejati-jabar.go.id"
                            class="transition hover:text-kejati-gold">perpustakaan@kejati-jabar.go.id</a>
                    </p>
                    <p class="text-slate-300">
                        (022) 4230758
                    </p>
                </div>
            </div>

            <!-- Kolom 2: Jam Layanan -->
            <div class="space-y-4 lg:border-r lg:border-white/10 lg:pr-8">
                <h4 class="text-xs font-bold uppercase tracking-wider text-kejati-gold">JAM LAYANAN</h4>

                <div class="space-y-4 text-xs">
                    <div>
                        <h5 class="font-bold uppercase tracking-wider text-amber-300/90">JAM OPERASIONAL</h5>
                        <p class="mt-1 text-slate-300">Senin-Jum'at: 08.00–16.00 WIB</p>
                        <p class="text-slate-400">Istirahat: 11.30–13.30 WIB</p>
                    </div>

                    <div>
                        <h5 class="font-bold uppercase tracking-wider text-amber-300/90">SIRKULASI DAN PEMINJAMAN</h5>
                        <p class="mt-1 text-slate-300">Senin–Jumat: 08.30–15.30 WIB</p>
                    </div>
                </div>
            </div>

            <!-- Kolom 3: Kontak -->
            <div class="space-y-4 lg:border-r lg:border-white/10 lg:pr-8">
                <h4 class="text-xs font-bold uppercase tracking-wider text-kejati-gold">KONTAK</h4>

                <div class="space-y-3 text-xs">
                    <a href="tel:0224230758"
                        class="group flex items-center gap-2.5 text-slate-300 transition hover:text-kejati-gold">
                        <span
                            class="flex h-7 w-7 items-center justify-center rounded-full bg-white/10 text-kejati-gold transition group-hover:bg-kejati-gold group-hover:text-kejati-dark">
                            <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                                stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z" />
                            </svg>
                        </span>
                        <span>Hallo-Kejati (022) 4230758</span>
                    </a>

                    <a href="https://wa.me/62811223400" target="_blank"
                        class="group flex items-center gap-2.5 text-slate-300 transition hover:text-kejati-gold">
                        <span
                            class="flex h-7 w-7 items-center justify-center rounded-full bg-white/10 text-kejati-gold transition group-hover:bg-kejati-gold group-hover:text-kejati-dark">
                            <svg class="h-3.5 w-3.5" fill="currentColor" viewBox="0 0 24 24">
                                <path
                                    d="M.057 24l1.687-6.163c-1.041-1.804-1.588-3.849-1.587-5.946.003-6.556 5.338-11.891 11.893-11.891 3.181.001 6.167 1.24 8.413 3.488 2.245 2.248 3.481 5.236 3.48 8.414-.003 6.557-5.338 11.892-11.893 11.892-1.99-.001-3.951-.5-5.688-1.448l-6.305 1.654zm6.597-3.807c1.676.995 3.276 1.591 5.392 1.592 5.448 0 9.886-4.434 9.889-9.885.002-5.462-4.415-9.89-9.881-9.892-5.452 0-9.887 4.434-9.889 9.884-.001 2.225.651 3.891 1.746 5.634l-.999 3.648 3.742-.981z" />
                            </svg>
                        </span>
                        <span>Chat WhatsApp Perpustakaan</span>
                    </a>

                    <!-- Social Icons -->
                    <div class="pt-2">
                        <div class="flex items-center gap-2">
                            <a href="https://www.instagram.com/kejati_jabar/?hl=en" target="_blank"
                                aria-label="Instagram"
                                class="flex h-8 w-8 items-center justify-center rounded-full bg-white/10 text-slate-300 transition hover:bg-kejati-gold hover:text-kejati-dark">
                                <svg class="h-4 w-4" fill="currentColor" viewBox="0 0 24 24">
                                    <path
                                        d="M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.849 0 3.205-.012 3.584-.069 4.849-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07-3.204 0-3.584-.012-4.849-.07-3.26-.149-4.771-1.699-4.919-4.92-.058-1.265-.07-1.644-.07-4.849 0-3.204.013-3.583.07-4.849.149-3.227 1.664-4.771 4.919-4.919 1.266-.057 1.645-.069 4.849-.069zm0-2.163c-3.259 0-3.667.014-4.947.072-4.358.2-6.78 2.618-6.98 6.98-.059 1.281-.073 1.689-.073 4.948 0 3.259.014 3.668.072 4.948.2 4.358 2.618 6.78 6.98 6.98 1.281.058 1.689.072 4.948.072 3.259 0 3.668-.014 4.948-.072 4.354-.2 6.782-2.618 6.979-6.98.059-1.28.073-1.689.073-4.948 0-3.259-.014-3.667-.072-4.947-.196-4.354-2.617-6.78-6.979-6.98-1.281-.059-1.69-.073-4.949-.073zm0 5.838c-3.403 0-6.162 2.759-6.162 6.162s2.759 6.163 6.162 6.163 6.162-2.759 6.162-6.163c0-3.403-2.759-6.162-6.162-6.162zm0 10.162c-2.209 0-4-1.79-4-4 0-2.209 1.791-4 4-4s4 1.791 4 4c0 2.21-1.791 4-4 4zm6.406-11.845c-.796 0-1.441.645-1.441 1.44s.645 1.44 1.441 1.44c.795 0 1.439-.645 1.439-1.44s-.644-1.44-1.439-1.44z" />
                                </svg>
                            </a>
                            <a href="https://x.com/kejati_jabar" target="_blank" aria-label="X / Twitter"
                                class="flex h-8 w-8 items-center justify-center rounded-full bg-white/10 text-slate-300 transition hover:bg-kejati-gold hover:text-kejati-dark">
                                <svg class="h-3.5 w-3.5" fill="currentColor" viewBox="0 0 24 24">
                                    <path
                                        d="M18.244 2.25h3.308l-7.227 8.26 8.502 11.24H16.17l-5.214-6.817L4.99 21.75H1.68l7.73-8.835L1.254 2.25H8.08l4.713 6.231zm-1.161 17.52h1.833L7.084 4.126H5.117z" />
                                </svg>
                            </a>
                            <a href="https://www.facebook.com/KejaksaanTinggiJawaBarat" target="_blank"
                                aria-label="Facebook"
                                class="flex h-8 w-8 items-center justify-center rounded-full bg-white/10 text-slate-300 transition hover:bg-kejati-gold hover:text-kejati-dark">
                                <svg class="h-4 w-4" fill="currentColor" viewBox="0 0 24 24">
                                    <path
                                        d="M9 8H6v4h3v12h5V12h3.642L18 8h-4V6.333C14 5.374 14.5 5 15.5 5H18V0h-3.808C10.592 0 9 1.583 9 4.615V8z" />
                                </svg>
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Kolom 4: Lokasi Map Card -->
            <div class="space-y-4">
                <h4 class="text-xs font-bold uppercase tracking-wider text-kejati-gold">LOKASI</h4>

                <div
                    class="group relative overflow-hidden rounded-xl border border-white/20 bg-black/20 p-1 shadow-lg transition hover:border-kejati-gold/50">
                    <!-- Map Thumbnail Graphic -->
                    <div class="relative h-28 w-full overflow-hidden rounded-lg bg-emerald-950/60">
                        <iframe title="Lokasi Kejaksaan Tinggi Jawa Barat"
                            src="https://maps.google.com/maps?q=-6.9061837,107.6194662&t=&z=16&ie=UTF8&iwloc=&output=embed"
                            class="h-full w-full border-0 opacity-75 grayscale contrast-125 transition duration-300 group-hover:opacity-100 group-hover:grayscale-0"
                            loading="lazy">
                        </iframe>
                        <div
                            class="pointer-events-none absolute inset-0 bg-gradient-to-t from-slate-950/80 via-transparent to-transparent">
                        </div>

                        <!-- Map Badge -->
                        <a href="https://maps.app.goo.gl/ahV3PSf3aN3WKvhV7" target="_blank" rel="noopener noreferrer"
                            class="absolute left-2 top-2 rounded bg-slate-950/90 px-2 py-1 text-[10px] font-semibold text-white shadow backdrop-blur transition hover:bg-kejati-gold hover:text-kejati-dark">
                            Maps ↗
                        </a>
                    </div>

                    <!-- Action Link -->
                    <div class="p-2 text-center">
                        <a href="https://maps.app.goo.gl/ahV3PSf3aN3WKvhV7" target="_blank" rel="noopener noreferrer"
                            class="inline-flex items-center gap-1.5 text-xs font-bold text-kejati-gold transition hover:text-yellow-200">
                            Buka di Maps
                            <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                                stroke-width="2.5">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14" />
                            </svg>
                        </a>
                    </div>
                </div>
            </div>

        </div>

        <!-- Sub-footer Bottom Divider & Copyright -->
        <div
            class="mt-12 border-t border-white/10 pt-6 text-center text-xs text-slate-400 sm:flex sm:items-center sm:justify-between sm:text-left">
            <p>© {{ date('Y') }} Perpustakaan Kejaksaan Tinggi Jawa Barat. Hak Cipta Dilindungi.</p>
            <p class="mt-2 font-medium text-slate-400 sm:mt-0">
                Kejaksaan Tinggi Jawa Barat • Berintegritas & Transparan
            </p>
        </div>
    </div>
</footer>