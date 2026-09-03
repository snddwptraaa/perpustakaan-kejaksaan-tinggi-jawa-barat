#!/usr/bin/env python3
"""
Script untuk membuat dokumen PDF panduan penggunaan aplikasi Perpustakaan Kejaksaan Tinggi Jawa Barat
"""

from fpdf import FPDF
from datetime import datetime

class PanduanPDF(FPDF):
    def header(self):
        # Logo (placeholder)
        self.set_font('Arial', 'B', 16)
        self.cell(0, 10, 'BUKU PANDUAN PENGGUNAAN', 0, 1, 'C')
        self.set_font('Arial', 'B', 14)
        self.cell(0, 8, 'SISTEM INFORMASI PERPUSTAKAAN', 0, 1, 'C')
        self.set_font('Arial', '', 12)
        self.cell(0, 6, 'KEJAKSAAN TINGGI JAWA BARAT', 0, 1, 'C')
        self.ln(10)

    def footer(self):
        self.set_y(-15)
        self.set_font('Arial', 'I', 8)
        self.cell(0, 10, f'Halaman {self.page_no()}', 0, 0, 'C')
        self.cell(0, 10, f'Dicetak pada {datetime.now().strftime("%d %B %Y")}', 0, 0, 'R')

    def chapter_title(self, title):
        self.set_font('Arial', 'B', 12)
        self.cell(0, 10, title, 0, 1, 'L')
        self.ln(2)

    def chapter_body(self, body):
        self.set_font('Arial', '', 10)
        self.multi_cell(0, 6, body)
        self.ln()

