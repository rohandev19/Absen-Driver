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

    // --- OPERASIONAL ---

    /**
     * PROSES ABSEN MASUK (CHECK-IN)
     */
    public function submitAttendance(Request $request)
    {
        $validated = $request->validate([
            'plate_number' => 'required|string',
            'gps_location' => ['required', 'string', 'regex:/^[-]?\d+(\.\d+)?,\s*[-]?\d+(\.\d+)?$/'],
            'timestamp' => 'required|date_format:Y-m-d H:i:s',
            'speedometer_manual' => 'required|integer',
            'selfie_photo' => 'required|image|mimes:jpeg,jpg,png|max:5120',
            'speedometer_photo' => 'required|image|mimes:jpeg,jpg,png|max:5120',
            'car_condition_photo_1' => 'nullable|image|mimes:jpeg,jpg,png|max:5120',
            'car_condition_photo_2' => 'nullable|image|mimes:jpeg,jpg,png|max:5120',
        ]);

        try {
            $driver = Auth::user();

            $clientTime = Carbon::parse($validated['timestamp']);
            if ($clientTime->gt(Carbon::now()->addMinutes(10))) {
                return response()->json(['status' => 'error', 'message' => 'Jam HP Anda tidak sesuai.'], 422);
            }

            $isOnDuty = Attendance::where('driver_id', $driver->id)->whereNull('time_out')->exists();
            if ($isOnDuty) {
                return response()->json(['status' => 'error', 'message' => 'Server mencatat Anda masih bertugas.'], 409);
            }

            $vehicle = Vehicle::firstOrCreate(
                ['plate_number' => strtoupper($validated['plate_number'])],
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
                'created_at' => Carbon::now(),
                'gps_location_in' => $validated['gps_location'],
                'speedo_awal' => $validated['speedometer_manual'],
                'selfie_photo_path' => $selfieUrl,
                'speedo_photo_awal_path' => $speedoUrl,
                'condition_photo_1_path' => $condition1Url,
                'condition_photo_2_path' => $condition2Url,
            ]);

            $this->clearDriverCacheLogic($driver->id);

            return response()->json(['status' => 'success', 'message' => 'Absensi masuk berhasil.']);

        } catch (\Exception $e) {
            Log::error("SubmitAttendance Error: " . $e->getMessage());
            return response()->json(['status' => 'error', 'message' => 'Gagal menyimpan absensi.'], 500);
        }
    }

    /**
     * PROSES ABSEN PULANG (CHECK-OUT)
     */
    public function submitEndOfDutyReport(Request $request)
    {
        $validated = $request->validate([
            'speedometer_manual_akhir' => 'required|integer',
            'gps_location' => ['nullable', 'string', 'regex:/^[-]?\d+(\.\d+)?,\s*[-]?\d+(\.\d+)?$/'],
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
                return response()->json(['status' => 'error', 'message' => 'Tidak ada tugas aktif.'], 404);
            }

            $speedoAkhirUrl = $this->optimizedImageProcessing($request->file('speedometer_photo_akhir'));

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

        } catch (\Exception $e) {
            Log::error("SubmitEndOfDuty Error: " . $e->getMessage());
            return response()->json(['status' => 'error', 'message' => 'Gagal mengakhiri tugas.'], 500);
        }
    }

    public function submitEmergencyReport(Request $request)
    {
        $validated = $request->validate([
            'plate_number' => 'required|string',
            'gps_location' => ['required', 'string', 'regex:/^[-]?\d+(\.\d+)?,\s*[-]?\d+(\.\d+)?$/'],
            'description' => 'required|string',
            'timestamp' => 'required|date_format:Y-m-d H:i:s',
            'proof_photo' => 'nullable|image|mimes:jpeg,jpg,png|max:5120',
        ]);

        try {
            $driver = Auth::user();
            $vehicle = Vehicle::firstOrCreate(
                ['plate_number' => strtoupper($validated['plate_number'])],
                ['type' => 'Darurat']
            );
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
                        return [
                            'jam_masuk' => $item->time_in
                                ? Carbon::parse($item->time_in)->toDateTimeString()
                                : '-',
                            'jam_keluar' => $item->time_out
                                ? Carbon::parse($item->time_out)->toDateTimeString()
                                : '-',
                            'plat_nomor' => $item->vehicle->plate_number ?? '-',
                        ];
                    })
                    ->toArray(); // Simpan sebagai array murni, bukan Collection
            });

            return response()->json([
                'status' => 'success',
                'data' => $cachedData,
            ]);

        } catch (\Exception $e) {
            Log::error("GetAttendanceHistory Error: " . $e->getMessage());
            return response()->json(['status' => 'error', 'message' => 'Gagal memuat riwayat.'], 500);
        }
    }

    public function clearCache()
    {
        $driverId = Auth::id();
        $this->clearDriverCacheLogic($driverId);
        return response()->json(['status' => 'success', 'message' => 'Cache cleared']);
    }

    private function clearDriverCacheLogic($driverId)
    {
        Cache::forget(self::CACHE_DRIVER_STATUS . $driverId);
        Cache::forget(self::CACHE_ATTENDANCE_HISTORY . $driverId);
    }

    private function optimizedImageProcessing($file)
    {
        $manager = new ImageManager(new GdDriver());
        $image = $manager->read($file);
        $image->scaleDown(width: 1200);
        $fileName = 'photos/' . Str::uuid() . '.jpg';
        Storage::disk('public')->put($fileName, $image->encodeByMediaType('image/jpeg', 70));
        return $fileName;
    }
}