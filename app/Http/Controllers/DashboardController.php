<?php

namespace App\Http\Controllers;

use App\Models\Bagian;
use App\Models\Employee;
use App\Models\EmployeeFamily;
use App\Models\Jabatan;

class DashboardController extends Controller
{
    public function index()
    {
        $karyawanAktif = Employee::active();

        // Statistik utama
        $totalKaryawan = (clone $karyawanAktif)->count();
        $totalBatih = EmployeeFamily::count();
        $totalBagian = Bagian::count();
        $totalJabatan = Jabatan::count();

        // Rekap status karyawan
        $rekapStatus = (clone $karyawanAktif)
            ->selectRaw('status_karyawan, COUNT(*) as total')
            ->groupBy('status_karyawan')
            ->orderByDesc('total')
            ->get();

        // Rekap pendidikan terakhir
        $rekapPendidikan = (clone $karyawanAktif)
            ->selectRaw("
                COALESCE(NULLIF(pendidikan_terakhir, ''), 'Belum Diisi')
                as pendidikan_terakhir,
                COUNT(*) as total
            ")
            ->groupBy('pendidikan_terakhir')
            ->orderByDesc('total')
            ->get();

        // Distribusi usia berdasarkan tanggal lahir
        $sekarang = now()->startOfDay();

        $hitungUsia = function ($usiaMinimum, $usiaMaksimum = null) use ($sekarang) {
            $query = Employee::active()
                ->whereNotNull('tanggal_lahir')
                ->whereDate(
                    'tanggal_lahir',
                    '<=',
                    $sekarang->copy()->subYears($usiaMinimum)
                );

            if ($usiaMaksimum !== null) {
                $query->whereDate(
                    'tanggal_lahir',
                    '>',
                    $sekarang->copy()->subYears($usiaMaksimum + 1)
                );
            }

            return $query->count();
        };

        $rekapUsia = [
            'Di bawah 22 tahun' => Employee::active()
                ->whereNotNull('tanggal_lahir')
                ->whereDate(
                    'tanggal_lahir',
                    '>',
                    $sekarang->copy()->subYears(22)
                )
                ->count(),

            '22–30 tahun' => $hitungUsia(22, 30),
            '31–40 tahun' => $hitungUsia(31, 40),
            '41–50 tahun' => $hitungUsia(41, 50),
            '51 tahun ke atas' => $hitungUsia(51),
        ];

        // Lima bagian dengan karyawan terbanyak
        $rekapBagian = Employee::active()
            ->whereNotNull('bagian_id')
            ->selectRaw('bagian_id, COUNT(*) as total')
            ->with('bagian')
            ->groupBy('bagian_id')
            ->orderByDesc('total')
            ->limit(5)
            ->get();

        // Lima karyawan terbaru
        $karyawanTerbaru = Employee::active()
            ->with(['bagian', 'jabatan'])
            ->latest()
            ->limit(5)
            ->get();

        return view('dashboard', compact(
            'totalKaryawan',
            'totalBatih',
            'totalBagian',
            'totalJabatan',
            'rekapStatus',
            'rekapPendidikan',
            'rekapUsia',
            'rekapBagian',
            'karyawanTerbaru'
        ));
    }
}
