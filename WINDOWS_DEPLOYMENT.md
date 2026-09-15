# Deployment pada Windows Server

Panduan ini melengkapi `DEPLOYMENT.md` untuk instalasi native pada Windows Server 2022/2025 menggunakan IIS, FastCGI, PHP, dan MySQL pada satu server. Untuk RAM 8 GB, mulai dengan paling banyak empat proses FastCGI dan ukur penggunaan memori sebelum menaikkannya.

## 1. Prasyarat

- Windows Server 2022 atau 2025 x64 yang masih menerima pembaruan keamanan.
- IIS dengan role service **CGI** dan modul **IIS URL Rewrite**.
- PHP 8.4 x64 **Non-Thread Safe** untuk FastCGI beserta ekstensi yang tercantum di `DEPLOYMENT.md`, khususnya `iconv`, `pdo_mysql`, `mbstring`, `openssl`, `fileinfo`, dan `zip`.
- OPcache aktif pada PHP production.
- Microsoft Visual C++ Redistributable yang dipersyaratkan oleh build PHP.
- MySQL 8.0+ atau MariaDB 10.6+ sebagai Windows Service.
- Composer 2. Node.js 22 hanya dibutuhkan saat membangun asset.
- Sertifikat TLS untuk nama host aplikasi.

Jangan gunakan XAMPP, `php artisan serve`, atau akun database `root` sebagai runtime production.

## 2. Tata letak yang disarankan

Contoh dalam panduan ini memakai:

```text
D:\apps\perpustakaan\current
D:\backups\perpustakaan
C:\PHP\php-cgi.exe
```

Atur physical path situs IIS ke:

```text
D:\apps\perpustakaan\current\public
```

Jangan arahkan IIS ke root repository karena `.env`, source code, dan file operasional tidak boleh dapat diunduh dari web.

## 3. PHP dan IIS

1. Aktifkan role **Web Server (IIS) > Application Development > CGI**.
2. Pasang IIS URL Rewrite. File `public/web.config` dalam repository akan meneruskan route yang bukan file/direktori nyata ke `index.php`.
3. Buat FastCGI application yang menunjuk ke `C:\PHP\php-cgi.exe`.
4. Buat handler mapping `*.php` ke FastCGI tersebut.
5. Set `PHPRC=C:\PHP`, `PHP_FCGI_MAX_REQUESTS=10000`, dan samakan `instanceMaxRequests=10000`.
6. Pada server RAM 8 GB, mulai dengan `maxInstances=4`. Sesuaikan setelah mengukur RAM dan waktu respons.
7. Aktifkan OPcache dalam `php.ini`, lalu restart IIS.

Pastikan PHP CLI yang dipakai oleh Composer dan Task Scheduler membaca `php.ini` yang sama:

```powershell
C:\PHP\php.exe --ini
C:\PHP\php.exe -m
C:\PHP\php.exe -r "foreach (['iconv','pdo_mysql','mbstring','openssl','fileinfo','zip'] as $e) { echo $e, ': ', extension_loaded($e) ? 'OK' : 'MISSING', PHP_EOL; }"
```

## 4. Akun dan permission

Gunakan application pool khusus, misalnya `Perpustakaan`, dan jangan jalankan sebagai Administrator. Berikan hak **Read & Execute** pada release, kemudian hak **Modify** hanya pada folder yang perlu ditulis:

```powershell
icacls "D:\apps\perpustakaan\current\storage" /grant "IIS AppPool\Perpustakaan:(OI)(CI)M" /T
icacls "D:\apps\perpustakaan\current\bootstrap\cache" /grant "IIS AppPool\Perpustakaan:(OI)(CI)M" /T
```

Batasi `.env` agar hanya akun deployment dan identity application pool yang dapat membacanya. Jangan menyimpan repository, `.env`, atau backup SQL di dalam `public`.

## 5. Database

