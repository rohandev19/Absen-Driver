<?php

use Illuminate\Support\Facades\Route;

// --- IMPORT CONTROLLER ---
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\MaintenanceController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\DriverController;
use App\Http\Controllers\PenggunaController;
use App\Http\Controllers\Auth\AdminLoginController;

/*
|--------------------------------------------------------------------------
| A. RUTE HALAMAN UTAMA (ROOT)
|--------------------------------------------------------------------------
*/
Route::get('/', function () {
    return redirect()->route('admin.login');
});

/*
|--------------------------------------------------------------------------
| B. OTENTIKASI (LOGIN/LOGOUT)
|--------------------------------------------------------------------------
*/
Route::prefix('admin')->group(function () {
    Route::get('/login', [AdminLoginController::class, 'showLoginForm'])->name('admin.login');
    Route::post('/login', [AdminLoginController::class, 'login'])->name('admin.login.submit');
    Route::post('/logout', [AdminLoginController::class, 'logout'])->name('admin.logout');
});

/*
|--------------------------------------------------------------------------
| C. ADMIN PANEL (DILINDUNGI MIDDLEWARE)
|--------------------------------------------------------------------------
*/
Route::middleware(['auth'])->prefix('admin')->group(function () {

    // 1. DASHBOARD
    Route::controller(DashboardController::class)->group(function () {
        Route::get('/dashboard', 'index')->name('admin.dashboard');
        Route::get('/dashboard/status', 'getStatus')->name('admin.dashboard.status');
    });

    // 2. MONITORING & LAPORAN
    Route::controller(ReportController::class)->group(function () {
        Route::get('/riwayat-driver', 'riwayatDriver')->name('admin.riwayat_driver');
        Route::get('/riwayat-unit', 'riwayatUnit')->name('admin.riwayat_unit');
        Route::get('/laporan-darurat', 'laporanDarurat')->name('admin.laporan_darurat');
        Route::get('/rekap-harian', 'rekapHarian')->name('admin.rekap_harian');
        Route::get('/rekap-bulanan', 'rekapBulanan')->name('admin.rekap_bulanan');
        Route::get('/rekap-bulanan/export-checklist', 'exportBulananChecklist')->name('admin.rekap_bulanan.export_checklist');
    });

    // ====================================================
    // 3. MANAJEMEN ASET & MAINTENANCE (YANG DIUPDATE)
    // ====================================================
    Route::controller(MaintenanceController::class)->group(function () {
        // Dashboard Khusus Maintenance
        Route::get('/maintenance-dashboard', 'index')->name('admin.maintenance.dashboard');

        // Kalender Maintenance
        Route::get('/maintenance-calendar', 'calendar')->name('admin.maintenance');
        Route::get('/api/maintenance-events', 'getEvents')->name('api.maintenance.events');

        // --- CRUD ASET (DIPERBARUI) ---
        Route::get('/daftar-aset', 'daftarAset')->name('admin.daftar_aset'); // List

        // FITUR BARU: TAMBAH ASET
        Route::get('/aset/tambah', 'create')->name('admin.aset.create');
        Route::post('/aset/simpan', 'store')->name('admin.aset.store');

        // FITUR BARU: EDIT & UPDATE
        Route::get('/aset/{vehicle}/edit', 'edit')->name('admin.aset.edit');
        Route::put('/aset/{vehicle}/update', 'update')->name('admin.aset.update');

        // FITUR BARU: HAPUS ASET
        Route::delete('/aset/{vehicle}/hapus', 'destroy')->name('admin.aset.destroy');
        

        // Fitur Servis & Visual Check
        Route::post('/daftar-aset/{vehicle}/catat-servis', 'catatServis')->name('admin.aset.catatServis');
        Route::get('/aset/{vehicle}/visual-check', 'visualCheck')->name('admin.aset.visual');
        Route::post('/aset/{vehicle}/resolve-issue', 'resolveIssue')->name('admin.aset.resolveIssue');
        Route::get('/aset/{vehicle}/riwayat-servis', 'riwayatServis')->name('admin.aset.riwayat');
    });

    // 4. MASTER DATA
    Route::resource('/driver', DriverController::class)->except(['show'])->names('admin.driver');
    Route::resource('/pengguna', PenggunaController::class)->except(['show'])->names('admin.pengguna');

});