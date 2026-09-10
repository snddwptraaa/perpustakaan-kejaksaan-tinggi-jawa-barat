<div class="page-shell">
    <div class="flex flex-col justify-between gap-4 sm:flex-row sm:items-center">
        <div>
            <h1 class="page-title mt-0">Anggota perpustakaan</h1>
            <p class="page-description">Kelola anggota yang dapat dipilih saat pencatatan peminjaman.</p>
        </div>
        <div class="flex flex-wrap items-center gap-2.5">
            <div class="relative" x-data="{ exportOpen: false }" @click.outside="exportOpen = false" @keydown.escape.window="exportOpen = false">
                <button type="button" class="btn-secondary" @click="exportOpen = !exportOpen; if (exportOpen) $nextTick(() => $refs.firstExport.focus())"
                    :aria-expanded="exportOpen" aria-haspopup="menu" aria-controls="member-export-menu">
                    <svg aria-hidden="true" class="h-4 w-4 text-kejati" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v12m0 0l-4-4m4 4l4-4M5 19h14" />
                    </svg>
                    <span>Ekspor</span>
                    <svg aria-hidden="true" class="h-4 w-4 transition" :class="exportOpen && 'rotate-180'" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m6 9 6 6 6-6" /></svg>
                </button>
                <div id="member-export-menu" role="menu" x-cloak x-show="exportOpen" x-transition.origin.top.right
                    @keydown.arrow-down.prevent="$focus.wrap().next()" @keydown.arrow-up.prevent="$focus.wrap().previous()"
                    class="select-panel left-auto right-0 w-48">
                    <button x-ref="firstExport" role="menuitem" type="button" wire:click="exportPdf" @click="exportOpen = false" class="select-option">PDF <span aria-hidden="true" class="text-xs font-bold text-rose-600">.pdf</span></button>
                    <button role="menuitem" type="button" wire:click="exportXlsx" @click="exportOpen = false" class="select-option">Excel <span aria-hidden="true" class="text-xs font-bold text-emerald-700">.xlsx</span></button>
                    <button role="menuitem" type="button" wire:click="exportCsv" @click="exportOpen = false" class="select-option">CSV <span aria-hidden="true" class="text-xs font-bold text-sky-700">.csv</span></button>
                </div>
            </div>
            <button wire:click="create" type="button" class="btn-primary">+ Tambah anggota</button>
        </div>
    </div>

    @if (session('success'))
        <div role="status" aria-live="polite" class="rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800">{{ session('success') }}</div>
    @endif

    @if ($showForm)
        <section class="surface p-6" aria-labelledby="member-form-title">
            <div class="flex items-center justify-between gap-4">
                <h2 id="member-form-title" class="text-xl font-semibold">{{ $editingId ? 'Edit anggota' : 'Tambah anggota' }}</h2>
                <button type="button" wire:click="resetForm" class="text-sm font-semibold text-slate-500 hover:text-slate-900">Tutup</button>
            </div>
            <form wire:submit="save" class="mt-5 grid gap-4 sm:grid-cols-2">
                <div class="sm:col-span-2">
                    <label for="member-name" class="mb-2 block text-sm font-semibold">Nama <span class="text-rose-600">*</span></label>
                    <input id="member-name" wire:model="nama" aria-describedby="member-name-error" aria-invalid="{{ $errors->has('nama') ? 'true' : 'false' }}" class="field-control">
                    <x-input-error id="member-name-error" :messages="$errors->get('nama')" class="mt-2" />
                </div>
                <div>
                    <label for="member-nip" class="mb-2 block text-sm font-semibold">NIP</label>
                    <input id="member-nip" wire:model="nip" aria-describedby="member-nip-error" aria-invalid="{{ $errors->has('nip') ? 'true' : 'false' }}" class="field-control">
                    <x-input-error id="member-nip-error" :messages="$errors->get('nip')" class="mt-2" />
                </div>
                <div>
                    <label for="member-unit" class="mb-2 block text-sm font-semibold">Instansi / unit</label>
                    <input id="member-unit" wire:model="instansi_unit" aria-describedby="member-unit-error" aria-invalid="{{ $errors->has('instansi_unit') ? 'true' : 'false' }}" class="field-control">
                    <x-input-error id="member-unit-error" :messages="$errors->get('instansi_unit')" class="mt-2" />
                </div>
                <div>
                    <label for="member-phone" class="mb-2 block text-sm font-semibold">No. HP</label>
                    <input id="member-phone" wire:model="no_hp" inputmode="tel" aria-describedby="member-phone-error" aria-invalid="{{ $errors->has('no_hp') ? 'true' : 'false' }}" class="field-control">
                    <x-input-error id="member-phone-error" :messages="$errors->get('no_hp')" class="mt-2" />
                </div>
                <label class="flex min-h-11 items-center gap-2 text-sm font-semibold"><input wire:model="aktif" type="checkbox" class="rounded border-stone-300 text-kejati"> Aktif</label>
                <div class="flex justify-end gap-2 sm:col-span-2">
                    <button type="button" wire:click="resetForm" class="btn-secondary">Batal</button>
                    <button type="submit" wire:loading.attr="disabled" class="btn-primary">Simpan anggota</button>
                </div>
            </form>
        </section>
    @endif

    <section class="surface overflow-hidden">
        <div class="border-b border-stone-100 p-4">
            <label for="member-search" class="sr-only">Cari anggota</label>
            <input id="member-search" wire:model.live.debounce.300ms="search" type="search" placeholder="Cari nama, NIP, atau instansi…" class="field-control max-w-md py-3">
        </div>
        <div class="table-scroll">
            <table>
                <caption class="sr-only">Daftar anggota perpustakaan</caption>
                <thead><tr><th class="px-6">Nama</th><th class="px-6">Instansi</th><th class="px-6">Status</th><th class="px-6 text-right">Aksi</th></tr></thead>
                <tbody>
                    @forelse ($members as $member)
                        <tr>
                            <td class="px-6"><p class="font-semibold">{{ $member->nama }}</p><p class="text-xs text-slate-500">{{ $member->nip ?: 'NIP tidak diisi' }}</p></td>
                            <td class="px-6">{{ $member->instansi_unit ?: '—' }}</td>
                            <td class="px-6">{{ $member->aktif ? 'Aktif' : 'Nonaktif' }}</td>
                            <td class="px-6 text-right">
                                <button type="button" wire:click="edit({{ $member->id }})" class="mr-3 font-semibold text-kejati hover:underline">Edit</button>
                                <button type="button"
                                    @click="$dispatch('open-confirm-modal', {
                                        title: '{{ $member->loans_count > 0 ? 'Nonaktifkan Anggota' : 'Hapus Anggota' }}',
                                        message: '{{ $member->loans_count > 0 ? 'Anggota memiliki riwayat peminjaman dan akan dinonaktifkan. Riwayat tetap tersimpan.' : 'Anggota ini tidak memiliki riwayat peminjaman dan akan dihapus permanen.' }}',
                                        confirmButtonText: '{{ $member->loans_count > 0 ? 'Nonaktifkan Anggota' : 'Hapus Anggota' }}',
                                        type: 'danger',
                                        onConfirm: () => $wire.delete({{ $member->id }})
                                    })"
                                    class="font-semibold text-rose-600 hover:underline">{{ $member->loans_count > 0 ? 'Nonaktifkan' : 'Hapus' }}</button>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="4" class="px-6 py-12 text-center text-slate-500">Belum ada anggota.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="border-t border-stone-100 px-6 py-4">{{ $members->links() }}</div>
    </section>
</div>
