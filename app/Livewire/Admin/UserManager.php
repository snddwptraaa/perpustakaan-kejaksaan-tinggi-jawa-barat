<?php

namespace App\Livewire\Admin;

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Livewire\Component;
use Livewire\WithPagination;

class UserManager extends Component
{
    use WithPagination;

    public bool $showForm = false;
    public string $name = '';
    public string $email = '';
    public string $password = '';
    public string $role = 'admin';

    protected function rules(): array
    {
        return ['name' => ['required', 'string', 'max:255'], 'email' => ['required', 'email', 'unique:users,email'], 'password' => ['required', 'string', 'min:8'], 'role' => ['required', 'in:admin,superadmin']];
    }

    public function save(): void
    {
        $data = $this->validate();
        $data['password'] = Hash::make($data['password']);
        User::create($data);
        session()->flash('success', 'Akun petugas berhasil ditambahkan.');
        $this->reset(['showForm', 'name', 'email', 'password', 'role']);
    }

    public function delete(int $id): void
    {
        if ($id === auth()->id()) { session()->flash('error', 'Akun yang sedang digunakan tidak dapat dihapus.'); return; }
        User::findOrFail($id)->delete();
        session()->flash('success', 'Akun petugas berhasil dihapus.');
    }

    public function render()
    {
        return view('livewire.admin.user-manager', ['users' => User::latest()->paginate(10)])->layout('layouts.admin', ['title' => 'Pengguna Admin']);
    }
}
