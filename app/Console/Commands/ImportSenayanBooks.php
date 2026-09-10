<?php

namespace App\Console\Commands;

use App\Models\Book;
use App\Models\Category;
use App\Services\AuditLogger;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use OpenSpout\Common\Entity\Cell\FormulaCell;
use OpenSpout\Reader\XLSX\Options;
use OpenSpout\Reader\XLSX\Reader;
use Throwable;

/**
 * Impor katalog buku dari hasil "Data Bibliografi (Rapi)" export SLiMS/Senayan.
 *
 * Aturan bisnis yang diterapkan:
 * - Hanya baris dengan "Nomor RAK" yang diimpor; baris tanpa nomor rak dilewati.
 * - Kolom "Kategori" dinormalisasi ke nama baku (varian "(2 Stock)", kapitalisasi,
 *   "&"/"dan", dan salah ketik digabung ke satu kategori yang sama).
 * - Judul ALL-CAPS diubah ke Title Case dengan pengecualian akronim hukum yang umum.
 * - Baris dengan judul + pengarang + tahun + penerbit yang sama dianggap eksemplar
 *   ganda: digabung jadi satu buku dengan stok = jumlah eksemplar.
 */
class ImportSenayanBooks extends Command
{
    protected $signature = 'books:import-senayan
                            {path : Lokasi file Excel .xlsx hasil export SLiMS}
                            {--sheet=Data Bibliografi (Rapi) : Nama sheet sumber}
                            {--replace : Ganti seluruh katalog buku yang ada}
                            {--delete-related-loans : Hapus transaksi peminjaman terkait saat mengganti katalog}
                            {--dry-run : Validasi dan tampilkan ringkasan tanpa menulis ke database}
                            {--force : Lewati konfirmasi interaktif}';

    protected $description = 'Impor katalog buku dari export Senayan/SLiMS (sheet Data Bibliografi Rapi)';

    private const COLUMN = [
        'judul' => 1,
        'penulis' => 2,
        'edisi' => 4,
        'isbn' => 5,
        'penerbit' => 6,
        'tahun' => 7,
        'tempat' => 8,
        'kolasi' => 9,
        'panggil' => 10,
        'subjek' => 12,
        'barcode' => 15,
        'rak' => 19,
        'kategori' => 20,
    ];

