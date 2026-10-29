<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Pindahkan seluruh karyawan dari Afdeling Inti I–IV ke Afdeling reguler I–IV,
 * lalu hapus baris Afdeling Inti dari tabel bagians.
 *
 * Pemetaan:
 *   AFDELING INTI I   (id=9)  → AFDELING I   (id=1)
 *   AFDELING INTI II  (id=10) → AFDELING II  (id=2)
 *   AFDELING INTI III (id=11) → AFDELING III (id=3)
 *   AFDELING INTI IV  (id=12) → AFDELING IV  (id=4)
 */
return new class extends Migration
{
    /** @var array<int, int> map inti_id => regular_id */
    private array $map = [
        9 => 1,  // INTI I   → I
        10 => 2,  // INTI II  → II
        11 => 3,  // INTI III → III
        12 => 4,  // INTI IV  → IV
    ];

    public function up(): void
    {
        foreach ($this->map as $intiId => $regularId) {
            DB::table('employees')
                ->where('bagian_id', $intiId)
                ->update(['bagian_id' => $regularId]);
        }

        // Hapus Afdeling Inti dari tabel bagians
        DB::table('bagians')
            ->whereIn('id', array_keys($this->map))
            ->delete();
    }

    public function down(): void
    {
        // Kembalikan baris Afdeling Inti
        $intiBagians = [
            ['id' => 9,  'nama_bagian' => 'AFDELING INTI I'],
            ['id' => 10, 'nama_bagian' => 'AFDELING INTI II'],
            ['id' => 11, 'nama_bagian' => 'AFDELING INTI III'],
            ['id' => 12, 'nama_bagian' => 'AFDELING INTI IV'],
        ];

        foreach ($intiBagians as $bagian) {
            DB::table('bagians')->insertOrIgnore($bagian);
        }

        // Kembalikan karyawan ke Afdeling Inti (tidak mungkin tepat karena sudah di-mix)
        // Rollback hanya mengembalikan struktur tabel, bukan data individual.
    }
};
