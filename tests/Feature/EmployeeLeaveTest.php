<?php

namespace Tests\Feature;

use App\Models\Bagian;
use App\Models\Employee;
use App\Models\Jabatan;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PhpOffice\PhpSpreadsheet\IOFactory;
use Tests\TestCase;

class EmployeeLeaveTest extends TestCase
{
    use RefreshDatabase;

    public function test_cuti_tahunan_follows_appointment_month_in_indonesian(): void
    {
        $employee = Employee::create([
            'personnel_number' => '10001',
            'nama' => 'Ahmad Fauzi',
            'jenis_kelamin' => 'Laki-Laki',
            'tanggal_mulai_kerja' => Carbon::create(1999, 8, 21),
            'status_karyawan' => 'Karpel',
            'is_active' => true,
        ]);

        $this->assertSame('Agustus', $employee->bulan_jatuh_tempo_cuti);

        $status2025 = $employee->getCutiStatusForYear(2025);
        $this->assertSame('Cuti Tahunan', $status2025['jenis_cuti']);
        $this->assertSame('Agustus', $status2025['bulan_jatuh_tempo']);
        $this->assertFalse($status2025['is_cuti_panjang']);
    }

    public function test_cuti_panjang_recurrence_formula_6_years(): void
    {
        // 1. Tahun pengangkatan 2020: 2026, 2032, 2038
        $emp2020 = Employee::create([
            'nama' => 'Karyawan 2020',
            'jenis_kelamin' => 'Laki-Laki',
            'tanggal_mulai_kerja' => Carbon::create(2020, 3, 15),
            'is_active' => true,
        ]);

        $status2025 = $emp2020->getCutiStatusForYear(2025);
        $this->assertSame('Cuti Tahunan', $status2025['jenis_cuti']);
        $this->assertFalse($status2025['is_cuti_panjang']);

        $status2026 = $emp2020->getCutiStatusForYear(2026);
        $this->assertSame('Cuti Panjang', $status2026['jenis_cuti']);
        $this->assertTrue($status2026['is_cuti_panjang']);
        $this->assertSame(1, $status2026['siklus_ke']);

        $status2032 = $emp2020->getCutiStatusForYear(2032);
        $this->assertSame('Cuti Panjang', $status2032['jenis_cuti']);
        $this->assertSame(2, $status2032['siklus_ke']);

        // 2. Tahun pengangkatan 2014: 2020, 2026, 2032
        $emp2014 = Employee::create([
            'nama' => 'Karyawan 2014',
            'jenis_kelamin' => 'Laki-Laki',
            'tanggal_mulai_kerja' => Carbon::create(2014, 5, 10),
            'is_active' => true,
        ]);

        $this->assertTrue($emp2014->getCutiStatusForYear(2020)['is_cuti_panjang']);
        $this->assertTrue($emp2014->getCutiStatusForYear(2026)['is_cuti_panjang']);
        $this->assertTrue($emp2014->getCutiStatusForYear(2032)['is_cuti_panjang']);
        $this->assertFalse($emp2014->getCutiStatusForYear(2025)['is_cuti_panjang']);

        // 3. Tahun pengangkatan 1990: 1996, 2002, 2008, 2014, 2020, 2026
        $emp1990 = Employee::create([
            'nama' => 'Karyawan 1990',
            'jenis_kelamin' => 'Laki-Laki',
            'tanggal_mulai_kerja' => Carbon::create(1990, 11, 1),
            'is_active' => true,
        ]);

        foreach ([1996, 2002, 2008, 2014, 2020, 2026] as $yr) {
            $this->assertTrue($emp1990->getCutiStatusForYear($yr)['is_cuti_panjang'], "Gagal pada tahun {$yr}");
        }
    }

