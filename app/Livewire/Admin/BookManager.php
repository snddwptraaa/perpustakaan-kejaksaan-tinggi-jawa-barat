<?php

namespace App\Livewire\Admin;

use App\Models\Book;
use App\Models\Category;
use App\Services\AuditLogger;
use App\Services\XlsxReportWriter;
use App\Support\Csv;
use App\Support\Isbn;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;
use Livewire\WithFileUploads;
use Livewire\WithPagination;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Throwable;

class BookManager extends Component
{
    use WithFileUploads;
    use WithPagination;

    public bool $showForm = false;

    public ?int $editingId = null;

    #[Url(except: '')]
    public string $search = '';

    #[Url(as: 'category', except: '')]
    public string $filterCategory = '';

    #[Url(as: 'status', except: '')]
    public string $filterStatus = '';

    #[Url(except: 'newest')]
    public string $sort = 'newest';

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
     *
     * @var TemporaryUploadedFile|null
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
            'judul' => ['required', 'string', 'max:500'],
            'penulis' => ['required', 'string', 'max:255'],
            'penerbit' => ['nullable', 'string', 'max:255'],
            'tahun_terbit' => ['nullable', 'integer', 'between:1000,2100'],
            'jumlah_halaman' => ['nullable', 'string', 'max:50'],
            'isbn' => ['nullable', 'string', 'max:50'],
            'no_klasifikasi' => ['nullable', 'string', 'max:50'],
            'lokasi_rak' => ['nullable', 'string', 'max:50'],
            'stok' => ['required', 'integer', 'min:0'],
            'stok_tersedia' => $this->editingId
                ? ['nullable', 'integer', 'min:0']
                : ['required', 'integer', 'min:0', 'lte:stok'],
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

        $slug = Str::slug($this->newCategoryName);

        if (Category::where('slug', $slug)->exists()) {
            throw ValidationException::withMessages([
                'newCategoryName' => 'Nama kategori menghasilkan slug yang sudah digunakan.',
            ]);
        }

        $category = Category::create([
            'nama_kategori' => $this->newCategoryName,
            'slug' => $slug,
        ]);

        Cache::forget('categories:options');

