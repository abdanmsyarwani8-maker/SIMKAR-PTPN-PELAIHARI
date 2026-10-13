<?php

namespace App\Imports;

use App\Models\Employee;
use App\Models\Unit;
use App\Models\Bagian;
use App\Models\Jabatan;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;
use Maatwebsite\Excel\Concerns\SkipsEmptyRows;

class EmployeeImport implements WithMultipleSheets
{
    protected string $sourceFile;
    protected array $selectedSheets;
    public array $importResults = [];

    public function __construct(string $sourceFile = '', array $selectedSheets = [])
    {
        $this->sourceFile     = $sourceFile;
        $this->selectedSheets = $selectedSheets;
    }

    public function sheets(): array
    {
        // Jika tidak ada lembar dipilih, import semua lembar
        // Kita return null-indexed untuk semua, diproses di SheetImport
        return []; // ditangani lewat onEachSheet
    }

    public function getResults(): array
    {
        return $this->importResults;
    }
}

/**
 * Kelas untuk mengimport satu lembar Excel
 */
class EmployeeSheetImport implements ToCollection, WithHeadingRow, SkipsEmptyRows
{
    protected string $sheetName;
    protected string $sourceFile;

    public int $inserted   = 0;
    public int $updated    = 0;
    public int $skipped    = 0;
    public array $errors   = [];

    // Cache referensi untuk efisiensi
    protected array $unitCache    = [];
    protected array $bagianCache  = [];
    protected array $jabatanCache = [];

    // Map kolom fleksibel (beberapa variasi nama kolom dari Excel)
    protected array $columnMap = [
        'pers_no'             => ['pers.no.', 'pers no', 'pers.no', 'no pers'],
        'personnel_number'    => ['personnel number', 'personnel no', 'nik sap', 'nomor personel'],
        'nama'                => ['employee name', 'nama', 'nama karyawan', 'name', 'employee  name'],
        'tanggal_lahir'       => ['date of birth', 'tanggal lahir', 'tgl lahir', 'date of  birth'],
        'tanggal_mulai_kerja' => ['start date', 'tanggal mulai kerja', 'tgl mulai kerja', 'tanggal masuk'],
        'jenis_kelamin'       => ['gender', 'jenis kelamin', 'j/k', 'sex'],
        'agama'               => ['religion', 'agama'],
        'pendidikan_terakhir' => ['education', 'pendidikan terakhir', 'pendidikan', 'last education'],
        'organizational_unit' => ['organizational unit', 'bagian', 'divisi', 'org. unit'],
        'position'            => ['position', 'jabatan', 'posisi'],
        'personnel_subarea'   => ['personnel subarea', 'unit', 'kebun', 'subarea'],
        'status_karyawan'     => ['employee group', 'status karyawan', 'employee  group', 'group'],
        'golongan'            => ['pay scale group', 'golongan', 'pay scale  group'],
        'grup_golongan'       => ['pay scale type', 'grup golongan', 'pay scale  type'],
        'grup_jabatan'        => ['emp. group', 'grup jabatan', 'employee group 2'],
        'unit_kode'           => ['unit', 'kode unit'],
    ];

    public function __construct(string $sheetName, string $sourceFile)
    {
        $this->sheetName  = $sheetName;
        $this->sourceFile = $sourceFile;
    }

    public function collection(Collection $rows)
    {
        foreach ($rows as $row) {
            try {
                $data = $this->mapRow($row->toArray());

                // Skip baris yang tidak punya nama
                if (empty($data['nama']) || strlen(trim($data['nama'])) < 2) {
                    $this->skipped++;
                    continue;
                }

                // Cari unit, bagian, jabatan
                $unitId    = $this->findOrCreateUnit($data);
                $bagianId  = $this->findOrCreateBagian($data['organizational_unit'] ?? null);
                $jabatanId = $this->findOrCreateJabatan($data['position'] ?? null);

                // Format tanggal
                $tglLahir  = $this->parseDate($data['tanggal_lahir'] ?? null);
                $tglMulai  = $this->parseDate($data['tanggal_mulai_kerja'] ?? null);

                // Map jenis kelamin
                $jk = $this->mapGender($data['jenis_kelamin'] ?? null);

                // Buat atau update berdasarkan personnel_number
                $identifier = trim($data['personnel_number'] ?? '');

                $payload = [
                    'nama'                 => $this->cleanString($data['nama']),
                    'pers_no'              => $this->cleanString($data['pers_no'] ?? null),
                    'tanggal_lahir'        => $tglLahir,
                    'tanggal_mulai_kerja'  => $tglMulai,
                    'jenis_kelamin'        => $jk,
                    'agama'                => $this->cleanString($data['agama'] ?? null),
                    'pendidikan_terakhir'  => $this->mapPendidikan($data['pendidikan_terakhir'] ?? null),
                    'unit_id'              => $unitId,
                    'bagian_id'            => $bagianId,
                    'jabatan_id'           => $jabatanId,
                    'status_karyawan'      => $this->cleanString($data['status_karyawan'] ?? null),
                    'golongan'             => $this->cleanString($data['golongan'] ?? null),
                    'grup_golongan'        => $this->cleanString($data['grup_golongan'] ?? null),
                    'grup_jabatan'         => $this->cleanString($data['grup_jabatan'] ?? null),
                    'sumber_lembar'        => $this->sheetName,
                    'sumber_file'          => $this->sourceFile,
                    'is_active'            => true,
                ];

                if (!empty($identifier)) {
                    $emp = Employee::withTrashed()
                        ->where('personnel_number', $identifier)
                        ->first();
                    if ($emp) {
                        $emp->restore();
                        $emp->update($payload);
                        $this->updated++;
                    } else {
                        Employee::create(array_merge($payload, ['personnel_number' => $identifier]));
                        $this->inserted++;
                    }
                } else {
                    // Tanpa NIK: cek duplikat nama + unit
                    $emp = Employee::where('nama', $payload['nama'])
                        ->where('unit_id', $unitId)
                        ->where('sumber_lembar', $this->sheetName)
                        ->first();
                    if ($emp) {
                        $emp->update($payload);
                        $this->updated++;
                    } else {
                        Employee::create($payload);
                        $this->inserted++;
                    }
                }
            } catch (\Throwable $e) {
                $this->errors[] = "Baris error: " . ($data['nama'] ?? 'unknown') . " — " . $e->getMessage();
                $this->skipped++;
            }
        }
    }

