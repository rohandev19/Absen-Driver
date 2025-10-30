<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Exception;
use Google\Client as GoogleClient;
use Google\Service\Sheets as GoogleSheets;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\Drivers\Gd\Driver;
use Intervention\Image\ImageManager;

class AttendanceController extends Controller
{
    private $spreadsheetId = '1JaQaEjtOUOJTO1I0jsGItnqMrrnso-v2S_vzQ4nqqcs';
    private $cacheTimeout = 300;

    const CACHE_DRIVER_DETAILS = 'driver_details_';
    const CACHE_DRIVER_STATUS = 'driver_status_';
    const CACHE_ATTENDANCE_HISTORY = 'attendance_history_';

    public function getDriverDetails($driverId)
    {
        $cacheKey = self::CACHE_DRIVER_DETAILS . $driverId;

        return Cache::remember($cacheKey, $this->cacheTimeout, function () use ($driverId) {
            try {
                $driverName = $this->getDriverName($driverId);

                if ($driverName === 'Unknown') {
                    return response()->json(['message' => 'Driver not found'], 404);
                }

                return response()->json([
                    'id' => $driverId,
                    'name' => $driverName
                ]);

            } catch (Exception $e) {
                Log::error("GetDriverDetails Error: " . $e->getMessage());
                return response()->json(['message' => 'Service unavailable'], 503);
            }
        });
    }

    public function checkDriverStatus($driverId)
    {
        $cacheKey = self::CACHE_DRIVER_STATUS . $driverId;

        return Cache::remember($cacheKey, 60, function () use ($driverId) {
            try {
                $client = $this->getOptimizedGoogleClient();
                $sheetsService = new GoogleSheets($client);

                $rangeRead = 'Absensi!B:E';
                $response = $sheetsService->spreadsheets_values->get($this->spreadsheetId, $rangeRead);
                $rows = $response->getValues() ?? [];

                $status = ['is_on_duty' => false];

                if (count($rows) > 1) {
                    for ($i = count($rows) - 1; $i >= 1; $i--) {
                        $row = $rows[$i];
                        if (
                            isset($row[1]) && $row[1] == $driverId &&
                            (!isset($row[0]) || trim($row[0]) === '')
                        ) {
                            $status = [
                                'is_on_duty' => true,
                                'plate_number' => $row[3] ?? 'N/A'
                            ];
                            break;
                        }
                    }
                }

                return response()->json([
                    'status' => 'success',
                    'is_on_duty' => $status['is_on_duty'],
                    'plate_number' => $status['plate_number'] ?? null
                ]);

            } catch (Exception $e) {
                Log::error("CheckDriverStatus Error: " . $e->getMessage());
                return response()->json([
                    'status' => 'error',
                    'message' => 'Service temporarily unavailable',
                    'is_on_duty' => false
                ], 503);
            }
        });
    }

