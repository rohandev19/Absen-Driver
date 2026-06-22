<?php

namespace Tests\Feature\Web;

use Tests\TestCase;
use App\Models\User;
use App\Models\Vehicle;
use App\Models\Project;
use Illuminate\Foundation\Testing\RefreshDatabase;

class VehicleManagementTest extends TestCase
{
    use RefreshDatabase;

    protected $admin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->admin = User::factory()->create(['role' => 'master']);
    }

    /** @test */
    public function admin_can_view_vehicle_list()
    {
        Vehicle::factory()->count(5)->create();

        $response = $this->actingAs($this->admin)
            ->get('/admin/daftar-aset');

        $response->assertStatus(200)
            ->assertViewIs('admin.daftar_aset')
            ->assertViewHas('vehicles');
    }

    /** @test */
    public function admin_can_view_add_vehicle_form()
    {
        $response = $this->actingAs($this->admin)
            ->get('/admin/aset/tambah');

        $response->assertStatus(200)
            ->assertViewIs('admin.aset.create');
    }

    /** @test */
    public function admin_can_create_vehicle()
    {
        $project = Project::factory()->create();

        $response = $this->actingAs($this->admin)
            ->post('/admin/aset/simpan', [
                'plate_number' => 'B1234XYZ',
                'type' => 'Truck',
                'project_id' => $project->id,
                'status' => 'Aktif',
                'current_km' => 50000,
                'service_interval_km' => 5000,
                'last_service_km' => 45000,
                'pajak_stnk_berlaku_sampai' => '2025-12-31',
                'kir_berlaku_sampai' => '2025-06-30',
            ]);

        $response->assertRedirect('/admin/daftar-aset');

        $this->assertDatabaseHas('vehicles', [
            'plate_number' => 'B1234XYZ',
            'type' => 'Truck',
            'current_km' => 50000,
        ]);
    }

    /** @test */
    public function admin_can_view_edit_vehicle_form()
    {
        $vehicle = Vehicle::factory()->create();

        $response = $this->actingAs($this->admin)
            ->get('/admin/aset/' . $vehicle->id . '/edit');

        $response->assertStatus(200)
            ->assertViewIs('admin.aset.edit')
            ->assertViewHas('vehicle', $vehicle);
    }

    /** @test */
    public function admin_can_update_vehicle()
    {
        $vehicle = Vehicle::factory()->create(['plate_number' => 'B1234XYZ']);

        $response = $this->actingAs($this->admin)
            ->put('/admin/aset/' . $vehicle->id . '/update', [
                'plate_number' => 'B5678ABC',
                'type' => 'Van',
                'status' => 'Aktif',
                'current_km' => 60000,
                'service_interval_km' => 5000,
                'last_service_km' => 55000,
            ]);

        $response->assertRedirect('/admin/daftar-aset');

        $this->assertDatabaseHas('vehicles', [
            'id' => $vehicle->id,
            'plate_number' => 'B5678ABC',
            'type' => 'Van',
        ]);
    }

    /** @test */
    public function admin_can_delete_vehicle()
    {
        $vehicle = Vehicle::factory()->create();

        $response = $this->actingAs($this->admin)
            ->delete('/admin/aset/' . $vehicle->id . '/hapus');

        $response->assertRedirect('/admin/daftar-aset');

        $this->assertDatabaseMissing('vehicles', [
            'id' => $vehicle->id,
        ]);
    }

    /** @test */
    public function vehicle_deletion_is_rate_limited()
    {
        $vehicles = Vehicle::factory()->count(15)->create();

        // Attempt 11 deletions (limit is 10/min)
        foreach ($vehicles->take(11) as $vehicle) {
            $response = $this->actingAs($this->admin)
                ->delete('/admin/aset/' . $vehicle->id . '/hapus');
        }

        $response->assertStatus(429); // Too Many Requests
    }

    /** @test */
    public function admin_can_view_vehicle_service_history()
    {
        $vehicle = Vehicle::factory()->create();

        $response = $this->actingAs($this->admin)
            ->get('/admin/aset/' . $vehicle->id . '/riwayat-servis');

        $response->assertStatus(200)
            ->assertViewIs('admin.aset.riwayat')
            ->assertViewHas('vehicle', $vehicle);
    }

    /** @test */
    public function admin_can_record_service()
    {
        $vehicle = Vehicle::factory()->create([
            'current_km' => 50000,
            'last_service_km' => 45000,
        ]);

        $response = $this->actingAs($this->admin)
            ->post('/admin/daftar-aset/' . $vehicle->id . '/catat-servis', [
                'service_date' => '2024-01-15',
                'km_servis_saat_ini' => 50000,
                'description' => 'Ganti Oli',
            ]);

        $response->assertRedirect();

        $this->assertDatabaseHas('maintenance_logs', [
            'vehicle_id' => $vehicle->id,
            'description' => 'Ganti Oli',
            'km_at_service' => 50000,
        ]);
    }

    /** @test */
    public function admin_can_search_vehicles()
    {
        Vehicle::factory()->create(['plate_number' => 'B1234XYZ']);
        Vehicle::factory()->create(['plate_number' => 'D5678ABC']);

        $response = $this->actingAs($this->admin)
            ->get('/admin/daftar-aset?search=B1234');

        $response->assertStatus(200)
            ->assertSee('B1234XYZ')
            ->assertDontSee('D5678ABC');
    }

    /** @test */
    public function vehicle_plate_number_must_be_unique()
    {
        Vehicle::factory()->create(['plate_number' => 'B1234XYZ']);

        $response = $this->actingAs($this->admin)
            ->post('/admin/aset/simpan', [
                'plate_number' => 'B1234XYZ',
                'type' => 'Truck',
                'status' => 'Aktif',
            ]);

        $response->assertSessionHasErrors('plate_number');
    }
}
