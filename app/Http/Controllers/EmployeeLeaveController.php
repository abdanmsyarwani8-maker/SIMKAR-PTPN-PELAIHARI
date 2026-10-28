<?php

namespace App\Http\Controllers;

use App\Models\Bagian;
use App\Models\Employee;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
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
            ->karyawanAktif()
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

        // KPI Ringkasan Cuti untuk tahun terpilih (khusus karyawan aktif, BHL dipisahkan)
        $kpiBase = Employee::karyawanAktif();
        $totalActive = (clone $kpiBase)->count();
        $countCutiPanjang = (clone $kpiBase)->filterJenisCuti(['Cuti Panjang'], $selectedYear)->count();
        $countCutiTahunan = (clone $kpiBase)->filterJenisCuti(['Cuti Tahunan'], $selectedYear)->count();
        $countPerluVerifikasi = (clone $kpiBase)->filterJenisCuti(['Perlu Verifikasi'], $selectedYear)->count();

        // Opsi filter (hanya status karyawan aktif)
        $bagians = Bagian::orderBy('nama_bagian')->get();
        $statuses = Employee::karyawanAktif()->distinct()->pluck('status_karyawan')->filter()->sort()->values();

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
            ->karyawanAktif()
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

        // Hitung rekap KPI untuk data yang diekspor
        $countTahunan = 0;
        $countPanjang = 0;
        $countVerif = 0;
        $countBelumSyarat = 0;

        foreach ($employees as $emp) {
            $c = $emp->getCutiStatusForYear($selectedYear);
            if (! $c['is_valid']) {
                $countVerif++;
            } elseif ($c['is_cuti_panjang']) {
                $countPanjang++;
            } elseif ($c['jenis_cuti'] === 'Belum Memenuhi Syarat') {
                $countBelumSyarat++;
            } else {
                $countTahunan++;
            }
        }

        // Susun ringkasan filter aktif jika ada
        $filterParts = [];
        if ($request->filled('q')) {
            $filterParts[] = 'Pencarian: "'.$request->get('q').'"';
        }
        if ($request->filled('jenis_cuti')) {
            $filterParts[] = 'Hak Cuti: '.implode(', ', (array) $request->get('jenis_cuti'));
        }
        if ($request->filled('months')) {
            $monthsMap = [
                1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
                5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
                9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember',
            ];
            $mNames = collect((array) $request->get('months'))->map(fn ($m) => $monthsMap[(int) $m] ?? $m)->implode(', ');
            $filterParts[] = 'Bulan: '.$mNames;
        }
        if ($request->filled('bagian_ids')) {
            $bNames = Bagian::whereIn('id', (array) $request->get('bagian_ids'))->pluck('nama_bagian')->implode(', ');
            if ($bNames) {
                $filterParts[] = 'Bagian: '.$bNames;
            }
        }
        if ($request->filled('statuses')) {
            $filterParts[] = 'Status: '.implode(', ', (array) $request->get('statuses'));
        }
        $filterSummary = ! empty($filterParts) ? implode(' | ', $filterParts) : 'Semua Karyawan Aktif (Tanpa Filter)';

        $spreadsheet = new Spreadsheet;
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle("Jadwal Cuti {$selectedYear}");
        $sheet->setShowGridLines(true);

        // Judul & Banner Dokumen
        $sheet->setCellValue('A1', "DAFTAR HAK & JADWAL CUTI KARYAWAN TAHUN {$selectedYear}");
        $sheet->setCellValue('A2', 'PT PERKEBUNAN NUSANTARA IV REGIONAL V - KEBUN PELAIHARI');
        $sheet->setCellValue('A3', 'Data diekspor: '.now()->translatedFormat('d F Y H:i').' WITA | Total Data: '.$employees->count().' Karyawan | Filter: '.$filterSummary);

        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(14)->getColor()->setRGB('1E5631');
        $sheet->getStyle('A2')->getFont()->setBold(true)->setSize(11)->getColor()->setRGB('2D6A4F');
        $sheet->getStyle('A3')->getFont()->setSize(9.5)->getColor()->setRGB('555555');

        // Baris 5: Rekap Mini KPI (Tanpa Perlu Verifikasi)
        $sheet->mergeCells('A5:C5');
        $sheet->setCellValue('A5', 'TOTAL KARYAWAN: '.number_format($employees->count()));

        $sheet->mergeCells('D5:F5');
        $sheet->setCellValue('D5', 'CUTI TAHUNAN: '.number_format($countTahunan));

        $sheet->mergeCells('G5:I5');
        $sheet->setCellValue('G5', 'CUTI PANJANG: '.number_format($countPanjang));

        $sheet->mergeCells('J5:K5');
        $sheet->setCellValue('J5', 'TAHUN ACUAN: '.$selectedYear);

        $kpiBoxes = [
            'A5:C5' => ['fill' => 'F0FDF4', 'color' => '166534', 'border' => 'BBF7D0'],
            'D5:F5' => ['fill' => 'F0FDF4', 'color' => '166534', 'border' => 'BBF7D0'],
            'G5:I5' => ['fill' => 'FEF3C7', 'color' => '92400E', 'border' => 'FDE68A'],
            'J5:K5' => ['fill' => 'F8FAFC', 'color' => '334155', 'border' => 'E2E8F0'],
        ];

        foreach ($kpiBoxes as $range => $style) {
            $sheet->getStyle($range)->applyFromArray([
                'font' => ['bold' => true, 'size' => 9.5, 'color' => ['rgb' => $style['color']]],
                'alignment' => [
                    'horizontal' => Alignment::HORIZONTAL_CENTER,
                    'vertical' => Alignment::VERTICAL_CENTER,
                ],
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'startColor' => ['rgb' => $style['fill']],
                ],
                'borders' => [
                    'allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => $style['border']]],
                ],
            ]);
        }
        $sheet->getRowDimension(5)->setRowHeight(24);
        $sheet->getRowDimension(6)->setRowHeight(8);

        // Header Kolom (Baris 7 - Urutan dan nama kolom disamakan persis dengan halaman Jadwal Cuti)
        $headers = [
            'No',
            'Nama Karyawan',
            'NIK SAP',
            'Bagian / Afdeling',
            'Jabatan',
            'Status Karyawan',
            'TMT Cuti',
            'Bulan Jatuh Tempo',
            "Hak Cuti Tahun {$selectedYear}",
            'Cuti Panjang Berikutnya',
            'Keterangan',
        ];

        foreach ($headers as $colIdx => $h) {
            $colLetter = Coordinate::stringFromColumnIndex($colIdx + 1);
            $sheet->setCellValue("{$colLetter}7", $h);
        }

        // Style Header Tabel
        $headerRange = 'A7:K7';
        $sheet->getStyle($headerRange)->applyFromArray([
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF'], 'size' => 10.5],
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
        $sheet->getRowDimension(7)->setRowHeight(30);

        // Isi Data (Mulai Baris 8)
        $rowNum = 8;
        foreach ($employees as $idx => $emp) {
            $cutiStatus = $emp->getCutiStatusForYear($selectedYear);

            $nik = $emp->personnel_number ?: '-';
            $tmtCuti = $emp->tmt_cuti ? $emp->tmt_cuti->format('d/m/Y') : '-';
            $bulanTempo = $cutiStatus['is_valid'] ? $cutiStatus['bulan_jatuh_tempo'] : '-';

            // Format Hak Cuti disamakan dengan label badge di halaman cuti
            if (! $cutiStatus['is_valid']) {
                $hakCutiText = 'Perlu Verifikasi';
            } elseif ($cutiStatus['is_cuti_panjang']) {
                $hakCutiText = 'Cuti Panjang (Ke-'.$cutiStatus['siklus_ke'].')';
            } elseif ($cutiStatus['jenis_cuti'] === 'Belum Memenuhi Syarat') {
                $hakCutiText = 'Belum Memenuhi Syarat';
            } else {
                $hakCutiText = 'Cuti Tahunan';
            }

            // Format Cuti Panjang Berikutnya disamakan dengan badge di halaman cuti
            $cutiPanjangBerikutnyaText = ($cutiStatus['is_valid'] && is_numeric($cutiStatus['tahun_cuti_panjang_berikutnya']))
                ? 'Thn '.$cutiStatus['tahun_cuti_panjang_berikutnya']
                : '-';

            $rowValues = [
                $idx + 1,
                $emp->nama ?: '-',
                $nik,
                $emp->bagian?->nama_bagian ?: '-',
                $emp->jabatan?->nama_jabatan ?: '-',
                $emp->status_karyawan ?: '-',
                $tmtCuti,
                $bulanTempo,
                $hakCutiText,
                $cutiPanjangBerikutnyaText,
                $cutiStatus['keterangan'] ?: '-',
            ];

            foreach ($rowValues as $cIdx => $val) {
                $colLetter = Coordinate::stringFromColumnIndex($cIdx + 1);
                // NIK SAP disimpan sebagai explicit string agar angka panjang/angka 0 di depan tidak rusak
                if ($cIdx === 2) {
                    $sheet->setCellValueExplicit("{$colLetter}{$rowNum}", (string) $val, DataType::TYPE_STRING);
                } else {
                    $sheet->setCellValue("{$colLetter}{$rowNum}", $val);
                }
            }

            // Zebra striping baris genap/ganjil
            if ($rowNum % 2 === 1) {
                $sheet->getStyle("A{$rowNum}:K{$rowNum}")->getFill()
                    ->setFillType(Fill::FILL_SOLID)
                    ->getStartColor()->setRGB('F8FAF9');
            }

            // Highlight Kolom Hak Cuti (Kolom I) sesuai status seperti di halaman web
            if ($cutiStatus['is_cuti_panjang']) {
                $sheet->getStyle("I{$rowNum}")->getFont()->setBold(true)->getColor()->setRGB('92400E');
                $sheet->getStyle("I{$rowNum}")->getFill()
                    ->setFillType(Fill::FILL_SOLID)
                    ->getStartColor()->setRGB('FEF3C7');
            } elseif (! $cutiStatus['is_valid']) {
                $sheet->getStyle("I{$rowNum}")->getFont()->setBold(true)->getColor()->setRGB('991B1B');
                $sheet->getStyle("I{$rowNum}")->getFill()
                    ->setFillType(Fill::FILL_SOLID)
                    ->getStartColor()->setRGB('FEE2E2');
            } else {
                $sheet->getStyle("I{$rowNum}")->getFont()->getColor()->setRGB('166534');
                $sheet->getStyle("I{$rowNum}")->getFill()
                    ->setFillType(Fill::FILL_SOLID)
                    ->getStartColor()->setRGB('DCFCE7');
            }

            // Highlight Kolom Cuti Panjang Berikutnya jika ada angka tahunnya
            if ($cutiPanjangBerikutnyaText !== '-') {
                $sheet->getStyle("J{$rowNum}")->getFont()->setBold(true)->getColor()->setRGB('065F46');
                $sheet->getStyle("J{$rowNum}")->getFill()
                    ->setFillType(Fill::FILL_SOLID)
                    ->getStartColor()->setRGB('ECFDF5');
            }

            // TMT Cuti tebal dengan warna hijau corporate jika tersedia
            if ($tmtCuti !== '-') {
                $sheet->getStyle("G{$rowNum}")->getFont()->setBold(true)->getColor()->setRGB('1E5631');
            }

            $sheet->getRowDimension($rowNum)->setRowHeight(22);
            $rowNum++;
        }

        $lastDataRow = $rowNum - 1;

        // Styling Border dan Alignment Tabel Data
        if ($lastDataRow >= 8) {
            $sheet->getStyle("A8:K{$lastDataRow}")->applyFromArray([
                'font' => ['size' => 10],
                'borders' => [
                    'allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => 'D0DFD5']],
                ],
                'alignment' => [
                    'vertical' => Alignment::VERTICAL_CENTER,
                ],
            ]);

            // Alignment per kolom
            $sheet->getStyle("A8:A{$lastDataRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle("B8:B{$lastDataRow}")->getFont()->setBold(true);
            $sheet->getStyle("C8:C{$lastDataRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle("D8:E{$lastDataRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);
            $sheet->getStyle("F8:F{$lastDataRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle("G8:J{$lastDataRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle("K8:K{$lastDataRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);

            // Aktifkan AutoFilter pada tabel
            $sheet->setAutoFilter("A7:K{$lastDataRow}");
        }

        // Freeze pane pada header tabel (baris 8 pertama yang scrolling)
        $sheet->freezePane('A8');

        // Pengaturan lebar kolom yang rapi dan nyaman dibaca
        $columnWidths = [
            1 => 6,   // No
            2 => 28,  // Nama Karyawan
            3 => 16,  // NIK SAP
            4 => 22,  // Bagian / Afdeling
            5 => 26,  // Jabatan
            6 => 16,  // Status Karyawan
            7 => 14,  // TMT Cuti
            8 => 18,  // Bulan Jatuh Tempo
            9 => 24,  // Hak Cuti Tahun
            10 => 22, // Cuti Panjang Berikutnya
            11 => 42, // Keterangan
        ];

        foreach ($columnWidths as $colIdx => $minWidth) {
            $sheet->getColumnDimensionByColumn($colIdx)->setWidth($minWidth);
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

    /**
     * Form edit data cuti karyawan (TMT Cuti & Tanggal Mulai Kerja).
     */
    public function edit(Employee $employee): View
    {
        return view('leaves.edit', compact('employee'));
    }

    /**
     * Simpan perubahan data cuti karyawan.
     */
    public function update(Request $request, Employee $employee): RedirectResponse
    {
        $validated = $request->validate([
            'tmt_cuti' => ['nullable', 'date'],
            'tanggal_mulai_kerja' => ['nullable', 'date'],
        ]);

        $employee->update($validated);

        return redirect()
            ->route('leaves.index')
            ->with('success', 'Data cuti karyawan '.$employee->nama.' berhasil diperbarui.');
    }

    /**
     * Reset TMT Cuti karyawan (hapus override, kembali ke tanggal mulai kerja).
     */
    public function destroy(Employee $employee): RedirectResponse
    {
        $employee->update(['tmt_cuti' => null]);

        return redirect()
            ->route('leaves.index')
            ->with('success', 'TMT Cuti karyawan '.$employee->nama.' telah direset.');
    }
}
