<?php

namespace App\Http\Controllers;

use App\Models\MaintenanceAlert;
use App\Services\MaintenanceAlertService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class MaintenanceAlertController extends Controller
{
    public function __construct(
        private MaintenanceAlertService $alertService
    ) {}

    /**
     * Get all alerts
     */
    public function index(Request $request)
    {
        $query = MaintenanceAlert::with(['vehicle', 'component']);

        // Filter by status
        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        // Filter by alert type
        if ($request->has('alert_type')) {
            $query->where('alert_type', $request->alert_type);
        }

        // Filter by vehicle
        if ($request->has('vehicle_id')) {
            $query->where('vehicle_id', $request->vehicle_id);
        }

        // Get only active alerts
        if ($request->has('active') && $request->active) {
            $query->active();
        }

        // Get only critical alerts
        if ($request->has('critical') && $request->critical) {
            $query->critical();
        }

        $alerts = $query->orderBy('alert_type')
            ->orderBy('triggered_at', 'desc')
            ->paginate(20);

        return response()->json([
            'success' => true,
            'data' => $alerts,
        ]);
    }

    /**
     * Get alerts summary
     */
    public function summary()
    {
        $summary = $this->alertService->getActiveAlertsSummary();

        return response()->json([
            'success' => true,
            'data' => $summary,
        ]);
    }

    /**
     * Acknowledge alert
     */
    public function acknowledge(MaintenanceAlert $alert)
    {
        $alert->acknowledge(Auth::user());

        return response()->json([
            'success' => true,
            'message' => 'Alert acknowledged',
            'data' => $alert->fresh(['vehicle', 'component', 'acknowledgedBy']),
        ]);
    }

    /**
     * Resolve alert
     */
    public function resolve(MaintenanceAlert $alert)
    {
        $alert->resolve();

        return response()->json([
            'success' => true,
            'message' => 'Alert resolved',
            'data' => $alert->fresh(['vehicle', 'component']),
        ]);
    }

    /**
     * Dismiss alert
     */
    public function dismiss(MaintenanceAlert $alert)
    {
        $alert->dismiss();

        return response()->json([
            'success' => true,
            'message' => 'Alert dismissed',
            'data' => $alert->fresh(['vehicle', 'component']),
        ]);
    }

    /**
     * Generate alerts manually
     */
    public function generate()
    {
        $stats = $this->alertService->generateAlertsForAllVehicles();

        return response()->json([
            'success' => true,
            'message' => 'Alerts generated successfully',
            'data' => $stats,
        ]);
    }
}
