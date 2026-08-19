<?php

namespace Database\Seeders;

use App\Models\Book;
use App\Models\Category;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::firstOrCreate(['email' => 'admin@perpustakaan.kejati-jabar.go.id'], [
            'name' => 'Administrator Perpustakaan',
            'password' => Hash::make('password'),
            'role' => 'superadmin',
        ]);

        $categories = [
            'Hukum Pidana', 'Hukum Perdata', 'Perundang-undangan', 'Manajemen Pemerintahan', 'Referensi Umum',
        ];

        foreach ($categories as $name) {
            Category::firstOrCreate(['slug' => Str::slug($name)], ['nama_kategori' => $name]);
        }

        $samples = [
            ['judul' => 'Pengantar Hukum Pidana Indonesia', 'penulis' => 'Prof. Dr. Andi Hamzah', 'category' => 'Hukum Pidana', 'penerbit' => 'Sinar Grafika', 'tahun_terbit' => 2022, 'stok' => 4],
            ['judul' => 'Hukum Acara Pidana Indonesia', 'penulis' => 'Yahya Harahap', 'category' => 'Hukum Pidana', 'penerbit' => 'Sinar Grafika', 'tahun_terbit' => 2021, 'stok' => 3],
            ['judul' => 'Pokok-Pokok Hukum Perdata', 'penulis' => 'Prof. Subekti', 'category' => 'Hukum Perdata', 'penerbit' => 'Intermasa', 'tahun_terbit' => 2019, 'stok' => 5],
            ['judul' => 'Kompilasi Peraturan Perundang-undangan', 'penulis' => 'Tim Redaksi', 'category' => 'Perundang-undangan', 'penerbit' => 'Bhuana Ilmu Populer', 'tahun_terbit' => 2023, 'stok' => 2],
            ['judul' => 'Etika Profesi Hukum', 'penulis' => 'Supriadi', 'category' => 'Referensi Umum', 'penerbit' => 'Sinar Grafika', 'tahun_terbit' => 2020, 'stok' => 3],
            ['judul' => 'Administrasi Publik Kontemporer', 'penulis' => 'Miftah Thoha', 'category' => 'Manajemen Pemerintahan', 'penerbit' => 'Kencana', 'tahun_terbit' => 2022, 'stok' => 4],
        ];

        foreach ($samples as $sample) {
            $category = Category::where('nama_kategori', $sample['category'])->first();
            Book::firstOrCreate(['judul' => $sample['judul']], [
                'category_id' => $category->id,
                'penulis' => $sample['penulis'],
                'penerbit' => $sample['penerbit'],
                'tahun_terbit' => $sample['tahun_terbit'],
                'stok' => $sample['stok'],
                'stok_tersedia' => $sample['stok'],
            ]);
        }
    }
}
