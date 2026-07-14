<?php

namespace App\Http\Controllers;

use App\Models\Driver;
use App\Models\Vehicle;
use App\Models\VehicleReplacement;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class VehicleReplacementController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = VehicleReplacement::with(['originalVehicle', 'replacementVehicle', 'driver'])
            ->latest('start_at');

        // Filter by status
        if ($request->filled('status') && $request->status !== 'Semua Status') {
            $query->where('status', $request->status);
        }

        // Filter by date range
        if ($request->filled('start_date') && $request->filled('end_date')) {
            $query->whereBetween('start_at', [
                $request->start_date . ' 00:00:00',
                $request->end_date . ' 23:59:59'
            ]);
        }

        // Filter by original plate number
        if ($request->filled('plate_number')) {
            $query->whereHas('originalVehicle', function ($q) use ($request) {
                $q->where('plate_number', 'like', '%' . $request->plate_number . '%');
            })->orWhereHas('replacementVehicle', function ($q) use ($request) {
                $q->where('plate_number', 'like', '%' . $request->plate_number . '%');
            });
        }

        $replacements = $query->paginate(15)->withQueryString();

        $countActive = VehicleReplacement::where('status', VehicleReplacement::STATUS_ACTIVE)->count();
        $countCompleted = VehicleReplacement::where('status', VehicleReplacement::STATUS_COMPLETED)->count();

        return view('admin.vehicle_replacements.index', compact('replacements', 'countActive', 'countCompleted'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $drivers = Driver::orderBy('full_name')->get(['id', 'full_name', 'driver_id_nik']);
        $vehicles = Vehicle::orderBy('plate_number')->get(['id', 'plate_number', 'type']);

        return view('admin.vehicle_replacements.create', compact('drivers', 'vehicles'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'original_vehicle_id' => 'required|exists:vehicles,id',
            'replacement_vehicle_id' => 'required|exists:vehicles,id|different:original_vehicle_id',
            'driver_id' => 'nullable|exists:drivers,id',
            'start_at' => 'required|date',
            'reason' => 'required|string|max:255',
            'notes' => 'nullable|string',
        ]);

        // Validate that replacement vehicle is not currently active in another replacement
        $isReplacementActive = VehicleReplacement::where('replacement_vehicle_id', $request->replacement_vehicle_id)
            ->where('status', VehicleReplacement::STATUS_ACTIVE)
            ->exists();

        if ($isReplacementActive) {
            return back()->with('error', 'Kendaraan pengganti sedang digunakan (aktif) pada penggantian lain!')->withInput();
        }

        try {
            DB::beginTransaction();

            VehicleReplacement::create([
                'original_vehicle_id' => $request->original_vehicle_id,
                'replacement_vehicle_id' => $request->replacement_vehicle_id,
                'driver_id' => $request->driver_id,
                'start_at' => $request->start_at,
                'reason' => $request->reason,
                'status' => VehicleReplacement::STATUS_ACTIVE,
                'notes' => $request->notes,
                'created_by' => Auth::id(),
            ]);

            DB::commit();

            return redirect()->route('admin.vehicle_replacements.index')->with('success', 'Penggantian kendaraan berhasil ditambahkan dan berstatus aktif.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Gagal menambahkan penggantian kendaraan: ' . $e->getMessage())->withInput();
        }
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(VehicleReplacement $vehicleReplacement)
    {
        if ($vehicleReplacement->status !== VehicleReplacement::STATUS_ACTIVE) {
            return redirect()->route('admin.vehicle_replacements.index')->with('error', 'Hanya penggantian berstatus aktif yang bisa diedit.');
        }

        $drivers = Driver::orderBy('full_name')->get(['id', 'full_name', 'driver_id_nik']);
        $vehicles = Vehicle::orderBy('plate_number')->get(['id', 'plate_number', 'type']);

        return view('admin.vehicle_replacements.edit', compact('vehicleReplacement', 'drivers', 'vehicles'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, VehicleReplacement $vehicleReplacement)
    {
        if ($vehicleReplacement->status !== VehicleReplacement::STATUS_ACTIVE) {
            return redirect()->route('admin.vehicle_replacements.index')->with('error', 'Hanya penggantian berstatus aktif yang bisa diedit.');
        }

        $request->validate([
            'reason' => 'required|string|max:255',
            'notes' => 'nullable|string',
        ]);

        try {
            $vehicleReplacement->update([
                'reason' => $request->reason,
                'notes' => $request->notes,
            ]);

            return redirect()->route('admin.vehicle_replacements.index')->with('success', 'Detail penggantian kendaraan berhasil diperbarui.');
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal memperbarui data: ' . $e->getMessage())->withInput();
        }
    }

    /**
     * Mark the replacement as completed.
     */
    public function complete(VehicleReplacement $vehicleReplacement)
    {
        if ($vehicleReplacement->status !== VehicleReplacement::STATUS_ACTIVE) {
            return back()->with('error', 'Penggantian sudah selesai atau dibatalkan.');
        }

        try {
            $vehicleReplacement->update([
                'status' => VehicleReplacement::STATUS_COMPLETED,
                'end_at' => Carbon::now(),
            ]);

            return redirect()->route('admin.vehicle_replacements.index')->with('success', 'Penggantian kendaraan telah diselesaikan.');
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal menyelesaikan penggantian: ' . $e->getMessage());
        }
    }

    /**
     * Mark the replacement as cancelled.
     */
    public function cancel(VehicleReplacement $vehicleReplacement)
    {
        if ($vehicleReplacement->status !== VehicleReplacement::STATUS_ACTIVE) {
            return back()->with('error', 'Hanya penggantian aktif yang bisa dibatalkan.');
        }

        try {
            $vehicleReplacement->update([
                'status' => VehicleReplacement::STATUS_CANCELLED,
            ]);

            return redirect()->route('admin.vehicle_replacements.index')->with('success', 'Penggantian kendaraan dibatalkan.');
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal membatalkan penggantian: ' . $e->getMessage());
        }
    }
}
