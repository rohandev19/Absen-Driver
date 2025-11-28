<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Driver;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use Carbon\Carbon;

/**
 * Class AuthController
 * * Mengelola otentikasi driver untuk aplikasi mobile (API).
 * Mencakup Login, Ganti Password, dan Logout.
 * * @package App\Http\Controllers\Api
 */
class AuthController extends Controller
{
    /**
     * Menangani proses login driver via API Mobile.
     * * Method ini melakukan validasi NIK dan Password, kemudian
     * mengecek status masa berlaku SIM (Aktif/Warning/Expired).
     * Jika SIM expired, driver akan diberi peringatan keras.
     * * @param  Request  $request  Input: driver_id (NIK) dan password.
     * @return \Illuminate\Http\JsonResponse  Return JSON berisi Token API dan Status SIM.
     */
    public function login(Request $request)
    {
        try {
            // 1. Validasi input
            $validated = $request->validate([
                'driver_id' => 'required|string',
                'password' => 'required|string',
            ]);

            // 2. Cari driver berdasarkan NIK
            $driver = Driver::where('driver_id_nik', $validated['driver_id'])->first();

            // 3. Verifikasi password
            if (!$driver || !Hash::check($validated['password'], $driver->password)) {
                throw ValidationException::withMessages([
                    'message' => 'ID Driver atau Password salah.',
                ]);
            }

            // --- UPDATE: SINGLE DEVICE LOGIN ---
            // Hapus semua token lama milik driver ini sebelum membuat yang baru.
            // Ini akan memaksa logout di perangkat lain (mencegah titip absen).
            $driver->tokens()->delete();

            // 4. Buat Token Baru (Sanctum)
            $token = $driver->createToken('flutter-app-token')->plainTextToken;

            // 5. Cek Status SIM
            $simStatus = $this->calculateSimStatus($driver);

            // 6. Kirim respons JSON ke Flutter
            return response()->json([
                'status' => 'success',
                'message' => 'Login berhasil!',
                'data' => [
                    'driver_id' => $driver->driver_id_nik,
                    'full_name' => $driver->full_name,
                ],
                'token' => $token,
                'sim_alert' => $simStatus
            ]);

        } catch (ValidationException $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->validator->errors()->first('message') ?? 'Data input tidak valid.'
            ], 422);

        } catch (\Exception $e) {
            // Log error asli untuk debugging admin, sembunyikan dari user
            Log::error("Login Error [ID: {$request->driver_id}]: " . $e->getMessage());

            return response()->json([
                'status' => 'error',
                'message' => 'Terjadi kesalahan pada server. Silakan coba lagi nanti.'
            ], 500);
        }
    }

    /**
     * Mengubah kata sandi driver yang sedang login.
     * * Driver wajib memasukkan password lama untuk verifikasi keamanan
     * sebelum menggantinya dengan password baru.
     * * @param  Request  $request  Input: current_password, new_password.
     * @return \Illuminate\Http\JsonResponse
     */
    public function changePassword(Request $request)
    {
        try {
            $request->validate([
                'current_password' => 'required',
                'new_password' => 'required|min:6|confirmed',
            ]);

            $driver = $request->user();

            // Cek kesesuaian password lama
            if (!Hash::check($request->current_password, $driver->password)) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Password lama tidak sesuai.'
                ], 422);
            }

            // Update ke password baru (Hashed)
            $driver->password = Hash::make($request->new_password);
            $driver->save();

            return response()->json([
                'status' => 'success',
                'message' => 'Password berhasil diubah.'
            ]);

        } catch (ValidationException $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->validator->errors()->first()
            ], 422);
        } catch (\Exception $e) {
            Log::error("Change Password Error: " . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'Gagal mengubah password.'
            ], 500);
        }
    }

    /**
     * Melakukan Logout (Hapus Token).
     * * Menghapus token akses saat ini (currentAccessToken) agar tidak bisa
     * digunakan kembali.
     * * @param  Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function logout(Request $request)
    {
        try {
            $request->user()->currentAccessToken()->delete();

            return response()->json([
                'status' => 'success',
                'message' => 'Logout berhasil'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Gagal logout.'
            ], 500);
        }
    }

    /**
     * Helper: Menghitung status masa berlaku SIM.
     * * Membandingkan tanggal kadaluarsa SIM dengan tanggal hari ini.
     * - Expired (H < 0): Status 'danger'
     * - Warning (H <= 30): Status 'warning'
     * - Aman (H > 30): Status 'aman'
     * * @param  \App\Models\Driver  $driver  Objek driver yang login.
     * @return array  Array berisi status, message, dan flag is_expired.
     */
    private function calculateSimStatus($driver)
    {
        $simStatus = [
            'status' => 'aman',
            'message' => '',
            'is_expired' => false
        ];

        if ($driver->sim_expiry_date) {
            try {
                $expiryDate = Carbon::parse($driver->sim_expiry_date)->startOfDay();
                $today = Carbon::now()->startOfDay();

                // Hitung selisih hari (false = agar bisa negatif jika sudah lewat)
                $daysLeft = $today->diffInDays($expiryDate, false);

                if ($daysLeft < 0) {
                    $simStatus = [
                        'status' => 'danger',
                        'is_expired' => true,
                        'message' => "PERINGATAN: SIM Anda telah MATI sejak " . abs($daysLeft) . " hari lalu. Akun Anda dibekukan sementara. Hubungi admin."
                    ];
                } elseif ($daysLeft <= 30) {
                    $simStatus = [
                        'status' => 'warning',
                        'is_expired' => false,
                        'message' => "Masa berlaku SIM Anda akan habis dalam $daysLeft hari lagi (" . $expiryDate->format('d M Y') . "). Segera perpanjang!"
                    ];
                }
            } catch (\Exception $e) {
                Log::warning("SIM Date Parse Error Driver {$driver->id}: " . $e->getMessage());
                $simStatus['message'] = "Format tanggal SIM tidak valid.";
            }
        } else {
            $simStatus = [
                'status' => 'warning',
                'is_expired' => false,
                'message' => "Data tanggal SIM Anda belum lengkap. Mohon hubungi Admin untuk update data."
            ];
        }

        return $simStatus;
    }
}