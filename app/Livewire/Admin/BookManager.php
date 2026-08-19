<?php

namespace App\Livewire\Admin;

use App\Models\Book;
use App\Models\Category;
use Illuminate\Validation\Rule;
use Livewire\Component;
use Livewire\WithPagination;

class BookManager extends Component
{
    use WithPagination;

    public bool $showForm = false;
    public ?int $editingId = null;
    public string $search = '';
    public string $category_id = '';
    public string $judul = '';
    public string $penulis = '';
    public string $penerbit = '';
    public string $tahun_terbit = '';
    public string $isbn = '';
    public string $no_klasifikasi = '';
    public string $lokasi_rak = '';
    public int $stok = 0;
    public int $stok_tersedia = 0;
    public string $deskripsi = '';

    protected function rules(): array
    {
        return [
            'category_id' => ['required', 'exists:categories,id'],
            'judul' => ['required', 'string', 'max:255'],
            'penulis' => ['required', 'string', 'max:255'],
            'penerbit' => ['nullable', 'string', 'max:255'],
            'tahun_terbit' => ['nullable', 'integer', 'between:1000,2100'],
            'isbn' => ['nullable', 'regex:/^(?:\d{9}[\dXx]|\d{13}|[\d-]{10,17})$/'],
            'no_klasifikasi' => ['nullable', 'string', 'max:50'],
            'lokasi_rak' => ['nullable', 'string', 'max:50'],
            'stok' => ['required', 'integer', 'min:0'],
            'stok_tersedia' => ['required', 'integer', 'min:0', 'lte:stok'],
            'deskripsi' => ['nullable', 'string'],
        ];
    }

    public function updatedSearch(): void { $this->resetPage(); }

    public function create(): void
    {
        $this->resetForm();
        $this->showForm = true;
    }

    public function edit(Book $book): void
    {
        $this->editingId = $book->id;
        foreach (['category_id', 'judul', 'penulis', 'penerbit', 'tahun_terbit', 'isbn', 'no_klasifikasi', 'lokasi_rak', 'stok', 'stok_tersedia', 'deskripsi'] as $field) {
            $this->{$field} = (string) ($book->{$field} ?? '');
        }
        $this->showForm = true;
    }

    public function save(): void
    {
        $data = $this->validate();
        if ($this->editingId) {
            Book::findOrFail($this->editingId)->update($data);
            session()->flash('success', 'Data buku berhasil diperbarui.');
        } else {
            Book::create($data);
            session()->flash('success', 'Buku baru berhasil ditambahkan.');
        }
        $this->resetForm();
    }

    public function delete(int $id): void
    {
        $book = Book::withCount(['loans as active_loans_count' => fn ($query) => $query->whereNull('tanggal_kembali')])->findOrFail($id);
        if ($book->active_loans_count > 0) {
            session()->flash('error', 'Buku tidak dapat dihapus karena masih memiliki peminjaman aktif.');
            return;
        }
        $book->delete();
        session()->flash('success', 'Buku berhasil dihapus.');
    }

    public function resetForm(): void
    {
        $this->reset(['showForm', 'editingId', 'category_id', 'judul', 'penulis', 'penerbit', 'tahun_terbit', 'isbn', 'no_klasifikasi', 'lokasi_rak', 'deskripsi']);
        $this->stok = 0;
        $this->stok_tersedia = 0;
        $this->resetValidation();
    }

    public function render()
    {
        return view('livewire.admin.book-manager', [
            'books' => Book::with('category')->when($this->search, fn ($query) => $query->where(fn ($query) => $query->where('judul', 'like', "%{$this->search}%")->orWhere('penulis', 'like', "%{$this->search}%")))->latest()->paginate(10),
            'categories' => Category::orderBy('nama_kategori')->get(),
        ])->layout('layouts.admin', ['title' => 'Koleksi Buku']);
    }
}
