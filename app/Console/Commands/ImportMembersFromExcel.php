<?php

namespace App\Console\Commands;

use App\Models\Member;
use App\Services\AuditLogger;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use OpenSpout\Common\Entity\Cell\FormulaCell;
use OpenSpout\Reader\XLSX\Options;
use OpenSpout\Reader\XLSX\Reader;
use Throwable;

class ImportMembersFromExcel extends Command
{
    protected $signature = 'members:import
                            {path : Lokasi file Excel .xlsx}
                            {--sheet=Data Anggota (Rapi) : Nama sheet sumber}
                            {--dry-run : Validasi dan tampilkan ringkasan tanpa menulis ke database}
                            {--force : Lewati konfirmasi interaktif}';

    protected $description = 'Impor atau perbarui anggota dari hasil ekspor anggota Senayan/SLiMS';

    /** @var array<string, string> */
    private const HEADERS = [
        'nip' => 'ID Anggota (NIP)',
        'nama' => 'Nama Lengkap',
        'instansi_unit' => 'Jabatan / Unit Kerja',
        'no_hp' => 'No Telepon',
    ];

    public function handle(): int
    {
        $path = realpath((string) $this->argument('path'));

        if ($path === false || ! is_file($path) || strtolower(pathinfo($path, PATHINFO_EXTENSION)) !== 'xlsx') {
            $this->error('File .xlsx tidak ditemukan atau tidak valid.');

            return self::FAILURE;
        }

        try {
            [$members, $statistics] = $this->readMembers($path, (string) $this->option('sheet'));
        } catch (Throwable $exception) {
            $this->error('Gagal membaca Excel: '.$exception->getMessage());

            return self::FAILURE;
        }

        $this->table(
            ['Pemeriksaan', 'Jumlah'],
            [
                ['Baris data', number_format($statistics['rows'])],
                ['Anggota valid', number_format(count($members))],
                ['Baris kosong', number_format($statistics['empty'])],
                ['Baris tidak valid', number_format($statistics['invalid'])],
                ['NIP duplikat dalam file', number_format($statistics['duplicates'])],
            ],
        );

        if ($statistics['invalid'] > 0 || $statistics['duplicates'] > 0) {
            $this->error('Impor dihentikan. Perbaiki baris tidak valid atau NIP duplikat pada workbook.');

            return self::FAILURE;
        }

        if ($members === []) {
            $this->error('Tidak ada data anggota valid pada sheet yang dipilih.');

            return self::FAILURE;
        }

        if ($this->option('dry-run')) {
            $this->info('Dry run selesai. Database tidak diubah.');

            return self::SUCCESS;
        }

        if (! $this->option('force') && ! $this->confirm(
            'Anggota baru akan ditambahkan dan anggota dengan NIP yang sama akan diperbarui. Lanjutkan?',
        )) {
            $this->warn('Impor dibatalkan.');

            return self::SUCCESS;
        }

        $result = ['created' => 0, 'updated' => 0, 'unchanged' => 0];

        try {
            DB::transaction(function () use ($members, $path, &$result): void {
                $existing = Member::query()
                    ->whereIn('nip', array_column($members, 'nip'))
                    ->lockForUpdate()
                    ->get()
                    ->groupBy(fn (Member $member): string => mb_strtolower((string) $member->nip));

                $ambiguous = $existing->filter(fn ($group): bool => $group->count() > 1);
                if ($ambiguous->isNotEmpty()) {
                    throw new \RuntimeException(
                        $ambiguous->count().' NIP memiliki lebih dari satu anggota di database. Rapikan duplikat sebelum impor.',
                    );
                }

                foreach ($members as $data) {
                    $key = mb_strtolower($data['nip']);
                    /** @var Member|null $member */
                    $member = $existing->get($key)?->first();

                    if ($member === null) {
                        Member::create([...$data, 'aktif' => true]);
                        $result['created']++;

                        continue;
                    }

                    $updates = ['nama' => $data['nama']];
                    foreach (['instansi_unit', 'no_hp'] as $field) {
                        if ($data[$field] !== null) {
                            $updates[$field] = $data[$field];
                        }
                    }

                    $member->fill($updates);
                    if ($member->isDirty()) {
                        $member->save();
                        $result['updated']++;
                    } else {
                        $result['unchanged']++;
                    }
                }

                app(AuditLogger::class)->record('impor', 'members', metadata: [
                    'source' => basename($path),
                    'sheet' => (string) $this->option('sheet'),
                    ...$result,
                ]);
            }, 3);
        } catch (Throwable $exception) {
            $this->error('Impor gagal dan seluruh perubahan dibatalkan: '.$exception->getMessage());

            return self::FAILURE;
        }

        Cache::forget('members:options');
        $this->info(sprintf(
            'Impor selesai: %s baru, %s diperbarui, %s tidak berubah.',
            number_format($result['created']),
            number_format($result['updated']),
            number_format($result['unchanged']),
        ));

        return self::SUCCESS;
    }

