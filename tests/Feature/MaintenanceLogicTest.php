<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Gate; 
use Tests\TestCase;
use App\Models\User;
use App\Models\Vehicle;
use App\Models\Attendance;
use App\Models\Driver;
use Carbon\Carbon;

class MaintenanceLogicTest extends TestCase
{
    use RefreshDatabase; 

    private $admin;
    private $vehicle;
    private $driver;

    protected function setUp(): void
    {
        parent::setUp();

        // 1. BYPASS OTORISASI
        Gate::define('is-master-admin', fn() => true);

        // 2. Buat User Admin palsu
        $this->admin = User::factory()->create();

        // 3. Buat Kendaraan Dummy
        $this->vehicle = Vehicle::create([
            'plate_number' => 'B 1234 TEST',
            'type' => 'Box',
            'service_interval_km' => 5000,
            'last_service_km' => 10000, 
            'pajak_stnk_berlaku_sampai' => now()->addYear(),
            'kir_berlaku_sampai' => now()->addYear(),
        ]);

        // 4. BUAT DRIVER DUMMY (PERBAIKAN: Tambah NIK)
        // Kita isi kolom-kolom yang kemungkinan besar wajib (NOT NULL) di database Anda
        $this->driver = Driver::create([
            'full_name' => 'Budi Supir Test',
            'driver_id_nik' => '1234567890123456', // <--- FIX ERROR DI SINI
            'phone_number' => '081234567890',      // Tambahan preventif
            'password' => bcrypt('password'),      // Tambahan preventif
            'is_active' => true,
        ]);
    }

    /** @test */
    public function it_updates_vehicle_km_when_new_service_is_recorded()
    {
        $response = $this->actingAs($this->admin)
            ->post(route('admin.aset.catatServis', $this->vehicle->id), [
                'service_date' => Carbon::now()->format('Y-m-d'),
                'km_servis_saat_ini' => 15000, 
                'description' => 'Ganti Oli Rutin',
            ]);

        $response->assertSessionHas('success');

        $this->assertDatabaseHas('vehicles', [
            'id' => $this->vehicle->id,
            'last_service_km' => 15000, 
        ]);

        $this->assertDatabaseHas('maintenance_logs', [
            'vehicle_id' => $this->vehicle->id,
            'km_at_service' => 15000,
            'description' => 'Ganti Oli Rutin',
        ]);
    }

    /** @test */
    public function it_does_not_update_vehicle_km_when_archiving_old_service()
    {
        $response = $this->actingAs($this->admin)
            ->post(route('admin.aset.catatServis', $this->vehicle->id), [
                'service_date' => Carbon::now()->subMonth()->format('Y-m-d'),
                'km_servis_saat_ini' => 9000, 
                'description' => 'Servis lama yang lupa dicatat',
            ]);

        $response->assertSessionHas('success');

        $this->assertDatabaseHas('vehicles', [
            'id' => $this->vehicle->id,
            'last_service_km' => 10000, 
        ]);

        $this->assertDatabaseHas('maintenance_logs', [
            'vehicle_id' => $this->vehicle->id,
            'km_at_service' => 9000,
            'description' => 'Servis lama yang lupa dicatat (Arsip Susulan)',
        ]);
    }

    /** @test */
    public function it_calculates_mileage_correctly()
    {
        $attendance = Attendance::create([
            'vehicle_id' => $this->vehicle->id,
            'driver_id' => $this->driver->id, 
            'time_in' => Carbon::now()->subHours(5),
            'time_out' => Carbon::now(),
            'speedo_awal' => 10000,
            'speedo_akhir' => 10150, 
            
            // Kolom Wajib
            'gps_location_in' => '-6.2,106.8',
            'selfie_photo_path' => 'dummy/selfie.jpg',
            'speedo_photo_awal_path' => 'dummy/awal.jpg', 
            'speedo_photo_akhir_path' => 'dummy/akhir.jpg', 
            'check_ban' => 'Aman',
            'check_rem' => 'Aman',
            'check_lampu' => 'Aman',
        ]);

        $jarakTempuh = $attendance->speedo_akhir - $attendance->speedo_awal;

        $this->assertEquals(150, $jarakTempuh);
        $this->assertGreaterThanOrEqual(0, $jarakTempuh);
    }
    
    /** @test */
    public function it_prevents_unrealistic_km_input()
    {
        Attendance::create([
            'vehicle_id' => $this->vehicle->id,
            'driver_id' => $this->driver->id, 
            'time_in' => now(), 
            'time_out' => now()->addHours(2), // FIX: Add time_out so query finds it
            'speedo_awal' => 10000,
            'speedo_akhir' => 10500, 
            
            // Kolom Wajib
            'gps_location_in' => '-6.2,106.8',
            'selfie_photo_path' => 'dummy/selfie.jpg',
            'speedo_photo_awal_path' => 'dummy/awal.jpg', 
            'speedo_photo_akhir_path' => 'dummy/akhir.jpg', // FIX: Add this
            'check_ban' => 'Aman',
            'check_rem' => 'Aman',
            'check_lampu' => 'Aman',
        ]);

        $response = $this->actingAs($this->admin)
            ->post(route('admin.aset.catatServis', $this->vehicle->id), [
                'service_date' => now()->format('Y-m-d'),
                'km_servis_saat_ini' => 15000, 
                'description' => 'Typo input',
            ]);
            
        $response->assertSessionHas('error');
        
        $this->assertDatabaseHas('vehicles', [
            'id' => $this->vehicle->id,
            'last_service_km' => 10000, 
        ]);
    }
}