<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use App\Models\Vehicle;
use App\Models\Attendance;
use App\Models\EmergencyReport;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use Carbon\Carbon;
use Intervention\Image\Drivers\Gd\Driver as GdDriver;
use Intervention\Image\ImageManager;

class AttendanceController extends Controller
{
    const CACHE_DRIVER_STATUS = 'driver_status_';
    const CACHE_ATTENDANCE_HISTORY = 'attendance_history_';

    // --- GET DATA ---

    public function getDriverDetails()
    {
        try {
            $driver = Auth::user();
            return response()->json([
                'id' => $driver->driver_id_nik,
                'name' => $driver->full_name,
            ]);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Service unavailable'], 503);
        }
    }

    public function checkDriverStatus()
    {
        $driverId = Auth::id();
        $cacheKey = self::CACHE_DRIVER_STATUS . $driverId;

        // FIX #1: Cache hanya menyimpan ARRAY data, bukan object Response.
        //
        // Bug lama: Cache::remember() menyimpan response()->json() langsung.
        // Object JsonResponse tidak dirancang untuk di-serialize ke cache storage
        // (file/redis/memcached). Ini bisa menyebabkan error deserialisasi atau
        // data corrupt saat cache di-retrieve pada request berikutnya.
        //
        // Fix: Simpan array data mentah ke cache, bungkus response() di luar.
        try {
            $cachedData = Cache::remember($cacheKey, 60, function () use ($driverId) {
                $activeAttendance = Attendance::with('vehicle')
                    ->where('driver_id', $driverId)
                    ->whereNull('time_out')
                    ->first();

                if ($activeAttendance) {
                    return [
                        'status' => 'success',
                        'is_on_duty' => true,
                        'plate_number' => $activeAttendance->vehicle->plate_number ?? 'N/A',
                    ];
                }

                return [
                    'status' => 'success',
                    'is_on_duty' => false,
                    'plate_number' => null,
                ];
            });

            return response()->json($cachedData);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Service temporarily unavailable',
                'is_on_duty' => false,
            ], 503);
        }
    }

    /**
     * Task 3.3: Cek status tugas driver (duty status) dari server.
     * Endpoint ini memungkinkan mobile dari device MANAPUN untuk mengecek
     * apakah driver sedang bertugas, sehingga mendukung multi-device clock-out.
     */
    public function getDutyStatus()
    {
        try {
            $driverId = Auth::id();
            $driverRecord = \App\Models\Driver::find($driverId);
            $activeAttendance = Attendance::where('driver_id', $driverId)
                ->whereNull('time_out')
                ->first();

            return response()->json([
                'status'    => 'success',
                'driver_id' => $driverId,
                'is_on_duty' => $driverRecord ? $driverRecord->is_on_duty : false,
                'active_attendance_id' => $activeAttendance?->id,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Gagal mengambil status tugas.',
            ], 500);
        }
    }

    // --- OPERASIONAL ---

    /**
     * PROSES ABSEN MASUK (CHECK-IN)
     */
    public function submitAttendance(Request $request)
    {
        if ($request->has('plate_number')) {
            $request->merge([
                'plate_number' => $this->normalizePlateNumber($request->input('plate_number')),
            ]);
        }

        if ($request->has('vehicle_entry_method')) {
            $request->merge([
                'vehicle_entry_method' => strtolower((string) $request->input('vehicle_entry_method')),
            ]);
        }

        $validated = $request->validate([
            'plate_number' => 'required|string|max:20',
            'vehicle_entry_method' => 'nullable|in:qr,manual',
            'manual_vehicle_reason' => 'required_if:vehicle_entry_method,manual|nullable|string|max:100',
            'manual_vehicle_photo' => 'required_if:vehicle_entry_method,manual|nullable|image|mimes:jpeg,jpg,png|max:4096',
            'gps_location' => ['required', 'string', 'regex:/^[-]?\d+(\.\d+)?,\s*[-]?\d+(\.\d+)?$/'],
            'timestamp' => 'required|date_format:Y-m-d H:i:s',
            'speedometer_manual' => 'required|integer',
            'selfie_photo' => 'required|image|mimes:jpeg,jpg,png|max:2048',
            'speedometer_photo' => 'required|image|mimes:jpeg,jpg,png|max:2048',
            'car_condition_photo_1' => 'nullable|image|mimes:jpeg,jpg,png|max:2048',
            'car_condition_photo_2' => 'nullable|image|mimes:jpeg,jpg,png|max:2048',
        ]);

        try {
            $driver = Auth::user();
            $vehicleEntryMethod = $validated['vehicle_entry_method'] ?? 'qr';
            $isManualVehicleEntry = $vehicleEntryMethod === 'manual';
            $plateNumber = $this->normalizePlateNumber($validated['plate_number']);

            if ($plateNumber === '') {
                return response()->json(['status' => 'error', 'message' => 'Plat nomor wajib diisi.'], 422);
            }

            $clientTime = Carbon::parse($validated['timestamp']);
            if ($clientTime->gt(Carbon::now()->addMinutes(10))) {
                return response()->json(['status' => 'error', 'message' => 'Jam HP Anda tidak sesuai (terlalu maju).'], 422);
            }
            if ($clientTime->lt(Carbon::now()->subMinutes(30))) {
                return response()->json(['status' => 'error', 'message' => 'Waktu terlalu jauh di masa lalu.'], 422);
            }

            // Task 3.1: Cek duty status dari server (bukan hanya attendance)
            $driverRecord = \App\Models\Driver::find($driver->id);
            if ($driverRecord && $driverRecord->is_on_duty) {
                return response()->json(['status' => 'error', 'message' => 'Driver sudah clock-in, tidak dapat clock-in lagi.'], 409);
            }

            $isOnDuty = Attendance::where('driver_id', $driver->id)->whereNull('time_out')->exists();
            if ($isOnDuty) {
                return response()->json(['status' => 'error', 'message' => 'Server mencatat Anda masih bertugas.'], 409);
            }

            $vehicle = Vehicle::where('plate_number', $plateNumber)->first();
            if (!$vehicle && !$isManualVehicleEntry) {
                return response()->json(['status' => 'error', 'message' => 'Plat nomor tidak dikenal di sistem. Silakan hubungi admin.'], 404);
            }

            if ($vehicle && !$this->vehicleCanBeUsedForCheckIn($vehicle)) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Unit ini sedang berstatus ' . $vehicle->status . ' dan tidak dapat digunakan untuk check-in.',
                ], 409);
            }

            $selfieUrl = app(\App\Services\ImageProcessingService::class)->optimize($request->file('selfie_photo'));
            $speedoUrl = app(\App\Services\ImageProcessingService::class)->optimize($request->file('speedometer_photo'));
            $manualVehiclePhotoUrl = $isManualVehicleEntry
                ? app(\App\Services\ImageProcessingService::class)->optimize($request->file('manual_vehicle_photo'))
                : null;
            $condition1Url = $request->hasFile('car_condition_photo_1')
                ? app(\App\Services\ImageProcessingService::class)->optimize($request->file('car_condition_photo_1'))
                : null;
            $condition2Url = $request->hasFile('car_condition_photo_2')
                ? app(\App\Services\ImageProcessingService::class)->optimize($request->file('car_condition_photo_2'))
                : null;

            // Task 3.1: Wrap dalam transaction — buat attendance + set is_on_duty = true
            \Illuminate\Support\Facades\DB::transaction(function () use (
                $driver,
                &$vehicle,
                $validated,
                $plateNumber,
                $vehicleEntryMethod,
                $isManualVehicleEntry,
                $selfieUrl,
                $speedoUrl,
                $manualVehiclePhotoUrl,
                $condition1Url,
                $condition2Url,
                $driverRecord
            ) {
                $isOnDutyLocked = Attendance::where('driver_id', $driver->id)
                    ->whereNull('time_out')
                    ->lockForUpdate()
                    ->exists();

                if ($isOnDutyLocked) {
                    throw new \Exception('ALREADY_ON_DUTY');
                }

                $vehicle = Vehicle::where('plate_number', $plateNumber)
                    ->lockForUpdate()
                    ->first();

                if (!$vehicle && $isManualVehicleEntry) {
                    $vehicle = Vehicle::create([
                        'plate_number' => $plateNumber,
                        'type' => null,
                        'project_id' => $driver->project_id,
                        'status' => 'Pending Verifikasi',
                        'current_km' => $validated['speedometer_manual'],
                        'service_interval_km' => 10000,
                        'is_temporary' => true,
                        'verification_status' => 'pending',
                        'source' => 'driver_manual',
                        'notes' => 'Dibuat otomatis dari check-in manual driver '
                            . ($driver->full_name ?? $driver->id)
                            . '. Alasan: '
                            . ($validated['manual_vehicle_reason'] ?? '-'),
                    ]);
                }

                if (!$vehicle) {
                    throw new \Exception('VEHICLE_NOT_FOUND');
                }

                Attendance::create([
                    'driver_id' => $driver->id,
                    'vehicle_id' => $vehicle->id,
                    'vehicle_entry_method' => $vehicleEntryMethod,
                    'manual_vehicle_plate' => $isManualVehicleEntry ? $plateNumber : null,
                    'manual_vehicle_reason' => $isManualVehicleEntry ? ($validated['manual_vehicle_reason'] ?? null) : null,
                    'manual_vehicle_photo_path' => $manualVehiclePhotoUrl,
                    'vehicle_verification_status' => $isManualVehicleEntry
                        ? ($vehicle->verification_status ?? 'pending')
                        : 'verified',
                    'time_in' => $validated['timestamp'],
                    'created_at' => Carbon::now(),
                    'gps_location_in' => $validated['gps_location'],
                    'speedo_awal' => $validated['speedometer_manual'],
                    'selfie_photo_path' => $selfieUrl,
                    'speedo_photo_awal_path' => $speedoUrl,
                    'condition_photo_1_path' => $condition1Url,
                    'condition_photo_2_path' => $condition2Url,
                ]);

                // Set driver as on duty
                if ($driverRecord) {
                    $driverRecord->update(['is_on_duty' => true]);
                }
            });

            $this->clearDriverCacheLogic($driver->id);

            $message = 'Absensi masuk berhasil.';
            if ($isManualVehicleEntry && ($vehicle?->verification_status === 'pending')) {
                $message = 'Absensi masuk berhasil. Unit pengganti menunggu verifikasi admin.';
            } elseif ($isManualVehicleEntry) {
                $message = 'Absensi masuk berhasil dengan input plat manual.';
            }

            return response()->json([
                'status' => 'success',
                'message' => $message,
                'data' => [
                    'vehicle_id' => $vehicle?->id,
                    'plate_number' => $vehicle?->plate_number,
                    'vehicle_entry_method' => $vehicleEntryMethod,
                    'vehicle_verification_status' => $vehicle?->verification_status ?? 'verified',
                ],
            ]);

        } catch (\Throwable $e) {
            Log::error('SubmitAttendance Error', [
                'message'  => $e->getMessage(),
                'file'     => $e->getFile(),
                'line'     => $e->getLine(),
                'driver'   => Auth::id(),
                'trace'    => $e->getTraceAsString(),
            ]);
            return response()->json(['status' => 'error', 'message' => 'Gagal menyimpan absensi.'], 500);
        }
    }

    /**
     * PROSES ABSEN PULANG (CHECK-OUT)
     */
    public function submitEndOfDutyReport(Request $request)
    {
        if (! $request->has('speedometer_manual_akhir') && $request->has('speedometer_manual')) {
            $request->merge([
                'speedometer_manual_akhir' => $request->input('speedometer_manual'),
            ]);
        }

        $validated = $request->validate([
            'speedometer_manual_akhir' => 'required|integer',
            'gps_location' => ['nullable', 'string', 'regex:/^[-]?\d+(\.\d+)?,\s*[-]?\d+(\.\d+)?$/'],
            'check_ban' => 'required|string',
            'check_lampu' => 'required|string',
            'check_rem' => 'required|string',
            'catatan' => 'nullable|string',
            'timestamp' => 'required|date_format:Y-m-d H:i:s',
            'speedometer_photo_akhir' => 'required_without:speedometer_photo|image|mimes:jpeg,jpg,png|max:2048',
            'speedometer_photo' => 'required_without:speedometer_photo_akhir|image|mimes:jpeg,jpg,png|max:2048',
        ]);

        try {
            $driver = Auth::user();

            // Task 3.2: Cek duty status dari server
            $driverRecord = \App\Models\Driver::find($driver->id);
            if (!$driverRecord) {
                return response()->json(['status' => 'error', 'message' => 'Data driver tidak ditemukan.'], 404);
            }

            $activeAttendance = Attendance::with('vehicle')
                ->where('driver_id', $driver->id)
                ->whereNull('time_out')
                ->first();

            if (!$activeAttendance) {
                return response()->json(['status' => 'error', 'message' => 'Tidak ada tugas aktif.'], 404);
            }

            $speedometerPhoto = $request->file('speedometer_photo_akhir') ?? $request->file('speedometer_photo');
            $speedoAkhirUrl = app(\App\Services\ImageProcessingService::class)->optimize($speedometerPhoto);

            // Task 3.2: Wrap update + duty status dalam transaction
            \Illuminate\Support\Facades\DB::transaction(function () use (
                $activeAttendance, $driverRecord, $validated, $speedoAkhirUrl
            ) {
                $activeAttendance->update([
                    'time_out' => $validated['timestamp'],
                    'gps_location_out' => $validated['gps_location'] ?? null,
                    'speedo_photo_akhir_path' => $speedoAkhirUrl,
                    'catatan' => $validated['catatan'] ?? '',
                    'check_ban' => $validated['check_ban'],
                    'check_lampu' => $validated['check_lampu'],
                    'check_rem' => $validated['check_rem'],
                    'speedo_akhir' => $validated['speedometer_manual_akhir'],
                ]);

                // Set driver as off duty
                if ($driverRecord) {
                    $driverRecord->update(['is_on_duty' => false]);
                }
            });

            // FIX #4: Invalidate history cache saat check-out
            // Bug lama: clearDriverCacheLogic() hanya di-panggil tapi
            // CACHE_ATTENDANCE_HISTORY tidak di-clear di sini.
            // History driver akan basi hingga 5 menit setelah check-out.
            $this->clearDriverCacheLogic($driver->id);

            // Kalkulasi Summary
            $waktuMasuk = Carbon::parse($activeAttendance->time_in);
            $waktuKeluar = Carbon::parse($validated['timestamp']);
            $totalMenit = $waktuMasuk->diffInMinutes($waktuKeluar);
            $durasiKerja = floor($totalMenit / 60) . " Jam " . ($totalMenit % 60) . " Menit";

            $jarak = $validated['speedometer_manual_akhir'] - $activeAttendance->speedo_awal;
            if ($jarak < 0)
                $jarak = 0;

            $masalah = [];
            if ($validated['check_ban'] === 'Bermasalah')
                $masalah[] = 'Ban';
            if ($validated['check_lampu'] === 'Bermasalah')
                $masalah[] = 'Lampu';
            if ($validated['check_rem'] === 'Bermasalah')
                $masalah[] = 'Rem';

            return response()->json([
                'status' => 'success',
                'message' => 'Laporan akhir tugas berhasil.',
                'data' => [
                    'driver_name' => $driver->full_name,
                    'plate_number' => $activeAttendance->vehicle->plate_number ?? 'N/A',
                    'waktu_keluar' => $waktuKeluar->format('H:i d-m-Y'),
                    'durasi_kerja' => $durasiKerja,
                    'total_km' => number_format($jarak) . " Km",
                    'vehicle_status' => empty($masalah) ? 'Prima' : 'Perlu Perbaikan',
                    'vehicle_issues' => empty($masalah) ? 'Siap digunakan kembali' : implode(', ', $masalah),
                ],
            ]);

        } catch (\Throwable $e) {
            Log::error('SubmitEndOfDuty Error', [
                'message' => $e->getMessage(),
                'file'    => $e->getFile(),
                'line'    => $e->getLine(),
                'driver'  => Auth::id(),
                'trace'   => $e->getTraceAsString(),
            ]);
            return response()->json(['status' => 'error', 'message' => 'Gagal mengakhiri tugas.'], 500);
        }
    }

    /**
     * PROSES ABSEN PULANG OFFLINE (OFFLINE CLOCK-OUT)
     *
     * Endpoint khusus untuk menerima data clock-out yang disimpan offline di HP driver.
     * Fitur kunci:
     * - Idempotency: offline_entry_id mencegah duplikasi data saat retry
     * - Device Timestamp: Waktu asli dari HP driver dipakai, bukan waktu server
     * - Duty Status: Server memverifikasi dan update status tugas driver
     * - Recovery Logging: Semua attempt dicatat untuk audit trail
     */
    public function clockOutOffline(Request $request)
    {
        // --- Validasi Input ---
        $validated = $request->validate([
            'offline_entry_id'        => 'required|string|max:255',
            'device_timestamp'        => 'required|date_format:Y-m-d H:i:s',
            'is_offline_recovery'     => 'required|in:0,1,true,false',
            'speedometer_manual_akhir'=> 'required|integer',
            'gps_location'            => ['nullable', 'string', 'regex:/^[-]?\d+(\.\d+)?,\s*[-]?\d+(\.\d+)?$/'],
            'check_ban'               => 'required|string',
            'check_lampu'             => 'required|string',
            'check_rem'               => 'required|string',
            'catatan'                 => 'nullable|string',
            'speedometer_photo_akhir' => 'required|image|mimes:jpeg,jpg,png|max:2048',
        ]);

        $driver = Auth::user();
        $driverId = $driver->id;

        // --- Task 2.4: Idempotency Check ---
        // Cek apakah offline_entry_id sudah pernah diproses
        $existingAttendance = Attendance::where('offline_entry_id', $validated['offline_entry_id'])->first();

        if ($existingAttendance) {
            // Sudah ada data dengan offline_entry_id yang sama
            if ($existingAttendance->time_out !== null) {
                // Idempotent success: Data sudah berhasil disimpan sebelumnya
                Log::info('ClockOutOffline Idempotent', [
                    'offline_entry_id' => $validated['offline_entry_id'],
                    'driver_id' => $driverId,
                ]);
                return response()->json([
                    'status'  => 'success',
                    'message' => 'Data sudah tersimpan sebelumnya (idempotent).',
                    'data'    => [
                        'attendance_id'    => $existingAttendance->id,
                        'time_out'         => $existingAttendance->time_out,
                        'offline_entry_id' => $existingAttendance->offline_entry_id,
                    ],
                ], 200);
            }

            // offline_entry_id ada tapi time_out belum terisi — conflict
            return response()->json([
                'status'  => 'error',
                'code'    => 'DUPLICATE_ENTRY_CONFLICT',
                'message' => 'Data dengan offline_entry_id yang sama sudah ada tapi belum selesai diproses.',
            ], 409);
        }

        // --- Task 2.6: Attendance State Validation ---
        $activeAttendance = Attendance::where('driver_id', $driverId)
            ->whereNull('time_out')
            ->first();

        if (!$activeAttendance) {
            // Tidak ada tugas aktif — mungkin sudah clock-out dari device lain
            $this->logOfflineRecovery($driverId, null, $validated, 'failed', 'NO_ACTIVE_ATTENDANCE', 'Tidak ada tugas aktif untuk driver ini.');
            return response()->json([
                'status'  => 'error',
                'code'    => 'NO_ACTIVE_ATTENDANCE',
                'message' => 'Tidak ada tugas aktif. Mungkin sudah clock-out dari perangkat lain.',
            ], 404);
        }

        // Cek duty status dari tabel drivers
        $driverRecord = \App\Models\Driver::find($driverId);
        if ($driverRecord && !$driverRecord->is_on_duty) {
            $this->logOfflineRecovery($driverId, $activeAttendance->id, $validated, 'failed', 'DRIVER_NOT_ON_DUTY', 'Driver tidak sedang bertugas menurut server.');
            return response()->json([
                'status'  => 'error',
                'code'    => 'DRIVER_NOT_ON_DUTY',
                'message' => 'Server mencatat Anda tidak sedang bertugas.',
            ], 409);
        }

        // --- Task 2.5: Device Timestamp Preservation ---
        try {
            $deviceTimestamp = Carbon::createFromFormat('Y-m-d H:i:s', $validated['device_timestamp']);
        } catch (\Exception $e) {
            return response()->json([
                'status'  => 'error',
                'code'    => 'INVALID_TIMESTAMP',
                'message' => 'Format device_timestamp tidak valid.',
            ], 422);
        }

        $now = Carbon::now();
        $delayMinutes = abs($now->diffInMinutes($deviceTimestamp, false));
        $isLateSubmission = $delayMinutes > 1440; // Lebih dari 24 jam

        // --- Proses Upload Foto ---
        try {
            $speedoAkhirUrl = app(\App\Services\ImageProcessingService::class)->optimize($request->file('speedometer_photo_akhir'));
        } catch (\Throwable $e) {
            Log::error('ClockOutOffline Photo Processing Error', [
                'message' => $e->getMessage(),
                'driver_id' => $driverId,
            ]);
            $this->logOfflineRecovery($driverId, $activeAttendance->id, $validated, 'failed', 'PHOTO_PROCESSING_ERROR', $e->getMessage());
            return response()->json([
                'status'  => 'error',
                'code'    => 'PHOTO_PROCESSING_ERROR',
                'message' => 'Gagal memproses foto speedometer.',
            ], 500);
        }

        // --- Task 2.7: DB Transaction (Attendance Update + Duty Status) ---
        try {
            \Illuminate\Support\Facades\DB::transaction(function () use (
                $activeAttendance, $driverRecord, $validated, $deviceTimestamp, $now,
                $isLateSubmission, $speedoAkhirUrl
            ) {
                // Update attendance record — pakai device_timestamp, BUKAN server time
                $activeAttendance->update([
                    'time_out'             => $deviceTimestamp,
                    'gps_location_out'     => $validated['gps_location'] ?? null,
                    'speedo_photo_akhir_path' => $speedoAkhirUrl,
                    'catatan'              => $validated['catatan'] ?? '',
                    'check_ban'            => $validated['check_ban'],
                    'check_lampu'          => $validated['check_lampu'],
                    'check_rem'            => $validated['check_rem'],
                    'speedo_akhir'         => $validated['speedometer_manual_akhir'],
                    'is_offline_recovery'  => $validated['is_offline_recovery'],
                    'recovery_timestamp'   => $now, // Waktu server terima data
                    'offline_entry_id'     => $validated['offline_entry_id'],
                    'is_late_submission'   => $isLateSubmission,
                ]);

                // Update driver duty status
                if ($driverRecord) {
                    $driverRecord->update(['is_on_duty' => false]);
                }
            });
        } catch (\Throwable $e) {
            Log::error('ClockOutOffline Transaction Error', [
                'message'  => $e->getMessage(),
                'file'     => $e->getFile(),
                'line'     => $e->getLine(),
                'driver_id' => $driverId,
            ]);
            $this->logOfflineRecovery($driverId, $activeAttendance->id, $validated, 'failed', 'TRANSACTION_ERROR', $e->getMessage());
            return response()->json([
                'status'  => 'error',
                'code'    => 'TRANSACTION_ERROR',
                'message' => 'Gagal menyimpan data. Silakan coba lagi.',
            ], 500);
        }

        // --- Task 2.8: Offline Recovery Logging (Success) ---
        $photoSizeKb = $request->file('speedometer_photo_akhir')
            ? round($request->file('speedometer_photo_akhir')->getSize() / 1024)
            : null;

        $this->logOfflineRecovery(
            $driverId,
            $activeAttendance->id,
            $validated,
            'success',
            null,
            null,
            $photoSizeKb
        );

        // Invalidate cache
        $this->clearDriverCacheLogic($driverId);

        // --- Build Response ---
        $waktuMasuk = Carbon::parse($activeAttendance->time_in);
        $totalMenit = $waktuMasuk->diffInMinutes($deviceTimestamp);
        $durasiKerja = floor($totalMenit / 60) . " Jam " . ($totalMenit % 60) . " Menit";

        $jarak = $validated['speedometer_manual_akhir'] - $activeAttendance->speedo_awal;
        if ($jarak < 0) $jarak = 0;

        $response = [
            'status'  => 'success',
            'message' => $isLateSubmission
                ? 'Data berhasil disimpan (pengiriman terlambat > 24 jam).'
                : 'Laporan akhir tugas offline berhasil disimpan.',
            'data'    => [
                'attendance_id'      => $activeAttendance->id,
                'driver_name'        => $driver->full_name,
                'plate_number'       => $activeAttendance->vehicle->plate_number ?? 'N/A',
                'waktu_keluar'       => $deviceTimestamp->format('H:i d-m-Y'),
                'durasi_kerja'       => $durasiKerja,
                'total_km'           => number_format($jarak) . " Km",
                'is_late_submission' => $isLateSubmission,
                'delay_minutes'      => $delayMinutes,
                'offline_entry_id'   => $validated['offline_entry_id'],
            ],
        ];

        // Return 200 even for late submissions to prevent client retries (data is successfully saved)
        $statusCode = 200;

        return response()->json($response, $statusCode);
    }

    /**
     * Helper: Catat log offline recovery ke tabel offline_recovery_logs.
     * Dipanggil untuk success maupun failure, agar semua attempt tercatat.
     */
    private function logOfflineRecovery(
        int $driverId,
        ?int $attendanceId,
        array $validated,
        string $result,
        ?string $errorCode = null,
        ?string $errorMessage = null,
        ?int $photoSizeKb = null
    ): void {
        try {
            $deviceTimestamp = isset($validated['device_timestamp'])
                ? Carbon::createFromFormat('Y-m-d H:i:s', $validated['device_timestamp'])
                : null;

            $now = Carbon::now();
            $delayMinutes = $deviceTimestamp ? $now->diffInMinutes($deviceTimestamp) : null;

            \App\Models\OfflineRecoveryLog::create([
                'driver_id'          => $driverId,
                'attendance_id'      => $attendanceId,
                'offline_entry_id'   => $validated['offline_entry_id'] ?? null,
                'device_timestamp'   => $deviceTimestamp,
                'recovery_timestamp' => $now,
                'delay_minutes'      => $delayMinutes,
                'result'             => $result,
                'error_code'         => $errorCode,
                'error_message'      => $errorMessage ? Str::limit($errorMessage, 500) : null,
                'retry_count'        => 0,
                'photo_size_kb'      => $photoSizeKb,
            ]);
        } catch (\Throwable $e) {
            // Log gagal JANGAN menggagalkan operasi utama
            Log::error('OfflineRecoveryLog Write Error', [
                'message'  => $e->getMessage(),
                'driver_id' => $driverId,
            ]);
        }
    }

    public function submitEmergencyReport(Request $request)
    {
        if (! $request->filled('plate_number')) {
            $activeAttendance = Attendance::with('vehicle')
                ->where('driver_id', Auth::id())
                ->whereNull('time_out')
                ->latest('time_in')
                ->first();

            if ($activeAttendance?->vehicle?->plate_number) {
                $request->merge([
                    'plate_number' => $activeAttendance->vehicle->plate_number,
                ]);
            }
        }

        $validated = $request->validate([
            'plate_number' => 'required|string',
            'gps_location' => ['required', 'string', 'regex:/^[-]?\d+(\.\d+)?,\s*[-]?\d+(\.\d+)?$/'],
            'description' => 'required|string',
            'timestamp' => 'required|date_format:Y-m-d H:i:s',
            'proof_photo' => 'nullable|image|mimes:jpeg,jpg,png|max:2048',  // Reduced from 5MB to 2MB
            'photo' => 'nullable|image|mimes:jpeg,jpg,png|max:2048',
        ]);

        try {
            $driver = Auth::user();
            $vehicle = Vehicle::firstOrCreate(
                ['plate_number' => strtoupper($validated['plate_number'])],
                ['type' => 'Darurat']
            );
            $proofPhoto = $request->file('proof_photo') ?? $request->file('photo');
            $proofPhotoUrl = $proofPhoto
                ? app(\App\Services\ImageProcessingService::class)->optimize($proofPhoto)
                : null;

            EmergencyReport::create([
                'driver_id' => $driver->id,
                'vehicle_id' => $vehicle->id,
                'timestamp' => $validated['timestamp'],
                'gps_location' => $validated['gps_location'],
                'description' => $validated['description'],
                'proof_photo_path' => $proofPhotoUrl,
            ]);

            return response()->json(['status' => 'success', 'message' => 'Laporan darurat terkirim.']);

        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => 'Gagal mengirim laporan.'], 500);
        }
    }

    // --- HISTORY & HELPERS ---

    public function getAttendanceHistory()
    {
        $driverId = Auth::id();
        $cacheKey = self::CACHE_ATTENDANCE_HISTORY . $driverId;

        // FIX #1: Sama seperti checkDriverStatus() — cache hanya menyimpan
        // array data mentah, bukan object Response.
        try {
            $cachedData = Cache::remember($cacheKey, 300, function () use ($driverId) {
                return Attendance::with('vehicle')
                    ->where('driver_id', $driverId)
                    ->orderBy('time_in', 'desc')
                    ->take(30)
                    ->get()
                    ->map(function (Attendance $item) {
                        $timeIn = $item->time_in
                            ? Carbon::parse($item->time_in)->toDateTimeString()
                            : null;
                        $timeOut = $item->time_out
                            ? Carbon::parse($item->time_out)->toDateTimeString()
                            : null;

                        return [
                            'id' => $item->id,
                            'time_in' => $timeIn,
                            'time_out' => $timeOut,
                            'vehicle' => $item->vehicle ? [
                                'id' => $item->vehicle->id,
                                'plate_number' => $item->vehicle->plate_number,
                                'type' => $item->vehicle->type,
                            ] : null,
                            'jam_masuk' => $timeIn ?? '-',
                            'jam_keluar' => $timeOut ?? '-',
                            'plat_nomor' => $item->vehicle->plate_number ?? '-',
                        ];
                    })
                    ->toArray(); // Simpan sebagai array murni, bukan Collection
            });

            return response()->json([
                'status' => 'success',
                'data' => $cachedData,
            ]);

        } catch (\Throwable $e) {
            Log::error('GetAttendanceHistory Error', [
                'message' => $e->getMessage(),
                'file'    => $e->getFile(),
                'line'    => $e->getLine(),
                'driver'  => Auth::id(),
            ]);
            return response()->json(['status' => 'error', 'message' => 'Gagal memuat riwayat.'], 500);
        }
    }

    public function clearCache()
    {
        $driverId = Auth::id();
        $this->clearDriverCacheLogic($driverId);
        return response()->json(['status' => 'success', 'message' => 'Cache cleared']);
    }

    public function getLastOdometer($plate_number)
    {
        try {
            $vehicle = Vehicle::where('plate_number', $this->normalizePlateNumber($plate_number))->first();
            
            return response()->json([
                'status' => 'success',
                'data' => [
                    'last_odometer' => $vehicle ? $vehicle->current_km : 0
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Gagal mengambil data kendaraan'
            ], 500);
        }
    }

    private function normalizePlateNumber(?string $plateNumber): string
    {
        return strtoupper(preg_replace('/\s+/', '', trim((string) $plateNumber)));
    }

    private function vehicleCanBeUsedForCheckIn(Vehicle $vehicle): bool
    {
        $blockedStatuses = [
            'maintenance',
            'perbaikan',
            'servis',
            'service',
            'rusak',
            'tidak aktif',
            'inactive',
            'nonaktif',
        ];

        return !in_array(strtolower(trim((string) $vehicle->status)), $blockedStatuses, true);
    }

    private function clearDriverCacheLogic($driverId)
    {
        Cache::forget(self::CACHE_DRIVER_STATUS . $driverId);
        Cache::forget(self::CACHE_ATTENDANCE_HISTORY . $driverId);
    }

}
