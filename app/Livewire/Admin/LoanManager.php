<?php

namespace App\Livewire\Admin;

use App\Models\Book;
use App\Models\Loan;
use App\Models\Member;
use App\Services\AuditLogger;
use App\Services\CirculationService;
use App\Services\XlsxReportWriter;
use App\Support\Csv;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Validation\ValidationException;
use Livewire\Component;
use Livewire\WithPagination;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\StreamedResponse;

class LoanManager extends Component
{
    use WithPagination;

    public bool $showForm = false;

    public ?int $editingId = null;

    public string $search = '';

    public string $book_id = '';

    public string $member_id = '';

    public string $bookSearch = '';

    public string $nama_peminjam = '';

    public string $nip_peminjam = '';

    public string $instansi_unit = '';

    public string $tanggal_pinjam = '';

    public string $tanggal_jatuh_tempo = '';

    public string $catatan = '';

    public string $statusFilter = '';

    public string $startDate = '';

    public string $endDate = '';

    protected function rules(): array
    {
        return [
            'book_id' => ['required', 'exists:books,id'],
            'member_id' => ['nullable', 'exists:members,id'],
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

    public function edit(Loan $loan): void
    {
        $this->resetForm();
        $this->editingId = $loan->id;
        $this->book_id = (string) $loan->book_id;
        $this->member_id = (string) ($loan->member_id ?? '');
        $this->nama_peminjam = $loan->nama_peminjam;
        $this->nip_peminjam = $loan->nip_peminjam ?? '';
        $this->instansi_unit = $loan->instansi_unit ?? '';
        $this->tanggal_pinjam = $loan->tanggal_pinjam->toDateString();
        $this->tanggal_jatuh_tempo = $loan->tanggal_jatuh_tempo->toDateString();
        $this->catatan = $loan->catatan ?? '';
        $this->showForm = true;
    }

    public function selectBook(int $id): void
    {
        if ($this->editingId) {
            return;
        }

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
        if ($this->editingId) {
            return;
        }

        $this->book_id = '';
        $this->bookSearch = '';
    }

    public function updatedTanggalPinjam(): void
    {
        if ($this->tanggal_pinjam) {
            $this->tanggal_jatuh_tempo = Carbon::parse($this->tanggal_pinjam)->addDays(7)->toDateString();
        }
    }

    public function updatedMemberId(): void
    {
        if ($this->member_id === '') {
            return;
        }

        $member = Member::active()->find($this->member_id);
        if (! $member) {
            $this->member_id = '';
            $this->addError('member_id', 'Anggota tidak aktif atau tidak ditemukan.');

            return;
        }

        $this->nama_peminjam = $member->nama;
        $this->nip_peminjam = $member->nip ?? '';
        $this->instansi_unit = $member->instansi_unit ?? '';
        $this->resetValidation(['member_id', 'nama_peminjam']);
    }

    public function save(): void
    {
        $data = $this->validate();
        $data['member_id'] = blank($data['member_id'] ?? null) ? null : (int) $data['member_id'];
        foreach (['nip_peminjam', 'instansi_unit', 'catatan'] as $field) {
            $data[$field] = blank($data[$field] ?? null) ? null : trim($data[$field]);
        }

        if ($this->editingId) {
            $loan = Loan::findOrFail($this->editingId);
            $before = $loan->toArray();
            unset($data['book_id']);
            $loan->update($data);
            app(AuditLogger::class)->model('koreksi', $loan, $before, $loan->fresh()->toArray(), [
                'judul_buku' => $loan->book?->judul,
                'nama_peminjam' => $loan->nama_peminjam,
            ]);
            session()->flash('success', 'Data peminjaman berhasil diperbarui.');
            $this->resetForm();

            return;
        }

        try {
            app(CirculationService::class)->borrow($data, (int) auth()->id());
        } catch (ValidationException $exception) {
            foreach ($exception->errors() as $field => $messages) {
                foreach ($messages as $message) {
                    $this->addError($field, $message);
                }
            }

            return;
        }
        session()->flash('success', 'Peminjaman berhasil dicatat.');
        $this->resetForm();
    }

    public function cancel(int $id): void
    {
        try {
            $cancelled = app(CirculationService::class)->cancel(Loan::findOrFail($id), (int) auth()->id());
        } catch (ValidationException $exception) {
            $this->mergeValidationErrors($exception);

            return;
        }

        session()->flash(
            $cancelled ? 'success' : 'error',
            $cancelled
                ? 'Peminjaman dibatalkan dan stok buku telah dikembalikan.'
                : 'Peminjaman tidak dapat dibatalkan. Transaksi mungkin sudah selesai atau stok tidak konsisten.'
        );
    }

    public function returnBook(int $id): void
    {
        try {
            app(CirculationService::class)->return(Loan::findOrFail($id), (int) auth()->id());
        } catch (ValidationException $exception) {
            $this->mergeValidationErrors($exception);

            return;
        }
        session()->flash('success', 'Pengembalian buku berhasil dicatat.');
    }

    public function extendLoan(int $id): void
    {
        try {
            app(CirculationService::class)->extend(Loan::findOrFail($id), (int) auth()->id());
            session()->flash('success', 'Peminjaman berhasil diperpanjang tujuh hari.');
        } catch (ValidationException $exception) {
            $this->mergeValidationErrors($exception);
        }
    }

    /**
     * Salin pesan ValidationException ke error bag komponen.
     */
    private function mergeValidationErrors(ValidationException $exception): void
    {
        foreach ($exception->errors() as $field => $messages) {
            foreach ($messages as $message) {
                $this->addError($field, $message);
            }
        }
    }

    public function resetForm(): void
    {
        $this->reset([
            'showForm',
            'editingId',
            'book_id',
            'member_id',
            'bookSearch',
            'nama_peminjam',
            'nip_peminjam',
            'instansi_unit',
            'tanggal_pinjam',
            'tanggal_jatuh_tempo',
            'catatan',
        ]);
        $this->resetValidation();
    }

    public function exportCsv(): StreamedResponse
    {
        abort_unless(auth()->user()?->isAdmin(), 403);

        [$from, $to, $suffix, $label] = $this->periodInfo();
        $statusLabel = $this->statusLabel();
        $filename = 'laporan-peminjaman'.$suffix.'-'.now()->format('Y-m-d_H-i-s').'.csv';
        $printedAt = now()->format('Y-m-d H:i:s');
        $printedBy = auth()->user()?->name ?? 'Sistem';

        return response()->streamDownload(function () use ($label, $statusLabel, $printedAt, $printedBy): void {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, ['Laporan Peminjaman']);
            fputcsv($handle, ['Periode', $label]);
            fputcsv($handle, ['Status', $statusLabel]);
            fputcsv($handle, ['Dicetak pada', $printedAt]);
            fputcsv($handle, ['Dicetak oleh', $printedBy]);
            fputcsv($handle, []);
            fputcsv($handle, ['Nama Peminjam', 'NIP', 'Instansi / Unit', 'Judul Buku', 'Tanggal Pinjam', 'Jatuh Tempo', 'Tanggal Kembali', 'Status', 'Petugas', 'Catatan']);

            $this->filteredLoansQuery()
                ->with(['book', 'petugas'])
                ->latest()
                ->chunkById(500, function ($loans) use ($handle): void {
                    foreach ($loans as $loan) {
                        fputcsv($handle, array_map([Csv::class, 'safeCell'], [
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
                        ]));
                    }
                });

            fclose($handle);
        }, $filename, ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    public function exportXlsx(): BinaryFileResponse
    {
        abort_unless(auth()->user()?->isAdmin(), 403);

        [$from, $to, $suffix, $label] = $this->periodInfo();

        return app(XlsxReportWriter::class)->download(
            'laporan-peminjaman'.$suffix.'-'.now()->format('Y-m-d_H-i-s').'.xlsx',
            ['Nama Peminjam', 'NIP', 'Instansi / Unit', 'Judul Buku', 'Tanggal Pinjam', 'Jatuh Tempo', 'Tanggal Kembali', 'Status', 'Petugas', 'Catatan'],
            function (callable $appendRow): void {
                $this->filteredLoansQuery()->with(['book', 'petugas'])->latest()->chunkById(500, function ($loans) use ($appendRow): void {
                    foreach ($loans as $loan) {
                        $appendRow([
                            $loan->nama_peminjam, $loan->nip_peminjam ?? '', $loan->instansi_unit ?? '',
                            $loan->book?->judul ?? '', $loan->tanggal_pinjam?->format('Y-m-d') ?? '',
                            $loan->tanggal_jatuh_tempo?->format('Y-m-d') ?? '', $loan->tanggal_kembali?->format('Y-m-d') ?? '',
                            $loan->current_status, $loan->petugas?->name ?? '', $loan->catatan ?? '',
                        ]);
                    }
                });
            },
            title: 'Laporan Peminjaman',
            meta: [
                'Periode' => $label,
                'Status' => $this->statusLabel(),
                'Dicetak pada' => now()->format('Y-m-d H:i:s'),
                'Dicetak oleh' => auth()->user()?->name ?? 'Sistem',
            ],
        );
    }

    public function updatedStartDate(): void
    {
        $this->resetPage();
    }

    public function updatedEndDate(): void
    {
        $this->resetPage();
    }

    public function resetDateRange(): void
    {
        $this->startDate = '';
        $this->endDate = '';
        $this->resetPage();
    }

    public function resetFilters(): void
    {
        $this->reset(['search', 'statusFilter', 'startDate', 'endDate']);
        $this->resetPage();
    }

    private function filteredLoansQuery()
    {
        return Loan::query()
            ->when($this->search, fn ($query) => $query->where(fn ($q) => $q
                ->where('nama_peminjam', 'like', "%{$this->search}%")
                ->orWhereHas('book', fn ($bq) => $bq->where('judul', 'like', "%{$this->search}%"))
            ))
            ->when($this->startDate, fn ($query) => $query->whereDate('tanggal_pinjam', '>=', $this->startDate))
            ->when($this->endDate, fn ($query) => $query->whereDate('tanggal_pinjam', '<=', $this->endDate))
            ->when($this->statusFilter, function ($query): void {
                if ($this->statusFilter === 'terlambat') {
                    $query->whereNull('tanggal_kembali')
                        ->whereNull('tanggal_dibatalkan')
                        ->whereDate('tanggal_jatuh_tempo', '<', today());

                    return;
                }

                if ($this->statusFilter === 'dipinjam') {
                    $query->whereNull('tanggal_kembali')
                        ->whereNull('tanggal_dibatalkan')
                        ->whereDate('tanggal_jatuh_tempo', '>=', today());

                    return;
                }

                if ($this->statusFilter === 'dibatalkan') {
                    $query->whereNotNull('tanggal_dibatalkan');

                    return;
                }

                $query->whereNotNull('tanggal_kembali');
            });
    }

    public function render()
    {
        $loans = $this->filteredLoansQuery()->with(['book', 'petugas', 'member'])->latest()->paginate(15);
        $selectedBook = $this->book_id ? Book::with('category')->find($this->book_id) : null;

        $search = trim($this->bookSearch);
        $availableBooks = $search !== ''
            ? Book::active()->with('category')
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
            'members' => Member::active()->orderBy('nama')->limit(200)->get(),
        ])->layout('layouts.admin', ['title' => 'Peminjaman']);
    }

    public function exportPdf(): StreamedResponse
    {
        abort_unless(auth()->user()?->isAdmin(), 403);

        [$from, $to, $suffix] = $this->periodInfo();
        $filename = 'laporan-peminjaman'.$suffix.'-'.now()->format('Y-m-d_H-i-s').'.pdf';

        $loans = $this->filteredLoansQuery()->with(['book', 'member'])->orderByDesc('tanggal_pinjam')->get();

        $pdf = Pdf::loadView('reports.loans-pdf', [
            'loans' => $loans,
            'from' => $from,
            'to' => $to,
            'statusFilter' => $this->statusFilter ?: 'semua',
        ])
            ->setPaper('a4', 'landscape');

        return response()->streamDownload(function () use ($pdf): void {
            echo $pdf->output();
        }, $filename, ['Content-Type' => 'application/pdf']);
    }

    /**
     * Kembalikan [$from, $to, $suffix, $label] dari filter periode aktif.
     *
     * @return array{?string, ?string, string, string}
     */
    private function periodInfo(): array
    {
        $from = $this->startDate ?: null;
        $to = $this->endDate ?: null;

        if ($from && $to && $from === $to) {
            return [$from, $to, "-{$from}", "Tanggal {$from}"];
        }
        if ($from && $to) {
            return [$from, $to, "-{$from}-sd-{$to}", "{$from} s/d {$to}"];
        }
        if ($from) {
            return [$from, $to, "-sejak-{$from}", "Sejak {$from}"];
        }
        if ($to) {
            return [$from, $to, "-sampai-{$to}", "Sampai {$to}"];
        }

        return [null, null, '', 'Semua periode'];
    }

    private function statusLabel(): string
    {
        return match ($this->statusFilter) {
            'dipinjam' => 'Sedang Dipinjam',
            'terlambat' => 'Terlambat Kembali',
            'dikembalikan' => 'Sudah Dikembalikan',
            'dibatalkan' => 'Dibatalkan',
            default => 'Semua Status',
        };
    }
}