def create_manual():
    pdf = PanduanPDF()
    pdf.add_page()
    
    # Pendahuluan
    pdf.chapter_title('1. PENDAHULUAN')
    pdf.chapter_body(
        'Sistem ini digunakan untuk:\n'
        '- Tamu: Mengakses katalog buku setelah mengisi form kunjungan\n'
        '- Admin: Mengelola data buku, peminjaman, dan laporan kunjungan\n\n'
        
        'CATATAN PENTING:\n'
        '❌ Tidak ada fitur peminjaman online (semua transaksi dicatat manual oleh petugas)\n'
        '❌ Tamu tidak memiliki akun/login\n'
        '✅ Data pengunjung tersimpan aman dan hanya bisa diakses admin'
    )
    
    # Untuk Tamu
    pdf.chapter_title('2. UNTUK TAMU/PEMBACA')
    pdf.chapter_body(
        'A. Mengakses Katalog Buku\n'
        '1. Buka halaman utama (/)\n'
        '2. Isi form kunjungan berikut:\n'
        '   - Kategori: Pilih "Pegawai" atau "Umum"\n'
        '   - Nama Lengkap: Wajib diisi\n'
        '   - NIP/NRP: Wajib untuk kategori Pegawai\n'
        '   - Instansi/Unit Kerja: Wajib diisi\n'
        '   - No. HP/Email: Opsional\n'
        '   - Keperluan: Pilih atau tulis tujuan kunjungan\n'
        '   - Persetujuan Privasi: Wajib dicentang\n'
        '3. Klik "Lanjutkan ke Katalog"\n\n'
        
        '⚠️ Form hanya bisa diisi 8 kali per IP dalam 1 menit (anti-spam)\n\n'
        
        'B. Mencari Buku\n'
        'Di halaman katalog (/katalog):\n'
        '1. Gunakan fitur pencarian dengan:\n'
        '   - Kata kunci (judul/penulis)\n'
        '   - Filter kategori (misal: Hukum Pidana)\n'
        '   - Status ketersediaan (Tersedia/Tidak Tersedia)\n'
        '2. Klik judul buku untuk melihat detail\n\n'
        
        'C. Informasi Detail Buku\n'
        'Halaman detail buku menampilkan:\n'
        '- Judul, penulis, penerbit\n'
        '- Tahun terbit, jumlah halaman\n'
        '- Nomor ISBN & klasifikasi\n'
        '- Lokasi rak di perpustakaan\n'
        '- Status ketersediaan:\n'
        '  - ✅ "Tersedia: X dari Y eksemplar"\n'
        '  - ❌ "Tidak Tersedia" (jika semua sedang dipinjam)\n\n'
        
        'ℹ️ Jika buku tidak tersedia, silakan tanyakan ke petugas untuk estimasi pengembalian'
    )
    
    # Untuk Admin
    pdf.chapter_title('3. UNTUK ADMIN/PETUGAS')
    pdf.chapter_body(
        'A. Autentikasi\n'
        '1. Buka halaman login (/login)\n'
        '2. Masukkan:\n'
        '   - Email terdaftar\n'
        '   - Password (minimal 8 karakter)\n'
        '3. Klik "Masuk"\n\n'
        
        '🔒 Akun terkunci sementara setelah 5 percobaan gagal\n\n'
        
        'B. Dashboard\n'
        'Setelah login, halaman utama admin menampilkan:\n'
        '- 📚 Jumlah total buku & eksemplar\n'
        '- 📊 Statistik peminjaman aktif & terlambat\n'
        '- 👥 Jumlah pengunjung hari ini\n'
        '- 📅 5 transaksi peminjaman terakhir\n\n'
        
        'C. Mengelola Buku\n'
        'Menu: /admin/buku\n\n'
        
        'Menambah Buku:\n'
        '1. Klik "Tambah Buku"\n'
        '2. Isi form dengan:\n'
        '   - Kategori (wajib dipilih)\n'
        '   - Judul & penulis (wajib)\n'
        '   - Data pendukung (penerbit, tahun terbit, dll)\n'
        '   - Stok & stok tersedia (otomatis sinkron)\n'
        '   - Sampul buku (opsional, maks 2MB)\n'
        '3. Klik "Simpan"\n\n'
        
        'Mengedit Buku:\n'
        '- Klik ikon pensil pada daftar buku\n'
        '- Perubahan stok akan menghitung ulang stok tersedia berdasarkan peminjaman aktif\n\n'
        
        '❌ Buku dengan riwayat peminjaman tidak bisa dihapus\n\n'
        
        'D. Mengelola Peminjaman\n'
        'Menu: /admin/peminjaman\n\n'
        
        'Mencatat Peminjaman:\n'
        '1. Klik "Tambah Peminjaman"\n'
        '2. Pilih buku dari daftar (hanya buku tersedia)\n'
        '3. Isi data peminjam:\n'
        '   - Nama lengkap (wajib)\n'
        '   - NIP/Instansi (opsional)\n'
        '   - Tanggal pinjam & jatuh tempo (otomatis +7 hari)\n'
        '4. Klik "Simpan"\n\n'
        
        'Memproses Pengembalian:\n'
        '- Klik tombol "Kembalikan" pada daftar peminjaman aktif\n'
        '- Sistem otomatis:\n'
        '  - Mengisi tanggal kembali\n'
        '  - Menambah stok tersedia\n'
        '  - Memperbarui status\n\n'
        
        'Status Peminjaman:\n'
        '- 🟢 Dikembalikan: Sudah dikembalikan\n'
        '- 🔵 Dipinjam: Masih dalam masa pinjam\n'
        '- 🟠 Jatuh Tempo Dekat: 1-2 hari sebelum jatuh tempo\n'
        '- 🔴 Terlambat: Melewati tanggal jatuh tempo\n\n'
        
        'E. Laporan Pengunjung\n'
        'Menu: /admin/pengunjung\n\n'
        
        'Menampilkan:\n'
        '- Daftar pengunjung harian\n'
        '- Filter berdasarkan:\n'
        '  - Tanggal kunjungan\n'
        '  - Kategori (Pegawai/Umum)\n'
        '  - Kata kunci pencarian\n'
        '- Tombol "Ekspor CSV" untuk laporan\n\n'
        
        'F. Manajemen Akun (Superadmin)\n'
        'Menu: /admin/pengguna\n\n'
        
        'Hanya bisa diakses oleh superadmin:\n'
        '- Menambah/menghapus akun admin\n'
        '- Mengubah peran (admin/superadmin)\n'
        '- Reset password\n\n'
        
        '⚠️ Akun terakhir superadmin tidak bisa dihapus'
    )
    
    # Catatan Penting
    pdf.chapter_title('4. CATATAN PENTING')
    pdf.chapter_body(
        'Untuk Tamu:\n'
        '- Akses katalog hanya berlaku untuk hari kunjungan yang sama\n'
        '- Form kunjungan harus diisi ulang jika tanggal berganti\n'
        '- Data diri tidak ditampilkan di katalog publik\n\n'
        
        'Untuk Admin:\n'
        '- Pastikan stok tersedia sebelum mencatat peminjaman\n'
        '- Sampul buku otomatis dihapus saat menghapus buku\n'
        '- Laporan CSV sudah di-antisipasi terhadap formula injection'
    )
    
    # Kontak Bantuan
    pdf.chapter_title('5. KONTAK BANTUAN')
    pdf.chapter_body(
        'Untuk kendala teknis atau pertanyaan lebih lanjut:\n'
        '- Hubungi petugas perpustakaan\n'
        '- Atau laporkan melalui email: perpustakaan@kejati-jabar.go.id\n\n'
        
        'Dokumen ini terakhir diperbarui: 28 Agustus 2026\n'
        'Sistem menggunakan Laravel 12.x dan Livewire 3.x\n'
        '© 2026 Kejaksaan Tinggi Jawa Barat'
    )
    
    # Simpan PDF
    pdf.output('/home/lcfr/perpustakaan-kejati-jawa-barat/Buku_Panduan_Penggunaan_Perpus_Kejati_Jabar.pdf')
    print("PDF panduan penggunaan telah dibuat: /home/lcfr/perpustakaan-kejati-jawa-barat/Buku_Panduan_Penggunaan_Perpus_Kejati_Jabar.pdf")

if __name__ == "__main__":
    create_manual()