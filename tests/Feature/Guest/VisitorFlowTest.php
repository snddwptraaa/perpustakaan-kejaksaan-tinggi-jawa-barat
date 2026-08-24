<?php

namespace Tests\Feature\Guest;

use App\Livewire\Guest\VisitorForm;
use App\Models\Visitor;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\RateLimiter;
use Livewire\Livewire;
use Tests\TestCase;

class VisitorFlowTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        RateLimiter::clear('visitor-form:127.0.0.1');
    }

    public function test_visitor_must_accept_privacy_notice(): void
    {
        Livewire::test(VisitorForm::class)
            ->set('nama', 'Budi Santoso')
            ->set('instansi_unit', 'Universitas Padjadjaran')
            ->set('keperluan', 'Riset Skripsi / Tesis / Penelitian')
            ->call('submit')
            ->assertHasErrors(['accepted_privacy']);

        $this->assertDatabaseCount('visitors', 0);
    }

    public function test_visitor_can_check_in_and_open_catalog(): void
    {
        Livewire::test(VisitorForm::class)
            ->set('nama', 'Budi Santoso')
            ->set('instansi_unit', 'Universitas Padjadjaran')
            ->set('keperluan', 'Riset Skripsi / Tesis / Penelitian')
            ->set('accepted_privacy', true)
            ->call('submit')
            ->assertHasNoErrors()
            ->assertRedirect(route('katalog'));

        $this->assertNotNull(Visitor::firstOrFail()->privacy_consented_at);
        $this->assertTrue(session('visitor_checked_in'));
    }

    public function test_catalog_requires_a_same_day_check_in(): void
    {
        $this->get(route('katalog'))->assertRedirect(route('kunjungan'));
        $this->withSession(['visitor_checked_in' => true, 'visitor_checked_in_at' => now()->subDay()->toDateString()])
            ->get(route('katalog'))->assertRedirect(route('kunjungan'));
        $this->withSession(['visitor_checked_in' => true, 'visitor_checked_in_at' => now()->toDateString()])
            ->get(route('katalog'))->assertOk();
    }

    public function test_visitor_form_is_rate_limited(): void
    {
        for ($attempt = 0; $attempt < 8; $attempt++) {
            RateLimiter::hit('visitor-form:127.0.0.1', 60);
        }

        Livewire::test(VisitorForm::class)
            ->set('nama', 'Budi Santoso')
            ->set('instansi_unit', 'Universitas Padjadjaran')
            ->set('keperluan', 'Membaca di Tempat')
            ->set('accepted_privacy', true)
            ->call('submit')
            ->assertHasErrors(['nama']);
    }
}
