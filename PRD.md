# Product Requirements Document (PRD)
## Sistem Informasi Perpustakaan Kejaksaan Tinggi Jawa Barat

> Dokumen ini adalah baseline kebutuhan produk v1. Status implementasi aktual dirangkum pada bagian 10 dan bukan bukti kesiapan production.

---

## 1. Latar Belakang

Kejaksaan Tinggi Jawa Barat memiliki perpustakaan internal yang selama ini pengelolaannya masih manual. Dibutuhkan sistem informasi berbasis web untuk:
- Mendata pengunjung yang datang ke perpustakaan
- Menampilkan katalog buku secara digital
- Membantu petugas mengelola data buku dan peminjaman

## 2. Tujuan

1. Memudahkan pengunjung mencari referensi buku sebelum datang langsung ke rak
2. Mendigitalkan pencatatan data kunjungan (menggantikan buku tamu fisik)
3. Membantu petugas perpustakaan mengelola stok buku dan transaksi peminjaman secara terpusat
4. Menyediakan laporan/rekap kunjungan dan peminjaman untuk kebutuhan administrasi instansi

## 3. Ruang Lingkup (Scope)

### Termasuk dalam scope:
- Form isi data kunjungan (guest book digital) sebagai syarat akses katalog
- Katalog buku publik (read-only, tanpa proses pinjam online)
- Admin panel untuk CRUD buku, kategori, dan pengelolaan peminjaman
- Pencatatan peminjaman & pengembalian buku dilakukan manual oleh petugas
- Laporan dan ekspor CSV kunjungan/peminjaman; rekap bulanan teragregasi masih pengembangan lanjutan

### Di luar scope (v1):
- Peminjaman online / reservasi buku oleh tamu
- Akun/login untuk tamu
- Notifikasi otomatis (email/SMS) jatuh tempo
- Integrasi dengan sistem kepegawaian Kejaksaan

## 4. Pengguna Sistem (User Roles)

| Role | Deskripsi | Akses |
|---|---|---|
| Tamu/Pengunjung | Pegawai/masyarakat yang berkunjung ke perpustakaan | Isi form kunjungan → lihat katalog |
| Admin/Petugas | Pengelola perpustakaan | Login → kelola buku, peminjaman, data pengunjung |
| Superadmin (opsional) | Kepala/penanggung jawab perpustakaan | Semua akses admin + kelola user admin |

## 5. Alur Pengguna (User Flow)

**Tamu:**
1. Membuka website perpustakaan
2. Mengisi form data kunjungan (nama, unit kerja/instansi, keperluan)
3. Sistem menyimpan data kunjungan & memberi akses sesi ke katalog
4. Tamu dapat mencari/melihat katalog buku beserta status ketersediaan
5. Jika ingin meminjam, tamu diarahkan untuk menghadap petugas di lokasi

**Admin/Petugas:**
1. Login ke admin panel
2. Melihat dashboard (statistik buku, peminjaman aktif, kunjungan hari ini)
3. Mengelola data buku & kategori (tambah/edit/hapus)
4. Mencatat transaksi peminjaman & pengembalian secara manual
5. Melihat/export data pengunjung dan laporan peminjaman

## 6. Kebutuhan Fungsional

### 6.1 Modul Publik (Tamu)
- FR-01: Sistem menampilkan form kunjungan sebelum tamu dapat mengakses katalog
- FR-02: Sistem menyimpan data kunjungan ke database
- FR-03: Sistem menampilkan katalog buku dengan fitur pencarian dan filter kategori
- FR-04: Sistem menampilkan detail buku (judul, penulis, penerbit, tahun, status stok)
- FR-05: Sistem menampilkan status ketersediaan buku (tersedia/dipinjam semua)

### 6.2 Modul Admin
- FR-06: Sistem menyediakan autentikasi login untuk admin/petugas
- FR-07: Admin dapat melakukan CRUD data buku dan kategori
- FR-08: Admin dapat mencatat peminjaman buku (pilih buku, data peminjam, tanggal pinjam & jatuh tempo)
- FR-09: Admin dapat memproses pengembalian buku
- FR-10: Sistem otomatis menandai status "terlambat" jika melewati tanggal jatuh tempo
- FR-11: Admin dapat melihat & mengekspor data kunjungan
- FR-12: Admin dapat melihat dashboard ringkasan (jumlah buku, peminjaman aktif, kunjungan hari ini)
- FR-13: (Opsional) Superadmin dapat mengelola akun admin lain

## 7. Kebutuhan Non-Fungsional

- NFR-01: Website responsif (dapat diakses via desktop & mobile/tablet di meja resepsionis perpustakaan)
- NFR-02: Waktu muat halaman katalog < 2 detik untuk data hingga ~2000 buku
- NFR-03: Data pengunjung tersimpan aman dan hanya bisa diakses admin
- NFR-04: Sistem menggunakan autentikasi standar Laravel (hashed password)
- NFR-05: Antarmuka menggunakan bahasa Indonesia formal, sesuai identitas instansi pemerintah
- NFR-06: Data pribadi memiliki dasar pemrosesan/persetujuan, masa retensi, prosedur penghapusan, serta backup yang terdokumentasi
- NFR-07: Deployment production menggunakan HTTPS, secure cookie, debug nonaktif, monitoring, dan backup teruji

## 8. Batasan (Constraints)

- Tech stack: Laravel + MySQL + Livewire + Tailwind CSS
- Proses peminjaman fisik tetap manual (tidak ada checkout online)
- Tim pengembang: 3 mahasiswa magang, timeline mengikuti masa magang

## 9. Metrik Keberhasilan

- Semua pengunjung tercatat otomatis di sistem (0% pencatatan manual di buku fisik)
- Petugas dapat menemukan status buku dalam < 30 detik lewat admin panel
- Laporan bulanan dapat digenerate tanpa rekap manual

## 10. Status Implementasi

- **Selesai:** gate buku tamu, katalog dan detail buku, autentikasi/role admin, pengelolaan buku/kategori/user, transaksi pinjam-kembali, dashboard, pencarian/filter, serta ekspor CSV.
- **Parsial:** laporan dapat difilter dan diekspor, tetapi belum menyediakan agregasi bulanan khusus; performa katalog 2 detik belum dibuktikan lewat load test.
- **Tersedia di kode:** persetujuan privasi, retensi terkonfigurasi (default nonaktif), CI, readiness check, security headers, dan penanganan penghapusan buku ber-riwayat.
- **Masih bergantung deployment:** keputusan durasi retensi, backup otomatis dan uji restore, TLS, monitoring/alerting, serta eksekusi full test pada runner dengan `pdo_sqlite`.
- **Belum tersedia:** audit trail administratif/export yang immutable.
