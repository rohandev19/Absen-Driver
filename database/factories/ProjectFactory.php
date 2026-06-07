<?php

namespace Database\Factories;

use App\Models\Customer;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Project>
 */
class ProjectFactory extends Factory
{
    public function definition(): array
    {
        return [
            'name' => $this->faker->company() . ' Project',
            'code' => strtoupper($this->faker->unique()->bothify('PRJ###')),
            'customer_id' => Customer::factory(),
        ];
    }
}
