<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Artisan;

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
use App\Http\Controllers\MaintenanceComponentController;
use App\Http\Controllers\MaintenanceAlertController;
use App\Http\Controllers\MaintenanceScheduleController;
use App\Http\Controllers\MaintenanceAssetController;
use App\Http\Controllers\PanduanController;
use App\Http\Controllers\VehicleReplacementController;
use App\Http\Controllers\AdminProfileController;

// Root redirect
Route::get('/', function () {
    return redirect()->route('admin.login');
});

// Authentication
Route::prefix('admin')->group(function () {
    Route::get('/login', [AdminLoginController::class, 'showLoginForm'])->name('admin.login');
    Route::post('/login', [AdminLoginController::class, 'login'])
        ->middleware('throttle:5,1')
        ->name('admin.login.submit');
    Route::post('/logout', [AdminLoginController::class, 'logout'])->name('admin.logout');
});

// Admin Panel (auth + RBAC + rate limiting)
Route::middleware(['auth', 'role:master,service_admin', 'throttle:60,1'])->prefix('admin')->group(function () {

    // Dashboard
    Route::controller(DashboardController::class)->group(function () {
        Route::get('/dashboard', 'index')->name('admin.dashboard');
        Route::get('/dashboard/status', 'getStatus')->name('admin.dashboard.status');
    });

    // Admin Profile
    Route::controller(AdminProfileController::class)->group(function () {
        Route::get('/profile/change-password', 'showChangePasswordForm')->name('admin.password.form');
        Route::post('/profile/change-password', 'changePassword')->name('admin.password.update');
    });

    // Reports & Monitoring
    Route::controller(ReportController::class)->group(function () {

        Route::get('/riwayat-driver', 'riwayatDriver')->name('admin.riwayat_driver');
        
        // KM correction (rate limited)
        Route::middleware('throttle:30,1')->group(function () {
            Route::put('/attendance/{id}/update-km', 'updateKm')->name('admin.attendance.updateKm');
        });

        Route::get('/report/driver/export', 'exportRiwayatDriver')->name('admin.riwayat_driver.export');
        Route::get('/riwayat-unit', 'riwayatUnit')->name('admin.riwayat_unit');
        Route::get('/laporan-darurat', 'laporanDarurat')->name('admin.laporan_darurat');
        Route::middleware('throttle:30,1')->group(function () {
            Route::post('/laporan-darurat/{emergencyReport}/create-service', 'createServiceFromEmergency')->name('admin.laporan_darurat.create_service');
            Route::post('/laporan-darurat/{emergencyReport}/resolve-info', 'resolveEmergencyInfo')->name('admin.laporan_darurat.resolve_info');
        });
        Route::get('/rekap-harian', 'rekapHarian')->name('admin.rekap_harian');

        // Monthly recap
        Route::get('/rekap-bulanan', 'rekapBulanan')->name('admin.rekap_bulanan');

        // Export
        Route::get('/rekap-bulanan/export-checklist', 'exportBulananChecklist')->name('admin.rekap_bulanan.export_checklist');

        // Projects
        Route::resource('/project', ProjectController::class)
            ->except(['create', 'edit', 'show'])
            ->names('admin.project');

        // Customers
        Route::resource('/customer', CustomerController::class)
            ->except(['create', 'edit', 'show'])
            ->names('admin.customer');
    });

    // Asset & Maintenance Management
    Route::controller(MaintenanceController::class)->group(function () {
        Route::get('/maintenance-dashboard', 'index')->name('admin.maintenance.dashboard');

        Route::get('/maintenance-calendar', 'calendar')->name('admin.maintenance');
        Route::get('/api/maintenance-events', 'getEvents')->name('api.maintenance.events');

        Route::get('/absensi/rekap-export', 'exportRekapAbsensi')->name('admin.absensi.export_rekap');

        Route::get('/maintenance/export/dashboard', 'exportDashboard')->name('admin.maintenance.export.dashboard');
    });

    Route::controller(MaintenanceAssetController::class)->group(function () {
        // Asset CRUD
        Route::get('/daftar-aset', 'daftarAset')->name('admin.daftar_aset');
        Route::get('/aset/tambah', 'create')->name('admin.aset.create');
        Route::post('/aset/simpan', 'store')->name('admin.aset.store');
        Route::get('/aset/{vehicle}/edit', 'edit')->name('admin.aset.edit');
        Route::put('/aset/{vehicle}/update', 'update')->name('admin.aset.update');
        Route::post('/aset/{vehicle}/verify-temporary', 'verifyTemporaryUnit')->name('admin.aset.verify_temporary');
        
        // Destructive action: stricter rate limit
        Route::middleware('throttle:10,1')->group(function () {
            Route::delete('/aset/{vehicle}/hapus', 'destroy')->name('admin.aset.destroy');
        });

        // Service & Visual Check
        Route::middleware('throttle:30,1')->group(function () {
            Route::post('/daftar-aset/{vehicle}/catat-servis', 'catatServis')->name('admin.aset.catatServis');
        });
        
        Route::get('/aset/{vehicle}/visual-check', 'visualCheck')->name('admin.aset.visual');
        Route::post('/aset/{vehicle}/resolve-issue', 'resolveIssue')->name('admin.aset.resolveIssue');
        Route::get('/aset/{vehicle}/riwayat-servis', 'riwayatServis')->name('admin.aset.riwayat');

        // Export
        Route::get('/aset/riwayat/{id}/export', 'exportExcel')->name('admin.aset.export_excel');

        // Preventive Maintenance
        // Components
        Route::controller(MaintenanceComponentController::class)->group(function () {
            Route::get('/maintenance/components/{vehicle}', 'index')->name('admin.maintenance.components');
            Route::post('/maintenance/components/{vehicle}/store', 'store')->name('admin.maintenance.components.store');
            Route::put('/maintenance/components/{component}/update', 'update')->name('admin.maintenance.components.update');
            Route::delete('/maintenance/components/{component}/delete', 'destroy')->name('admin.maintenance.components.delete');
            Route::get('/api/vehicles/{vehicle}/components', 'apiGetVehicleComponents')->name('admin.api.vehicle.components');
        });
        
        // Alert routes
        Route::controller(MaintenanceAlertController::class)->group(function () {
            Route::get('/maintenance/alerts', 'index')->name('admin.maintenance.alerts');
            Route::post('/maintenance/alerts/generate', 'generate')->name('admin.maintenance.alerts.generate');
            Route::post('/maintenance/alerts/{alert}/acknowledge', 'acknowledge')->name('admin.maintenance.alerts.acknowledge');
            Route::post('/maintenance/alerts/{alert}/resolve', 'resolve')->name('admin.maintenance.alerts.resolve');
            Route::get('/maintenance/export/alerts', 'export')->name('admin.maintenance.export.alerts');
        });
        
        // Schedule routes
        Route::controller(MaintenanceScheduleController::class)->group(function () {
            Route::get('/maintenance/schedules', 'index')->name('admin.maintenance.schedules');
            Route::post('/maintenance/schedules/refresh', 'refresh')->name('admin.maintenance.schedules.refresh');
            Route::post('/maintenance/schedules/store', 'store')->name('admin.maintenance.schedules.store');
            Route::post('/maintenance/schedules/{schedule}/complete', 'complete')->name('admin.maintenance.schedules.complete');
            Route::get('/maintenance/export/schedules', 'export')->name('admin.maintenance.export.schedules');
            Route::get('/maintenance/schedules/{schedule}/finance-pdf/preview', 'previewFinancePdf')->name('admin.maintenance.schedules.finance_pdf.preview');
            Route::get('/maintenance/schedules/{schedule}/finance-pdf/download', 'downloadFinancePdf')->name('admin.maintenance.schedules.finance_pdf.download');
        });
        

    });

    // User Management (stricter rate limit for deletes)
    Route::middleware('throttle:10,1')->group(function () {
        Route::delete('/driver/{driver}', [DriverController::class, 'destroy'])->name('admin.driver.destroy');
        Route::delete('/pengguna/{pengguna}', [PenggunaController::class, 'destroy'])->name('admin.pengguna.destroy');
    });
    
    Route::get('/driver/dokumen/{id}/{jenis}', [DriverController::class, 'lihatDokumen'])->name('admin.driver.dokumen');
    Route::resource('/driver', DriverController::class)->except(['show', 'destroy'])->names('admin.driver');
    Route::resource('/pengguna', PenggunaController::class)->except(['show', 'destroy'])->names('admin.pengguna');

    // QR Code Management
    Route::controller(\App\Http\Controllers\QRCodeAdminController::class)->group(function () {
        Route::get('/drivers/{driver}/qrcode/download', 'downloadDriverQRCode')->name('admin.drivers.qrcode.download');
        Route::get('/drivers/{driver}/qr-print', 'printDriverQRCode')->name('admin.drivers.qr-print');
        Route::post('/drivers/{driver}/qrcode/regenerate', 'regenerateDriverQRCode')->name('admin.drivers.qrcode.regenerate');

        Route::get('/vehicles/{vehicle}/qrcode/download', 'downloadVehicleQRCode')->name('admin.vehicles.qrcode.download');
        Route::get('/vehicles/{vehicle}/qr-print', 'printVehicleQRCode')->name('admin.vehicles.qr-print');
        Route::post('/vehicles/{vehicle}/qrcode/regenerate', 'regenerateVehicleQRCode')->name('admin.vehicles.qrcode.regenerate');
    });

    // Service Reports
    Route::middleware('role:master,service_admin')->prefix('service')->controller(ServiceReportController::class)->group(function () {
        Route::get('/', 'index')->name('admin.service.index');
        // Static routes must be declared before {id} wildcard
        Route::get('/customer-approvals/list', 'customerApprovalsView')->name('admin.service.customer_approvals');
        Route::get('/manual/create', 'createManual')->name('admin.service.create');
        Route::middleware('throttle:30,1')->post('/manual', 'storeManual')->name('admin.service.store_manual');
        Route::get('/{id}', 'show')->name('admin.service.show');
        Route::middleware('throttle:30,1')->post('/{id}/approve', 'approve')->name('admin.service.approve');
        Route::middleware('throttle:10,1')->post('/{id}/reject', 'reject')->name('admin.service.reject');
        Route::middleware('throttle:30,1')->post('/{id}/complete-by-admin', 'completeByAdmin')->name('admin.service.complete_by_admin');
        Route::get('/{id}/customer-pdf/preview', 'previewCustomerPdf')->name('admin.service.customer_pdf.preview');
        Route::get('/{id}/customer-pdf/download', 'downloadCustomerPdf')->name('admin.service.customer_pdf.download');
        Route::get('/{id}/internal-pdf/preview', 'previewAdminInternalPdf')->name('admin.service.internal_pdf.preview');
        Route::get('/{id}/internal-pdf/download', 'downloadAdminInternalPdf')->name('admin.service.internal_pdf.download');
        Route::get('/{id}/finance-pdf/preview', 'previewFinancePdf')->name('admin.service.finance_pdf.preview');
        Route::get('/{id}/finance-pdf/download', 'downloadFinancePdf')->name('admin.service.finance_pdf.download');
    });

    // Transport Cost Monitoring
    Route::prefix('transport-costs')->controller(TransportCostAdminController::class)->group(function () {
        Route::get('/dashboard', 'dashboard')->name('admin.transport-costs.dashboard');
        
        Route::get('/', 'index')->name('admin.transport-costs.index');
        Route::get('/{id}', 'show')->name('admin.transport-costs.show');
        
        // Approvals (stricter rate limit)
        Route::middleware('throttle:10,1')->group(function () {
            Route::post('/{id}/approve', 'approve')->name('admin.transport-costs.approve');
            Route::post('/{id}/reject', 'reject')->name('admin.transport-costs.reject');
        });
        
        // Monthly recap & export
        Route::get('/recap/monthly', 'recap')->name('admin.transport-costs.recap');
        Route::get('/recap/export-finance', 'exportFinanceRecap')->name('admin.transport-costs.export_finance_recap');
        
        // Finance
        Route::post('/{id}/submit-to-finance', 'submitToFinance')->name('admin.transport-costs.submit_to_finance');
        Route::post('/bulk-submit-to-finance', 'bulkSubmitToFinance')->name('admin.transport-costs.bulk_submit_to_finance');
        Route::get('/{id}/export-finance', 'exportFinance')->name('admin.transport-costs.export_finance');
    });

    // Vehicle Replacements
    Route::controller(VehicleReplacementController::class)->prefix('vehicle-replacements')->name('admin.vehicle_replacements.')->group(function() {
        Route::get('/', 'index')->name('index');
        Route::get('/create', 'create')->name('create');
        Route::post('/', 'store')->name('store');
        Route::get('/{vehicleReplacement}/edit', 'edit')->name('edit');
        Route::put('/{vehicleReplacement}', 'update')->name('update');
        Route::post('/{vehicleReplacement}/complete', 'complete')->name('complete');
        Route::post('/{vehicleReplacement}/cancel', 'cancel')->name('cancel');
    });

    // Panduan
    Route::get('/panduan', [PanduanController::class, 'admin'])->name('admin.panduan');

});

