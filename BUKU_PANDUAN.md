# BUKU PANDUAN PERPUSTAKAAN KEJARI JAWA BARAT

## Ringkasan

Aplikasi ini adalah *Sistem Informasi Perpustakaan* internal Kejaksaan Tinggi Jawa Barat.  
Terdiri dari dua sisi:

1. **Publik (tamu)** – form kunjungan & katalog buku (read-only).
2. **Admin** – kelola buku, anggota, peminjaman, laporan, dll.

Dokumen ini mencakup instalasi, konfigurasi, cara memakai fitur utama, serta _tips_ pengembangan lanjutan.

---

## 1. Instalasi & Persiapan

```bash
# Clone repo
git clone https://github.com/your-org/perpustakaan-kejati-jawa-barat.git
cd perpustakaan-kejati-jawa-barat

# Install PHP dependencies (Laravel 12)
composer install

# Install Node dependencies (Tailwind, Livewire…) – optional for dev
npm install && npm run dev

# Buat file .env (copy .env.example)
cp .env.example .env
php artisan key:generate

# Buat database MySQL dan set variabel DB_* di .env
# contoh: DB_DATABASE=perpus_kejati

# Migrate & seed (opsional)
php artisan migrate --seed
```

> **Catatan:** Pastikan ekstensi `iconv` ter-install (Composer membutuhkan untuk `dompdf`). Pada distro yang tidak menyediakannya, gunakan `sudo pacman -S libiconv` atau `apt-get install php-iconv`.

---

## 2. Menjalankan Server

```bash
php artisan serve   # http://localhost:8000
```

Akses:
- `/` – halaman depan (welcome).
- `/kunjungan` – form kunjungan tamu (tanpa login).
- `/katalog` – katalog publik (harus dulu “check-in” lewat form).
- `/admin/*` – panel admin (login dulu, gunakan user admin).

---

## 3. Fitur Utama

| Fitur | Lokasi | Keterangan |
|-------|--------|------------|
| **Form Kunjungan** | `/kunjungan` (Livewire `VisitorForm`) | Catat nama, NIP, instansi, keperluan. Data hanya bisa diakses admin. |
| **Katalog Buku** | `/katalog` (Livewire `CatalogBrowser`) | Daftar buku, pencarian, filter kategori. Hanya **read-only** – tidak ada checkout online. |
| **Kelola Buku** | `/admin/buku` (Livewire `BookManager`) | CRUD buku, upload cover, arsipkan, audit log. |
| **Kelola Anggota** | `/admin/anggota` (Livewire `MemberManager`) | Daftar anggota, status aktif, histori sirkulasi. |
| **Peminjaman** | `/admin/peminjaman` (Livewire `LoanManager`) | Buat, edit, batalkan, kembali, perpanjang pinjaman. | 
| **Laporan Pengunjung** | `/admin/pengunjung` (Livewire `VisitorReport`) | Tabel paginasi, filter tanggal/kategori, **Export CSV / PDF**. |
| **Laporan Peminjaman** | `/admin/peminjaman` (Livewire `LoanManager`) | Export CSV / XLSX / **PDF**. |
| **Audit Log** | `/admin/audit-log` (Livewire `AuditLogViewer`) | Histori perubahan data, hanya superadmin. |

---

## 4. Export PDF – Cara Pakai

### 4.1. Dari UI (tombol Export PDF)

1. Buka halaman admin yang ingin diekspor (mis.: **Pengunjung**, **Peminjaman**, atau **Katalog Buku**).  
2. Klik tombol **Export PDF** yang berada di bar aksi atas.  
3. Browser akan men-download file PDF dengan nama `laporan-<tipe>-YYYY-MM-DD_HH-MM-SS.pdf`.

> Semua export PDF menggunakan **barryvdh/laravel-dompdf**.  
> Layout standar berada di `resources/views/reports/*.blade.php` (catalog-pdf, visitors-pdf, loans-pdf).

### 4.2. Dari CLI (Artisan command)

Jika Anda ingin menghasilkan PDF secara otomatis (mis.: scheduled job), gunakan perintah berikut:

```bash
# Export katalog buku
php artisan route:call GET /admin/buku/export-pdf

# Export laporan pengunjung
php artisan route:call GET /admin/pengunjung/export-pdf

# Export laporan peminjaman
php artisan route:call GET /admin/peminjaman/export-pdf
```

Perintah di atas memanggil route yang didefinisikan di `routes/web.php` dan meng-output file PDF ke **stdout** (browser akan menerima download). Untuk menyimpan ke disk, gunakan `php artisan route:call ... > output.pdf`.

---

## 5. Pengembangan Tambahan

- **Menambah Format Export** – Anda dapat menambah export **HTML** atau **DOCX** dengan men-create view Blade baru dan menggunakan library lain (mis.: `phpoffice/phpword`).
- **Ubah Tampilan PDF** – Edit file Blade di `resources/views/reports/`.
- **Audit Log** – Setiap perubahan buku/peminjaman tercatat lewat `App\Services\AuditLogger`.  
  Untuk men-tambah event baru, gunakan `app(AuditLogger::class)->model('tindakan', $model, $before, $after);`.
- **Testing** – Run `php artisan test` untuk memastikan semua fitur (termasuk export) berfungsi.

---

## 6. FAQ

1. **Kenapa PDF tidak ter-generate?**  
   Pastikan ekstensi `iconv` ter-install. Pada sistem yang belum ada, instal lewat paket manager.
2. **Apakah data sensitif ikut ter-export?**  
   Export PDF hanya mencakup data yang ditampilkan pada UI.  
   Pastikan tidak men-expose kolom sensitif (mis.: password) di view Blade.
3. **Bagaimana men-generate PDF otomatis tiap hari?**  
   Buat scheduler di `app/Console/Kernel.php`:
   ```php
   $schedule->command('route:call GET /admin/pengunjung/export-pdf')
            ->dailyAt('02:00')
            ->output(storage_path('app/reports/pengunjung-'.now()->format('Y-m-d').'.pdf'));
   ```

---

## 7. Referensi & Dokumentasi Tambahan

- **Laravel Docs** – https://laravel.com/docs/12.x
- **Livewire Docs** – https://livewire.laravel.com/docs/3.x/quickstart
- **DomPDF (barryvdh)** – https://github.com/barryvdh/laravel-dompdf
- **PRD.md** – spesifikasi produk detail (dalam repo).
- **RULES.md** – aturan bisnis (stok, peminjaman, audit).

---

*Dokumen ini terakhir diperbarui pada **28 Agustus 2026**.*