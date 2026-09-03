<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>Katalog Buku - Perpustakaan Kejati Jawa Barat</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Segoe UI', Arial, sans-serif; font-size: 11px; color: #1a1a1a; padding: 1.5cm; }
        .header { text-align: center; margin-bottom: 20px; border-bottom: 2px solid #166534; padding-bottom: 12px; }
        .header h1 { font-size: 18px; color: #166534; margin-bottom: 4px; }
        .header p { font-size: 10px; color: #666; }
        .meta { text-align: right; font-size: 9px; color: #888; margin-bottom: 12px; }
        table { width: 100%; border-collapse: collapse; }
        th { background: #166534; color: white; padding: 6px 8px; text-align: left; font-size: 10px; font-weight: 600; }
        td { padding: 5px 8px; border-bottom: 1px solid #e5e5e5; font-size: 10px; vertical-align: top; }
        tr:nth-child(even) td { background: #f9fafb; }
        .footer { position: fixed; bottom: 1cm; left: 1.5cm; right: 1.5cm; text-align: center; font-size: 8px; color: #999; border-top: 1px solid #ddd; padding-top: 6px; }
        .page-number:after { content: counter(page); }
        .total { text-align: right; font-size: 10px; color: #666; margin-top: 10px; font-weight: 600; }
    </style>
</head>
<body>
    <div class="header">
        <h1>KATALOG BUKU PERPUSTAKAAN</h1>
        <p>Kejaksaan Tinggi Jawa Barat</p>
    </div>
    <div class="meta">Dicetak: {{ now()->translatedFormat('l, d F Y H:i') }} WIB</div>
    
    <table>
        <thead>
            <tr>
                <th style="width:5%">No</th>
                <th style="width:35%">Judul</th>
                <th style="width:20%">Penulis</th>
                <th style="width:15%">ISBN</th>
                <th style="width:15%">Kategori</th>
                <th style="width:10%">Lokasi</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($books as $i => $book)
            <tr>
                <td style="text-align:center">{{ $i + 1 }}</td>
                <td>{{ $book->judul }}</td>
                <td>{{ $book->penulis }}</td>
                <td style="font-family:monospace;font-size:9px">{{ $book->isbn ?: '-' }}</td>
                <td>{{ $book->category->nama_kategori ?? '-' }}</td>
                <td style="text-align:center">{{ $book->lokasi_rak ?: '-' }}</td>
            </tr>
            @empty
            <tr>
                <td colspan="6" style="text-align:center;padding:20px;color:#999">Tidak ada buku yang ditemukan.</td>
            </tr>
            @endforelse
        </tbody>
    </table>
    
    @if($books->count() > 0)
    <div class="total">Total: {{ $books->count() }} judul buku</div>
    @endif
    
    <div class="footer">
        Perpustakaan Kejaksaan Tinggi Jawa Barat &bull; Halaman <span class="page-number"></span>
    </div>
</body>
</html>
