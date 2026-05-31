<?php

namespace App\Services;

use App\Models\Vehicle;
use App\Models\VehicleComponent;
use Carbon\Carbon;

class VehicleHealthService
{
    /**
     * Calculate overall vehicle health score (0-100)
     * 
     * Formula:
     * Health Score = (
     *     Component_Health_Average * 0.40 +
     *     Maintenance_Compliance * 0.30 +
     *     Daily_Check_Score * 0.20 +
     *     Age_Factor * 0.10
     * ) * 100
     */
    public function calculateHealthScore(Vehicle $vehicle): float
    {
        $componentHealth = $this->getComponentHealthAverage($vehicle);
        $maintenanceCompliance = $this->getMaintenanceCompliance($vehicle);
        $dailyCheckScore = $this->getDailyCheckScore($vehicle);
        $ageFactor = $this->getAgeFactor($vehicle);

        $score = (
            $componentHealth * 0.40 +
            $maintenanceCompliance * 0.30 +
            $dailyCheckScore * 0.20 +
            $ageFactor * 0.10
        ) * 100;

        return round($score, 2);
    }

    /**
     * Get average health of all components (0-1)
     */
    private function getComponentHealthAverage(Vehicle $vehicle): float
    {
        $components = $vehicle->components;

        if ($components->isEmpty()) {
            return 1.0; // No components tracked = assume healthy
        }

        $totalHealth = $components->sum(function ($component) {
            return $this->calculateComponentHealth($component);
        });

        return $totalHealth / $components->count();
    }

    /**
     * Calculate individual component health (0-1)
     */
    private function calculateComponentHealth(VehicleComponent $component): float
    {
        if (!$component->next_replacement_km || !$component->vehicle) {
            return 1.0;
        }

        $kmRemaining = $component->next_replacement_km - $component->vehicle->current_km;
        $kmInterval = $component->replacement_interval_km;

        if ($kmRemaining <= 0) {
            return 0.0; // Overdue
        }

        if ($kmRemaining <= $component->critical_threshold_km) {
            return 0.2; // Critical
        }

        if ($kmRemaining <= $component->warning_threshold_km) {
            return 0.5; // Warning
        }

        // Healthy: Linear interpolation
        return min(1.0, $kmRemaining / $kmInterval);
    }

    /**
     * Get maintenance compliance rate (0-1)
     * Percentage of scheduled maintenance completed on time
     */
    private function getMaintenanceCompliance(Vehicle $vehicle): float
    {
        $schedules = $vehicle->maintenanceSchedules()
            ->where('created_at', '>=', Carbon::now()->subMonths(6))
            ->get();

        if ($schedules->isEmpty()) {
            return 1.0; // No schedules = assume compliant
        }

        $completedOnTime = $schedules->filter(function ($schedule) {
            return $schedule->status === 'completed' 
                && $schedule->completed_at <= $schedule->scheduled_date;
        })->count();

        return $completedOnTime / $schedules->count();
    }

    /**
     * Get daily check score from driver inspections (0-1)
     */
    private function getDailyCheckScore(Vehicle $vehicle): float
    {
        $recentChecks = $vehicle->attendances()
            ->where('time_in', '>=', Carbon::now()->subDays(30))
            ->whereNotNull('check_ban')
            ->get();

        if ($recentChecks->isEmpty()) {
            return 0.8; // No recent checks = assume okay but not perfect
        }

        $totalScore = $recentChecks->sum(function ($attendance) {
            $score = 0;
            $checks = 0;

            if ($attendance->check_ban) {
                $checks++;
                $score += $attendance->check_ban === 'Baik' ? 1 : 0;
            }

            if ($attendance->check_lampu) {
                $checks++;
                $score += $attendance->check_lampu === 'Baik' ? 1 : 0;
            }

            if ($attendance->check_rem) {
                $checks++;
                $score += $attendance->check_rem === 'Baik' ? 1 : 0;
            }

            return $checks > 0 ? $score / $checks : 1;
        });

        return $totalScore / $recentChecks->count();
    }

    /**
     * Get age factor (0-1)
     * Newer vehicles score higher
     */
    private function getAgeFactor(Vehicle $vehicle): float
    {
        // Assuming expected lifespan is 10 years
        $expectedLifespanYears = 10;

        // Try to get vehicle age from pajak_stnk_berlaku_sampai
        // This is an approximation - ideally you'd have a purchase_date field
        if ($vehicle->pajak_stnk_berlaku_sampai) {
            $estimatedPurchaseYear = Carbon::parse($vehicle->pajak_stnk_berlaku_sampai)->year - 1;
            $vehicleAgeYears = Carbon::now()->year - $estimatedPurchaseYear;
        } else {
            // If no date available, use KM as proxy (assuming 20,000 KM per year)
            $vehicleAgeYears = $vehicle->current_km / 20000;
        }

        $ageFactor = 1 - ($vehicleAgeYears / $expectedLifespanYears);

        return max(0, min(1, $ageFactor));
    }

    /**
     * Get health status label and color
     */
    public function getHealthStatus(float $score): array
    {
        if ($score >= 90) {
            return [
                'label' => 'Sangat Baik',
                'color' => 'green',
                'icon' => '🟢',
                'action' => 'Kondisi prima, aman beroperasi'
            ];
        }

        if ($score >= 75) {
            return [
                'label' => 'Baik',
                'color' => 'green',
                'icon' => '🟢',
                'action' => 'Aman untuk beroperasi'
            ];
        }

        if ($score >= 60) {
            return [
                'label' => 'Cukup',
                'color' => 'yellow',
                'icon' => '🟡',
                'action' => 'Dalam pantauan servis rutin'
            ];
        }

        if ($score >= 40) {
            return [
                'label' => 'Buruk',
                'color' => 'orange',
                'icon' => '🟠',
                'action' => 'Butuh penanganan segera'
            ];
        }

        return [
            'label' => 'Kritis',
            'color' => 'red',
            'icon' => '🔴',
            'action' => 'Hentikan operasi, perbaikan darurat'
        ];
    }

    /**
     * Get detailed health report for a vehicle
     */
    public function getHealthReport(Vehicle $vehicle): array
    {
        $score = $this->calculateHealthScore($vehicle);
        $status = $this->getHealthStatus($score);

        return [
            'vehicle_id' => $vehicle->id,
            'plate_number' => $vehicle->plate_number,
            'health_score' => $score,
            'status' => $status,
            'breakdown' => [
                'component_health' => round($this->getComponentHealthAverage($vehicle) * 100, 2),
                'maintenance_compliance' => round($this->getMaintenanceCompliance($vehicle) * 100, 2),
                'daily_check_score' => round($this->getDailyCheckScore($vehicle) * 100, 2),
                'age_factor' => round($this->getAgeFactor($vehicle) * 100, 2),
            ],
            'components_needing_attention' => $vehicle->components()
                ->needsMaintenance()
                ->get()
                ->map(function ($component) {
                    return [
                        'name' => $component->component_name,
                        'status' => $component->status,
                        'km_remaining' => $component->km_remaining,
                        'days_remaining' => $component->days_remaining,
                    ];
                }),
            'active_alerts' => $vehicle->maintenanceAlerts()
                ->active()
                ->count(),
            'upcoming_maintenance' => $vehicle->maintenanceSchedules()
                ->upcoming(7)
                ->count(),
        ];
    }
}
