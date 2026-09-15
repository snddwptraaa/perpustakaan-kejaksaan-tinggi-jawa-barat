# BUKU PANDUAN PENGGUNAAN APLIKASI

## Sistem Informasi Perpustakaan Kejaksaan Tinggi Jawa Barat

**Versi dokumen:** 3.0

**Tanggal:** 14 September 2026

**Lingkup:** penggunaan aplikasi, operasional harian, dan administrasi dasar
**Sasaran pembaca:** pengunjung, petugas perpustakaan, superadmin, dan pengelola server

> Panduan ini mengikuti tampilan dan perilaku aplikasi yang diverifikasi langsung pada 14 September 2026. Data, nama, dan kredensial yang dipakai saat pengujian bersifat fiktif dan tidak berasal dari database operasional.

---

## Daftar isi

1. [Mengenal aplikasi](#1-mengenal-aplikasi)
2. [Alamat dan persiapan akses](#2-alamat-dan-persiapan-akses)
3. [Panduan pengunjung](#3-panduan-pengunjung)
4. [Panduan admin atau petugas](#4-panduan-admin-atau-petugas)
5. [Panduan superadmin](#5-panduan-superadmin)
6. [Operasional harian](#6-operasional-harian)
7. [Backup, restore, dan retensi data](#7-backup-restore-dan-retensi-data)
8. [Operasional pada jaringan lokal](#8-operasional-pada-jaringan-lokal)
9. [Impor data lanjutan](#9-impor-data-lanjutan)
10. [Pemecahan masalah](#10-pemecahan-masalah)
11. [Keamanan dan perlindungan data](#11-keamanan-dan-perlindungan-data)
12. [Referensi cepat](#12-referensi-cepat)

---

## 1. Mengenal aplikasi

Aplikasi ini membantu Perpustakaan Kejaksaan Tinggi Jawa Barat menjalankan:

- buku tamu digital;
- katalog dan informasi ketersediaan koleksi;
- pengelolaan kategori, buku, dan anggota;
- pencatatan peminjaman, perpanjangan, pengembalian, dan pembatalan;
- laporan serta ekspor data;
- pengelolaan akun petugas; dan
- audit perubahan data oleh petugas.

### 1.1 Peran pengguna

| Peran | Hak akses utama |
|---|---|
| Pengunjung | Mengisi buku tamu, melihat katalog, mencari buku, dan melihat detail ketersediaan. |
| Admin/Petugas | Mengelola kategori, koleksi, anggota, peminjaman, riwayat sirkulasi, dan laporan pengunjung. |
| Superadmin | Seluruh hak admin, ditambah pengelolaan akun admin dan audit log. |
| Pengelola server | Deployment, konfigurasi, backup, restore, scheduler, sertifikat, dan pemantauan layanan. |

> Pengunjung tidak dapat meminjam buku secara mandiri melalui website. Transaksi peminjaman tetap dicatat oleh petugas perpustakaan.

### 1.2 Aturan penting

- Katalog hanya dapat dibuka setelah pengunjung mengisi buku tamu pada hari yang sama.
- Akses pengunjung disimpan dalam sesi browser dan tidak membuat akun pengunjung.
- Buku dengan stok tersedia nol tetap muncul di katalog dengan status tidak tersedia atau habis.
- Setiap peminjaman mengurangi stok tersedia sebanyak satu.
- Pengembalian dan pembatalan transaksi aktif mengembalikan stok sebanyak satu.
- Peminjaman hanya dapat diperpanjang satu kali selama tujuh hari.
- Perubahan operasional penting dicatat pada audit log.
- Denda belum dihitung oleh aplikasi; nilainya tetap nol sampai kebijakan resmi ditetapkan.

---

## 2. Alamat dan persiapan akses

Gunakan alamat yang diberikan pengelola jaringan. Contoh penulisan dalam dokumen ini:

```text
https://perpustakaan.intra.domain-resmi
```

Jangan menyimpan alamat IP atau nama server contoh sebagai alamat produksi. Gunakan hostname internal dan sertifikat HTTPS yang dipercaya komputer pengguna.

### 2.1 Alamat halaman

| Kebutuhan | Path |
|---|---|
| Beranda | `/` |
| Buku tamu | `/kunjungan` |
| Katalog | `/katalog` |
| Login petugas | `/login` |
| Dashboard admin | `/admin/dashboard` |
| Pemeriksaan kesiapan | `/ready` |

### 2.2 Perangkat yang disarankan

- Browser modern yang masih menerima pembaruan keamanan.
- Resolusi minimum ponsel atau kiosk 360 piksel.
- JavaScript dan cookie diaktifkan.
- Komputer kiosk ditempatkan pada halaman `/kunjungan`.
- Komputer petugas tidak digunakan bersama tanpa logout.

> Jangan mengabaikan peringatan sertifikat. Jika browser menampilkan peringatan keamanan, hentikan penggunaan dan hubungi pengelola server.

---

## 3. Panduan pengunjung

### 3.1 Mengisi buku tamu sebagai tamu umum

1. Buka halaman **Buku tamu** pada `/kunjungan`.
2. Pilih **Tamu / Umum**.
3. Isi **Nama Lengkap**.
4. Isi **Asal Instansi / Universitas / Lembaga**.
5. Isi **No. HP / WhatsApp / Email** jika diperlukan. Field ini opsional.
6. Buka **Keperluan Kunjungan**, lalu pilih tujuan yang sesuai.
7. Centang persetujuan pemrosesan data kunjungan.
8. Klik **Simpan & Masuk ke Katalog**.

Pilihan keperluan yang tersedia pada form:

- Mencari Referensi Hukum & Koleksi;
- Membaca di Tempat;
- Riset Skripsi / Tesis / Penelitian;
- Studi Pustaka / Kunjungan Lembaga; dan
- Keperluan Lainnya.

### 3.2 Mengisi buku tamu sebagai pegawai

1. Pilih **Pegawai Kejaksaan**.
2. Isi **Nama Lengkap & Gelar**.
3. Isi **NIP / NRP (Nomor Induk Pegawai)**.
4. Pilih **Unit Kerja / Bidang Kejaksaan** dari daftar yang tersedia.
5. Isi nomor kontak jika diperlukan.
6. Pilih keperluan, setujui pemrosesan data, lalu klik **Simpan & Masuk ke Katalog**.

> NIP/NRP dan unit kerja wajib diisi untuk kategori pegawai. Gunakan pilihan unit yang tersedia dan jangan memasukkan unit bebas di luar daftar.

### 3.3 Menggunakan katalog

Setelah check-in berhasil, aplikasi mengarahkan pengunjung ke `/katalog`.

- Gunakan kotak **Buku apa yang Anda cari?** untuk mencari judul atau pengarang.
- Gunakan **Kategori koleksi** untuk menyaring bidang koleksi.
- Gunakan **Ketersediaan** untuk menyaring buku yang tersedia atau tidak tersedia.
- Pilih kartu atau judul buku untuk melihat detail bibliografi, lokasi rak, dan jumlah stok.

Katalog hanya menampilkan informasi. Untuk meminjam buku, sampaikan judul dan lokasi rak kepada petugas.

### 3.4 Mengakhiri kunjungan

Klik **Selesai kunjungan** atau **Selesai & Reset Kiosk (Pengunjung Baru)**. Aplikasi akan menghapus sesi katalog dan mengembalikan perangkat ke buku tamu.

Pada komputer kiosk, selalu akhiri sesi sebelum menyerahkan perangkat kepada pengunjung berikutnya.

### 3.5 Jika katalog tidak dapat dibuka

- Pastikan buku tamu sudah disimpan pada hari yang sama.
- Jangan membuka katalog dari tab privat yang berbeda.
- Jika sesi kedaluwarsa atau tanggal sudah berganti, isi ulang buku tamu.
- Pastikan JavaScript dan cookie tidak diblokir browser.

---

## 4. Panduan admin atau petugas

### 4.1 Login dan logout

1. Buka `/login`.
2. Isi **Email** dan **Kata sandi**.
3. Gunakan **Tampilkan** jika perlu memeriksa pengetikan kata sandi.
4. Pilih **Ingat saya** hanya pada komputer kerja pribadi yang terlindungi.
5. Klik **Masuk**.
6. Setelah selesai bekerja, klik **Keluar** pada navigasi admin.

Jika lupa kata sandi, pilih **Lupa kata sandi?**. Pengiriman tautan reset membutuhkan konfigurasi SMTP yang aktif.

> Sistem membatasi percobaan login gagal. Jangan membagikan akun dan jangan menyimpan kata sandi pada komputer kiosk atau komputer umum.

### 4.2 Ringkasan dashboard

Halaman **Ringkasan** menyediakan statistik koleksi, stok, peminjaman aktif, keterlambatan, dan pengunjung hari ini. Bagian **Peminjaman terbaru** menampilkan transaksi terkini, sedangkan **Akses cepat** menuju pengelolaan peminjaman, koleksi, dan anggota.

Jika terdapat indikator transaksi terlambat, buka menu **Peminjaman** dan tindak lanjuti sesuai prosedur perpustakaan.

### 4.3 Mengelola kategori

1. Buka **Kategori**.
2. Klik **Tambah kategori**.
3. Isi nama kategori atau bidang hukum.
4. Simpan perubahan.

Gunakan **Cari kategori** dan **Urutkan** untuk menemukan data. Tautan **Lihat buku** membuka koleksi yang sudah difilter berdasarkan kategori tersebut.

Kategori yang masih digunakan buku tidak dapat dihapus. Pindahkan buku ke kategori lain terlebih dahulu.

### 4.4 Mengelola koleksi buku

Buka menu **Koleksi buku**. Gunakan **Cari koleksi**, filter **Kategori**, **Status koleksi**, dan **Urutkan** untuk mempersempit daftar.

#### Menambah buku

1. Klik **Tambah Buku Baru**.
2. Isi field wajib:
   - **Judul Buku**;
   - **Penulis / Pengarang**;
   - **Kategori / Bidang Hukum**;
   - **Total Eksemplar (Stok)**; dan
   - **Stok Tersedia di Rak**.
3. Lengkapi penerbit, tahun terbit, jumlah halaman, nomor panggil/DDC, lokasi rak, ISBN, dan sinopsis bila tersedia.
4. Unggah sampul berformat JPG, PNG, atau WEBP dengan ukuran maksimum 2 MB bila diperlukan.
5. Klik **Simpan Koleksi Buku**.

ISBN bersifat opsional, tetapi jika diisi harus berupa ISBN-10 atau ISBN-13 dengan checksum yang valid. Spasi dan tanda hubung akan dinormalisasi aplikasi.

#### Mengedit, mengarsipkan, dan menghapus

- **Edit** memperbarui metadata atau stok.
- **Arsipkan** menyembunyikan buku dari katalog aktif tanpa menghapus riwayatnya.
- **Hapus** hanya dapat digunakan jika buku tidak mempunyai riwayat peminjaman.

Saat total stok diedit, jumlahnya tidak boleh lebih kecil daripada jumlah peminjaman aktif. Stok tersedia dihitung berdasarkan stok fisik dikurangi peminjaman aktif.

#### Ekspor koleksi

Klik **Ekspor**, lalu pilih **PDF**, **Excel**, atau **CSV**. File mengikuti pencarian dan filter yang sedang aktif.

### 4.5 Mengelola anggota

1. Buka **Anggota**.
2. Klik **+ Tambah anggota**.
3. Isi **Nama** dan, bila tersedia, NIP, instansi/unit, serta nomor HP.
4. Biarkan **Aktif** tercentang agar anggota dapat dipilih pada transaksi baru.
5. Klik **Simpan anggota**.

NIP anggota harus unik. Untuk mempertahankan riwayat, nonaktifkan anggota yang sudah tidak dilayani alih-alih menghapus data yang telah memiliki transaksi.

Gunakan menu **Ekspor** untuk menghasilkan PDF, Excel, atau CSV sesuai pencarian aktif.

### 4.6 Mencatat peminjaman

1. Buka **Peminjaman**.
2. Klik **Catat Peminjaman**.
3. Cari dan pilih **Buku yang Dipinjam**. Hanya buku aktif dengan stok tersedia yang dapat dipinjamkan.
4. Pilih **Anggota Terdaftar** jika peminjam sudah terdaftar. Identitas anggota akan diisikan otomatis.
5. Untuk peminjam manual, isi **Nama Lengkap Peminjam**. NIP/NRP dan instansi/bidang dapat dilengkapi bila ada.
6. Periksa tanggal pinjam dan jatuh tempo. Tanggal jatuh tempo awal adalah tujuh hari setelah tanggal pinjam dan tidak boleh lebih awal dari tanggal pinjam.
7. Isi catatan tambahan bila diperlukan.
8. Klik **Simpan Transaksi Peminjaman**.

Setelah transaksi berhasil, stok tersedia buku berkurang satu.

### 4.7 Memproses transaksi aktif

| Tindakan | Dampak |
|---|---|
| Koreksi | Mengubah identitas peminjam, tanggal, atau catatan tanpa menambah atau mengurangi stok. |
| Perpanjang | Menambah masa pinjam tujuh hari. Hanya dapat dilakukan satu kali. Untuk transaksi terlambat, tujuh hari dihitung mulai hari perpanjangan. |
| Kembalikan | Mengisi tanggal kembali dengan hari ini dan menambah stok tersedia satu. |
| Batalkan | Menandai transaksi batal dan mengembalikan stok satu. Gunakan hanya untuk pencatatan yang keliru. |

Status transaksi:

- **Dipinjam:** transaksi aktif dan belum melewati jatuh tempo.
- **Terlambat:** transaksi aktif telah melewati jatuh tempo.
- **Dikembalikan:** buku telah dikembalikan.
- **Dibatalkan:** transaksi dibatalkan oleh petugas.

> Jangan menekan Kembalikan atau Batalkan sebelum memastikan transaksi yang dipilih benar. Gunakan dialog konfirmasi dan periksa kembali stok sesudah tindakan.

### 4.8 Riwayat sirkulasi

Menu **Riwayat sirkulasi** menampilkan peminjam, buku, periode, dan status akhir. Gunakan **Cari nama peminjam**, **Filter anggota**, atau **Filter buku** untuk penelusuran.

Halaman ini bersifat riwayat. Koreksi transaksi dilakukan dari menu **Peminjaman**.

### 4.9 Data pengunjung

Menu **Pengunjung** menampilkan ringkasan total kunjungan, pegawai Kejaksaan, tamu/umum, serta tabel riwayat.

Filter yang tersedia:

- nama, NIP, atau instansi;
- kategori pengunjung; dan
- rentang tanggal kunjungan.

Klik **Ekspor laporan**, lalu pilih PDF, CSV, atau XLSX. Hasil ekspor mengikuti filter aktif.

### 4.10 Ekspor laporan

| Halaman | Format |
|---|---|
| Koleksi buku | PDF, Excel `.xlsx`, CSV |
| Anggota | PDF, Excel `.xlsx`, CSV |
| Peminjaman | PDF, XLSX, CSV |
| Pengunjung | PDF, XLSX, CSV |

Setelah mengunduh:

1. Periksa periode dan filter pada dokumen.
2. Simpan file hanya di lokasi kerja yang berwenang.
3. Jangan mengirim file berisi data pribadi melalui kanal yang tidak disetujui.
4. Hapus salinan sementara setelah tidak diperlukan.

### 4.11 Pengaturan profil

Pilih nama pengguna pada bagian bawah navigasi untuk membuka **Pengaturan akun**. Petugas dapat memperbarui nama, email, dan kata sandi sendiri.

Gunakan kata sandi yang kuat dan unik. Setelah mengganti email atau kata sandi, lakukan login ulang jika diminta.

---

## 5. Panduan superadmin

### 5.1 Mengelola pengguna admin

1. Buka **Pengguna Admin**.
2. Klik **Tambah pengguna**.
3. Isi nama, email, peran, status, dan kata sandi.
4. Simpan pengguna.

Peran yang tersedia adalah admin dan superadmin. Admin biasa tidak dapat membuka menu ini.

- Nonaktifkan akun petugas yang tidak lagi bertugas.
- Jangan menghapus akun yang mempunyai riwayat transaksi.
- Superadmin terakhir tidak dapat dihapus.
- Saat mengedit tanpa mengisi kata sandi baru, kata sandi lama tetap dipertahankan.

### 5.2 Audit log

Menu **Audit log** hanya dapat dibuka superadmin. Halaman ini mencatat waktu dan IP, petugas, jenis tindakan, entitas target, serta rincian perubahan.

Gunakan filter:

- pencarian;
- entitas target;
- jenis tindakan; dan
- rentang tanggal.

Audit log membantu penelusuran operasional, tetapi bukan pengganti backup dan belum bersifat immutable. Batasi akses superadmin dan tinjau aktivitas tidak biasa secara berkala.

---

## 6. Operasional harian

### 6.1 Sebelum layanan dibuka

1. Pastikan server, IIS, dan MySQL berjalan.
2. Buka `/ready`; hasil normal adalah HTTP 200 dengan `{"status":"ready"}`.
3. Buka buku tamu dari komputer kiosk.
4. Login sebagai petugas dan periksa indikator keterlambatan.
5. Pastikan sampul buku dan ekspor laporan dapat diakses sesuai kebutuhan.

### 6.2 Selama layanan

- Pastikan setiap pengunjung mengakhiri sesi katalog.
- Catat peminjaman sebelum buku meninggalkan meja petugas.
- Proses pengembalian pada transaksi yang benar dan periksa stok.
- Hindari membuka file ekspor berisi data pribadi pada komputer umum.
- Catat gangguan, waktu kejadian, halaman, dan tindakan terakhir pengguna.

### 6.3 Setelah layanan selesai

1. Periksa transaksi aktif dan terlambat.
2. Pastikan tidak ada modal perubahan yang belum disimpan.
3. Logout dari akun admin.
4. Pastikan backup terjadwal berhasil dan salinan eksternal tersedia.
5. Jangan mematikan server tanpa prosedur pengelola infrastruktur.

### 6.4 Pemeriksaan berkala

- Mingguan: periksa ruang disk, log error, file backup, dan keberhasilan scheduler.
- Bulanan: uji restore pada database non-production.
- Berkala: tinjau akun admin aktif, audit log, sertifikat TLS, dan pembaruan keamanan.

---

## 7. Backup, restore, dan retensi data

Bagian ini hanya dijalankan pengelola server dari terminal pada server, bukan dari browser pengguna.

### 7.1 Membuat backup MySQL

```powershell
Set-Location "D:\apps\perpustakaan\current"
C:\PHP\php.exe artisan database:backup --no-interaction
```

Untuk menyimpan pada direktori backup khusus:

```powershell
C:\PHP\php.exe artisan database:backup --path="D:\backups\perpustakaan" --no-interaction
```

Perintah menggunakan `mysqldump`. Pastikan direktori `bin` MySQL tersedia dalam PATH dan hasil backup mempunyai ukuran wajar. Backup database harus disertai backup `storage/app/public`.

### 7.2 Restore database

> PERINGATAN: Restore menimpa isi database. Buat backup terbaru, catat checksum, hentikan akses pengguna, dan lakukan uji pada database non-production terlebih dahulu.

Command restore hanya menerima file SQL yang berada di `storage/app/backups`:

```powershell
C:\PHP\php.exe artisan database:restore "nama-backup.sql"
```

Jangan memakai `--force` kecuali prosedur pemulihan telah disetujui dan target database sudah diverifikasi.

### 7.3 Retensi data pengunjung

`VISITOR_RETENTION_DAYS` menentukan umur penyimpanan data pengunjung. Scheduler menjalankan penghapusan setiap hari pukul 01.30.

```powershell
C:\PHP\php.exe artisan visitors:prune
C:\PHP\php.exe artisan schedule:list
```

Nilai nol menonaktifkan penghapusan otomatis dan hanya boleh digunakan bila organisasi menerima retensi tanpa batas secara tertulis melalui konfigurasi yang disediakan.

---

## 8. Operasional pada jaringan lokal

Arsitektur yang disarankan:

```text
Komputer kiosk ─┐
Komputer admin ─┼── HTTPS 443 ──> IIS + Laravel ──> MySQL 127.0.0.1
Komputer LAN ───┘                         │
                                         └── Backup eksternal/NAS
```

Ketentuan utama:

- server memakai IP statis atau DHCP reservation;
- pengguna mengakses hostname internal, bukan alamat IP;
- IIS hanya menunjuk ke folder `public`;
- hanya port HTTPS 443 yang dibuka dari subnet LAN yang diperlukan;
- MySQL tetap terikat pada localhost dan port 3306 tidak dibuka ke pengguna;
- sertifikat TLS dipercaya seluruh komputer;
- `php artisan serve` tidak digunakan untuk operasional;
- komputer kiosk membuka `/kunjungan`; dan
- Task Scheduler menjalankan `artisan schedule:run` setiap menit tanpa instance ganda.

Konfigurasi minimum production:

```env
APP_ENV=production
APP_DEBUG=false
APP_URL=https://perpustakaan.intra.domain-resmi
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
SESSION_DRIVER=database
SESSION_ENCRYPT=true
SESSION_SECURE_COOKIE=true
CACHE_STORE=database
QUEUE_CONNECTION=sync
```

Ikuti `WINDOWS_DEPLOYMENT.md` untuk konfigurasi IIS/FastCGI, permission, TLS, firewall, scheduler, dan gate go-live.

---

## 9. Impor data lanjutan

Impor hanya dilakukan pengelola yang memahami format sumber. Selalu buat backup dan jalankan dry-run sebelum menulis data.

### 9.1 Impor katalog Excel

```powershell
C:\PHP\php.exe artisan books:import "D:\data\katalog.xlsx" --dry-run
C:\PHP\php.exe artisan books:import "D:\data\katalog.xlsx"
```

Sheet default adalah `ALL`. Opsi `--replace` mengganti katalog dan dapat gagal jika buku memiliki peminjaman. Opsi `--delete-related-loans` menghapus transaksi terkait dan tidak boleh digunakan tanpa persetujuan eksplisit serta backup terverifikasi.

### 9.2 Impor anggota

```powershell
C:\PHP\php.exe artisan members:import "D:\data\anggota.xlsx" --dry-run
C:\PHP\php.exe artisan members:import "D:\data\anggota.xlsx"
```

Sheet default adalah `Data Anggota (Rapi)`. NIP duplikat harus diselesaikan sebelum impor final.

> Jangan menggunakan file contoh, backup lama, atau database demo sebagai sumber data operasional tanpa verifikasi.

---

## 10. Pemecahan masalah

### 10.1 Tombol, dropdown, atau modal tidak merespons

1. Muat ulang halaman dengan `Ctrl+F5`.
2. Pastikan JavaScript tidak diblokir.
3. Periksa bahwa `/livewire/livewire.min.js` tidak menghasilkan 404.
4. Dari server, gunakan environment production lalu jalankan:

```powershell
C:\PHP\php.exe artisan optimize:clear
C:\PHP\php.exe artisan optimize
C:\PHP\php.exe artisan production:check
```

### 10.2 Halaman 404

Periksa alamat halaman dan kembali ke beranda. Aplikasi menyediakan halaman 404 khusus; jangan menganggap halaman 404 sebagai tanda database rusak.

### 10.3 Halaman 419 atau sesi berakhir

Muat ulang dari beranda, login kembali, atau isi ulang buku tamu. Jangan mengirim ulang form lama dari tab yang sudah lama terbuka.

### 10.4 Halaman 500 atau 503

- Catat waktu, alamat halaman, dan tindakan terakhir.
- Periksa `/ready`.
- Jangan mengaktifkan `APP_DEBUG=true` pada server produksi.
- Pengelola server memeriksa log di `storage/logs` tanpa menampilkan detail teknis kepada pengguna.

### 10.5 Login gagal

- Pastikan email dan kata sandi benar.
- Periksa Caps Lock dan layout keyboard.
- Tunggu jika pembatasan percobaan login aktif.
- Pastikan akun masih aktif.
- Gunakan reset password hanya jika SMTP berfungsi.

### 10.6 Upload sampul gagal

- Gunakan JPG, PNG, atau WEBP maksimal 2 MB.
- Pastikan `storage:link` tersedia.
- Pastikan identity IIS memiliki permission Modify pada `storage` dan `bootstrap/cache`.

### 10.7 Ekspor tidak terunduh

- Pastikan pop-up atau download tidak diblokir browser.
- Ulangi dengan filter lebih sempit jika data sangat banyak.
- Pastikan PHP mempunyai ekstensi `iconv`, `zip`, dan dependensi PDF.
- Periksa ruang disk dan log aplikasi.

### 10.8 Database atau health check gagal

1. Pastikan service MySQL berjalan.
2. Verifikasi `DB_HOST`, nama database, user, dan password pada `.env`.
3. Jalankan pemeriksaan berikut dan hentikan deployment jika salah satunya gagal:

```powershell
C:\PHP\php.exe artisan migrate:status
C:\PHP\php.exe artisan production:check
```

---

## 11. Keamanan dan perlindungan data

- Gunakan HTTPS meskipun aplikasi hanya tersedia di LAN.
- Setiap petugas memakai akun sendiri.
- Superadmin diberikan hanya kepada personel yang benar-benar membutuhkan.
- Jangan mengirim `.env`, password, dump SQL, atau file ekspor melalui percakapan umum.
- Simpan backup terenkripsi pada perangkat atau server lain dan lakukan uji restore.
- Jangan memakai akun database root untuk runtime aplikasi.
- Jangan mengarahkan document root IIS ke root repository.
- Logout saat meninggalkan komputer.
- Tetapkan masa retensi data pengunjung secara tertulis.
- Perlakukan data NIP, nomor kontak, kunjungan, dan peminjaman sebagai data terbatas.

---

## 12. Referensi cepat

### 12.1 Menu admin

| Menu | Path | Pengguna |
|---|---|---|
| Ringkasan | `/admin/dashboard` | Admin, superadmin |
| Koleksi buku | `/admin/buku` | Admin, superadmin |
| Kategori | `/admin/kategori` | Admin, superadmin |
| Peminjaman | `/admin/peminjaman` | Admin, superadmin |
| Anggota | `/admin/anggota` | Admin, superadmin |
| Riwayat sirkulasi | `/admin/riwayat-sirkulasi` | Admin, superadmin |
| Pengunjung | `/admin/pengunjung` | Admin, superadmin |
| Pengguna Admin | `/admin/pengguna` | Superadmin |
| Audit log | `/admin/audit-log` | Superadmin |

### 12.2 Command operasional

```powershell
# Cek aplikasi
C:\PHP\php.exe artisan production:check
C:\PHP\php.exe artisan migrate:status
C:\PHP\php.exe artisan schedule:list

# Bersihkan dan bangun cache production
C:\PHP\php.exe artisan optimize:clear
C:\PHP\php.exe artisan optimize

# Backup
C:\PHP\php.exe artisan database:backup --no-interaction

# Retensi pengunjung
C:\PHP\php.exe artisan visitors:prune
```

### 12.3 Dokumen teknis terkait

- `WINDOWS_DEPLOYMENT.md`: deployment Windows Server, IIS, dan jaringan lokal.
- `DEPLOYMENT.md`: release, rollback, backup, dan health check.
- `RULES.md`: aturan bisnis aplikasi.
- `DATABASE_SCHEMA.md`: struktur database.
- `LAPORAN_PENGUJIAN.md`: catatan pengujian.

---

## Catatan verifikasi edisi 3.0

Panduan ini diperbarui dari source aktif dan pengujian browser terisolasi menggunakan SQLite sementara. Alur yang diperiksa mencakup buku tamu umum dan pegawai, akses katalog setelah check-in, login superadmin, dashboard, kategori, koleksi, anggota, peminjaman, riwayat sirkulasi, pengunjung, pengguna admin, audit log, serta pilihan ekspor.

Pengujian dokumentasi tidak menggunakan database MySQL operasional dan tidak mengubah data perpustakaan sebenarnya. Gate kode terakhir pada saat penyusunan panduan lulus 138 test dengan 592 assertion; hasil tersebut bukan pengganti uji deployment langsung pada Windows Server tujuan.

---

**Pemilik dokumen:** Perpustakaan Kejaksaan Tinggi Jawa Barat

**Klasifikasi:** panduan internal

**Pembaruan berikutnya:** setiap ada perubahan label, alur, aturan bisnis, atau infrastruktur
