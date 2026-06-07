<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Models\Vehicle;
use App\Models\VehicleComponent;
use App\Models\Attendance;
use App\Models\MaintenanceSchedule;
use App\Models\Driver;
use App\Services\VehicleHealthService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Carbon\Carbon;

class VehicleHealthServiceTest extends TestCase
{
    use RefreshDatabase;

    protected VehicleHealthService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = app(VehicleHealthService::class);
    }

    /** @test */
    public function it_calculates_health_score_correctly()
    {
        $vehicle = Vehicle::factory()->create(['current_km' => 50000]);

        // Add healthy components
        VehicleComponent::factory()->create([
            'vehicle_id' => $vehicle->id,
            'replacement_interval_km' => 5000,
            'last_replacement_km' => 48000,
            'warning_threshold_km' => 1000,
            'critical_threshold_km' => 500,
        ]);

        $score = $this->service->calculateHealthScore($vehicle);

        $this->assertIsFloat($score);
        $this->assertGreaterThanOrEqual(0, $score);
        $this->assertLessThanOrEqual(100, $score);
    }

    /** @test */
    public function healthy_vehicle_has_high_score()
    {
        $vehicle = Vehicle::factory()->create([
            'current_km' => 50000,
            'tahun_pembuatan' => Carbon::now()->year,
        ]);

        // Add healthy component (9000 KM remaining)
        VehicleComponent::factory()->create([
            'vehicle_id' => $vehicle->id,
            'replacement_interval_km' => 10000,
            'last_replacement_km' => 49000,
            'warning_threshold_km' => 1000,
            'critical_threshold_km' => 500,
        ]);

        // Add recent good daily checks
        Attendance::factory()->count(5)->create([
            'vehicle_id' => $vehicle->id,
            'time_in' => Carbon::now()->subDays(rand(1, 10)),
            'check_ban' => 'Baik',
            'check_lampu' => 'Baik',
            'check_rem' => 'Baik',
        ]);

        $score = $this->service->calculateHealthScore($vehicle);

        $this->assertGreaterThan(70, $score);
    }

    /** @test */
    public function critical_component_lowers_health_score()
    {
        $vehicle = Vehicle::factory()->create([
            'current_km' => 49900,
            'tahun_pembuatan' => Carbon::now()->year - 10,
        ]);

        // Add critical component (100 KM remaining, below 500 critical threshold)
        VehicleComponent::factory()->create([
            'vehicle_id' => $vehicle->id,
            'replacement_interval_km' => 5000,
            'last_replacement_km' => 45000,
            'warning_threshold_km' => 1000,
            'critical_threshold_km' => 500,
            'replacement_interval_days' => null,
            'last_replacement_date' => null,
            'next_replacement_date' => null,
        ]);

        $driver = Driver::factory()->create();

        // Add bad daily checks to lower the daily check score
        Attendance::create([
            'driver_id' => $driver->id,
            'vehicle_id' => $vehicle->id,
            'time_in' => Carbon::now(),
            'gps_location_in' => '-6.200000, 106.816666',
            'selfie_photo_path' => 'photos/test_selfie.jpg',
            'speedo_photo_awal_path' => 'photos/test_speedo.jpg',
            'speedo_awal' => 49900,
            'check_ban' => 'Bermasalah',
            'check_lampu' => 'Bermasalah',
            'check_rem' => 'Bermasalah',
        ]);

        $score = $this->service->calculateHealthScore($vehicle);

        $this->assertLessThan(50, $score);
    }

    /** @test */
    public function overdue_component_results_in_very_low_score()
    {
        $vehicle = Vehicle::factory()->create([
            'current_km' => 51000,
            'tahun_pembuatan' => Carbon::now()->year - 10,
        ]);

        // Add overdue component
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

        $driver = Driver::factory()->create();

        // Add bad daily checks to lower the daily check score
        Attendance::create([
            'driver_id' => $driver->id,
            'vehicle_id' => $vehicle->id,
            'time_in' => Carbon::now(),
            'gps_location_in' => '-6.200000, 106.816666',
            'selfie_photo_path' => 'photos/test_selfie.jpg',
            'speedo_photo_awal_path' => 'photos/test_speedo.jpg',
            'speedo_awal' => 51000,
            'check_ban' => 'Bermasalah',
            'check_lampu' => 'Bermasalah',
            'check_rem' => 'Bermasalah',
        ]);

        // Add overdue maintenance schedules to lower maintenance compliance
        MaintenanceSchedule::create([
            'vehicle_id' => $vehicle->id,
            'component_id' => $component->id,
            'status' => 'pending',
            'scheduled_date' => Carbon::now()->subDays(30),
            'type' => 'preventive',
            'priority' => 'medium',
        ]);

        $score = $this->service->calculateHealthScore($vehicle);

        $this->assertLessThan(30, $score);
    }

    /** @test */
    public function bad_daily_checks_lower_health_score()
    {
        $vehicle = Vehicle::factory()->create(['current_km' => 50000]);

        // Add healthy component
        VehicleComponent::factory()->create([
            'vehicle_id' => $vehicle->id,
            'replacement_interval_km' => 5000,
            'last_replacement_km' => 47000,
            'warning_threshold_km' => 1000,
            'critical_threshold_km' => 500,
        ]);

        // Add recent bad daily checks
        Attendance::factory()->count(5)->create([
            'vehicle_id' => $vehicle->id,
            'time_in' => Carbon::now()->subDays(rand(1, 10)),
            'check_ban' => 'Bermasalah',
            'check_lampu' => 'Bermasalah',
            'check_rem' => 'Bermasalah',
        ]);

        $score = $this->service->calculateHealthScore($vehicle);

        $this->assertLessThan(70, $score);
    }

    /** @test */
    public function it_returns_correct_health_status_labels()
    {
        $excellentStatus = $this->service->getHealthStatus(95);
        $this->assertEquals('Sangat Baik', $excellentStatus['label']);
        $this->assertEquals('green', $excellentStatus['color']);

        $goodStatus = $this->service->getHealthStatus(80);
        $this->assertEquals('Baik', $goodStatus['label']);

        $fairStatus = $this->service->getHealthStatus(65);
        $this->assertEquals('Cukup', $fairStatus['label']);
        $this->assertEquals('yellow', $fairStatus['color']);

        $poorStatus = $this->service->getHealthStatus(45);
        $this->assertEquals('Buruk', $poorStatus['label']);
        $this->assertEquals('orange', $poorStatus['color']);

        $criticalStatus = $this->service->getHealthStatus(25);
        $this->assertEquals('Kritis', $criticalStatus['label']);
        $this->assertEquals('red', $criticalStatus['color']);
    }

    /** @test */
    public function it_generates_detailed_health_report()
    {
        $vehicle = Vehicle::factory()->create(['current_km' => 50000]);

        VehicleComponent::factory()->create([
            'vehicle_id' => $vehicle->id,
            'component_name' => 'Oli Mesin',
            'replacement_interval_km' => 5000,
            'last_replacement_km' => 49500,
            'warning_threshold_km' => 1000,
            'critical_threshold_km' => 500,
        ]);

        $report = $this->service->getHealthReport($vehicle);

        $this->assertArrayHasKey('vehicle_id', $report);
        $this->assertArrayHasKey('plate_number', $report);
        $this->assertArrayHasKey('health_score', $report);
        $this->assertArrayHasKey('status', $report);
        $this->assertArrayHasKey('breakdown', $report);
        $this->assertArrayHasKey('components_needing_attention', $report);
        $this->assertArrayHasKey('active_alerts', $report);
        $this->assertArrayHasKey('upcoming_maintenance', $report);
    }

    /** @test */
    public function vehicle_with_no_components_has_default_healthy_score()
    {
        $vehicle = Vehicle::factory()->create(['current_km' => 50000]);

        $score = $this->service->calculateHealthScore($vehicle);

        // Should have decent score since no components are tracked
        $this->assertGreaterThan(50, $score);
    }

    /** @test */
    public function maintenance_compliance_affects_health_score()
    {
        $vehicle = Vehicle::factory()->create([
            'current_km' => 50000,
            'tahun_pembuatan' => Carbon::now()->year,
        ]);

        VehicleComponent::factory()->create([
            'vehicle_id' => $vehicle->id,
            'replacement_interval_km' => 5000,
            'last_replacement_km' => 47000,
            'warning_threshold_km' => 1000,
            'critical_threshold_km' => 500,
            'replacement_interval_days' => null,
            'last_replacement_date' => null,
            'next_replacement_date' => null,
        ]);

        // Add completed maintenance schedules (good compliance)
        MaintenanceSchedule::factory()->count(5)->create([
            'vehicle_id' => $vehicle->id,
            'status' => 'completed',
            'scheduled_date' => Carbon::now()->subDays(30),
            'completed_at' => Carbon::now()->subDays(31),
        ]);

        $scoreWithGoodCompliance = $this->service->calculateHealthScore($vehicle);

        // Create new vehicle with poor compliance
        $vehicle2 = Vehicle::factory()->create([
            'current_km' => 50000,
            'tahun_pembuatan' => Carbon::now()->year,
        ]);

        VehicleComponent::factory()->create([
            'vehicle_id' => $vehicle2->id,
            'replacement_interval_km' => 5000,
            'last_replacement_km' => 47000,
            'warning_threshold_km' => 1000,
            'critical_threshold_km' => 500,
            'replacement_interval_days' => null,
            'last_replacement_date' => null,
            'next_replacement_date' => null,
        ]);

        // Add overdue maintenance schedules (poor compliance)
        MaintenanceSchedule::factory()->count(5)->create([
            'vehicle_id' => $vehicle2->id,
            'status' => 'pending',
            'scheduled_date' => Carbon::now()->subDays(30),
        ]);

        $scoreWithPoorCompliance = $this->service->calculateHealthScore($vehicle2);

        $this->assertGreaterThan($scoreWithPoorCompliance, $scoreWithGoodCompliance);
    }
}
