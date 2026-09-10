<div class="space-y-6">
    <!-- Top Action Bar -->
    <div class="flex flex-col justify-between gap-4 sm:flex-row sm:items-center">
        <div>
            <h1 class="page-title mt-0">Koleksi buku</h1>
            <p class="mt-1 text-sm text-slate-500">Kelola daftar buku fisik, nomor panggil rak, stok eksemplar, dan cover katalog.</p>
        </div>
        <div class="flex flex-wrap items-center gap-2.5">
            <div class="relative" x-data="{ exportOpen: false }" @click.outside="exportOpen = false" @keydown.escape.window="exportOpen = false">
                <button type="button" class="btn-secondary" @click="exportOpen = !exportOpen; if (exportOpen) $nextTick(() => $refs.firstExport.focus())"
                    :aria-expanded="exportOpen" aria-haspopup="menu" aria-controls="book-export-menu">
                    <svg aria-hidden="true" class="h-4 w-4 text-kejati" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v12m0 0l-4-4m4 4l4-4M5 19h14" />
                    </svg>
                    <span>Ekspor</span>
                    <svg aria-hidden="true" class="h-4 w-4 transition" :class="exportOpen && 'rotate-180'" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m6 9 6 6 6-6" /></svg>
                </button>
                <div id="book-export-menu" role="menu" x-cloak x-show="exportOpen" x-transition.origin.top.right
                    @keydown.arrow-down.prevent="$focus.wrap().next()" @keydown.arrow-up.prevent="$focus.wrap().previous()"
                    class="select-panel left-auto right-0 w-48">
                    <button x-ref="firstExport" role="menuitem" type="button" wire:click="exportPdf" @click="exportOpen = false" class="select-option">PDF <span aria-hidden="true" class="text-xs font-bold text-rose-600">.pdf</span></button>
                    <button role="menuitem" type="button" wire:click="exportXlsx" @click="exportOpen = false" class="select-option">Excel <span aria-hidden="true" class="text-xs font-bold text-emerald-700">.xlsx</span></button>
                    <button role="menuitem" type="button" wire:click="exportCsv" @click="exportOpen = false" class="select-option">CSV <span aria-hidden="true" class="text-xs font-bold text-sky-700">.csv</span></button>
                </div>
            </div>
            <button wire:click="create"
                type="button"
                class="inline-flex items-center gap-2 rounded-xl bg-kejati px-5 py-3 text-sm font-bold text-white shadow-lg shadow-kejati/20 transition duration-150 hover:bg-kejati-dark hover:shadow-kejati/30 focus-visible:ring-2 focus-visible:ring-kejati-gold focus-visible:ring-offset-2 active:scale-95">
                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4" />
                </svg>
                <span>Tambah Buku Baru</span>
            </button>
        </div>
    </div>

    <!-- Alert Notifications -->
    @if (session('success'))
        <div class="flex items-center gap-3 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3.5 text-sm font-medium text-emerald-800 shadow-sm">
            <svg class="h-5 w-5 shrink-0 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
            <p>{{ session('success') }}</p>
        </div>
    @endif

    @if (session('error'))
        <div class="flex items-center gap-3 rounded-xl border border-rose-200 bg-rose-50 px-4 py-3.5 text-sm font-medium text-rose-800 shadow-sm">
            <svg class="h-5 w-5 shrink-0 text-rose-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
            <p>{{ session('error') }}</p>
        </div>
    @endif

    <!-- Books Data Card -->
    <div class="rounded-2xl border border-stone-200 bg-white shadow-sm">
        <!-- Search & Filter Header -->
        <div class="border-b border-stone-100 p-4 sm:p-5">
            <div class="grid gap-4 sm:grid-cols-2 min-[1400px]:grid-cols-[minmax(16rem,1.5fr)_minmax(12rem,1fr)_minmax(12rem,1fr)_minmax(12rem,1fr)]">
                <div class="min-w-0">
                    <label for="book-search" class="mb-1.5 block text-xs font-semibold text-slate-700">Cari koleksi</label>
                    <div class="relative">
                        <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3.5 text-slate-400">
                            <svg aria-hidden="true" class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                            </svg>
                        </div>
                        <input id="book-search" name="book-search" wire:model.live.debounce.300ms="search"
                            type="search"
                            autocomplete="off"
                            placeholder="Cari judul, penulis, nomor panggil, atau rak…"
                            class="w-full rounded-xl border-stone-200 bg-stone-50 py-2.5 pl-10 pr-4 text-sm text-slate-900 transition placeholder:text-slate-400 focus:border-kejati focus:bg-white focus-visible:ring-2 focus-visible:ring-kejati-gold focus-visible:ring-offset-2 focus:ring-1 focus:ring-kejati">
                    </div>
            </div>
                <div class="min-w-0">
                    <label for="book-filter-category" class="mb-1.5 block text-xs font-semibold text-slate-700">Kategori</label>
                    <x-custom-select wire:model.live="filterCategory" id="book-filter-category" wire:key="book-filter-category" :options="$categories" empty-option="Semua kategori" />
                </div>
                <div class="min-w-0">
                    <label for="book-filter-status" class="mb-1.5 block text-xs font-semibold text-slate-700">Status koleksi</label>
                    <x-custom-select wire:model.live="filterStatus" id="book-filter-status" wire:key="book-filter-status" :options="[
                        'active' => 'Aktif di katalog',
                        'archived' => 'Diarsipkan',
                        'available' => 'Tersedia',
                        'unavailable' => 'Stok habis',
                    ]" empty-option="Semua status" :searchable="false" />
                </div>
                <div class="min-w-0">
                    <label for="book-sort" class="mb-1.5 block text-xs font-semibold text-slate-700">Urutkan</label>
                    <x-custom-select wire:model.live="sort" id="book-sort" wire:key="book-sort" :options="[
                        'newest' => 'Terbaru ditambahkan',
                        'title_asc' => 'Judul A–Z',
                        'category_asc' => 'Kategori A–Z',
                        'stock_asc' => 'Stok terendah',
                    ]" :searchable="false" />
                </div>
            </div>
            <div class="mt-4 flex flex-wrap items-center justify-between gap-3 border-t border-stone-100 pt-4">
                <p role="status" aria-live="polite" aria-atomic="true" class="text-xs font-medium text-slate-600">
                    Ditemukan <span class="font-bold text-slate-900 tabular-nums">{{ number_format($books->total(), 0, ',', '.') }}</span> judul buku
                </p>
                @if ($search !== '' || $filterCategory !== '' || $filterStatus !== '' || $sort !== 'newest')
                    <button type="button" wire:click="resetFilters" wire:loading.attr="disabled" class="inline-flex min-h-10 items-center gap-2 rounded-lg px-3 text-xs font-bold text-kejati transition hover:bg-kejati/10 disabled:opacity-50">
                        Hapus semua filter <span aria-hidden="true">×</span>
                    </button>
                @else
                    <span class="text-xs text-slate-500">Menampilkan seluruh koleksi</span>
                @endif
                </div>
        </div>

        <!-- Table Listing -->
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm">
                <thead class="bg-stone-50/80 text-xs font-semibold uppercase tracking-wider text-slate-500">
                    <tr>
                        <th class="px-4 py-3.5">Buku & Pengarang</th>
                        <th class="px-4 py-3.5">Kategori</th>
                        <th class="px-4 py-3.5">No. Panggil / Rak</th>
                        <th class="px-4 py-3.5 text-center">Stok Fisik</th>
                        <th class="px-4 py-3.5 text-center">Status</th>
                        <th class="px-4 py-3.5 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-stone-100">
                    @forelse ($books as $book)
                        <tr class="transition hover:bg-stone-50/70">
                            <!-- Cover & Book Title -->
                            <td class="px-4 py-4">
                                <div class="flex items-start gap-3.5">
                                    <div class="relative h-14 w-10 shrink-0 overflow-hidden rounded-md border border-stone-200 bg-stone-100 shadow-sm">
                                        @if ($book->cover_image)
                                            <img src="{{ Storage::url($book->cover_image) }}"
                                                alt="{{ $book->judul }}"
                                                class="h-full w-full object-cover">
                                        @else
                                            <div class="flex h-full w-full flex-col items-center justify-center bg-emerald-950/10 text-emerald-800">
                                                <svg class="h-5 w-5 opacity-60" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                                                </svg>
                                            </div>
                                        @endif
                                    </div>
                                    <div class="min-w-0">
                                        <p class="font-bold text-slate-900 line-clamp-1">{{ $book->judul }}</p>
                                        <p class="mt-0.5 text-xs text-slate-600 line-clamp-1">Penulis: <span class="font-medium text-slate-800">{{ $book->penulis }}</span></p>
                                        @if ($book->penerbit || $book->tahun_terbit)
                                            <p class="mt-0.5 text-[11px] text-slate-400">
                                                {{ $book->penerbit ? $book->penerbit : '' }}{{ $book->penerbit && $book->tahun_terbit ? ' • ' : '' }}{{ $book->tahun_terbit ? $book->tahun_terbit : '' }}
                                            </p>
                                        @endif
                                    </div>
                                </div>
                            </td>

                            <!-- Category -->
                            <td class="px-4 py-4">
                                <span class="inline-flex items-center rounded-lg bg-stone-100 px-2.5 py-1 text-xs font-medium text-slate-700">
                                    {{ $book->category->nama_kategori }}
                                </span>
                            </td>

                            <!-- Call Number & Shelf Location -->
                            <td class="px-4 py-4">
                                <div class="text-xs">
                                    @if ($book->no_klasifikasi)
                                        <span class="inline-block font-mono font-bold text-kejati-dark bg-emerald-50 px-2 py-0.5 rounded border border-emerald-200/60">
                                            {{ $book->no_klasifikasi }}
                                        </span>
                                    @else
                                        <span class="text-slate-400 italic">-</span>
                                    @endif
                                    @if ($book->lokasi_rak)
                                        <p class="mt-1 text-slate-500 font-medium flex items-center gap-1">
                                            <svg class="h-3.5 w-3.5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                                            </svg>
                                            {{ $book->lokasi_rak }}
                                        </p>
                                    @endif
                                </div>
                            </td>

                            <!-- Physical Copies / Stock -->
                            <td class="px-4 py-4 text-center">
                                <span class="font-semibold text-slate-900">{{ $book->stok_tersedia }}</span>
                                <span class="text-xs text-slate-400">/ {{ $book->stok }} eks.</span>
                            </td>

                            <!-- Status Badge -->
                            <td class="px-4 py-4 text-center">
                                <span class="inline-flex items-center rounded-full px-2.5 py-1 text-xs font-bold {{ $book->archived_at ? 'bg-stone-200 text-slate-700' : ($book->is_available ? 'bg-emerald-100 text-emerald-800' : 'bg-rose-100 text-rose-800') }}">
                                    <span class="mr-1.5 h-1.5 w-1.5 rounded-full {{ $book->archived_at ? 'bg-slate-500' : ($book->is_available ? 'bg-emerald-500' : 'bg-rose-500') }}"></span>
                                    {{ $book->archived_at ? 'Diarsipkan' : ($book->is_available ? 'Tersedia' : 'Habis') }}
                                </span>
                            </td>

                            <!-- Actions -->
                            <td class="px-4 py-4 text-right">
                                <div class="inline-flex items-center gap-2">
                                    <button wire:click="edit({{ $book->id }})"
                                        type="button"
                                        class="inline-flex items-center gap-1 rounded-lg px-2.5 py-1.5 text-xs font-bold text-kejati transition hover:bg-kejati/10 focus-visible:ring-2 focus-visible:ring-kejati-gold focus-visible:ring-offset-2">
                                        <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                        </svg>
                                        Edit
                                    </button>
                                    <button type="button" wire:click="toggleArchive({{ $book->id }})"
                                        wire:confirm="{{ $book->archived_at ? 'Pulihkan buku ini ke katalog publik?' : 'Arsipkan buku ini dari katalog publik?' }}"
                                        class="inline-flex items-center rounded-lg px-2.5 py-1.5 text-xs font-bold text-amber-700 transition hover:bg-amber-50">
                                        {{ $book->archived_at ? 'Pulihkan' : 'Arsipkan' }}
                                    </button>
                                    <button type="button"
                                        @click="$dispatch('open-confirm-modal', {
                                            title: 'Hapus Koleksi Buku',
                                            message: 'Apakah Anda yakin ingin menghapus buku \'{{ addslashes($book->judul) }}\' dari katalog fisik?',
                                            confirmButtonText: 'Ya, Hapus Buku',
                                            type: 'danger',
                                            onConfirm: () => $wire.delete({{ $book->id }})
                                        })"
                                        class="inline-flex items-center gap-1 rounded-lg px-2.5 py-1.5 text-xs font-bold text-rose-600 transition hover:bg-rose-50 focus-visible:ring-2 focus-visible:ring-kejati-gold focus-visible:ring-offset-2">
                                        <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                        </svg>
                                        Hapus
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-16 text-center">
                                <div class="mx-auto flex h-12 w-12 items-center justify-center rounded-full bg-stone-100 text-slate-400">
                                    <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                                    </svg>
                                </div>
                                <p class="mt-3 font-semibold text-slate-800">Tidak ada buku ditemukan</p>
                                <p class="mt-1 text-xs text-slate-500">
                                    {{ $search !== '' || $filterCategory !== '' || $filterStatus !== '' ? 'Tidak ada buku yang sesuai dengan filter tersebut.' : 'Belum ada koleksi buku yang ditambahkan.' }}
                                </p>
                                @if ($search !== '' || $filterCategory !== '' || $filterStatus !== '')
                                    <button type="button" wire:click="resetFilters" class="btn-secondary mt-5">Tampilkan semua koleksi</button>
                                @endif
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        @if ($books->hasPages())
            <div class="border-t border-stone-100 px-6 py-4">
                {{ $books->links() }}
            </div>
        @endif
    </div>

    <!-- MODAL POPUP FORM TAMBAH / EDIT BUKU -->
    @if ($showForm)
        <div x-data x-trap.inert.noscroll="true" @keydown.escape.stop="$wire.resetForm()" class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
            <!-- Background Backdrop with Blur -->
            <div class="fixed inset-0 bg-slate-950/60 backdrop-blur-sm transition-opacity"
                wire:click="resetForm"></div>

            <div class="flex min-h-full items-center justify-center p-4 text-center sm:p-6 lg:p-8">
                <!-- Modal Card Content -->
                <div class="relative my-8 w-full max-w-3xl transform overflow-hidden rounded-3xl border border-stone-200/80 bg-white text-left shadow-2xl transition">
                    
                    <!-- Modal Header -->
                    <div class="relative bg-kejati-dark px-6 py-5 text-white sm:px-8">
                        <div class="flex items-center justify-between">
                            <div>
                                <h3 class="text-xl font-bold text-white sm:text-2xl" id="modal-title">
                                    {{ $editingId ? 'Edit buku' : 'Tambah buku' }}
                                </h3>
                            </div>
                            <button aria-label="Tutup form buku" wire:click="resetForm"
                                type="button"
                                class="rounded-xl bg-white/10 p-2 text-white/80 hover:bg-white/20 hover:text-white transition focus-visible:ring-2 focus-visible:ring-kejati-gold focus-visible:ring-offset-2">
                                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                </svg>
                            </button>
                        </div>
                    </div>

                    <!-- Modal Form -->
                    <form wire:submit="save">
                        <div class="flex max-h-[75vh] flex-col gap-6 overflow-y-auto px-6 py-6 sm:px-8">
                            
                            <!-- 1. SECTION: INFORMASI UTAMA BUKU -->
                            <div class="space-y-4">
                                <h4 class="text-xs font-bold uppercase tracking-wider text-kejati-dark flex items-center gap-1.5">
                                    <svg class="h-4 w-4 text-kejati" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                    Informasi bibliografi
                                </h4>

                                <!-- Judul Buku -->
                                <div>
                                    <label for="book-judul" class="block text-xs font-bold uppercase tracking-wider text-slate-700">
                                        Judul Buku <span class="text-rose-600">*</span>
                                    </label>
                                    <input wire:model="judul" id="book-judul"
                                        type="text"
                                        placeholder="Contoh: Hukum Acara Pidana Indonesia"
                                        class="mt-1.5 w-full rounded-xl border-stone-300 text-sm focus:border-kejati focus:ring-kejati shadow-sm placeholder:text-slate-400">
                                    <x-input-error :messages="$errors->get('judul')" class="mt-1.5" />
                                </div>

                                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                                    <!-- Penulis / Pengarang -->
                                    <div>
                                        <label for="book-penulis" class="block text-xs font-bold uppercase tracking-wider text-slate-700">
                                            Penulis / Pengarang <span class="text-rose-600">*</span>
                                        </label>
                                        <input wire:model="penulis" id="book-penulis"
                                            type="text"
                                            placeholder="Contoh: Prof. Dr. Andi Hamzah, S.H."
                                            class="mt-1.5 w-full rounded-xl border-stone-300 text-sm focus:border-kejati focus:ring-kejati shadow-sm placeholder:text-slate-400">
                                        <x-input-error :messages="$errors->get('penulis')" class="mt-1.5" />
                                    </div>

                                    <!-- Kategori Buku -->
                                    <div>
                                        <div class="flex items-center justify-between">
                                            <label for="book-category_id" class="block text-xs font-bold uppercase tracking-wider text-slate-700">
                                                Kategori / Bidang Hukum <span class="text-rose-600">*</span>
                                            </label>
                                            <button type="button"
                                                wire:click="openQuickCategoryModal"
                                                class="text-[11px] font-bold text-kejati hover:text-kejati-dark hover:underline inline-flex items-center gap-0.5">
                                                + Tambah Kategori
                                            </button>
                                        </div>
                                        <x-custom-select wire:model="category_id" id="book-category_id" wire:key="book-category" :options="$categories" placeholder="-- Pilih Kategori --" />
                                        <x-input-error :messages="$errors->get('category_id')" class="mt-1.5" />
                                    </div>

                                </div>

                                <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">
                                    <!-- Penerbit -->
                                    <div>
                                        <label for="book-penerbit" class="block text-xs font-bold uppercase tracking-wider text-slate-700">
                                            Penerbit
                                        </label>
                                        <input wire:model="penerbit" id="book-penerbit"
                                            type="text"
                                            placeholder="Contoh: Sinar Grafika"
                                            class="mt-1.5 w-full rounded-xl border-stone-300 text-sm focus:border-kejati focus:ring-kejati shadow-sm placeholder:text-slate-400">
                                        <x-input-error :messages="$errors->get('penerbit')" class="mt-1.5" />
                                    </div>

                                    <!-- Tahun Terbit -->
                                    <div>
                                        <label for="book-tahun_terbit" class="block text-xs font-bold uppercase tracking-wider text-slate-700">
                                            Tahun Terbit
                                        </label>
                                        <input wire:model="tahun_terbit" id="book-tahun_terbit"
                                            type="number"
                                            min="1000"
                                            max="2100"
                                            placeholder="Contoh: 2023"
                                            class="mt-1.5 w-full rounded-xl border-stone-300 text-sm focus:border-kejati focus:ring-kejati shadow-sm placeholder:text-slate-400">
                                        <x-input-error :messages="$errors->get('tahun_terbit')" class="mt-1.5" />
                                    </div>

                                    <!-- Jumlah Halaman -->
                                    <div>
                                        <label for="book-jumlah_halaman" class="block text-xs font-bold uppercase tracking-wider text-slate-700">
                                            Jumlah Halaman
                                        </label>
                                        <input wire:model="jumlah_halaman" id="book-jumlah_halaman"
                                            type="text"
                                            placeholder="Contoh: 350 hlm"
                                            class="mt-1.5 w-full rounded-xl border-stone-300 text-sm focus:border-kejati focus:ring-kejati shadow-sm placeholder:text-slate-400">
                                        <x-input-error :messages="$errors->get('jumlah_halaman')" class="mt-1.5" />
                                    </div>
                                </div>
                            </div>

                            <!-- 3. SECTION: TATA LETAK & IDENTITAS FISIK -->
                            <div class="rounded-2xl border border-emerald-100 bg-emerald-50/40 p-4 sm:p-5 space-y-4">
                                <h4 class="text-xs font-bold uppercase tracking-wider text-kejati-dark flex items-center gap-1.5">
                                    <svg class="h-4 w-4 text-kejati" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z" />
                                    </svg>
                                    Lokasi dan identitas fisik
                                </h4>

                                <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">
                                    <!-- Nomor Panggil / Klasifikasi Buku -->
                                    <div>
                                        <label for="book-no_klasifikasi" class="block text-xs font-bold uppercase tracking-wider text-slate-700">
                                            Nomor Panggil / DDC
                                        </label>
                                        <input wire:model="no_klasifikasi" id="book-no_klasifikasi"
                                            type="text"
                                            placeholder="345.02 HAM h"
                                            class="mt-1.5 w-full rounded-xl border-stone-300 font-mono text-sm focus:border-kejati focus:ring-kejati shadow-sm placeholder:text-slate-400">
                                        <p class="mt-1 text-[11px] text-slate-500">Nomor stiker punggung buku fisik.</p>
                                        <x-input-error :messages="$errors->get('no_klasifikasi')" class="mt-1" />
                                    </div>

                                    <!-- Lokasi Rak / Lemari -->
                                    <div>
                                        <label for="book-lokasi_rak" class="block text-xs font-bold uppercase tracking-wider text-slate-700">
                                            Lokasi Rak / Lemari
                                        </label>
                                        <input wire:model="lokasi_rak" id="book-lokasi_rak"
                                            type="text"
                                            placeholder="Rak Pidana Lt.2 - A3"
                                            class="mt-1.5 w-full rounded-xl border-stone-300 text-sm focus:border-kejati focus:ring-kejati shadow-sm placeholder:text-slate-400">
                                        <p class="mt-1 text-[11px] text-slate-500">Posisi rak di ruangan perpustakaan.</p>
                                        <x-input-error :messages="$errors->get('lokasi_rak')" class="mt-1" />
                                    </div>

                                    <!-- Nomor ISBN -->
                                    <div>
                                        <label for="book-isbn" class="block text-xs font-bold uppercase tracking-wider text-slate-700">
                                            Nomor ISBN
                                        </label>
                                        <input wire:model="isbn" id="book-isbn"
                                            type="text"
                                            placeholder="978-602-..."
                                            class="mt-1.5 w-full rounded-xl border-stone-300 text-sm focus:border-kejati focus:ring-kejati shadow-sm placeholder:text-slate-400">
                                        <p class="mt-1 text-[11px] text-slate-500">Barcode penerbit (bila ada).</p>
                                        <x-input-error :messages="$errors->get('isbn')" class="mt-1" />
                                    </div>
                                </div>
                            </div>

                            <!-- 4. SECTION: INVENTARIS & STOK EKSEMPLAR -->
                            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                                <div>
                                    <label for="book-stok" class="block text-xs font-bold uppercase tracking-wider text-slate-700">
                                        Total Eksemplar (Stok) <span class="text-rose-600">*</span>
                                    </label>
                                    <input wire:model.live.debounce.200ms="stok" id="book-stok"
                                        type="number"
                                        min="0"
                                        class="mt-1.5 w-full rounded-xl border-stone-300 text-sm focus:border-kejati focus:ring-kejati shadow-sm">
                                    <p class="mt-1 text-[11px] text-slate-500">Total seluruh fisik buku yang dimiliki.</p>
                                    <x-input-error :messages="$errors->get('stok')" class="mt-1.5" />
                                </div>

                                <div>
                                    <label for="book-stok_tersedia" class="block text-xs font-bold uppercase tracking-wider text-slate-700">
                                        Stok Tersedia di Rak <span class="text-rose-600">*</span>
                                    </label>
                                    <input wire:model="stok_tersedia" id="book-stok_tersedia"
                                        type="number"
                                        min="0"
                                        class="mt-1.5 w-full rounded-xl border-stone-300 text-sm focus:border-kejati focus:ring-kejati shadow-sm"
                                        @if($editingId) readonly aria-readonly="true" @endif>
                                    @if($editingId)
                                        <p class="mt-1 text-[11px] text-slate-500">
                                            Dihitung otomatis = Total eksemplar − {{ $this->activeLoansCount }} peminjaman aktif.
                                        </p>
                                    @else
                                        <p class="mt-1 text-[11px] text-slate-500">Jumlah fisik yang belum sedang dipinjam.</p>
                                    @endif
                                    <x-input-error :messages="$errors->get('stok_tersedia')" class="mt-1.5" />
                                </div>
                            </div>

                            <!-- 5. SECTION: SINOPSIS & DESKRIPSI -->
                            <div>
                                <label for="book-deskripsi" class="block text-xs font-bold uppercase tracking-wider text-slate-700">
                                    Sinopsis / Deskripsi Ringkas Buku
                                </label>
                                <textarea wire:model="deskripsi" id="book-deskripsi"
                                    rows="3"
                                    placeholder="Tuliskan ringkasan isi buku, daftar bab pokok, atau catatan khusus buku hukum ini…"
                                    class="mt-1.5 w-full rounded-xl border-stone-300 text-sm focus:border-kejati focus:ring-kejati shadow-sm placeholder:text-slate-400"></textarea>
                                <x-input-error :messages="$errors->get('deskripsi')" class="mt-1.5" />
                            </div>

                            <!-- 5. SECTION: COVER BUKU -->
                            <div class="rounded-2xl border border-stone-200 bg-stone-50/60 p-4">
                                <div class="mb-3 flex items-center justify-between">
                                    <h4 class="text-sm font-bold text-slate-900">Sampul buku <span class="font-normal text-slate-500">(opsional)</span></h4>
                                    @if ($cover || $existingCover)
                                        <button type="button"
                                            wire:click="{{ $cover ? 'removeCoverPreview' : 'removeExistingCover' }}"
                                            class="text-xs font-semibold text-rose-600 hover:text-rose-700 hover:underline">
                                            Hapus Sampul
                                        </button>
                                    @endif
                                </div>

                                <div class="flex flex-col items-center gap-5 sm:flex-row">
                                    <div class="relative flex h-24 w-20 shrink-0 items-center justify-center overflow-hidden rounded-xl border-2 border-dashed border-stone-300 bg-white shadow-inner">
                                        @if ($cover)
                                            <img src="{{ $cover->temporaryUrl() }}" alt="Pratinjau sampul" class="h-full w-full object-cover">
                                        @elseif ($existingCover)
                                            <img src="{{ Storage::url($existingCover) }}" alt="Sampul buku saat ini" class="h-full w-full object-cover">
                                        @else
                                            <div class="p-3 text-center text-slate-500">
                                                <svg class="mx-auto h-8 w-8 text-stone-300" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                                </svg>
                                                <span class="mt-1 block text-[10px]">Belum ada foto</span>
                                            </div>
                                        @endif

                                        <div wire:loading.flex wire:target="cover"
                                            class="absolute inset-0 z-20 flex flex-col items-center justify-center bg-slate-950/70 p-2 text-center text-white backdrop-blur-[1px]">
                                            <svg class="h-7 w-7 animate-spin text-kejati-gold" fill="none" viewBox="0 0 24 24" aria-hidden="true">
                                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                            </svg>
                                            <span class="mt-1.5 text-[11px] font-bold tracking-wide text-white">Mengunggah…</span>
                                        </div>
                                    </div>

                                    <div class="w-full flex-1">
                                        <label for="cover-upload" class="flex cursor-pointer items-center justify-between gap-3 rounded-xl border border-stone-200 bg-white px-4 py-3 shadow-sm transition hover:border-kejati hover:bg-emerald-50/30">
                                            <span><span class="block text-xs font-bold text-slate-800">Pilih file sampul</span><span class="mt-0.5 block text-[11px] text-slate-500">JPG, PNG, atau WEBP · maks. 2 MB</span></span>
                                            <span class="text-kejati" aria-hidden="true">Unggah</span>
                                        </label>
                                        <input id="cover-upload" name="cover" wire:model="cover" type="file" accept="image/jpeg,image/png,image/jpg,image/webp" class="sr-only">
                                        <x-input-error :messages="$errors->get('cover')" class="mt-2" />
                                    </div>
                                </div>
                            </div>

                        </div>

                        <!-- Modal Actions Footer -->
                        <div class="border-t border-stone-200 bg-stone-50 px-6 py-4 sm:px-8 flex items-center justify-end gap-3 rounded-b-3xl">
                            <button type="button"
                                wire:click="resetForm"
                                class="rounded-xl border border-stone-300 bg-white px-5 py-2.5 text-sm font-semibold text-slate-700 shadow-sm transition hover:bg-stone-50 hover:text-slate-900 focus-visible:ring-2 focus-visible:ring-kejati-gold focus-visible:ring-offset-2">
                                Batal
                            </button>
                            <button type="submit"
                                wire:loading.attr="disabled"
                                class="inline-flex items-center gap-2 rounded-xl bg-kejati px-6 py-2.5 text-sm font-bold text-white shadow-lg shadow-kejati/20 transition hover:bg-kejati-dark focus-visible:ring-2 focus-visible:ring-kejati-gold focus-visible:ring-offset-2 focus:ring-2 focus:ring-kejati focus:ring-offset-2 disabled:opacity-50">
                                <svg wire:loading wire:target="save" class="h-4 w-4 animate-spin text-white" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                </svg>
                                <span>{{ $editingId ? 'Simpan Perubahan' : 'Simpan Koleksi Buku' }}</span>
                            </button>
                        </div>
                    </form>

                </div>
            </div>
        </div>
    @endif

    <!-- MODAL CEPAT TAMBAH KATEGORI BARU -->
    @if ($showQuickCategoryModal)
        <div x-data x-trap.inert.noscroll="true" @keydown.escape.stop="$wire.closeQuickCategoryModal()" class="fixed inset-0 z-[60] overflow-y-auto" aria-labelledby="modal-quick-category" role="dialog" aria-modal="true">
            <div class="fixed inset-0 bg-slate-950/70 backdrop-blur-sm transition-opacity"
                wire:click="closeQuickCategoryModal"></div>

            <div class="flex min-h-full items-center justify-center p-4 text-center sm:p-6">
                <div class="relative my-8 w-full max-w-md transform overflow-hidden rounded-3xl border border-stone-200/80 bg-white text-left shadow-2xl transition">
                    
                    <div class="bg-gradient-to-r from-kejati-dark to-kejati px-6 py-4 text-white">
                        <div class="flex items-center justify-between">
                            <div>
                                <h3 class="text-lg font-bold text-white" id="modal-quick-category">
                                    Tambah Kategori Baru
                                </h3>
                                <p class="text-xs text-white/80">Kategori akan otomatis terpilih pada form buku.</p>
                            </div>
                            <button aria-label="Tutup form kategori" wire:click="closeQuickCategoryModal"
                                type="button"
                                class="rounded-xl bg-white/10 p-1.5 text-white/80 hover:bg-white/20 hover:text-white transition focus-visible:ring-2 focus-visible:ring-kejati-gold focus-visible:ring-offset-2">
                                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                </svg>
                            </button>
                        </div>
                    </div>

                    <form wire:submit="saveQuickCategory">
                        <div class="p-6 space-y-4">
                            <div>
                                <label for="book-newCategoryName" class="block text-xs font-bold uppercase tracking-wider text-slate-700">
                                    Nama Kategori / Bidang Hukum <span class="text-rose-600">*</span>
                                </label>
                                <input wire:model="newCategoryName" id="book-newCategoryName"
                                    type="text"
                                    placeholder="Contoh: Hukum Acara Perdata"
                                    autofocus
                                    class="mt-1.5 w-full rounded-xl border-stone-300 text-sm focus:border-kejati focus:ring-kejati shadow-sm placeholder:text-slate-400">
                                <x-input-error :messages="$errors->get('newCategoryName')" class="mt-1.5" />
                            </div>
                        </div>

                        <div class="border-t border-stone-200 bg-stone-50 px-6 py-3.5 flex items-center justify-end gap-2.5 rounded-b-3xl">
                            <button type="button"
                                wire:click="closeQuickCategoryModal"
                                class="rounded-xl border border-stone-300 bg-white px-4 py-2 text-xs font-semibold text-slate-700 shadow-sm transition hover:bg-stone-50 focus-visible:ring-2 focus-visible:ring-kejati-gold focus-visible:ring-offset-2">
                                Batal
                            </button>
                            <button type="submit"
                                wire:loading.attr="disabled"
                                class="inline-flex items-center gap-1.5 rounded-xl bg-kejati px-4 py-2 text-xs font-bold text-white shadow-md shadow-kejati/20 transition hover:bg-kejati-dark focus-visible:ring-2 focus-visible:ring-kejati-gold focus-visible:ring-offset-2 disabled:opacity-50">
                                <span>Simpan & Pilih Kategori</span>
                            </button>
                        </div>
                    </form>

                </div>
            </div>
        </div>
    @endif
</div>
