<?php

namespace App\Console\Commands;

use App\Models\Book;
use App\Models\Category;
use App\Models\Loan;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use OpenSpout\Common\Entity\Cell\FormulaCell;
use OpenSpout\Reader\XLSX\Options;
use OpenSpout\Reader\XLSX\Reader;
use Throwable;

class ImportBooksFromExcel extends Command
{
    protected $signature = 'books:import
                            {path : Lokasi file Excel .xlsx}
                            {--sheet=ALL : Nama sheet sumber}
                            {--replace : Ganti seluruh katalog buku yang ada}
                            {--delete-related-loans : Hapus transaksi peminjaman terkait saat mengganti katalog}
                            {--dry-run : Validasi dan tampilkan ringkasan tanpa menulis ke database}
                            {--force : Lewati konfirmasi interaktif}
                            {--chunk-size=500 : Jumlah buku per batch insert}';

    protected $description = 'Impor katalog buku induk Kejati dari file Excel';

    /** @var array<string, string> */
    private const CATEGORY_NAMES = [
        'B' => 'Buku',
        'P' => 'Peraturan / Perundang-undangan',
        'M' => 'Majalah / Media',
        'L' => 'Laporan',
    ];

    public function handle(): int
    {
        $path = realpath((string) $this->argument('path'));

        if ($path === false || ! is_file($path) || strtolower(pathinfo($path, PATHINFO_EXTENSION)) !== 'xlsx') {
            $this->error('File .xlsx tidak ditemukan atau tidak valid.');

            return self::FAILURE;
        }

        $chunkSize = filter_var($this->option('chunk-size'), FILTER_VALIDATE_INT, [
            'options' => ['min_range' => 1, 'max_range' => 5000],
        ]);

        if ($chunkSize === false) {
            $this->error('--chunk-size harus berupa angka antara 1 dan 5000.');

            return self::INVALID;
        }

        try {
            [$books, $statistics] = $this->readBooks($path, (string) $this->option('sheet'));
        } catch (Throwable $exception) {
            $this->error('Gagal membaca Excel: '.$exception->getMessage());

            return self::FAILURE;
        }

        $this->table(
            ['Pemeriksaan', 'Jumlah'],
            [
                ['Eksemplar valid', number_format($statistics['valid'])],
                ['Judul + penulis unik', number_format(count($books))],
                ['Baris kosong/tanpa judul', number_format($statistics['empty'])],
                ['Header berulang', number_format($statistics['headers'])],
                ['Nomor induk tidak valid', number_format($statistics['invalid_inventory_number'])],
            ],
        );

        if ($books === []) {
            $this->error('Tidak ada data buku valid pada sheet yang dipilih.');

            return self::FAILURE;
        }

        $replace = (bool) $this->option('replace');
        $relatedLoans = $replace ? Loan::count() : 0;

        if ($replace && $relatedLoans > 0 && ! $this->option('delete-related-loans')) {
            $this->error("Penggantian dibatalkan: ada {$relatedLoans} transaksi yang masih merujuk katalog lama.");
            $this->line('Tinjau transaksi tersebut. Jika memang data dummy, ulangi dengan --delete-related-loans.');

            return self::FAILURE;
        }

        if ($this->option('dry-run')) {
            $this->info('Dry run selesai. Database tidak diubah.');

            return self::SUCCESS;
        }

        if ($replace && ! $this->option('force') && ! $this->confirm(
            'Seluruh katalog lama'.($relatedLoans > 0 ? " dan {$relatedLoans} transaksi terkait" : '').' akan dihapus. Lanjutkan?',
        )) {
            $this->warn('Impor dibatalkan.');

            return self::SUCCESS;
        }

        try {
            DB::transaction(function () use ($books, $chunkSize, $replace): void {
                if ($replace) {
                    if ($this->option('delete-related-loans')) {
                        Loan::query()->delete();
                    }

                    Book::query()->delete();
                }

                $categories = $this->categoryIds();
                $now = now();
                $rows = [];

                foreach ($books as $book) {
                    $book['category_id'] = $categories[$book['category']];
                    unset($book['category']);
                    $book['created_at'] = $now;
                    $book['updated_at'] = $now;
                    $rows[] = $book;
                }

                foreach (array_chunk($rows, $chunkSize) as $chunk) {
                    DB::table('books')->insert($chunk);
                }
            }, 3);
        } catch (Throwable $exception) {
            $this->error('Impor gagal dan seluruh perubahan dibatalkan: '.$exception->getMessage());

            return self::FAILURE;
        }

        $this->info(number_format(count($books)).' buku berhasil diimpor dari '.number_format($statistics['valid']).' eksemplar.');

        return self::SUCCESS;
    }

