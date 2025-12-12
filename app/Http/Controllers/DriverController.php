<?php

namespace App\Http\Controllers;

use App\Models\Driver;
use App\Http\Requests\StoreDriverRequest; // <--- Panggil Satpam yang kita buat tadi
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

/**
 * Class DriverController
 * * Mengelola data Master Driver.
 * * Kode ini sudah direfactor untuk menggunakan Form Request Validation
 * dan Business Logic yang ada di Model.
 */
class DriverController extends Controller
{
    public function __construct()
    {
        $this->middleware('can:is-master-admin')->except(['index']);
    }

    public function index()
    {
        $drivers = Driver::latest()->paginate(20);
        return view('admin.driver.index', compact('drivers'));
    }

    public function create()
    {
        return view('admin.driver.create');
    }

    /**
     * Store Driver Baru.
     * * REFACTOR: Kita ganti 'Request $request' jadi 'StoreDriverRequest $request'.
     * Otomatis validasi jalan sebelum masuk method ini.
     */
    public function store(StoreDriverRequest $request)
    {
        // $request->validated() otomatis mengambil hanya data yang lolos validasi.
        // Jauh lebih aman daripada $request->all().
        Driver::create([
            'full_name' => $request->full_name,
            'driver_id_nik' => $request->driver_id_nik,
            'sim_expiry_date' => $request->sim_expiry_date,
            'password' => Hash::make($request->password),
        ]);

        return redirect()->route('admin.driver.index')->with('success', 'Driver baru berhasil ditambahkan.');
    }

    public function edit(Driver $driver)
    {
        return view('admin.driver.edit', compact('driver'));
    }

    /**
     * Update Driver.
     * * NOTE: Untuk update, validasinya sedikit beda (harus ignore ID sendiri).
     * Biasanya kita buat 'UpdateDriverRequest' terpisah, tapi untuk sekarang
     * pakai validasi manual di sini masih oke karena logic 'unique'-nya dinamis.
     */
    public function update(Request $request, Driver $driver)
    {
        $request->validate([
            'full_name' => ['required', 'string', 'max:255'],
            'driver_id_nik' => ['required', 'string', 'max:255', 'unique:drivers,driver_id_nik,' . $driver->id],
            'sim_expiry_date' => ['required', 'date'],
            'password' => ['nullable', 'confirmed'],
        ]);

        $driver->full_name = $request->full_name;
        $driver->driver_id_nik = $request->driver_id_nik;
        $driver->sim_expiry_date = $request->sim_expiry_date;

        if ($request->filled('password')) {
            $driver->password = Hash::make($request->password);
        }

        $driver->save();

        return redirect()->route('admin.driver.index')->with('success', 'Data driver diperbarui.');
    }

    /**
     * Hapus Driver.
     * * REFACTOR: Menggunakan method isOnDuty() dari Model Driver.
     * Tidak ada lagi query manual di controller.
     */
    public function destroy(Driver $driver)
    {
        if ($driver->isOnDuty()) {
            return back()->with('error', 'GAGAL: Driver sedang aktif bertugas (Check-in).');
        }

        $driver->delete();
        return redirect()->route('admin.driver.index')->with('success', 'Driver berhasil dihapus.');
    }
}