<?php

use App\Models\EmployeeFamily;
use Carbon\Carbon;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $families = EmployeeFamily::with('employee')
            ->where(function ($query) {
                $query->whereNull('hubungan')
                    ->orWhere('hubungan', '')
                    ->orWhere('hubungan', '-');
            })
            ->get();

        foreach ($families as $fam) {
            $employee = $fam->employee;
            if (! $employee) {
                continue;
            }

            $empGender = $employee->jenis_kelamin;
            $famGender = $fam->jenis_kelamin;
            $empBirth = $employee->tanggal_lahir ? Carbon::parse($employee->tanggal_lahir) : null;
            $famBirth = $fam->tanggal_lahir ? Carbon::parse($fam->tanggal_lahir) : null;
            $famAge = $famBirth ? $famBirth->diffInYears(now()) : null;

            // Cek apakah karyawan sudah memiliki pasangan yang tercatat
            $hasSpouse = EmployeeFamily::where('employee_id', $employee->id)
                ->where('id', '!=', $fam->id)
                ->whereIn('hubungan', ['Istri', 'Suami'])
                ->exists();

            $rel = 'Anak';

            if ($empBirth && $famBirth) {
                $ageDiff = $empBirth->diffInYears($famBirth, false);
                if ($ageDiff >= 15) {
                    $rel = 'Anak';
                } elseif ($ageDiff <= -16) {
                    $rel = 'Orang Tua';
                } else {
                    if (! $hasSpouse) {
                        if ($empGender === 'Laki-Laki' && $famGender === 'Perempuan') {
                            $rel = 'Istri';
                        } elseif ($empGender === 'Perempuan' && $famGender === 'Laki-Laki') {
                            $rel = 'Suami';
                        } elseif ($famGender === 'Perempuan') {
                            $rel = 'Istri';
                        } elseif ($famGender === 'Laki-Laki') {
                            $rel = 'Suami';
                        } else {
                            $rel = ($empGender === 'Perempuan') ? 'Suami' : 'Istri';
                        }
                    } else {
                        $rel = 'Anak';
                    }
                }
            } elseif ($famAge !== null) {
                if ($famAge <= 25) {
                    $rel = 'Anak';
                } elseif ($famAge > 65) {
                    $rel = 'Orang Tua';
                } elseif (! $hasSpouse) {
                    if ($empGender === 'Laki-Laki' && $famGender === 'Perempuan') {
                        $rel = 'Istri';
                    } elseif ($empGender === 'Perempuan' && $famGender === 'Laki-Laki') {
                        $rel = 'Suami';
                    } else {
                        $rel = ($empGender === 'Perempuan') ? 'Suami' : 'Istri';
                    }
                } else {
                    $rel = 'Anak';
                }
            } else {
                if (! $hasSpouse) {
                    if ($empGender === 'Laki-Laki' && $famGender === 'Perempuan') {
                        $rel = 'Istri';
                    } elseif ($empGender === 'Perempuan' && $famGender === 'Laki-Laki') {
                        $rel = 'Suami';
                    } else {
                        $rel = 'Anak';
                    }
                } else {
                    $rel = 'Anak';
                }
            }

            $updateData = ['hubungan' => $rel];
            if ($famGender === 'Tidak Diketahui' || empty($famGender)) {
                if ($rel === 'Istri') {
                    $updateData['jenis_kelamin'] = 'Perempuan';
                } elseif ($rel === 'Suami') {
                    $updateData['jenis_kelamin'] = 'Laki-Laki';
                }
            }

            $fam->update($updateData);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // No-op to preserve relationships
    }
};
