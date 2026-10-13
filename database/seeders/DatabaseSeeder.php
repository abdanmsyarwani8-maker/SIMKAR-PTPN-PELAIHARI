<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Unit;
use App\Models\Bagian;
use App\Models\Jabatan;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // ── Buat Akun Admin dan User Default ──────────────────────
        User::firstOrCreate(
            ['email' => 'admin@simkar.ptpn'],
            [
                'name'     => 'Administrator SIMKAR',
                'password' => Hash::make('Admin@Simkar2024!'),
                'role'     => 'admin',
                'is_active' => true,
            ]
        );

        User::firstOrCreate(
            ['email' => 'user@simkar.ptpn'],
            [
                'name'     => 'Pengguna SIMKAR',
                'password' => Hash::make('User@Simkar2024!'),
                'role'     => 'user',
                'is_active' => true,
            ]
        );

        // ── Seed Unit dari Lampiran Rekon (35 lembar) ─────────────
        $units = [
            ['kode_unit' => '01 KANDIR',    'nama_unit' => 'Kantor Regional 5',           'nama_singkat' => 'Region Office'],
            ['kode_unit' => '44 KPJ',       'nama_unit' => 'KP Jakarta',                  'nama_singkat' => 'KPJ'],
            ['kode_unit' => '02 DKB-I',     'nama_unit' => 'Unit Group Kalimantan Barat', 'nama_singkat' => 'UGKB'],
            ['kode_unit' => '03 GUNME',     'nama_unit' => 'Kebun Gunung Meliau',         'nama_singkat' => 'GUNME'],
            ['kode_unit' => '04 PAGUN',     'nama_unit' => 'PKS Gunung Meliau',           'nama_singkat' => 'PAGUN'],
            ['kode_unit' => 'DEKAN',        'nama_unit' => 'Kebun Dekati',                'nama_singkat' => 'DEKAN'],
            ['kode_unit' => 'RIMBA',        'nama_unit' => 'Kebun Rimba',                 'nama_singkat' => 'RIMBA'],
            ['kode_unit' => 'PARBA',        'nama_unit' => 'Kebun Parba',                 'nama_singkat' => 'PARBA'],
            ['kode_unit' => 'GUMAS',        'nama_unit' => 'Kebun Gumas',                 'nama_singkat' => 'GUMAS'],
            ['kode_unit' => 'SINTA',        'nama_unit' => 'Kebun Sinta',                 'nama_singkat' => 'SINTA'],
            ['kode_unit' => 'NGABA',        'nama_unit' => 'Kebun Ngabang',               'nama_singkat' => 'NGABA'],
            ['kode_unit' => 'PANGA',        'nama_unit' => 'Kebun Panga',                 'nama_singkat' => 'PANGA'],
            ['kode_unit' => 'PARIN',        'nama_unit' => 'Kebun Parin',                 'nama_singkat' => 'PARIN'],
            ['kode_unit' => 'PAPAR',        'nama_unit' => 'Kebun Papar',                 'nama_singkat' => 'PAPAR'],
            ['kode_unit' => 'BAYAN',        'nama_unit' => 'Kebun Bayan',                 'nama_singkat' => 'BAYAN'],
            ['kode_unit' => 'PAKEM',        'nama_unit' => 'Kebun Pakem',                 'nama_singkat' => 'PAKEM'],
            ['kode_unit' => 'UGKT',         'nama_unit' => 'Unit Group Kalimantan Tengah','nama_singkat' => 'UGKT'],
            ['kode_unit' => 'TABAR',        'nama_unit' => 'Kebun Tabar',                 'nama_singkat' => 'TABAR'],
            ['kode_unit' => 'TAJAT',        'nama_unit' => 'Kebun Tajat',                 'nama_singkat' => 'TAJAT'],
            ['kode_unit' => 'PANDAWA',      'nama_unit' => 'Kebun Pandawa I',             'nama_singkat' => 'PANDAWA'],
            ['kode_unit' => 'LOKAL',        'nama_unit' => 'Kebun Lokal',                 'nama_singkat' => 'LOKAL'],
            ['kode_unit' => 'PASAM',        'nama_unit' => 'Kebun Pasam',                 'nama_singkat' => 'PASAM'],
            ['kode_unit' => 'PALPI',        'nama_unit' => 'Kebun Palpi',                 'nama_singkat' => 'PALPI'],
            ['kode_unit' => 'UGKST',        'nama_unit' => 'Unit Group Kalimantan Selatan Timur', 'nama_singkat' => 'UGKST'],
            ['kode_unit' => 'DASAL',        'nama_unit' => 'Kebun Danau Salak',           'nama_singkat' => 'DASAL'],
            ['kode_unit' => 'TAMBA',        'nama_unit' => 'Kebun Tamba',                 'nama_singkat' => 'TAMBA'],
            ['kode_unit' => '39 BALIN',     'nama_unit' => 'Kebun Balin',                 'nama_singkat' => 'BALIN'],
            ['kode_unit' => '40 PELAI',     'nama_unit' => 'Kebun Pelaihari',             'nama_singkat' => 'PELAI'],
            ['kode_unit' => '41 KUMAI',     'nama_unit' => 'Kebun Kumai',                 'nama_singkat' => 'KUMAI'],
            ['kode_unit' => '42 PALAI',     'nama_unit' => 'PKS Pelaihari',               'nama_singkat' => 'PALAI'],
            ['kode_unit' => 'PKS PAMUKAN',  'nama_unit' => 'PKS Pamukan',                 'nama_singkat' => 'PKS PAMUKAN'],
            ['kode_unit' => '48 KEBUN PAMUKAN', 'nama_unit' => 'Kebun Pamukan',           'nama_singkat' => 'KEBUN PAMUKAN'],
            ['kode_unit' => 'P3B2',         'nama_unit' => 'Kebun Kemitraan Kalimantan Barat', 'nama_singkat' => 'Kebun Kemitraan Kalbar'],
            ['kode_unit' => 'Proyek Batubara', 'nama_unit' => 'Proyek Batubara Danau Salak & Buntok', 'nama_singkat' => 'PROYEK BATUBARA'],
            ['kode_unit' => 'Raren Batuah', 'nama_unit' => 'Kebun Raren Batuah',          'nama_singkat' => 'RAREN BATUAH'],
        ];

        foreach ($units as $unit) {
            Unit::firstOrCreate(['kode_unit' => $unit['kode_unit']], $unit);
        }

        // ── Seed Bagian Umum ────────────────────────────────────
        $bagians = [
            'AFDELING I', 'AFDELING II', 'AFDELING III', 'AFDELING IV',
            'AFDELING V', 'AFDELING VI', 'AFDELING VII', 'AFDELING VIII',
            'AFDELING INTI I', 'AFDELING INTI II', 'AFDELING INTI III', 'AFDELING INTI IV',
            'BAGIAN TATA USAHA', 'BAGIAN PERSONALIA & TEKNIK', 'BAGIAN TANAMAN',
            'BAGIAN KEAMANAN', 'BAGIAN PENGOLAHAN', 'BAGIAN TEKNIK',
            'BAGIAN QUALITY ASSURANCE', 'BAGIAN AKUNTANSI & KEUANGAN',
            'BAGIAN PENGADAAN & TEKNOLOGI INFORMASI', 'BAGIAN SDM DAN SISTEM MANAJEMEN',
            'PLASMA', 'PLASMA WILAYAH NGABANG', 'PLASMA WILAYAH PARINDU',
            'PLASMA WILAYAH GUNUNG EMAS', 'PLASMA WILAYAH RIMBA BELIAN',
            'PLASMA WILAYAH KEMBAYAN',
        ];

        foreach ($bagians as $nama) {
            Bagian::firstOrCreate(['nama_bagian' => $nama]);
        }

        // ── Seed Jabatan dari data aktual Excel ────────────────
        $jabatans = [
            'MANAJER', 'ASISTEN TATA USAHA', 'ASISTEN TANAMAN', 'ASISTEN TEKNIK',
            'KRANI AFDELING', 'KRANI PERSONALIA', 'KRANI CEK SAWIT', 'KRANI KEUANGAN DAN ANGGARAN',
            'KRANI SEKRETARIAT', 'KRANI ADMINISTRASI', 'KRANI ANGSURAN KREDIT',
            'KRANI DOKUMEN SISTEM DAN SERTIFIKASI', 'KRANI BIDANG UMUM',
            'PEMANEN', 'MANDOR PANEN', 'MANDOR PEMELIHARAAN', 'MANDOR PLASMA / WILAYAH',
            'MANDOR SORTASI', 'MANDOR TEKNIK',
            'SATPAM', 'DANTON',
            'SOPIR TRUK', 'SOPIR',
            'OPERATOR ALAT BERAT', 'OPERATOR WATER TREATMENT',
            'MEKANIK', 'PEMBANTU MEKANIK',
            'PEMBANTU OPERATOR BOILER', 'PEMBANTU OPERATOR KERNEL',
            'PEMBANTU OPERATOR REBUSAN',
            'TIMBANG BRONDOLAN', 'KCS', 'MBT',
            'PETUGAS KEAMANAN', 'PETUGAS RAIL TRACK', 'PETUGAS GUDANG',
            'PETUGAS EMPLASMENT', 'PETUGAS KEAGAMAAN / KAUM / BILAL',
            'PETUGAS GLOBAL TELING', 'PETUGAS KESEHATAN (POLIKLINIK)',
            'PETUGAS MESS INTEGRASI',
            'GURU TK', 'PELAYAN KANTOR', 'PEMELIHARAAN',
            'DEDICATED LO',
            'KEPALA BAGIAN AKUNTANSI & KEUANGAN',
            'KEPALA BAGIAN PENGADAAN & TEKNOLOGI INFORMASI',
            'KEPALA BAGIAN SUMBER DAYA MANUSIA',
        ];

        foreach ($jabatans as $nama) {
            Jabatan::firstOrCreate(['nama_jabatan' => $nama]);
        }
    }
}
