# Design Guidelines
## Sistem Informasi Perpustakaan Kejaksaan Tinggi Jawa Barat

---

## 1. Prinsip Desain

- **Formal & institusional** — ini website instansi pemerintah (Kejaksaan), bukan produk konsumer. Hindari desain yang terlalu playful/casual.
- **Jelas & fungsional** — prioritas utama adalah kemudahan tamu mencari buku dan petugas mencatat transaksi, bukan estetika berlebihan.
- **Konsisten** — gunakan komponen yang sama berulang (card buku, tabel admin, form) di seluruh aplikasi.
- **Aksesibel** — kontras warna cukup, ukuran font terbaca, form mudah diisi di perangkat tablet/desktop meja resepsionis.

## 2. Palet Warna

Palet aplikasi mengambil referensi dari lambang pada logo yang diberikan: hijau sebagai warna identitas utama, kuning emas sebagai aksen, serta hitam-putih sebagai fondasi yang formal dan mudah dibaca.

| Peran | Warna | Hex (referensi) |
|---|---|---|
| Primary | Hijau lambang | `#166534` |
| Primary Dark (hover/active) | Hijau tua | `#064E3B` |
| Secondary | Hijau daun | `#15803D` |
| Secondary/Accent | Kuning emas | `#F4C542` |
| Accent Dark | Emas gelap untuk teks kecil | `#806000` |
| Background | Putih kehijauan | `#F7FAF8` |
| Surface/Card | Putih dengan border tipis | `#FFFFFF` + `border-stone-200` |
| Text Primary | Abu gelap/hitam | `#1F2937` |
| Text Secondary | Abu sedang | `#6B7280` |
| Success | Hijau status | `#16A34A` (status "tersedia", "dikembalikan") |
| Warning | Kuning/oranye | `#D97706` (status "jatuh tempo dekat") |
| Danger | Merah | `#DC2626` (status "terlambat", hapus data) |

> Catatan: nilai di atas adalah aproksimasi UI dari logo referensi. Jika tersedia panduan identitas visual resmi Kejaksaan, token dapat disesuaikan tanpa mengubah struktur komponen.

Token Tailwind utama tersedia pada `tailwind.config.js` melalui namespace `kejati` (`kejati`, `kejati-dark`, `kejati-green`, `kejati-gold`, dan `kejati-gold-dark`). Warna status success/warning/danger tetap dibedakan dari warna brand agar makna status tidak rancu.

> Kontras: hijau gelap digunakan untuk teks dan tombol di permukaan terang; kuning emas digunakan sebagai aksen atau teks di atas hijau gelap, bukan sebagai teks body di atas putih.

> Catatan: sesuaikan hex pasti dengan logo/branding resmi Kejaksaan Tinggi Jawa Barat jika tersedia panduan identitas visual instansi.

## 2.1. Referensi Logo

Logo referensi menampilkan komposisi hijau, emas, hitam, dan putih. Aplikasi menggunakan komposisi tersebut secara proporsional: hijau untuk navigasi dan aksi utama, emas untuk penanda fokus/aksen, dan bidang putih untuk menjaga keterbacaan data administrasi.

Karena file gambar logo belum tersimpan di repository, implementasi saat ini menggunakan token CSS/Tailwind dan tidak mengubah logo menjadi aset baru.

## 2.2. Token implementasi

| Token Tailwind | Nilai | Penggunaan |
|---|---|---|
| `kejati` | `#166534` | Tombol utama, tautan aksi, header publik |
| `kejati-dark` | `#064E3B` | Sidebar admin, hover tombol utama, bidang gelap |
| `kejati-green` | `#15803D` | Aksen hijau sekunder |
| `kejati-gold` | `#F4C542` | Aksen logo, focus ring, highlight |
| `kejati-gold-dark` | `#B89100` | Aksen emas pada elemen yang memerlukan kontras |
| `kejati-ink` | `#1F2937` | Teks utama |
| `kejati-canvas` | `#F7FAF8` | Latar halaman |
| `kejati-surface` | `#FFFFFF` | Card dan surface |

## 3. Tipografi

- Font: **Inter** atau **Plus Jakarta Sans** (bersih, formal, mendukung karakter Indonesia dengan baik) — fallback ke `sans-serif` Tailwind default
- Heading: font-semibold hingga font-bold
- Body text: font-normal, ukuran minimal 14px (16px untuk form supaya nyaman diisi tamu segala usia)

| Elemen | Tailwind class (contoh) |
|---|---|
| H1 (judul halaman) | `text-2xl md:text-3xl font-bold text-gray-900` |
| H2 (section) | `text-xl font-semibold text-gray-800` |
| Body | `text-sm md:text-base text-gray-700` |
| Label form | `text-sm font-medium text-gray-700` |
| Caption/meta | `text-xs text-gray-500` |

## 4. Layout

### Halaman Publik (Tamu)
- Layout sederhana, single column untuk form kunjungan
- Katalog buku: grid card, responsive (`grid-cols-1 sm:grid-cols-2 lg:grid-cols-4`)
- Header berisi logo Kejaksaan + nama "Perpustakaan Kejati Jawa Barat"
- Tidak ada sidebar kompleks — fokus ke konten

### Admin Panel
- Layout dengan sidebar navigasi tetap (kiri) + konten utama (kanan)
- Sidebar: Dashboard, Buku, Kategori, Peminjaman, Pengunjung, (Users — jika superadmin)
- Tabel data menggunakan style zebra-striping ringan + pagination Livewire

## 5. Komponen Utama

**Card Buku (katalog)**
- Cover buku (atau placeholder jika tidak ada gambar)
- Judul (truncate 2 baris)
- Penulis
- Badge status: "Tersedia" (hijau) / "Tidak Tersedia" (abu/merah)

**Badge Status Peminjaman**
- Dipinjam → abu/biru
- Dikembalikan → hijau
- Terlambat → merah

**Form Kunjungan (guest)**
- Field minimal, jelas, satu kolom, tombol submit besar (mudah disentuh di tablet)
- Validasi inline (Livewire real-time validation)

**Tabel Admin**
- Header sticky untuk tabel panjang
- Aksi (edit/hapus) berupa icon button dengan tooltip
- Konfirmasi modal sebelum hapus data

## 6. Iconography

- Gunakan set ikon konsisten: **Heroicons** (cocok dengan ekosistem Tailwind/Livewire)
- Ikon buku, kalender (tanggal pinjam), user (pengunjung), check-circle (tersedia), exclamation (terlambat)

## 7. Responsiveness

- Prioritas: desktop untuk admin panel (petugas kerja di meja), tapi tetap harus berfungsi baik di tablet
- Katalog publik: mobile-first, karena tamu mungkin akses dari HP sambil menunggu
- Breakpoint Tailwind standar: `sm`, `md`, `lg`, `xl`

## 8. Tone & Microcopy

- Bahasa Indonesia formal namun tetap ramah, contoh:
  - "Selamat datang di Perpustakaan Kejaksaan Tinggi Jawa Barat"
  - "Silakan isi data kunjungan Anda untuk melihat katalog buku"
  - "Buku belum tersedia. Silakan tanyakan ke petugas untuk estimasi pengembalian."
- Hindari istilah teknis di sisi tamu (contoh: jangan tampilkan "stok_tersedia", tapi "Tersedia: 2 dari 5 eksemplar")
