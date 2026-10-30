<?php

namespace App\Imports;

use App\Models\Bagian;
use App\Models\Employee;
use App\Models\EmployeeFamily;
use App\Models\Jabatan;
use App\Models\Unit;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\SkipsEmptyRows;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use PhpOffice\PhpSpreadsheet\Shared\Date;

class EmployeeSheetImport implements SkipsEmptyRows, ToCollection, WithHeadingRow
{
    protected string $sheetName;

    protected string $sourceFile;

    public int $inserted = 0;

    public int $updated = 0;

    public int $skipped = 0;

    public int $familyProcessed = 0;

    public int $familyInserted = 0;

    public int $familyUpdated = 0;

    public array $errors = [];

    public array $skippedDetails = [];

    // Cache identitas dan referensi untuk efisiensi
    protected array $employeeCache = [];

    protected array $unitCache = [];

    protected array $bagianCache = [];

    protected array $jabatanCache = [];

    protected int $currentRowNumber = 2; // Row 1 is header, data starts at row 2

    public function __construct(string $sheetName = 'Sheet1', string $sourceFile = '')
    {
        $this->sheetName = $sheetName;
        $this->sourceFile = $sourceFile;
    }

    public function getSheetName(): string
    {
        return $this->sheetName;
    }

    public function collection(Collection $rows): void
    {
        if ($rows->isEmpty()) {
            $this->errors[] = "[{$this->sheetName}] Lembar kosong, tidak ada baris data.";

            return;
        }

        // Validasi header pada baris pertama
        $firstRow = $rows->first();
        if ($firstRow instanceof Collection) {
            $firstRow = $firstRow->toArray();
        }

        if (! $this->validateHeaders($firstRow)) {
            $this->errors[] = "[{$this->sheetName}] Format lembar tidak sesuai atau kolom wajib (NIK SAP / Personnel Number / Nama Karyawan) tidak ditemukan.";
            $this->skipped += $rows->count();

            return;
        }

        foreach ($rows as $rawRow) {
            $rowNum = $this->currentRowNumber++;
            $rowArray = $rawRow instanceof Collection ? $rawRow->toArray() : (array) $rawRow;

            // Lewati baris yang seluruhnya kosong
            if ($this->isRowEntirelyEmpty($rowArray)) {
                continue;
            }

            try {
                $this->processRow($rowArray, $rowNum);
            } catch (\Throwable $e) {
                $this->skipped++;
                $errorMsg = "[{$this->sheetName}] Baris {$rowNum}: Gagal diproses — ".$e->getMessage();
                $this->errors[] = $errorMsg;
                $this->skippedDetails[] = $errorMsg;
            }
        }
    }

