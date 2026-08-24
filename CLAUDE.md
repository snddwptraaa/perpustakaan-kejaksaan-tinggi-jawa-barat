# CLAUDE.md
Panduan konteks proyek untuk AI coding assistant (Claude Code) — Sistem Informasi Perpustakaan Kejaksaan Tinggi Jawa Barat.

> Dokumen ini menjelaskan konvensi source yang aktif. Instruksi agent tingkat repository, bila tersedia, tetap memiliki prioritas lebih tinggi.

Dokumen pendukung lain di repo ini (baca dulu sebelum mulai coding):
- `PRD.md` — kebutuhan produk & scope
- `ARCHITECTURE.md` — arsitektur sistem & folder structure
- `DATABASE_SCHEMA.md` — struktur tabel & relasi
- `DESIGN_GUIDELINES.md` — palet warna, tipografi, komponen UI
- `RULES.md` — business rules yang wajib diikuti di level kode

---

## 1. Ringkasan Proyek

Website perpustakaan internal untuk Kejaksaan Tinggi Jawa Barat. Dua sisi utama:
1. **Publik (tamu)** — isi form kunjungan → lihat katalog buku (read-only, tanpa login)
2. **Admin panel** — petugas login untuk kelola buku, kategori, dan mencatat peminjaman/pengembalian secara manual

Peminjaman buku dilakukan secara fisik di tempat (tamu datang ke petugas), **bukan** self-service online. Jangan bangun fitur checkout/reservasi untuk tamu kecuali diminta eksplisit.

## 2. Tech Stack

- Laravel 12.x
- Livewire 3.x (gunakan ini untuk interaktivitas, hindari bikin API/controller terpisah kecuali memang perlu)
- Tailwind CSS 3.x
- MySQL 8.x
- Laravel Breeze (Livewire stack) untuk auth admin

## 3. Konvensi Kode

- **Bahasa nama variabel/kolom database**: Bahasa Indonesia (`nama`, `judul`, `penulis`, `tanggal_pinjam`, dll) — sudah konsisten sejak `DATABASE_SCHEMA.md`, jangan diterjemahkan ke Inggris di tengah jalan.
- **Bahasa nama class/method/route**: Bahasa Inggris standar Laravel convention (`BookManager`, `LoanController`, `storeLoan()`), kecuali untuk hal yang sangat spesifik domain (boleh Indonesia jika lebih jelas).
- **Livewire components**: taruh di `app/Livewire/Guest/` untuk sisi publik, `app/Livewire/Admin/` untuk sisi admin.
- **Blade view**: ikuti struktur folder yang sama di `resources/views/livewire/guest/` dan `resources/views/livewire/admin/`.
- **Migration**: gunakan migration incremental untuk perubahan schema; jangan mengubah migration yang sudah pernah dirilis. Gunakan `foreignId()->constrained()` dan index untuk kolom pencarian utama (`judul`, `penulis`).
- **Validasi**: gunakan `#[Validate]` atau `rules()` di komponen. Untuk form publik, pilih modifier `blur` agar tidak mengirim request pada setiap ketikan; gunakan debounce hanya untuk pencarian.
- **Styling**: utamakan utility Tailwind dan gunakan primitives bersama (`surface`, `field-control`, `btn-primary`, dan lainnya) dari `resources/css/app.css`. Rujuk `DESIGN_GUIDELINES.md`.

## 4. Hal yang Wajib Diperhatikan (dari RULES.md)

Sebelum implementasi fitur peminjaman, pastikan logic berikut ada:
- `stok_tersedia` dikurangi saat pinjam, ditambah saat kembali (lihat R-11, R-12 di `RULES.md`)
- Validasi buku tidak bisa dipinjam jika `stok_tersedia = 0`
- Status `terlambat` dihitung on-the-fly dari `tanggal_jatuh_tempo` dan `tanggal_kembali`
- Buku tidak boleh dihapus jika masih ada peminjaman aktif

Sebelum implementasi fitur katalog publik, pastikan:
- Middleware `EnsureVisitorHasCheckedIn` melindungi route `/katalog`
- Data pengunjung (`visitors`) tidak pernah ditampilkan ke publik, hanya admin

## 5. Command yang Sering Dipakai

```bash
# development
php artisan serve
npm run dev

# migration
php artisan make:model Book -m
php artisan migrate
php artisan migrate:fresh --seed

# livewire component
php artisan make:livewire Guest/CatalogBrowser
php artisan make:livewire Admin/BookManager

# testing
php artisan test
```

## 6. Yang Harus Dihindari

- Jangan bikin fitur login/akun untuk tamu — sesuai PRD, tamu hanya isi form kunjungan tanpa akun
- Jangan bikin proses checkout/pinjam online — semua peminjaman dicatat manual oleh admin
- Jangan hardcode warna di luar palet `DESIGN_GUIDELINES.md`
- Jangan taruh credential/API key di kode — gunakan `.env`
- Jangan gunakan Eloquent `->get()` tanpa batas untuk daftar besar (`books`, `visitors`, `loans`); gunakan pagination atau limit eksplisit untuk widget ringkas
- Jangan menulis nilai spreadsheet dari pengguna langsung ke CSV; gunakan `App\Support\Csv::safeCell()`
- Perubahan stok wajib berada dalam transaksi dan menggunakan `lockForUpdate()`

## 7. Struktur Prioritas Kerja (3 anggota tim)

Sesuai `RULES.md` bagian prioritas implementasi:
1. MVP dulu: form kunjungan + gate, CRUD buku, CRUD peminjaman manual, login admin
2. Baru lanjut: dashboard, search/filter katalog, status otomatis terlambat
3. Terakhir (jika waktu cukup): export laporan, multi-admin, audit log

Saat diminta membuat fitur baru, cek dulu apakah sudah tercakup di `PRD.md` — jika tidak, tanyakan konfirmasi scope sebelum implementasi besar.
