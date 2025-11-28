<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rules;

/**
 * Class PenggunaController
 * * Mengelola data Administrator (Pengguna Web).
 * * Controller ini dilindungi oleh Middleware Authorization.
 * Hanya 'Master Admin' yang memiliki akses penuh (Create, Edit, Delete).
 * Admin biasa hanya bisa melihat daftar (Index).
 * * @package App\Http\Controllers
 */
class PenggunaController extends Controller
{
    /**
     * Constructor untuk menerapkan Middleware.
     * * Memastikan hanya Master Admin yang bisa mengubah data.
     */
    public function __construct()
    {
        // Hanya 'master-admin' yang boleh mengakses fungsi Create, Store, Edit, Update, Destroy.
        // Fungsi 'index' (melihat daftar) dikecualikan, sehingga semua admin bisa melihatnya.
        $this->middleware('can:is-master-admin')->except(['index']);
    }

    /**
     * Menampilkan daftar semua pengguna admin.
     * * Mengambil seluruh data User dari database untuk ditampilkan di tabel manajemen.
     * * @return \Illuminate\View\View
     */
    public function index()
    {
        $users = User::all();
        return view('admin.pengguna.index', compact('users'));
    }

    /**
     * Menampilkan formulir pembuatan pengguna admin baru.
     * * @return \Illuminate\View\View
     */
    public function create()
    {
        return view('admin.pengguna.create');
    }

    /**
     * Menyimpan pengguna admin baru ke database.
     * * Melakukan validasi input ketat (Nama, Email Unik, Password Kuat).
     * * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
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
            'password' => Hash::make($request->password), // Hashing password wajib dilakukan sebelum simpan
        ]);

        return redirect()->route('admin.pengguna.index')->with('success', 'Pengguna baru berhasil ditambahkan.');
    }

    /**
     * Menampilkan formulir edit untuk pengguna tertentu.
     * * Menggunakan Route Model Binding untuk mengambil data user secara otomatis.
     * * @param User $pengguna
     * @return \Illuminate\View\View
     */
    public function edit(User $pengguna)
    {
        return view('admin.pengguna.edit', compact('pengguna'));
    }

    /**
     * Memperbarui data pengguna di database.
     * * Logika khusus:
     * 1. Email harus unik, KECUALI untuk pengguna yang sedang diedit (ignore current ID).
     * 2. Password hanya di-update jika field password diisi (Nullable).
     * * @param Request $request
     * @param User $pengguna
     * @return \Illuminate\Http\RedirectResponse
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

        // Update password HANYA JIKA diisi (biarkan password lama jika kosong)
        if ($request->filled('password')) {
            $pengguna->password = Hash::make($request->password);
        }

        $pengguna->save();

        return redirect()->route('admin.pengguna.index')->with('success', 'Data pengguna berhasil diperbarui.');
    }

    /**
     * Menghapus pengguna dari database.
     * * Fitur Keamanan (Self-Delete Prevention):
     * Mencegah admin menghapus akunnya sendiri saat sedang login untuk menghindari lockout.
     * * @param User $pengguna
     * @return \Illuminate\Http\RedirectResponse
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