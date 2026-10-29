<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Pindahkan seluruh karyawan dari Afdeling Inti I-IV ke Afdeling reguler I-IV,
 * hapus Afdeling Inti dari tabel bagians, dan hapus Afdeling V, VI, VII, VIII.
 */
return new class extends Migration
{
    public function up(): void
    {
        // 1. Pastikan Afdeling reguler I-IV tersedia dan ambil ID-nya
        $regularMap = [
            1 => $this->getOrCreateBagianId('AFDELING I'),
            2 => $this->getOrCreateBagianId('AFDELING II'),
            3 => $this->getOrCreateBagianId('AFDELING III'),
            4 => $this->getOrCreateBagianId('AFDELING IV'),
        ];

        // 2. Cari semua ID bagian yang merupakan Afdeling Inti I-IV
        $intiMap = [
            1 => $this->findIntiBagianIds([1, 'I']),
            2 => $this->findIntiBagianIds([2, 'II']),
            3 => $this->findIntiBagianIds([3, 'III']),
            4 => $this->findIntiBagianIds([4, 'IV']),
        ];

        // Juga sertakan hardcoded IDs jika ada dari seeder awal (9, 10, 11, 12)
        $legacyIntiIds = [1 => 9, 2 => 10, 3 => 11, 4 => 12];
        foreach ($legacyIntiIds as $num => $legacyId) {
            if (! in_array($legacyId, $intiMap[$num], true)) {
                $exists = DB::table('bagians')->where('id', $legacyId)->exists();
                if ($exists) {
                    $intiMap[$num][] = $legacyId;
                }
            }
        }

        // Pindahkan karyawan dari Afdeling Inti ke Afdeling Reguler
        foreach ($intiMap as $num => $intiIds) {
            $targetRegularId = $regularMap[$num];
            if (! empty($intiIds)) {
                // Update semua karyawan di Afdeling Inti ini ke Afdeling Reguler terkait
                DB::table('employees')
                    ->whereIn('bagian_id', $intiIds)
                    ->update(['bagian_id' => $targetRegularId]);

                // Hapus Afdeling Inti dari tabel bagians
                DB::table('bagians')
                    ->whereIn('id', $intiIds)
                    ->delete();
            }
        }

        // 3. Hapus Afdeling V, VI, VII, VIII
        $afd58Patterns = [
            'AFDELING V', 'AFDELING 5',
            'AFDELING VI', 'AFDELING 6',
            'AFDELING VII', 'AFDELING 7',
            'AFDELING VIII', 'AFDELING 8',
        ];

        $afd58Ids = DB::table('bagians')
            ->where(function ($q) use ($afd58Patterns) {
                foreach ($afd58Patterns as $p) {
                    $q->orWhere('nama_bagian', 'LIKE', $p);
                }
            })
            ->pluck('id')
            ->toArray();

        // Sertakan juga ID 5, 6, 7, 8 jika nama_bagian mengandung AFDELING
        $legacyAfd58 = DB::table('bagians')
            ->whereIn('id', [5, 6, 7, 8])
            ->where('nama_bagian', 'LIKE', '%AFDELING%')
            ->pluck('id')
            ->toArray();
        $afd58Ids = array_unique(array_merge($afd58Ids, $legacyAfd58));

        if (! empty($afd58Ids)) {
            // Lepaskan keterikatan karyawan jika ada karyawan yang terdaftar di afdeling 5-8
            DB::table('employees')
                ->whereIn('bagian_id', $afd58Ids)
                ->update(['bagian_id' => null]);

            // Hapus dari tabel bagians
            DB::table('bagians')
                ->whereIn('id', $afd58Ids)
                ->delete();
        }
    }

    public function down(): void
    {
        // Rollback struktur dasar
        $recreate = [
            'AFDELING V', 'AFDELING VI', 'AFDELING VII', 'AFDELING VIII',
            'AFDELING INTI I', 'AFDELING INTI II', 'AFDELING INTI III', 'AFDELING INTI IV',
        ];

        foreach ($recreate as $nama) {
            $exists = DB::table('bagians')->where('nama_bagian', $nama)->exists();
            if (! $exists) {
                DB::table('bagians')->insert(['nama_bagian' => $nama]);
            }
        }
    }

    protected function getOrCreateBagianId(string $name): int
    {
        $row = DB::table('bagians')->where('nama_bagian', $name)->first();
        if ($row) {
            return (int) $row->id;
        }

        return (int) DB::table('bagians')->insertGetId(['nama_bagian' => $name]);
    }

    /**
     * @param  array<int|string>  $suffixes
     * @return array<int>
     */
    protected function findIntiBagianIds(array $suffixes): array
    {
        $query = DB::table('bagians')->where('nama_bagian', 'LIKE', '%INTI%');

        $query->where(function ($q) use ($suffixes) {
            foreach ($suffixes as $s) {
                $q->orWhere('nama_bagian', 'LIKE', "%INTI {$s}")
                    ->orWhere('nama_bagian', 'LIKE', "%INTI {$s}%")
                    ->orWhere('nama_bagian', 'LIKE', "%INTI_{$s}%");
            }
        });

        // Pastikan tidak mencocokkan roman numeral yang lebih panjang (contoh: I tidak boleh mencocokkan II atau III atau IV)
        $rows = $query->get();
        $ids = [];

        foreach ($rows as $row) {
            $nama = strtoupper(trim((string) $row->nama_bagian));
            foreach ($suffixes as $s) {
                $targetPattern = '/\bINTI\s+'.preg_quote((string) $s, '/').'\b/i';
                if (preg_match($targetPattern, $nama)) {
                    $ids[] = (int) $row->id;
                    break;
                }
            }
        }

        return array_unique($ids);
    }
};