Jalankan MySQL sebagai Windows Service dan buat database serta user khusus aplikasi. Jika MySQL berada di server yang sama, gunakan `DB_HOST=127.0.0.1` dan jangan membuka port 3306 ke jaringan kecuali benar-benar diperlukan.

Pastikan direktori `bin` MySQL tersedia pada system `PATH`, karena command `database:backup` dan `database:restore` memanggil `mysqldump.exe` serta `mysql.exe`.

## 6. Menyiapkan release

Jalankan dari PowerShell sebagai akun deployment, bukan identity IIS:

```powershell
Set-Location "D:\apps\perpustakaan\current"
Copy-Item .env.production.example .env
composer install --no-dev --classmap-authoritative --no-interaction
npm ci
npm audit --audit-level=moderate
npm run build
C:\PHP\php.exe artisan key:generate
```

Isi `.env` menggunakan secret production yang sebenarnya. Minimal: URL HTTPS, MySQL, SMTP, session terenkripsi dan secure, log non-debug, serta kebijakan retensi. Jangan menjalankan `key:generate` lagi setelah aplikasi menyimpan data terenkripsi.

Lanjutkan hanya setelah backup tersedia:

```powershell
C:\PHP\php.exe artisan optimize:clear
C:\PHP\php.exe artisan migrate --force
C:\PHP\php.exe artisan storage:link
C:\PHP\php.exe artisan optimize
C:\PHP\php.exe artisan production:check
```

Jika `storage:link` ditolak oleh kebijakan Windows, buat directory junction dari `public\storage` ke `storage\app\public` menggunakan akun administrator, kemudian verifikasi upload dan pembacaan cover buku melalui IIS.

## 7. Task Scheduler

Buat task bernama `Perpustakaan Laravel Scheduler` dengan pengaturan berikut:

- Trigger: setiap hari, ulangi setiap 1 menit tanpa batas waktu.
- Program: `C:\PHP\php.exe`
- Arguments: `artisan schedule:run`
- Start in: `D:\apps\perpustakaan\current`
- Jalankan menggunakan service account dengan permission minimum dan opsi **Run whether user is logged on or not**.
- Jangan izinkan dua instance berjalan bersamaan.

Template production memakai `QUEUE_CONNECTION=sync`, sehingga queue worker terpisah belum diperlukan. Jika nanti memakai queue asynchronous, jalankan worker sebagai Windows Service dan monitor `failed_jobs`.

## 8. Backup, firewall, dan TLS

- Jadwalkan `php artisan database:backup --path=D:\backups\perpustakaan` dan backup `storage\app\public`.
- Salin backup terenkripsi ke perangkat atau server lain; backup pada disk yang sama bukan perlindungan terhadap kerusakan disk.
- Lakukan restore drill ke database non-production dan catat hasilnya.
- Buka port 443 dari jaringan yang membutuhkan aplikasi. Tutup port development dan batasi 3306 ke localhost.
- Pasang binding HTTPS pada IIS dan redirect HTTP ke HTTPS di level IIS/reverse proxy.

## 9. Gate sebelum go-live

Semua perintah berikut harus lulus pada server tujuan:

```powershell
composer audit --locked
composer check-platform-reqs --no-dev
npm audit --audit-level=moderate
C:\PHP\php.exe artisan migrate:status
C:\PHP\php.exe artisan schedule:list
C:\PHP\php.exe artisan production:check
```

Kemudian verifikasi:

- `/up` mengembalikan HTTP 200.
- `/ready` mengembalikan HTTP 200 dan `{"status":"ready"}`.
- Buku tamu, keluar dari katalog, login admin, CRUD/upload buku, sirkulasi, PDF/XLSX/CSV, dan reset password SMTP.
- Tampilan desktop/mobile, navigasi keyboard, browser console, serta beberapa komputer LAN secara bersamaan.
- Restart Windows: IIS, MySQL, scheduler, aplikasi, dan health check pulih tanpa login interaktif.

Ikuti juga prosedur rollback, retensi, dan pencatatan deployment dalam `DEPLOYMENT.md`.
