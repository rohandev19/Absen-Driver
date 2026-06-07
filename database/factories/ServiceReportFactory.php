<?php

namespace Database\Factories;

use App\Models\Driver;
use App\Models\Vehicle;
use App\Models\Customer;
use Illuminate\Database\Eloquent\Factories\Factory;
use Carbon\Carbon;

class ServiceReportFactory extends Factory
{
    public function definition(): array
    {
        $statuses = [
            'pending',
            'approved_admin',
            'pending_customer',
            'approved_customer',
            'rejected',
        ];

        return [
            'driver_id' => Driver::factory(),
            'vehicle_id' => Vehicle::factory(),
            'customer_id' => Customer::factory(),
            'timestamp' => Carbon::now()->subHours($this->faker->numberBetween(1, 48)),
            'gps_location' => $this->faker->latitude() . ', ' . $this->faker->longitude(),
            'description' => $this->faker->sentence(10),
            'vehicle_condition_photo_path' => 'service_reports/vehicle_' . $this->faker->uuid() . '.jpg',
            'receipt_photo_path' => 'service_reports/receipt_' . $this->faker->uuid() . '.jpg',
            'status' => $this->faker->randomElement($statuses),
            'admin_notes' => $this->faker->optional()->sentence(),
            'approved_by_admin_id' => null,
            'approved_at_admin' => null,
            'admin_signature_path' => null,
            'admin_signer_name' => null,
            'admin_signer_role' => null,
            'finance_word_path' => null,
            'customer_word_path' => null,
            'customer_signed_document_path' => null,
            'customer_signature_path' => null,
            'customer_signer_name' => null,
            'customer_signer_role' => null,
            'approved_by_customer_id' => null,
            'approved_at_customer' => null,
            'rejected_reason' => null,
        ];
    }

    public function pending(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'pending',
        ]);
    }

    public function approvedByAdmin(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'approved_admin',
            'approved_by_admin_id' => 1,
            'approved_at_admin' => Carbon::now()->subHours(2),
        ]);
    }

    public function approvedByCustomer(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'approved_customer',
            'approved_by_customer_id' => 1,
            'approved_at_customer' => Carbon::now(),
        ]);
    }

    public function rejected(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'rejected',
            'rejected_reason' => $this->faker->sentence(),
        ]);
    }
}