    /**
     * Varian nama kategori -> nama baku.
     *
     * @var array<string, string>
     */
    private const CATEGORY_ALIASES = [
        'agama' => 'Agama',
        'agama islam' => 'Agama Islam',
        'administrasi pemerintahan dan ilmu kemiliteran' => 'Administrasi Pemerintahan dan Ilmu Kemiliteran',
        'antropologi dan biologiantropologi dan biologi' => 'Antropologi dan Biologi',
        'antropologi dan biologi' => 'Antropologi dan Biologi',
        'bahasa' => 'Bahasa',
        'geografi dan sejarah umum' => 'Geografi dan Sejarah Umum',
        'hukum acara perdata' => 'Hukum Acara Perdata',
        'hukum acara perdata perundang-undangan peraturan dan perkara' => 'Hukum Acara Perdata, Perundang-Undangan, Peraturan dan Perkara',
        'hukum perdata' => 'Hukum Perdata',
        'hukum pidana dan hukum acara pidana' => 'Hukum Pidana dan Hukum Acara Pidana',
        'hukum publik dan hukum sosial' => 'Hukum Publik dan Hukum Sosial',
        'hukum sosial, hukum pidana, hukum acara pidana' => 'Hukum Sosial, Hukum Pidana dan Hukum Acara Pidana',
        'hukum tata negara dan administrasi' => 'Hukum Tata Negara dan Hukum Administrasi',
        'hukum tata negara dan hukum administrasi' => 'Hukum Tata Negara dan Hukum Administrasi',
        'hukum tata negara dan hukum administrasi, hukum acara perdata' => 'Hukum Tata Negara dan Hukum Administrasi, Hukum Acara Perdata',
        'hukum negara, hukum administrasi dan hukum publik' => 'Hukum Negara, Hukum Administrasi dan Hukum Publik',
        'hukum, termasuk teori dan filsafat' => 'Ilmu Pengetahuan Sosial, Hukum, Termasuk Teori dan Filsafat',
        'ilmu manajemen umum dan administrasi umum' => 'Ilmu Manajemen Umum dan Administrasi Umum',
        'ilmu managemen umum dan administrasi umum' => 'Ilmu Manajemen Umum dan Administrasi Umum',
        'ilmu pengetahuan sosial dan ekonomi' => 'Ilmu Pengetahuan Sosial dan Ilmu Ekonomi',
        'ilmu pengetahuan sosial dan hukum internasional' => 'Ilmu Pengetahuan Sosial dan Hukum Internasional',
        'ilmu pengetahuan sosial dan ilmu ekonomi' => 'Ilmu Pengetahuan Sosial dan Ilmu Ekonomi',
        'ilmu pengetahuan sosial dan ilmu ekonomi, hukum acara perdata' => 'Ilmu Pengetahuan Sosial dan Ilmu Ekonomi, Hukum Acara Perdata',
        'ilmu pengetahuan sosial dan politik' => 'Ilmu Pengetahuan Sosial dan Politik',
        'ilmu pengetahuan sosial hukum termasuk teori dan filsafat' => 'Ilmu Pengetahuan Sosial, Hukum, Termasuk Teori dan Filsafat',
        'ilmu pengetahuan sosial, hukum, termasuk teori dan filsafat' => 'Ilmu Pengetahuan Sosial, Hukum, Termasuk Teori dan Filsafat',
        'ilmu pengetahuan sosial dan sosiologi' => 'Ilmu Pengetahuan, Sosial dan Sosiologi',
        'ilmu pengetahuan, sosial dan sosiologi' => 'Ilmu Pengetahuan, Sosial dan Sosiologi',
        'ilmu terapan dan kesenian' => 'Ilmu Terapan dan Kesenian',
        'informasi dan karya umum' => 'Informasi dan Karya Umum',
        'kesusasteraan amerika dan sastra jepang' => 'Kesusasteraan Amerika dan Sastra Jepang',
        'kesusasteraan dan sastra amerika' => 'Kesusasteraan dan Sastra Amerika',
        'kesusasteraan umum' => 'Kesusasteraan Umum',
        'manajemen dan tata laksana perkantoran' => 'Manajemen dan Tata Laksana Perkantoran',
        'pelayanan sosial, kesejahteraan masyarakat dan pendidikan' => 'Pelayanan Sosial, Kesejahteraan Masyarakat dan Pendidikan',
        'pelayanan sosial kesejahteraan masyarakat dan pendidikan' => 'Pelayanan Sosial, Kesejahteraan Masyarakat dan Pendidikan',
        'pendidikan' => 'Pendidikan',
        'peraturan perundang-undangan' => 'Peraturan Perundang-Undangan',
        'perundang-undangan peraturan perkara' => 'Perundang-Undangan, Peraturan, Perkara',
        'perundang-undangan, peraturan, perkara' => 'Perundang-Undangan, Peraturan, Perkara',
    ];

    /**
     * Akronim/frasa yang dipertahankan bentuk aslinya saat title-casing judul.
     *
     * @var list<string>
     */
    private const TITLE_UPPER_WORDS = [
        'uu', 'uud', 'uuham', 'ri', 'r.i', 'dki', 'dprd', 'bam', 'bani', 'bnpt', 'bpkn',
        'kejagung', 'kejati', 'kejari', 'cssn', 'iapi', 'ini', 'iso', 'pbb', 'tpu', 'tppu',
        'bsi', 'kpk', 'polri', 'bkpm', 'bumn', 'bps', 'bmn', 'npwp', 'kkks', 'perpilu',
        'icj', 'icc', 'asean', 'un', 'unesco', 'ilo', 'imf', 'wto', 'who', 'ipo', 'isbn',
        'issn', 'hki', 'otonomi', 'covid-19', 'hiv', 'aids',
    ];

    /** @var list<string> */
    private const TITLE_ROMAN_WORDS = [
        'i', 'ii', 'iii', 'iv', 'v', 'vi', 'vii', 'viii', 'ix', 'x', 'xi', 'xii', 'xiii',
        'xiv', 'xv', 'xvi', 'xvii', 'xviii', 'xix', 'xx',
    ];

