<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AttendanceController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\DriverGuidanceController;
use App\Http\Controllers\Api\ServiceReportController;
use App\Http\Controllers\Api\TransportCostController;
use App\Http\Controllers\VehicleComponentController;
use App\Http\Controllers\VehicleHealthController;
use App\Http\Controllers\MaintenanceScheduleController;
use App\Http\Controllers\MaintenanceAlertController;
use App\Http\Controllers\Api\QRCodeScanController;

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
// SECURITY FIX: Added role-based authorization to prevent unauthorized access
Route::middleware(['auth:sanctum', 'throttle:60,1'])->group(function () {

    // ====================================================
    // DRIVER-ONLY ROUTES
    // ====================================================
    Route::middleware('role:driver')->group(function () {
        // A. OTORISASI
        Route::post('/logout', [AuthController::class, 'logout']);
        Route::post('/change-password', [AuthController::class, 'changePassword']);
        Route::post('/fcm-token', [AuthController::class, 'updateFcmToken']);
        Route::post('/driver/update-photo', [AuthController::class, 'updateProfilePhoto']);

        // B. OPERASIONAL ABSENSI
        Route::post('/submit-attendance', [AttendanceController::class, 'submitAttendance']);
        Route::post('/submit-end-of-duty', [AttendanceController::class, 'submitEndOfDutyReport']);
        Route::post('/clock-out-offline', [AttendanceController::class, 'clockOutOffline']);
        Route::post('/submit-emergency-report', [AttendanceController::class, 'submitEmergencyReport']);
        Route::post('/submit-service-report', [ServiceReportController::class, 'submitServiceReport']);
        Route::post('/submit-vehicle-damage-report', [ServiceReportController::class, 'submitVehicleDamageReport']);
        Route::post('/service-reports/{serviceReport}/complete', [ServiceReportController::class, 'completeServiceReport']);
        Route::get('/service-reports', [ServiceReportController::class, 'index']);
        Route::get('/service-reports/{serviceReport}', [ServiceReportController::class, 'show']);

        // C. PENGAMBILAN DATA (GET)
        Route::get('/driver-details', [AttendanceController::class, 'getDriverDetails']);
        Route::get('/driver/guidance', DriverGuidanceController::class);
        Route::get('/driver/status', [AttendanceController::class, 'checkDriverStatus']);
        Route::get('/attendance/duty-status', [AttendanceController::class, 'getDutyStatus']);
        Route::get('/driver/history', [AttendanceController::class, 'getAttendanceHistory']);

        // D. UTILITIES
        Route::post('/clear-cache', [AttendanceController::class, 'clearCache']);
        Route::get('/vehicles/{plate_number}/last-odometer', [AttendanceController::class, 'getLastOdometer']);

        // E. QR CODE SCANNING
        Route::post('/qrcode/scan/driver', [QRCodeScanController::class, 'scanDriver']);
        Route::post('/qrcode/scan/vehicle', [QRCodeScanController::class, 'scanVehicle']);

        // F. TRANSPORT COST MONITORING (Uang Jalan)
        Route::get('/transport-costs/can-create', [TransportCostController::class, 'canCreate']);
        Route::post('/transport-costs', [TransportCostController::class, 'store']);
        Route::get('/transport-costs', [TransportCostController::class, 'index']);
        Route::get('/transport-costs/{id}', [TransportCostController::class, 'show']);
    });

    // ====================================================
    // ADMIN-ONLY ROUTES (Master & Service Admin)
    // ====================================================
    Route::middleware('role:master,service_admin')->group(function () {
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

        // --- 3b. DIAGNOSTIC ENDPOINT (SECURED) ---
        Route::get('/diagnostic', function () {
            if (!app()->isLocal()) {
                abort(403, 'Akses ditolak di production');
            }

            $checks = [];

            // 1. PHP Config
            $checks['php_version'] = PHP_VERSION;
            $checks['memory_limit'] = ini_get('memory_limit');
            $checks['upload_max_filesize'] = ini_get('upload_max_filesize');
            $checks['post_max_size'] = ini_get('post_max_size');
            $checks['max_execution_time'] = ini_get('max_execution_time');
            $checks['max_file_uploads'] = ini_get('max_file_uploads');

            // 2. GD Extension
            $checks['gd_loaded'] = extension_loaded('gd');
            if (extension_loaded('gd')) {
                $gdInfo = gd_info();
                $checks['gd_jpeg_support'] = $gdInfo['JPEG Support'] ?? false;
                $checks['gd_png_support'] = $gdInfo['PNG Support'] ?? false;
            }

            // 3. Storage
            $storagePath = storage_path('app/photos'); // Updated to reflect new secured path
            $checks['storage_photos_exists'] = is_dir($storagePath);
            $checks['storage_photos_writable'] = is_writable($storagePath);
            $checks['storage_disk_free'] = disk_free_space(storage_path()) > 0
                ? round(disk_free_space(storage_path()) / 1024 / 1024) . ' MB'
                : 'Unknown';

            // 4. Intervention Image
            try {
                $manager = new \Intervention\Image\ImageManager(
                    new \Intervention\Image\Drivers\Gd\Driver()
                );
                $img = $manager->create(10, 10);
                $encoded = $img->encodeByMediaType('image/jpeg', 70);
                $checks['intervention_image'] = 'OK (' . strlen((string)$encoded) . ' bytes)';
            } catch (\Throwable $e) {
                $checks['intervention_image'] = 'GAGAL: ' . $e->getMessage();
            }

            // 5. Storage symlink
            $checks['storage_link_exists'] = file_exists(public_path('storage'));

            // 6. Controllers file status
            $attendanceCtrlPath = app_path('Http/Controllers/Api/AttendanceController.php');
            if (file_exists($attendanceCtrlPath)) {
                $attendanceContent = file_get_contents($attendanceCtrlPath);
                $checks['attendance_controller'] = [
                    'exists' => true,
                    'size' => filesize($attendanceCtrlPath),
                    'last_modified' => date('Y-m-d H:i:s', filemtime($attendanceCtrlPath)),
                    'md5' => md5($attendanceContent),
                ];
            } else {
                $checks['attendance_controller'] = ['exists' => false];
            }

            // 7. Laravel error log
            $logPath = storage_path('logs/laravel.log');
            if (file_exists($logPath)) {
                $lines = file($logPath);
                $recentLines = array_slice($lines, -50);
                $cleanedLogs = [];
                foreach ($recentLines as $line) {
                    $cleanedLine = mb_convert_encoding($line, 'UTF-8', 'UTF-8');
                    if (strlen($cleanedLine) > 250) {
                        $cleanedLine = substr($cleanedLine, 0, 250) . '... [TRUNCATED]';
                    }
                    $cleanedLine = trim($cleanedLine);
                    if (!empty($cleanedLine)) {
                        $cleanedLogs[] = $cleanedLine;
                    }
                }
                
                $filteredLogs = [];
                foreach ($cleanedLogs as $line) {
                    $lowered = strtolower($line);
                    if (str_contains($lowered, 'error') || 
                        str_contains($lowered, 'exception') || 
                        str_contains($lowered, 'fatal')) {
                        $filteredLogs[] = $line;
                    }
                }
                
                $checks['recent_filtered_errors'] = array_slice($filteredLogs, -10);
            } else {
                $checks['recent_filtered_errors'] = 'Log file not found';
            }

            return response()->json([
                'status' => 'diagnostic_secure',
                'timestamp' => now()->toDateTimeString(),
                'checks' => $checks,
            ]);
        });
    });
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

$maintenanceTokenIsValid = static function (Request $request): bool {
    $expectedToken = config('services.maintenance_url_token');

    if (!is_string($expectedToken) || trim($expectedToken) === '') {
        return false;
    }

    return hash_equals($expectedToken, (string) $request->query('token', ''));
};

// --- 3c. CRON JOB TRIGGER (VIA URL) ---
// Berguna untuk shared hosting (FTP Only) tanpa akses terminal/crontab.
// Daftarkan URL ini ke layanan CronJob Gratis (misal: cron-job.org) setiap 15 menit.
Route::get('/cron/run-schedules', function (Request $request) use ($maintenanceTokenIsValid) {
    if (! $maintenanceTokenIsValid($request)) {
        return response()->json(['status' => 'error', 'message' => 'Unauthorized token'], 403);
    }

    try {
        \Illuminate\Support\Facades\Artisan::call('schedule:run');
        return response()->json([
            'status' => 'success',
            'message' => 'Scheduler executed successfully.',
            'output' => \Illuminate\Support\Facades\Artisan::output()
        ]);
    } catch (\Exception $e) {
        return response()->json([
            'status' => 'error',
            'message' => 'Failed to execute schedule: ' . $e->getMessage()
        ], 500);
    }
});

// --- 3d. MIGRATE DATABASE (VIA URL) ---
// Hanya untuk sekali pakai saat upload ke hosting FTP.
Route::get('/migrate/run-secret', function (Request $request) use ($maintenanceTokenIsValid) {
    if (app()->isProduction()) {
        return response()->json(['status' => 'error', 'message' => 'Endpoint disabled in production'], 403);
    }

    if (! $maintenanceTokenIsValid($request)) {
        return response()->json(['status' => 'error', 'message' => 'Unauthorized'], 403);
    }

    try {
        \Illuminate\Support\Facades\Artisan::call('migrate', ['--force' => true]);
        return response()->json([
            'status' => 'success',
            'message' => 'Database migrated successfully!',
            'output' => \Illuminate\Support\Facades\Artisan::output()
        ]);
    } catch (\Exception $e) {
        return response()->json(['status' => 'error', 'message' => $e->getMessage()], 500);
    }
});

// --- 3e. RUN ARTISAN COMMANDS (VIA URL) ---
// Sangat berguna untuk shared hosting (FTP) tanpa akses terminal.
Route::get('/artisan/run-secret', function (Request $request) use ($maintenanceTokenIsValid) {
    if (app()->isProduction()) {
        return response()->json(['status' => 'error', 'message' => 'Endpoint disabled in production'], 403);
    }

    if (! $maintenanceTokenIsValid($request)) {
        return response()->json(['status' => 'error', 'message' => 'Unauthorized'], 403);
    }

    $command = $request->query('command');
    
    // Daftar command yang diizinkan untuk alasan keamanan
    $allowedCommands = [
        'storage:link',
        'qrcode:generate-missing',
        'route:clear',
        'config:clear',
        'view:clear',
        'cache:clear'
    ];

    if (!in_array($command, $allowedCommands)) {
        return response()->json(['status' => 'error', 'message' => 'Command tidak diizinkan atau tidak ada.'], 403);
    }

    try {
        \Illuminate\Support\Facades\Artisan::call($command);
        return response()->json([
            'status' => 'success',
            'message' => "Command '{$command}' berhasil dieksekusi!",
            'output' => \Illuminate\Support\Facades\Artisan::output()
        ]);
    } catch (\Exception $e) {
        return response()->json(['status' => 'error', 'message' => $e->getMessage()], 500);
    }
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
