<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Vehicle>
 */
class VehicleFactory extends Factory
{
    public function definition(): array
    {
        return [
            'plate_number' => strtoupper($this->faker->unique()->bothify('B####??')),
            'type' => $this->faker->randomElement(['Truck', 'Pickup', 'Box']),
            'tahun_pembuatan' => $this->faker->numberBetween(2015, 2025),
            'project_id' => null,
            'current_km' => $this->faker->numberBetween(20000, 80000),
            'service_interval_km' => 10000,
            'last_service_km' => $this->faker->numberBetween(10000, 60000),
            'pajak_stnk_berlaku_sampai' => now()->addYear(),
            'kir_berlaku_sampai' => now()->addMonths(6),
        ];
    }
}
