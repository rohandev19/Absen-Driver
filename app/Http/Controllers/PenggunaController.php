<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rules;

class PenggunaController extends Controller
{
    public function __construct()
    {
        // Hanya 'master-admin' yang boleh mengakses fungsi-fungsi ini
        // 'index' (melihat daftar) boleh diakses semua admin
        $this->middleware('can:is-master-admin')->except(['index']);
    }

    /**
     * Tampilkan daftar semua pengguna admin.
     */

    public function index()
    {
        $users = User::all();
        return view('admin.pengguna.index', compact('users'));
    }

    /**
     * Tampilkan form untuk membuat pengguna baru.
     */
    public function create()
    {
        return view('admin.pengguna.create');
    }

    /**
     * Simpan pengguna baru ke database.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:' . User::class],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
        ]);

        return redirect()->route('admin.pengguna.index')->with('success', 'Pengguna baru berhasil ditambahkan.');
    }

    /**
     * Tampilkan form untuk mengedit pengguna.
     */
    public function edit(User $pengguna) // Route model binding
    {
        return view('admin.pengguna.edit', compact('pengguna'));
    }

    /**
     * Perbarui pengguna di database.
     */
    public function update(Request $request, User $pengguna)
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:' . User::class . ',email,' . $pengguna->id],
            'password' => ['nullable', 'confirmed', Rules\Password::defaults()],
        ]);

        // Update data dasar
        $pengguna->name = $request->name;
        $pengguna->email = $request->email;

        // Update password HANYA JIKA diisi
        if ($request->filled('password')) {
            $pengguna->password = Hash::make($request->password);
        }

        $pengguna->save();

        return redirect()->route('admin.pengguna.index')->with('success', 'Data pengguna berhasil diperbarui.');
    }

    /**
     * Hapus pengguna dari database.
     */
    public function destroy(User $pengguna)
    {
        // PENTING: Mencegah admin menghapus akunnya sendiri
        if ($pengguna->id === Auth::id()) {
            return back()->with('error', 'Anda tidak dapat menghapus akun Anda sendiri.');
        }

        $pengguna->delete();
        return redirect()->route('admin.pengguna.index')->with('success', 'Pengguna berhasil dihapus.');
    }
}