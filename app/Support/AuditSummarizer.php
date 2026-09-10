<?php

namespace App\Support;

use App\Models\AuditLog;
use Carbon\Carbon;

/**
 * Pembangun teks ringkasan dan perhitungan diff untuk sebuah AuditLog.
 *
 * Dipisahkan dari model agar AuditLog fokus pada relasi/casts,
 * sedangkan cara menampilkan "apa yang berubah" hidup di sini.
 */
final class AuditSummarizer
{
    /**
     * @return array<int, array{key: string, label: string, old_raw: mixed, new_raw: mixed, old_formatted: string, new_formatted: string}>
     */
    public static function differences(AuditLog $log): array
    {
        if (! is_array($log->sebelum) || ! is_array($log->sesudah)) {
            return [];
        }

        $ignoredKeys = [
            'id', 'created_at', 'updated_at', 'password',
            'remember_token', 'email_verified_at',
        ];

        $diffs = [];
        $allKeys = array_unique(array_merge(array_keys($log->sebelum), array_keys($log->sesudah)));

        foreach ($allKeys as $key) {
            if (in_array($key, $ignoredKeys, true)) {
                continue;
            }

            $valBefore = $log->sebelum[$key] ?? null;
            $valAfter = $log->sesudah[$key] ?? null;

            $beforeNorm = is_numeric($valBefore) ? (string) $valBefore : $valBefore;
            $afterNorm = is_numeric($valAfter) ? (string) $valAfter : $valAfter;

            if ($beforeNorm !== $afterNorm) {
                $diffs[] = [
                    'key' => $key,
                    'label' => AuditFieldMap::label($key),
                    'old_raw' => $valBefore,
                    'new_raw' => $valAfter,
                    'old_formatted' => AuditFieldMap::format($key, $valBefore),
                    'new_formatted' => AuditFieldMap::format($key, $valAfter),
                ];
            }
        }

        return $diffs;
    }

    public static function summary(AuditLog $log): string
    {
        $subject = $log->subject_title;

        if (in_array($log->aksi, ['ubah', 'koreksi'], true)) {
            $diffs = self::differences($log);
            if (count($diffs) === 1) {
                $diff = $diffs[0];

                return "Mengubah {$diff['label']}: {$diff['old_formatted']} → {$diff['new_formatted']}";
            }
            if (count($diffs) > 1) {
                $labels = array_column(array_slice($diffs, 0, 3), 'label');
                $extra = count($diffs) > 3 ? ' +'.(count($diffs) - 3).' lainnya' : '';

                return 'Mengubah '.count($diffs).' kolom ('.implode(', ', $labels).$extra.')';
            }

            return $subject ? "Pembaruan data pada {$subject}" : 'Pembaruan data';
        }

        if ($log->aksi === 'buat') {
            return $subject ? "Menambahkan data baru: {$subject}" : 'Menambahkan data baru';
        }

        if ($log->aksi === 'hapus') {
            return $subject ? "Menghapus data: {$subject}" : 'Menghapus data';
        }

        if ($log->aksi === 'pinjam') {
            $borrower = $log->metadata['nama_peminjam'] ?? $log->sesudah['nama_peminjam'] ?? null;
            $book = $log->metadata['judul_buku'] ?? null;
            if ($borrower && $book) {
                return "Peminjaman: {$borrower} meminjam \"{$book}\"";
            }

            return $borrower ? "Peminjaman oleh {$borrower}" : 'Pencatatan peminjaman buku';
        }

        if ($log->aksi === 'kembali') {
            $book = $log->metadata['judul_buku'] ?? null;
            $borrower = $log->metadata['nama_peminjam'] ?? null;
            if ($book) {
                return "Pengembalian buku: \"{$book}\"".($borrower ? " ({$borrower})" : '');
            }

            return 'Konfirmasi pengembalian buku';
        }

        if ($log->aksi === 'perpanjang') {
            $newDue = $log->sesudah['tanggal_jatuh_tempo'] ?? null;
            $dateFormatted = $newDue ? Carbon::parse($newDue)->translatedFormat('d M Y') : '';

            return 'Perpanjangan jatuh tempo'.($dateFormatted ? " s.d. {$dateFormatted}" : '');
        }

        if ($log->aksi === 'batal') {
            return 'Pembatalan transaksi'.($subject ? " ({$subject})" : '').' — stok dipulihkan';
        }

        if ($log->aksi === 'arsipkan') {
            return 'Mengarsipkan buku'.($subject ? ": {$subject}" : '').' dari katalog aktif';
        }

        if ($log->aksi === 'pulihkan') {
            return 'Memulihkan buku'.($subject ? ": {$subject}" : '').' ke katalog aktif';
        }

        if ($log->aksi === 'nonaktifkan') {
            return 'Menonaktifkan data'.($subject ? ": {$subject}" : '');
        }

        return ucfirst($log->aksi).($subject ? " pada {$subject}" : '');
    }
}
