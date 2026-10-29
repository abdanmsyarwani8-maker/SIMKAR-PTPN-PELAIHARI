<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MonthlySnapshot extends Model
{
    protected $fillable = [
        'bulan',
        'tahun',
        'nama_file',
        'path',
        'jumlah_karyawan',
        'user_id',
        'catatan',
    ];

    protected $casts = [
        'bulan' => 'integer',
        'tahun' => 'integer',
        'jumlah_karyawan' => 'integer',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Label bulan dalam bahasa Indonesia.
     */
    public function getBulanLabelAttribute(): string
    {
        $bulanIndo = [
            1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
            5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
            9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember',
        ];

        return $bulanIndo[$this->bulan] ?? '-';
    }

    /**
     * Label periode lengkap (e.g. "Oktober 2026").
     */
    public function getPeriodeLabelAttribute(): string
    {
        return $this->bulan_label.' '.$this->tahun;
    }
}
