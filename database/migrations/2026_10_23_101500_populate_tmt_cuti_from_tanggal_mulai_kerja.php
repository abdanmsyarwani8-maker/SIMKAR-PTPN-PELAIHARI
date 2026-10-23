<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Isi kolom tmt_cuti dari tanggal_mulai_kerja yang sudah ada di database
        DB::table('employees')
            ->whereNull('tmt_cuti')
            ->whereNotNull('tanggal_mulai_kerja')
            ->update([
                'tmt_cuti' => DB::raw('tanggal_mulai_kerja'),
            ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Tidak perlu mengosongkan tmt_cuti pada rollback
    }
};
