<?php

namespace App\Livewire\Admin;

use App\Livewire\Actions\DeleteUser;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Livewire\Component;
use Livewire\WithPagination;

class UserManager extends Component
{
    use WithPagination;

    public bool $showForm = false;

    public string $search = '';

    public string $name = '';

    public string $email = '';

    public string $password = '';

    public string $role = 'admin';

    protected function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8'],
            'role' => ['required', 'in:admin,superadmin'],
        ];
    }

    public function create(): void
    {
        $this->resetForm();
        $this->showForm = true;
    }

    public function resetForm(): void
    {
        $this->reset(['showForm', 'name', 'email', 'password']);
        $this->role = 'admin';
        $this->resetValidation();
    }

    public function save(): void
    {
        $data = $this->validate();
        $data['password'] = Hash::make($data['password']);
        User::create($data);
        session()->flash('success', 'Akun petugas berhasil ditambahkan.');
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
            ->paginate(10);

        return view('livewire.admin.user-manager', [
            'users' => $users,
        ])->layout('layouts.admin', ['title' => 'Pengguna Admin']);
    }
}
