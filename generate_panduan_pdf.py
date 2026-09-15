#!/usr/bin/env python3
"""Generate the user-guide PDF from BUKU_PANDUAN.md."""

from __future__ import annotations

import re
from pathlib import Path

from fpdf import FPDF


ROOT = Path(__file__).resolve().parent
SOURCE = ROOT / "BUKU_PANDUAN.md"
OUTPUT = ROOT / "Buku_Panduan_Penggunaan_Perpus_Kejati_Jabar.pdf"
LOGO = ROOT / "public" / "apple-touch-icon.png"

GREEN = (22, 101, 52)
DARK_GREEN = (6, 78, 59)
GOLD = (244, 197, 66)
GOLD_DARK = (128, 100, 0)
INK = (31, 41, 55)
MUTED = (91, 103, 117)
SURFACE = (247, 250, 248)
BORDER = (218, 226, 220)
WHITE = (255, 255, 255)


def font_path(name: str) -> Path:
    candidates = [
        Path("/usr/share/fonts/TTF") / name,
        Path("/usr/share/fonts/truetype/dejavu") / name,
    ]
    for candidate in candidates:
        if candidate.is_file():
            return candidate
    raise FileNotFoundError(f"Font {name} tidak ditemukan")


def plain(text: str) -> str:
    """Remove the small Markdown subset used by the guide."""
    text = re.sub(r"!\[([^]]*)]\([^)]+\)", r"\1", text)
    text = re.sub(r"\[([^]]+)]\([^)]+\)", r"\1", text)
    text = text.replace("**", "").replace("__", "")
    text = text.replace("`", "").replace("*", "")
    return text.strip()


