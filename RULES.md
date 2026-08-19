# Business Rules
## Sistem Informasi Perpustakaan Kejaksaan Tinggi Jawa Barat

---

## 1. Aturan Akses Katalog (Tamu)

- **R-01**: Tamu wajib mengisi form kunjungan sebelum dapat mengakses halaman katalog buku.
- **R-02**: Satu pengisian form kunjungan berlaku untuk satu sesi browser (disimpan di session, bukan cookie permanen).
- **R-03**: Jika session berakhir (tutup browser/logout sesi) atau berganti hari, tamu wajib mengisi ulang form kunjungan.
- **R-04**: Tamu tidak dapat melakukan peminjaman melalui website — hanya dapat melihat ketersediaan dan diarahkan untuk menghadap petugas.
- **R-05**: Data kunjungan tidak dapat diedit/dihapus oleh tamu setelah submit (hanya admin yang punya akses koreksi jika ada kesalahan input).

## 2. Aturan Data Buku

- **R-06**: Field `stok_tersedia` tidak boleh melebihi `stok` — divalidasi di level model (mutator/observer) maupun form admin.
- **R-07**: Buku dengan `stok_tersedia = 0` ditampilkan dengan status "Tidak Tersedia" di katalog publik, namun tetap muncul di hasil pencarian (agar tamu tahu buku itu ada, hanya sedang dipinjam semua).
- **R-08**: Penghapusan buku (`books`) tidak diperbolehkan jika masih ada `loans` aktif (status `dipinjam`) terkait buku tersebut — gunakan soft delete atau tolak aksi dengan pesan error.
- **R-09**: Setiap buku wajib memiliki kategori (`category_id` tidak nullable).

## 3. Aturan Peminjaman (Loans)

- **R-10**: Peminjaman hanya dapat dicatat oleh admin/petugas yang sudah login (tidak ada self-service dari tamu).
- **R-11**: Saat transaksi peminjaman baru dibuat dengan status `dipinjam`:
  - `books.stok_tersedia -= 1`
  - Jika `stok_tersedia` sebelum transaksi = 0, sistem menolak transaksi (buku tidak bisa dipinjamkan)
- **R-12**: Saat status peminjaman diubah menjadi `dikembalikan`:
  - `books.stok_tersedia += 1`
  - `tanggal_kembali` diisi otomatis dengan tanggal hari ini (dapat diubah manual oleh petugas jika perlu)
- **R-13**: `tanggal_jatuh_tempo` default = `tanggal_pinjam + 7 hari` (dapat disesuaikan sesuai kebijakan internal Kejati, misal 14 hari untuk pegawai internal).
- **R-14**: Status `terlambat` dihitung otomatis:
  - Jika `tanggal_kembali` masih null DAN `tanggal_jatuh_tempo` < tanggal hari ini → status berubah menjadi `terlambat`
  - Dapat dijalankan via scheduled command (`php artisan schedule:run` harian) atau dihitung on-the-fly saat query (accessor)
- **R-15**: Satu buku (satu eksemplar) tidak dapat dipinjam oleh lebih dari satu peminjam dalam waktu bersamaan — dikontrol lewat `stok_tersedia`, bukan tracking per-eksemplar (kecuali kebutuhan ke depan meningkat ke level barcode per buku fisik).

## 4. Aturan Autentikasi & Otorisasi

- **R-16**: Hanya user dengan role `admin` atau `superadmin` yang dapat mengakses route `/admin/*`.
- **R-17**: Hanya `superadmin` yang dapat menambah/menghapus akun admin lain.
- **R-18**: Password admin minimal 8 karakter, di-hash menggunakan bcrypt (default Laravel).
- **R-19**: Setelah 3x percobaan login gagal, akun admin di-lock sementara (opsional — gunakan rate limiting bawaan Laravel).

## 5. Aturan Data & Privasi

- **R-20**: Data pengunjung (`visitors`) hanya dapat diakses oleh admin, tidak ada endpoint publik yang menampilkan data pengunjung lain.
- **R-21**: Data pribadi (NIP, no. HP) tidak ditampilkan di halaman publik manapun.
- **R-22**: Export data (kunjungan/peminjaman) hanya dapat dilakukan oleh admin yang sudah login, dan sebaiknya dicatat log siapa yang melakukan export (audit trail — opsional untuk v1).

## 6. Aturan Validasi Form

- **R-23**: Form kunjungan wajib: nama, instansi/unit kerja, keperluan. NIP dan no. HP bersifat opsional.
- **R-24**: Form buku (admin) wajib: judul, penulis, kategori, stok. Field lain opsional.
- **R-25**: Nomor ISBN jika diisi harus divalidasi format (10 atau 13 digit, boleh dengan strip).
- **R-26**: Tanggal jatuh tempo tidak boleh lebih awal dari tanggal pinjam (validasi form peminjaman).

## 7. Prioritas Implementasi (untuk timeline magang)

1. **Wajib (MVP):** Form kunjungan + gate katalog, CRUD buku, CRUD peminjaman manual, login admin
2. **Penting:** Dashboard statistik, filter/search katalog, status otomatis "terlambat"
3. **Nice-to-have:** Export laporan (Excel/PDF), manajemen multi-admin, audit log
