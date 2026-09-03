<?php

namespace App\Livewire\Admin;

use App\Models\Visitor;
use App\Services\XlsxReportWriter;
use App\Support\Csv;
use Livewire\Component;
use Livewire\WithPagination;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\StreamedResponse;

class VisitorReport extends Component
{
    use WithPagination;

    public string $search = '';

    public string $date = '';

    public string $kategori = ''; // '' (Semua), 'umum', 'pegawai'

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function updatedDate(): void
    {
        $this->resetPage();
    }

    public function updatedKategori(): void
    {
        $this->resetPage();
    }

    public function exportCsv(): StreamedResponse
    {
        $filename = 'laporan-pengunjung-'.now()->format('Y-m-d_H-i-s').'.csv';

        return response()->streamDownload(function (): void {
            $handle = fopen('php://output', 'w');
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

        return app(XlsxReportWriter::class)->download(
            'laporan-pengunjung-'.now()->format('Y-m-d_H-i-s').'.xlsx',
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
            }
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
            ->when($this->date, fn ($query) => $query->whereDate('created_at', $this->date));
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

    public function exportPdf(): \Symfony\Component\HttpFoundation\Response
    {
        abort_unless(auth()->user()?->isAdmin(), 403);

        $filename = 'laporan-pengunjung-'.now()->format('Y-m-d_H-i-s').'.pdf';

        $visitors = $this->filteredVisitorsQuery()->orderByDesc('id')->get();
        
        return \Barryvdh\DomPDF\Facade\Pdf::loadView('reports.visitors-pdf', [
            'visitors' => $visitors,
            'from' => null,
            'to' => null,
        ])
        ->setPaper('a4', 'landscape')
        ->download($filename);
    }
}
