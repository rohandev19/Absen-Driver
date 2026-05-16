<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Vehicle;
use App\Models\VehicleComponent;
use Carbon\Carbon;

class VehicleComponentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $vehicles = Vehicle::all();

        if ($vehicles->isEmpty()) {
            $this->command->warn('No vehicles found. Please run VehicleSeeder first.');
            return;
        }

        $componentTemplates = [
            [
                'component_name' => 'Engine Oil',
                'category' => 'Fluids',
                'replacement_interval_km' => 5000,
                'replacement_interval_days' => 180,
                'cost_per_replacement' => 350000,
                'warning_threshold_km' => 500,
                'critical_threshold_km' => 100,
            ],
            [
                'component_name' => 'Oil Filter',
                'category' => 'Filters',
                'replacement_interval_km' => 5000,
                'replacement_interval_days' => 180,
                'cost_per_replacement' => 75000,
                'warning_threshold_km' => 500,
                'critical_threshold_km' => 100,
            ],
            [
                'component_name' => 'Air Filter',
                'category' => 'Filters',
                'replacement_interval_km' => 10000,
                'replacement_interval_days' => 365,
                'cost_per_replacement' => 150000,
                'warning_threshold_km' => 1000,
                'critical_threshold_km' => 200,
            ],
            [
                'component_name' => 'Brake Pads',
                'category' => 'Brakes',
                'replacement_interval_km' => 30000,
                'replacement_interval_days' => null,
                'cost_per_replacement' => 800000,
                'warning_threshold_km' => 3000,
                'critical_threshold_km' => 500,
            ],
            [
                'component_name' => 'Brake Fluid',
                'category' => 'Brakes',
                'replacement_interval_km' => 20000,
                'replacement_interval_days' => 730,
                'cost_per_replacement' => 150000,
                'warning_threshold_km' => 2000,
                'critical_threshold_km' => 500,
            ],
            [
                'component_name' => 'Coolant',
                'category' => 'Fluids',
                'replacement_interval_km' => 40000,
                'replacement_interval_days' => 730,
                'cost_per_replacement' => 250000,
                'warning_threshold_km' => 4000,
                'critical_threshold_km' => 1000,
            ],
            [
                'component_name' => 'Timing Belt',
                'category' => 'Belts & Hoses',
                'replacement_interval_km' => 80000,
                'replacement_interval_days' => 1825,
                'cost_per_replacement' => 2500000,
                'warning_threshold_km' => 5000,
                'critical_threshold_km' => 1000,
            ],
            [
                'component_name' => 'Battery',
                'category' => 'Battery',
                'replacement_interval_km' => null,
                'replacement_interval_days' => 1095,
                'cost_per_replacement' => 1200000,
                'warning_threshold_km' => 0,
                'critical_threshold_km' => 0,
            ],
        ];

        foreach ($vehicles as $vehicle) {
            $this->command->info("Creating components for vehicle: {$vehicle->plate_number}");

            foreach ($componentTemplates as $template) {
                // Randomize last replacement to create varied scenarios
                $lastReplacementKm = $vehicle->current_km - rand(1000, 4000);
                $lastReplacementDate = Carbon::now()->subDays(rand(30, 150));

                VehicleComponent::create([
                    'vehicle_id' => $vehicle->id,
                    'component_name' => $template['component_name'],
                    'category' => $template['category'],
                    'replacement_interval_km' => $template['replacement_interval_km'],
                    'replacement_interval_days' => $template['replacement_interval_days'],
                    'last_replacement_km' => $lastReplacementKm,
                    'last_replacement_date' => $lastReplacementDate,
                    'cost_per_replacement' => $template['cost_per_replacement'],
                    'warning_threshold_km' => $template['warning_threshold_km'],
                    'critical_threshold_km' => $template['critical_threshold_km'],
                ]);
            }
        }

        $this->command->info('✅ Vehicle components seeded successfully!');
    }
}
