<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;

class Employee extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'pers_no',
        'personnel_number',
        'nik_lokal',
        'nama',
        'tanggal_lahir',
        'tanggal_mulai_kerja',
        'tmt_cuti',
        'jenis_kelamin',
        'agama',
        'pendidikan_terakhir',
        'unit_id',
        'bagian_id',
        'jabatan_id',
        'status_karyawan',
        'golongan',
        'grup_golongan',
        'grup_jabatan',
        'no_pkwt',
        'grade',
        'suku',
        'alamat',
        'domisili',
        'sumber_lembar',
        'sumber_file',
        'is_active',
        'catatan',
    ];

    protected $casts = [
        'tanggal_lahir' => 'date',
        'tanggal_mulai_kerja' => 'date',
        'tmt_cuti' => 'date',
        'is_active' => 'boolean',
    ];

    protected static function booted(): void
    {
        static::saving(function (Employee $employee) {
            if (str_contains(strtoupper($employee->status_karyawan ?? ''), 'PKWT')) {
                $employee->grup_golongan = null;
                $employee->grup_jabatan = null;
            }
            if (str_contains(strtoupper($employee->status_karyawan ?? ''), 'BHL')) {
                $employee->grup_golongan = null;
                $employee->grup_jabatan = null;
                $employee->no_pkwt = null;
                $employee->personnel_number = null;
                $employee->pers_no = null;
            }
            if (empty($employee->domisili) && ! empty($employee->alamat)) {
                $employee->domisili = static::determineDomisili($employee->alamat);
            }
        });
    }

    // ─── Relasi ───────────────────────────────────────────────

    public function unit(): BelongsTo
    {
        return $this->belongsTo(Unit::class);
    }

    public function bagian(): BelongsTo
    {
        return $this->belongsTo(Bagian::class);
    }

    public function jabatan(): BelongsTo
    {
        return $this->belongsTo(Jabatan::class);
    }

    public function families(): HasMany
    {
        return $this->hasMany(EmployeeFamily::class, 'employee_id');
    }

    public function batihs(): HasMany
    {
        return $this->families();
    }

    public function batih(): HasMany
    {
        return $this->families();
    }

    // ─── Accessor ─────────────────────────────────────────────

    public function getUmurAttribute(): ?int
    {
        if (! $this->tanggal_lahir) {
            return null;
        }

        return Carbon::parse($this->tanggal_lahir)->age;
    }

    public function getTanggalPensiunAttribute(): ?Carbon
    {
        if (! $this->tanggal_lahir || str_contains(strtoupper($this->status_karyawan ?? ''), 'BHL')) {
            return null;
        }

        return Carbon::parse($this->tanggal_lahir)->addYears(55);
    }

    public function getSisaWaktuPensiunAttribute(): ?string
    {
        if (! $this->tanggal_lahir || str_contains(strtoupper($this->status_karyawan ?? ''), 'BHL')) {
            return null;
        }

        $now = now()->startOfDay();
        $pensiun = $this->tanggal_pensiun?->copy()->startOfDay();

        if (! $pensiun || $now->greaterThanOrEqualTo($pensiun)) {
            return 'Sudah Pensiun';
        }

        $diff = $now->diff($pensiun);
        $years = (int) $diff->y;
        $months = (int) $diff->m;
        $days = (int) $diff->d;

        if ($years > 0 && $months > 0) {
            return "{$years} tahun, {$months} bulan lagi";
        } elseif ($years > 0) {
            return "{$years} tahun lagi";
        } elseif ($months > 0) {
            return "{$months} bulan lagi";
        } elseif ($days > 0) {
            return "{$days} hari lagi";
        }

        return 'Hari ini pensiun';
    }

    public function getRentangUsiaAttribute(): string
    {
        $umur = $this->umur;
        if ($umur === null) {
            return 'Tidak Diketahui';
        }
        if ($umur < 25) {
            return '< 25 Tahun';
        }
        if ($umur < 35) {
            return '25–34 Tahun';
        }
        if ($umur < 45) {
            return '35–44 Tahun';
        }
        if ($umur < 55) {
            return '45–54 Tahun';
        }

        return '≥ 55 Tahun';
    }

    public function getMasaKerjaAttribute(): ?string
    {
        if (! $this->tanggal_mulai_kerja) {
            return null;
        }

        $mulai = Carbon::parse($this->tanggal_mulai_kerja)->startOfDay();
        $now = now()->startOfDay();

        if ($mulai->isFuture()) {
            return 'Belum mulai bekerja';
        }

        $diff = $mulai->diff($now);
        $years = (int) $diff->y;
        $months = (int) $diff->m;
        $days = (int) $diff->d;

        $parts = [];
        if ($years > 0) {
            $parts[] = "{$years} tahun";
        }
        if ($months > 0) {
            $parts[] = "{$months} bulan";
        }
        if ($days > 0 || empty($parts)) {
            $parts[] = "{$days} hari";
        }

        return implode(', ', $parts);
    }

    public function getStatusKaryawanLabelAttribute(): string
    {
        return $this->status_karyawan ?? 'Belum Diisi';
    }

    public function getPendidikanLabelAttribute(): string
    {
        $map = [
            'SD' => 'SD', 'SMP' => 'SMP/SLTP', 'SLTP' => 'SMP/SLTP',
            'SMA' => 'SMA/SLTA', 'SLTA' => 'SMA/SLTA',
            'D1' => 'D1', 'D2' => 'D2', 'D3' => 'D3', 'D4' => 'D4',
            'S1' => 'S1/Sarjana', 'S2' => 'S2/Magister', 'S3' => 'S3/Doktor',
        ];

        return $map[$this->pendidikan_terakhir] ?? ($this->pendidikan_terakhir ?? 'Belum Diisi');
    }

    public function getPenghargaanMasaKerjaAttribute(): ?string
    {
        if (! $this->tanggal_mulai_kerja) {
            return null;
        }

        $years = (int) Carbon::parse($this->tanggal_mulai_kerja)->diffInYears(now());

        if ($years >= 35) {
            return '35 Tahun';
        }
        if ($years >= 30) {
            return '30 Tahun';
        }
        if ($years >= 25) {
            return '25 Tahun';
        }

        return null;
    }

    // ─── Cuti Tahunan & Cuti Panjang ─────────────────────────

    /**
     * Tanggal acuan perhitungan cuti:
     * Mengutamakan tanggal_mulai_kerja (pengangkatan kerja).
     * Jika tidak ada, gunakan tmt_cuti bila tersedia.
     */
    public function getTanggalAcuanCutiAttribute(): ?Carbon
    {
        if ($this->tmt_cuti) {
            return Carbon::parse($this->tmt_cuti);
        }

        if ($this->tanggal_mulai_kerja) {
            return Carbon::parse($this->tanggal_mulai_kerja);
        }

        return null;
    }

    /**
     * Bulan jatuh tempo cuti dalam bahasa Indonesia.
     */
    public function getBulanJatuhTempoCutiAttribute(): string
    {
        $acuan = $this->tanggal_acuan_cuti;
        if (! $acuan) {
            return 'Perlu Verifikasi';
        }

        $bulanIndo = [
            1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
            5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
            9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember',
        ];

        return $bulanIndo[(int) $acuan->month] ?? 'Perlu Verifikasi';
    }

    /**
     * Perhitungan dinamis hak cuti untuk tahun tertentu.
     * Rumus Cuti Panjang: Tahun Pengangkatan + (6 * n), dengan n >= 1.
     *
     * @return array{
     *   tahun: int,
     *   jenis_cuti: string,
     *   status_label: string,
     *   is_cuti_panjang: bool,
     *   is_valid: bool,
     *   bulan_jatuh_tempo: string,
     *   tanggal_acuan: ?Carbon,
     *   tahun_cuti_panjang_berikutnya: string|int,
     *   siklus_ke: ?int,
     *   keterangan: string
     * }
     */
    public function getCutiStatusForYear(?int $year = null): array
    {
        $tahun = $year ?? (int) now()->year;
        $acuan = $this->tanggal_acuan_cuti;

        if (! $acuan) {
            return [
                'tahun' => $tahun,
                'jenis_cuti' => 'Perlu Verifikasi',
                'status_label' => 'Perlu Verifikasi',
                'is_cuti_panjang' => false,
                'is_valid' => false,
                'bulan_jatuh_tempo' => 'Perlu Verifikasi',
                'tanggal_acuan' => null,
                'tahun_cuti_panjang_berikutnya' => 'Perlu Verifikasi',
                'siklus_ke' => null,
                'keterangan' => 'Tanggal pengangkatan kerja belum tersedia',
            ];
        }

        $tahunPengangkatan = (int) $acuan->year;
        $selisihTahun = $tahun - $tahunPengangkatan;

        // Tahun cuti panjang berikutnya
        if ($tahun < $tahunPengangkatan) {
            $tahunBerikutnya = $tahunPengangkatan + 6;
        } else {
            $n = (int) floor($selisihTahun / 6) + 1;
            $tahunBerikutnya = $tahunPengangkatan + (6 * $n);
        }

        if ($selisihTahun < 1) {
            return [
                'tahun' => $tahun,
                'jenis_cuti' => 'Belum Memenuhi Syarat',
                'status_label' => 'Belum Memenuhi Syarat',
                'is_cuti_panjang' => false,
                'is_valid' => true,
                'bulan_jatuh_tempo' => $this->bulan_jatuh_tempo_cuti,
                'tanggal_acuan' => $acuan,
                'tahun_cuti_panjang_berikutnya' => $tahunPengangkatan + 6,
                'siklus_ke' => null,
                'keterangan' => 'Masa kerja belum mencapai 1 tahun pada tahun '.$tahun,
            ];
        }

        $isCutiPanjang = ($selisihTahun % 6 === 0);
        $siklusKe = $isCutiPanjang ? (int) ($selisihTahun / 6) : null;

        return [
            'tahun' => $tahun,
            'jenis_cuti' => $isCutiPanjang ? 'Cuti Panjang' : 'Cuti Tahunan',
            'status_label' => $isCutiPanjang ? 'Jatuh Tempo Cuti Panjang' : 'Cuti Tahunan',
            'is_cuti_panjang' => $isCutiPanjang,
            'is_valid' => true,
            'bulan_jatuh_tempo' => $this->bulan_jatuh_tempo_cuti,
            'tanggal_acuan' => $acuan,
            'tahun_cuti_panjang_berikutnya' => $tahunBerikutnya,
            'siklus_ke' => $siklusKe,
            'keterangan' => $isCutiPanjang
                ? 'Jatuh tempo hak Cuti Panjang ke-'.$siklusKe.' pada tahun '.$tahun
                : 'Hak Cuti Tahunan berulang pada bulan '.$this->bulan_jatuh_tempo_cuti.' '.$tahun,
        ];
    }

    public function getTahunCutiPanjangBerikutnyaAttribute(): string|int
    {
        return $this->getCutiStatusForYear()['tahun_cuti_panjang_berikutnya'];
    }

    // ─── Scopes ───────────────────────────────────────────────

    public function scopeSearch(Builder $query, ?string $term): Builder
    {
        if (! $term) {
            return $query;
        }

        return $query->where(function ($q) use ($term) {
            $q->where('nama', 'like', "%{$term}%")
                ->orWhere('personnel_number', 'like', "%{$term}%")
                ->orWhere('nik_lokal', 'like', "%{$term}%")
                ->orWhere('pers_no', 'like', "%{$term}%");
        });
    }

    public function scopeFilterUnit(Builder $query, ?array $unitIds): Builder
    {
        if (empty($unitIds)) {
            return $query;
        }

        return $query->whereIn('unit_id', $unitIds);
    }

    public function scopeFilterBagian(Builder $query, ?array $bagianIds): Builder
    {
        if (empty($bagianIds)) {
            return $query;
        }

        return $query->whereIn('bagian_id', $bagianIds);
    }

    public function scopeFilterJabatan(Builder $query, ?array $jabatanIds): Builder
    {
        if (empty($jabatanIds)) {
            return $query;
        }

        return $query->whereIn('jabatan_id', $jabatanIds);
    }

    public function scopeFilterStatus(Builder $query, ?array $statuses): Builder
    {
        if (empty($statuses)) {
            return $query;
        }

        return $query->whereIn('status_karyawan', $statuses);
    }

    public function scopeFilterJenisKelamin(Builder $query, ?array $values): Builder
    {
        if (empty($values)) {
            return $query;
        }

        return $query->whereIn('jenis_kelamin', $values);
    }

    public function scopeFilterAgama(Builder $query, ?array $values): Builder
    {
        if (empty($values)) {
            return $query;
        }

        return $query->whereIn('agama', $values);
    }

    public function scopeFilterBulanCuti(Builder $query, ?array $months): Builder
    {
        if (empty($months)) {
            return $query;
        }

        $driver = DB::getDriverName();
        if ($driver === 'sqlite') {
            $monthExpr = "CAST(strftime('%m', COALESCE(tmt_cuti, tanggal_mulai_kerja)) AS INTEGER)";
        } else {
            $monthExpr = 'MONTH(COALESCE(tmt_cuti, tanggal_mulai_kerja))';
        }

        return $query->whereNotNull(DB::raw('COALESCE(tmt_cuti, tanggal_mulai_kerja)'))
            ->whereIn(DB::raw($monthExpr), $months);
    }

    public function scopeFilterJenisCuti(Builder $query, ?array $jenisCuti, int $year): Builder
    {
        if (empty($jenisCuti)) {
            return $query;
        }

        $driver = DB::getDriverName();
        if ($driver === 'sqlite') {
            $diffExpr = "($year - CAST(strftime('%Y', COALESCE(tmt_cuti, tanggal_mulai_kerja)) AS INTEGER))";
            $modZero = "$diffExpr > 0 AND $diffExpr % 6 = 0";
            $modNotZero = "$diffExpr > 0 AND $diffExpr % 6 != 0";
        } else {
            $diffExpr = "($year - YEAR(COALESCE(tmt_cuti, tanggal_mulai_kerja)))";
            $modZero = "$diffExpr > 0 AND MOD($diffExpr, 6) = 0";
            $modNotZero = "$diffExpr > 0 AND MOD($diffExpr, 6) != 0";
        }

        return $query->where(function ($q) use ($jenisCuti, $modZero, $modNotZero) {
            foreach ($jenisCuti as $jenis) {
                if ($jenis === 'Cuti Panjang') {
                    $q->orWhere(function ($sub) use ($modZero) {
                        $sub->whereNotNull(DB::raw('COALESCE(tmt_cuti, tanggal_mulai_kerja)'))
                            ->whereRaw($modZero);
                    });
                } elseif ($jenis === 'Cuti Tahunan') {
                    $q->orWhere(function ($sub) use ($modNotZero) {
                        $sub->whereNotNull(DB::raw('COALESCE(tmt_cuti, tanggal_mulai_kerja)'))
                            ->whereRaw($modNotZero);
                    });
                } elseif ($jenis === 'Perlu Verifikasi') {
                    $q->orWhere(function ($sub) {
                        $sub->whereNull('tmt_cuti')->whereNull('tanggal_mulai_kerja');
                    });
                }
            }
        });
    }

    public function scopeFilterPendidikan(Builder $query, ?array $values): Builder
    {
        if (empty($values)) {
            return $query;
        }

        $expanded = [];
        foreach ($values as $p) {
            $expanded[] = $p;
            if ($p === 'SLTP' || $p === 'SMP') {
                $expanded[] = 'SLTP';
                $expanded[] = 'SMP';
            } elseif ($p === 'SLTA' || $p === 'SMA' || $p === 'SMK') {
                $expanded[] = 'SLTA';
                $expanded[] = 'SMA';
                $expanded[] = 'SMK';
            }
        }

        return $query->whereIn('pendidikan_terakhir', array_unique($expanded));
    }

    public function scopeFilterPensiun(Builder $query, ?array $options): Builder
    {
        if (empty($options)) {
            return $query;
        }

        $now = now()->startOfDay();

        return $query->whereNotNull('tanggal_lahir')
            ->where(function ($q) {
                $q->whereNull('status_karyawan')
                    ->orWhere('status_karyawan', 'not like', '%BHL%');
            })
            ->where(function ($q) use ($options, $now) {
                foreach ($options as $opt) {
                    $q->orWhere(function ($sub) use ($opt, $now) {
                        if ($opt === '≤ 1 Tahun' || $opt === '<= 1 Tahun' || $opt === '1_tahun') {
                            // Belum pensiun tapi pensiun dalam <= 1 tahun lagi (usia 54 - 55)
                            $sub->whereDate('tanggal_lahir', '>=', $now->copy()->subYears(55))
                                ->whereDate('tanggal_lahir', '<=', $now->copy()->subYears(54));
                        } elseif ($opt === '1–2 Tahun' || $opt === '1-2 Tahun' || $opt === '2_tahun') {
                            // Pensiun dalam 1-2 tahun lagi (usia 53 - 54)
                            $sub->whereDate('tanggal_lahir', '>', $now->copy()->subYears(54))
                                ->whereDate('tanggal_lahir', '<=', $now->copy()->subYears(53));
                        } elseif ($opt === 'Sudah Pensiun' || $opt === 'sudah') {
                            // Sudah mencapai atau melewati usia 55 tahun
                            $sub->whereDate('tanggal_lahir', '<', $now->copy()->subYears(55));
                        }
                    });
                }
            });
    }

    public function scopeFilterPenghargaan(Builder $query, ?array $options): Builder
    {
        if (empty($options)) {
            return $query;
        }

        $now = now()->startOfDay();

        return $query->whereNotNull('tanggal_mulai_kerja')->where(function ($q) use ($options, $now) {
            foreach ($options as $opt) {
                $q->orWhere(function ($sub) use ($opt, $now) {
                    $optStr = (string) $opt;
                    if (str_starts_with($optStr, '25')) {
                        // 25 - 29 Tahun
                        $sub->whereDate('tanggal_mulai_kerja', '<=', $now->copy()->subYears(25))
                            ->whereDate('tanggal_mulai_kerja', '>', $now->copy()->subYears(30));
                    } elseif (str_starts_with($optStr, '30')) {
                        // 30 - 34 Tahun
                        $sub->whereDate('tanggal_mulai_kerja', '<=', $now->copy()->subYears(30))
                            ->whereDate('tanggal_mulai_kerja', '>', $now->copy()->subYears(35));
                    } elseif (str_starts_with($optStr, '35')) {
                        // 35 Tahun ke atas
                        $sub->whereDate('tanggal_mulai_kerja', '<=', $now->copy()->subYears(35));
                    }
                });
            }
        });
    }

    public function scopeFilterRentangUsia(Builder $query, ?array $ranges): Builder
    {
        if (empty($ranges)) {
            return $query;
        }

        $now = now();

        return $query->where(function ($q) use ($ranges, $now) {
            foreach ($ranges as $range) {
                $q->orWhere(function ($sub) use ($range, $now) {
                    match ($range) {
                        '< 25 Tahun' => $sub->whereDate('tanggal_lahir', '>', $now->copy()->subYears(25)),
                        '25–34 Tahun' => $sub->whereDate('tanggal_lahir', '<=', $now->copy()->subYears(25))
                            ->whereDate('tanggal_lahir', '>', $now->copy()->subYears(35)),
                        '35–44 Tahun' => $sub->whereDate('tanggal_lahir', '<=', $now->copy()->subYears(35))
                            ->whereDate('tanggal_lahir', '>', $now->copy()->subYears(45)),
                        '45–54 Tahun' => $sub->whereDate('tanggal_lahir', '<=', $now->copy()->subYears(45))
                            ->whereDate('tanggal_lahir', '>', $now->copy()->subYears(55)),
                        '≥ 55 Tahun' => $sub->whereDate('tanggal_lahir', '<=', $now->copy()->subYears(55)),
                        default => null,
                    };
                });
            }
        });
    }

    public function scopeFilterSumberLembar(Builder $query, ?array $lembar): Builder
    {
        if (empty($lembar)) {
            return $query;
        }

        return $query->whereIn('sumber_lembar', $lembar);
    }

    /**
     * Filter berdasarkan lokasi alamat (Lokal = Kalimantan Selatan, Non-Lokal = di luar).
     *
     * @param  array<string>|null  $lokasi
     */
    public function getDomisiliAttribute(?string $value): ?string
    {
        if (! empty($value)) {
            return $value;
        }

        if (! empty($this->alamat)) {
            return static::determineDomisili($this->alamat);
        }

        return null;
    }

    public static function determineDomisili(?string $alamat): ?string
    {
        if (empty($alamat)) {
            return null;
        }

        foreach (static::kalselKeywords() as $kw) {
            if (stripos($alamat, $kw) !== false) {
                return 'Lokal';
            }
        }

        return 'Non-Lokal';
    }

    public function scopeFilterLokasi(Builder $query, ?array $lokasi): Builder
    {
        if (empty($lokasi)) {
            return $query;
        }

        $wantLokal = in_array('Lokal (Kalimantan Selatan)', $lokasi) || in_array('Lokal', $lokasi);
        $wantNonLokal = in_array('Non-Lokal (Luar Kalimantan Selatan)', $lokasi) || in_array('Non-Lokal', $lokasi);

        if ($wantLokal && $wantNonLokal) {
            return $query; // Keduanya dipilih → tidak perlu filter
        }

        if ($wantLokal) {
            return $query->where(function ($q) {
                $q->where('domisili', 'Lokal')
                    ->orWhere(function ($sub) {
                        $sub->whereNull('domisili');
                        foreach (static::kalselKeywords() as $kw) {
                            $sub->orWhere('alamat', 'like', "%{$kw}%");
                        }
                    });
            });
        }

        if ($wantNonLokal) {
            return $query->where(function ($q) {
                $q->where('domisili', 'Non-Lokal')
                    ->orWhere(function ($sub) {
                        $sub->whereNull('domisili');
                        foreach (static::kalselKeywords() as $kw) {
                            $sub->where('alamat', 'not like', "%{$kw}%");
                        }
                    });
            });
        }

        return $query;
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope karyawan aktif tetap / PKWT (memisahkan / tanpa BHL).
     */
    public function scopeKaryawanAktif(Builder $query): Builder
    {
        return $query->where('is_active', true)
            ->where(function (Builder $q) {
                $q->whereNull('status_karyawan')
                    ->orWhere('status_karyawan', 'not like', '%BHL%');
            });
    }

    /**
     * Scope khusus Buruh Harian Lepas (BHL).
     */
    public function scopeBhl(Builder $query): Builder
    {
        return $query->where('is_active', true)
            ->where('status_karyawan', 'like', '%BHL%');
    }

    // ─── Static helpers ───────────────────────────────────────

    public static function rentangUsiaOptions(): array
    {
        return ['< 25 Tahun', '25–34 Tahun', '35–44 Tahun', '45–54 Tahun', '≥ 55 Tahun'];
    }

    public static function masaPensiunOptions(): array
    {
        return ['≤ 1 Tahun', '1–2 Tahun', 'Sudah Pensiun'];
    }

    public static function penghargaanOptions(): array
    {
        return ['25 Tahun', '30 Tahun', '35 Tahun'];
    }

    /**
     * Kata kunci nama kota/kabupaten/kecamatan di Kalimantan Selatan
     * yang digunakan untuk mendeteksi alamat lokal.
     *
     * @return array<string>
     */
    public static function kalselKeywords(): array
    {
        return [
            // Kota & Kabupaten
            'Banjarmasin', 'Banjarbaru', 'Banjar', 'Martapura',
            'Pelaihari', 'Tala', 'Tanah Laut',
            'Kotabaru', 'Batulicin', 'Tanah Bumbu',
            'Kandangan', 'Hulu Sungai Selatan',
            'Amuntai', 'Hulu Sungai Utara',
            'Barabai', 'Hulu Sungai Tengah',
            'Rantau', 'Tapin',
            'Marabahan', 'Barito Kuala', 'Batola',
            'Pelaihari', 'Jorong', 'Takisung', 'Bajuin',
            'Pagatan', 'Satui', 'Angsana',
            'Binuang', 'Tambang Ulang',
            // Kecamatan umum Kalsel
            'Bati-Bati', 'Kurau', 'Batu Ampar',
            'Sungai Tabuk', 'Kertak Hanyar', 'Gambut',
            'Landasan Ulin', 'Liang Anggang',
            'Astambul', 'Karang Intan',
            'Lok Baintan',
            // Singkatan umum
            'Kalsel', 'Kalimantan Sel',
        ];
    }
}
