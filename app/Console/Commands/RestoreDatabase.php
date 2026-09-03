<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Process;

class RestoreDatabase extends Command
{
    protected $signature = 'database:restore {file : File SQL backup} {--force : Lewati konfirmasi}';

    protected $description = 'Memulihkan database MySQL dari backup SQL yang tersimpan di storage/app/backups.';

    public function handle(): int
    {
        $file = $this->resolveBackupPath((string) $this->argument('file'));
        if ($file === null) {
            $this->error('File harus berupa SQL yang dapat dibaca di dalam storage/app/backups.');

            return self::FAILURE;
        }

        if (! $this->option('force') && ! $this->confirm('Ini akan menimpa data database. Lanjutkan?', false)) {
            return self::FAILURE;
        }

        $config = config('database.connections.'.config('database.default'));
        if (($config['driver'] ?? null) !== 'mysql') {
            $this->error('Restore command ini memerlukan koneksi MySQL.');

            return self::FAILURE;
        }

        $handle = fopen($file, 'rb');
        if ($handle === false) {
            $this->error('File backup tidak dapat dibuka.');

            return self::FAILURE;
        }

        try {
            $result = Process::env(['MYSQL_PWD' => (string) ($config['password'] ?? '')])
                ->input($handle)
                ->run(['mysql', '--host='.$config['host'], '--port='.$config['port'], '--user='.$config['username'], $config['database']]);
        } finally {
            fclose($handle);
        }

        if ($result->failed()) {
            $this->error('Restore gagal: '.$result->errorOutput());

            return self::FAILURE;
        }

        $this->info('Database berhasil dipulihkan.');

        return self::SUCCESS;
    }

    private function resolveBackupPath(string $path): ?string
    {
        $root = realpath(storage_path('app/backups'));
        if ($root === false || ! str_ends_with(strtolower($path), '.sql')) {
            return null;
        }

        $candidate = str_starts_with($path, DIRECTORY_SEPARATOR) ? $path : $root.DIRECTORY_SEPARATOR.$path;
        $resolved = realpath($candidate);
        if ($resolved === false || ! is_file($resolved) || ! is_readable($resolved)) {
            return null;
        }

        return str_starts_with($resolved, rtrim($root, DIRECTORY_SEPARATOR).DIRECTORY_SEPARATOR) ? $resolved : null;
    }
}
