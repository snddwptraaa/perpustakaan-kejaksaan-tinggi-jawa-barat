<?php

namespace App\Services;

use App\Models\Book;
use App\Models\Loan;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use RuntimeException;

class CirculationService
{
    public function __construct(private readonly AuditLogger $auditLogger) {}

    public function borrow(array $data, int $petugasId): Loan
    {
        return DB::transaction(function () use ($data, $petugasId): Loan {
            $book = Book::query()->whereKey($data['book_id'])->lockForUpdate()->firstOrFail();
            if ($book->archived_at || $book->stok_tersedia < 1) {
                throw ValidationException::withMessages(['book_id' => 'Buku sedang tidak tersedia untuk dipinjam.']);
            }

            $loan = Loan::create([...$data, 'petugas_id' => $petugasId, 'denda' => 0, 'denda_dibayar' => 0]);
            $book->decrement('stok_tersedia');
            Cache::forget('books:options');
            $this->auditLogger->model('pinjam', $loan, null, $loan->fresh()->toArray());

            return $loan;
        });
    }

    public function return(Loan $loan, int $petugasId): Loan
    {
        return DB::transaction(function () use ($loan, $petugasId): Loan {
            $lockedLoan = Loan::query()->whereKey($loan->id)->lockForUpdate()->firstOrFail();
            if ($lockedLoan->tanggal_dibatalkan) {
                throw new RuntimeException('Peminjaman yang dibatalkan tidak dapat dikembalikan.');
            }
            if ($lockedLoan->tanggal_kembali) {
                return $lockedLoan;
            }

            $book = Book::query()->whereKey($lockedLoan->book_id)->lockForUpdate()->first();
            if (! $book || $book->stok_tersedia >= $book->stok) {
                throw new RuntimeException('Stok buku tidak konsisten. Periksa inventaris sebelum pengembalian.');
            }

            $before = $lockedLoan->toArray();
            $lockedLoan->update(['tanggal_kembali' => today()]);
            $book->increment('stok_tersedia');
            Cache::forget('books:options');
            $this->auditLogger->model('kembali', $lockedLoan, $before, $lockedLoan->fresh()->toArray(), ['petugas_id' => $petugasId]);

            return $lockedLoan->fresh();
        });
    }

    public function cancel(Loan $loan, int $petugasId): bool
    {
        return DB::transaction(function () use ($loan, $petugasId): bool {
            $lockedLoan = Loan::query()->whereKey($loan->id)->lockForUpdate()->firstOrFail();
            if ($lockedLoan->tanggal_kembali || $lockedLoan->tanggal_dibatalkan) {
                return false;
            }

            $book = Book::query()->whereKey($lockedLoan->book_id)->lockForUpdate()->firstOrFail();
            if ($book->stok_tersedia >= $book->stok) {
                return false;
            }

            $before = $lockedLoan->toArray();
            $lockedLoan->update(['tanggal_dibatalkan' => now(), 'petugas_pembatal_id' => $petugasId]);
            $book->increment('stok_tersedia');
            Cache::forget('books:options');
            $this->auditLogger->model('batal', $lockedLoan, $before, $lockedLoan->fresh()->toArray());

            return true;
        });
    }

    public function extend(Loan $loan, int $petugasId): Loan
    {
        return DB::transaction(function () use ($loan, $petugasId): Loan {
            $lockedLoan = Loan::query()->whereKey($loan->id)->lockForUpdate()->firstOrFail();
            if ($lockedLoan->tanggal_kembali || $lockedLoan->tanggal_dibatalkan || $lockedLoan->jumlah_perpanjangan >= 1) {
                throw new RuntimeException('Peminjaman ini tidak dapat diperpanjang lagi.');
            }

            $before = $lockedLoan->toArray();
            $today = today();
            $dueDate = Carbon::parse($lockedLoan->tanggal_jatuh_tempo);
            $newDueDate = ($dueDate->greaterThan($today) ? $dueDate : $today)->addDays(7);
            $lockedLoan->update([
                'jumlah_perpanjangan' => 1,
                'tanggal_perpanjangan' => $today,
                'tanggal_jatuh_tempo' => $newDueDate,
            ]);
            $this->auditLogger->model('perpanjang', $lockedLoan, $before, $lockedLoan->fresh()->toArray(), ['petugas_id' => $petugasId]);

            return $lockedLoan->fresh();
        });
    }
}
