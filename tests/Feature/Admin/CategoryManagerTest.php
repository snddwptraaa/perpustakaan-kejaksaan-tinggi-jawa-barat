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
}
