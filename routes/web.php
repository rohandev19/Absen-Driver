<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Artisan;
// --- IMPORT CONTROLLER ---
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\MaintenanceController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\DriverController;
use App\Http\Controllers\PenggunaController;
use App\Http\Controllers\Auth\AdminLoginController;
use App\Http\Controllers\ProjectController;

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

    // ====================================================
    // 1. DASHBOARD UTAMA
    // ====================================================
    Route::controller(DashboardController::class)->group(function () {
        Route::get('/dashboard', 'index')->name('admin.dashboard');
        Route::get('/dashboard/status', 'getStatus')->name('admin.dashboard.status');
    });

    // ====================================================
    // 2. MONITORING & LAPORAN (REPORT CONTROLLER)
    // ====================================================
    Route::controller(ReportController::class)->group(function () {

        // A. Laporan Harian & Detail
        Route::get('/riwayat-driver', 'riwayatDriver')->name('admin.riwayat_driver');
        
        // --- [BARU] ROUTE KOREKSI KM (Modal Popup) ---
        Route::put('/attendance/{id}/update-km', 'updateKm')->name('admin.attendance.updateKm');

        Route::get('admin/report/driver/export', 'exportRiwayatDriver')->name('admin.riwayat_driver.export');
        Route::get('/riwayat-unit', 'riwayatUnit')->name('admin.riwayat_unit');
        Route::get('/laporan-darurat', 'laporanDarurat')->name('admin.laporan_darurat');
        Route::get('/rekap-harian', 'rekapHarian')->name('admin.rekap_harian');

        // B. Rekap Bulanan
        Route::get('/rekap-bulanan', 'rekapBulanan')->name('admin.rekap_bulanan');

        // C. Export Excel Bulanan
        Route::get('/rekap-bulanan/export-checklist', 'exportBulananChecklist')->name('admin.rekap_bulanan.export_checklist');

        // D. Manajemen Project
        Route::resource('/project', ProjectController::class)
            ->except(['create', 'edit', 'show'])
            ->names('admin.project');
    });

    // ====================================================
    // 3. MANAJEMEN ASET & MAINTENANCE (MAINTENANCE CONTROLLER)
    // ====================================================
    Route::controller(MaintenanceController::class)->group(function () {
        // Dashboard Khusus Maintenance
        Route::get('/maintenance-dashboard', 'index')->name('admin.maintenance.dashboard');

        // Kalender Maintenance
        Route::get('/maintenance-calendar', 'calendar')->name('admin.maintenance');
        Route::get('/api/maintenance-events', 'getEvents')->name('api.maintenance.events');

        // --- CRUD ASET ---
        Route::get('/daftar-aset', 'daftarAset')->name('admin.daftar_aset');

        // CRUD: Create, Edit, Update, Delete
        Route::get('/aset/tambah', 'create')->name('admin.aset.create');
        Route::post('/aset/simpan', 'store')->name('admin.aset.store');
        Route::get('/aset/{vehicle}/edit', 'edit')->name('admin.aset.edit');
        Route::put('/aset/{vehicle}/update', 'update')->name('admin.aset.update');
        Route::delete('/aset/{vehicle}/hapus', 'destroy')->name('admin.aset.destroy');

        // Fitur Servis & Visual Check
        Route::post('/daftar-aset/{vehicle}/catat-servis', 'catatServis')->name('admin.aset.catatServis');
        Route::get('/aset/{vehicle}/visual-check', 'visualCheck')->name('admin.aset.visual');
        Route::post('/aset/{vehicle}/resolve-issue', 'resolveIssue')->name('admin.aset.resolveIssue');
        Route::get('/aset/{vehicle}/riwayat-servis', 'riwayatServis')->name('admin.aset.riwayat');

        // Export Khusus Maintenance
        Route::get('/aset/riwayat/{id}/export', 'exportExcel')->name('admin.aset.export_excel');

        // Export Rekap Absensi (Versi Detail/Harian)
        Route::get('/absensi/rekap-export', 'exportRekapAbsensi')->name('admin.absensi.export_rekap');

        // === PREVENTIVE MAINTENANCE (NEW) ===
        Route::get('/maintenance/components/{vehicle}', 'components')->name('admin.maintenance.components');
        Route::post('/maintenance/components/{vehicle}/store', 'storeComponent')->name('admin.maintenance.components.store');
        Route::put('/maintenance/components/{component}/update', 'updateComponent')->name('admin.maintenance.components.update');
        Route::delete('/maintenance/components/{component}/delete', 'deleteComponent')->name('admin.maintenance.components.delete');
        
        Route::get('/maintenance/alerts', 'alerts')->name('admin.maintenance.alerts');
        Route::post('/maintenance/alerts/{alert}/acknowledge', 'acknowledgeAlert')->name('admin.maintenance.alerts.acknowledge');
        Route::post('/maintenance/alerts/{alert}/resolve', 'resolveAlert')->name('admin.maintenance.alerts.resolve');
        
        Route::get('/maintenance/schedules', 'schedules')->name('admin.maintenance.schedules');
        Route::post('/maintenance/schedules/store', 'storeSchedule')->name('admin.maintenance.schedules.store');
        Route::post('/maintenance/schedules/{schedule}/complete', 'completeSchedule')->name('admin.maintenance.schedules.complete');
        
        // API Helper untuk AJAX
        Route::get('/api/vehicles/{vehicle}/components', 'getVehicleComponents')->name('admin.api.vehicle.components');
        
        // === EXPORT EXCEL ROUTES ===
        Route::get('/maintenance/export/dashboard', 'exportDashboard')->name('admin.maintenance.export.dashboard');
        Route::get('/maintenance/export/schedules', 'exportSchedules')->name('admin.maintenance.export.schedules');
        Route::get('/maintenance/export/alerts', 'exportAlerts')->name('admin.maintenance.export.alerts');
    });

    // ====================================================
    // 4. MASTER DATA PENGGUNA
    // ====================================================
    Route::resource('/driver', DriverController::class)->except(['show'])->names('admin.driver');
    Route::resource('/pengguna', PenggunaController::class)->except(['show'])->names('admin.pengguna');

});

/*
|--------------------------------------------------------------------------
| D. DARURAT: BYPASS STORAGE LINK
|--------------------------------------------------------------------------
*/
Route::get('/storage/photos/{filename}', function ($filename) {
    $path = storage_path('app/public/photos/' . $filename);
    if (!file_exists($path)) {
        abort(404);
    }
    $file = file_get_contents($path);
    $type = mime_content_type($path);
    $response = Response::make($file, 200);
    $response->header("Content-Type", $type);
    return $response;
});