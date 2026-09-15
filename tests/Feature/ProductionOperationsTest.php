<?php

namespace Tests\Feature;

use App\Models\Visitor;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class ProductionOperationsTest extends TestCase
{
    use RefreshDatabase;

    public function test_iis_front_controller_configuration_is_present(): void
    {
        $configuration = file_get_contents(public_path('web.config'));

        $this->assertIsString($configuration);
        $this->assertStringContainsString('<rewrite>', $configuration);
        $this->assertStringContainsString('url="index.php"', $configuration);
        $this->assertStringContainsString('existingResponse="PassThrough"', $configuration);
    }

    public function test_https_responses_include_security_headers(): void
    {
        $response = $this
            ->withServerVariables(['HTTPS' => 'on', 'SERVER_PORT' => 443])
            ->get('https://localhost/');

        $response
            ->assertOk()
            ->assertHeader('X-Content-Type-Options', 'nosniff')
            ->assertHeader('X-Frame-Options', 'SAMEORIGIN')
            ->assertHeader('Referrer-Policy', 'strict-origin-when-cross-origin')
            ->assertHeader('Strict-Transport-Security', 'max-age=31536000; includeSubDomains');

        $this->assertStringContainsString(
            "default-src 'self'",
            (string) $response->headers->get('Content-Security-Policy')
        );
    }

    public function test_readiness_endpoint_checks_database_and_storage(): void
    {
        $this->getJson('/ready')
            ->assertOk()
            ->assertExactJson(['status' => 'ready']);
    }

    public function test_readiness_endpoint_fails_when_database_is_unavailable(): void
    {
        $originalConnection = config('database.default');

        config([
            'database.default' => 'unavailable',
            'database.connections.unavailable' => [
                'driver' => 'sqlite',
                'database' => '/missing-directory/database.sqlite',
                'prefix' => '',
            ],
        ]);
        DB::purge('unavailable');

        try {
            $this->getJson('/ready')
                ->assertStatus(503)
                ->assertExactJson(['status' => 'not_ready']);
        } finally {
            config(['database.default' => $originalConnection]);
            DB::purge('unavailable');
        }
    }

    public function test_visitor_pruning_deletes_only_records_outside_retention_period(): void
    {
        config(['privacy.visitor_retention_days' => 30]);

        $expired = $this->createVisitor('Pengunjung Lama');
        $current = $this->createVisitor('Pengunjung Baru');

        Visitor::query()->whereKey($expired)->update(['created_at' => now()->subDays(31)]);
        Visitor::query()->whereKey($current)->update(['created_at' => now()->subDays(29)]);

        $this->assertSame(0, Artisan::call('visitors:prune'));
        $this->assertDatabaseMissing('visitors', ['id' => $expired]);
        $this->assertDatabaseHas('visitors', ['id' => $current]);
    }

    public function test_visitor_pruning_is_disabled_without_an_approved_period(): void
    {
        config(['privacy.visitor_retention_days' => 0]);

        $visitor = $this->createVisitor('Pengunjung Dipertahankan');
        Visitor::query()->whereKey($visitor)->update(['created_at' => now()->subYears(2)]);

        $this->assertSame(0, Artisan::call('visitors:prune'));
        $this->assertDatabaseHas('visitors', ['id' => $visitor]);
    }

    public function test_production_check_rejects_unsafe_environment(): void
    {
        $exitCode = Artisan::call('production:check');

        $this->assertSame(1, $exitCode);
        $this->assertStringContainsString('Deployment harus dihentikan', Artisan::output());
    }

    private function createVisitor(string $name): int
    {
        return Visitor::query()->create([
            'kategori' => 'umum',
            'nama' => $name,
            'instansi_unit' => 'Instansi Pengujian',
            'keperluan' => 'Membaca di Tempat',
            'privacy_consented_at' => now(),
        ])->getKey();
    }
}
