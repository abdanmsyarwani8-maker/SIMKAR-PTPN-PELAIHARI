<?php

use App\Models\Bagian;
use App\Models\Employee;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (app()->environment('testing')) {
            return;
        }

        $bagianAfd2 = Bagian::where('nama_bagian', 'like', '%AFDELING II%')->where('nama_bagian', 'not like', '%INTI%')->first()?->id
            ?? Bagian::firstOrCreate(['nama_bagian' => 'AFDELING II'])->id;
        $bagianAfd3 = Bagian::where('nama_bagian', 'like', '%AFDELING III%')->where('nama_bagian', 'not like', '%INTI%')->first()?->id
            ?? Bagian::firstOrCreate(['nama_bagian' => 'AFDELING III'])->id;
        $bagianAfd4 = Bagian::where('nama_bagian', 'like', '%AFDELING IV%')->where('nama_bagian', 'not like', '%INTI%')->first()?->id
            ?? Bagian::firstOrCreate(['nama_bagian' => 'AFDELING IV'])->id;

        $bhlData = [
            // Afdeling II
            [
                'bagian_id' => $bagianAfd2,
                'nama' => 'Saifuddin',
                'tanggal_lahir' => '1979-04-10',
                'tanggal_mulai_kerja' => '2026-08-01',
            ],
            [
                'bagian_id' => $bagianAfd2,
                'nama' => 'Muh. Dedi Fathul Rohman',
                'tanggal_lahir' => '1993-09-08',
                'tanggal_mulai_kerja' => '2026-08-01',
            ],
            [
                'bagian_id' => $bagianAfd2,
                'nama' => 'Suparlan',
                'tanggal_lahir' => '1989-12-30',
                'tanggal_mulai_kerja' => '2026-09-01',
            ],
            [
                'bagian_id' => $bagianAfd2,
                'nama' => 'Subhan',
                'tanggal_lahir' => '1986-03-21',
                'tanggal_mulai_kerja' => '2026-09-25',
            ],
            [
                'bagian_id' => $bagianAfd2,
                'nama' => 'Heri Edi Candra',
                'tanggal_lahir' => '1993-12-31',
                'tanggal_mulai_kerja' => '2026-09-28',
            ],
            [
                'bagian_id' => $bagianAfd2,
                'nama' => 'Rico Pebriantara',
                'tanggal_lahir' => '2005-12-13',
                'tanggal_mulai_kerja' => '2026-10-12',
            ],
            [
                'bagian_id' => $bagianAfd2,
                'nama' => 'Muhamad Sahlan',
                'tanggal_lahir' => '1988-07-01',
                'tanggal_mulai_kerja' => '2026-10-12',
            ],
            [
                'bagian_id' => $bagianAfd2,
                'nama' => 'Agustiawan',
                'tanggal_lahir' => '2006-09-18',
                'tanggal_mulai_kerja' => '2026-10-20',
            ],

            // Afdeling III
            [
                'bagian_id' => $bagianAfd3,
                'nama' => 'Nasriadi',
                'tanggal_lahir' => '1967-06-10',
                'tanggal_mulai_kerja' => '2026-03-27',
            ],
            [
                'bagian_id' => $bagianAfd3,
                'nama' => 'Hilpan',
                'tanggal_lahir' => '2008-12-04',
                'tanggal_mulai_kerja' => '2026-04-06',
            ],
            [
                'bagian_id' => $bagianAfd3,
                'nama' => 'Muhammad Fihiruddin Nasri',
                'tanggal_lahir' => '1994-12-31',
                'tanggal_mulai_kerja' => '2026-08-19',
            ],
            [
                'bagian_id' => $bagianAfd3,
                'nama' => 'Amril Wahyu Hidayatullah',
                'tanggal_lahir' => '1996-12-31',
                'tanggal_mulai_kerja' => '2026-08-21',
            ],
            [
                'bagian_id' => $bagianAfd3,
                'nama' => 'Subardan',
                'tanggal_lahir' => '1982-07-01',
                'tanggal_mulai_kerja' => '2026-08-21',
            ],
            [
                'bagian_id' => $bagianAfd3,
                'nama' => 'Samuel Sabneno',
                'tanggal_lahir' => '1993-07-28',
                'tanggal_mulai_kerja' => '2026-10-21',
            ],
            [
                'bagian_id' => $bagianAfd3,
                'nama' => 'Samsul Hadi',
                'tanggal_lahir' => '1988-06-03',
                'tanggal_mulai_kerja' => '2026-10-22',
            ],
            [
                'bagian_id' => $bagianAfd3,
                'nama' => 'Roy Kurniawan',
                'tanggal_lahir' => '1999-06-26',
                'tanggal_mulai_kerja' => '2026-10-22',
            ],
            [
                'bagian_id' => $bagianAfd3,
                'nama' => 'Roni Mustiadi',
                'tanggal_lahir' => '1993-07-01',
                'tanggal_mulai_kerja' => '2026-10-22',
            ],

            // Afdeling IV
            [
                'bagian_id' => $bagianAfd4,
                'nama' => 'Sukriadi',
                'tanggal_lahir' => '1980-07-01',
                'tanggal_mulai_kerja' => '2026-04-07',
            ],
            [
                'bagian_id' => $bagianAfd4,
                'nama' => 'Yan Dana Saputra',
                'tanggal_lahir' => '1999-08-15',
                'tanggal_mulai_kerja' => '2026-04-07',
            ],
            [
                'bagian_id' => $bagianAfd4,
                'nama' => 'Sumingan',
                'tanggal_lahir' => '1982-04-14',
                'tanggal_mulai_kerja' => '2026-05-04',
            ],
            [
                'bagian_id' => $bagianAfd4,
                'nama' => 'Dandi Setiawan',
                'tanggal_lahir' => '2005-01-02',
                'tanggal_mulai_kerja' => '2026-05-15',
            ],
        ];

        foreach ($bhlData as $row) {
            Employee::firstOrCreate(
                [
                    'nama' => $row['nama'],
                    'status_karyawan' => 'BHL',
                ],
                [
                    'bagian_id' => $row['bagian_id'],
                    'tanggal_lahir' => $row['tanggal_lahir'],
                    'tanggal_mulai_kerja' => $row['tanggal_mulai_kerja'],
                    'jenis_kelamin' => 'Tidak Diketahui',
                    'domisili' => null,
                    'personnel_number' => null,
                    'pers_no' => null,
                    'golongan' => 'BHL',
                    'sumber_lembar' => 'BHL',
                    'sumber_file' => 'Data BHL',
                    'is_active' => true,
                ]
            );
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Employee::where('status_karyawan', 'BHL')
            ->where('sumber_lembar', 'BHL')
            ->forceDelete();
    }
};
