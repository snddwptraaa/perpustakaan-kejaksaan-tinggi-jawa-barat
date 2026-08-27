<?php

namespace Database\Seeders;

use App\Models\Book;
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
use RuntimeException;

class DemoDataSeeder extends Seeder
{
    public function run(): void
    {
        $faker = Factory::create('id_ID');
        $faker->seed(24082026);

        DB::transaction(function () use ($faker): void {
            $staff = $this->seedStaff();
            $books = Book::query()->where('stok', '>', 0)->orderBy('id')->get();

            if ($books->count() < 6) {
                throw new RuntimeException('Katalog resmi minimal harus berisi 6 buku sebelum data demo dibuat.');
            }

            $this->seedVisitors($faker);
            $this->seedLoans($faker, $books, $staff);
            $this->seedActiveLoans($books, $staff);
            $this->synchronizeBookStock();
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

    private function synchronizeBookStock(): void
    {
        Book::query()->update(['stok_tersedia' => DB::raw('stok')]);

        $activeLoans = Loan::query()
            ->active()
            ->selectRaw('book_id, COUNT(*) AS total')
            ->groupBy('book_id')
            ->pluck('total', 'book_id');

        foreach ($activeLoans as $bookId => $total) {
            $book = Book::findOrFail($bookId);
            $book->update(['stok_tersedia' => max(0, $book->stok - (int) $total)]);
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
