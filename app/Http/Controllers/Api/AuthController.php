<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Driver;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Carbon\Carbon; // <--- PENTING: Tambahkan ini untuk hitung tanggal

class AuthController extends Controller
{
    /**
     * Menangani permintaan login dari driver (API).
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

            // 4. Buat Token
            $token = $driver->createToken('flutter-app-token')->plainTextToken;

            // ======================================================
            // 5. LOGIKA BARU: CEK STATUS SIM UNTUK JSON
            // ======================================================
            $simStatus = [
                'status' => 'aman',      // Default: aman
                'message' => '',         // Pesan untuk ditampilkan
                'is_expired' => false    // Flag untuk mengunci aplikasi
            ];

            if ($driver->sim_expiry_date) {
                $expiryDate = Carbon::parse($driver->sim_expiry_date)->startOfDay();
                $today = Carbon::now()->startOfDay();

                // Hitung selisih hari (false = agar bisa negatif jika sudah lewat)
                $daysLeft = $today->diffInDays($expiryDate, false);

                if ($daysLeft < 0) {
                    // KASUS: SUDAH MATI (Expired)
                    $simStatus = [
                        'status' => 'danger',
                        'is_expired' => true,
                        'message' => "PERINGATAN: SIM Anda telah MATI sejak " . abs($daysLeft) . " hari lalu. Akun Anda dibekukan sementara. Hubungi admin."
                    ];
                } elseif ($daysLeft <= 30) {
                    // KASUS: AKAN MATI (H-30)
                    $simStatus = [
                        'status' => 'warning',
                        'is_expired' => false,
                        'message' => "Masa berlaku SIM Anda akan habis dalam $daysLeft hari lagi (" . $expiryDate->format('d M Y') . "). Segera perpanjang!"
                    ];
                }
                // Jika > 30 hari, biarkan default 'aman'
            } else {
                // KASUS: DATA TANGGAL SIM KOSONG
                $simStatus = [
                    'status' => 'warning',
                    'is_expired' => false,
                    'message' => "Data tanggal SIM Anda belum lengkap. Mohon hubungi Admin untuk update data."
                ];
            }
            // ======================================================


            // 6. Kirim respons JSON ke Flutter
            return response()->json([
                'status' => 'success',
                'message' => 'Login berhasil!',
                'data' => [
                    'driver_id' => $driver->driver_id_nik,
                    'full_name' => $driver->full_name,
                    // Anda bisa kirim data lain jika perlu
                ],
                'token' => $token,

                // INI DATA YANG DITUNGGU FLUTTER:
                'sim_alert' => $simStatus
            ]);

        } catch (ValidationException $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->validator->errors()->first('message')
            ], 422);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Terjadi kesalahan server: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Ganti Password Driver
     */
    public function changePassword(Request $request)
    {
        $request->validate([
            'current_password' => 'required',
            'new_password' => 'required|min:6|confirmed', // Butuh field 'new_password_confirmation' di Flutter
        ]);

        $driver = $request->user();

        // 1. Cek password lama
        if (!Hash::check($request->current_password, $driver->password)) {
            return response()->json([
                'message' => 'Password lama tidak sesuai.'
            ], 422);
        }

        // 2. Update password baru
        $driver->password = Hash::make($request->new_password);
        $driver->save();

        return response()->json([
            'message' => 'Password berhasil diubah.'
        ]);
    }

    /**
     * Logout API
     */
    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'Logout berhasil'
        ]);
    }
}