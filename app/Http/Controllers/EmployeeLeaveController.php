<?php

namespace App\Http\Controllers;

use App\Models\Bagian;
use App\Models\Employee;
use Illuminate\Http\Request;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Cell\DataType;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Symfony\Component\HttpFoundation\StreamedResponse;

class EmployeeLeaveController extends Controller
{
    /**
     * Daftar monitoring jadwal & hak cuti tahunan dan cuti panjang.
     */
    public function index(Request $request)
    {
        $selectedYear = $request->integer('year', (int) now()->year);
        if ($selectedYear < 1970 || $selectedYear > 2100) {
            $selectedYear = (int) now()->year;
        }

        $baseFilterQuery = Employee::with(['bagian', 'jabatan'])
            ->active()
            ->search($request->get('q'))
            ->filterBagian($request->get('bagian_ids'))
            ->filterStatus($request->get('statuses'))
            ->filterBulanCuti($request->get('months'))
            ->filterJenisCuti($request->get('jenis_cuti'), $selectedYear);

        // Sorting
        $sortBy = $request->get('sort', 'nama');
        $sortDir = $request->get('dir', 'asc');
        $allowedSort = ['nama', 'personnel_number', 'tanggal_mulai_kerja', 'status_karyawan'];
        if (in_array($sortBy, $allowedSort)) {
            $baseFilterQuery->orderBy($sortBy, $sortDir === 'desc' ? 'desc' : 'asc');
        } else {
            $baseFilterQuery->orderBy('nama');
        }

        $employees = $baseFilterQuery->paginate(25)->withQueryString();
        $totalHasil = $employees->total();

        // KPI Ringkasan Cuti untuk tahun terpilih
        $kpiBase = Employee::active();
        $totalActive = (clone $kpiBase)->count();
        $countCutiPanjang = (clone $kpiBase)->filterJenisCuti(['Cuti Panjang'], $selectedYear)->count();
        $countCutiTahunan = (clone $kpiBase)->filterJenisCuti(['Cuti Tahunan'], $selectedYear)->count();
        $countPerluVerifikasi = (clone $kpiBase)->filterJenisCuti(['Perlu Verifikasi'], $selectedYear)->count();

        // Opsi filter
        $bagians = Bagian::orderBy('nama_bagian')->get();
        $statuses = Employee::active()->distinct()->pluck('status_karyawan')->filter()->sort()->values();

        $monthsList = [
            1 => 'Januari',
            2 => 'Februari',
            3 => 'Maret',
            4 => 'April',
            5 => 'Mei',
            6 => 'Juni',
            7 => 'Juli',
            8 => 'Agustus',
            9 => 'September',
            10 => 'Oktober',
            11 => 'November',
            12 => 'Desember',
        ];

        $jenisCutiList = ['Cuti Panjang', 'Cuti Tahunan', 'Perlu Verifikasi'];

        // Hitung filter aktif
        $activeFilters = collect(['bagian_ids', 'statuses', 'months', 'jenis_cuti'])
            ->filter(fn ($k) => $request->filled($k))->count();
        $activeFilters += $request->filled('q') ? 1 : 0;

        return view('leaves.index', compact(
            'employees',
            'totalHasil',
            'selectedYear',
            'totalActive',
            'countCutiPanjang',
            'countCutiTahunan',
            'countPerluVerifikasi',
            'bagians',
            'statuses',
            'monthsList',
            'jenisCutiList',
            'activeFilters'
        ));
    }

