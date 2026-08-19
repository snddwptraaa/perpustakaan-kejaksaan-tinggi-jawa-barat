<?php

namespace App\Livewire\Admin;

use App\Models\Visitor;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Livewire\Component;
use Livewire\WithPagination;

class VisitorReport extends Component
{
    use WithPagination;

    public string $search = '';
    public string $date = '';

    public function updatedSearch(): void { $this->resetPage(); }
    public function updatedDate(): void { $this->resetPage(); }

    public function exportCsv(): StreamedResponse
    {
        $filename = 'laporan-pengunjung-'.now()->format('Y-m-d_H-i-s').'.csv';

        return response()->streamDownload(function (): void {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, ['Nama', 'NIP', 'Instansi / Unit', 'No. HP', 'Keperluan', 'Waktu Kunjungan']);

            $this->filteredVisitorsQuery()
                ->latest()
                ->chunkById(500, function ($visitors) use ($handle): void {
                    foreach ($visitors as $visitor) {
                        fputcsv($handle, [
                            $visitor->nama,
                            $visitor->nip ?? '',
                            $visitor->instansi_unit,
                            $visitor->no_hp ?? '',
                            $visitor->keperluan,
                            $visitor->created_at->format('Y-m-d H:i:s'),
                        ]);
                    }
                });

            fclose($handle);
        }, $filename, ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    private function filteredVisitorsQuery()
    {
        return Visitor::query()
            ->when($this->search, fn ($query) => $query->where(fn ($query) => $query
                ->where('nama', 'like', "%{$this->search}%")
                ->orWhere('instansi_unit', 'like', "%{$this->search}%")))
            ->when($this->date, fn ($query) => $query->whereDate('created_at', $this->date));
    }

    public function render()
    {
        return view('livewire.admin.visitor-report', [
            'visitors' => $this->filteredVisitorsQuery()->latest()->paginate(15),
        ])->layout('layouts.admin', ['title' => 'Pengunjung']);
    }
}