class GuidePDF(FPDF):
    is_cover = True

    def __init__(self) -> None:
        super().__init__(orientation="P", unit="mm", format="A4")
        self.set_margins(17, 20, 17)
        self.set_auto_page_break(auto=True, margin=22)
        self.add_font("GuideSans", "", str(font_path("DejaVuSans.ttf")))
        self.add_font("GuideSans", "B", str(font_path("DejaVuSans-Bold.ttf")))
        self.add_font("GuideSans", "I", str(font_path("DejaVuSans-Oblique.ttf")))
        self.add_font("GuideMono", "", str(font_path("DejaVuSansMono.ttf")))
        self.alias_nb_pages()

    def header(self) -> None:
        if self.is_cover:
            return
        self.set_draw_color(*GREEN)
        self.set_line_width(.55)
        self.line(17, 12, self.w - 17, 12)
        self.set_xy(17, 6)
        self.set_font("GuideSans", "I", 7)
        self.set_text_color(*MUTED)
        self.cell(0, 5, "Panduan Penggunaan — Perpustakaan Kejati Jawa Barat")

    def footer(self) -> None:
        if self.is_cover:
            return
        self.set_y(-16)
        self.set_draw_color(*BORDER)
        self.set_line_width(.3)
        self.line(17, self.h - 17, self.w - 17, self.h - 17)
        self.set_font("GuideSans", "", 7.5)
        self.set_text_color(*MUTED)
        self.cell(0, 8, f"Panduan internal · Versi 3.0 · Halaman {self.page_no()} dari {{nb}}", align="C")

    @property
    def content_width(self) -> float:
        return self.w - self.l_margin - self.r_margin

    def ensure_space(self, height: float) -> None:
        if self.get_y() + height > self.h - 22:
            self.add_page()

    def cover(self) -> None:
        self.is_cover = True
        self.add_page()
        self.set_fill_color(*DARK_GREEN)
        self.rect(0, 0, self.w, self.h, "F")
        self.set_fill_color(*GOLD)
        self.rect(0, 0, self.w, 4, "F")
        self.rect(0, self.h - 4, self.w, 4, "F")

        if LOGO.is_file():
            self.image(str(LOGO), x=(self.w - 40) / 2, y=32, w=40, h=40)

        self.set_y(89)
        self.set_font("GuideSans", "B", 25)
        self.set_text_color(*WHITE)
        self.multi_cell(0, 12, "BUKU PANDUAN\nPENGGUNAAN APLIKASI", align="C")
        self.ln(7)
        self.set_draw_color(*GOLD)
        self.set_line_width(.8)
        self.line(self.w / 2 - 38, self.get_y(), self.w / 2 + 38, self.get_y())
        self.ln(10)
        self.set_font("GuideSans", "", 13)
        self.set_text_color(*GOLD)
        self.multi_cell(0, 7, "Sistem Informasi Perpustakaan\nKejaksaan Tinggi Jawa Barat", align="C")
        self.ln(14)
        self.set_font("GuideSans", "", 9.5)
        self.set_text_color(194, 218, 203)
        self.cell(0, 6, "Versi 3.0 · 14 September 2026", align="C")
        self.ln(6)
        self.cell(0, 6, "Panduan internal untuk pengunjung, petugas, dan pengelola server", align="C")

    def heading(self, level: int, text: str) -> None:
        text = plain(text)
        if level == 2:
            # A major section always starts on a clean page unless the current
            # page has not received body content yet.
            if self.get_y() > self.t_margin + 1:
                self.add_page()
            self.set_fill_color(*GREEN)
            self.rect(self.l_margin, self.get_y(), self.content_width, 11, "F")
            self.set_xy(self.l_margin + 4, self.get_y() + 1.5)
            self.set_font("GuideSans", "B", 12)
            self.set_text_color(*WHITE)
            self.cell(self.content_width - 8, 7.5, text.upper())
            self.ln(14)
            return

        self.ensure_space(16)
        self.ln(1.5 if level == 3 else 2)
        self.set_x(self.l_margin)
        self.set_font("GuideSans", "B", 10.5 if level == 3 else 9.5)
        self.set_text_color(*GREEN if level == 3 else GOLD_DARK)
        self.multi_cell(self.content_width, 5.5 if level == 3 else 6, text)
        self.ln(.5 if level == 3 else 1)

    def paragraph(self, text: str) -> None:
        self.set_x(self.l_margin)
        self.set_font("GuideSans", "", 9.2)
        self.set_text_color(*INK)
        self.multi_cell(self.content_width, 5.25, plain(text))
        self.ln(1.2)

    def list_item(self, text: str, number: str | None = None, nested: bool = False) -> None:
        left = self.l_margin + (7 if nested else 3)
        marker = f"{number}." if number else "•"
        marker_width = max(5, self.get_string_width(marker) + 2)
        self.set_font("GuideSans", "", 9.1)
        self.set_text_color(*INK)
        self.set_x(left)
        self.cell(marker_width, 5.1, marker)
        self.multi_cell(self.w - self.r_margin - left - marker_width, 5.1, plain(text))
        self.ln(.5)

    def callout(self, text: str) -> None:
        text = plain(text)
        self.set_font("GuideSans", "", 8.8)
        lines = self.multi_cell(self.content_width - 12, 5, text, dry_run=True, output="LINES")
        height = max(13, len(lines) * 5 + 7)
        self.ensure_space(height + 3)
        y = self.get_y()
        self.set_fill_color(255, 250, 231)
        self.rect(self.l_margin, y, self.content_width, height, "F")
        self.set_fill_color(*GOLD)
        self.rect(self.l_margin, y, 3, height, "F")
        self.set_xy(self.l_margin + 7, y + 3.5)
        self.set_text_color(*INK)
        self.multi_cell(self.content_width - 12, 5, text)
        self.set_y(y + height + 3)

    def code_block(self, lines: list[str]) -> None:
        text = "\n".join(lines).rstrip()
        if not text:
            return
        self.set_font("GuideMono", "", 7.4)
        wrapped = self.multi_cell(self.content_width - 10, 4.2, text, dry_run=True, output="LINES")
        height = len(wrapped) * 4.2 + 7
        self.ensure_space(min(height, self.h - 45))
        y = self.get_y()
        self.set_fill_color(239, 244, 241)
        self.rect(self.l_margin, y, self.content_width, height, "F")
        self.set_xy(self.l_margin + 5, y + 3.5)
        self.set_text_color(*DARK_GREEN)
        self.multi_cell(self.content_width - 10, 4.2, text)
        self.set_y(y + height + 3)

    def table(self, rows: list[list[str]]) -> None:
        if not rows:
            return
        columns = max(len(row) for row in rows)
        normalized = [row + [""] * (columns - len(row)) for row in rows]
        lengths = [max(5, max(len(plain(row[i])) for row in normalized)) for i in range(columns)]
        weights = [min(value, 38) for value in lengths]
        total = sum(weights)
        widths = [self.content_width * weight / total for weight in weights]

        for row_index, row in enumerate(normalized):
            style = "B" if row_index == 0 else ""
            size = 7.6 if columns >= 3 else 8.2
            self.set_font("GuideSans", style, size)
            line_counts = [
                len(self.multi_cell(widths[i] - 4, 4.5, plain(cell), dry_run=True, output="LINES"))
                for i, cell in enumerate(row)
            ]
            height = max(7.5, max(line_counts) * 4.5 + 3)
            self.ensure_space(height)
            y = self.get_y()
            x = self.l_margin
            fill = row_index == 0 or row_index % 2 == 0
            self.set_fill_color(*(GREEN if row_index == 0 else SURFACE))
            self.set_text_color(*(WHITE if row_index == 0 else INK))
            self.set_draw_color(*BORDER)
            for index, cell in enumerate(row):
                self.rect(x, y, widths[index], height, "DF" if fill else "D")
                self.set_xy(x + 2, y + 1.5)
                self.multi_cell(widths[index] - 4, 4.5, plain(cell))
                x += widths[index]
            self.set_y(y + height)
        self.ln(3)


