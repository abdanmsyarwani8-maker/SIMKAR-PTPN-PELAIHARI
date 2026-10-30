<?php

use App\Models\Bagian;
use App\Models\Employee;
use App\Models\Jabatan;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (! Schema::hasTable('jabatans')) {
            return;
        }

        $newJabatans = [
            'KRANI TATA USAHA',
            'KRANI TEKNIK',
            'KRANI PRODUKSI',
            'KRANI AKUNTANSI',
            'KRANI FINANSIL',
            'PETUGAS GLOBAL TELING AFD I',
            'PETUGAS GLOBAL TELING AFDELING I',
        ];

        $jabatanModels = [];
        foreach ($newJabatans as $nama) {
            $jabatanModels[$nama] = Jabatan::firstOrCreate(['nama_jabatan' => $nama]);
        }

        // Sinkronisasi data karyawan yang jabatannya sebelumnya tercatat di bagian dari file excel
        if (Schema::hasTable('employees') && Schema::hasTable('bagians')) {
            $mapping = [
                'KRANI AKUNTANSI' => 'KRANI AKUNTANSI',
                'KRANI FINANSIL' => 'KRANI FINANSIL',
                'KRANI PRODUKSI' => 'KRANI PRODUKSI',
                'KRANI TEKNIK' => 'KRANI TEKNIK',
                'PETUGAS GLOBAL TELING AFD I' => 'PETUGAS GLOBAL TELING AFD I',
            ];

            $afdeling1 = Bagian::where('nama_bagian', 'AFDELING I')->first();

            foreach ($mapping as $bagianNama => $jabatanNama) {
                $bagian = Bagian::where('nama_bagian', $bagianNama)->first();
                $targetJabatan = $jabatanModels[$jabatanNama] ?? null;

                if ($bagian && $targetJabatan) {
                    $employees = Employee::where('bagian_id', $bagian->id)->get();
                    foreach ($employees as $emp) {
                        $emp->jabatan_id = $targetJabatan->id;
                        // Untuk Petugas Global Teling Afd I, arahkan bagian ke AFDELING I
                        if ($bagianNama === 'PETUGAS GLOBAL TELING AFD I' && $afdeling1) {
                            $emp->bagian_id = $afdeling1->id;
                        }
                        $emp->save();
                    }
                }
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Tetap dipertahankan agar tidak menghilangkan referensi jabatan
    }
};
