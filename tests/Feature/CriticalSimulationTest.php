<?php

namespace Tests\Feature;

use App\Models\Vehicle;
use App\Models\VehicleComponent;
use App\Services\VehicleHealthService;
use App\Services\MaintenanceAlertService;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class CriticalSimulationTest extends TestCase
{
    use DatabaseTransactions;

    /**
     * Test critical scenario for vehicle maintenance.
     */
    public function test_critical_maintenance_scenario()
    {
        // 1. Ambil atau buat kendaraan dengan komponen
        $vehicle = Vehicle::with('components')->first();
        
        if (!$vehicle || $vehicle->components->count() < 2) {
            $this->markTestSkipped('Tidak ada data kendaraan yang cukup untuk simulasi. Perlu setidaknya 1 kendaraan dengan komponen Engine Oil dan Brake Pads.');
        }

        $engineOil = $vehicle->components()->where('component_name', 'like', '%Oil%')->first();
        $brakePads = $vehicle->components()->where('component_name', 'like', '%Brake%')->first();

        if (!$engineOil || !$brakePads) {
            $this->markTestSkipped('Kendaraan pertama tidak memiliki komponen Engine Oil atau Brake Pads.');
        }

        // 2. Simulasi: Engine Oil hampir overdue
        $newLastKm = $vehicle->current_km - ($engineOil->replacement_interval_km - 100); 
        $engineOil->update(['last_replacement_km' => $newLastKm]);
        $engineOil->refresh();

        // 3. Simulasi: Brake Pads sudah overdue
        $brakePads->update(['last_replacement_km' => $vehicle->current_km - ($brakePads->replacement_interval_km + 1000)]);
        $brakePads->refresh();

        // 4. Generate alerts
        $alertService = new MaintenanceAlertService();
        $alerts = $alertService->generateAlertsForVehicle($vehicle->fresh());

        $this->assertNotEmpty($alerts, 'Alerts should be generated for overdue and critical components.');

        // 5. Calculate health score
        $healthService = new VehicleHealthService();
        $report = $healthService->getHealthReport($vehicle->fresh());

        $this->assertArrayHasKey('health_score', $report);
        $this->assertArrayHasKey('status', $report);

        // 6. Komponen yang perlu perhatian
        $needsAttention = $vehicle->fresh()->components()->needsMaintenance()->get();
        $this->assertGreaterThanOrEqual(2, $needsAttention->count());

        // 7. Check if we can auto-generate schedules logic
        $schedulesCreated = 0;
        foreach ($needsAttention as $comp) {
            $exists = DB::table('maintenance_schedules')
                ->where('vehicle_id', $vehicle->id)
                ->where('component_id', $comp->id)
                ->whereIn('status', ['pending', 'scheduled'])
                ->exists();
            
            if (!$exists) {
                DB::table('maintenance_schedules')->insert([
                    'vehicle_id' => $vehicle->id,
                    'component_id' => $comp->id,
                    'scheduled_date' => now()->addDays(1),
                    'scheduled_km' => $comp->next_replacement_km,
                    'type' => 'preventive',
                    'priority' => 'high',
                    'status' => 'pending',
                    'estimated_cost' => $comp->cost_per_replacement,
                    'notes' => "Auto-generated test",
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
                $schedulesCreated++;
            }
        }

        $this->assertGreaterThan(0, $schedulesCreated, 'Maintenance schedules should be generated.');
    }
}
