<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AttendanceController;
use App\Http\Controllers\Api\AuthController;

Route::middleware('throttle:60,1')->group(function () {
    // Authentication
    Route::post('/login', [AuthController::class, 'login'])->middleware('throttle:10,1');

    // Attendance
    Route::post('/submit-attendance', [AttendanceController::class, 'submitAttendance']);
    Route::post('/submit-end-of-duty', [AttendanceController::class, 'submitEndOfDutyReport']);
    Route::post('/submit-emergency-report', [AttendanceController::class, 'submitEmergencyReport']);

    // Data
    Route::get('/driver-details/{driverId}', [AttendanceController::class, 'getDriverDetails']);
    Route::get('/driver/status/{driverId}', [AttendanceController::class, 'checkDriverStatus']);
    Route::get('/driver/history/{driverId}', [AttendanceController::class, 'getAttendanceHistory']);
});

// Health check
Route::get('/health', function () {
    return response()->json(['status' => 'OK', 'timestamp' => now()]);
});

// Cache management
Route::post('/clear-cache/{driverId?}', [AttendanceController::class, 'clearCache']);