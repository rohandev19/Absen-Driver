<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AttendanceController;
use App\Http\Controllers\Api\AuthController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
*/

// --- Rute Publik ---
// Endpoint ini adalah satu-satunya yang boleh diakses tanpa token.
Route::post('/login', [AuthController::class, 'login'])->middleware('throttle:10,1');


// --- Rute Aman (WAJIB PAKAI TOKEN) ---
// Semua rute di dalam grup ini akan otomatis menolak
// request yang tidak memiliki 'Authorization: Bearer <token>' header.
Route::middleware('auth:sanctum')->group(function () {

    // Rute Otorisasi
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::post('/change-password', [App\Http\Controllers\Api\AuthController::class, 'changePassword']);

    // Rute Absensi (Attendance)
    Route::post('/submit-attendance', [AttendanceController::class, 'submitAttendance']);
    Route::post('/submit-end-of-duty', [AttendanceController::class, 'submitEndOfDutyReport']);
    Route::post('/submit-emergency-report', [AttendanceController::class, 'submitEmergencyReport']);

    // Rute Pengambilan Data
    // Kita hapus '{driverId}' karena kita akan ambil ID dari token (lebih aman)
    Route::get('/driver-details', [AttendanceController::class, 'getDriverDetails']);
    Route::get('/driver/status', [AttendanceController::class, 'checkDriverStatus']);
    Route::get('/driver/history', [AttendanceController::class, 'getAttendanceHistory']);

    // Rute Cache (jika masih diperlukan, tapi sekarang pakai ID dari token)
    Route::post('/clear-cache', [AttendanceController::class, 'clearCache']);
});


// Rute Health Check (opsional, biarkan terbuka)
Route::get('/health', function () {
    return response()->json(['status' => 'OK', 'timestamp' => now()]);


});
