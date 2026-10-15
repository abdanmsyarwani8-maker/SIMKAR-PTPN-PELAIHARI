<?php

namespace App\Http\Controllers;

use App\Imports\EmployeeImport;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Facades\Excel;
use PhpOffice\PhpSpreadsheet\IOFactory;

class EmployeeImportController extends Controller
{
    /**
     * Tampilkan halaman form import.
     */
    public function showForm()
    {
        return view('employees.import');
    }

    /**
     * Baca daftar lembar dari file yang diupload (AJAX).
     */
    public function getSheets(Request $request)
    {
        $request->validate([
            'file' => 'required|file|mimes:xlsx,xls|max:51200',
        ], [
            'file.required' => 'File Excel wajib dipilih.',
            'file.mimes' => 'Format file harus .xlsx atau .xls.',
            'file.max' => 'Ukuran file maksimal 50MB.',
        ]);

        $file = $request->file('file');
        $path = $file->store('imports/temp', 'local');
        $fullPath = Storage::disk('local')->path($path);

        try {
            $spreadsheet = IOFactory::load($fullPath);
            $sheets = [];
            foreach ($spreadsheet->getSheetNames() as $i => $name) {
                $sheet = $spreadsheet->getSheet($i);
                $rowCount = max(0, $sheet->getHighestDataRow() - 1);
                $sheets[] = [
                    'index' => $i,
                    'name' => $name,
                    'rows' => $rowCount,
                ];
            }
            Storage::disk('local')->delete($path);

            return response()->json(['sheets' => $sheets]);
        } catch (\Throwable $e) {
            Storage::disk('local')->delete($path);

            return response()->json(['error' => 'Gagal membaca lembar file Excel: '.$e->getMessage()], 422);
        }
    }

    /**
     * Proses import file Excel (karyawan dan batih).
     */
    public function import(Request $request)
    {
        $request->validate([
            'file' => 'required|file|mimes:xlsx,xls|max:51200',
            'sheet_names' => 'nullable|array',
            'sheet_names.*' => 'nullable|string',
        ], [
            'file.required' => 'File Excel wajib dipilih.',
            'file.mimes' => 'Format file harus .xlsx atau .xls.',
            'file.max' => 'Ukuran file maksimal 50MB.',
        ]);

        $file = $request->file('file');
        $fileName = $file->getClientOriginalName();
        $path = $file->store('imports', 'local');
        $fullPath = Storage::disk('local')->path($path);

        $selectedSheets = $request->input('sheet_names', []);
        // Bersihkan sheet_names dari nilai kosong
        $selectedSheets = array_values(array_filter((array) $selectedSheets, fn ($s) => $s !== null && trim((string) $s) !== ''));

        try {
            DB::beginTransaction();

            $importer = new EmployeeImport($fileName, $selectedSheets);
            Excel::import($importer, $fullPath);

            $results = $importer->getResults();

            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();

            return redirect()->route('employees.import.form')
                ->with('error', 'Terjadi kesalahan sistem saat memproses import: '.$e->getMessage());
        } finally {
            Storage::disk('local')->delete($path);
        }

        session()->flash('import_results', [
            'file_name' => $fileName,
            'inserted' => $results['inserted'],
            'updated' => $results['updated'],
            'family_processed' => $results['family_processed'],
            'family_inserted' => $results['family_inserted'],
            'family_updated' => $results['family_updated'],
            'skipped' => $results['skipped'],
            'skipped_details' => array_slice($results['skipped_details'], 0, 50),
            'errors' => array_slice($results['errors'], 0, 50),
            'sheet_results' => $results['sheet_results'],
        ]);

        return redirect()->route('employees.import.result');
    }

    /**
     * Tampilkan halaman hasil import.
     */
    public function result()
    {
        $results = session('import_results');
        if (! $results) {
            return redirect()->route('employees.import.form')
                ->with('error', 'Tidak ada hasil import. Silakan import file terlebih dahulu.');
        }

        return view('employees.import_result', compact('results'));
    }
}
