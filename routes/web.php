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
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\ServiceReportController;
use App\Http\Controllers\CustomerApprovalController;
use App\Http\Controllers\TransportCostAdminController;

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
    Route::post('/login', [AdminLoginController::class, 'login'])
        ->middleware('throttle:5,1')
        ->name('admin.login.submit');
    Route::post('/logout', [AdminLoginController::class, 'logout'])->name('admin.logout');
});

/*
|--------------------------------------------------------------------------
| C. ADMIN PANEL (DILINDUNGI MIDDLEWARE)
| SECURITY FIX: Added rate limiting to prevent brute force and DoS attacks
| SECURITY FIX: Added role middleware to prevent unauthorized access
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'role:master,service_admin', 'throttle:60,1'])->prefix('admin')->group(function () {

    // ====================================================
    // 1. DASHBOARD UTAMA
    // ====================================================
    Route::controller(DashboardController::class)->group(function () {
        Route::get('/dashboard', 'index')->name('admin.dashboard');
        Route::get('/dashboard/status', 'getStatus')->name('admin.dashboard.status');
    });

    // ====================================================
    // 2. MONITORING & LAPORAN (REPORT CONTROLLER)
    // SECURITY FIX: Rate limiting for data modification
    // ====================================================
    Route::controller(ReportController::class)->group(function () {

        // A. Laporan Harian & Detail
        Route::get('/riwayat-driver', 'riwayatDriver')->name('admin.riwayat_driver');
        
        // --- [BARU] ROUTE KOREKSI KM (Modal Popup) - Rate limited ---
        Route::middleware('throttle:30,1')->group(function () {
            Route::put('/attendance/{id}/update-km', 'updateKm')->name('admin.attendance.updateKm');
        });

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

        // E. Manajemen Customer
        Route::resource('/customer', CustomerController::class)
            ->except(['create', 'edit', 'show'])
            ->names('admin.customer');
    });

    // ====================================================
    // 3. MANAJEMEN ASET & MAINTENANCE (MAINTENANCE CONTROLLER)
    // SECURITY FIX: Stricter rate limiting for destructive actions
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
        
        // DESTRUCTIVE ACTION: Stricter rate limit (10 requests/minute)
        Route::middleware('throttle:10,1')->group(function () {
            Route::delete('/aset/{vehicle}/hapus', 'destroy')->name('admin.aset.destroy');
        });

        // Fitur Servis & Visual Check
        Route::middleware('throttle:30,1')->group(function () {
            Route::post('/daftar-aset/{vehicle}/catat-servis', 'catatServis')->name('admin.aset.catatServis');
        });
        
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
        Route::post('/maintenance/alerts/generate', 'generateAlerts')->name('admin.maintenance.alerts.generate');
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
        
        // === TEST DESIGN SYSTEM (TEMPORARY) ===
        Route::get('/maintenance/test-design-system', function () {
            return view('admin.maintenance.test-design-system');
        })->name('admin.maintenance.test-design-system');
    });

    // ====================================================
    // 4. MASTER DATA PENGGUNA
    // SECURITY FIX: Stricter rate limiting for destructive actions
    // ====================================================
    Route::middleware('throttle:10,1')->group(function () {
        Route::delete('/driver/{driver}', [DriverController::class, 'destroy'])->name('admin.driver.destroy');
        Route::delete('/pengguna/{pengguna}', [PenggunaController::class, 'destroy'])->name('admin.pengguna.destroy');
    });
    
    Route::get('/driver/dokumen/{id}/{jenis}', [DriverController::class, 'lihatDokumen'])->name('admin.driver.dokumen');
    Route::resource('/driver', DriverController::class)->except(['show', 'destroy'])->names('admin.driver');
    Route::resource('/pengguna', PenggunaController::class)->except(['show', 'destroy'])->names('admin.pengguna');

    // ====================================================
    // 5. SERVICE DARURAT (Master & Service Admin)
    // ====================================================
    Route::middleware('role:master,service_admin')->prefix('service')->controller(ServiceReportController::class)->group(function () {
        Route::get('/', 'index')->name('admin.service.index');
        // SECURITY FIX: Rute statis HARUS dideklarasikan SEBELUM rute {id} wildcard
        Route::get('/customer-approvals/list', 'customerApprovalsView')->name('admin.service.customer_approvals');
        Route::get('/{id}', 'show')->name('admin.service.show');
        Route::middleware('throttle:30,1')->post('/{id}/approve', 'approve')->name('admin.service.approve');
        Route::middleware('throttle:10,1')->post('/{id}/reject', 'reject')->name('admin.service.reject');
        Route::get('/{id}/export-finance', 'exportFinance')->name('admin.service.export_finance');
    });

    // ====================================================
    // 6. TRANSPORT COST MONITORING (Uang Jalan)
    // ====================================================
    Route::prefix('transport-costs')->controller(TransportCostAdminController::class)->group(function () {
        // Dashboard
        Route::get('/dashboard', 'dashboard')->name('admin.transport-costs.dashboard');
        
        // Trip Entry List & Detail
        Route::get('/', 'index')->name('admin.transport-costs.index');
        Route::get('/{id}', 'show')->name('admin.transport-costs.show');
        
        // Approval Actions (stricter rate limit)
        Route::middleware('throttle:10,1')->group(function () {
            Route::post('/{id}/approve', 'approve')->name('admin.transport-costs.approve');
            Route::post('/{id}/reject', 'reject')->name('admin.transport-costs.reject');
        });
        
        // Monthly Recap
        Route::get('/recap/monthly', 'recap')->name('admin.transport-costs.recap');
        
        // Finance Actions
        Route::post('/{id}/submit-to-finance', 'submitToFinance')->name('admin.transport-costs.submit_to_finance');
        Route::post('/bulk-submit-to-finance', 'bulkSubmitToFinance')->name('admin.transport-costs.bulk_submit_to_finance');
        Route::get('/{id}/export-finance', 'exportFinance')->name('admin.transport-costs.export_finance');
        Route::get('/recap/export-finance', 'exportFinanceRecap')->name('admin.transport-costs.export_finance_recap');
    });

});

/*
|--------------------------------------------------------------------------
| E. CUSTOMER PORTAL (Customer Role Only)
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'role:customer', 'throttle:60,1'])->prefix('customer')->group(function () {
    // 1. Dashboard
    Route::get('/dashboard', [\App\Http\Controllers\CustomerDashboardController::class, 'index'])->name('customer.dashboard');

    // 2. Unit Kendaraan (with customer.vehicle protection for detail pages)
    Route::controller(\App\Http\Controllers\CustomerVehicleController::class)->group(function () {
        Route::get('/vehicles', 'index')->name('customer.vehicles');
        
        Route::middleware('customer.vehicle')->group(function () {
            Route::get('/vehicles/{vehicle}', 'show')->name('customer.vehicles.show');
            Route::get('/vehicles/{vehicle}/certificate', 'certificate')->name('customer.vehicles.certificate');
        });
    });

    // 3. Service Approvals
    Route::controller(\App\Http\Controllers\CustomerApprovalController::class)->group(function () {
        Route::get('/approve', 'index')->name('customer.approve.index');
        Route::get('/approve/{id}', 'show')->name('customer.approve.show');
        Route::get('/approve/{id}/download', 'downloadApprovalDoc')->name('customer.approve.download');
        Route::middleware('throttle:10,1')->post('/approve/{id}/upload', 'uploadSignedDocument')->name('customer.approve.upload');
    });

    // 4. Account & Profile
    Route::controller(\App\Http\Controllers\CustomerProfileController::class)->group(function () {
        Route::get('/profile', 'showProfile')->name('customer.profile');
        Route::get('/profile/change-password', 'showChangePasswordForm')->name('customer.password.form');
        Route::post('/profile/change-password', 'changePassword')->name('customer.password.update');
    });

    // 5. Informasi & Kebijakan
    Route::get('/about', fn() => view('customer.about'))->name('customer.about');
    Route::get('/privacy', fn() => view('customer.privacy'))->name('customer.privacy');
});

/*
|--------------------------------------------------------------------------
| D. SECURE FILE STORAGE ACCESS
| SECURITY FIX: Added authentication, path traversal prevention, and file type validation
|--------------------------------------------------------------------------
*/
Route::middleware(['auth'])->get('/storage/photos/{filename}', function ($filename) {
    // 1. Sanitize filename - prevent path traversal attacks
    $filename = basename($filename);
    
    // 2. Whitelist allowed extensions
    $allowedExtensions = ['jpg', 'jpeg', 'png'];
    $extension = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
    
    if (!in_array($extension, $allowedExtensions)) {
        abort(403, 'File type not allowed');
    }
    
    // 3. Build safe path
    $path = storage_path('app/photos/' . $filename);
    
    // 4. Verify file is within allowed directory (prevent path traversal)
    $realPath = realpath($path);
    $allowedPath = realpath(storage_path('app/photos'));
    
    if (!$realPath || strpos($realPath, $allowedPath) !== 0) {
        abort(403, 'Access denied');
    }
    
    if (!file_exists($realPath)) {
        abort(404);
    }
    
    // 5. Return file securely
    return response()->file($realPath);
})->where('filename', '.*');

// Secure Route for Receipts
// SECURITY FIX: Added file type validation matching photos route pattern
Route::middleware(['auth'])->get('/storage/receipts/{filename}', function ($filename) {
    // 1. Sanitize filename - prevent path traversal
    $filename = basename($filename);
    
    // 2. Whitelist allowed extensions
    $allowedExtensions = ['jpg', 'jpeg', 'png', 'pdf'];
    $extension = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
    
    if (!in_array($extension, $allowedExtensions)) {
        abort(403, 'File type not allowed');
    }
    
    // 3. Build safe path
    $path = storage_path('app/receipts/' . $filename);
    
    // 4. Verify file is within allowed directory
    $realPath = realpath($path);
    $allowedPath = realpath(storage_path('app/receipts'));
    
    if (!$realPath || strpos($realPath, $allowedPath) !== 0) {
        abort(403, 'Access denied');
    }
    
    if (!file_exists($realPath)) {
        abort(404, 'File not found');
    }
    
    // 5. Return file securely
    return response()->file($realPath);
})->where('filename', '.*');