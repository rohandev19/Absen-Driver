<?php

namespace App\Services;

use App\Models\Vehicle;
use App\Models\VehicleComponent;
use App\Models\MaintenanceAlert;
use Carbon\Carbon;

class MaintenanceAlertService
{
    /**
     * Generate alerts for all vehicles
     */
    public function generateAlertsForAllVehicles(): array
    {
        $vehicles = Vehicle::with('components')->get();
        $stats = [
            'total_vehicles' => $vehicles->count(),
            'alerts_created' => 0,
            'critical' => 0,
            'warning' => 0,
            'overdue' => 0,
        ];

        foreach ($vehicles as $vehicle) {
            $alerts = $this->generateAlertsForVehicle($vehicle);
            $stats['alerts_created'] += count($alerts);

            foreach ($alerts as $alert) {
                $stats[$alert['alert_type']]++;
            }
        }

        return $stats;
    }

    /**
     * Generate alerts for a specific vehicle
     */
    public function generateAlertsForVehicle(Vehicle $vehicle): array
    {
        $alerts = [];

        foreach ($vehicle->components as $component) {
            $alert = $this->checkComponentAlert($component);
            
            if ($alert) {
                $alerts[] = $alert;
            }
        }

        return $alerts;
    }

    /**
     * Check if component needs alert
     */
    private function checkComponentAlert(VehicleComponent $component): ?array
    {
        $vehicle = $component->vehicle;
        
        if (!$vehicle || (!$component->next_replacement_km && !$component->next_replacement_date)) {
            return null;
        }

        $kmRemaining = $component->next_replacement_km ? ($component->next_replacement_km - $vehicle->current_km) : null;
        $alertType = null;
        $message = null;

        // Check KM-based alerts
        if ($kmRemaining !== null) {
            if ($kmRemaining <= 0) {
                $alertType = 'overdue';
                $message = sprintf(
                    'OVERDUE: %s untuk %s (%s) sudah melewati batas penggantian. ' .
                    'Seharusnya diganti pada %s KM, saat ini %s KM.',
                    $component->component_name,
                    $vehicle->plate_number,
                    $vehicle->type,
                    number_format($component->next_replacement_km),
                    number_format($vehicle->current_km)
                );
            } elseif ($kmRemaining <= $component->critical_threshold_km) {
                $alertType = 'critical';
                $message = sprintf(
                    'CRITICAL: %s untuk %s (%s) perlu segera diganti. ' .
                    'Sisa %s KM lagi (target: %s KM).',
                    $component->component_name,
                    $vehicle->plate_number,
                    $vehicle->type,
                    number_format($kmRemaining),
                    number_format($component->next_replacement_km)
                );
            } elseif ($kmRemaining <= $component->warning_threshold_km) {
                $alertType = 'warning';
                $message = sprintf(
                    'WARNING: %s untuk %s (%s) akan segera perlu diganti. ' .
                    'Sisa %s KM lagi (target: %s KM).',
                    $component->component_name,
                    $vehicle->plate_number,
                    $vehicle->type,
                    number_format($kmRemaining),
                    number_format($component->next_replacement_km)
                );
            }
        }

        // Check date-based alerts
        if ($component->next_replacement_date) {
            $daysRemaining = Carbon::now()->diffInDays($component->next_replacement_date, false);
            
            if ($daysRemaining < 0 && (!$alertType || $alertType !== 'overdue')) {
                $alertType = 'overdue';
                $message = sprintf(
                    'OVERDUE: %s untuk %s (%s) sudah melewati tanggal penggantian (%s).',
                    $component->component_name,
                    $vehicle->plate_number,
                    $vehicle->type,
                    $component->next_replacement_date->format('d M Y')
                );
            } elseif ($daysRemaining <= 3 && $daysRemaining >= 0 && $alertType !== 'overdue') {
                if (!$alertType || $alertType === 'warning') {
                    $alertType = 'critical';
                    $message = sprintf(
                        'CRITICAL: %s untuk %s (%s) perlu diganti dalam %d hari (tanggal: %s).',
                        $component->component_name,
                        $vehicle->plate_number,
                        $vehicle->type,
                        $daysRemaining,
                        $component->next_replacement_date->format('d M Y')
                    );
                }
            } elseif ($daysRemaining <= 7 && $daysRemaining > 3 && !$alertType) {
                $alertType = 'warning';
                $message = sprintf(
                    'WARNING: %s untuk %s (%s) perlu diganti dalam %d hari (tanggal: %s).',
                    $component->component_name,
                    $vehicle->plate_number,
                    $vehicle->type,
                    $daysRemaining,
                    $component->next_replacement_date->format('d M Y')
                );
            }
        }

        if (!$alertType) {
            return null;
        }

        // Check if alert already exists and is active or acknowledged
        $existingAlert = MaintenanceAlert::where('vehicle_id', $vehicle->id)
            ->where('component_id', $component->id)
            ->whereIn('status', ['active', 'acknowledged'])
            ->where('alert_type', $alertType)
            ->first();

        if ($existingAlert) {
            return null; // Don't create duplicate alert
        }

        // Create new alert
        $alert = MaintenanceAlert::create([
            'vehicle_id' => $vehicle->id,
            'component_id' => $component->id,
            'alert_type' => $alertType,
            'message' => $message,
            'triggered_at' => now(),
            'status' => 'active',
        ]);

        return $alert->toArray();
    }

    /**
     * Get alert priority for notification routing
     */
    public function getAlertPriority(string $alertType): array
    {
        $priorities = [
            'overdue' => [
                'level' => 'critical',
                'channels' => ['email', 'sms', 'push', 'dashboard'],
                'escalation' => 'immediate',
            ],
            'critical' => [
                'level' => 'high',
                'channels' => ['email', 'sms', 'push', 'dashboard'],
                'escalation' => '24_hours',
            ],
            'warning' => [
                'level' => 'medium',
                'channels' => ['email', 'push', 'dashboard'],
                'escalation' => '3_days',
            ],
        ];

        return $priorities[$alertType] ?? $priorities['warning'];
    }

    /**
     * Resolve alerts for a component after maintenance
     */
    public function resolveComponentAlerts(VehicleComponent $component): int
    {
        $resolved = MaintenanceAlert::where('component_id', $component->id)
            ->whereIn('status', ['active', 'acknowledged'])
            ->update([
                'status' => 'resolved',
                'resolved_at' => now(),
            ]);

        return $resolved;
    }

    /**
     * Get active alerts summary
     */
    public function getActiveAlertsSummary(): array
    {
        $alerts = MaintenanceAlert::active()->with(['vehicle', 'component'])->get();

        return [
            'total' => $alerts->count(),
            'by_type' => [
                'overdue' => $alerts->where('alert_type', 'overdue')->count(),
                'critical' => $alerts->where('alert_type', 'critical')->count(),
                'warning' => $alerts->where('alert_type', 'warning')->count(),
            ],
            'by_vehicle' => $alerts->groupBy('vehicle_id')->map(function ($group) {
                return [
                    'plate_number' => $group->first()->vehicle->plate_number,
                    'count' => $group->count(),
                    'highest_priority' => $group->sortBy('alert_type')->first()->alert_type,
                ];
            })->values(),
        ];
    }
}
