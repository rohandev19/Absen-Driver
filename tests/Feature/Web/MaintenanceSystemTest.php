<?php

namespace Tests\Feature\Web;

use Tests\TestCase;
use App\Models\User;
use App\Models\Vehicle;
use App\Models\VehicleComponent;
use App\Models\MaintenanceSchedule;
use App\Models\MaintenanceAlert;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Carbon\Carbon;

class MaintenanceSystemTest extends TestCase
{
    use RefreshDatabase;

    protected $admin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->admin = User::factory()->create(['role' => 'master']);
    }

    /** @test */
    public function admin_can_view_maintenance_dashboard()
    {
        $response = $this->actingAs($this->admin)
            ->get('/admin/maintenance-dashboard');

        $response->assertStatus(200)
            ->assertViewIs('admin.maintenance.index');
    }

    /** @test */
    public function admin_can_view_component_management_page()
    {
        $vehicle = Vehicle::factory()->create();

        $response = $this->actingAs($this->admin)
            ->get('/admin/maintenance/components/' . $vehicle->id);

        $response->assertStatus(200)
            ->assertViewIs('admin.maintenance.components')
            ->assertViewHas('vehicle', $vehicle);
    }

    /** @test */
    public function admin_can_add_vehicle_component()
    {
        $vehicle = Vehicle::factory()->create(['current_km' => 50000]);

        $response = $this->actingAs($this->admin)
            ->post('/admin/maintenance/components/' . $vehicle->id . '/store', [
                'component_name' => 'Oli Mesin',
                'category' => 'engine',
                'replacement_interval_km' => 5000,
                'replacement_interval_days' => 180,
                'last_replacement_km' => 45000,
                'last_replacement_date' => '2024-01-01',
                'cost_per_replacement' => 500000,
                'warning_threshold_km' => 1000,
                'critical_threshold_km' => 500,
            ]);

        $response->assertRedirect();

        $this->assertDatabaseHas('vehicle_components', [
            'vehicle_id' => $vehicle->id,
            'component_name' => 'Oli Mesin',
            'replacement_interval_km' => 5000,
        ]);
    }

    /** @test */
    public function admin_can_update_vehicle_component()
    {
        $component = VehicleComponent::factory()->create([
            'component_name' => 'Oli Mesin',
            'replacement_interval_km' => 5000,
        ]);

        $response = $this->actingAs($this->admin)
            ->put('/admin/maintenance/components/' . $component->id . '/update', [
                'component_name' => 'Oli Mesin Synthetic',
                'category' => 'engine',
                'replacement_interval_km' => 7500,
                'replacement_interval_days' => 180,
                'cost_per_replacement' => 750000,
                'warning_threshold_km' => 1500,
                'critical_threshold_km' => 750,
            ]);

        $response->assertRedirect();

        $this->assertDatabaseHas('vehicle_components', [
            'id' => $component->id,
            'component_name' => 'Oli Mesin',
            'replacement_interval_km' => 7500,
        ]);
    }

    /** @test */
    public function admin_can_delete_vehicle_component()
    {
        $component = VehicleComponent::factory()->create();

        $response = $this->actingAs($this->admin)
            ->delete('/admin/maintenance/components/' . $component->id . '/delete');

        $response->assertRedirect();

        $this->assertDatabaseMissing('vehicle_components', [
            'id' => $component->id,
        ]);
    }

    /** @test */
    public function admin_can_view_maintenance_alerts()
    {
        MaintenanceAlert::factory()->count(5)->create(['status' => 'active']);

        $response = $this->actingAs($this->admin)
            ->get('/admin/maintenance/alerts');

        $response->assertStatus(200)
            ->assertViewIs('admin.maintenance.alerts')
            ->assertViewHas('alerts');
    }

    /** @test */
    public function admin_can_generate_maintenance_alerts()
    {
        $vehicle = Vehicle::factory()->create(['current_km' => 50000]);
        VehicleComponent::factory()->create([
            'vehicle_id' => $vehicle->id,
            'last_replacement_km' => 45000,
            'replacement_interval_km' => 5100, // next_replacement_km will be 50100 (100 KM remaining)
            'critical_threshold_km' => 500,
            'warning_threshold_km' => 1000,
            'last_replacement_date' => Carbon::now()->subDays(10),
            'replacement_interval_days' => 30, // next_replacement_date will be 20 days in the future (no date alert)
        ]);

        $response = $this->actingAs($this->admin)
            ->post('/admin/maintenance/alerts/generate');

        $response->assertRedirect();

        $this->assertDatabaseHas('maintenance_alerts', [
            'vehicle_id' => $vehicle->id,
            'status' => 'active',
        ]);
    }

    /** @test */
    public function admin_can_acknowledge_alert()
    {
        $alert = MaintenanceAlert::factory()->create(['status' => 'active']);

        $response = $this->actingAs($this->admin)
            ->post('/admin/maintenance/alerts/' . $alert->id . '/acknowledge');

        $response->assertRedirect();

        $this->assertDatabaseHas('maintenance_alerts', [
            'id' => $alert->id,
            'status' => 'acknowledged',
            'acknowledged_by' => $this->admin->id,
        ]);
    }

    /** @test */
    public function admin_can_resolve_alert()
    {
        $alert = MaintenanceAlert::factory()->create(['status' => 'active']);

        $response = $this->actingAs($this->admin)
            ->post('/admin/maintenance/alerts/' . $alert->id . '/resolve');

        $response->assertRedirect();

        $this->assertDatabaseHas('maintenance_alerts', [
            'id' => $alert->id,
            'status' => 'resolved',
        ]);
    }

    /** @test */
    public function admin_can_view_maintenance_schedules()
    {
        MaintenanceSchedule::factory()->count(5)->create();

        $response = $this->actingAs($this->admin)
            ->get('/admin/maintenance/schedules');

        $response->assertStatus(200)
            ->assertViewIs('admin.maintenance.schedules')
            ->assertViewHas('schedules');
    }

    /** @test */
    public function admin_can_create_maintenance_schedule()
    {
        $vehicle = Vehicle::factory()->create();
        $component = VehicleComponent::factory()->create(['vehicle_id' => $vehicle->id]);

        $response = $this->actingAs($this->admin)
            ->post('/admin/maintenance/schedules/store', [
                'vehicle_id' => $vehicle->id,
                'component_id' => $component->id,
                'scheduled_date' => '2024-12-31',
                'scheduled_km' => 55000,
                'type' => 'preventive',
                'priority' => 'high',
                'estimated_cost' => 500000,
                'workshop_name' => 'Bengkel Jaya',
                'notes' => 'Scheduled maintenance',
            ]);

        $response->assertRedirect();

        $this->assertDatabaseHas('maintenance_schedules', [
            'vehicle_id' => $vehicle->id,
            'component_id' => $component->id,
            'type' => 'preventive',
        ]);
    }

    /** @test */
    public function admin_can_complete_maintenance_schedule()
    {
        \Illuminate\Support\Facades\Storage::fake('public');

        $vehicle = Vehicle::factory()->create(['current_km' => 50000]);
        $component = VehicleComponent::factory()->create([
            'vehicle_id' => $vehicle->id,
            'last_replacement_km' => 45000,
        ]);
        $schedule = MaintenanceSchedule::factory()->create([
            'vehicle_id' => $vehicle->id,
            'component_id' => $component->id,
            'status' => 'pending',
        ]);

        $response = $this->actingAs($this->admin)
            ->post('/admin/maintenance/schedules/' . $schedule->id . '/complete', [
                'actual_cost' => 550000,
                'receipt_photo' => \Illuminate\Http\UploadedFile::fake()->image('receipt.jpg'),
                'odometer_photo' => \Illuminate\Http\UploadedFile::fake()->image('odometer.jpg'),
                'signer_name' => 'John Doe',
                'signer_role' => 'Operator',
                'signature' => 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mNk+M9QDwADhgGAWjR9awAAAABJRU5ErkJggg==',
            ]);

        $response->assertRedirect();

        $this->assertDatabaseHas('maintenance_schedules', [
            'id' => $schedule->id,
            'status' => 'completed',
            'completed_by' => $this->admin->id,
        ]);

        // Component should be updated
        $component->refresh();
        $this->assertEquals(50000, $component->last_replacement_km);
    }

    /** @test */
    public function admin_can_view_maintenance_calendar()
    {
        $response = $this->actingAs($this->admin)
            ->get('/admin/maintenance-calendar');

        $response->assertStatus(200)
            ->assertViewIs('admin.maintenance_calendar');
    }

    /** @test */
    public function admin_can_get_maintenance_calendar_events()
    {
        Vehicle::factory()->count(3)->create();

        $response = $this->actingAs($this->admin)
            ->get('/admin/api/maintenance-events');

        $response->assertStatus(200)
            ->assertJsonCount(6);
    }

    /** @test */
    public function component_next_replacement_is_calculated_automatically()
    {
        $vehicle = Vehicle::factory()->create(['current_km' => 50000]);

        $component = VehicleComponent::create([
            'vehicle_id' => $vehicle->id,
            'component_name' => 'Oli Mesin',
            'category' => 'engine',
            'replacement_interval_km' => 5000,
            'last_replacement_km' => 45000,
            'last_replacement_date' => Carbon::now()->subDays(90),
            'replacement_interval_days' => 180,
            'cost_per_replacement' => 500000,
            'warning_threshold_km' => 1000,
            'critical_threshold_km' => 500,
        ]);

        $this->assertEquals(50000, $component->next_replacement_km); // 45000 + 5000
        $this->assertNotNull($component->next_replacement_date);
    }

    /** @test */
    public function component_status_is_updated_automatically()
    {
        $vehicle = Vehicle::factory()->create(['current_km' => 49900]);

        $component = VehicleComponent::create([
            'vehicle_id' => $vehicle->id,
            'component_name' => 'Oli Mesin',
            'category' => 'engine',
            'replacement_interval_km' => 5000,
            'last_replacement_km' => 45000,
            'cost_per_replacement' => 500000,
            'warning_threshold_km' => 1000,
            'critical_threshold_km' => 500,
        ]);

        // 50000 - 49900 = 100 KM remaining (< 500 critical threshold)
        $this->assertEquals('critical', $component->status);
    }

    /** @test */
    public function overdue_component_has_correct_status()
    {
        $vehicle = Vehicle::factory()->create(['current_km' => 51000]);

        $component = VehicleComponent::create([
            'vehicle_id' => $vehicle->id,
            'component_name' => 'Oli Mesin',
            'category' => 'engine',
            'replacement_interval_km' => 5000,
            'last_replacement_km' => 45000,
            'cost_per_replacement' => 500000,
            'warning_threshold_km' => 1000,
            'critical_threshold_km' => 500,
        ]);

        // 50000 - 51000 = -1000 KM (overdue)
        $this->assertEquals('overdue', $component->status);
    }
}
