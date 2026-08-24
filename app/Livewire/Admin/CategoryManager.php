<?php

namespace App\Livewire\Admin;

use App\Models\Category;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Livewire\Component;
use Livewire\WithPagination;

class CategoryManager extends Component
{
    use WithPagination;

    public bool $showForm = false;

    public ?int $editingId = null;

    public string $nama_kategori = '';

    public string $search = '';

    protected function rules(): array
    {
        return [
            'nama_kategori' => ['required', 'string', 'max:100', 'unique:categories,nama_kategori,'.$this->editingId],
        ];
    }

    protected $messages = [
        'nama_kategori.required' => 'Nama bidang/kategori wajib diisi.',
        'nama_kategori.unique' => 'Nama kategori ini sudah ada.',
        'nama_kategori.max' => 'Nama kategori maksimal 100 karakter.',
    ];

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function create(): void
    {
        $this->resetForm();
        $this->showForm = true;
    }

    public function edit(Category $category): void
    {
        $this->resetForm();
        $this->editingId = $category->id;
        $this->nama_kategori = $category->nama_kategori;
        $this->showForm = true;
    }

    public function save(): void
    {
        $data = $this->validate();
        $data['slug'] = Str::slug($data['nama_kategori']);

        $slugExists = Category::query()
            ->where('slug', $data['slug'])
            ->when($this->editingId, fn ($query) => $query->whereKeyNot($this->editingId))
            ->exists();

        if ($slugExists) {
            throw ValidationException::withMessages([
                'nama_kategori' => 'Nama kategori menghasilkan slug yang sudah digunakan.',
            ]);
        }

        if ($this->editingId) {
            Category::findOrFail($this->editingId)->update($data);
            session()->flash('success', 'Kategori buku berhasil diperbarui.');
        } else {
            Category::create($data);
            session()->flash('success', 'Kategori buku baru berhasil ditambahkan.');
        }

        $this->resetForm();
    }

    public function delete(int $id): void
    {
        $category = Category::withCount('books')->findOrFail($id);

        if ($category->books_count > 0) {
            session()->flash('error', "Kategori '{$category->nama_kategori}' tidak dapat dihapus karena masih digunakan oleh {$category->books_count} buku.");

            return;
        }

        $category->delete();
        session()->flash('success', 'Kategori berhasil dihapus.');
    }

    public function resetForm(): void
    {
        $this->reset(['showForm', 'editingId', 'nama_kategori']);
        $this->resetValidation();
    }

    public function render()
    {
        return view('livewire.admin.category-manager', [
            'categories' => Category::withCount('books')
                ->when($this->search, fn ($q) => $q->where('nama_kategori', 'like', "%{$this->search}%"))
                ->orderBy('nama_kategori')
                ->paginate(10),
        ])->layout('layouts.admin', ['title' => 'Kategori Buku']);
    }
}
