<?php

namespace App\Livewire\Admin;

use App\Models\AuditLog;
use Livewire\Component;
use Livewire\WithPagination;

class AuditLogViewer extends Component
{
    use WithPagination;

    public string $search = '';

    public string $filterAksi = '';

    public string $filterEntitas = '';

    public string $startDate = '';

    public string $endDate = '';

    public ?int $selectedLogId = null;

    public bool $showDetailModal = false;

    public string $activeDetailTab = 'diff';

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function updatedFilterAksi(): void
    {
        $this->resetPage();
    }

    public function updatedFilterEntitas(): void
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

    public function resetFilters(): void
    {
        $this->reset(['search', 'filterAksi', 'filterEntitas', 'startDate', 'endDate']);
        $this->resetPage();
    }

    public function showDetail(int $logId): void
    {
        $this->selectedLogId = $logId;
        $this->activeDetailTab = 'diff';
        $this->showDetailModal = true;
    }

    public function closeDetail(): void
    {
        $this->showDetailModal = false;
        $this->selectedLogId = null;
    }

    public function getSelectedLogProperty(): ?AuditLog
    {
        if (! $this->selectedLogId) {
            return null;
        }

        return AuditLog::with('user')->find($this->selectedLogId);
    }

    public function render()
    {
        $search = trim($this->search);

        $query = AuditLog::with('user')
            ->when($search !== '', function ($query) use ($search): void {
                $query->where(function ($sub) use ($search): void {
                    $sub->where('aksi', 'like', "%{$search}%")
                        ->orWhere('entitas', 'like', "%{$search}%")
                        ->orWhere('entitas_id', 'like', "%{$search}%")
                        ->orWhere('ip_address', 'like', "%{$search}%")
                        ->orWhereHas('user', fn ($u) => $u->where('name', 'like', "%{$search}%"));
                });
            })
            ->when($this->filterAksi !== '', fn ($query) => $query->where('aksi', $this->filterAksi))
            ->when($this->filterEntitas !== '', fn ($query) => $query->where('entitas', $this->filterEntitas))
            ->when($this->startDate !== '', fn ($query) => $query->whereDate('created_at', '>=', $this->startDate))
            ->when($this->endDate !== '', fn ($query) => $query->whereDate('created_at', '<=', $this->endDate));

        return view('livewire.admin.audit-log-viewer', [
            'logs' => $query->latest()->paginate(20),
            'aksiOptions' => [
                'buat' => 'Tambah Data (Buat)',
                'ubah' => 'Ubah Data',
                'koreksi' => 'Koreksi Data',
                'hapus' => 'Hapus Data',
                'pinjam' => 'Peminjaman',
                'kembali' => 'Pengembalian',
                'perpanjang' => 'Perpanjangan',
                'batal' => 'Pembatalan',
                'arsipkan' => 'Arsipkan',
                'pulihkan' => 'Pulihkan',
                'nonaktifkan' => 'Nonaktifkan',
            ],
            'entitasOptions' => [
                'books' => 'Koleksi Buku',
                'loans' => 'Peminjaman',
                'members' => 'Anggota',
                'users' => 'Pengguna Admin',
                'categories' => 'Kategori Buku',
            ],
            'selectedLog' => $this->selectedLog,
        ])->layout('layouts.admin', ['title' => 'Audit Log Sistem']);
    }
}
