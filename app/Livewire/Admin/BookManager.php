<?php

namespace App\Livewire\Admin;

use App\Models\Book;
use App\Models\Category;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Livewire\Component;
use Livewire\WithFileUploads;
use Livewire\WithPagination;

class BookManager extends Component
{
    use WithFileUploads;
    use WithPagination;

    public bool $showForm = false;
    public ?int $editingId = null;
    public string $search = '';
    public string $category_id = '';
    public string $judul = '';
    public string $penulis = '';
    public string $penerbit = '';
    public string $tahun_terbit = '';
    public string $jumlah_halaman = '';
    public string $isbn = '';
    public string $no_klasifikasi = '';
    public string $lokasi_rak = '';
    public int $stok = 1;
    public int $stok_tersedia = 1;
    public string $deskripsi = '';

    /**
     * File upload property for new book cover
     * @var \Livewire\Features\SupportFileUploads\TemporaryUploadedFile|null
     */
    public $cover = null;

    /**
     * Existing cover path when editing
     */
    public ?string $existingCover = null;

    protected function rules(): array
    {
        return [
            'category_id' => ['required', 'exists:categories,id'],
            'judul' => ['required', 'string', 'max:255'],
            'penulis' => ['required', 'string', 'max:255'],
            'penerbit' => ['nullable', 'string', 'max:255'],
            'tahun_terbit' => ['nullable', 'integer', 'between:1000,2100'],
            'jumlah_halaman' => ['nullable', 'string', 'max:50'],
            'isbn' => ['nullable', 'string', 'max:50'],
            'no_klasifikasi' => ['nullable', 'string', 'max:50'],
            'lokasi_rak' => ['nullable', 'string', 'max:50'],
            'stok' => ['required', 'integer', 'min:0'],
            'stok_tersedia' => ['required', 'integer', 'min:0', 'lte:stok'],
            'deskripsi' => ['nullable', 'string'],
            'cover' => ['nullable', 'image', 'max:2048', 'mimes:jpeg,png,jpg,webp'],
        ];
    }

    protected $messages = [
        'category_id.required' => 'Kategori buku wajib dipilih.',
        'category_id.exists' => 'Kategori yang dipilih tidak valid.',
        'judul.required' => 'Judul buku wajib diisi.',
        'penulis.required' => 'Penulis / pengarang buku wajib diisi.',
        'tahun_terbit.between' => 'Tahun terbit harus berupa tahun yang valid (antara 1000 - 2100).',
        'stok.required' => 'Jumlah total eksemplar/stok wajib diisi.',
        'stok.min' => 'Jumlah stok minimal 0.',
        'stok_tersedia.required' => 'Stok tersedia wajib diisi.',
        'stok_tersedia.lte' => 'Stok tersedia tidak boleh melebihi total stok.',
        'cover.image' => 'File sampul buku harus berupa gambar.',
        'cover.mimes' => 'Format sampul buku yang didukung adalah JPEG, PNG, JPG, atau WEBP.',
        'cover.max' => 'Ukuran file sampul buku maksimal 2MB.',
    ];

    public bool $showQuickCategoryModal = false;
    public string $newCategoryName = '';

    public function openQuickCategoryModal(): void
    {
        $this->newCategoryName = '';
        $this->resetValidation('newCategoryName');
        $this->showQuickCategoryModal = true;
    }

    public function closeQuickCategoryModal(): void
    {
        $this->showQuickCategoryModal = false;
        $this->newCategoryName = '';
        $this->resetValidation('newCategoryName');
    }

    public function saveQuickCategory(): void
    {
        $this->validate([
            'newCategoryName' => ['required', 'string', 'max:100', 'unique:categories,nama_kategori'],
        ], [
            'newCategoryName.required' => 'Nama kategori wajib diisi.',
            'newCategoryName.unique' => 'Nama kategori ini sudah ada.',
        ]);

        $category = Category::create([
            'nama_kategori' => $this->newCategoryName,
            'slug' => \Illuminate\Support\Str::slug($this->newCategoryName),
        ]);

        $this->category_id = (string) $category->id;
        $this->showQuickCategoryModal = false;
        $this->newCategoryName = '';
        session()->flash('success', "Kategori '{$category->nama_kategori}' berhasil ditambahkan dan dipilih.");
    }

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function updatedStok($value): void
    {
        // Auto sync stok_tersedia for new book creation
        if (! $this->editingId) {
            $this->stok_tersedia = (int) $value;
        }
    }

