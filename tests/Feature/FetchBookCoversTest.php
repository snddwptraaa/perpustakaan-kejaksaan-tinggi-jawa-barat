<?php

namespace Tests\Feature;

use App\Models\Book;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class FetchBookCoversTest extends TestCase
{
    use RefreshDatabase;

    public function test_dry_run_reports_counts_without_writing(): void
    {
        Book::factory()->create(['isbn' => '978-979-414-019-2', 'cover_image' => null]);
        Book::factory()->create(['isbn' => null, 'cover_image' => null]);

        $this->artisan('books:fetch-covers', ['--dry-run' => true])
            ->expectsOutputToContain('Dry run selesai. Database tidak diubah.')
            ->assertSuccessful();

        $this->assertSame(0, Book::whereNotNull('cover_image')->count());
    }

    public function test_downloads_cover_by_isbn_and_skips_placeholder(): void
    {
        Storage::fake('public');
        $valid = Book::factory()->create(['isbn' => '978-979-414-019-2', 'cover_image' => null]);
        Book::factory()->create(['isbn' => '0000000000000', 'cover_image' => null]);
        Book::factory()->create(['isbn' => 'bukan-isbn', 'cover_image' => null]);

        Http::fake([
            'covers.openlibrary.org/b/isbn/9789794140192*' => Http::response(str_repeat('x', 2048), 200, ['Content-Type' => 'image/jpeg']),
            'covers.openlibrary.org/b/isbn/*' => Http::response('x', 200, ['Content-Type' => 'image/jpeg']),
        ]);

        $this->artisan('books:fetch-covers', ['--force' => true])
            ->expectsOutputToContain('Sampul terunduh 1')
            ->assertSuccessful();

        $this->assertSame("covers/{$valid->id}.jpg", $valid->fresh()->cover_image);
        Storage::disk('public')->assertExists("covers/{$valid->id}.jpg");
        $this->assertSame(1, Book::whereNotNull('cover_image')->count());
    }

    public function test_falls_back_to_title_search_with_flag(): void
    {
        Storage::fake('public');
        $book = Book::factory()->create(['judul' => 'Judul Unik Tanpa ISBN', 'isbn' => null, 'cover_image' => null]);

        Http::fake([
            'openlibrary.org/search.json*' => Http::response(['docs' => [['cover_i' => 12345]]], 200),
            'covers.openlibrary.org/b/id/12345*' => Http::response(str_repeat('y', 2048), 200, ['Content-Type' => 'image/jpeg']),
        ]);

        $this->artisan('books:fetch-covers', ['--force' => true, '--with-title' => true])
            ->expectsOutputToContain('Sampul terunduh 1')
            ->assertSuccessful();

        $this->assertSame("covers/{$book->id}.jpg", $book->fresh()->cover_image);
    }
}
