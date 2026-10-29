<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('monthly_snapshots', function (Blueprint $table) {
            $table->id();
            $table->unsignedSmallInteger('bulan'); // 1-12
            $table->unsignedSmallInteger('tahun'); // e.g. 2026
            $table->string('nama_file');            // nama file Excel yang disimpan
            $table->string('path');                 // path di storage
            $table->unsignedInteger('jumlah_karyawan')->default(0);
            $table->boolean('include_family')->default(true);
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->text('catatan')->nullable();
            $table->timestamps();

            $table->unique(['bulan', 'tahun', 'include_family']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('monthly_snapshots');
    }
};
