<?php

namespace App\Console\Commands;

use App\Models\Loan;
use App\Models\Visitor;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Console\Command;

class ExportReportPdf extends Command
{
    protected $signature = 'reports:print {type : visitors atau loans} {--from=} {--to=} {--path=}';

    protected $description = 'Membuat laporan PDF dari data pengunjung atau peminjaman.';

    public function handle(): int
    {
        $type = (string) $this->argument('type');
        if (! in_array($type, ['visitors', 'loans'], true)) {
            $this->error('Jenis laporan harus visitors atau loans.');

            return self::INVALID;
        }

        $from = $this->option('from');
        $to = $this->option('to');
        $query = $type === 'visitors' ? Visitor::query() : Loan::with(['book', 'member']);
        $dateColumn = $type === 'visitors' ? 'created_at' : 'tanggal_pinjam';
        $query->when($from, fn ($query) => $query->whereDate($dateColumn, '>=', $from));
        $query->when($to, fn ($query) => $query->whereDate($dateColumn, '<=', $to));

        $rows = $query->latest()->take(200)->get();
        
        $viewName = $type === 'visitors' ? 'reports.visitors-pdf' : 'reports.loans-pdf';
        $pdf = Pdf::loadView($viewName, [
            'visitors' => $type === 'visitors' ? $rows : collect(),
            'loans' => $type === 'loans' ? $rows : collect(),
            'from' => $from,
            'to' => $to,
            'statusFilter' => 'semua',
        ]);
        
        $path = $this->option('path') ?: storage_path('app/reports/'.$type.'-'.now()->format('Y-m-d_H-i-s').'.pdf');
        if (! is_dir(dirname($path)) && ! mkdir(dirname($path), 0750, true) && ! is_dir(dirname($path))) {
            throw new \RuntimeException('Direktori laporan tidak dapat dibuat.');
        }
        
        file_put_contents($path, $pdf->output());

        $this->info("Laporan PDF berhasil dibuat: {$path}");

        return self::SUCCESS;
    }
}
