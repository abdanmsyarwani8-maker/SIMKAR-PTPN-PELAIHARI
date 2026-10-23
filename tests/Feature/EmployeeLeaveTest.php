<?php

namespace Tests\Feature;

use App\Models\Bagian;
use App\Models\Employee;
use App\Models\Jabatan;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
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

        Employee::create([
            'personnel_number' => '130002',
            'nama' => 'Sugeng Supriyadi',
            'jenis_kelamin' => 'Laki-Laki',
            'tanggal_mulai_kerja' => Carbon::create(2014, 7, 1),
            'status_karyawan' => 'Karpel',
            'is_active' => true,
        ]);

        $response = $this->actingAs($user)->get(route('leaves.export', ['year' => 2026]));

        $response->assertStatus(200);
        $this->assertStringContainsString('spreadsheetml.sheet', (string) $response->headers->get('Content-Type'));
        $this->assertStringContainsString('Jadwal_Cuti_Karyawan_PTPN_Pelaihari_2026.xlsx', (string) $response->headers->get('Content-Disposition'));
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
}
