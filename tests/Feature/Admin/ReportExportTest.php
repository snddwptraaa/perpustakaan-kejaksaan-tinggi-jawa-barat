<?php

namespace Tests\Feature\Admin;

use App\Livewire\Admin\VisitorReport;
use App\Models\User;
use App\Models\Visitor;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class ReportExportTest extends TestCase
{
    use RefreshDatabase;

    public function test_visitor_xlsx_export_uses_active_filters(): void
    {
        $user = User::factory()->create(['role' => 'admin']);
        Visitor::create([
            'kategori' => 'pegawai',
            'nama' => 'Sari Anggraini',
            'nip' => '001234567890',
            'instansi_unit' => 'Bagian Hukum',
            'no_hp' => '081200000001',
            'keperluan' => 'Cari referensi',
            'consent_privacy_at' => now(),
        ]);
        Visitor::create([
            'kategori' => 'umum',
            'nama' => 'Budi Santoso',
            'instansi_unit' => 'Bagian Umum',
            'keperluan' => 'Baca di tempat',
            'consent_privacy_at' => now(),
        ]);

        $component = Livewire::actingAs($user)
            ->test(VisitorReport::class)
            ->set('search', 'Hukum')
            ->set('kategori', 'pegawai');

        $component->call('exportXlsx')->assertFileDownloaded();
    }

    public function test_print_report_command_creates_html_report(): void
    {
        $path = storage_path('framework/testing-report.html');

        $this->artisan('reports:print', ['type' => 'visitors', '--path' => $path])
            ->assertSuccessful();

        $this->assertFileExists($path);
        $this->assertStringContainsString('Laporan Pengunjung', file_get_contents($path));
        @unlink($path);
    }
}
