<!DOCTYPE html>
<html lang="id"><head><meta charset="utf-8"><title>Laporan perpustakaan</title><style>body{font:12px Arial,sans-serif;color:#17202a;margin:32px}h1{font-size:20px}p{color:#5b6573}table{width:100%;border-collapse:collapse;margin-top:20px}th,td{border:1px solid #cfd6dc;padding:7px;text-align:left;vertical-align:top}th{background:#0f5132;color:#fff}@media print{body{margin:0}}</style></head>
<body>
    <h1>Laporan {{ $type === 'visitors' ? 'Pengunjung' : 'Peminjaman' }}</h1>
    <p>Perpustakaan Kejaksaan Tinggi Jawa Barat · Periode {{ $from ?: 'awal data' }} sampai {{ $to ?: 'sekarang' }}</p>
    <table><thead><tr>
        @if ($type === 'visitors')<th>Nama</th><th>Instansi</th><th>Keperluan</th><th>Waktu</th>
        @else<th>Peminjam</th><th>Buku</th><th>Pinjam</th><th>Jatuh tempo</th><th>Status</th>@endif
    </tr></thead><tbody>
        @foreach ($rows as $row)<tr>
            @if ($type === 'visitors')<td>{{ $row->nama }}</td><td>{{ $row->instansi_unit }}</td><td>{{ $row->keperluan }}</td><td>{{ $row->created_at?->format('d-m-Y H:i') }}</td>
            @else<td>{{ $row->nama_peminjam }}</td><td>{{ $row->book?->judul ?? '—' }}</td><td>{{ $row->tanggal_pinjam?->format('d-m-Y') }}</td><td>{{ $row->tanggal_jatuh_tempo?->format('d-m-Y') }}</td><td>{{ ucfirst($row->current_status) }}</td>@endif
        </tr>@endforeach
    </tbody></table>
</body></html>
