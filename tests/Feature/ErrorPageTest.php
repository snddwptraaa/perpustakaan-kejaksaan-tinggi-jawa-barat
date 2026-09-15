<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\Route;
use Tests\TestCase;

class ErrorPageTest extends TestCase
{
    public function test_missing_page_uses_the_library_error_design(): void
    {
        $this->get('/halaman-yang-tidak-tersedia')
            ->assertNotFound()
            ->assertSeeText('404')
            ->assertSeeText('Halaman tidak ditemukan')
            ->assertSeeText('Kembali ke beranda')
            ->assertSee('images/logo.svg', false)
            ->assertSee('favicon.ico', false)
            ->assertDontSeeText('Laravel');
    }

    public function test_common_http_errors_use_the_library_error_design(): void
    {
        Route::get('/__test/error/{status}', fn (int $status) => abort($status));

        $errors = [
            403 => 'Akses tidak tersedia',
            419 => 'Sesi telah berakhir',
            429 => 'Terlalu banyak permintaan',
            500 => 'Layanan mengalami kendala',
            503 => 'Layanan sedang dirawat',
        ];

        foreach ($errors as $status => $title) {
            $this->get("/__test/error/{$status}")
                ->assertStatus($status)
                ->assertSeeText((string) $status)
                ->assertSeeText($title)
                ->assertSeeText('Kembali ke beranda')
                ->assertDontSeeText('Laravel');
        }
    }

    public function test_favicon_assets_are_present_and_not_empty(): void
    {
        $this->assertFileExists(public_path('favicon.ico'));
        $this->assertFileExists(public_path('apple-touch-icon.png'));
        $this->assertGreaterThan(0, filesize(public_path('favicon.ico')));
        $this->assertGreaterThan(0, filesize(public_path('apple-touch-icon.png')));
    }
}
