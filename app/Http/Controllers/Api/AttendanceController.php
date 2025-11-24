<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

// --- MODEL BARU KITA ---
use App\Models\Driver;
use App\Models\Vehicle;
use App\Models\Attendance;
use App\Models\EmergencyReport;

// --- FUNGSI BAWAAN LARAVEL ---
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use Carbon\Carbon;

// --- UNTUK PROSES GAMBAR ---
use Intervention\Image\Drivers\Gd\Driver as GdDriver;
use Intervention\Image\ImageManager;

class AttendanceController extends Controller
{
    // Cache timeout
    private $cacheTimeout = 300;
    const CACHE_DRIVER_STATUS = 'driver_status_';
    const CACHE_ATTENDANCE_HISTORY = 'attendance_history_';

    /**
     * Mengambil detail driver yang sedang login
     */
    public function getDriverDetails()
    {
        try {
            $driver = Auth::user();
            return response()->json([
                'id' => $driver->driver_id_nik,
                'name' => $driver->full_name
            ]);
        } catch (\Exception $e) {
            Log::error("GetDriverDetails Error: " . $e->getMessage());
            return response()->json(['message' => 'Service unavailable'], 503);
        }
    }

    /**
     * Cek status driver yang sedang login
     */
    public function checkDriverStatus()
    {
        $driverId = Auth::id();
        $cacheKey = self::CACHE_DRIVER_STATUS . $driverId;

        return Cache::remember($cacheKey, 60, function () use ($driverId) {
            try {
                $activeAttendance = Attendance::with('vehicle')
                    ->where('driver_id', $driverId)
                    ->whereNull('time_out')
                    ->first();

                if ($activeAttendance) {
                    return response()->json([
                        'status' => 'success',
                        'is_on_duty' => true,
                        'plate_number' => $activeAttendance->vehicle->plate_number ?? 'N/A'
                    ]);
                } else {
                    return response()->json([
                        'status' => 'success',
                        'is_on_duty' => false,
                        'plate_number' => null
                    ]);
                }
            } catch (\Exception $e) {
                Log::error("CheckDriverStatus Error: " . $e->getMessage());
                return response()->json([
                    'status' => 'error',
                    'message' => 'Service temporarily unavailable',
                    'is_on_duty' => false
                ], 503);
            }
        });
    }

