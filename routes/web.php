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
*/

// --- RUTE LOGIN ADMIN ---
Route::get('/admin/login', [AdminLoginController::class, 'showLoginForm'])->name('admin.login');
Route::post('/admin/login', [AdminLoginController::class, 'login'])->name('admin.login.submit');
Route::post('/admin/logout', [AdminLoginController::class, 'logout'])->name('admin.logout');


// --- RUTE ADMIN DASHBOARD YANG AMAN ---
Route::middleware(['auth'])->prefix('admin')->group(function () {

    Route::get('/dashboard', [AdminDashboardController::class, 'dashboard'])->name('admin.dashboard');
    Route::get('/riwayat-driver', [AdminDashboardController::class, 'riwayatDriver'])->name('admin.riwayat_driver');

    // --- TAMBAHKAN RUTE BARU DI SINI UNTUK REAL-TIME DATA ---
    Route::get('/dashboard/status', [AdminDashboardController::class, 'getDashboardStatus'])->name('admin.dashboard.status');
    // --- AKHIR TAMBAHAN ---

    Route::get('/laporan-darurat', [AdminDashboardController::class, 'laporanDarurat'])->name('admin.laporan_darurat');
    Route::get('/riwayat-unit', [AdminDashboardController::class, 'riwayatUnit'])->name('admin.riwayat_unit');

    Route::resource('/driver', DriverController::class)
        ->except(['show'])
        ->names('admin.driver');

    Route::resource('/pengguna', PenggunaController::class)
        ->except(['show'])
        ->names('admin.pengguna');

    Route::get('/daftar-aset', [AdminDashboardController::class, 'daftarAset'])->name('admin.daftar_aset');
    Route::post('/daftar-aset/{vehicle}/catat-servis', [AdminDashboardController::class, 'catatServis'])->name('admin.aset.catatServis');
    Route::get('/aset/{vehicle}/edit', [AdminDashboardController::class, 'editAset'])->name('admin.aset.edit');
    Route::post('/aset/{vehicle}/update', [AdminDashboardController::class, 'updateAset'])->name('admin.aset.update');

    Route::get('/rekap-harian', [AdminDashboardController::class, 'rekapHarian'])->name('admin.rekap_harian');
    Route::get('/rekap-bulanan', [AdminDashboardController::class, 'rekapBulanan'])->name('admin.rekap_bulanan');

});


// Halaman default
Route::get('/', function () {
    return redirect()->route('admin.dashboard');
});