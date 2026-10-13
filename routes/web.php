<?php

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\EmployeeController;
use App\Http\Controllers\UserManagementController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

// ─── Redirect root ke login ─────────────────────────────────
Route::get('/', fn() => redirect()->route('login'));

// ─── Rute yang butuh autentikasi ────────────────────────────
Route::middleware(['auth', 'verified'])->group(function () {

    // Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Profil akun
    Route::get('/profil', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profil', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profil', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // ── Data Karyawan ─────────────────────────────────────────
    Route::get('/karyawan', [EmployeeController::class, 'index'])->name('employees.index');
    Route::get('/karyawan/{employee}', [EmployeeController::class, 'show'])->name('employees.show');

    // ── Operasi Karyawan: Hanya Admin ────────────────────────
    Route::middleware('admin')->group(function () {
        Route::get('/karyawan/tambah', [EmployeeController::class, 'create'])->name('employees.create');
        Route::post('/karyawan', [EmployeeController::class, 'store'])->name('employees.store');
        Route::get('/karyawan/{employee}/edit', [EmployeeController::class, 'edit'])->name('employees.edit');
        Route::put('/karyawan/{employee}', [EmployeeController::class, 'update'])->name('employees.update');
        Route::delete('/karyawan/{employee}', [EmployeeController::class, 'destroy'])->name('employees.destroy');

        // ── Import Excel ──────────────────────────────────────
        Route::get('/karyawan/import/form', [\App\Http\Controllers\EmployeeImportController::class, 'showForm'])->name('employees.import.form');
        Route::post('/karyawan/import/sheets', [\App\Http\Controllers\EmployeeImportController::class, 'getSheets'])->name('employees.import.sheets');
        Route::post('/karyawan/import', [\App\Http\Controllers\EmployeeImportController::class, 'import'])->name('employees.import');
        Route::get('/karyawan/import/hasil', [\App\Http\Controllers\EmployeeImportController::class, 'result'])->name('employees.import.result');

        // ── Manajemen Pengguna: Hanya Admin ──────────────────
        Route::resource('/pengguna', UserManagementController::class)
            ->names([
                'index'   => 'users.index',
                'create'  => 'users.create',
                'store'   => 'users.store',
                'show'    => 'users.show',
                'edit'    => 'users.edit',
                'update'  => 'users.update',
                'destroy' => 'users.destroy',
            ]);
    });
});

// ─── Auth routes dari Breeze ─────────────────────────────────
require __DIR__.'/auth.php';
