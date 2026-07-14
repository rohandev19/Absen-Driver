<?php

namespace Tests\Feature;

use App\Models\Driver;
use App\Models\User;
use App\Models\Vehicle;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;
use Carbon\Carbon;

class VehicleReplacementAndAttendanceIntegrationTest extends TestCase
{
    use RefreshDatabase;

    private $admin;
    private $driver;
    private $originalVehicle;
    private $replacementVehicle;

    protected function setUp(): void
    {
        parent::setUp();
        
        Storage::fake('public');

        $this->admin = User::factory()->create([
            'role' => 'master',
        ]);

        $this->driver = Driver::factory()->create([
            'is_on_duty' => false,
        ]);

        $this->originalVehicle = Vehicle::factory()->create([
            'plate_number' => 'B 1234 ASLI',
            'type' => 'Engkel',
        ]);

        $this->replacementVehicle = Vehicle::factory()->create([
            'plate_number' => 'B 9999 GANTI',
            'type' => 'Engkel',
        ]);
    }

    public function test_admin_can_create_replacement_and_driver_can_checkin_manual()
    {
        // 1. ADMIN CREATES REPLACEMENT
        $response = $this->actingAs($this->admin)->post(route('admin.vehicle_replacements.store'), [
            'original_vehicle_id' => $this->originalVehicle->id,
            'replacement_vehicle_id' => $this->replacementVehicle->id,
            'driver_id' => $this->driver->id,
            'start_at' => Carbon::now()->format('Y-m-d\TH:i'),
            'reason' => 'Mobil asli servis rem',
            'notes' => 'Ganti sementara selama 2 hari',
        ]);

        $response->assertRedirect(route('admin.vehicle_replacements.index'));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('vehicle_replacements', [
            'original_vehicle_id' => $this->originalVehicle->id,
            'replacement_vehicle_id' => $this->replacementVehicle->id,
            'driver_id' => $this->driver->id,
            'status' => 'active',
        ]);

        // 2. DRIVER DOES MANUAL CHECK-IN
        $photo = UploadedFile::fake()->image('mobil_pengganti.jpg');
        $selfie = UploadedFile::fake()->image('selfie.jpg');
        $speedo = UploadedFile::fake()->image('speedo.jpg');
        $kondisi1 = UploadedFile::fake()->image('kiri.jpg');
        $kondisi2 = UploadedFile::fake()->image('kanan.jpg');

        $checkinResponse = $this->actingAs($this->driver, 'sanctum')->postJson('/api/submit-attendance', [
            'type' => 'check_in',
            'plate_number' => 'B 9999 GANTI',
            'vehicle_entry_method' => 'manual',
            'manual_vehicle_plate' => 'B 9999 GANTI',
            'manual_vehicle_reason' => 'Menggunakan mobil pengganti',
            'manual_vehicle_photo' => $photo,
            'gps_location' => '-6.200000, 106.816666',
            'timestamp' => Carbon::now()->format('Y-m-d H:i:s'),
            'speedometer_manual' => 15000,
            'selfie_photo' => $selfie,
            'speedometer_photo' => $speedo,
            'condition_photo_1' => $kondisi1,
            'condition_photo_2' => $kondisi2,
        ]);

        $checkinResponse->assertStatus(200);

        // Verifikasi DB Attendances
        $this->assertDatabaseHas('attendances', [
            'driver_id' => $this->driver->id,
            'vehicle_entry_method' => 'manual',
            'manual_vehicle_plate' => 'B9999GANTI',
            'manual_vehicle_reason' => 'Menggunakan mobil pengganti',
        ]);
        
        $this->assertDatabaseHas('drivers', [
            'id' => $this->driver->id,
            'is_on_duty' => 1,
        ]);
    }
}
