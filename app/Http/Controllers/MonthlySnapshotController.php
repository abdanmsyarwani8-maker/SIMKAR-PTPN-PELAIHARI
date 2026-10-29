<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use App\Models\MonthlySnapshot;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Cell\DataType;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Symfony\Component\HttpFoundation\Response;

class MonthlySnapshotController extends Controller
{
    /**
     * Halaman daftar snapshot bulanan.
     */
    public function index()
    {
        $snapshots = MonthlySnapshot::with('user')
            ->orderByDesc('tahun')
            ->orderByDesc('bulan')
            ->get();

        $bulanIndo = [
            1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
            5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
            9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember',
        ];

        return view('snapshots.index', compact('snapshots', 'bulanIndo'));
    }

    /**
     * Simpan snapshot data karyawan bulan ini ke storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'bulan' => ['required', 'integer', 'min:1', 'max:12'],
            'tahun' => ['required', 'integer', 'min:2020', 'max:2099'],
            'catatan' => ['nullable', 'string', 'max:500'],
        ]);

        $bulan = (int) $request->input('bulan');
        $tahun = (int) $request->input('tahun');

        // Cek duplikat — timpa jika sudah ada
        $existing = MonthlySnapshot::where('bulan', $bulan)->where('tahun', $tahun)->first();
        if ($existing) {
            Storage::disk('local')->delete($existing->path);
            $existing->delete();
        }

        // Generate Excel
        $spreadsheet = $this->buildSnapshotSpreadsheet();
        $totalKaryawan = Employee::active()->count();

        $bulanIndo = [
            1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
            5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
            9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember',
        ];
        $bulanLabel = $bulanIndo[$bulan] ?? $bulan;
        $fileName = "Snapshot_Karyawan_{$bulanLabel}_{$tahun}.xlsx";
        $storagePath = "snapshots/{$tahun}/{$bulan}_{$fileName}";

        // Simpan ke storage local
        $tempStream = fopen('php://memory', 'r+');
        $writer = new Xlsx($spreadsheet);
        $writer->save($tempStream);
        rewind($tempStream);
        Storage::disk('local')->put($storagePath, $tempStream);
        if (is_resource($tempStream)) {
            fclose($tempStream);
        }

        MonthlySnapshot::create([
            'bulan' => $bulan,
            'tahun' => $tahun,
            'nama_file' => $fileName,
            'path' => $storagePath,
            'jumlah_karyawan' => $totalKaryawan,
            'user_id' => auth()->id(),
            'catatan' => $request->input('catatan'),
        ]);

        return redirect()
            ->route('snapshots.index')
            ->with('success', "Snapshot data karyawan bulan {$bulanLabel} {$tahun} berhasil disimpan ({$totalKaryawan} karyawan).");
    }

    /**
     * Download snapshot yang sudah disimpan.
     */
    public function download(MonthlySnapshot $snapshot): Response
    {
        if (! Storage::disk('local')->exists($snapshot->path)) {
            return redirect()
                ->route('snapshots.index')
                ->with('error', 'File snapshot tidak ditemukan di storage.');
        }

        return Storage::disk('local')->download(
            $snapshot->path,
            $snapshot->nama_file,
            [
                'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            ]
        );
    }

    /**
     * Hapus snapshot.
     */
    public function destroy(MonthlySnapshot $snapshot)
    {
        Storage::disk('local')->delete($snapshot->path);
        $snapshot->delete();

        return redirect()
            ->route('snapshots.index')
            ->with('success', "Snapshot {$snapshot->periode_label} berhasil dihapus.");
    }

    /**
     * Generate spreadsheet multi-sheet berisi semua kategori karyawan aktif.
     */
    protected function buildSnapshotSpreadsheet(): Spreadsheet
    {
        $spreadsheet = new Spreadsheet;
        $spreadsheet->removeSheetByIndex(0);

        $categories = ['PKWT', 'Karpim', 'Karpel', 'KTNG', 'BHL'];

        foreach ($categories as $category) {
            $query = Employee::with(['bagian', 'jabatan', 'families'])->active();

            if ($category === 'PKWT') {
                $query->where('status_karyawan', 'like', '%PKWT%');
            } elseif ($category === 'KTNG') {
                $query->where('status_karyawan', 'like', '%KTNG%');
            } elseif ($category === 'BHL') {
                $query->where('status_karyawan', 'like', '%BHL%');
            } elseif ($category === 'Karpim') {
                $query->where('status_karyawan', 'like', '%Karpim%');
            } elseif ($category === 'Karpel') {
                $query->where('status_karyawan', 'like', '%Karpel%');
            }

            $emps = $query->orderBy('nama')->get();

            $sheet = $spreadsheet->createSheet();
            $sheet->setTitle($category);

            $isPkwt = ($category === 'PKWT');

            if ($isPkwt) {
                $headers = [
                    'NIK SAP', 'Nama Karyawan', 'Status Karyawan', 'No. PKWT',
                    'TMT Kerja', 'Tanggal Lahir Karyawan', 'Jenis Kelamin Karyawan',
                    'Agama', 'Pendidikan Terakhir', 'Grade', 'Bagian', 'Jabatan',
                    'Suku', 'Alamat', 'Domisili',
                    'Nama Anggota Keluarga', 'Hubungan Keluarga',
                    'Jenis Kelamin Keluarga', 'Tanggal Lahir Keluarga',
                ];
            } else {
                $headers = [
                    'NIK SAP', 'Nama Karyawan', 'Status Karyawan',
                    'TMT Kerja', 'Tanggal Lahir Karyawan', 'Jenis Kelamin Karyawan',
                    'Agama', 'Pendidikan Terakhir', 'Grade', 'Golongan',
                    'Bagian', 'Jabatan', 'Suku', 'Alamat', 'Domisili',
                    'Nama Anggota Keluarga', 'Hubungan Keluarga',
                    'Jenis Kelamin Keluarga', 'Tanggal Lahir Keluarga',
                ];
            }

            // Header row
            foreach ($headers as $colIdx => $header) {
                $colLetter = Coordinate::stringFromColumnIndex($colIdx + 1);
                $sheet->setCellValue($colLetter.'1', $header);
            }

            $lastColLetter = Coordinate::stringFromColumnIndex(count($headers));
            $headerRange = "A1:{$lastColLetter}1";
            $sheet->getStyle($headerRange)->applyFromArray([
                'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF'], 'size' => 11],
                'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '1E5E3A']],
                'alignment' => [
                    'horizontal' => Alignment::HORIZONTAL_CENTER,
                    'vertical' => Alignment::VERTICAL_CENTER,
                    'wrapText' => true,
                ],
            ]);
            $sheet->getRowDimension(1)->setRowHeight(28);

            // Data rows
            $rowNum = 2;
            foreach ($emps as $emp) {
                $jk = in_array($emp->jenis_kelamin, ['Laki-Laki', 'Perempuan']) ? $emp->jenis_kelamin : '-';
                $domisili = $emp->domisili ?: '-';
                $nik = $emp->personnel_number ?: '-';

                if ($isPkwt) {
                    $baseValues = [
                        $nik, (string) ($emp->nama ?? '-'), (string) ($emp->status_karyawan ?? '-'),
                        (string) ($emp->no_pkwt ?: '-'),
                        $emp->tanggal_mulai_kerja?->format('Y-m-d') ?? '-',
                        $emp->tanggal_lahir?->format('Y-m-d') ?? '-', $jk,
                        (string) ($emp->agama ?: '-'), (string) ($emp->pendidikan_terakhir ?: '-'),
                        (string) ($emp->grade ?: '-'), (string) ($emp->bagian?->nama_bagian ?: '-'),
                        (string) ($emp->jabatan?->nama_jabatan ?: '-'),
                        (string) ($emp->suku ?: '-'), (string) ($emp->alamat ?: '-'), $domisili,
                    ];
                } else {
                    $baseValues = [
                        $nik, (string) ($emp->nama ?? '-'), (string) ($emp->status_karyawan ?? '-'),
                        $emp->tanggal_mulai_kerja?->format('Y-m-d') ?? '-',
                        $emp->tanggal_lahir?->format('Y-m-d') ?? '-', $jk,
                        (string) ($emp->agama ?: '-'), (string) ($emp->pendidikan_terakhir ?: '-'),
                        (string) ($emp->grade ?: '-'), (string) ($emp->golongan ?: '-'),
                        (string) ($emp->bagian?->nama_bagian ?: '-'),
                        (string) ($emp->jabatan?->nama_jabatan ?: '-'),
                        (string) ($emp->suku ?: '-'), (string) ($emp->alamat ?: '-'), $domisili,
                    ];
                }

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

            // Border & layout
            if ($rowNum > 2) {
                $dataRange = "A2:{$lastColLetter}".($rowNum - 1);
                $sheet->getStyle($dataRange)->applyFromArray([
                    'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => 'DCE7E1']]],
                    'alignment' => ['vertical' => Alignment::VERTICAL_CENTER],
                ]);
            }

            for ($col = 1; $col <= count($headers); $col++) {
                $colLetter = Coordinate::stringFromColumnIndex($col);
                $sheet->getColumnDimension($colLetter)->setAutoSize(true);
            }
            $sheet->freezePane('A2');
        }

        $spreadsheet->setActiveSheetIndex(0);

        return $spreadsheet;
    }

    /**
     * Tulis 1 baris nilai ke sheet dengan format text eksplisit untuk NIK SAP.
     */
    protected function writeRowValues($sheet, int $rowNum, array $values): void
    {
        foreach ($values as $colIdx => $val) {
            $colLetter = Coordinate::stringFromColumnIndex($colIdx + 1);
            if ($colIdx === 0) {
                $sheet->setCellValueExplicit($colLetter.$rowNum, (string) $val, DataType::TYPE_STRING);
            } else {
                $sheet->setCellValue($colLetter.$rowNum, (string) $val);
            }
        }
        $sheet->getRowDimension($rowNum)->setRowHeight(20);
    }
}
