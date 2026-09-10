#!/usr/bin/env python3
"""
Script untuk menghasilkan Dokumen Kajian Teknis & Solusi:
Optimalisasi Alur Kiosk Buku Tamu Digital & Penanganan Antrean Pengunjung
Perpustakaan Kejaksaan Tinggi Jawa Barat.
"""

from fpdf import FPDF
from datetime import datetime

# ── Warna Brand Kejati ───────────────────────────────────────────
KEJATI_GREEN   = (22, 101, 52)    # #166534
KEJATI_DARK    = (6, 78, 59)      # #064E3B
KEJATI_GREEN2  = (21, 128, 61)    # #15803D
KEJATI_GOLD    = (244, 197, 66)   # #F4C542
KEJATI_GOLD_DK = (184, 145, 0)    # #B89100
INK            = (31, 41, 55)     # #1F2937
INK_LIGHT      = (107, 114, 128)  # #6B7280
WHITE          = (255, 255, 255)
SURFACE        = (247, 250, 248)  # #F7FAF8
BORDER         = (229, 231, 235)  # gray-200
SUCCESS        = (22, 163, 74)
DANGER         = (220, 38, 38)
WARNING        = (217, 119, 6)
INFO           = (37, 99, 235)


class KajianKioskPDF(FPDF):
    """Kelas PDF kustom dengan layout profesional untuk kajian teknis."""

    is_cover = True

    def __init__(self, *args, **kwargs):
        super().__init__(*args, **kwargs)
        font_dir = "/usr/share/fonts/TTF"
        self.add_font("DejaVu", "", f"{font_dir}/DejaVuSans.ttf")
        self.add_font("DejaVu", "B", f"{font_dir}/DejaVuSans-Bold.ttf")
        self.add_font("DejaVu", "I", f"{font_dir}/DejaVuSans-Oblique.ttf")
        self.add_font("DejaVu", "BI", f"{font_dir}/DejaVuSans-BoldOblique.ttf")
        self.font_family = "DejaVu"

    def set_font(self, family=None, style="", size=0):
        if family in (None, "Helvetica", "Arial"):
            family = "DejaVu"
        return super().set_font(family, style, size)

    def header(self):
        if self.is_cover:
            return
        self.set_draw_color(*KEJATI_GREEN)
        self.set_line_width(0.6)
        self.line(15, 12, self.w - 15, 12)
        self.set_y(6)
        self.set_font("Helvetica", "I", 7.5)
        self.set_text_color(*INK_LIGHT)
        self.cell(0, 5, "Kajian Teknis: Alur Kiosk Buku Tamu Digital — Perpustakaan Kejati Jawa Barat", align="L")
        self.ln(10)

    def footer(self):
        if self.is_cover:
            return
        self.set_y(-16)
        self.set_draw_color(*BORDER)
        self.set_line_width(0.3)
        self.line(15, self.h - 16, self.w - 15, self.h - 16)
        self.set_font("Helvetica", "", 8)
        self.set_text_color(*INK_LIGHT)
        self.cell(0, 6, f"Halaman {self.page_no()}", align="C")
        self.ln(4)
        self.set_font("Helvetica", "I", 6.5)
        self.cell(0, 4, "Dokumen Pembahasan Teknis Internal — Kejaksaan Tinggi Jawa Barat © 2026", align="C")

    # ── Helper Formatting ────────────────────────────────────────
    def _reset_cursor(self):
        self.set_x(15)

    def chapter_title(self, number: str, title: str):
        self.ln(2)
        y = self.get_y()
        self.set_fill_color(*KEJATI_GREEN)
        self.rect(15, y, self.w - 30, 8.5, "F")
        self.set_xy(18, y + 1)
        self.set_font("Helvetica", "B", 10)
        self.set_text_color(*WHITE)
        self.cell(0, 6.5, f"{number}.   {title.upper()}")
        self.set_y(y + 11.5)
        self.set_text_color(*INK)

    def sub_title(self, label: str):
        self.ln(2)
        self.set_font("Helvetica", "B", 9.5)
        self.set_text_color(*KEJATI_GREEN)
        self._reset_cursor()
        self.cell(0, 5.5, label)
        self.ln(5.5)
        self.set_text_color(*INK)

    def paragraph(self, text: str, spacing: float = 1.0):
        self.set_font("Helvetica", "", 9)
        self.set_text_color(*INK)
        self._reset_cursor()
        self.multi_cell(self.w - 30, 4.8, text)
        self.ln(spacing)

    def bullet_points(self, items: list[str], indent: int = 20):
        self.set_font("Helvetica", "", 9)
        self.set_text_color(*INK)
        for item in items:
            self.set_x(indent)
            self.cell(4, 4.8, "•")
            self.multi_cell(self.w - indent - 4 - 15, 4.8, item)
            self.ln(0.4)
        self.ln(0.8)

    def numbered_points(self, items: list[str], start: int = 1, indent: int = 20):
        self.set_font("Helvetica", "", 9)
        self.set_text_color(*INK)
        for i, item in enumerate(items, start=start):
            self.set_x(indent)
            num_w = self.get_string_width(f"{i}.") + 2
            self.cell(num_w, 4.8, f"{i}.")
            self.multi_cell(self.w - indent - num_w - 15, 4.8, item)
            self.ln(0.4)
        self.ln(0.8)

    def alert_box(self, text: str, kind: str = "info", title: str = ""):
        colors = {
            "info":    (INFO,    (239, 246, 255)),
            "warning": (WARNING, (255, 251, 235)),
            "danger":  (DANGER,  (254, 242, 242)),
            "success": (SUCCESS, (240, 253, 244)),
        }
        accent, bg = colors.get(kind, colors["info"])
        y = self.get_y()
        self.set_fill_color(*bg)
        self.set_font("Helvetica", "", 8.8)
        content_w = self.w - 30 - 12
        full_text = f"**{title.upper()}:** {text}" if title else text
        lines = self.multi_cell(content_w, 4.6, full_text, dry_run=True, output="LINES", markdown=True)
        box_h = len(lines) * 4.6 + 5.5
        self.rect(15, y, self.w - 30, box_h, "F")
        self.set_fill_color(*accent)
        self.rect(15, y, 3.5, box_h, "F")
        old_l = self.l_margin
        self.set_left_margin(22)
        self.set_xy(22, y + 2.8)
        self.set_text_color(*INK)
        self.multi_cell(content_w, 4.6, full_text, markdown=True)
        self.set_left_margin(old_l)
        self.set_y(y + box_h + 2.5)

    def styled_table(self, headers: list[str], rows: list[list[str]], col_widths: list[float] | None = None, font_size: float = 8.0):
        if col_widths is None:
            w = (self.w - 30) / len(headers)
            col_widths = [w] * len(headers)
        self.set_fill_color(*KEJATI_GREEN)
        self.set_text_color(*WHITE)
        self.set_font("Helvetica", "B", font_size)
        self._reset_cursor()
        for i, h in enumerate(headers):
            self.cell(col_widths[i], 6.8, h, border=0, fill=True, align="C")
        self.ln(6.8)
        self.set_text_color(*INK)
        self.set_font("Helvetica", "", font_size)
        for ri, row in enumerate(rows):
            fill = ri % 2 == 1
            if fill:
                self.set_fill_color(*SURFACE)
            else:
                self.set_fill_color(*WHITE)
            self._reset_cursor()
            for i, cell in enumerate(row):
                align = "C" if i == 0 else "L"
                self.cell(col_widths[i], 6.2, cell, border=1, fill=True, align=align)
            self.ln(6.2)
        self.ln(2)


