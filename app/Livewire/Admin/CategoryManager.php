<?php

namespace App\Livewire\Admin;

use App\Models\Category;
use Illuminate\Support\Str;
use Livewire\Component;
use Livewire\WithPagination;

class CategoryManager extends Component
{
    use WithPagination;

    public bool $showForm = false;
    public ?int $editingId = null;
    public string $nama_kategori = '';

    protected function rules(): array
    {
        return ['nama_kategori' => ['required', 'string', 'max:100', 'unique:categories,nama_kategori,' . $this->editingId]];
    }

    public function create(): void { $this->resetForm(); $this->showForm = true; }

    public function edit(Category $category): void
    {
        $this->editingId = $category->id;
        $this->nama_kategori = $category->nama_kategori;
        $this->showForm = true;
    }

    public function save(): void
    {
        $data = $this->validate();
        $data['slug'] = Str::slug($data['nama_kategori']);
        $this->editingId ? Category::findOrFail($this->editingId)->update($data) : Category::create($data);
        session()->flash('success', $this->editingId ? 'Kategori berhasil diperbarui.' : 'Kategori berhasil ditambahkan.');
        $this->resetForm();
    }

    public function delete(int $id): void
    {
        $category = Category::withCount('books')->findOrFail($id);
        if ($category->books_count) { session()->flash('error', 'Kategori tidak dapat dihapus karena masih digunakan oleh buku.'); return; }
        $category->delete();
        session()->flash('success', 'Kategori berhasil dihapus.');
    }

    public function resetForm(): void { $this->reset(['showForm', 'editingId', 'nama_kategori']); $this->resetValidation(); }

    public function render()
    {
        return view('livewire.admin.category-manager', ['categories' => Category::withCount('books')->orderBy('nama_kategori')->paginate(10)])->layout('layouts.admin', ['title' => 'Kategori']);
    }
}
