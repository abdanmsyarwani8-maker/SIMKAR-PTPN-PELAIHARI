<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use App\Models\EmployeeFamily;
use Illuminate\Http\Request;

class EmployeeFamilyController extends Controller
{
    /**
     * Simpan data anggota keluarga baru untuk karyawan.
     */
    public function store(Request $request, Employee $employee)
    {
        $validated = $request->validate([
            'nama' => 'required|string|max:255',
            'hubungan' => 'required|string|max:50',
            'jenis_kelamin' => 'required|in:Laki-Laki,Perempuan,Tidak Diketahui',
            'tanggal_lahir' => 'nullable|date|before_or_equal:today',
            'tempat_lahir' => 'nullable|string|max:100',
            'keterangan' => 'nullable|string|max:500',
        ], [
            'nama.required' => 'Nama anggota keluarga wajib diisi.',
            'hubungan.required' => 'Hubungan keluarga wajib dipilih.',
            'jenis_kelamin.required' => 'Jenis kelamin wajib dipilih.',
            'tanggal_lahir.before_or_equal' => 'Tanggal lahir tidak boleh melebihi hari ini.',
        ]);

        $employee->families()->create($validated);

        return redirect()->back()->with('success', 'Anggota keluarga berhasil ditambahkan.');
    }

    /**
     * Update data anggota keluarga karyawan.
     */
    public function update(Request $request, Employee $employee, EmployeeFamily $family)
    {
        abort_if($family->employee_id !== $employee->id, 404);

        $validated = $request->validate([
            'nama' => 'required|string|max:255',
            'hubungan' => 'required|string|max:50',
            'jenis_kelamin' => 'required|in:Laki-Laki,Perempuan,Tidak Diketahui',
            'tanggal_lahir' => 'nullable|date|before_or_equal:today',
            'tempat_lahir' => 'nullable|string|max:100',
            'keterangan' => 'nullable|string|max:500',
        ], [
            'nama.required' => 'Nama anggota keluarga wajib diisi.',
            'hubungan.required' => 'Hubungan keluarga wajib dipilih.',
            'jenis_kelamin.required' => 'Jenis kelamin wajib dipilih.',
            'tanggal_lahir.before_or_equal' => 'Tanggal lahir tidak boleh melebihi hari ini.',
        ]);

        $family->update($validated);

        return redirect()->back()->with('success', 'Data anggota keluarga berhasil diperbarui.');
    }

    /**
     * Hapus data anggota keluarga karyawan.
     */
    public function destroy(Employee $employee, EmployeeFamily $family)
    {
        abort_if($family->employee_id !== $employee->id, 404);

        $family->delete();

        return redirect()->back()->with('success', 'Data anggota keluarga berhasil dihapus.');
    }
}
