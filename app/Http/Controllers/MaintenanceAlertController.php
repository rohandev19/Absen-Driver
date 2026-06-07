<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\MaintenanceAlert;
use App\Services\MaintenanceAlertService;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\MaintenanceAlertsExport;

class MaintenanceAlertController extends Controller
{
    protected $alertService;

    public function __construct(MaintenanceAlertService $alertService)
    {
        $this->alertService = $alertService;
    }

    public function index(Request $request)
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

    public function generate()
    {
        $this->alertService->generateAlertsForAllVehicles();
        return back()->with('success', 'Alert pemeliharaan berhasil diperbarui dari data terbaru.');
    }

    public function acknowledge($alertId)
    {
        $alert = MaintenanceAlert::findOrFail($alertId);
        $alert->acknowledge(Auth::user());

        return back()->with('success', 'Alert telah di-acknowledge.');
    }

    public function resolve($alertId)
    {
        $alert = MaintenanceAlert::findOrFail($alertId);
        $alert->resolve();

        return back()->with('success', 'Alert telah di-resolve.');
    }

    public function export(Request $request)
    {
        $namaFile = 'Laporan_Alert_Maintenance_' . Carbon::now()->format('Ymd_His') . '.xlsx';
        return Excel::download(new MaintenanceAlertsExport($request), $namaFile);
    }
}