    /**
     * Memproses satu baris data Excel (karyawan dan batih).
     */
    protected function processRow(array $rowArray, int $rowNum): void
    {
        $data = $this->mapRow($rowArray, $rowNum);

        $nik = $data['nik_sap'] ?? null;
        $namaKaryawan = $data['nama_karyawan'] ?? null;
        $statusKaryawan = $data['status_karyawan'] ?? null;
        $isBhlRow = str_contains(strtoupper((string) $statusKaryawan), 'BHL');

        // Validasi NIK SAP sebagai identitas utama
        // Pengecualian: BHL boleh tanpa NIK SAP — akan diidentifikasi via nama
        if (empty($nik)) {
            if ($isBhlRow && ! empty($namaKaryawan)) {
                // Buat pseudo-NIK dari nama agar tidak duplikat dalam cache
                $nik = 'BHL-'.md5(strtolower(trim($namaKaryawan)).'-'.$this->sheetName);
            } else {
                $this->skipped++;
                $label = $namaKaryawan ?: 'Nama tidak tersedia';
                $msg = "[{$this->sheetName}] Baris {$rowNum}: NIK SAP kosong atau tidak valid (Karyawan: '{$label}'). Baris dilewati.";
                $this->errors[] = $msg;
                $this->skippedDetails[] = $msg;

                return;
            }
        }

        // Resolusi referensi Unit, Bagian, Jabatan
        $unitId = $this->findOrCreateUnit($data['unit_kode'], $data['personnel_subarea']);
        $bagianId = $this->findOrCreateBagian($data['organizational_unit']);
        $jabatanId = $this->findOrCreateJabatan($data['position']);

        // Format tanggal karyawan
        $tglLahirKaryawan = $this->parseDate($data['tanggal_lahir_karyawan'], 'Tanggal Lahir Karyawan', $rowNum);
        $tglMulaiKerja = $this->parseDate($data['tanggal_mulai_kerja'], 'Tanggal Mulai Kerja', $rowNum);
        $tglCuti = $this->parseDate($data['tmt_cuti'] ?? null, 'TMT Cuti', $rowNum);

        // Format jenis kelamin & pendidikan
        $jkKaryawan = $this->mapGender($data['jenis_kelamin_karyawan']);
        $pendidikan = $this->mapPendidikan($data['pendidikan_terakhir']);

        $employeePayload = [
            'pers_no' => $data['pers_no'] ?: $nik,
            'nama' => $namaKaryawan,
            'tanggal_lahir' => $tglLahirKaryawan,
            'tanggal_mulai_kerja' => $tglMulaiKerja,
            'tmt_cuti' => $tglCuti,
            'jenis_kelamin' => $jkKaryawan,
            'agama' => $this->cleanString($data['agama']),
            'pendidikan_terakhir' => $pendidikan,
            'unit_id' => $unitId,
            'bagian_id' => $bagianId,
            'jabatan_id' => $jabatanId,
            'status_karyawan' => $this->cleanString($data['status_karyawan']),
            'golongan' => $this->cleanString($data['golongan']),
            'grup_golongan' => $this->cleanString($data['grup_golongan']),
            'grup_jabatan' => $this->cleanString($data['grup_jabatan']),
            'no_pkwt' => $this->cleanString($data['no_pkwt']),
            'grade' => $this->cleanString($data['grade']),
            'suku' => $this->cleanString($data['suku']),
            'alamat' => $this->cleanString($data['alamat']),
            'domisili' => $data['domisili'] ?? Employee::determineDomisili($data['alamat'] ?? null),
            'sumber_lembar' => $this->sheetName,
            'sumber_file' => $this->sourceFile,
            'is_active' => true,
        ];

        // ── 1. Pemrosesan Data Karyawan ───────────────────────────────
        $employee = $this->employeeCache[$nik] ?? null;
        $isBhlPseudoNik = str_starts_with($nik, 'BHL-');

        if (! $employee) {
            // Pertama kali NIK ini dibaca dalam sesi import ini
            if ($isBhlPseudoNik) {
                // BHL tanpa NIK SAP: cari berdasarkan nama karyawan
                $employee = Employee::withTrashed()
                    ->where('nama', $namaKaryawan)
                    ->where('status_karyawan', 'like', '%BHL%')
                    ->first();
            } else {
                $employee = Employee::withTrashed()->where('personnel_number', $nik)->first();
            }

            if ($employee) {
                // Karyawan sudah ada: restore jika soft-deleted & perbarui data
                if ($employee->trashed()) {
                    $employee->restore();
                }

                // Perbarui field karyawan yang bernilai non-empty pada payload
                $fieldsToUpdate = [];
                foreach ($employeePayload as $col => $val) {
                    if (in_array($col, ['is_active', 'sumber_lembar', 'sumber_file'])) {
                        $fieldsToUpdate[$col] = $val;
                    } elseif ($val !== null && $val !== '') {
                        $fieldsToUpdate[$col] = $val;
                    }
                }

                if (! empty($fieldsToUpdate)) {
                    $employee->update($fieldsToUpdate);
                }
                $this->updated++;
            } else {
                // Karyawan baru: pastikan memiliki nama sebelum disimpan
                if (empty($namaKaryawan)) {
                    $this->skipped++;
                    $msg = "[{$this->sheetName}] Baris {$rowNum}: NIK SAP '{$nik}' belum terdaftar di sistem dan kolom nama kosong. Baris dilewati.";
                    $this->errors[] = $msg;
                    $this->skippedDetails[] = $msg;

                    return;
                }

                // Untuk BHL tanpa NIK SAP: simpan tanpa personnel_number
                $createPayload = $isBhlPseudoNik
                    ? $employeePayload
                    : array_merge($employeePayload, ['personnel_number' => $nik]);

                $employee = Employee::create($createPayload);
                $this->inserted++;
            }

            $this->employeeCache[$nik] = $employee;
        } else {
            // NIK SAP sudah pernah diproses pada baris sebelumnya di lembar ini
            // Aturan 9: Jangan menimpa data karyawan yang sudah valid dengan nilai kosong dari baris lanjutan
            $fillableNewData = [];
            foreach ($employeePayload as $col => $val) {
                if ($val !== null && $val !== '') {
                    $curr = $employee->{$col};
                    if ($curr === null || $curr === '') {
                        $fillableNewData[$col] = $val;
                        $employee->{$col} = $val;
                    }
                }
            }

            if (! empty($fillableNewData)) {
                $employee->save();
            }
        }

        // ── 2. Pemrosesan Data Batih / Keluarga ─────────────────────────
        $namaKeluarga = $this->cleanString($data['nama_keluarga']);

        if ($namaKeluarga && ! in_array(strtoupper($namaKeluarga), ['-', 'TIDAK ADA', 'NIHIL', 'NONE', '0', 'N/A'])) {
            $jkKeluarga = $this->mapGender($data['jenis_kelamin_keluarga']);
            $hubungan = $this->mapRelationship($data['hubungan_keluarga'], $jkKeluarga);
            $tglLahirKeluarga = $this->parseDate($data['tanggal_lahir_keluarga'], 'Tanggal Lahir Keluarga', $rowNum);

            // Jika hubungan belum terisi dari file Excel, lakukan inferensi otomatis (Istri, Suami, Anak, Orang Tua)
            if (empty($hubungan)) {
                $hubungan = $this->inferRelationship($employee, $jkKeluarga, $tglLahirKeluarga);
            }

            // Jika jenis kelamin batih belum diketahui namun statusnya Istri/Suami, inferensikan
            if ($jkKeluarga === 'Tidak Diketahui') {
                if ($hubungan === 'Istri') {
                    $jkKeluarga = 'Perempuan';
                } elseif ($hubungan === 'Suami') {
                    $jkKeluarga = 'Laki-Laki';
                }
            }

            $familyPayload = [
                'employee_id' => $employee->id,
                'nama' => $namaKeluarga,
                'hubungan' => $hubungan,
                'jenis_kelamin' => $jkKeluarga,
                'tanggal_lahir' => $tglLahirKeluarga,
            ];

            // Pencocokan batih untuk menghindari duplikasi saat re-import:
            // 1. Cocokkan berdasarkan employee_id + nama batih
            $existingFamily = EmployeeFamily::where('employee_id', $employee->id)
                ->where('nama', $namaKeluarga)
                ->first();

            // 2. Jika tidak cocok nama persis, cocokkan berdasarkan employee_id + hubungan + tanggal lahir
            if (! $existingFamily && ! empty($hubungan) && ! empty($tglLahirKeluarga)) {
                $existingFamily = EmployeeFamily::where('employee_id', $employee->id)
                    ->where('hubungan', $hubungan)
                    ->whereDate('tanggal_lahir', $tglLahirKeluarga)
                    ->first();
            }

            if ($existingFamily) {
                $familyUpdates = [];
                if ($hubungan && $existingFamily->hubungan !== $hubungan) {
                    $familyUpdates['hubungan'] = $hubungan;
                }
                if ($jkKeluarga !== 'Tidak Diketahui' && $existingFamily->jenis_kelamin !== $jkKeluarga) {
                    $familyUpdates['jenis_kelamin'] = $jkKeluarga;
                }
                if ($tglLahirKeluarga && (empty($existingFamily->tanggal_lahir) || $existingFamily->tanggal_lahir->toDateString() !== $tglLahirKeluarga)) {
                    $familyUpdates['tanggal_lahir'] = $tglLahirKeluarga;
                }

                if (! empty($familyUpdates)) {
                    $existingFamily->update($familyUpdates);
                }
                $this->familyUpdated++;
            } else {
                EmployeeFamily::create($familyPayload);
                $this->familyInserted++;
            }

            $this->familyProcessed++;
        }
    }

