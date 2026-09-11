<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $duplicateNips = DB::table('members')
            ->selectRaw('LOWER(TRIM(nip)) as normalized_nip')
            ->whereNotNull('nip')
            ->whereRaw("TRIM(nip) != ''")
            ->groupByRaw('LOWER(TRIM(nip))')
            ->havingRaw('COUNT(*) > 1')
            ->limit(10)
            ->pluck('normalized_nip');

        if ($duplicateNips->isNotEmpty()) {
            throw new RuntimeException(
                'Migrasi dibatalkan karena terdapat NIP anggota duplikat. Rapikan data duplikat lalu jalankan migrasi kembali.'
            );
        }

        DB::table('members')
            ->whereNotNull('nip')
            ->update(['nip' => DB::raw("NULLIF(TRIM(nip), '')")]);

        Schema::table('members', function (Blueprint $table): void {
            $table->dropIndex('members_nip_index');
            $table->unique('nip', 'members_nip_unique');
        });
    }

    public function down(): void
    {
        Schema::table('members', function (Blueprint $table): void {
            $table->dropUnique('members_nip_unique');
            $table->index('nip', 'members_nip_index');
        });
    }
};
