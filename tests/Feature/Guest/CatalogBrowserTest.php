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
}
