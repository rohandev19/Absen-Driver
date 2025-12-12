<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Vehicle;
use App\Models\Attendance;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class MaintenanceController extends Controller
{
    /**
     * 1. DASHBOARD MONITORING
     */
    public function index(Request $request)
    {
        $query = Vehicle::query();
        if ($request->filled('search')) {
            $query->search($request->search);
        }
        $maintenanceData = $query->with('latestAttendance')
            ->orderBy('plate_number', 'asc')
            ->get();
        $searchKeyword = $request->search;
        return view('admin.maintenance.index', compact('maintenanceData', 'searchKeyword'));
    }

    /**
     * 2. DAFTAR ASET
     */
    public function daftarAset(Request $request)
    {
        $query = Vehicle::query();
        if ($request->filled('search')) {
            $query->search($request->search);
        }
        // Mengurutkan aset terbaru ditambahkan di paling atas agar mudah dicek
        $vehicles = $query->orderBy('created_at', 'desc')->paginate(10);
        return view('admin.daftar_aset', compact('vehicles'));
    }

    /**
     * 3. VISUAL CHECK
     */
    public function visualCheck($id)
    {
        $vehicle = Vehicle::findOrFail($id);
        $lastLog = Attendance::where('vehicle_id', $id)->whereNotNull('time_out')->orderBy('time_out', 'desc')->first();
        $status = ['ban' => 'safe', 'rem' => 'safe', 'lampu' => 'safe', 'mesin' => 'safe'];

        if ($lastLog) {
            $valBan = $lastLog->check_ban;
            $valRem = $lastLog->check_rem;
            $valLampu = $lastLog->check_lampu;
            $status['ban'] = ($valBan == 'Aman' || $valBan == 1) ? 'safe' : 'danger';
            $status['rem'] = ($valRem == 'Aman' || $valRem == 1) ? 'safe' : 'danger';
            $status['lampu'] = ($valLampu == 'Aman' || $valLampu == 1) ? 'safe' : 'danger';
        }

        $sisaKm = $vehicle->sisa_km;
        if ($sisaKm !== null && $sisaKm < 1000) {
            $status['mesin'] = 'danger';
        }

        return view('admin.aset.visual', compact('vehicle', 'status', 'lastLog'));
    }

    /**
     * 4. RIWAYAT SERVIS
     */
    public function riwayatServis($id)
    {
        $vehicle = Vehicle::with(['maintenanceLogs.recorder'])->findOrFail($id);
        $sisaKm = $vehicle->sisa_km;
        $color = 'success';
        if ($sisaKm !== null) {
            if ($sisaKm <= 0)
                $color = 'danger';
            elseif ($sisaKm < 1000)
                $color = 'warning';
        }
        $statusSummary = [
            'km_saat_ini' => $vehicle->current_km,
            'sisa_km' => $sisaKm,
            'color' => $color
        ];
        return view('admin.aset.riwayat', compact('vehicle', 'statusSummary'));
    }

    /**
     * 5. KALENDER
     */
    public function calendar()
    {
        return view('admin.maintenance_calendar');
    }

    /**
     * 6. API EVENTS
     */
    public function getEvents()
    {
        $vehicles = Vehicle::all();
        $events = [];
        $today = Carbon::now('Asia/Jakarta')->startOfDay();
        $warningLimit = $today->copy()->addDays(30);

        foreach ($vehicles as $vehicle) {
            if ($vehicle->pajak_stnk_berlaku_sampai) {
                $dateStnk = Carbon::parse($vehicle->pajak_stnk_berlaku_sampai)->startOfDay();
                $color = $dateStnk->lt($today) ? '#dc3545' : ($dateStnk->lte($warningLimit) ? '#ffc107' : '#0d6efd');
                $events[] = [
                    'title' => 'STNK: ' . $vehicle->plate_number,
                    'start' => $dateStnk->format('Y-m-d'),
                    'backgroundColor' => $color,
                    'borderColor' => $color,
                    'textColor' => '#ffffff',
                    'url' => route('admin.aset.edit', $vehicle->id)
                ];
            }
            if ($vehicle->kir_berlaku_sampai) {
                $dateKir = Carbon::parse($vehicle->kir_berlaku_sampai)->startOfDay();
                $color = $dateKir->lt($today) ? '#dc3545' : ($dateKir->lte($warningLimit) ? '#ffc107' : '#198754');
                $events[] = [
                    'title' => 'KIR: ' . $vehicle->plate_number,
                    'start' => $dateKir->format('Y-m-d'),
                    'backgroundColor' => $color,
                    'borderColor' => $color,
                    'textColor' => '#ffffff',
                    'url' => route('admin.aset.edit', $vehicle->id)
                ];
            }
        }
        return response()->json($events);
    }

    /**
     * 7. CATAT SERVIS
     */
    public function catatServis(Request $request, $id)
    {
        $vehicle = Vehicle::findOrFail($id);
        $request->validate([
            'service_date' => 'required|date',
            'km_servis_saat_ini' => 'required|numeric|min:' . $vehicle->last_service_km,
            'description' => 'required|string',
        ]);
        if (method_exists($vehicle, 'maintenanceLogs')) {
            $vehicle->maintenanceLogs()->create([
                'service_date' => $request->service_date,
                'km_at_service' => $request->km_servis_saat_ini,
                'description' => $request->description,
                'recorded_by' => Auth::id(),
            ]);
        }
        $vehicle->update(['last_service_km' => $request->km_servis_saat_ini]);
        return back()->with('success', 'Servis berhasil dicatat.');
    }

    /**
     * 8. RESOLVE ISSUE
     */
    public function resolveIssue($id)
    {
        $vehicle = Vehicle::findOrFail($id);
        $lastLog = Attendance::where('vehicle_id', $id)->whereNotNull('time_out')->latest('time_out')->first();
        if ($lastLog) {
            $lastLog->update([
                'check_ban' => 'Aman',
                'check_rem' => 'Aman',
                'check_lampu' => 'Aman',
                'catatan' => $lastLog->catatan . ' [DIPERBAIKI ADMIN TGL ' . now()->format('d/m') . ']',
            ]);
            return back()->with('success', 'Status kendaraan berhasil diperbarui.');
        }
        return back()->with('error', 'Data riwayat pemeriksaan tidak ditemukan.');
    }

    // --- CRUD: EDIT & UPDATE ---
    public function edit($id)
    {
        $vehicle = Vehicle::findOrFail($id);
        return view('admin.aset.edit', compact('vehicle'));
    }

    public function update(Request $request, $id)
    {
        $vehicle = Vehicle::findOrFail($id);
        $request->validate(['type' => 'required']);
        $vehicle->update($request->except(['plate_number']));
        return redirect()->route('admin.daftar_aset')->with('success', 'Data aset diperbarui.');
    }

    // --- CRUD: TAMBAH & HAPUS (BARU) ---

    public function create()
    {
        return view('admin.aset.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'plate_number' => 'required|unique:vehicles,plate_number|max:15',
            'type' => 'required|string|max:50',
        ], [
            'plate_number.required' => 'Plat nomor wajib diisi.',
            'plate_number.unique' => 'Plat nomor sudah terdaftar.',
            'type.required' => 'Jenis mobil wajib diisi.',
        ]);

        Vehicle::create([
            'plate_number' => strtoupper($request->plate_number),
            'type' => $request->type,
            'status' => 'ready',
            'current_km' => 0,
        ]);

        return redirect()->route('admin.daftar_aset')->with('success', 'Aset baru berhasil ditambahkan.');
    }

    public function destroy($id)
    {
        $vehicle = Vehicle::findOrFail($id);
        $vehicle->delete();
        return redirect()->route('admin.daftar_aset')->with('success', 'Data aset berhasil dihapus.');
    }
}