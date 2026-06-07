<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Vehicle;
use App\Models\Attendance;
use App\Models\Project;
use App\Models\MaintenanceSchedule;
use App\Services\VehicleHealthService;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use App\Http\Requests\StoreAssetRequest;
use App\Http\Requests\UpdateAssetRequest;
use App\Http\Requests\CatatServisRequest;

class MaintenanceAssetController extends Controller
{
    protected $healthService;

    public function __construct(VehicleHealthService $healthService)
    {
        $this->healthService = $healthService;
    }

    public function daftarAset(Request $request)
    {
        $query = Vehicle::with('project');
        if ($request->filled('project_id'))
            $query->where('project_id', $request->project_id);
        if ($request->filled('kategori'))
            $query->where('type', $request->kategori);
        if ($request->filled('verification_status'))
            $query->where('verification_status', $request->verification_status);
        if ($request->boolean('temporary_only'))
            $query->where('is_temporary', true);
        if ($request->filled('search'))
            $query->search($request->search);

        $vehicles = $query->orderBy('created_at', 'desc')->paginate(10);
        $projects = Project::orderBy('name')->get();
        $categories = Vehicle::select('type')->distinct()->pluck('type');
        $pendingVerificationCount = Vehicle::where('verification_status', 'pending')->count();

        return view('admin.daftar_aset', compact('vehicles', 'projects', 'categories', 'pendingVerificationCount'));
    }

    public function create()
    {
        $projects = Project::all();
        return view('admin.aset.create', compact('projects'));
    }

    public function store(StoreAssetRequest $request)
    {
        Vehicle::create([
            'plate_number' => $request->plate_number,
            'type' => $request->type,
            'tahun_pembuatan' => $request->tahun_pembuatan,
            'current_km' => $request->current_km,
            'project_id' => $request->project_id,
            'pajak_stnk_berlaku_sampai' => $request->pajak_stnk_berlaku_sampai,
            'kir_berlaku_sampai' => $request->kir_berlaku_sampai,
            'status' => $request->status ?? 'Aktif',
            'is_temporary' => $request->boolean('is_temporary'),
            'verification_status' => $request->verification_status
                ?? ($request->status === 'Pending Verifikasi' ? 'pending' : 'verified'),
            'source' => $request->source ?? 'admin',
            'notes' => $request->notes,
            'service_interval_km' => $request->service_interval_km ?? 10000,
        ]);

        return redirect()->route('admin.daftar_aset')->with('success', 'Aset baru berhasil ditambahkan.');
    }

    public function edit($id)
    {
        $vehicle = Vehicle::findOrFail($id);
        $projects = Project::all();
        return view('admin.aset.edit', compact('vehicle', 'projects'));
    }

    public function update(UpdateAssetRequest $request, $id)
    {
        $vehicle = Vehicle::findOrFail($id);
        $data = $request->validated();
        $data['is_temporary'] = $request->boolean('is_temporary');
        $vehicle->update($data);
        return redirect()->route('admin.daftar_aset')->with('success', 'Data aset diperbarui.');
    }

    public function destroy($id)
    {
        $vehicle = Vehicle::findOrFail($id);
        $vehicle->delete();
        return redirect()->route('admin.daftar_aset')->with('success', 'Data aset berhasil dihapus.');
    }

    public function verifyTemporaryUnit(Request $request, Vehicle $vehicle)
    {
        $vehicle->update([
            'verification_status' => 'verified',
            'verified_by' => Auth::id(),
            'verified_at' => now(),
            'status' => $vehicle->status === 'Pending Verifikasi' ? 'Aktif' : $vehicle->status,
            'notes' => trim(($vehicle->notes ?? '') . "\n[VERIFIED " . now()->format('Y-m-d H:i') . '] Unit diverifikasi admin.'),
        ]);

        return back()->with('success', 'Unit ' . $vehicle->plate_number . ' berhasil diverifikasi. QR sudah bisa dicetak.');
    }

