<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Symfony\Component\HttpFoundation\Response;

class UserManagementController extends Controller
{
    public function index()
    {
        $users = User::orderBy('name')->paginate(20);

        return view('users.index', compact('users'));
    }

    public function create()
    {
        return view('users.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => ['required', 'confirmed', Password::min(8)->letters()->numbers()],
            'role' => 'required|in:admin,user',
        ], [
            'name.required' => 'Nama lengkap wajib diisi.',
            'email.required' => 'Alamat email wajib diisi.',
            'email.unique' => 'Alamat email sudah digunakan.',
            'password.required' => 'Kata sandi wajib diisi.',
            'password.confirmed' => 'Konfirmasi kata sandi tidak cocok. Pastikan kedua kolom kata sandi terisi sama persis.',
            'password.min' => 'Kata sandi terlalu pendek. Minimal harus 8 karakter.',
            'password.letters' => 'Kata sandi harus mengandung minimal 1 huruf.',
            'password.numbers' => 'Kata sandi harus mengandung minimal 1 angka.',
            'role.required' => 'Peran pengguna wajib dipilih.',
        ]);

        User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'role' => $validated['role'],
            'is_active' => true,
        ]);

        return redirect()->route('users.index')
            ->with('success', 'Pengguna berhasil ditambahkan.');
    }

    public function edit(User $pengguna)
    {
        return view('users.edit', ['user' => $pengguna]);
    }

    public function update(Request $request, User $pengguna)
    {
        $request->merge([
            'is_active' => $request->boolean('is_active'),
        ]);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => ['required', 'email', Rule::unique('users')->ignore($pengguna->id)],
            'role' => 'required|in:admin,user',
            'is_active' => 'boolean',
            'password' => ['nullable', 'confirmed', Password::min(8)->letters()->numbers()],
        ], [
            'name.required' => 'Nama lengkap wajib diisi.',
            'email.required' => 'Alamat email wajib diisi.',
            'email.unique' => 'Alamat email sudah terdaftar untuk pengguna lain.',
            'role.required' => 'Peran pengguna wajib dipilih.',
            'is_active.boolean' => 'Status aktif tidak valid.',
            'password.confirmed' => 'Konfirmasi kata sandi tidak cocok dengan kata sandi baru. Pastikan kedua kolom kata sandi terisi sama persis.',
            'password.min' => 'Kata sandi baru terlalu pendek. Minimal harus 8 karakter.',
            'password.letters' => 'Kata sandi baru harus mengandung minimal 1 huruf.',
            'password.numbers' => 'Kata sandi baru harus mengandung minimal 1 angka.',
        ]);

        $pengguna->update([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'role' => $validated['role'],
            'is_active' => $validated['is_active'],
        ]);

        if (! empty($validated['password'])) {
            $pengguna->update(['password' => Hash::make($validated['password'])]);
        }

        return redirect()->route('users.index')
            ->with('success', 'Data pengguna berhasil diperbarui.');
    }

    public function destroy(User $pengguna)
    {
        if ($pengguna->id === auth()->id()) {
            return back()->with('error', 'Tidak dapat menghapus akun yang sedang aktif.');
        }
        $pengguna->delete();

        return redirect()->route('users.index')
            ->with('success', 'Pengguna berhasil dihapus.');
    }

    public function show(User $pengguna)
    {
        return view('users.show', ['user' => $pengguna]);
    }

    /**
     * Export daftar pengguna ke file Excel.
     */
    public function export(): Response
    {
        @ini_set('memory_limit', '256M');
        @set_time_limit(180);

        $users = User::orderBy('name')->get();

        $spreadsheet = new Spreadsheet;
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Data Pengguna');

        // Header
        $headers = ['#', 'Nama', 'Email', 'Peran', 'Status', 'Tanggal Dibuat'];
        foreach ($headers as $col => $header) {
            $letter = Coordinate::stringFromColumnIndex($col + 1);
            $sheet->setCellValue($letter.'1', $header);
        }

        $sheet->getStyle('A1:F1')->applyFromArray([
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF'], 'size' => 11],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '1E5E3A']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
        ]);
        $sheet->getRowDimension(1)->setRowHeight(26);

        // Data rows
        foreach ($users as $i => $user) {
            $row = $i + 2;
            $sheet->setCellValue('A'.$row, $i + 1);
            $sheet->setCellValue('B'.$row, $user->name);
            $sheet->setCellValue('C'.$row, $user->email);
            $sheet->setCellValue('D'.$row, $user->role === 'admin' ? 'Admin' : 'Pengguna');
            $sheet->setCellValue('E'.$row, $user->is_active ? 'Aktif' : 'Nonaktif');
            $sheet->setCellValue('F'.$row, $user->created_at->format('d/m/Y'));
            $sheet->getRowDimension($row)->setRowHeight(20);
        }

        if ($users->count() > 0) {
            $sheet->getStyle('A2:F'.($users->count() + 1))->applyFromArray([
                'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => 'DCE7E1']]],
                'alignment' => ['vertical' => Alignment::VERTICAL_CENTER],
            ]);
        }

        foreach (range(1, 6) as $col) {
            $sheet->getColumnDimension(
                Coordinate::stringFromColumnIndex($col)
            )->setAutoSize(true);
        }

        $sheet->freezePane('A2');

        $dateSuffix = now()->format('Ymd_His');
        $fileName = "SIMKAR_Data_Pengguna_{$dateSuffix}.xlsx";

        return response()->streamDownload(function () use ($spreadsheet) {
            if (! app()->runningUnitTests()) {
                while (ob_get_level() > 0) {
                    ob_end_clean();
                }
            }
            (new Xlsx($spreadsheet))->save('php://output');
        }, $fileName, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'Cache-Control' => 'max-age=0, no-cache, must-revalidate',
            'Pragma' => 'public',
        ]);
    }
}
