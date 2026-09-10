<?php

namespace App\Livewire\Admin;

use App\Livewire\Actions\DeleteUser;
use App\Models\User;
use App\Services\AuditLogger;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Livewire\Component;
use Livewire\WithPagination;

class UserManager extends Component
{
    use WithPagination;

    public bool $showForm = false;

    public ?int $editingId = null;

    public string $search = '';

    public string $name = '';

    public string $email = '';

    public string $password = '';

    public string $role = 'admin';

    public bool $aktif = true;

    protected function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => [
                'required',
                'email',
                Rule::unique('users', 'email')->ignore($this->editingId),
            ],
            'password' => [$this->editingId ? 'nullable' : 'required', 'string', 'min:8'],
            'role' => ['required', 'in:admin,superadmin'],
            'aktif' => ['boolean'],
        ];
    }

    public function create(): void
    {
        $this->resetForm();
        $this->showForm = true;
    }

    public function edit(User $user): void
    {
        $this->resetForm();
        $this->editingId = $user->id;
        $this->name = $user->name;
        $this->email = $user->email;
        $this->role = $user->role;
        $this->aktif = $user->aktif;
        $this->showForm = true;
    }

    public function resetForm(): void
    {
        $this->reset(['showForm', 'editingId', 'name', 'email', 'password']);
        $this->role = 'admin';
        $this->aktif = true;
        $this->resetValidation();
    }

    public function save(): void
    {
        $data = $this->validate();

        if ($this->editingId) {
            $user = User::findOrFail($this->editingId);

            // A superadmin must not accidentally revoke their own access.
            if ($user->is(auth()->user())) {
                $data['role'] = $user->role;
                $data['aktif'] = true;
            }

            if ($data['password'] !== '') {
                $data['password'] = Hash::make($data['password']);
            } else {
                unset($data['password']);
            }

            $before = $user->toArray();
            $user->update($data);
            app(AuditLogger::class)->model('ubah', $user, $before, $user->fresh()->toArray());
            session()->flash('success', 'Akun petugas berhasil diperbarui.');
        } else {
            $data['password'] = Hash::make($data['password']);
            $user = User::create($data);
            app(AuditLogger::class)->model('buat', $user, null, $user->toArray());
            session()->flash('success', 'Akun petugas berhasil ditambahkan.');
        }

        $this->resetForm();
    }

    public function delete(int $id, DeleteUser $deleteUser): void
    {
        $error = $deleteUser->handle($id, auth()->id());

        if ($error) {
            session()->flash('error', $error);

            return;
        }

        session()->flash('success', 'Akun petugas berhasil dihapus.');
    }

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function render()
    {
        $users = User::query()
            ->when($this->search, function ($query) {
                $query->where(function ($q) {
                    $q->where('name', 'like', "%{$this->search}%")
                        ->orWhere('email', 'like', "%{$this->search}%");
                });
            })
            ->latest()
            ->paginate(15);

        return view('livewire.admin.user-manager', [
            'users' => $users,
        ])->layout('layouts.admin', ['title' => 'Pengguna Admin']);
    }
}
