<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Daftar nama karyawan Karpim (Karyawan Pimpinan).
     * Sumber: dokumen daftar Karpim yang diberikan.
     * Seluruh karyawan dengan status 'Karpim & Karpel' yang TIDAK ada di daftar ini
     * akan diubah menjadi 'Karpel'.
     */
    private array $namaKarpim = [
        'Abdullah Syafii',
        'Adam Brahmantyo Yogatama',
        'Agus Handoko',
        'Anwar Anshari',
        'Ari Susanto',
        'Arnold Widiarto',
        'Asrofi',
        'Teuku Akil Faza',
    ];

    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Set semua yang Karpim & Karpel → Karpel terlebih dahulu
        DB::table('employees')
            ->where('status_karyawan', 'Karpim & Karpel')
            ->update(['status_karyawan' => 'Karpel']);

        // Lalu ubah yang namanya ada di daftar Karpim → Karpim
        DB::table('employees')
            ->whereIn('nama', $this->namaKarpim)
            ->where('status_karyawan', 'Karpel')
            ->update(['status_karyawan' => 'Karpim']);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Kembalikan Karpim & Karpel ke nilai gabungan semula
        DB::table('employees')
            ->whereIn('status_karyawan', ['Karpim', 'Karpel'])
            ->update(['status_karyawan' => 'Karpim & Karpel']);
    }
};