def generate_kajian_pdf(filename: str):
    pdf = KajianKioskPDF(orientation="P", unit="mm", format="A4")
    pdf.set_auto_page_break(auto=False)

    # ═════════════════════════════════════════════════════════════════
    # HALAMAN 1: SAMPUL EKSEKUTIF (COVER)
    # ═════════════════════════════════════════════════════════════════
    pdf.add_page()
    pdf.is_cover = True

    # Background Kejati Dark
    pdf.set_fill_color(*KEJATI_DARK)
    pdf.rect(0, 0, pdf.w, pdf.h, "F")

    # Garis Aksen Emas Atas
    pdf.set_fill_color(*KEJATI_GOLD)
    pdf.rect(0, 0, pdf.w, 4.5, "F")

    # Badge Kategori Dokumen
    pdf.set_y(42)
    pdf.set_fill_color(255, 255, 255)
    pdf.set_font("Helvetica", "B", 9)
    badge_text = "KAJIAN TEKNIS & ANALISIS ALUR OPERASIONAL"
    badge_w = pdf.get_string_width(badge_text) + 14
    pdf.set_x((pdf.w - badge_w) / 2)
    pdf.set_text_color(*KEJATI_DARK)
    pdf.cell(badge_w, 7.5, badge_text, fill=True, align="C")
    pdf.ln(18)

    # Judul Utama
    pdf.set_font("Helvetica", "B", 22)
    pdf.set_text_color(*WHITE)
    pdf.cell(0, 11, "OPTIMALISASI ALUR KIOSK", align="C")
    pdf.ln(11)
    pdf.cell(0, 11, "BUKU TAMU DIGITAL", align="C")
    pdf.ln(11)
    pdf.set_font("Helvetica", "B", 16)
    pdf.set_text_color(*KEJATI_GOLD)
    pdf.cell(0, 10, "& PENANGANAN ANTREAN PENGUNJUNG", align="C")
    pdf.ln(16)

    # Garis Pembatas Emas
    pdf.set_draw_color(*KEJATI_GOLD)
    pdf.set_line_width(0.8)
    cx = pdf.w / 2
    pdf.line(cx - 35, pdf.get_y(), cx + 35, pdf.get_y())
    pdf.ln(14)

    # Subjudul Keterangan Kasus
    pdf.set_font("Helvetica", "", 10.5)
    pdf.set_text_color(220, 235, 225)
    pdf.cell(0, 6, "Kajian Penanganan Pengalihan Sesi, Auto-Reset Countdown,", align="C")
    pdf.ln(6)
    pdf.cell(0, 6, "dan Antrean Pengunjung pada Terminal Tunggal Perpustakaan", align="C")
    pdf.ln(22)

    # Kotak Metadata Informasi
    box_w = 154
    box_x = (pdf.w - box_w) / 2
    box_y = pdf.get_y()
    pdf.set_fill_color(255, 255, 255)
    pdf.rect(box_x, box_y, box_w, 48, "F")

    metadata = [
        ("Peruntukan Dokumen", ": Bahan Diskusi Bersama Pegawai & Tim IT Perpustakaan"),
        ("Topik Utama", ": Manajemen Antrean Kiosk & Pengalihan Sesi Katalog"),
        ("Instansi", ": Perpustakaan Kejaksaan Tinggi Jawa Barat"),
        ("Lokasi Terminal", ": Meja Depan / Lobi Pelayanan Perpustakaan"),
        ("Waktu Penyusunan", ": September 2026"),
        ("Status Dokumen", ": PROPOSAL TEKNIS (PERLU KEPUTUSAN BERSAMA)"),
    ]

    pdf.set_y(box_y + 5)
    for label, val in metadata:
        pdf.set_x(box_x + 8)
        pdf.set_font("Helvetica", "B", 8.5)
        pdf.set_text_color(*KEJATI_GREEN)
        pdf.cell(40, 6, label, align="L")
        pdf.set_font("Helvetica", "B" if "PROPOSAL" in val else "", 8.5)
        pdf.set_text_color(*(WARNING if "PROPOSAL" in val else INK))
        pdf.cell(0, 6, val, align="L")
        pdf.ln(6.2)

    # Aksen Emas Bawah
    pdf.set_fill_color(*KEJATI_GOLD)
    pdf.rect(0, pdf.h - 4.5, pdf.w, 4.5, "F")

    # ═════════════════════════════════════════════════════════════════
    # HALAMAN 2: BAB 1 & BAB 2 (PERMASALAHAN & ANALISIS OPERASIONAL)
    # ═════════════════════════════════════════════════════════════════
    pdf.add_page()
    pdf.is_cover = False
    pdf.set_y(16)

    pdf.chapter_title("1", "Latar Belakang & Identifikasi Permasalahan Lapangan")

    pdf.paragraph(
        "Sistem Informasi Perpustakaan Kejaksaan Tinggi Jawa Barat menyediakan formulir buku tamu digital "
        "(/kunjungan) yang ditempatkan pada komputer terminal di meja depan lobi. Dalam penerapannya, "
        "komputer ini dioperasikan sebagai perangkat publik bersama (shared kiosk terminal)."
    )

    pdf.alert_box(
        "Kondisi Saat Ini: Setelah Pengunjung A mengisi buku tamu dan submit, sistem menyimpan data lalu "
        "langsung mengarahkan layar ke Katalog Buku (/katalog). Ketika ada antrean 5 pengunjung di belakangnya, "
        "layar komputer tertinggal di katalog buku dan sesi check-in masih tertaut pada Pengunjung A. "
        "Pengunjung B yang mengantre tidak dapat langsung mengisi buku tamu.",
        kind="warning",
        title="Masalah Kritis Alur Kiosk"
    )

    pdf.sub_title("1.1 Kronologi Skenario Antrean di Meja Layanan")
    pdf.numbered_points([
        "Pengunjung A tiba, mengisi buku tamu digital, lalu menekan 'Simpan & Masuk ke Katalog'.",
        "Sistem menyimpan data kunjungan ke database dan mengalihkan browser ke halaman katalog buku (/katalog).",
        "Pengunjung A meninggalkan meja kiosk untuk langsung masuk ke ruang baca fisik atau menuju rak buku.",
        "Pengunjung B tiba di meja kiosk untuk mengisi kehadiran, namun layar sedang menampilkan halaman katalog buku.",
        "Jika Pengunjung B mencoba mengakses alamat buku tamu, sistem mendeteksi sesi aktif Pengunjung A ('Anda sudah check-in hari ini').",
        "Akibatnya, Pengunjung B, C, D, dan E terhambat mengisi buku tamu, menimbulkan antrean dan potensi tamu tidak tercatat."
    ])

    pdf.sub_title("1.2 Dampak Operasional Jika Dibiarkan")
    pdf.bullet_points([
        "Distorsi Data Kunjungan: Tamu yang enggan menunggu antrean akan langsung masuk tanpa mengisi buku tamu, sehingga data statistik harian instansi menjadi tidak akurat.",
        "Beban Tambahan Petugas: Petugas jaga perpustakaan harus berulang kali mereset peramban secara manual untuk tamu berikutnya.",
        "Penurunan Citra Layanan: Alur yang macet memberikan kesan bahwa sistem digital belum siap menangani alur fisik perpustakaan."
    ])

    pdf.chapter_title("2", "Analisis Karakteristik Perangkat Kiosk vs Komputer Pribadi")
    pdf.paragraph(
        "Perlu ditegaskan pemisahan peran operasional antara terminal meja depan dan workstation pencarian referensi:"
    )

    pdf.styled_table(
        ["Aspek Operasional", "Komputer Kiosk Meja Depan", "Komputer Katalog / Perangkat Tamu"],
        [
            ["Fungsi Utama", "Pencatatan Buku Tamu Cepat (Guest Check-in)", "Eksplorasi & Pencarian Referensi Buku"],
            ["Waktu Penggunaan", "Sangat Singkat (20 - 40 detik per tamu)", "Fleksibel (10 - 45 menit)"],
            ["Perilaku Sesi", "Harus otomatis reset untuk antrean berikutnya", "Sesi aktif sepanjang hari kunjungan"],
            ["Tingkat Antrean", "Tinggi (saat jam masuk / rombongan dinas)", "Rendah (individual / tidak beruntun)"],
            ["Prioritas Layanan", "Kecepatan throughput & formulir selalu siap", "Kedalaman pencarian literatur hukum"]
        ],
        [36, 74, 70],
        font_size=7.8
    )

    # ═════════════════════════════════════════════════════════════════
    # HALAMAN 3: BAB 3 (OPSI SOLUSI & ALUR)
    # ═════════════════════════════════════════════════════════════════
    pdf.add_page()
    pdf.set_y(16)

    pdf.chapter_title("3", "Usulan Pilihan Solusi Penanganan Antrean")

    # Opsi 1
    pdf.sub_title("OPSI 1: Layar Sukses & Auto-Reset Countdown (Rekomendasi Utama)")
    pdf.paragraph(
        "Setelah pengunjung menekan tombol submit form, sistem TIDAK langsung memindahkan browser ke halaman katalog. "
        "Sistem menampilkan layar/modal konfirmasi keberhasilan yang elegan dengan timer hitung mundur:"
    )
    pdf.bullet_points([
        "Pesan Konfirmasi: 'Terima Kasih, [Nama Pengunjung]! Check-in Anda berhasil dicatat. Selamat datang di Perpustakaan Kejati Jabar.'",
        "Hitung Mundur Otomatis (5 - 8 Detik): Layar menampilkan hitung mundur: 'Layar otomatis kembali untuk antrean berikutnya dalam 5 detik...'",
        "Tombol Aksi [Pengunjung Berikutnya]: Tombol hijau mencolok untuk langsung mereset form ke kondisi kosong seketika tanpa menunggu timer.",
        "Tombol Opsi [Buka Katalog]: Disediakan jika pengunjung secara sadar ingin mencari buku di terminal tersebut."
    ])
    pdf.alert_box(
        "Keunggulan Opsi 1: Alur antrean mengalir lancar secara otomatis. Jika pengunjung langsung masuk ke ruang baca, "
        "layar kembali bersih dalam 5 detik siap melayani orang di belakangnya tanpa intervensi petugas.",
        kind="success",
        title="Evaluasi Opsi 1"
    )

    # Opsi 2
    pdf.sub_title("OPSI 2: Tombol Reset di Header Katalog + Inactivity Timeout (Idle Auto-Lock)")
    pdf.paragraph(
        "Jika kebijakan perpustakaan menghendaki pengunjung tetap masuk ke katalog di komputer meja depan, "
        "maka wajib dipasang pengaman otomatis:"
    )
    pdf.bullet_points([
        "Tombol Cepat di Header Katalog: Tombol bertanda '[Reset / Pengunjung Baru]' di sudut kanan atas navbar katalog untuk membersihkan sesi.",
        "Inactivity Timeout (60 Detik): Jika komputer di halaman katalog ditinggal diam tanpa gerakan mouse atau ketukan keyboard selama 60 detik, sistem otomatis me-redirect browser kembali ke formulir /kunjungan."
    ])
    pdf.alert_box(
        "Catatan Opsi 2: Cocok jika perpustakaan hanya memiliki 1 komputer untuk melayani buku tamu sekaligus katalog. "
        "Namun, pengunjung antrean berikutnya tetap harus menunggu timeout atau menekan tombol reset secara sadar.",
        kind="info",
        title="Evaluasi Opsi 2"
    )

    # Opsi 3
    pdf.sub_title("OPSI 3: Desentralisasi Input via Standee QR Code Mandiri di Smartphone Tamu")
    pdf.paragraph(
        "Untuk memecah antrean rombongan besar (misal: rombongan dinas luar kota atau universitas sebanyak 5-10 orang sekaligus):"
    )
    pdf.bullet_points([
        "Standee Akrilik QR Code: Ditempatkan di meja lobi bertuliskan 'Antrean Panjang? Scan QR ini untuk mengisi buku tamu di smartphone Anda'.",
        "Akses Mandiri: Tamu mengisi formulir di ponsel masing-masing, data langsung masuk ke database perpustakaan secara paralel."
    ])

    # ═════════════════════════════════════════════════════════════════
    # HALAMAN 4: BAB 4 & BAB 5 (MATRIKS KOMPARASI & REKOMENDASI TERPADU)
    # ═════════════════════════════════════════════════════════════════
    pdf.add_page()
    pdf.set_y(16)

    pdf.chapter_title("4", "Matriks Komparasi & Evaluasi Pilihan Solusi")

    pdf.styled_table(
        ["Parameter Penilaian", "Opsi 1 (Auto-Reset)", "Opsi 2 (Idle Timeout)", "Opsi 3 (QR Code Mandiri)"],
        [
            ["Kecepatan Mengurai Antrean", "Sangat Cepat (< 5 detik)", "Sedang (30-60 detik)", "Instan (Paralel di HP)"],
            ["Beban Intervensi Petugas", "Nol (100% Otomatis)", "Sangat Rendah", "Nol (Mandiri oleh Tamu)"],
            ["Kenyamanan Tamu Awam", "Sangat Mudah & Intuitif", "Perlu Sadar Klik Tombol", "Perlu HP & Kamera"],
            ["Kebutuhan Internet Tamu", "Tidak Ada (Pakai PC Kiosk)", "Tidak Ada (Pakai PC Kiosk)", "Perlu Paket Data / Wi-Fi"],
            ["Dampak Integritas Data", "Sangat Tertib & Lengkap", "Baik", "Sangat Tertib"],
            ["Waktu Implementasi IT", "Cepat (1 - 2 Jam kerja)", "Cepat (1 Jam kerja)", "Cepat (Cetak Akrilik QR)"],
            ["Status Rekomendasi", "PRIORITAS WAJIB", "PELENGKAP PENGAMAN", "AKSELERATOR ROMBONGAN"]
        ],
        [42, 46, 46, 46],
        font_size=7.6
    )

    pdf.chapter_title("5", "Rekomendasi Arsitektur Solusi Terpadu (Hybrid)")
    pdf.paragraph(
        "Solusi paling ideal untuk Perpustakaan Kejaksaan Tinggi Jawa Barat bukanlah memilih salah satu opsi secara kaku, "
        "melainkan mengintegrasikan ketiganya menjadi SATU ALUR LAYANAN TERPADU yang saling melengkapi:"
    )

    pdf.numbered_points([
        "Lapisan 1 (Pondasi Kiosk): Terapkan Opsi 1 pada formulir buku tamu digital. Setelah submit, layar menampilkan pesan sukses dengan hitung mundur 5 detik dan tombol besar 'Pengunjung Berikutnya' untuk mereset form. Ini menyelesaikan 90% kendala antrean harian.",
        "Lapisan 2 (Jaring Pengaman Katalog): Terapkan Opsi 2. Jika pengunjung memilih tombol 'Buka Katalog' di komputer kiosk, pasang timer idle 60 detik dan tombol '[Reset / Pengunjung Baru]' di navbar atas. Layar tidak akan tertinggal selamanya di katalog jika ditinggal.",
        "Lapisan 3 (Pencegah Lonjakan Rombongan): Terapkan Opsi 3 dengan mencetak standee akrilik QR Code di meja layanan. Jika rombongan 5-10 orang datang bersamaan, petugas cukup mempersilakan sebagian tamu memindai QR Code via smartphone masing-masing."
    ])

    pdf.alert_box(
        "Hasil Penerapan Solusi Terpadu: Terminal kiosk meja depan tidak akan pernah terkunci oleh sesi tamu sebelumnya, "
        "antrean mengalir cepat dan tertib, data buku tamu 100% akurat, dan petugas perpustakaan dapat fokus pada pelayanan informasi hukum.",
        kind="success",
        title="Manfaat Penerapan Solusi Terpadu"
    )

    # ═════════════════════════════════════════════════════════════════
    # HALAMAN 5: BAB 6 (POIN KEPUTUSAN & LEMBAR PENGESAHAN)
    # ═════════════════════════════════════════════════════════════════
    pdf.add_page()
    pdf.set_y(16)

    pdf.chapter_title("6", "Daftar Pertimbangan Keputusan & Lembar Pengesahan Rapat")

    pdf.paragraph(
        "Gunakan tabel checklist berikut sebagai panduan terstruktur saat berdiskusi bersama pegawai perpustakaan dan tim IT:"
    )

    pdf.styled_table(
        ["No", "Topik Pertimbangan Kebijakan", "Alternatif Pilihan", "Hasil Kesepakatan / Catatan Tim"],
        [
            ["1", "Durasi hitung mundur layar sukses", "[ ] 5 Detik    [ ] 8 Detik    [ ] 10 Detik", "...................................................................."],
            ["2", "Aksi default setelah timer habis", "[ ] Reset Form Bersih    [ ] Buka Katalog", "...................................................................."],
            ["3", "Toleransi idle di katalog sebelum reset", "[ ] 60 Detik   [ ] 90 Detik   [ ] 120 Detik", "...................................................................."],
            ["4", "Penyediaan Standee QR Code Meja", "[ ] Setuju cetak akrilik   [ ] Belum perlu", "...................................................................."],
            ["5", "Penyediaan akses Wi-Fi tamu", "[ ] Tersedia di lobi       [ ] Belum tersedia", "...................................................................."]
        ],
        [8, 54, 56, 62],
        font_size=7.6
    )

    pdf.sub_title("Catatan Tambahan Hasil Diskusi Internal:")
    pdf.set_fill_color(250, 250, 250)
    pdf.rect(15, pdf.get_y(), pdf.w - 30, 22)
    pdf.set_xy(18, pdf.get_y() + 2)
    pdf.set_font("Helvetica", "I", 8)
    pdf.set_text_color(*INK_LIGHT)
    pdf.multi_cell(pdf.w - 36, 4.5, "Tuliskan poin kesepakatan khusus, preferensi staf operasional, atau kendala lapangan lainnya di sini:\n\n\n")
    pdf.set_y(pdf.get_y() + 22)

    pdf.sub_title("Lembar Pengesahan & Komitmen Tindak Lanjut")

    y_sign = pdf.get_y() + 2
    col_w = (pdf.w - 30 - 10) / 2

    # Pihak 1: Staf / Pengelola Perpustakaan
    pdf.rect(15, y_sign, col_w, 38)
    pdf.set_xy(17, y_sign + 3)
    pdf.set_font("Helvetica", "B", 8.5)
    pdf.set_text_color(*INK)
    pdf.cell(col_w - 4, 5, "Perwakilan Pengelola Perpustakaan", align="C")
    pdf.set_xy(17, y_sign + 7.5)
    pdf.set_font("Helvetica", "", 7.5)
    pdf.set_text_color(*INK_LIGHT)
    pdf.cell(col_w - 4, 4, "Kejaksaan Tinggi Jawa Barat", align="C")

    pdf.set_xy(17, y_sign + 26)
    pdf.set_font("Helvetica", "", 8.5)
    pdf.set_text_color(*INK)
    pdf.cell(col_w - 4, 5, "( .............................................................. )", align="C")
    pdf.set_xy(17, y_sign + 31)
    pdf.set_font("Helvetica", "I", 7.5)
    pdf.set_text_color(*INK_LIGHT)
    pdf.cell(col_w - 4, 4, "NIP / Jabatan:", align="C")

    # Pihak 2: Tim IT / Pengembang Aplikasi
    pdf.rect(15 + col_w + 10, y_sign, col_w, 38)
    pdf.set_xy(15 + col_w + 12, y_sign + 3)
    pdf.set_font("Helvetica", "B", 8.5)
    pdf.set_text_color(*INK)
    pdf.cell(col_w - 4, 5, "Perwakilan Tim Pengembang Sistem / IT", align="C")
    pdf.set_xy(15 + col_w + 12, y_sign + 7.5)
    pdf.set_font("Helvetica", "", 7.5)
    pdf.set_text_color(*INK_LIGHT)
    pdf.cell(col_w - 4, 4, "Sistem Informasi Perpustakaan", align="C")

    pdf.set_xy(15 + col_w + 12, y_sign + 26)
    pdf.set_font("Helvetica", "", 8.5)
    pdf.set_text_color(*INK)
    pdf.cell(col_w - 4, 5, "( .............................................................. )", align="C")
    pdf.set_xy(15 + col_w + 12, y_sign + 31)
    pdf.set_font("Helvetica", "I", 7.5)
    pdf.set_text_color(*INK_LIGHT)
    pdf.cell(col_w - 4, 4, "Tanggal Diskusi: ..... / ..... / 2026", align="C")

    pdf.output(filename)
    print(f"PDF berhasil dibuat: {filename}")


if __name__ == "__main__":
    output_pdf = "Kajian_Alur_Kiosk_Buku_Tamu_Kejati_Jabar.pdf"
    generate_kajian_pdf(output_pdf)
