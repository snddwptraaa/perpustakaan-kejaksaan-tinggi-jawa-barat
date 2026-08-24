<?php

namespace Database\Seeders;

use App\Models\Book;
use App\Models\Category;
use App\Models\Loan;
use App\Models\User;
use App\Models\Visitor;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Faker\Factory;
use Illuminate\Database\Seeder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class DemoDataSeeder extends Seeder
{
    public function run(): void
    {
        $faker = Factory::create('id_ID');
        $faker->seed(24082026);

        DB::transaction(function () use ($faker): void {
            $staff = $this->seedStaff();
            $books = $this->seedCatalog();
            $this->seedVisitors($faker);
            $this->seedLoans($faker, $books, $staff);
            $this->seedActiveLoans($books, $staff);
            $this->synchronizeBookStock($books);
        }, attempts: 3);
    }

    /** @return Collection<int, User> */
    private function seedStaff()
    {
        return collect([
            ['name' => 'Rina Kurniasih', 'email' => 'rina.petugas@demo.test'],
            ['name' => 'Dedi Mulyana', 'email' => 'dedi.petugas@demo.test'],
            ['name' => 'Siti Nurhayati', 'email' => 'siti.petugas@demo.test'],
        ])->map(fn (array $staff) => User::firstOrCreate(
            ['email' => $staff['email']],
            [
                'name' => $staff['name'],
                'password' => Hash::make(Str::random(40)),
                'role' => 'admin',
                'email_verified_at' => now(),
            ],
        ));
    }

    /** @return Collection<int, Book> */
    private function seedCatalog()
    {
        $catalog = [
            ['Hukum Pidana', 'Asas-Asas Hukum Pidana di Indonesia', 'Moeljatno', 'Rineka Cipta', 2018, 5, '345 MOE a'],
            ['Hukum Pidana', 'Hukum Acara Pidana Indonesia', 'Andi Hamzah', 'Sinar Grafika', 2022, 4, '345.05 HAM h'],
            ['Hukum Pidana', 'Delik-Delik Khusus', 'Leden Marpaung', 'Sinar Grafika', 2021, 3, '345.02 MAR d'],
            ['Hukum Perdata', 'Pokok-Pokok Hukum Perdata', 'Subekti', 'Intermasa', 2019, 5, '346 SUB p'],
            ['Hukum Perdata', 'Hukum Perjanjian', 'Subekti', 'Intermasa', 2020, 4, '346.02 SUB h'],
            ['Hukum Perdata', 'Perbuatan Melawan Hukum', 'Rosa Agustina', 'Program Pascasarjana FHUI', 2018, 2, '346.03 AGU p'],
            ['Hukum Tata Negara', 'Pengantar Ilmu Hukum Tata Negara', 'Jimly Asshiddiqie', 'Rajawali Pers', 2021, 4, '342 ASS p'],
            ['Hukum Tata Negara', 'Konstitusi dan Konstitusionalisme Indonesia', 'Jimly Asshiddiqie', 'Sinar Grafika', 2020, 3, '342 ASS k'],
            ['Hukum Administrasi Negara', 'Hukum Administrasi Negara', 'Ridwan HR', 'Rajawali Pers', 2022, 4, '342.06 RID h'],
            ['Hukum Administrasi Negara', 'Peradilan Administrasi Negara', 'S. F. Marbun', 'FH UII Press', 2018, 2, '342.066 MAR p'],
            ['Hukum Internasional', 'Pengantar Hukum Internasional', 'Mochtar Kusumaatmadja', 'Alumni', 2019, 3, '341 KUS p'],
            ['Hukum Internasional', 'Hukum Perjanjian Internasional', 'Boer Mauna', 'Alumni', 2020, 2, '341.37 MAU h'],
            ['Hukum Agraria', 'Hukum Agraria Indonesia', 'Boedi Harsono', 'Universitas Trisakti', 2020, 3, '346.04 HAR h'],
            ['Hukum Agraria', 'Hak Menguasai Negara', 'Nurhasan Ismail', 'Kepel Press', 2018, 2, '346.044 ISM h'],
            ['Hukum Bisnis', 'Hukum Perusahaan Indonesia', 'Abdulkadir Muhammad', 'Citra Aditya Bakti', 2021, 4, '346.07 MUH h'],
            ['Hukum Bisnis', 'Hukum Kepailitan', 'Sutan Remy Sjahdeini', 'Prenadamedia', 2022, 3, '346.078 SJA h'],
            ['Tindak Pidana Korupsi', 'Pemberantasan Tindak Pidana Korupsi', 'Ermansjah Djaja', 'Sinar Grafika', 2020, 5, '345.023 DJA p'],
            ['Tindak Pidana Korupsi', 'Pembuktian Terbalik dalam Korupsi', 'Lilik Mulyadi', 'Alumni', 2019, 3, '345.023 MUL p'],
            ['Perundang-undangan', 'Teknik Penyusunan Peraturan Perundang-undangan', 'Maria Farida Indrati', 'Kanisius', 2021, 4, '348.02 IND t'],
            ['Perundang-undangan', 'Kompilasi Peraturan Kejaksaan Republik Indonesia', 'Tim Redaksi', 'Kejaksaan RI', 2024, 6, '348.598 TIM k'],
            ['Yurisprudensi', 'Yurisprudensi Mahkamah Agung Republik Indonesia', 'Mahkamah Agung RI', 'Mahkamah Agung RI', 2023, 4, '348.598 MAH y'],
            ['Yurisprudensi', 'Kaidah Hukum Putusan Mahkamah Konstitusi', 'Mahkamah Konstitusi RI', 'MKRI', 2022, 3, '342.598 MAH k'],
            ['Kejaksaan dan Penuntutan', 'Hukum dan Praktik Penuntutan', 'Bambang Waluyo', 'Sinar Grafika', 2020, 5, '345.01 WAL h'],
            ['Kejaksaan dan Penuntutan', 'Strategi Penanganan Perkara Pidana', 'Tim Pusdiklat Kejaksaan', 'Kejaksaan RI', 2023, 5, '345.01 TIM s'],
            ['Etika Profesi', 'Etika Profesi Hukum', 'Supriadi', 'Sinar Grafika', 2020, 3, '174.3 SUP e'],
            ['Etika Profesi', 'Kode Etik Jaksa dan Perilaku Aparatur', 'Komisi Kejaksaan RI', 'Komisi Kejaksaan RI', 2022, 4, '174.3 KOM k'],
            ['Manajemen Pemerintahan', 'Administrasi Publik Kontemporer', 'Miftah Thoha', 'Kencana', 2022, 4, '351 THO a'],
            ['Manajemen Pemerintahan', 'Manajemen Pelayanan Publik', 'Ratminto', 'Pustaka Pelajar', 2021, 3, '352.63 RAT m'],
            ['Referensi Umum', 'Kamus Hukum', 'Sudarsono', 'Rineka Cipta', 2021, 6, '340.03 SUD k'],
            ['Referensi Umum', 'Metode Penelitian Hukum', 'Peter Mahmud Marzuki', 'Kencana', 2023, 5, '340.072 MAR m'],
        ];

        return collect($catalog)->map(function (array $item, int $index): Book {
            [$categoryName, $title, $author, $publisher, $year, $stock, $classification] = $item;
            $category = Category::firstOrCreate(
                ['slug' => Str::slug($categoryName)],
                ['nama_kategori' => $categoryName],
            );

            return Book::updateOrCreate(['judul' => $title], [
                'category_id' => $category->id,
                'penulis' => $author,
                'penerbit' => $publisher,
                'tahun_terbit' => $year,
                'jumlah_halaman' => (180 + ($index * 17) % 520).' hlm',
                'isbn' => '978-602-'.str_pad((string) (12000 + $index), 5, '0', STR_PAD_LEFT).'-'.($index % 10),
                'no_klasifikasi' => $classification,
                'lokasi_rak' => 'Rak '.chr(65 + ($index % 8)).'-'.str_pad((string) (($index % 12) + 1), 2, '0', STR_PAD_LEFT),
                'stok' => $stock,
                'stok_tersedia' => $stock,
                'deskripsi' => "Referensi {$categoryName} untuk mendukung riset, penyusunan berkas, dan layanan literasi hukum.",
            ]);
        });
    }

    private function seedVisitors($faker): void
    {
        $start = Carbon::create(2022, 1, 1)->startOfMonth();
        $end = today()->startOfMonth();
        $sequence = 1;

        foreach (CarbonPeriod::create($start, '1 month', $end) as $month) {
            $isCurrentMonth = $month->isSameMonth(today());
            $lastDay = $isCurrentMonth ? today()->day : $month->daysInMonth;
            $volume = $isCurrentMonth ? max(18, $lastDay * 3) : 24 + (($month->month + $month->year) % 18);

            for ($i = 0; $i < $volume; $i++) {
                $date = $month->copy()->day(min($lastDay, 1 + (($i * 7 + $sequence) % $lastDay)))->setTime(8 + ($i % 8), ($i * 13) % 60);
                $employee = $i % 4 === 0;
                $name = $faker->name();

                Visitor::firstOrCreate([
                    'nama' => $name,
                    'created_at' => $date,
                ], [
                    'kategori' => $employee ? 'pegawai' : 'umum',
                    'nip' => $employee ? '19'.str_pad((string) $sequence, 16, '0', STR_PAD_LEFT) : null,
                    'instansi_unit' => $employee
                        ? $faker->randomElement(['Tindak Pidana Umum', 'Tindak Pidana Khusus', 'Intelijen', 'Pembinaan', 'Perdata dan Tata Usaha Negara'])
                        : $faker->randomElement(['Universitas Padjadjaran', 'Universitas Pendidikan Indonesia', 'UIN Sunan Gunung Djati', 'Masyarakat Umum', 'Pemerintah Kota Bandung']),
                    'no_hp' => $sequence % 3 === 0 ? '08'.str_pad((string) (1200000000 + $sequence), 10, '0', STR_PAD_LEFT) : null,
                    'keperluan' => $faker->randomElement(['Membaca di Tempat', 'Mencari Referensi Hukum & Koleksi', 'Riset Skripsi / Tesis / Penelitian', 'Mencari Referensi Tugas Kedinasan', 'Peminjaman Koleksi Buku']),
                    'privacy_consented_at' => $date,
                    'updated_at' => $date,
                ]);
                $sequence++;
            }
        }
    }

    private function seedLoans($faker, $books, $staff): void
    {
        $start = Carbon::create(2022, 1, 10);
        $months = max(1, $start->diffInMonths(today()));

        for ($index = 0; $index < $months * 3; $index++) {
            $loanDate = $start->copy()->addDays($index * 10);

            if ($loanDate->isAfter(today())) {
                break;
            }

            $book = $books[$index % $books->count()];
            $dueDate = $loanDate->copy()->addDays(7 + ($index % 8));
            $isRecentActive = $loanDate->greaterThanOrEqualTo(today()->subDays(20)) && $index % 3 !== 0;
            $returnedAt = $isRecentActive ? null : $dueDate->copy()->subDays(2 - ($index % 5));

            Loan::firstOrCreate([
                'book_id' => $book->id,
                'nama_peminjam' => 'Demo '.$faker->name(),
                'tanggal_pinjam' => $loanDate->toDateString(),
            ], [
                'petugas_id' => $staff[$index % $staff->count()]->id,
                'nip_peminjam' => $index % 3 === 0 ? '19'.str_pad((string) (5000 + $index), 16, '0', STR_PAD_LEFT) : null,
                'instansi_unit' => $faker->randomElement(['Kejati Jawa Barat', 'Kejari Kota Bandung', 'Universitas Padjadjaran', 'Masyarakat Umum']),
                'tanggal_jatuh_tempo' => $dueDate->toDateString(),
                'tanggal_kembali' => $returnedAt?->min(today())?->toDateString(),
                'catatan' => $returnedAt ? 'Buku dikembalikan dalam kondisi baik.' : 'Peminjaman demo masih aktif.',
                'created_at' => $loanDate,
                'updated_at' => $returnedAt ?? $loanDate,
            ]);
        }
    }

    private function synchronizeBookStock($books): void
    {
        foreach ($books as $book) {
            $activeLoans = $book->loans()->active()->count();
            $book->update(['stok_tersedia' => max(0, $book->stok - $activeLoans)]);
        }
    }

    private function seedActiveLoans($books, $staff): void
    {
        $borrowers = [
            ['Asep Permana', 'Tindak Pidana Umum', 18, 7],
            ['Nina Herlina', 'Universitas Padjadjaran', 14, 7],
            ['Yusuf Maulana', 'Kejari Kota Bandung', 10, 7],
            ['Dian Puspitasari', 'Tindak Pidana Khusus', 5, 10],
            ['Rizky Firmansyah', 'UIN Sunan Gunung Djati', 3, 10],
            ['Maya Sari', 'Perdata dan Tata Usaha Negara', 1, 14],
        ];

        foreach ($borrowers as $index => [$name, $unit, $daysAgo, $loanDays]) {
            $loanDate = today()->subDays($daysAgo);

            Loan::firstOrCreate([
                'book_id' => $books[$index]->id,
                'nama_peminjam' => $name,
                'tanggal_pinjam' => $loanDate->toDateString(),
            ], [
                'petugas_id' => $staff[$index % $staff->count()]->id,
                'nip_peminjam' => $index % 2 === 0 ? '19900101202012100'.($index + 1) : null,
                'instansi_unit' => $unit,
                'tanggal_jatuh_tempo' => $loanDate->copy()->addDays($loanDays)->toDateString(),
                'tanggal_kembali' => null,
                'catatan' => $daysAgo > $loanDays ? 'Perlu tindak lanjut pengembalian.' : 'Peminjaman aktif.',
                'created_at' => $loanDate->copy()->setTime(10, 15),
                'updated_at' => $loanDate->copy()->setTime(10, 15),
            ]);
        }
    }
}
