<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreEmployeeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check() && auth()->user()->isAdmin();
    }

    public function rules(): array
    {
        return [
            'pers_no' => 'nullable|string|max:20',
            'personnel_number' => 'nullable|string|max:20|unique:employees,personnel_number',
            'nik_lokal' => 'nullable|string|max:30',
            'nama' => 'required|string|max:255',
            'tanggal_lahir' => 'nullable|date|before:today',
            'tanggal_mulai_kerja' => 'nullable|date|before_or_equal:today',
            'tmt_cuti' => 'nullable|date',
            'jenis_kelamin' => 'required|in:Laki-Laki,Perempuan,Tidak Diketahui',
            'agama' => 'nullable|string|max:50',
            'pendidikan_terakhir' => 'nullable|string|max:20',
            'unit_id' => 'nullable|exists:units,id',
            'bagian_id' => 'nullable|exists:bagians,id',
            'jabatan_id' => 'nullable|exists:jabatans,id',
            'status_karyawan' => 'nullable|string|max:50',
            'golongan' => 'nullable|string|max:20',
            'grup_golongan' => 'nullable|string|max:20',
            'grup_jabatan' => 'nullable|string|max:50',
            'no_pkwt' => 'nullable|string|max:100',
            'grade' => 'nullable|string|max:20',
            'suku' => 'nullable|string|max:50',
            'alamat' => 'nullable|string',
            'domisili' => 'nullable|string|max:30',
            'sumber_lembar' => 'nullable|string|max:20',
            'catatan' => 'nullable|string',
            'families' => 'nullable|array',
            'families.*.nama' => 'nullable|string|max:255',
            'families.*.hubungan' => 'nullable|string|max:50',
            'families.*.jenis_kelamin' => 'nullable|string|max:20',
            'families.*.tanggal_lahir' => 'nullable|date',
        ];
    }

    public function messages(): array
    {
        return [
            'nama.required' => 'Nama karyawan wajib diisi.',
            'jenis_kelamin.required' => 'Jenis kelamin wajib dipilih.',
            'jenis_kelamin.in' => 'Jenis kelamin tidak valid.',
            'personnel_number.unique' => 'NIK SAP sudah digunakan oleh karyawan lain.',
            'tanggal_lahir.before' => 'Tanggal lahir harus sebelum hari ini.',
            'tanggal_mulai_kerja.before_or_equal' => 'Tanggal mulai kerja tidak boleh lebih dari hari ini.',
            'unit_id.exists' => 'Unit/kebun tidak ditemukan.',
            'bagian_id.exists' => 'Bagian tidak ditemukan.',
            'jabatan_id.exists' => 'Jabatan tidak ditemukan.',
        ];
    }
}
