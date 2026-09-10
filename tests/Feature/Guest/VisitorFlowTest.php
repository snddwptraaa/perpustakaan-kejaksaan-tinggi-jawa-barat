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

    public function test_visitor_form_uses_a_focused_kiosk_layout(): void
    {
        $this->get(route('kunjungan'))
            ->assertOk()
            ->assertSee('Perpustakaan Kejati Jawa Barat')
            ->assertDontSee('<nav', false)
            ->assertDontSee('<footer', false);
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

    public function test_failed_submissions_also_count_toward_rate_limit(): void
    {
        // Setiap percobaan tidak valid tetap dihitung, supaya form buku tamu tidak bisa
        // di-spam entri invalid tanpa batas sebelum throttle aktif.
        for ($attempt = 0; $attempt < 8; $attempt++) {
            Livewire::test(VisitorForm::class)
                ->set('accepted_privacy', true)
                ->call('submit')
                ->assertHasErrors(['nama']);
        }

        // Setelah 8 percobaan invalid, percobaan valid sekarang ditolak throttle.
        Livewire::test(VisitorForm::class)
            ->set('nama', 'Budi Santoso')
            ->set('instansi_unit', 'Universitas Padjadjaran')
            ->set('keperluan', 'Membaca di Tempat')
            ->set('accepted_privacy', true)
            ->call('submit')
            ->assertHasErrors(['nama']);

        $this->assertDatabaseCount('visitors', 0);
    }

    public function test_keperluan_must_be_one_of_the_listed_options(): void
    {
        RateLimiter::clear('visitor-form:127.0.0.1');

        // Arbitrary / tampered payload untuk kategori umum.
        Livewire::test(VisitorForm::class)
            ->set('kategori', 'umum')
            ->set('nama', 'Attacker')
            ->set('instansi_unit', 'Script')
            ->set('keperluan', 'Lorem ipsum bukan dari opsi')
            ->set('accepted_privacy', true)
            ->call('submit')
            ->assertHasErrors(['keperluan']);

        // Opsi khusus pegawai tidak bisa dipakai saat kategori umum.
        Livewire::test(VisitorForm::class)
            ->set('kategori', 'umum')
            ->set('nama', 'Attacker')
            ->set('instansi_unit', 'Script')
            ->set('keperluan', 'Penyusunan Berkas / Dakwaan / Tuntutan')
            ->set('accepted_privacy', true)
            ->call('submit')
            ->assertHasErrors(['keperluan']);

        RateLimiter::clear('visitor-form:127.0.0.1');
        $this->assertDatabaseCount('visitors', 0);
    }

    public function test_visitor_cannot_submit_with_empty_required_fields(): void
    {
        Livewire::test(VisitorForm::class)
            ->set('accepted_privacy', true)
            ->call('submit')
            ->assertHasErrors(['nama', 'instansi_unit', 'keperluan']);

        $this->assertDatabaseCount('visitors', 0);
    }

    public function test_pegawai_requires_valid_nip(): void
    {
        Livewire::test(VisitorForm::class)
            ->call('setKategori', 'pegawai')
            ->set('nama', 'Jaksa Pratama')
            ->set('instansi_unit', 'Tindak Pidana Khusus')
            ->set('keperluan', 'Penyusunan Berkas / Dakwaan / Tuntutan')
            ->set('accepted_privacy', true)
            ->call('submit')
            ->assertHasErrors(['nip']);

        Livewire::test(VisitorForm::class)
            ->call('setKategori', 'pegawai')
            ->set('nama', 'Jaksa Pratama')
            ->set('nip', '198501012010121001')
            ->set('instansi_unit', 'Tindak Pidana Khusus')
            ->set('keperluan', 'Penyusunan Berkas / Dakwaan / Tuntutan')
            ->set('accepted_privacy', true)
            ->call('submit')
            ->assertHasNoErrors()
            ->assertRedirect(route('katalog'));
    }

    public function test_realtime_validation_triggers_when_property_is_updated(): void
    {
        Livewire::test(VisitorForm::class)
            ->set('nama', '')
            ->assertHasErrors(['nama'])
            ->set('nama', 'Ahmad Dahlan')
            ->assertHasNoErrors(['nama']);
    }

    public function test_form_shows_notice_when_visitor_already_checked_in_today(): void
    {
        $this->withSession([
            'visitor_checked_in' => true,
            'visitor_checked_in_at' => now()->toDateString(),
        ])
            ->get(route('kunjungan'))
            ->assertOk()
            ->assertSee('Anda sudah check-in hari ini')
            ->assertSee('Buka Katalog');
    }

    public function test_form_renders_custom_select_dropdown_components(): void
    {
        $this->get(route('kunjungan'))
            ->assertOk()
            ->assertSee('-- Pilih Keperluan Kunjungan --')
            ->assertSee('custom-select')
            ->assertSee('Ketik untuk mencari…')
            ->assertSee('Mencari Referensi Hukum');

        Livewire::test(VisitorForm::class)
            ->call('setKategori', 'pegawai')
            ->assertSee('-- Pilih Unit Kerja / Bidang --')
            ->assertSeeInOrder([
                '1. Pembinaan',
                '2. Intelijen',
                '3. Pidana Umum',
                '4. Pidana Khusus',
                '5. Perdata dan Tata Usaha Negara',
                '6. Pidana Militer',
                '7. Pengawasan',
                '8. Pemulihan Aset',
                '9. Bagian Tata Usaha',
            ]);
    }

    public function test_pegawai_unit_must_come_from_the_ordered_unit_list(): void
    {
        Livewire::test(VisitorForm::class)
            ->call('setKategori', 'pegawai')
            ->set('nama', 'Jaksa Penguji')
            ->set('nip', '198501012010121001')
            ->set('instansi_unit', 'Unit yang tidak tersedia')
            ->set('keperluan', 'Mencari Referensi Tugas Kedinasan')
            ->set('accepted_privacy', true)
            ->call('submit')
            ->assertHasErrors(['instansi_unit' => 'in']);
    }
}
