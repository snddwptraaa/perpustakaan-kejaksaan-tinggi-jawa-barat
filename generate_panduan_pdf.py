#!/usr/bin/env python3
"""
Membuat dokumen PDF panduan penggunaan yang rapi dan profesional
untuk Sistem Informasi Perpustakaan Kejaksaan Tinggi Jawa Barat.
"""

from fpdf import FPDF
from datetime import datetime


# ── Warna Brand ──────────────────────────────────────────────────
KEJATI_GREEN   = (22, 101, 52)    # #166534
KEJATI_DARK    = (6, 78, 59)      # #064E3B
KEJATI_GREEN2  = (21, 128, 61)    # #15803D
KEJATI_GOLD    = (244, 197, 66)   # #F4C542
KEJATI_GOLD_DK = (184, 145, 0)    # #B89100
INK            = (31, 41, 55)     # #1F2937
INK_LIGHT      = (107, 114, 128)  # #6B7280
WHITE          = (255, 255, 255)
SURFACE        = (247, 250, 248)  # #F7FAF8
BORDER         = (231, 229, 228)  # stone-200
SUCCESS        = (22, 163, 74)
DANGER         = (220, 38, 38)
WARNING        = (217, 119, 6)
INFO           = (37, 99, 235)


class PanduanPDF(FPDF):
    """PDF document class with professional formatting."""

    is_cover = True  # skip header/footer on cover

    def __init__(self, *args, **kwargs):
        super().__init__(*args, **kwargs)
        font_dir = "/usr/share/fonts/TTF"
        self.add_font("DejaVu", "", f"{font_dir}/DejaVuSans.ttf")
        self.add_font("DejaVu", "B", f"{font_dir}/DejaVuSans-Bold.ttf")
        self.add_font("DejaVu", "I", f"{font_dir}/DejaVuSans-Oblique.ttf")
        self.add_font("DejaVu", "BI", f"{font_dir}/DejaVuSans-BoldOblique.ttf")
        self.font_family = "DejaVu"

    def set_font(self, family=None, style="", size=0):
        # Keep existing calls using Helvetica, but render through Unicode DejaVu.
        if family in (None, "Helvetica", "Arial"):
            family = "DejaVu"
        return super().set_font(family, style, size)

    # ── Header & Footer ──────────────────────────────────────────
    def header(self):
        if self.is_cover:
            return
        # Garis hijau tipis di atas
        self.set_draw_color(*KEJATI_GREEN)
        self.set_line_width(0.6)
        self.line(15, 12, self.w - 15, 12)
        # Teks kecil
        self.set_y(6)
        self.set_font("Helvetica", "I", 7)
        self.set_text_color(*INK_LIGHT)
        self.cell(0, 5, "Panduan Penggunaan — Perpustakaan Kejati Jawa Barat", align="L")
        self.cell(0, 5, "", align="R")
        self.ln(10)

    def footer(self):
        if self.is_cover:
            return
        self.set_y(-18)
        # Garis tipis
        self.set_draw_color(*BORDER)
        self.set_line_width(0.3)
        self.line(15, self.h - 18, self.w - 15, self.h - 18)
        self.set_font("Helvetica", "", 8)
        self.set_text_color(*INK_LIGHT)
        self.cell(0, 8, f"Halaman {self.page_no()}", align="C")
        self.ln(4)
        self.set_font("Helvetica", "I", 7)
        self.cell(0, 4, f"© 2026 Kejaksaan Tinggi Jawa Barat", align="C")

    # ── Helper Methods ───────────────────────────────────────────
    def _reset_cursor(self):
        self.set_x(15)

    def section_heading(self, number: str, title: str):
        """Section heading with green bar."""
        self.ln(6)
        # Bar background
        y = self.get_y()
        self.set_fill_color(*KEJATI_GREEN)
        self.rect(15, y, self.w - 30, 9, "F")
        # Number + title
        self.set_xy(18, y + 1)
        self.set_font("Helvetica", "B", 11)
        self.set_text_color(*WHITE)
        self.cell(0, 7, f"{number}   {title.upper()}")
        self.set_y(y + 13)
        self.set_text_color(*INK)

    def sub_heading(self, label: str):
        """Sub-section heading (A, B, C...)."""
        self.ln(4)
        self.set_font("Helvetica", "B", 10)
        self.set_text_color(*KEJATI_GREEN)
        self._reset_cursor()
        self.cell(0, 7, label)
        self.ln(7)
        self.set_text_color(*INK)

    def body_text(self, text: str):
        self.set_font("Helvetica", "", 9.5)
        self.set_text_color(*INK)
        self._reset_cursor()
        self.multi_cell(self.w - 30, 5.2, text)
        self.ln(1)

    def bullet_list(self, items: list[str], indent: int = 20):
        self.set_font("Helvetica", "", 9.5)
        self.set_text_color(*INK)
        for item in items:
            self.set_x(indent)
            self.cell(4, 5.2, "•")
            self.multi_cell(self.w - indent - 4 - 15, 5.2, item)
            self.ln(0.5)

    def numbered_list(self, items: list[str], start: int = 1, indent: int = 20):
        self.set_font("Helvetica", "", 9.5)
        self.set_text_color(*INK)
        for i, item in enumerate(items, start=start):
            self.set_x(indent)
            num_w = self.get_string_width(f"{i}.") + 2
            self.cell(num_w, 5.2, f"{i}.")
            self.multi_cell(self.w - indent - num_w - 15, 5.2, item)
            self.ln(0.5)

    def callout_box(self, text: str, kind: str = "info"):
        """Colored callout box (info / warning / danger / success)."""
        colors = {
            "info":    (INFO,    (239, 246, 255)),
            "warning": (WARNING, (255, 251, 235)),
            "danger":  (DANGER,  (254, 242, 242)),
            "success": (SUCCESS, (240, 253, 244)),
        }
        accent, bg = colors.get(kind, colors["info"])
        y = self.get_y()
        # Background
        self.set_fill_color(*bg)
        # Calculate height via dry-run
        self.set_font("Helvetica", "", 9)
        lines = self.multi_cell(
            self.w - 30 - 10, 5, text, dry_run=True, output="LINES"
        )
        box_h = len(lines) * 5 + 6
        self.rect(15, y, self.w - 30, box_h, "F")
        # Left accent bar
        self.set_fill_color(*accent)
        self.rect(15, y, 3, box_h, "F")
        # Text
        self.set_xy(22, y + 3)
        self.set_text_color(*INK)
        self.multi_cell(self.w - 30 - 10, 5, text)
        self.set_y(y + box_h + 3)

    def info_table(self, headers: list[str], rows: list[list[str]], col_widths: list[float] | None = None):
        """Simple styled table."""
        if col_widths is None:
            w = (self.w - 30) / len(headers)
            col_widths = [w] * len(headers)
        # Header row
        self.set_fill_color(*KEJATI_GREEN)
        self.set_text_color(*WHITE)
        self.set_font("Helvetica", "B", 8.5)
        self._reset_cursor()
        for i, h in enumerate(headers):
            self.cell(col_widths[i], 7, h, border=0, fill=True)
        self.ln(7)
        # Data rows
        self.set_text_color(*INK)
        self.set_font("Helvetica", "", 8.5)
        for ri, row in enumerate(rows):
            fill = ri % 2 == 1
            if fill:
                self.set_fill_color(*SURFACE)
            self._reset_cursor()
            for i, cell in enumerate(row):
                self.cell(col_widths[i], 6.5, cell, border=0, fill=fill)
            self.ln(6.5)
        self.ln(3)


