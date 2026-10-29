<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use App\Models\MonthlySnapshot;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
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
        $hasTable = Schema::hasTable('monthly_snapshots');
        $snapshots = collect();
        $dbError = null;

        if ($hasTable) {
            try {
                $snapshots = MonthlySnapshot::with('user')
                    ->orderByDesc('tahun')
                    ->orderByDesc('bulan')
                    ->orderByDesc('id')
                    ->get();
            } catch (\Throwable $e) {
                Log::error('Gagal mengambil data snapshots: '.$e->getMessage());
                $dbError = 'Terjadi kendala saat membaca data snapshot: '.$e->getMessage();
            }
        } else {
            $dbError = 'Tabel database snapshot (`monthly_snapshots`) belum dibuat di server hosting. Silakan jalankan migrasi database.';
        }

        $bulanIndo = [
            1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
            5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
            9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember',
        ];

        // Hitung statistik karyawan aktif untuk info form
        $countPkwt = Employee::active()->where('status_karyawan', 'like', '%PKWT%')->count();
        $countKarpim = Employee::active()->where('status_karyawan', 'like', '%Karpim%')->count();
        $countKarpel = Employee::active()->where('status_karyawan', 'like', '%Karpel%')->count();
        $countKtng = Employee::active()->where('status_karyawan', 'like', '%KTNG%')->count();
        $countBhl = Employee::active()->where('status_karyawan', 'like', '%BHL%')->count();
        $totalEmployee = Employee::active()->count();

        return view('snapshots.index', compact(
            'snapshots',
            'bulanIndo',
            'dbError',
            'countPkwt',
            'countKarpim',
            'countKarpel',
            'countKtng',
            'countBhl',
            'totalEmployee'
        ));
    }

    /**
     * Simpan snapshot data karyawan bulan ini ke storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'bulan' => ['required', 'integer', 'min:1', 'max:12'],
            'tahun' => ['required', 'integer', 'min:2020', 'max:2099'],
            'include_family' => ['nullable', 'boolean'],
            'categories' => ['nullable', 'array'],
            'categories.*' => ['string'],
            'catatan' => ['nullable', 'string', 'max:500'],
        ]);

        if (! Schema::hasTable('monthly_snapshots')) {
            return redirect()
                ->route('snapshots.index')
                ->with('error', 'Tabel `monthly_snapshots` belum ada di database. Silakan jalankan migrasi database.');
        }

        $bulan = (int) $request->input('bulan');
        $tahun = (int) $request->input('tahun');
        $includeFamily = $request->boolean('include_family', true);

        $allowedCategories = ['PKWT', 'Karpim', 'Karpel', 'KTNG', 'BHL'];
        $rawCategories = $request->input('categories', $allowedCategories);
        if (! is_array($rawCategories) || empty($rawCategories)) {
            $rawCategories = $allowedCategories;
        }
        $categories = array_values(array_intersect($allowedCategories, $rawCategories));
        if (empty($categories)) {
            $categories = $allowedCategories;
        }

        $bulanIndo = [
            1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
            5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
            9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember',
        ];
        $bulanLabel = $bulanIndo[$bulan] ?? $bulan;
        $tipeLabel = $includeFamily ? 'Lengkap' : 'Tanpa_Batih';
        $fileName = "Snapshot_Karyawan_{$tipeLabel}_{$bulanLabel}_{$tahun}.xlsx";
        $storagePath = "snapshots/{$tahun}/{$bulan}_{$fileName}";

        // Cek duplikat — timpa jika sudah ada snapshot bulan, tahun, & tipe yang sama
        $hasIncludeFamilyCol = Schema::hasColumn('monthly_snapshots', 'include_family');
        $queryExisting = MonthlySnapshot::where('bulan', $bulan)->where('tahun', $tahun);
        if ($hasIncludeFamilyCol) {
            $queryExisting->where('include_family', $includeFamily);
        }
        $existing = $queryExisting->first();
        if ($existing) {
            try {
                Storage::disk('local')->delete($existing->path);
            } catch (\Throwable $e) {
                Log::warning('Gagal menghapus file snapshot lama: '.$e->getMessage());
            }
            $existing->delete();
        }

        try {
            // Generate Excel multi-sheet
            $spreadsheet = $this->buildSnapshotSpreadsheet($categories, $includeFamily);

            // Hitung total karyawan yang tersimpan dalam snapshot
            $totalKaryawan = Employee::active()
                ->where(function ($q) use ($categories) {
                    foreach ($categories as $cat) {
                        if ($cat === 'PKWT') {
                            $q->orWhere('status_karyawan', 'like', '%PKWT%');
                        } elseif ($cat === 'KTNG') {
                            $q->orWhere('status_karyawan', 'like', '%KTNG%');
                        } elseif ($cat === 'BHL') {
                            $q->orWhere('status_karyawan', 'like', '%BHL%');
                        } elseif ($cat === 'Karpim') {
                            $q->orWhere('status_karyawan', 'like', '%Karpim%');
                        } elseif ($cat === 'Karpel') {
                            $q->orWhere('status_karyawan', 'like', '%Karpel%');
                        }
                    }
                })
                ->count();

            // Simpan ke storage local
            $tempStream = fopen('php://memory', 'r+');
            $writer = new Xlsx($spreadsheet);
            $writer->save($tempStream);
            rewind($tempStream);

            Storage::disk('local')->put($storagePath, $tempStream);
            if (is_resource($tempStream)) {
                fclose($tempStream);
            }

            $payload = [
                'bulan' => $bulan,
                'tahun' => $tahun,
                'nama_file' => $fileName,
                'path' => $storagePath,
                'jumlah_karyawan' => $totalKaryawan,
                'user_id' => auth()->id(),
                'catatan' => $request->input('catatan'),
            ];

            if ($hasIncludeFamilyCol) {
                $payload['include_family'] = $includeFamily;
            }

            MonthlySnapshot::create($payload);

            $modeText = $includeFamily ? 'Lengkap (+ Batih)' : 'Tanpa Batih';

            return redirect()
                ->route('snapshots.index')
                ->with('success', "Snapshot data karyawan bulan {$bulanLabel} {$tahun} ({$modeText}) berhasil disimpan ({$totalKaryawan} karyawan, sheets: ".implode(', ', $categories).').');
        } catch (\Throwable $e) {
            Log::error('Gagal menyimpan snapshot: '.$e->getMessage(), ['trace' => $e->getTraceAsString()]);

            return redirect()
                ->route('snapshots.index')
                ->with('error', 'Gagal membuat snapshot: '.$e->getMessage().'. Pastikan izin folder storage di server hosting Anda sudah benar.');
        }
    }

    /**
     * Download snapshot yang sudah disimpan.
     */
    public function download(MonthlySnapshot $snapshot): Response
    {
        if (! Storage::disk('local')->exists($snapshot->path)) {
            return redirect()
                ->route('snapshots.index')
                ->with('error', 'File snapshot tidak ditemukan di storage server.');
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
        try {
            Storage::disk('local')->delete($snapshot->path);
        } catch (\Throwable $e) {
            Log::warning('Gagal menghapus file snapshot dari disk: '.$e->getMessage());
        }
        $snapshot->delete();

        return redirect()
            ->route('snapshots.index')
            ->with('success', "Snapshot {$snapshot->periode_label} berhasil dihapus.");
    }

    /**
     * Generate spreadsheet multi-sheet berisi kategori karyawan aktif yang dipilih.
     *
     * @param  array<string>  $categories
     */
    protected function buildSnapshotSpreadsheet(array $categories = ['PKWT', 'Karpim', 'Karpel', 'KTNG', 'BHL'], bool $includeFamily = true): Spreadsheet
    {
        @ini_set('memory_limit', '512M');
        @set_time_limit(300);

        $spreadsheet = new Spreadsheet;
        $spreadsheet->removeSheetByIndex(0);

        foreach ($categories as $category) {
            $relations = ['bagian', 'jabatan'];
            if ($includeFamily) {
                $relations[] = 'families';
            }
            $query = Employee::with($relations)->active();

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
                ];
            } else {
                $headers = [
                    'NIK SAP', 'Nama Karyawan', 'Status Karyawan',
                    'TMT Kerja', 'Tanggal Lahir Karyawan', 'Jenis Kelamin Karyawan',
                    'Agama', 'Pendidikan Terakhir', 'Grade', 'Golongan',
                    'Bagian', 'Jabatan', 'Suku', 'Alamat', 'Domisili',
                ];
            }

            if ($includeFamily) {
                $headers = array_merge($headers, [
                    'Nama Anggota Keluarga', 'Hubungan Keluarga',
                    'Jenis Kelamin Keluarga', 'Tanggal Lahir Keluarga',
                ]);
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
