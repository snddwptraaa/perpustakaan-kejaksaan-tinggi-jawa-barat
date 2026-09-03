<div class="space-y-6">
    <!-- PAGE HEADER -->
    <div class="flex flex-col justify-between gap-4 sm:flex-row sm:items-center">
        <div>
            <div class="flex items-center gap-2">
                <span class="inline-flex items-center rounded-md bg-kejati/10 px-2.5 py-0.5 text-xs font-bold uppercase tracking-wider text-kejati">
                    Sirkulasi & Layanan Fisik
                </span>
            </div>
            <h1 class="mt-2 text-2xl font-bold tracking-tight text-slate-900 sm:text-3xl">Peminjaman Buku</h1>
            <p class="mt-1 text-sm text-slate-500">Catat transaksi peminjaman fisik dan konfirmasi pengembalian buku perpustakaan.</p>
        </div>
        <div class="flex flex-wrap gap-3">
            <button wire:click="exportCsv"
                type="button"
                class="inline-flex items-center justify-center gap-2 rounded-xl border border-kejati/30 bg-white px-4 py-3 text-sm font-bold text-kejati shadow-sm transition hover:bg-emerald-50 hover:border-kejati">
                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                </svg>
                Export CSV
            </button>
            <button wire:click="exportXlsx" type="button"
                class="inline-flex items-center justify-center rounded-xl border border-kejati/30 bg-white px-4 py-3 text-sm font-bold text-kejati shadow-sm transition hover:bg-emerald-50">
                Export XLSX
            </button>
            <button wire:click="create"
                type="button"
                class="inline-flex items-center justify-center gap-2 rounded-xl bg-kejati px-5 py-3 text-sm font-bold text-white shadow-lg shadow-kejati/20 transition hover:bg-kejati-dark">
                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                </svg>
                Catat Peminjaman
            </button>
        </div>
    </div>

    <!-- FLASH MESSAGES -->
    @if (session('success'))
        <div class="mt-6 flex items-center gap-3 rounded-2xl border border-emerald-200 bg-emerald-50/90 p-4 text-sm font-medium text-emerald-800 shadow-sm">
            <svg class="h-5 w-5 shrink-0 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
            <span>{{ session('success') }}</span>
        </div>
    @endif

    @if (session('error'))
        <div class="mt-6 flex items-center gap-3 rounded-2xl border border-rose-200 bg-rose-50/90 p-4 text-sm font-medium text-rose-800 shadow-sm">
            <svg class="h-5 w-5 shrink-0 text-rose-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
            <span>{{ session('error') }}</span>
        </div>
    @endif

    <!-- MODAL POPUP FORM PEMINJAMAN BUKU -->
    <div x-data="{ show: @entangle('showForm') }"
        x-cloak
        x-show="show"
        class="fixed inset-0 z-50 overflow-y-auto"
        aria-labelledby="modal-loan-title"
        role="dialog"
        aria-modal="true">

        <!-- Backdrop Overlay -->
        <div x-show="show"
            x-transition:enter="ease-out duration-300"
            x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100"
            x-transition:leave="ease-in duration-200"
            x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0"
            class="fixed inset-0 bg-slate-950/70 backdrop-blur-sm transition-opacity"
            @click="$wire.resetForm()"></div>

        <div class="flex min-h-full items-center justify-center p-4 text-center sm:p-6">
            <!-- Modal Card Container -->
            <div x-show="show"
                x-transition:enter="ease-out duration-300"
                x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                x-transition:leave="ease-in duration-200"
                x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                class="relative w-full max-w-3xl transform overflow-hidden rounded-3xl bg-white text-left shadow-2xl transition-all border border-stone-200 my-8">

                <!-- MODAL HEADER -->
                <div class="flex items-center justify-between border-b border-stone-200/80 bg-gradient-to-r from-stone-50 via-white to-stone-50 px-6 py-5 sm:px-8">
                    <div class="flex items-center gap-3">
                        <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-2xl bg-kejati text-white shadow-md shadow-kejati/20">
                            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                            </svg>
                        </div>
                        <div>
                            <h3 class="text-lg font-bold text-slate-900" id="modal-loan-title">
                                {{ $editingId ? 'Koreksi Data Peminjaman' : 'Catat Peminjaman Buku Baru' }}
                            </h3>
                            <p class="text-xs text-slate-500">
                                {{ $editingId ? 'Buku tidak dapat diganti agar perhitungan stok tetap konsisten.' : 'Stok eksemplar buku fisik akan otomatis berkurang setelah dicatat.' }}
                            </p>
                        </div>
                    </div>
                    <button wire:click="resetForm"
                        type="button"
                        class="rounded-xl p-2 text-slate-400 hover:bg-stone-100 hover:text-slate-700 transition"
                        aria-label="Tutup form">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                <!-- MODAL BODY -->
                <form wire:submit="save" class="p-6 sm:p-8 space-y-6 max-h-[75vh] overflow-y-auto">

                    <!-- 1. SECTION: PEMILIHAN BUKU (SEARCHABLE SELECTION) -->
                    <div class="space-y-3">
                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-700">
                            Buku yang Dipinjam <span class="text-rose-600">*</span>
                        </label>

                        @if ($selectedBook)
                            <!-- SELECTED BOOK CARD -->
                            <div class="relative overflow-hidden rounded-2xl border-2 border-kejati/40 bg-emerald-50/50 p-4 sm:p-5 transition shadow-sm">
                                <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
                                    <div class="flex items-center gap-4">
                                        <div class="h-16 w-12 shrink-0 overflow-hidden rounded-lg bg-emerald-900 flex items-center justify-center text-white shadow-inner">
                                            @if ($selectedBook->cover_image)
                                                <img src="{{ Storage::url($selectedBook->cover_image) }}" alt="Cover" class="h-full w-full object-cover">
                                            @else
                                                <span class="text-lg font-bold text-kejati-gold">▤</span>
                                            @endif
                                        </div>
                                        <div>
                                            <div class="flex items-center gap-2">
                                                <span class="rounded-md bg-kejati/10 px-2 py-0.5 text-[10px] font-bold uppercase tracking-wider text-kejati">
                                                    {{ $selectedBook->category->nama_kategori }}
                                                </span>
                                                <span class="text-xs text-slate-400">•</span>
                                                <span class="text-xs font-semibold text-emerald-800">
                                                    Tersedia: {{ $selectedBook->stok_tersedia }} dari {{ $selectedBook->stok }} eks.
                                                </span>
                                            </div>
                                            <h4 class="text-sm font-bold text-slate-900 mt-1">{{ $selectedBook->judul }}</h4>
                                            <p class="text-xs text-slate-500 mt-0.5">Penulis: {{ $selectedBook->penulis }}</p>
                                            @if ($selectedBook->no_klasifikasi || $selectedBook->lokasi_rak)
                                                <p class="text-[11px] font-mono text-slate-600 mt-1">
                                                    {{ $selectedBook->no_klasifikasi ? 'DDC: ' . $selectedBook->no_klasifikasi : '' }}
                                                    {{ $selectedBook->lokasi_rak ? ' | Rak: ' . $selectedBook->lokasi_rak : '' }}
                                                </p>
                                            @endif
                                        </div>
                                    </div>
                                    @unless ($editingId)
                                        <button wire:click="deselectBook"
                                            type="button"
                                            class="inline-flex items-center gap-1 rounded-xl border border-stone-300 bg-white px-3 py-2 text-xs font-semibold text-slate-700 shadow-sm hover:bg-stone-50 hover:text-rose-600 transition">
                                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                                            </svg>
                                            Ganti Buku
                                        </button>
                                    @endunless
                                </div>
                            </div>
                        @else
                            <!-- BOOK SEARCH & LIST PICKER -->
                            <div class="rounded-2xl border border-stone-200 bg-stone-50/70 p-4 space-y-3">
                                <!-- Search Input -->
                                <div class="relative">
                                    <div class="pointer-events-none absolute inset-y-0 left-3.5 flex items-center text-slate-400">
                                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                                        </svg>
                                    </div>
                                    <input wire:model.live.debounce.250ms="bookSearch"
                                        type="text"
                                        placeholder="Cari judul buku, nama pengarang, ISBN, nomor panggil DDC, atau lokasi rak..."
                                        class="w-full rounded-xl border-stone-300 bg-white py-2.5 pl-10 pr-4 text-sm focus:border-kejati focus:ring-kejati shadow-sm placeholder:text-slate-400">
                                </div>

                                <!-- Available Books Suggestions List / Prompt -->
                                @if (trim($bookSearch) !== '')
                                    <div class="space-y-2 max-h-56 overflow-y-auto pr-1">
                                        @forelse ($availableBooks as $buku)
                                            <div class="flex items-center justify-between gap-3 rounded-xl border border-stone-200/80 bg-white p-3 shadow-xs hover:border-kejati hover:bg-emerald-50/30 transition">
                                                <div class="flex items-center gap-3 min-w-0">
                                                    <div class="h-10 w-8 shrink-0 overflow-hidden rounded bg-stone-100 border border-stone-200 flex items-center justify-center">
                                                        @if ($buku->cover_image)
                                                            <img src="{{ Storage::url($buku->cover_image) }}" alt="Cover" class="h-full w-full object-cover">
                                                        @else
                                                            <span class="text-xs text-slate-400">▤</span>
                                                        @endif
                                                    </div>
                                                    <div class="min-w-0">
                                                        <p class="truncate text-xs font-bold text-slate-800">{{ $buku->judul }}</p>
                                                        <p class="truncate text-[11px] text-slate-500">
                                                            {{ $buku->penulis }} • <span class="text-kejati font-medium">{{ $buku->category->nama_kategori }}</span>
                                                            @if ($buku->lokasi_rak)
                                                                • <span class="font-mono text-slate-600">Rak: {{ $buku->lokasi_rak }}</span>
                                                            @endif
                                                        </p>
                                                    </div>
                                                </div>
                                                <div class="flex items-center gap-2 shrink-0">
                                                    <span class="inline-flex rounded-full bg-emerald-100 px-2 py-0.5 text-[10px] font-bold text-emerald-800">
                                                        Stok: {{ $buku->stok_tersedia }}
                                                    </span>
                                                    <button wire:click="selectBook({{ $buku->id }})"
                                                        type="button"
                                                        class="inline-flex items-center gap-1 rounded-lg bg-kejati px-2.5 py-1.5 text-xs font-bold text-white shadow-xs hover:bg-kejati-dark transition">
                                                        Pilih
                                                    </button>
                                                </div>
                                            </div>
                                        @empty
                                            <div class="py-6 text-center text-xs text-slate-500">
                                                Buku dengan kata kunci "<span class="font-medium text-slate-700">{{ $bookSearch }}</span>" tidak ditemukan atau stok sedang habis.
                                            </div>
                                        @endforelse
                                    </div>
                                @else
                                    <div class="py-3 px-2 text-center text-xs text-slate-500 flex items-center justify-center gap-2">
                                        <svg class="h-4 w-4 text-slate-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                        </svg>
                                        <span>Ketik judul, pengarang, nomor DDC/ISBN/rak di atas untuk mencari dan memilih buku.</span>
                                    </div>
                                @endif
                            </div>
                        @endif
                        <x-input-error :messages="$errors->get('book_id')" class="mt-1" />
                    </div>

                    <!-- 2. SECTION: DATA PEMINJAM -->
                    <div class="space-y-4 pt-2 border-t border-stone-100">
                        <h4 class="text-xs font-bold uppercase tracking-wider text-kejati-dark flex items-center gap-1.5">
                            <svg class="h-4 w-4 text-kejati" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                            </svg>
                            Identitas Peminjam
                        </h4>

                        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                            <div class="sm:col-span-2">
                                <label class="block text-xs font-bold uppercase tracking-wider text-slate-700">Anggota terdaftar (opsional)</label>
                                <select wire:model.live="member_id" class="mt-1.5 w-full rounded-xl border-stone-300 text-sm focus:border-kejati focus:ring-kejati">
                                    <option value="">Peminjam manual / bukan anggota</option>
                                    @foreach ($members as $member)
                                        <option value="{{ $member->id }}">{{ $member->nama }}{{ $member->nip ? ' — '.$member->nip : '' }}</option>
                                    @endforeach
                                </select>
                                <x-input-error :messages="$errors->get('member_id')" class="mt-1.5" />
                            </div>
                            <!-- Nama Peminjam -->
                            <div class="sm:col-span-2">
                                <label class="block text-xs font-bold uppercase tracking-wider text-slate-700">
                                    Nama Lengkap Peminjam <span class="text-rose-600">*</span>
                                </label>
                                <input wire:model="nama_peminjam"
                                    type="text"
                                    placeholder="Contoh: Budi Santoso, S.H."
                                    class="mt-1.5 w-full rounded-xl border-stone-300 text-sm focus:border-kejati focus:ring-kejati shadow-sm placeholder:text-slate-400">
                                <x-input-error :messages="$errors->get('nama_peminjam')" class="mt-1.5" />
                            </div>

                            <!-- NIP / No. Identitas -->
                            <div>
                                <label class="block text-xs font-bold uppercase tracking-wider text-slate-700">
                                    NIP / NRP Pegawai
                                </label>
                                <input wire:model="nip_peminjam"
                                    type="text"
                                    placeholder="Contoh: 19850115 201001 1 002"
                                    class="mt-1.5 w-full rounded-xl border-stone-300 text-sm focus:border-kejati focus:ring-kejati shadow-sm placeholder:text-slate-400">
                                <x-input-error :messages="$errors->get('nip_peminjam')" class="mt-1.5" />
                            </div>

                            <!-- Instansi / Unit Kerja -->
                            <div>
                                <label class="block text-xs font-bold uppercase tracking-wider text-slate-700">
                                    Instansi / Bidang Kerja
                                </label>
                                <input wire:model="instansi_unit"
                                    type="text"
                                    placeholder="Contoh: Bidang Tindak Pidana Umum"
                                    class="mt-1.5 w-full rounded-xl border-stone-300 text-sm focus:border-kejati focus:ring-kejati shadow-sm placeholder:text-slate-400">
                                <x-input-error :messages="$errors->get('instansi_unit')" class="mt-1.5" />
                            </div>
                        </div>
                    </div>

                    <!-- 3. SECTION: TANGGAL & CATATAN -->
                    <div class="space-y-4 pt-2 border-t border-stone-100">
                        <h4 class="text-xs font-bold uppercase tracking-wider text-kejati-dark flex items-center gap-1.5">
                            <svg class="h-4 w-4 text-kejati" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                            </svg>
                            Jangka Waktu Peminjaman & Catatan
                        </h4>

                        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                            <!-- Tanggal Pinjam -->
                            <div>
                                <label class="block text-xs font-bold uppercase tracking-wider text-slate-700">
                                    Tanggal Pinjam <span class="text-rose-600">*</span>
                                </label>
                                <input wire:model.live="tanggal_pinjam"
                                    type="date"
                                    class="mt-1.5 w-full rounded-xl border-stone-300 text-sm focus:border-kejati focus:ring-kejati shadow-sm">
                                <x-input-error :messages="$errors->get('tanggal_pinjam')" class="mt-1.5" />
                            </div>

                            <!-- Tanggal Jatuh Tempo -->
                            <div>
                                <label class="block text-xs font-bold uppercase tracking-wider text-slate-700">
                                    Tanggal Jatuh Tempo (Batas Kembali) <span class="text-rose-600">*</span>
                                </label>
                                <input wire:model="tanggal_jatuh_tempo"
                                    type="date"
                                    class="mt-1.5 w-full rounded-xl border-stone-300 text-sm focus:border-kejati focus:ring-kejati shadow-sm">
                                <x-input-error :messages="$errors->get('tanggal_jatuh_tempo')" class="mt-1.5" />
                            </div>

                            <!-- Catatan Tambahan -->
                            <div class="sm:col-span-2">
                                <label class="block text-xs font-bold uppercase tracking-wider text-slate-700">
                                    Catatan Tambahan (Opsional)
                                </label>
                                <textarea wire:model="catatan"
                                    rows="2"
                                    placeholder="Contoh: Dipinjam untuk keperluan penyusunan berkas perkara..."
                                    class="mt-1.5 w-full rounded-xl border-stone-300 text-sm focus:border-kejati focus:ring-kejati shadow-sm placeholder:text-slate-400"></textarea>
                                <x-input-error :messages="$errors->get('catatan')" class="mt-1.5" />
                            </div>
                        </div>
                    </div>

                    <!-- FOOTER ACTIONS -->
                    <div class="flex items-center justify-end gap-3 pt-4 border-t border-stone-200">
                        <button type="button"
                            wire:click="resetForm"
                            class="rounded-xl border border-stone-300 bg-white px-5 py-2.5 text-xs font-semibold text-slate-700 shadow-sm transition hover:bg-stone-50 focus:outline-none">
                            Batal
                        </button>
                        <button type="submit"
                            wire:loading.attr="disabled"
                            class="inline-flex items-center gap-2 rounded-xl bg-kejati px-6 py-2.5 text-xs font-bold text-white shadow-lg shadow-kejati/20 transition hover:bg-kejati-dark focus:outline-none disabled:opacity-50">
                            <span wire:loading wire:target="save" class="h-4 w-4 animate-spin rounded-full border-2 border-white border-t-transparent"></span>
                            {{ $editingId ? 'Simpan Koreksi' : 'Simpan Transaksi Peminjaman' }}
                        </button>
                    </div>

                </form>

            </div>
        </div>
    </div>

    <!-- TABLE & LIST TRANSAKSI PEMINJAMAN -->
    <div class="mt-8 rounded-2xl border border-stone-200 bg-white shadow-sm overflow-hidden">
        <!-- FILTER BAR -->
        <div class="flex flex-col gap-4 border-b border-stone-100 p-4 sm:flex-row sm:items-center sm:justify-between bg-stone-50/50">
            <div class="relative max-w-md flex-1">
                <span class="pointer-events-none absolute inset-y-0 left-3.5 flex items-center text-slate-400">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                    </svg>
                </span>
                <input wire:model.live.debounce.300ms="search"
                    type="search"
                    placeholder="Cari nama peminjam..."
                    class="w-full rounded-xl border-stone-200 bg-white py-2.5 pl-10 text-sm focus:border-kejati focus:ring-kejati shadow-xs placeholder:text-slate-400">
            </div>
            <div>
                <select wire:model.live="statusFilter"
                    class="rounded-xl border-stone-200 bg-white py-2.5 text-sm focus:border-kejati focus:ring-kejati shadow-xs">
                    <option value="">Semua Status Transaksi</option>
                    <option value="dipinjam">Sedang Dipinjam</option>
                    <option value="terlambat">Terlambat Kembali</option>
                    <option value="dikembalikan">Sudah Dikembalikan</option>
                    <option value="dibatalkan">Dibatalkan</option>
                </select>
            </div>
        </div>

        <!-- TABLE -->
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm">
                <thead class="bg-stone-50 text-xs uppercase tracking-wide text-slate-500 border-b border-stone-200">
                    <tr>
                        <th class="px-6 py-4 font-semibold">Peminjam</th>
                        <th class="px-6 py-4 font-semibold">Buku & Tanggal Pinjam</th>
                        <th class="px-6 py-4 font-semibold">Jatuh Tempo</th>
                        <th class="px-6 py-4 font-semibold text-center">Status</th>
                        <th class="px-6 py-4 text-right font-semibold">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-stone-100">
                    @forelse ($loans as $loan)
                        <tr class="hover:bg-stone-50/80 transition">
                            <td class="px-6 py-4">
                                <p class="font-bold text-slate-900">{{ $loan->nama_peminjam }}</p>
                                <p class="mt-0.5 text-xs text-slate-500">
                                    {{ $loan->nip_peminjam ? 'NIP: ' . $loan->nip_peminjam : '' }}
                                    {{ $loan->nip_peminjam && $loan->instansi_unit ? '•' : '' }}
                                    {{ $loan->instansi_unit ?: '' }}
                                </p>
                            </td>
                            <td class="max-w-xs px-6 py-4">
                                <p class="font-semibold text-slate-800 line-clamp-1">{{ $loan->book->judul }}</p>
                                <p class="mt-0.5 text-xs text-slate-400">
                                    Dipinjam: {{ $loan->tanggal_pinjam->format('d M Y') }}
                                </p>
                            </td>
                            <td class="px-6 py-4 text-slate-600 font-medium">
                                {{ $loan->tanggal_jatuh_tempo->format('d M Y') }}
                            </td>
                            <td class="px-6 py-4 text-center">
                                @if ($loan->current_status === 'dibatalkan')
                                    <span class="inline-flex items-center rounded-full bg-stone-200 px-2.5 py-1 text-xs font-bold text-slate-700">
                                        Dibatalkan
                                    </span>
                                @elseif ($loan->current_status === 'dikembalikan')
                                    <span class="inline-flex items-center rounded-full bg-emerald-100 px-2.5 py-1 text-xs font-bold text-emerald-800">
                                        <span class="mr-1.5 h-1.5 w-1.5 rounded-full bg-emerald-500"></span>
                                        Dikembalikan
                                    </span>
                                @elseif ($loan->current_status === 'terlambat')
                                    <span class="inline-flex items-center rounded-full bg-rose-100 px-2.5 py-1 text-xs font-bold text-rose-800">
                                        <span class="mr-1.5 h-1.5 w-1.5 rounded-full bg-rose-500"></span>
                                        Terlambat
                                    </span>
                                @else
                                    <span class="inline-flex items-center rounded-full bg-blue-100 px-2.5 py-1 text-xs font-bold text-blue-800">
                                        <span class="mr-1.5 h-1.5 w-1.5 rounded-full bg-blue-500"></span>
                                        Dipinjam
                                    </span>
                                @endif
                            </td>
                            <td class="px-6 py-4 text-right">
                                <div class="flex flex-wrap justify-end gap-1">
                                    <button type="button" wire:click="edit({{ $loan->id }})"
                                        class="inline-flex items-center rounded-lg px-3 py-1.5 text-xs font-bold text-kejati hover:bg-emerald-50 transition">
                                        Koreksi
                                    </button>
                                @if (!$loan->tanggal_kembali && !$loan->tanggal_dibatalkan)
                                    @if ($loan->jumlah_perpanjangan < 1)
                                        <button type="button" wire:click="extendLoan({{ $loan->id }})"
                                            wire:confirm="Perpanjang jatuh tempo peminjaman ini selama tujuh hari?"
                                            class="inline-flex items-center rounded-lg px-3 py-1.5 text-xs font-bold text-blue-700 hover:bg-blue-50 transition">
                                            Perpanjang
                                        </button>
                                    @endif
                                    <button type="button"
                                        @click="$dispatch('open-confirm-modal', {
                                            title: 'Pengembalian Buku',
                                            message: 'Catat pengembalian buku \'{{ addslashes($loan->book->judul) }}\' yang dipinjam oleh {{ addslashes($loan->nama_peminjam) }}?',
                                            confirmButtonText: 'Ya, Kembalikan Buku',
                                            type: 'warning',
                                            onConfirm: () => $wire.returnBook({{ $loan->id }})
                                        })"
                                        class="inline-flex items-center gap-1 rounded-lg px-3 py-1.5 text-xs font-bold text-kejati bg-emerald-50 hover:bg-emerald-100 transition focus:outline-none">
                                        <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                        </svg>
                                        Kembalikan
                                    </button>
                                    <button type="button"
                                        @click="$dispatch('open-confirm-modal', {
                                            title: 'Batalkan Peminjaman',
                                            message: 'Batalkan transaksi peminjaman buku \'{{ addslashes($loan->book->judul) }}\'? Stok buku akan dikembalikan.',
                                            confirmButtonText: 'Ya, Batalkan',
                                            type: 'danger',
                                            onConfirm: () => $wire.cancel({{ $loan->id }})
                                        })"
                                        class="inline-flex items-center rounded-lg px-3 py-1.5 text-xs font-bold text-rose-600 hover:bg-rose-50 transition">
                                        Batalkan
                                    </button>
                                @elseif ($loan->tanggal_dibatalkan)
                                    <span class="px-2 py-1.5 text-xs text-slate-400 font-mono">
                                        {{ $loan->tanggal_dibatalkan->format('d M Y') }}
                                    </span>
                                @else
                                    <span class="text-xs text-slate-400 font-mono">
                                        Kembali {{ $loan->tanggal_kembali->format('d M Y') }}
                                    </span>
                                @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-6 py-14 text-center">
                                <div class="mx-auto flex h-12 w-12 items-center justify-center rounded-full bg-stone-100 text-slate-400">
                                    <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                                    </svg>
                                </div>
                                <p class="mt-3 font-semibold text-slate-800">Belum ada transaksi peminjaman</p>
                                <p class="mt-1 text-xs text-slate-500">Klik "Catat Peminjaman" untuk menambahkan peminjaman buku baru.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="border-t border-stone-100 px-6 py-4 bg-stone-50/30">
            {{ $loans->links() }}
        </div>
    </div>
</div>
