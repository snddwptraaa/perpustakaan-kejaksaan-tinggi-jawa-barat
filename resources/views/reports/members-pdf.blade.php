<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>Daftar Anggota - Perpustakaan Kejati Jawa Barat</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Segoe UI', Arial, sans-serif; font-size: 10px; color: #1a1a1a; padding: 1cm; }
        .header { text-align: center; margin-bottom: 16px; border-bottom: 2px solid #166534; padding-bottom: 10px; }
        .header h1 { font-size: 16px; color: #166534; margin-bottom: 4px; }
        .header p, .meta { font-size: 9px; color: #666; }
        .meta { margin-bottom: 12px; text-align: center; }
        table { width: 100%; border-collapse: collapse; }
        th { background: #166534; color: white; padding: 6px; text-align: left; font-size: 9px; }
        td { padding: 5px 6px; border-bottom: 1px solid #e5e5e5; font-size: 9px; vertical-align: top; }
        tr:nth-child(even) td { background: #f9fafb; }
        .center { text-align: center; }
        .footer { position: fixed; bottom: 1cm; left: 1cm; right: 1cm; border-top: 1px solid #ddd; padding-top: 6px; text-align: center; font-size: 8px; color: #999; }
        .page-number:after { content: counter(page); }
        .total { margin-top: 8px; text-align: right; font-size: 9px; font-weight: 600; color: #666; }
    </style>
</head>
<body>
    <div class="header">
        <h1>DAFTAR ANGGOTA PERPUSTAKAAN</h1>
        <p>Kejaksaan Tinggi Jawa Barat</p>
    </div>
    <div class="meta">Dicetak: {{ now()->translatedFormat('d F Y H:i') }} WIB</div>
    <table>
        <thead>
            <tr><th style="width:4%">No</th><th style="width:21%">Nama</th><th style="width:16%">NIP</th><th style="width:23%">Instansi / Unit</th><th style="width:14%">No. HP</th><th style="width:10%">Status</th><th style="width:12%">Peminjaman</th></tr>
        </thead>
        <tbody>
            @forelse ($members as $index => $member)
                <tr>
                    <td class="center">{{ $index + 1 }}</td><td>{{ $member->nama }}</td><td>{{ $member->nip ?: '-' }}</td><td>{{ $member->instansi_unit ?: '-' }}</td><td>{{ $member->no_hp ?: '-' }}</td><td class="center">{{ $member->aktif ? 'Aktif' : 'Nonaktif' }}</td><td class="center">{{ $member->loans_count }}</td>
                </tr>
            @empty
                <tr><td colspan="7" class="center" style="padding:20px;color:#999">Tidak ada anggota yang ditemukan.</td></tr>
            @endforelse
        </tbody>
    </table>
    @if ($members->isNotEmpty()) <div class="total">Total: {{ $members->count() }} anggota</div> @endif
    <div class="footer">Perpustakaan Kejaksaan Tinggi Jawa Barat &bull; Halaman <span class="page-number"></span></div>
</body>
</html>
