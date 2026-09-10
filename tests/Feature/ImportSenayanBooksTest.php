<?php

namespace Tests\Feature;

use App\Models\AuditLog;
use App\Models\Book;
use App\Models\Loan;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use OpenSpout\Common\Entity\Row;
use OpenSpout\Writer\XLSX\Writer;
use Tests\TestCase;

class ImportSenayanBooksTest extends TestCase
{
    use RefreshDatabase;

    /** @var list<string> */
    private array $temporaryFiles = [];

    protected function tearDown(): void
    {
        foreach ($this->temporaryFiles as $path) {
            @unlink($path);
        }

        parent::tearDown();
    }

    public function test_dry_run_validates_workbook_without_writing_to_database(): void
    {
        $path = $this->workbook([
            // judul, penulis, edisi, isbn, penerbit, tahun, tempat, kolasi, panggil, subjek, barcode, rak, kategori
            ['PRINSIP BERACARA PERDATA', 'Marni Emmy Mustafa', '', '978-979-414-019-2', 'Alumni', '2016', 'Bandung', 'ix, 364 hlm : 21 cm', '347.058 598 MAR p', '', '9816', '49.0', 'Hukum Acara Perdata'],
            ['BUKU TANPA RAK', 'Penulis Tanpa Rak', '', '', 'Penerbit', '2010', '', '', '340 TAN r', '', '1234', '', 'Hukum Perdata'],
        ]);

        $this->artisan('books:import-senayan', ['path' => $path, '--dry-run' => true])
            ->expectsOutputToContain('Dry run selesai. Database tidak diubah.')
            ->assertSuccessful();

        $this->assertDatabaseCount('books', 0);
        $this->assertDatabaseCount('categories', 0);
        $this->assertDatabaseCount('audit_logs', 0);
    }

    public function test_import_only_rows_with_shelf_number_and_normalizes_names(): void
    {
        $path = $this->workbook([
            // Baris valid: rak terisi, judul ALL-CAPS.
            ['PRINSIP BERACARA PERDATA', 'Marni Emmy Mustafa', '', '978-979-414-019-2', 'Alumni', '2016', 'Bandung', 'ix, 364 hlm : 21 cm', '347.058 598 MAR p', '', '9816', '49.0', 'Hukum Acara Perdata'],
            // Baris duplikat dari judul yang sama: digabung, stok menjadi 2.
            ['PRINSIP BERACARA PERDATA', 'Marni Emmy Mustafa', '', '', 'Alumni', '2016', 'Bandung', '', '347.058 598 MAR p', '', '9817', '49.0', 'Hukum Acara Perdata'],
            // Baris tanpa nomor rak: harus dilewati.
            ['BUKU TANPA RAK', 'Penulis Tanpa Rak', '', '', 'Penerbit', '2010', '', '', '340 TAN r', '', '1234', '', 'Hukum Perdata (2)'],
        ]);

        $this->artisan('books:import-senayan', ['path' => $path, '--force' => true])
            ->expectsOutputToContain('Impor selesai: 1 judul buku (2 eksemplar) berhasil diimpor.')
            ->assertSuccessful();

        $this->assertDatabaseCount('books', 1);
        $this->assertDatabaseCount('categories', 1);

        $book = Book::first();
        $this->assertNotNull($book);
        $this->assertSame('Marni Emmy Mustafa', $book->penulis);
        $this->assertSame('Prinsip Beracara Perdata', $book->judul);
        $this->assertSame(2, $book->stok);
        $this->assertSame(2, $book->stok_tersedia);
        $this->assertSame('49', $book->lokasi_rak);
        $this->assertSame('347.058 598 MAR p', $book->no_klasifikasi);
    }

    public function test_category_stock_suffix_counts_as_multiple_copies(): void
    {
        $path = $this->workbook([
            ['ENSIKLOPEDI HADIST', 'Penulis Hadist', '', '', 'Penerbit', '2015', '', '', '297 ENS e', '', '5001', '4.0', 'Agama (3 stock)'],
            ['BATTLING UNBELIEF', 'Penulis Lain', '', '', 'Penerbit', '2014', '', '', '297 BAT b', '', '5002', '4.0', 'Agama (stock 2)'],
            ['KAMUS MINI', 'Penulis Kamus', '', '', 'Penerbit', '2013', '', '', '413 KAM k', '', '5003', '5.0', 'Bahasa (2)'],
        ]);

        $this->artisan('books:import-senayan', ['path' => $path, '--force' => true])
            ->expectsOutputToContain('Impor selesai: 3 judul buku (7 eksemplar) berhasil diimpor.')
            ->assertSuccessful();

        $this->assertSame(3, (int) Book::where('judul', 'Ensiklopedi Hadist')->value('stok'));
        $this->assertSame(2, (int) Book::where('judul', 'Battling Unbelief')->value('stok'));
        $this->assertSame(2, (int) Book::where('judul', 'Kamus Mini')->value('stok'));
    }

