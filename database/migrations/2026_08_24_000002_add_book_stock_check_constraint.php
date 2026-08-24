<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        if (in_array(DB::getDriverName(), ['mysql', 'mariadb'], true)) {
            DB::statement('ALTER TABLE books ADD CONSTRAINT books_valid_stock CHECK (stok_tersedia <= stok)');
        }
    }

    public function down(): void
    {
        if (DB::getDriverName() === 'mysql') {
            DB::statement('ALTER TABLE books DROP CHECK books_valid_stock');
        }

        if (DB::getDriverName() === 'mariadb') {
            DB::statement('ALTER TABLE books DROP CONSTRAINT books_valid_stock');
        }
    }
};