# ══════════════════════════════════════════════════════════════════
def build_pdf() -> PanduanPDF:
    pdf = PanduanPDF(orientation="P", unit="mm", format="A4")
    pdf.set_auto_page_break(auto=True, margin=22)

    # ── HALAMAN SAMPUL ───────────────────────────────────────────
    pdf.add_page()
    pdf.is_cover = True
    # Background hijau gelap
    pdf.set_fill_color(*KEJATI_DARK)
    pdf.rect(0, 0, pdf.w, pdf.h, "F")

    # Aksen emas di atas
    pdf.set_fill_color(*KEJATI_GOLD)
    pdf.rect(0, 0, pdf.w, 4, "F")

    # Judul utama
    pdf.set_y(55)
    pdf.set_font("Helvetica", "B", 28)
    pdf.set_text_color(*WHITE)
    pdf.cell(0, 14, "BUKU PANDUAN", align="C")
    pdf.ln(14)
    pdf.set_font("Helvetica", "B", 24)
    pdf.cell(0, 12, "PENGGUNAAN APLIKASI", align="C")
    pdf.ln(18)

    # Garis emas pemisah
    pdf.set_draw_color(*KEJATI_GOLD)
    pdf.set_line_width(0.8)
    cx = pdf.w / 2
    pdf.line(cx - 40, pdf.get_y(), cx + 40, pdf.get_y())
    pdf.ln(12)

    # Subjudul
    pdf.set_font("Helvetica", "", 14)
    pdf.set_text_color(*KEJATI_GOLD)
    pdf.cell(0, 8, "Sistem Informasi Perpustakaan", align="C")
    pdf.ln(8)
    pdf.cell(0, 8, "Kejaksaan Tinggi Jawa Barat", align="C")
    pdf.ln(20)

    # Versi & tanggal
    pdf.set_font("Helvetica", "", 10)
    pdf.set_text_color(180, 210, 190)
    pdf.cell(0, 6, "Versi 2.0  —  30 Agustus 2026", align="C")
    pdf.ln(6)
    pdf.cell(0, 6, "Dibuat otomatis berdasarkan pengujian fitur aplikasi", align="C")

    # Aksen emas bawah
    pdf.set_fill_color(*KEJATI_GOLD)
    pdf.rect(0, pdf.h - 4, pdf.w, 4, "F")

    # ── HALAMAN DAFTAR ISI ───────────────────────────────────────
    pdf.add_page()
    pdf.is_cover = False
    pdf.set_y(20)

    pdf.set_font("Helvetica", "B", 16)
    pdf.set_text_color(*KEJATI_GREEN)
    pdf.cell(0, 10, "DAFTAR ISI", align="C")
    pdf.ln(12)

    toc_items = [
        ("1", "Pendahuluan", ""),
        ("2", "Untuk Tamu / Pembaca", ""),
        ("", "2.1  Mengakses Katalog Buku", ""),
        ("", "2.2  Mencari Buku", ""),
        ("", "2.3  Informasi Detail Buku", ""),
        ("3", "Untuk Admin / Petugas", ""),
        ("", "3.1  Autentikasi", ""),
        ("", "3.2  Dashboard", ""),
        ("", "3.3  Mengelola Buku", ""),
        ("", "3.4  Mengelola Peminjaman", ""),
        ("", "3.5  Mengelola Anggota", ""),
        ("", "3.6  Riwayat Sirkulasi", ""),
        ("", "3.7  Laporan Pengunjung", ""),
        ("", "3.8  Manajemen Akun (Superadmin)", ""),
        ("", "3.9  Audit Log (Superadmin)", ""),
        ("4", "Hasil Pengujian", ""),
        ("5", "Catatan Penting & Troubleshooting", ""),
        ("6", "Kontak Bantuan", ""),
    ]
    for num, title, _ in toc_items:
        is_main = num != ""
        if is_main:
            pdf.set_font("Helvetica", "B", 10.5)
            pdf.set_text_color(*INK)
            pdf._reset_cursor()
            pdf.cell(8, 7, num)
            pdf.cell(0, 7, title)
            pdf.ln(7)
        else:
            pdf.set_font("Helvetica", "", 9.5)
            pdf.set_text_color(*INK_LIGHT)
            pdf.set_x(23)
            pdf.cell(0, 6, title)
            pdf.ln(6)

    # ── BAB 1: PENDAHULUAN ───────────────────────────────────────
    pdf.add_page()
    pdf.set_y(20)
    pdf.section_heading("1", "Pendahuluan")

    pdf.body_text(
        "Sistem Informasi Perpustakaan Kejaksaan Tinggi Jawa Barat adalah aplikasi berbasis web "
        "yang dirancang untuk mendigitalkan pengelolaan perpustakaan internal instansi. "
        "Aplikasi ini melayani dua kelompok pengguna utama:"
    )
    pdf.bullet_list([
        "Tamu / Pembaca — mengakses katalog buku setelah mengisi form kunjungan digital.",
        "Admin / Petugas — mengelola data buku, kategori, peminjaman, dan laporan kunjungan.",
    ])
    pdf.ln(2)

    pdf.sub_heading("Batasan Fitur")
    pdf.callout_box(
        "PERHATIAN: Tidak ada fitur peminjaman online. Semua transaksi peminjaman dan "
        "pengembalian dicatat secara manual oleh petugas perpustakaan di tempat. "
        "Tamu tidak memiliki akun dan tidak dapat melakukan peminjaman mandiri.",
        kind="warning"
    )
    pdf.ln(2)
    pdf.callout_box(
        "KEAMANAN DATA: Data pengunjung tersimpan aman dan hanya bisa diakses oleh admin. "
        "Informasi pribadi (NIP, nomor HP) tidak ditampilkan di halaman publik.",
        kind="success"
    )

    # ── BAB 2: UNTUK TAMU ────────────────────────────────────────
    pdf.add_page()
    pdf.set_y(20)
    pdf.section_heading("2", "Untuk Tamu / Pembaca")

    # 2.1
    pdf.sub_heading("2.1  Mengakses Katalog Buku")
    pdf.body_text(
        "Sebelum dapat melihat katalog buku, setiap tamu wajib mengisi form kunjungan digital "
        "yang menggantikan buku tamu fisik. Berikut langkah-langkahnya:"
    )
    pdf.numbered_list([
        "Buka halaman utama aplikasi di browser (alamat: /).",
        "Pilih kategori pengunjung: \"Pegawai\" atau \"Umum\".",
        "Isi data diri yang diminta pada form kunjungan.",
        "Centang kotak persetujuan pemrosesan data pribadi.",
        "Klik tombol \"Lanjutkan ke Katalog\".",
    ])
    pdf.ln(2)

    pdf.body_text("Field yang wajib diisi pada form kunjungan:")
    pdf.info_table(
        ["Field", "Keterangan", "Wajib?"],
        [
            ["Kategori", "Pegawai atau Umum", "Ya"],
            ["Nama Lengkap", "Nama tamu sesuai identitas", "Ya"],
            ["NIP / NRP", "Nomor induk pegawai", "Ya (pegawai)"],
            ["Instansi / Unit Kerja", "Asal instansi atau unit kerja", "Ya"],
            ["No. HP / Email", "Kontak yang dapat dihubungi", "Tidak"],
            ["Keperluan", "Tujuan kunjungan ke perpustakaan", "Ya"],
            ["Persetujuan Privasi", "Persetujuan pemrosesan data", "Ya"],
        ],
        col_widths=[45, 75, 30]
    )

    pdf.callout_box(
        "BATASAN: Form kunjungan dibatasi 8 kali pengisian per alamat IP dalam 1 menit "
        "untuk mencegah penyalahgunaan (anti-spam).",
        kind="info"
    )

    # 2.2
    pdf.sub_heading("2.2  Mencari Buku")
    pdf.body_text(
        "Setelah berhasil check-in, tamu diarahkan ke halaman katalog buku (/katalog). "
        "Halaman ini menyediakan tiga cara untuk menemukan buku:"
    )
    pdf.numbered_list([
        "Pencarian kata kunci — ketik judul atau nama penulis di kolom pencarian.",
        "Filter kategori — pilih kategori buku dari dropdown (misal: Hukum Pidana, Perundang-undangan).",
        "Filter ketersediaan — pilih \"Tersedia\" untuk hanya menampilkan buku yang masih bisa dipinjam, "
        "atau \"Tidak Tersedia\" untuk melihat buku yang sedang dipinjam semua.",
    ])
    pdf.ln(2)
    pdf.body_text(
        "Hasil pencarian ditampilkan dalam bentuk kartu buku (grid) dengan paginasi 12 buku per halaman. "
        "Klik judul atau kartu buku untuk membuka halaman detail."
    )

    # 2.3
    pdf.sub_heading("2.3  Informasi Detail Buku")
    pdf.body_text(
        "Halaman detail buku menampilkan informasi lengkap sebagai berikut:"
    )
    pdf.info_table(
        ["Informasi", "Keterangan"],
        [
            ["Judul", "Judul lengkap buku"],
            ["Penulis", "Nama penulis / pengarang"],
            ["Penerbit", "Penerbit buku (jika tersedia)"],
            ["Tahun Terbit", "Tahun penerbitan"],
            ["Jumlah Halaman", "Jumlah atau format halaman"],
            ["ISBN", "Nomor ISBN buku"],
            ["No. Klasifikasi", "Nomor DDC / klasifikasi perpustakaan"],
            ["Lokasi Rak", "Letak buku di rak perpustakaan (misal: Rak A-3)"],
            ["Ketersediaan", "Jumlah eksemplar tersedia dari total stok"],
        ],
        col_widths=[45, 105]
    )

    pdf.body_text("Status ketersediaan buku ditampilkan dengan indikator berikut:")
    pdf.bullet_list([
        "TERSEDIA — \"Tersedia: X dari Y eksemplar\" berarti buku masih dapat dipinjam.",
        "TIDAK TERSEDIA — semua eksemplar sedang dipinjam.",
    ])
    pdf.ln(2)
    pdf.callout_box(
        "Jika buku yang diinginkan berstatus \"Tidak Tersedia\", silakan hubungi petugas "
        "perpustakaan untuk informasi estimasi pengembalian.",
        kind="info"
    )

    pdf.ln(2)
    pdf.body_text(
        "PENTING: Akses ke katalog hanya berlaku untuk hari kunjungan yang sama. "
        "Jika tanggal berganti atau session kedaluwarsa, tamu perlu mengisi ulang form kunjungan."
    )

    # ── BAB 3: UNTUK ADMIN ───────────────────────────────────────
    pdf.add_page()
    pdf.set_y(20)
    pdf.section_heading("3", "Untuk Admin / Petugas")

    # 3.1
    pdf.sub_heading("3.1  Autentikasi")
    pdf.body_text(
        "Admin dan petugas perpustakaan mengakses panel admin melalui halaman login khusus. "
        "Berikut langkah untuk masuk:"
    )
    pdf.numbered_list([
        "Buka halaman login di alamat /login.",
        "Masukkan alamat email yang sudah terdaftar di sistem.",
        "Masukkan password (minimal 8 karakter).",
        "Klik tombol \"Masuk\".",
    ])
    pdf.ln(2)
    pdf.callout_box(
        "KEAMANAN: Sistem membatasi 5 percobaan login gagal per kombinasi email dan alamat IP. "
        "Setelah batas terlampaui, akun terkunci sementara (throttle).",
        kind="warning"
    )

    # 3.2
    pdf.sub_heading("3.2  Dashboard")
    pdf.body_text(
        "Setelah berhasil login, admin akan diarahkan ke halaman Dashboard (/admin/dashboard) "
        "yang menampilkan ringkasan operasional perpustakaan:"
    )
    pdf.info_table(
        ["Widget", "Keterangan"],
        [
            ["Total Judul Buku", "Jumlah judul buku yang terdaftar di katalog"],
            ["Total Eksemplar", "Jumlah seluruh copy buku (stok)"],
            ["Eksemplar Tersedia", "Jumlah copy yang belum dipinjam"],
            ["Peminjaman Aktif", "Jumlah buku yang sedang dipinjam"],
            ["Peminjaman Terlambat", "Jumlah yang melewati jatuh tempo"],
            ["Pengunjung Hari Ini", "Jumlah tamu yang check-in hari ini"],
            ["Transaksi Terakhir", "5 peminjaman terbaru"],
        ],
        col_widths=[45, 105]
    )

    # 3.3
    pdf.sub_heading("3.3  Mengelola Buku")
    pdf.body_text("Menu: /admin/buku — Kelola seluruh data buku di katalog perpustakaan.")
    pdf.ln(1)

    pdf.set_font("Helvetica", "B", 9.5)
    pdf.set_text_color(*KEJATI_GREEN2)
    pdf._reset_cursor()
    pdf.cell(0, 6, "Menambah Buku Baru")
    pdf.ln(6)
    pdf.numbered_list([
        "Klik tombol \"Tambah Buku\" di bagian atas daftar.",
        "Pilih kategori buku dari dropdown (wajib). Jika kategori belum ada, klik \"Tambah Kategori Baru\".",
        "Isi data buku: judul & penulis wajib; field lain (penerbit, tahun, ISBN, dll) opsional.",
        "Masukkan jumlah total stok. Stok tersedia akan otomatis disesuaikan.",
        "Lampirkan file sampul buku jika tersedia (format: JPEG/PNG/WEBP, maks 2 MB).",
        "Klik \"Simpan\" untuk menambahkan buku ke katalog.",
    ])

    pdf.ln(2)
    pdf.set_font("Helvetica", "B", 9.5)
    pdf.set_text_color(*KEJATI_GREEN2)
    pdf._reset_cursor()
    pdf.cell(0, 6, "Mengedit Data Buku")
    pdf.ln(6)
    pdf.numbered_list([
        "Klik ikon pensil pada baris buku yang ingin diedit.",
        "Ubah data yang diperlukan.",
        "Jika mengubah total stok, stok tersedia akan dihitung ulang otomatis: "
        "stok_tersedia = total_stok - peminjaman_aktif.",
        "Klik \"Simpan\" untuk menyimpan perubahan.",
    ])

    pdf.ln(2)
    pdf.callout_box(
        "PENTING: Buku yang sudah memiliki riwayat peminjaman tidak dapat dihapus. "
        "Jika buku sudah tidak dilayankan, kosongkan stok atau arsipkan sebagai pengganti penghapusan.",
        kind="danger"
    )

    pdf.ln(2)
    pdf.set_font("Helvetica", "B", 9.5)
    pdf.set_text_color(*KEJATI_GREEN2)
    pdf._reset_cursor()
    pdf.cell(0, 6, "Menghapus Sampul Buku")
    pdf.ln(6)
    pdf.body_text(
        "Pada mode edit, klik tombol hapus (×) pada preview sampul untuk menghapus file gambar. "
        "File sampul juga otomatis terhapus dari storage saat buku dihapus."
    )

    # 3.4
    pdf.sub_heading("3.4  Mengelola Peminjaman")
    pdf.body_text("Menu: /admin/peminjaman — Catat dan kelola transaksi peminjaman buku.")
    pdf.ln(1)

    pdf.set_font("Helvetica", "B", 9.5)
    pdf.set_text_color(*KEJATI_GREEN2)
    pdf._reset_cursor()
    pdf.cell(0, 6, "Mencatat Peminjaman Baru")
    pdf.ln(6)
    pdf.numbered_list([
        "Klik tombol \"Tambah Peminjaman\".",
        "Cari buku yang akan dipinjamkan menggunakan kolom pencarian (hanya buku dengan stok tersedia > 0 yang muncul).",
        "Pilih buku dari hasil pencarian.",
        "Isi data peminjam: nama lengkap (wajib), NIP, dan instansi/unit (opsional).",
        "Tanggal pinjam otomatis diisi hari ini; tanggal jatuh tempo default +7 hari dan dapat diubah.",
        "Klik \"Simpan\" — sistem otomatis mengurangi stok tersedia buku.",
    ])

    pdf.ln(2)
    pdf.set_font("Helvetica", "B", 9.5)
    pdf.set_text_color(*KEJATI_GREEN2)
    pdf._reset_cursor()
    pdf.cell(0, 6, "Memproses Pengembalian")
    pdf.ln(6)
    pdf.body_text(
        "Pada daftar peminjaman, temukan transaksi aktif dan klik tombol \"Kembalikan\". "
        "Sistem akan secara otomatis:"
    )
    pdf.bullet_list([
        "Mengisi tanggal kembali dengan tanggal hari ini.",
        "Menambah stok tersedia buku (+1).",
        "Memperbarui status peminjaman menjadi \"Dikembalikan\".",
    ])

    pdf.ln(2)
    pdf.set_font("Helvetica", "B", 9.5)
    pdf.set_text_color(*KEJATI_GREEN2)
    pdf._reset_cursor()
    pdf.cell(0, 6, "Membatalkan Peminjaman")
    pdf.ln(6)
    pdf.body_text(
        "Jika pencatatan peminjaman dilakukan secara keliru, klik tombol \"Batalkan\" pada transaksi "
        "tersebut. Sistem akan mengembalikan stok tersedia buku dan menandai transaksi sebagai dibatalkan."
    )

    pdf.ln(2)
    pdf.set_font("Helvetica", "B", 9.5)
    pdf.set_text_color(*KEJATI_GREEN2)
    pdf._reset_cursor()
    pdf.cell(0, 6, "Mengoreksi Data Peminjaman")
    pdf.ln(6)
    pdf.body_text(
        "Klik ikon edit pada transaksi untuk mengubah data peminjam, tanggal, atau catatan. "
        "Perubahan tidak mempengaruhi stok buku."
    )

    pdf.ln(2)
    pdf.set_font("Helvetica", "B", 9.5)
    pdf.set_text_color(*KEJATI_GREEN2)
    pdf._reset_cursor()
    pdf.cell(0, 6, "Status Peminjaman")
    pdf.ln(6)
    pdf.info_table(
        ["Status", "Warna", "Keterangan"],
        [
            ["Dipinjam", "Biru", "Masih dalam masa pinjam, belum jatuh tempo"],
            ["Terlambat", "Merah", "Melewati tanggal jatuh tempo, belum dikembalikan"],
            ["Dikembalikan", "Hijau", "Buku sudah dikembalikan ke perpustakaan"],
            ["Dibatalkan", "Abu-abu", "Transaksi dibatalkan, stok dikembalikan"],
        ],
        col_widths=[35, 25, 90]
    )
    pdf.body_text(
        "Status \"Terlambat\" dihitung secara otomatis oleh sistem berdasarkan perbandingan "
        "tanggal jatuh tempo dengan tanggal hari ini. Tidak perlu pembaruan manual."
    )

    pdf.ln(2)
    pdf.set_font("Helvetica", "B", 9.5)
    pdf.set_text_color(*KEJATI_GREEN2)
    pdf._reset_cursor()
    pdf.cell(0, 6, "Ekspor Laporan Peminjaman")
    pdf.ln(6)
    pdf.body_text(
        "Gunakan filter pencarian dan status untuk menyaring data, lalu klik tombol \"Export CSV\" "
        "atau \"Export XLSX\". Isi file mengikuti filter yang sedang aktif."
    )

    # 3.5
    pdf.sub_heading("3.5  Mengelola Anggota")
    pdf.body_text("Menu: /admin/anggota — Kelola identitas anggota perpustakaan.")
    pdf.numbered_list([
        "Klik \"+ Tambah anggota\".",
        "Isi nama (wajib), lalu NIP, instansi/unit, dan nomor HP jika tersedia.",
        "Biarkan status \"Aktif\" agar anggota dapat dipilih pada transaksi baru.",
        "Klik \"Simpan anggota\".",
    ])
    pdf.body_text(
        "Saat anggota dipilih pada formulir peminjaman, nama, NIP, dan instansi terisi otomatis. "
        "Anggota yang memiliki histori tidak dihapus permanen, tetapi dinonaktifkan agar riwayat tetap utuh."
    )

    # 3.6
    pdf.sub_heading("3.6  Riwayat Sirkulasi")
    pdf.body_text(
        "Menu: /admin/riwayat-sirkulasi — Telusuri transaksi berdasarkan nama peminjam, anggota, "
        "atau buku. Tabel menampilkan periode peminjaman dan status akhirnya."
    )

    # 3.7
    pdf.sub_heading("3.7  Laporan Pengunjung")
    pdf.body_text("Menu: /admin/pengunjung — Lihat dan ekspor data kunjungan tamu.")
    pdf.ln(1)
    pdf.body_text("Halaman ini menampilkan daftar seluruh pengunjung yang telah check-in, dilengkapi fitur:")
    pdf.bullet_list([
        "Pencarian berdasarkan nama, instansi, atau NIP.",
        "Filter kategori pengunjung (Pegawai / Umum).",
        "Filter berdasarkan tanggal kunjungan.",
        "Ringkasan statistik: total pengunjung, jumlah pegawai, dan tamu umum.",
        "Tombol \"Export CSV\" dan \"Export XLSX\" untuk mengunduh laporan.",
    ])
    pdf.ln(2)
    pdf.callout_box(
        "KEAMANAN: Data pengunjung bersifat rahasia dan hanya dapat diakses oleh admin yang sudah login. "
        "File spreadsheet dilindungi dari formula injection menggunakan mekanisme sanitasi otomatis.",
        kind="success"
    )

    # 3.8
    pdf.sub_heading("3.8  Manajemen Akun (Superadmin)")
    pdf.body_text(
        "Menu: /admin/pengguna — Hanya dapat diakses oleh pengguna dengan role Superadmin."
    )
    pdf.ln(1)
    pdf.body_text("Fitur yang tersedia:")
    pdf.bullet_list([
        "Menambah akun petugas/admin baru (nama, email, password, role).",
        "Mengedit data akun dan mengubah role (admin / superadmin).",
        "Reset password akun (kosongkan field password jika tidak ingin mengubah).",
        "Menghapus akun petugas lain (tidak dapat menghapus akun sendiri).",
    ])
    pdf.ln(2)
    pdf.callout_box(
        "PENTING: Password akun baru minimal 8 karakter. Superadmin tidak dapat menghapus, "
        "menonaktifkan, atau mencabut role superadmin dari akunnya sendiri.",
        kind="warning"
    )

    # 3.9
    pdf.sub_heading("3.9  Audit Log (Superadmin)")
    pdf.body_text(
        "Menu: /admin/audit-log — Menampilkan waktu, petugas, aksi, entitas, dan alamat IP. "
        "Dalam uji langsung, aksi Buat, Pinjam, Perpanjang, dan Kembali tercatat otomatis."
    )

    # ── BAB 4: HASIL PENGUJIAN ───────────────────────────────────
    pdf.add_page()
    pdf.set_y(20)
    pdf.section_heading("4", "Hasil Pengujian")
    pdf.body_text(
        "Panduan ini diverifikasi menggunakan browser dan data fiktif pada database SQLite terpisah. "
        "Seluruh 69 test otomatis (223 assertion) juga lulus tanpa kegagalan."
    )
    pdf.info_table(
        ["Skenario", "Hasil"],
        [
            ["Validasi buku tamu", "Pesan wajib tampil; data kosong ditolak"],
            ["Check-in tamu", "Berhasil diarahkan ke katalog"],
            ["Kategori & buku", "Data tersimpan; stok awal 2/2"],
            ["Anggota", "Tersimpan aktif dan dapat dipilih"],
            ["Peminjaman", "Identitas otomatis; stok berkurang"],
            ["Perpanjangan", "Jatuh tempo bertambah 7 hari, satu kali"],
            ["Pengembalian", "Status selesai; stok kembali"],
            ["Riwayat & audit", "Transaksi dan aksi petugas tercatat"],
            ["Export XLSX", "File Excel valid dan tidak rusak"],
        ],
        col_widths=[55, 95]
    )

    # ── BAB 5: CATATAN PENTING ───────────────────────────────────
    pdf.section_heading("5", "Catatan Penting & Troubleshooting")

    pdf.sub_heading("Untuk Tamu / Pembaca")
    pdf.bullet_list([
        "Akses ke katalog hanya berlaku untuk hari kunjungan yang sama. Jika tanggal berganti, "
        "tamu wajib mengisi ulang form kunjungan.",
        "Menutup browser tidak selalu mengakhiri sesi — tergantung konfigurasi server.",
        "Data pribadi (NIP, nomor HP) tidak pernah ditampilkan di halaman publik.",
        "Tamu tidak dapat meminjam buku melalui website — silakan hubungi petugas di lokasi perpustakaan.",
    ])

    pdf.ln(3)
    pdf.sub_heading("Untuk Admin / Petugas")
    pdf.bullet_list([
        "Pastikan stok tersedia > 0 sebelum mencatat peminjaman baru. Sistem menolak peminjaman "
        "jika stok habis.",
        "Perubahan stok buku dilakukan dalam transaksi database dengan penguncian (lockForUpdate) "
        "untuk mencegah inkonsistensi data saat ada transaksi bersamaan.",
        "Sampul buku otomatis dihapus dari storage saat buku dihapus atau saat sampul diganti.",
        "File CSV/XLSX laporan sudah disanitasi dari formula injection.",
        "Penghapusan data pengunjung otomatis (retensi) hanya aktif jika administrator "
        "mengatur VISITOR_RETENTION_DAYS di file konfigurasi. Nilai 0 berarti nonaktif.",
    ])

    pdf.ln(3)
    pdf.sub_heading("Kendala Umum")
    pdf.bullet_list([
        "CSS tidak tampil: jalankan composer dev saat pengembangan, atau npm ci && npm run build untuk deployment.",
        "Buku tidak muncul saat transaksi: pastikan buku aktif dan stok tersedia lebih dari nol.",
        "Anggota tidak muncul: pastikan status anggota masih Aktif.",
        "Tombol Perpanjang hilang: peminjaman hanya dapat diperpanjang satu kali.",
        "Ekspor kosong: hapus atau sesuaikan pencarian dan filter aktif.",
    ])

    # ── BAB 6: KONTAK BANTUAN ────────────────────────────────────
    pdf.ln(6)
    pdf.section_heading("6", "Kontak Bantuan")

    pdf.body_text(
        "Untuk kendala teknis, pertanyaan, atau permintaan perubahan fitur, silakan hubungi:"
    )
    pdf.ln(2)
    pdf.bullet_list([
        "Petugas Perpustakaan Kejaksaan Tinggi Jawa Barat (langsung di lokasi).",
        "Email: perpustakaan@kejati-jabar.go.id",
    ])

    pdf.ln(8)
    # Garis emas
    pdf.set_draw_color(*KEJATI_GOLD)
    pdf.set_line_width(0.5)
    cx = pdf.w / 2
    pdf.line(cx - 30, pdf.get_y(), cx + 30, pdf.get_y())
    pdf.ln(6)

    pdf.set_font("Helvetica", "I", 8)
    pdf.set_text_color(*INK_LIGHT)
    pdf._reset_cursor()
    pdf.cell(0, 5, "Dokumen ini terakhir diperbarui dan diverifikasi: 30 Agustus 2026", align="C")
    pdf.ln(4)
    pdf.cell(0, 5, "Aplikasi dibangun dengan Laravel 12.x + Livewire 3.x + Tailwind CSS 3.x", align="C")
    pdf.ln(4)
    pdf.cell(0, 5, "© 2026 Kejaksaan Tinggi Jawa Barat", align="C")

    return pdf


if __name__ == "__main__":
    pdf = build_pdf()
    out_path = "/home/lcfr/perpustakaan-kejati-jawa-barat/Buku_Panduan_Penggunaan_Perpus_Kejati_Jabar.pdf"
    pdf.output(out_path)
    print(f"PDF berhasil dibuat: {out_path}")