    /**
     * Map raw row ke kolom yang dikenal menggunakan columnMap yang fleksibel.
     */
    protected function mapRow(array $row): array
    {
        $normalized = [];
        foreach ($row as $rawKey => $value) {
            $normalized[strtolower(trim((string)$rawKey))] = $value;
        }

        $result = [];
        foreach ($this->columnMap as $field => $aliases) {
            foreach ($aliases as $alias) {
                if (array_key_exists($alias, $normalized)) {
                    $result[$field] = $normalized[$alias];
                    break;
                }
            }
            if (!isset($result[$field])) {
                $result[$field] = null;
            }
        }
        return $result;
    }

    protected function findOrCreateUnit(array $data): ?int
    {
        // Coba dari 'unit_kode' lalu 'personnel_subarea'
        $kode  = $this->cleanString($data['unit_kode'] ?? null);
        $nama  = $this->cleanString($data['personnel_subarea'] ?? null);

        if (empty($kode) && empty($nama)) return null;

        $cacheKey = $kode . '|' . $nama;
        if (isset($this->unitCache[$cacheKey])) return $this->unitCache[$cacheKey];

        $unit = null;
        if ($kode) {
            $unit = Unit::firstOrCreate(
                ['kode_unit' => strtoupper($kode)],
                ['nama_unit' => $nama ?: $kode, 'nama_singkat' => strtoupper($kode)]
            );
        } elseif ($nama) {
            $unit = Unit::firstOrCreate(
                ['nama_unit' => $nama],
                ['kode_unit' => strtoupper(substr($nama, 0, 10)), 'nama_singkat' => strtoupper(substr($nama, 0, 10))]
            );
        }

        $id = $unit?->id;
        $this->unitCache[$cacheKey] = $id;
        return $id;
    }

    protected function findOrCreateBagian(?string $nama): ?int
    {
        $nama = $this->cleanString($nama);
        if (empty($nama)) return null;
        $key = strtoupper($nama);
        if (isset($this->bagianCache[$key])) return $this->bagianCache[$key];
        $bagian = Bagian::firstOrCreate(['nama_bagian' => strtoupper($nama)]);
        $this->bagianCache[$key] = $bagian->id;
        return $bagian->id;
    }

    protected function findOrCreateJabatan(?string $nama): ?int
    {
        $nama = $this->cleanString($nama);
        if (empty($nama)) return null;
        $key = strtoupper($nama);
        if (isset($this->jabatanCache[$key])) return $this->jabatanCache[$key];
        $jabatan = Jabatan::firstOrCreate(['nama_jabatan' => strtoupper($nama)]);
        $this->jabatanCache[$key] = $jabatan->id;
        return $jabatan->id;
    }

    protected function parseDate($value): ?string
    {
        if (empty($value)) return null;
        try {
            // Excel serial number
            if (is_numeric($value) && $value > 1000) {
                return Carbon::instance(\PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($value))->toDateString();
            }
            // String date
            return Carbon::parse((string)$value)->toDateString();
        } catch (\Throwable) {
            return null;
        }
    }

    protected function mapGender(?string $val): string
    {
        $v = strtolower(trim((string)$val));
        if (in_array($v, ['l', 'laki-laki', 'laki', 'male', 'm', 'pria'])) return 'Laki-Laki';
        if (in_array($v, ['p', 'perempuan', 'wanita', 'female', 'f'])) return 'Perempuan';
        return 'Tidak Diketahui';
    }

    protected function mapPendidikan(?string $val): ?string
    {
        $v = strtoupper(trim((string)$val));
        $map = [
            'SD'   => 'SD', 'SEKOLAH DASAR' => 'SD',
            'SMP'  => 'SLTP', 'SLTP' => 'SLTP',
            'SMA'  => 'SLTA', 'SMK'  => 'SLTA', 'SLTA' => 'SLTA',
            'D1'   => 'D1', 'D2' => 'D2', 'D3' => 'D3', 'D4' => 'D4',
            'S1'   => 'S1', 'S2' => 'S2', 'S3' => 'S3',
            'SARJANA' => 'S1', 'DIPLOMA' => 'D3',
        ];
        return $map[$v] ?? ($v ?: null);
    }

    protected function cleanString(?string $val): ?string
    {
        if ($val === null) return null;
        $clean = trim((string)$val);
        return $clean === '' ? null : $clean;
    }
}
