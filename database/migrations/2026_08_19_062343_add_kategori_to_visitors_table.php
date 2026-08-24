<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('visitors', function (Blueprint $table) {
            if (!Schema::hasColumn('visitors', 'kategori')) {
                $table->string('kategori', 30)->default('umum')->after('id');
            }
            $table->string('no_hp', 100)->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('visitors', function (Blueprint $table) {
            if (Schema::hasColumn('visitors', 'kategori')) {
                $table->dropColumn('kategori');
            }
            $table->string('no_hp', 20)->nullable()->change();
        });
    }
};
