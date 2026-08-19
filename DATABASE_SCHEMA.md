# Database Schema
## Sistem Informasi Perpustakaan Kejaksaan Tinggi Jawa Barat

Database: MySQL 8.x
Naming convention: `snake_case`, tabel plural

---

## ERD (ringkas)

```
users            categories         books              visitors           loans
┌──────────┐    ┌──────────┐    ┌──────────────┐    ┌──────────────┐   ┌──────────────┐
│ id       │    │ id       │    │ id           │    │ id           │   │ id           │
│ name     │    │ nama     │◄───│ category_id  │    │ nama         │   │ book_id      │──►
│ email    │    └──────────┘    │ judul        │    │ nip          │   │ nama_peminjam│
│ password │                    │ penulis      │    │ instansi     │   │ nip_peminjam │
│ role     │◄───────────────────│ ...          │    │ keperluan    │   │ petugas_id   │
└──────────┘        petugas_id  │ stok         │    │ created_at   │   │ tgl_pinjam   │
   (loans.petugas_id)           │ stok_tersedia│    └──────────────┘   │ tgl_jatuh_   │
                                 └──────────────┘                       │   tempo      │
                                                                         │ tgl_kembali  │
                                                                         │ status       │
                                                                         └──────────────┘
```

---

## 1. Tabel `users`

Digunakan untuk admin/petugas perpustakaan (bukan tamu).

| Kolom | Tipe | Keterangan |
|---|---|---|
| id | BIGINT UNSIGNED, PK, AI | |
| name | VARCHAR(255) | Nama petugas |
| email | VARCHAR(255), UNIQUE | Login |
| password | VARCHAR(255) | Hashed |
| role | ENUM('admin','superadmin') | Default `admin` |
| email_verified_at | TIMESTAMP, nullable | |
| remember_token | VARCHAR(100), nullable | |
| created_at, updated_at | TIMESTAMP | |

## 2. Tabel `categories`

| Kolom | Tipe | Keterangan |
|---|---|---|
| id | BIGINT UNSIGNED, PK, AI | |
| nama_kategori | VARCHAR(100) | ex: Hukum Pidana, Hukum Perdata, Perundang-undangan |
| slug | VARCHAR(100), UNIQUE | untuk URL filter |
| created_at, updated_at | TIMESTAMP | |

## 3. Tabel `books`

| Kolom | Tipe | Keterangan |
|---|---|---|
| id | BIGINT UNSIGNED, PK, AI | |
| category_id | BIGINT UNSIGNED, FK → categories.id | |
| judul | VARCHAR(255) | |
| penulis | VARCHAR(255) | |
| penerbit | VARCHAR(255), nullable | |
| tahun_terbit | YEAR, nullable | |
| isbn | VARCHAR(20), nullable | |
| no_klasifikasi | VARCHAR(50), nullable | nomor DDC/klasifikasi perpustakaan hukum |
| lokasi_rak | VARCHAR(50), nullable | ex: "Rak A-3" |
| stok | INT UNSIGNED | total eksemplar |
| stok_tersedia | INT UNSIGNED | jumlah yang belum dipinjam |
| cover_image | VARCHAR(255), nullable | path file cover |
| deskripsi | TEXT, nullable | |
| created_at, updated_at | TIMESTAMP | |

**Index tambahan:** `INDEX (judul)`, `INDEX (penulis)` untuk mempercepat pencarian.

## 4. Tabel `visitors`

Log kunjungan tamu — bukan akun/user, satu baris = satu kunjungan.

| Kolom | Tipe | Keterangan |
|---|---|---|
| id | BIGINT UNSIGNED, PK, AI | |
| nama | VARCHAR(255) | |
| nip | VARCHAR(30), nullable | jika pegawai negeri |
| instansi_unit | VARCHAR(255) | asal instansi/unit kerja |
| no_hp | VARCHAR(20), nullable | |
| keperluan | VARCHAR(255) | ex: "Cari referensi", "Baca di tempat" |
| created_at | TIMESTAMP | dipakai sebagai waktu kunjungan |
| updated_at | TIMESTAMP | |

## 5. Tabel `loans`

Dicatat manual oleh petugas saat tamu meminjam di tempat.

| Kolom | Tipe | Keterangan |
|---|---|---|
| id | BIGINT UNSIGNED, PK, AI | |
| book_id | BIGINT UNSIGNED, FK → books.id | |
| petugas_id | BIGINT UNSIGNED, FK → users.id | siapa yang input transaksi |
| nama_peminjam | VARCHAR(255) | |
| nip_peminjam | VARCHAR(30), nullable | |
| instansi_unit | VARCHAR(255), nullable | |
| tanggal_pinjam | DATE | |
| tanggal_jatuh_tempo | DATE | |
| tanggal_kembali | DATE, nullable | diisi saat buku dikembalikan |
| status | ENUM('dipinjam','dikembalikan','terlambat') | default `dipinjam` |
| catatan | TEXT, nullable | ex: kondisi buku saat kembali |
| created_at, updated_at | TIMESTAMP | |

**Relasi:**
- `books.id` ← 1:N → `loans.book_id`
- `categories.id` ← 1:N → `books.category_id`
- `users.id` ← 1:N → `loans.petugas_id`

## 6. Business Rules terkait Data

- Saat `loans` baru dibuat dengan status `dipinjam` → `books.stok_tersedia` dikurangi 1
- Saat `loans.status` diubah ke `dikembalikan` → `books.stok_tersedia` ditambah 1
- Status `terlambat` dihitung otomatis (via scheduled command atau accessor) jika `tanggal_jatuh_tempo` < hari ini dan `tanggal_kembali` masih null
- `stok_tersedia` tidak boleh melebihi `stok` (validasi di level aplikasi)

## 7. Contoh Migration (books)

```php
Schema::create('books', function (Blueprint $table) {
    $table->id();
    $table->foreignId('category_id')->constrained()->cascadeOnDelete();
    $table->string('judul');
    $table->string('penulis');
    $table->string('penerbit')->nullable();
    $table->year('tahun_terbit')->nullable();
    $table->string('isbn', 20)->nullable();
    $table->string('no_klasifikasi', 50)->nullable();
    $table->string('lokasi_rak', 50)->nullable();
    $table->unsignedInteger('stok')->default(0);
    $table->unsignedInteger('stok_tersedia')->default(0);
    $table->string('cover_image')->nullable();
    $table->text('deskripsi')->nullable();
    $table->timestamps();

    $table->index('judul');
    $table->index('penulis');
});
```
