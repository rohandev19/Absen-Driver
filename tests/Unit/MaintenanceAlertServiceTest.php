<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Models\Vehicle;
use App\Models\VehicleComponent;
use App\Models\MaintenanceAlert;
use App\Services\MaintenanceAlertService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Carbon\Carbon;

class MaintenanceAlertServiceTest extends TestCase
{
    use RefreshDatabase;

    protected MaintenanceAlertService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = app(MaintenanceAlertService::class);
    }

    /** @test */
    public function it_generates_warning_alert_for_component_near_replacement()
    {
        $vehicle = Vehicle::factory()->create(['current_km' => 49200]);

        $component = VehicleComponent::factory()->create([
            'vehicle_id' => $vehicle->id,
            'component_name' => 'Oli Mesin',
            'replacement_interval_km' => 5000,
            'last_replacement_km' => 45000,
            'warning_threshold_km' => 1000,
            'critical_threshold_km' => 500,
            'replacement_interval_days' => null,
            'last_replacement_date' => null,
            'next_replacement_date' => null,
        ]);

        $alerts = $this->service->generateAlertsForVehicle($vehicle);

        $this->assertCount(1, $alerts);
        $this->assertEquals('warning', $alerts[0]['alert_type']);
    }

    /** @test */
    public function it_generates_critical_alert_for_component_very_close_to_replacement()
    {
        $vehicle = Vehicle::factory()->create(['current_km' => 49900]);

        $component = VehicleComponent::factory()->create([
            'vehicle_id' => $vehicle->id,
            'component_name' => 'Oli Mesin',
            'replacement_interval_km' => 5000,
            'last_replacement_km' => 45000,
            'warning_threshold_km' => 1000,
            'critical_threshold_km' => 500,
            'replacement_interval_days' => null,
            'last_replacement_date' => null,
            'next_replacement_date' => null,
        ]);

        $alerts = $this->service->generateAlertsForVehicle($vehicle);

        $this->assertCount(1, $alerts);
        $this->assertEquals('critical', $alerts[0]['alert_type']);
    }

    /** @test */
    public function it_generates_overdue_alert_for_component_past_replacement()
    {
        $vehicle = Vehicle::factory()->create(['current_km' => 51000]);

        $component = VehicleComponent::factory()->create([
            'vehicle_id' => $vehicle->id,
            'component_name' => 'Oli Mesin',
            'replacement_interval_km' => 5000,
            'last_replacement_km' => 45000,
            'warning_threshold_km' => 1000,
            'critical_threshold_km' => 500,
            'replacement_interval_days' => null,
            'last_replacement_date' => null,
            'next_replacement_date' => null,
        ]);

        $alerts = $this->service->generateAlertsForVehicle($vehicle);

        $this->assertCount(1, $alerts);
        $this->assertEquals('overdue', $alerts[0]['alert_type']);
    }

    /** @test */
    public function it_generates_date_based_alerts()
    {
        $vehicle = Vehicle::factory()->create(['current_km' => 45000]);

        $component = VehicleComponent::factory()->create([
            'vehicle_id' => $vehicle->id,
            'component_name' => 'Oli Mesin',
            'replacement_interval_km' => 10000,
            'last_replacement_km' => 40000,
            'next_replacement_date' => Carbon::now()->addDays(2), // 2 days remaining
            'warning_threshold_km' => 1000,
            'critical_threshold_km' => 500,
            'replacement_interval_days' => null,
            'last_replacement_date' => null,
        ]);

        $alerts = $this->service->generateAlertsForVehicle($vehicle);

        $this->assertCount(1, $alerts);
        $this->assertEquals('critical', $alerts[0]['alert_type']);
    }

    /** @test */
    public function it_does_not_generate_duplicate_alerts()
    {
        $vehicle = Vehicle::factory()->create(['current_km' => 49900]);

        $component = VehicleComponent::factory()->create([
            'vehicle_id' => $vehicle->id,
            'replacement_interval_km' => 5000,
            'last_replacement_km' => 45000,
            'warning_threshold_km' => 1000,
            'critical_threshold_km' => 500,
        ]);

        // Generate alerts first time
        $alerts1 = $this->service->generateAlertsForVehicle($vehicle);
        $this->assertCount(1, $alerts1);

        // Generate alerts second time
        $alerts2 = $this->service->generateAlertsForVehicle($vehicle);
        $this->assertCount(0, $alerts2); // Should not create duplicate
    }

    /** @test */
    public function it_generates_alerts_for_all_vehicles()
    {
        $vehicle1 = Vehicle::factory()->create(['current_km' => 49900]);
        VehicleComponent::factory()->create([
            'vehicle_id' => $vehicle1->id,
            'replacement_interval_km' => 5000,
            'last_replacement_km' => 45000,
            'critical_threshold_km' => 500,
        ]);

        $vehicle2 = Vehicle::factory()->create(['current_km' => 51000]);
        VehicleComponent::factory()->create([
            'vehicle_id' => $vehicle2->id,
            'replacement_interval_km' => 5000,
            'last_replacement_km' => 45000,
            'critical_threshold_km' => 500,
        ]);

        $stats = $this->service->generateAlertsForAllVehicles();

        $this->assertEquals(2, $stats['total_vehicles']);
        $this->assertEquals(2, $stats['alerts_created']);
        $this->assertGreaterThan(0, $stats['critical'] + $stats['overdue']);
    }

    /** @test */
    public function it_resolves_component_alerts_after_maintenance()
    {
        $vehicle = Vehicle::factory()->create(['current_km' => 51000]);

        $component = VehicleComponent::factory()->create([
            'vehicle_id' => $vehicle->id,
            'replacement_interval_km' => 5000,
            'last_replacement_km' => 45000,
        ]);

        // Create active alert
        MaintenanceAlert::factory()->create([
            'vehicle_id' => $vehicle->id,
            'component_id' => $component->id,
            'status' => 'active',
        ]);

        $resolved = $this->service->resolveComponentAlerts($component);

        $this->assertEquals(1, $resolved);
        $this->assertDatabaseHas('maintenance_alerts', [
            'component_id' => $component->id,
            'status' => 'resolved',
        ]);
    }

    /** @test */
    public function it_provides_correct_alert_priority()
    {
        $overduePriority = $this->service->getAlertPriority('overdue');
        $this->assertEquals('critical', $overduePriority['level']);
        $this->assertContains('email', $overduePriority['channels']);
        $this->assertContains('sms', $overduePriority['channels']);

        $criticalPriority = $this->service->getAlertPriority('critical');
        $this->assertEquals('high', $criticalPriority['level']);

        $warningPriority = $this->service->getAlertPriority('warning');
        $this->assertEquals('medium', $warningPriority['level']);
    }

    /** @test */
    public function it_generates_active_alerts_summary()
    {
        $vehicle1 = Vehicle::factory()->create();
        $vehicle2 = Vehicle::factory()->create();

        MaintenanceAlert::factory()->create([
            'vehicle_id' => $vehicle1->id,
            'alert_type' => 'critical',
            'status' => 'active',
        ]);

        MaintenanceAlert::factory()->create([
            'vehicle_id' => $vehicle2->id,
            'alert_type' => 'overdue',
            'status' => 'active',
        ]);

        MaintenanceAlert::factory()->create([
            'vehicle_id' => $vehicle1->id,
            'alert_type' => 'warning',
            'status' => 'active',
        ]);

        $summary = $this->service->getActiveAlertsSummary();

        $this->assertEquals(3, $summary['total']);
        $this->assertEquals(1, $summary['by_type']['critical']);
        $this->assertEquals(1, $summary['by_type']['overdue']);
        $this->assertEquals(1, $summary['by_type']['warning']);
    }

    /** @test */
    public function it_does_not_generate_alert_for_healthy_component()
    {
        $vehicle = Vehicle::factory()->create(['current_km' => 47000]);

        $component = VehicleComponent::factory()->create([
            'vehicle_id' => $vehicle->id,
            'replacement_interval_km' => 5000,
            'last_replacement_km' => 45000,
            'warning_threshold_km' => 1000,
            'critical_threshold_km' => 500,
            'replacement_interval_days' => null,
            'last_replacement_date' => null,
            'next_replacement_date' => null,
        ]);

        // 50000 - 47000 = 3000 KM remaining (healthy)
        $alerts = $this->service->generateAlertsForVehicle($vehicle);

        $this->assertCount(0, $alerts);
    }
}
