<?php

namespace Tests\Feature;

use App\Imports\EmployeeImport;
use App\Models\Bagian;
use App\Models\Employee;
use App\Models\EmployeeFamily;
use App\Models\Jabatan;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Maatwebsite\Excel\Facades\Excel;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\IOFactory;
use Tests\TestCase;

class EmployeeExportTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_view_export_page(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $response = $this->actingAs($admin)->get(route('employees.export.form'));

        $response->assertStatus(200);
        $response->assertSee('Export Data Karyawan');
        $response->assertSee('PKWT');
        $response->assertSee('Karpim &amp; Karpel', false);
        $response->assertSee('KTNG');
        $response->assertSee('Masukkan Data Keluarga');
        $response->assertSee('Tanpa Data Keluarga');
    }

    public function test_admin_can_export_without_family_data(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $bagian = Bagian::create(['nama_bagian' => 'Tanaman']);
        $jabatan = Jabatan::create(['nama_jabatan' => 'Pemanen']);

        $emp = Employee::create([
            'personnel_number' => '19009999',
            'nama' => 'Rudi Tanpa Keluarga',
            'status_karyawan' => 'PKWT',
            'bagian_id' => $bagian->id,
            'jabatan_id' => $jabatan->id,
            'is_active' => true,
        ]);

        EmployeeFamily::create([
            'employee_id' => $emp->id,
            'nama' => 'Siti Istri',
            'hubungan' => 'Istri',
        ]);
        EmployeeFamily::create([
            'employee_id' => $emp->id,
            'nama' => 'Budi Anak',
            'hubungan' => 'Anak',
        ]);

        $response = $this->actingAs($admin)->get(route('employees.export', [
            'categories' => ['PKWT'],
            'include_family' => '0',
        ]));

        $response->assertStatus(200);

        $tempFile = tempnam(sys_get_temp_dir(), 'test_no_family_').'.xlsx';
        ob_start();
        $response->sendContent();
        $content = ob_get_clean();
        file_put_contents($tempFile, $content);

        $spreadsheet = IOFactory::load($tempFile);
        $sheet = $spreadsheet->getSheet(0);

        // Header harus 14 kolom untuk PKWT tanpa keluarga (A sampai N)
        $this->assertEquals('No. PKWT', $sheet->getCell('D1')->getValue());
        $this->assertEquals('Alamat', $sheet->getCell('N1')->getValue());
        $this->assertNull($sheet->getCell('O1')->getValue());

        // Baris data hanya 1 (baris 2), bukan 2 baris keluarga
        $this->assertEquals('19009999', $sheet->getCell('A2')->getValue());
        $this->assertNull($sheet->getCell('A3')->getValue());

        // Tes Re-Import file tanpa keluarga ke dalam SIMKAR
        $importer = new EmployeeImport($tempFile, ['PKWT']);
        Excel::import($importer, $tempFile);

        $results = $importer->getResults();
        $this->assertEmpty($results['errors'], 'Re-import file tanpa data keluarga harus berjalan sukses!');

        @unlink($tempFile);
    }

    public function test_authenticated_user_can_access_export_page(): void
    {
        $user = User::factory()->create(['role' => 'user']);

        $response = $this->actingAs($user)->get(route('employees.export.form'));

        $response->assertStatus(200);

        // Tamu (guest) diarahkan ke login
        auth()->logout();
        $guestResponse = $this->get(route('employees.export.form'));
        $guestResponse->assertRedirect(route('login'));
    }

    public function test_admin_can_export_single_category_pkwt(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $bagian = Bagian::create(['nama_bagian' => 'Tanaman']);
        $jabatan = Jabatan::create(['nama_jabatan' => 'Pemanen']);

        $emp = Employee::create([
            'personnel_number' => '19001234',
            'nama' => 'Budi PKWT',
            'status_karyawan' => 'PKWT',
            'no_pkwt' => 'Pelai/SPKWT/10/2026',
            'bagian_id' => $bagian->id,
            'jabatan_id' => $jabatan->id,
            'is_active' => true,
        ]);

        EmployeeFamily::create([
            'employee_id' => $emp->id,
            'nama' => 'Ani Istri',
            'hubungan' => 'Istri',
            'jenis_kelamin' => 'Perempuan',
        ]);

        $response = $this->actingAs($admin)->get(route('employees.export', ['categories' => ['PKWT']]));

        $response->assertStatus(200);
        $this->assertEquals(
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            $response->headers->get('content-type')
        );

        // Simpan output ke file sementara untuk inspeksi sheet
        $tempFile = tempnam(sys_get_temp_dir(), 'test_export_').'.xlsx';
        ob_start();
        $response->sendContent();
        $content = ob_get_clean();
        file_put_contents($tempFile, $content);

        $spreadsheet = IOFactory::load($tempFile);
        $this->assertEquals(1, $spreadsheet->getSheetCount());
        $this->assertEquals('PKWT', $spreadsheet->getSheet(0)->getTitle());

        // Verifikasi baris header
        $this->assertEquals('NIK SAP', $spreadsheet->getSheet(0)->getCell('A1')->getValue());
        $this->assertEquals('Nama Karyawan', $spreadsheet->getSheet(0)->getCell('B1')->getValue());
        $this->assertEquals('No. PKWT', $spreadsheet->getSheet(0)->getCell('D1')->getValue());

        // Verifikasi baris data (Ani Istri berada di kolom O2 setelah 14 kolom data karyawan)
        $this->assertEquals('19001234', $spreadsheet->getSheet(0)->getCell('A2')->getValue());
        $this->assertEquals('Budi PKWT', $spreadsheet->getSheet(0)->getCell('B2')->getValue());
        $this->assertEquals('Ani Istri', $spreadsheet->getSheet(0)->getCell('O2')->getValue());

        @unlink($tempFile);
    }

    public function test_admin_can_export_multiple_categories_with_multiple_sheets_and_reimport(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $bagian = Bagian::create(['nama_bagian' => 'Pabrik']);
        $jabatan = Jabatan::create(['nama_jabatan' => 'Operator']);

        // 1. PKWT
        $pkwt = Employee::create([
            'personnel_number' => '19002001',
            'nama' => 'Karyawan PKWT Test',
            'status_karyawan' => 'PKWT',
            'no_pkwt' => 'Pelai/SPKWT/22/2026',
            'bagian_id' => $bagian->id,
            'jabatan_id' => $jabatan->id,
            'is_active' => true,
        ]);
        EmployeeFamily::create([
            'employee_id' => $pkwt->id,
            'nama' => 'Keluarga PKWT',
            'hubungan' => 'Anak',
            'jenis_kelamin' => 'Laki-Laki',
        ]);

        // 2. KTNG
        $ktng = Employee::create([
            'personnel_number' => '13003001',
            'nama' => 'Karyawan KTNG Test',
            'status_karyawan' => 'KTNG',
            'bagian_id' => $bagian->id,
            'jabatan_id' => $jabatan->id,
            'is_active' => true,
        ]);

        // 3. Karpim & Karpel
        $karpel = Employee::create([
            'personnel_number' => '13004001',
            'nama' => 'Karyawan Karpel Test',
            'status_karyawan' => 'Karpel - Tetap',
            'bagian_id' => $bagian->id,
            'jabatan_id' => $jabatan->id,
            'is_active' => true,
        ]);

        // Ekspor ketiga kategori
        $response = $this->actingAs($admin)->get(route('employees.export', [
            'categories' => ['PKWT', 'Karpim & Karpel', 'KTNG'],
        ]));

        $response->assertStatus(200);

        // Tulis stream ke file temporer
        $tempFile = tempnam(sys_get_temp_dir(), 'test_multi_sheet_').'.xlsx';
        ob_start();
        $response->sendContent();
        $content = ob_get_clean();
        file_put_contents($tempFile, $content);

        $spreadsheet = IOFactory::load($tempFile);
        $this->assertEquals(3, $spreadsheet->getSheetCount());

        $sheetNames = $spreadsheet->getSheetNames();
        $this->assertContains('PKWT', $sheetNames);
        $this->assertContains('Karpim & Karpel', $sheetNames);
        $this->assertContains('KTNG', $sheetNames);

        // Verifikasi sheet PKWT: Memiliki No. PKWT dan TIDAK MEMILIKI Golongan
        $pkwtSheet = $spreadsheet->getSheetByName('PKWT');
        $this->assertEquals('No. PKWT', $pkwtSheet->getCell('D1')->getValue());
        $pkwtHeaders = [];
        for ($c = 1; $c <= 18; $c++) {
            $colLetter = Coordinate::stringFromColumnIndex($c);
            $pkwtHeaders[] = $pkwtSheet->getCell($colLetter.'1')->getValue();
        }
        $this->assertNotContains('Golongan', $pkwtHeaders);

        // Verifikasi sheet Karpim & Karpel: Memiliki Golongan dan TIDAK MEMILIKI No. PKWT
        $karpimSheet = $spreadsheet->getSheetByName('Karpim & Karpel');
        $this->assertEquals('Golongan', $karpimSheet->getCell('J1')->getValue());
        $karpimHeaders = [];
        for ($c = 1; $c <= 18; $c++) {
            $colLetter = Coordinate::stringFromColumnIndex($c);
            $karpimHeaders[] = $karpimSheet->getCell($colLetter.'1')->getValue();
        }
        $this->assertNotContains('No. PKWT', $karpimHeaders);

        // Tes Re-Import file hasil ekspor ke dalam SIMKAR
        $importer = new EmployeeImport($tempFile, ['PKWT', 'Karpim & Karpel', 'KTNG']);
        Excel::import($importer, $tempFile);

        $results = $importer->getResults();
        $this->assertEmpty($results['errors'], 'Re-import harus berjalan tanpa error!');
        $this->assertGreaterThan(0, $results['updated'] + $results['inserted']);

        @unlink($tempFile);
    }
}
