<div class="space-y-6">
    <!-- PAGE HEADER -->
    <div class="flex flex-col justify-between gap-4 sm:flex-row sm:items-center">
        <div>
            <div class="flex items-center gap-2">
                <span class="inline-flex items-center rounded-md bg-kejati/10 px-2.5 py-0.5 text-xs font-bold uppercase tracking-wider text-kejati">
                    Keamanan & Pengawasan
                </span>
            </div>
            <h1 class="mt-2 text-2xl font-bold tracking-tight text-slate-900 sm:text-3xl">Audit Log Sistem</h1>
            <p class="mt-1 text-sm text-slate-500">
                Jejak rekam lengkap perubahan data, transaksi peminjaman, dan tindakan administratif petugas perpustakaan.
            </p>
        </div>
    </div>

    <!-- FILTER & SEARCH PANEL -->
    <div class="rounded-2xl border border-stone-200 bg-white p-5 shadow-sm">
        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
            <!-- Search input -->
            <div class="lg:col-span-2">
                <label for="audit-search" class="block text-xs font-semibold uppercase tracking-wider text-slate-600 mb-1.5">
                    Pencarian
                </label>
                <div class="relative">
                    <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3.5 text-slate-400">
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                        </svg>
                    </div>
                    <input id="audit-search"
                        wire:model.live.debounce.300ms="search"
                        type="text"
                        placeholder="Cari petugas, aksi, entitas, ID, atau IP..."
                        class="w-full rounded-xl border-stone-200 bg-stone-50 py-2.5 pl-10 pr-4 text-sm text-slate-900 transition placeholder:text-slate-400 focus:border-kejati focus:bg-white focus:outline-none focus:ring-1 focus:ring-kejati">
                </div>
            </div>

            <!-- Filter Entitas -->
            <div>
                <label for="filter-entitas" class="block text-xs font-semibold uppercase tracking-wider text-slate-600 mb-1.5">
                    Entitas Target
                </label>
                <select id="filter-entitas"
                    wire:model.live="filterEntitas"
                    class="w-full rounded-xl border-stone-200 bg-stone-50 py-2.5 px-3 text-sm text-slate-900 transition focus:border-kejati focus:bg-white focus:outline-none focus:ring-1 focus:ring-kejati">
                    <option value="">Semua Entitas</option>
                    @foreach ($entitasOptions as $key => $label)
                        <option value="{{ $key }}">{{ $label }}</option>
                    @endforeach
                </select>
            </div>

            <!-- Filter Aksi -->
            <div>
                <label for="filter-aksi" class="block text-xs font-semibold uppercase tracking-wider text-slate-600 mb-1.5">
                    Jenis Tindakan
                </label>
                <select id="filter-aksi"
                    wire:model.live="filterAksi"
                    class="w-full rounded-xl border-stone-200 bg-stone-50 py-2.5 px-3 text-sm text-slate-900 transition focus:border-kejati focus:bg-white focus:outline-none focus:ring-1 focus:ring-kejati">
                    <option value="">Semua Tindakan</option>
                    @foreach ($aksiOptions as $key => $label)
                        <option value="{{ $key }}">{{ $label }}</option>
                    @endforeach
                </select>
            </div>
        </div>

        <!-- Date Range & Reset -->
        <div class="mt-4 flex flex-wrap items-center justify-between gap-4 border-t border-stone-100 pt-4">
            <div class="flex flex-wrap items-center gap-3 text-sm">
                <span class="text-xs font-semibold uppercase tracking-wider text-slate-500">Rentang Waktu:</span>
                <input type="date"
                    wire:model.live="startDate"
                    class="rounded-xl border-stone-200 bg-stone-50 py-1.5 px-3 text-xs text-slate-900 focus:border-kejati focus:bg-white focus:ring-1 focus:ring-kejati">
                <span class="text-xs text-slate-400">s.d.</span>
                <input type="date"
                    wire:model.live="endDate"
                    class="rounded-xl border-stone-200 bg-stone-50 py-1.5 px-3 text-xs text-slate-900 focus:border-kejati focus:bg-white focus:ring-1 focus:ring-kejati">
            </div>

            @if ($search || $filterAksi || $filterEntitas || $startDate || $endDate)
                <button type="button"
                    wire:click="resetFilters"
                    class="inline-flex items-center gap-1.5 rounded-xl border border-stone-200 bg-stone-100 px-3 py-1.5 text-xs font-semibold text-slate-600 transition hover:bg-stone-200 hover:text-slate-900">
                    <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                    Reset Filter
                </button>
            @endif
        </div>
    </div>

    <!-- LOGS TABLE CARD -->
    <div class="rounded-2xl border border-stone-200 bg-white shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm">
                <thead class="bg-stone-50 text-xs uppercase tracking-wide text-slate-500 border-b border-stone-200">
                    <tr>
                        <th class="px-6 py-4 font-semibold">Waktu & IP</th>
                        <th class="px-6 py-4 font-semibold">Petugas</th>
                        <th class="px-6 py-4 font-semibold">Tindakan</th>
                        <th class="px-6 py-4 font-semibold">Entitas Target</th>
                        <th class="px-6 py-4 font-semibold">Informasi / Rincian Perubahan</th>
                        <th class="px-6 py-4 text-right font-semibold">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-stone-100">
                    @forelse ($logs as $log)
                        @php
                            $diffs = $log->getDifferences();
                        @endphp
                        <tr class="hover:bg-stone-50/80 transition">
                            <!-- Waktu & IP -->
                            <td class="whitespace-nowrap px-6 py-4">
                                <p class="font-bold text-slate-800">{{ $log->created_at->translatedFormat('d M Y') }}</p>
                                <p class="text-xs text-slate-500 font-mono mt-0.5">
                                    {{ $log->created_at->format('H:i:s') }} WIB
                                </p>
                                <p class="text-[11px] text-slate-400 font-mono mt-0.5">
                                    IP: {{ $log->ip_address ?: '—' }}
                                </p>
                            </td>

                            <!-- Petugas -->
                            <td class="px-6 py-4">
                                <div class="flex items-center gap-2">
                                    <div class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full bg-kejati/10 text-kejati font-bold text-xs">
                                        {{ strtoupper(substr($log->user?->name ?? 'S', 0, 1)) }}
                                    </div>
                                    <div>
                                        <p class="font-semibold text-slate-900 leading-tight">
                                            {{ $log->user?->name ?? 'Sistem / Otomatis' }}
                                        </p>
                                        <p class="text-[11px] text-slate-500">
                                            {{ $log->user ? ucfirst($log->user->role) : 'Internal' }}
                                        </p>
                                    </div>
                                </div>
                            </td>

                            <!-- Tindakan / Aksi -->
                            <td class="px-6 py-4">
                                <span class="inline-flex items-center gap-1.5 rounded-lg border px-2.5 py-1 text-xs font-bold {{ $log->action_badge_color }}">
                                    <span class="h-1.5 w-1.5 rounded-full bg-current"></span>
                                    {{ $log->action_label }}
                                </span>
                            </td>

                            <!-- Entitas Target -->
                            <td class="px-6 py-4">
                                <div class="flex items-center gap-1.5">
                                    <span class="font-semibold text-slate-800">{{ $log->entity_label }}</span>
                                    @if ($log->entitas_id)
                                        <span class="rounded bg-stone-100 px-1.5 py-0.5 font-mono text-[11px] font-bold text-slate-600 border border-stone-200">
                                            #{{ $log->entitas_id }}
                                        </span>
                                    @endif
                                </div>
                                @if ($log->subject_title)
                                    <p class="mt-0.5 text-xs text-slate-500 line-clamp-1 font-medium" title="{{ $log->subject_title }}">
                                        {{ $log->subject_title }}
                                    </p>
                                @endif
                            </td>

                            <!-- Informasi / Ringkasan Perubahan -->
                            <td class="px-6 py-4">
                                <p class="text-xs font-semibold text-slate-800">
                                    {{ $log->summary }}
                                </p>

                                <!-- Quick Diff Pills for Ubah/Koreksi -->
                                @if (in_array($log->aksi, ['ubah', 'koreksi']) && count($diffs) > 0)
                                    <div class="mt-1.5 flex flex-wrap gap-1">
                                        @foreach (array_slice($diffs, 0, 3) as $d)
                                            <span class="inline-flex items-center gap-1 rounded bg-stone-100 px-2 py-0.5 text-[11px] text-slate-700 border border-stone-200">
                                                <span class="font-medium text-slate-500">{{ $d['label'] }}:</span>
                                                <span class="line-through text-rose-600 truncate max-w-[100px]">{{ $d['old_formatted'] }}</span>
                                                <span class="text-slate-400">→</span>
                                                <span class="font-bold text-emerald-700 truncate max-w-[100px]">{{ $d['new_formatted'] }}</span>
                                            </span>
                                        @endforeach
                                        @if (count($diffs) > 3)
                                            <span class="inline-flex items-center rounded bg-stone-100 px-1.5 py-0.5 text-[10px] font-bold text-slate-500">
                                                +{{ count($diffs) - 3 }} kolom lagi
                                            </span>
                                        @endif
                                    </div>
                                @endif
                            </td>

                            <!-- Aksi / Detail Button -->
                            <td class="px-6 py-4 text-right">
                                <button type="button"
                                    wire:click="showDetail({{ $log->id }})"
                                    class="inline-flex items-center gap-1.5 rounded-xl border border-stone-200 bg-white px-3 py-1.5 text-xs font-bold text-kejati shadow-sm transition hover:bg-emerald-50 hover:border-kejati/40">
                                    <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                    </svg>
                                    <span>Lihat Rincian</span>
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-14 text-center">
                                <div class="mx-auto flex h-12 w-12 items-center justify-center rounded-full bg-stone-100 text-slate-400">
                                    <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                    </svg>
                                </div>
                                <p class="mt-3 font-semibold text-slate-800">Belum ada catatan audit log</p>
                                <p class="mt-1 text-xs text-slate-500">
                                    {{ $search || $filterAksi || $filterEntitas || $startDate || $endDate ? 'Tidak ada catatan audit yang cocok dengan filter yang dipilih.' : 'Aktivitas perubahan data oleh petugas akan tercatat secara otomatis di sini.' }}
                                </p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="border-t border-stone-100 px-6 py-4 bg-stone-50/40">
            {{ $logs->links() }}
        </div>
    </div>

    <!-- MODAL DETAIL PERUBAHAN AUDIT LOG -->
    @if ($showDetailModal && $selectedLog)
        <div class="fixed inset-0 z-50 overflow-y-auto" role="dialog" aria-modal="true" aria-labelledby="modal-audit-title">
            <!-- Backdrop -->
            <div class="fixed inset-0 bg-slate-950/60 backdrop-blur-sm transition-opacity"
                wire:click="closeDetail"></div>

            <div class="flex min-h-full items-center justify-center p-4 text-center sm:p-6">
                <div class="relative w-full max-w-3xl transform overflow-hidden rounded-3xl bg-white text-left shadow-2xl transition-all border border-stone-200/80 my-8">
                    <!-- Modal Header -->
                    <div class="bg-gradient-to-r from-kejati-dark to-kejati px-6 py-5 text-white">
                        <div class="flex items-start justify-between gap-4">
                            <div>
                                <div class="flex items-center gap-2">
                                    <span class="inline-flex items-center rounded-md bg-kejati-gold/20 px-2.5 py-0.5 text-xs font-bold uppercase tracking-wider text-kejati-gold">
                                        Audit #{{ $selectedLog->id }} • {{ $selectedLog->entity_label }}
                                    </span>
                                    <span class="inline-flex items-center rounded-md bg-white/20 px-2 py-0.5 text-xs font-semibold text-white">
                                        {{ $selectedLog->action_label }}
                                    </span>
                                </div>
                                <h3 class="mt-2 text-xl font-bold text-white" id="modal-audit-title">
                                    {{ $selectedLog->subject_title ? $selectedLog->subject_title : 'Rincian Aktivitas ' . $selectedLog->action_label }}
                                </h3>
                                <p class="text-xs text-white/80 mt-0.5">
                                    {{ $selectedLog->summary }}
                                </p>
                            </div>
                            <button wire:click="closeDetail"
                                type="button"
                                class="rounded-xl bg-white/10 p-2 text-white/80 hover:bg-white/20 hover:text-white transition focus:outline-none"
                                aria-label="Tutup rincian">
                                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                </svg>
                            </button>
                        </div>
                    </div>

                    <!-- Meta Information Grid -->
                    <div class="border-b border-stone-200 bg-stone-50/80 px-6 py-3.5 text-xs">
                        <div class="grid grid-cols-2 gap-3 sm:grid-cols-4">
                            <div>
                                <span class="block font-semibold uppercase tracking-wider text-slate-400 text-[10px]">Waktu Pencatatan</span>
                                <span class="font-bold text-slate-800">{{ $selectedLog->created_at->translatedFormat('d M Y H:i:s') }} WIB</span>
                            </div>
                            <div>
                                <span class="block font-semibold uppercase tracking-wider text-slate-400 text-[10px]">Petugas Pelaksana</span>
                                <span class="font-bold text-slate-800">{{ $selectedLog->user?->name ?? 'Sistem' }}</span>
                            </div>
                            <div>
                                <span class="block font-semibold uppercase tracking-wider text-slate-400 text-[10px]">Entitas & ID</span>
                                <span class="font-bold text-slate-800 font-mono">{{ $selectedLog->entitas }} #{{ $selectedLog->entitas_id ?: '—' }}</span>
                            </div>
                            <div>
                                <span class="block font-semibold uppercase tracking-wider text-slate-400 text-[10px]">Alamat IP</span>
                                <span class="font-bold text-slate-800 font-mono">{{ $selectedLog->ip_address ?: '—' }}</span>
                            </div>
                        </div>
                    </div>

                    <!-- Modal Tab Navigation -->
                    <div class="border-b border-stone-200 px-6">
                        <nav class="flex gap-4 -mb-px" aria-label="Tabs">
                            <button type="button"
                                wire:click="$set('activeDetailTab', 'diff')"
                                class="border-b-2 py-3 text-xs font-bold transition {{ $activeDetailTab === 'diff' ? 'border-kejati text-kejati' : 'border-transparent text-slate-500 hover:text-slate-800 hover:border-stone-300' }}">
                                Perubahan Nilai (Diff)
                            </button>
                            <button type="button"
                                wire:click="$set('activeDetailTab', 'semua')"
                                class="border-b-2 py-3 text-xs font-bold transition {{ $activeDetailTab === 'semua' ? 'border-kejati text-kejati' : 'border-transparent text-slate-500 hover:text-slate-800 hover:border-stone-300' }}">
                                Data Lengkap Snapshot
                            </button>
                            <button type="button"
                                wire:click="$set('activeDetailTab', 'json')"
                                class="border-b-2 py-3 text-xs font-bold transition {{ $activeDetailTab === 'json' ? 'border-kejati text-kejati' : 'border-transparent text-slate-500 hover:text-slate-800 hover:border-stone-300' }}">
                                Data Mentah JSON
                            </button>
                        </nav>
                    </div>

                    <!-- Modal Body -->
                    <div class="p-6 max-h-[60vh] overflow-y-auto space-y-4">
                        @if ($activeDetailTab === 'diff')
                            @php
                                $modalDiffs = $selectedLog->getDifferences();
                            @endphp

                            <!-- Case 1: Ubah / Koreksi / Ada Perbedaan Field -->
                            @if (count($modalDiffs) > 0)
                                <div>
                                    <div class="flex items-center justify-between mb-3">
                                        <h4 class="text-xs font-bold uppercase tracking-wider text-slate-700">
                                            Perbandingan Nilai Sebelum & Sesudah ({{ count($modalDiffs) }} atribut berubah)
                                        </h4>
                                    </div>
                                    <div class="overflow-hidden rounded-xl border border-stone-200">
                                        <table class="w-full text-left text-xs">
                                            <thead class="bg-stone-100 text-slate-600 font-semibold border-b border-stone-200">
                                                <tr>
                                                    <th class="px-4 py-2.5 w-1/3">Kolom / Atribut</th>
                                                    <th class="px-4 py-2.5 w-1/3 text-rose-700 bg-rose-50/50">Sebelum Diubah</th>
                                                    <th class="px-4 py-2.5 w-1/3 text-emerald-800 bg-emerald-50/50">Setelah Diubah</th>
                                                </tr>
                                            </thead>
                                            <tbody class="divide-y divide-stone-200">
                                                @foreach ($modalDiffs as $d)
                                                    <tr class="hover:bg-stone-50/50 transition">
                                                        <td class="px-4 py-3 font-semibold text-slate-800">
                                                            {{ $d['label'] }}
                                                            <span class="block text-[10px] font-mono text-slate-400 font-normal">{{ $d['key'] }}</span>
                                                        </td>
                                                        <td class="px-4 py-3 bg-rose-50/30 text-rose-800 font-medium">
                                                            <span class="inline-block line-through">{{ $d['old_formatted'] }}</span>
                                                        </td>
                                                        <td class="px-4 py-3 bg-emerald-50/40 text-emerald-900 font-bold">
                                                            {{ $d['new_formatted'] }}
                                                        </td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            @elseif ($selectedLog->aksi === 'buat' || ($selectedLog->aksi === 'pinjam' && ! $selectedLog->sebelum))
                                <!-- Case 2: Penambahan Data Baru -->
                                <div>
                                    <div class="mb-3">
                                        <h4 class="text-xs font-bold uppercase tracking-wider text-emerald-800">
                                            Data Awal Yang Dimasukkan (Baru Dibuat)
                                        </h4>
                                    </div>
                                    <div class="overflow-hidden rounded-xl border border-stone-200">
                                        <table class="w-full text-left text-xs">
                                            <thead class="bg-stone-100 text-slate-600 font-semibold border-b border-stone-200">
                                                <tr>
                                                    <th class="px-4 py-2.5 w-1/3">Kolom / Atribut</th>
                                                    <th class="px-4 py-2.5 w-2/3 text-emerald-800 bg-emerald-50/50">Nilai</th>
                                                </tr>
                                            </thead>
                                            <tbody class="divide-y divide-stone-200">
                                                @php
                                                    $payload = $selectedLog->sesudah ?: [];
                                                    $ignored = ['id', 'created_at', 'updated_at', 'password', 'remember_token'];
                                                @endphp
                                                @foreach ($payload as $k => $val)
                                                    @continue(in_array($k, $ignored, true))
                                                    <tr class="hover:bg-stone-50/50 transition">
                                                        <td class="px-4 py-2.5 font-semibold text-slate-800">
                                                            {{ \App\Models\AuditLog::getFieldLabel($k) }}
                                                            <span class="block text-[10px] font-mono text-slate-400 font-normal">{{ $k }}</span>
                                                        </td>
                                                        <td class="px-4 py-2.5 font-medium text-slate-900">
                                                            {{ \App\Models\AuditLog::formatFieldValue($k, $val) }}
                                                        </td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            @elseif ($selectedLog->aksi === 'hapus')
                                <!-- Case 3: Data Yang Dihapus -->
                                <div>
                                    <div class="mb-3">
                                        <h4 class="text-xs font-bold uppercase tracking-wider text-rose-800">
                                            Data Terakhir Sebelum Dihapus
                                        </h4>
                                    </div>
                                    <div class="overflow-hidden rounded-xl border border-rose-200 bg-rose-50/20">
                                        <table class="w-full text-left text-xs">
                                            <thead class="bg-rose-100/60 text-rose-900 font-semibold border-b border-rose-200">
                                                <tr>
                                                    <th class="px-4 py-2.5 w-1/3">Kolom / Atribut</th>
                                                    <th class="px-4 py-2.5 w-2/3 text-rose-900">Nilai Sebelum Dihapus</th>
                                                </tr>
                                            </thead>
                                            <tbody class="divide-y divide-rose-100">
                                                @php
                                                    $payload = $selectedLog->sebelum ?: [];
                                                    $ignored = ['id', 'created_at', 'updated_at', 'password', 'remember_token'];
                                                @endphp
                                                @foreach ($payload as $k => $val)
                                                    @continue(in_array($k, $ignored, true))
                                                    <tr>
                                                        <td class="px-4 py-2.5 font-semibold text-slate-800">
                                                            {{ \App\Models\AuditLog::getFieldLabel($k) }}
                                                            <span class="block text-[10px] font-mono text-slate-400 font-normal">{{ $k }}</span>
                                                        </td>
                                                        <td class="px-4 py-2.5 font-medium text-rose-900">
                                                            {{ \App\Models\AuditLog::formatFieldValue($k, $val) }}
                                                        </td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            @else
                                <!-- Case 4: Tidak Ada Perbedaan Kolom Utama -->
                                <div class="rounded-xl border border-stone-200 bg-stone-50 p-4 text-center text-xs text-slate-600">
                                    <p class="font-semibold text-slate-800">Tidak ada perubahan nilai kolom yang signifikan pada tindakan ini.</p>
                                    <p class="mt-1 text-slate-500">Tindakan ini mungkin merupakan perubahan status operasional atau pembaruan tanpa modifikasi isi.</p>
                                </div>
                            @endif

                            <!-- Metadata Section (if any) -->
                            @if (! empty($selectedLog->metadata))
                                <div class="mt-4 rounded-xl border border-stone-200 bg-stone-50/60 p-4">
                                    <h4 class="text-xs font-bold uppercase tracking-wider text-slate-700 mb-2">
                                        Informasi Konteks / Metadata Tambahan
                                    </h4>
                                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 text-xs">
                                        @foreach ($selectedLog->metadata as $mk => $mv)
                                            <div class="rounded-lg bg-white p-2.5 border border-stone-200/80">
                                                <span class="block text-[10px] font-semibold text-slate-400 uppercase tracking-wider">
                                                    {{ \App\Models\AuditLog::getFieldLabel($mk) }}
                                                </span>
                                                <span class="font-bold text-slate-800">
                                                    {{ is_array($mv) ? json_encode($mv) : $mv }}
                                                </span>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            @endif
                        @elseif ($activeDetailTab === 'semua')
                            <!-- Tab: Snapshot Lengkap Semua Kolom -->
                            <div>
                                <h4 class="text-xs font-bold uppercase tracking-wider text-slate-700 mb-3">
                                    Snapshot Nilai Lengkap ({{ $selectedLog->sesudah ? 'Kondisi Akhir' : 'Kondisi Sebelum' }})
                                </h4>
                                <div class="overflow-hidden rounded-xl border border-stone-200">
                                    <table class="w-full text-left text-xs">
                                        <thead class="bg-stone-100 text-slate-600 font-semibold border-b border-stone-200">
                                            <tr>
                                                <th class="px-4 py-2.5 w-1/3">Atribut</th>
                                                <th class="px-4 py-2.5 w-2/3">Nilai</th>
                                            </tr>
                                        </thead>
                                        <tbody class="divide-y divide-stone-200">
                                            @php
                                                $snapshot = $selectedLog->sesudah ?: ($selectedLog->sebelum ?: []);
                                            @endphp
                                            @forelse ($snapshot as $sk => $sval)
                                                <tr class="hover:bg-stone-50/50">
                                                    <td class="px-4 py-2.5 font-semibold text-slate-800">
                                                        {{ \App\Models\AuditLog::getFieldLabel($sk) }}
                                                        <span class="block text-[10px] font-mono text-slate-400 font-normal">{{ $sk }}</span>
                                                    </td>
                                                    <td class="px-4 py-2.5 font-mono text-slate-800 break-all">
                                                        {{ \App\Models\AuditLog::formatFieldValue($sk, $sval) }}
                                                    </td>
                                                </tr>
                                            @empty
                                                <tr>
                                                    <td colspan="2" class="px-4 py-6 text-center text-slate-500">
                                                        Tidak ada data snapshot tersimpan.
                                                    </td>
                                                </tr>
                                            @endforelse
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        @elseif ($activeDetailTab === 'json')
                            <!-- Tab: Raw JSON Payload -->
                            <div class="space-y-3">
                                <div>
                                    <span class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1">
                                        Payload Sebelum (sebelum)
                                    </span>
                                    <pre class="rounded-xl bg-stone-900 p-4 text-xs font-mono text-emerald-400 overflow-x-auto max-h-48">{{ json_encode($selectedLog->sebelum, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) ?: 'null' }}</pre>
                                </div>
                                <div>
                                    <span class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1">
                                        Payload Sesudah (sesudah)
                                    </span>
                                    <pre class="rounded-xl bg-stone-900 p-4 text-xs font-mono text-emerald-400 overflow-x-auto max-h-48">{{ json_encode($selectedLog->sesudah, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) ?: 'null' }}</pre>
                                </div>
                                @if (! empty($selectedLog->metadata))
                                    <div>
                                        <span class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1">
                                            Metadata Tambahan (metadata)
                                        </span>
                                        <pre class="rounded-xl bg-stone-900 p-4 text-xs font-mono text-amber-400 overflow-x-auto max-h-36">{{ json_encode($selectedLog->metadata, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>
                                    </div>
                                @endif
                            </div>
                        @endif
                    </div>

                    <!-- Modal Footer -->
                    <div class="border-t border-stone-200 bg-stone-50 px-6 py-4 flex justify-end">
                        <button type="button"
                            wire:click="closeDetail"
                            class="rounded-xl bg-stone-200 px-5 py-2.5 text-xs font-bold text-slate-800 transition hover:bg-stone-300 focus:outline-none">
                            Tutup Rincian
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>

