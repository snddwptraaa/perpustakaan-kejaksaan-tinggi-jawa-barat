<?php

namespace Tests\Feature\Admin;

use App\Livewire\Admin\CategoryManager;
use App\Models\Book;
use App\Models\Category;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class CategoryManagerTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_create_category_with_generated_slug(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        Livewire::actingAs($admin)->test(CategoryManager::class)
            ->call('create')
            ->set('nama_kategori', 'Hukum Tata Negara')
            ->call('save')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('categories', ['nama_kategori' => 'Hukum Tata Negara', 'slug' => 'hukum-tata-negara']);
    }

    public function test_used_category_cannot_be_deleted(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $category = Category::create(['nama_kategori' => 'Pidana']);
        Book::create([
            'category_id' => $category->id,
            'judul' => 'KUHP',
            'penulis' => 'Penyusun',
            'stok' => 1,
            'stok_tersedia' => 1,
        ]);

        Livewire::actingAs($admin)->test(CategoryManager::class)
            ->call('delete', $category->id)
            ->assertSee('tidak dapat dihapus');

        $this->assertDatabaseHas('categories', ['id' => $category->id]);
    }

    public function test_categories_can_be_sorted_by_book_count_and_link_to_filtered_books(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $emptyCategory = Category::create(['nama_kategori' => 'Kategori Kosong']);
        $popularCategory = Category::create(['nama_kategori' => 'Kategori Populer']);

        foreach (['Buku Pertama', 'Buku Kedua'] as $title) {
            Book::create([
                'category_id' => $popularCategory->id,
                'judul' => $title,
                'penulis' => 'Penulis',
                'stok' => 1,
                'stok_tersedia' => 1,
            ]);
        }

        Livewire::actingAs($admin)
            ->test(CategoryManager::class)
            ->set('sort', 'books_desc')
            ->assertSeeInOrder(['Kategori Populer', 'Kategori Kosong'])
            ->assertSee(route('admin.books', ['category' => $popularCategory->id]), false)
            ->call('resetFilters')
            ->assertSet('search', '')
            ->assertSet('sort', 'name_asc');

        $this->actingAs($admin)
            ->get(route('admin.categories'))
            ->assertOk()
            ->assertSee('Lihat buku');
    }
}
