<?php

namespace App\Services;

use Carbon\Carbon;

class OvertimeService
{
    /**
     * Calculate overtime hours and payment
     * 
     * @param string $startTime Delivery start datetime
     * @param string $endTime Delivery end datetime
     * @param int $projectId Project ID for configuration (unused after bonus config removal)
     * @return array ['actual_delivery_hours', 'overtime_hours', 'overtime_rate_per_hour', 'overtime_payment']
     */
    public function calculate(string $startTime, string $endTime, int $projectId): array
    {
        $start = Carbon::parse($startTime);
        $end = Carbon::parse($endTime);
        
        // Calculate actual delivery hours
        $actualHours = round($start->diffInMinutes($end) / 60, 2);
        
        // Use default values since bonus configuration was removed
        $deliveryTarget = 8.00; // Default 8 hours
        $overtimeRate = 0.00; // Default no overtime rate
        
        // Calculate overtime
        $overtimeHours = max(0, $actualHours - $deliveryTarget);
        $overtimePayment = round($overtimeHours * $overtimeRate, 2);
        
        return [
            'actual_delivery_hours' => $actualHours,
            'overtime_hours' => $overtimeHours,
            'overtime_rate_per_hour' => $overtimeRate,
            'overtime_payment' => $overtimePayment,
        ];
    }
}
