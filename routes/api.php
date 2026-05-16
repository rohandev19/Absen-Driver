<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AttendanceController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\VehicleComponentController;
use App\Http\Controllers\VehicleHealthController;
use App\Http\Controllers\MaintenanceScheduleController;
use App\Http\Controllers\MaintenanceAlertController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| File ini mendaftarkan rute untuk Aplikasi Mobile (Flutter).
| Rute di sini bersifat "Stateless" (menggunakan Token, bukan Session).
| Semua URL otomatis diawali dengan '/api'.
|
*/

// --- 1. RUTE PUBLIK (Tanpa Token) ---
// Endpoint Login: Pintu masuk utama.
// Throttle ketat (10x/menit) untuk mencegah Brute Force Password.
Route::post('/login', [AuthController::class, 'login'])
    ->middleware('throttle:10,1')
    ->name('login');


// --- 2. RUTE AMAN (Wajib Pakai Token Bearer) ---
// Middleware 'auth:sanctum': Wajib Login.
// Middleware 'throttle:60,1': Batasi driver maks 60 request/menit (Mencegah Spam/Bug Looping dari HP).
Route::middleware(['auth:sanctum', 'throttle:60,1'])->group(function () {

    // A. OTORISASI
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::post('/change-password', [AuthController::class, 'changePassword']);

    // B. OPERASIONAL ABSENSI
    Route::post('/submit-attendance', [AttendanceController::class, 'submitAttendance']);
    Route::post('/submit-end-of-duty', [AttendanceController::class, 'submitEndOfDutyReport']);
    Route::post('/submit-emergency-report', [AttendanceController::class, 'submitEmergencyReport']);

    // C. PENGAMBILAN DATA (GET)
    Route::get('/driver-details', [AttendanceController::class, 'getDriverDetails']);
    Route::get('/driver/status', [AttendanceController::class, 'checkDriverStatus']);
    Route::get('/driver/history', [AttendanceController::class, 'getAttendanceHistory']);

    // D. UTILITIES
    // Endpoint ini sekarang sudah ada method-nya di Controller (public function clearCache)
    Route::post('/clear-cache', [AttendanceController::class, 'clearCache']);

    // E. PREVENTIVE MAINTENANCE
    // Vehicle Health
    Route::get('/vehicles/health', [VehicleHealthController::class, 'index']);
    Route::get('/vehicles/{vehicle}/health', [VehicleHealthController::class, 'show']);

    // Vehicle Components
    Route::get('/vehicles/{vehicle}/components', [VehicleComponentController::class, 'index']);
    Route::post('/vehicles/{vehicle}/components', [VehicleComponentController::class, 'store']);
    Route::put('/vehicles/{vehicle}/components/{component}', [VehicleComponentController::class, 'update']);
    Route::delete('/vehicles/{vehicle}/components/{component}', [VehicleComponentController::class, 'destroy']);
    Route::get('/component-categories', [VehicleComponentController::class, 'categories']);

    // Maintenance Schedules
    Route::get('/maintenance/schedules', [MaintenanceScheduleController::class, 'index']);
    Route::post('/maintenance/schedules', [MaintenanceScheduleController::class, 'store']);
    Route::put('/maintenance/schedules/{schedule}', [MaintenanceScheduleController::class, 'update']);
    Route::delete('/maintenance/schedules/{schedule}', [MaintenanceScheduleController::class, 'destroy']);
    Route::post('/maintenance/schedules/{schedule}/complete', [MaintenanceScheduleController::class, 'complete']);
    Route::get('/maintenance/dashboard', [MaintenanceScheduleController::class, 'dashboard']);

    // Maintenance Alerts
    Route::get('/maintenance/alerts', [MaintenanceAlertController::class, 'index']);
    Route::get('/maintenance/alerts/summary', [MaintenanceAlertController::class, 'summary']);
    Route::post('/maintenance/alerts/{alert}/acknowledge', [MaintenanceAlertController::class, 'acknowledge']);
    Route::post('/maintenance/alerts/{alert}/resolve', [MaintenanceAlertController::class, 'resolve']);
    Route::post('/maintenance/alerts/{alert}/dismiss', [MaintenanceAlertController::class, 'dismiss']);
    Route::post('/maintenance/alerts/generate', [MaintenanceAlertController::class, 'generate']);
});


// --- 3. HEALTH CHECK ---
// Cek status server (berguna untuk monitoring uptime).
Route::get('/health', function () {
    return response()->json([
        'status' => 'OK',
        'timestamp' => now()->toDateTimeString(),
        'ip' => request()->ip()
    ]);
});

// --- 4. FALLBACK ROUTE (PENTING UNTUK FLUTTER) ---
// Jika Flutter menembak URL yang salah (typo), server akan membalas JSON error, BUKAN HTML.
// Ini mencegah aplikasi Flutter crash "FormatException".
Route::fallback(function () {
    return response()->json([
        'status' => 'error',
        'message' => 'Endpoint API tidak ditemukan (404). Periksa URL request.'
    ], 404);
});