<div class="mx-auto max-w-5xl px-5 py-8 lg:px-8 lg:py-14"><a href="{{ route('katalog') }}"
        class="btn-secondary" wire:navigate>← Kembali
        ke katalog</a>
    <div
        class="surface mt-6 grid gap-10 overflow-hidden p-6 sm:p-10 lg:grid-cols-[280px_1fr] lg:p-12">
        <div
            class="flex h-80 items-center justify-center overflow-hidden rounded-2xl bg-gradient-to-br from-[#166534] to-[#064E3B]">
            @if ($book->cover_image)<img src="{{ Storage::url($book->cover_image) }}" alt="Sampul {{ $book->judul }}"
            class="h-full w-full object-cover">@else<span class="text-8xl font-bold text-white/10"
                aria-hidden="true">B</span>@endif
        </div>
        <div>
            <div class="flex flex-wrap items-center gap-3"><span
                    class="rounded-full bg-amber-100 px-3 py-1 text-xs font-bold uppercase tracking-wide text-yellow-900">{{ $book->category->nama_kategori }}</span><span
                    class="rounded-full px-3 py-1 text-xs font-bold {{ $book->is_available ? 'bg-emerald-100 text-emerald-800' : 'bg-stone-100 text-slate-600' }}">{{ $book->is_available ? 'Tersedia' : 'Tidak tersedia' }}</span>
            </div>
            <h1 class="mt-5 font-display text-4xl leading-tight tracking-tight text-kejati-dark sm:text-5xl">
                {{ $book->judul }}
            </h1>
            <p class="mt-3 text-lg text-slate-500">{{ $book->penulis }}</p>
            <div class="mt-8 grid gap-4 border-y border-stone-200 py-6 sm:grid-cols-2">
                <div>
                    <p class="text-xs uppercase tracking-wide text-slate-400">Penerbit</p>
                    <p class="mt-1 font-medium">{{ $book->penerbit ?: '—' }}</p>
                </div>
                <div>
                    <p class="text-xs uppercase tracking-wide text-slate-400">Tahun terbit</p>
                    <p class="mt-1 font-medium">{{ $book->tahun_terbit ?: '—' }}</p>
                </div>
                <div>
                    <p class="text-xs uppercase tracking-wide text-slate-400">Jumlah Halaman</p>
                    <p class="mt-1 font-medium text-slate-800">{{ $book->jumlah_halaman ?: '—' }}</p>
                </div>
                <div>
                    <p class="text-xs uppercase tracking-wide text-slate-400">Nomor Panggil / DDC</p>
                    <p class="mt-1 font-mono font-bold text-kejati-dark">{{ $book->no_klasifikasi ?: '—' }}</p>
                </div>
                <div>
                    <p class="text-xs uppercase tracking-wide text-slate-400">Lokasi Rak / Lemari</p>
                    <p class="mt-1 font-medium text-slate-800">{{ $book->lokasi_rak ?: '—' }}</p>
                </div>
                <div>
                    <p class="text-xs uppercase tracking-wide text-slate-400">Nomor ISBN</p>
                    <p class="mt-1 font-mono text-sm text-slate-700">{{ $book->isbn ?: '—' }}</p>
                </div>
            </div>
            <div class="mt-6 rounded-2xl border border-stone-200 bg-stone-50 p-4">
                <p class="text-xs uppercase tracking-wide text-slate-400">Ketersediaan saat ini</p>
                <p class="mt-1 text-lg font-semibold {{ $book->is_available ? 'text-emerald-700' : 'text-slate-600' }}">
                    {{ $book->stok_tersedia }} dari {{ $book->stok }} eksemplar tersedia
                </p>
            </div>

            @if ($book->deskripsi)
                <div class="mt-8">
                    <h2 class="font-semibold text-slate-900">Tentang buku</h2>
                    <p class="mt-2 whitespace-pre-line text-sm leading-7 text-slate-600 text-justify">
                        {{ $book->deskripsi }}
                    </p>
                </div>
            @endif

            <div class="mt-8 flex items-start gap-3 rounded-2xl border border-kejati-gold/50 bg-amber-50 p-4 text-sm leading-6 text-yellow-900">
                <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl bg-kejati-gold text-kejati-dark">i</span>
                <p><strong class="block text-kejati-dark">Tertarik meminjam?</strong>Silakan tunjukkan halaman ini kepada petugas perpustakaan. Peminjaman dicatat langsung di lokasi.</p>
            </div>
        </div>
    </div>
</div>