    /**
     * Menyimpan absensi masuk ke database SQL
     */
    public function submitAttendance(Request $request)
    {
        $validated = $request->validate([
            'plate_number' => 'required|string',
            'gps_location' => 'required|string',
            'timestamp' => 'required|date_format:Y-m-d H:i:s',
            'speedometer_manual' => 'required|integer',
            'selfie_photo' => 'required|image|mimes:jpeg,jpg,png|max:5120',
            'speedometer_photo' => 'required|image|mimes:jpeg,jpg,png|max:5120',
            'car_condition_photo_1' => 'nullable|image|mimes:jpeg,jpg,png|max:5120',
            'car_condition_photo_2' => 'nullable|image|mimes:jpeg,jpg,png|max:5120',
        ]);

        try {
            $driver = Auth::user();

            $isOnDuty = Attendance::where('driver_id', $driver->id)
                ->whereNull('time_out')
                ->exists();
            if ($isOnDuty) {
                throw new \Exception("Driver sudah dalam status bertugas.");
            }

            $vehicle = Vehicle::firstOrCreate(
                ['plate_number' => $validated['plate_number']],
                ['type' => 'Otomatis Ditambah']
            );

            $selfieUrl = $this->optimizedImageProcessing($request->file('selfie_photo'));
            $speedoUrl = $this->optimizedImageProcessing($request->file('speedometer_photo'));
            $condition1Url = $request->hasFile('car_condition_photo_1')
                ? $this->optimizedImageProcessing($request->file('car_condition_photo_1'))
                : null;
            $condition2Url = $request->hasFile('car_condition_photo_2')
                ? $this->optimizedImageProcessing($request->file('car_condition_photo_2'))
                : null;

            Attendance::create([
                'driver_id' => $driver->id,
                'vehicle_id' => $vehicle->id,
                'time_in' => $validated['timestamp'],
                'gps_location_in' => $validated['gps_location'],
                'speedo_awal' => $validated['speedometer_manual'],
                'selfie_photo_path' => $selfieUrl,
                'speedo_photo_awal_path' => $speedoUrl,
                'condition_photo_1_path' => $condition1Url,
                'condition_photo_2_path' => $condition2Url,
            ]);

            $this->clearDriverCache($driver->id);

            Log::info("Attendance submitted successfully for driver: " . $driver->driver_id_nik);
            return response()->json([
                'status' => 'success',
                'message' => 'Absensi berhasil disimpan'
            ]);

        } catch (\Exception $e) {
            Log::error("SubmitAttendance Error: " . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'Terjadi kesalahan sistem: ' . $e->getMessage()
            ], 422);
        }
    }

    /**
     * [UPDATE] Menyimpan laporan akhir tugas ke database SQL
     * Dan mengembalikan data ringkasan untuk ditampilkan di HP
     */
    public function submitEndOfDutyReport(Request $request)
    {
        $validated = $request->validate([
            'speedometer_manual_akhir' => 'required|integer',
            'check_ban' => 'required|string',
            'check_lampu' => 'required|string',
            'check_rem' => 'required|string',
            'catatan' => 'nullable|string',
            'timestamp' => 'required|date_format:Y-m-d H:i:s',
            'speedometer_photo_akhir' => 'required|image|mimes:jpeg,jpg,png|max:5120',
        ]);

        try {
            $driver = Auth::user();

            // 1. Cari data absensi yang sedang aktif
            $activeAttendance = Attendance::with('vehicle') // Load relasi vehicle
                ->where('driver_id', $driver->id)
                ->whereNull('time_out')
                ->first();

            if (!$activeAttendance) {
                throw new \Exception("Tidak ditemukan data absensi masuk yang aktif.");
            }

            // 2. Proses foto speedometer akhir
            $speedoAkhirUrl = $this->optimizedImageProcessing($request->file('speedometer_photo_akhir'));

            // 3. Update data absensi
            $activeAttendance->update([
                'time_out' => $validated['timestamp'],
                'speedo_photo_akhir_path' => $speedoAkhirUrl,
                'catatan' => $validated['catatan'] ?? '',
                'check_ban' => $validated['check_ban'],
                'check_lampu' => $validated['check_lampu'],
                'check_rem' => $validated['check_rem'],
                'speedo_akhir' => $validated['speedometer_manual_akhir'],
            ]);

            // 4. Hapus cache status driver
            $this->clearDriverCache($driver->id);

            // ==================================================
            // 5. [BARU] HITUNG DATA RINGKASAN UNTUK HP
            // ==================================================

            // Hitung Durasi
            $waktuMasuk = Carbon::parse($activeAttendance->time_in);
            $waktuKeluar = Carbon::parse($validated['timestamp']);

            // Hitung selisih total menit dulu agar akurat
            $totalMenit = $waktuMasuk->diffInMinutes($waktuKeluar);
            $jam = floor($totalMenit / 60);
            $menit = $totalMenit % 60;
            $durasiKerja = "{$jam} Jam {$menit} Menit";

            // Hitung Jarak
            $jarak = $validated['speedometer_manual_akhir'] - $activeAttendance->speedo_awal;

            // Cek Kesehatan Mobil
            $masalah = [];
            if ($validated['check_ban'] == 'Bermasalah')
                $masalah[] = 'Ban';
            if ($validated['check_lampu'] == 'Bermasalah')
                $masalah[] = 'Lampu';
            if ($validated['check_rem'] == 'Bermasalah')
                $masalah[] = 'Rem';

            $statusKesehatan = empty($masalah) ? 'Prima' : 'Perlu Perbaikan';
            $detailMasalah = empty($masalah) ? 'Siap digunakan kembali' : implode(', ', $masalah);

            return response()->json([
                'status' => 'success',
                'message' => 'Laporan akhir tugas berhasil dikirim.',
                'data' => [
                    'driver_name' => $driver->full_name,
                    'plate_number' => $activeAttendance->vehicle->plate_number ?? 'N/A',
                    'waktu_keluar' => $waktuKeluar->format('H:i d-m-Y'),
                    'durasi_kerja' => $durasiKerja,
                    'total_km' => number_format($jarak) . " Km",
                    'vehicle_status' => $statusKesehatan,
                    'vehicle_issues' => $detailMasalah,
                ]
            ]);
            // ==================================================

        } catch (\Exception $e) {
            Log::error("SubmitEndOfDuty Error: " . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage()
            ], 422);
        }
    }

    /**
     * Menyimpan laporan darurat ke database SQL
     */
    public function submitEmergencyReport(Request $request)
    {
        $validated = $request->validate([
            'plate_number' => 'required|string',
            'gps_location' => 'required|string',
            'description' => 'required|string',
            'timestamp' => 'required|date_format:Y-m-d H:i:s',
            'proof_photo' => 'nullable|image|mimes:jpeg,jpg,png|max:5120',
        ]);

        try {
            $driver = Auth::user();

            $vehicle = Vehicle::where('plate_number', $validated['plate_number'])->first();
            if (!$vehicle) {
                throw new \Exception("Plat nomor tidak terdaftar.");
            }

            $proofPhotoUrl = $request->hasFile('proof_photo')
                ? $this->optimizedImageProcessing($request->file('proof_photo'))
                : null;

            EmergencyReport::create([
                'driver_id' => $driver->id,
                'vehicle_id' => $vehicle->id,
                'timestamp' => $validated['timestamp'],
                'gps_location' => $validated['gps_location'],
                'description' => $validated['description'],
                'proof_photo_path' => $proofPhotoUrl,
            ]);

            return response()->json([
                'status' => 'success',
                'message' => 'Laporan darurat berhasil dikirim.'
            ]);

        } catch (\Exception $e) {
            Log::error("SubmitEmergencyReport Error: " . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage()
            ], 422);
        }
    }

    /**
     * Mengambil riwayat absensi dari database SQL
     */
    public function getAttendanceHistory()
    {
        $driverId = Auth::id();
        $cacheKey = self::CACHE_ATTENDANCE_HISTORY . $driverId;

        return Cache::remember($cacheKey, 300, function () use ($driverId) {
            try {
                $history = Attendance::with('vehicle')
                    ->where('driver_id', $driverId)
                    ->orderBy('time_in', 'desc')
                    ->take(30)
                    ->get()
                    ->map(function ($item) {
                        return [
                            'jam_masuk' => $item->time_in ? Carbon::parse($item->time_in)->toDateTimeString() : '-',
                            'jam_keluar' => $item->time_out ? Carbon::parse($item->time_out)->toDateTimeString() : '-',
                            'plat_nomor' => $item->vehicle->plate_number ?? '-'
                        ];
                    });

                return response()->json([
                    'status' => 'success',
                    'data' => $history
                ]);

            } catch (\Exception $e) {
                Log::error("GetAttendanceHistory Error: " . $e->getMessage());
                return response()->json([
                    'status' => 'error',
                    'message' => 'Failed to load history',
                    'data' => []
                ], 500);
            }
        });
    }

    /**
     * Menghapus cache
     */
    public function clearCache()
    {
        try {
            $driverId = Auth::id();
            if ($driverId) {
                Cache::forget(self::CACHE_DRIVER_STATUS . $driverId);
                Cache::forget(self::CACHE_ATTENDANCE_HISTORY . $driverId);
            }
            return response()->json(['status' => 'success', 'message' => 'Cache cleared']);
        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 500);
        }
    }


    // --- FUNGSI HELPER ---

    private function optimizedImageProcessing($file)
    {
        $manager = new ImageManager(new GdDriver());
        $image = $manager->read($file);
        $image->scaleDown(width: 1200);
        $fileName = 'photos/' . uniqid('img_') . '.jpg';
        Storage::disk('public')->put($fileName, $image->encodeByMediaType('image/jpeg', 70));
        return $fileName;
    }

    private function clearDriverCache($driverId)
    {
        Cache::forget(self::CACHE_DRIVER_STATUS . $driverId);
        Cache::forget(self::CACHE_ATTENDANCE_HISTORY . $driverId);
    }
}