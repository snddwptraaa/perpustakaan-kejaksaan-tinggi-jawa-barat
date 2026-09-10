<div class="space-y-6">
    <!-- Top Action Bar -->
    <div class="flex flex-col justify-between gap-4 sm:flex-row sm:items-center">
        <div>
            <h1 class="page-title mt-0">Pengguna admin</h1>
            <p class="mt-1 text-sm text-slate-500">Kelola akun petugas dan hak akses untuk panel administrasi perpustakaan.</p>
        </div>
        <div>
            <button wire:click="create"
                type="button"
                class="btn-primary">
                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4" />
                </svg>
                <span>Tambah pengguna</span>
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

    <!-- Users Data Table Card -->
    <div class="rounded-2xl border border-stone-200 bg-white shadow-sm overflow-hidden">
        <!-- Search & Info Bar -->
        <div class="border-b border-stone-100 p-4 sm:p-5">
            <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                <div class="relative max-w-md flex-1">
                    <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3.5 text-slate-400">
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                        </svg>
                    </div>
                    <input wire:model.live.debounce.300ms="search"
                        type="search"
                        placeholder="Cari nama atau email petugas…"
                        class="w-full rounded-xl border-stone-200 bg-stone-50 py-2.5 pl-10 pr-4 text-sm text-slate-900 transition placeholder:text-slate-400 focus:border-kejati focus:bg-white focus:outline-none focus:ring-1 focus:ring-kejati">
                </div>
                <div class="text-xs font-medium text-slate-500">
                    Total: <span class="font-bold text-slate-800">{{ $users->total() }}</span> pengguna terdaftar
                </div>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm">
                <thead class="bg-stone-50/80 text-xs font-semibold uppercase tracking-wider text-slate-500">
                    <tr>
                        <th class="px-6 py-3.5">Petugas / Pengguna</th>
                        <th class="px-6 py-3.5">Peran & Hak Akses</th>
                        <th class="px-6 py-3.5">Bergabung</th>
                        <th class="px-6 py-3.5 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-stone-100">
                    @forelse ($users as $user)
                        <tr class="transition hover:bg-stone-50/70">
                            <td class="px-6 py-4">
                                <div class="flex items-center gap-3">
                                    <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-kejati/10 text-kejati font-bold text-sm">
                                        {{ mb_substr($user->name, 0, 1) }}
                                    </div>
                                    <div>
                                        <div class="flex items-center gap-2">
                                            <p class="font-bold text-slate-900">{{ $user->name }}</p>
                                            @if ($user->id === auth()->id())
                                                <span class="inline-flex items-center rounded-md bg-stone-100 px-1.5 py-0.5 text-[10px] font-semibold text-slate-600">
                                                    Anda
                                                </span>
                                            @endif
                                        </div>
                                        <p class="text-xs text-slate-500 mt-0.5">{{ $user->email }}</p>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                @if ($user->role === 'superadmin')
                                    <span class="inline-flex items-center gap-1.5 rounded-full bg-amber-100 px-3 py-1 text-xs font-bold text-amber-900">
                                        <span class="h-1.5 w-1.5 rounded-full bg-amber-500"></span>
                                        Superadmin
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1.5 rounded-full bg-emerald-100 px-3 py-1 text-xs font-bold text-emerald-800">
                                        <span class="h-1.5 w-1.5 rounded-full bg-emerald-500"></span>
                                        Admin Petugas
                                    </span>
                                @endif
                            </td>
                            <td class="px-6 py-4 text-slate-500 text-xs">
                                {{ $user->created_at->format('d M Y') }}
                            </td>
                            <td class="px-6 py-4 text-right">
                                <button type="button"
                                    wire:click="edit({{ $user->id }})"
                                    class="inline-flex items-center gap-1 rounded-lg px-2.5 py-1.5 text-xs font-bold text-kejati transition hover:bg-emerald-50 focus:outline-none">
                                    Edit
                                </button>
                                @if ($user->id !== auth()->id())
                                    <button type="button"
                                        @click="$dispatch('open-confirm-modal', {
                                            title: 'Hapus Pengguna Admin',
                                            message: 'Apakah Anda yakin ingin menghapus akun admin \'{{ addslashes($user->name) }}\' ({{ addslashes($user->email) }})?',
                                            confirmButtonText: 'Ya, Hapus Akun',
                                            type: 'danger',
                                            onConfirm: () => $wire.delete({{ $user->id }})
                                        })"
                                        class="inline-flex items-center gap-1 rounded-lg px-2.5 py-1.5 text-xs font-bold text-rose-600 transition hover:bg-rose-50 focus:outline-none">
                                        <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                        </svg>
                                        Hapus
                                    </button>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="px-6 py-14 text-center">
                                <div class="mx-auto flex h-12 w-12 items-center justify-center rounded-full bg-stone-100 text-slate-400">
                                    <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                                    </svg>
                                </div>
                                <p class="mt-3 font-semibold text-slate-800">Tidak ada pengguna ditemukan</p>
                                <p class="mt-1 text-xs text-slate-500">
                                    {{ $search ? 'Tidak ada hasil untuk pencarian kata kunci tersebut.' : 'Silakan tambahkan akun pengguna admin baru.' }}
                                </p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($users->hasPages())
            <div class="border-t border-stone-100 px-6 py-4">
                {{ $users->links() }}
            </div>
        @endif
    </div>

    <!-- MODAL POPUP FORM PENGGUNA (CARD MODAL) -->
    @if ($showForm)
        <div x-data x-trap.inert.noscroll="true" @keydown.escape.stop="$wire.resetForm()" class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-user-title" role="dialog" aria-modal="true">
            <!-- Background Backdrop with Blur -->
            <div class="fixed inset-0 bg-slate-950/60 backdrop-blur-sm transition-opacity"
                wire:click="resetForm"></div>

            <div class="flex min-h-full items-center justify-center p-4 text-center sm:p-6">
                <!-- Modal Card Container -->
                <div class="relative my-8 w-full max-w-lg transform overflow-hidden rounded-3xl border border-stone-200/80 bg-white text-left shadow-2xl transition">
                    
                    <!-- Header -->
                    <div class="bg-kejati-dark px-6 py-5 text-white">
                        <div class="flex items-center justify-between">
                            <div>
                                <h3 class="text-xl font-bold text-white" id="modal-user-title">
                                    {{ $editingId ? 'Edit pengguna' : 'Tambah pengguna' }}
                                </h3>
                            </div>
                            <button wire:click="resetForm"
                                type="button"
                                class="rounded-xl bg-white/10 p-2 text-white/80 hover:bg-white/20 hover:text-white transition focus:outline-none"
                                aria-label="Tutup form">
                                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                </svg>
                            </button>
                        </div>
                    </div>

                    <!-- Form Body -->
                    <form wire:submit="save">
                        <div class="p-6 space-y-4">
                            <!-- Nama -->
                            <div>
                                <label for="admin-user-name" class="block text-xs font-bold uppercase tracking-wider text-slate-700">
                                    Nama Lengkap <span class="text-rose-600">*</span>
                                </label>
                                <input id="admin-user-name" wire:model="name"
                                    type="text"
                                    placeholder="Contoh: Ahmad Fauzi, S.H."
                                    class="mt-1.5 w-full rounded-xl border-stone-300 text-sm focus:border-kejati focus:ring-kejati shadow-sm placeholder:text-slate-400">
                                <x-input-error :messages="$errors->get('name')" class="mt-1.5" />
                            </div>

                            <!-- Email -->
                            <div>
                                <label for="admin-user-email" class="block text-xs font-bold uppercase tracking-wider text-slate-700">
                                    Alamat Email <span class="text-rose-600">*</span>
                                </label>
                                <input id="admin-user-email" wire:model="email"
                                    type="email"
                                    placeholder="Contoh: ahmad.fauzi@kejati-jabar.go.id"
                                    class="mt-1.5 w-full rounded-xl border-stone-300 text-sm focus:border-kejati focus:ring-kejati shadow-sm placeholder:text-slate-400">
                                <x-input-error :messages="$errors->get('email')" class="mt-1.5" />
                            </div>

                            <!-- Password -->
                            <div>
                                <label for="admin-user-password" class="block text-xs font-bold uppercase tracking-wider text-slate-700">
                                    Kata Sandi (Password) @unless ($editingId)<span class="text-rose-600">*</span>@endunless
                                </label>
                                <input id="admin-user-password" wire:model="password"
                                    type="password"
                                    placeholder="{{ $editingId ? 'Kosongkan jika tidak ingin mengganti' : 'Minimal 8 karakter' }}"
                                    class="mt-1.5 w-full rounded-xl border-stone-300 text-sm focus:border-kejati focus:ring-kejati shadow-sm placeholder:text-slate-400">
                                <x-input-error :messages="$errors->get('password')" class="mt-1.5" />
                            </div>

                            <!-- Peran -->
                            <div>
                                <label for="admin-user-role" class="block text-xs font-bold uppercase tracking-wider text-slate-700">
                                    Peran & Hak Akses <span class="text-rose-600">*</span>
                                </label>
                                <x-custom-select id="admin-user-role" wire:model="role" wire:key="user-role" :options="['admin' => 'Petugas Admin (Sirkulasi, Katalog, Pengunjung)', 'superadmin' => 'Superadmin (Akses Penuh Seluruh Sistem)']" placeholder="Pilih peran" :disabled="$editingId === auth()->id()" />
                                <x-input-error :messages="$errors->get('role')" class="mt-1.5" />
                                @if ($editingId === auth()->id())
                                    <p class="mt-1.5 text-xs text-slate-500">Peran akun yang sedang aktif tidak dapat diubah.</p>
                                @endif
                            </div>
                            <label class="flex items-center gap-2 text-sm font-semibold text-slate-700">
                                <input wire:model="aktif" type="checkbox" @disabled($editingId === auth()->id()) class="rounded border-stone-300 text-kejati focus:ring-kejati">
                                Akun aktif dan dapat masuk
                            </label>
                        </div>

                        <!-- Footer Actions -->
                        <div class="border-t border-stone-200 bg-stone-50 px-6 py-4 flex items-center justify-end gap-3 rounded-b-3xl">
                            <button type="button"
                                wire:click="resetForm"
                                class="rounded-xl border border-stone-300 bg-white px-4 py-2.5 text-xs font-semibold text-slate-700 shadow-sm transition hover:bg-stone-50 focus:outline-none">
                                Batal
                            </button>
                            <button type="submit"
                                wire:loading.attr="disabled"
                                class="inline-flex items-center gap-2 rounded-xl bg-kejati px-5 py-2.5 text-xs font-bold text-white shadow-lg shadow-kejati/20 transition hover:bg-kejati-dark focus:outline-none disabled:opacity-50">
                                <span wire:loading wire:target="save" class="h-3.5 w-3.5 animate-spin rounded-full border-2 border-white border-t-transparent"></span>
                                <span>{{ $editingId ? 'Simpan Perubahan' : 'Simpan Pengguna' }}</span>
                            </button>
                        </div>
                    </form>

                </div>
            </div>
        </div>
    @endif
</div>
