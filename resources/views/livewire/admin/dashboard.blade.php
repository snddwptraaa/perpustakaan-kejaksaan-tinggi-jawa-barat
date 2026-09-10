<div class="page-shell">
    <div>
        <h1 class="page-title mt-0">Ringkasan</h1>
        <p class="page-description">Kondisi koleksi dan aktivitas layanan hari ini.</p>
    </div>

    <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
        <div class="surface p-5">
            <p class="text-sm font-medium text-slate-500">Judul buku</p>
            <p class="mt-3 text-3xl font-bold tabular-nums text-slate-950">{{ number_format($totalBooks) }}</p>
            <p class="mt-2 text-xs text-slate-500">{{ number_format($totalCopies) }} eksemplar terdata</p>
        </div>
        <div class="surface p-5">
            <p class="text-sm font-medium text-slate-500">Eksemplar tersedia</p>
            <p class="mt-3 text-3xl font-bold tabular-nums text-emerald-700">{{ number_format($availableCopies) }}</p>
            <div class="mt-2 h-1.5 overflow-hidden rounded-full bg-stone-100">
                <div class="h-full rounded-full bg-emerald-500" style="width: {{ $totalCopies > 0 ? min(100, round(($availableCopies / $totalCopies) * 100)) : 0 }}%"></div>
            </div>
            <p class="mt-2 text-xs text-slate-500">Dari {{ number_format($totalCopies) }} total eksemplar</p>
        </div>
        <div class="surface p-5">
            <p class="text-sm font-medium text-slate-500">Peminjaman aktif</p>
            <p class="mt-3 text-3xl font-bold tabular-nums text-slate-950">{{ number_format($activeLoans) }}</p>
            @if ($overdueLoans > 0)
                <a href="{{ route('admin.loans') }}" class="mt-2 inline-flex rounded-md bg-rose-50 px-2 py-1 text-xs font-bold text-rose-700 hover:bg-rose-100" wire:navigate>{{ number_format($overdueLoans) }} terlambat — tinjau</a>
            @else
                <p class="mt-2 text-xs text-slate-500">Tidak ada keterlambatan</p>
            @endif
        </div>
        <div class="surface p-5">
            <p class="text-sm font-medium text-slate-500">Kunjungan hari ini</p>
            <p class="mt-3 text-3xl font-bold tabular-nums text-slate-950">{{ number_format($todayVisitors) }}</p>
            <p class="mt-2 text-xs text-slate-500">Tercatat melalui buku tamu</p>
        </div>
    </div>

    <div class="grid gap-6 xl:grid-cols-[1fr_280px]">
        <section class="surface overflow-hidden">
            <div class="flex flex-wrap items-center justify-between gap-3 border-b border-stone-100 px-6 py-5">
                <div>
                    <h2 class="font-bold text-slate-900">Peminjaman terbaru</h2>
                    <p class="mt-0.5 text-xs text-slate-500">5 transaksi terakhir yang dicatat petugas.</p>
                </div>
                <a href="{{ route('admin.loans') }}" class="text-xs font-bold text-kejati hover:underline" wire:navigate>Lihat semua</a>
            </div>
            <div class="divide-y divide-stone-100">
                @forelse ($recentLoans as $loan)
                    <div class="flex items-center justify-between gap-4 px-6 py-4 transition hover:bg-stone-50/50">
                        <div class="min-w-0">
                            <p class="truncate text-sm font-semibold text-slate-900">{{ $loan->nama_peminjam }}</p>
                            <p class="mt-0.5 truncate text-xs text-slate-500">{{ $loan->book->judul }}</p>
                        </div>
                        <span class="shrink-0 rounded-full px-2.5 py-1 text-[10px] font-bold uppercase {{ $loan->current_status === 'dikembalikan' ? 'bg-emerald-100 text-emerald-800' : ($loan->current_status === 'terlambat' ? 'bg-rose-100 text-rose-800' : 'bg-blue-100 text-blue-800') }}">
                            {{ ucfirst($loan->current_status) }}
                        </span>
                    </div>
                @empty
                    <div class="px-6 py-12 text-center">
                        <p class="text-sm font-semibold text-slate-700">Belum ada transaksi</p>
                        <p class="mt-1 text-xs text-slate-500">Peminjaman terbaru akan tampil di sini.</p>
                    </div>
                @endforelse
            </div>
        </section>

        <section class="surface overflow-hidden">
            <div class="border-b border-stone-100 px-5 py-4">
                <h2 class="font-bold text-slate-900">Akses cepat</h2>
            </div>
            <nav class="grid gap-2 p-4" aria-label="Akses cepat">
                <a href="{{ route('admin.loans') }}" class="btn-primary justify-between" wire:navigate>Kelola peminjaman <span aria-hidden="true">→</span></a>
                <a href="{{ route('admin.books') }}" class="btn-secondary justify-between" wire:navigate>Kelola koleksi <span aria-hidden="true">→</span></a>
                <a href="{{ route('admin.members') }}" class="btn-secondary justify-between" wire:navigate>Kelola anggota <span aria-hidden="true">→</span></a>
            </nav>
        </section>
    </div>
</div>
