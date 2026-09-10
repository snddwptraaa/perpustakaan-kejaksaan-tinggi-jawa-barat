<?php

namespace Tests\Feature;

use App\Models\AuditLog;
use App\Models\Member;
use Illuminate\Foundation\Testing\RefreshDatabase;
use OpenSpout\Common\Entity\Row;
use OpenSpout\Writer\XLSX\Writer;
use Tests\TestCase;

class ImportMembersFromExcelTest extends TestCase
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
            ['1', 'A-001', 'Anggota Satu', '', 'Bidang Pidum', '', '', '', '', '', '', '', '', '', '', '', '', '', ''],
            ['2', 'A-002', 'Anggota Dua', '', 'Bidang Datun', '', '', '', '', '', '', '', '', '', '08123456789', '', '', '', ''],
        ]);

        $this->artisan('members:import', ['path' => $path, '--dry-run' => true])
            ->expectsOutputToContain('Dry run selesai. Database tidak diubah.')
            ->assertSuccessful();

        $this->assertDatabaseCount('members', 0);
        $this->assertDatabaseCount('audit_logs', 0);
    }

    public function test_import_creates_and_updates_members_without_erasing_existing_phone(): void
    {
        Member::create([
            'nip' => 'A-001',
            'nama' => 'Nama Lama',
            'instansi_unit' => 'Unit Lama',
            'no_hp' => '0811111111',
            'aktif' => false,
        ]);
        $path = $this->workbook([
            ['1', 'A-001', 'Nama Diperbarui', '', 'Bidang Pidum', '', '', '', '', '', '', '', '', '', '', '', '', '', ''],
            ['2', 'A-002', 'Anggota Baru', '', 'Bidang Datun', '', '', '', '', '', '', '', '', '', '0822222222', '', '', '', ''],
        ]);

        $this->artisan('members:import', ['path' => $path, '--force' => true])
            ->expectsOutputToContain('Impor selesai: 1 baru, 1 diperbarui, 0 tidak berubah.')
            ->assertSuccessful();

        $this->assertDatabaseCount('members', 2);
        $this->assertDatabaseHas('members', [
            'nip' => 'A-001',
            'nama' => 'Nama Diperbarui',
            'instansi_unit' => 'Bidang Pidum',
            'no_hp' => '0811111111',
            'aktif' => false,
        ]);
        $this->assertDatabaseHas('members', [
            'nip' => 'A-002',
            'nama' => 'Anggota Baru',
            'no_hp' => '0822222222',
            'aktif' => true,
        ]);
        $this->assertSame(1, AuditLog::query()->where('aksi', 'impor')->where('entitas', 'members')->count());
    }

    public function test_duplicate_nip_aborts_import(): void
    {
        $path = $this->workbook([
            ['1', 'A-001', 'Anggota Satu', '', 'Pidum', '', '', '', '', '', '', '', '', '', '', '', '', '', ''],
            ['2', 'A-001', 'Anggota Duplikat', '', 'Datun', '', '', '', '', '', '', '', '', '', '', '', '', '', ''],
        ]);

        $this->artisan('members:import', ['path' => $path, '--force' => true])
            ->expectsOutputToContain('Impor dihentikan.')
            ->assertFailed();

        $this->assertDatabaseCount('members', 0);
    }

    /** @param list<list<string>> $rows */
    private function workbook(array $rows): string
    {
        $path = tempnam(sys_get_temp_dir(), 'members-import-').'.xlsx';
        $this->temporaryFiles[] = $path;
        $writer = new Writer;
        $writer->openToFile($path);
        $writer->getCurrentSheet()->setName('Data Anggota (Rapi)');
        $writer->addRow(Row::fromValues([
            'No', 'ID Anggota (NIP)', 'Nama Lengkap', 'Jenis Kelamin', 'Jabatan / Unit Kerja',
            'Tipe Anggota', 'Email', 'Alamat', 'Kode Pos', 'Tanggal Registrasi', 'Masa Berlaku',
            'Tanggal Lahir', 'Anggota Sejak', 'File Foto', 'No Telepon', 'PIN', 'No Fax',
            'Anggota Baru', 'Catatan',
        ]));

        foreach ($rows as $row) {
            $writer->addRow(Row::fromValues($row));
        }

        $writer->close();

        return $path;
    }
}
