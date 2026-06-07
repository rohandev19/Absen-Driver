<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * Class AdminLoginController
 * * Mengatur proses otentikasi khusus untuk Administrator Web.
 * Controller ini menggunakan Session-based Authentication (bukan Token),
 * karena digunakan di browser.
 * * @package App\Http\Controllers\Auth
 */
class AdminLoginController extends Controller
{
    /**
     * Menampilkan halaman/form login admin.
     * * @return \Illuminate\View\View
     */
    public function showLoginForm()
    {
        return view('auth.admin-login');
    }

    /**
     * Memproses data login yang dikirim dari form.
     * * Fitur Keamanan:
     * 1. Validasi Input (Email & Password wajib).
     * 2. Session Regeneration: Mencegah serangan "Session Fixation"
     * di mana hacker menggunakan ID sesi lama korban.
     * * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function login(Request $request)
    {
        // 1. Validasi input agar tidak kosong
        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required|string',
        ]);

        $throttleKey = \Illuminate\Support\Str::lower($request->input('email')) . '|' . $request->ip();

        if (\Illuminate\Support\Facades\RateLimiter::tooManyAttempts($throttleKey, 5)) {
            \Illuminate\Support\Facades\Log::warning("Admin login locked out", ['email' => $request->email, 'ip' => $request->ip()]);
            $seconds = \Illuminate\Support\Facades\RateLimiter::availableIn($throttleKey);
            return back()->withErrors([
                'email' => "Terlalu banyak percobaan login. Silakan coba lagi dalam {$seconds} detik.",
            ])->onlyInput('email');
        }

        // 2. Coba login menggunakan Facade Auth (Guard default: web)
        // 'remember' akan membuat cookie "Remember Me" jika dicentang
        if (Auth::attempt($credentials, $request->boolean('remember'))) {

            \Illuminate\Support\Facades\RateLimiter::clear($throttleKey);

            // SECURITY CRITICAL: Regenerasi ID Sesi setelah login sukses
            // Ini wajib untuk mencegah Session Fixation attacks.
            $request->session()->regenerate();

            // 3. Redirect based on user role
            $user = Auth::user();
            $redirectRoute = match ($user->role) {
                'master' => route('admin.dashboard'),
                'service_admin' => route('admin.service.index'),
                'customer' => route('customer.dashboard'),
                default => route('admin.dashboard'),
            };

            return redirect()->intended($redirectRoute);
        }

        \Illuminate\Support\Facades\RateLimiter::hit($throttleKey, 300); // 5 minutes lockout
        \Illuminate\Support\Facades\Log::warning("Failed admin login", ['email' => $request->email, 'ip' => $request->ip()]);

        // 4. Jika gagal, kembalikan ke form dengan pesan error
        // 'onlyInput' mengembalikan email agar user tidak perlu mengetik ulang
        // Sesuai BUG #38, pesan generic "Email atau Password yang Anda masukkan salah." tetap dipertahankan
        return back()->withErrors([
            'email' => 'Email atau Password yang Anda masukkan salah.',
        ])->onlyInput('email');
    }

    /**
     * Memproses logout admin.
     * * Membersihkan sesi, menghapus data auth, dan meregenerasi token CSRF
     * untuk memastikan sesi benar-benar bersih.
     * * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function logout(Request $request)
    {
        Auth::logout();

        // Invalidasi sesi PHP saat ini
        $request->session()->invalidate();

        // Generate ulang CSRF token baru untuk form login berikutnya
        $request->session()->regenerateToken();

        return redirect()->route('admin.login');
    }
}