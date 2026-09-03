<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>Laporan Pengunjung - Perpustakaan Kejati Jawa Barat</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Segoe UI', Arial, sans-serif; font-size: 10px; color: #1a1a1a; padding: 1cm; }
        .header { text-align: center; margin-bottom: 16px; border-bottom: 2px solid #166534; padding-bottom: 10px; }
        .header h1 { font-size: 16px; color: #166534; margin-bottom: 4px; }
        .header p { font-size: 9px; color: #666; }
        .meta { font-size: 9px; color: #666; margin-bottom: 12px; text-align: center; }
        table { width: 100%; border-collapse: collapse; }
        th { background: #166534; color: white; padding: 5px 6px; text-align: left; font-size: 9px; font-weight: 600; }
        td { padding: 4px 6px; border-bottom: 1px solid #e5e5e5; font-size: 9px; }
        tr:nth-child(even) td { background: #f9fafb; }
        .footer { position: fixed; bottom: 1cm; left: 1cm; right: 1cm; text-align: center; font-size: 8px; color: #999; border-top: 1px solid #ddd; padding-top: 6px; }
        .page-number:after { content: counter(page); }
        .total { text-align: right; font-size: 9px; color: #666; margin-top: 8px; font-weight: 600; }
    </style>
</head>
<body>
    <div class="header">
        <h1>LAPORAN PENGUNJUNG PERPUSTAKAAN</h1>
        <p>Kejaksaan Tinggi Jawa Barat</p>
    </div>
    <div class="meta">
        Periode: {{ $from ? date('d/m/Y', strtotime($from)) : 'Semua' }} 
        s.d. {{ $to ? date('d/m/Y', strtotime($to)) : 'Sekarang' }}
        &bull; Dicetak: {{ now()->translatedFormat('d F Y H:i') }} WIB
    </div>
    
    <table>
        <thead>
            <tr>
                <th style="width:5%">No</th>
                <th style="width:20%">Nama</th>
                <th style="width:20%">Instansi/Unit</th>
                <th style="width:15%">NIP</th>
                <th style="width:15%">Kontak</th>
                <th style="width:15%">Keperluan</th>
                <th style="width:10%">Waktu</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($visitors as $i => $visitor)
            <tr>
                <td style="text-align:center">{{ $i + 1 }}</td>
                <td>{{ $visitor->nama }}</td>
                <td>{{ $visitor->instansi_unit }}</td>
                <td style="font-family:monospace">{{ $visitor->nip ?: '-' }}</td>
                <td style="font-size:8px">{{ $visitor->no_hp ?: '-' }}</td>
                <td>{{ $visitor->keperluan }}</td>
                <td style="font-size:8px">{{ $visitor->created_at->format('d/m/Y H:i') }}</td>
            </tr>
            @empty
            <tr>
                <td colspan="7" style="text-align:center;padding:20px;color:#999">Tidak ada data pengunjung.</td>
            </tr>
            @endforelse
        </tbody>
    </table>
    
    @if($visitors->count() > 0)
    <div class="total">Total: {{ $visitors->count() }} pengunjung</div>
    @endif
    
    <div class="footer">
        Perpustakaan Kejaksaan Tinggi Jawa Barat &bull; Halaman <span class="page-number"></span>
    </div>
</body>
</html>
