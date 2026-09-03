<?php

namespace App\Livewire\Admin;

use App\Models\Member;
use App\Services\AuditLogger;
use Illuminate\Support\Facades\Cache;
use Livewire\Component;
use Livewire\WithPagination;

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
            'nip' => ['nullable', 'string', 'max:30'],
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

    public function render()
    {
        return view('livewire.admin.member-manager', [
            'members' => Member::search($this->search)->latest()->paginate(10),
        ])->layout('layouts.admin', ['title' => 'Anggota']);
    }
}
