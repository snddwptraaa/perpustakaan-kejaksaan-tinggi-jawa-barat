<?php

namespace Tests\Feature\Admin;

use App\Livewire\Admin\LoanManager;
use App\Models\Book;
use App\Models\Category;
use App\Models\Loan;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class LoanManagerTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    private Category $category;

    private Book $book;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->create(['role' => 'admin']);
        $this->category = Category::create(['nama_kategori' => 'Hukum Pidana']);
        $this->book = Book::create([
            'category_id' => $this->category->id,
            'judul' => 'KUHP dan KUHAP',
            'penulis' => 'Moeljatno',
            'stok' => 3,
            'stok_tersedia' => 3,
            'lokasi_rak' => 'Rak Pidana 1',
        ]);
    }

    public function test_admin_can_access_loans_page(): void
    {
        $response = $this->actingAs($this->admin)->get(route('admin.loans'));
        $response->assertOk();
    }

    public function test_admin_can_open_create_loan_modal(): void
    {
        Livewire::actingAs($this->admin)
            ->test(LoanManager::class)
            ->assertSet('showForm', false)
            ->call('create')
            ->assertSet('showForm', true)
            ->assertSet('tanggal_pinjam', now()->toDateString())
            ->assertSet('tanggal_jatuh_tempo', now()->addDays(7)->toDateString());
    }

    public function test_admin_can_search_and_select_book(): void
    {
        Livewire::actingAs($this->admin)
            ->test(LoanManager::class)
            ->call('create')
            ->assertSet('bookSearch', '')
            ->assertViewHas('availableBooks', fn ($books) => $books->isEmpty())
            ->set('bookSearch', 'KUHP')
            ->assertViewHas('availableBooks', fn ($books) => $books->count() === 1 && $books->first()->id === $this->book->id)
            ->call('selectBook', $this->book->id)
            ->assertSet('book_id', (string) $this->book->id)
            ->assertSet('bookSearch', '')
            ->call('deselectBook')
            ->assertSet('book_id', '');
    }

    public function test_admin_can_record_loan_and_decrement_stock(): void
    {
        Livewire::actingAs($this->admin)
            ->test(LoanManager::class)
            ->call('create')
            ->call('selectBook', $this->book->id)
            ->set('nama_peminjam', 'Jaksa Pratama Ahmad')
            ->set('nip_peminjam', '198801012015011001')
            ->set('instansi_unit', 'Pidana Umum')
            ->set('tanggal_pinjam', now()->toDateString())
            ->set('tanggal_jatuh_tempo', now()->addDays(7)->toDateString())
            ->set('catatan', 'Keperluan sidang perkara pidana.')
            ->call('save')
            ->assertHasNoErrors()
            ->assertSet('showForm', false);

        $this->assertDatabaseHas('loans', [
            'book_id' => $this->book->id,
            'nama_peminjam' => 'Jaksa Pratama Ahmad',
            'nip_peminjam' => '198801012015011001',
        ]);

        $this->assertSame('dipinjam', Loan::latest('id')->first()->current_status);

        $this->assertEquals(2, $this->book->fresh()->stok_tersedia);
    }

    public function test_admin_can_return_book_and_increment_stock(): void
    {
        $loan = Loan::create([
            'book_id' => $this->book->id,
            'petugas_id' => $this->admin->id,
            'nama_peminjam' => 'Jaksa Budi',
            'tanggal_pinjam' => now()->subDays(3)->toDateString(),
            'tanggal_jatuh_tempo' => now()->addDays(4)->toDateString(),
        ]);
        $this->book->decrement('stok_tersedia'); // stok_tersedia now 2

        Livewire::actingAs($this->admin)
            ->test(LoanManager::class)
            ->call('returnBook', $loan->id);

        $this->assertNotNull($loan->fresh()->tanggal_kembali);
        $this->assertEquals('dikembalikan', $loan->fresh()->current_status);
        $this->assertEquals(3, $this->book->fresh()->stok_tersedia);
    }

    public function test_validation_rules_for_recording_loan(): void
    {
        Livewire::actingAs($this->admin)
            ->test(LoanManager::class)
            ->call('create')
            ->set('book_id', '')
            ->set('nama_peminjam', '')
            ->call('save')
            ->assertHasErrors(['book_id', 'nama_peminjam']);
    }

    public function test_admin_can_correct_loan_without_changing_stock(): void
    {
        $loan = Loan::create([
            'book_id' => $this->book->id,
            'petugas_id' => $this->admin->id,
            'nama_peminjam' => 'Nama Salah',
            'tanggal_pinjam' => now()->toDateString(),
            'tanggal_jatuh_tempo' => now()->addDays(7)->toDateString(),
        ]);
        $this->book->decrement('stok_tersedia');

        Livewire::actingAs($this->admin)
            ->test(LoanManager::class)
            ->call('edit', $loan->id)
            ->set('nama_peminjam', 'Nama Benar')
            ->set('tanggal_jatuh_tempo', now()->addDays(14)->toDateString())
            ->call('save')
            ->assertHasNoErrors();

        $this->assertSame('Nama Benar', $loan->fresh()->nama_peminjam);
        $this->assertEquals(2, $this->book->fresh()->stok_tersedia);
    }

    public function test_admin_can_cancel_active_loan_and_restore_stock(): void
    {
        $loan = Loan::create([
            'book_id' => $this->book->id,
            'petugas_id' => $this->admin->id,
            'nama_peminjam' => 'Peminjam Batal',
            'tanggal_pinjam' => now()->toDateString(),
            'tanggal_jatuh_tempo' => now()->addDays(7)->toDateString(),
        ]);
        $this->book->decrement('stok_tersedia');

        Livewire::actingAs($this->admin)
            ->test(LoanManager::class)
            ->call('cancel', $loan->id)
            ->assertSee('Peminjaman dibatalkan');

        $loan->refresh();
        $this->assertNotNull($loan->tanggal_dibatalkan);
        $this->assertSame($this->admin->id, $loan->petugas_pembatal_id);
        $this->assertSame('dibatalkan', $loan->current_status);
        $this->assertEquals(3, $this->book->fresh()->stok_tersedia);
    }

    public function test_double_cancel_and_double_extend_surface_errors_not_500(): void
    {
        $loan = Loan::create([
            'book_id' => $this->book->id,
            'petugas_id' => $this->admin->id,
            'nama_peminjam' => 'Peminjam Kritis',
            'tanggal_pinjam' => now()->toDateString(),
            'tanggal_jatuh_tempo' => now()->addDays(7)->toDateString(),
            'tanggal_dibatalkan' => now(),
            'petugas_pembatal_id' => $this->admin->id,
        ]);

        Livewire::actingAs($this->admin)
            ->test(LoanManager::class)
            ->call('returnBook', $loan->id)
            ->assertHasErrors(['loan']);

        Livewire::actingAs($this->admin)
            ->test(LoanManager::class)
            ->call('extendLoan', $loan->id)
            ->assertHasErrors(['loan']);
    }

    public function test_admin_can_filter_and_reset_loans(): void
    {
        Loan::create([
            'book_id' => $this->book->id,
            'petugas_id' => $this->admin->id,
            'nama_peminjam' => 'Ahmad Peminjam',
            'tanggal_pinjam' => '2026-06-01',
            'tanggal_jatuh_tempo' => '2026-06-08',
        ]);

        Loan::create([
            'book_id' => $this->book->id,
            'petugas_id' => $this->admin->id,
            'nama_peminjam' => 'Budi Peminjam',
            'tanggal_pinjam' => '2026-07-01',
            'tanggal_jatuh_tempo' => '2026-07-08',
        ]);

        Livewire::actingAs($this->admin)
            ->test(LoanManager::class)
            ->set('search', 'Ahmad')
            ->assertSee('Ahmad Peminjam')
            ->assertDontSee('Budi Peminjam')
            ->call('resetFilters')
            ->assertSet('search', '')
            ->assertSee('Ahmad Peminjam')
            ->assertSee('Budi Peminjam');
    }
}
