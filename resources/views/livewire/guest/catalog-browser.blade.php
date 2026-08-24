<div class="mx-auto max-w-7xl px-5 py-8 lg:px-8 lg:py-12">
    <div class="flex flex-col gap-6 border-b border-stone-200 pb-8 md:flex-row md:items-end md:justify-between">
        <div>
            <p class="page-kicker">Katalog terbuka</p>
            <h1 class="mt-3 font-display text-4xl tracking-tight text-kejati-dark sm:text-5xl">Koleksi untuk Anda
                telusuri</h1>
            <p class="mt-3 max-w-2xl text-sm leading-6 text-slate-500">Temukan referensi hukum dan pengetahuan
                pendukung. Peminjaman dilakukan secara langsung melalui petugas perpustakaan.</p>
        </div>
        <div class="surface flex items-center gap-2 px-4 py-2.5 text-sm font-semibold text-slate-600"><span
                class="status-dot bg-emerald-500 text-emerald-500"></span> {{ $books->total() }} judul ditemukan</div>
    </div>
    <div class="surface sticky top-[73px] z-20 mt-6 p-3 sm:p-4">
        <div class="grid gap-4 lg:grid-cols-[1fr_220px_200px_auto]">
            <div class="relative"><label for="search" class="sr-only">Cari judul atau penulis</label><span
                    class="pointer-events-none absolute inset-y-0 left-4 flex items-center text-slate-400"
                    aria-hidden="true">⌕</span><input wire:model.live.debounce.300ms="search" id="search" type="search"
                    placeholder="Cari judul atau penulis…"
                    class="field-control py-3 pl-11 pr-4">
            </div>
            <div><label for="category" class="sr-only">Filter kategori</label><select wire:model.live="category"
                    id="category"
                    class="field-control py-3">
                    <option value="">Semua kategori</option>@foreach ($categories as $item)
                    <option value="{{ $item->id }}">{{ $item->nama_kategori }}</option>@endforeach
                </select></div>
            <div><label for="availability" class="sr-only">Filter ketersediaan</label><select
                    wire:model.live="availability" id="availability"
                    class="field-control py-3">
                    <option value="">Semua status</option>
                    <option value="available">Tersedia</option>
                    <option value="unavailable">Tidak tersedia</option>
                </select></div>@if ($search || $category || $availability)<button
                    wire:click="$set('search', '') ; $set('category', '') ; $set('availability', '')" type="button"
                class="btn-secondary">Reset</button>@endif
        </div>
    </div>
    <div wire:loading.delay class="py-10 text-center" role="status" aria-live="polite">
        <span class="inline-flex items-center gap-3 rounded-full bg-white px-4 py-2 text-sm font-semibold text-kejati shadow-sm"><span class="h-4 w-4 animate-spin rounded-full border-2 border-kejati/20 border-t-kejati"></span>Memuat koleksi…</span>
    </div>
    <div wire:loading.remove class="mt-8">@if ($books->count())
        <div class="grid gap-5 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
            @foreach ($books as $book)
                <a href="{{ route('buku.detail', $book) }}"
                    wire:navigate
                    class="group surface flex flex-col overflow-hidden transition duration-200 hover:-translate-y-1 hover:border-kejati/50 hover:shadow-soft focus:outline-none focus:ring-2 focus:ring-kejati">
                    <div class="relative flex h-52 items-center justify-center overflow-hidden bg-gradient-to-br from-[#166534] to-[#064E3B]">
                        @if ($book->cover_image)
                            <img src="{{ Storage::url($book->cover_image) }}"
                                alt="Sampul {{ $book->judul }}"
                                class="h-full w-full object-cover transition duration-300 group-hover:scale-105">
                        @else
                            <span class="absolute -right-4 -top-8 text-[9rem] font-bold leading-none text-white/5" aria-hidden="true">B</span>
                            <div class="relative px-8 text-center text-white">
                                <div class="mx-auto mb-3 flex h-10 w-10 items-center justify-center rounded-lg border border-kejati-gold/50 text-kejati-gold transition duration-200 group-hover:scale-110" aria-hidden="true">▤</div>
                                <p class="line-clamp-3 text-sm font-semibold leading-5">{{ $book->judul }}</p>
                            </div>
                        @endif
                        <div class="absolute left-3 top-3 rounded-full px-2.5 py-1 text-[10px] font-bold uppercase tracking-wide {{ $book->is_available ? 'bg-emerald-100 text-emerald-800' : 'bg-white/90 text-slate-600' }}">
                            {{ $book->is_available ? 'Tersedia' : 'Tidak tersedia' }}
                        </div>
                    </div>
                    <div class="flex flex-1 flex-col p-5">
                        <p class="text-xs font-semibold uppercase tracking-wide text-kejati-gold-dark">
                            {{ $book->category->nama_kategori }}
                        </p>
                        <h2 class="mt-2 line-clamp-2 min-h-[3rem] font-semibold leading-6 text-slate-900 transition group-hover:text-kejati">
                            {{ $book->judul }}
                        </h2>
                        <p class="mt-2 line-clamp-1 text-sm text-slate-500">{{ $book->penulis }}</p>
                        <div class="mt-auto flex items-end justify-between gap-3 border-t border-stone-100 pt-4">
                            <div>
                                <p class="text-[10px] uppercase tracking-wide text-slate-400">Ketersediaan</p>
                                <p class="mt-1 text-sm font-semibold {{ $book->is_available ? 'text-emerald-700' : 'text-slate-500' }}">
                                    {{ $book->stok_tersedia }} / {{ $book->stok }} eksemplar
                                </p>
                            </div>
                            <span class="rounded-lg p-2 text-kejati transition group-hover:bg-kejati group-hover:text-white" aria-hidden="true">
                                →
                            </span>
                        </div>
                    </div>
                </a>
            @endforeach
        </div>
    <div class="mt-10">{{ $books->links() }}</div>@else<div
                            class="surface border-dashed px-6 py-16 text-center"><span
                class="mx-auto flex h-12 w-12 items-center justify-center rounded-full bg-stone-100 text-xl text-slate-400"
                aria-hidden="true">⌕</span>
            <h2 class="mt-4 font-semibold text-slate-800">Koleksi tidak ditemukan</h2>
            <p class="mt-2 text-sm text-slate-500">Coba gunakan kata kunci atau filter yang berbeda.</p>
            @if ($search || $category || $availability)
                <button wire:click="$set('search', '') ; $set('category', '') ; $set('availability', '')" type="button" class="btn-secondary mt-5">Hapus semua filter</button>
            @endif
        </div>@endif
    </div>
</div>
