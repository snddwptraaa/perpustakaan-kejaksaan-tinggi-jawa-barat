<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('visitors', function (Blueprint $table): void {
            $table->timestamp('privacy_consented_at')->nullable()->after('keperluan');
        });
    }

    public function down(): void
    {
        Schema::table('visitors', function (Blueprint $table): void {
            $table->dropColumn('privacy_consented_at');
        });
    }
};
