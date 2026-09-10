# Business Rules
## Sistem Informasi Perpustakaan Kejaksaan Tinggi Jawa Barat

> Status: aturan aktif pada source. Item berlabel "belum diimplementasikan" tidak boleh dianggap sebagai perilaku aplikasi saat ini.

---

## 1. Aturan Akses Katalog (Tamu)

- **R-01**: Tamu wajib mengisi form kunjungan sebelum dapat mengakses halaman katalog buku.
- **R-02**: Satu pengisian form kunjungan memberi akses melalui session Laravel dan tidak membuat akun tamu.
- **R-03**: Jika session kedaluwarsa atau tanggal berganti, tamu wajib mengisi ulang form kunjungan. Menutup browser hanya mengakhiri akses bila deployment mengaktifkan `SESSION_EXPIRE_ON_CLOSE`.
- **R-04**: Tamu tidak dapat melakukan peminjaman melalui website — hanya dapat melihat ketersediaan dan diarahkan untuk menghadap petugas.
- **R-05**: Data kunjungan tidak dapat diedit/dihapus setelah submit. UI koreksi oleh admin belum tersedia; koreksi operasional harus mengikuti prosedur data yang disetujui.

## 2. Aturan Data Buku

- **R-06**: Field `stok_tersedia` tidak boleh melebihi `stok` — divalidasi di level model (mutator/observer) maupun form admin.
- **R-07**: Buku dengan `stok_tersedia = 0` ditampilkan dengan status "Tidak Tersedia" di katalog publik, namun tetap muncul di hasil pencarian (agar tamu tahu buku itu ada, hanya sedang dipinjam semua).
- **R-08**: Buku yang memiliki riwayat `loans` tidak boleh dihapus; UI menolak dengan pesan ramah sebelum file sampul atau record diubah.
- **R-09**: Setiap buku wajib memiliki kategori (`category_id` tidak nullable).
- **R-09A**: Saat total stok diedit, stok tersedia dihitung ulang dari total stok dikurangi peminjaman aktif; total tidak boleh lebih kecil dari jumlah peminjaman aktif.

## 3. Aturan Peminjaman (Loans)

- **R-10**: Peminjaman hanya dapat dicatat oleh admin/petugas yang sudah login (tidak ada self-service dari tamu).
- **R-11**: Saat transaksi peminjaman baru dibuat:
  - `books.stok_tersedia -= 1`
  - Jika `stok_tersedia` sebelum transaksi = 0, sistem menolak transaksi (buku tidak bisa dipinjamkan)
- **R-12**: Saat buku dikembalikan:
  - `books.stok_tersedia += 1`
  - `tanggal_kembali` diisi otomatis dengan tanggal hari ini; perubahan manual tanggal kembali belum tersedia di UI
- **R-13**: `tanggal_jatuh_tempo` default = `tanggal_pinjam + 7 hari` dan dapat diedit pada form sebelum transaksi disimpan.
- **R-14**: Status `terlambat` dihitung otomatis:
  - Jika `tanggal_kembali` masih null DAN `tanggal_jatuh_tempo` < tanggal hari ini → status berubah menjadi `terlambat`
  - Implementasi aktif menghitung status on-the-fly melalui accessor; tidak ada kolom status turunan di database
- **R-15**: Satu buku (satu eksemplar) tidak dapat dipinjam oleh lebih dari satu peminjam dalam waktu bersamaan — dikontrol lewat `stok_tersedia`, bukan tracking per-eksemplar (kecuali kebutuhan ke depan meningkat ke level barcode per buku fisik).

## 4. Aturan Autentikasi & Otorisasi

- **R-16**: Hanya user dengan role `admin` atau `superadmin` yang dapat mengakses route `/admin/*`.
- **R-17**: Hanya `superadmin` yang dapat menambah/menghapus akun admin lain.
- **R-18**: Password admin minimal 8 karakter dan di-hash melalui cast `hashed` Laravel. Password bootstrap superadmin minimal 12 karakter.
- **R-19**: Login dibatasi 5 percobaan per kombinasi email dan alamat IP sebelum throttle sementara.
- **R-19A**: Akun yang memiliki riwayat transaksi tidak dapat dihapus dan superadmin terakhir tidak dapat dihapus.

## 5. Aturan Data & Privasi

- **R-20**: Data pengunjung (`visitors`) hanya dapat diakses oleh admin, tidak ada endpoint publik yang menampilkan data pengunjung lain.
- **R-21**: Data pribadi (NIP, no. HP) tidak ditampilkan di halaman publik manapun.
- **R-22**: Export data kunjungan/peminjaman hanya dapat dilakukan admin yang sudah login dan setiap sel harus dinetralisasi dari spreadsheet formula injection. Audit trail export belum diimplementasikan.
- **R-22A**: Pengunjung wajib memberi persetujuan yang waktunya dicatat. Scheduled deletion hanya aktif bila organisasi menetapkan `VISITOR_RETENTION_DAYS`; nilai `0` berarti nonaktif.

## 6. Aturan Validasi Form

- **R-23**: Form kunjungan wajib: kategori, nama, instansi/unit kerja, keperluan, dan persetujuan privasi. NIP wajib untuk kategori `pegawai`, sedangkan NIP tamu umum dan kontak bersifat opsional.
- **R-24**: Form buku (admin) wajib: judul, penulis, kategori, stok. Field lain opsional.
- **R-25**: ISBN bersifat opsional. Input boleh memakai dash/spasi; sistem menormalkan dan memvalidasi checksum ISBN-10/ISBN-13 sebelum menyimpan (`App\Support\Isbn`). Data yang sudah tersimpan sebelum fitur ini aktif tidak dimigrasikan ulang — normalisasi berlaku untuk penulisan baru via form admin.
- **R-26**: Tanggal jatuh tempo tidak boleh lebih awal dari tanggal pinjam (validasi form peminjaman).

## 7. Prioritas Implementasi (untuk timeline magang)

1. **Wajib (MVP):** Form kunjungan + gate katalog, CRUD buku, CRUD peminjaman manual, login admin
2. **Penting:** Dashboard statistik, filter/search katalog, status otomatis "terlambat"
3. **Nice-to-have:** Export laporan (Excel/PDF), manajemen multi-admin, audit log
