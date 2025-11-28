<?php

namespace App\Http\Controllers;

use App\Models\Driver;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;

/**
 * Class DriverController
 * * Mengelola data Master Driver (CRUD).
 * * Controller ini dilindungi middleware 'is-master-admin', artinya
 * Admin biasa (Viewer) hanya bisa melihat daftar (Index), tapi tidak bisa
 * menambah, mengedit, atau menghapus driver.
 * * @package App\Http\Controllers
 */
class DriverController extends Controller
{
    /**
     * Constructor untuk proteksi akses.
     */
    public function __construct()
    {
        // Hanya Master Admin yang boleh Create/Edit/Delete.
        // Index (Lihat daftar) dibuka untuk semua level admin.
        $this->middleware('can:is-master-admin')->except(['index']);
    }

    /**
     * Menampilkan daftar semua driver.
     * * Data dipaginasi 20 item per halaman agar loading tidak berat.
     * * @return \Illuminate\View\View
     */
    public function index()
    {
        $drivers = Driver::latest()->paginate(20);
        return view('admin.driver.index', compact('drivers'));
    }

    /**
     * Menampilkan form tambah driver baru.
     * * @return \Illuminate\View\View
     */
    public function create()
    {
        return view('admin.driver.create');
    }

    /**
     * Menyimpan data driver baru ke database.
     * * Melakukan validasi:
     * 1. ID Driver (NIK) harus unik (tidak boleh ada yang sama).
     * 2. Tanggal SIM wajib diisi (penting untuk fitur alert di App).
     * 3. Password di-hash menggunakan Bcrypt sebelum disimpan.
     * * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(Request $request)
    {
        $request->validate([
            'full_name' => ['required', 'string', 'max:255'],
            'driver_id_nik' => ['required', 'string', 'max:255', 'unique:' . Driver::class],
            'sim_expiry_date' => ['required', 'date'], // Validasi Tanggal SIM
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        Driver::create([
            'full_name' => $request->full_name,
            'driver_id_nik' => $request->driver_id_nik,
            'sim_expiry_date' => $request->sim_expiry_date,
            'password' => Hash::make($request->password),
        ]);

        return redirect()->route('admin.driver.index')->with('success', 'Driver baru berhasil ditambahkan.');
    }

    /**
     * Menampilkan form edit driver.
     * * @param Driver $driver (Route Model Binding)
     * @return \Illuminate\View\View
     */
    public function edit(Driver $driver)
    {
        return view('admin.driver.edit', compact('driver'));
    }

    /**
     * Memperbarui data driver.
     * * Logika Validasi Unik:
     * Saat update NIK, kita harus mengecualikan ID driver yang sedang diedit
     * agar tidak dianggap duplikat dengan dirinya sendiri.
     * (unique:drivers,driver_id_nik,ID_SAAT_INI)
     * * @param Request $request
     * @param Driver $driver
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(Request $request, Driver $driver)
    {
        $request->validate([
            'full_name' => ['required', 'string', 'max:255'],
            // Ignore unique check untuk ID driver ini sendiri
            'driver_id_nik' => ['required', 'string', 'max:255', 'unique:' . Driver::class . ',driver_id_nik,' . $driver->id],
            'sim_expiry_date' => ['required', 'date'],
            'password' => ['nullable', 'confirmed', Rules\Password::defaults()], // Password opsional
        ]);

        // Update data
        $driver->full_name = $request->full_name;
        $driver->driver_id_nik = $request->driver_id_nik;
        $driver->sim_expiry_date = $request->sim_expiry_date;

        // Update password hanya jika input tidak kosong
        if ($request->filled('password')) {
            $driver->password = Hash::make($request->password);
        }

        $driver->save();

        return redirect()->route('admin.driver.index')->with('success', 'Data driver berhasil diperbarui.');
    }

    /**
     * Menghapus driver dari database.
     * * FITUR KEAMANAN (Safety Check):
     * Sebelum menghapus, sistem mengecek apakah driver sedang bertugas (Check-in).
     * Jika masih bertugas, penghapusan DITOLAK untuk menjaga integritas data absensi.
     * * @param Driver $driver
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy(Driver $driver)
    {
        // Cek apakah ada data absensi yang belum Check-out (time_out NULL)
        $isOnDuty = $driver->attendances()->whereNull('time_out')->exists();

        if ($isOnDuty) {
            return back()->with('error', 'GAGAL: Tidak dapat menghapus driver yang sedang aktif bertugas di jalan.');
        }

        $driver->delete();
        return redirect()->route('admin.driver.index')->with('success', 'Driver berhasil dihapus.');
    }
}