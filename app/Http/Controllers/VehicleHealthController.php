<?php

namespace App\Http\Controllers;

use App\Models\Vehicle;
use App\Services\VehicleHealthService;
use Illuminate\Http\Request;

class VehicleHealthController extends Controller
{
    public function __construct(
        private VehicleHealthService $healthService
    ) {
    }

    /**
     * Get health report for a specific vehicle
     */
    public function show(Vehicle $vehicle)
    {
        $report = $this->healthService->getHealthReport($vehicle);

        return response()->json([
            'success' => true,
            'data' => $report,
        ]);
    }

    /**
     * Get health summary for all vehicles
     */
    public function index(Request $request)
    {
        $query = Vehicle::with(['components', 'maintenanceAlerts']);

        // Filter by project if provided
        if ($request->has('project_id')) {
            $query->where('project_id', $request->project_id);
        }

        // Filter by status if provided
        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        $vehicles = $query->get();

        $healthReports = $vehicles->map(function ($vehicle) {
            return $this->healthService->getHealthReport($vehicle);
        });

        // Sort by health score (lowest first - most critical)
        $healthReports = $healthReports->sortBy('health_score')->values();

        // Calculate fleet statistics
        $fleetStats = [
            'total_vehicles' => $vehicles->count(),
            'average_health_score' => round($healthReports->avg('health_score'), 2),
            'by_status' => [
                'excellent' => $healthReports->filter(fn($r) => $r['health_score'] >= 90)->count(),
                'good' => $healthReports->filter(fn($r) => $r['health_score'] >= 75 && $r['health_score'] < 90)->count(),
                'fair' => $healthReports->filter(fn($r) => $r['health_score'] >= 60 && $r['health_score'] < 75)->count(),
                'poor' => $healthReports->filter(fn($r) => $r['health_score'] >= 40 && $r['health_score'] < 60)->count(),
                'critical' => $healthReports->filter(fn($r) => $r['health_score'] < 40)->count(),
            ],
            'total_active_alerts' => $healthReports->sum('active_alerts'),
            'total_upcoming_maintenance' => $healthReports->sum('upcoming_maintenance'),
        ];

        return response()->json([
            'success' => true,
            'data' => [
                'fleet_stats' => $fleetStats,
                'vehicles' => $healthReports,
            ],
        ]);
    }
}
