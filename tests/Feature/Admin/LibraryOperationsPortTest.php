<?php

namespace Tests\Feature\Admin;

use App\Livewire\Admin\LoanManager;
use App\Livewire\Admin\MemberManager;
use App\Models\AuditLog;
use App\Models\Book;
use App\Models\Category;
use App\Models\Loan;
use App\Models\Member;
use App\Models\User;
use App\Services\AuditLogger;
use App\Services\CirculationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Livewire\Livewire;
use Tests\TestCase;

class LibraryOperationsPortTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    private Book $book;

    protected function setUp(): void
    {
        parent::setUp();
        $this->admin = User::factory()->create(['role' => 'admin']);
        $category = Category::create(['nama_kategori' => 'Referensi']);
        $this->book = Book::create([
            'category_id' => $category->id,
            'judul' => 'Referensi Hukum',
            'penulis' => 'Tim Perpustakaan',
            'stok' => 2,
            'stok_tersedia' => 2,
        ]);
    }

    public function test_member_can_be_created_and_selected_for_a_loan(): void
    {
        Livewire::actingAs($this->admin)->test(MemberManager::class)
            ->call('create')
            ->set('nama', 'Budi Anggota')
            ->set('nip', '12345')
            ->set('instansi_unit', 'Pidum')
            ->call('save')
            ->assertHasNoErrors();

        $member = Member::firstOrFail();
        Livewire::actingAs($this->admin)->test(LoanManager::class)
            ->call('create')
            ->call('selectBook', $this->book->id)
            ->set('member_id', (string) $member->id)
            ->assertSet('nama_peminjam', 'Budi Anggota')
            ->set('tanggal_pinjam', today()->toDateString())
            ->set('tanggal_jatuh_tempo', today()->addDays(7)->toDateString())
            ->call('save')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('loans', ['member_id' => $member->id, 'nama_peminjam' => 'Budi Anggota']);
        $this->assertDatabaseHas('audit_logs', ['aksi' => 'pinjam', 'entitas' => 'loans']);
    }

    public function test_loan_can_only_be_extended_once_and_uses_today_for_overdue_loan(): void
    {
        Auth::login($this->admin);
        $loan = Loan::create([
            'book_id' => $this->book->id,
            'petugas_id' => $this->admin->id,
            'nama_peminjam' => 'Peminjam',
            'tanggal_pinjam' => today()->subDays(14),
            'tanggal_jatuh_tempo' => today()->subDay(),
        ]);

        $extended = app(CirculationService::class)->extend($loan, $this->admin->id);
        $this->assertSame(today()->addDays(7)->toDateString(), $extended->tanggal_jatuh_tempo->toDateString());
        $this->assertSame(1, $extended->jumlah_perpanjangan);
        $this->expectException(ValidationException::class);
        app(CirculationService::class)->extend($extended, $this->admin->id);
    }

    public function test_archive_scope_hides_books_and_audit_route_is_superadmin_only(): void
    {
        $this->book->update(['archived_at' => now()]);
        $this->assertFalse(Book::active()->whereKey($this->book->id)->exists());

        $this->actingAs($this->admin)->get(route('admin.audit'))->assertForbidden();
        $superadmin = User::factory()->create(['role' => 'superadmin']);
        $this->actingAs($superadmin)->get(route('admin.audit'))->assertOk();
    }

    public function test_inactive_admin_cannot_login_or_keep_using_admin_routes(): void
    {
        $this->admin->update(['aktif' => false]);
        $this->post(route('logout'));

        Livewire::test('pages.auth.login')
            ->set('form.email', $this->admin->email)
            ->set('form.password', 'password')
            ->call('login')
            ->assertHasErrors('form.email');

        $this->actingAs($this->admin)->get(route('admin.dashboard'))->assertForbidden();
    }

    public function test_audit_log_records_before_and_after_payloads(): void
    {
        Auth::login($this->admin);
        $member = Member::create(['nama' => 'Anggota Awal']);
        $before = $member->toArray();
        $member->update(['nama' => 'Anggota Baru']);
        app(AuditLogger::class)->model('ubah', $member, $before, $member->fresh()->toArray());

        $log = AuditLog::firstOrFail();
        $this->assertSame('Anggota Awal', $log->sebelum['nama']);
        $this->assertSame('Anggota Baru', $log->sesudah['nama']);
        $this->assertSame($this->admin->id, $log->user_id);
    }
}
