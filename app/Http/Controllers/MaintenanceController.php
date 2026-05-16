<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Vehicle;
use App\Models\Attendance;
use App\Models\User;
use App\Models\Driver;
use App\Models\Project;
use App\Models\VehicleComponent;
use App\Models\MaintenanceSchedule;
use App\Models\MaintenanceAlert;
use App\Services\VehicleHealthService;
use App\Services\MaintenanceAlertService;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\MaintenanceDashboardExport;
use App\Exports\MaintenanceSchedulesExport;
use App\Exports\MaintenanceAlertsExport;

class MaintenanceController extends Controller
{
    protected $healthService;
    protected $alertService;

    public function __construct()
    {
        Carbon::setLocale('id');
        setlocale(LC_TIME, 'id_ID');
        $this->healthService = new VehicleHealthService();
        $this->alertService = new MaintenanceAlertService();
    }

    public function index(Request $request)
    {
        $query = Vehicle::query();

        if ($request->filled('search')) {
            $query->search($request->search);
        }
        if ($request->filled('project_id')) {
            $query->where('project_id', $request->project_id);
        }
        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        $vehicles = $query->with(['latestAttendance', 'project', 'components'])->get();

        $stats = [
            'total' => $vehicles->count(),
            'sehat' => 0,
            'warning' => 0,
            'danger' => 0,
        ];

        foreach ($vehicles as $vehicle) {
            $score = $this->healthService->calculateHealthScore($vehicle);
            $vehicle->health_score = $score;

            if ($vehicle->health_status_code === 'physical_issue' || $score < 40) {
                $stats['danger']++;
                $vehicle->dashboard_status = 'danger';
            } elseif ($score >= 40 && $score < 75) {
                $stats['warning']++;
                $vehicle->dashboard_status = 'warning';
            } else {
                $stats['sehat']++;
                $vehicle->dashboard_status = 'safe';
            }
        }

        if ($request->filled('status_filter')) {
            $vehicles = $vehicles->filter(function ($vehicle) use ($request) {
                return $vehicle->dashboard_status === $request->status_filter;
            });
        }

        $maintenanceData = $vehicles->sortBy(function ($vehicle) {
            if ($vehicle->dashboard_status === 'danger')
                return 1;
            if ($vehicle->dashboard_status === 'warning')
                return 2;
            return 3;
        });

        $projects = Project::orderBy('name')->get();
        $types = Vehicle::select('type')->distinct()->orderBy('type')->pluck('type');
        
        // Get unread alerts count
        $unreadAlerts = MaintenanceAlert::where('status', 'active')->count();

        return view('admin.maintenance.index', compact('maintenanceData', 'stats', 'projects', 'types', 'unreadAlerts'));
    }

    public function daftarAset(Request $request)
    {
        $query = Vehicle::with('project');
        if ($request->filled('project_id'))
            $query->where('project_id', $request->project_id);
        if ($request->filled('kategori'))
            $query->where('type', $request->kategori);
        if ($request->filled('search'))
            $query->search($request->search);

        $vehicles = $query->orderBy('created_at', 'desc')->paginate(10);
        $projects = Project::orderBy('name')->get();
        $categories = Vehicle::select('type')->distinct()->pluck('type');

        return view('admin.daftar_aset', compact('vehicles', 'projects', 'categories'));
    }

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
     * FUNGSI RIWAYAT SERVIS YANG SUDAH DIPERBARUI
     */
    public function riwayatServis($id)
    {
        $vehicle = Vehicle::with([
            'maintenanceSchedules' => function ($query) {
                $query->where('status', 'completed')
                    ->orderBy('scheduled_date', 'desc')
                    ->with('component');
            }
        ])->findOrFail($id);

        // Cari jadwal servis berikutnya yang belum selesai
        $nextSchedule = MaintenanceSchedule::where('vehicle_id', $id)
            ->whereIn('status', ['pending', 'scheduled'])
            ->orderBy('scheduled_date', 'asc')
            ->first();

        // Hitung Health Score agar konsisten dengan dashboard
        $score = $this->healthService->calculateHealthScore($vehicle);

        return view('admin.aset.riwayat', compact('vehicle', 'nextSchedule', 'score'));
    }
    public function calendar()
    {
        $today = Carbon::now('Asia/Jakarta')->startOfDay();
        $overdueCount = Vehicle::where(function ($q) use ($today) {
            $q->whereNotNull('pajak_stnk_berlaku_sampai')
                ->where('pajak_stnk_berlaku_sampai', '<', $today);
        })->orWhere(function ($q) use ($today) {
            $q->whereNotNull('kir_berlaku_sampai')
                ->where('kir_berlaku_sampai', '<', $today);
        })->count();

        return view('admin.maintenance_calendar', compact('overdueCount'));
    }

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
                    'id' => 'stnk_' . $vehicle->id,
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
                    'id' => 'kir_' . $vehicle->id,
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