    public function visualCheck($id)
    {
        $vehicle = Vehicle::with('components')->findOrFail($id);
        
        $lastLog = Attendance::where('vehicle_id', $id)
            ->whereNotNull('time_out')
            ->orderBy('time_out', 'desc')
            ->first();
        
        $operationalStatus = [
            'ban' => 'safe',
            'rem' => 'safe',
            'lampu' => 'safe',
            'mesin' => 'safe'
        ];
        
        if ($lastLog) {
            $valBan = $lastLog->check_ban;
            $valRem = $lastLog->check_rem;
            $valLampu = $lastLog->check_lampu;
            
            $operationalStatus['ban'] = ($valBan == 'Aman' || $valBan == 1) ? 'safe' : 'danger';
            $operationalStatus['rem'] = ($valRem == 'Aman' || $valRem == 1) ? 'safe' : 'danger';
            $operationalStatus['lampu'] = ($valLampu == 'Aman' || $valLampu == 1) ? 'safe' : 'danger';
        }
        
        $predictiveStatus = [
            'ban' => $this->getComponentStatus($vehicle, ['Ban Depan Kiri', 'Ban Depan Kanan', 'Ban Belakang Kiri', 'Ban Belakang Kanan']),
            'rem' => $this->getComponentStatus($vehicle, ['Kampas Rem', 'Minyak Rem', 'Cakram Rem']),
            'lampu' => $this->getComponentStatus($vehicle, ['Lampu Utama', 'Lampu Belakang', 'Lampu Sein', 'Lampu Rem']),
            'mesin' => $this->getComponentStatus($vehicle, ['Oli Mesin', 'Filter Oli', 'Filter Udara', 'Busi'])
        ];
        
        $finalStatus = [];
        foreach (['ban', 'rem', 'lampu', 'mesin'] as $system) {
            $finalStatus[$system] = $this->combineStatus(
                $operationalStatus[$system],
                $predictiveStatus[$system]
            );
        }
        
        $detailInfo = [
            'ban' => $this->getComponentDetails($vehicle, ['Ban Depan Kiri', 'Ban Depan Kanan', 'Ban Belakang Kiri', 'Ban Belakang Kanan']),
            'rem' => $this->getComponentDetails($vehicle, ['Kampas Rem', 'Minyak Rem', 'Cakram Rem']),
            'lampu' => $this->getComponentDetails($vehicle, ['Lampu Utama', 'Lampu Belakang', 'Lampu Sein', 'Lampu Rem']),
            'mesin' => $this->getComponentDetails($vehicle, ['Oli Mesin', 'Filter Oli', 'Filter Udara', 'Busi'])
        ];
        
        return view('admin.aset.visual', compact(
            'vehicle',
            'finalStatus',
            'operationalStatus',
            'predictiveStatus',
            'detailInfo',
            'lastLog'
        ));
    }
    
    private function getComponentStatus($vehicle, $componentNames)
    {
        $components = $vehicle->components()
            ->whereIn('component_name', $componentNames)
            ->get();
        
        if ($components->isEmpty()) {
            return 'unknown';
        }
        
        $statuses = $components->pluck('status')->toArray();
        
        if (in_array('overdue', $statuses)) return 'danger';
        if (in_array('critical', $statuses)) return 'danger';
        if (in_array('warning', $statuses)) return 'warning';
        
        return 'safe';
    }
    
    private function getComponentDetails($vehicle, $componentNames)
    {
        $components = $vehicle->components()
            ->whereIn('component_name', $componentNames)
            ->get();
        
        if ($components->isEmpty()) {
            return null;
        }
        
        $details = [];
        foreach ($components as $comp) {
            if (in_array($comp->status, ['overdue', 'critical', 'warning'])) {
                $details[] = [
                    'name' => $comp->component_name,
                    'status' => $comp->status,
                    'km_remaining' => $comp->km_remaining,
                    'next_replacement_km' => $comp->next_replacement_km
                ];
            }
        }
        
        return $details;
    }
    
