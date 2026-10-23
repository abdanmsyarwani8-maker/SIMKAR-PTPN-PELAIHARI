<?php

namespace Tests\Feature;

use App\Imports\EmployeeImport;
use App\Imports\EmployeeSheetImport;
use App\Models\Employee;
use App\Models\EmployeeFamily;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Maatwebsite\Excel\Facades\Excel;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Tests\TestCase;

class EmployeeBatihImportTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test satu sheet Excel yang berisi data karyawan sekaligus batih.
     */
    public function test_single_sheet_with_employee_and_batih_imports_successfully(): void
    {
        $rows = collect([
            [
                'nik_sap' => '10001',
                'nama_karyawan' => 'Ahmad',
                'kategori_karyawan' => 'PKWT',
                'no_pkwt' => 'Pelai/SPKWT/01/2026',
                'tmt_kerja' => '01/01/2022',
                'tanggal_lahir_karyawan' => '15/05/1985',
                'jenis_kelamin_karyawan' => 'Laki-Laki',
                'agama' => 'Islam',
                'pendidikan_terakhir' => 'S1',
                'grade' => '12',
                'suku' => 'Banjar',
                'alamat' => 'Jl. Kebun Sawit No. 10 Pelaihari',
                'unit' => 'Kb Pelaihari',
                'bagian' => 'Afdeling I',
                'jabatan' => 'Pemanen',
                'nama_keluarga' => 'Siti Aminah',
                'hubungan_keluarga' => 'Istri',
                'jenis_kelamin_keluarga' => 'Perempuan',
                'tanggal_lahir_keluarga' => '20/10/1988',
            ],
            [
                'nik_sap' => '10001',
                'nama_karyawan' => 'Ahmad',
                'kategori_karyawan' => 'PKWT',
                'no_pkwt' => 'Pelai/SPKWT/01/2026',
                'tmt_kerja' => '01/01/2022',
                'tanggal_lahir_karyawan' => '15/05/1985',
                'jenis_kelamin_karyawan' => 'Laki-Laki',
                'agama' => 'Islam',
                'pendidikan_terakhir' => 'S1',
                'grade' => '12',
                'suku' => 'Banjar',
                'alamat' => 'Jl. Kebun Sawit No. 10 Pelaihari',
                'unit' => 'Kb Pelaihari',
                'bagian' => 'Afdeling I',
                'jabatan' => 'Pemanen',
                'nama_keluarga' => 'Budi Ahmad',
                'hubungan_keluarga' => 'Anak',
                'jenis_kelamin_keluarga' => 'Laki-Laki',
                'tanggal_lahir_keluarga' => '12/03/2015',
            ],
            [
                'nik_sap' => '10002',
                'nama_karyawan' => 'Rahman',
                'kategori_karyawan' => 'Karpim & Karpel',
                'no_pkwt' => null,
                'tmt_kerja' => '2018-06-01',
                'tanggal_lahir_karyawan' => '1990-07-20',
                'jenis_kelamin_karyawan' => 'L',
                'agama' => 'Islam',
                'pendidikan_terakhir' => 'S2',
                'grade' => '15',
                'suku' => 'Jawa',
                'alamat' => 'Emplasmen Pelaihari Blok B',
                'unit' => 'Kb Pelaihari',
                'bagian' => 'Tata Usaha',
                'jabatan' => 'Asisten Tata Usaha',
                'nama_keluarga' => 'Dewi Sartika',
                'hubungan_keluarga' => 'Spouse',
                'jenis_kelamin_keluarga' => 'P',
                'tanggal_lahir_keluarga' => '1992-04-10',
            ],
        ]);

        $importer = new EmployeeSheetImport('Sheet1', 'SIMKAR_Import_Satu_Sheet_Karyawan_Batih.xlsx');
        $importer->collection($rows);

        // Hanya 2 karyawan yang dibuat, bukan 3
        $this->assertEquals(2, Employee::count());
        $this->assertEquals(2, $importer->inserted);
        $this->assertEquals(0, $importer->updated);

        // Seluruh 3 anggota keluarga tersimpan dan terhubung ke NIK SAP yang tepat
        $this->assertEquals(3, EmployeeFamily::count());
        $this->assertEquals(3, $importer->familyProcessed);

        // Periksa data Ahmad
        $ahmad = Employee::where('personnel_number', '10001')->first();
        $this->assertNotNull($ahmad);
        $this->assertEquals('Ahmad', $ahmad->nama);
        $this->assertEquals('PKWT', $ahmad->status_karyawan);
        $this->assertEquals('Banjar', $ahmad->suku);
        $this->assertEquals('12', $ahmad->grade);
        $this->assertEquals('Jl. Kebun Sawit No. 10 Pelaihari', $ahmad->alamat);
        $this->assertEquals('1985-05-15', $ahmad->tanggal_lahir->format('Y-m-d'));
        $this->assertCount(2, $ahmad->families);

        // Periksa anggota batih Ahmad
        $siti = $ahmad->families->where('nama', 'Siti Aminah')->first();
        $this->assertNotNull($siti);
        $this->assertEquals('Istri', $siti->hubungan);
        $this->assertEquals('Perempuan', $siti->jenis_kelamin);
        $this->assertEquals('1988-10-20', $siti->tanggal_lahir->format('Y-m-d'));

        $budi = $ahmad->families->where('nama', 'Budi Ahmad')->first();
        $this->assertNotNull($budi);
        $this->assertEquals('Anak', $budi->hubungan);
        $this->assertEquals('Laki-Laki', $budi->jenis_kelamin);
        $this->assertEquals('2015-03-12', $budi->tanggal_lahir->format('Y-m-d'));

        // Pastikan tanggal lahir keluarga tidak masuk ke tanggal lahir karyawan
        $this->assertNotEquals($siti->tanggal_lahir, $ahmad->tanggal_lahir);
        $this->assertNotEquals($budi->tanggal_lahir, $ahmad->tanggal_lahir);

        // Periksa data Rahman
        $rahman = Employee::where('personnel_number', '10002')->first();
        $this->assertNotNull($rahman);
        $this->assertEquals('Rahman', $rahman->nama);
        $this->assertEquals('Karpel', $rahman->status_karyawan);
        $this->assertEquals('Jawa', $rahman->suku);
        $this->assertEquals('Laki-Laki', $rahman->jenis_kelamin);
        $this->assertCount(1, $rahman->families);

        $dewi = $rahman->families->first();
        $this->assertEquals('Dewi Sartika', $dewi->nama);
        $this->assertEquals('Istri', $dewi->hubungan);
        $this->assertEquals('Perempuan', $dewi->jenis_kelamin);
        $this->assertEquals('1992-04-10', $dewi->tanggal_lahir->format('Y-m-d'));
    }

    /**
     * Test re-import tidak menduplikasi data karyawan maupun batih.
     */
    public function test_reimport_does_not_duplicate_employee_or_family(): void
    {
        $rows = collect([
            [
                'nik_sap' => '10001',
                'nama_karyawan' => 'Ahmad',
                'status_karyawan' => 'PKWT',
                'suku' => 'Banjar',
                'nama_keluarga' => 'Siti Aminah',
                'hubungan_keluarga' => 'Istri',
                'tanggal_lahir_keluarga' => '1988-10-20',
            ],
            [
                'nik_sap' => '10001',
                'nama_karyawan' => 'Ahmad',
                'status_karyawan' => 'PKWT',
                'suku' => 'Banjar',
                'nama_keluarga' => 'Budi Ahmad',
                'hubungan_keluarga' => 'Anak',
                'tanggal_lahir_keluarga' => '2015-03-12',
            ],
        ]);

        // Import pertama
        $importer1 = new EmployeeSheetImport('Sheet1', 'test.xlsx');
        $importer1->collection($rows);

        $this->assertEquals(1, Employee::count());
        $this->assertEquals(2, EmployeeFamily::count());
        $this->assertEquals(1, $importer1->inserted);
        $this->assertEquals(2, $importer1->familyInserted);

        // Import kedua dengan file yang sama
        $importer2 = new EmployeeSheetImport('Sheet1', 'test.xlsx');
        $importer2->collection($rows);

        // Jumlah tidak boleh bertambah
        $this->assertEquals(1, Employee::count());
        $this->assertEquals(2, EmployeeFamily::count());
        $this->assertEquals(0, $importer2->inserted);
        $this->assertEquals(1, $importer2->updated);
        $this->assertEquals(0, $importer2->familyInserted);
        $this->assertEquals(2, $importer2->familyProcessed);
    }

    /**
     * Test baris lanjutan tidak menimpa data karyawan valid dengan nilai kosong.
     */
    public function test_continuation_rows_do_not_overwrite_valid_data_with_empty(): void
    {
        $rows = collect([
            [
                'nik_sap' => '10005',
                'nama_karyawan' => 'Bambang',
                'status_karyawan' => 'KTNG',
                'suku' => 'Dayak',
                'alamat' => 'Jl. Perintis Kemerdekaan No. 5',
                'agama' => 'Kristen',
                'nama_keluarga' => 'Lina',
                'hubungan_keluarga' => 'Istri',
                'tanggal_lahir_keluarga' => '1989-01-01',
            ],
            [
                'nik_sap' => '10005',
                'nama_karyawan' => 'Bambang',
                'status_karyawan' => null,
                'suku' => null,
                'alamat' => null,
                'agama' => null,
                'nama_keluarga' => 'Rio',
                'hubungan_keluarga' => 'Anak',
                'tanggal_lahir_keluarga' => '2016-05-10',
            ],
        ]);

        $importer = new EmployeeSheetImport('Sheet1', 'test.xlsx');
        $importer->collection($rows);

        $bambang = Employee::where('personnel_number', '10005')->first();
        $this->assertNotNull($bambang);
        // Field pada baris pertama tetap utuh
        $this->assertEquals('KTNG', $bambang->status_karyawan);
        $this->assertEquals('Dayak', $bambang->suku);
        $this->assertEquals('Jl. Perintis Kemerdekaan No. 5', $bambang->alamat);
        $this->assertEquals('Kristen', $bambang->agama);

        // Kedua anak/istri tetap tersimpan
        $this->assertCount(2, $bambang->families);
    }

    /**
     * Test format SAP HCM ALV Grid (Pers.No angka & Personnel Number nama karyawan).
     */
    public function test_sap_alv_format_with_pers_no_and_personnel_number_name(): void
    {
        $rows = collect([
            [
                'pers_no' => '13000329',
                'personnel_number' => 'SUYATNO',
                'type_of_family_record' => 'Spouse',
                'birth_date' => '27669', // Excel serial date untuk 1975-09-28
                'jenis_kelamin_batih' => 'Female',
                'full_name' => 'LEGIANI',
            ],
            [
                'pers_no' => '13000329',
                'personnel_number' => 'SUYATNO',
                'type_of_family_record' => 'Child',
                'birth_date' => '39906', // Excel serial date untuk 2009-04-03
                'jenis_kelamin_batih' => 'Male',
                'full_name' => 'GALIH ABIMANYU',
            ],
        ]);

        $importer = new EmployeeSheetImport('BATIH', 'BATIH.xlsx');
        $importer->collection($rows);

        $this->assertEquals(1, Employee::count());
        $suyatno = Employee::where('personnel_number', '13000329')->first();
        $this->assertNotNull($suyatno);
        $this->assertEquals('SUYATNO', $suyatno->nama);

        $this->assertCount(2, $suyatno->families);
        $legiani = $suyatno->families->where('nama', 'LEGIANI')->first();
        $this->assertNotNull($legiani);
        $this->assertEquals('Istri', $legiani->hubungan);
        $this->assertEquals('Perempuan', $legiani->jenis_kelamin);
        $this->assertEquals('1975-10-02', $legiani->tanggal_lahir->format('Y-m-d'));

        $galih = $suyatno->families->where('nama', 'GALIH ABIMANYU')->first();
        $this->assertNotNull($galih);
        $this->assertEquals('Anak', $galih->hubungan);
        $this->assertEquals('Laki-Laki', $galih->jenis_kelamin);
        $this->assertEquals('2009-04-03', $galih->tanggal_lahir->format('Y-m-d'));
    }

    /**
     * Test baris dengan NIK SAP tidak valid atau kosong dilewati dengan aman.
     */
    public function test_invalid_nik_sap_is_skipped_and_recorded(): void
    {
        $rows = collect([
            [
                'nik_sap' => '',
                'nama_karyawan' => 'Tanpa NIK',
            ],
            [
                'nik_sap' => '-',
                'nama_karyawan' => 'Strip NIK',
            ],
        ]);

        $importer = new EmployeeSheetImport('Sheet1', 'test.xlsx');
        $importer->collection($rows);

        $this->assertEquals(0, Employee::count());
        $this->assertEquals(2, $importer->skipped);
        $this->assertCount(2, $importer->skippedDetails);
    }

    /**
     * Test baris karyawan tanpa data keluarga tetap tersimpan dengan benar.
     */
    public function test_employee_without_family_is_processed(): void
    {
        $rows = collect([
            [
                'nik_sap' => '10099',
                'nama_karyawan' => 'Joko Tanpa Batih',
                'nama_keluarga' => '',
            ],
        ]);

        $importer = new EmployeeSheetImport('Sheet1', 'test.xlsx');
        $importer->collection($rows);

        $this->assertEquals(1, Employee::count());
        $this->assertEquals(0, EmployeeFamily::count());
        $this->assertEquals(1, $importer->inserted);
        $this->assertEquals(0, $importer->familyProcessed);
    }

    /**
     * Test end-to-end import file Excel satu sheet fisik dengan Maatwebsite Excel.
     */
    public function test_end_to_end_single_sheet_excel_file_import(): void
    {
        $spreadsheet = new Spreadsheet;
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Data Karyawan & Batih');

        $headers = [
            'NIK SAP', 'Nama Karyawan', 'Kategori Karyawan', 'Suku', 'Alamat',
            'Agama', 'Pendidikan Terakhir', 'Grade', 'Jenis Kelamin', 'Tanggal Lahir',
            'Nama Anggota Keluarga', 'Hubungan Keluarga', 'Tanggal Lahir Keluarga', 'Jenis Kelamin Keluarga',
        ];
        $sheet->fromArray([$headers], null, 'A1');

        $dataRows = [
            ['10001', 'Ahmad Subarjo', 'PKWT', 'Banjar', 'Jl. Pelaihari No 1', 'Islam', 'S1', '12', 'Laki-Laki', '1985-05-15', 'Siti', 'Istri', '1990-01-15', 'Perempuan'],
            ['10001', 'Ahmad Subarjo', 'PKWT', 'Banjar', 'Jl. Pelaihari No 1', 'Islam', 'S1', '12', 'Laki-Laki', '1985-05-15', 'Budi', 'Anak', '2015-06-20', 'Laki-Laki'],
            ['10002', 'Rahman Hakim', 'KTNG', 'Jawa', 'Emplasmen Pelaihari', 'Islam', 'SMA', '08', 'Laki-Laki', '1992-04-10', 'Aminah', 'Istri', '1995-08-17', 'Perempuan'],
        ];
        $sheet->fromArray($dataRows, null, 'A2');

        $tempPath = tempnam(sys_get_temp_dir(), 'simkar_test_').'.xlsx';
        $writer = new Xlsx($spreadsheet);
        $writer->save($tempPath);

        // Import menggunakan EmployeeImport tanpa argumen lembar terpilih (otomatis baca lembar pertama)
        $importer = new EmployeeImport('SIMKAR_Import_Satu_Sheet_Karyawan_Batih.xlsx');
        Excel::import($importer, $tempPath);

        $results = $importer->getResults();

        // Verifikasi hasil
        $this->assertEquals(2, $results['inserted']);
        $this->assertEquals(0, $results['updated']);
        $this->assertEquals(3, $results['family_processed']);
        $this->assertEquals(3, $results['family_inserted']);
        $this->assertEquals(0, $results['skipped']);

        $this->assertEquals(2, Employee::count());
        $this->assertEquals(3, EmployeeFamily::count());

        $ahmad = Employee::where('personnel_number', '10001')->first();
        $this->assertNotNull($ahmad);
        $this->assertEquals('Ahmad Subarjo', $ahmad->nama);
        $this->assertEquals('Banjar', $ahmad->suku);
        $this->assertEquals('PKWT', $ahmad->status_karyawan);
        $this->assertCount(2, $ahmad->families);

        $rahman = Employee::where('personnel_number', '10002')->first();
        $this->assertNotNull($rahman);
        $this->assertEquals('Rahman Hakim', $rahman->nama);
        $this->assertEquals('Jawa', $rahman->suku);
        $this->assertEquals('KTNG', $rahman->status_karyawan);
        $this->assertCount(1, $rahman->families);

        // Uji re-import file yang sama: tidak ada duplikasi
        $importerRe = new EmployeeImport('SIMKAR_Import_Satu_Sheet_Karyawan_Batih.xlsx');
        Excel::import($importerRe, $tempPath);
        $reResults = $importerRe->getResults();

        $this->assertEquals(0, $reResults['inserted']);
        $this->assertEquals(2, $reResults['updated']);
        $this->assertEquals(3, $reResults['family_processed']);
        $this->assertEquals(0, $reResults['family_inserted']);
        $this->assertEquals(3, $reResults['family_updated']);

        $this->assertEquals(2, Employee::count());
        $this->assertEquals(3, EmployeeFamily::count());

        if (file_exists($tempPath)) {
            unlink($tempPath);
        }
    }

    /**
     * Test import file nyata D:\SIMKAR\BATIH.xlsx jika ada di disk.
     */
    public function test_import_real_batih_file_if_present(): void
    {
        $realFile = 'D:/SIMKAR/BATIH.xlsx';
        if (! file_exists($realFile)) {
            $this->markTestSkipped("File {$realFile} tidak ditemukan di sistem.");
        }

        $importer = new EmployeeImport('BATIH.xlsx');
        Excel::import($importer, $realFile);

        $results = $importer->getResults();

        $this->assertGreaterThan(0, $results['inserted']);
        $this->assertGreaterThan(0, $results['family_processed']);
        $this->assertLessThanOrEqual(2, $results['skipped']);

        // Verifikasi karyawan SUYATNO (13000329)
        $suyatno = Employee::where('personnel_number', '13000329')->first();
        $this->assertNotNull($suyatno);
        $this->assertEquals('SUYATNO', $suyatno->nama);
        $this->assertGreaterThanOrEqual(1, $suyatno->families->count());
    }

    /**
     * Test inferensi otomatis hubungan keluarga saat kolom hubungan kosong.
     */
    public function test_relationship_is_automatically_inferred_when_column_is_blank(): void
    {
        $rows = collect([
            [
                'nik_sap' => '13004520',
                'nama_karyawan' => 'Anwar Anshari',
                'jenis_kelamin_karyawan' => 'Laki-Laki',
                'tanggal_lahir_karyawan' => '24/05/1984',
                'nama_keluarga' => 'WALIDAH ARIYANI',
                'hubungan_keluarga' => null,
                'jenis_kelamin_keluarga' => 'Perempuan',
                'tanggal_lahir_keluarga' => '01/12/1985',
            ],
            [
                'nik_sap' => '13004520',
                'nama_karyawan' => 'Anwar Anshari',
                'jenis_kelamin_karyawan' => 'Laki-Laki',
                'tanggal_lahir_karyawan' => '24/05/1984',
                'nama_keluarga' => 'TALITHA ATHIYA ANWAR',
                'hubungan_keluarga' => null,
                'jenis_kelamin_keluarga' => 'Perempuan',
                'tanggal_lahir_keluarga' => '23/05/2011',
            ],
            [
                'nik_sap' => '13004520',
                'nama_karyawan' => 'Anwar Anshari',
                'jenis_kelamin_karyawan' => 'Laki-Laki',
                'tanggal_lahir_karyawan' => '24/05/1984',
                'nama_keluarga' => 'FAYYADH AKHDAN ATHAYA ANWAR',
                'hubungan_keluarga' => '',
                'jenis_kelamin_keluarga' => 'Laki-Laki',
                'tanggal_lahir_keluarga' => '24/04/2014',
            ],
        ]);

        $importer = new EmployeeSheetImport('Sheet1', 'test.xlsx');
        $importer->collection($rows);

        $emp = Employee::where('personnel_number', '13004520')->first();
        $this->assertNotNull($emp);
        $this->assertCount(3, $emp->families);

        $walidah = $emp->families->where('nama', 'WALIDAH ARIYANI')->first();
        $this->assertEquals('Istri', $walidah->hubungan);
        $this->assertEquals('Perempuan', $walidah->jenis_kelamin);

        $talitha = $emp->families->where('nama', 'TALITHA ATHIYA ANWAR')->first();
        $this->assertEquals('Anak', $talitha->hubungan);

        $fayyadh = $emp->families->where('nama', 'FAYYADH AKHDAN ATHAYA ANWAR')->first();
        $this->assertEquals('Anak', $fayyadh->hubungan);
    }
}
