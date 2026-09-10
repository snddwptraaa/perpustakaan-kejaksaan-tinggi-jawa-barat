<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Database\Migrations\Migrator;
use Illuminate\Support\Facades\DB;
use Throwable;

class CheckProductionReadiness extends Command
{
    protected $signature = 'production:check';

    protected $description = 'Memeriksa konfigurasi, database, migrasi, dan storage sebelum aplikasi go-live.';

    public function handle(Migrator $migrator): int
    {
        $retentionDays = (int) config('privacy.visitor_retention_days');
        $indefiniteRetentionAccepted = (bool) config('privacy.indefinite_retention_accepted');
        $databaseDriver = (string) config('database.default');
        $mailDriver = (string) config('mail.default');
        $mailHost = (string) config("mail.mailers.{$mailDriver}.host");
        $mailFrom = (string) config('mail.from.address');

        $checks = [
            $this->check('Environment', app()->environment('production'), 'APP_ENV harus production.'),
            $this->check('Debug mode', config('app.debug') === false, 'APP_DEBUG harus false.'),
            $this->check('Application key', filled(config('app.key')), 'APP_KEY belum diisi.'),
            $this->check('HTTPS URL', parse_url((string) config('app.url'), PHP_URL_SCHEME) === 'https', 'APP_URL harus memakai HTTPS.'),
            $this->check('Timezone', config('app.timezone') === 'Asia/Jakarta', 'APP_TIMEZONE harus Asia/Jakarta.'),
            $this->check('Locale', config('app.locale') === 'id', 'APP_LOCALE harus id.'),
            $this->check('Encrypted session', config('session.encrypt') === true, 'SESSION_ENCRYPT harus true.'),
            $this->check('Secure cookie', config('session.secure') === true, 'SESSION_SECURE_COOKIE harus true.'),
            $this->check('Log level', ! in_array(config('logging.channels.single.level'), ['debug'], true), 'LOG_LEVEL tidak boleh debug.'),
            $this->check('Session store', config('session.driver') !== 'array', 'SESSION_DRIVER tidak boleh array.'),
            $this->check('Cache store', ! in_array(config('cache.default'), ['array', 'null'], true), 'CACHE_STORE harus persisten.'),
            $this->check(
                'Mail transport',
                $mailDriver === 'smtp' && filled($mailHost) && filter_var($mailFrom, FILTER_VALIDATE_EMAIL) !== false,
                'Konfigurasikan MAIL_MAILER=smtp, MAIL_HOST, dan MAIL_FROM_ADDRESS yang valid.'
            ),
            $this->check('Demo seeding', config('seeding.demo_data') === false, 'SEED_DEMO_DATA harus false.'),
            $this->check(
                'Visitor retention',
                $retentionDays > 0 || $indefiniteRetentionAccepted,
                'Tetapkan VISITOR_RETENTION_DAYS atau akui kebijakan tanpa batas secara eksplisit.'
            ),
            $this->check('Database driver', in_array($databaseDriver, ['mysql', 'mariadb'], true), 'DB_CONNECTION production harus mysql atau mariadb.'),
            $this->check('Storage permissions', is_writable(storage_path()) && is_writable(base_path('bootstrap/cache')), 'storage dan bootstrap/cache harus writable.'),
        ];

        try {
            DB::select('select 1');
            $checks[] = $this->check('Database connection', true, '');
        } catch (Throwable $exception) {
            $checks[] = $this->check('Database connection', false, $this->safeExceptionMessage($exception));
        }

        try {
            $migrationFiles = array_keys($migrator->getMigrationFiles(database_path('migrations')));
            $ranMigrations = $migrator->repositoryExists() ? $migrator->getRepository()->getRan() : [];
            $pendingMigrations = array_values(array_diff($migrationFiles, $ranMigrations));

            $checks[] = $this->check(
                'Database migrations',
                $migrator->repositoryExists() && $pendingMigrations === [],
                $migrator->repositoryExists()
                    ? count($pendingMigrations).' migrasi belum dijalankan.'
                    : 'Tabel migrations belum tersedia.'
            );
        } catch (Throwable $exception) {
            $checks[] = $this->check('Database migrations', false, $this->safeExceptionMessage($exception));
        }

        $this->table(
            ['Pemeriksaan', 'Status', 'Keterangan'],
            array_map(fn (array $check): array => [
                $check['label'],
                $check['passed'] ? 'PASS' : 'FAIL',
                $check['passed'] ? '-' : $check['message'],
            ], $checks)
        );

        $failed = array_filter($checks, fn (array $check): bool => ! $check['passed']);

        if ($failed !== []) {
            $this->error(count($failed).' pemeriksaan gagal. Deployment harus dihentikan.');

            return self::FAILURE;
        }

        $this->info('Semua pemeriksaan production lulus.');

        return self::SUCCESS;
    }

    /**
     * @return array{label: string, passed: bool, message: string}
     */
    private function check(string $label, bool $passed, string $message): array
    {
        return compact('label', 'passed', 'message');
    }

    private function safeExceptionMessage(Throwable $exception): string
    {
        return class_basename($exception).': koneksi atau pemeriksaan gagal; lihat log server untuk detail.';
    }
}
