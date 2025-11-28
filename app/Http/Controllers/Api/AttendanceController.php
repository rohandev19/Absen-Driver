<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

// Models
use App\Models\Driver;
use App\Models\Vehicle;
use App\Models\Attendance;
use App\Models\EmergencyReport;

// Facades & Libraries
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use Carbon\Carbon;

// Image Processing
use Intervention\Image\Drivers\Gd\Driver as GdDriver;
use Intervention\Image\ImageManager;

/**
 * Class AttendanceController
 * * Mengontrol seluruh alur kerja operasional driver.
 * Mencakup: Check-in, Check-out, Cek Status (On-Duty), dan Laporan Darurat.
 * * Controller ini menggunakan CACHE untuk mengurangi beban database saat
 * aplikasi melakukan polling status driver setiap beberapa detik.
 * * @package App\Http\Controllers\Api
 */
class AttendanceController extends Controller
{
    // Konstanta untuk Key Cache agar konsisten
    const CACHE_DRIVER_STATUS = 'driver_status_';
    const CACHE_ATTENDANCE_HISTORY = 'attendance_history_';

    /**
     * Mengambil informasi dasar driver yang sedang login.
     * Digunakan oleh Mobile App untuk menampilkan nama di Header Home.
     * * @return \Illuminate\Http\JsonResponse
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
     * Mengecek status terkini driver (Apakah sedang bertugas?).
     * * PENTING: Menggunakan CACHE selama 60 detik.
     * Alasannya: Aplikasi mobile memanggil endpoint ini berulang-ulang (polling).
     * Tanpa cache, database akan terbebani oleh ribuan query "SELECT" yang sama.
     * * @return \Illuminate\Http\JsonResponse JSON berisi boolean 'is_on_duty' dan plat nomor aktif.
     */
    public function checkDriverStatus()
    {
        $driverId = Auth::id();
        $cacheKey = self::CACHE_DRIVER_STATUS . $driverId;

        // Cek Cache dulu, jika tidak ada baru query DB
        return Cache::remember($cacheKey, 60, function () use ($driverId) {
            try {
                // Cari data absensi yang time_out-nya masih NULL (belum pulang)
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
                // Return 503 agar aplikasi mobile tahu server sedang sibuk/error
                return response()->json([
                    'status' => 'error',
                    'message' => 'Service temporarily unavailable',
                    'is_on_duty' => false
                ], 503);
            }
        });
    }

