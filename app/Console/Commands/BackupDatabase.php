<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Process;
use RuntimeException;

class BackupDatabase extends Command
{
    protected $signature = 'database:backup {--path= : Direktori tujuan backup}';

    protected $description = 'Membuat backup database MySQL melalui mysqldump.';

    public function handle(): int
    {
        $directory = $this->option('path') ?: storage_path('app/backups');
        if (! is_dir($directory) && ! mkdir($directory, 0750, true) && ! is_dir($directory)) {
            throw new RuntimeException('Direktori backup tidak dapat dibuat.');
        }

        $config = config('database.connections.'.config('database.default'));
        if (($config['driver'] ?? null) !== 'mysql') {
            $this->error('Backup command ini memerlukan koneksi MySQL.');

            return self::FAILURE;
        }

        $filename = rtrim($directory, DIRECTORY_SEPARATOR).DIRECTORY_SEPARATOR.'perpus-'.now()->format('Y-m-d_H-i-s').'.sql';
        $result = Process::env(['MYSQL_PWD' => (string) ($config['password'] ?? '')])->run([
            'mysqldump', '--single-transaction', '--host='.$config['host'], '--port='.$config['port'],
            '--user='.$config['username'], '--result-file='.$filename, $config['database'],
        ]);
        if ($result->failed()) {
            @unlink($filename);
            $this->error('mysqldump gagal: '.$result->errorOutput());

            return self::FAILURE;
        }

        chmod($filename, 0600);
        $this->info("Backup dibuat: {$filename}");

        return self::SUCCESS;
    }
}
