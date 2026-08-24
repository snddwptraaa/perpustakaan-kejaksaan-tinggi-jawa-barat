<?php

use App\Models\Visitor;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('visitors:prune', function (): int {
    $days = (int) config('privacy.visitor_retention_days');

    if ($days < 1) {
        $this->warn('Penghapusan dilewati: VISITOR_RETENTION_DAYS belum dikonfigurasi.');

        return self::SUCCESS;
    }

    $deleted = Visitor::query()->where('created_at', '<', now()->subDays($days))->delete();
    $this->info("{$deleted} data pengunjung di luar masa retensi dihapus.");

    return self::SUCCESS;
})->purpose('Delete visitor records older than the approved retention period');

Schedule::command('visitors:prune')->dailyAt('01:30')->withoutOverlapping();
