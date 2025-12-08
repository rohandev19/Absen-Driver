<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Driver;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use Carbon\Carbon;

class AuthController extends Controller
{
    public function login(Request $request)
    {
        try {
            $validated = $request->validate([
                'driver_id' => 'required|string',
                'password' => 'required|string',
            ]);

            $driver = Driver::where('driver_id_nik', $validated['driver_id'])->first();

            if (!$driver || !Hash::check($validated['password'], $driver->password)) {
                throw ValidationException::withMessages([
                    'message' => 'ID Driver atau Password salah.',
                ]);
            }

            // SINGLE DEVICE LOGIN: Hapus token lama
            $driver->tokens()->delete();

            // Buat Token Baru
            $token = $driver->createToken('flutter-app-token')->plainTextToken;

            // Cek SIM
            $simStatus = $this->calculateSimStatus($driver);

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
            Log::error("Login Error [ID: {$request->driver_id}]: " . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'Terjadi kesalahan pada server.'
            ], 500);
        }
    }

    public function changePassword(Request $request)
    {
        try {
            $request->validate([
                'current_password' => 'required',
                'new_password' => 'required|min:6|confirmed',
            ]);

            $driver = $request->user();

            if (!Hash::check($request->current_password, $driver->password)) {
                return response()->json(['status' => 'error', 'message' => 'Password lama salah.'], 422);
            }

            $driver->password = Hash::make($request->new_password);
            $driver->save();

            // Opsional: Hapus semua token lain agar device lain ter-logout
            // $driver->tokens()->where('id', '!=', $request->user()->currentAccessToken()->id)->delete();

            return response()->json(['status' => 'success', 'message' => 'Password berhasil diubah.']);

        } catch (ValidationException $e) {
            return response()->json(['status' => 'error', 'message' => $e->validator->errors()->first()], 422);
        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => 'Gagal mengubah password.'], 500);
        }
    }

    public function logout(Request $request)
    {
        try {
            $request->user()->currentAccessToken()->delete();
            return response()->json(['status' => 'success', 'message' => 'Logout berhasil']);
        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => 'Gagal logout.'], 500);
        }
    }

    private function calculateSimStatus($driver)
    {
        $simStatus = ['status' => 'aman', 'message' => '', 'is_expired' => false];

        if ($driver->sim_expiry_date) {
            try {
                $expiryDate = Carbon::parse($driver->sim_expiry_date)->startOfDay();
                $today = Carbon::now()->startOfDay();
                $daysLeft = $today->diffInDays($expiryDate, false);

                if ($daysLeft < 0) {
                    $simStatus = [
                        'status' => 'danger',
                        'is_expired' => true,
                        'message' => "PERINGATAN: SIM MATI sejak " . abs($daysLeft) . " hari lalu. Akun dibekukan."
                    ];
                } elseif ($daysLeft <= 30) {
                    $simStatus = [
                        'status' => 'warning',
                        'is_expired' => false,
                        'message' => "Masa berlaku SIM habis dalam $daysLeft hari."
                    ];
                }
            } catch (\Exception $e) {
                // Ignore date parse error
            }
        } else {
            $simStatus = [
                'status' => 'warning',
                'is_expired' => false,
                'message' => "Data SIM belum lengkap."
            ];
        }
        return $simStatus;
    }
}