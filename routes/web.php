<?php

use Illuminate\Support\Facades\Route;

// --- 1. IMPORT CONTROLLER BARU ---
// Kita memecah AdminDashboardController menjadi 3 bagian:
use App\Http\Controllers\DashboardController;     // Untuk Dashboard & KPI
use App\Http\Controllers\MaintenanceController;   // Untuk Aset & Servis
use App\Http\Controllers\ReportController;        // Untuk Laporan & Rekap

// Controller lama yang tetap dipertahankan:
use App\Http\Controllers\DriverController;
use App\Http\Controllers\PenggunaController;
use App\Http\Controllers\Auth\AdminLoginController; 

/*
|--------------------------------------------------------------------------
| A. RUTE HALAMAN UTAMA (ROOT)
|--------------------------------------------------------------------------
*/
Route::get('/', function () {
    // Praktik terbaik: Arahkan ke login jika belum masuk
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
| Semua route di bawah ini memerlukan Login.
*/
Route::middleware(['auth'])->prefix('admin')->group(function () {

    // ====================================================
    // 1. DASHBOARD UTAMA (DashboardController)
    // ====================================================
    Route::controller(DashboardController::class)->group(function () {
        Route::get('/dashboard', 'index')->name('admin.dashboard'); // Dulu: dashboard
        Route::get('/dashboard/status', 'getStatus')->name('admin.dashboard.status');
    });

    // ====================================================
    // 2. MONITORING & LAPORAN (ReportController)
    // ====================================================
    Route::controller(ReportController::class)->group(function () {
        // Monitoring Harian
        Route::get('/riwayat-driver', 'riwayatDriver')->name('admin.riwayat_driver');
        Route::get('/riwayat-unit', 'riwayatUnit')->name('admin.riwayat_unit');
        Route::get('/laporan-darurat', 'laporanDarurat')->name('admin.laporan_darurat');

        // Rekapitulasi & Export
        Route::get('/rekap-harian', 'rekapHarian')->name('admin.rekap_harian');
        Route::get('/rekap-bulanan', 'rekapBulanan')->name('admin.rekap_bulanan');
        Route::get('/rekap-bulanan/export-checklist', 'exportBulananChecklist')->name('admin.rekap_bulanan.export_checklist');
    });

    // ====================================================
    // 3. MANAJEMEN ASET & MAINTENANCE (MaintenanceController)
    // ====================================================
    Route::controller(MaintenanceController::class)->group(function () {
        // Dashboard Khusus Maintenance
        Route::get('/maintenance-dashboard', 'index')->name('admin.maintenance.dashboard');
        
        // Kalender Maintenance
        Route::get('/maintenance-calendar', 'calendar')->name('admin.maintenance'); // Nama route dijaga agar view tidak error
        Route::get('/api/maintenance-events', 'getEvents')->name('api.maintenance.events');

        // CRUD & Manajemen Aset
        Route::get('/daftar-aset', 'daftarAset')->name('admin.daftar_aset');
        Route::get('/aset/{vehicle}/edit', 'edit')->name('admin.aset.edit');
        Route::put('/aset/{vehicle}/update', 'update')->name('admin.aset.update');

        // Fitur Servis & Visual Check
        Route::post('/daftar-aset/{vehicle}/catat-servis', 'catatServis')->name('admin.aset.catatServis');
        Route::get('/aset/{vehicle}/visual-check', 'visualCheck')->name('admin.aset.visual');
        Route::post('/aset/{vehicle}/resolve-issue', 'resolveIssue')->name('admin.aset.resolveIssue');
        Route::get('/aset/{vehicle}/riwayat-servis', 'riwayatServis')->name('admin.aset.riwayat');
    });

    // ====================================================
    // 4. MASTER DATA (Tetap Menggunakan Resource)
    // ====================================================
    Route::resource('/driver', DriverController::class)->except(['show'])->names('admin.driver');
    Route::resource('/pengguna', PenggunaController::class)->except(['show'])->names('admin.pengguna');

});