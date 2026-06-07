<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Models\Attendance;
use App\Models\Driver;
use App\Models\Vehicle;
use App\Models\OfflineRecoveryLog;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Carbon\Carbon;

class AttendanceModelTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_has_offline_recovery_fillable_fields()
    {
        $driver = Driver::factory()->create();
        $vehicle = Vehicle::factory()->create();

        $attendance = Attendance::create([
            'driver_id' => $driver->id,
            'vehicle_id' => $vehicle->id,
            'time_in' => Carbon::now(),
            'speedo_awal' => 50000,
            'gps_location_in' => '-6.200000, 106.816666',
            'selfie_photo_path' => 'photos/test_selfie.jpg',
            'speedo_photo_awal_path' => 'photos/test_speedo.jpg',
            'is_offline_recovery' => true,
            'recovery_timestamp' => Carbon::now(),
            'offline_entry_id' => 'test-uuid-123',
            'is_late_submission' => false,
            'gps_location_out' => '-6.200000, 106.816666',
        ]);

        $this->assertNotNull($attendance->id);
        $this->assertTrue($attendance->is_offline_recovery);
        $this->assertNotNull($attendance->recovery_timestamp);
        $this->assertEquals('test-uuid-123', $attendance->offline_entry_id);
        $this->assertFalse($attendance->is_late_submission);
        $this->assertEquals('-6.200000, 106.816666', $attendance->gps_location_out);
    }

    /** @test */
    public function it_casts_is_offline_recovery_to_boolean()
    {
        $driver = Driver::factory()->create();
        $vehicle = Vehicle::factory()->create();

        $attendance = Attendance::create([
            'driver_id' => $driver->id,
            'vehicle_id' => $vehicle->id,
            'time_in' => Carbon::now(),
            'speedo_awal' => 50000,
            'gps_location_in' => '-6.200000, 106.816666',
            'selfie_photo_path' => 'photos/test_selfie.jpg',
            'speedo_photo_awal_path' => 'photos/test_speedo.jpg',
            'is_offline_recovery' => 1, // Integer
        ]);

        $this->assertIsBool($attendance->is_offline_recovery);
        $this->assertTrue($attendance->is_offline_recovery);
    }

    /** @test */
    public function it_casts_is_late_submission_to_boolean()
    {
        $driver = Driver::factory()->create();
        $vehicle = Vehicle::factory()->create();

        $attendance = Attendance::create([
            'driver_id' => $driver->id,
            'vehicle_id' => $vehicle->id,
            'time_in' => Carbon::now(),
            'speedo_awal' => 50000,
            'gps_location_in' => '-6.200000, 106.816666',
            'selfie_photo_path' => 'photos/test_selfie.jpg',
            'speedo_photo_awal_path' => 'photos/test_speedo.jpg',
            'is_late_submission' => 1, // Integer
        ]);

        $this->assertIsBool($attendance->is_late_submission);
        $this->assertTrue($attendance->is_late_submission);
    }

    /** @test */
    public function it_casts_recovery_timestamp_to_datetime()
    {
        $driver = Driver::factory()->create();
        $vehicle = Vehicle::factory()->create();

        $attendance = Attendance::create([
            'driver_id' => $driver->id,
            'vehicle_id' => $vehicle->id,
            'time_in' => Carbon::now(),
            'speedo_awal' => 50000,
            'gps_location_in' => '-6.200000, 106.816666',
            'selfie_photo_path' => 'photos/test_selfie.jpg',
            'speedo_photo_awal_path' => 'photos/test_speedo.jpg',
            'recovery_timestamp' => '2025-01-15 18:00:00', // String
        ]);

        $this->assertInstanceOf(Carbon::class, $attendance->recovery_timestamp);
        $this->assertEquals('2025-01-15 18:00:00', $attendance->recovery_timestamp->format('Y-m-d H:i:s'));
    }

    /** @test */
    public function it_has_offline_recovery_log_relationship()
    {
        $driver = Driver::factory()->create();
        $vehicle = Vehicle::factory()->create();

        $attendance = Attendance::create([
            'driver_id' => $driver->id,
            'vehicle_id' => $vehicle->id,
            'time_in' => Carbon::now(),
            'speedo_awal' => 50000,
            'gps_location_in' => '-6.200000, 106.816666',
            'selfie_photo_path' => 'photos/test_selfie.jpg',
            'speedo_photo_awal_path' => 'photos/test_speedo.jpg',
            'is_offline_recovery' => true,
            'offline_entry_id' => 'test-uuid-123',
        ]);

        $recoveryLog = OfflineRecoveryLog::create([
            'driver_id' => $driver->id,
            'attendance_id' => $attendance->id,
            'offline_entry_id' => 'test-uuid-123',
            'device_timestamp' => Carbon::now()->subMinutes(30),
            'recovery_timestamp' => Carbon::now(),
            'delay_minutes' => 30,
            'result' => 'success',
            'retry_count' => 0,
        ]);

        $this->assertInstanceOf(OfflineRecoveryLog::class, $attendance->offlineRecoveryLog);
        $this->assertEquals($recoveryLog->id, $attendance->offlineRecoveryLog->id);
    }

    /** @test */
    public function it_allows_null_values_for_offline_recovery_fields()
    {
        $driver = Driver::factory()->create();
        $vehicle = Vehicle::factory()->create();

        $attendance = Attendance::create([
            'driver_id' => $driver->id,
            'vehicle_id' => $vehicle->id,
            'time_in' => Carbon::now(),
            'speedo_awal' => 50000,
            'gps_location_in' => '-6.200000, 106.816666',
            'selfie_photo_path' => 'photos/test_selfie.jpg',
            'speedo_photo_awal_path' => 'photos/test_speedo.jpg',
            'is_offline_recovery' => false,
            'recovery_timestamp' => null,
            'offline_entry_id' => null,
            'is_late_submission' => false,
            'gps_location_out' => null,
        ]);

        $this->assertFalse($attendance->is_offline_recovery);
        $this->assertNull($attendance->recovery_timestamp);
        $this->assertNull($attendance->offline_entry_id);
        $this->assertFalse($attendance->is_late_submission);
        $this->assertNull($attendance->gps_location_out);
    }
}