    public function submitAttendance(Request $request)
    {
        // Validasi dasar
        $validated = $request->validate([
            'driver_id' => 'required|string',
            'plate_number' => 'required|string',
            'gps_location' => 'required|string',
            'timestamp' => 'required|date_format:Y-m-d H:i:s',
            'speedometer_manual' => 'required|integer',
        ]);

        // Debug logging
        Log::info("SubmitAttendance Request Data: ", $request->all());
        Log::info("SubmitAttendance Files: ", array_keys($request->allFiles()));

        // Validasi file dengan error yang lebih spesifik
        if (!$request->hasFile('selfie_photo')) {
            return response()->json([
                'status' => 'error',
                'message' => 'Foto selfie wajib diambil.'
            ], 422);
        }

        if (!$request->hasFile('speedometer_photo')) {
            return response()->json([
                'status' => 'error',
                'message' => 'Foto speedometer wajib diambil.'
            ], 422);
        }

        // Validasi file type dan size
        $selfieFile = $request->file('selfie_photo');
        $speedoFile = $request->file('speedometer_photo');

        $validImageTypes = ['image/jpeg', 'image/jpg', 'image/png'];

        if (!in_array($selfieFile->getMimeType(), $validImageTypes)) {
            return response()->json([
                'status' => 'error',
                'message' => 'Format foto selfie tidak valid. Gunakan JPEG atau PNG.'
            ], 422);
        }

        if (!in_array($speedoFile->getMimeType(), $validImageTypes)) {
            return response()->json([
                'status' => 'error',
                'message' => 'Format foto speedometer tidak valid. Gunakan JPEG atau PNG.'
            ], 422);
        }

        // Validasi file size (max 5MB)
        if ($selfieFile->getSize() > 5242880) {
            return response()->json([
                'status' => 'error',
                'message' => 'Ukuran foto selfie terlalu besar. Maksimal 5MB.'
            ], 422);
        }

        if ($speedoFile->getSize() > 5242880) {
            return response()->json([
                'status' => 'error',
                'message' => 'Ukuran foto speedometer terlalu besar. Maksimal 5MB.'
            ], 422);
        }

        try {
            $driverName = Cache::remember('driver_' . $validated['driver_id'], 300, function () use ($validated) {
                $name = $this->getDriverName($validated['driver_id']);
                if ($name === 'Unknown') {
                    throw new Exception("ID Driver tidak terdaftar.");
                }
                return $name;
            });

            // Process images
            $selfieUrl = $this->optimizedImageProcessing($selfieFile);
            $speedoUrl = $this->optimizedImageProcessing($speedoFile);

            $condition1Url = $request->hasFile('car_condition_photo_1')
                ? $this->optimizedImageProcessing($request->file('car_condition_photo_1'))
                : '';

            $condition2Url = $request->hasFile('car_condition_photo_2')
                ? $this->optimizedImageProcessing($request->file('car_condition_photo_2'))
                : '';

            // Prepare data for Google Sheets
            $newRow = [
                $validated['timestamp'],
                '',
                $validated['driver_id'],
                $driverName,
                $validated['plate_number'],
                'https://maps.google.com/?q=' . $validated['gps_location'],
                '',
                $this->formatAsHyperlink($selfieUrl),
                $this->formatAsHyperlink($speedoUrl),
                '',
                $this->formatAsHyperlink($condition1Url),
                $this->formatAsHyperlink($condition2Url),
                '',
                '',
                '',
                '',
                $validated['speedometer_manual'],
                '',
                ''
            ];

            $this->appendToGoogleSheet($newRow);
            $this->clearDriverCache($validated['driver_id']);

            Log::info("Attendance submitted successfully for driver: " . $validated['driver_id']);

            return response()->json([
                'status' => 'success',
                'message' => 'Absensi berhasil disimpan'
            ]);

        } catch (Exception $e) {
            Log::error("SubmitAttendance Error: " . $e->getMessage());
            Log::error("SubmitAttendance Stack Trace: " . $e->getTraceAsString());

            return response()->json([
                'status' => 'error',
                'message' => 'Terjadi kesalahan sistem: ' . $e->getMessage()
            ], 422);
        }
    }

