<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('books', function (Blueprint $table) {
            $table->id();
            $table->foreignId('category_id')->constrained()->restrictOnDelete();
            $table->string('judul');
            $table->string('penulis');
            $table->string('penerbit')->nullable();
            $table->year('tahun_terbit')->nullable();
            $table->string('isbn', 20)->nullable();
            $table->string('no_klasifikasi', 50)->nullable();
            $table->string('lokasi_rak', 50)->nullable();
            $table->unsignedInteger('stok')->default(0);
            $table->unsignedInteger('stok_tersedia')->default(0);
            $table->string('cover_image')->nullable();
            $table->text('deskripsi')->nullable();
            $table->timestamps();

            $table->index('judul');
            $table->index('penulis');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('books');
    }
};
