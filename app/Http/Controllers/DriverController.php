<?php

namespace App\Http\Controllers;

use App\Models\Driver;
use App\Models\Project;
use App\Http\Requests\StoreDriverRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class DriverController extends Controller
{
    public function __construct()
    {
        $this->middleware('can:is-master-admin')->except(['index']);
    }

    public function index(Request $request)
    {
        $query = Driver::with('project')->latest();

        // 1. Filter Dropdown Project
        if ($request->filled('project_id')) {
            $query->where('project_id', $request->project_id);
        }

        // 2. Filter Search Teks (Nama / NIK)
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('full_name', 'like', "%{$search}%")
                    ->orWhere('driver_id_nik', 'like', "%{$search}%");
            });
        }

        $drivers = $query->paginate(20);

        // 3. Ambil Data Project untuk Dropdown
        $projects = Project::orderBy('name', 'asc')->get();

        return view('admin.driver.index', compact('drivers', 'projects'));
    }

    public function create()
    {
        $projects = Project::all();
        return view('admin.driver.create', compact('projects'));
    }

    public function store(StoreDriverRequest $request)
    {
        // Pastikan di StoreDriverRequest validasi nik_ktp sudah ada
        Driver::create([
            'full_name' => $request->full_name,
            'driver_id_nik' => $request->driver_id_nik,
            'nik_ktp' => $request->nik_ktp, // Sudah Benar
            'sim_expiry_date' => $request->sim_expiry_date,
            'sim_type' => $request->sim_type,
            'password' => Hash::make($request->password),
            'project_id' => $request->project_id,
        ]);

        return redirect()->route('admin.driver.index')->with('success', 'Driver baru berhasil ditambahkan.');
    }

    public function edit(Driver $driver)
    {
        $projects = Project::all();
        return view('admin.driver.edit', compact('driver', 'projects'));
    }

    /**
     * PERBAIKAN DI SINI:
     * Menambahkan validasi dan penyimpanan untuk 'nik_ktp'
     */
    public function update(Request $request, Driver $driver)
    {
        $request->validate([
            'full_name' => ['required', 'string', 'max:255'],
            // Validasi ID Badge (unik kecuali punya diri sendiri)
            'driver_id_nik' => ['required', 'string', 'max:255', 'unique:drivers,driver_id_nik,' . $driver->id],
            // Validasi NIK KTP (tambahkan ini)
            'nik_ktp' => ['nullable', 'string', 'max:20', 'unique:drivers,nik_ktp,' . $driver->id],
            'sim_expiry_date' => ['required', 'date'],
            'sim_type' => ['required', 'string'],
            'password' => ['nullable', 'confirmed'],
            'project_id' => ['nullable', 'exists:projects,id'],
        ]);

        $driver->full_name = $request->full_name;
        $driver->driver_id_nik = $request->driver_id_nik;
        $driver->nik_ktp = $request->nik_ktp; // <--- PENTING: Jangan lupa update ini
        $driver->sim_expiry_date = $request->sim_expiry_date;
        $driver->sim_type = $request->sim_type;
        $driver->project_id = $request->project_id;

        if ($request->filled('password')) {
            $driver->password = Hash::make($request->password);
        }

        $driver->save();

        return redirect()->route('admin.driver.index')->with('success', 'Data driver diperbarui.');
    }

    public function destroy(Driver $driver)
    {
        if ($driver->isOnDuty()) {
            return back()->with('error', 'GAGAL: Driver sedang aktif bertugas (Check-in).');
        }
        $driver->delete();
        return redirect()->route('admin.driver.index')->with('success', 'Driver berhasil dihapus.');
    }
}