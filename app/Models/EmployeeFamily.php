<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EmployeeFamily extends Model
{
    protected $table = 'employee_families';

    protected $fillable = [
        'employee_id',
        'nama',
        'hubungan',
        'jenis_kelamin',
        'tanggal_lahir',
        'tempat_lahir',
        'keterangan',
    ];

    protected $casts = [
        'tanggal_lahir' => 'date',
    ];

    /**
     * Relasi ke karyawan (Employee)
     */
    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'employee_id');
    }

    /**
     * Hitung umur anggota keluarga
     */
    public function getUmurAttribute(): ?int
    {
        if (! $this->tanggal_lahir) {
            return null;
        }

        return Carbon::parse($this->tanggal_lahir)->age;
    }
}
