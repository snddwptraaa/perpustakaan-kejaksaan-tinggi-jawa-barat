<div>
    <section class="relative overflow-hidden bg-gradient-to-br from-kejati-dark via-kejati to-[#043327]">
        <!-- Decorative Background Elements -->
        <div class="pointer-events-none absolute -right-24 -top-32 h-96 w-96 rounded-full border border-kejati-gold/20 blur-sm"></div>
        <div class="pointer-events-none absolute -bottom-40 left-1/3 h-96 w-96 rounded-full border border-kejati-gold/10 blur-sm"></div>
        <div class="pointer-events-none absolute right-1/4 top-1/2 h-64 w-64 rounded-full bg-kejati-gold/5 blur-3xl"></div>

        <div class="relative mx-auto grid max-w-7xl gap-12 px-5 py-14 lg:grid-cols-[1fr_1.05fr] lg:items-center lg:px-8 lg:py-20">
            
            <!-- Kolom Kiri: Pengantar & Info -->
            <div class="max-w-2xl text-white">
                <div class="mb-5 inline-flex items-center gap-2 rounded-full border border-kejati-gold/40 bg-white/10 px-3.5 py-1.5 text-xs font-bold uppercase tracking-[0.2em] text-kejati-gold backdrop-blur-sm">
                    <span class="h-2 w-2 rounded-full bg-kejati-gold animate-pulse"></span>
                    Buku Tamu Digital
                </div>

                <h1 class="max-w-xl text-3xl font-bold leading-tight tracking-tight sm:text-4xl lg:text-5xl">
                    Pencatatan Kunjungan <br>
                    <span class="text-kejati-gold">Perpustakaan Kejati Jabar</span>
                </h1>

                <p class="mt-5 max-w-lg text-base leading-relaxed text-white/80 sm:text-lg">
                    Selamat datang di Perpustakaan Kejaksaan Tinggi Jawa Barat. Silakan isi buku tamu digital di samping sesuai kategori Anda untuk mengakses ruang baca dan katalog koleksi hukum kami.
                </p>

                <!-- 2 Fitur Ringkas -->
                <div class="mt-8 grid gap-4 sm:grid-cols-2">
                    <div class="flex items-center gap-3.5 rounded-2xl border border-white/10 bg-white/5 p-4 backdrop-blur-sm">
                        <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl bg-kejati-gold text-kejati-dark shadow-sm">
                            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                            </svg>
                        </div>
                        <div>
                            <h4 class="text-sm font-bold text-white">Akses Terbuka</h4>
                            <p class="mt-0.5 text-xs text-white/70">Terbuka untuk pegawai Kejaksaan dan masyarakat umum.</p>
                        </div>
                    </div>

                    <div class="flex items-center gap-3.5 rounded-2xl border border-white/10 bg-white/5 p-4 backdrop-blur-sm">
                        <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl bg-kejati-gold text-kejati-dark shadow-sm">
                            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                            </svg>
                        </div>
                        <div>
                            <h4 class="text-sm font-bold text-white">Data Tercatat Rapi</h4>
                            <p class="mt-0.5 text-xs text-white/70">Pencatatan statistik kunjungan terintegrasi dan aman.</p>
                        </div>
                    </div>
                </div>

                <!-- Info Kontak / Lokasi Ringkas -->
                <div class="mt-8 flex flex-wrap items-center gap-6 text-xs text-white/70 border-t border-white/10 pt-6">
                    <span class="inline-flex items-center gap-2">
                        <svg class="h-4 w-4 text-kejati-gold" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                        Jl. L. R. E. Martadinata No. 54, Bandung
                    </span>
                    <span class="inline-flex items-center gap-2">
                        <svg class="h-4 w-4 text-kejati-gold" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        Senin - Jumat: 08.00 - 16.00 WIB
                    </span>
                </div>
            </div>

            <!-- Kolom Kanan: Card Form Buku Tamu -->
            <div class="overflow-hidden rounded-3xl border border-stone-200/90 bg-white shadow-2xl shadow-black/25">
                
                <!-- Card Header -->
                <div class="border-b border-stone-100 bg-stone-50/70 p-6 sm:p-7">
                    <div class="flex items-center justify-between gap-4">
                        <div>
                            <p class="text-[11px] font-bold uppercase tracking-[0.2em] text-kejati-gold-dark">Buku Tamu Digital</p>
                            <h2 class="mt-1 text-xl font-bold tracking-tight text-slate-900 sm:text-2xl">Mulai Kunjungan Anda</h2>
                        </div>
                        <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-2xl bg-kejati/10 text-kejati">
                            <img src="{{ asset('images/logo.svg') }}" alt="Logo Kejaksaan" class="h-8 w-auto object-contain">
                        </div>
                    </div>

                    <!-- Statistik Hari Ini -->
                    <div wire:poll.15s class="mt-4 flex items-center justify-between rounded-xl border border-kejati/15 bg-emerald-50/70 px-4 py-2.5" aria-live="polite">
                        <div class="flex items-center gap-2.5">
                            <span class="flex h-2.5 w-2.5 rounded-full bg-kejati animate-pulse" aria-hidden="true"></span>
                            <span class="text-xs font-semibold text-kejati-dark">Kunjungan Hari Ini:</span>
                        </div>
                        <div class="text-xs font-bold text-kejati">
                            <span>{{ number_format($todayVisitors) }} Total</span>
                            <span class="text-slate-400 font-normal">({{ $todayPegawai }} Pegawai · {{ $todayUmum }} Umum)</span>
                        </div>
                    </div>

                    <!-- TAB SWITCHER: 2 KATEGORI PENGUNJUNG -->
                    <div class="mt-5">
                        <label class="mb-2 block text-xs font-bold uppercase tracking-wider text-slate-500">Pilih Kategori Pengunjung</label>
                        <div class="grid grid-cols-2 gap-2 rounded-2xl bg-stone-200/70 p-1.5 text-xs font-bold">
                            <!-- Tab Tamu / Umum -->
                            <button 
                                type="button" 
                                wire:click="setKategori('umum')"
                                class="flex items-center justify-center gap-2 rounded-xl py-3 px-3 transition-all duration-200 {{ $kategori === 'umum' ? 'bg-kejati text-white shadow-md shadow-kejati/20 font-bold' : 'text-slate-600 hover:text-slate-900 hover:bg-white/50' }}">
                                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                </svg>
                                <span>Tamu / Umum</span>
                            </button>

                            <!-- Tab Pegawai Kejaksaan -->
                            <button 
                                type="button" 
                                wire:click="setKategori('pegawai')"
                                class="flex items-center justify-center gap-2 rounded-xl py-3 px-3 transition-all duration-200 {{ $kategori === 'pegawai' ? 'bg-kejati text-white shadow-md shadow-kejati/20 font-bold' : 'text-slate-600 hover:text-slate-900 hover:bg-white/50' }}">
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

                    <!-- Banner Kategori Info -->
                    @if ($kategori === 'pegawai')
                        <div class="mb-5 flex items-center gap-3 rounded-xl border border-emerald-200 bg-emerald-50/90 p-3.5 text-xs text-emerald-950">
                            <span class="flex h-7 w-7 shrink-0 items-center justify-center rounded-lg bg-kejati text-white font-bold">⚖️</span>
                            <div>
                                <strong class="font-bold text-kejati-dark">Formulir Pegawai / Anggota Kejaksaan</strong>
                                <p class="text-slate-600 mt-0.5">Khusus Jaksa dan Pegawai Kejaksaan Tinggi Jabar / Kejaksaan Negeri (Wajib mencantumkan NIP/NRP).</p>
                            </div>
                        </div>
                    @else
                        <div class="mb-5 flex items-center gap-3 rounded-xl border border-stone-200 bg-stone-50 p-3.5 text-xs text-slate-700">
                            <span class="flex h-7 w-7 shrink-0 items-center justify-center rounded-lg bg-stone-200 text-slate-700 font-bold">🏛️</span>
                            <div>
                                <strong class="font-bold text-slate-900">Formulir Pengunjung Umum / Tamu</strong>
                                <p class="text-slate-500 mt-0.5">Untuk masyarakat umum, mahasiswa, akademisi, atau tamu instansi luar.</p>
                            </div>
                        </div>
                    @endif

                    <form wire:submit="submit" class="space-y-4 sm:space-y-5">

                        <!-- NAMA LENGKAP -->
                        <div>
                            <label for="nama" class="mb-1.5 block text-xs sm:text-sm font-semibold text-slate-700">
                                Nama Lengkap {{ $kategori === 'pegawai' ? '& Gelar' : '' }} <span class="text-rose-600">*</span>
                            </label>
                            <input 
                                wire:model.live="nama" 
                                id="nama" 
                                type="text" 
                                autocomplete="name" 
                                placeholder="{{ $kategori === 'pegawai' ? 'Contoh: Ahmad Fauzi, S.H., M.H.' : 'Contoh: Budi Santoso' }}" 
                                class="block w-full rounded-xl border-stone-300 bg-stone-50/50 px-4 py-2.5 sm:py-3 text-sm shadow-sm transition focus:border-kejati focus:bg-white focus:ring-kejati">
                            <x-input-error :messages="$errors->get('nama')" class="mt-1.5" />
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
                                    wire:model.live="nip" 
                                    id="nip" 
                                    type="text" 
                                    inputmode="numeric" 
                                    placeholder="Contoh: 19850101 201012 1 001" 
                                    class="block w-full rounded-xl border-emerald-300 bg-emerald-50/30 px-4 py-2.5 sm:py-3 text-sm font-mono shadow-sm transition focus:border-kejati focus:bg-white focus:ring-kejati">
                                <p class="mt-1 text-[11px] text-slate-500">Masukkan 18 digit NIP atau NRP Kejaksaan Anda.</p>
                                <x-input-error :messages="$errors->get('nip')" class="mt-1.5" />
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
                                <select 
                                    wire:model.live="instansi_unit" 
                                    id="instansi_unit" 
                                    class="block w-full rounded-xl border-stone-300 bg-stone-50/50 px-4 py-2.5 sm:py-3 text-sm shadow-sm transition focus:border-kejati focus:bg-white focus:ring-kejati">
                                    <option value="">-- Pilih Unit Kerja / Bidang --</option>
                                    <option value="Bagian Tata Usaha">Bagian Tata Usaha</option>
                                    <option value="Intelijen">Intelijen</option>
                                    <option value="Pembinaan">Pembinaan</option>
                                    <option value="Pemulihan Aset">Pemulihan Aset</option>
                                    <option value="Pengawasan">Pengawasan</option>
                                    <option value="Perdata dan Tata Usaha Negara">Perdata dan Tata Usaha Negara (Datun)</option>
                                    <option value="Pidana Militer">Pidana Militer</option>
                                    <option value="Tindak Pidana Khusus">Tindak Pidana Khusus (Pidsus)</option>
                                    <option value="Tindak Pidana Umum">Tindak Pidana Umum (Pidum)</option>
                                    <option value="Kejaksaan Negeri / Satker Daerah">Kejaksaan Negeri / Satker Daerah</option>
                                </select>
                            @else
                                <input 
                                    wire:model.live="instansi_unit" 
                                    id="instansi_unit" 
                                    type="text" 
                                    autocomplete="organization" 
                                    placeholder="Contoh: Universitas Padjadjaran / PT Solusi / Umum" 
                                    class="block w-full rounded-xl border-stone-300 bg-stone-50/50 px-4 py-2.5 sm:py-3 text-sm shadow-sm transition focus:border-kejati focus:bg-white focus:ring-kejati">
                            @endif
                            <x-input-error :messages="$errors->get('instansi_unit')" class="mt-1.5" />
                        </div>

                        <!-- NOMOR HP / EMAIL / KONTAK -->
                        <div>
                            <label for="no_hp" class="mb-1.5 block text-xs sm:text-sm font-semibold text-slate-700">
                                @if ($kategori === 'pegawai')
                                    No. HP / WhatsApp <span class="text-slate-400 font-normal">(opsional)</span>
                                @else
                                    No. HP / WhatsApp / Email <span class="text-slate-400 font-normal">(opsional)</span>
                                @endif
                            </label>
                            <input 
                                wire:model.live="no_hp" 
                                id="no_hp" 
                                type="text" 
                                placeholder="{{ $kategori === 'pegawai' ? '0812-xxxx-xxxx' : '0812-xxxx-xxxx atau email@domain.com' }}" 
                                class="block w-full rounded-xl border-stone-300 bg-stone-50/50 px-4 py-2.5 sm:py-3 text-sm shadow-sm transition focus:border-kejati focus:bg-white focus:ring-kejati">
                            <x-input-error :messages="$errors->get('no_hp')" class="mt-1.5" />
                        </div>

                        <!-- KEPERLUAN KUNJUNGAN -->
                        <div>
                            <label for="keperluan" class="mb-1.5 block text-xs sm:text-sm font-semibold text-slate-700">
                                Keperluan Kunjungan <span class="text-rose-600">*</span>
                            </label>
                            
                            <select 
                                wire:model.live="keperluan" 
                                id="keperluan" 
                                class="block w-full rounded-xl border-stone-300 bg-stone-50/50 px-4 py-2.5 sm:py-3 text-sm shadow-sm transition focus:border-kejati focus:bg-white focus:ring-kejati">
                                <option value="">-- Pilih Keperluan Kunjungan --</option>
                                @if ($kategori === 'pegawai')
                                    <option value="Penyusunan Berkas / Dakwaan / Tuntutan">Penyusunan Berkas / Dakwaan / Tuntutan</option>
                                    <option value="Riset Yurisprudensi, Doktrin & Peraturan">Riset Yurisprudensi, Doktrin & Peraturan</option>
                                    <option value="Mencari Referensi Tugas Kedinasan">Mencari Referensi Tugas Kedinasan</option>
                                    <option value="Membaca di Tempat">Membaca di Tempat</option>
                                    <option value="Peminjaman Koleksi Buku">Peminjaman Koleksi Buku</option>
                                    <option value="Keperluan Kedinasan Lainnya">Keperluan Kedinasan Lainnya</option>
                                @else
                                    <option value="Mencari Referensi Hukum & Koleksi">Mencari Referensi Hukum & Koleksi</option>
                                    <option value="Membaca di Tempat">Membaca di Tempat</option>
                                    <option value="Riset Skripsi / Tesis / Penelitian">Riset Skripsi / Tesis / Penelitian</option>
                                    <option value="Studi Pustaka / Kunjungan Lembaga">Studi Pustaka / Kunjungan Lembaga</option>
                                    <option value="Keperluan Lainnya">Keperluan Lainnya</option>
                                @endif
                            </select>
                            <x-input-error :messages="$errors->get('keperluan')" class="mt-1.5" />
                        </div>

                        <!-- SUBMIT BUTTON -->
                        <div class="pt-2">
                            <button 
                                type="submit" 
                                wire:loading.attr="disabled" 
                                class="inline-flex w-full items-center justify-center gap-2 rounded-xl bg-kejati px-5 py-3.5 text-sm font-bold text-white shadow-lg shadow-kejati/25 transition duration-200 hover:bg-kejati-dark hover:shadow-xl disabled:cursor-wait disabled:opacity-75">
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

                        <p class="text-center text-[11px] leading-relaxed text-slate-400">
                            Dengan mengisi buku tamu, Anda terdaftar sebagai pengunjung resmi Perpustakaan Kejaksaan Tinggi Jawa Barat.
                        </p>
                    </form>
                </div>
            </div>
        </div>
    </section>

    <!-- Langkah Mudah Alur Kunjungan -->
    <section class="mx-auto max-w-7xl px-5 py-14 lg:px-8">
        <div class="mb-8 text-center">
            <p class="text-xs font-bold uppercase tracking-[0.2em] text-kejati-gold-dark">Panduan Kunjungan</p>
            <h3 class="mt-1 text-2xl font-bold tracking-tight text-slate-900">Alur Layanan Perpustakaan</h3>
        </div>

        <div class="grid gap-6 sm:grid-cols-3">
            <div class="rounded-2xl border border-stone-200 bg-white p-6 shadow-sm transition hover:border-kejati-gold/60 hover:shadow-md">
                <span class="inline-flex h-10 w-10 items-center justify-center rounded-xl bg-kejati/10 text-base font-bold text-kejati">01</span>
                <h4 class="mt-4 font-bold text-slate-900">Isi Buku Tamu</h4>
                <p class="mt-1.5 text-sm leading-relaxed text-slate-500">Pilih kategori (Tamu/Umum atau Pegawai Kejaksaan) dan lengkapi data kunjungan Anda.</p>
            </div>

            <div class="rounded-2xl border border-stone-200 bg-white p-6 shadow-sm transition hover:border-kejati-gold/60 hover:shadow-md">
                <span class="inline-flex h-10 w-10 items-center justify-center rounded-xl bg-kejati/10 text-base font-bold text-kejati">02</span>
                <h4 class="mt-4 font-bold text-slate-900">Jelajahi Koleksi</h4>
                <p class="mt-1.5 text-sm leading-relaxed text-slate-500">Cari buku, referensi perundang-undangan, dan literatur hukum melalui katalog digital.</p>
            </div>

            <div class="rounded-2xl border border-stone-200 bg-white p-6 shadow-sm transition hover:border-kejati-gold/60 hover:shadow-md">
                <span class="inline-flex h-10 w-10 items-center justify-center rounded-xl bg-kejati/10 text-base font-bold text-kejati">03</span>
                <h4 class="mt-4 font-bold text-slate-900">Layanan di Tempat</h4>
                <p class="mt-1.5 text-sm leading-relaxed text-slate-500">Nikmati ruang baca yang representatif atau hubungi petugas untuk peminjaman koleksi.</p>
            </div>
        </div>
    </section>
</div>
