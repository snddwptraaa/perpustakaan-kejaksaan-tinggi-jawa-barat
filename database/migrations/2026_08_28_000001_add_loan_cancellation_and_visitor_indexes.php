<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('loans', function (Blueprint $table): void {
            $table->timestamp('tanggal_dibatalkan')->nullable()->after('tanggal_kembali');
            $table->foreignId('petugas_pembatal_id')
                ->nullable()
                ->after('petugas_id')
                ->constrained('users')
                ->restrictOnDelete();
            $table->index('tanggal_dibatalkan');
        });

        Schema::table('visitors', function (Blueprint $table): void {
            $table->index('kategori');
        });
    }

    public function down(): void
    {
        Schema::table('visitors', function (Blueprint $table): void {
            $table->dropIndex(['kategori']);
        });

        Schema::table('loans', function (Blueprint $table): void {
            $table->dropIndex(['tanggal_dibatalkan']);
            $table->dropConstrainedForeignId('petugas_pembatal_id');
            $table->dropColumn('tanggal_dibatalkan');
        });
    }
};
