<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Str; // <-- WAJIB: Untuk UUID
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
                'name' => $driver->full_name
            ]);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Service unavailable'], 503);
        }
    }

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
                return response()->json([
                    'status' => 'error',
                    'message' => 'Service temporarily unavailable',
                    'is_on_duty' => false
                ], 503);
            }
        });
    }

    // --- OPERASIONAL ---

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

            // 1. SANITY CHECK WAKTU (Anti Manipulasi Jam HP)
            // Jika jam HP lebih dari 10 menit ke depan dibanding server, tolak.
            $clientTime = Carbon::parse($validated['timestamp']);
            if ($clientTime->gt(Carbon::now()->addMinutes(10))) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Jam HP Anda tidak sesuai (Terlalu cepat). Mohon atur ke Otomatis.'
                ], 422);
            }

            // 2. Cek Status (Mencegah Double Login)
            $isOnDuty = Attendance::where('driver_id', $driver->id)
                ->whereNull('time_out')
                ->exists();
            
            if ($isOnDuty) {
                // Return 409 Conflict agar Mobile App tahu harus stop kirim data ini
                return response()->json([
                    'status' => 'error',
                    'message' => 'Server mencatat Anda masih bertugas. Mohon refresh status.'
                ], 409);
            }

            // 3. Auto Register Vehicle
            $vehicle = Vehicle::firstOrCreate(
                ['plate_number' => $validated['plate_number']],
                ['type' => 'Otomatis Ditambah']
            );

            // 4. Image Processing (UUID)
            $selfieUrl = $this->optimizedImageProcessing($request->file('selfie_photo'));
            $speedoUrl = $this->optimizedImageProcessing($request->file('speedometer_photo'));
            
            $condition1Url = $request->hasFile('car_condition_photo_1')
                ? $this->optimizedImageProcessing($request->file('car_condition_photo_1')) : null;
            $condition2Url = $request->hasFile('car_condition_photo_2')
                ? $this->optimizedImageProcessing($request->file('car_condition_photo_2')) : null;

            // 5. Simpan
            Attendance::create([
                'driver_id' => $driver->id,
                'vehicle_id' => $vehicle->id,
                'time_in' => $validated['timestamp'], // Gunakan waktu HP yang sudah divalidasi
                'created_at' => Carbon::now(), // Waktu Server mencatat
                'gps_location_in' => $validated['gps_location'],
                'speedo_awal' => $validated['speedometer_manual'],
                'selfie_photo_path' => $selfieUrl,
                'speedo_photo_awal_path' => $speedoUrl,
                'condition_photo_1_path' => $condition1Url,
                'condition_photo_2_path' => $condition2Url,
            ]);

            $this->clearDriverCacheLogic($driver->id);

            return response()->json([
                'status' => 'success',
                'message' => 'Absensi masuk berhasil disimpan'
            ]);

        } catch (\Exception $e) {
            Log::error("SubmitAttendance Error: " . $e->getMessage());
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 500);
        }
    }

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

            $activeAttendance = Attendance::with('vehicle')
                ->where('driver_id', $driver->id)
                ->whereNull('time_out')
                ->first();

            if (!$activeAttendance) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Tidak ada tugas aktif. Mungkin Anda sudah check-out?'
                ], 404);
            }

            $speedoAkhirUrl = $this->optimizedImageProcessing($request->file('speedometer_photo_akhir'));

            $activeAttendance->update([
                'time_out' => $validated['timestamp'],
                'speedo_photo_akhir_path' => $speedoAkhirUrl,
                'catatan' => $validated['catatan'] ?? '',
                'check_ban' => $validated['check_ban'],
                'check_lampu' => $validated['check_lampu'],
                'check_rem' => $validated['check_rem'],
                'speedo_akhir' => $validated['speedometer_manual_akhir'],
            ]);

            $this->clearDriverCacheLogic($driver->id);

            // Kalkulasi Summary
            $waktuMasuk = Carbon::parse($activeAttendance->time_in);
            $waktuKeluar = Carbon::parse($validated['timestamp']);
            $totalMenit = $waktuMasuk->diffInMinutes($waktuKeluar);
            $durasiKerja = floor($totalMenit / 60) . " Jam " . ($totalMenit % 60) . " Menit";

            $jarak = $validated['speedometer_manual_akhir'] - $activeAttendance->speedo_awal;
            if ($jarak < 0) $jarak = 0; 

            $masalah = [];
            if ($validated['check_ban'] == 'Bermasalah') $masalah[] = 'Ban';
            if ($validated['check_lampu'] == 'Bermasalah') $masalah[] = 'Lampu';
            if ($validated['check_rem'] == 'Bermasalah') $masalah[] = 'Rem';

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
                ]
            ]);

        } catch (\Exception $e) {
            Log::error("SubmitEndOfDuty Error: " . $e->getMessage());
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 500);
        }
    }

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
                // Buat temporary vehicle jika tidak ada, agar laporan tetap masuk
                $vehicle = Vehicle::firstOrCreate(['plate_number' => $validated['plate_number']], ['type' => 'Darurat']);
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
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 500);
        }
    }

    // --- HISTORY ---

    public function getAttendanceHistory()
    {
        $driverId = Auth::id();
        $cacheKey = self::CACHE_ATTENDANCE_HISTORY . $driverId;

        return Cache::remember($cacheKey, 300, function () use ($driverId) {
            return response()->json([
                'status' => 'success',
                'data' => Attendance::with('vehicle')
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
                    })
            ]);
        });
    }

    // --- HELPERS & UTILITIES ---

    // Method Public untuk Rute API /clear-cache
    public function clearCache()
    {
        $driverId = Auth::id();
        $this->clearDriverCacheLogic($driverId);
        return response()->json(['status' => 'success', 'message' => 'Cache cleared']);
    }

    // Logic Internal untuk hapus cache
    private function clearDriverCacheLogic($driverId)
    {
        Cache::forget(self::CACHE_DRIVER_STATUS . $driverId);
        Cache::forget(self::CACHE_ATTENDANCE_HISTORY . $driverId);
    }

    // Optimasi Gambar dengan UUID
    private function optimizedImageProcessing($file)
    {
        $manager = new ImageManager(new GdDriver());
        $image = $manager->read($file);
        
        $image->scaleDown(width: 1200);
        
        // GUNAKAN UUID agar nama file unik dan tidak tabrakan
        $fileName = 'photos/' . Str::uuid() . '.jpg';
        
        Storage::disk('public')->put($fileName, $image->encodeByMediaType('image/jpeg', 70));
        
        return $fileName;
    }
}