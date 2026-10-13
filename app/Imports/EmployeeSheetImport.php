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
use Maatwebsite\Excel\Concerns\SkipsEmptyRows;

class EmployeeSheetImport implements ToCollection, WithHeadingRow, SkipsEmptyRows
{
    protected string $sheetName;
    protected string $sourceFile;

    public int $inserted   = 0;
    public int $updated    = 0;
    public int $skipped    = 0;
    public array $errors   = [];

    protected array $unitCache    = [];
    protected array $bagianCache  = [];
    protected array $jabatanCache = [];

    /**
     * Pemetaan nama kolom fleksibel dari berbagai format Excel SAP / internal.
     */
    protected array $columnMap = [
        'pers_no'             => ['pers.no.', 'pers no', 'pers.no', 'no pers', 'no.'],
        'personnel_number'    => ['personnel number', 'personnel no', 'nik sap', 'nomor personel', 'nip'],
        'nama'                => ['employee name', 'nama', 'nama karyawan', 'name', 'employee  name', 'nama lengkap'],
        'tanggal_lahir'       => ['date of birth', 'tanggal lahir', 'tgl lahir', 'date of  birth', 'tgl. lahir'],
        'tanggal_mulai_kerja' => ['start date', 'tanggal mulai kerja', 'tgl mulai kerja', 'tanggal masuk', 'start  date'],
        'jenis_kelamin'       => ['gender', 'jenis kelamin', 'j/k', 'sex', 'jenis  kelamin'],
        'agama'               => ['religion', 'agama'],
        'pendidikan_terakhir' => ['education', 'pendidikan terakhir', 'pendidikan', 'last education', 'educ.'],
        'organizational_unit' => ['organizational unit', 'bagian', 'divisi', 'org. unit', 'organizational  unit'],
        'position'            => ['position', 'jabatan', 'posisi'],
        'personnel_subarea'   => ['personnel subarea', 'unit', 'kebun', 'subarea', 'personnel  subarea'],
        'status_karyawan'     => ['employee group', 'status karyawan', 'employee  group', 'group', 'emp.group'],
        'golongan'            => ['pay scale group', 'golongan', 'pay scale  group', 'pay  scale group'],
        'grup_golongan'       => ['pay scale type', 'grup golongan', 'pay scale  type', 'pay  scale type'],
        'grup_jabatan'        => ['emp. group', 'grup jabatan', 'employee group 2', 'emp.  group'],
        'unit_kode'           => ['unit', 'kode unit'],
    ];

    public function __construct(string $sheetName, string $sourceFile)
    {
        $this->sheetName  = $sheetName;
        $this->sourceFile = $sourceFile;
    }

