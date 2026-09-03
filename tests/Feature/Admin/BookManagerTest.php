<?php

namespace Tests\Feature\Admin;

use App\Livewire\Admin\BookManager;
use App\Models\Book;
use App\Models\Category;
use App\Models\Loan;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Tests\TestCase;

class BookManagerTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;

    protected Category $category;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->create([
            'role' => 'admin',
        ]);

        $this->category = Category::create([
            'nama_kategori' => 'Hukum Pidana',
            'slug' => 'hukum-pidana',
        ]);
    }

    public function test_admin_can_access_books_page(): void
    {
        $response = $this->actingAs($this->admin)->get(route('admin.books'));

        $response->assertStatus(200);
        $response->assertSee('Koleksi Buku');
        $response->assertSee('Tambah Buku Baru');
    }

    public function test_admin_can_open_create_modal(): void
    {
        Livewire::actingAs($this->admin)
            ->test(BookManager::class)
            ->assertSet('showForm', false)
            ->call('create')
            ->assertSet('showForm', true)
            ->assertSet('editingId', null)
            ->assertSet('judul', '');
    }

    public function test_admin_can_create_book_without_cover(): void
    {
        Livewire::actingAs($this->admin)
            ->test(BookManager::class)
            ->call('create')
            ->set('category_id', (string) $this->category->id)
            ->set('judul', 'Hukum Acara Pidana Indonesia')
            ->set('penulis', 'Prof. Dr. Andi Hamzah')
            ->set('penerbit', 'Sinar Grafika')
            ->set('tahun_terbit', 2023)
            ->set('jumlah_halaman', '450 hlm')
            ->set('isbn', '978-602-1234-56-7')
            ->set('no_klasifikasi', '345.02 HAM h')
            ->set('lokasi_rak', 'Rak Pidana Lt. 2 - A3')
            ->set('stok', 5)
            ->set('stok_tersedia', 5)
            ->set('deskripsi', 'Buku referensi lengkap mengenai prosedur dan hukum acara pidana.')
            ->call('save')
            ->assertHasNoErrors()
            ->assertSet('showForm', false);

        $this->assertDatabaseHas('books', [
            'category_id' => $this->category->id,
            'judul' => 'Hukum Acara Pidana Indonesia',
            'penulis' => 'Prof. Dr. Andi Hamzah',
            'jumlah_halaman' => '450 hlm',
            'no_klasifikasi' => '345.02 HAM h',
            'lokasi_rak' => 'Rak Pidana Lt. 2 - A3',
            'stok' => 5,
            'stok_tersedia' => 5,
            'cover_image' => null,
        ]);
    }

    public function test_admin_can_create_book_with_cover_upload(): void
    {
        Storage::fake('public');

        $file = UploadedFile::fake()->createWithContent(
            'cover_buku.png',
            base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mNk+A8AAQUBAScY42YAAAAASUVORK5CYII=', true)
        );

        Livewire::actingAs($this->admin)
            ->test(BookManager::class)
            ->call('create')
            ->set('category_id', (string) $this->category->id)
            ->set('judul', 'Kapita Selekta Hukum Pidana')
            ->set('penulis', 'Dr. Barda Nawawi Arief')
            ->set('stok', 3)
            ->set('stok_tersedia', 3)
            ->set('cover', $file)
            ->call('save')
            ->assertHasNoErrors();

        $book = Book::where('judul', 'Kapita Selekta Hukum Pidana')->first();
        $this->assertNotNull($book);
        $this->assertNotNull($book->cover_image);
        Storage::disk('public')->assertExists($book->cover_image);
    }

    public function test_admin_can_edit_book_data(): void
    {
        $book = Book::create([
            'category_id' => $this->category->id,
            'judul' => 'Buku Lama',
            'penulis' => 'Penulis Lama',
            'stok' => 2,
            'stok_tersedia' => 2,
        ]);

        Livewire::actingAs($this->admin)
            ->test(BookManager::class)
            ->call('edit', $book->id)
            ->assertSet('showForm', true)
            ->assertSet('editingId', $book->id)
            ->assertSet('judul', 'Buku Lama')
            ->set('judul', 'Buku Telah Diupdate')
            ->set('no_klasifikasi', '345.01 BUK b')
            ->call('save')
            ->assertHasNoErrors()
            ->assertSet('showForm', false);

        $this->assertDatabaseHas('books', [
            'id' => $book->id,
            'judul' => 'Buku Telah Diupdate',
            'no_klasifikasi' => '345.01 BUK b',
        ]);
    }

    public function test_validation_rules_for_creating_book(): void
    {
        Livewire::actingAs($this->admin)
            ->test(BookManager::class)
            ->call('create')
            ->set('category_id', '')
            ->set('judul', '')
            ->set('penulis', '')
            ->set('stok', -1)
            ->set('stok_tersedia', 5)
            ->call('save')
            ->assertHasErrors(['category_id', 'judul', 'penulis', 'stok', 'stok_tersedia']);
    }

    public function test_admin_can_edit_imported_book_with_long_title(): void
    {
        $book = Book::create([
            'category_id' => $this->category->id,
            'judul' => str_repeat('Judul panjang ', 30),
            'penulis' => 'Penulis',
            'stok' => 1,
            'stok_tersedia' => 1,
        ]);

        Livewire::actingAs($this->admin)
            ->test(BookManager::class)
            ->call('edit', $book->id)
            ->set('lokasi_rak', 'Rak A1')
            ->call('save')
            ->assertHasNoErrors('judul');

        $this->assertSame('Rak A1', $book->fresh()->lokasi_rak);
    }

    public function test_book_with_completed_loan_history_and_cover_cannot_be_deleted(): void
    {
        Storage::fake('public');
        Storage::disk('public')->put('covers/history.jpg', 'image');

        $book = Book::create([
            'category_id' => $this->category->id,
            'judul' => 'Arsip Buku',
            'penulis' => 'Penulis',
            'stok' => 1,
            'stok_tersedia' => 1,
            'cover_image' => 'covers/history.jpg',
        ]);
        Loan::create([
            'book_id' => $book->id,
            'petugas_id' => $this->admin->id,
            'nama_peminjam' => 'Peminjam Lama',
            'tanggal_pinjam' => now()->subDays(10),
            'tanggal_jatuh_tempo' => now()->subDays(3),
            'tanggal_kembali' => now()->subDays(4),
        ]);

        Livewire::actingAs($this->admin)
            ->test(BookManager::class)
            ->call('delete', $book->id)
            ->assertSee('tidak dapat dihapus');

        $this->assertDatabaseHas('books', ['id' => $book->id]);
        Storage::disk('public')->assertExists('covers/history.jpg');
    }
}
