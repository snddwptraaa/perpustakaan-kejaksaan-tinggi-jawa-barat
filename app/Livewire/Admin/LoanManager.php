<?php

namespace App\Livewire\Admin;

use App\Models\Book;
use App\Models\Loan;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Livewire\Component;
use Livewire\WithPagination;

class LoanManager extends Component
{
    use WithPagination;

    public bool $showForm = false;
    public string $search = '';
    public string $book_id = '';
    public string $bookSearch = '';
    public string $nama_peminjam = '';
    public string $nip_peminjam = '';
    public string $instansi_unit = '';
    public string $tanggal_pinjam = '';
    public string $tanggal_jatuh_tempo = '';
    public string $catatan = '';
    public string $statusFilter = '';

    protected function rules(): array
    {
        return [
            'book_id' => ['required', 'exists:books,id'],
            'nama_peminjam' => ['required', 'string', 'max:255'],
            'nip_peminjam' => ['nullable', 'string', 'max:30'],
            'instansi_unit' => ['nullable', 'string', 'max:255'],
            'tanggal_pinjam' => ['required', 'date'],
            'tanggal_jatuh_tempo' => ['required', 'date', 'after_or_equal:tanggal_pinjam'],
            'catatan' => ['nullable', 'string'],
        ];
    }

    public function create(): void
    {
        $this->resetForm();
        $this->tanggal_pinjam = now()->toDateString();
        $this->tanggal_jatuh_tempo = now()->addDays(7)->toDateString();
        $this->showForm = true;
    }

    public function selectBook(int $id): void
    {
        $book = Book::find($id);
        if ($book && $book->stok_tersedia > 0) {
            $this->book_id = (string) $id;
            $this->bookSearch = '';
            $this->resetValidation('book_id');
        } else {
            $this->addError('book_id', 'Buku ini sedang tidak tersedia untuk dipinjam.');
        }
    }

    public function deselectBook(): void
    {
        $this->book_id = '';
        $this->bookSearch = '';
    }

    public function updatedTanggalPinjam(): void
    {
        if ($this->tanggal_pinjam) $this->tanggal_jatuh_tempo = \Carbon\Carbon::parse($this->tanggal_pinjam)->addDays(7)->toDateString();
    }

    public function save(): void
    {
        $data = $this->validate();
        DB::transaction(function () use ($data): void {
            $book = Book::whereKey($data['book_id'])->lockForUpdate()->firstOrFail();
            if ($book->stok_tersedia < 1) $this->addError('book_id', 'Buku sedang tidak tersedia untuk dipinjam.');
            if ($this->getErrorBag()->has('book_id')) throw new \RuntimeException('Buku tidak tersedia.');
            Loan::create([...$data, 'petugas_id' => auth()->id(), 'status' => 'dipinjam']);
            $book->decrement('stok_tersedia');
        });
        session()->flash('success', 'Peminjaman berhasil dicatat.');
        $this->resetForm();
    }

    public function returnBook(int $id): void
    {
        DB::transaction(function () use ($id): void {
            $loan = Loan::whereKey($id)->lockForUpdate()->firstOrFail();
            if ($loan->tanggal_kembali) return;
            $loan->update(['status' => 'dikembalikan', 'tanggal_kembali' => today()]);
            $loan->book()->lockForUpdate()->first()->increment('stok_tersedia');
        });
        session()->flash('success', 'Pengembalian buku berhasil dicatat.');
    }

    public function resetForm(): void
    {
        $this->reset([
            'showForm',
            'book_id',
            'bookSearch',
            'nama_peminjam',
            'nip_peminjam',
            'instansi_unit',
            'tanggal_pinjam',
            'tanggal_jatuh_tempo',
            'catatan'
        ]);
        $this->resetValidation();
    }

    public function exportCsv(): StreamedResponse
    {
        $filename = 'laporan-peminjaman-'.now()->format('Y-m-d_H-i-s').'.csv';

        return response()->streamDownload(function (): void {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, ['Nama Peminjam', 'NIP', 'Instansi / Unit', 'Judul Buku', 'Tanggal Pinjam', 'Jatuh Tempo', 'Tanggal Kembali', 'Status', 'Petugas', 'Catatan']);

            $this->filteredLoansQuery()
                ->with(['book', 'petugas'])
                ->latest()
                ->chunkById(500, function ($loans) use ($handle): void {
                    foreach ($loans as $loan) {
                        fputcsv($handle, [
                            $loan->nama_peminjam,
                            $loan->nip_peminjam ?? '',
                            $loan->instansi_unit ?? '',
                            $loan->book?->judul ?? '',
                            $loan->tanggal_pinjam?->format('Y-m-d') ?? '',
                            $loan->tanggal_jatuh_tempo?->format('Y-m-d') ?? '',
                            $loan->tanggal_kembali?->format('Y-m-d') ?? '',
                            $loan->current_status,
                            $loan->petugas?->name ?? '',
                            $loan->catatan ?? '',
                        ]);
                    }
                });

            fclose($handle);
        }, $filename, ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    private function filteredLoansQuery()
    {
        return Loan::query()
            ->when($this->search, fn ($query) => $query->where('nama_peminjam', 'like', "%{$this->search}%"))
            ->when($this->statusFilter, function ($query): void {
                if ($this->statusFilter === 'terlambat') {
                    $query->whereNull('tanggal_kembali')->whereDate('tanggal_jatuh_tempo', '<', today());

                    return;
                }

                $query->where('status', $this->statusFilter);
            });
    }

    public function render()
    {
        $loans = $this->filteredLoansQuery()->with(['book', 'petugas'])->latest()->paginate(10);
        $loans->getCollection()->each(function (Loan $loan): void {
            if ($loan->current_status !== $loan->status && ! $loan->tanggal_kembali) {
                $loan->status = $loan->current_status;
            }
        });

        $selectedBook = $this->book_id ? Book::with('category')->find($this->book_id) : null;

        $search = trim($this->bookSearch);
        $availableBooks = $search !== ''
            ? Book::with('category')
                ->where('stok_tersedia', '>', 0)
                ->where(function ($query) use ($search) {
                    $query->where('judul', 'like', "%{$search}%")
                        ->orWhere('penulis', 'like', "%{$search}%")
                        ->orWhere('no_klasifikasi', 'like', "%{$search}%")
                        ->orWhere('isbn', 'like', "%{$search}%")
                        ->orWhere('lokasi_rak', 'like', "%{$search}%");
                })
                ->latest()
                ->take(8)
                ->get()
            : collect();

        return view('livewire.admin.loan-manager', [
            'loans' => $loans,
            'availableBooks' => $availableBooks,
            'selectedBook' => $selectedBook,
        ])->layout('layouts.admin', ['title' => 'Peminjaman']);
    }
}