// Customer Portal
Route::middleware(['auth', 'role:customer', 'throttle:60,1'])->prefix('customer')->group(function () {
    Route::get('/dashboard', [\App\Http\Controllers\CustomerDashboardController::class, 'index'])->name('customer.dashboard');

    // Vehicles
    Route::controller(\App\Http\Controllers\CustomerVehicleController::class)->group(function () {
        Route::get('/vehicles', 'index')->name('customer.vehicles');
        
        Route::middleware('customer.vehicle')->group(function () {
            Route::get('/vehicles/{vehicle}', 'show')->name('customer.vehicles.show');
            Route::get('/vehicles/{vehicle}/certificate', 'certificate')->name('customer.vehicles.certificate');
        });
    });

    // Service Approvals
    Route::controller(\App\Http\Controllers\CustomerApprovalController::class)->group(function () {
        Route::get('/approve', 'index')->name('customer.approve.index');
        Route::get('/approve/{id}', 'show')->name('customer.approve.show');
        Route::get('/approve/{id}/preview-pdf', 'previewApprovalPdf')->name('customer.approve.preview_pdf');
        Route::get('/approve/{id}/download', 'downloadApprovalDoc')->name('customer.approve.download');
        Route::get('/approve/{id}/sign', 'sign')->name('customer.approve.sign');
        Route::get('/approve/{id}/success', 'success')->name('customer.approve.success');
        Route::middleware('throttle:30,1')->post('/approve/{id}/upload', 'uploadSignedDocument')->name('customer.approve.upload');
        Route::middleware('throttle:30,1')->post('/approve/{id}/reject', 'reject')->name('customer.approve.reject');
        Route::middleware('throttle:30,1')->post('/approve/{id}/clarify', 'clarify')->name('customer.approve.clarify');
    });

    // Profile
    Route::controller(\App\Http\Controllers\CustomerProfileController::class)->group(function () {
        Route::get('/profile', 'showProfile')->name('customer.profile');
        Route::get('/profile/change-password', 'showChangePasswordForm')->name('customer.password.form');
        Route::post('/profile/change-password', 'changePassword')->name('customer.password.update');
    });

    // Info pages
    Route::get('/about', fn() => view('customer.about'))->name('customer.about');
    Route::get('/privacy', fn() => view('customer.privacy'))->name('customer.privacy');

    Route::get('/panduan', [PanduanController::class, 'customer'])->name('customer.panduan');
});

