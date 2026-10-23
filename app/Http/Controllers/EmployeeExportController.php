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
    public function showForm(Request $request)
    {
        $hasActiveFilter = $request->hasAny([
            'q', 'bagian_ids', 'jabatan_ids', 'statuses',
            'jenis_kelamin', 'agama', 'pendidikan', 'pensiun', 'penghargaan', 'rentang_usia', 'sumber_lembar', 'lokasi',
        ]);

        $baseQuery = Employee::active()
            ->search($request->get('q'))
            ->filterBagian($request->get('bagian_ids'))
            ->filterJabatan($request->get('jabatan_ids'))
            ->filterStatus($request->get('statuses'))
            ->filterJenisKelamin($request->get('jenis_kelamin'))
            ->filterAgama($request->get('agama'))
            ->filterPendidikan($request->get('pendidikan'))
            ->filterPensiun($request->get('pensiun'))
            ->filterPenghargaan($request->get('penghargaan'))
            ->filterRentangUsia($request->get('rentang_usia'))
            ->filterSumberLembar($request->get('sumber_lembar'))
            ->filterLokasi($request->get('lokasi'));

        $countPkwt = (clone $baseQuery)->where('status_karyawan', 'like', '%PKWT%')->count();
        $countKtng = (clone $baseQuery)->where('status_karyawan', 'like', '%KTNG%')->count();
        $countBhl = (clone $baseQuery)->where('status_karyawan', 'like', '%BHL%')->count();
        $countKarpim = (clone $baseQuery)->where('status_karyawan', 'Karpim')->count();
        $countKarpel = (clone $baseQuery)->where('status_karyawan', 'Karpel')->count();
        $totalEmployee = (clone $baseQuery)->count();
        $totalFamily = EmployeeFamily::whereIn('employee_id', (clone $baseQuery)->select('id'))->count();

        // Hitung jumlah filter aktif
        $activeFilters = collect([
            'bagian_ids', 'jabatan_ids', 'statuses',
            'jenis_kelamin', 'agama', 'pendidikan', 'pensiun', 'penghargaan', 'rentang_usia', 'sumber_lembar', 'lokasi',
        ])->filter(fn ($key) => $request->filled($key))->count();
        $activeFilters += $request->filled('q') ? 1 : 0;

        return view('employees.export', compact(
            'countPkwt',
            'countKtng',
            'countBhl',
            'countKarpim',
            'countKarpel',
            'totalEmployee',
            'totalFamily',
            'hasActiveFilter',
            'activeFilters'
        ));
    }

    /**
     * Ekspor data karyawan ke format Excel multi-sheet.
     */
    public function export(Request $request): Response
    {
        $hasActiveFilter = $request->hasAny([
            'q', 'bagian_ids', 'jabatan_ids', 'statuses',
            'jenis_kelamin', 'agama', 'pendidikan', 'rentang_usia', 'sumber_lembar', 'lokasi',
        ]);

        $categories = $request->input('categories', []);
        if (! is_array($categories) || empty($categories)) {
            $filterStatuses = (array) $request->input('statuses', []);
            if (! empty($filterStatuses)) {
                $categories = [];
                foreach ($filterStatuses as $st) {
                    if (str_contains($st, 'PKWT')) {
                        $categories[] = 'PKWT';
                    } elseif (str_contains($st, 'Karpim')) {
                        $categories[] = 'Karpim';
                    } elseif (str_contains($st, 'Karpel')) {
                        $categories[] = 'Karpel';
                    } elseif (str_contains($st, 'KTNG')) {
                        $categories[] = 'KTNG';
                    } elseif (str_contains($st, 'BHL')) {
                        $categories[] = 'BHL';
                    }
                }
                $categories = array_unique($categories);
            }
            if (empty($categories)) {
                $categories = ['PKWT', 'Karpim', 'Karpel', 'KTNG', 'BHL'];
            }
        }

        $allowedCategories = ['PKWT', 'Karpim', 'Karpel', 'Karpim & Karpel', 'KTNG', 'BHL'];
        $selectedCategories = array_values(array_intersect($allowedCategories, $categories));
        if (empty($selectedCategories)) {
            $selectedCategories = ['PKWT', 'Karpim', 'Karpel', 'KTNG', 'BHL'];
        }

        $includeFamily = $request->boolean('include_family', true);

        // Helper untuk menerapkan seluruh filter pencarian dan panel filter
        $applyQueryFilters = function ($query) use ($request) {
            return $query
                ->search($request->get('q'))
                ->filterBagian($request->get('bagian_ids'))
                ->filterJabatan($request->get('jabatan_ids'))
                ->filterJenisKelamin($request->get('jenis_kelamin'))
                ->filterAgama($request->get('agama'))
                ->filterPendidikan($request->get('pendidikan'))
                ->filterPensiun($request->get('pensiun'))
                ->filterPenghargaan($request->get('penghargaan'))
                ->filterRentangUsia($request->get('rentang_usia'))
                ->filterSumberLembar($request->get('sumber_lembar'))
                ->filterLokasi($request->get('lokasi'));
        };

        // Kumpulkan data tiap kategori yang diminta
        $categoryData = [];
        $totalFound = 0;
        foreach ($selectedCategories as $category) {
            $relations = ['bagian', 'jabatan'];
            if ($includeFamily) {
                $relations[] = 'families';
            }
            $query = Employee::with($relations)->active();
            $query = $applyQueryFilters($query);

            if ($category === 'PKWT') {
                $query->where('status_karyawan', 'like', '%PKWT%');
            } elseif ($category === 'KTNG') {
                $query->where('status_karyawan', 'like', '%KTNG%');
            } elseif ($category === 'BHL') {
                $query->where('status_karyawan', 'like', '%BHL%');
            } elseif ($category === 'Karpim & Karpel') {
                $query->where(function ($q) {
                    $q->where('status_karyawan', 'Karpim & Karpel')
                        ->orWhere('status_karyawan', 'like', '%Karpim%')
                        ->orWhere('status_karyawan', 'like', '%Karpel%');
                });
            } elseif ($category === 'Karpim') {
                $query->where('status_karyawan', 'like', '%Karpim%');
            } elseif ($category === 'Karpel') {
                $query->where('status_karyawan', 'like', '%Karpel%');
            }

            // Sorting
            $sortBy = $request->get('sort', 'nama');
            $sortDir = $request->get('dir', 'asc');
            $allowedSort = ['nama', 'personnel_number', 'tanggal_lahir', 'tanggal_mulai_kerja', 'status_karyawan'];
            if (in_array($sortBy, $allowedSort)) {
                $query->orderBy($sortBy, $sortDir === 'desc' ? 'desc' : 'asc');
            } else {
                $query->orderBy('nama');
            }

            $emps = $query->get();
            $categoryData[$category] = $emps;
            $totalFound += $emps->count();
        }

        // Tentukan sheet mana yang dibuat
        $categoriesToWrite = [];
        if ($hasActiveFilter && $totalFound > 0) {
            foreach ($selectedCategories as $cat) {
                if ($categoryData[$cat]->isNotEmpty()) {
                    $categoriesToWrite[] = $cat;
                }
            }
        } else {
            $categoriesToWrite = $selectedCategories;
        }

        if (empty($categoriesToWrite)) {
            $categoriesToWrite = [$selectedCategories[0]];
        }

        $spreadsheet = new Spreadsheet;
        // Hapus default sheet awal
        $spreadsheet->removeSheetByIndex(0);

        foreach ($categoriesToWrite as $category) {
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
                    'Domisili',
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
                    'Domisili',
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

            $employees = $categoryData[$category] ?? collect();

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
                $jk = in_array($emp->jenis_kelamin, ['Laki-Laki', 'Perempuan']) ? $emp->jenis_kelamin : '-';
                $domisili = $emp->domisili ?: '-';
                $nik = $emp->personnel_number ?: '-';

                if ($isPkwt) {
                    $baseValues = [
                        $nik,
                        (string) ($emp->nama ?? '-'),
                        (string) ($emp->status_karyawan ?? '-'),
                        (string) ($emp->no_pkwt ?: '-'),
                        $emp->tanggal_mulai_kerja?->format('Y-m-d') ?? '-',
                        $emp->tanggal_lahir?->format('Y-m-d') ?? '-',
                        $jk,
                        (string) ($emp->agama ?: '-'),
                        (string) ($emp->pendidikan_terakhir ?: '-'),
                        (string) ($emp->grade ?: '-'),
                        (string) ($emp->bagian?->nama_bagian ?: '-'),
                        (string) ($emp->jabatan?->nama_jabatan ?: '-'),
                        (string) ($emp->suku ?: '-'),
                        (string) ($emp->alamat ?: '-'),
                        $domisili,
                    ];
                } else {
                    $baseValues = [
                        $nik,
                        (string) ($emp->nama ?? '-'),
                        (string) ($emp->status_karyawan ?? '-'),
                        $emp->tanggal_mulai_kerja?->format('Y-m-d') ?? '-',
                        $emp->tanggal_lahir?->format('Y-m-d') ?? '-',
                        $jk,
                        (string) ($emp->agama ?: '-'),
                        (string) ($emp->pendidikan_terakhir ?: '-'),
                        (string) ($emp->grade ?: '-'),
                        (string) ($emp->golongan ?: '-'),
                        (string) ($emp->bagian?->nama_bagian ?: '-'),
                        (string) ($emp->jabatan?->nama_jabatan ?: '-'),
                        (string) ($emp->suku ?: '-'),
                        (string) ($emp->alamat ?: '-'),
                        $domisili,
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
        $filePrefix = $hasActiveFilter ? 'SIMKAR_Data_Karyawan_Filter' : 'SIMKAR_Data_Karyawan';
        $fileName = "{$filePrefix}_{$dateSuffix}.xlsx";
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