    /**
     * @return array{0: array<int, array<string, int|string|null>>, 1: array<string, int>}
     */
    private function readBooks(string $path, string $sheetName): array
    {
        $options = new Options;
        $options->SHOULD_PRESERVE_EMPTY_ROWS = true;
        $reader = new Reader($options);
        $reader->open($path);

        $groups = [];
        $sheetFound = false;
        $statistics = [
            'valid' => 0,
            'empty' => 0,
            'headers' => 0,
            'invalid_inventory_number' => 0,
        ];

        try {
            foreach ($reader->getSheetIterator() as $sheet) {
                if ($sheet->getName() !== $sheetName) {
                    continue;
                }

                $sheetFound = true;

                foreach ($sheet->getRowIterator() as $rowNumber => $row) {
                    if ($rowNumber < 7) {
                        continue;
                    }

                    $values = array_map(
                        fn ($cell): mixed => $cell instanceof FormulaCell ? $cell->getComputedValue() : $cell->getValue(),
                        array_slice($row->getCells(), 0, 15),
                    );
                    $values = array_pad($values, 15, null);
                    $title = $this->clean($values[3]);
                    $type = strtoupper($this->clean($values[4]));

                    if ($title === '') {
                        $statistics['empty']++;

                        continue;
                    }

                    if (strtoupper($title) === 'JUDUL' || $type === 'JENIS') {
                        $statistics['headers']++;

                        continue;
                    }

                    if (! preg_match('/^\d+(?:\.0)?$/', $this->clean($values[1]))) {
                        $statistics['invalid_inventory_number']++;

                        continue;
                    }

                    $author = $this->clean($values[5]) ?: '-';
                    $key = mb_strtolower($title).'|'.mb_strtolower($author);
                    $statistics['valid']++;

                    if (! isset($groups[$key])) {
                        $groups[$key] = ['stock' => 0, 'score' => -1, 'values' => []];
                    }

                    $groups[$key]['stock']++;
                    $score = count(array_filter($values, fn (mixed $value): bool => ! in_array($this->clean($value), ['', '-'], true)));

                    if ($score > $groups[$key]['score']) {
                        $groups[$key]['score'] = $score;
                        $groups[$key]['values'] = $values;
                    }
                }

                break;
            }
        } finally {
            $reader->close();
        }

        if (! $sheetFound) {
            throw new \RuntimeException("Sheet '{$sheetName}' tidak ditemukan.");
        }

        $books = [];

        foreach ($groups as $group) {
            $values = $group['values'];
            $type = strtoupper($this->clean($values[4]));
            $notes = $this->clean($values[14]);

            $books[] = [
                'category' => self::CATEGORY_NAMES[$type] ?? 'Lainnya',
                'judul' => $this->clean($values[3]),
                'penulis' => $this->clean($values[5]) ?: '-',
                'penerbit' => $this->nullable($values[10]),
                'tahun_terbit' => $this->year($values[11]),
                'jumlah_halaman' => null,
                'isbn' => $this->isbn($values[7]),
                'no_klasifikasi' => $this->nullable($values[12]),
                'lokasi_rak' => null,
                'stok' => $group['stock'],
                'stok_tersedia' => $group['stock'],
                'cover_image' => null,
                'deskripsi' => $notes !== '' && $notes !== '-' ? $notes : null,
            ];
        }

        return [$books, $statistics];
    }

    /** @return array<string, int> */
    private function categoryIds(): array
    {
        $ids = [];

        foreach ([...array_values(self::CATEGORY_NAMES), 'Lainnya'] as $name) {
            $category = Category::firstOrCreate(
                ['slug' => Str::slug($name)],
                ['nama_kategori' => $name],
            );
            $ids[$name] = $category->id;
        }

        return $ids;
    }

    private function clean(mixed $value): string
    {
        if ($value instanceof \DateTimeInterface) {
            return $value->format('Y-m-d');
        }

        return trim((string) preg_replace('/\s+/u', ' ', (string) ($value ?? '')));
    }

    private function nullable(mixed $value): ?string
    {
        $value = $this->clean($value);

        return $value === '' || $value === '-' ? null : $value;
    }

    private function year(mixed $value): ?int
    {
        $value = $this->clean($value);

        if (! preg_match('/\b(1\d{3}|2\d{3})\b/', $value, $matches)) {
            return null;
        }

        $year = (int) $matches[1];

        return $year >= 1000 && $year <= ((int) date('Y') + 1) ? $year : null;
    }

    private function isbn(mixed $value): ?string
    {
        $value = strtoupper($this->clean($value));

        if ($value === '' || $value === '-') {
            return null;
        }

        preg_match_all('/[0-9X][0-9X .-]{8,24}[0-9X]/', $value, $matches);

        foreach ($matches[0] ?? [] as $candidate) {
            $candidate = trim((string) preg_replace('/[^0-9X-]/', '', $candidate), '-');
            $digits = str_replace('-', '', $candidate);

            if (in_array(strlen($digits), [10, 13], true) && strlen($candidate) <= 20) {
                return $candidate;
            }
        }

        return null;
    }
}
