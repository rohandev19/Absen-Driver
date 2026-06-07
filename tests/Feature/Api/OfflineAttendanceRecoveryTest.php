<?php

namespace Tests\Feature\Api;

use Tests\TestCase;
use App\Models\User;
use App\Models\Vehicle;
use App\Models\Attendance;
use App\Models\Driver;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;

class OfflineAttendanceRecoveryTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('public');
    }

    private function createDriver($overrides = [])
    {
        $project = \App\Models\Project::firstOrCreate(
            ['id' => 1],
            ['name' => 'Test Project', 'client_name' => 'Test Client', 'location' => 'Jakarta']
        );

        return Driver::create(array_merge([
            'full_name' => 'Test Driver',
            'driver_id_nik' => 'DRV' . rand(1000, 9999),
            'nik_ktp' => '1234567890123456',
            'password' => Hash::make('password'),
            'project_id' => $project->id,
            'is_on_duty' => false,
        ], $overrides));
    }

    private function createVehicle($overrides = [])
    {
        return Vehicle::create(array_merge([
            'plate_number' => 'B ' . rand(1000, 9999) . ' XYZ',
            'last_odometer' => 50000,
        ], $overrides));
    }

    private function createAttendance($overrides = [])
    {
        return Attendance::create(array_merge([
            'driver_id' => 1,
            'vehicle_id' => 1,
            'time_in' => Carbon::now()->subHours(8),
            'time_out' => null,
            'speedo_awal' => 50000,
            'gps_location_in' => '-6.200000,106.816666',
            'selfie_photo_path' => 'dummy/selfie.jpg',
            'speedo_photo_awal_path' => 'dummy/speedo.jpg',
        ], $overrides));
    }

    /** @test */
    public function driver_can_get_duty_status()
    {
        $driverOnDuty = $this->createDriver(['is_on_duty' => true]);
        $vehicle = $this->createVehicle();
        
        $this->createAttendance([
            'driver_id' => $driverOnDuty->id,
            'vehicle_id' => $vehicle->id,
        ]);

        $responseOnDuty = $this->actingAs($driverOnDuty, 'sanctum')
            ->getJson('/api/attendance/duty-status');
        
        $responseOnDuty->assertStatus(200)
            ->assertJson([
                'status' => 'success',
                'is_on_duty' => true,
            ]);

        $driverOffDuty = $this->createDriver(['is_on_duty' => false]);
        
        $responseOffDuty = $this->actingAs($driverOffDuty, 'sanctum')
            ->getJson('/api/attendance/duty-status');
        
        $responseOffDuty->assertStatus(200)
            ->assertJson([
                'status' => 'success',
                'is_on_duty' => false,
            ]);
    }

    /** @test */
    public function driver_can_clock_out_offline()
    {
        $driver = $this->createDriver(['is_on_duty' => true]);
        $vehicle = $this->createVehicle();
        
        $attendance = $this->createAttendance([
            'driver_id' => $driver->id,
            'vehicle_id' => $vehicle->id,
        ]);

        $offlineTimestamp = Carbon::now()->subHours(2)->format('Y-m-d H:i:s');
        $offlineEntryId = 'test-uuid-12345';

        $response = $this->actingAs($driver, 'sanctum')
            ->postJson('/api/clock-out-offline', [
                'offline_entry_id' => $offlineEntryId,
                'device_timestamp' => $offlineTimestamp,
                'is_offline_recovery' => 'true',
                'speedometer_manual_akhir' => 60000,
                'catatan' => 'Offline test',
                'check_ban' => 'Aman',
                'check_lampu' => 'Aman',
                'check_rem' => 'Aman',
                'speedometer_photo_akhir' => UploadedFile::fake()->image('speedo_end.jpg'),
            ]);

        $response->assertStatus(200)->assertJson(['status' => 'success']);

        $attendance->refresh();
        $this->assertNotNull($attendance->time_out);
        $this->assertEquals($offlineTimestamp, $attendance->time_out);
        $this->assertEquals(60000, $attendance->speedo_akhir);
        $this->assertEquals(1, $attendance->is_offline_recovery);
        $this->assertNotNull($attendance->recovery_timestamp);
    }

    /** @test */
    public function offline_clock_out_is_idempotent()
    {
        $driver = $this->createDriver(['is_on_duty' => true]);
        $vehicle = $this->createVehicle();
        
        $this->createAttendance([
            'driver_id' => $driver->id,
            'vehicle_id' => $vehicle->id,
        ]);

        $offlineTimestamp = Carbon::now()->subHour()->format('Y-m-d H:i:s');
        $offlineEntryId = 'idempotent-uuid-5678';
        
        $payload = [
            'offline_entry_id' => $offlineEntryId,
            'device_timestamp' => $offlineTimestamp,
            'is_offline_recovery' => 'true',
            'speedometer_manual_akhir' => 65000,
            'catatan' => 'Idempotent test',
            'check_ban' => 'Aman',
            'check_lampu' => 'Aman',
            'check_rem' => 'Aman',
            'speedometer_photo_akhir' => UploadedFile::fake()->image('speedo_end.jpg'),
        ];

        // First call should succeed
        $response1 = $this->actingAs($driver, 'sanctum')
            ->postJson('/api/clock-out-offline', $payload);
        
        $response1->assertStatus(200);

        // Second call with same payload and UUID should return 200 (idempotent success)
        $response2 = $this->actingAs($driver, 'sanctum')
            ->postJson('/api/clock-out-offline', $payload);
        
        $response2->assertStatus(200)
            ->assertJson([
                'status' => 'success',
                'message' => 'Data sudah tersimpan sebelumnya (idempotent).'
            ]);
    }

    /** @test */
    public function late_submission_is_flagged()
    {
        $driver = $this->createDriver(['is_on_duty' => true]);
        $vehicle = $this->createVehicle();
        
        $attendance = $this->createAttendance([
            'driver_id' => $driver->id,
            'vehicle_id' => $vehicle->id,
        ]);

        // Submitted 25 hours ago, should trigger late_submission = true
        $offlineTimestamp = Carbon::now()->subHours(25)->format('Y-m-d H:i:s');
        $offlineEntryId = 'late-uuid-999';

        $response = $this->actingAs($driver, 'sanctum')
            ->postJson('/api/clock-out-offline', [
                'offline_entry_id' => $offlineEntryId,
                'device_timestamp' => $offlineTimestamp,
                'is_offline_recovery' => 'true',
                'speedometer_manual_akhir' => 70000,
                'catatan' => 'Late test',
                'check_ban' => 'Aman',
                'check_lampu' => 'Aman',
                'check_rem' => 'Aman',
                'speedometer_photo_akhir' => UploadedFile::fake()->image('speedo_end.jpg'),
            ]);

        $attendance->refresh();
        $this->assertTrue($attendance->is_late_submission);
    }
}
