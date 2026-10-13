<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use App\Models\Unit;
use App\Models\Bagian;
use App\Models\Jabatan;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index()
    {
        // Statistik utama
        $totalKaryawan   = Employee::active()->count();
        $totalUnit       = Unit::count();
        $totalBagian     = Bagian::count();
        $totalJabatan    = Jabatan::count();

        // Rekap per Status Karyawan
        $rekapStatus = Employee::active()
            ->selectRaw('status_karyawan, COUNT(*) as total')
            ->groupBy('status_karyawan')
            ->orderByDesc('total')
            ->get();

        // Rekap per Jenis Kelamin
        $rekapJenisKelamin = Employee::active()
            ->selectRaw('jenis_kelamin, COUNT(*) as total')
            ->groupBy('jenis_kelamin')
            ->get();

        // Rekap per Agama
        $rekapAgama = Employee::active()
            ->selectRaw('COALESCE(agama, "Belum Diisi") as agama, COUNT(*) as total')
            ->groupBy('agama')
            ->orderByDesc('total')
            ->get();

        // Rekap per Unit (top 10)
        $rekapUnit = Employee::active()
            ->with('unit')
            ->selectRaw('unit_id, COUNT(*) as total')
            ->groupBy('unit_id')
            ->orderByDesc('total')
            ->limit(10)
            ->get();

        // Rekap rentang usia
        $now = now();
        $rekapUsia = [
            '< 25 Tahun'  => Employee::active()->whereDate('tanggal_lahir', '>', $now->copy()->subYears(25))->count(),
            '25–34 Tahun' => Employee::active()->whereDate('tanggal_lahir', '<=', $now->copy()->subYears(25))->whereDate('tanggal_lahir', '>', $now->copy()->subYears(35))->count(),
            '35–44 Tahun' => Employee::active()->whereDate('tanggal_lahir', '<=', $now->copy()->subYears(35))->whereDate('tanggal_lahir', '>', $now->copy()->subYears(45))->count(),
            '45–54 Tahun' => Employee::active()->whereDate('tanggal_lahir', '<=', $now->copy()->subYears(45))->whereDate('tanggal_lahir', '>', $now->copy()->subYears(55))->count(),
            '≥ 55 Tahun'  => Employee::active()->whereDate('tanggal_lahir', '<=', $now->copy()->subYears(55))->count(),
            'Tidak Ada Data' => Employee::active()->whereNull('tanggal_lahir')->count(),
        ];

        // Rekap per Pendidikan
        $rekapPendidikan = Employee::active()
            ->selectRaw('COALESCE(pendidikan_terakhir, "Belum Diisi") as pendidikan_terakhir, COUNT(*) as total')
            ->groupBy('pendidikan_terakhir')
            ->orderByDesc('total')
            ->get();

        // Karyawan terbaru ditambahkan
        $karyawanTerbaru = Employee::active()
            ->with(['unit', 'bagian', 'jabatan'])
            ->latest()
            ->limit(5)
            ->get();

        return view('dashboard', compact(
            'totalKaryawan', 'totalUnit', 'totalBagian', 'totalJabatan',
            'rekapStatus', 'rekapJenisKelamin', 'rekapAgama',
            'rekapUnit', 'rekapUsia', 'rekapPendidikan', 'karyawanTerbaru'
        ));
    }
}
