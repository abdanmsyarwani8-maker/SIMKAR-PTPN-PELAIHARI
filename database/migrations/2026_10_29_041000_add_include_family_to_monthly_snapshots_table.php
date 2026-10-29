<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('monthly_snapshots')) {
            Schema::table('monthly_snapshots', function (Blueprint $table) {
                if (! Schema::hasColumn('monthly_snapshots', 'include_family')) {
                    $table->boolean('include_family')->default(true)->after('jumlah_karyawan');
                }
            });

            try {
                Schema::table('monthly_snapshots', function (Blueprint $table) {
                    $table->dropUnique(['bulan', 'tahun']);
                });
            } catch (Throwable) {
                // Abaikan jika index tidak ditemukan
            }

            try {
                Schema::table('monthly_snapshots', function (Blueprint $table) {
                    $table->unique(['bulan', 'tahun', 'include_family']);
                });
            } catch (Throwable) {
                // Abaikan jika index sudah ada atau tidak didukung pada engine tertentu
            }
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('monthly_snapshots')) {
            Schema::table('monthly_snapshots', function (Blueprint $table) {
                if (Schema::hasColumn('monthly_snapshots', 'include_family')) {
                    try {
                        $table->dropUnique(['bulan', 'tahun', 'include_family']);
                    } catch (Throwable) {
                    }
                    $table->dropColumn('include_family');
                }
            });
        }
    }
};
