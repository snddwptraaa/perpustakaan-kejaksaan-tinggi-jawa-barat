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
        $response->assertSee('$wire.set(\'filterCategory\', val, true)', false);
    }

    public function test_admin_can_open_create_modal(): void
    {
        Livewire::actingAs($this->admin)
            ->test(BookManager::class)
            ->assertSet('showForm', false)
            ->call('create')
            ->assertSet('showForm', true)
            ->assertSet('editingId', null)
            ->assertSet('judul', '')
            ->assertSee('$wire.set(\'category_id\', val, false)', false);
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
            ->set('isbn', '978-0-13-468599-1')
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
            'isbn' => '9780134685991',
            'no_klasifikasi' => '345.02 HAM h',
            'lokasi_rak' => 'Rak Pidana Lt. 2 - A3',
            'stok' => 5,
            'stok_tersedia' => 5,
            'cover_image' => null,
        ]);
    }

    public function test_isbn_with_invalid_checksum_is_rejected(): void
    {
        Livewire::actingAs($this->admin)
            ->test(BookManager::class)
            ->call('create')
            ->set('category_id', (string) $this->category->id)
            ->set('judul', 'Buku Uji')
            ->set('penulis', 'Pengarang Uji')
            ->set('stok', 1)
            ->set('stok_tersedia', 1)
            ->set('isbn', '9780134685992') // checksum salah
            ->call('save')
            ->assertHasErrors(['isbn']);

        $this->assertDatabaseMissing('books', ['judul' => 'Buku Uji']);
    }

    public function test_isbn_malformed_input_is_rejected(): void
    {
        Livewire::actingAs($this->admin)
            ->test(BookManager::class)
            ->call('create')
            ->set('category_id', (string) $this->category->id)
            ->set('judul', 'Buku Uji 2')
            ->set('penulis', 'Pengarang Uji')
            ->set('stok', 1)
            ->set('stok_tersedia', 1)
            ->set('isbn', '12345')
            ->call('save')
            ->assertHasErrors(['isbn']);

        $this->assertDatabaseMissing('books', ['judul' => 'Buku Uji 2']);
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

    public function test_book_without_publication_year_can_be_created_and_edited(): void
    {
        Livewire::actingAs($this->admin)->test(BookManager::class)
            ->call('create')
            ->set('category_id', (string) $this->category->id)
            ->set('judul', 'Buku tanpa tahun')
            ->set('penulis', 'Penulis')
            ->call('save')
            ->assertHasNoErrors();

        $book = Book::where('judul', 'Buku tanpa tahun')->firstOrFail();
        $this->assertNull($book->tahun_terbit);

        Livewire::actingAs($this->admin)->test(BookManager::class)
            ->call('edit', $book->id)
            ->assertSet('tahun_terbit', '')
            ->set('judul', 'Buku diperbarui')
            ->call('save')
            ->assertHasNoErrors()
            ->assertSet('showForm', false);

        $this->assertNull($book->fresh()->tahun_terbit);
        $this->assertSame('Buku diperbarui', $book->fresh()->judul);
    }

    public function test_admin_can_filter_sort_and_reset_book_collection(): void
    {
        $civilCategory = Category::create([
            'nama_kategori' => 'Hukum Perdata',
            'slug' => 'hukum-perdata',
        ]);

        Book::create([
            'category_id' => $this->category->id,
            'judul' => 'Zulu Pidana',
            'penulis' => 'Penulis A',
            'stok' => 2,
            'stok_tersedia' => 2,
        ]);
        Book::create([
            'category_id' => $civilCategory->id,
            'judul' => 'Alpha Perdata',
            'penulis' => 'Penulis B',
            'stok' => 3,
            'stok_tersedia' => 1,
        ]);
        Book::create([
            'category_id' => $civilCategory->id,
            'judul' => 'Buku Diarsipkan',
            'penulis' => 'Penulis C',
            'stok' => 1,
            'stok_tersedia' => 0,
            'archived_at' => now(),
        ]);
        Book::create([
            'category_id' => $this->category->id,
            'judul' => 'Kosong Pidana',
            'penulis' => 'Penulis D',
            'stok' => 1,
            'stok_tersedia' => 0,
        ]);

        Livewire::actingAs($this->admin)
            ->test(BookManager::class)
            ->set('filterCategory', (string) $civilCategory->id)
            ->assertSee('Alpha Perdata')
            ->assertSee('Buku Diarsipkan')
            ->assertDontSee('Zulu Pidana')
            ->set('filterStatus', 'archived')
            ->assertSee('Buku Diarsipkan')
            ->assertDontSee('Alpha Perdata')
            ->set('filterCategory', '')
            ->set('filterStatus', 'unavailable')
            ->assertSee('Kosong Pidana')
            ->assertDontSee('Buku Diarsipkan')
            ->set('filterStatus', '')
            ->set('sort', 'title_asc')
            ->assertSeeInOrder(['Alpha Perdata', 'Buku Diarsipkan', 'Kosong Pidana', 'Zulu Pidana'])
            ->call('resetFilters')
            ->assertSet('search', '')
            ->assertSet('filterCategory', '')
            ->assertSet('filterStatus', '')
            ->assertSet('sort', 'newest');
    }

    public function test_category_filter_can_be_opened_from_url(): void
    {
        $civilCategory = Category::create([
            'nama_kategori' => 'Hukum Perdata',
            'slug' => 'hukum-perdata',
        ]);
        Book::create([
            'category_id' => $this->category->id,
            'judul' => 'Buku Pidana',
            'penulis' => 'Penulis A',
            'stok' => 1,
            'stok_tersedia' => 1,
        ]);
        Book::create([
            'category_id' => $civilCategory->id,
            'judul' => 'Buku Perdata',
            'penulis' => 'Penulis B',
            'stok' => 1,
            'stok_tersedia' => 1,
        ]);

        $this->actingAs($this->admin)
            ->get(route('admin.books', ['category' => $civilCategory->id]))
            ->assertOk()
            ->assertSee('Buku Perdata')
            ->assertDontSee('Buku Pidana');
    }
}
