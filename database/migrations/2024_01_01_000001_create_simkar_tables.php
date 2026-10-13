<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Tabel referensi Unit/Kebun (dari kolom Unit & Personnel Subarea Excel)
        Schema::create('units', function (Blueprint $table) {
            $table->id();
            $table->string('kode_unit', 20)->unique();       // "40 PELAI"
            $table->string('nama_unit');                      // "Kb Pelaihari"
            $table->string('nama_singkat', 50)->nullable();   // "PELAI"
            $table->string('lokasi')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->index('kode_unit');
        });

        // Tabel referensi Bagian/Organizational Unit
        Schema::create('bagians', function (Blueprint $table) {
            $table->id();
            $table->string('nama_bagian');                    // "AFDELING I", "BAGIAN TATA USAHA"
            $table->string('kode_bagian', 20)->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->index('nama_bagian');
        });

        // Tabel referensi Jabatan/Position
        Schema::create('jabatans', function (Blueprint $table) {
            $table->id();
            $table->string('nama_jabatan');                   // "PEMANEN", "SATPAM", "MANAJER"
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->index('nama_jabatan');
        });

        // Tabel utama karyawan — struktur dari Lampiran Rekon (format SAP)
        Schema::create('employees', function (Blueprint $table) {
            $table->id();
            // Identifier dari sumber Excel
            $table->string('pers_no', 20)->nullable()->index();           // Pers.No. (nomor urut SAP)
            $table->string('personnel_number', 20)->nullable()->index();  // Personnel Number (NIK SAP: 13xxxxxxx)
            $table->string('nik_lokal', 30)->nullable()->index();         // NIK dari file internal Pelaihari

            // Data personal
            $table->string('nama')->index();                              // Nama lengkap
            $table->date('tanggal_lahir')->nullable();
            $table->date('tanggal_mulai_kerja')->nullable();
            $table->enum('jenis_kelamin', ['Laki-Laki', 'Perempuan', 'Tidak Diketahui'])->default('Tidak Diketahui');
            $table->string('agama', 50)->nullable();                      // Islam, Protestan, Old Catholic, dll
            $table->string('pendidikan_terakhir', 20)->nullable();        // SD, SLTP, SLTA, D3, S1, S2, dll

            // Relasi ke tabel referensi
            $table->unsignedBigInteger('unit_id')->nullable()->index();
            $table->unsignedBigInteger('bagian_id')->nullable()->index();
            $table->unsignedBigInteger('jabatan_id')->nullable()->index();
            $table->foreign('unit_id', 'fk_emp_unit')->references('id')->on('units')->nullOnDelete();
            $table->foreign('bagian_id', 'fk_emp_bagian')->references('id')->on('bagians')->nullOnDelete();
            $table->foreign('jabatan_id', 'fk_emp_jabatan')->references('id')->on('jabatans')->nullOnDelete();

            // Status dan kategori
            $table->string('status_karyawan', 50)->nullable()->index();   // Karpim-Tetap, Karpel-Tetap, dll
            $table->string('golongan', 20)->nullable();                   // IA/0, IB, dll (dari KTNG)
            $table->string('grup_golongan', 20)->nullable();              // IA, IA(NG), MBT
            $table->string('grup_jabatan', 50)->nullable();               // Petugas, Mandor, Operator
            $table->string('no_pkwt', 100)->nullable();                   // Nomor kontrak PKWT

            // Sumber data (dari lembar Excel mana)
            $table->string('sumber_lembar', 20)->nullable()->index();     // "PELAI", "Region Office", dll
            $table->string('sumber_file', 100)->nullable();               // nama file Excel asal

            // Status sistem
            $table->boolean('is_active')->default(true)->index();
            $table->text('catatan')->nullable();
            $table->timestamps();
            $table->softDeletes();

            // Index gabungan untuk pencarian
            $table->index(['nama', 'status_karyawan']);
            $table->index(['unit_id', 'bagian_id']);
            $table->index(['jenis_kelamin', 'agama']);
            $table->index(['tanggal_lahir']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('employees');
        Schema::dropIfExists('jabatans');
        Schema::dropIfExists('bagians');
        Schema::dropIfExists('units');
    }
};
