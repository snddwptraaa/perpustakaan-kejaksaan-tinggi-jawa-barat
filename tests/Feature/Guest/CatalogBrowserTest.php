<?php

namespace Tests\Feature\Guest;

use App\Livewire\Guest\CatalogBrowser;
use App\Models\Book;
use App\Models\Category;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class CatalogBrowserTest extends TestCase
{
    use RefreshDatabase;

    public function test_catalog_search_and_availability_filters_work(): void
    {
        $category = Category::create(['nama_kategori' => 'Pidana']);
        Book::create(['category_id' => $category->id, 'judul' => 'KUHP', 'penulis' => 'A', 'stok' => 1, 'stok_tersedia' => 1]);
        Book::create(['category_id' => $category->id, 'judul' => 'KUHAP', 'penulis' => 'B', 'stok' => 1, 'stok_tersedia' => 0]);

        Livewire::withQueryParams([])->test(CatalogBrowser::class)
            ->set('search', 'KUHP')
            ->assertSee('KUHP')
            ->assertDontSee('KUHAP')
            ->set('search', '')
            ->set('availability', 'unavailable')
            ->assertSee('KUHAP')
            ->assertDontSee('KUHP');
    }

    public function test_reset_filters_restores_catalog_and_first_page(): void
    {
        $category = Category::create(['nama_kategori' => 'Pidana']);
        for ($index = 1; $index <= 13; $index++) {
            Book::create([
                'category_id' => $category->id,
                'judul' => sprintf('Referensi %02d', $index),
                'penulis' => 'Penulis',
                'stok' => 1,
                'stok_tersedia' => 1,
            ]);
        }

        Livewire::test(CatalogBrowser::class)
            ->set('search', 'Referensi')
            ->set('category', (string) $category->id)
            ->set('availability', 'available')
            ->call('setPage', 2)
            ->assertSee('Referensi 13')
            ->assertDontSee('Referensi 01')
            ->call('resetFilters')
            ->assertSet('search', '')
            ->assertSet('category', '')
            ->assertSet('availability', '')
            ->assertSet('paginators.page', 1)
            ->assertSee('Referensi 01')
            ->assertDontSee('Referensi 13');
    }

    public function test_catalog_page_has_no_navbar_and_has_exit_button_when_checked_in(): void
    {
        $response = $this->withSession([
            'visitor_checked_in' => true,
            'visitor_checked_in_at' => now()->toDateString(),
        ])->get(route('katalog'));

        $response->assertOk()
            ->assertDontSee('public-header', false)
            ->assertDontSee('#layanan')
            ->assertDontSee('#tentang')
            ->assertSee('Kejaksaan Tinggi Jawa Barat')
            ->assertSee(route('kunjungan.selesai'))
            ->assertSee('Selesai kunjungan');
    }

    public function test_visitor_can_exit_and_reset_session(): void
    {
        $response = $this->withSession([
            'visitor_checked_in' => true,
            'visitor_id' => 123,
            'visitor_checked_in_at' => now()->toDateString(),
        ])->post(route('kunjungan.selesai'));

        $response->assertRedirect(route('kunjungan'));
        $this->assertFalse(session()->has('visitor_checked_in'));
        $this->assertFalse(session()->has('visitor_id'));
        $this->assertFalse(session()->has('visitor_checked_in_at'));

        // After exit, visiting catalog should be blocked by middleware
        $this->get(route('katalog'))
            ->assertRedirect(route('kunjungan'));
    }

    public function test_book_detail_matches_catalog_shell_and_surfaces_shelf_information(): void
    {
        $category = Category::create(['nama_kategori' => 'Hukum Tata Negara']);
        $book = Book::create([
            'category_id' => $category->id,
            'judul' => 'Pengantar Hukum Indonesia',
            'penulis' => 'Rahmat Hidayat',
            'penerbit' => 'Pustaka Hukum',
            'tahun_terbit' => 2025,
            'jumlah_halaman' => 320,
            'isbn' => '978-602-0000-00-1',
            'no_klasifikasi' => '340 HID p',
            'lokasi_rak' => 'A-03',
            'stok' => 3,
            'stok_tersedia' => 2,
        ]);

        $response = $this->withSession([
            'visitor_checked_in' => true,
            'visitor_checked_in_at' => now()->toDateString(),
        ])->get(route('buku.detail', $book));

        $response->assertOk()
            ->assertSee('catalog-page', false)
            ->assertSee('catalog-masthead', false)
            ->assertSee('Pengantar Hukum Indonesia')
            ->assertSee('Rak A-03')
            ->assertSee('340 HID p')
            ->assertSeeText('2 dari 3')
            ->assertSee('Detail koleksi')
            ->assertSee('Selesai kunjungan');
    }
}
