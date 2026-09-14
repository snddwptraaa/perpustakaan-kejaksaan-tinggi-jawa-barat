# Sistem Informasi Perpustakaan Kejati Jawa Barat

Aplikasi buku tamu, katalog, inventaris buku, dan pencatatan peminjaman berbasis Laravel 12, Livewire 3, Tailwind CSS, dan Vite.

> Status: kode telah memiliki quality gate, hardening dasar, serta health checks. Go-live tetap bergantung pada konfigurasi infrastruktur dan checklist deployment.

## Persyaratan

- PHP 8.4 dengan ekstensi `ctype`, `curl`, `dom`, `fileinfo`, `filter`, `iconv`, `mbstring`, `openssl`, `pdo`, `pdo_mysql`, `session`, `tokenizer`, `xmlreader`, dan `zip`
- Ekstensi `pdo_sqlite` untuk test otomatis
- Composer 2
- Node.js 20 atau lebih baru dan npm
- MySQL 8/MariaDB yang kompatibel

## Instalasi lokal

```bash
composer install
npm ci
cp .env.example .env
php artisan key:generate
php artisan migrate
php artisan storage:link
npm run build
php artisan serve
```

Isi koneksi database di `.env` sebelum menjalankan migrasi.

Untuk membuat superadmin awal, isi `SEED_ADMIN_EMAIL` dan `SEED_ADMIN_PASSWORD` (minimal 12 karakter), kemudian jalankan:

```bash
php artisan db:seed
```

Kosongkan kembali `SEED_ADMIN_PASSWORD` setelah akun dibuat. Seeder tidak lagi memiliki password bawaan.

Untuk mengisi data demo historis dari Januari 2022 sampai hari ini, jalankan seeder khusus berikut. Seeder ini dapat dijalankan ulang tanpa menggandakan record deterministiknya dan akun petugas demo tidak memiliki password yang diketahui.

```bash
php artisan db:seed --class=DemoDataSeeder
```

Alternatifnya, set `SEED_DEMO_DATA=true` untuk menyertakan data tersebut saat menjalankan `DatabaseSeeder`. Jangan aktifkan data demo pada production yang sudah berisi data nyata.

## Verifikasi

```bash
php artisan test
vendor/bin/pint --test
npm run build
composer audit --locked
npm audit
```

## Production

- Gunakan `APP_ENV=production`, `APP_DEBUG=false`, URL HTTPS, `SESSION_SECURE_COOKIE=true`, dan `SESSION_ENCRYPT=true`.
- Mulai dari `.env.production.example`, simpan sebagai `.env` dengan permission `0600`, lalu isi key dan kredensial melalui secret manager/server—jangan commit file tersebut.
- Jalankan `php artisan migrate --force`, `php artisan storage:link`, `npm ci && npm run build`, lalu `php artisan optimize:clear` dan `php artisan optimize` menggunakan environment production saat deployment.
- Jalankan `php artisan production:check`; hentikan deployment bila satu saja pemeriksaan gagal.
- Arahkan document root web server ke direktori `public/`.
- Jalankan queue worker dengan process supervisor bila queue selain `sync` digunakan.
- Cadangkan database dan `storage/app/public` secara berkala.
- Jangan menyimpan `.env`, dump database, atau kredensial di Git.
- Lihat [DEPLOYMENT.md](DEPLOYMENT.md) untuk prosedur release, backup, rollback, health checks, dan retensi data.
- Untuk Windows Server dengan IIS/FastCGI, ikuti [WINDOWS_DEPLOYMENT.md](WINDOWS_DEPLOYMENT.md) dan gunakan `public/web.config`.

### Checklist rilis

- Pastikan seluruh `php artisan test` lulus pada CI dengan ekstensi `pdo_sqlite`.
- Pastikan job MySQL di CI juga lulus; job tersebut menjalankan migrasi, test suite, dan `production:check` pada MySQL 8.4.
- Siapkan `.env` production: `APP_ENV=production`, `APP_DEBUG=false`, `APP_URL=https://...`, `APP_TIMEZONE=Asia/Jakarta`, `APP_LOCALE=id`, `LOG_LEVEL=warning`, SMTP aktif, serta cookie secure/encrypted.
- Jalankan `composer install --no-dev --optimize-autoloader`, `npm ci && npm run build`, `php artisan migrate --force`, `php artisan storage:link`, `php artisan optimize:clear`, dan `php artisan optimize`.
- Konfigurasikan web server agar hanya direktori `public/` yang dapat diakses dan tambahkan security headers/HSTS pada koneksi HTTPS.
- Jika `QUEUE_CONNECTION` bukan `sync`, jalankan worker dengan Supervisor/systemd dan monitor `failed_jobs`.
- Aktifkan backup database serta `storage/app/public`, lakukan uji restore, dan monitor endpoint `/up` (liveness) serta `/ready` (database/storage).
- Tetapkan durasi retensi yang disetujui pada `VISITOR_RETENTION_DAYS`; nilai `0` menonaktifkan scheduled deletion.
- Bila pemilik data secara resmi menerima retensi tanpa batas, set `VISITOR_INDEFINITE_RETENTION_ACCEPTED=true`; jangan aktifkan tanpa keputusan tertulis.
- Buat release commit/tag dan prosedur rollback sebelum migrasi production.

### Keterbatasan saat ini

- Belum tersedia audit trail administratif yang immutable.
- Rekap dapat difilter/diekspor CSV, tetapi belum ada laporan bulanan teragregasi khusus.
- Backup, TLS, monitoring eksternal, kredensial, dan uji restore merupakan tanggung jawab infrastruktur deployment.
