<?php

namespace Tests\Feature\Api;

use Tests\TestCase;
use App\Models\Driver;
use App\Models\Vehicle;
use App\Models\Attendance;
use App\Models\TransportCost;
use App\Models\Project;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;

class TransportCostTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('public');
    }

    /** @test */
    public function driver_can_check_if_can_create_trip_entry()
    {
        $driver = Driver::factory()->create();
        $vehicle = Vehicle::factory()->create();
        $attendance = Attendance::factory()->create([
            'driver_id' => $driver->id,
            'vehicle_id' => $vehicle->id,
            'time_in' => Carbon::today()->setHour(8),
            'time_out' => Carbon::today()->setHour(17),
            'speedo_awal' => 50000,
            'speedo_akhir' => 50150,
        ]);

        $response = $this->actingAs($driver, 'sanctum')
            ->getJson('/api/transport-costs/can-create?date=' . Carbon::today()->toDateString());

        $response->assertStatus(200)
            ->assertJson([
                'can_create' => true,
                'attendance_id' => $attendance->id,
            ]);
    }

    /** @test */
    public function driver_cannot_create_trip_entry_without_checkout()
    {
        $driver = Driver::factory()->create();
        $vehicle = Vehicle::factory()->create();
        Attendance::factory()->create([
            'driver_id' => $driver->id,
            'vehicle_id' => $vehicle->id,
            'time_in' => Carbon::today()->setHour(8),
            'time_out' => null, // No checkout
        ]);

        $response = $this->actingAs($driver, 'sanctum')
            ->getJson('/api/transport-costs/can-create?date=' . Carbon::today()->toDateString());

        $response->assertStatus(200)
            ->assertJson([
                'can_create' => false,
                'reason' => 'no_checkout',
            ]);
    }

    /** @test */
    public function driver_can_create_trip_entry()
    {
        $project = Project::factory()->create();
        $driver = Driver::factory()->create(['project_id' => $project->id]);
        $vehicle = Vehicle::factory()->create();
        $attendance = Attendance::factory()->create([
            'driver_id' => $driver->id,
            'vehicle_id' => $vehicle->id,
            'time_in' => Carbon::today()->setHour(8),
            'time_out' => Carbon::today()->setHour(17),
            'speedo_awal' => 50000,
            'speedo_akhir' => 50150,
        ]);

        $response = $this->actingAs($driver, 'sanctum')
            ->postJson('/api/transport-costs', [
                'trip_date' => Carbon::today()->toDateString(),
                'do_number' => 'DO-2024-001',
                'drop_point_count' => 3,
                'delivery_location' => 'Jakarta - Bandung',
                'gasoline_cost' => 200000,
                'toll_cost' => 50000,
                'parking_cost' => 10000,
                'gasoline_price_per_liter' => 10000,
                'delivery_start_time' => Carbon::today()->setHour(8)->toDateTimeString(),
                'delivery_end_time' => Carbon::today()->setHour(17)->toDateTimeString(),
                'gasoline_receipt_path' => UploadedFile::fake()->image('gasoline.jpg'),
                'toll_receipt_path' => UploadedFile::fake()->image('toll.jpg'),
                'parking_receipt_path' => UploadedFile::fake()->image('parking.jpg'),
            ]);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'status',
                'message',
                'data' => [
                    'id',
                    'fuel_efficiency_ratio',
                    'overtime_hours',
                    'overtime_payment',
                ],
            ]);

        $this->assertDatabaseHas('transport_costs', [
            'driver_id' => $driver->id,
            'do_number' => 'DO-2024-001',
            'gasoline_cost' => 200000,
        ]);
    }

    /** @test */
    public function driver_cannot_create_duplicate_trip_entry()
    {
        $project = Project::factory()->create();
        $driver = Driver::factory()->create(['project_id' => $project->id]);
        $vehicle = Vehicle::factory()->create();
        $attendance = Attendance::factory()->create([
            'driver_id' => $driver->id,
            'vehicle_id' => $vehicle->id,
            'time_in' => Carbon::today()->setHour(8),
            'time_out' => Carbon::today()->setHour(17),
            'speedo_awal' => 50000,
            'speedo_akhir' => 50150,
        ]);

        // Create first trip entry
        TransportCost::factory()->create([
            'driver_id' => $driver->id,
            'trip_date' => Carbon::today(),
        ]);

        // Try to create second trip entry for same date
        $response = $this->actingAs($driver, 'sanctum')
            ->postJson('/api/transport-costs', [
                'trip_date' => Carbon::today()->toDateString(),
                'do_number' => 'DO-2024-002',
                'drop_point_count' => 3,
                'delivery_location' => 'Jakarta - Bandung',
                'gasoline_cost' => 200000,
                'toll_cost' => 50000,
                'parking_cost' => 10000,
                'delivery_start_time' => Carbon::today()->setHour(8)->toDateTimeString(),
                'delivery_end_time' => Carbon::today()->setHour(17)->toDateTimeString(),
            ]);

        $response->assertStatus(422);
    }

    /** @test */
    public function driver_can_view_trip_entries()
    {
        $driver = Driver::factory()->create();
        for ($i = 0; $i < 5; $i++) {
            TransportCost::factory()->create([
                'driver_id' => $driver->id,
                'trip_date' => Carbon::today()->subDays($i),
            ]);
        }

        $response = $this->actingAs($driver, 'sanctum')
            ->getJson('/api/transport-costs');

        $response->assertStatus(200)
            ->assertJsonCount(5, 'data');
    }

    /** @test */
    public function driver_can_view_trip_entry_detail()
    {
        $driver = Driver::factory()->create();
        $tripEntry = TransportCost::factory()->create([
            'driver_id' => $driver->id,
            'do_number' => 'DO-2024-001',
        ]);

        $response = $this->actingAs($driver, 'sanctum')
            ->getJson('/api/transport-costs/' . $tripEntry->id);

        $response->assertStatus(200)
            ->assertJsonPath('data.do_number', 'DO-2024-001');
    }

    /** @test */
    public function fuel_efficiency_is_calculated_automatically()
    {
        $project = Project::factory()->create();
        $driver = Driver::factory()->create(['project_id' => $project->id]);
        $vehicle = Vehicle::factory()->create();
        $attendance = Attendance::factory()->create([
            'driver_id' => $driver->id,
            'vehicle_id' => $vehicle->id,
            'speedo_awal' => 50000,
            'speedo_akhir' => 50150, // 150 KM
        ]);

        $response = $this->actingAs($driver, 'sanctum')
            ->postJson('/api/transport-costs', [
                'trip_date' => Carbon::today()->toDateString(),
                'do_number' => 'DO-2024-001',
                'drop_point_count' => 3,
                'delivery_location' => 'Jakarta - Bandung',
                'gasoline_cost' => 150000, // 15 liters at 10k/liter
                'toll_cost' => 50000,
                'parking_cost' => 10000,
                'gasoline_price_per_liter' => 10000,
                'delivery_start_time' => Carbon::today()->setHour(8)->toDateTimeString(),
                'delivery_end_time' => Carbon::today()->setHour(17)->toDateTimeString(),
            ]);

        $response->assertStatus(201);

        $tripEntry = TransportCost::first();
        $this->assertEquals(15, $tripEntry->fuel_consumed); // 150000 / 10000
        $this->assertEquals(10, $tripEntry->fuel_efficiency_ratio); // 150 KM / 15 liters
    }

    /** @test */
    public function overtime_is_calculated_automatically()
    {
        $project = Project::factory()->create();
        $driver = Driver::factory()->create(['project_id' => $project->id]);
        $vehicle = Vehicle::factory()->create();
        $attendance = Attendance::factory()->create([
            'driver_id' => $driver->id,
            'vehicle_id' => $vehicle->id,
            'speedo_awal' => 50000,
            'speedo_akhir' => 50150,
        ]);

        $response = $this->actingAs($driver, 'sanctum')
            ->postJson('/api/transport-costs', [
                'trip_date' => Carbon::today()->toDateString(),
                'do_number' => 'DO-2024-001',
                'drop_point_count' => 3,
                'delivery_location' => 'Jakarta - Bandung',
                'gasoline_cost' => 150000,
                'toll_cost' => 50000,
                'parking_cost' => 10000,
                'delivery_start_time' => Carbon::today()->setHour(8)->toDateTimeString(),
                'delivery_end_time' => Carbon::today()->setHour(20)->toDateTimeString(), // 12 hours
            ]);

        $response->assertStatus(201);

        $tripEntry = TransportCost::first();
        $this->assertGreaterThan(0, $tripEntry->overtime_hours);
        $this->assertGreaterThan(0, $tripEntry->overtime_payment);
    }
}
