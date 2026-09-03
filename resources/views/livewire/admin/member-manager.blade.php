<div class="space-y-6">
    <div class="flex flex-col justify-between gap-4 sm:flex-row sm:items-end">
        <div><p class="text-xs font-bold uppercase tracking-[0.2em] text-kejati-gold-dark">Direktori</p><h1 class="mt-2 text-3xl font-semibold tracking-tight">Anggota perpustakaan</h1><p class="mt-2 text-sm text-slate-500">Kelola anggota yang dapat dipilih saat pencatatan peminjaman.</p></div>
        <button wire:click="create" class="rounded-xl bg-kejati px-4 py-3 text-sm font-bold text-white hover:bg-kejati-dark">+ Tambah anggota</button>
    </div>
    @if (session('success'))<div role="status" aria-live="polite" class="rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800">{{ session('success') }}</div>@endif
    @if ($showForm)
        <div class="rounded-2xl border border-stone-200 bg-white p-6 shadow-sm">
            <div class="flex justify-between"><h2 class="text-xl font-semibold">{{ $editingId ? 'Edit anggota' : 'Tambah anggota' }}</h2><button type="button" wire:click="resetForm" class="text-sm text-slate-500">Batal</button></div>
            <form wire:submit="save" class="mt-5 grid gap-4 sm:grid-cols-2">
                <div class="sm:col-span-2"><label for="member-name" class="mb-2 block text-sm font-semibold">Nama *</label><input id="member-name" wire:model="nama" class="w-full rounded-xl border-stone-300"><x-input-error :messages="$errors->get('nama')" class="mt-2" /></div>
                <div><label for="member-nip" class="mb-2 block text-sm font-semibold">NIP</label><input id="member-nip" wire:model="nip" class="w-full rounded-xl border-stone-300"><x-input-error :messages="$errors->get('nip')" class="mt-2" /></div>
                <div><label for="member-unit" class="mb-2 block text-sm font-semibold">Instansi / unit</label><input id="member-unit" wire:model="instansi_unit" class="w-full rounded-xl border-stone-300"></div>
                <div><label for="member-phone" class="mb-2 block text-sm font-semibold">No. HP</label><input id="member-phone" wire:model="no_hp" class="w-full rounded-xl border-stone-300"></div>
                <label class="mt-8 flex items-center gap-2 text-sm font-semibold"><input wire:model="aktif" type="checkbox" class="rounded border-stone-300 text-kejati"> Aktif</label>
                <button class="sm:col-span-2 justify-self-end rounded-xl bg-kejati px-5 py-3 text-sm font-bold text-white">Simpan anggota</button>
            </form>
        </div>
    @endif
    <div class="rounded-2xl border border-stone-200 bg-white shadow-sm">
        <div class="border-b border-stone-100 p-4"><label for="member-search" class="sr-only">Cari anggota</label><input id="member-search" wire:model.live.debounce.300ms="search" placeholder="Cari nama, NIP, atau instansi…" class="w-full max-w-md rounded-xl border-stone-200 bg-stone-50 py-3 text-sm"></div>
        <div class="overflow-x-auto"><table class="w-full text-left text-sm"><caption class="sr-only">Daftar anggota perpustakaan</caption><thead class="bg-stone-50 text-xs uppercase tracking-wide text-slate-500"><tr><th class="px-6 py-4">Nama</th><th class="px-6 py-4">Instansi</th><th class="px-6 py-4">Status</th><th class="px-6 py-4 text-right">Aksi</th></tr></thead><tbody class="divide-y divide-stone-100">
        @forelse ($members as $member)<tr><td class="px-6 py-4"><p class="font-semibold">{{ $member->nama }}</p><p class="text-xs text-slate-500">{{ $member->nip ?: 'NIP tidak diisi' }}</p></td><td class="px-6 py-4">{{ $member->instansi_unit ?: '—' }}</td><td class="px-6 py-4">{{ $member->aktif ? 'Aktif' : 'Nonaktif' }}</td><td class="px-6 py-4 text-right"><button wire:click="edit({{ $member->id }})" class="mr-3 font-semibold text-kejati">Edit</button><button wire:click="delete({{ $member->id }})" wire:confirm="Hapus atau nonaktifkan anggota ini?" class="font-semibold text-red-600">Hapus</button></td></tr>
        @empty<tr><td colspan="4" class="px-6 py-12 text-center text-slate-500">Belum ada anggota.</td></tr>@endforelse
        </tbody></table></div><div class="border-t border-stone-100 px-6 py-4">{{ $members->links() }}</div>
    </div>
</div>