    public function collection(Collection $rows): void
    {
        foreach ($rows as $row) {
            try {
                $data = $this->mapRow($row->toArray());

                // Skip baris yang tidak punya nama valid
                if (empty($data['nama']) || strlen(trim((string)$data['nama'])) < 2) {
                    $this->skipped++;
                    continue;
                }

                // Resolve relasi
                $unitId    = $this->findOrCreateUnit($data);
                $bagianId  = $this->findOrCreateBagian($data['organizational_unit'] ?? null);
                $jabatanId = $this->findOrCreateJabatan($data['position'] ?? null);

                // Format tanggal
                $tglLahir = $this->parseDate($data['tanggal_lahir'] ?? null);
                $tglMulai = $this->parseDate($data['tanggal_mulai_kerja'] ?? null);

                // Map jenis kelamin
                $jk = $this->mapGender($data['jenis_kelamin'] ?? null);

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

                // Cari/buat berdasarkan personnel_number (NIK SAP)
                $nik = $this->cleanString($data['personnel_number'] ?? null);

                if (!empty($nik)) {
                    $emp = Employee::withTrashed()->where('personnel_number', $nik)->first();
                    if ($emp) {
                        $emp->restore();
                        $emp->update($payload);
                        $this->updated++;
                    } else {
                        Employee::create(array_merge($payload, ['personnel_number' => $nik]));
                        $this->inserted++;
                    }
                } else {
                    // Tanpa NIK — dedup berdasarkan nama + unit + lembar
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
                $nama = $data['nama'] ?? 'baris tanpa nama';
                $this->errors[] = "[{$this->sheetName}] {$nama}: " . $e->getMessage();
                $this->skipped++;
            }
        }
    }

    protected function mapRow(array $row): array
    {
        // Normalisasi key: lowercase + trim
        $normalized = [];
        foreach ($row as $rawKey => $value) {
            $key = strtolower(trim(preg_replace('/\s+/', ' ', (string)$rawKey)));
            $normalized[$key] = $value;
        }

        $result = [];
        foreach ($this->columnMap as $field => $aliases) {
            $result[$field] = null;
            foreach ($aliases as $alias) {
                if (array_key_exists($alias, $normalized)) {
                    $result[$field] = $normalized[$alias];
                    break;
                }
            }
        }
        return $result;
    }

    protected function findOrCreateUnit(array $data): ?int
    {
        $kode = $this->cleanString($data['unit_kode'] ?? null);
        $nama = $this->cleanString($data['personnel_subarea'] ?? null);

        if (empty($kode) && empty($nama)) return null;
        $cacheKey = ($kode ?? '') . '|' . ($nama ?? '');

        if (isset($this->unitCache[$cacheKey])) return $this->unitCache[$cacheKey];

        if ($kode) {
            $unit = Unit::firstOrCreate(
                ['kode_unit' => strtoupper($kode)],
                ['nama_unit' => $nama ?: $kode, 'nama_singkat' => strtoupper(substr($kode, 0, 20))]
            );
        } else {
            $unit = Unit::firstOrCreate(
                ['nama_unit' => $nama],
                ['kode_unit' => strtoupper(substr($nama, 0, 20)), 'nama_singkat' => strtoupper(substr($nama, 0, 20))]
            );
        }

        return $this->unitCache[$cacheKey] = $unit->id;
    }

    protected function findOrCreateBagian(?string $nama): ?int
    {
        $nama = $this->cleanString($nama);
        if (empty($nama)) return null;
        $key = strtoupper($nama);
        if (isset($this->bagianCache[$key])) return $this->bagianCache[$key];
        $bagian = Bagian::firstOrCreate(['nama_bagian' => strtoupper($nama)]);
        return $this->bagianCache[$key] = $bagian->id;
    }

    protected function findOrCreateJabatan(?string $nama): ?int
    {
        $nama = $this->cleanString($nama);
        if (empty($nama)) return null;
        $key = strtoupper($nama);
        if (isset($this->jabatanCache[$key])) return $this->jabatanCache[$key];
        $jabatan = Jabatan::firstOrCreate(['nama_jabatan' => strtoupper($nama)]);
        return $this->jabatanCache[$key] = $jabatan->id;
    }

    protected function parseDate($value): ?string
    {
        if ($value === null || $value === '') return null;
        try {
            if (is_numeric($value) && $value > 100) {
                // Excel serial date
                $dt = \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject((float)$value);
                return Carbon::instance($dt)->toDateString();
            }
            return Carbon::parse((string)$value)->toDateString();
        } catch (\Throwable) {
            return null;
        }
    }

    protected function mapGender($val): string
    {
        $v = strtolower(trim((string)$val));
        return match(true) {
            in_array($v, ['l','laki-laki','laki','male','m','pria','1']) => 'Laki-Laki',
            in_array($v, ['p','perempuan','wanita','female','f','0'])    => 'Perempuan',
            default => 'Tidak Diketahui',
        };
    }

    protected function mapPendidikan($val): ?string
    {
        $v = strtoupper(trim((string)$val));
        return match($v) {
            'SD', 'SEKOLAH DASAR' => 'SD',
            'SMP', 'SLTP'         => 'SLTP',
            'SMA', 'SMK', 'SLTA'  => 'SLTA',
            'D1'  => 'D1', 'D2' => 'D2', 'D3' => 'D3', 'D4' => 'D4',
            'S1', 'SARJANA'       => 'S1',
            'S2', 'MAGISTER'      => 'S2',
            'S3', 'DOKTOR'        => 'S3',
            default => ($v ?: null),
        };
    }

    protected function cleanString($val): ?string
    {
        if ($val === null) return null;
        $clean = trim((string)$val);
        return $clean === '' ? null : $clean;
    }
}
