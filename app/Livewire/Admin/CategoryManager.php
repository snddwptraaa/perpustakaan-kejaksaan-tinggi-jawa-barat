<?php

namespace App\Livewire\Admin;

use App\Models\Category;
use App\Services\AuditLogger;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

class CategoryManager extends Component
{
    use WithPagination;

    public bool $showForm = false;

    public ?int $editingId = null;

    public string $nama_kategori = '';

    #[Url(except: '')]
    public string $search = '';

    #[Url(except: 'name_asc')]
    public string $sort = 'name_asc';

    protected function rules(): array
    {
        return [
            'nama_kategori' => [
                'required',
                'string',
                'max:100',
                Rule::unique('categories', 'nama_kategori')->ignore($this->editingId),
            ],
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

    public function updatedSort(): void
    {
        $this->resetPage();
    }

    public function resetFilters(): void
    {
        $this->search = '';
        $this->sort = 'name_asc';
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
            $category = Category::findOrFail($this->editingId);
            $before = $category->toArray();
            $category->update($data);
            app(AuditLogger::class)->model('ubah', $category, $before, $category->fresh()->toArray());
            session()->flash('success', 'Kategori buku berhasil diperbarui.');
        } else {
            $category = Category::create($data);
            app(AuditLogger::class)->model('buat', $category, null, $category->toArray());
            session()->flash('success', 'Kategori buku baru berhasil ditambahkan.');
        }

        Cache::forget('categories:options');
        $this->resetForm();
    }

    public function delete(int $id): void
    {
        $category = Category::withCount('books')->findOrFail($id);

        if ($category->books_count > 0) {
            session()->flash('error', "Kategori '{$category->nama_kategori}' tidak dapat dihapus karena masih digunakan oleh {$category->books_count} buku.");

            return;
        }

        $before = $category->toArray();
        $category->delete();
        app(AuditLogger::class)->model('hapus', $category, $before, null);
        Cache::forget('categories:options');
        session()->flash('success', 'Kategori berhasil dihapus.');
    }

    public function resetForm(): void
    {
        $this->reset(['showForm', 'editingId', 'nama_kategori']);
        $this->resetValidation();
    }

    public function render()
    {
        $categories = Category::query()
            ->withCount('books')
            ->when($this->search !== '', fn ($query) => $query->where('nama_kategori', 'like', "%{$this->search}%"));

        match ($this->sort) {
            'name_desc' => $categories->orderByDesc('nama_kategori'),
            'books_desc' => $categories->orderByDesc('books_count')->orderBy('nama_kategori'),
            default => $categories->orderBy('nama_kategori'),
        };

        return view('livewire.admin.category-manager', [
            'categories' => $categories->paginate(15),
        ])->layout('layouts.admin', ['title' => 'Kategori Buku']);
    }
}
