<?php

namespace Tests\Feature;

use App\Models\Bagian;
use App\Models\Employee;
use App\Models\EmployeeFamily;
use App\Models\Jabatan;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EmployeeViewTest extends TestCase
{
    use RefreshDatabase;

    public function test_dashboard_renders_with_batih_and_bagian(): void
    {
        $user = User::factory()->create();
        $bagian = Bagian::create(['nama_bagian' => 'Tanaman']);
        $jabatan = Jabatan::create(['nama_jabatan' => 'Mandor']);

        $emp = Employee::create([
            'personnel_number' => '13000001',
            'nama' => 'Budi Santoso',
            'jenis_kelamin' => 'Laki-Laki',
            'bagian_id' => $bagian->id,
            'jabatan_id' => $jabatan->id,
            'status_karyawan' => 'Karpel - Tetap',
            'is_active' => true,
        ]);

        EmployeeFamily::create([
            'employee_id' => $emp->id,
            'nama' => 'Siti Budi',
            'hubungan' => 'Istri',
            'jenis_kelamin' => 'Perempuan',
        ]);

        $response = $this->actingAs($user)->get(route('dashboard'));

        $response->assertStatus(200);
        $response->assertSee('Keluarga / Batih');
        $response->assertSee('10 Bagian dengan Karyawan Terbanyak');
        $response->assertDontSee('10 Unit dengan Karyawan Terbanyak');
        $response->assertDontSee('Unit / Kebun');
    }

    public function test_employee_index_renders_without_unit(): void
    {
        $user = User::factory()->create();
        $bagian = Bagian::create(['nama_bagian' => 'Pengolahan']);
        $jabatan = Jabatan::create(['nama_jabatan' => 'Operator']);

        Employee::create([
            'personnel_number' => '13000002',
            'nama' => 'Slamet Riyadi',
            'jenis_kelamin' => 'Laki-Laki',
            'bagian_id' => $bagian->id,
            'jabatan_id' => $jabatan->id,
            'status_karyawan' => 'Karpel - Tetap',
            'is_active' => true,
        ]);

        $response = $this->actingAs($user)->get(route('employees.index'));

        $response->assertStatus(200);
        $response->assertSee('Slamet Riyadi');
        $response->assertDontSee('unitList');
        $response->assertDontSee('unit_ids');
    }

    public function test_employee_show_renders_batih_with_relationships_and_without_unit(): void
    {
        $user = User::factory()->create();
        $emp = Employee::create([
            'personnel_number' => '13004520',
            'nama' => 'Anwar Anshari',
            'jenis_kelamin' => 'Laki-Laki',
            'tanggal_lahir' => '1984-05-24',
            'status_karyawan' => 'Karpel - Tetap',
            'is_active' => true,
        ]);

        EmployeeFamily::create([
            'employee_id' => $emp->id,
            'nama' => 'WALIDAH ARIYANI',
            'hubungan' => 'Istri',
            'jenis_kelamin' => 'Perempuan',
            'tanggal_lahir' => '1985-12-01',
        ]);

        EmployeeFamily::create([
            'employee_id' => $emp->id,
            'nama' => 'TALITHA ATHIYA ANWAR',
            'hubungan' => 'Anak',
            'jenis_kelamin' => 'Perempuan',
            'tanggal_lahir' => '2011-05-23',
        ]);

        $response = $this->actingAs($user)->get(route('employees.show', $emp));

        $response->assertStatus(200);
        $response->assertSee('Anwar Anshari');
        $response->assertSee('WALIDAH ARIYANI');
        $response->assertSee('Istri');
        $response->assertSee('TALITHA ATHIYA ANWAR');
        $response->assertSee('Anak');
        $response->assertDontSee('Unit / Kebun');
        $response->assertDontSee('Pers. No.');
        $response->assertDontSee('No. PKWT');
    }

    public function test_employee_show_renders_no_pkwt_only_for_pkwt_employees(): void
    {
        $user = User::factory()->create();

        $pkwtEmp = Employee::create([
            'personnel_number' => '19001111',
            'nama' => 'Karyawan PKWT',
            'status_karyawan' => 'PKWT',
            'no_pkwt' => 'Pelai/SPKWT/99/2026',
            'is_active' => true,
        ]);

        $nonPkwtEmp = Employee::create([
            'personnel_number' => '13002222',
            'nama' => 'Karyawan KTNG',
            'status_karyawan' => 'KTNG',
            'is_active' => true,
        ]);

        // PKWT employee must display No. PKWT
        $resPkwt = $this->actingAs($user)->get(route('employees.show', $pkwtEmp));
        $resPkwt->assertStatus(200);
        $resPkwt->assertSee('No. PKWT');
        $resPkwt->assertSee('Pelai/SPKWT/99/2026');

        // Non-PKWT employee must NOT display No. PKWT
        $resNonPkwt = $this->actingAs($user)->get(route('employees.show', $nonPkwtEmp));
        $resNonPkwt->assertStatus(200);
        $resNonPkwt->assertDontSee('No. PKWT');
    }

    public function test_retirement_calculation_at_age_55_and_views(): void
    {
        $user = User::factory()->create();

        $empActive = Employee::create([
            'personnel_number' => '13009901',
            'nik_lokal' => '6301011234560001',
            'nama' => 'Karyawan Aktif 1990',
            'jenis_kelamin' => 'Laki-Laki',
            'tanggal_lahir' => '1990-06-15',
            'status_karyawan' => 'Karpim & Karpel',
            'is_active' => true,
        ]);

        $empRetired = Employee::create([
            'personnel_number' => '13009902',
            'nik_lokal' => '6301011234560002',
            'nama' => 'Karyawan Pensiun 1960',
            'jenis_kelamin' => 'Laki-Laki',
            'tanggal_lahir' => '1960-01-01',
            'status_karyawan' => 'Karpim & Karpel',
            'is_active' => true,
        ]);

        // Model attribute verification
        $this->assertEquals('2045-06-15', $empActive->tanggal_pensiun->format('Y-m-d'));
        $this->assertStringContainsString('lagi', $empActive->sisa_waktu_pensiun);
        $this->assertDoesNotMatchRegularExpression('/\d+\.\d+/', $empActive->sisa_waktu_pensiun);

        $this->assertEquals('2015-01-01', $empRetired->tanggal_pensiun->format('Y-m-d'));
        $this->assertEquals('Sudah Pensiun', $empRetired->sisa_waktu_pensiun);

        // Show view verification
        $resShow = $this->actingAs($user)->get(route('employees.show', $empActive));
        $resShow->assertStatus(200);
        $resShow->assertSee('Perkiraan Pensiun (55 Thn)');
        $resShow->assertSee('Pensiun (Usia 55)');
        $resShow->assertSee('NIK SAP');
        $resShow->assertDontSee('NIK Lokal');
        $resShow->assertDontSee('Grup Golongan');
        $resShow->assertDontSee('Grup Jabatan');
        $resShow->assertDontSee('Tempat Lahir');

        // Index view verification
        $resIndex = $this->actingAs($user)->get(route('employees.index'));
        $resIndex->assertStatus(200);
        $resIndex->assertSee('Pensiun (55 Thn)');
        $resIndex->assertSee('Sudah Pensiun');
    }

    public function test_employee_form_displays_grade_and_restricted_statuses_without_grup_fields(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $emp = Employee::create([
            'personnel_number' => '13009903',
            'nama' => 'Karyawan Edit Test',
            'jenis_kelamin' => 'Laki-Laki',
            'status_karyawan' => 'Karpel - Tetap',
            'grade' => 'G1',
            'is_active' => true,
        ]);

        // Create page
        $resCreate = $this->actingAs($admin)->get(route('employees.create'));
        $resCreate->assertStatus(200);
        $resCreate->assertSee('PKWT');
        $resCreate->assertSee('Karpim &amp; Karpel', false);
        $resCreate->assertSee('KTNG');
        $resCreate->assertSee('Grade');
        $resCreate->assertDontSee('NIK Lokal');
        $resCreate->assertDontSee('Grup Golongan');
        $resCreate->assertDontSee('Grup Jabatan');

        // Edit page
        $resEdit = $this->actingAs($admin)->get(route('employees.edit', $emp));
        $resEdit->assertStatus(200);
        $resEdit->assertSee('PKWT');
        $resEdit->assertSee('Karpim &amp; Karpel', false);
        $resEdit->assertSee('KTNG');
        $resEdit->assertSee('Grade');
        $resEdit->assertSee('G1');
        $resEdit->assertDontSee('NIK Lokal');
        $resEdit->assertDontSee('Grup Golongan');
        $resEdit->assertDontSee('Grup Jabatan');
        $resEdit->assertDontSee('Tempat Lahir');
    }

    public function test_pkwt_employee_saving_ensures_no_grup_golongan_or_jabatan(): void
    {
        $pkwt = Employee::create([
            'personnel_number' => '13009904',
            'nama' => 'PKWT Test Auto Clear',
            'jenis_kelamin' => 'Laki-Laki',
            'status_karyawan' => 'PKWT',
            'grup_golongan' => 'IA',
            'grup_jabatan' => 'Pelaksana',
            'is_active' => true,
        ]);

        $this->assertNull($pkwt->fresh()->grup_golongan);
        $this->assertNull($pkwt->fresh()->grup_jabatan);
    }

    public function test_admin_can_add_edit_and_delete_employee_family(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $employee = Employee::create([
            'personnel_number' => '13009905',
            'nama' => 'Karyawan Keluarga Test',
            'jenis_kelamin' => 'Laki-Laki',
            'status_karyawan' => 'Karpim & Karpel',
            'is_active' => true,
        ]);

        // 1. Tambah Keluarga
        $responseStore = $this->actingAs($admin)->post(route('employees.families.store', $employee), [
            'nama' => 'Dewi Lestari',
            'hubungan' => 'Istri',
            'jenis_kelamin' => 'Perempuan',
            'tanggal_lahir' => '1992-04-10',
            'tempat_lahir' => 'Pelaihari',
            'keterangan' => 'Tercatat di KK',
        ]);

        $responseStore->assertRedirect();
        $this->assertDatabaseHas('employee_families', [
            'employee_id' => $employee->id,
            'nama' => 'Dewi Lestari',
            'hubungan' => 'Istri',
        ]);

        $family = EmployeeFamily::where('nama', 'Dewi Lestari')->first();

        // 2. Edit / Update Keluarga
        $responseUpdate = $this->actingAs($admin)->put(route('employees.families.update', [$employee, $family]), [
            'nama' => 'Dewi Lestari S.Pd',
            'hubungan' => 'Istri',
            'jenis_kelamin' => 'Perempuan',
            'tanggal_lahir' => '1992-04-10',
            'tempat_lahir' => 'Banjarmasin',
        ]);

        $responseUpdate->assertRedirect();
        $this->assertDatabaseHas('employee_families', [
            'id' => $family->id,
            'nama' => 'Dewi Lestari S.Pd',
            'tempat_lahir' => 'Banjarmasin',
        ]);

        // 3. Hapus Keluarga
        $responseDestroy = $this->actingAs($admin)->delete(route('employees.families.destroy', [$employee, $family]));
        $responseDestroy->assertRedirect();
        $this->assertDatabaseMissing('employee_families', [
            'id' => $family->id,
        ]);
    }
}
