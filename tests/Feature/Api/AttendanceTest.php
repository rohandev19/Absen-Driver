<?php

namespace Tests\Feature\Api;

use Tests\TestCase;
use App\Models\Driver;
use App\Models\Vehicle;
use App\Models\Attendance;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;

class AttendanceTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('public');
    }

    /** @test */
    public function driver_can_check_in()
    {
        $driver = Driver::factory()->create();
        $vehicle = Vehicle::factory()->create(['plate_number' => 'B1234XYZ']);

        $response = $this->actingAs($driver, 'sanctum')
            ->postJson('/api/submit-attendance', [
                'plate_number' => 'B1234XYZ',
                'gps_location' => '-6.200000, 106.816666',
                'timestamp' => Carbon::now()->format('Y-m-d H:i:s'),
                'speedometer_manual' => 50000,
                'selfie_photo' => UploadedFile::fake()->image('selfie.jpg'),
                'speedometer_photo' => UploadedFile::fake()->image('speedo.jpg'),
                'car_condition_photo_1' => UploadedFile::fake()->image('condition1.jpg'),
                'car_condition_photo_2' => UploadedFile::fake()->image('condition2.jpg'),
            ]);

        $response->assertStatus(200)
            ->assertJson(['status' => 'success']);

        $this->assertDatabaseHas('attendances', [
            'driver_id' => $driver->id,
            'vehicle_id' => $vehicle->id,
            'speedo_awal' => 50000,
        ]);
    }

    /** @test */
    public function driver_can_check_in_with_manual_existing_vehicle()
    {
        $driver = Driver::factory()->create();
        $vehicle = Vehicle::factory()->create(['plate_number' => 'B9554UCY']);

        $response = $this->actingAs($driver, 'sanctum')
            ->postJson('/api/submit-attendance', [
                'plate_number' => 'B 9554 UCY',
                'vehicle_entry_method' => 'manual',
                'manual_vehicle_reason' => 'QR belum ditempel',
                'manual_vehicle_photo' => UploadedFile::fake()->image('unit.jpg'),
                'gps_location' => '-6.200000, 106.816666',
                'timestamp' => Carbon::now()->format('Y-m-d H:i:s'),
                'speedometer_manual' => 50000,
                'selfie_photo' => UploadedFile::fake()->image('selfie.jpg'),
                'speedometer_photo' => UploadedFile::fake()->image('speedo.jpg'),
            ]);

        $response->assertStatus(200)
            ->assertJsonPath('data.vehicle_id', $vehicle->id)
            ->assertJsonPath('data.vehicle_entry_method', 'manual')
            ->assertJsonPath('data.vehicle_verification_status', 'verified');

        $this->assertDatabaseHas('attendances', [
            'driver_id' => $driver->id,
            'vehicle_id' => $vehicle->id,
            'vehicle_entry_method' => 'manual',
            'manual_vehicle_plate' => 'B9554UCY',
            'manual_vehicle_reason' => 'QR belum ditempel',
            'vehicle_verification_status' => 'verified',
        ]);
    }

    /** @test */
    public function driver_can_check_in_with_manual_new_replacement_vehicle_pending_admin_verification()
    {
        $driver = Driver::factory()->create(['project_id' => null]);

        $response = $this->actingAs($driver, 'sanctum')
            ->postJson('/api/submit-attendance', [
                'plate_number' => 'B 9804 UCY',
                'vehicle_entry_method' => 'manual',
                'manual_vehicle_reason' => 'Unit pengganti dari luar',
                'manual_vehicle_photo' => UploadedFile::fake()->image('unit.jpg'),
                'gps_location' => '-6.200000, 106.816666',
                'timestamp' => Carbon::now()->format('Y-m-d H:i:s'),
                'speedometer_manual' => 60000,
                'selfie_photo' => UploadedFile::fake()->image('selfie.jpg'),
                'speedometer_photo' => UploadedFile::fake()->image('speedo.jpg'),
            ]);

        $response->assertStatus(200)
            ->assertJsonPath('data.plate_number', 'B9804UCY')
            ->assertJsonPath('data.vehicle_entry_method', 'manual')
            ->assertJsonPath('data.vehicle_verification_status', 'pending');

        $this->assertDatabaseHas('vehicles', [
            'plate_number' => 'B9804UCY',
            'status' => 'Pending Verifikasi',
            'is_temporary' => true,
            'verification_status' => 'pending',
            'source' => 'driver_manual',
        ]);

        $vehicle = Vehicle::where('plate_number', 'B9804UCY')->firstOrFail();

        $this->assertDatabaseHas('attendances', [
            'driver_id' => $driver->id,
            'vehicle_id' => $vehicle->id,
            'vehicle_entry_method' => 'manual',
            'manual_vehicle_plate' => 'B9804UCY',
            'manual_vehicle_reason' => 'Unit pengganti dari luar',
            'vehicle_verification_status' => 'pending',
        ]);
    }

    /** @test */
    public function driver_cannot_check_in_with_vehicle_in_service_status()
    {
        $driver = Driver::factory()->create();
        Vehicle::factory()->create([
            'plate_number' => 'B1234XYZ',
            'status' => 'Servis',
        ]);

        $response = $this->actingAs($driver, 'sanctum')
            ->postJson('/api/submit-attendance', [
                'plate_number' => 'B1234XYZ',
                'gps_location' => '-6.200000, 106.816666',
                'timestamp' => Carbon::now()->format('Y-m-d H:i:s'),
                'speedometer_manual' => 50000,
                'selfie_photo' => UploadedFile::fake()->image('selfie.jpg'),
                'speedometer_photo' => UploadedFile::fake()->image('speedo.jpg'),
            ]);

        $response->assertStatus(409)
            ->assertJsonPath('status', 'error');
    }

    /** @test */
    public function driver_cannot_check_in_twice()
    {
        $driver = Driver::factory()->create();
        $vehicle = Vehicle::factory()->create(['plate_number' => 'B1234XYZ']);

        // First check-in
        Attendance::factory()->create([
            'driver_id' => $driver->id,
            'vehicle_id' => $vehicle->id,
            'time_out' => null, // Still on duty
        ]);

        // Try second check-in
        $response = $this->actingAs($driver, 'sanctum')
            ->postJson('/api/submit-attendance', [
                'plate_number' => 'B1234XYZ',
                'gps_location' => '-6.200000, 106.816666',
                'timestamp' => Carbon::now()->format('Y-m-d H:i:s'),
                'speedometer_manual' => 50000,
                'selfie_photo' => UploadedFile::fake()->image('selfie.jpg'),
                'speedometer_photo' => UploadedFile::fake()->image('speedo.jpg'),
            ]);

        $response->assertStatus(409); // Conflict
    }

    /** @test */
    public function driver_can_check_out()
    {
        $driver = Driver::factory()->create();
        $vehicle = Vehicle::factory()->create();
        $attendance = Attendance::factory()->create([
            'driver_id' => $driver->id,
            'vehicle_id' => $vehicle->id,
            'speedo_awal' => 50000,
            'time_out' => null,
        ]);

        $response = $this->actingAs($driver, 'sanctum')
            ->postJson('/api/submit-end-of-duty', [
                'timestamp' => Carbon::now()->format('Y-m-d H:i:s'),
                'speedometer_manual' => 50150,
                'speedometer_photo' => UploadedFile::fake()->image('speedo_end.jpg'),
                'catatan' => 'Perjalanan lancar',
                'check_ban' => 'Baik',
                'check_lampu' => 'Baik',
                'check_rem' => 'Baik',
            ]);

        $response->assertStatus(200)
            ->assertJson(['status' => 'success']);

        $attendance->refresh();
        $this->assertNotNull($attendance->time_out);
        $this->assertEquals(50150, $attendance->speedo_akhir);
    }

    /** @test */
    public function driver_cannot_check_out_without_check_in()
    {
        $driver = Driver::factory()->create();

        $response = $this->actingAs($driver, 'sanctum')
            ->postJson('/api/submit-end-of-duty', [
                'timestamp' => Carbon::now()->format('Y-m-d H:i:s'),
                'speedometer_manual' => 50150,
                'speedometer_photo' => UploadedFile::fake()->image('speedo_end.jpg'),
                'catatan' => 'Test',
                'check_ban' => 'Baik',
                'check_lampu' => 'Baik',
                'check_rem' => 'Baik',
            ]);

        $response->assertStatus(404);
    }

    /** @test */
    public function driver_can_submit_emergency_report()
    {
        $driver = Driver::factory()->create();
        $vehicle = Vehicle::factory()->create();
        Attendance::factory()->create([
            'driver_id' => $driver->id,
            'vehicle_id' => $vehicle->id,
            'time_out' => null,
        ]);

        $response = $this->actingAs($driver, 'sanctum')
            ->postJson('/api/submit-emergency-report', [
                'gps_location' => '-6.200000, 106.816666',
                'timestamp' => Carbon::now()->format('Y-m-d H:i:s'),
                'description' => 'Ban bocor di tol',
                'photo' => UploadedFile::fake()->image('emergency.jpg'),
            ]);

        $response->assertStatus(200)
            ->assertJson(['status' => 'success']);

        $this->assertDatabaseHas('emergency_reports', [
            'driver_id' => $driver->id,
            'description' => 'Ban bocor di tol',
        ]);
    }

    /** @test */
    public function check_in_validates_gps_format()
    {
        $driver = Driver::factory()->create();

        $response = $this->actingAs($driver, 'sanctum')
            ->postJson('/api/submit-attendance', [
                'plate_number' => 'B1234XYZ',
                'gps_location' => 'invalid-gps',
                'timestamp' => Carbon::now()->format('Y-m-d H:i:s'),
                'speedometer_manual' => 50000,
                'selfie_photo' => UploadedFile::fake()->image('selfie.jpg'),
                'speedometer_photo' => UploadedFile::fake()->image('speedo.jpg'),
            ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors('gps_location');
    }

    /** @test */
    public function check_in_validates_image_files()
    {
        $driver = Driver::factory()->create();

        $response = $this->actingAs($driver, 'sanctum')
            ->postJson('/api/submit-attendance', [
                'plate_number' => 'B1234XYZ',
                'gps_location' => '-6.200000, 106.816666',
                'timestamp' => Carbon::now()->format('Y-m-d H:i:s'),
                'speedometer_manual' => 50000,
                'selfie_photo' => UploadedFile::fake()->create('document.pdf'),
                'speedometer_photo' => UploadedFile::fake()->image('speedo.jpg'),
            ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors('selfie_photo');
    }

    /** @test */
    public function driver_can_check_status()
    {
        $driver = Driver::factory()->create();
        $vehicle = Vehicle::factory()->create();
        Attendance::factory()->create([
            'driver_id' => $driver->id,
            'vehicle_id' => $vehicle->id,
            'time_out' => null,
        ]);

        $response = $this->actingAs($driver, 'sanctum')
            ->getJson('/api/driver/status');

        $response->assertStatus(200)
            ->assertJson([
                'status' => 'success',
                'is_on_duty' => true,
            ]);
    }

    /** @test */
    public function driver_can_view_attendance_history()
    {
        $driver = Driver::factory()->create();
        $vehicle = Vehicle::factory()->create();
        Attendance::factory()->count(5)->create([
            'driver_id' => $driver->id,
            'vehicle_id' => $vehicle->id,
        ]);

        $response = $this->actingAs($driver, 'sanctum')
            ->getJson('/api/driver/history');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'status',
                'data' => [
                    '*' => ['id', 'time_in', 'time_out', 'vehicle'],
                ],
            ]);
    }

    /** @test */
    public function future_timestamp_is_rejected()
    {
        $driver = Driver::factory()->create();

        $response = $this->actingAs($driver, 'sanctum')
            ->postJson('/api/submit-attendance', [
                'plate_number' => 'B1234XYZ',
                'gps_location' => '-6.200000, 106.816666',
                'timestamp' => Carbon::now()->addHours(2)->format('Y-m-d H:i:s'),
                'speedometer_manual' => 50000,
                'selfie_photo' => UploadedFile::fake()->image('selfie.jpg'),
                'speedometer_photo' => UploadedFile::fake()->image('speedo.jpg'),
            ]);

        $response->assertStatus(422);
    }
}
