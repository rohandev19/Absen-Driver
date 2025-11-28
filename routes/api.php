<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AttendanceController;
use App\Http\Controllers\Api\AuthController;

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
// Endpoint Login: Satu-satunya pintu masuk driver.
// Middleware 'throttle:10,1': Membatasi maks 10 request per menit (Anti Brute-force).
Route::post('/login', [AuthController::class, 'login'])->middleware('throttle:10,1');


// --- 2. RUTE AMAN (Wajib Pakai Token Bearer) ---
// Middleware 'auth:sanctum': Memastikan request memiliki header 'Authorization: Bearer <token>' yang valid.
Route::middleware('auth:sanctum')->group(function () {

    // A. OTORISASI
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::post('/change-password', [AuthController::class, 'changePassword']);

    // B. OPERASIONAL ABSENSI
    // Kirim data check-in (Foto Selfie, Speedo Awal, Lokasi)
    Route::post('/submit-attendance', [AttendanceController::class, 'submitAttendance']);
    // Kirim data check-out (Speedo Akhir, Kondisi Mobil)
    Route::post('/submit-end-of-duty', [AttendanceController::class, 'submitEndOfDutyReport']);
    // Kirim laporan darurat (Ban pecah, Mogok, dll)
    Route::post('/submit-emergency-report', [AttendanceController::class, 'submitEmergencyReport']);

    // C. PENGAMBILAN DATA (GET)
    // Mengambil detail profil driver (Nama, NIK) dari token
    Route::get('/driver-details', [AttendanceController::class, 'getDriverDetails']);
    // Cek apakah driver sedang bertugas (untuk UI State di HP)
    Route::get('/driver/status', [AttendanceController::class, 'checkDriverStatus']);
    // Ambil 30 riwayat terakhir
    Route::get('/driver/history', [AttendanceController::class, 'getAttendanceHistory']);

    // D. UTILITIES
    // Menghapus cache server jika data di HP tidak sinkron
    Route::post('/clear-cache', [AttendanceController::class, 'clearCache']);
});


// --- 3. HEALTH CHECK ---
// Endpoint sederhana untuk mengecek apakah server hidup/online.
Route::get('/health', function () {
    return response()->json(['status' => 'OK', 'timestamp' => now()]);
});