    public function test_identical_rows_are_merged_into_single_book_with_aggregated_stock(): void
    {
        $path = $this->workbook([
            ['KAJIAN HUKUM PIDANA', 'Tim Redaksi', '', '', 'Kejaksaan Agung RI', '2022', '', '', '345 KAJ k', '', '9791', '43.0', 'Hukum Pidana & Hukum Acara Pidana'],
            ['KAJIAN HUKUM PIDANA', 'Tim Redaksi', '', '', 'Kejaksaan Agung RI', '2022', '', '', '345 KAJ k', '', '9790', '43.0', 'Hukum pidana dan hukum acara pidana (2 stock)'],
        ]);

        $this->artisan('books:import-senayan', ['path' => $path, '--force' => true])
            ->expectsOutputToContain('Impor selesai: 1 judul buku (3 eksemplar) berhasil diimpor.')
            ->assertSuccessful();

        $this->assertDatabaseCount('books', 1);

        $book = Book::first();
        $this->assertSame('Kajian Hukum Pidana', $book->judul);
        $this->assertSame(3, $book->stok);
        $this->assertSame(3, $book->stok_tersedia);
    }

    public function test_replace_flag_swaps_entire_catalog(): void
    {
        $existing = Book::factory()->create();
        $path = $this->workbook([
            ['BUKU PENGGANTI', 'Penulis Pengganti', '', '', 'Penerbit', '2020', '', '', '345 PEN b', '', '9001', '30.0', 'Hukum Pidana & Hukum Acara Pidana'],
        ]);

        $this->artisan('books:import-senayan', ['path' => $path, '--replace' => true, '--force' => true])
            ->expectsOutputToContain('Impor selesai: 1 judul buku (1 eksemplar) berhasil diimpor.')
            ->assertSuccessful();

        $this->assertDatabaseMissing('books', ['id' => $existing->id]);
        $this->assertDatabaseCount('books', 1);
    }

    public function test_replace_refuses_to_delete_books_referenced_by_loans(): void
    {
        $book = Book::factory()->create();
        $admin = User::factory()->create(['role' => 'admin']);
        Loan::create([
            'book_id' => $book->id,
            'petugas_id' => $admin->id,
            'nama_peminjam' => 'Peminjam Uji',
            'tanggal_pinjam' => now()->subDays(2)->toDateString(),
            'tanggal_jatuh_tempo' => now()->addDays(5)->toDateString(),
        ]);
        $path = $this->workbook([
            ['BUKU PENGGANTI', 'Penulis Pengganti', '', '', 'Penerbit', '2020', '', '', '345 PEN b', '', '9001', '30.0', 'Hukum Pidana & Hukum Acara Pidana'],
        ]);

        $this->artisan('books:import-senayan', ['path' => $path, '--replace' => true, '--force' => true])
            ->expectsOutputToContain('Penggantian dibatalkan')
            ->assertFailed();

        $this->assertDatabaseHas('books', ['id' => $book->id]);
    }

    public function test_import_writes_audit_log(): void
    {
        $path = $this->workbook([
            ['BUKU AUDIT', 'Penulis Audit', '', '', 'Penerbit', '2021', '', '', '340 AUD b', '', '9002', '20.0', 'Bahasa'],
        ]);

        $this->artisan('books:import-senayan', ['path' => $path, '--force' => true])
            ->assertSuccessful();

        $this->assertSame(
            1,
            AuditLog::query()
                ->where('aksi', 'impor')
                ->where('entitas', 'books')
                ->count(),
        );
    }

    /**
     * @param  list<list<string>>  $rows
     */
    private function workbook(array $rows): string
    {
        $path = tempnam(sys_get_temp_dir(), 'senayan-books-').'.xlsx';
        $this->temporaryFiles[] = $path;
        $writer = new Writer;
        $writer->openToFile($path);
        $writer->getCurrentSheet()->setName('Data Bibliografi (Rapi)');
        $writer->addRow(Row::fromValues([
            'No', 'Judul Buku', 'Pengarang', 'Tipe Koleksi (GMD)', 'Edisi', 'ISBN / ISSN', 'Penerbit',
            'Tahun Terbit', 'Tempat Terbit', 'Deskripsi Fisik (Kolasi)', 'Nomor Panggil',
            'Nomor Klasifikasi (DDC)', 'Subjek / Topik', 'Bahasa', 'Judul Seri', 'Kode Eksemplar / Barcode',
            'Pernyataan Tanggung Jawab (SOR)', 'File Cover', 'Catatan / Abstrak', 'Nomor RAK', 'Kategori',
        ]));

        foreach ($rows as $row) {
            $writer->addRow(Row::fromValues([
                '1', $row[0], $row[1], 'Text', $row[2], $row[3], $row[4], $row[5], $row[6],
                $row[7], $row[8], '', $row[9], 'Indonesia', '', $row[10], '', '', '', $row[11], $row[12],
            ]));
        }

        $writer->close();

        return $path;
    }
}
