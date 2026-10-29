<?php

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\EmployeeController;
use App\Http\Controllers\EmployeeExportController;
use App\Http\Controllers\EmployeeFamilyController;
use App\Http\Controllers\EmployeeImportController;
use App\Http\Controllers\EmployeeLeaveController;
use App\Http\Controllers\MonthlySnapshotController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\UserManagementController;
use Illuminate\Support\Facades\Route;

// ─── Redirect root ke login ─────────────────────────────────
Route::get('/', fn () => redirect()->route('login'));

// ─── Rute yang butuh autentikasi ────────────────────────────
Route::middleware(['auth', 'verified'])->group(function () {

    // Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Profil akun
    Route::get('/profil', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profil', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profil', [ProfileController::class, 'destroy'])->name('profile.destroy');
    Route::post('/profil/avatar', [ProfileController::class, 'updateAvatar'])->name('profile.avatar');

    // ── Data Karyawan ─────────────────────────────────────────
    Route::get('/karyawan', [EmployeeController::class, 'index'])->name('employees.index');

    // ── Jadwal & Hak Cuti ─────────────────────────────────────
    Route::get('/cuti', [EmployeeLeaveController::class, 'index'])->name('leaves.index');
    Route::get('/cuti/export', [EmployeeLeaveController::class, 'export'])->name('leaves.export');

    // ── Export Excel (Dapat diakses Admin maupun User) ────────
    Route::get('/karyawan/export', [EmployeeExportController::class, 'showForm'])->name('employees.export.form');
    Route::match(['get', 'post'], '/karyawan/export/download', [EmployeeExportController::class, 'export'])->name('employees.export');

    // ── Snapshot Bulanan (Dapat diakses semua user) ──────────
    Route::get('/snapshot', [MonthlySnapshotController::class, 'index'])->name('snapshots.index');
    Route::get('/snapshot/{snapshot}/download', [MonthlySnapshotController::class, 'download'])->name('snapshots.download');

    // ── Operasi Karyawan: Hanya Admin ────────────────────────
    Route::middleware('admin')->group(function () {
        // ── Snapshot Bulanan: Simpan & Hapus ──────────────────────
        Route::post('/snapshot', [MonthlySnapshotController::class, 'store'])->name('snapshots.store');
        Route::delete('/snapshot/{snapshot}', [MonthlySnapshotController::class, 'destroy'])->name('snapshots.destroy');

        // ── Edit / Hapus Data Cuti Karyawan (Hak Cuti) ───────────
        Route::get('/cuti/{employee}/edit', [EmployeeLeaveController::class, 'edit'])->name('leaves.edit');
        Route::put('/cuti/{employee}', [EmployeeLeaveController::class, 'update'])->name('leaves.update');
        Route::delete('/cuti/{employee}', [EmployeeLeaveController::class, 'destroy'])->name('leaves.destroy');

        Route::get('/karyawan/tambah', [EmployeeController::class, 'create'])->name('employees.create');
        Route::post('/karyawan', [EmployeeController::class, 'store'])->name('employees.store');
        Route::delete('/karyawan/hapus-semua', [EmployeeController::class, 'destroyAll'])->name('employees.destroy-all');
        Route::get('/karyawan/{employee}/edit', [EmployeeController::class, 'edit'])->name('employees.edit');
        Route::put('/karyawan/{employee}', [EmployeeController::class, 'update'])->name('employees.update');
        Route::delete('/karyawan/{employee}', [EmployeeController::class, 'destroy'])->name('employees.destroy');

        // ── Data Keluarga / Batih Karyawan ────────────────────
        Route::post('/karyawan/{employee}/keluarga', [EmployeeFamilyController::class, 'store'])->name('employees.families.store');
        Route::put('/karyawan/{employee}/keluarga/{family}', [EmployeeFamilyController::class, 'update'])->name('employees.families.update');
        Route::delete('/karyawan/{employee}/keluarga/{family}', [EmployeeFamilyController::class, 'destroy'])->name('employees.families.destroy');

        // ── Import Excel: Hanya Admin ─────────────────────────
        Route::get('/karyawan/import/form', [EmployeeImportController::class, 'showForm'])->name('employees.import.form');
        Route::get('/karyawan/import/riwayat', [EmployeeImportController::class, 'history'])->name('employees.import.history');
        Route::get('/karyawan/import/riwayat/{history}', [EmployeeImportController::class, 'showHistory'])->name('employees.import.history.show');
        Route::delete('/karyawan/import/riwayat/{history}', [EmployeeImportController::class, 'destroyHistory'])->name('employees.import.history.destroy');
        Route::post('/karyawan/import/sheets', [EmployeeImportController::class, 'getSheets'])->name('employees.import.sheets');
        Route::post('/karyawan/import', [EmployeeImportController::class, 'import'])->name('employees.import');
        Route::get('/karyawan/import/hasil', [EmployeeImportController::class, 'result'])->name('employees.import.result');

        // ── Manajemen Pengguna: Hanya Admin ──────────────────
        Route::get('/pengguna/export', [UserManagementController::class, 'export'])->name('users.export');
        Route::resource('/pengguna', UserManagementController::class)
            ->names([
                'index' => 'users.index',
                'create' => 'users.create',
                'store' => 'users.store',
                'show' => 'users.show',
                'edit' => 'users.edit',
                'update' => 'users.update',
                'destroy' => 'users.destroy',
            ]);
    });

    Route::get('/karyawan/{employee}', [EmployeeController::class, 'show'])->name('employees.show');
});

// ─── Auth routes dari Breeze ─────────────────────────────────
require __DIR__.'/auth.php';
