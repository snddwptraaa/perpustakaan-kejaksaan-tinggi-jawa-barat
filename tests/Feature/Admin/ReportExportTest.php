<?php

namespace Tests\Feature\Admin;

use App\Livewire\Admin\BookManager;
use App\Livewire\Admin\LoanManager;
use App\Livewire\Admin\MemberManager;
use App\Livewire\Admin\VisitorReport;
use App\Models\Book;
use App\Models\Category;
use App\Models\Loan;
use App\Models\Member;
use App\Models\User;
use App\Models\Visitor;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class ReportExportTest extends TestCase
{
    use RefreshDatabase;

    public function test_visitor_xlsx_export_uses_active_filters(): void
    {
        $user = User::factory()->create(['role' => 'admin']);
        Visitor::create([
            'kategori' => 'pegawai',
            'nama' => 'Sari Anggraini',
            'nip' => '001234567890',
            'instansi_unit' => 'Bagian Hukum',
            'no_hp' => '081200000001',
            'keperluan' => 'Cari referensi',
            'privacy_consented_at' => now(),
        ]);
        Visitor::create([
            'kategori' => 'umum',
            'nama' => 'Budi Santoso',
            'instansi_unit' => 'Bagian Umum',
            'keperluan' => 'Baca di tempat',
            'privacy_consented_at' => now(),
        ]);

        $component = Livewire::actingAs($user)
            ->test(VisitorReport::class)
            ->set('search', 'Hukum')
            ->set('kategori', 'pegawai');

        $component->call('exportXlsx')->assertFileDownloaded();
    }

    public function test_visitor_pdf_export_with_date_range(): void
    {
        Carbon::setTestNow(now());
        $user = User::factory()->create(['role' => 'admin']);
        Visitor::create([
            'kategori' => 'umum',
            'nama' => 'Pengunjung Juni',
            'instansi_unit' => 'Unpad',
            'keperluan' => 'Riset',
            'created_at' => '2026-06-15 10:00:00',
        ]);
        Visitor::create([
            'kategori' => 'umum',
            'nama' => 'Pengunjung Juli',
            'instansi_unit' => 'ITB',
            'keperluan' => 'Membaca',
            'created_at' => '2026-07-15 10:00:00',
        ]);

        $component = Livewire::actingAs($user)
            ->test(VisitorReport::class)
            ->set('startDate', '2026-06-01')
            ->set('endDate', '2026-06-30');

        $response = $component->call('exportPdf');
        $response->assertFileDownloaded('laporan-pengunjung-2026-06-01-sd-2026-06-30-'.now()->format('Y-m-d_H-i-s').'.pdf');
        Carbon::setTestNow();
    }

    public function test_loan_pdf_export_with_date_range(): void
    {
        Carbon::setTestNow(now());
        $user = User::factory()->create(['role' => 'admin']);
        $category = Category::create(['nama_kategori' => 'Hukum Pidana']);
        $book = Book::create([
            'category_id' => $category->id,
            'judul' => 'KUHP',
            'penulis' => 'Moeljatno',
            'stok' => 5,
            'stok_tersedia' => 5,
        ]);

        Loan::create([
            'book_id' => $book->id,
            'petugas_id' => $user->id,
            'nama_peminjam' => 'Jaksa Juni',
            'tanggal_pinjam' => '2026-06-05',
            'tanggal_jatuh_tempo' => '2026-06-12',
        ]);
        Loan::create([
            'book_id' => $book->id,
            'petugas_id' => $user->id,
            'nama_peminjam' => 'Jaksa Juli',
            'tanggal_pinjam' => '2026-07-05',
            'tanggal_jatuh_tempo' => '2026-07-12',
        ]);

        $component = Livewire::actingAs($user)
            ->test(LoanManager::class)
            ->set('startDate', '2026-06-01')
            ->set('endDate', '2026-07-01');

        $response = $component->call('exportPdf');
        $response->assertFileDownloaded('laporan-peminjaman-2026-06-01-sd-2026-07-01-'.now()->format('Y-m-d_H-i-s').'.pdf');
        Carbon::setTestNow();
    }

    public function test_book_manager_can_export_pdf(): void
    {
        Carbon::setTestNow(now());
        $user = User::factory()->create(['role' => 'admin']);
        $category = Category::create(['nama_kategori' => 'Hukum Pidana']);
        Book::create([
            'category_id' => $category->id,
            'judul' => 'Hukum Acara Pidana',
            'penulis' => 'Andi Hamzah',
            'stok' => 3,
            'stok_tersedia' => 3,
        ]);

        $component = Livewire::actingAs($user)
            ->test(BookManager::class)
            ->set('search', 'Pidana');

        $response = $component->call('exportPdf');
        $response->assertFileDownloaded('katalog-buku-'.now()->format('Y-m-d_H-i-s').'.pdf');
        Carbon::setTestNow();
    }

    public function test_book_manager_can_export_filtered_csv_and_excel(): void
    {
        Carbon::setTestNow('2026-09-10 09:30:00');
        $user = User::factory()->create(['role' => 'admin']);
        $category = Category::create(['nama_kategori' => 'Hukum Administrasi']);
        Book::create([
            'category_id' => $category->id,
            'judul' => 'Hukum Tata Usaha Negara',
            'penulis' => 'Indroharto',
            'stok' => 2,
            'stok_tersedia' => 1,
        ]);

        $component = Livewire::actingAs($user)
            ->test(BookManager::class)
            ->set('search', 'Tata Usaha');

        $component->call('exportCsv')->assertFileDownloaded('koleksi-buku-2026-09-10_09-30-00.csv');
        $component->call('exportXlsx')->assertFileDownloaded('koleksi-buku-2026-09-10_09-30-00.xlsx');
        Carbon::setTestNow();
    }

    public function test_member_manager_can_export_pdf_csv_and_excel(): void
    {
        Carbon::setTestNow('2026-09-10 10:15:00');
        $user = User::factory()->create(['role' => 'admin']);
        Member::create([
            'nama' => 'Siti Rahmawati',
            'nip' => '198801012010012001',
            'instansi_unit' => 'Bidang Pembinaan',
            'no_hp' => '081200000001',
            'aktif' => true,
        ]);

        $component = Livewire::actingAs($user)
            ->test(MemberManager::class)
            ->set('search', 'Pembinaan');

        $component->call('exportPdf')->assertFileDownloaded('daftar-anggota-2026-09-10_10-15-00.pdf');
        $component->call('exportCsv')->assertFileDownloaded('daftar-anggota-2026-09-10_10-15-00.csv');
        $component->call('exportXlsx')->assertFileDownloaded('daftar-anggota-2026-09-10_10-15-00.xlsx');
        Carbon::setTestNow();
    }

    public function test_export_pdf_controller_routes(): void
    {
        $user = User::factory()->create(['role' => 'admin']);

        $res1 = $this->actingAs($user)->get(route('admin.books.export-pdf'));
        $res1->assertOk();
        $this->assertStringStartsWith('%PDF', $res1->getContent());

        $res2 = $this->actingAs($user)->get(route('admin.visitors.export-pdf', ['from' => '2026-06-01', 'to' => '2026-07-01']));
        $res2->assertOk();
        $this->assertStringStartsWith('%PDF', $res2->getContent());

        $res3 = $this->actingAs($user)->get(route('admin.loans.export-pdf', ['from' => '2026-06-01', 'to' => '2026-07-01']));
        $res3->assertOk();
        $this->assertStringStartsWith('%PDF', $res3->getContent());
    }

    public function test_print_report_command_creates_pdf_report(): void
    {
        $path = storage_path('framework/testing-report.pdf');

        $this->artisan('reports:print', ['type' => 'visitors', '--path' => $path])
            ->assertSuccessful();

        $this->assertFileExists($path);
        $this->assertStringStartsWith('%PDF', file_get_contents($path));
        @unlink($path);
    }
}