    /**
     * Proses Absen Masuk (Check-In).
     * * Alur Logika:
     * 1. Validasi input (termasuk foto wajib).
     * 2. Cek apakah driver masih punya sesi aktif (mencegah double login).
     * 3. Cari atau Buat data Kendaraan (Vehicle) based on Plat Nomor.
     * 4. Kompresi gambar (Selfie & Speedometer) agar hemat storage.
     * 5. Simpan record baru ke tabel 'attendances'.
     * 6. Hapus Cache status driver agar status di HP langsung update.
     * * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function submitAttendance(Request $request)
    {
        $validated = $request->validate([
            'plate_number' => 'required|string',
            'gps_location' => 'required|string',
            'timestamp' => 'required|date_format:Y-m-d H:i:s', // Waktu dari Client
            'speedometer_manual' => 'required|integer',
            'selfie_photo' => 'required|image|mimes:jpeg,jpg,png|max:5120',
            'speedometer_photo' => 'required|image|mimes:jpeg,jpg,png|max:5120',
            // Foto kondisi mobil opsional (nullable)
            'car_condition_photo_1' => 'nullable|image|mimes:jpeg,jpg,png|max:5120',
            'car_condition_photo_2' => 'nullable|image|mimes:jpeg,jpg,png|max:5120',
        ]);

        try {
            $driver = Auth::user();

            // Guard Clause: Mencegah driver check-in 2x tanpa check-out
            $isOnDuty = Attendance::where('driver_id', $driver->id)
                ->whereNull('time_out')
                ->exists();
            
            if ($isOnDuty) {
                // Gunakan Log::warning untuk mencatat aktivitas mencurigakan
                Log::warning("Double Check-in Attempt: Driver {$driver->driver_id_nik}");
                throw new \Exception("Anda masih dalam status bertugas. Harap check-out terlebih dahulu.");
            }

            // Fitur: Auto-Register Vehicle jika plat nomor belum ada di DB
            $vehicle = Vehicle::firstOrCreate(
                ['plate_number' => $validated['plate_number']],
                ['type' => 'Otomatis Ditambah']
            );

            // Proses Optimasi Gambar (Resize & Compress)
            $selfieUrl = $this->optimizedImageProcessing($request->file('selfie_photo'));
            $speedoUrl = $this->optimizedImageProcessing($request->file('speedometer_photo'));
            
            $condition1Url = $request->hasFile('car_condition_photo_1')
                ? $this->optimizedImageProcessing($request->file('car_condition_photo_1'))
                : null;
            $condition2Url = $request->hasFile('car_condition_photo_2')
                ? $this->optimizedImageProcessing($request->file('car_condition_photo_2'))
                : null;

            // Simpan Data
            Attendance::create([
                'driver_id' => $driver->id,
                'vehicle_id' => $vehicle->id,
                'time_in' => $validated['timestamp'], // Sebaiknya gunakan Carbon::now() untuk keamanan waktu server
                'gps_location_in' => $validated['gps_location'],
                'speedo_awal' => $validated['speedometer_manual'],
                'selfie_photo_path' => $selfieUrl,
                'speedo_photo_awal_path' => $speedoUrl,
                'condition_photo_1_path' => $condition1Url,
                'condition_photo_2_path' => $condition2Url,
            ]);

            // INVALIDASI CACHE: Penting agar method checkDriverStatus mengambil data terbaru
            $this->clearDriverCache($driver->id);

            Log::info("Attendance IN Success: " . $driver->driver_id_nik);
            
            return response()->json([
                'status' => 'success',
                'message' => 'Absensi berhasil disimpan'
            ]);

        } catch (\Exception $e) {
            // Error handling aman (Log error asli, return pesan umum ke user)
            Log::error("SubmitAttendance Error: " . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'Gagal menyimpan absensi: ' . $e->getMessage() 
            ], 422);
        }
    }

    /**
     * Proses Absen Keluar (Check-Out) & Laporan Akhir Tugas.
     * * Method ini melakukan UPDATE pada baris absensi yang aktif (where time_out IS NULL).
     * * Fitur Tambahan:
     * Menghitung durasi kerja dan jarak tempuh secara real-time untuk ditampilkan
     * pada ringkasan di aplikasi HP setelah driver menekan tombol selesai.
     * * @param Request $request
     * @return \Illuminate\Http\JsonResponse Data ringkasan (Summary).
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

            // 1. Cari sesi aktif
            $activeAttendance = Attendance::with('vehicle')
                ->where('driver_id', $driver->id)
                ->whereNull('time_out')
                ->first();

            if (!$activeAttendance) {
                throw new \Exception("Tidak ditemukan sesi tugas aktif. Mungkin Anda sudah check-out?");
            }

            // 2. Proses Foto
            $speedoAkhirUrl = $this->optimizedImageProcessing($request->file('speedometer_photo_akhir'));

            // 3. Update DB (Finalisasi data)
            $activeAttendance->update([
                'time_out' => $validated['timestamp'],
                'speedo_photo_akhir_path' => $speedoAkhirUrl,
                'catatan' => $validated['catatan'] ?? '',
                'check_ban' => $validated['check_ban'],
                'check_lampu' => $validated['check_lampu'],
                'check_rem' => $validated['check_rem'],
                'speedo_akhir' => $validated['speedometer_manual_akhir'],
            ]);

            // 4. Hapus cache status
            $this->clearDriverCache($driver->id);

            // 5. Kalkulasi Ringkasan (Untuk UX di Mobile App)
            // Hitung Durasi
            $waktuMasuk = Carbon::parse($activeAttendance->time_in);
            $waktuKeluar = Carbon::parse($validated['timestamp']);
            $totalMenit = $waktuMasuk->diffInMinutes($waktuKeluar);
            $durasiKerja = floor($totalMenit / 60) . " Jam " . ($totalMenit % 60) . " Menit";

            // Hitung Jarak Tempuh
            $jarak = $validated['speedometer_manual_akhir'] - $activeAttendance->speedo_awal;
            // Validasi jarak negatif (jika driver salah input)
            if ($jarak < 0) $jarak = 0; 

            // Cek Kesehatan Mobil untuk Summary
            $masalah = [];
            if ($validated['check_ban'] == 'Bermasalah') $masalah[] = 'Ban';
            if ($validated['check_lampu'] == 'Bermasalah') $masalah[] = 'Lampu';
            if ($validated['check_rem'] == 'Bermasalah') $masalah[] = 'Rem';

            return response()->json([
                'status' => 'success',
                'message' => 'Laporan akhir tugas berhasil dikirim.',
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
            return response()->json([
                'status' => 'error',
                'message' => 'Gagal check-out: ' . $e->getMessage()
            ], 422);
        }
    }

    /**
     * Menerima laporan masalah darurat di jalan.
     * Memastikan plat nomor valid sebelum menyimpan laporan.
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

            // Validasi: Plat nomor harus terdaftar di sistem
            $vehicle = Vehicle::where('plate_number', $validated['plate_number'])->first();
            if (!$vehicle) {
                throw new \Exception("Plat nomor tidak ditemukan di database.");
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
                'message' => 'Laporan darurat berhasil dikirim. Admin telah dinotifikasi.'
            ]);

        } catch (\Exception $e) {
            Log::error("SubmitEmergencyReport Error: " . $e->getMessage());
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 422);
        }
    }

    /**
     * Mengambil 30 riwayat absensi terakhir driver.
     * Data di-cache selama 5 menit (300 detik) untuk efisiensi.
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
                    'message' => 'Gagal memuat riwayat',
                    'data' => []
                ], 500);
            }
        });
    }

    /**
     * Helper: Menghapus cache spesifik driver.
     * Dipanggil setiap kali ada perubahan status (Check-in/Check-out).
     */
    private function clearDriverCache($driverId)
    {
        Cache::forget(self::CACHE_DRIVER_STATUS . $driverId);
        Cache::forget(self::CACHE_ATTENDANCE_HISTORY . $driverId);
    }

    /**
     * Helper: Optimasi Gambar menggunakan Intervention Image.
     * - Resize lebar max 1200px (agar tidak terlalu besar).
     * - Encode ke JPG kualitas 70% (hemat storage & bandwidth).
     * - Generate nama file unik.
     * * @param \Illuminate\Http\UploadedFile $file
     * @return string Path file relatif (storage/...)
     */
    private function optimizedImageProcessing($file)
    {
        $manager = new ImageManager(new GdDriver());
        $image = $manager->read($file);
        
        // Resize hanya jika lebar > 1200px, aspect ratio tetap
        $image->scaleDown(width: 1200);
        
        // Nama file unik (menggunakan uniqid time-based)
        $fileName = 'photos/' . uniqid('img_') . '.jpg';
        
        // Simpan ke storage public
        Storage::disk('public')->put($fileName, $image->encodeByMediaType('image/jpeg', 70));
        
        return $fileName;
    }
}