<div class="page-shell">
    <!-- Top Action Bar -->
    <div class="flex flex-col justify-between gap-4 sm:flex-row sm:items-center">
        <div>
            <div class="flex items-center gap-2">
                <span class="page-kicker">
                    <span class="status-dot bg-kejati text-kejati"></span>
                    Ringkasan Sistem
                </span>
            </div>
            <h1 class="page-title">Selamat datang, {{ Str::before(auth()->user()->name, ' ') }}</h1>
            <p class="page-description">Berikut kondisi koleksi dan aktivitas layanan perpustakaan hari ini.</p>
        </div>
        <div class="surface flex items-center gap-2 px-4 py-2.5">
            <svg class="h-4 w-4 text-kejati" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
            </svg>
            <span class="text-xs font-semibold text-slate-700">{{ now()->translatedFormat('l, d F Y') }}</span>
        </div>
    </div>

    <!-- Statistik Cards -->
    <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
        <div class="relative overflow-hidden rounded-2xl bg-gradient-to-br from-kejati to-kejati-dark p-5 text-white shadow-soft">
            <div class="absolute -right-8 -top-10 h-28 w-28 rounded-full border-[18px] border-white/5"></div>
            <div class="flex items-start justify-between"><p class="text-sm font-medium text-white/70">Total Judul Buku</p><span class="rounded-xl bg-white/10 p-2 text-kejati-gold">▤</span></div>
            <p class="mt-4 text-3xl font-extrabold">{{ number_format($totalBooks) }}</p>
            <p class="mt-1 text-xs text-white/55">{{ number_format($totalCopies) }} eksemplar terdata</p>
        </div>
        <div class="surface p-5 transition hover:-translate-y-0.5 hover:shadow-soft">
            <div class="flex items-start justify-between"><p class="text-sm font-medium text-slate-500">Eksemplar Tersedia</p><span class="rounded-xl bg-emerald-50 p-2 text-emerald-700">✓</span></div>
            <p class="mt-4 text-3xl font-extrabold text-emerald-700">{{ number_format($availableCopies) }}</p>
            <div class="mt-2 h-1.5 overflow-hidden rounded-full bg-stone-100"><div class="h-full rounded-full bg-emerald-500" style="width: {{ $totalCopies > 0 ? min(100, round(($availableCopies / $totalCopies) * 100)) : 0 }}%"></div></div>
            <p class="mt-2 text-xs text-slate-400">Dari {{ number_format($totalCopies) }} total eksemplar</p>
        </div>
        <div class="surface p-5 transition hover:-translate-y-0.5 hover:shadow-soft">
            <div class="flex items-start justify-between"><p class="text-sm font-medium text-slate-500">Peminjaman Aktif</p><span class="rounded-xl bg-amber-50 p-2 text-amber-700">↗</span></div>
            <p class="mt-4 text-3xl font-extrabold text-amber-700">{{ number_format($activeLoans) }}</p>
            <p class="mt-2 text-xs {{ $overdueLoans > 0 ? 'font-semibold text-rose-600' : 'text-slate-400' }}">{{ $overdueLoans > 0 ? $overdueLoans.' terlambat dikembalikan' : 'Tidak ada keterlambatan' }}</p>
        </div>
        <div class="surface p-5 transition hover:-translate-y-0.5 hover:shadow-soft">
            <div class="flex items-start justify-between"><p class="text-sm font-medium text-slate-500">Kunjungan Hari Ini</p><span class="rounded-xl bg-sky-50 p-2 text-sky-700">♙</span></div>
            <p class="mt-4 text-3xl font-extrabold text-slate-900">{{ number_format($todayVisitors) }}</p>
            <p class="mt-2 text-xs text-slate-400">Tercatat melalui buku tamu</p>
        </div>
    </div>

    <!-- Aktivitas & Akses Cepat -->
    <div class="grid gap-6 xl:grid-cols-[1fr_320px]">
        <section class="surface overflow-hidden">
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
                    <div class="px-6 py-14 text-center">
                        <div class="mx-auto flex h-12 w-12 items-center justify-center rounded-2xl bg-stone-100 text-xl text-slate-400">▤</div>
                        <p class="mt-3 text-sm font-semibold text-slate-700">Belum ada transaksi</p>
                        <p class="mt-1 text-xs text-slate-400">Peminjaman terbaru akan tampil di sini.</p>
                    </div>
                @endforelse
            </div>
        </section>

        <section class="relative flex flex-col justify-between overflow-hidden rounded-2xl border border-amber-200 bg-gradient-to-br from-amber-50 to-amber-100/70 p-6 shadow-soft">
            <div class="absolute -right-10 -top-10 h-32 w-32 rounded-full border-[20px] border-amber-200/40"></div>
            <div>
                <span class="inline-flex items-center rounded-md bg-amber-200/80 px-2 py-0.5 text-[10px] font-bold uppercase tracking-wider text-amber-900">
                    Akses Cepat
                </span>
                <h2 class="mt-3 text-lg font-bold text-kejati-dark">Jaga koleksi buku tetap tertib.</h2>
                <p class="mt-2 text-xs leading-relaxed text-yellow-900/80">Catat setiap transaksi peminjaman dan pengembalian agar data stok fisik selalu akurat.</p>
            </div>
            <a href="{{ route('admin.loans') }}" class="btn-primary relative mt-6 text-xs" wire:navigate>
                <span>Catat Transaksi</span>
                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3" />
                </svg>
            </a>
        </section>
    </div>
</div>
