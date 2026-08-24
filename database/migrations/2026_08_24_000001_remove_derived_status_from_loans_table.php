<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('loans', function (Blueprint $table): void {
            $table->dropIndex(['status', 'tanggal_jatuh_tempo']);
            $table->dropColumn('status');
            $table->index(['tanggal_kembali', 'tanggal_jatuh_tempo']);
        });
    }

    public function down(): void
    {
        Schema::table('loans', function (Blueprint $table): void {
            $table->dropIndex(['tanggal_kembali', 'tanggal_jatuh_tempo']);
            $table->enum('status', ['dipinjam', 'dikembalikan', 'terlambat'])->default('dipinjam')->after('tanggal_kembali');
            $table->index(['status', 'tanggal_jatuh_tempo']);
        });
    }
};
