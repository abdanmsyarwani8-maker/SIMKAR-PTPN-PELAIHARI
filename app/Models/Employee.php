<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Builder;

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
        'sumber_lembar',
        'sumber_file',
        'is_active',
        'catatan',
    ];

    protected $casts = [
        'tanggal_lahir'       => 'date',
        'tanggal_mulai_kerja' => 'date',
        'is_active'           => 'boolean',
    ];

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

    // ─── Accessor ─────────────────────────────────────────────

    public function getUmurAttribute(): ?int
    {
        if (!$this->tanggal_lahir) return null;
        return Carbon::parse($this->tanggal_lahir)->age;
    }

    public function getRentangUsiaAttribute(): string
    {
        $umur = $this->umur;
        if ($umur === null) return 'Tidak Diketahui';
        if ($umur < 25)  return '< 25 Tahun';
        if ($umur < 35)  return '25–34 Tahun';
        if ($umur < 45)  return '35–44 Tahun';
        if ($umur < 55)  return '45–54 Tahun';
        return '≥ 55 Tahun';
    }

    public function getMasaKerjaAttribute(): ?string
    {
        if (!$this->tanggal_mulai_kerja) return null;
        $years = Carbon::parse($this->tanggal_mulai_kerja)->diffInYears(now());
        return $years . ' Tahun';
    }

    public function getStatusKaryawanLabelAttribute(): string
    {
        return $this->status_karyawan ?? 'Belum Diisi';
    }

    public function getPendidikanLabelAttribute(): string
    {
        $map = [
            'SD'   => 'SD', 'SMP' => 'SMP/SLTP', 'SLTP' => 'SMP/SLTP',
            'SMA'  => 'SMA/SLTA', 'SLTA' => 'SMA/SLTA',
            'D1'   => 'D1', 'D2' => 'D2', 'D3' => 'D3', 'D4' => 'D4',
            'S1'   => 'S1/Sarjana', 'S2' => 'S2/Magister', 'S3' => 'S3/Doktor',
        ];
        return $map[$this->pendidikan_terakhir] ?? ($this->pendidikan_terakhir ?? 'Belum Diisi');
    }

    // ─── Scopes ───────────────────────────────────────────────

    public function scopeSearch(Builder $query, ?string $term): Builder
    {
        if (!$term) return $query;
        return $query->where(function ($q) use ($term) {
            $q->where('nama', 'like', "%{$term}%")
              ->orWhere('personnel_number', 'like', "%{$term}%")
              ->orWhere('nik_lokal', 'like', "%{$term}%")
              ->orWhere('pers_no', 'like', "%{$term}%");
        });
    }

    public function scopeFilterUnit(Builder $query, ?array $unitIds): Builder
    {
        if (empty($unitIds)) return $query;
        return $query->whereIn('unit_id', $unitIds);
    }

    public function scopeFilterBagian(Builder $query, ?array $bagianIds): Builder
    {
        if (empty($bagianIds)) return $query;
        return $query->whereIn('bagian_id', $bagianIds);
    }

    public function scopeFilterJabatan(Builder $query, ?array $jabatanIds): Builder
    {
        if (empty($jabatanIds)) return $query;
        return $query->whereIn('jabatan_id', $jabatanIds);
    }

    public function scopeFilterStatus(Builder $query, ?array $statuses): Builder
    {
        if (empty($statuses)) return $query;
        return $query->whereIn('status_karyawan', $statuses);
    }

    public function scopeFilterJenisKelamin(Builder $query, ?array $values): Builder
    {
        if (empty($values)) return $query;
        return $query->whereIn('jenis_kelamin', $values);
    }

    public function scopeFilterAgama(Builder $query, ?array $values): Builder
    {
        if (empty($values)) return $query;
        return $query->whereIn('agama', $values);
    }

    public function scopeFilterRentangUsia(Builder $query, ?array $ranges): Builder
    {
        if (empty($ranges)) return $query;

        $now = now();
        return $query->where(function ($q) use ($ranges, $now) {
            foreach ($ranges as $range) {
                $q->orWhere(function ($sub) use ($range, $now) {
                    match ($range) {
                        '< 25 Tahun'  => $sub->whereDate('tanggal_lahir', '>', $now->copy()->subYears(25)),
                        '25–34 Tahun' => $sub->whereDate('tanggal_lahir', '<=', $now->copy()->subYears(25))
                                             ->whereDate('tanggal_lahir', '>', $now->copy()->subYears(35)),
                        '35–44 Tahun' => $sub->whereDate('tanggal_lahir', '<=', $now->copy()->subYears(35))
                                             ->whereDate('tanggal_lahir', '>', $now->copy()->subYears(45)),
                        '45–54 Tahun' => $sub->whereDate('tanggal_lahir', '<=', $now->copy()->subYears(45))
                                             ->whereDate('tanggal_lahir', '>', $now->copy()->subYears(55)),
                        '≥ 55 Tahun'  => $sub->whereDate('tanggal_lahir', '<=', $now->copy()->subYears(55)),
                        default       => null,
                    };
                });
            }
        });
    }

    public function scopeFilterSumberLembar(Builder $query, ?array $lembar): Builder
    {
        if (empty($lembar)) return $query;
        return $query->whereIn('sumber_lembar', $lembar);
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    // ─── Static helpers ───────────────────────────────────────

    public static function rentangUsiaOptions(): array
    {
        return ['< 25 Tahun', '25–34 Tahun', '35–44 Tahun', '45–54 Tahun', '≥ 55 Tahun'];
    }
}