    /**
     * @return array{0: list<array{nip: string, nama: string, instansi_unit: ?string, no_hp: ?string}>, 1: array{rows: int, empty: int, invalid: int, duplicates: int}}
     */
    private function readMembers(string $path, string $sheetName): array
    {
        $options = new Options;
        $options->SHOULD_PRESERVE_EMPTY_ROWS = true;
        $reader = new Reader($options);
        $reader->open($path);

        $members = [];
        $seen = [];
        $sheetFound = false;
        $headerIndexes = null;
        $statistics = ['rows' => 0, 'empty' => 0, 'invalid' => 0, 'duplicates' => 0];

        try {
            foreach ($reader->getSheetIterator() as $sheet) {
                if ($sheet->getName() !== $sheetName) {
                    continue;
                }

                $sheetFound = true;

                foreach ($sheet->getRowIterator() as $rowNumber => $row) {
                    $values = array_map(
                        fn ($cell): string => $this->clean($cell instanceof FormulaCell ? $cell->getComputedValue() : $cell->getValue()),
                        $row->getCells(),
                    );

                    if ($headerIndexes === null) {
                        $headerIndexes = $this->headerIndexes($values);

                        continue;
                    }

                    $statistics['rows']++;
                    if (count(array_filter($values, fn (string $value): bool => $value !== '')) === 0) {
                        $statistics['empty']++;

                        continue;
                    }

                    $nip = $values[$headerIndexes['nip']] ?? '';
                    $nama = $values[$headerIndexes['nama']] ?? '';
                    $unit = $values[$headerIndexes['instansi_unit']] ?? '';
                    $phone = $values[$headerIndexes['no_hp']] ?? '';

                    if ($nip === '' || $nama === '' || mb_strlen($nip) > 30 || mb_strlen($nama) > 255
                        || mb_strlen($unit) > 255 || mb_strlen($phone) > 20) {
                        $statistics['invalid']++;

                        continue;
                    }

                    $key = mb_strtolower($nip);
                    if (isset($seen[$key])) {
                        $statistics['duplicates']++;

                        continue;
                    }

                    $seen[$key] = true;
                    $members[] = [
                        'nip' => $nip,
                        'nama' => $nama,
                        'instansi_unit' => $unit !== '' ? $unit : null,
                        'no_hp' => $phone !== '' ? $phone : null,
                    ];
                }

                break;
            }
        } finally {
            $reader->close();
        }

        if (! $sheetFound) {
            throw new \RuntimeException("Sheet '{$sheetName}' tidak ditemukan.");
        }

        if ($headerIndexes === null) {
            throw new \RuntimeException('Header workbook tidak ditemukan.');
        }

        return [$members, $statistics];
    }

    /** @param list<string> $headers
     * @return array<string, int>
     */
    private function headerIndexes(array $headers): array
    {
        $normalized = array_map(fn (string $header): string => mb_strtolower($this->clean($header)), $headers);
        $indexes = [];

        foreach (self::HEADERS as $field => $label) {
            $index = array_search(mb_strtolower($label), $normalized, true);
            if ($index === false) {
                throw new \RuntimeException("Kolom '{$label}' tidak ditemukan.");
            }
            $indexes[$field] = $index;
        }

        return $indexes;
    }

    private function clean(mixed $value): string
    {
        if ($value instanceof \DateTimeInterface) {
            return $value->format('Y-m-d');
        }

        return trim((string) preg_replace('/\s+/u', ' ', (string) ($value ?? '')));
    }
}
