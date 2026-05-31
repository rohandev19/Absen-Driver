<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

/**
 * CustomerProfileController
 * 
 * Handles customer profile viewing and password management.
 */
class CustomerProfileController extends Controller
{
    /**
     * Show customer profile page.
     */
    public function showProfile()
    {
        $user = Auth::user();
        $customer = $user->customer;

        return view('customer.profile.index', compact('user', 'customer'));
    }

    /**
     * Show change password form.
     */
    public function showChangePasswordForm()
    {
        return view('customer.profile.change-password');
    }

    /**
     * Process password change.
     */
    public function changePassword(Request $request)
    {
        $request->validate([
            'current_password' => 'required',
            'new_password' => [
                'required',
                'confirmed',
                Password::min(8)
                    ->mixedCase()
                    ->numbers()
                    ->symbols(),
            ],
        ], [
            'current_password.required' => 'Password lama wajib diisi.',
            'new_password.required' => 'Password baru wajib diisi.',
            'new_password.confirmed' => 'Konfirmasi password tidak cocok.',
            'new_password.min' => 'Password minimal 8 karakter.',
        ]);

        $user = Auth::user();

        if (!Hash::check($request->current_password, $user->password)) {
            return back()->withErrors(['current_password' => 'Password lama salah.']);
        }

        $user->update([
            'password' => Hash::make($request->new_password),
        ]);

        return redirect()->route('customer.profile')
            ->with('success', 'Password berhasil diubah.');
    }
}
