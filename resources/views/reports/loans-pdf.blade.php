<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>Laporan Peminjaman - Perpustakaan Kejati Jawa Barat</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Segoe UI', Arial, sans-serif; font-size: 10px; color: #1a1a1a; padding: 1cm; }
        .header { text-align: center; margin-bottom: 16px; border-bottom: 2px solid #166534; padding-bottom: 10px; }
        .header h1 { font-size: 16px; color: #166534; margin-bottom: 4px; font-weight: bold; }
        .header p { font-size: 9px; color: #4b5563; }
        .meta { font-size: 9px; color: #4b5563; margin-bottom: 12px; text-align: center; }
        table { width: 100%; border-collapse: collapse; }
        th { background: #166534; color: white; padding: 6px 8px; text-align: left; font-size: 9px; font-weight: 600; }
        td { padding: 5px 8px; border-bottom: 1px solid #e5e5e5; font-size: 9px; vertical-align: top; }
        tr:nth-child(even) td { background: #f9fafb; }
        .badge { display: inline-block; padding: 2px 6px; border-radius: 4px; font-size: 8px; font-weight: bold; }
        .status-dipinjam { background: #fef3c7; color: #92400e; }
        .status-dikembalikan { background: #dcfce7; color: #166534; }
        .status-terlambat { background: #fee2e2; color: #991b1b; }
        .status-dibatalkan { background: #f3f4f6; color: #4b5563; }
        .footer { position: fixed; bottom: 1cm; left: 1cm; right: 1cm; text-align: center; font-size: 8px; color: #999; border-top: 1px solid #ddd; padding-top: 6px; }
        .page-number:after { content: counter(page); }
        .total { text-align: right; font-size: 9px; color: #4b5563; margin-top: 8px; font-weight: 600; }
    </style>
</head>
<body>
    <div class="header">
        <h1>LAPORAN PEMINJAMAN BUKU PERPUSTAKAAN</h1>
        <p>Kejaksaan Tinggi Jawa Barat</p>
    </div>
    <div class="meta">
        Periode: {{ $from ? date('d/m/Y', strtotime($from)) : 'Awal' }} 
        s.d. {{ $to ? date('d/m/Y', strtotime($to)) : 'Sekarang' }}
        &bull; Status: {{ match($statusFilter ?? 'semua') {
            'dipinjam' => 'Sedang Dipinjam',
            'terlambat' => 'Terlambat Kembali',
            'dikembalikan' => 'Sudah Dikembalikan',
            'dibatalkan' => 'Dibatalkan',
            default => 'Semua Status'
        } }}
        &bull; Dicetak: {{ now()->translatedFormat('d F Y H:i') }} WIB
    </div>

    <table>
        <thead>
            <tr>
                <th style="width: 4%; text-align: center;">No</th>
                <th style="width: 22%;">Peminjam</th>
                <th style="width: 32%;">Buku / Judul</th>
                <th style="width: 12%;">Tgl Pinjam</th>
                <th style="width: 12%;">Jatuh Tempo</th>
                <th style="width: 10%;">Tgl Kembali</th>
                <th style="width: 8%; text-align: center;">Status</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($loans as $i => $loan)
            <tr>
                <td style="text-align: center;">{{ $i + 1 }}</td>
                <td>
                    <strong>{{ $loan->member->nama ?? $loan->nama_peminjam }}</strong>
                    @if($loan->member?->nip || $loan->nip_peminjam)
                        <br><span style="color: #666; font-size: 8px;">NIP: {{ $loan->member->nip ?? $loan->nip_peminjam }}</span>
                    @endif
                    @if($loan->member?->instansi_unit || $loan->instansi_unit)
                        <br><span style="color: #666; font-size: 8px;">{{ $loan->member->instansi_unit ?? $loan->instansi_unit }}</span>
                    @endif
                </td>
                <td>
                    <strong>{{ $loan->book->judul ?? '-' }}</strong>
                    @if($loan->book?->no_klasifikasi || $loan->book?->lokasi_rak)
                        <br><span style="color: #666; font-size: 8px;">
                            {{ $loan->book->no_klasifikasi ? '[' . $loan->book->no_klasifikasi . ']' : '' }}
                            {{ $loan->book->lokasi_rak ? '• ' . $loan->book->lokasi_rak : '' }}
                        </span>
                    @endif
                </td>
                <td>{{ $loan->tanggal_pinjam ? $loan->tanggal_pinjam->format('d/m/Y') : '-' }}</td>
                <td>{{ $loan->tanggal_jatuh_tempo ? $loan->tanggal_jatuh_tempo->format('d/m/Y') : '-' }}</td>
                <td>{{ $loan->tanggal_kembali ? $loan->tanggal_kembali->format('d/m/Y') : '-' }}</td>
                <td style="text-align: center;">
                    @php $st = $loan->current_status; @endphp
                    <span class="badge status-{{ $st }}">
                        {{ match($st) {
                            'dikembalikan' => 'Kembali',
                            'terlambat' => 'Terlambat',
                            'dibatalkan' => 'Batal',
                            default => 'Dipinjam'
                        } }}
                    </span>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="7" style="text-align: center; padding: 20px; color: #999;">Tidak ada transaksi peminjaman pada periode ini.</td>
            </tr>
            @endforelse
        </tbody>
    </table>

    @if($loans->count() > 0)
    <div class="total">Total Transaksi: {{ $loans->count() }} data</div>
    @endif

    <div class="footer">
        Perpustakaan Kejaksaan Tinggi Jawa Barat &bull; Halaman <span class="page-number"></span>
    </div>
</body>
</html>
