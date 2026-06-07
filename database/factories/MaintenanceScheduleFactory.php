<?php

namespace Database\Factories;

use App\Models\Vehicle;
use App\Models\VehicleComponent;
use Illuminate\Database\Eloquent\Factories\Factory;
use Carbon\Carbon;

class MaintenanceScheduleFactory extends Factory
{
    public function definition(): array
    {
        $types = ['preventive', 'corrective', 'predictive'];
        $priorities = ['low', 'medium', 'high', 'critical'];
        $statuses = ['pending', 'in_progress', 'completed', 'cancelled'];

        return [
            'vehicle_id' => Vehicle::factory(),
            'component_id' => VehicleComponent::factory(),
            'scheduled_date' => Carbon::now()->addDays($this->faker->numberBetween(1, 30)),
            'scheduled_km' => $this->faker->numberBetween(50000, 60000),
            'type' => $this->faker->randomElement($types),
            'priority' => $this->faker->randomElement($priorities),
            'status' => $this->faker->randomElement($statuses),
            'estimated_cost' => $this->faker->numberBetween(500000, 5000000),
            'actual_cost' => null,
            'workshop_name' => $this->faker->company(),
            'notes' => $this->faker->optional()->sentence(),
            'completed_at' => null,
            'completed_by' => null,
        ];
    }

    public function pending(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'pending',
            'completed_at' => null,
            'completed_by' => null,
        ]);
    }

    public function completed(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'completed',
            'completed_at' => Carbon::now()->subDays($this->faker->numberBetween(1, 30)),
            'completed_by' => 1,
            'actual_cost' => $this->faker->numberBetween(500000, 5000000),
        ]);
    }

    public function upcoming(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'pending',
            'scheduled_date' => Carbon::now()->addDays($this->faker->numberBetween(1, 7)),
        ]);
    }

    public function overdue(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'pending',
            'scheduled_date' => Carbon::now()->subDays($this->faker->numberBetween(1, 30)),
        ]);
    }
}
