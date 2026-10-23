<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreEmployeeRequest;
use App\Http\Requests\UpdateEmployeeRequest;
use App\Models\Bagian;
use App\Models\Employee;
use App\Models\EmployeeFamily;
use App\Models\Jabatan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class EmployeeController extends Controller
{
    /**
     * Daftar karyawan dengan filter & pencarian.
     */
    public function index(Request $request)
    {
        $query = Employee::with(['bagian', 'jabatan'])
            ->active()
            ->search($request->get('q'))
            ->filterBagian($request->get('bagian_ids'))
            ->filterJabatan($request->get('jabatan_ids'))
            ->filterStatus($request->get('statuses'))
            ->filterJenisKelamin($request->get('jenis_kelamin'))
            ->filterAgama($request->get('agama'))
            ->filterPendidikan($request->get('pendidikan'))
            ->filterPensiun($request->get('pensiun'))
            ->filterPenghargaan($request->get('penghargaan'))
            ->filterRentangUsia($request->get('rentang_usia'))
            ->filterSumberLembar($request->get('sumber_lembar'))
            ->filterLokasi($request->get('lokasi'));

        // Sorting
        $sortBy = $request->get('sort', 'nama');
        $sortDir = $request->get('dir', 'asc');
        $allowed = ['nama', 'personnel_number', 'tanggal_lahir', 'tanggal_mulai_kerja', 'status_karyawan'];
        if (in_array($sortBy, $allowed)) {
            $query->orderBy($sortBy, $sortDir === 'desc' ? 'desc' : 'asc');
        }

        $employees = $query->paginate(25)->withQueryString();
        $totalHasil = $query->toBase()->getCountForPagination();

        // Data untuk filter panel
        $bagians = Bagian::orderBy('nama_bagian')->get();
        $jabatans = Jabatan::orderBy('nama_jabatan')->get();
        $statuses = Employee::active()->distinct()->pluck('status_karyawan')->filter()->sort()->values();
        $agamaList = Employee::active()->distinct()->pluck('agama')->filter()->sort()->values();
        $jenisKelaminList = ['Laki-Laki', 'Perempuan'];
        $rentangUsiaList = Employee::rentangUsiaOptions();
        $masaPensiunList = Employee::masaPensiunOptions();
        $penghargaanList = Employee::penghargaanOptions();
        $sumberList = Employee::active()->distinct()->pluck('sumber_lembar')->filter()->sort()->values();
        $lokasiList = ['Lokal (Kalimantan Selatan)', 'Non-Lokal (Luar Kalimantan Selatan)'];

        $distinctPendidikan = Employee::active()->distinct()->pluck('pendidikan_terakhir')->filter()->values()->toArray();
        $orderPendidikan = ['SD', 'SLTP', 'SMP', 'SLTA', 'SMA', 'SMK', 'D1', 'D2', 'D3', 'D4', 'S1', 'S2', 'S3'];
        $pendidikanList = collect($orderPendidikan)
            ->filter(fn ($p) => in_array($p, $distinctPendidikan))
            ->merge(collect($distinctPendidikan)->diff($orderPendidikan))
            ->values();

        // Hitung filter aktif
        $activeFilters = collect([
            'bagian_ids', 'jabatan_ids', 'statuses',
            'jenis_kelamin', 'agama', 'pendidikan', 'pensiun', 'penghargaan', 'rentang_usia', 'sumber_lembar', 'lokasi',
        ])->filter(fn ($key) => $request->filled($key))->count();
        $activeFilters += $request->filled('q') ? 1 : 0;

        $statusCounts = Employee::active()->selectRaw('status_karyawan, count(*) as total')->groupBy('status_karyawan')->pluck('total', 'status_karyawan')->toArray();
        $totalActiveEmployees = Employee::active()->count();

        return view('employees.index', compact(
            'employees', 'totalHasil', 'activeFilters',
            'bagians', 'jabatans', 'statuses', 'statusCounts', 'totalActiveEmployees',
            'agamaList', 'jenisKelaminList', 'pendidikanList', 'masaPensiunList', 'penghargaanList', 'rentangUsiaList', 'sumberList', 'lokasiList'
        ));
    }

    /**
     * Halaman detail karyawan.
     */
    public function show(Employee $employee)
    {
        $employee->load(['bagian', 'jabatan', 'families']);

        return view('employees.show', compact('employee'));
    }

    /**
     * Form tambah karyawan (Admin only).
     */
    public function create()
    {
        $bagians = Bagian::orderBy('nama_bagian')->get();
        $jabatans = Jabatan::orderBy('nama_jabatan')->get();
        $statuses = ['PKWT', 'Karpim', 'Karpel', 'KTNG', 'BHL'];

        return view('employees.create', compact('bagians', 'jabatans', 'statuses'));
    }

    /**
     * Simpan data karyawan baru (Admin only).
     */
    public function store(StoreEmployeeRequest $request)
    {
        $validated = $request->validated();
        if (! str_contains(strtoupper($validated['status_karyawan'] ?? ''), 'PKWT')) {
            $validated['no_pkwt'] = null;
        }

        $employee = Employee::create($validated);

        if ($request->filled('families') && is_array($request->input('families'))) {
            foreach ($request->input('families') as $famData) {
                if (! empty($famData['nama'])) {
                    $employee->families()->create([
                        'nama' => $famData['nama'],
                        'hubungan' => $famData['hubungan'] ?? 'Anak',
                        'jenis_kelamin' => in_array($famData['jenis_kelamin'] ?? '', ['Laki-Laki', 'Perempuan']) ? $famData['jenis_kelamin'] : 'Tidak Diketahui',
                        'tanggal_lahir' => ! empty($famData['tanggal_lahir']) ? $famData['tanggal_lahir'] : null,
                    ]);
                }
            }
        }

        return redirect()
            ->route('employees.show', $employee)
            ->with('success', 'Data karyawan berhasil ditambahkan.');
    }

    /**
     * Form edit karyawan (Admin only).
     */
    public function edit(Employee $employee)
    {
        $bagians = Bagian::orderBy('nama_bagian')->get();
        $jabatans = Jabatan::orderBy('nama_jabatan')->get();
        $statuses = ['PKWT', 'Karpim', 'Karpel', 'KTNG', 'BHL'];

        return view('employees.edit', compact('employee', 'bagians', 'jabatans', 'statuses'));
    }

    /**
     * Update data karyawan (Admin only).
     */
    public function update(UpdateEmployeeRequest $request, Employee $employee)
    {
        $validated = $request->validated();
        if (! str_contains(strtoupper($validated['status_karyawan'] ?? ''), 'PKWT')) {
            $validated['no_pkwt'] = null;
        }

        $employee->update($validated);

        return redirect()
            ->route('employees.show', $employee)
            ->with('success', 'Data karyawan berhasil diperbarui.');
    }

    /**
     * Hapus data karyawan (Admin only - soft delete).
     */
    public function destroy(Employee $employee)
    {
        $employee->delete();

        return redirect()
            ->route('employees.index')
            ->with('success', 'Data karyawan berhasil dihapus.');
    }

    /**
     * Hapus seluruh data karyawan dan anggota keluarga/batih (Admin only).
     */
    public function destroyAll()
    {
        try {
            DB::statement('SET FOREIGN_KEY_CHECKS=0;');
            EmployeeFamily::truncate();
            Employee::truncate();
        } finally {
            DB::statement('SET FOREIGN_KEY_CHECKS=1;');
        }

        return redirect()
            ->route('employees.index')
            ->with('success', 'Seluruh data karyawan dan anggota keluarga berhasil dikosongkan.');
    }
}
