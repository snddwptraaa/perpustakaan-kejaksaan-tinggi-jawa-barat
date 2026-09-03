<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('members', function (Blueprint $table): void {
            $table->id();
            $table->string('nama');
            $table->string('nip', 30)->nullable()->index();
            $table->string('instansi_unit')->nullable();
            $table->string('no_hp', 20)->nullable();
            $table->boolean('aktif')->default(true)->index();
            $table->timestamps();
            $table->index(['nama', 'instansi_unit']);
        });

        Schema::create('audit_logs', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('aksi', 100);
            $table->string('entitas', 100);
            $table->unsignedBigInteger('entitas_id')->nullable();
            $table->json('sebelum')->nullable();
            $table->json('sesudah')->nullable();
            $table->json('metadata')->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->timestamps();
            $table->index(['entitas', 'entitas_id']);
            $table->index(['aksi', 'created_at']);
        });

        Schema::table('books', function (Blueprint $table): void {
            $table->timestamp('archived_at')->nullable()->after('cover_image')->index();
        });

        Schema::table('loans', function (Blueprint $table): void {
            $table->foreignId('member_id')->nullable()->after('petugas_id')->constrained('members')->nullOnDelete();
            $table->unsignedTinyInteger('jumlah_perpanjangan')->default(0)->after('tanggal_jatuh_tempo');
            $table->date('tanggal_perpanjangan')->nullable()->after('jumlah_perpanjangan');
            $table->decimal('denda', 12, 2)->unsigned()->default(0)->after('tanggal_dibatalkan');
            $table->decimal('denda_dibayar', 12, 2)->unsigned()->default(0)->after('denda');
            $table->date('denda_dibayar_pada')->nullable()->after('denda_dibayar');
            $table->index(['member_id', 'tanggal_pinjam']);
        });

        Schema::table('users', function (Blueprint $table): void {
            $table->boolean('aktif')->default(true)->after('role')->index();
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table): void {
            $table->dropIndex(['aktif']);
            $table->dropColumn('aktif');
        });

        Schema::table('loans', function (Blueprint $table): void {
            $table->dropForeign(['member_id']);
            $table->dropIndex(['member_id', 'tanggal_pinjam']);
            $table->dropColumn(['member_id', 'jumlah_perpanjangan', 'tanggal_perpanjangan', 'denda', 'denda_dibayar', 'denda_dibayar_pada']);
        });

        Schema::table('books', function (Blueprint $table): void {
            $table->dropIndex(['archived_at']);
            $table->dropColumn('archived_at');
        });

        Schema::dropIfExists('audit_logs');
        Schema::dropIfExists('members');
    }
};
