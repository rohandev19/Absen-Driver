<?php

namespace App\Http\Controllers;

use App\Models\Vehicle;
use App\Models\MaintenanceLog;
use App\Models\MaintenanceSchedule;
use App\Services\VehicleHealthService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * CustomerVehicleController
 * 
 * Shows vehicle health details and maintenance history to customers.
 * All data is scoped by the EnsureCustomerOwnsVehicle middleware.
 * NO cost information is exposed to customers.
 */
class CustomerVehicleController extends Controller
{
    public function __construct(
        private VehicleHealthService $healthService
    ) {}

    /**
     * Display a listing of all vehicles belonging to the customer's projects.
     */
    public function index(Request $request)
    {
        $customerId = Auth::user()->customer_id;

        if (!$customerId) {
            abort(403, 'User tidak terhubung dengan customer manapun.');
        }

        $search = $request->input('search');

        // Get all projects belonging to this customer
        $projectIds = \App\Models\Project::where('customer_id', $customerId)->pluck('id');

        // Query vehicles in those projects
        $vehiclesQuery = Vehicle::whereIn('project_id', $projectIds)
            ->with(['project', 'components', 'maintenanceAlerts']);

        if ($search) {
            $vehiclesQuery->where(function ($q) use ($search) {
                $q->where('plate_number', 'like', "%{$search}%")
                  ->orWhere('type', 'like', "%{$search}%");
            });
        }

        $vehicles = $vehiclesQuery->paginate(12);

        // Map health score for each vehicle
        $vehicles->getCollection()->transform(function ($vehicle) {
            $report = $this->healthService->getHealthReport($vehicle);
            $vehicle->health_score = $report['health_score'];
            $vehicle->health_status = $report['status'];
            $vehicle->active_alerts_count = $report['active_alerts'];
            return $vehicle;
        });

        return view('customer.vehicles.index', compact('vehicles', 'search'));
    }

    /**
     * Show detailed health report for a single vehicle.
     */
    public function show(Vehicle $vehicle)
    {
        $vehicle->load(['project', 'components', 'maintenanceAlerts', 'maintenanceLogs']);

        $healthReport = $this->healthService->getHealthReport($vehicle);
        $healthStatus = $this->healthService->getHealthStatus($healthReport['health_score']);

        // Get component details (WITHOUT cost info)
        $components = $vehicle->components->map(function ($comp) {
            return [
                'id' => $comp->id,
                'name' => $comp->component_name,
                'category' => $comp->category,
                'status' => $comp->status,
                'health_score' => round($comp->health_score * 100),
                'km_remaining' => $comp->km_remaining,
                'days_remaining' => $comp->days_remaining,
                'last_replacement_date' => $comp->last_replacement_date?->format('d M Y'),
                'next_replacement_date' => $comp->next_replacement_date?->format('d M Y'),
            ];
        })->groupBy('category');

        // STNK & KIR status
        $documents = [
            'stnk' => $this->getDocumentStatus($vehicle->pajak_stnk_berlaku_sampai, 'STNK'),
            'kir' => $this->getDocumentStatus($vehicle->kir_berlaku_sampai, 'KIR'),
        ];

        // Recent maintenance history (NO cost info)
        $maintenanceHistory = MaintenanceSchedule::where('vehicle_id', $vehicle->id)
            ->where('status', 'completed')
            ->orderBy('completed_at', 'desc')
            ->limit(10)
            ->get()
            ->map(function ($schedule) {
                return [
                    'date' => $schedule->completed_at?->format('d M Y'),
                    'type' => $schedule->type,
                    'component' => $schedule->component?->component_name ?? 'General',
                    'workshop' => $schedule->workshop_name,
                    'notes' => $schedule->notes,
                ];
            });

        // Active alerts
        $activeAlerts = $vehicle->maintenanceAlerts()
            ->active()
            ->with('component')
            ->get()
            ->map(function ($alert) {
                return [
                    'type' => $alert->alert_type,
                    'message' => $alert->message,
                    'component' => $alert->component?->component_name,
                    'triggered_at' => $alert->triggered_at?->format('d M Y H:i'),
                ];
            });

        // Upcoming maintenance
        $upcomingMaintenance = MaintenanceSchedule::where('vehicle_id', $vehicle->id)
            ->upcoming(30)
            ->with('component')
            ->get()
            ->map(function ($schedule) {
                return [
                    'date' => $schedule->scheduled_date?->format('d M Y'),
                    'type' => $schedule->type,
                    'component' => $schedule->component?->component_name ?? 'General',
                    'priority' => $schedule->priority,
                    'days_until' => $schedule->days_until,
                ];
            });

        return view('customer.vehicles.show', compact(
            'vehicle', 'healthReport', 'healthStatus', 'components',
            'documents', 'maintenanceHistory', 'activeAlerts', 'upcomingMaintenance'
        ));
    }


    private function getDocumentStatus($expiryDate, string $label): array
    {
        if (!$expiryDate) {
            return [
                'label' => $label,
                'status' => 'Belum diisi',
                'color' => 'secondary',
                'icon' => 'bi-question-circle',
                'expiry' => null,
            ];
        }

        $daysLeft = (int) now()->diffInDays($expiryDate, false);

        if ($daysLeft < 0) {
            return [
                'label' => $label,
                'status' => 'MATI (' . abs($daysLeft) . ' hari lalu)',
                'color' => 'danger',
                'icon' => 'bi-x-circle-fill',
                'expiry' => $expiryDate->format('d M Y'),
            ];
        }

        if ($daysLeft <= 30) {
            return [
                'label' => $label,
                'status' => "Akan habis ({$daysLeft} hari)",
                'color' => 'warning',
                'icon' => 'bi-exclamation-triangle-fill',
                'expiry' => $expiryDate->format('d M Y'),
            ];
        }

        return [
            'label' => $label,
            'status' => 'Aktif',
            'color' => 'success',
            'icon' => 'bi-check-circle-fill',
            'expiry' => $expiryDate->format('d M Y'),
        ];
    }

    /**
     * Show print-friendly Health Certificate for a vehicle.
     */
    public function certificate(Vehicle $vehicle)
    {
        $vehicle->load(['project', 'components', 'maintenanceAlerts']);

        $healthReport = $this->healthService->getHealthReport($vehicle);
        $healthStatus = $this->healthService->getHealthStatus($healthReport['health_score']);

        // Only show if score is Good or Excellent (>= 75)
        if ($healthReport['health_score'] < 75) {
            return back()->with('error', 'Sertifikat Kelayakan hanya dapat diterbitkan untuk unit dengan skor kesehatan minimal 75 (Kategori Baik/Sangat Baik).');
        }

        // Generate verification URL
        $qrCodeData = route('customer.vehicles.show', $vehicle->id);

        return view('customer.vehicles.certificate', compact(
            'vehicle', 'healthReport', 'healthStatus', 'qrCodeData'
        ));
    }
}
