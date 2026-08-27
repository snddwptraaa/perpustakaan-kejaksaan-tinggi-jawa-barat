<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use RuntimeException;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $adminEmail = config('seeding.admin_email');
        $adminPassword = config('seeding.admin_password');

        if (($adminEmail && ! $adminPassword) || (! $adminEmail && $adminPassword)) {
            throw new RuntimeException('SEED_ADMIN_EMAIL dan SEED_ADMIN_PASSWORD harus diisi bersamaan.');
        }

        if ($adminEmail && $adminPassword) {
            if (mb_strlen($adminPassword) < 12) {
                throw new RuntimeException('SEED_ADMIN_PASSWORD minimal 12 karakter.');
            }

            User::firstOrCreate(['email' => $adminEmail], [
                'name' => config('seeding.admin_name'),
                'password' => Hash::make($adminPassword),
                'role' => 'superadmin',
            ]);
        }

        if (config('seeding.demo_data')) {
            $this->call(DemoDataSeeder::class);
        }
    }
}
