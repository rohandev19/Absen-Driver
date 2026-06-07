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

    public function __construct(VehicleHealthService $healthService, MaintenanceAlertService $alertService)
    {
        Carbon::setLocale('id');
        setlocale(LC_TIME, 'id_ID');
        $this->healthService = $healthService;
        $this->alertService = $alertService;
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

        $vehicles = $query->with([
            'latestAttendance', 
            'project', 
            'components',
            'maintenanceSchedules' => function($q) {
                $q->where('created_at', '>=', \Carbon\Carbon::now()->subMonths(6));
            },
            'attendances' => function($q) {
                $q->where('time_in', '>=', \Carbon\Carbon::now()->subDays(30))
                  ->whereNotNull('check_ban');
            }
        ])->get();

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

        $attendances = Attendance::whereBetween('created_at', [$startDate->copy()->startOfDay(), $endDate->copy()->endOfDay()])
            ->with('vehicle')
            ->get();
            
        $attendanceMap = [];
        $lastLogMap = [];
        foreach ($attendances as $att) {
            $date = $att->created_at->format('Y-m-d');
            $attendanceMap[$att->driver_id][$date] = true;
            if (!isset($lastLogMap[$att->driver_id]) || $att->created_at > $lastLogMap[$att->driver_id]->created_at) {
                $lastLogMap[$att->driver_id] = $att;
            }
        }

        $dataRekap = [];
        foreach ($drivers as $driver) {
            $row = [];
            $row['nik_ktp'] = $driver->nik_ktp ?? '-';
            $row['id_driver'] = $driver->driver_id_nik;
            $row['nama'] = $driver->full_name;
            $row['project'] = $driver->project->name ?? '-';
            $row['id'] = $driver->id;

            $lastLog = $lastLogMap[$driver->id] ?? null;

            $row['no_pol'] = $lastLog && $lastLog->vehicle ? $lastLog->vehicle->plate_number : '-';
            $row['type'] = $lastLog && $lastLog->vehicle ? $lastLog->vehicle->type : '-';

            $totalHadir = 0;
            $harian = [];
            foreach ($periode as $date) {
                $dateStr = $date->format('Y-m-d');
                $isPresent = isset($attendanceMap[$driver->id][$dateStr]);
                if ($isPresent) {
                    $harian[$dateStr] = '✓';
                    $totalHadir++;
                } else {
                    $harian[$dateStr] = 'X';
                }
            }
            $row['total_hadir'] = $totalHadir;
            $row['harian'] = $harian;

            $dataRekap[] = $row;
        }

        $rangeName = $startDate->format('dM') . '-' . $endDate->format('dM_Y');
        $safeProjectName = str_replace([' ', '/', '\\'], '_', $projectName);
        $namaFile = 'Rekap_Absensi_' . $safeProjectName . '_' . $rangeName . '.xls';

        return response(view('admin.absensi.rekap_excel', compact('dataRekap', 'periode', 'startDate', 'endDate', 'projectName')))
            ->header('Content-Type', 'application/vnd.ms-excel')
            ->header('Content-Disposition', 'attachment; filename="' . $namaFile . '"');
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
        $filename = 'Maintenance_Alerts_' . now()->format('Y-m_His') . '.xlsx';
        
        return Excel::download(new MaintenanceAlertsExport($filters), $filename);
    }

    /**
     * Show maintenance calendar view
     */
    public function calendar()
    {
        return view('admin.maintenance_calendar');
    }

    /**
     * Get maintenance calendar events in JSON format
     */
    public function getEvents()
    {
        $events = [];

        // 1. Get STNK Expirations
        $vehiclesWithStnk = Vehicle::whereNotNull('pajak_stnk_berlaku_sampai')->get();
        foreach ($vehiclesWithStnk as $vehicle) {
            $events[] = [
                'id' => 'stnk_' . $vehicle->id,
                'title' => $vehicle->plate_number . ' - STNK',
                'start' => Carbon::parse($vehicle->pajak_stnk_berlaku_sampai)->toDateString(),
                'backgroundColor' => '#0d6efd',
                'url' => route('admin.aset.edit', $vehicle->id),
            ];
        }

        // 2. Get KIR Expirations
        $vehiclesWithKir = Vehicle::whereNotNull('kir_berlaku_sampai')->get();
        foreach ($vehiclesWithKir as $vehicle) {
            $events[] = [
                'id' => 'kir_' . $vehicle->id,
                'title' => $vehicle->plate_number . ' - KIR',
                'start' => Carbon::parse($vehicle->kir_berlaku_sampai)->toDateString(),
                'backgroundColor' => '#198754',
                'url' => route('admin.aset.edit', $vehicle->id),
            ];
        }

        return response()->json($events);
    }
}