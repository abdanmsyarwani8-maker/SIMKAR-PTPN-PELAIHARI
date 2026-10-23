<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('employees', function (Blueprint $table) {
            $table->string('domisili', 30)->nullable()->after('alamat')->index();
        });

        $keywords = [
            'Banjarmasin', 'Banjarbaru', 'Banjar', 'Martapura',
            'Pelaihari', 'Tala', 'Tanah Laut',
            'Kotabaru', 'Batulicin', 'Tanah Bumbu',
            'Kandangan', 'Hulu Sungai Selatan',
            'Amuntai', 'Hulu Sungai Utara',
            'Barabai', 'Hulu Sungai Tengah',
            'Rantau', 'Tapin',
            'Marabahan', 'Barito Kuala', 'Batola',
            'Jorong', 'Takisung', 'Bajuin',
            'Pagatan', 'Satui', 'Angsana',
            'Binuang', 'Tambang Ulang',
            'Bati-Bati', 'Kurau', 'Batu Ampar',
            'Sungai Tabuk', 'Kertak Hanyar', 'Gambut',
            'Landasan Ulin', 'Liang Anggang',
            'Astambul', 'Karang Intan',
            'Lok Baintan',
            'Kalsel', 'Kalimantan Sel',
        ];

        DB::table('employees')->whereNull('domisili')->update(['domisili' => 'Non-Lokal']);

        DB::table('employees')->where(function ($q) use ($keywords) {
            foreach ($keywords as $kw) {
                $q->orWhere('alamat', 'like', "%{$kw}%");
            }
        })->update(['domisili' => 'Lokal']);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('employees', function (Blueprint $table) {
            $table->dropColumn('domisili');
        });
    }
};
