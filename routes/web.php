<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AdminDashboardController;
use App\Http\Controllers\Auth\AdminLoginController;
use App\Http\Controllers\PenggunaController;
use App\Http\Controllers\DriverController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| File ini mendaftarkan rute untuk aplikasi berbasis Browser (Admin Panel).
| Semua rute di sini menggunakan middleware 'web' yang menyediakan fitur
| Session state, CSRF protection, dan Cookie encryption.
|
*/

// --- 1. OTENTIKASI ADMIN ---
// Rute untuk Login dan Logout Administrator.
Route::get('/admin/login', [AdminLoginController::class, 'showLoginForm'])->name('admin.login');
Route::post('/admin/login', [AdminLoginController::class, 'login'])->name('admin.login.submit');
Route::post('/admin/logout', [AdminLoginController::class, 'logout'])->name('admin.logout');


// --- 2. DASHBOARD & MANAJEMEN (PROTECTED) ---
// Middleware 'auth': Memastikan hanya admin yang sudah login yang bisa akses.
// Prefix 'admin': Semua URL akan diawali dengan /admin (cth: /admin/dashboard).
Route::middleware(['auth'])->prefix('admin')->group(function () {

    // A. DASHBOARD UTAMA
    Route::get('/dashboard', [AdminDashboardController::class, 'dashboard'])->name('admin.dashboard');

    // API Internal untuk Live Update Dashboard (AJAX)
    // Dipanggil oleh JavaScript setiap 30 detik untuk update angka tanpa reload.
    Route::get('/dashboard/status', [AdminDashboardController::class, 'getDashboardStatus'])->name('admin.dashboard.status');

    // B. FITUR MONITORING
    Route::get('/riwayat-driver', [AdminDashboardController::class, 'riwayatDriver'])->name('admin.riwayat_driver');
    Route::get('/laporan-darurat', [AdminDashboardController::class, 'laporanDarurat'])->name('admin.laporan_darurat');
    Route::get('/riwayat-unit', [AdminDashboardController::class, 'riwayatUnit'])->name('admin.riwayat_unit');

    // C. MANAJEMEN MASTER DATA (CRUD)
    // Resource controller otomatis membuat rute: index, create, store, edit, update, destroy.
    Route::resource('/driver', DriverController::class)
        ->except(['show']) // Kita tidak butuh halaman detail driver (show)
        ->names('admin.driver');

    Route::resource('/pengguna', PenggunaController::class)
        ->except(['show'])
        ->names('admin.pengguna');

    // D. MANAJEMEN ASET KENDARAAN
    Route::get('/daftar-aset', [AdminDashboardController::class, 'daftarAset'])->name('admin.daftar_aset');
    Route::post('/daftar-aset/{vehicle}/catat-servis', [AdminDashboardController::class, 'catatServis'])->name('admin.aset.catatServis');
    Route::get('/aset/{vehicle}/edit', [AdminDashboardController::class, 'editAset'])->name('admin.aset.edit');
    Route::put('/aset/{vehicle}/update', [AdminDashboardController::class, 'updateAset'])->name('admin.aset.update');
    Route::get('/aset/{vehicle}/visual-check', [AdminDashboardController::class, 'visualCheck'])->name('admin.aset.visual');
    Route::post('/aset/{vehicle}/resolve-issue', [AdminDashboardController::class, 'resolveIssue'])->name('admin.aset.resolveIssue');
    // Di dalam grup 'admin'
    Route::get('/maintenance-dashboard', [AdminDashboardController::class, 'maintenanceDashboard'])->name('admin.maintenance.dashboard');
    // E. LAPORAN & REKAP
    Route::get('/rekap-harian', [AdminDashboardController::class, 'rekapHarian'])->name('admin.rekap_harian');
    Route::get('/rekap-bulanan', [AdminDashboardController::class, 'rekapBulanan'])->name('admin.rekap_bulanan');
    // Fitur Export Excel (Checklist Kehadiran)
    Route::get('/rekap-bulanan/export-checklist', [AdminDashboardController::class, 'exportBulananChecklist'])->name('admin.rekap_bulanan.export_checklist');

    // F. KALENDER MAINTENANCE
    Route::get('/maintenance-calendar', [AdminDashboardController::class, 'maintenanceCalendar'])->name('admin.maintenance');
    // API Internal untuk data event kalender (JSON)
    Route::get('/api/maintenance-events', [AdminDashboardController::class, 'getMaintenanceEvents'])->name('api.maintenance.events');
});


// --- 3. HALAMAN DEFAULT ---
// Jika user membuka root url (/), langsung arahkan ke dashboard admin.
Route::get('/', function () {
    return redirect()->route('admin.dashboard');
});