    public function test_tmt_cuti_fallback_when_appointment_missing(): void
    {
        // Ada TMT Cuti jika tanggal_mulai_kerja kosong
        $empWithTmtCuti = Employee::create([
            'nama' => 'Karyawan Acuan Cuti',
            'jenis_kelamin' => 'Laki-Laki',
            'tanggal_mulai_kerja' => null,
            'tmt_cuti' => Carbon::create(2018, 5, 10),
            'is_active' => true,
        ]);

        $this->assertSame('Mei', $empWithTmtCuti->bulan_jatuh_tempo_cuti);
        $status2024 = $empWithTmtCuti->getCutiStatusForYear(2024);
        $this->assertSame('Cuti Panjang', $status2024['jenis_cuti']);
        $this->assertTrue($status2024['is_valid']);

        // Kedua tanggal kosong -> Perlu Verifikasi
        $empEmpty = Employee::create([
            'nama' => 'Karyawan Kosong',
            'jenis_kelamin' => 'Laki-Laki',
            'tanggal_mulai_kerja' => null,
            'tmt_cuti' => null,
            'is_active' => true,
        ]);

        $this->assertSame('Perlu Verifikasi', $empEmpty->bulan_jatuh_tempo_cuti);
        $statusEmpty = $empEmpty->getCutiStatusForYear(2026);
        $this->assertSame('Perlu Verifikasi', $statusEmpty['jenis_cuti']);
        $this->assertFalse($statusEmpty['is_valid']);
    }

    public function test_cuti_index_page_renders_with_leave_data_and_kpis(): void
    {
        $user = User::factory()->create();
        $bagian = Bagian::create(['nama_bagian' => 'Tanaman']);
        $jabatan = Jabatan::create(['nama_jabatan' => 'Mandor Panen']);

        Employee::create([
            'personnel_number' => '130001',
            'nama' => 'Budi Santoso',
            'jenis_kelamin' => 'Laki-Laki',
            'bagian_id' => $bagian->id,
            'jabatan_id' => $jabatan->id,
            'status_karyawan' => 'Karpel',
            'tanggal_mulai_kerja' => Carbon::create(2020, 8, 21),
            'is_active' => true,
        ]);

        $response = $this->actingAs($user)->get(route('leaves.index', ['year' => 2026]));

        $response->assertStatus(200);
        $response->assertSeeText('Jadwal & Hak Cuti Karyawan');
        $response->assertSee('Budi Santoso');
        $response->assertSee('Cuti Panjang');
        $response->assertSee('Agustus');
    }

    public function test_cuti_index_filtering_by_jenis_cuti(): void
    {
        $user = User::factory()->create();

        // 2020: Di 2026 adalah Cuti Panjang
        Employee::create([
            'nama' => 'Karyawan Cuti Panjang',
            'jenis_kelamin' => 'Laki-Laki',
            'tanggal_mulai_kerja' => Carbon::create(2020, 4, 1),
            'is_active' => true,
        ]);

        // 2021: Di 2026 adalah Cuti Tahunan (5 thn kerja)
        Employee::create([
            'nama' => 'Karyawan Cuti Tahunan',
            'jenis_kelamin' => 'Perempuan',
            'tanggal_mulai_kerja' => Carbon::create(2021, 6, 1),
            'is_active' => true,
        ]);

        // Filter Cuti Panjang saja
        $response = $this->actingAs($user)->get(route('leaves.index', [
            'year' => 2026,
            'jenis_cuti' => ['Cuti Panjang'],
        ]));

        $response->assertStatus(200);
        $response->assertSee('Karyawan Cuti Panjang');
        $response->assertDontSee('Karyawan Cuti Tahunan');
    }

