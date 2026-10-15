<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreEmployeeRequest;
use App\Http\Requests\UpdateEmployeeRequest;
use App\Models\Bagian;
use App\Models\Employee;
use App\Models\Jabatan;
use Illuminate\Http\Request;

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
            ->filterRentangUsia($request->get('rentang_usia'))
            ->filterSumberLembar($request->get('sumber_lembar'));

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
        $sumberList = Employee::active()->distinct()->pluck('sumber_lembar')->filter()->sort()->values();

        // Hitung filter aktif
        $activeFilters = collect([
            'bagian_ids', 'jabatan_ids', 'statuses',
            'jenis_kelamin', 'agama', 'rentang_usia', 'sumber_lembar',
        ])->filter(fn ($key) => $request->filled($key))->count();
        $activeFilters += $request->filled('q') ? 1 : 0;

        return view('employees.index', compact(
            'employees', 'totalHasil', 'activeFilters',
            'bagians', 'jabatans', 'statuses',
            'agamaList', 'jenisKelaminList', 'rentangUsiaList', 'sumberList'
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
        $statuses = ['PKWT', 'Karpim & Karpel', 'KTNG'];

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
        $statuses = ['PKWT', 'Karpim & Karpel', 'KTNG'];

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
}