    public function submitEndOfDutyReport(Request $request)
    {
        try {
            $validated = $request->validate([
                'driver_id' => 'required|string',
                'speedometer_manual_akhir' => 'required|integer',
                'check_ban' => 'required|string',
                'check_lampu' => 'required|string',
                'check_rem' => 'required|string',
                'catatan' => 'nullable|string',
                'timestamp' => 'required|date_format:Y-m-d H:i:s',
            ]);

            if (!$request->hasFile('speedometer_photo_akhir')) {
                throw new Exception("Foto speedometer akhir wajib diisi.");
            }

            $client = $this->getOptimizedGoogleClient();
            $sheetsService = new GoogleSheets($client);

            $rangeRead = 'Absensi!A:C';
            $response = $sheetsService->spreadsheets_values->get($this->spreadsheetId, $rangeRead);
            $rows = $response->getValues() ?? [];

            $rowIndexToUpdate = -1;
            if (count($rows) > 1) {
                for ($i = count($rows) - 1; $i >= 1; $i--) {
                    $row = $rows[$i];
                    if (
                        isset($row[2]) && $row[2] == $validated['driver_id'] &&
                        (!isset($row[1]) || trim($row[1]) === '')
                    ) {
                        $rowIndexToUpdate = $i + 1;
                        break;
                    }
                }
            }

            if ($rowIndexToUpdate === -1) {
                throw new Exception("Tidak ditemukan data absensi masuk yang aktif.");
            }

            $speedoAkhirUrl = $this->optimizedImageProcessing($request->file('speedometer_photo_akhir'));

            $data = [
                new \Google_Service_Sheets_ValueRange([
                    'range' => 'Absensi!B' . $rowIndexToUpdate,
                    'values' => [[$validated['timestamp']]]
                ]),
                new \Google_Service_Sheets_ValueRange([
                    'range' => 'Absensi!J' . $rowIndexToUpdate,
                    'values' => [[$this->formatAsHyperlink($speedoAkhirUrl)]]
                ]),
                new \Google_Service_Sheets_ValueRange([
                    'range' => 'Absensi!M' . $rowIndexToUpdate,
                    'values' => [
                        [
                            $validated['catatan'] ?? '',
                            $validated['check_ban'],
                            $validated['check_lampu'],
                            $validated['check_rem']
                        ]
                    ]
                ]),
                new \Google_Service_Sheets_ValueRange([
                    'range' => 'Absensi!R' . $rowIndexToUpdate,
                    'values' => [[$validated['speedometer_manual_akhir']]]
                ]),
            ];

            $batchUpdateRequest = new \Google_Service_Sheets_BatchUpdateValuesRequest([
                'valueInputOption' => 'USER_ENTERED',
                'data' => $data
            ]);

            $sheetsService->spreadsheets_values->batchUpdate($this->spreadsheetId, $batchUpdateRequest);
            $this->clearDriverCache($validated['driver_id']);

            return response()->json([
                'status' => 'success',
                'message' => 'Laporan akhir tugas berhasil dikirim.'
            ]);

        } catch (Exception $e) {
            Log::error("SubmitEndOfDuty Error: " . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage()
            ], 422);
        }
    }

    public function submitEmergencyReport(Request $request)
    {
        try {
            $validated = $request->validate([
                'driver_id' => 'required|string',
                'plate_number' => 'required|string',
                'gps_location' => 'required|string',
                'description' => 'required|string',
                'timestamp' => 'required|date_format:Y-m-d H:i:s',
            ]);

            $proofPhotoUrl = $request->hasFile('proof_photo')
                ? $this->optimizedImageProcessing($request->file('proof_photo'))
                : '';

            $client = $this->getOptimizedGoogleClient();
            $sheetsService = new GoogleSheets($client);

            $newRow = [
                $validated['timestamp'],
                $validated['driver_id'],
                $validated['plate_number'],
                'https://maps.google.com/?q=' . $validated['gps_location'],
                $validated['description'],
                $this->formatAsHyperlink($proofPhotoUrl),
            ];

            $body = new \Google_Service_Sheets_ValueRange(['values' => [$newRow]]);
            $params = ['valueInputOption' => 'USER_ENTERED'];

            $sheetsService->spreadsheets_values->append(
                $this->spreadsheetId,
                'Laporan Masalah',
                $body,
                $params
            );

            return response()->json([
                'status' => 'success',
                'message' => 'Laporan darurat berhasil dikirim.'
            ]);

        } catch (Exception $e) {
            Log::error("SubmitEmergencyReport Error: " . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage()
            ], 422);
        }
    }

    public function getAttendanceHistory($driverId)
    {
        $cacheKey = self::CACHE_ATTENDANCE_HISTORY . $driverId;

        return Cache::remember($cacheKey, 300, function () use ($driverId) {
            try {
                $client = $this->getOptimizedGoogleClient();
                $sheetsService = new GoogleSheets($client);

                $rangeRead = 'Absensi!A:E';
                $response = $sheetsService->spreadsheets_values->get($this->spreadsheetId, $rangeRead);
                $rows = $response->getValues() ?? [];

                $history = [];
                if (count($rows) > 1) {
                    array_shift($rows);
                    $recentRows = array_slice(array_reverse($rows), 0, 50);

                    foreach ($recentRows as $row) {
                        if (isset($row[2]) && $row[2] == $driverId) {
                            $history[] = [
                                'jam_masuk' => $row[0] ?? '-',
                                'jam_keluar' => $row[1] ?? '-',
                                'plat_nomor' => $row[4] ?? '-'
                            ];
                            if (count($history) >= 30)
                                break;
                        }
                    }
                }

                return response()->json([
                    'status' => 'success',
                    'data' => $history
                ]);

            } catch (Exception $e) {
                Log::error("GetAttendanceHistory Error: " . $e->getMessage());
                return response()->json([
                    'status' => 'error',
                    'message' => 'Failed to load history',
                    'data' => []
                ], 500);
            }
        });
    }

    public function clearCache($driverId = null)
    {
        try {
            if ($driverId) {
                Cache::forget(self::CACHE_DRIVER_DETAILS . $driverId);
                Cache::forget(self::CACHE_DRIVER_STATUS . $driverId);
                Cache::forget(self::CACHE_ATTENDANCE_HISTORY . $driverId);
            } else {
                Cache::flush();
            }

            return response()->json(['status' => 'success', 'message' => 'Cache cleared']);
        } catch (Exception $e) {
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 500);
        }
    }

    private function getOptimizedGoogleClient()
    {
        $client = new GoogleClient();
        $client->setAuthConfig(config('services.google.credentials_path'));
        $client->addScope(GoogleSheets::SPREADSHEETS);
        $client->setHttpClient(new \GuzzleHttp\Client([
            'timeout' => 10,
            'connect_timeout' => 5,
            'read_timeout' => 10,
        ]));
        return $client;
    }

    private function optimizedImageProcessing($file)
    {
        $manager = new ImageManager(new Driver());
        $image = $manager->read($file);
        $image->scaleDown(width: 1200);

        $fileName = 'photos/' . uniqid('opt_') . '.jpg';
        Storage::disk('public')->put($fileName, $image->encodeByMediaType('image/jpeg', 70));

        return Storage::url($fileName);
    }

    private function appendToGoogleSheet($rowData, $retryCount = 0)
    {
        try {
            $client = $this->getOptimizedGoogleClient();
            $sheetsService = new GoogleSheets($client);

            $body = new \Google_Service_Sheets_ValueRange(['values' => [$rowData]]);
            $params = ['valueInputOption' => 'USER_ENTERED'];

            $sheetsService->spreadsheets_values->append(
                $this->spreadsheetId,
                'Absensi!A1',
                $body,
                $params
            );

        } catch (Exception $e) {
            if ($retryCount < 2) {
                Log::warning("Google Sheets retry attempt: " . ($retryCount + 1));
                sleep(1);
                $this->appendToGoogleSheet($rowData, $retryCount + 1);
            } else {
                throw $e;
            }
        }
    }

    private function clearDriverCache($driverId)
    {
        Cache::forget(self::CACHE_DRIVER_STATUS . $driverId);
        Cache::forget(self::CACHE_ATTENDANCE_HISTORY . $driverId);
    }

    private function getDriverName($driverId)
    {
        try {
            $client = $this->getOptimizedGoogleClient();
            $sheetsService = new GoogleSheets($client);

            $range = 'Daftar Driver!A:B';
            $response = $sheetsService->spreadsheets_values->get($this->spreadsheetId, $range);
            $rows = $response->getValues();

            if (!empty($rows)) {
                array_shift($rows);
                foreach ($rows as $row) {
                    if (isset($row[0]) && $row[0] == $driverId) {
                        return $row[1] ?? 'Unknown';
                    }
                }
            }
            return 'Unknown';
        } catch (Exception $e) {
            Log::error("GetDriverName Error: " . $e->getMessage());
            return 'Unknown';
        }
    }

    private function formatAsHyperlink($url, $text = 'Lihat Foto')
    {
        if (empty($url))
            return '';
        return sprintf('=HYPERLINK("%s"; "%s")', url($url), $text);
    }
} 