    /**
     * Ekspor jadwal dan hak cuti karyawan ke file Excel (.xlsx).
     */
    public function export(Request $request): StreamedResponse
    {
        $selectedYear = $request->integer('year', (int) now()->year);
        if ($selectedYear < 1970 || $selectedYear > 2100) {
            $selectedYear = (int) now()->year;
        }

        $query = Employee::with(['bagian', 'jabatan'])
            ->active()
            ->search($request->get('q'))
            ->filterBagian($request->get('bagian_ids'))
            ->filterStatus($request->get('statuses'))
            ->filterBulanCuti($request->get('months'))
            ->filterJenisCuti($request->get('jenis_cuti'), $selectedYear);

        $sortBy = $request->get('sort', 'nama');
        $sortDir = $request->get('dir', 'asc');
        $allowedSort = ['nama', 'personnel_number', 'tanggal_mulai_kerja', 'status_karyawan'];
        if (in_array($sortBy, $allowedSort)) {
            $query->orderBy($sortBy, $sortDir === 'desc' ? 'desc' : 'asc');
        } else {
            $query->orderBy('nama');
        }

        $employees = $query->get();

        $spreadsheet = new Spreadsheet;
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle("Jadwal Cuti {$selectedYear}");

        // Judul Laporan
        $sheet->setCellValue('A1', "DAFTAR HAK & JADWAL CUTI KARYAWAN TAHUN {$selectedYear}");
        $sheet->setCellValue('A2', 'PTPN IV REGIONAL V KEBUN PELAIHARI');
        $sheet->setCellValue('A3', 'Data diekspor pada: '.now()->translatedFormat('d F Y H:i').' WITA | Total: '.$employees->count().' Karyawan');

        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(14)->getColor()->setRGB('1E5631');
        $sheet->getStyle('A2')->getFont()->setBold(true)->setSize(11)->getColor()->setRGB('4CAF72');
        $sheet->getStyle('A3')->getFont()->setSize(10)->getColor()->setRGB('666666');

        // Header Kolom
        $headers = [
            'No',
            'NIK SAP',
            'Nama Karyawan',
            'Bagian / Afdeling',
            'Jabatan',
            'Status Karyawan',
            'TMT Pengangkatan Kerja',
            'TMT Cuti',
            'Bulan Jatuh Tempo',
            "Hak Cuti Tahun {$selectedYear}",
            'Tahun Cuti Panjang Berikutnya',
            'Keterangan',
        ];

        $col = 1;
        foreach ($headers as $h) {
            $colLetter = Coordinate::stringFromColumnIndex($col);
            $sheet->setCellValue("{$colLetter}5", $h);
            $col++;
        }

        // Style Header
        $headerRange = 'A5:L5';
        $sheet->getStyle($headerRange)->applyFromArray([
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF'], 'size' => 11],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical' => Alignment::VERTICAL_CENTER,
                'wrapText' => true,
            ],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['rgb' => '1E5631'],
            ],
            'borders' => [
                'allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => '144023']],
            ],
        ]);
        $sheet->getRowDimension(5)->setRowHeight(28);

        // Isi Data
        $rowNum = 6;
        foreach ($employees as $idx => $emp) {
            $cutiStatus = $emp->getCutiStatusForYear($selectedYear);

            $nik = $emp->personnel_number ?: '-';
            $tmtKerja = $emp->tanggal_mulai_kerja ? $emp->tanggal_mulai_kerja->format('d/m/Y') : '-';
            $tmtCuti = $emp->tmt_cuti ? $emp->tmt_cuti->format('d/m/Y') : '-';

            $rowValues = [
                $idx + 1,
                $nik,
                $emp->nama ?: '-',
                $emp->bagian?->nama_bagian ?: '-',
                $emp->jabatan?->nama_jabatan ?: '-',
                $emp->status_karyawan ?: '-',
                $tmtKerja,
                $tmtCuti,
                $cutiStatus['bulan_jatuh_tempo'],
                $cutiStatus['jenis_cuti'],
                (string) $cutiStatus['tahun_cuti_panjang_berikutnya'],
                $cutiStatus['keterangan'],
            ];

            foreach ($rowValues as $cIdx => $val) {
                $colLetter = Coordinate::stringFromColumnIndex($cIdx + 1);
                // NIK SAP simpan sebagai string
                if ($cIdx === 1) {
                    $sheet->setCellValueExplicit("{$colLetter}{$rowNum}", (string) $val, DataType::TYPE_STRING);
                } else {
                    $sheet->setCellValue("{$colLetter}{$rowNum}", $val);
                }
            }

            // Zebra striping
            if ($rowNum % 2 === 1) {
                $sheet->getStyle("A{$rowNum}:L{$rowNum}")->getFill()
                    ->setFillType(Fill::FILL_SOLID)
                    ->getStartColor()->setRGB('F8FAF9');
            }

            // Highlight Cuti Panjang
            if ($cutiStatus['is_cuti_panjang']) {
                $sheet->getStyle("J{$rowNum}")->getFont()->setBold(true)->getColor()->setRGB('B45309');
                $sheet->getStyle("J{$rowNum}")->getFill()
                    ->setFillType(Fill::FILL_SOLID)
                    ->getStartColor()->setRGB('FEF3C7');
            } elseif (! $cutiStatus['is_valid']) {
                $sheet->getStyle("J{$rowNum}")->getFont()->setBold(true)->getColor()->setRGB('DC2626');
            }

            $rowNum++;
        }

        // Border seluruh tabel data
        if ($rowNum > 6) {
            $sheet->getStyle('A6:L'.($rowNum - 1))->applyFromArray([
                'borders' => [
                    'allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => 'D0DFD5']],
                ],
                'alignment' => [
                    'vertical' => Alignment::VERTICAL_CENTER,
                ],
            ]);
            $sheet->getStyle('A6:A'.($rowNum - 1))->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle('B6:B'.($rowNum - 1))->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle('G6:I'.($rowNum - 1))->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle('J6:K'.($rowNum - 1))->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        }

        // Auto width
        foreach (range(1, 12) as $colIdx) {
            $sheet->getColumnDimensionByColumn($colIdx)->setAutoSize(true);
        }

        $fileName = "Jadwal_Cuti_Karyawan_PTPN_Pelaihari_{$selectedYear}.xlsx";

        return response()->streamDownload(function () use ($spreadsheet) {
            $writer = new Xlsx($spreadsheet);
            $writer->save('php://output');
        }, $fileName, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'Cache-Control' => 'max-age=0',
        ]);
    }
}
