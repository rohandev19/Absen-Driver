<?php

namespace Tests\Feature\Api;

use Tests\TestCase;
use App\Models\Driver;
use App\Models\Vehicle;
use App\Models\Attendance;
use App\Models\ServiceReport;
use App\Models\Customer;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;

class ServiceReportTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('public');
    }

    /** @test */
    public function driver_can_submit_service_report()
    {
        $customer = Customer::factory()->create();
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
            ->assertJson(['status' => 'success']);

        $this->assertDatabaseHas('service_reports', [
            'driver_id' => $driver->id,
            'vehicle_id' => $vehicle->id,
            'description' => 'Mesin overheat, perlu pengecekan radiator',
            'status' => ServiceReport::STATUS_PENDING,
        ]);
    }

    /** @test */
    public function driver_cannot_submit_service_report_without_being_on_duty()
    {
        $driver = Driver::factory()->create();

        $response = $this->actingAs($driver, 'sanctum')
            ->postJson('/api/submit-service-report', [
                'gps_location' => '-6.200000, 106.816666',
                'timestamp' => Carbon::now()->format('Y-m-d H:i:s'),
                'description' => 'Test',
                'vehicle_condition_photo' => UploadedFile::fake()->image('vehicle.jpg'),
                'receipt_photo' => UploadedFile::fake()->image('receipt.jpg'),
            ]);

        $response->assertStatus(404);
    }

    /** @test */
    public function service_report_validates_required_fields()
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
                // Missing required fields
            ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['gps_location', 'timestamp', 'description']);
    }

    /** @test */
    public function service_report_validates_image_files()
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
                'description' => 'Test',
                'vehicle_condition_photo' => UploadedFile::fake()->create('document.pdf'),
                'receipt_photo' => UploadedFile::fake()->image('receipt.jpg'),
            ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors('vehicle_condition_photo');
    }

    /** @test */
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

    /** @test */
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