def markdown_body(text: str) -> list[str]:
    lines = text.splitlines()
    first_rule = next((index for index, line in enumerate(lines) if line.strip() == "---"), 0)
    return lines[first_rule + 1 :]


def render_markdown(pdf: GuidePDF, lines: list[str]) -> None:
    index = 0
    paragraph: list[str] = []

    def flush_paragraph() -> None:
        if paragraph:
            pdf.paragraph(" ".join(part.strip() for part in paragraph))
            paragraph.clear()

    while index < len(lines):
        line = lines[index]
        stripped = line.strip()

        if stripped.startswith("```"):
            flush_paragraph()
            code: list[str] = []
            index += 1
            while index < len(lines) and not lines[index].strip().startswith("```"):
                code.append(lines[index])
                index += 1
            pdf.code_block(code)
        elif stripped.startswith("|") and index + 1 < len(lines) and re.match(r"^\s*\|?\s*:?-+", lines[index + 1]):
            flush_paragraph()
            table_rows: list[list[str]] = []
            while index < len(lines) and lines[index].strip().startswith("|"):
                cells = [cell.strip() for cell in lines[index].strip().strip("|").split("|")]
                if not all(re.fullmatch(r":?-+:?", cell.replace(" ", "")) for cell in cells):
                    table_rows.append(cells)
                index += 1
            pdf.table(table_rows)
            continue
        elif match := re.match(r"^(#{2,4})\s+(.+)$", stripped):
            flush_paragraph()
            pdf.heading(len(match.group(1)), match.group(2))
        elif stripped.startswith(">"):
            flush_paragraph()
            pdf.callout(stripped.lstrip("> "))
        elif match := re.match(r"^(\s*)-\s+(.+)$", line):
            flush_paragraph()
            pdf.list_item(match.group(2), nested=len(match.group(1)) > 0)
        elif match := re.match(r"^(\d+)\.\s+(.+)$", stripped):
            flush_paragraph()
            pdf.list_item(match.group(2), number=match.group(1))
        elif stripped in {"", "---"}:
            flush_paragraph()
        else:
            paragraph.append(stripped)
        index += 1

    flush_paragraph()


def build() -> Path:
    source = SOURCE.read_text(encoding="utf-8")
    pdf = GuidePDF()
    pdf.cover()
    pdf.is_cover = False
    pdf.add_page()
    render_markdown(pdf, markdown_body(source))
    pdf.output(str(OUTPUT))
    return OUTPUT


if __name__ == "__main__":
    result = build()
    print(f"PDF dibuat: {result} ({result.stat().st_size:,} byte)")
