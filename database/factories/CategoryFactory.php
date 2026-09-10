<?php

namespace Database\Factories;

use App\Models\Category;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Category>
 */
class CategoryFactory extends Factory
{
    public function definition(): array
    {
        $nama = 'Kategori Uji '.$this->faker->unique()->numberBetween(1, 999999);

        return [
            'nama_kategori' => $nama,
            'slug' => str($nama)->slug(),
        ];
    }
}
