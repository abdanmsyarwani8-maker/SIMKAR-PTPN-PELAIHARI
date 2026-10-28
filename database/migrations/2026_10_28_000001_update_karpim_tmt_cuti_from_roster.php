<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Data 8 Karpim Kebun Pelaihari sesuai dokumen Roster Staf:
     * NIK => TMT Diangkat Staf (Acuan Cuti)
     */
    private array $karpimTmtCuti = [
        '13004520' => '2010-01-01', // ANWAR ANSHARI (Manajer) - Diangkat 01-01-2010
        '13002791' => '1999-09-01', // AGUS HANDOKO (Askep Tanaman) - Diangkat 01-09-1999
        '13001734' => '2019-12-01', // ASROFI (Asisten TUK) - Diangkat 01-12-2019
        '13005464' => '2022-07-01', // ARNOLD WIDIARTO (Asisten Personalia & Teknik) - Diangkat 01-07-2022
        '13006998' => '2021-04-01', // ABDULLAH SYAFII (Asisten Afd I) - Diangkat 01-04-2021
        '19003363' => '2025-07-01', // TEUKU AKIL FAZA (Asisten Afd II) - Diangkat 01-07-2025
        '13004850' => '2012-06-01', // ARI SUSANTO (Asisten Afd III) - Diangkat 01-06-2012
        '13007684' => '2024-06-01', // ADAM BRAHMANTYO YOGATAMA (Asisten Afd IV) - Diangkat 01-06-2024
    ];

    /**
     * Run the migrations.
     */
    public function up(): void
    {
        foreach ($this->karpimTmtCuti as $nik => $tmtCuti) {
            DB::table('employees')
                ->where('personnel_number', $nik)
                ->update([
                    'tmt_cuti' => $tmtCuti,
                ]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::table('employees')
            ->whereIn('personnel_number', array_keys($this->karpimTmtCuti))
            ->update([
                'tmt_cuti' => DB::raw('tanggal_mulai_kerja'),
            ]);
    }
};
