# Architecture Document
## Sistem Informasi Perpustakaan Kejaksaan Tinggi Jawa Barat

---

## 1. Overview

Aplikasi monolitik berbasis Laravel dengan Livewire untuk interaktivitas tanpa perlu membangun API terpisah (SSR-first). Cocok untuk tim kecil (3 orang) dan timeline magang yang terbatas.

## 2. Tech Stack

| Layer | Teknologi |
|---|---|
| Backend Framework | Laravel 11.x |
| Frontend Interactivity | Livewire 3.x |
| Styling | Tailwind CSS 3.x |
| Database | MySQL 8.x |
| Auth | Laravel Breeze/Fortify (khusus admin) |
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
│       └── VisitorReport.php
├── Models/
│   ├── Book.php
│   ├── Category.php
│   ├── Visitor.php
│   ├── Loan.php
│   └── User.php
└── Policies/
    └── (jika perlu role-based permission)

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
2. Tamu isi form di `/kunjungan` → data disimpan ke tabel `visitors`, session diset `session(['visitor_checked_in' => true, 'visitor_id' => $id])`
3. Middleware `EnsureVisitorHasCheckedIn` melindungi route `/katalog` — redirect ke `/kunjungan` jika session belum ada
4. Session berlaku per hari kunjungan (bisa di-reset tiap hari atau tiap browser session, sesuai kebijakan)

## 6. Autentikasi Admin

- Menggunakan Laravel Breeze (Livewire stack) untuk login admin
- Kolom `role` di tabel `users` (`admin` / `superadmin`) untuk pembedaan akses
- Middleware `auth` + custom middleware `role:admin` untuk proteksi route `/admin/*`

## 7. Deployment Considerations

- Karena ini website instansi pemerintah, pertimbangkan:
  - Hosting internal/on-premise Kejaksaan atau shared hosting resmi pemerintah
  - HTTPS wajib (data pengunjung termasuk data pribadi)
  - Backup database berkala (mysqldump terjadwal)
- `.env` tidak boleh masuk repo — gunakan `.env.example` sebagai referensi

## 8. Skalabilitas & Batasan

- Skala kecil-menengah: cocok untuk single-server deployment, tidak perlu load balancer/queue worker kompleks
- Jika volume buku/pengunjung besar (>10rb data), pertimbangkan indexing tambahan di kolom pencarian (`judul`, `penulis`) dan pagination di sisi query, bukan collection
