<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Vehicle;
use App\Models\Attendance;
use App\Models\User;
use App\Models\Driver;
use App\Models\Project;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Facades\Excel;

class MaintenanceController extends Controller
{
    /**
     * CONSTRUCTOR: SETTING BAHASA INDONESIA
     * Agar hari muncul sebagai 'Senin', 'Minggu', dst.
     */
    public function __construct()
    {
        Carbon::setLocale('id');
        setlocale(LC_TIME, 'id_ID');
    }

    /**
     * 1. DASHBOARD MONITORING (FIXED STATS & LANGUAGE)
     */
    public function index(Request $request)
    {
        $query = Vehicle::query();

        // 1. FILTER DATABASE
        if ($request->filled('search')) {
            $query->search($request->search);
        }
        if ($request->filled('project_id')) {
            $query->where('project_id', $request->project_id);
        }
        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        $vehicles = $query->with(['latestAttendance', 'project'])->get();

        // 2. HITUNG STATISTIK (DIPERBAIKI)
        // Hitung dulu yang bermasalah
        $countDanger = $vehicles->filter(fn($v) => in_array($v->health_status_code, ['service_due', 'physical_issue']))->count();
        $countWarning = $vehicles->filter(fn($v) => $v->health_status_code == 'warning')->count();
        $countTotal = $vehicles->count();

        // Sisa-nya pasti SEHAT (KONDISI PRIMA)
        // Ini menjamin angka tidak akan 0 jika ada unit yang tersisa
        $countSehat = $countTotal - $countDanger - $countWarning;

        $stats = [
            'total' => $countTotal,
            'sehat' => $countSehat,
            'warning' => $countWarning,
            'danger' => $countDanger,
        ];

        // 3. FILTER STATUS (Interaksi Klik Kartu Atas)
        if ($request->filled('status_filter')) {
            $vehicles = $vehicles->filter(function ($vehicle) use ($request) {
                if ($request->status_filter == 'danger') {
                    return in_array($vehicle->health_status_code, ['service_due', 'physical_issue']);
                }
                if ($request->status_filter == 'warning') {
                    return $vehicle->health_status_code == 'warning';
                }
                if ($request->status_filter == 'safe') {
                    // Safe adalah selain danger dan warning
                    return !in_array($vehicle->health_status_code, ['service_due', 'physical_issue', 'warning']);
                }
                return true;
            });
        }

        // 4. SORTING FINAL
        $maintenanceData = $vehicles->sortBy(function ($vehicle) {
            if (($vehicle->sisa_km !== null && $vehicle->sisa_km < 0) || $vehicle->health_status_code === 'physical_issue')
                return 1;
            if ($vehicle->sisa_km !== null && $vehicle->sisa_km < 1000)
                return 2;
            return 3;
        });

        $projects = Project::orderBy('name')->get();
        $types = Vehicle::select('type')->distinct()->orderBy('type')->pluck('type');

        return view('admin.maintenance.index', compact(
            'maintenanceData',
            'stats',
            'projects',
            'types'
        ));
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

    // ==============================================================
    // FUNGSI INI YANG DIPERBAIKI (Penghitungan Banner Peringatan)
    // ==============================================================
    public function calendar()
    {
        $today = Carbon::now('Asia/Jakarta')->startOfDay();

        // Hitung total kendaraan yang STNK atau KIR-nya sudah lewat hari ini
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
    // ==============================================================

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
        ]);

        Vehicle::create([
            'plate_number' => strtoupper($request->plate_number),
            'type' => $request->type,
            'current_km' => 0,
            'project_id' => $request->project_id,
        ]);

        return redirect()->route('admin.daftar_aset')->with('success', 'Aset baru berhasil ditambahkan.');
    }

    public function destroy($id)
    {
        $vehicle = Vehicle::findOrFail($id);
        $vehicle->delete();
        return redirect()->route('admin.daftar_aset')->with('success', 'Data aset berhasil dihapus.');
    }

    public function exportExcel($id)
    {
        $vehicle = Vehicle::findOrFail($id);
        $logs = $vehicle->maintenanceLogs()->orderBy('service_date', 'desc')->get();
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
}