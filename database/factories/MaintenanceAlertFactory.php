<?php

namespace Database\Factories;

use App\Models\Vehicle;
use App\Models\VehicleComponent;
use Illuminate\Database\Eloquent\Factories\Factory;
use Carbon\Carbon;

class MaintenanceAlertFactory extends Factory
{
    public function definition(): array
    {
        $alertTypes = ['warning', 'critical', 'overdue'];
        $alertType = $this->faker->randomElement($alertTypes);

        $messages = [
            'warning' => 'WARNING: Komponen akan segera perlu diganti.',
            'critical' => 'CRITICAL: Komponen perlu segera diganti.',
            'overdue' => 'OVERDUE: Komponen sudah melewati batas penggantian.',
        ];

        return [
            'vehicle_id' => Vehicle::factory(),
            'component_id' => VehicleComponent::factory(),
            'alert_type' => $alertType,
            'message' => $messages[$alertType],
            'triggered_at' => Carbon::now()->subDays($this->faker->numberBetween(0, 7)),
            'acknowledged_at' => null,
            'acknowledged_by' => null,
            'resolved_at' => null,
            'status' => 'active',
        ];
    }

    public function active(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'active',
            'acknowledged_at' => null,
            'resolved_at' => null,
        ]);
    }

    public function acknowledged(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'acknowledged',
            'acknowledged_at' => Carbon::now()->subDays(1),
            'acknowledged_by' => 1,
        ]);
    }

    public function resolved(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'resolved',
            'resolved_at' => Carbon::now(),
        ]);
    }
}
