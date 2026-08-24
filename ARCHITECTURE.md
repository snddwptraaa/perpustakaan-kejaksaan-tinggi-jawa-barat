# Architecture Document
## Sistem Informasi Perpustakaan Kejaksaan Tinggi Jawa Barat

> Status: arsitektur yang sedang diimplementasikan. Terakhir diselaraskan dengan source pada 24 Agustus 2026.

---

## 1. Overview

Aplikasi monolitik berbasis Laravel dengan Livewire untuk interaktivitas tanpa perlu membangun API terpisah (SSR-first). Cocok untuk tim kecil (3 orang) dan timeline magang yang terbatas.

## 2. Tech Stack

| Layer | Teknologi |
|---|---|
| Backend Framework | Laravel 12.x (minimum 12.61.1) |
| Frontend Interactivity | Livewire 3.x |
| Styling | Tailwind CSS 3.x |
| Database | MySQL 8.x |
| Auth | Laravel Breeze + Livewire Volt (khusus admin) |
| Server | Apache/Nginx + PHP-FPM (sesuaikan dengan server instansi) |
| Version Control | Git + GitHub |

## 3. High-Level Architecture

```
┌─────────────────────────────────────────────────┐
│                    Browser                        │
│  (Tamu - tanpa login)   (Admin - login required)  │
└───────────────┬───────────────────┬───────────────┘
                │                   │
                ▼                   ▼
┌─────────────────────────────────────────────────┐
│              Laravel Application                  │
│  ┌───────────────┐        ┌───────────────────┐  │
│  │ Public Routes  │        │  Admin Routes      │  │
│  │ (guest)        │        │  (auth middleware) │  │
│  └───────┬───────┘        └─────────┬──────────┘  │
│          │                          │              │
│  ┌───────▼──────────────────────────▼──────────┐  │
│  │        Livewire Components                    │  │
│  │  - VisitorForm     - Admin\BookManager        │  │
│  │  - CatalogBrowser  - Admin\LoanManager        │  │
│  │  - BookDetail      - Admin\VisitorReport      │  │
│  │                    - Admin\Dashboard          │  │
│  └───────┬──────────────────────────┬───────────┘  │
│          │                          │              │
│  ┌───────▼──────────────────────────▼──────────┐  │
│  │              Eloquent Models                  │  │
│  │  Book, Category, Visitor, Loan, User          │  │
│  └───────────────────┬───────────────────────────┘  │
└──────────────────────┼───────────────────────────────┘
                        ▼
                 ┌─────────────┐
                 │   MySQL     │
                 └─────────────┘
```

## 4. Folder Structure (kunci)

```
app/
├── Http/
│   ├── Controllers/
│   │   └── (minim, sebagian besar logic ada di Livewire)
│   └── Middleware/
│       └── EnsureVisitorHasCheckedIn.php   # gate akses katalog
├── Livewire/
│   ├── Guest/
│   │   ├── VisitorForm.php
│   │   ├── CatalogBrowser.php
│   │   └── BookDetail.php
│   └── Admin/
│       ├── Dashboard.php
│       ├── BookManager.php
│       ├── CategoryManager.php
│       ├── LoanManager.php
│       ├── VisitorReport.php
│       └── UserManager.php
└── Models/
│   ├── Book.php
│   ├── Category.php
│   ├── Visitor.php
│   ├── Loan.php
│   └── User.php

resources/
├── views/
│   ├── livewire/
│   │   ├── guest/
│   │   └── admin/
│   ├── layouts/
│   │   ├── guest.blade.php
│   │   └── admin.blade.php
│   └── components/

database/
├── migrations/
└── seeders/
```

## 5. Alur Akses Katalog (Middleware Pattern)

1. Tamu membuka `/` → landing page
2. Tamu isi form di `/kunjungan` → data disimpan ke tabel `visitors`; session menyimpan flag, ID pengunjung, kategori, dan tanggal check-in
3. Middleware `EnsureVisitorHasCheckedIn` melindungi route `/katalog` — redirect ke `/kunjungan` jika session belum ada
4. Middleware memvalidasi tanggal check-in sehingga akses selalu berakhir ketika tanggal berganti; lifetime teknis session tetap mengikuti `SESSION_LIFETIME`

## 6. Autentikasi Admin

- Menggunakan Laravel Breeze dengan halaman autentikasi Livewire Volt
- Kolom `role` di tabel `users` (`admin` / `superadmin`) untuk pembedaan akses
- Middleware `auth` + `admin` melindungi `/admin/*`; halaman pengguna ditambah middleware `superadmin`
- Login dibatasi 5 percobaan per kombinasi email dan alamat IP sebelum throttle sementara

## 7. Deployment Considerations

- Karena ini website instansi pemerintah, pertimbangkan:
  - Hosting internal/on-premise Kejaksaan atau shared hosting resmi pemerintah
  - HTTPS wajib (data pengunjung termasuk data pribadi)
  - Backup database berkala (mysqldump terjadwal)
- `.env` tidak boleh masuk repo — gunakan `.env.example` sebagai referensi
- Document root wajib menunjuk ke `public/`, jalankan `storage:link`, dan cache config/routes/views saat deployment
- Endpoint `/up` memeriksa liveness proses; `/ready` memeriksa koneksi database dan storage lokal
- Scheduler menjalankan `visitors:prune` setiap hari; perintah aman/nonaktif ketika `VISITOR_RETENTION_DAYS=0`

## 8. Skalabilitas & Batasan

- Skala kecil-menengah: cocok untuk single-server deployment, tidak perlu load balancer/queue worker kompleks
- Query daftar utama sudah memakai pagination dan index `judul`/`penulis`; pencarian `%keyword%` tetap memerlukan strategi full-text/search engine bila data berkembang besar
- Upload cover berada di disk `public`; single-server deployment perlu backup `storage/app/public`, sedangkan multi-server memerlukan object storage bersama
