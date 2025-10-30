<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Exception;
use Google\Client as GoogleClient;
use Google\Service\Sheets as GoogleSheets;
use Illuminate\Http\Request;

class AuthController extends Controller
{
    private $spreadsheetId = '1JaQaEjtOUOJTO1I0jsGItnqMrrnso-v2S_vzQ4nqqcs';

    /**
     * Menangani permintaan login dari driver.
     */
    public function login(Request $request)
    {
        try {
            // 1. Validasi input dari aplikasi Flutter.
            $validated = $request->validate([
                'driver_id' => 'required|string',
                'password' => 'required|string',
            ]);

            // 2. Hubungkan ke Google Sheets.
            $client = new GoogleClient();
            $client->setAuthConfig(config('services.google.credentials_path'));
            $client->addScope(GoogleSheets::SPREADSHEETS_READONLY);

            $sheetsService = new GoogleSheets($client);

            // 3. Baca semua data dari sheet 'Daftar Driver'.
            $range = 'Daftar Driver!A:C'; // Kolom A: ID, B: Nama, C: Password.
            $response = $sheetsService->spreadsheets_values->get($this->spreadsheetId, $range);
            $rows = $response->getValues();

            $driverFound = false;
            $driverData = null;

            // 4. Cari driver berdasarkan ID dan cocokkan password.
            if (!empty($rows)) {
                array_shift($rows); // Lewati baris header.

                foreach ($rows as $row) {
                    // Cek jika ID Driver cocok.
                    if (isset($row[0]) && $row[0] == $validated['driver_id']) {
                        // Cek jika password cocok.
                        if (isset($row[2]) && $row[2] == $validated['password']) {
                            $driverFound = true;
                            $driverData = [
                                'driver_id' => $row[0],
                                'full_name' => $row[1] ?? 'Nama tidak ditemukan',
                            ];
                        }
                        break; // Hentikan pencarian setelah ID ditemukan.
                    }
                }
            }

            // 5. Kirim respons berdasarkan hasil pencocokan.
            if ($driverFound) {
                return response()->json([
                    'status' => 'success',
                    'message' => 'Login berhasil!',
                    'data' => $driverData
                ]);
            } else {
                // Jika ID tidak ditemukan atau password salah.
                throw new Exception("ID Driver atau Password salah.");
            }

        } catch (Exception $e) {
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 401); // 401 Unauthorized.
        }
    }
}