    public function handle(): int
    {
        $path = realpath((string) $this->argument('path'));

        if ($path === false || ! is_file($path) || strtolower(pathinfo($path, PATHINFO_EXTENSION)) !== 'xlsx') {
            $this->error('File .xlsx tidak ditemukan atau tidak valid.');

            return self::FAILURE;
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
                ['Baris data terbaca', number_format($statistics['rows'])],
                ['Baris tanpa nomor rak (dilewati)', number_format($statistics['no_rak'])],
                ['Baris rak terisi (valid)', number_format($statistics['valid'])],
                ['Judul unik hasil gabung duplikat', number_format(count($books))],
                ['Total eksemplar (termasuk sufiks "(2)", "(2 stock)", dll)', number_format($statistics['copies'])],
                ['Tahun tidak valid (dibuat NULL)', number_format($statistics['bad_year'])],
            ],
        );

        if ($books === []) {
            $this->error('Tidak ada baris dengan nomor rak pada sheet yang dipilih.');

            return self::FAILURE;
        }

        $replace = (bool) $this->option('replace');
        $existingBooks = Book::count();
        $relatedLoans = $replace ? DB::table('loans')->count() : 0;

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
            'Seluruh katalog lama'.($existingBooks > 0 ? " ({$existingBooks} buku)" : '')
            .($relatedLoans > 0 ? " dan {$relatedLoans} transaksi terkait" : '').' akan dihapus. Lanjutkan?',
        )) {
            $this->warn('Impor dibatalkan.');

            return self::SUCCESS;
        }

        if (! $replace && ! $this->option('force') && $existingBooks > 0 && ! $this->confirm(
            "Katalog sudah berisi {$existingBooks} buku. Buku hasil impor akan ditambahkan. Lanjutkan?",
        )) {
            $this->warn('Impor dibatalkan.');

            return self::SUCCESS;
        }

        try {
            DB::transaction(function () use ($books, $path, $replace, &$statistics): void {
                if ($replace) {
                    if ($this->option('delete-related-loans')) {
                        DB::table('loans')->delete();
                    }

                    Book::query()->delete();
                }

                $categories = $this->categoryIds(array_unique(array_column($books, 'kategori')));
                $now = now();

                foreach ($books as $book) {
                    DB::table('books')->insert([
                        'category_id' => $categories[$book['kategori']],
                        'judul' => $book['judul'],
                        'penulis' => $book['penulis'],
                        'penerbit' => $book['penerbit'],
                        'tahun_terbit' => $book['tahun_terbit'],
                        'jumlah_halaman' => $book['jumlah_halaman'],
                        'isbn' => $book['isbn'],
                        'no_klasifikasi' => $book['no_klasifikasi'],
                        'lokasi_rak' => $book['lokasi_rak'],
                        'stok' => $book['stok'],
                        'stok_tersedia' => $book['stok'],
                        'cover_image' => null,
                        'deskripsi' => $book['deskripsi'],
                        'created_at' => $now,
                        'updated_at' => $now,
                    ]);
                }

                $statistics['inserted'] = count($books);

                app(AuditLogger::class)->record('impor', 'books', metadata: [
                    'source' => basename($path),
                    'sheet' => (string) $this->option('sheet'),
                    'judul_unik' => count($books),
                    'eksemplar' => $statistics['copies'],
                    'replace' => $replace,
                ]);
            }, 3);
        } catch (Throwable $exception) {
            $this->error('Impor gagal dan seluruh perubahan dibatalkan: '.$exception->getMessage());

            return self::FAILURE;
        }

        $this->info(sprintf(
            'Impor selesai: %s judul buku (%s eksemplar) berhasil diimpor.',
            number_format(count($books)),
            number_format($statistics['copies']),
        ));

        return self::SUCCESS;
    }

    /**
     * @return array{0: list<array{kategori: string, judul: string, penulis: string, penerbit: ?string, tahun_terbit: ?int, jumlah_halaman: ?string, isbn: ?string, no_klasifikasi: ?string, lokasi_rak: ?string, stok: int, deskripsi: ?string}>, 1: array{rows: int, no_rak: int, valid: int, bad_year: int, inserted?: int}}
     */
    private function readBooks(string $path, string $sheetName): array
    {
        $options = new Options;
        $options->SHOULD_PRESERVE_EMPTY_ROWS = true;
        $reader = new Reader($options);
        $reader->open($path);

        $groups = [];
        $sheetFound = false;
        $statistics = ['rows' => 0, 'no_rak' => 0, 'valid' => 0, 'copies' => 0, 'bad_year' => 0];

        try {
            foreach ($reader->getSheetIterator() as $sheet) {
                if ($sheet->getName() !== $sheetName) {
                    continue;
                }

                $sheetFound = true;

                foreach ($sheet->getRowIterator() as $rowNumber => $row) {
                    if ($rowNumber === 1) {
                        continue; // header
                    }

                    $values = array_map(
                        fn ($cell): mixed => $cell instanceof FormulaCell ? $cell->getComputedValue() : $cell->getValue(),
                        $row->getCells(),
                    );
                    $values = array_pad($values, self::COLUMN['kategori'] + 1, null);

                    $rawJudul = $this->clean($values[self::COLUMN['judul']]);

                    if ($rawJudul === '' || mb_strtolower($rawJudul) === 'judul buku') {
                        continue;
                    }

                    $statistics['rows']++;

                    $rak = $this->rak($values[self::COLUMN['rak']]);

                    if ($rak === null) {
                        $statistics['no_rak']++;

                        continue;
                    }

                    $kategori = $this->kategori($values[self::COLUMN['kategori']]);

                    if ($kategori === null) {
                        $this->warn(sprintf(
                            'Baris %d dilewati: kategori tidak dikenali ("%s").',
                            $rowNumber,
                            Str::limit($this->clean($values[self::COLUMN['kategori']]), 60),
                        ));

                        continue;
                    }

                    // Sufiks angka pada kategori, mis. "(2)", "(2 stock)", "(stock 2)",
                    // menandakan jumlah eksemplar baris tersebut.
                    $eksemplar = $this->eksemplar($values[self::COLUMN['kategori']]);
                    $statistics['copies'] += $eksemplar;

                    $tahun = $this->year($values[self::COLUMN['tahun']]);

                    if ($tahun === null && $this->clean($values[self::COLUMN['tahun']]) !== '') {
                        $statistics['bad_year']++;
                    }

                    $statistics['valid']++;

                    $penulis = $this->clean($values[self::COLUMN['penulis']]);
                    $judul = $this->titleCase($rawJudul);
                    $penerbit = $this->nullable($values[self::COLUMN['penerbit']]);
                    $key = mb_strtolower($judul).'|'.mb_strtolower($this->clean($penulis)).'|'.$tahun.'|'.mb_strtolower((string) $penerbit);

                    if (! isset($groups[$key])) {
                        $groups[$key] = [
                            'kategori' => $kategori,
                            'judul' => $judul,
                            'penulis' => $penulis !== '' ? $penulis : '-',
                            'penerbit' => $penerbit,
                            'tahun_terbit' => $tahun,
                            'jumlah_halaman' => $this->jumlahHalaman($values[self::COLUMN['kolasi']]),
                            'isbn' => $this->isbn($values[self::COLUMN['isbn']]),
                            'no_klasifikasi' => $this->nullable($values[self::COLUMN['panggil']]),
                            'lokasi_rak' => $rak,
                            'stok' => 0,
                            'deskripsi' => $this->deskripsi($values),
                        ];
                    }

                    $groups[$key]['stok'] += $eksemplar;
                }

                break;
            }
        } finally {
            $reader->close();
        }

        if (! $sheetFound) {
            throw new \RuntimeException("Sheet '{$sheetName}' tidak ditemukan.");
        }

        return [array_values($groups), $statistics];
    }

    /** @return array<string, int> */
    private function categoryIds(array $usedNames): array
    {
        $ids = [];

        foreach ($usedNames as $name) {
            $category = Category::firstOrCreate(
                ['slug' => Str::slug($name)],
                ['nama_kategori' => $name],
            );
            $ids[$name] = $category->id;
        }

        return $ids;
    }

    private function kategori(mixed $value): ?string
    {
        $normalized = Str::of($this->clean($value))
            ->replace('&', ' dan ')
            ->lower()
            ->squish()
            ->toString();

        // Buang sufiks jumlah eksemplar seperti "(2 stock)", "(stock 2)", "(2)".
        $normalized = trim((string) preg_replace('/\((?:stock\s*)?\d+(?:\s*stock)?\)/', '', $normalized));

        // Rapikan pemisah yang salah ketik: "Perundang - Undangan" -> "Perundang-Undangan",
        // buang tanda baca nyangkut di awal/akhir, dan teks kategori yang terduplikasi
        // ("Antropologi & BiologiAntropologi & Biologi,").
        $normalized = (string) Str::of($normalized)
            ->replace(' - ', '-')
            ->replaceMatches('/(\b[a-z][a-z -]{4,}\b)[,;]?\1+/', '$1')
            ->replaceMatches('/^[\s,;.:-]+|[\s,;.:-]+$/', '')
            ->squish()
            ->toString();

        return self::CATEGORY_ALIASES[$normalized] ?? null;
    }

    /**
     * Ambil nomor rak: bilangan di awal sel (mis. "49.0", "Rak 52", "26, 32").
     */
    private function rak(mixed $value): ?string
    {
        $value = $this->clean($value);

        if ($value === '') {
            return null;
        }

        if (preg_match('/^(\d+)(?:[.,]\d+)?/', $value, $matches) === 1) {
            return ltrim($matches[1], '0') !== '' ? $matches[1] : '0';
        }

        return null;
    }

    private function year(mixed $value): ?int
    {
        $value = $this->clean($value);

        if (preg_match('/\b(1\d{3}|2\d{3})\b/', $value, $matches) === 1) {
            $year = (int) $matches[1];

            if ($year >= 1000 && $year <= ((int) date('Y') + 1)) {
                return $year;
            }
        }

        return null;
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

    /**
     * Ambil jumlah halaman dari kolasi, mis. "vi, 813 hlm : 21 cm" -> "813 hlm".
     */
    private function jumlahHalaman(mixed $value): ?string
    {
        $value = $this->clean($value);

        if (preg_match('/(\d+)\s*h/i', $value, $matches) === 1) {
            return $matches[1].' hlm';
        }

        return null;
    }

    /**
     * Susun deskripsi dari kolasi, tempat terbit, subjek, dan catatan edisi.
     *
     * @param  list<mixed>  $values
     */
    private function deskripsi(array $values): ?string
    {
        $parts = array_filter([
            $this->nullable($values[self::COLUMN['kolasi']]),
            $this->nullable($values[self::COLUMN['edisi']]) !== null
                ? 'Edisi '.$this->nullable($values[self::COLUMN['edisi']])
                : null,
            $this->nullable($values[self::COLUMN['tempat']]),
            $this->nullable($values[self::COLUMN['subjek']]),
        ], fn (?string $part): bool => $part !== null);

        if ($parts === []) {
            return null;
        }

        return implode(' | ', $parts);
    }

    private function titleCase(string $value): string
    {
        $value = trim($value);

        if ($value === '') {
            return $value;
        }

        return (string) Str::of($value)->replaceMatches('/[^\s]+/', function (array $matches): string {
            $word = $matches[0];
            $key = mb_strtolower(preg_replace('/[^A-Za-z]/', '', $word));

            if (in_array($key, self::TITLE_UPPER_WORDS, true)) {
                return mb_strtoupper($word);
            }

            if (in_array($key, self::TITLE_ROMAN_WORDS, true)) {
                return mb_strtoupper($word);
            }

            return mb_strtoupper(mb_substr($word, 0, 1)).mb_strtolower(mb_substr($word, 1));
        });
    }

    private function clean(mixed $value): string
    {
        if ($value instanceof \DateTimeInterface) {
            return $value->format('Y-m-d');
        }

        return trim((string) preg_replace('/\s+/u', ' ', (string) ($value ?? '')));
    }

    private function eksemplar(mixed $value): int
    {
        $clean = $this->clean($value);
        if (preg_match('/\((?:stock\s*)?(\d+)(?:\s*stock)?\)/i', $clean, $m)) {
            return (int) $m[1];
        }

        return 1;
    }

    private function nullable(mixed $value): ?string
    {
        $value = $this->clean($value);

        return $value === '' || $value === '-' ? null : $value;
    }
}
