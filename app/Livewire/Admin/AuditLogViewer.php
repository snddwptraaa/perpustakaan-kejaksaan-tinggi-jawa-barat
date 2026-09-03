<?php

namespace App\Livewire\Admin;

use App\Models\AuditLog;
use Livewire\Component;
use Livewire\WithPagination;

class AuditLogViewer extends Component
{
    use WithPagination;

    public string $search = '';

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function render()
    {
        $search = trim($this->search);

        return view('livewire.admin.audit-log-viewer', [
            'logs' => AuditLog::with('user')
                ->when($search !== '', fn ($query) => $query->where(fn ($query) => $query
                    ->where('aksi', 'like', "%{$search}%")
                    ->orWhere('entitas', 'like', "%{$search}%")))
                ->latest()->paginate(20),
        ])->layout('layouts.admin', ['title' => 'Audit Log']);
    }
}
