<?php

namespace App\Console\Commands;

use App\Models\Book;
use App\Services\AuditLogger;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Throwable;

/**
 * Mengunduh sampul buku (cover image) dari layanan publik Open Library.
 *
 * Dua tahap pencarian per buku:
 * 1. Berdasarkan ISBN (dinormalisasi ke digit+X): covers.openlibrary.org/b/isbn/{ISBN}-L.jpg
 * 2. Opsional (--with-title) berdasarkan judul via search.json untuk buku tanpa ISBN.
 *
 * Gambar yang tidak ditemukan (redirect 1x1 placeholder) ditolak berdasarkan
 * status HTTP final + ukuran body + tipe konten, lalu buku dilewati tanpa diubah.
 * File disimpan unik per buku ke storage/app/public/covers/{id}.jpg dan kolom
 * `cover_image` diisi path relatif tersebut (dipakai Storage::url() di Blade).
 */
class FetchBookCovers extends Command
{
    protected $signature = 'books:fetch-covers
                            {--batch=100 : Jumlah maksimal buku yang diproses per panggilan}
                            {--with-title : Cari juga berdasarkan judul untuk buku tanpa ISBN}
                            {--dry-run : Tampilkan ringkasan tanpa mengunduh/menulis database}
                            {--force : Lewati konfirmasi interaktif}';

    protected $description = 'Unduh sampul buku dari Open Library berdasarkan ISBN (opsional: judul)';

    private const MIN_IMAGE_BYTES = 1024;

    public function handle(): int
    {
        $batch = max(1, (int) $this->option('batch'));
        $withTitle = (bool) $this->option('with-title');

        $total = Book::query()->whereNull('cover_image')->count();

        if ($total === 0) {
            $this->info('Tidak ada buku yang memerlukan sampul.');

            return self::SUCCESS;
        }

        $withIsbn = Book::query()->whereNull('cover_image')->whereNotNull('isbn')->count();

        if ($this->option('dry-run')) {
            $this->table(
                ['Pemeriksaan', 'Jumlah'],
                [
                    ['Buku tanpa sampul', number_format($total)],
                    ['Di antaranya ber-ISBN (tahap 1)', number_format($withIsbn)],
                    ['Tanpa ISBN (tahap 2, judul)'.($withTitle ? '' : ' — dilewati tanpa --with-title'), number_format($total - $withIsbn)],
                ],
            );
            $this->info('Dry run selesai. Database tidak diubah.');

            return self::SUCCESS;
        }

        if (! $this->option('force') && ! $this->confirm(
            "Akan memproses maksimal {$batch} dari {$total} buku tanpa sampul"
            .($withTitle ? ' (termasuk pencarian judul).' : '.').' Lanjutkan?',
        )) {
            $this->warn('Operasi dibatalkan.');

            return self::SUCCESS;
        }

        $stats = ['isbn' => 0, 'title' => 0, 'skipped' => 0, 'failed' => 0];

        Book::query()->whereNull('cover_image')->orderBy('id')->limit($batch)
            ->lazyById(100)
            ->each(function (Book $book) use ($withTitle, &$stats): void {
                try {
                    $path = $this->coverByIsbn($book);

                    if ($path === null && $withTitle) {
                        $path = $this->coverByTitle($book);
                        if ($path !== null) {
                            $stats['title']++;
                        }
                    } elseif ($path !== null) {
                        $stats['isbn']++;
                    }

                    if ($path === null) {
                        $stats['skipped']++;

                        return;
                    }

                    $book->cover_image = $path;
                    $book->save();
                } catch (Throwable $e) {
                    $stats['failed']++;
                    $this->warn("Buku #{$book->id} gagal: ".$e->getMessage());
                }
            });

        $downloaded = $stats['isbn'] + $stats['title'];

        app(AuditLogger::class)->record('impor', 'books', metadata: [
            'source' => 'openlibrary',
            'processed' => $stats['isbn'] + $stats['title'] + $stats['skipped'] + $stats['failed'],
            'via_isbn' => $stats['isbn'],
            'via_judul' => $stats['title'],
            'dilewati' => $stats['skipped'],
            'gagal' => $stats['failed'],
        ]);

        $this->info(sprintf(
            'Selesai. Sampul terunduh %s (ISBN: %s, judul: %s), dilewati %s, gagal %s.',
            number_format($downloaded),
            number_format($stats['isbn']),
            number_format($stats['title']),
            number_format($stats['skipped']),
            number_format($stats['failed']),
        ));

        return self::SUCCESS;
    }

    private function coverByIsbn(Book $book): ?string
    {
        $isbn = $this->normalizeIsbn((string) $book->isbn);

        if ($isbn === null) {
            return null;
        }

        $body = $this->download("https://covers.openlibrary.org/b/isbn/{$isbn}-L.jpg?default=false");

        return $body === null ? null : $this->store($book, $body);
    }

    private function coverByTitle(Book $book): ?string
    {
        $title = trim($book->judul);

        if ($title === '') {
            return null;
        }

        try {
            $response = Http::timeout(20)->retry(2, 500)->get('https://openlibrary.org/search.json', [
                'title' => $title,
                'limit' => 3,
            ]);
        } catch (Throwable) {
            return null;
        }

        if (! $response->successful()) {
            return null;
        }

        foreach ((array) ($response->json('docs') ?? []) as $doc) {
            $coverId = $doc['cover_i'] ?? null;

            if (! is_int($coverId)) {
                continue;
            }

            $body = $this->download("https://covers.openlibrary.org/b/id/{$coverId}-L.jpg?default=false");

            if ($body !== null) {
                return $this->store($book, $body);
            }
        }

        return null;
    }

    /**
     * Normalisasi ISBN ke digit (+X untuk ISBN-10). Kembalikan null bila tidak valid.
     */
    private function normalizeIsbn(string $isbn): ?string
    {
        $clean = strtoupper((string) preg_replace('/[^0-9X]/', '', $isbn));

        if (preg_match('/^\d{9}[\dX]$/', $clean) === 1 || preg_match('/^\d{13}$/', $clean) === 1) {
            return $clean;
        }

        return null;
    }

    /**
     * Unduh URL gambar. Kembalikan body bila benar-benar gambar (bukan placeholder).
     */
    private function download(string $url): ?string
    {
        try {
            $response = Http::timeout(20)->retry(2, 500)->get($url);
        } catch (Throwable) {
            return null;
        }

        if (! $response->successful()) {
            return null;
        }

        $type = strtolower((string) $response->header('Content-Type'));

        if (! str_starts_with($type, 'image/')) {
            return null;
        }

        $body = $response->body();

        return strlen($body) >= self::MIN_IMAGE_BYTES ? $body : null;
    }

    private function store(Book $book, string $body): string
    {
        $path = "covers/{$book->id}.jpg";
        Storage::disk('public')->put($path, $body);

        return $path;
    }
}