    public function create(): void
    {
        $this->resetForm();
        $this->showForm = true;
    }

    public function edit(Book $book): void
    {
        $this->resetForm();
        $this->editingId = $book->id;
        $this->category_id = (string) ($book->category_id ?? '');
        $this->judul = (string) ($book->judul ?? '');
        $this->penulis = (string) ($book->penulis ?? '');
        $this->penerbit = (string) ($book->penerbit ?? '');
        $this->tahun_terbit = (string) ($book->tahun_terbit ?? '');
        $this->jumlah_halaman = (string) ($book->jumlah_halaman ?? '');
        $this->isbn = (string) ($book->isbn ?? '');
        $this->no_klasifikasi = (string) ($book->no_klasifikasi ?? '');
        $this->lokasi_rak = (string) ($book->lokasi_rak ?? '');
        $this->stok = (int) ($book->stok ?? 0);
        $this->stok_tersedia = (int) ($book->stok_tersedia ?? 0);
        $this->deskripsi = (string) ($book->deskripsi ?? '');
        $this->existingCover = $book->cover_image;
        $this->cover = null;
        $this->showForm = true;
    }

    public function removeCoverPreview(): void
    {
        $this->cover = null;
    }

    public function removeExistingCover(): void
    {
        if ($this->editingId && $this->existingCover) {
            Storage::disk('public')->delete($this->existingCover);
            Book::where('id', $this->editingId)->update(['cover_image' => null]);
            $this->existingCover = null;
            session()->flash('success', 'Sampul buku berhasil dihapus.');
        }
    }

    public function save(): void
    {
        $data = $this->validate();
        unset($data['cover']); // Remove uploaded file object from mass assignment array

        // Handle cover image upload
        if ($this->cover) {
            $path = $this->cover->store('covers', 'public');
            $data['cover_image'] = $path;

            // Delete previous cover if exists
            if ($this->editingId && $this->existingCover) {
                Storage::disk('public')->delete($this->existingCover);
            }
        }

        if ($this->editingId) {
            Book::findOrFail($this->editingId)->update($data);
            session()->flash('success', 'Data buku berhasil diperbarui.');
        } else {
            Book::create($data);
            session()->flash('success', 'Buku baru berhasil ditambahkan ke katalog.');
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

        if ($book->cover_image) {
            Storage::disk('public')->delete($book->cover_image);
        }

        $book->delete();
        session()->flash('success', 'Buku berhasil dihapus dari katalog.');
    }

    public function resetForm(): void
    {
        $this->reset([
            'showForm',
            'editingId',
            'category_id',
            'judul',
            'penulis',
            'penerbit',
            'tahun_terbit',
            'jumlah_halaman',
            'isbn',
            'no_klasifikasi',
            'lokasi_rak',
            'deskripsi',
            'cover',
            'existingCover',
        ]);
        $this->stok = 1;
        $this->stok_tersedia = 1;
        $this->resetValidation();
    }

    public function render()
    {
        return view('livewire.admin.book-manager', [
            'books' => Book::with('category')
                ->when($this->search, function ($query) {
                    $query->where(function ($q) {
                        $q->where('judul', 'like', "%{$this->search}%")
                            ->orWhere('penulis', 'like', "%{$this->search}%")
                            ->orWhere('isbn', 'like', "%{$this->search}%")
                            ->orWhere('no_klasifikasi', 'like', "%{$this->search}%")
                            ->orWhere('lokasi_rak', 'like', "%{$this->search}%");
                    });
                })
                ->latest()
                ->paginate(10),
            'categories' => Category::orderBy('nama_kategori')->get(),
        ])->layout('layouts.admin', ['title' => 'Koleksi Buku']);
    }
}

