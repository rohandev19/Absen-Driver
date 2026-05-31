<?php

namespace App\Services;

class FuelEfficiencyService
{
    /**
     * Calculate fuel efficiency metrics
     * 
     * @param int $odometerDifference KM traveled
     * @param float $gasolineCost Total gasoline cost
     * @param float|null $pricePerLiter Price per liter
     * @return array ['fuel_consumed', 'fuel_efficiency_ratio']
     */
    public function calculate(int $odometerDifference, float $gasolineCost, ?float $pricePerLiter): array
    {
        $fuelConsumed = null;
        $fuelEfficiencyRatio = null;

        if ($pricePerLiter && $pricePerLiter > 0) {
            $fuelConsumed = round($gasolineCost / $pricePerLiter, 2);
            
            if ($fuelConsumed > 0) {
                $fuelEfficiencyRatio = round($odometerDifference / $fuelConsumed, 2);
            }
        }

        return [
            'fuel_consumed' => $fuelConsumed,
            'fuel_efficiency_ratio' => $fuelEfficiencyRatio,
        ];
    }

    /**
     * Format fuel efficiency for display
     */
    public function format(?float $ratio): string
    {
        if ($ratio === null) {
            return 'N/A';
        }

        return number_format($ratio, 2) . ' KM/L';
    }
}
