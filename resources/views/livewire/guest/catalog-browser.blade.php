<div class="catalog-page">
    <x-catalog-header />

    <section class="catalog-intro" aria-labelledby="catalog-title">
        <div class="catalog-container catalog-intro-layout">
            <div>
                <p class="catalog-eyebrow">Ruang referensi Anda</p>
                <h1 id="catalog-title">Katalog buku<span aria-hidden="true">.</span></h1>
                <p class="catalog-intro-description">Temukan bacaan, telusuri pengetahuan.<br class="hidden sm:block"> Referensi berikutnya dimulai dari sini.</p>
            </div>
            <div class="catalog-guide">
                <svg aria-hidden="true" class="h-7 w-7 shrink-0 text-kejati-gold" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.4"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6.5C9 4.5 5 4.5 3 5v14c3-.8 6-.4 9 1 3-1.4 6-1.8 9-1V5c-2-.5-6-.5-9 1.5Zm0 0V20" /></svg>
                <div>
                    <p class="font-semibold text-white">Dari katalog ke rak buku</p>
                    <p class="mt-2 text-sm leading-6 text-emerald-100">Cek ketersediaan dan catat lokasi rak. Petugas kami siap membantu Anda menemukan bukunya.</p>
                </div>
            </div>
        </div>
    </section>

    <div class="catalog-container pb-12">
        <section class="catalog-search-panel" aria-label="Pencarian dan filter buku">
            <div class="catalog-search-field">
                <label for="search" class="catalog-label">Buku apa yang Anda cari?</label>
                <div class="relative">
                    <svg aria-hidden="true" class="pointer-events-none absolute left-4 top-1/2 h-5 w-5 -translate-y-1/2 text-kejati" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75"><circle cx="10.5" cy="10.5" r="6.5" /><path stroke-linecap="round" d="m16 16 4.5 4.5" /></svg>
                    <input wire:model.live.debounce.300ms="search" id="search" type="search" placeholder="Ketik judul atau nama penulis…" class="catalog-input catalog-search-input" autocomplete="off">
                </div>
            </div>
            <div>
                <label for="category" class="catalog-label">Kategori koleksi</label>
                <x-custom-select wire:model.live="category" id="category" wire:key="catalog-category" :options="$categories" empty-option="Semua kategori" />
            </div>
            <div>
                <label for="availability" class="catalog-label">Ketersediaan</label>
                <x-custom-select wire:model.live="availability" id="availability" wire:key="catalog-availability" :options="['available' => 'Tersedia', 'unavailable' => 'Tidak tersedia']" empty-option="Semua status" />
            </div>
        </section>

        <div class="catalog-results-heading">
            <div>
                <h2 class="text-xl font-bold tracking-tight text-kejati-dark">{{ $search || $category || $availability ? 'Hasil penelusuran' : 'Jelajahi koleksi' }}</h2>
                <p role="status" aria-live="polite" aria-atomic="true" class="mt-1 text-sm text-slate-600">
                    <span class="font-semibold text-slate-800">{{ number_format($books->total(), 0, ',', '.') }}</span> judul ditemukan
                    @if ($books->total())<span class="mx-1 text-slate-400" aria-hidden="true">/</span> Menampilkan {{ $books->firstItem() }}–{{ $books->lastItem() }}@endif
                </p>
            </div>
            @if ($search || $category || $availability)
                <button wire:click="resetFilters" wire:loading.attr="disabled" type="button" class="catalog-reset">Hapus semua filter <span aria-hidden="true">×</span></button>
            @else
                <span class="hidden text-xs font-medium text-slate-600 sm:block">Diurutkan berdasarkan judul A–Z</span>
            @endif
        </div>

        <div class="relative">
            <div wire:loading.delay wire:target="search,category,availability,resetFilters,gotoPage,nextPage,previousPage" class="catalog-loading" role="status">
                <span class="inline-flex items-center gap-2 rounded-full border border-emerald-200 bg-white px-4 py-2 text-sm font-semibold text-kejati shadow-soft"><span class="h-4 w-4 animate-spin rounded-full border-2 border-kejati/20 border-t-kejati" aria-hidden="true"></span>Memuat koleksi…</span>
            </div>
            <div wire:loading.class="opacity-50" wire:target="search,category,availability,resetFilters,gotoPage,nextPage,previousPage" class="transition-opacity">
                @if ($books->count())
                    <div class="catalog-grid">
                        @foreach ($books as $book)
                            <a wire:key="catalog-book-{{ $book->id }}" href="{{ route('buku.detail', $book) }}" wire:navigate class="catalog-card group" aria-labelledby="book-title-{{ $book->id }}">
                                <div class="catalog-cover-stage">
                                    <span class="catalog-availability {{ $book->is_available ? 'catalog-available' : 'catalog-unavailable' }}"><span aria-hidden="true"></span>{{ $book->is_available ? 'Tersedia' : 'Tidak tersedia' }}</span>
                                    @if ($book->cover_image)
                                        <img src="{{ Storage::url($book->cover_image) }}" alt="Sampul {{ $book->judul }}" loading="lazy" decoding="async" width="160" height="220" class="catalog-cover-image">
                                    @else
                                        <div class="catalog-book-placeholder" aria-hidden="true">
                                            <span class="catalog-placeholder-label">KOLEKSI PERPUSTAKAAN</span>
                                            <span class="catalog-placeholder-title">{{ $book->judul }}</span>
                                            <span class="catalog-placeholder-author">{{ $book->penulis }}</span>
                                            <span class="catalog-placeholder-rule"></span>
                                        </div>
                                    @endif
                                </div>
                                <div class="catalog-card-body">
                                    <p class="catalog-category" title="{{ $book->category->nama_kategori }}">{{ $book->category->nama_kategori }}</p>
                                    <h3 id="book-title-{{ $book->id }}" class="catalog-book-title">{{ $book->judul }}</h3>
                                    <p class="catalog-author">{{ $book->penulis }}</p>
                                    <div class="catalog-book-location">
                                        <span class="inline-flex min-w-0 items-center gap-1.5"><svg aria-hidden="true" class="h-4 w-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.6"><path stroke-linecap="round" stroke-linejoin="round" d="M4 3v18m16-18v18M4 11h16M4 20h16M8 4v7m4-7v7m4 4v5M8 15v5" /></svg><span class="break-words">{{ $book->lokasi_rak ? 'Rak '.$book->lokasi_rak : 'Tanyakan lokasi rak' }}</span></span>
                                        <span class="shrink-0">{{ $book->tahun_terbit ?: 'Tahun —' }}</span>
                                    </div>
                                    <div class="catalog-card-footer">
                                        <span><strong>{{ $book->stok_tersedia }}</strong> / {{ $book->stok }} eksemplar</span>
                                        <span class="catalog-detail-link">Detail buku <span aria-hidden="true">↗</span></span>
                                    </div>
                                </div>
                            </a>
                        @endforeach
                    </div>
                    <div class="mt-9">{{ $books->onEachSide(1)->links('livewire.guest.catalog-pagination') }}</div>
                @else
                    <div class="catalog-empty">
                        <svg aria-hidden="true" class="mx-auto h-12 w-12 text-kejati" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.2"><circle cx="10.5" cy="10.5" r="6.5" /><path stroke-linecap="round" d="m16 16 4.5 4.5M8 10.5h5" /></svg>
                        <h3 class="mt-5 text-xl font-bold text-kejati-dark">Koleksi tidak ditemukan</h3>
                        <p class="mx-auto mt-2 max-w-sm text-sm leading-6 text-slate-600">Coba kata kunci yang lebih singkat, pilih kategori lain, atau tampilkan semua status ketersediaan.</p>
                        @if ($search || $category || $availability)
                            <button wire:click="resetFilters" wire:loading.attr="disabled" type="button" class="btn-primary mt-6">Tampilkan semua buku</button>
                        @endif
                    </div>
                @endif
            </div>
        </div>
        <p class="mt-10 text-center text-xs leading-6 text-slate-600">Butuh bantuan menemukan referensi? Silakan hubungi petugas perpustakaan.</p>
    </div>
</div>
