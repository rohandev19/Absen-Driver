<?php

namespace App\Http\Controllers;

use App\Models\Vehicle;
use App\Models\VehicleComponent;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class VehicleComponentController extends Controller
{
    /**
     * Get all components for a vehicle
     * (Biarkan ini tetap JSON karena mungkin digunakan oleh AJAX/API)
     */
    public function index(Vehicle $vehicle)
    {
        $components = $vehicle->components()
            ->orderBy('status')
            ->orderBy('component_name')
            ->get()
            ->map(function ($component) {
                return [
                    'id' => $component->id,
                    'component_name' => $component->component_name,
                    'category' => $component->category,
                    'status' => $component->status,
                    'km_remaining' => $component->km_remaining,
                    'days_remaining' => $component->days_remaining,
                    'health_score' => $component->health_score,
                    'next_replacement_km' => $component->next_replacement_km,
                    'next_replacement_date' => $component->next_replacement_date?->format('Y-m-d'),
                    'cost_per_replacement' => $component->cost_per_replacement,
                ];
            });

        return response()->json([
            'success' => true,
            'data' => $components,
        ]);
    }

    /**
     * Store new component
     */
    public function store(Request $request, Vehicle $vehicle)
    {
        // 1. Bersihkan format harga dari titik/koma/Rp agar tidak error
        if ($request->has('cost_per_replacement')) {
            $request->merge([
                'cost_per_replacement' => str_replace(['Rp', '.', ',', ' '], '', $request->cost_per_replacement)
            ]);
        }

        $validator = Validator::make($request->all(), [
            'component_name' => 'required|string|max:100',
            'category' => 'required|string|max:50',
            'replacement_interval_km' => 'nullable|integer|min:0',
            'replacement_interval_days' => 'nullable|integer|min:0',
            'last_replacement_km' => 'nullable|integer|min:0',
            'last_replacement_date' => 'nullable|date',
            'cost_per_replacement' => 'nullable|numeric|min:0',
            'warning_threshold_km' => 'nullable|integer|min:0',
            'critical_threshold_km' => 'nullable|integer|min:0',
            'notes' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        $vehicle->components()->create($validator->validated());

        // PERBAIKAN: Gunakan back()->with() untuk halaman web
        return back()->with('success', 'Komponen baru berhasil ditambahkan!');
    }

    /**
     * Update component
     */
    public function update(Request $request, Vehicle $vehicle, VehicleComponent $component)
    {
        if ($component->vehicle_id !== $vehicle->id) {
            return back()->with('error', 'Komponen tidak sesuai dengan kendaraan ini.');
        }

        // 1. Bersihkan format harga dari titik/koma/Rp agar tidak error saat edit
        if ($request->has('cost_per_replacement')) {
            $request->merge([
                'cost_per_replacement' => str_replace(['Rp', '.', ',', ' '], '', $request->cost_per_replacement)
            ]);
        }

        $validator = Validator::make($request->all(), [
            'component_name' => 'sometimes|string|max:100',
            'category' => 'sometimes|string|max:50',
            'replacement_interval_km' => 'nullable|integer|min:0',
            'replacement_interval_days' => 'nullable|integer|min:0',
            'last_replacement_km' => 'nullable|integer|min:0',
            'last_replacement_date' => 'nullable|date',
            'cost_per_replacement' => 'nullable|numeric|min:0',
            'warning_threshold_km' => 'nullable|integer|min:0',
            'critical_threshold_km' => 'nullable|integer|min:0',
            'notes' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        $component->update($validator->validated());

        // PERBAIKAN: Gunakan back()->with() untuk halaman web
        return back()->with('success', 'Data komponen berhasil diperbarui!');
    }

    /**
     * Delete component
     */
    public function destroy(Vehicle $vehicle, VehicleComponent $component)
    {
        if ($component->vehicle_id !== $vehicle->id) {
            return back()->with('error', 'Gagal menghapus komponen.');
        }

        $component->delete();

        // PERBAIKAN: Gunakan back()->with()
        return back()->with('success', 'Komponen berhasil dihapus!');
    }

    /**
     * Get component categories
     * (Versi Bahasa Indonesia)
     */
    public function categories()
    {
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

        return response()->json([
            'success' => true,
            'data' => $categories,
        ]);
    }
}