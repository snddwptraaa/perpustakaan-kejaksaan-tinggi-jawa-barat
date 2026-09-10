<?php

namespace Database\Factories;

use App\Models\Book;
use App\Models\Category;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Book>
 */
class BookFactory extends Factory
{
    public function definition(): array
    {
        return [
            'category_id' => Category::factory(),
            'judul' => 'Buku Uji '.$this->faker->unique()->numberBetween(1, 999999),
            'penulis' => $this->faker->name(),
            'penerbit' => 'Penerbit Uji',
            'tahun_terbit' => 2020,
            'stok' => 1,
            'stok_tersedia' => 1,
        ];
    }
}
