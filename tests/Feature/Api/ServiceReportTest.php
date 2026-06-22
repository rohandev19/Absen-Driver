<?php

namespace Tests\Feature\Api;

use App\Models\Attendance;
use App\Models\Driver;
use App\Models\ServiceReport;
use App\Models\Vehicle;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class ServiceReportTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('public');
    }

    #[Test]
    public function legacy_service_report_endpoint_still_accepts_completed_service_payload()
    {
        $driver = Driver::factory()->create();
        $vehicle = Vehicle::factory()->create();

        Attendance::factory()->create([
            'driver_id' => $driver->id,
            'vehicle_id' => $vehicle->id,
            'time_out' => null,
        ]);

        $response = $this->actingAs($driver, 'sanctum')
            ->postJson('/api/submit-service-report', [
                'gps_location' => '-6.200000, 106.816666',
                'timestamp' => Carbon::now()->format('Y-m-d H:i:s'),
                'description' => 'Mesin overheat, perlu pengecekan radiator',
                'vehicle_condition_photo' => UploadedFile::fake()->image('vehicle.jpg'),
                'receipt_photo' => UploadedFile::fake()->image('receipt.jpg'),
            ]);

        $response->assertStatus(200)
            ->assertJsonPath('status', 'success')
            ->assertJsonPath('data.status', ServiceReport::STATUS_PENDING);

        $this->assertDatabaseHas('service_reports', [
            'driver_id' => $driver->id,
            'vehicle_id' => $vehicle->id,
            'description' => 'Mesin overheat, perlu pengecekan radiator',
            'status' => ServiceReport::STATUS_PENDING,
            'report_source' => 'driver_service_completion',
        ]);
    }

    #[Test]
    public function driver_can_submit_vehicle_damage_report_without_active_attendance_or_receipt()
    {
        $driver = Driver::factory()->create();
        $vehicle = Vehicle::factory()->create([
            'plate_number' => 'B 1234 ABC',
            'status' => 'Aktif',
        ]);

        $response = $this->actingAs($driver, 'sanctum')
            ->postJson('/api/submit-vehicle-damage-report', [
                'plate_number' => 'b   1234   abc',
                'manual_location' => 'Bundaran KSU',
                'timestamp' => Carbon::now()->format('Y-m-d H:i:s'),
                'description' => 'Rem bermasalah dan pedal harus dikocok beberapa kali.',
                'vehicle_condition_photo' => UploadedFile::fake()->image('damage.jpg'),
            ]);

        $response->assertStatus(200)
            ->assertJsonPath('status', 'success')
            ->assertJsonPath('data.status', ServiceReport::STATUS_WAITING_COMPLETION)
            ->assertJsonPath('data.report_source', 'driver_damage');

        $this->assertDatabaseHas('service_reports', [
            'driver_id' => $driver->id,
            'vehicle_id' => $vehicle->id,
            'gps_location' => 'Bundaran KSU',
            'location_source' => 'manual',
            'status' => ServiceReport::STATUS_WAITING_COMPLETION,
            'receipt_photo_path' => null,
        ]);

        $this->assertEquals('Rusak', $vehicle->fresh()->status);
    }

    #[Test]
    public function driver_can_submit_service_completion_without_gps()
    {
        $driver = Driver::factory()->create();
        $vehicle = Vehicle::factory()->create(['status' => 'Rusak']);
        $report = ServiceReport::factory()->create([
            'driver_id' => $driver->id,
            'vehicle_id' => $vehicle->id,
            'status' => ServiceReport::STATUS_WAITING_COMPLETION,
            'receipt_photo_path' => null,
        ]);

        $response = $this->actingAs($driver, 'sanctum')
            ->postJson("/api/service-reports/{$report->id}/complete", [
                'timestamp' => Carbon::now()->format('Y-m-d H:i:s'),
                'service_action' => 'Kampas rem dicek dan minyak rem ditambah.',
                'unit_status_after_service' => 'Aman digunakan',
                'after_service_photo' => UploadedFile::fake()->image('after.jpg'),
                'receipt_photo' => UploadedFile::fake()->image('receipt.jpg'),
            ]);

        $response->assertStatus(200)
            ->assertJsonPath('status', 'success')
            ->assertJsonPath('data.status', ServiceReport::STATUS_PENDING)
            ->assertJsonPath('data.unit_status_after_service', 'Aman digunakan');

        $report->refresh();
        $this->assertNotNull($report->after_service_photo_path);
        $this->assertNotNull($report->receipt_photo_path);
        $this->assertEquals($driver->id, $report->completed_by_driver_id);
        $this->assertEquals('Aktif', $vehicle->fresh()->status);
    }

    #[Test]
    public function vehicle_damage_report_validates_required_fields()
    {
        $driver = Driver::factory()->create();

        $response = $this->actingAs($driver, 'sanctum')
            ->postJson('/api/submit-vehicle-damage-report', []);

        $response->assertStatus(422)
            ->assertJsonValidationErrors([
                'plate_number',
                'gps_location',
                'timestamp',
                'description',
                'vehicle_condition_photo',
            ]);
    }

    #[Test]
    public function service_report_validates_image_files()
    {
        $driver = Driver::factory()->create();
        $vehicle = Vehicle::factory()->create();

        $response = $this->actingAs($driver, 'sanctum')
            ->postJson('/api/submit-vehicle-damage-report', [
                'plate_number' => $vehicle->plate_number,
                'gps_location' => '-6.200000, 106.816666',
                'timestamp' => Carbon::now()->format('Y-m-d H:i:s'),
                'description' => 'Rem bermasalah dan perlu dicek admin service.',
                'vehicle_condition_photo' => UploadedFile::fake()->create('document.pdf'),
            ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors('vehicle_condition_photo');
    }

    #[Test]
    public function driver_can_view_service_report_history()
    {
        $driver = Driver::factory()->create();
        $vehicle = Vehicle::factory()->create();

        ServiceReport::factory()->count(3)->create([
            'driver_id' => $driver->id,
            'vehicle_id' => $vehicle->id,
        ]);

        $response = $this->actingAs($driver, 'sanctum')
            ->getJson('/api/service-reports');

        $response->assertStatus(200)
            ->assertJsonCount(3, 'data');
    }

    #[Test]
    public function driver_can_view_service_report_detail()
    {
        $driver = Driver::factory()->create();
        $vehicle = Vehicle::factory()->create();

        $serviceReport = ServiceReport::factory()->create([
            'driver_id' => $driver->id,
            'vehicle_id' => $vehicle->id,
            'description' => 'Test service report',
        ]);

        $response = $this->actingAs($driver, 'sanctum')
            ->getJson('/api/service-reports/' . $serviceReport->id);

        $response->assertStatus(200)
            ->assertJsonPath('data.description', 'Test service report');
    }
}