// Secure File Storage Access (auth + RBAC + path traversal prevention)
Route::middleware(['auth', 'role:master,service_admin,driver'])->get('/storage/photos/{filename}', function ($filename) {
    $filename = basename($filename);
    
    // Whitelist allowed extensions
    $allowedExtensions = ['jpg', 'jpeg', 'png'];
    $extension = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
    
    if (!in_array($extension, $allowedExtensions)) {
        \Illuminate\Support\Facades\Log::warning('Unauthorized file type access attempt', [
            'user_id' => auth()->id(),
            'filename' => $filename,
            'extension' => $extension,
            'ip' => request()->ip(),
        ]);
        abort(404, 'File type not allowed');
    }
    
    // Build safe path
    $path = storage_path('app/photos/' . $filename);
    
    // Verify file is within allowed directory
    $realPath = realpath($path);
    $allowedPath = realpath(storage_path('app/photos'));
    
    if (!$realPath || strpos($realPath, $allowedPath) !== 0) {
        \Illuminate\Support\Facades\Log::error('Path traversal attempt detected in photos', [
            'user_id' => auth()->id(),
            'requested_file' => $filename,
            'resolved_path' => $realPath,
            'ip' => request()->ip(),
        ]);
        abort(404, 'Access denied');
    }
    
    if (!file_exists($realPath)) {
        abort(404);
    }
    
    // Audit log
    \Illuminate\Support\Facades\Log::info('Photo accessed', [
        'user_id' => auth()->id(),
        'user_role' => auth()->user()->role,
        'filename' => $filename,
        'ip' => request()->ip(),
    ]);
    
    // 6. Return file securely
    return response()->file($realPath);
})->where('filename', '.*');

