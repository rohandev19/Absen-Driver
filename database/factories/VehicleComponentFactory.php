<?php

namespace Database\Factories;

use App\Models\Vehicle;
use Illuminate\Database\Eloquent\Factories\Factory;
use Carbon\Carbon;

class VehicleComponentFactory extends Factory
{
    public function definition(): array
    {
        $categories = ['engine', 'transmission', 'brake', 'tire', 'electrical', 'suspension'];
        $componentNames = [
            'engine' => ['Oli Mesin', 'Filter Oli', 'Busi', 'Radiator'],
            'transmission' => ['Oli Transmisi', 'Kopling'],
            'brake' => ['Kampas Rem', 'Minyak Rem', 'Disc Brake'],
            'tire' => ['Ban Depan', 'Ban Belakang'],
            'electrical' => ['Aki', 'Alternator'],
            'suspension' => ['Shock Absorber', 'Per'],
        ];

        $category = $this->faker->randomElement($categories);
        $componentName = $this->faker->randomElement($componentNames[$category]);

        $replacementIntervalKm = $this->faker->randomElement([5000, 10000, 15000, 20000]);
        $lastReplacementKm = $this->faker->numberBetween(30000, 45000);

        return [
            'vehicle_id' => Vehicle::factory(),
            'component_name' => $componentName,
            'category' => $category,
            'replacement_interval_km' => $replacementIntervalKm,
            'replacement_interval_days' => $this->faker->randomElement([90, 180, 365]),
            'last_replacement_km' => $lastReplacementKm,
            'last_replacement_date' => Carbon::now()->subDays($this->faker->numberBetween(30, 180)),
            'next_replacement_km' => $lastReplacementKm + $replacementIntervalKm,
            'next_replacement_date' => Carbon::now()->addDays($this->faker->numberBetween(30, 180)),
            'cost_per_replacement' => $this->faker->numberBetween(100000, 2000000),
            'warning_threshold_km' => 1000,
            'critical_threshold_km' => 500,
            'status' => $this->faker->randomElement(['healthy', 'warning', 'critical', 'overdue']),
            'notes' => $this->faker->optional()->sentence(),
        ];
    }

    public function healthy(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'healthy',
            'next_replacement_km' => $attributes['last_replacement_km'] + 5000,
        ]);
    }

    public function warning(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'warning',
            'next_replacement_km' => $attributes['last_replacement_km'] + 800,
        ]);
    }

    public function critical(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'critical',
            'next_replacement_km' => $attributes['last_replacement_km'] + 300,
        ]);
    }

    public function overdue(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'overdue',
            'next_replacement_km' => $attributes['last_replacement_km'] - 1000,
        ]);
    }
}
