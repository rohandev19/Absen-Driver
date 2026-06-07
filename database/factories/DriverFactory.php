<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Driver>
 */
class DriverFactory extends Factory
{
    public function definition(): array
    {
        return [
            'driver_id_nik' => strtoupper($this->faker->unique()->bothify('DRV###')),
            'nik_ktp' => $this->faker->unique()->numerify('################'),
            'full_name' => $this->faker->name(),
            'password' => Hash::make('password123'),
            'sim_expiry_date' => now()->addYear(),
            'sim_type' => 'B1',
            'project_id' => null,
            'is_on_duty' => false,
        ];
    }
}
