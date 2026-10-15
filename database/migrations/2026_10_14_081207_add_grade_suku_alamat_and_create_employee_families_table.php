<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('employees', function (Blueprint $table) {
            if (! Schema::hasColumn('employees', 'grade')) {
                $table->string('grade', 20)->nullable()->after('pendidikan_terakhir');
            }
            if (! Schema::hasColumn('employees', 'suku')) {
                $table->string('suku', 50)->nullable()->after('grade');
            }
            if (! Schema::hasColumn('employees', 'alamat')) {
                $table->text('alamat')->nullable()->after('suku');
            }
        });

        if (! Schema::hasTable('employee_families')) {
            Schema::create('employee_families', function (Blueprint $table) {
                $table->id();
                $table->foreignId('employee_id')->constrained('employees')->cascadeOnDelete();
                $table->string('nama')->index();
                $table->string('hubungan', 50)->nullable()->index();
                $table->enum('jenis_kelamin', ['Laki-Laki', 'Perempuan', 'Tidak Diketahui'])->default('Tidak Diketahui');
                $table->date('tanggal_lahir')->nullable()->index();
                $table->string('tempat_lahir')->nullable();
                $table->text('keterangan')->nullable();
                $table->timestamps();

                $table->index(['employee_id', 'nama']);
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('employee_families');

        Schema::table('employees', function (Blueprint $table) {
            $dropCols = [];
            if (Schema::hasColumn('employees', 'alamat')) {
                $dropCols[] = 'alamat';
            }
            if (Schema::hasColumn('employees', 'suku')) {
                $dropCols[] = 'suku';
            }
            if (Schema::hasColumn('employees', 'grade')) {
                $dropCols[] = 'grade';
            }
            if (! empty($dropCols)) {
                $table->dropColumn($dropCols);
            }
        });
    }
};
