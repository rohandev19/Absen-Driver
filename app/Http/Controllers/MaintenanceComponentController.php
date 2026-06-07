<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Vehicle;
use App\Models\VehicleComponent;
use App\Services\VehicleHealthService;
use Illuminate\Validation\Rule;

class MaintenanceComponentController extends Controller
{
    protected $healthService;

    public function __construct(VehicleHealthService $healthService)
    {
        $this->healthService = $healthService;
    }

    public function index($vehicleId)
    {
        $vehicle = Vehicle::with('components')->findOrFail($vehicleId);
        $healthReport = $this->healthService->getHealthReport($vehicle);

        // PERBAIKAN: Ubah ke Bahasa Indonesia agar tabel bisa membaca data yang diinput
        $categories = [
            'Cairan & Pelumas' => ['Oli Mesin', 'Air Radiator', 'Minyak Rem', 'Oli Power Steering', 'Oli Transmisi'],
            'Filter' => ['Filter Oli', 'Filter Udara', 'Filter Bahan Bakar', 'Filter AC / Kabin'],
            'Rem' => ['Kampas Rem', 'Cakram Rem', 'Minyak Rem'],
            'Ban' => ['Ban Depan Kiri', 'Ban Depan Kanan', 'Ban Belakang Kiri', 'Ban Belakang Kanan', 'Ban Serep'],
            'Aki & Kelistrikan' => ['Aki', 'Alternator / Dinamo Ampere'],
            'Lampu' => ['Lampu Utama', 'Lampu Belakang', 'Lampu Sein', 'Lampu Rem'],
            'Fan Belt & Selang' => ['Timing Belt', 'V-Belt / Fan Belt', 'Selang Radiator'],
            'Kaki-kaki & Suspensi' => ['Shockbreaker', 'Ball Joint', 'Tie Rod'],
            'Mesin' => ['Busi', 'Koil Pengapian', 'Injektor'],
            'Transmisi' => ['Oli Transmisi', 'Kampas Kopling'],
        ];

        return view('admin.maintenance.components', compact('vehicle', 'healthReport', 'categories'));
    }

    public function store(Request $request, $vehicleId)
    {
        $validated = $request->validate([
            'component_name' => [
                'required',
                'string',
                'max:100',
                Rule::unique('vehicle_components')->where(function ($query) use ($vehicleId) {
                    return $query->where('vehicle_id', $vehicleId);
                })
            ],
            'category' => 'required|string|max:50',
            'replacement_interval_km' => 'nullable|integer|min:0',
            'replacement_interval_days' => 'nullable|integer|min:0',
            'last_replacement_km' => 'nullable|integer|min:0',
            'last_replacement_date' => 'nullable|date',
            'cost_per_replacement' => 'required|numeric|min:0',
        ]);

        $vehicle = Vehicle::findOrFail($vehicleId);
        $vehicle->components()->create($validated);

        return back()->with('success', 'Komponen berhasil ditambahkan.');
    }

    public function update(Request $request, $componentId)
    {
        $component = VehicleComponent::findOrFail($componentId);

        $validated = $request->validate([
            'replacement_interval_km' => 'nullable|integer|min:0',
            'replacement_interval_days' => 'nullable|integer|min:0',
            'last_replacement_km' => 'nullable|integer|min:0',
            'last_replacement_date' => 'nullable|date',
            'cost_per_replacement' => 'nullable|numeric|min:0',
        ]);

        $component->update($validated);

        return back()->with('success', 'Komponen berhasil diupdate.');
    }

    public function destroy($componentId)
    {
        $component = VehicleComponent::findOrFail($componentId);
        $component->delete();

        return back()->with('success', 'Komponen berhasil dihapus.');
    }

    public function apiGetVehicleComponents(Vehicle $vehicle)
    {
        return response()->json([
            'components' => $vehicle->components()->select('id', 'component_name as name')->get()
        ]);
    }
}