// Secure Route for Receipts
Route::middleware(['auth', 'role:master,service_admin,driver'])->get('/storage/receipts/{filename}', function ($filename) {
    $filename = basename($filename);
    
    // Whitelist allowed extensions
    $allowedExtensions = ['jpg', 'jpeg', 'png', 'pdf'];
    $extension = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
    
    if (!in_array($extension, $allowedExtensions)) {
        \Illuminate\Support\Facades\Log::warning('Unauthorized receipt file type access', [
            'user_id' => auth()->id(),
            'filename' => $filename,
            'extension' => $extension,
            'ip' => request()->ip(),
        ]);
        abort(404, 'File type not allowed');
    }
    
    // Build safe path
    $path = storage_path('app/receipts/' . $filename);
    
    // Verify file is within allowed directory
    $realPath = realpath($path);
    $allowedPath = realpath(storage_path('app/receipts'));
    
    if (!$realPath || strpos($realPath, $allowedPath) !== 0) {
        \Illuminate\Support\Facades\Log::error('Path traversal attempt detected in receipts', [
            'user_id' => auth()->id(),
            'requested_file' => $filename,
            'resolved_path' => $realPath,
            'ip' => request()->ip(),
        ]);
        abort(404, 'Access denied');
    }
    
    if (!file_exists($realPath)) {
        abort(404, 'File not found');
    }
    
    // Audit log
    \Illuminate\Support\Facades\Log::info('Receipt accessed', [
        'user_id' => auth()->id(),
        'user_role' => auth()->user()->role,
        'filename' => $filename,
        'ip' => request()->ip(),
    ]);
    
    return response()->file($realPath);
})->where('filename', '.*');