    public function test_cuti_export_streams_excel(): void
    {
        $user = User::factory()->create();

        // Karyawan Karpel aktif (2014 ke 2026 = 12 tahun -> Cuti Panjang Ke-2)
        Employee::create([
            'personnel_number' => '130002',
            'nama' => 'Sugeng Supriyadi',
            'jenis_kelamin' => 'Laki-Laki',
            'tanggal_mulai_kerja' => Carbon::create(2014, 7, 1),
            'status_karyawan' => 'Karpel',
            'is_active' => true,
        ]);

        // Karyawan BHL aktif (harus dipisahkan / TIDAK boleh masuk ke export cuti)
        Employee::create([
            'personnel_number' => 'BHL-999',
            'nama' => 'Buruh Lepas Sawit',
            'status_karyawan' => 'BHL',
            'sumber_lembar' => 'BHL',
            'tanggal_mulai_kerja' => Carbon::create(2014, 7, 1),
            'is_active' => true,
        ]);

        $response = $this->actingAs($user)->get(route('leaves.export', ['year' => 2026]));

        $response->assertStatus(200);
        $this->assertStringContainsString('spreadsheetml.sheet', (string) $response->headers->get('Content-Type'));
        $this->assertStringContainsString('Jadwal_Cuti_Karyawan_PTPN_Pelaihari_2026.xlsx', (string) $response->headers->get('Content-Disposition'));

        // Baca file Excel yang dihasilkan untuk verifikasi kolom dan data
        $tempFile = tempnam(sys_get_temp_dir(), 'test_cuti_').'.xlsx';
        ob_start();
        $response->sendContent();
        $content = ob_get_clean();
        file_put_contents($tempFile, $content);

        $spreadsheet = IOFactory::load($tempFile);
        $sheet = $spreadsheet->getActiveSheet();

        // 1. Verifikasi Judul Dokumen
        $this->assertSame('DAFTAR HAK & JADWAL CUTI KARYAWAN TAHUN 2026', $sheet->getCell('A1')->getValue());
        $this->assertSame('PT PERKEBUNAN NUSANTARA IV REGIONAL V - KEBUN PELAIHARI', $sheet->getCell('A2')->getValue());

        // 2. Verifikasi Baris Rekap Mini KPI (Baris 5 - tanpa Perlu Verifikasi)
        $this->assertStringContainsString('TOTAL KARYAWAN: 1', (string) $sheet->getCell('A5')->getValue());
        $this->assertStringContainsString('CUTI TAHUNAN: 0', (string) $sheet->getCell('D5')->getValue());
        $this->assertStringContainsString('CUTI PANJANG: 1', (string) $sheet->getCell('G5')->getValue());
        $this->assertStringNotContainsString('PERLU VERIFIKASI', (string) $sheet->getCell('A5')->getValue());
        $this->assertStringNotContainsString('PERLU VERIFIKASI', (string) $sheet->getCell('G5')->getValue());
        $this->assertStringNotContainsString('PERLU VERIFIKASI', (string) $sheet->getCell('J5')->getValue());

        // 3. Verifikasi Header Kolom (Baris 7 - Sesuai tampilan web cuti)
        $this->assertSame('No', $sheet->getCell('A7')->getValue());
        $this->assertSame('Nama Karyawan', $sheet->getCell('B7')->getValue());
        $this->assertSame('NIK SAP', $sheet->getCell('C7')->getValue());
        $this->assertSame('Bagian / Afdeling', $sheet->getCell('D7')->getValue());
        $this->assertSame('Jabatan', $sheet->getCell('E7')->getValue());
        $this->assertSame('Status Karyawan', $sheet->getCell('F7')->getValue());
        $this->assertSame('TMT Cuti', $sheet->getCell('G7')->getValue());
        $this->assertSame('Bulan Jatuh Tempo', $sheet->getCell('H7')->getValue());
        $this->assertSame('Hak Cuti Tahun 2026', $sheet->getCell('I7')->getValue());
        $this->assertSame('Cuti Panjang Berikutnya', $sheet->getCell('J7')->getValue());
        $this->assertSame('Keterangan', $sheet->getCell('K7')->getValue());

        // 4. Verifikasi Data Baris Pertama (Baris 8)
        $this->assertEquals(1, $sheet->getCell('A8')->getValue());
        $this->assertSame('Sugeng Supriyadi', $sheet->getCell('B8')->getValue());
        $this->assertSame('130002', (string) $sheet->getCell('C8')->getValue());
        $this->assertSame('Karpel', $sheet->getCell('F8')->getValue());
        $this->assertSame('Juli', $sheet->getCell('H8')->getValue());
        $this->assertSame('Cuti Panjang (Ke-2)', $sheet->getCell('I8')->getValue());
        $this->assertSame('Thn 2032', $sheet->getCell('J8')->getValue());

        // 5. Verifikasi BHL tidak masuk ke data export cuti (hanya ada 1 baris data di baris 8, baris 9 kosong)
        $this->assertNull($sheet->getCell('A9')->getValue());
        $this->assertNull($sheet->getCell('B9')->getValue());

        @unlink($tempFile);
    }

