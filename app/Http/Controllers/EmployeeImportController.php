<?php

namespace App\Http\Controllers;

use App\Imports\EmployeeSheetImport;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use PhpOffice\PhpSpreadsheet\IOFactory;
use Illuminate\Support\Facades\Storage;

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
     * Baca nama lembar dari file yang diupload (AJAX).
     */
    public function getSheets(Request $request)
    {
        $request->validate([
            'file' => 'required|file|mimes:xlsx,xls|max:51200',
        ]);

        $file     = $request->file('file');
        $path     = $file->store('imports/temp', 'local');
        $fullPath = Storage::disk('local')->path($path);

        try {
            $spreadsheet = IOFactory::load($fullPath);
            $sheets = [];
            foreach ($spreadsheet->getSheetNames() as $i => $name) {
                $sheet = $spreadsheet->getSheet($i);
                $rowCount = max(0, $sheet->getHighestDataRow() - 1);
                $sheets[] = [
                    'index' => $i,
                    'name'  => $name,
                    'rows'  => $rowCount,
                ];
            }
            Storage::disk('local')->delete($path);
            return response()->json(['sheets' => $sheets]);
        } catch (\Throwable $e) {
            Storage::disk('local')->delete($path);
            return response()->json(['error' => 'Gagal membaca file: ' . $e->getMessage()], 422);
        }
    }

    /**
     * Proses import lembar yang dipilih.
     */
    public function import(Request $request)
    {
        $request->validate([
            'file'          => 'required|file|mimes:xlsx,xls|max:51200',
            'sheet_names'   => 'required|array|min:1',
            'sheet_names.*' => 'required|string',
        ], [
            'file.required'        => 'File Excel wajib dipilih.',
            'file.mimes'           => 'Format file harus .xlsx atau .xls.',
            'file.max'             => 'Ukuran file maksimal 50MB.',
            'sheet_names.required' => 'Pilih minimal satu lembar untuk diimport.',
        ]);

        $file     = $request->file('file');
        $fileName = $file->getClientOriginalName();
        $path     = $file->store('imports', 'local');
        $fullPath = Storage::disk('local')->path($path);

        $totalInserted = 0;
        $totalUpdated  = 0;
        $totalSkipped  = 0;
        $allErrors     = [];
        $sheetResults  = [];

        try {
            foreach ($request->input('sheet_names') as $sheetName) {
                try {
                    $importer = new EmployeeSheetImport($sheetName, $fileName);

                    Excel::import($importer, $fullPath, null, \Maatwebsite\Excel\Excel::XLSX);

                    $totalInserted += $importer->inserted;
                    $totalUpdated  += $importer->updated;
                    $totalSkipped  += $importer->skipped;
                    $allErrors      = array_merge($allErrors, $importer->errors);

                    $sheetResults[] = [
                        'sheet'    => $sheetName,
                        'inserted' => $importer->inserted,
                        'updated'  => $importer->updated,
                        'skipped'  => $importer->skipped,
                    ];
                } catch (\Throwable $e) {
                    $allErrors[]    = "Error pada lembar '{$sheetName}': " . $e->getMessage();
                    $sheetResults[] = [
                        'sheet'    => $sheetName,
                        'inserted' => 0,
                        'updated'  => 0,
                        'skipped'  => 0,
                    ];
                }
            }
        } finally {
            Storage::disk('local')->delete($path);
        }

        session()->flash('import_results', [
            'inserted'      => $totalInserted,
            'updated'       => $totalUpdated,
            'skipped'       => $totalSkipped,
            'errors'        => array_slice($allErrors, 0, 30),
            'sheet_results' => $sheetResults,
        ]);

        return redirect()->route('employees.import.result');
    }

    /**
     * Tampilkan halaman hasil import.
     */
    public function result()
    {
        $results = session('import_results');
        if (!$results) {
            return redirect()->route('employees.import.form')
                ->with('error', 'Tidak ada hasil import. Silakan import file terlebih dahulu.');
        }
        return view('employees.import_result', compact('results'));
    }
}