    /**
     * Memetakan baris mentah Excel menjadi array terstruktur dengan variasi nama kolom.
     */
    protected function mapRow(array $row, int $rowNum): array
    {
        $normalized = [];
        foreach ($row as $rawKey => $val) {
            $key = strtolower(trim((string) $rawKey));
            $normalized[$key] = $val;

            $cleanKey = preg_replace('/[^a-z0-9]+/', '_', $key);
            $cleanKey = trim($cleanKey, '_');
            $normalized[$cleanKey] = $val;

            $noUnderscore = str_replace('_', '', $cleanKey);
            $normalized[$noUnderscore] = $val;
        }

        $get = function (array $aliases) use ($normalized) {
            foreach ($aliases as $alias) {
                $k = strtolower(trim($alias));
                if (array_key_exists($k, $normalized) && $normalized[$k] !== null && trim((string) $normalized[$k]) !== '') {
                    return $normalized[$k];
                }
                $clean = trim(preg_replace('/[^a-z0-9]+/', '_', $k), '_');
                if (array_key_exists($clean, $normalized) && $normalized[$clean] !== null && trim((string) $normalized[$clean]) !== '') {
                    return $normalized[$clean];
                }
                $noUnderscore = str_replace('_', '', $clean);
                if (array_key_exists($noUnderscore, $normalized) && $normalized[$noUnderscore] !== null && trim((string) $normalized[$noUnderscore]) !== '') {
                    return $normalized[$noUnderscore];
                }
            }

            return null;
        };

        // Resolusi Pers.No, Personnel Number, NIK SAP, dan Nama Karyawan
        $rawPersNo = $get(['pers_no', 'persno', 'pers.no.', 'pers no', 'no pers', 'no. pers', 'pers_no_']);
        $rawPersNum = $get(['personnel_number', 'personnelnumber', 'personnel no', 'personnel no.', 'nomor personel', 'no personel']);
        $rawNikSap = $get(['nik_sap', 'niksap', 'nik sap', 'nik', 'no_sap', 'nik_karyawan']);
        $rawEmpName = $get(['nama_karyawan', 'namakaryawan', 'nama karyawan', 'employee_name', 'employee name', 'nama', 'nama_lengkap', 'nama pegawai']);

        $nik = null;
        $namaKaryawan = null;

        if ($rawNikSap !== null) {
            $nik = $this->cleanNik($rawNikSap);
            $namaKaryawan = $this->cleanString($rawEmpName);
        } elseif ($rawPersNo !== null && $rawPersNum !== null) {
            // Format ekspor SAP HCM ALV Grid: Pers.No. berisi digit angka, Personnel Number berisi nama karyawan
            $persNoIsDigits = preg_match('/^\d+$/', trim((string) $rawPersNo));
            $persNumHasLetters = preg_match('/[a-zA-Z]/', trim((string) $rawPersNum));

            if ($persNoIsDigits && $persNumHasLetters) {
                $nik = $this->cleanNik($rawPersNo);
                $namaKaryawan = $this->cleanString($rawPersNum);
            } else {
                $nik = $this->cleanNik($rawPersNum);
                $namaKaryawan = $this->cleanString($rawPersNo ?: $rawEmpName);
            }
        } else {
            $nik = $this->cleanNik($rawNikSap ?? $rawPersNum ?? $rawPersNo);
            $namaKaryawan = $this->cleanString($rawEmpName);
        }

        // Resolusi Kategori / Status Karyawan
        $rawCategory = $get([
            'kategori_karyawan', 'kategori', 'status_karyawan', 'status karyawan', 'status',
            'employee_group', 'employee group', 'emp_group', 'emp. group', 'kelompok_karyawan',
        ]);
        $statusKaryawan = $this->mapCategory($rawCategory, $this->sheetName, $namaKaryawan, $nik);

        // Resolusi Tanggal Lahir Karyawan vs Tanggal Lahir Keluarga
        $rawEmpBirth = $get([
            'tanggal_lahir_karyawan', 'tgl_lahir_karyawan', 'tanggal_lahir', 'tgl_lahir', 'tgl.lahir', 'tgl. lahir',
            'date_of_birth', 'tgl_lahir_ksr', 'tgl_lahir_pegawai',
        ]);
        $rawFamilyBirth = $get([
            'tanggal_lahir_keluarga', 'tanggal_lahir_batih', 'tanggal_lahir_anggota_keluarga',
            'tgl_lahir_keluarga', 'tgl_lahir_batih', 'birth_date_keluarga', 'birth_date_batih',
        ]);
        $rawGenericBirth = $get(['birth_date', 'birthdate', 'tgl_lahir_anak', 'tgl_lahir_istri']);

        if ($rawFamilyBirth === null && $rawGenericBirth !== null) {
            $hasFamilyIndicators = $get([
                'type_of_family_record', 'hubungan_keluarga', 'hubungan_batih',
                'nama_keluarga', 'nama_batih', 'full_name',
            ]);
            if ($hasFamilyIndicators) {
                $rawFamilyBirth = $rawGenericBirth;
                if ($rawEmpBirth === $rawGenericBirth) {
                    $rawEmpBirth = null;
                }
            }
        }

        // Resolusi Jenis Kelamin Karyawan vs Jenis Kelamin Batih
        $rawEmpGender = $get([
            'jenis_kelamin_karyawan', 'jk_karyawan', 'jenis_kelamin', 'gender', 'sex', 'j/k', 'j_k', 'jk',
        ]);
        $rawFamilyGender = $get([
            'jenis_kelamin_batih', 'jenis_kelamin_keluarga', 'jenis_kelamin_anggota_keluarga',
            'gender_batih', 'gender_keluarga', 'jk_batih', 'jk_keluarga', 'sex_batih', 'sex_keluarga',
        ]);

        // Resolusi Data Batih / Keluarga
        $rawFamilyName = $get([
            'nama_anggota_keluarga', 'nama_keluarga', 'nama_batih', 'nama_anggota_batih',
            'family_member_name', 'full_name', 'nama_suami_istri_anak', 'nama_anak', 'nama_istri', 'nama_suami',
        ]);
        $rawFamilyRelation = $get([
            'hubungan_keluarga', 'hubungan_batih', 'hubungan', 'type_of_family_record',
            'family_relationship', 'relation', 'relationship', 'status_hubungan', 'status_batih', 'status_keluarga',
            'status_dalam_keluarga', 'kedudukan_keluarga', 'kedudukan_dalam_keluarga',
            'sebagai', 'kedudukan', 'hub', 'hub_keluarga', 'hubungan_dg_karyawan', 'hubungan_dengan_karyawan',
            'keterangan_keluarga', 'ket_keluarga', 'keterangan_batih', 'ket_batih', 'status_anggota',
            'relasi', 'relasi_keluarga', 'peran_keluarga', 'anggota', 'posisi_keluarga', 'kategori_keluarga',
            'jenis_anggota', 'jenis_keluarga',
        ]);
        if (! $rawFamilyRelation) {
            $rawKet = $get(['keterangan', 'ket']);
            if ($rawKet && in_array(strtolower(trim((string) $rawKet)), ['istri', 'suami', 'anak', 'child', 'spouse', 'pasangan', 'ayah', 'ibu', 'orang tua'])) {
                $rawFamilyRelation = $rawKet;
            }
        }

        // Field Karyawan Lainnya
        $noPkwt = $get(['no_pkwt', 'no.pkwt', 'no. pkwt', 'nomor_pkwt', 'nomor_kontrak', 'no_kontrak', 'spkwt', 'no_spkwt']);
        $tmtKerja = $get(['tmt_kerja', 'tmt.kerja', 'tmt', 'tanggal_mulai_kerja', 'tgl_mulai_kerja', 'start_date', 'tanggal_masuk', 'tgl_masuk', 'mulai_kerja']);
        $tmtCuti = $get(['tmt_cuti', 'tmt.cuti', 'tmt cuti', 'tanggal_cuti', 'tgl_cuti']);
        if ($tmtKerja === null && $tmtCuti !== null) {
            $tmtKerja = $tmtCuti;
        }
        $agama = $get(['agama', 'religion', 'agama_karyawan']);
        $pendidikan = $get(['pendidikan_terakhir', 'pendidikan', 'last_education', 'education', 'educ.', 'pendidikan_karyawan']);
        $grade = $get(['grade', 'grade_karyawan', 'grade_jabatan']);
        $suku = $get(['suku', 'suku_bangsa', 'suku_karyawan', 'etnis']);
        $alamat = $get(['alamat', 'alamat_domisili', 'alamat_karyawan', 'address', 'alamat_ktp', 'alamat_tinggal']);
        $rawDomisili = $get(['domisili', 'status_domisili', 'lokasi_domisili', 'domisili_lokal_non_lokal', 'kategori_domisili', 'lokasi']);
        $domisili = null;
        if ($rawDomisili) {
            $domLower = strtolower(trim((string) $rawDomisili));
            if (str_contains($domLower, 'non')) {
                $domisili = 'Non-Lokal';
            } elseif (str_contains($domLower, 'lokal')) {
                $domisili = 'Lokal';
            }
        }
        if (! $domisili) {
            $domisili = Employee::determineDomisili($alamat);
        }

        $unitKode = $get(['unit', 'kode_unit', 'kebun']);
        $subarea = $get(['personnel_subarea', 'subarea', 'unit', 'kebun', 'lokasi']);
        $bagian = $get(['organizational_unit', 'bagian', 'sub_bagian', 'sub/bagian', 'org_unit', 'org._unit', 'divisi', 'departemen']);
        $jabatan = $get(['position', 'jabatan', 'posisi', 'pekerjaan']);

        // Jika kolom bagian berisi jabatan khusus krani / petugas global teling dan jabatan belum diisi atau default ke PEMANEN
        if ($bagian) {
            $upperBagian = strtoupper(trim((string) $bagian));
            $kraniPositions = [
                'KRANI PRODUKSI',
                'KRANI TEKNIK',
                'KRANI AKUNTANSI',
                'KRANI FINANSIL',
                'KRANI TATA USAHA',
                'PETUGAS GLOBAL TELING AFD I',
                'PETUGAS GLOBAL TELING AFDELING I',
            ];
            foreach ($kraniPositions as $pos) {
                if (str_contains($upperBagian, $pos)) {
                    if (empty($jabatan) || strtoupper(trim((string) $jabatan)) === 'PEMANEN') {
                        $jabatan = $pos;
                    }
                    break;
                }
            }
        }

        $golongan = $get(['pay_scale_group', 'golongan', 'payscale_group']);
        $grupGolongan = $get(['pay_scale_type', 'grup_golongan', 'payscale_type']);
        $grupJabatan = $get(['emp._group', 'emp_group', 'grup_jabatan', 'employee_group_2']);

        // Jika golongan berisi teks kontrak PKWT seperti 'Pelai/SPKWT/...'
        if ($noPkwt === null && $golongan && str_contains(strtoupper((string) $golongan), 'SPKWT')) {
            $noPkwt = $golongan;
        }

        // Hanya karyawan berstatus PKWT yang memiliki no_pkwt
        if (! str_contains(strtoupper((string) $statusKaryawan), 'PKWT')) {
            $noPkwt = null;
        }

        return [
            'nik_sap' => $nik,
            'pers_no' => $rawPersNo,
            'nama_karyawan' => $namaKaryawan,
            'status_karyawan' => $statusKaryawan,
            'no_pkwt' => $noPkwt,
            'tanggal_mulai_kerja' => $tmtKerja,
            'tmt_cuti' => $tmtCuti,
            'tanggal_lahir_karyawan' => $rawEmpBirth,
            'jenis_kelamin_karyawan' => $rawEmpGender,
            'agama' => $agama,
            'pendidikan_terakhir' => $pendidikan,
            'grade' => $grade,
            'suku' => $suku,
            'alamat' => $alamat,
            'domisili' => $domisili,
            'unit_kode' => $unitKode,
            'personnel_subarea' => $subarea,
            'organizational_unit' => $bagian,
            'position' => $jabatan,
            'golongan' => $golongan,
            'grup_golongan' => $grupGolongan,
            'grup_jabatan' => $grupJabatan,
            'nama_keluarga' => $rawFamilyName,
            'hubungan_keluarga' => $rawFamilyRelation,
            'jenis_kelamin_keluarga' => $rawFamilyGender,
            'tanggal_lahir_keluarga' => $rawFamilyBirth,
        ];
    }

