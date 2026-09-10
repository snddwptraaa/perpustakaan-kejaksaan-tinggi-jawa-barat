<div class="space-y-6">
    <!-- Top Action Bar -->
    <div class="flex flex-col justify-between gap-4 sm:flex-row sm:items-center">
        <div>
            <h1 class="page-title mt-0">Kategori buku</h1>
            <p class="mt-1 text-sm text-slate-500">Kelola bidang hukum dan kategori untuk klasifikasi buku perpustakaan.</p>
        </div>
        <div>
            <button wire:click="create"
                type="button"
                class="btn-primary">
                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4" />
                </svg>
                <span>Tambah kategori</span>
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

    <!-- Category Data Table Card -->
    <div class="rounded-2xl border border-stone-200 bg-white shadow-sm">
        <!-- Search & Info Bar -->
        <div class="border-b border-stone-100 p-4 sm:p-5">
            <div class="grid gap-4 sm:grid-cols-[minmax(15rem,1fr)_minmax(13rem,18rem)] sm:items-end">
                <div class="min-w-0">
                    <label for="category-search" class="mb-1.5 block text-xs font-semibold text-slate-700">Cari kategori</label>
                    <div class="relative">
                        <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3.5 text-slate-400">
                            <svg aria-hidden="true" class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                            </svg>
                        </div>
                        <input id="category-search" name="category-search" wire:model.live.debounce.300ms="search"
                            type="search"
                            autocomplete="off"
                            placeholder="Cari nama kategori…"
                            class="w-full rounded-xl border-stone-200 bg-stone-50 py-2.5 pl-10 pr-4 text-sm text-slate-900 transition placeholder:text-slate-400 focus:border-kejati focus:bg-white focus:outline-none focus:ring-1 focus:ring-kejati">
                    </div>
                </div>
                <div class="min-w-0">
                    <label for="category-sort" class="mb-1.5 block text-xs font-semibold text-slate-700">Urutkan</label>
                    <x-custom-select wire:model.live="sort" id="category-sort" wire:key="category-sort" :options="[
                        'name_asc' => 'Nama A–Z',
                        'name_desc' => 'Nama Z–A',
                        'books_desc' => 'Jumlah buku terbanyak',
                    ]" :searchable="false" />
                </div>
            </div>
            <div class="mt-4 flex flex-wrap items-center justify-between gap-3 border-t border-stone-100 pt-4">
                <p role="status" aria-live="polite" aria-atomic="true" class="text-xs font-medium text-slate-600">
                    Ditemukan <span class="font-bold text-slate-900 tabular-nums">{{ number_format($categories->total(), 0, ',', '.') }}</span> kategori
                </p>
                @if ($search !== '' || $sort !== 'name_asc')
                    <button type="button" wire:click="resetFilters" wire:loading.attr="disabled" class="inline-flex min-h-10 items-center gap-2 rounded-lg px-3 text-xs font-bold text-kejati transition hover:bg-kejati/10 disabled:opacity-50">
                        Hapus filter <span aria-hidden="true">×</span>
                    </button>
                @endif
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm">
                <thead class="bg-stone-50/80 text-xs font-semibold uppercase tracking-wider text-slate-500">
                    <tr>
                        <th class="px-6 py-3.5">Nama Bidang / Kategori</th>
                        <th class="px-6 py-3.5 text-center">Jumlah Buku Terdaftar</th>
                        <th class="px-6 py-3.5 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-stone-100">
                    @forelse ($categories as $category)
                        <tr class="transition hover:bg-stone-50/70">
                            <td class="px-6 py-4">
                                <span class="font-bold text-slate-900">{{ $category->nama_kategori }}</span>
                            </td>
                            <td class="px-6 py-4 text-center">
                                <span class="inline-flex items-center rounded-full bg-stone-100 px-2.5 py-1 text-xs font-semibold text-slate-700">
                                    {{ $category->books_count }} judul
                                </span>
                            </td>
                            <td class="px-6 py-4 text-right">
                                <div class="inline-flex items-center gap-2">
                                    <a href="{{ route('admin.books', ['category' => $category->id]) }}" wire:navigate
                                        class="inline-flex items-center gap-1 rounded-lg px-2.5 py-1.5 text-xs font-bold text-slate-700 transition hover:bg-stone-100 hover:text-kejati focus-visible:ring-2 focus-visible:ring-kejati-gold focus-visible:ring-offset-2">
                                        Lihat buku
                                        <svg aria-hidden="true" class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m9 18 6-6-6-6" /></svg>
                                    </a>
                                    <button wire:click="edit({{ $category->id }})"
                                        type="button"
                                        class="inline-flex items-center gap-1 rounded-lg px-2.5 py-1.5 text-xs font-bold text-kejati transition hover:bg-kejati/10 focus:outline-none">
                                        <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                        </svg>
                                        Edit
                                    </button>
                                    <button type="button"
                                        @click="$dispatch('open-confirm-modal', {
                                            title: 'Hapus Kategori Buku',
                                            message: 'Apakah Anda yakin ingin menghapus kategori \'{{ addslashes($category->nama_kategori) }}\'?',
                                            confirmButtonText: 'Ya, Hapus Kategori',
                                            type: 'danger',
                                            onConfirm: () => $wire.delete({{ $category->id }})
                                        })"
                                        class="inline-flex items-center gap-1 rounded-lg px-2.5 py-1.5 text-xs font-bold text-rose-600 transition hover:bg-rose-50 focus:outline-none">
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
                            <td colspan="3" class="px-6 py-14 text-center">
                                <p class="font-semibold text-slate-800">Tidak ada kategori ditemukan</p>
                                <p class="mt-1 text-xs text-slate-500">Silakan tambahkan kategori buku baru.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($categories->hasPages())
            <div class="border-t border-stone-100 px-6 py-4">
                {{ $categories->links() }}
            </div>
        @endif
    </div>

    <!-- MODAL POPUP FORM TAMBAH / EDIT KATEGORI -->
    @if ($showForm)
        <div x-data x-trap.inert.noscroll="true" @keydown.escape.stop="$wire.resetForm()" class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-category-title" role="dialog" aria-modal="true">
            <div class="fixed inset-0 bg-slate-950/60 backdrop-blur-sm transition-opacity"
                wire:click="resetForm"></div>

            <div class="flex min-h-full items-center justify-center p-4 text-center sm:p-6">
                <div class="relative my-8 w-full max-w-lg transform overflow-hidden rounded-3xl border border-stone-200/80 bg-white text-left shadow-2xl transition">
                    
                    <!-- Header -->
                    <div class="bg-kejati-dark px-6 py-5 text-white">
                        <div class="flex items-center justify-between">
                            <div>
                                <h3 class="text-xl font-bold text-white" id="modal-category-title">
                                    {{ $editingId ? 'Edit kategori' : 'Tambah kategori' }}
                                </h3>
                            </div>
                            <button wire:click="resetForm"
                                type="button"
                                class="rounded-xl bg-white/10 p-2 text-white/80 transition hover:bg-white/20 hover:text-white"
                                aria-label="Tutup form kategori">
                                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                </svg>
                            </button>
                        </div>
                    </div>

                    <!-- Form Body -->
                    <form wire:submit="save">
                        <div class="p-6 space-y-4">
                            <div>
                                <label for="category-name" class="block text-xs font-bold uppercase tracking-wider text-slate-700">
                                    Nama Kategori / Bidang Hukum <span class="text-rose-600">*</span>
                                </label>
                                <input id="category-name" x-ref="name" x-init="$nextTick(() => $refs.name.focus())" wire:model="nama_kategori"
                                    type="text"
                                    placeholder="Contoh: Hukum Pidana Khusus / Tipikor"
                                    class="mt-1.5 w-full rounded-xl border-stone-300 text-sm focus:border-kejati focus:ring-kejati shadow-sm placeholder:text-slate-400">
                                <p class="mt-1 text-[11px] text-slate-500">Kategori digunakan untuk pengelompokan pada katalog dan pencarian.</p>
                                <x-input-error :messages="$errors->get('nama_kategori')" class="mt-1.5" />
                            </div>
                        </div>

                        <!-- Footer Actions -->
                        <div class="border-t border-stone-200 bg-stone-50 px-6 py-4 flex items-center justify-end gap-3 rounded-b-3xl">
                            <button type="button"
                                wire:click="resetForm"
                                class="rounded-xl border border-stone-300 bg-white px-4 py-2 text-sm font-semibold text-slate-700 shadow-sm transition hover:bg-stone-50 focus:outline-none">
                                Batal
                            </button>
                            <button type="submit"
                                wire:loading.attr="disabled"
                                class="inline-flex items-center gap-2 rounded-xl bg-kejati px-5 py-2 text-sm font-bold text-white shadow-lg shadow-kejati/20 transition hover:bg-kejati-dark focus:outline-none disabled:opacity-50">
                                <span>{{ $editingId ? 'Simpan Perubahan' : 'Simpan Kategori' }}</span>
                            </button>
                        </div>
                    </form>

                </div>
            </div>
        </div>
    @endif
</div>
