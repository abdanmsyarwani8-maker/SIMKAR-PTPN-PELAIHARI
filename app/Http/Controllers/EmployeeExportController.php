<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use App\Models\EmployeeFamily;
use Illuminate\Http\Request;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Cell\DataType;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Symfony\Component\HttpFoundation\Response;

class EmployeeExportController extends Controller
{
    /**
     * Tampilkan halaman formulir ekspor data karyawan.
     */
    public function showForm()
    {
        $countPkwt = Employee::where('status_karyawan', 'like', '%PKWT%')->active()->count();
        $countKtng = Employee::where('status_karyawan', 'like', '%KTNG%')->active()->count();
        $countKarpim = Employee::where('status_karyawan', 'Karpim')->active()->count();
        $countKarpel = Employee::where('status_karyawan', 'Karpel')->active()->count();
        $totalEmployee = Employee::active()->count();
        $totalFamily = EmployeeFamily::count();

        return view('employees.export', compact(
            'countPkwt',
            'countKtng',
            'countKarpim',
            'countKarpel',
            'totalEmployee',
            'totalFamily'
        ));
    }

    /**
     * Ekspor data karyawan ke format Excel multi-sheet.
     */
    public function export(Request $request): Response
    {
        $categories = $request->input('categories', ['PKWT', 'Karpim', 'Karpel', 'KTNG']);
        if (! is_array($categories) || empty($categories)) {
            $categories = ['PKWT', 'Karpim', 'Karpel', 'KTNG'];
        }

        $allowedCategories = ['PKWT', 'Karpim', 'Karpel', 'KTNG'];
        $selectedCategories = array_values(array_intersect($allowedCategories, $categories));
        if (empty($selectedCategories)) {
            $selectedCategories = $allowedCategories;
        }

        $includeFamily = $request->boolean('include_family', true);

        $spreadsheet = new Spreadsheet;
        // Hapus default sheet awal
        $spreadsheet->removeSheetByIndex(0);

        foreach ($selectedCategories as $category) {
            $sheet = $spreadsheet->createSheet();
            $sheet->setTitle($category);

            $isPkwt = ($category === 'PKWT');

            if ($isPkwt) {
                // PKWT: Memiliki No. PKWT, TIDAK MEMILIKI Golongan
                $headers = [
                    'NIK SAP',
                    'Nama Karyawan',
                    'Status Karyawan',
                    'No. PKWT',
                    'TMT Kerja',
                    'Tanggal Lahir Karyawan',
                    'Jenis Kelamin Karyawan',
                    'Agama',
                    'Pendidikan Terakhir',
                    'Grade',
                    'Bagian',
                    'Jabatan',
                    'Suku',
                    'Alamat',
                ];
            } else {
                // Non-PKWT: TIDAK MEMILIKI No. PKWT, Memiliki Golongan
                $headers = [
                    'NIK SAP',
                    'Nama Karyawan',
                    'Status Karyawan',
                    'TMT Kerja',
                    'Tanggal Lahir Karyawan',
                    'Jenis Kelamin Karyawan',
                    'Agama',
                    'Pendidikan Terakhir',
                    'Grade',
                    'Golongan',
                    'Bagian',
                    'Jabatan',
                    'Suku',
                    'Alamat',
                ];
            }

            if ($includeFamily) {
                $headers = array_merge($headers, [
                    'Nama Anggota Keluarga',
                    'Hubungan Keluarga',
                    'Jenis Kelamin Keluarga',
                    'Tanggal Lahir Keluarga',
                ]);
            }

            // Query data karyawan untuk kategori ini
            $relations = ['bagian', 'jabatan'];
            if ($includeFamily) {
                $relations[] = 'families';
            }
            $query = Employee::with($relations)->active();

            if ($category === 'PKWT') {
                $query->where('status_karyawan', 'like', '%PKWT%');
            } elseif ($category === 'KTNG') {
                $query->where('status_karyawan', 'like', '%KTNG%');
            } elseif ($category === 'Karpim') {
                $query->where('status_karyawan', 'Karpim');
            } elseif ($category === 'Karpel') {
                $query->where('status_karyawan', 'Karpel');
            }

            $employees = $query->orderBy('nama')->get();

            // Tulis Header di baris 1
            foreach ($headers as $colIdx => $header) {
                $colLetter = Coordinate::stringFromColumnIndex($colIdx + 1);
                $sheet->setCellValue($colLetter.'1', $header);
            }

            // Styling Header
            $lastColLetter = Coordinate::stringFromColumnIndex(count($headers));
            $headerRange = "A1:{$lastColLetter}1";

            $sheet->getStyle($headerRange)->applyFromArray([
                'font' => [
                    'bold' => true,
                    'color' => ['rgb' => 'FFFFFF'],
                    'size' => 11,
                ],
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'startColor' => ['rgb' => '1E5E3A'], // Hijau resmi PTPN
                ],
                'alignment' => [
                    'horizontal' => Alignment::HORIZONTAL_CENTER,
                    'vertical' => Alignment::VERTICAL_CENTER,
                    'wrapText' => true,
                ],
            ]);
            $sheet->getRowDimension(1)->setRowHeight(28);

            // Tulis Data baris demi baris
            $rowNum = 2;
            foreach ($employees as $emp) {
                if ($isPkwt) {
                    $baseValues = [
                        (string) ($emp->personnel_number ?? ''),
                        (string) ($emp->nama ?? ''),
                        (string) ($emp->status_karyawan ?? ''),
                        (string) ($emp->no_pkwt ?? ''),
                        $emp->tanggal_mulai_kerja?->format('Y-m-d') ?? '',
                        $emp->tanggal_lahir?->format('Y-m-d') ?? '',
                        (string) ($emp->jenis_kelamin ?? ''),
                        (string) ($emp->agama ?? ''),
                        (string) ($emp->pendidikan_terakhir ?? ''),
                        (string) ($emp->grade ?? ''),
                        (string) ($emp->bagian?->nama_bagian ?? ''),
                        (string) ($emp->jabatan?->nama_jabatan ?? ''),
                        (string) ($emp->suku ?? ''),
                        (string) ($emp->alamat ?? ''),
                    ];
                } else {
                    $baseValues = [
                        (string) ($emp->personnel_number ?? ''),
                        (string) ($emp->nama ?? ''),
                        (string) ($emp->status_karyawan ?? ''),
                        $emp->tanggal_mulai_kerja?->format('Y-m-d') ?? '',
                        $emp->tanggal_lahir?->format('Y-m-d') ?? '',
                        (string) ($emp->jenis_kelamin ?? ''),
                        (string) ($emp->agama ?? ''),
                        (string) ($emp->pendidikan_terakhir ?? ''),
                        (string) ($emp->grade ?? ''),
                        (string) ($emp->golongan ?? ''),
                        (string) ($emp->bagian?->nama_bagian ?? ''),
                        (string) ($emp->jabatan?->nama_jabatan ?? ''),
                        (string) ($emp->suku ?? ''),
                        (string) ($emp->alamat ?? ''),
                    ];
                }

                if (! $includeFamily) {
                    $this->writeRowValues($sheet, $rowNum, $baseValues);
                    $rowNum++;
                } else {
                    if ($emp->families->isNotEmpty()) {
                        foreach ($emp->families as $fam) {
                            $rowValues = array_merge($baseValues, [
                                (string) ($fam->nama ?? ''),
                                (string) ($fam->hubungan ?? ''),
                                (string) ($fam->jenis_kelamin ?? ''),
                                $fam->tanggal_lahir?->format('Y-m-d') ?? '',
                            ]);
                            $this->writeRowValues($sheet, $rowNum, $rowValues);
                            $rowNum++;
                        }
                    } else {
                        $rowValues = array_merge($baseValues, ['', '', '', '']);
                        $this->writeRowValues($sheet, $rowNum, $rowValues);
                        $rowNum++;
                    }
                }
            }

            // Border & layout data
            if ($rowNum > 2) {
                $dataRange = "A2:{$lastColLetter}".($rowNum - 1);
                $sheet->getStyle($dataRange)->applyFromArray([
                    'borders' => [
                        'allBorders' => [
                            'borderStyle' => Border::BORDER_THIN,
                            'color' => ['rgb' => 'DCE7E1'],
                        ],
                    ],
                    'alignment' => [
                        'vertical' => Alignment::VERTICAL_CENTER,
                    ],
                ]);
            }

            // Auto-width kolom
            for ($col = 1; $col <= count($headers); $col++) {
                $colLetter = Coordinate::stringFromColumnIndex($col);
                $sheet->getColumnDimension($colLetter)->setAutoSize(true);
            }

            // Freeze header baris 1
            $sheet->freezePane('A2');
        }

        // Set active sheet ke sheet pertama
        $spreadsheet->setActiveSheetIndex(0);

        $dateSuffix = now()->format('Ymd_His');
        $fileName = "SIMKAR_Data_Karyawan_{$dateSuffix}.xlsx";
        $tempPath = tempnam(sys_get_temp_dir(), 'simkar_export_').'.xlsx';

        $writer = new Xlsx($spreadsheet);
        $writer->save($tempPath);

        return response()->download($tempPath, $fileName, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ])->deleteFileAfterSend(true);
    }

    /**
     * Tulis 1 baris nilai ke sheet dengan format text eksplisit untuk NIK SAP.
     */
    protected function writeRowValues($sheet, int $rowNum, array $values): void
    {
        foreach ($values as $colIdx => $val) {
            $colLetter = Coordinate::stringFromColumnIndex($colIdx + 1);
            if ($colIdx === 0) {
                // Set NIK SAP sebagai string eksplisit agar leading zero tidak hilang
                $sheet->setCellValueExplicit($colLetter.$rowNum, (string) $val, DataType::TYPE_STRING);
            } else {
                $sheet->setCellValue($colLetter.$rowNum, (string) $val);
            }
        }

        $sheet->getRowDimension($rowNum)->setRowHeight(20);
    }
}