        $this->category_id = (string) $category->id;
        $this->showQuickCategoryModal = false;
        $this->newCategoryName = '';
        session()->flash('success', "Kategori '{$category->nama_kategori}' berhasil ditambahkan dan dipilih.");
    }

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function updatedFilterCategory(): void
    {
        $this->resetPage();
    }

    public function updatedFilterStatus(): void
    {
        $this->resetPage();
    }

    public function updatedSort(): void
    {
        $this->resetPage();
    }

    public function resetFilters(): void
    {
        $this->reset(['search', 'filterCategory', 'filterStatus']);
        $this->sort = 'newest';
        $this->resetPage();
    }

    public function updatedStok($value): void
    {
        if (! $this->editingId) {
            // Mode buat: stok tersedia mengikuti total karena belum ada peminjaman.
            $this->stok_tersedia = (int) $value;

            return;
        }

        // Mode edit: stok_tersedia dihitung ulang di save() sebagai
        // total stok - jumlah peminjaman aktif. Tampilkan preview agar petugas
        // langsung melihat angka yang akan tersimpan.
        $book = Book::find($this->editingId);
        if (! $book) {
            return;
        }

        $activeLoans = $book->loans()->active()->count();
        $this->stok_tersedia = max(0, (int) $value - $activeLoans);
    }

    /**
     * Jumlah peminjaman aktif saat ini — untuk pesan bantuan di form.
     */
    public function getActiveLoansCountProperty(): int
    {
        if (! $this->editingId) {
            return 0;
        }

        $book = Book::find($this->editingId);

        return $book ? $book->loans()->active()->count() : 0;
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
        $this->tahun_terbit = $book->tahun_terbit ? (string) $book->tahun_terbit : '';
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
        if (! $this->editingId || ! $this->existingCover) {
            return;
        }

        $coverToDelete = DB::transaction(function (): ?string {
            $book = Book::query()->whereKey($this->editingId)->lockForUpdate()->firstOrFail();
            if (! $book->cover_image) {
                return null;
            }

            $before = $book->toArray();
            $cover = $book->cover_image;
            $book->update(['cover_image' => null]);
            app(AuditLogger::class)->model('ubah', $book, $before, $book->fresh()->toArray(), [
                'perubahan' => 'hapus_sampul',
            ]);

            return $cover;
        });

        if ($coverToDelete) {
            Storage::disk('public')->delete($coverToDelete);
        }

        $this->existingCover = null;
        session()->flash('success', 'Sampul buku berhasil dihapus.');
    }

    public function save(): void
    {
        // Normalisasi ISBN sebelum validasi: terima input dengan dash/spasi/titik,
        // tolak checksum yang salah dengan pesan ramah (bukan 500).
        if ($this->isbn !== '') {
            try {
                $this->isbn = Isbn::normalize($this->isbn);
            } catch (\InvalidArgumentException $exception) {
                throw ValidationException::withMessages(['isbn' => $exception->getMessage()]);
            }
        }

        $data = $this->validate();
        $data['tahun_terbit'] = $data['tahun_terbit'] === '' ? null : $data['tahun_terbit'];
        unset($data['cover']); // Remove uploaded file object from mass assignment array

        $newCover = $this->cover?->store('covers', 'public');

        if ($newCover) {
            $data['cover_image'] = $newCover;
        }

        try {
            DB::transaction(function () use ($data): void {
                if ($this->editingId) {
                    $book = Book::whereKey($this->editingId)->lockForUpdate()->firstOrFail();
                    $activeLoans = $book->loans()->active()->count();

                    if ($data['stok'] < $activeLoans) {
                        throw ValidationException::withMessages([
                            'stok' => "Total stok minimal {$activeLoans}, sesuai jumlah peminjaman aktif.",
                        ]);
                    }

                    $data['stok_tersedia'] = $data['stok'] - $activeLoans;
                    $before = $book->toArray();
                    $book->update($data);
                    app(AuditLogger::class)->model('ubah', $book, $before, $book->fresh()->toArray());

                    return;
                }

                $data['stok_tersedia'] = $data['stok'];
                $book = Book::create($data);
                app(AuditLogger::class)->model('buat', $book, null, $book->toArray());
            });
        } catch (Throwable $exception) {
            if ($newCover) {
                Storage::disk('public')->delete($newCover);
            }

            throw $exception;
        }

        if ($newCover && $this->editingId && $this->existingCover) {
            Storage::disk('public')->delete($this->existingCover);
        }

        session()->flash('success', $this->editingId
            ? 'Data buku berhasil diperbarui.'
            : 'Buku baru berhasil ditambahkan ke katalog.');

        $this->resetForm();
        Cache::forget('books:options');
    }

    public function toggleArchive(int $id): void
    {
        $archived = DB::transaction(function () use ($id): ?bool {
            $book = Book::query()->whereKey($id)->lockForUpdate()->firstOrFail();
            if (! $book->archived_at && $book->loans()->active()->exists()) {
                return null;
            }

            $before = $book->toArray();
            $book->update(['archived_at' => $book->archived_at ? null : now()]);
            app(AuditLogger::class)->model($book->archived_at ? 'arsipkan' : 'pulihkan', $book, $before, $book->fresh()->toArray());

            return (bool) $book->archived_at;
        });

        if ($archived === null) {
            session()->flash('error', 'Buku dengan peminjaman aktif tidak dapat diarsipkan.');

            return;
        }

        Cache::forget('books:options');
        session()->flash('success', $archived ? 'Buku diarsipkan dari katalog publik.' : 'Buku dikembalikan ke katalog publik.');
    }

    public function delete(int $id): void
    {
        $book = Book::withCount('loans')->findOrFail($id);

        if ($book->loans_count > 0) {
            session()->flash('error', 'Buku tidak dapat dihapus karena memiliki riwayat peminjaman. Arsipkan atau kosongkan stok jika buku tidak lagi dilayankan.');

            return;
        }

        $coverToDelete = $book->cover_image;
        app(AuditLogger::class)->model('hapus', $book, $book->toArray());
        $book->delete();

        if ($coverToDelete) {
            Storage::disk('public')->delete($coverToDelete);
        }

        session()->flash('success', 'Buku berhasil dihapus dari katalog.');
        Cache::forget('books:options');
    }

    public function exportPdf(): StreamedResponse
    {
        abort_unless(auth()->user()?->isAdmin(), 403);

        $filename = 'katalog-buku-'.now()->format('Y-m-d_H-i-s').'.pdf';

        $books = $this->filteredBooksQuery()->get();

        $pdf = Pdf::loadView('reports.catalog-pdf', [
            'books' => $books,
        ])
            ->setPaper('a4', 'portrait');

        return response()->streamDownload(function () use ($pdf): void {
            echo $pdf->output();
        }, $filename, ['Content-Type' => 'application/pdf']);
    }

    public function exportCsv(): StreamedResponse
    {
        abort_unless(auth()->user()?->isAdmin(), 403);

        $filename = 'koleksi-buku-'.now()->format('Y-m-d_H-i-s').'.csv';

        return response()->streamDownload(function (): void {
            $handle = fopen('php://output', 'w');
            fwrite($handle, "\xEF\xBB\xBF");
            fputcsv($handle, ['Judul', 'Penulis', 'Penerbit', 'Tahun Terbit', 'ISBN', 'Kategori', 'No. Klasifikasi', 'Lokasi Rak', 'Total Stok', 'Stok Tersedia', 'Status']);

            foreach ($this->filteredBooksQuery()->lazy(500) as $book) {
                fputcsv($handle, array_map([Csv::class, 'safeCell'], [
                    $book->judul,
                    $book->penulis,
                    $book->penerbit ?? '',
                    $book->tahun_terbit ?? '',
                    $book->isbn ?? '',
                    $book->category?->nama_kategori ?? '',
                    $book->no_klasifikasi ?? '',
                    $book->lokasi_rak ?? '',
                    $book->stok,
                    $book->stok_tersedia,
                    $book->archived_at ? 'Diarsipkan' : 'Aktif',
                ]));
            }

            fclose($handle);
        }, $filename, ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    public function exportXlsx(): BinaryFileResponse
    {
        abort_unless(auth()->user()?->isAdmin(), 403);

        return app(XlsxReportWriter::class)->download(
            'koleksi-buku-'.now()->format('Y-m-d_H-i-s').'.xlsx',
            ['Judul', 'Penulis', 'Penerbit', 'Tahun Terbit', 'ISBN', 'Kategori', 'No. Klasifikasi', 'Lokasi Rak', 'Total Stok', 'Stok Tersedia', 'Status'],
            function (callable $appendRow): void {
                foreach ($this->filteredBooksQuery()->lazy(500) as $book) {
                    $appendRow([
                        $book->judul,
                        $book->penulis,
                        $book->penerbit ?? '',
                        $book->tahun_terbit ?? '',
                        $book->isbn ?? '',
                        $book->category?->nama_kategori ?? '',
                        $book->no_klasifikasi ?? '',
                        $book->lokasi_rak ?? '',
                        $book->stok,
                        $book->stok_tersedia,
                        $book->archived_at ? 'Diarsipkan' : 'Aktif',
                    ]);
                }
            },
            title: 'Koleksi Buku Perpustakaan',
            meta: [
                'Dicetak pada' => now()->format('Y-m-d H:i:s'),
                'Dicetak oleh' => auth()->user()?->name ?? 'Sistem',
            ],
        );
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
            'books' => $this->filteredBooksQuery()->paginate(15),
            'categories' => Cache::remember('categories:options', now()->addMinutes(5), fn () => Category::orderBy('nama_kategori')->get()),
        ])->layout('layouts.admin', ['title' => 'Koleksi Buku']);
    }

    private function filteredBooksQuery(): Builder
    {
        $query = Book::query()
            ->with('category')
            ->when($this->search !== '', function (Builder $query): void {
                $query->where(function (Builder $query): void {
                    $query->where('judul', 'like', "%{$this->search}%")
                        ->orWhere('penulis', 'like', "%{$this->search}%")
                        ->orWhere('isbn', 'like', "%{$this->search}%")
                        ->orWhere('no_klasifikasi', 'like', "%{$this->search}%")
                        ->orWhere('lokasi_rak', 'like', "%{$this->search}%");
                });
            })
            ->when($this->filterCategory !== '', fn (Builder $query) => $query->where('category_id', $this->filterCategory));

        match ($this->filterStatus) {
            'active' => $query->whereNull('archived_at'),
            'archived' => $query->whereNotNull('archived_at'),
            'available' => $query->whereNull('archived_at')->where('stok_tersedia', '>', 0),
            'unavailable' => $query->whereNull('archived_at')->where('stok_tersedia', '<=', 0),
            default => null,
        };

        match ($this->sort) {
            'title_asc' => $query->orderBy('judul')->orderBy('id'),
            'category_asc' => $query
                ->orderBy(Category::query()->select('nama_kategori')->whereColumn('categories.id', 'books.category_id'))
                ->orderBy('judul'),
            'stock_asc' => $query->orderBy('stok_tersedia')->orderBy('judul'),
            default => $query->latest()->orderByDesc('id'),
        };

        return $query;
    }
}
