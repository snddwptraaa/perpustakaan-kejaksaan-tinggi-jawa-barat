<?php

namespace App\Livewire\Guest;

use App\Models\Book;
use App\Models\Category;
use Livewire\Component;
use Livewire\WithPagination;

class CatalogBrowser extends Component
{
    use WithPagination;

    public string $search = '';

    public string $category = '';

    public string $availability = '';

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function updatedCategory(): void
    {
        $this->resetPage();
    }

    public function updatedAvailability(): void
    {
        $this->resetPage();
    }

    public function render()
    {
        $books = Book::active()
            ->with('category')
            ->when($this->search, fn ($query) => $query->where(fn ($query) => $query->where('judul', 'like', "%{$this->search}%")->orWhere('penulis', 'like', "%{$this->search}%")))
            ->when($this->category, fn ($query) => $query->where('category_id', $this->category))
            ->when($this->availability === 'available', fn ($query) => $query->where('stok_tersedia', '>', 0))
            ->when($this->availability === 'unavailable', fn ($query) => $query->where('stok_tersedia', 0))
            ->orderBy('judul')
            ->paginate(12);

        return view('livewire.guest.catalog-browser', [
            'books' => $books,
            'categories' => Category::query()->orderBy('nama_kategori')->get(),
        ])->layout('layouts.guest', ['title' => 'Katalog Buku']);
    }
}
