<div class="space-y-6">
    <!-- Top Action Bar -->
    <div class="flex flex-col justify-between gap-4 sm:flex-row sm:items-center">
        <div>
            <div class="flex items-center gap-2">
                <span class="inline-flex items-center rounded-md bg-kejati/10 px-2.5 py-0.5 text-xs font-bold uppercase tracking-wider text-kejati">
                    Ringkasan Sistem
                </span>
            </div>
            <h1 class="mt-2 text-2xl font-bold tracking-tight text-slate-900 sm:text-3xl">Ringkasan Perpustakaan</h1>
            <p class="mt-1 text-sm text-slate-500">Pantau statistik koleksi buku dan aktivitas sirkulasi layanan hari ini.</p>
        </div>
        <div class="flex items-center gap-2 rounded-xl border border-stone-200 bg-white px-4 py-2.5 shadow-xs">
            <svg class="h-4 w-4 text-kejati" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
            </svg>
            <span class="text-xs font-semibold text-slate-700">{{ now()->translatedFormat('l, d F Y') }}</span>
        </div>
    </div>

    <!-- Statistik Cards -->
    <div class="grid gap-5 sm:grid-cols-2 xl:grid-cols-4">
        <div class="rounded-2xl bg-kejati p-6 text-white shadow-lg shadow-kejati/10">
            <p class="text-sm text-white/70">Total Judul Buku</p>
            <p class="mt-3 text-4xl font-semibold">{{ number_format($totalBooks) }}</p>
            <p class="mt-2 text-xs text-kejati-gold">Dalam katalog</p>
        </div>
        <div class="rounded-2xl border border-stone-200 bg-white p-6 shadow-sm">
            <p class="text-sm text-slate-500">Eksemplar Tersedia</p>
            <p class="mt-3 text-4xl font-semibold text-emerald-700">{{ number_format($availableCopies) }}</p>
            <p class="mt-2 text-xs text-slate-400">Siap dipinjam</p>
        </div>
        <div class="rounded-2xl border border-stone-200 bg-white p-6 shadow-sm">
            <p class="text-sm text-slate-500">Peminjaman Aktif</p>
            <p class="mt-3 text-4xl font-semibold text-kejati-gold-dark">{{ number_format($activeLoans) }}</p>
            <p class="mt-2 text-xs text-slate-400">Perlu dipantau</p>
        </div>
        <div class="rounded-2xl border border-stone-200 bg-white p-6 shadow-sm">
            <p class="text-sm text-slate-500">Kunjungan Hari Ini</p>
            <p class="mt-3 text-4xl font-semibold text-slate-900">{{ number_format($todayVisitors) }}</p>
            <p class="mt-2 text-xs text-slate-400">Buku tamu digital</p>
        </div>
    </div>

    <!-- Aktivitas & Akses Cepat -->
    <div class="grid gap-6 xl:grid-cols-[1fr_320px]">
        <section class="rounded-2xl border border-stone-200 bg-white shadow-sm overflow-hidden">
            <div class="flex items-center justify-between border-b border-stone-100 px-6 py-5">
                <div>
                    <h2 class="font-bold text-slate-900">Aktivitas Peminjaman Terbaru</h2>
                    <p class="mt-0.5 text-xs text-slate-500">Transaksi peminjaman buku yang baru dicatat petugas.</p>
                </div>
                <a href="{{ route('admin.loans') }}" class="text-xs font-bold text-kejati hover:underline" wire:navigate>Lihat semua</a>
            </div>
            <div class="divide-y divide-stone-100">
                @forelse ($recentLoans as $loan)
                    <div class="flex items-center justify-between gap-4 px-6 py-4 hover:bg-stone-50/50 transition">
                        <div class="min-w-0">
                            <p class="truncate text-sm font-semibold text-slate-900">{{ $loan->nama_peminjam }}</p>
                            <p class="mt-0.5 truncate text-xs text-slate-500">{{ $loan->book->judul }}</p>
                        </div>
                        <span class="shrink-0 rounded-full px-2.5 py-1 text-[10px] font-bold uppercase {{ $loan->current_status === 'dikembalikan' ? 'bg-emerald-100 text-emerald-800' : ($loan->current_status === 'terlambat' ? 'bg-rose-100 text-rose-800' : 'bg-blue-100 text-blue-800') }}">
                            {{ ucfirst($loan->current_status) }}
                        </span>
                    </div>
                @empty
                    <div class="px-6 py-12 text-center text-sm text-slate-500">Belum ada transaksi peminjaman.</div>
                @endforelse
            </div>
        </section>

        <section class="rounded-2xl border border-amber-200 bg-gradient-to-br from-amber-50 to-amber-100/70 p-6 flex flex-col justify-between shadow-sm">
            <div>
                <span class="inline-flex items-center rounded-md bg-amber-200/80 px-2 py-0.5 text-[10px] font-bold uppercase tracking-wider text-amber-900">
                    Akses Cepat
                </span>
                <h2 class="mt-3 text-lg font-bold text-kejati-dark">Jaga koleksi buku tetap tertib.</h2>
                <p class="mt-2 text-xs leading-relaxed text-yellow-900/80">Catat setiap transaksi peminjaman dan pengembalian agar data stok fisik selalu akurat.</p>
            </div>
            <a href="{{ route('admin.loans') }}" class="mt-6 inline-flex items-center justify-center gap-2 rounded-xl bg-kejati px-4 py-2.5 text-xs font-bold text-white shadow-sm transition hover:bg-kejati-dark" wire:navigate>
                <span>Catat Transaksi</span>
                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3" />
                </svg>
            </a>
        </section>
    </div>
</div>
