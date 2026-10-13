<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use App\Models\Unit;
use App\Models\Bagian;
use App\Models\Jabatan;
use App\Http\Requests\StoreEmployeeRequest;
use App\Http\Requests\UpdateEmployeeRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class EmployeeController extends Controller
{
    /**
     * Daftar karyawan dengan filter & pencarian.
     */
    public function index(Request $request)
    {
        $query = Employee::with(['unit', 'bagian', 'jabatan'])
            ->active()
            ->search($request->get('q'))
            ->filterUnit($request->get('unit_ids'))
            ->filterBagian($request->get('bagian_ids'))
            ->filterJabatan($request->get('jabatan_ids'))
            ->filterStatus($request->get('statuses'))
            ->filterJenisKelamin($request->get('jenis_kelamin'))
            ->filterAgama($request->get('agama'))
            ->filterRentangUsia($request->get('rentang_usia'))
            ->filterSumberLembar($request->get('sumber_lembar'));

        // Sorting
        $sortBy  = $request->get('sort', 'nama');
        $sortDir = $request->get('dir', 'asc');
        $allowed = ['nama', 'personnel_number', 'tanggal_lahir', 'tanggal_mulai_kerja', 'status_karyawan'];
        if (in_array($sortBy, $allowed)) {
            $query->orderBy($sortBy, $sortDir === 'desc' ? 'desc' : 'asc');
        }

        $employees   = $query->paginate(25)->withQueryString();
        $totalHasil  = $query->toBase()->getCountForPagination();

        // Data untuk filter panel
        $units           = Unit::orderBy('nama_unit')->get();
        $bagians         = Bagian::orderBy('nama_bagian')->get();
        $jabatans        = Jabatan::orderBy('nama_jabatan')->get();
        $statuses        = Employee::active()->distinct()->pluck('status_karyawan')->filter()->sort()->values();
        $agamaList       = Employee::active()->distinct()->pluck('agama')->filter()->sort()->values();
        $jenisKelaminList = ['Laki-Laki', 'Perempuan'];
        $rentangUsiaList = Employee::rentangUsiaOptions();
        $sumberList      = Employee::active()->distinct()->pluck('sumber_lembar')->filter()->sort()->values();

        // Hitung filter aktif
        $activeFilters = collect([
            'unit_ids', 'bagian_ids', 'jabatan_ids', 'statuses',
            'jenis_kelamin', 'agama', 'rentang_usia', 'sumber_lembar',
        ])->filter(fn($key) => $request->filled($key))->count();
        $activeFilters += $request->filled('q') ? 1 : 0;

        return view('employees.index', compact(
            'employees', 'totalHasil', 'activeFilters',
            'units', 'bagians', 'jabatans', 'statuses',
            'agamaList', 'jenisKelaminList', 'rentangUsiaList', 'sumberList'
        ));
    }

    /**
     * Halaman detail karyawan.
     */
    public function show(Employee $employee)
    {
        $employee->load(['unit', 'bagian', 'jabatan']);
        return view('employees.show', compact('employee'));
    }

    /**
     * Form tambah karyawan (Admin only).
     */
    public function create()
    {
        $units    = Unit::orderBy('nama_unit')->get();
        $bagians  = Bagian::orderBy('nama_bagian')->get();
        $jabatans = Jabatan::orderBy('nama_jabatan')->get();
        $statuses = ['Karpim - Tetap', 'Karpel - Tetap', 'Karpel - Tidak Tetap', 'Papam'];
        return view('employees.create', compact('units', 'bagians', 'jabatans', 'statuses'));
    }

    /**
     * Simpan data karyawan baru (Admin only).
     */
    public function store(StoreEmployeeRequest $request)
    {
        $employee = Employee::create($request->validated());

        return redirect()
            ->route('employees.show', $employee)
            ->with('success', 'Data karyawan berhasil ditambahkan.');
    }

    /**
     * Form edit karyawan (Admin only).
     */
    public function edit(Employee $employee)
    {
        $units    = Unit::orderBy('nama_unit')->get();
        $bagians  = Bagian::orderBy('nama_bagian')->get();
        $jabatans = Jabatan::orderBy('nama_jabatan')->get();
        $statuses = ['Karpim - Tetap', 'Karpel - Tetap', 'Karpel - Tidak Tetap', 'Papam'];
        return view('employees.edit', compact('employee', 'units', 'bagians', 'jabatans', 'statuses'));
    }

    /**
     * Update data karyawan (Admin only).
     */
    public function update(UpdateEmployeeRequest $request, Employee $employee)
    {
        $employee->update($request->validated());

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
