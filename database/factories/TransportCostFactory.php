<?php

namespace Database\Factories;

use App\Models\Driver;
use App\Models\Vehicle;
use App\Models\Project;
use App\Models\Attendance;
use Illuminate\Database\Eloquent\Factories\Factory;
use Carbon\Carbon;

class TransportCostFactory extends Factory
{
    public function definition(): array
    {
        $odometerStart = $this->faker->numberBetween(40000, 50000);
        $odometerEnd = $odometerStart + $this->faker->numberBetween(50, 300);
        $kmTraveled = $odometerEnd - $odometerStart;

        $gasolineCost = $this->faker->numberBetween(100000, 500000);
        $gasolinePrice = 10000;
        $fuelConsumed = $gasolineCost / $gasolinePrice;
        $fuelEfficiency = $kmTraveled / $fuelConsumed;

        $deliveryStartTime = Carbon::today()->setHour(8);
        $deliveryEndTime = $deliveryStartTime->copy()->addHours($this->faker->numberBetween(8, 14));
        $actualHours = $deliveryStartTime->diffInHours($deliveryEndTime);
        $overtimeHours = max(0, $actualHours - 8);
        $overtimePayment = $overtimeHours * 25000;

        return [
            'driver_id' => Driver::factory(),
            'vehicle_id' => Vehicle::factory(),
            'project_id' => Project::factory(),
            'attendance_id' => Attendance::factory(),
            'trip_date' => Carbon::today()->subDays($this->faker->numberBetween(0, 30)),
            'do_number' => 'DO-' . date('Y') . '-' . str_pad($this->faker->numberBetween(1, 999), 3, '0', STR_PAD_LEFT),
            'drop_point_count' => $this->faker->numberBetween(1, 5),
            'delivery_location' => $this->faker->city() . ' - ' . $this->faker->city(),
            'odometer_start' => $odometerStart,
            'odometer_end' => $odometerEnd,
            'gasoline_cost' => $gasolineCost,
            'toll_cost' => $this->faker->numberBetween(20000, 100000),
            'parking_cost' => $this->faker->numberBetween(5000, 20000),
            'gasoline_price_per_liter' => $gasolinePrice,
            'fuel_consumed' => round($fuelConsumed, 2),
            'fuel_efficiency_ratio' => round($fuelEfficiency, 2),
            'delivery_start_time' => $deliveryStartTime,
            'delivery_end_time' => $deliveryEndTime,
            'actual_delivery_hours' => $actualHours,
            'overtime_hours' => $overtimeHours,
            'overtime_rate_per_hour' => 25000,
            'overtime_payment' => $overtimePayment,
            'bonus_driver' => 0,
            'bonus_notes' => 'Fitur bonus telah dihapus',
            'approval_status' => $this->faker->randomElement(['pending', 'approved', 'rejected']),
            'approved_by' => null,
            'approved_at' => null,
            'rejection_reason' => null,
            'submitted_to_finance' => false,
            'submitted_to_finance_at' => null,
            'submitted_to_finance_by' => null,
            'finance_word_path' => null,
            'gasoline_receipt_path' => 'transport_costs/gasoline_' . $this->faker->uuid() . '.jpg',
            'toll_receipt_path' => 'transport_costs/toll_' . $this->faker->uuid() . '.jpg',
            'parking_receipt_path' => 'transport_costs/parking_' . $this->faker->uuid() . '.jpg',
            'created_by' => null,
            'updated_by' => null,
        ];
    }

    public function pending(): static
    {
        return $this->state(fn (array $attributes) => [
            'approval_status' => 'pending',
            'approved_by' => null,
            'approved_at' => null,
        ]);
    }

    public function approved(): static
    {
        return $this->state(fn (array $attributes) => [
            'approval_status' => 'approved',
            'approved_by' => 1,
            'approved_at' => Carbon::now()->subDays($this->faker->numberBetween(1, 7)),
        ]);
    }

    public function rejected(): static
    {
        return $this->state(fn (array $attributes) => [
            'approval_status' => 'rejected',
            'approved_by' => 1,
            'approved_at' => Carbon::now()->subDays($this->faker->numberBetween(1, 7)),
            'rejection_reason' => $this->faker->sentence(),
        ]);
    }
}
