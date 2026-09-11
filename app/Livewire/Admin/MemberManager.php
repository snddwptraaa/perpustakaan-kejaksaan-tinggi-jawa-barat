<?php

namespace App\Livewire\Admin;

use App\Models\Member;
use App\Services\AuditLogger;
use App\Services\XlsxReportWriter;
use App\Support\Csv;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Cache;
use Illuminate\Validation\Rule;
use Livewire\Component;
use Livewire\WithPagination;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\StreamedResponse;

class MemberManager extends Component
{
    use WithPagination;

    public bool $showForm = false;

    public ?int $editingId = null;

    public string $search = '';

    public string $nama = '';

    public string $nip = '';

    public string $instansi_unit = '';

    public string $no_hp = '';

    public bool $aktif = true;

    protected function rules(): array
    {
        return [
            'nama' => ['required', 'string', 'max:255'],
            'nip' => [
                'nullable',
                'string',
                'max:30',
                Rule::unique('members', 'nip')->ignore($this->editingId),
            ],
            'instansi_unit' => ['nullable', 'string', 'max:255'],
            'no_hp' => ['nullable', 'string', 'max:20'],
            'aktif' => ['boolean'],
        ];
    }

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function create(): void
    {
        $this->resetForm();
        $this->showForm = true;
    }

    public function edit(Member $member): void
    {
        $this->editingId = $member->id;
        $this->nama = $member->nama;
        $this->nip = $member->nip ?? '';
        $this->instansi_unit = $member->instansi_unit ?? '';
        $this->no_hp = $member->no_hp ?? '';
        $this->aktif = $member->aktif;
        $this->showForm = true;
    }

    public function save(): void
    {
        $this->nip = trim($this->nip);
        $data = $this->validate();
        foreach (['nip', 'instansi_unit', 'no_hp'] as $field) {
            $data[$field] = blank($data[$field]) ? null : trim($data[$field]);
        }

        $before = null;
        if ($this->editingId) {
            $member = Member::findOrFail($this->editingId);
            $before = $member->toArray();
            $member->update($data);
            $action = 'ubah';
        } else {
            $member = Member::create($data);
            $action = 'buat';
        }

        app(AuditLogger::class)->model($action, $member, $before, $member->fresh()->toArray());
        Cache::forget('members:options');
        session()->flash('success', $this->editingId ? 'Data anggota berhasil diperbarui.' : 'Anggota berhasil ditambahkan.');
        $this->resetForm();
    }

    public function delete(int $id): void
    {
        $member = Member::withCount('loans')->findOrFail($id);
        $before = $member->toArray();
        if ($member->loans_count > 0) {
            $member->update(['aktif' => false]);
            app(AuditLogger::class)->model('nonaktifkan', $member, $before, $member->fresh()->toArray());
            $message = 'Anggota dinonaktifkan karena memiliki histori peminjaman.';
        } else {
            app(AuditLogger::class)->model('hapus', $member, $before);
            $member->delete();
            $message = 'Anggota berhasil dihapus.';
        }

        Cache::forget('members:options');
        session()->flash('success', $message);
    }

    public function resetForm(): void
    {
        $this->reset(['showForm', 'editingId', 'nama', 'nip', 'instansi_unit', 'no_hp']);
        $this->aktif = true;
        $this->resetValidation();
    }

    public function exportPdf(): StreamedResponse
    {
        abort_unless(auth()->user()?->isAdmin(), 403);

        $members = $this->filteredMembersQuery()->get();
        $pdf = Pdf::loadView('reports.members-pdf', ['members' => $members])
            ->setPaper('a4', 'landscape');

        return response()->streamDownload(function () use ($pdf): void {
            echo $pdf->output();
        }, 'daftar-anggota-'.now()->format('Y-m-d_H-i-s').'.pdf', ['Content-Type' => 'application/pdf']);
    }

    public function exportCsv(): StreamedResponse
    {
        abort_unless(auth()->user()?->isAdmin(), 403);

        return response()->streamDownload(function (): void {
            $handle = fopen('php://output', 'w');
            fwrite($handle, "\xEF\xBB\xBF");
            fputcsv($handle, ['Nama', 'NIP', 'Instansi / Unit', 'No. HP', 'Status', 'Jumlah Peminjaman', 'Terdaftar']);

            foreach ($this->filteredMembersQuery()->lazy(500) as $member) {
                fputcsv($handle, array_map([Csv::class, 'safeCell'], [
                    $member->nama,
                    $member->nip ?? '',
                    $member->instansi_unit ?? '',
                    $member->no_hp ?? '',
                    $member->aktif ? 'Aktif' : 'Nonaktif',
                    $member->loans_count,
                    $member->created_at?->format('Y-m-d H:i:s') ?? '',
                ]));
            }

            fclose($handle);
        }, 'daftar-anggota-'.now()->format('Y-m-d_H-i-s').'.csv', ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    public function exportXlsx(): BinaryFileResponse
    {
        abort_unless(auth()->user()?->isAdmin(), 403);

        return app(XlsxReportWriter::class)->download(
            'daftar-anggota-'.now()->format('Y-m-d_H-i-s').'.xlsx',
            ['Nama', 'NIP', 'Instansi / Unit', 'No. HP', 'Status', 'Jumlah Peminjaman', 'Terdaftar'],
            function (callable $appendRow): void {
                foreach ($this->filteredMembersQuery()->lazy(500) as $member) {
                    $appendRow([
                        $member->nama,
                        $member->nip ?? '',
                        $member->instansi_unit ?? '',
                        $member->no_hp ?? '',
                        $member->aktif ? 'Aktif' : 'Nonaktif',
                        $member->loans_count,
                        $member->created_at?->format('Y-m-d H:i:s') ?? '',
                    ]);
                }
            },
            title: 'Daftar Anggota Perpustakaan',
            meta: [
                'Dicetak pada' => now()->format('Y-m-d H:i:s'),
                'Dicetak oleh' => auth()->user()?->name ?? 'Sistem',
            ],
        );
    }

    public function render()
    {
        return view('livewire.admin.member-manager', [
            'members' => $this->filteredMembersQuery()->paginate(15),
        ])->layout('layouts.admin', ['title' => 'Anggota']);
    }

    private function filteredMembersQuery(): Builder
    {
        return Member::query()
            ->search($this->search)
            ->withCount('loans')
            ->latest();
    }
}
