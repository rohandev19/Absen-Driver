<?php

namespace App\Http\Controllers;

use App\Models\Vehicle;
use App\Models\Project;
use App\Services\VehicleHealthService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * CustomerDashboardController
 * 
 * Customer hub page showing fleet overview, alerts, and pending approvals.
 * All data is scoped to the customer's own projects only.
 */
class CustomerDashboardController extends Controller
{
    public function __construct(
        private VehicleHealthService $healthService
    ) {}

    /**
     * Show customer dashboard with fleet overview.
     */
    public function index()
    {
        $customerId = Auth::user()->customer_id;

        if (!$customerId) {
            abort(403, 'User tidak terhubung dengan customer manapun.');
        }

        // Get all projects belonging to this customer
        $projects = Project::where('customer_id', $customerId)->get();
        $projectIds = $projects->pluck('id');

        // Get all vehicles in customer's projects
        $vehicles = Vehicle::whereIn('project_id', $projectIds)
            ->with(['project', 'components', 'maintenanceAlerts'])
            ->get();

        // Calculate health reports for each vehicle
        $healthReports = $vehicles->map(function ($vehicle) {
            $report = $this->healthService->getHealthReport($vehicle);
            $report['project_name'] = $vehicle->project->name ?? '-';
            $report['vehicle_type'] = $vehicle->type;
            $report['stnk_status'] = $this->getStnkStatus($vehicle);
            $report['kir_status'] = $this->getKirStatus($vehicle);
            return $report;
        })->sortBy('health_score')->values();

        // Fleet statistics
        $stats = [
            'total_vehicles' => $vehicles->count(),
            'total_projects' => $projects->count(),
            'avg_health' => $healthReports->avg('health_score') ?? 0,
            'excellent' => $healthReports->filter(fn($r) => $r['health_score'] >= 90)->count(),
            'good' => $healthReports->filter(fn($r) => $r['health_score'] >= 75 && $r['health_score'] < 90)->count(),
            'warning' => $healthReports->filter(fn($r) => $r['health_score'] >= 60 && $r['health_score'] < 75)->count(),
            'critical' => $healthReports->filter(fn($r) => $r['health_score'] < 60)->count(),
            'total_alerts' => $healthReports->sum('active_alerts'),
        ];

        // Pending service approvals count
        $pendingApprovals = \App\Models\ServiceReport::where('customer_id', $customerId)
            ->where('status', \App\Models\ServiceReport::STATUS_PENDING_CUSTOMER)
            ->count();

        return view('customer.dashboard', compact(
            'projects', 'healthReports', 'stats', 'pendingApprovals'
        ));
    }

    private function getStnkStatus(Vehicle $vehicle): array
    {
        if (!$vehicle->pajak_stnk_berlaku_sampai) {
            return ['status' => 'unknown', 'label' => 'Belum diisi', 'color' => 'secondary'];
        }

        $daysLeft = now()->diffInDays($vehicle->pajak_stnk_berlaku_sampai, false);
        
        if ($daysLeft < 0) {
            return ['status' => 'expired', 'label' => 'MATI', 'color' => 'danger'];
        }
        if ($daysLeft <= 30) {
            return ['status' => 'warning', 'label' => "{$daysLeft} hari lagi", 'color' => 'warning'];
        }
        return ['status' => 'active', 'label' => 'Aktif', 'color' => 'success'];
    }

    private function getKirStatus(Vehicle $vehicle): array
    {
        if (!$vehicle->kir_berlaku_sampai) {
            return ['status' => 'unknown', 'label' => 'Belum diisi', 'color' => 'secondary'];
        }

        $daysLeft = now()->diffInDays($vehicle->kir_berlaku_sampai, false);
        
        if ($daysLeft < 0) {
            return ['status' => 'expired', 'label' => 'MATI', 'color' => 'danger'];
        }
        if ($daysLeft <= 30) {
            return ['status' => 'warning', 'label' => "{$daysLeft} hari lagi", 'color' => 'warning'];
        }
        return ['status' => 'active', 'label' => 'Aktif', 'color' => 'success'];
    }
}