    public function catatServis(Request $request, $id)
    {
        // Fungsi lama ini bisa dibiarkan saja di controller, tapi tidak akan dipanggil lagi oleh sistem baru.
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

    public function edit($id)
    {
        $vehicle = Vehicle::findOrFail($id);
        $projects = Project::all();
        return view('admin.aset.edit', compact('vehicle', 'projects'));
    }

    public function update(Request $request, $id)
    {
        $vehicle = Vehicle::findOrFail($id);
        $request->validate(['type' => 'required']);
        $vehicle->update($request->except(['plate_number']));
        return redirect()->route('admin.daftar_aset')->with('success', 'Data aset diperbarui.');
    }

    public function create()
    {
        $projects = Project::all();
        return view('admin.aset.create', compact('projects'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'plate_number' => 'required|unique:vehicles,plate_number|max:15',
            'type' => 'required|string|max:50',
            'current_km' => 'required|numeric|min:0', // Validasi baru
        ]);

        Vehicle::create([
            'plate_number' => strtoupper($request->plate_number),
            'type' => $request->type,
            'current_km' => $request->current_km, // Simpan Odometer inputan admin
            'project_id' => $request->project_id,
        ]);

        return redirect()->route('admin.daftar_aset')->with('success', 'Aset baru berhasil ditambahkan.');
    }

    public function destroy($id)
    {
        $vehicle = Vehicle::findOrFail($id);

        // Hapus kendaraan
        $vehicle->delete();

        return redirect()->route('admin.daftar_aset')->with('success', 'Data aset berhasil dihapus.');
    }
    /**
     * FUNGSI EXPORT EXCEL YANG SUDAH DIPERBARUI
     */
    public function exportExcel($id)
    {
        // PERBAIKAN: Gunakan nama relasi yang benar yaitu 'maintenanceSchedules'
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

    public function exportRekapAbsensi(Request $request)
    {
        $startDate = $request->input('start_date') ? Carbon::parse($request->input('start_date')) : Carbon::now()->startOfMonth();
        $endDate = $request->input('end_date') ? Carbon::parse($request->input('end_date')) : Carbon::now()->endOfMonth();
        $projectId = $request->project_id;

        $periode = CarbonPeriod::create($startDate, $endDate);
        $projectName = 'SEMUA PROJECT';
        if ($projectId) {
            $project = Project::find($projectId);
            if ($project)
                $projectName = strtoupper($project->name);
        }

        $query = Driver::with('project')->orderBy('full_name', 'asc');
        if ($projectId)
            $query->where('project_id', $projectId);
        $drivers = $query->get();

        $dataRekap = [];
        foreach ($drivers as $driver) {
            $row = [];
            $row['nik_ktp'] = $driver->nik_ktp ?? '-';
            $row['id_driver'] = $driver->driver_id_nik;
            $row['nama'] = $driver->full_name;
            $row['project'] = $driver->project->name ?? '-';
            $row['id'] = $driver->id;

            $lastLog = Attendance::where('driver_id', $driver->id)
                ->whereBetween('created_at', [$startDate->copy()->startOfDay(), $endDate->copy()->endOfDay()])
                ->with('vehicle')->latest()->first();

            $row['no_pol'] = $lastLog && $lastLog->vehicle ? $lastLog->vehicle->plate_number : '-';
            $row['type'] = $lastLog && $lastLog->vehicle ? $lastLog->vehicle->type : '-';

            $totalHadir = 0;
            $harian = [];
            foreach ($periode as $date) {
                $dateStr = $date->format('Y-m-d');
                $isPresent = Attendance::where('driver_id', $driver->id)->whereDate('created_at', $dateStr)->exists();
                if ($isPresent) {
                    $harian[$dateStr] = '✓';
                    $totalHadir++;
                } else {
                    $harian[$dateStr] = 'X';
                }
            }
            $row['harian'] = $harian;
            $row['total'] = $totalHadir;
            $dataRekap[] = $row;
        }

        $rangeName = $startDate->format('dM') . '-' . $endDate->format('dM_Y');
        $safeProjectName = str_replace([' ', '/', '\\'], '_', $projectName);
        $namaFile = 'Rekap_Absensi_' . $safeProjectName . '_' . $rangeName . '.xls';

        return response(view('admin.absensi.rekap_excel', compact('dataRekap', 'periode', 'startDate', 'endDate', 'projectName')))
            ->header('Content-Type', 'application/vnd.ms-excel')
            ->header('Content-Disposition', 'attachment; filename="' . $namaFile . '"');
    }

    public function components($vehicleId)
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

    public function storeComponent(Request $request, $vehicleId)
    {
        $request->validate([
            'component_name' => 'required|string|max:100',
            'category' => 'required|string|max:50',
            'replacement_interval_km' => 'nullable|integer|min:0',
            'replacement_interval_days' => 'nullable|integer|min:0',
            'last_replacement_km' => 'nullable|integer|min:0',
            'last_replacement_date' => 'nullable|date',
            'cost_per_replacement' => 'required|numeric|min:0',
        ]);

        $vehicle = Vehicle::findOrFail($vehicleId);
        $vehicle->components()->create($request->all());

        return back()->with('success', 'Komponen berhasil ditambahkan.');
    }

    public function updateComponent(Request $request, $componentId)
    {
        $component = VehicleComponent::findOrFail($componentId);

        $request->validate([
            'replacement_interval_km' => 'nullable|integer|min:0',
            'replacement_interval_days' => 'nullable|integer|min:0',
            'last_replacement_km' => 'nullable|integer|min:0',
            'last_replacement_date' => 'nullable|date',
            'cost_per_replacement' => 'nullable|numeric|min:0',
        ]);

        $component->update($request->all());

        return back()->with('success', 'Komponen berhasil diupdate.');
    }

    public function deleteComponent($componentId)
    {
        $component = VehicleComponent::findOrFail($componentId);
        $component->delete();

        return back()->with('success', 'Komponen berhasil dihapus.');
    }

    public function alerts(Request $request)
    {
        $query = MaintenanceAlert::with(['vehicle', 'component']);

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        } else {
            $query->active();
        }

        if ($request->filled('alert_type')) {
            $query->where('alert_type', $request->alert_type);
        }

        $alerts = $query->orderBy('alert_type')
            ->orderBy('triggered_at', 'desc')
            ->paginate(20);

        $summary = $this->alertService->getActiveAlertsSummary();

        return view('admin.maintenance.alerts', compact('alerts', 'summary'));
    }

    public function acknowledgeAlert($alertId)
    {
        $alert = MaintenanceAlert::findOrFail($alertId);
        $alert->acknowledge(Auth::user());

        return back()->with('success', 'Alert telah di-acknowledge.');
    }

    public function resolveAlert($alertId)
    {
        $alert = MaintenanceAlert::findOrFail($alertId);
        $alert->resolve();

        return back()->with('success', 'Alert telah di-resolve.');
    }

    public function schedules(Request $request)
    {
        $query = MaintenanceSchedule::with(['vehicle', 'component']);

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('priority')) {
            $query->where('priority', $request->priority);
        }

        if ($request->filled('vehicle_id')) {
            $query->where('vehicle_id', $request->vehicle_id);
        }

        $schedules = $query->orderBy('scheduled_date')->paginate(20);

        $stats = [
            'overdue' => MaintenanceSchedule::overdue()->count(),
            'today' => MaintenanceSchedule::where('scheduled_date', now()->toDateString())
                ->where('status', '!=', 'completed')->count(),
            'this_week' => MaintenanceSchedule::upcoming(7)->count(),
        ];

        $vehicles = Vehicle::orderBy('plate_number')->get();

        return view('admin.maintenance.schedules', compact('schedules', 'stats', 'vehicles'));
    }