    public function test_employee_show_page_displays_cuti_card(): void
    {
        $user = User::factory()->create();

        $employee = Employee::create([
            'personnel_number' => '130003',
            'nama' => 'Dewi Lestari',
            'jenis_kelamin' => 'Perempuan',
            'tanggal_mulai_kerja' => Carbon::create(2014, 5, 20),
            'status_karyawan' => 'Karpim',
            'is_active' => true,
        ]);

        $response = $this->actingAs($user)->get(route('employees.show', $employee));

        $response->assertStatus(200);
        $response->assertSee('Hak & Jadwal Cuti Karyawan', false);
        $response->assertSee('Mei');
    }

    public function test_karpim_cuti_uses_diangkat_staf_tmt_cuti(): void
    {
        // Asrofi: TMK 1997-09-21, tapi diangkat staf / TMT Cuti 2019-12-01
        $asrofi = Employee::create([
            'personnel_number' => '13001734',
            'nama' => 'Asrofi',
            'status_karyawan' => 'Karpim',
            'tanggal_mulai_kerja' => Carbon::create(1997, 9, 21),
            'tmt_cuti' => Carbon::create(2019, 12, 1),
            'is_active' => true,
        ]);

        $this->assertSame('Desember', $asrofi->bulan_jatuh_tempo_cuti);
        $this->assertSame('2019-12-01', $asrofi->tanggal_acuan_cuti->format('Y-m-d'));

        // Arnold Widiarto: TMK 2014-03-21, tapi diangkat staf / TMT Cuti 2022-07-01
        $arnold = Employee::create([
            'personnel_number' => '13005464',
            'nama' => 'Arnold Widiarto',
            'status_karyawan' => 'Karpim',
            'tanggal_mulai_kerja' => Carbon::create(2014, 3, 21),
            'tmt_cuti' => Carbon::create(2022, 7, 1),
            'is_active' => true,
        ]);

        $this->assertSame('Juli', $arnold->bulan_jatuh_tempo_cuti);
        $this->assertSame('2022-07-01', $arnold->tanggal_acuan_cuti->format('Y-m-d'));
    }

    public function test_bhl_is_excluded_from_jadwal_cuti(): void
    {
        $user = User::factory()->create();

        $karpel = Employee::create([
            'nama' => 'Karyawan Tetap Karpel',
            'status_karyawan' => 'Karpel',
            'tanggal_mulai_kerja' => Carbon::create(2020, 1, 1),
            'is_active' => true,
        ]);

        $bhl = Employee::create([
            'nama' => 'Pekerja Buruh Harian Lepas',
            'status_karyawan' => 'BHL',
            'tanggal_mulai_kerja' => Carbon::create(2026, 8, 1),
            'is_active' => true,
        ]);

        $response = $this->actingAs($user)->get(route('leaves.index', ['year' => 2026]));

        $response->assertStatus(200);
        $response->assertSee('Karyawan Tetap Karpel');
        $response->assertDontSee('Pekerja Buruh Harian Lepas');
    }

    public function test_admin_can_edit_and_reset_employee_tmt_cuti(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $employee = Employee::create([
            'nama' => 'Pegawai Cuti',
            'status_karyawan' => 'Karpel',
            'tanggal_mulai_kerja' => Carbon::create(2020, 5, 1),
            'is_active' => true,
        ]);

        // Akses form edit
        $response = $this->actingAs($admin)->get(route('leaves.edit', $employee));
        $response->assertStatus(200);
        $response->assertSee('Edit Data Cuti');

        // Update TMT Cuti
        $updateResponse = $this->actingAs($admin)->put(route('leaves.update', $employee), [
            'tmt_cuti' => '2021-10-01',
            'tanggal_mulai_kerja' => '2020-05-01',
        ]);
        $updateResponse->assertRedirect(route('leaves.index'));
        $this->assertSame('2021-10-01', $employee->fresh()->tmt_cuti?->format('Y-m-d'));

        // Reset TMT Cuti
        $deleteResponse = $this->actingAs($admin)->delete(route('leaves.destroy', $employee));
        $deleteResponse->assertRedirect(route('leaves.index'));
        $this->assertDatabaseHas('employees', [
            'id' => $employee->id,
            'tmt_cuti' => null,
        ]);
    }
}
