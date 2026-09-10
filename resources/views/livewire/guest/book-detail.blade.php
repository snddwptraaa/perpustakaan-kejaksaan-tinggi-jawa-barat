<div class="catalog-page">
    <x-catalog-header />

    <article>
        <section class="catalog-detail-hero" aria-labelledby="book-title">
            <div class="catalog-container">
                <nav aria-label="Breadcrumb" class="catalog-breadcrumb">
                    <a href="{{ route('katalog') }}" wire:navigate>
                        <svg aria-hidden="true" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="m15 18-6-6 6-6" /></svg>
                        Kembali ke katalog
                    </a>
                </nav>

                <div class="catalog-detail-hero-grid">
                    <div class="catalog-detail-stage">
                        <span class="catalog-availability {{ $book->is_available ? 'catalog-available' : 'catalog-unavailable' }}"><span aria-hidden="true"></span>{{ $book->is_available ? 'Tersedia' : 'Tidak tersedia' }}</span>
                        @if ($book->cover_image)
                            <img src="{{ Storage::url($book->cover_image) }}" alt="Sampul {{ $book->judul }}" decoding="async" fetchpriority="high" width="240" height="320" class="catalog-cover-image">
                        @else
                            <div class="catalog-book-placeholder" aria-hidden="true">
                                <span class="catalog-placeholder-label">KOLEKSI PERPUSTAKAAN</span>
                                <span class="catalog-placeholder-title">{{ $book->judul }}</span>
                                <span class="catalog-placeholder-author">{{ $book->penulis }}</span>
                                <span class="catalog-placeholder-rule"></span>
                            </div>
                        @endif
                    </div>

                    <div class="min-w-0 self-center">
                        <p class="catalog-detail-category">{{ $book->category->nama_kategori }}</p>
                        <h1 id="book-title" class="catalog-detail-title">{{ $book->judul }}</h1>
                        <p class="catalog-detail-author">oleh {{ $book->penulis }}</p>

                        <div class="catalog-shelf-ticket" aria-label="Petunjuk menemukan buku">
                            <div>
                                <p class="catalog-shelf-label">Lokasi koleksi</p>
                                <p class="catalog-shelf-value">{{ $book->lokasi_rak ? 'Rak '.$book->lokasi_rak : 'Tanyakan kepada petugas' }}</p>
                            </div>
                            <div>
                                <p class="catalog-shelf-label">Nomor panggil / DDC</p>
                                <p class="catalog-shelf-code">{{ $book->no_klasifikasi ?: 'Belum tercatat' }}</p>
                            </div>
                            <div class="catalog-shelf-stock">
                                <p class="catalog-shelf-label">Stok tersedia</p>
                                <p><strong>{{ $book->stok_tersedia }}</strong> dari {{ $book->stok }}</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <div class="catalog-container catalog-detail-content">
            <div class="min-w-0">
                <section aria-labelledby="book-information-title">
                    <p class="catalog-eyebrow-dark">Informasi bibliografi</p>
                    <h2 id="book-information-title" class="catalog-section-title">Detail koleksi</h2>
                    <dl class="catalog-metadata">
                        <div>
                            <dt>Penerbit</dt>
                            <dd>{{ $book->penerbit ?: 'Belum tercatat' }}</dd>
                        </div>
                        <div>
                            <dt>Tahun terbit</dt>
                            <dd>{{ $book->tahun_terbit ?: 'Belum tercatat' }}</dd>
                        </div>
                        <div>
                            <dt>Jumlah halaman</dt>
                            <dd>{{ $book->jumlah_halaman ? $book->jumlah_halaman.' halaman' : 'Belum tercatat' }}</dd>
                        </div>
                        <div>
                            <dt>Nomor ISBN</dt>
                            <dd class="font-mono">{{ $book->isbn ?: 'Belum tercatat' }}</dd>
                        </div>
                    </dl>
                </section>

                @if ($book->deskripsi)
                    <section class="catalog-description" aria-labelledby="book-description-title">
                        <p class="catalog-eyebrow-dark">Ringkasan</p>
                        <h2 id="book-description-title" class="catalog-section-title">Tentang buku</h2>
                        <p class="whitespace-pre-line">{{ $book->deskripsi }}</p>
                    </section>
                @endif
            </div>

            <aside class="catalog-borrow-note" aria-labelledby="borrow-note-title">
                <svg aria-hidden="true" class="h-7 w-7 text-kejati" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M4 3v18m16-18v18M4 11h16M4 20h16M8 4v7m4-7v7m4 4v5M8 15v5" /></svg>
                <p class="catalog-eyebrow-dark mt-5">Langkah berikutnya</p>
                <h2 id="borrow-note-title">Ingin membaca buku ini?</h2>
                <p>Tunjukkan judul atau nomor panggil kepada petugas. Peminjaman akan dicatat langsung di perpustakaan.</p>
                <a href="{{ route('katalog') }}" class="catalog-back-link" wire:navigate>
                    <svg aria-hidden="true" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="m15 18-6-6 6-6" /></svg>
                    Lanjut telusuri katalog
                </a>
            </aside>
        </div>
    </article>
</div>