    /**
     * Validasi ketersediaan kolom header esensial.
     */
    protected function validateHeaders(array $firstRow): bool
    {
        $keys = array_map(fn ($k) => strtolower(trim((string) $k)), array_keys($firstRow));
        $cleanKeys = array_map(fn ($k) => trim(preg_replace('/[^a-z0-9]+/', '_', $k), '_'), $keys);

        $allowedIdentifiers = [
            'nik_sap', 'niksap', 'nik', 'personnel_number', 'personnelnumber', 'pers_no', 'persno',
            'pers_no_', 'no_pers', 'nopers', 'nomor_personel', 'nomorpersonel', 'nama', 'nama_karyawan',
            'namakaryawan', 'employee_name', 'employeename', 'name', 'full_name', 'fullname',
        ];

        foreach ($cleanKeys as $clean) {
            $noUnder = str_replace('_', '', $clean);
            if (in_array($clean, $allowedIdentifiers, true) || in_array($noUnder, $allowedIdentifiers, true)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Periksa apakah baris sepenuhnya kosong.
     */
    protected function isRowEntirelyEmpty(array $row): bool
    {
        foreach ($row as $val) {
            if ($val !== null && trim((string) $val) !== '') {
                return false;
            }
        }

        return true;
    }

    /**
     * Bersihkan NIK SAP dan pastikan diperlakukan sebagai teks (angka nol di depan tidak hilang).
     */
    protected function cleanNik($val): ?string
    {
        if ($val === null) {
            return null;
        }

        $clean = trim((string) $val);
        // Hapus petik tunggal pembuka jika file Excel mengekspor teks sebagai '13000...
        $clean = ltrim($clean, "'");
        $clean = trim($clean);

        if ($clean === '' || $clean === '-' || $clean === '0') {
            return null;
        }

        return $clean;
    }

    /**
     * Daftar nama Karpim (Karyawan Pimpinan).
     */
    protected array $namaKarpim = [
        'Abdullah Syafii',
        'Adam Brahmantyo Yogatama',
        'Agus Handoko',
        'Anwar Anshari',
        'Ari Susanto',
        'Arnold Widiarto',
        'Asrofi',
        'Teuku Akil Faza',
    ];

    /**
     * Daftar NIK SAP Karpim (Karyawan Pimpinan).
     */
    protected array $nikKarpim = [
        '13006998',
        '13007684',
        '13002791',
        '13004520',
        '13004850',
        '13005464',
        '13001734',
        '19003363',
    ];

    /**
     * Pemetaan kategori karyawan ke PKWT, KTNG, Karpim, Karpel, atau status asli.
     */
    protected function mapCategory(?string $val, string $sheetName, ?string $namaKaryawan = null, ?string $nik = null): ?string
    {
        $target = $val ? strtolower(trim($val)) : strtolower(trim($sheetName));

        if (str_contains($target, 'pkwt')) {
            return 'PKWT';
        }
        if (str_contains($target, 'ktng')) {
            return 'KTNG';
        }
        if (str_contains($target, 'bhl')) {
            return 'BHL';
        }
        if (str_contains($target, 'karpim & karpel') || str_contains($target, 'karpim/karpel') || str_contains($target, 'karpim dan karpel')) {
            $isKarpim = false;
            if ($namaKaryawan) {
                foreach ($this->namaKarpim as $karpim) {
                    if (strcasecmp(trim($namaKaryawan), trim($karpim)) === 0) {
                        $isKarpim = true;
                        break;
                    }
                }
            }
            if (! $isKarpim && $nik && in_array((string) $nik, $this->nikKarpim, true)) {
                $isKarpim = true;
            }

            return $isKarpim ? 'Karpim' : 'Karpel';
        }
        if (str_contains($target, 'karpim') && ! str_contains($target, 'karpel')) {
            return 'Karpim';
        }
        if (str_contains($target, 'karpel') && ! str_contains($target, 'karpim')) {
            return 'Karpel';
        }
        if (str_contains($target, 'papam')) {
            return 'Papam';
        }

        return $val ? trim($val) : null;
    }

    /**
     * Konversi tanggal fleksibel (Serial Excel, dd/mm/yyyy, yyyy-mm-dd, nama bulan Indonesia).
     */
    protected function parseDate($value, string $fieldName = 'Tanggal', int $rowNum = 0): ?string
    {
        if ($value === null || $value === '') {
            return null;
        }

        if ($value instanceof \DateTimeInterface) {
            return Carbon::instance($value)->toDateString();
        }

        $valStr = trim((string) $value);
        if ($valStr === '' || $valStr === '-' || strtolower($valStr) === 'null') {
            return null;
        }

        try {
            // Excel serial date number
            if (is_numeric($valStr) && (float) $valStr > 1000 && (float) $valStr < 100000) {
                $dt = Date::excelToDateTimeObject((float) $valStr);

                return Carbon::instance($dt)->toDateString();
            }

            // dd/mm/yyyy atau dd-mm-yyyy
            if (preg_match('/^(\d{1,2})[\/\-](\d{1,2})[\/\-](\d{4})$/', $valStr, $m)) {
                $day = (int) $m[1];
                $month = (int) $m[2];
                $year = (int) $m[3];
                if (checkdate($month, $day, $year)) {
                    return sprintf('%04d-%02d-%02d', $year, $month, $day);
                }
            }

            // yyyy-mm-dd
            if (preg_match('/^(\d{4})[\/\-](\d{1,2})[\/\-](\d{1,2})$/', $valStr, $m)) {
                $year = (int) $m[1];
                $month = (int) $m[2];
                $day = (int) $m[3];
                if (checkdate($month, $day, $year)) {
                    return sprintf('%04d-%02d-%02d', $year, $month, $day);
                }
            }

            // Nama bulan bahasa Indonesia (contoh: 15 Januari 1990)
            $indoMonths = [
                'januari' => '01', 'jan' => '01',
                'februari' => '02', 'feb' => '02',
                'maret' => '03', 'mar' => '03',
                'april' => '04', 'apr' => '04',
                'mei' => '05', 'may' => '05',
                'juni' => '06', 'jun' => '06',
                'juli' => '07', 'jul' => '07',
                'agustus' => '08', 'agu' => '08', 'agt' => '08',
                'september' => '09', 'sep' => '09',
                'oktober' => '10', 'okt' => '10', 'oct' => '10',
                'november' => '11', 'nov' => '11',
                'desember' => '12', 'des' => '12', 'dec' => '12',
            ];
            $lower = strtolower($valStr);
            foreach ($indoMonths as $mName => $mNum) {
                if (str_contains($lower, $mName)) {
                    $converted = preg_replace('/\b'.$mName.'\b/i', $mNum, $lower);
                    if (preg_match('/(\d{1,2})\s+(\d{2})\s+(\d{4})/', $converted, $matches)) {
                        if (checkdate((int) $matches[2], (int) $matches[1], (int) $matches[3])) {
                            return sprintf('%04d-%02d-%02d', (int) $matches[3], (int) $matches[2], (int) $matches[1]);
                        }
                    }
                }
            }

            // Parser bawaan Carbon
            $carbon = Carbon::parse($valStr);
            if ($carbon->year > 1900 && $carbon->year < 2100) {
                return $carbon->toDateString();
            }

            throw new \Exception("Tahun di luar rentang wajar: {$carbon->year}");
        } catch (\Throwable) {
            $rowMsg = $rowNum > 0 ? " (Baris {$rowNum})" : '';
            $this->errors[] = "[{$this->sheetName}] Peringatan tanggal{$rowMsg}: Nilai '{$valStr}' pada {$fieldName} tidak dapat diubah ke format tanggal valid.";

            return null;
        }
    }

    /**
     * Pemetaan jenis kelamin sesuai enum database.
     */
    protected function mapGender(?string $val): string
    {
        if ($val === null || $val === '') {
            return 'Tidak Diketahui';
        }

        $v = strtolower(trim($val));

        return match (true) {
            in_array($v, ['l', 'laki-laki', 'laki', 'laki laki', 'male', 'm', 'pria', '1', 'lk']) => 'Laki-Laki',
            in_array($v, ['p', 'perempuan', 'wanita', 'female', 'f', '0', 'pr']) => 'Perempuan',
            default => 'Tidak Diketahui',
        };
    }

    /**
     * Pemetaan hubungan keluarga (Spouse -> Istri/Suami, Child -> Anak).
     */
    protected function mapRelationship(?string $val, ?string $gender = null): ?string
    {
        if ($val === null || $val === '') {
            return null;
        }

        $v = strtolower(trim($val));

        if (in_array($v, ['keluarga', 'batih', 'family', 'anggota', 'anggota keluarga', '-', 'none', 'null', '0'])) {
            return null;
        }

        if (in_array($v, ['spouse', 'pasangan'])) {
            if ($gender === 'Perempuan' || in_array(strtolower((string) $gender), ['p', 'perempuan', 'female', 'f'])) {
                return 'Istri';
            }
            if ($gender === 'Laki-Laki' || in_array(strtolower((string) $gender), ['l', 'laki-laki', 'male', 'm'])) {
                return 'Suami';
            }

            return 'Istri';
        }

        if (in_array($v, ['child', 'anak', 'anak kandung', 'anak angkat', 'anak tiri']) || str_starts_with($v, 'anak')) {
            return 'Anak';
        }

        if (in_array($v, ['istri', 'wife']) || str_starts_with($v, 'istri')) {
            return 'Istri';
        }

        if (in_array($v, ['suami', 'husband']) || str_starts_with($v, 'suami')) {
            return 'Suami';
        }

        if (in_array($v, ['parent', 'orang tua', 'ayah', 'ibu'])) {
            return 'Orang Tua';
        }

        return ucwords(trim($val));
    }

    /**
     * Inferensi hubungan keluarga secara otomatis jika tidak disediakan di kolom Excel.
     * Mengelompokkan ke Istri, Suami, Anak, atau Orang Tua berdasarkan selisih umur dan jenis kelamin.
     */
    protected function inferRelationship(Employee $employee, ?string $famGender, mixed $famBirthDate = null): string
    {
        $empGender = $employee->jenis_kelamin;
        $empBirth = $employee->tanggal_lahir ? Carbon::parse($employee->tanggal_lahir) : null;
        $famBirth = $famBirthDate ? Carbon::parse($famBirthDate) : null;
        $famAge = $famBirth ? $famBirth->diffInYears(now()) : null;

        // Cek apakah karyawan sudah memiliki pasangan (Istri/Suami) yang tercatat
        $hasSpouse = EmployeeFamily::where('employee_id', $employee->id)
            ->whereIn('hubungan', ['Istri', 'Suami'])
            ->exists();

        // 1. Jika tanggal lahir karyawan dan anggota keluarga keduanya diketahui
        if ($empBirth && $famBirth) {
            $ageDiff = $empBirth->diffInYears($famBirth, false); // Positif jika anggota keluarga lahir setelah karyawan (lebih muda)

            // Lahir 15+ tahun setelah karyawan -> Anak
            if ($ageDiff >= 15) {
                return 'Anak';
            }

            // Lahir 16+ tahun sebelum karyawan -> Orang Tua
            if ($ageDiff <= -16) {
                return 'Orang Tua';
            }

            // Selisih usia sebaya (-15 s/d +14 tahun) -> Pasangan jika belum ada pasangan
            if (! $hasSpouse) {
                if ($empGender === 'Laki-Laki' && $famGender === 'Perempuan') {
                    return 'Istri';
                }
                if ($empGender === 'Perempuan' && $famGender === 'Laki-Laki') {
                    return 'Suami';
                }
            }

            // Jika usia anggota keluarga masih muda (< 21 tahun)
            if ($famAge !== null && $famAge < 21) {
                return 'Anak';
            }

            if (! $hasSpouse) {
                if ($famGender === 'Perempuan') {
                    return 'Istri';
                }
                if ($famGender === 'Laki-Laki') {
                    return 'Suami';
                }
            }

            return 'Anak';
        }

        // 2. Jika hanya tanggal lahir anggota keluarga yang diketahui
        if ($famAge !== null) {
            if ($famAge <= 25) {
                return 'Anak';
            }
            if ($famAge > 65) {
                return 'Orang Tua';
            }
            if (! $hasSpouse) {
                if ($empGender === 'Laki-Laki' && $famGender === 'Perempuan') {
                    return 'Istri';
                }
                if ($empGender === 'Perempuan' && $famGender === 'Laki-Laki') {
                    return 'Suami';
                }

                return ($empGender === 'Perempuan') ? 'Suami' : 'Istri';
            }

            return 'Anak';
        }

        // 3. Jika tanggal lahir tidak ada
        if (! $hasSpouse) {
            if ($empGender === 'Laki-Laki' && $famGender === 'Perempuan') {
                return 'Istri';
            }
            if ($empGender === 'Perempuan' && $famGender === 'Laki-Laki') {
                return 'Suami';
            }
        }

        return 'Anak';
    }

    /**
     * Pemetaan jenjang pendidikan terakhir.
     */
    protected function mapPendidikan(?string $val): ?string
    {
        if ($val === null || $val === '') {
            return null;
        }

        $v = strtoupper(trim($val));

        return match ($v) {
            'SD', 'SEKOLAH DASAR' => 'SD',
            'SMP', 'SLTP', 'MTS' => 'SLTP',
            'SMA', 'SMK', 'SLTA', 'MAN', 'STM', 'SMEA' => 'SLTA',
            'D1' => 'D1',
            'D2' => 'D2',
            'D3', 'DIPLOMA' => 'D3',
            'D4' => 'D4',
            'S1', 'SARJANA' => 'S1',
            'S2', 'MAGISTER' => 'S2',
            'S3', 'DOKTOR' => 'S3',
            default => $v,
        };
    }

    /**
     * Cari atau buat Unit/Kebun.
     */
    protected function findOrCreateUnit(?string $kode, ?string $nama): ?int
    {
        $kode = $this->cleanString($kode);
        $nama = $this->cleanString($nama);

        if (empty($kode) && empty($nama)) {
            return null;
        }

        $cacheKey = ($kode ?? '').'|'.($nama ?? '');
        if (isset($this->unitCache[$cacheKey])) {
            return $this->unitCache[$cacheKey];
        }

        if ($kode) {
            $unit = Unit::firstOrCreate(
                ['kode_unit' => strtoupper(substr($kode, 0, 20))],
                [
                    'nama_unit' => $nama ?: $kode,
                    'nama_singkat' => strtoupper(substr($kode, 0, 20)),
                ]
            );
        } else {
            $unit = Unit::firstOrCreate(
                ['nama_unit' => $nama],
                [
                    'kode_unit' => strtoupper(substr(preg_replace('/[^a-zA-Z0-9]/', '', $nama), 0, 20)),
                    'nama_singkat' => strtoupper(substr($nama, 0, 20)),
                ]
            );
        }

        return $this->unitCache[$cacheKey] = $unit->id;
    }

    /**
     * Cari atau buat Bagian.
     * Mengarahkan variasi Afdeling Inti ke Afdeling reguler.
     */
    protected function findOrCreateBagian(?string $nama): ?int
    {
        $nama = $this->cleanString($nama);
        if (empty($nama)) {
            return null;
        }

        $key = strtoupper($nama);

        // Petakan Afdeling Inti I-IV ke Afdeling I-IV reguler
        if (preg_match('/\b(AFDELING\s+)?INTI\s+(IV|4)\b/i', $key)) {
            $key = 'AFDELING IV';
        } elseif (preg_match('/\b(AFDELING\s+)?INTI\s+(III|3)\b/i', $key)) {
            $key = 'AFDELING III';
        } elseif (preg_match('/\b(AFDELING\s+)?INTI\s+(II|2)\b/i', $key)) {
            $key = 'AFDELING II';
        } elseif (preg_match('/\b(AFDELING\s+)?INTI\s+(I|1)\b/i', $key)) {
            $key = 'AFDELING I';
        } elseif (str_contains($key, 'PETUGAS GLOBAL TELING AFD I') || str_contains($key, 'GLOBAL TELING AFD I')) {
            $key = 'AFDELING I';
        }

        if (isset($this->bagianCache[$key])) {
            return $this->bagianCache[$key];
        }

        $bagian = Bagian::firstOrCreate(['nama_bagian' => $key]);

        return $this->bagianCache[$key] = $bagian->id;
    }

    /**
     * Cari atau buat Jabatan.
     */
    protected function findOrCreateJabatan(?string $nama): ?int
    {
        $nama = $this->cleanString($nama);
        if (empty($nama)) {
            return null;
        }

        $key = strtoupper($nama);
        if (isset($this->jabatanCache[$key])) {
            return $this->jabatanCache[$key];
        }

        $jabatan = Jabatan::firstOrCreate(['nama_jabatan' => $key]);

        return $this->jabatanCache[$key] = $jabatan->id;
    }

    /**
     * Bersihkan string.
     */
    protected function cleanString($val): ?string
    {
        if ($val === null) {
            return null;
        }

        $clean = trim((string) $val);

        return $clean === '' ? null : $clean;
    }
}
