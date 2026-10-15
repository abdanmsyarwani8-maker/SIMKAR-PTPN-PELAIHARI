<?php

namespace App\Imports;

use Maatwebsite\Excel\Concerns\Import;
use Maatwebsite\Excel\Concerns\SkipsUnknownSheets;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class EmployeeImport implements Import, SkipsUnknownSheets, WithMultipleSheets
{
    protected string $sourceFile;

    protected array $selectedSheets;

    /**
     * @var array<string|int, EmployeeSheetImport>
     */
    public array $sheetImporters = [];

    public function __construct(string $sourceFile = '', array $selectedSheets = [])
    {
        $this->sourceFile = $sourceFile;
        $this->selectedSheets = $selectedSheets;
    }

    /**
     * Menentukan lembar mana yang akan diimport.
     * Jika array selectedSheets diisi, hanya lembar yang dipilih yang akan diproses.
     * Jika kosong, lembar pertama (indeks 0) akan diproses secara default.
     *
     * @return array<string|int, EmployeeSheetImport>
     */
    public function sheets(): array
    {
        $sheets = [];

        if (! empty($this->selectedSheets)) {
            foreach ($this->selectedSheets as $sheetName) {
                $sheetKey = (string) $sheetName;
                $importer = new EmployeeSheetImport($sheetKey, $this->sourceFile);
                $this->sheetImporters[$sheetKey] = $importer;
                $sheets[$sheetKey] = $importer;
            }
        } else {
            // Import lembar pertama (indeks 0) jika file satu lembar atau tidak ada lembar spesifik yang dipilih
            $importer = new EmployeeSheetImport('Sheet 1', $this->sourceFile);
            $this->sheetImporters[0] = $importer;
            $sheets[0] = $importer;
        }

        return $sheets;
    }

    /**
     * Abaikan lembar yang tidak ditemukan tanpa melempar fatal exception.
     */
    public function onUnknownSheet(string|int $sheetName): void
    {
        // Lembar tidak ditemukan di file Excel, dilewati secara aman
    }

    /**
     * Ambil rekapitulasi hasil import gabungan dari seluruh lembar yang diproses.
     */
    public function getResults(): array
    {
        $totalInserted = 0;
        $totalUpdated = 0;
        $totalSkipped = 0;
        $totalFamilyProcessed = 0;
        $totalFamilyInserted = 0;
        $totalFamilyUpdated = 0;
        $allErrors = [];
        $allSkippedDetails = [];
        $sheetResults = [];

        foreach ($this->sheetImporters as $importer) {
            $totalInserted += $importer->inserted;
            $totalUpdated += $importer->updated;
            $totalSkipped += $importer->skipped;
            $totalFamilyProcessed += $importer->familyProcessed;
            $totalFamilyInserted += $importer->familyInserted;
            $totalFamilyUpdated += $importer->familyUpdated;

            $allErrors = array_merge($allErrors, $importer->errors);
            $allSkippedDetails = array_merge($allSkippedDetails, $importer->skippedDetails);

            $sheetResults[] = [
                'sheet' => $importer->getSheetName(),
                'inserted' => $importer->inserted,
                'updated' => $importer->updated,
                'family_processed' => $importer->familyProcessed,
                'family_inserted' => $importer->familyInserted,
                'family_updated' => $importer->familyUpdated,
                'skipped' => $importer->skipped,
            ];
        }

        return [
            'inserted' => $totalInserted,
            'updated' => $totalUpdated,
            'family_processed' => $totalFamilyProcessed,
            'family_inserted' => $totalFamilyInserted,
            'family_updated' => $totalFamilyUpdated,
            'skipped' => $totalSkipped,
            'errors' => $allErrors,
            'skipped_details' => $allSkippedDetails,
            'sheet_results' => $sheetResults,
        ];
    }
}
