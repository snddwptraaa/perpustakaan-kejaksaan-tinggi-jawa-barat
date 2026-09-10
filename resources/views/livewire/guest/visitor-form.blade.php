<div>
    <section class="min-h-screen bg-kejati-canvas">
        <div class="mx-auto max-w-xl px-5 py-6 sm:py-10 lg:px-8">
            <div class="overflow-hidden rounded-2xl border border-stone-200 bg-white shadow-soft">

                <!-- Card Header -->
                <div class="border-b border-stone-100 bg-stone-50/70 p-6 sm:p-7">
                    <div class="flex items-center justify-between gap-4">
                        <div>
                            <p class="text-xs font-bold uppercase tracking-[0.16em] text-kejati-gold-dark">Perpustakaan Kejati Jawa Barat</p>
                            <h1 class="text-2xl font-bold tracking-tight text-slate-950 sm:text-3xl">Buku tamu</h1>
                            <p class="mt-2 text-sm leading-6 text-slate-600">Isi data kunjungan hari ini untuk membuka katalog buku.</p>
                        </div>
                        <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-2xl bg-kejati/10 text-kejati">
                            <img src="{{ asset('images/logo.svg') }}" alt="Logo Kejaksaan" class="h-8 w-auto object-contain">
                        </div>
                    </div>

                    <!-- TAB SWITCHER: 2 KATEGORI PENGUNJUNG -->
                    <div class="mt-5">
                        <label class="mb-2 block text-xs font-bold uppercase tracking-wider text-slate-500">Pilih Kategori Pengunjung</label>
                        <div class="grid grid-cols-2 gap-2 rounded-2xl bg-stone-200/70 p-1.5 text-xs font-bold">
                            <!-- Tab Tamu / Umum -->
                            <button
                                type="button"
                                wire:click="setKategori('umum')" aria-pressed="{{ $kategori === 'umum' ? 'true' : 'false' }}"
                                class="flex items-center justify-center gap-2 rounded-xl px-3 py-3 transition-colors duration-200 {{ $kategori === 'umum' ? 'bg-kejati text-white shadow-sm font-bold' : 'text-slate-600 hover:bg-white/70 hover:text-slate-900' }}">
                                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                </svg>
                                <span>Tamu / Umum</span>
                            </button>

                            <!-- Tab Pegawai Kejaksaan -->
                            <button
                                type="button"
                                wire:click="setKategori('pegawai')" aria-pressed="{{ $kategori === 'pegawai' ? 'true' : 'false' }}"
                                class="flex items-center justify-center gap-2 rounded-xl px-3 py-3 transition-colors duration-200 {{ $kategori === 'pegawai' ? 'bg-kejati text-white shadow-sm font-bold' : 'text-slate-600 hover:bg-white/70 hover:text-slate-900' }}">
                                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                                </svg>
                                <span>Pegawai Kejaksaan</span>
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Form Section -->
                <div class="p-6 sm:p-7">
                    @if (session('info'))
                        <div class="mb-5 rounded-xl border border-kejati-gold/40 bg-amber-50 px-4 py-3 text-sm text-yellow-900">
                            {{ session('info') }}
                        </div>
                    @endif

                    @if (session('status') || session('success'))
                        <div class="mb-5 flex items-center gap-3 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-900">
                            <svg class="h-5 w-5 shrink-0 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            <span>{{ session('status') ?? session('success') }}</span>
                        </div>
                    @endif

                    @if ($alreadyCheckedIn)
                        <div class="mb-5 flex flex-col sm:flex-row sm:items-center justify-between gap-3 rounded-2xl border border-emerald-200 bg-emerald-50/90 p-4 text-emerald-950">
                            <div class="flex items-start sm:items-center gap-3">
                                <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl bg-emerald-600 text-white shadow-sm">
                                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                                    </svg>
                                </span>
                                <div>
                                    <p class="text-sm font-bold text-emerald-900">Anda sudah check-in hari ini</p>
                                    <p class="text-xs text-emerald-700">Akses katalog sudah terbuka dan berlaku sepanjang hari ini.</p>
                                </div>
                            </div>
                            <div class="flex items-center gap-2">
                                <form action="{{ route('kunjungan.selesai') }}" method="POST" class="inline-block">
                                    @csrf
                                    <button type="submit"
                                        title="Reset sesi jika Anda adalah pengunjung berikutnya"
                                        class="inline-flex items-center justify-center gap-1 rounded-xl border border-stone-300 bg-white px-3 py-2 text-xs font-semibold text-slate-700 shadow-sm transition hover:border-red-300 hover:bg-red-50 hover:text-red-700">
                                        Pengunjung Baru ⟲
                                    </button>
                                </form>
                                <a href="{{ route('katalog') }}" class="inline-flex items-center justify-center gap-1.5 rounded-xl bg-emerald-700 px-4 py-2 text-xs font-bold text-white shadow-sm transition hover:bg-emerald-800">
                                    Buka Katalog
                                    <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5L21 12m0 0l-7.5 7.5M21 12H3" />
                                    </svg>
                                </a>
                            </div>
                        </div>
                    @endif

                    @if ($kategori === 'pegawai')
                        <p class="mb-5 rounded-xl bg-emerald-50 px-4 py-3 text-xs leading-5 text-emerald-900">NIP/NRP wajib diisi untuk pegawai Kejaksaan.</p>
                    @endif

                    <form wire:submit="submit" class="space-y-4 sm:space-y-5">

                        <!-- NAMA LENGKAP -->
                        <div>
                            <label for="nama" class="mb-1.5 block text-xs sm:text-sm font-semibold text-slate-700">
                                Nama Lengkap {{ $kategori === 'pegawai' ? '& Gelar' : '' }} <span class="text-rose-600">*</span>
                            </label>
                            <input
                                wire:model.blur="nama"
                                id="nama"
                                required
                                aria-describedby="nama-error"
                                aria-invalid="{{ $errors->has('nama') ? 'true' : 'false' }}"
                                type="text"
                                autocomplete="name"
                                placeholder="{{ $kategori === 'pegawai' ? 'Contoh: Ahmad Fauzi, S.H., M.H.' : 'Contoh: Budi Santoso' }}"
                                class="block w-full rounded-xl border-stone-300 bg-stone-50/50 px-4 py-2.5 sm:py-3 text-sm shadow-sm transition focus:border-kejati focus:bg-white focus:ring-kejati">
                            <x-input-error id="nama-error" :messages="$errors->get('nama')" class="mt-1.5" />
                        </div>

                        <!-- KHUSUS PEGAWAI KEJAKSAAN: NIP / NRP (WAJIB) -->
                        @if ($kategori === 'pegawai')
                            <div>
                                <div class="flex items-center justify-between">
                                    <label for="nip" class="mb-1.5 block text-xs sm:text-sm font-semibold text-slate-700">
                                        NIP / NRP (Nomor Induk Pegawai) <span class="text-rose-600">*</span>
                                    </label>
                                    <span class="text-[11px] font-bold text-kejati uppercase tracking-wider">Wajib Pegawai</span>
                                </div>
                                <input
                                    wire:model.blur="nip"
                                    id="nip"
                                    required
                                    aria-describedby="nip-error"
                                    aria-invalid="{{ $errors->has('nip') ? 'true' : 'false' }}"
                                    type="text"
                                    inputmode="numeric"
                                    placeholder="Contoh: 19850101 201012 1 001"
                                    class="block w-full rounded-xl border-emerald-300 bg-emerald-50/30 px-4 py-2.5 sm:py-3 text-sm font-mono shadow-sm transition focus:border-kejati focus:bg-white focus:ring-kejati">
                                <p class="mt-1 text-[11px] text-slate-500">Masukkan NIP atau NRP sesuai identitas kepegawaian Anda (5–30 karakter).</p>
                                <x-input-error id="nip-error" :messages="$errors->get('nip')" class="mt-1.5" />
                            </div>
                        @endif

                        <!-- INSTANSI / UNIT KERJA -->
                        <div>
                            <label for="instansi_unit" class="mb-1.5 block text-xs sm:text-sm font-semibold text-slate-700">
                                @if ($kategori === 'pegawai')
                                    Unit Kerja / Bidang Kejaksaan <span class="text-rose-600">*</span>
                                @else
                                    Asal Instansi / Universitas / Lembaga <span class="text-rose-600">*</span>
                                @endif
                            </label>

                            @if ($kategori === 'pegawai')
                                <x-custom-select
                                    wire:model="instansi_unit"
                                    id="instansi_unit"
                                    wire:key="instansi-unit-select-pegawai"
                                    placeholder="-- Pilih Unit Kerja / Bidang --"
                                    :options="$unitKerjaList"
                                    :error="$errors->first('instansi_unit')"
                                    required
                                />
                            @else
                                <input
                                    wire:model.blur="instansi_unit"
                                    id="instansi_unit"
                                    required
                                    aria-describedby="instansi_unit-error"
                                    aria-invalid="{{ $errors->has('instansi_unit') ? 'true' : 'false' }}"
                                    type="text"
                                    autocomplete="organization"
                                    placeholder="Contoh: Universitas Padjadjaran / PT Solusi / Umum"
                                    class="block w-full rounded-xl border-stone-300 bg-stone-50/50 px-4 py-2.5 sm:py-3 text-sm shadow-sm transition focus:border-kejati focus:bg-white focus:ring-kejati">
                            @endif
                            <x-input-error id="instansi_unit-error" :messages="$errors->get('instansi_unit')" class="mt-1.5" />
                        </div>

                        <!-- NOMOR HP / EMAIL / KONTAK -->
                        <div>
                            <label for="no_hp" class="mb-1.5 block text-xs sm:text-sm font-semibold text-slate-700">
                                @if ($kategori === 'pegawai')
                                    No. HP / WhatsApp <span class="font-normal text-slate-500">(opsional)</span>
                                @else
                                    No. HP / WhatsApp / Email <span class="font-normal text-slate-500">(opsional)</span>
                                @endif
                            </label>
                            <input
                                wire:model.blur="no_hp"
                                id="no_hp" aria-describedby="no_hp-error" aria-invalid="{{ $errors->has('no_hp') ? 'true' : 'false' }}"
                                type="text"
                                autocomplete="tel"
                                placeholder="{{ $kategori === 'pegawai' ? '0812-xxxx-xxxx' : '0812-xxxx-xxxx atau email@domain.com' }}"
                                class="block w-full rounded-xl border-stone-300 bg-stone-50/50 px-4 py-2.5 sm:py-3 text-sm shadow-sm transition focus:border-kejati focus:bg-white focus:ring-kejati">
                            <x-input-error id="no_hp-error" :messages="$errors->get('no_hp')" class="mt-1.5" />
                        </div>

                        <!-- KEPERLUAN KUNJUNGAN -->
                        <div>
                            <label for="keperluan" class="mb-1.5 block text-xs sm:text-sm font-semibold text-slate-700">
                                Keperluan Kunjungan <span class="text-rose-600">*</span>
                            </label>

                            <x-custom-select
                                wire:model="keperluan"
                                id="keperluan"
                                wire:key="keperluan-select-{{ $kategori }}"
                                placeholder="-- Pilih Keperluan Kunjungan --"
                                :options="$keperluanList"
                                :error="$errors->first('keperluan')"
                                required
                            />
                            <x-input-error id="keperluan-error" :messages="$errors->get('keperluan')" class="mt-1.5" />
                        </div>

                        <div class="rounded-xl border border-stone-200 bg-stone-50 p-4 transition-colors duration-200 {{ $accepted_privacy ? 'border-emerald-300 bg-emerald-50/30' : '' }}">
                            <label for="accepted_privacy" class="flex cursor-pointer items-start gap-3 text-xs leading-5 text-slate-600">
                                <input
                                    id="accepted_privacy"
                                    aria-describedby="accepted_privacy-error"
                                    aria-invalid="{{ $errors->has('accepted_privacy') ? 'true' : 'false' }}"
                                    type="checkbox"
                                    required
                                    wire:model.live="accepted_privacy"
                                    class="mt-0.5 rounded border-stone-300 text-kejati focus:ring-kejati">
                                <span>Saya memahami bahwa data di atas digunakan untuk administrasi, keamanan, dan statistik kunjungan perpustakaan. Data tidak digunakan untuk pemasaran. <span class="text-rose-600">*</span></span>
                            </label>
                            @if (! $accepted_privacy)
                                <p class="mt-2 flex items-center gap-1.5 text-[11px] font-medium text-amber-700">
                                    <svg class="h-3.5 w-3.5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                                    </svg>
                                    <span>Centang pernyataan di atas untuk dapat melanjutkan pengisian buku tamu.</span>
                                </p>
                            @endif
                            <x-input-error id="accepted_privacy-error" :messages="$errors->get('accepted_privacy')" class="mt-2" />
                        </div>

                        <!-- SUBMIT BUTTON -->
                        <div class="pt-2">
                            <button
                                type="submit"
                                wire:loading.attr="disabled"
                                @disabled(!$accepted_privacy)
                                class="inline-flex w-full items-center justify-center gap-2 rounded-xl bg-kejati px-5 py-3.5 text-sm font-bold text-white shadow-lg shadow-kejati/25 transition duration-200 hover:bg-kejati-dark hover:shadow-xl disabled:cursor-not-allowed disabled:opacity-50 disabled:shadow-none">
                                <!-- Loading spinner -->
                                <svg wire:loading wire:target="submit" class="h-4 w-4 shrink-0 animate-spin text-white" viewBox="0 0 24 24" fill="none">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path>
                                </svg>

                                <!-- Button label -->
                                <span wire:loading.remove wire:target="submit">Simpan & Masuk ke Katalog</span>
                                <span wire:loading wire:target="submit">Menyimpan data kunjungan…</span>

                                <!-- Arrow icon -->
                                <svg wire:loading.remove wire:target="submit" class="h-4 w-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5L21 12m0 0l-7.5 7.5M21 12H3" />
                                </svg>
                            </button>
                        </div>

                        <p class="text-center text-[11px] leading-relaxed text-slate-500">
                            Hubungi petugas perpustakaan untuk pertanyaan, koreksi, atau penghapusan data kunjungan Anda.
                        </p>
                    </form>
                </div>
            </div>
        </div>
    </section>

</div>
