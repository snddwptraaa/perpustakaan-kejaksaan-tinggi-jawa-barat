<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    protected function setUp(): void
    {
        // Guard insiden: saat bootstrap/cache/config.php ada, env dari phpunit.xml
        // (DB_CONNECTION=sqlite, DB_DATABASE=:memory:) diabaikan dan test suite
        // berjalan langsung di database MySQL .env — RefreshDatabase lalu
        // melakukan migrate:fresh pada data asli. Lihat insiden 2026-09-08.
        if (is_file(dirname(__DIR__).'/bootstrap/cache/config.php')) {
            self::fail(
                'bootstrap/cache/config.php masih ada. Config cache menimpa env testing '.PHP_EOL.
                'dan membuat test berjalan di database produksi. Jalankan: php artisan config:clear'
            );
        }

        parent::setUp();
    }
}
