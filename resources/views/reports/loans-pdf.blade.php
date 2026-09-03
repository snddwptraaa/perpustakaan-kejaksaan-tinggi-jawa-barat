<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>Laporan Peminjaman - Perpustakaan Kejati Jawa Barat</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 2cm; }
        h1 { text-align: center; margin-bottom: 20px; }
        .meta { text-align: center; margin-bottom: 30px; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { border: 1px solid #000; padding: 8px; text-align: left; }
        th { background-color: #f0f0f0; }
        .status-pending { color: #d97706; }
        .status-returned { color: #059669; }
        .status-overdue { color: #dc2626; }
        .footer { position: fixed; bottom: 1cm; width: 100%; text-align: center; font-size: 10px; }
        .page-number:after { content: counter(page); }
    </style>
</head>
<body>
    <h1>Laporan Peminjaman Perpustakaan</h1>
    <div class="meta">
        Periode Peminjaman: {{ $from ? date('d/m/Y', strtotime($from)) : 'Semua' }} 
        sampai {{ $to ? date('d/m/Y', strtotime($to)) : 'Sekarang' }}<br>
        Status: {{ $statusFilter === 'semua' ? 'Semua Status' : ($statusFilter === 'pending' ? 'Belum Kembali' : 'Sudah Kembali') }}
    </div>

    <table>
        <thead>
            <tr>
                <th>Peminjam</th>
                <th>Buku</th>
                <th>Tanggal Pinjam</th>
                <th>Jatuh Tempo</th>
                <th>Tanggal Kembali</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($loans as $loan)
            <tr>
                <td>{{ $loan->member->nama ?? $loan->nama_peminjam }}<br>
                    <small>{{ $loan->member->nip ?? $loan->nip_peminjam }}</small></td>
                <td>{{ $loan->book->judul ?? '-' }}<br>
                    <small>{{ $loan->book->isbn ?? '-' }}</small></td>
                <td>{{ $loan->tanggal_pinjam->format('d/m/Y') }}</td>
                <td>{{ $loan->tanggal_jatuh_tempo->format('d/m/Y') }}</td>
                <td>{{ $loan->tanggal_kembali ? $loan->tanggal_kembali->format('d/m/Y') : '-' }}</td>
                <td class="{{ $loan->status === 'returned' ? 'status-returned' : ($loan->current_status === 'terlambat' ? 'status-overdue' : 'status-pending') }}">
                    {{ $loan->status === 'returned' ? 'Dikembalikan' : ($loan->current_status === 'terlambat' ? 'Terlambat' : 'Pending') }}
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <div class="footer">
        <span>Perpustakaan Kejaksaan Tinggi Jawa Barat - Dicetak pada {{ now()->format('d/m/Y H:i') }}</span>
        <span>Halaman <span class="page-number"></span></span>
    </div>
</body>
</html>
