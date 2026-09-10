<?php

namespace Tests\Feature\Admin;

use App\Livewire\Admin\AuditLogViewer;
use App\Models\AuditLog;
use App\Models\Book;
use App\Models\Category;
use App\Models\User;
use App\Services\AuditLogger;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Livewire\Livewire;
use Tests\TestCase;

class AuditLogViewerTest extends TestCase
{
    use RefreshDatabase;

    private User $superadmin;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->superadmin = User::factory()->create(['role' => 'superadmin']);
        $this->admin = User::factory()->create(['role' => 'admin']);
    }

    public function test_superadmin_can_access_audit_log_page(): void
    {
        $this->actingAs($this->superadmin)
            ->get(route('admin.audit'))
            ->assertOk()
            ->assertSee('Audit Log Sistem');
    }

    public function test_regular_admin_is_forbidden(): void
    {
        $this->actingAs($this->admin)
            ->get(route('admin.audit'))
            ->assertForbidden();
    }

    public function test_audit_log_displays_summary_and_differences_correctly(): void
    {
        Auth::login($this->superadmin);

        $category = Category::create(['nama_kategori' => 'Hukum']);
        $book = Book::create([
            'category_id' => $category->id,
            'judul' => 'KUHP Lama',
            'penulis' => 'Penulis A',
            'stok' => 5,
            'stok_tersedia' => 5,
        ]);

        $before = $book->toArray();
        $book->update(['judul' => 'KUHP Baru', 'stok' => 10, 'stok_tersedia' => 10]);
        $after = $book->fresh()->toArray();

        $log = app(AuditLogger::class)->model('ubah', $book, $before, $after);

        $this->assertSame('Koleksi Buku', $log->entity_label);
        $this->assertSame('Ubah Data', $log->action_label);
        $this->assertSame('KUHP Baru', $log->subject_title);

        $diffs = $log->getDifferences();
        $this->assertCount(3, $diffs); // judul, stok, stok_tersedia

        $keys = array_column($diffs, 'key');
        $this->assertContains('judul', $keys);
        $this->assertContains('stok', $keys);
        $this->assertContains('stok_tersedia', $keys);

        // Component rendering
        Livewire::actingAs($this->superadmin)
            ->test(AuditLogViewer::class)
            ->assertSee('KUHP Baru')
            ->assertSee('Ubah Data')
            ->assertSee('Koleksi Buku')
            ->assertSeeHtml('Lihat Rincian');
    }

    public function test_audit_log_detail_modal_opens_with_comparison_table(): void
    {
        Auth::login($this->superadmin);

        $category = Category::create(['nama_kategori' => 'Pidana']);
        $before = $category->toArray();
        $category->update(['nama_kategori' => 'Pidana Khusus']);
        $after = $category->fresh()->toArray();

        $log = app(AuditLogger::class)->model('ubah', $category, $before, $after);

        Livewire::actingAs($this->superadmin)
            ->test(AuditLogViewer::class)
            ->call('showDetail', $log->id)
            ->assertSet('showDetailModal', true)
            ->assertSet('selectedLogId', $log->id)
            ->assertSee('Sebelum Diubah')
            ->assertSee('Setelah Diubah')
            ->assertSee('Pidana')
            ->assertSee('Pidana Khusus')
            ->call('closeDetail')
            ->assertSet('showDetailModal', false);
    }

    public function test_audit_log_filtering_by_action_and_entity(): void
    {
        Auth::login($this->superadmin);

        AuditLog::create([
            'user_id' => $this->superadmin->id,
            'aksi' => 'buat',
            'entitas' => 'books',
            'entitas_id' => 1,
            'sebelum' => null,
            'sesudah' => ['judul' => 'Buku Pertama'],
        ]);

        AuditLog::create([
            'user_id' => $this->superadmin->id,
            'aksi' => 'pinjam',
            'entitas' => 'loans',
            'entitas_id' => 2,
            'sebelum' => null,
            'sesudah' => ['nama_peminjam' => 'Budi'],
            'metadata' => ['nama_peminjam' => 'Budi'],
        ]);

        Livewire::actingAs($this->superadmin)
            ->test(AuditLogViewer::class)
            ->set('filterAksi', 'buat')
            ->assertSee('Buku Pertama')
            ->assertDontSee('Budi')
            ->set('filterAksi', '')
            ->set('filterEntitas', 'loans')
            ->assertSee('Budi')
            ->assertDontSee('Buku Pertama')
            ->call('resetFilters')
            ->assertSet('filterEntitas', '')
            ->assertSet('filterAksi', '')
            ->assertSee('Buku Pertama')
            ->assertSee('Budi');
    }
}