    private function combineStatus($operational, $predictive)
    {
        $priority = [
            'danger' => 3,
            'warning' => 2,
            'safe' => 1,
            'unknown' => 0
        ];
        
        $opPriority = $priority[$operational] ?? 0;
        $predPriority = $priority[$predictive] ?? 0;
        
        return ($opPriority >= $predPriority) ? $operational : $predictive;
    }

    public function riwayatServis($id)
    {
        $vehicle = Vehicle::with([
            'maintenanceSchedules' => function ($query) {
                $query->where('status', 'completed')
                    ->orderBy('scheduled_date', 'desc')
                    ->with('component');
            }
        ])->findOrFail($id);

        $nextSchedule = MaintenanceSchedule::where('vehicle_id', $id)
            ->whereIn('status', ['pending', 'scheduled'])
            ->orderBy('scheduled_date', 'asc')
            ->first();

        $score = $this->healthService->calculateHealthScore($vehicle);

        return view('admin.aset.riwayat', compact('vehicle', 'nextSchedule', 'score'));
    }

    public function catatServis(CatatServisRequest $request, $id)
    {
        $vehicle = Vehicle::findOrFail($id);
        
        $validated = $request->validated();
        
        $kmServis = $validated['km_servis_saat_ini'];
        $serviceDate = Carbon::parse($validated['service_date']);
        $description = $validated['description'];
        
        $isOldRecord = $kmServis < $vehicle->last_service_km;
        
        $latestAttendance = Attendance::where('vehicle_id', $vehicle->id)
            ->whereNotNull('speedo_akhir')
            ->latest('time_out')
            ->first();
            
        if ($latestAttendance) {
            $maxRealisticKm = $latestAttendance->speedo_akhir + 1000;
            if ($kmServis > $maxRealisticKm) {
                return back()->with('error', 'KM servis tidak realistis. Selisih terlalu besar dengan data terakhir (' . $latestAttendance->speedo_akhir . ' km).');
            }
        }
        
        if ($isOldRecord) {
            $description .= ' (Arsip Susulan)';
        }
        
        if (method_exists($vehicle, 'maintenanceLogs')) {
            $vehicle->maintenanceLogs()->create([
                'service_date' => $serviceDate,
                'km_at_service' => $kmServis,
                'description' => $description,
                'recorded_by_user_id' => Auth::id(),
            ]);
        }
        
        if (!$isOldRecord) {
            $vehicle->update(['last_service_km' => $kmServis]);
        }
        
        return back()->with('success', 'Servis berhasil dicatat.');
    }

    public function resolveIssue(Request $request, $id)
    {
        $vehicle = Vehicle::findOrFail($id);
        $lastLog = Attendance::where('vehicle_id', $id)->whereNotNull('time_out')->latest('time_out')->first();
        if ($lastLog) {
            $part = $request->input('part');
            $updates = [];

            if ($part && in_array($part, ['ban', 'rem', 'lampu'])) {
                $updates["check_{$part}"] = 'Aman';
            } else {
                $updates = [
                    'check_ban' => 'Aman',
                    'check_rem' => 'Aman',
                    'check_lampu' => 'Aman',
                ];
            }

            $updates['catatan'] = $lastLog->catatan . ' [DIPERBAIKI ADMIN TGL ' . now()->format('d/m') . ']';
            
            $lastLog->update($updates);
            
            return back()->with('success', 'Status kendaraan berhasil diperbarui.');
        }
        return back()->with('error', 'Data riwayat pemeriksaan tidak ditemukan.');
    }

    public function exportExcel($id)
    {
        $vehicle = Vehicle::with([
            'maintenanceSchedules' => function ($query) {
                $query->where('status', 'completed')
                    ->orderBy('scheduled_date', 'desc')
                    ->with('component');
            }
        ])->findOrFail($id);

        $logs = $vehicle->maintenanceSchedules;
        $namaFile = 'Riwayat_Servis_' . str_replace(' ', '_', $vehicle->plate_number) . '.xls';

        return response(view('admin.aset.riwayat_excel', compact('vehicle', 'logs')))
            ->header('Content-Type', 'application/vnd.ms-excel')
            ->header('Content-Disposition', 'attachment; filename="' . $namaFile . '"');
    }
}
