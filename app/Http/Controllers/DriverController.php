<?php

namespace App\Http\Controllers;

use App\Models\Driver; // <-- Gunakan Model Driver
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;

class DriverController extends Controller
{

    public function __construct()
    {
        // Hanya 'master-admin' yang boleh mengakses fungsi-fungsi ini
        // 'index' (melihat daftar) boleh diakses semua admin
        $this->middleware('can:is-master-admin')->except(['index']);
    }

    /**
     * Tampilkan daftar semua driver.
     */
    public function index()
    {
        $drivers = Driver::latest()->paginate(20); // Ambil 20 driver terbaru
        return view('admin.driver.index', compact('drivers'));
    }

    /**
     * Tampilkan form untuk membuat driver baru.
     */
    public function create()
    {
        return view('admin.driver.create');
    }

    /**
     * Simpan driver baru ke database.
     */
    public function store(Request $request)
    {
        $request->validate([
            'full_name' => ['required', 'string', 'max:255'],
            'driver_id_nik' => ['required', 'string', 'max:255', 'unique:' . Driver::class],
            'sim_expiry_date' => ['required', 'date'], // <--- VALIDASI BARU (Wajib Tanggal)
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        Driver::create([
            'full_name' => $request->full_name,
            'driver_id_nik' => $request->driver_id_nik,
            'sim_expiry_date' => $request->sim_expiry_date, // <--- SIMPAN DATA SIM
            'password' => Hash::make($request->password),
        ]);

        return redirect()->route('admin.driver.index')->with('success', 'Driver baru berhasil ditambahkan.');
    }

    /**
     * Tampilkan form untuk mengedit driver.
     */
    public function edit(Driver $driver) // Route model binding
    {
        return view('admin.driver.edit', compact('driver'));
    }

    /**
     * Perbarui driver di database.
     */
    public function update(Request $request, Driver $driver)
    {
        $request->validate([
            'full_name' => ['required', 'string', 'max:255'],
            'driver_id_nik' => ['required', 'string', 'max:255', 'unique:' . Driver::class . ',driver_id_nik,' . $driver->id],
            'sim_expiry_date' => ['required', 'date'], // <--- VALIDASI BARU
            'password' => ['nullable', 'confirmed', Rules\Password::defaults()], // Password boleh kosong
        ]);

        // Update data dasar
        $driver->full_name = $request->full_name;
        $driver->driver_id_nik = $request->driver_id_nik;
        $driver->sim_expiry_date = $request->sim_expiry_date; // <--- UPDATE DATA SIM

        // Update password HANYA JIKA diisi
        if ($request->filled('password')) {
            $driver->password = Hash::make($request->password);
        }

        $driver->save();

        return redirect()->route('admin.driver.index')->with('success', 'Data driver berhasil diperbarui.');
    }

    /**
     * Hapus driver dari database.
     */
    public function destroy(Driver $driver)
    {
        // Fitur keamanan: Cek apakah driver sedang bertugas
        $isOnDuty = $driver->attendances()->whereNull('time_out')->exists();
        if ($isOnDuty) {
            return back()->with('error', 'Tidak dapat menghapus driver yang sedang aktif bertugas.');
        }

        // Hapus absensi dan laporan (jika perlu, atau biarkan)
        // $driver->attendances()->delete();
        // $driver->emergencyReports()->delete();

        $driver->delete();
        return redirect()->route('admin.driver.index')->with('success', 'Driver berhasil dihapus.');
    }
}