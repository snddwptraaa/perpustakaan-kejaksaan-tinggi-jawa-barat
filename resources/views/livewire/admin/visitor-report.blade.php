<div class="space-y-6">
    <!-- Top Action Bar -->
    <div class="flex flex-col justify-between gap-4 sm:flex-row sm:items-center">
        <div>
            <div class="flex items-center gap-2">
                <span class="inline-flex items-center rounded-md bg-kejati/10 px-2.5 py-0.5 text-xs font-bold uppercase tracking-wider text-kejati">
                    Buku Tamu Digital
                </span>
            </div>
            <h1 class="mt-2 text-2xl font-bold tracking-tight text-slate-900 sm:text-3xl">Data Pengunjung</h1>
            <p class="mt-1 text-sm text-slate-500">Rekapitulasi dan riwayat kunjungan tamu umum serta pegawai Kejaksaan.</p>
        </div>
        <div class="flex flex-wrap gap-2">
            <button wire:click="exportPdf"
                class="inline-flex items-center justify-center rounded-xl border border-kejati/30 bg-white px-4 py-2.5 text-sm font-bold text-kejati shadow-sm transition hover:bg-emerald-50">
                Export PDF
            </button>
            <button wire:click="exportCsv"
                class="inline-flex items-center justify-center gap-2 rounded-xl bg-kejati px-4 py-2.5 text-sm font-bold text-white shadow-sm transition hover:bg-kejati-dark">
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round"
                        d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.5V16.5M16.5 12L12 16.5m0 0L7.5 12m4.5 4.5V3" />
                </svg>
                <span>Export CSV</span>
            </button>
        </div>
    </div>

    <!-- Ringkasan Statistik Singkat -->
    <div class="mt-6 grid grid-cols-1 gap-4 sm:grid-cols-3">
        <div class="rounded-2xl border border-stone-200 bg-white p-5 shadow-sm">
            <p class="text-xs font-semibold uppercase tracking-wider text-slate-500">Total Kunjungan</p>
            <p class="mt-1 text-2xl font-bold text-slate-900">{{ number_format($totalVisitors) }}</p>
        </div>
        <div class="rounded-2xl border border-emerald-200 bg-emerald-50/50 p-5 shadow-sm">
            <p class="text-xs font-semibold uppercase tracking-wider text-kejati-dark">Pegawai Kejaksaan</p>
            <p class="mt-1 text-2xl font-bold text-kejati">{{ number_format($totalPegawai) }}</p>
        </div>
        <div class="rounded-2xl border border-stone-200 bg-stone-50/70 p-5 shadow-sm">
            <p class="text-xs font-semibold uppercase tracking-wider text-slate-500">Tamu / Umum</p>
            <p class="mt-1 text-2xl font-bold text-slate-800">{{ number_format($totalUmum) }}</p>
        </div>
    </div>

    <!-- Filter & Tabel -->
    <div class="mt-6 rounded-2xl border border-stone-200 bg-white shadow-sm">
        <div class="flex flex-col gap-3 border-b border-stone-100 p-4 sm:flex-row sm:items-center sm:justify-between">
            <div class="flex flex-1 flex-col gap-3 sm:flex-row sm:items-center">
                <!-- Search Input -->
                <div class="relative max-w-md flex-1">
                    <span class="pointer-events-none absolute inset-y-0 left-4 flex items-center text-slate-400">
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                        </svg>
                    </span>
                    <input wire:model.live.debounce.300ms="search" type="search"
                        placeholder="Cari nama, NIP, atau instansi…"
                        class="w-full rounded-xl border-stone-200 bg-stone-50 py-2.5 pl-11 text-sm focus:border-kejati focus:bg-white focus:ring-kejati">
                </div>

                <!-- Filter Kategori -->
                <select wire:model.live="kategori"
                    class="rounded-xl border-stone-200 bg-stone-50 py-2.5 text-sm focus:border-kejati focus:bg-white focus:ring-kejati">
                    <option value="">Semua Kategori</option>
                    <option value="pegawai">Pegawai Kejaksaan</option>
                    <option value="umum">Tamu / Umum</option>
                </select>
            </div>

            <!-- Filter Tanggal -->
            <div>
                <input wire:model.live="date" type="date"
                    class="rounded-xl border-stone-200 bg-stone-50 py-2.5 text-sm focus:border-kejati focus:bg-white focus:ring-kejati">
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm">
                <thead class="bg-stone-50 text-xs uppercase tracking-wider text-slate-500">
                    <tr>
                        <th class="px-6 py-4 font-semibold">Kategori</th>
                        <th class="px-6 py-4 font-semibold">Nama Pengunjung</th>
                        <th class="px-6 py-4 font-semibold">Instansi / Unit Kerja</th>
                        <th class="px-6 py-4 font-semibold">Keperluan</th>
                        <th class="px-6 py-4 font-semibold">Waktu Kunjungan</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-stone-100">
                    @forelse ($visitors as $visitor)
                        <tr class="transition hover:bg-stone-50/70">
                            <!-- Kategori Badge -->
                            <td class="px-6 py-4">
                                @if ($visitor->kategori === 'pegawai')
                                    <span
                                        class="inline-flex items-center gap-1.5 rounded-full border border-emerald-200 bg-emerald-50 px-2.5 py-1 text-xs font-semibold text-kejati-dark">
                                        <span class="h-1.5 w-1.5 rounded-full bg-kejati"></span>
                                        Pegawai
                                    </span>
                                @else
                                    <span
                                        class="inline-flex items-center gap-1.5 rounded-full border border-stone-200 bg-stone-100 px-2.5 py-1 text-xs font-semibold text-slate-700">
                                        <span class="h-1.5 w-1.5 rounded-full bg-slate-400"></span>
                                        Umum
                                    </span>
                                @endif
                            </td>

                            <!-- Nama & NIP / Kontak -->
                            <td class="px-6 py-4">
                                <p class="font-semibold text-slate-900">{{ $visitor->nama }}</p>
                                <div class="mt-1 flex flex-wrap items-center gap-x-2 text-xs text-slate-500">
                                    @if ($visitor->nip)
                                        <span class="font-mono font-medium text-emerald-800">NIP: {{ $visitor->nip }}</span>
                                    @endif
                                    @if ($visitor->no_hp)
                                        <span>· {{ $visitor->no_hp }}</span>
                                    @endif
                                </div>
                            </td>

                            <!-- Instansi / Unit Kerja -->
                            <td class="px-6 py-4 font-medium text-slate-700">
                                {{ $visitor->instansi_unit }}
                            </td>

                            <!-- Keperluan -->
                            <td class="px-6 py-4 text-slate-600">
                                {{ $visitor->keperluan }}
                            </td>

                            <!-- Waktu Kunjungan -->
                            <td class="px-6 py-4 whitespace-nowrap text-slate-500 text-xs">
                                <p class="font-medium text-slate-700">{{ $visitor->created_at->format('d M Y') }}</p>
                                <p class="text-slate-400">{{ $visitor->created_at->format('H:i') }} WIB</p>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-6 py-14 text-center text-slate-500">
                                <p class="font-medium">Belum ada data kunjungan ditemukan.</p>
                                <p class="mt-1 text-xs text-slate-400">Ganti kata kunci pencarian atau filter kategori untuk
                                    melihat data lain.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="border-t border-stone-100 px-6 py-4">
            {{ $visitors->links() }}
        </div>
    </div>
</div>
