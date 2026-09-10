<?php

namespace App\Livewire\Admin;

use App\Models\Visitor;
use App\Services\XlsxReportWriter;
use App\Support\Csv;
use Barryvdh\DomPDF\Facade\Pdf;
use Livewire\Component;
use Livewire\WithPagination;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\StreamedResponse;

class VisitorReport extends Component
{
    use WithPagination;

    public string $search = '';

    public string $date = '';

    public string $startDate = '';

    public string $endDate = '';

    public string $kategori = ''; // '' (Semua), 'umum', 'pegawai'

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function updatedDate(): void
    {
        $this->resetPage();
    }

    public function updatedStartDate(): void
    {
        $this->resetPage();
    }

    public function updatedEndDate(): void
    {
        $this->resetPage();
    }

    public function resetDateRange(): void
    {
        $this->startDate = '';
        $this->endDate = '';
        $this->date = '';
        $this->resetPage();
    }

    public function updatedKategori(): void
    {
        $this->resetPage();
    }

    public function exportCsv(): StreamedResponse
    {
        [$from, $to, $suffix, $label] = $this->periodInfo();
        $filename = 'laporan-pengunjung'.$suffix.'-'.now()->format('Y-m-d_H-i-s').'.csv';
        $printedAt = now()->format('Y-m-d H:i:s');
        $printedBy = auth()->user()?->name ?? 'Sistem';

        return response()->streamDownload(function () use ($label, $printedAt, $printedBy): void {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, ['Laporan Pengunjung']);
            fputcsv($handle, ['Periode', $label]);
            fputcsv($handle, ['Dicetak pada', $printedAt]);
            fputcsv($handle, ['Dicetak oleh', $printedBy]);
            fputcsv($handle, []);
            fputcsv($handle, ['Kategori', 'Nama', 'NIP / NRP', 'Instansi / Unit', 'Kontak (HP/Email)', 'Keperluan', 'Waktu Kunjungan']);

            $this->filteredVisitorsQuery()
                ->chunkByIdDesc(500, function ($visitors) use ($handle): void {
                    foreach ($visitors as $visitor) {
                        fputcsv($handle, array_map([Csv::class, 'safeCell'], [
                            $visitor->kategori === 'pegawai' ? 'Pegawai Kejaksaan' : 'Tamu / Umum',
                            $visitor->nama,
                            $visitor->nip ?? '-',
                            $visitor->instansi_unit,
                            $visitor->no_hp ?? '-',
                            $visitor->keperluan,
                            $visitor->created_at->format('Y-m-d H:i:s'),
                        ]));
                    }
                });

            fclose($handle);
        }, $filename, ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    public function exportXlsx(): BinaryFileResponse
    {
        abort_unless(auth()->user()?->isAdmin(), 403);

        [$from, $to, $suffix, $label] = $this->periodInfo();

        return app(XlsxReportWriter::class)->download(
            'laporan-pengunjung'.$suffix.'-'.now()->format('Y-m-d_H-i-s').'.xlsx',
            ['Kategori', 'Nama', 'NIP / NRP', 'Instansi / Unit', 'Kontak', 'Keperluan', 'Waktu Kunjungan'],
            function (callable $appendRow): void {
                $this->filteredVisitorsQuery()->orderByDesc('id')->chunkByIdDesc(500, function ($visitors) use ($appendRow): void {
                    foreach ($visitors as $visitor) {
                        $appendRow([
                            $visitor->kategori === 'pegawai' ? 'Pegawai Kejaksaan' : 'Tamu / Umum',
                            $visitor->nama, $visitor->nip ?? '', $visitor->instansi_unit,
                            $visitor->no_hp ?? '', $visitor->keperluan, $visitor->created_at->format('Y-m-d H:i:s'),
                        ]);
                    }
                });
            },
            title: 'Laporan Pengunjung',
            meta: [
                'Periode' => $label,
                'Dicetak pada' => now()->format('Y-m-d H:i:s'),
                'Dicetak oleh' => auth()->user()?->name ?? 'Sistem',
            ],
        );
    }

    private function filteredVisitorsQuery()
    {
        return Visitor::query()
            ->when($this->search, fn ($query) => $query->where(fn ($query) => $query
                ->where('nama', 'like', "%{$this->search}%")
                ->orWhere('instansi_unit', 'like', "%{$this->search}%")
                ->orWhere('nip', 'like', "%{$this->search}%")))
            ->when($this->kategori, fn ($query) => $query->where('kategori', $this->kategori))
            ->when($this->startDate, fn ($query) => $query->whereDate('created_at', '>=', $this->startDate))
            ->when($this->endDate, fn ($query) => $query->whereDate('created_at', '<=', $this->endDate))
            ->when($this->date && ! $this->startDate && ! $this->endDate, fn ($query) => $query->whereDate('created_at', $this->date));
    }

    public function render()
    {
        return view('livewire.admin.visitor-report', [
            'visitors' => $this->filteredVisitorsQuery()
                ->orderByDesc('id')
                ->paginate(15),
            'totalVisitors' => Visitor::count(),
            'totalPegawai' => Visitor::where('kategori', 'pegawai')->count(),
            'totalUmum' => Visitor::where('kategori', 'umum')->orWhereNull('kategori')->count(),
        ])->layout('layouts.admin', ['title' => 'Pengunjung']);
    }

    public function exportPdf(): StreamedResponse
    {
        abort_unless(auth()->user()?->isAdmin(), 403);

        [$from, $to, $suffix] = $this->periodInfo();
        $filename = 'laporan-pengunjung'.$suffix.'-'.now()->format('Y-m-d_H-i-s').'.pdf';

        $visitors = $this->filteredVisitorsQuery()->orderByDesc('id')->get();

        $pdf = Pdf::loadView('reports.visitors-pdf', [
            'visitors' => $visitors,
            'from' => $from,
            'to' => $to,
        ])
            ->setPaper('a4', 'landscape');

        return response()->streamDownload(function () use ($pdf): void {
            echo $pdf->output();
        }, $filename, ['Content-Type' => 'application/pdf']);
    }

    /**
     * Kembalikan [$from, $to, $suffix, $label] dari filter periode aktif.
     *
     * @return array{?string, ?string, string, string}
     */
    private function periodInfo(): array
    {
        $from = $this->startDate ?: ($this->date ?: null);
        $to = $this->endDate ?: ($this->date ?: null);

        if ($from && $to && $from === $to) {
            return [$from, $to, "-{$from}", "Tanggal {$from}"];
        }
        if ($from && $to) {
            return [$from, $to, "-{$from}-sd-{$to}", "{$from} s/d {$to}"];
        }
        if ($from) {
            return [$from, $to, "-sejak-{$from}", "Sejak {$from}"];
        }
        if ($to) {
            return [$from, $to, "-sampai-{$to}", "Sampai {$to}"];
        }

        return [null, null, '', 'Semua periode'];
    }
}