    public function storeSchedule(Request $request)
    {
        $request->validate([
            'vehicle_id' => 'required|exists:vehicles,id',
            'component_id' => 'nullable|exists:vehicle_components,id',
            'scheduled_date' => 'required|date',
            'scheduled_km' => 'nullable|integer|min:0',
            'type' => 'required|in:preventive,corrective,predictive',
            'priority' => 'required|in:low,medium,high,critical',
            'estimated_cost' => 'nullable|numeric|min:0',
            'workshop_name' => 'nullable|string|max:255',
            'notes' => 'nullable|string',
        ]);

        MaintenanceSchedule::create($request->all());

        return back()->with('success', 'Jadwal maintenance berhasil dibuat.');
    }

    public function completeSchedule(Request $request, $scheduleId)
    {
        $schedule = MaintenanceSchedule::findOrFail($scheduleId);

        $request->validate([
            'actual_cost' => 'nullable|numeric|min:0',
            'notes' => 'nullable|string',
        ]);

        if ($request->filled('notes')) {
            $schedule->notes = $request->notes;
        }

        $schedule->markAsCompleted(Auth::user(), $request->actual_cost);

        return back()->with('success', 'Maintenance telah diselesaikan.');
    }

    public function getVehicleComponents($vehicleId)
    {
        $components = VehicleComponent::where('vehicle_id', $vehicleId)
            ->select('id', 'component_name', 'category', 'status')
            ->get();

        return response()->json($components);
    }

    /**
     * Export Dashboard to Excel
     */
    public function exportDashboard(Request $request)
    {
        $filters = $request->only(['project_id', 'type', 'search', 'status_filter']);
        $filename = 'Maintenance_Dashboard_' . now()->format('Y-m-d_His') . '.xlsx';
        
        return Excel::download(new MaintenanceDashboardExport($filters), $filename);
    }

    /**
     * Export Schedules to Excel
     */
    public function exportSchedules(Request $request)
    {
        $filters = $request->only(['status', 'priority', 'vehicle_id', 'type']);
        $filename = 'Maintenance_Schedules_' . now()->format('Y-m-d_His') . '.xlsx';
        
        return Excel::download(new MaintenanceSchedulesExport($filters), $filename);
    }

    /**
     * Export Alerts to Excel
     */
    public function exportAlerts(Request $request)
    {
        $filters = $request->only(['status', 'alert_type']);
        $filename = 'Maintenance_Alerts_' . now()->format('Y-m-d_His') . '.xlsx';
        
        return Excel::download(new MaintenanceAlertsExport($filters), $filename);
    }
}