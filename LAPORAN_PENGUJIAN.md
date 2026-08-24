# Laporan Pengujian — Perpustakaan Kejati Jawa Barat

**Tanggal:** 24 Agustus 2026 · **Target:** http://127.0.0.1:8000 (Laravel + Livewire 3)
**Server:** `php artisan serve` (PHP 8.5.9, single-worker) · **DB:** MySQL (`perpus_kejatijabar`)
**Data uji:** 32 buku, 14 kategori, 1.982 pengunjung, 173 peminjaman, 4 user

---

## Skor Ringkas

| Aspek | Nilai | Catatan |
|---|---|---|
| Keamanan | **A−** | Semua vektor umum lolos; 1 temuan konfigurasi (debug mode) |
| Ketahanan | **A** | Input ekstrem & data kosong ditangani rapi |
| Performa | **B** (lokal/dev) | Cepat untuk dev; arsitektur dev-server membatasi throughput produksi |

---

## 1. Keamanan

### ✅ Lolos

| Tes | Metode | Hasil |
|---|---|---|
| Security headers | Inspeksi respons | CSP, X-Content-Type-Options, X-Frame-Options, Referrer-Policy, Permissions-Policy semuanya terpasang |
| Eksposur file sensitif | `/.env`, `.env.backup`, `.git/config`, `composer.json` → 404; log → 403 | Dokumen root benar (public/) |
| Proteksi admin | `/admin/*` tanpa login → 302 ke `/login`; middleware role `admin`/`superadmin` aktif | Aman |
| SQL Injection | Payload `' OR 1=1--`, `UNION SELECT`, `';DROP TABLE--` pada `search`, ID detail, URL rusak | Tidak ada error SQL; Eloquent pakai binding |
| XSS refleksi | `<script>alert(1)</script>` di parameter & path | Tidak ada output mentah (Blade auto-escape); tidak ada `{!! !!}` di view |
| CSRF | POST `/livewire/update` tanpa token → **419** | Verifikasi Laravel aktif |
| Rate limiting | Kode `VisitorForm::submit()` memakai RateLimiter 8 percobaan/60 detik per IP | Ada |
| Session security | Cookie httponly + samesite=lax, driver database, gate kunjungan harian | Baik |

### ⚠️ Perlu diperhatikan

1. **`APP_DEBUG=true`** — exception membocorkan stack trace penuh (path file, struktur vendor).
   Wajar untuk lokal, tapi **wajib `false` saat deploy** (beserta `APP_ENV=production`).
2. **HSTS belum aktif** — middleware baru mengirimnya bila HTTPS; otomatis beres setelah pasang SSL.
3. *(Catatan, bukan celah)* `/katalog/1%27` tetap merender buku #1 karena MySQL mentransmisikan
   `'1'` → angka secara longgar. Binding tetap bekerja; tidak bisa dieksploitasi.

---

## 2. Ketahanan

| Skenario | Hasil |
|---|---|
| `page=99999`, `page=-5` | HTTP 200 — paginator Laravel menanganinya, tidak crash |
| Pencarian 5000 karakter | HTTP 200 — ditangani normal |
| ID buku `999999`, `abc`, `0`, `-1` | HTTP 404 halaman rapi (bukan stack trace) |
| Sesi tanpa/palsu pada halaman bergate | Redirect bersih ke `/kunjungan` dengan pesan informatif |
| Buku tamu: validasi ganda sisi client (Livewire) & server | NIP wajib hanya utk pegawai; privacy consent dicatat dengan timestamp |

Tidak ditemukan unhandled exception pada seluruh skenario input buruk yang diuji.

---

## 3. Performa

### Latensi dasar (20 request berurutan/halaman)

| Halaman | Avg | p95 | Max |
|---|---|---|---|
| `/kunjungan` | 23 ms | 46 ms | 46 ms |
| `/katalog` | 25 ms | 29 ms | 29 ms |
| `/katalog/1` | 21 ms | 26 ms | 26 ms |

### Load test konkuren (halaman `/katalog`)

| Konkurensi | Wall time | Sukses | p50 per-request | Max |
|---|---|---|---|---|
| 5 | 147 ms | 5/5 | 88 ms | 142 ms |
| 20 | 495 ms | 20/20 | 278 ms | 478 ms |
| 50 | 1.129 ms | 50/50 | 560 ms | 1.105 ms |

**Nol error, nol timeout** sampai 50 koneksi paralel.

### Temuan arsitektural penting

Wall-time 50 konkuren (1.129 ms) ≈ 50 × latensi tunggal (22 ms) → request
**diproses serial**. Penyebabnya: `php artisan serve` = PHP built-in server
single-threaded (diverifikasi: 0 child process). Artinya:

- Throughput langit-langit ±45–90 req/s apa pun jumlah klien.
- Satu request lambat akan menahan semua pengunjung lain.
- **Ini keterbatasan tool dev, bukan aplikasi Anda** — kode Laravel-nya sendiri cepat.

### Rekomendasi sebelum produksi

1. Ganti serving: **nginx + PHP-FPM** (atau FrankenPHP/Octane) → paralelisme sungguhan.
2. Aktifkan OPcache (`opcache.enable=1`, `validate_timestamps=0` di prod).
3. `php artisan config:cache route:cache view:cache`.
4. Cache query statistik pengunjung di dashboard (COUNT per hari) bila datanya besar.
5. Set `APP_DEBUG=false`, `APP_ENV=production`, pasang HTTPS → HSTS ikut aktif otomatis.

---

## Metodologi

- Sesi pengunjung diperoleh lewat **alur resmi Livewire** (GET snapshot → POST update
  dengan CSRF + cookie sesi), bukan manipulasi data — sehingga hasil mencerminkan
  kondisi nyata pengunjung.
- Load test dari mesin yang sama (localhost) — angka absolut akan sedikit berbeda
  di jaringan produksi; pola serialisasi tetap valid karena terjadi di layer server.
