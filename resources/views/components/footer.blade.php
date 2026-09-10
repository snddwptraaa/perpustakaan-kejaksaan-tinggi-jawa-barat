@props(['compact' => false])

@if ($compact)
    <footer class="border-t border-stone-200 bg-white" id="kontak">
        <div class="mx-auto flex max-w-7xl flex-col gap-4 px-5 py-6 text-sm text-slate-600 sm:flex-row sm:items-center sm:justify-between lg:px-8">
            <div>
                <p class="font-semibold text-kejati-dark">Perpustakaan Kejati Jawa Barat</p>
                <p class="mt-1 text-xs">Senin–Jumat, 08.00–16.00 WIB</p>
            </div>
            @if (session('visitor_checked_in'))
                <div class="flex items-center gap-3">
                    <form action="{{ route('kunjungan.selesai') }}" method="POST" class="inline-block">
                        @csrf
                        <button type="submit"
                            title="Selesaikan sesi kunjungan dan reset formulir untuk antrean berikutnya"
                            class="inline-flex items-center gap-2 rounded-xl border border-red-200 bg-red-50 px-4 py-2 text-xs font-bold text-red-700 shadow-sm transition hover:bg-red-100 hover:text-red-800 focus:outline-none focus:ring-2 focus:ring-red-500">
                            <svg class="h-4 w-4 text-red-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 9V5.25A2.25 2.25 0 0013.5 3h-6a2.25 2.25 0 00-2.25 2.25v13.5A2.25 2.25 0 007.5 21h6a2.25 2.25 0 002.25-2.25V15M12 9l-3 3m0 0l3 3m-3-3h12.75" />
                            </svg>
                            <span>Selesai & Reset Kiosk (Pengunjung Baru)</span>
                        </button>
                    </form>
                </div>
            @endif
            <div class="flex flex-wrap gap-x-5 gap-y-2 text-xs font-semibold">
                <a href="tel:0224230758" class="hover:text-kejati">(022) 4230758</a>
                <a href="https://maps.app.goo.gl/ahV3PSf3aN3WKvhV7" target="_blank" rel="noopener noreferrer" class="inline-flex items-center gap-1 hover:text-kejati">
                    Petunjuk lokasi
                    <svg aria-hidden="true" class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5H19.5V10.5M19 5L10 14M19.5 13.5V18A1.5 1.5 0 0118 19.5H6A1.5 1.5 0 014.5 18V6A1.5 1.5 0 016 4.5H10.5" />
                    </svg>
                </a>
                <a href="{{ route('login') }}" class="hover:text-kejati" wire:navigate>Masuk petugas</a>
            </div>
        </div>
    </footer>
@else
    <footer class="border-t-4 border-kejati-gold bg-kejati-dark text-white" id="kontak">
        <div class="mx-auto max-w-7xl px-5 py-10 lg:px-8">
            <div class="grid gap-8 md:grid-cols-2 lg:grid-cols-12 lg:gap-10">
                <section aria-label="Identitas perpustakaan" class="md:col-span-2 lg:col-span-3">
                    <div class="flex items-center gap-3">
                        <img src="{{ asset('images/logo.svg') }}" alt="" width="32" height="36" class="h-9 w-8 object-contain">
                        <p class="font-semibold">Perpustakaan Kejati Jawa Barat</p>
                    </div>
                    <address class="mt-4 text-sm not-italic leading-6 text-white/70">Jl. L. R. E. Martadinata No. 54, Bandung 40115</address>
                </section>
                <section aria-labelledby="footer-hours" class="border-t border-white/10 pt-6 md:border-0 md:pt-0 lg:col-span-2">
                    <h2 id="footer-hours" class="text-xs font-bold uppercase tracking-wider text-kejati-gold">Jam layanan</h2>
                    <p class="mt-4 text-sm text-white/80">Senin–Jumat, 08.00–16.00 WIB</p>
                    <p class="mt-1 text-sm text-white/60">Sirkulasi: 08.30–15.30 WIB</p>
                </section>
                <section aria-labelledby="footer-contact" class="border-t border-white/10 pt-6 md:border-0 md:pt-0 lg:col-span-3">
                    <h2 id="footer-contact" class="text-xs font-bold uppercase tracking-wider text-kejati-gold">Kontak</h2>
                    <div class="mt-4 grid gap-2 text-sm text-white/80">
                        <a href="tel:0224230758" class="hover:text-kejati-gold">Hallo-Kejati (022) 4230758</a>
                        <a href="mailto:perpustakaan@kejati-jabar.go.id" class="break-all hover:text-kejati-gold">perpustakaan@kejati-jabar.go.id</a>
                        <a href="https://wa.me/62811223400" target="_blank" rel="noopener noreferrer" class="hover:text-kejati-gold">WhatsApp perpustakaan</a>
                        <a href="{{ route('login') }}" class="mt-2 inline-flex min-h-11 items-center gap-2 self-start font-semibold text-kejati-gold hover:text-yellow-200" wire:navigate>
                            Masuk petugas
                            <span aria-hidden="true">→</span>
                        </a>
                    </div>
                </section>
                <section aria-labelledby="footer-location" class="border-t border-white/10 pt-6 md:col-span-2 lg:col-span-4 lg:border-0 lg:pt-0">
                    <h2 id="footer-location" class="text-xs font-bold uppercase tracking-wider text-kejati-gold">Lokasi</h2>
                    <div class="mt-4 overflow-hidden rounded-2xl border border-white/15 bg-white/5 shadow-sm">
                        <iframe
                            title="Peta lokasi Perpustakaan Kejaksaan Tinggi Jawa Barat"
                            src="https://www.google.com/maps?q=Kejaksaan%20Tinggi%20Jawa%20Barat%2C%20Jl.%20L.%20R.%20E.%20Martadinata%20No.%2054%2C%20Bandung&output=embed"
                            class="h-48 w-full border-0"
                            loading="lazy"
                            referrerpolicy="no-referrer-when-downgrade"
                            allowfullscreen></iframe>
                    </div>
                    <a href="https://maps.app.goo.gl/ahV3PSf3aN3WKvhV7" target="_blank" rel="noopener noreferrer" class="mt-3 inline-flex items-center gap-1.5 text-sm font-semibold text-kejati-gold hover:text-yellow-200">
                        Buka peta lebih besar
                        <svg aria-hidden="true" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5H19.5V10.5M19 5L10 14M19.5 13.5V18A1.5 1.5 0 0118 19.5H6A1.5 1.5 0 014.5 18V6A1.5 1.5 0 016 4.5H10.5" />
                        </svg>
                    </a>
                </section>
            </div>
            <p class="mt-8 border-t border-white/10 pt-5 text-xs text-white/70">© {{ date('Y') }} Perpustakaan Kejaksaan Tinggi Jawa Barat</p>
        </div>
    </footer>
@endif
