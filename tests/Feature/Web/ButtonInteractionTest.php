<?php

namespace Tests\Feature\Web;

use Tests\TestCase;
use App\Models\User;
use App\Models\Driver;
use App\Models\Vehicle;
use App\Models\Project;
use App\Models\Customer;
use App\Models\Attendance;
use App\Models\ServiceReport;
use App\Models\TransportCost;
use App\Models\VehicleComponent;
use App\Models\MaintenanceAlert;
use App\Models\MaintenanceSchedule;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ButtonInteractionTest extends TestCase
{
    use RefreshDatabase;

    protected $admin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->admin = User::factory()->create(['role' => 'master']);
    }

    // ==========================================
    // DASHBOARD BUTTONS
    // ==========================================

    /** @test */
    public function dashboard_view_all_vehicles_button_works()
    {
        $response = $this->actingAs($this->admin)
            ->get('/admin/dashboard');

        $response->assertStatus(200)
            ->assertSee('Aset Tersedia')
            ->assertSee('/admin/daftar-aset');
    }

    /** @test */
    public function dashboard_view_reports_button_works()
    {
        $response = $this->actingAs($this->admin)
            ->get('/admin/dashboard');

        $response->assertStatus(200)
            ->assertSee('Laporan');
    }

    // ==========================================
    // VEHICLE MANAGEMENT BUTTONS
    // ==========================================

    /** @test */
    public function vehicle_list_add_button_works()
    {
        $response = $this->actingAs($this->admin)
            ->get('/admin/daftar-aset');

        $response->assertStatus(200)
            ->assertSee('Tambah Aset')
            ->assertSee('/admin/aset/tambah');
    }

    /** @test */
    public function vehicle_list_search_button_works()
    {
        Vehicle::factory()->create(['plate_number' => 'B1234XYZ']);

        $response = $this->actingAs($this->admin)
            ->get('/admin/daftar-aset?search=B1234');

        $response->assertStatus(200)
            ->assertSee('B1234XYZ');
    }

    /** @test */
    public function vehicle_list_edit_button_works()
    {
        $vehicle = Vehicle::factory()->create();

        $response = $this->actingAs($this->admin)
            ->get('/admin/daftar-aset');

        $response->assertStatus(200)
            ->assertSee('Edit')
            ->assertSee('/admin/aset/' . $vehicle->id . '/edit');
    }

    /** @test */
    public function vehicle_list_delete_button_works()
    {
        $vehicle = Vehicle::factory()->create();

        $response = $this->actingAs($this->admin)
            ->delete('/admin/aset/' . $vehicle->id . '/hapus');

        $response->assertRedirect('/admin/daftar-aset');
        $this->assertDatabaseMissing('vehicles', ['id' => $vehicle->id]);
    }

    /** @test */
    public function vehicle_list_view_history_button_works()
    {
        $vehicle = Vehicle::factory()->create();

        $response = $this->actingAs($this->admin)
            ->get('/admin/aset/' . $vehicle->id . '/riwayat-servis');

        $response->assertStatus(200);
    }

    /** @test */
    public function vehicle_add_save_button_works()
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
            ]);

        $response->assertRedirect('/admin/daftar-aset');
        $this->assertDatabaseHas('vehicles', ['plate_number' => 'B1234XYZ']);
    }

    /** @test */
    public function vehicle_add_cancel_button_works()
    {
        $response = $this->actingAs($this->admin)
            ->get('/admin/aset/tambah');

        $response->assertStatus(200)
            ->assertSee('Batal')
            ->assertSee('/admin/daftar-aset');
    }

    /** @test */
    public function vehicle_edit_update_button_works()
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
        $this->assertDatabaseHas('vehicles', ['plate_number' => 'B5678ABC']);
    }

    /** @test */
    public function vehicle_record_service_button_works()
    {
        $vehicle = Vehicle::factory()->create(['last_service_km' => 45000]);

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

    // ==========================================
    // DRIVER MANAGEMENT BUTTONS
    // ==========================================

    /** @test */
    public function driver_list_add_button_works()
    {
        $response = $this->actingAs($this->admin)
            ->get('/admin/driver');

        $response->assertStatus(200)
            ->assertSee('Tambah Driver');
    }

    /** @test */
    public function driver_list_edit_button_works()
    {
        $driver = Driver::factory()->create();

        $response = $this->actingAs($this->admin)
            ->get('/admin/driver/' . $driver->id . '/edit');

        $response->assertStatus(200);
    }

    /** @test */
    public function driver_list_delete_button_works()
    {
        $driver = Driver::factory()->create();

        $response = $this->actingAs($this->admin)
            ->delete('/admin/driver/' . $driver->id);

        $response->assertRedirect('/admin/driver');
        $this->assertDatabaseMissing('drivers', ['id' => $driver->id]);
    }

    /** @test */
    public function driver_view_documents_button_works()
    {
        \Illuminate\Support\Facades\Storage::fake('local');
        \Illuminate\Support\Facades\Storage::disk('local')->put('drivers/ktp_test.jpg', 'fake ktp content');

        $driver = Driver::factory()->create([
            'foto_ktp' => 'drivers/ktp_test.jpg',
        ]);

        $response = $this->actingAs($this->admin)
            ->get('/admin/driver/dokumen/' . $driver->id . '/ktp');

        $response->assertStatus(200);
    }

    // ==========================================
    // MAINTENANCE SYSTEM BUTTONS
    // ==========================================

    /** @test */
    public function maintenance_dashboard_button_works()
    {
        $response = $this->actingAs($this->admin)
            ->get('/admin/maintenance-dashboard');

        $response->assertStatus(200)
            ->assertSee('Maintenance Monitor');
    }

    /** @test */
    public function maintenance_add_component_button_works()
    {
        $vehicle = Vehicle::factory()->create();

        $response = $this->actingAs($this->admin)
            ->post('/admin/maintenance/components/' . $vehicle->id . '/store', [
                'component_name' => 'Oli Mesin',
                'category' => 'Cairan & Pelumas', // Must match categories in code/database validation
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
        ]);
    }

    /** @test */
    public function maintenance_edit_component_button_works()
    {
        $component = VehicleComponent::factory()->create([
            'component_name' => 'Oli Mesin',
            'cost_per_replacement' => 500000,
        ]);

        $response = $this->actingAs($this->admin)
            ->put('/admin/maintenance/components/' . $component->id . '/update', [
                'replacement_interval_km' => 7500,
                'replacement_interval_days' => 180,
                'cost_per_replacement' => 750000,
            ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('vehicle_components', [
            'id' => $component->id,
            'cost_per_replacement' => 750000,
        ]);
    }

    /** @test */
    public function maintenance_delete_component_button_works()
    {
        $component = VehicleComponent::factory()->create();

        $response = $this->actingAs($this->admin)
            ->delete('/admin/maintenance/components/' . $component->id . '/delete');

        $response->assertRedirect();
        $this->assertDatabaseMissing('vehicle_components', ['id' => $component->id]);
    }

    /** @test */
    public function maintenance_generate_alerts_button_works()
    {
        $vehicle = Vehicle::factory()->create(['current_km' => 49900]);
        VehicleComponent::factory()->create([
            'vehicle_id' => $vehicle->id,
            'next_replacement_km' => 50000,
            'critical_threshold_km' => 500,
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
    public function maintenance_acknowledge_alert_button_works()
    {
        $alert = MaintenanceAlert::factory()->create(['status' => 'active']);

        $response = $this->actingAs($this->admin)
            ->post('/admin/maintenance/alerts/' . $alert->id . '/acknowledge');

        $response->assertRedirect();
        $this->assertDatabaseHas('maintenance_alerts', [
            'id' => $alert->id,
            'status' => 'acknowledged',
        ]);
    }

    /** @test */
    public function maintenance_resolve_alert_button_works()
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
    public function maintenance_create_schedule_button_works()
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
        ]);
    }

    /** @test */
    public function maintenance_complete_schedule_button_works()
    {
        $schedule = MaintenanceSchedule::factory()->create(['status' => 'pending']);

        $response = $this->actingAs($this->admin)
            ->post('/admin/maintenance/schedules/' . $schedule->id . '/complete', [
                'actual_cost' => 550000,
            ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('maintenance_schedules', [
            'id' => $schedule->id,
            'status' => 'completed',
        ]);
    }

    // ==========================================
    // SERVICE REPORT BUTTONS
    // ==========================================

    /** @test */
    public function service_report_approve_button_works()
    {
        $serviceReport = ServiceReport::factory()->create(['status' => 'pending']);
        $dummySignature = 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mNk+M9QDwADhgGAWjR9awAAAABJRU5ErkJggg==';

        $response = $this->actingAs($this->admin)
            ->post('/admin/service/' . $serviceReport->id . '/approve', [
                'admin_notes' => 'Approved',
                'signature' => $dummySignature,
                'signer_name' => 'Admin Test',
                'signer_role' => 'Manager',
                'workshop_name' => 'Bengkel Test',
                'invoice_number' => 'INV-123',
                'service_cost' => 150000,
                'sparepart_cost' => 350000,
                'total_cost' => 500000,
            ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('service_reports', [
            'id' => $serviceReport->id,
            'status' => 'pending_customer',
        ]);
    }

    /** @test */
    public function service_report_reject_button_works()
    {
        $serviceReport = ServiceReport::factory()->create(['status' => 'pending']);

        $response = $this->actingAs($this->admin)
            ->post('/admin/service/' . $serviceReport->id . '/reject', [
                'rejected_reason' => 'Data tidak lengkap',
            ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('service_reports', [
            'id' => $serviceReport->id,
            'status' => 'rejected',
        ]);
    }

    /** @test */
    public function service_report_export_finance_button_works()
    {
        $serviceReport = ServiceReport::factory()->create([
            'status' => 'pending_customer',
        ]);

        $response = $this->actingAs($this->admin)
            ->get('/admin/service/' . $serviceReport->id . '/finance-pdf/download');

        $response->assertStatus(200);
    }

    // ==========================================
    // TRANSPORT COST BUTTONS
    // ==========================================

    /** @test */
    public function transport_cost_approve_button_works()
    {
        $transportCost = TransportCost::factory()->create(['approval_status' => 'pending']);

        $response = $this->actingAs($this->admin)
            ->post('/admin/transport-costs/' . $transportCost->id . '/approve');

        $response->assertRedirect();
        $this->assertDatabaseHas('transport_costs', [
            'id' => $transportCost->id,
            'approval_status' => 'approved',
        ]);
    }

    /** @test */
    public function transport_cost_reject_button_works()
    {
        $transportCost = TransportCost::factory()->create(['approval_status' => 'pending']);

        $response = $this->actingAs($this->admin)
            ->post('/admin/transport-costs/' . $transportCost->id . '/reject', [
                'rejection_reason' => 'Data tidak sesuai',
            ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('transport_costs', [
            'id' => $transportCost->id,
            'approval_status' => 'rejected',
        ]);
    }

    /** @test */
    public function transport_cost_submit_to_finance_button_works()
    {
        $transportCost = TransportCost::factory()->create([
            'approval_status' => 'approved',
            'submitted_to_finance' => false,
        ]);

        $response = $this->actingAs($this->admin)
            ->post('/admin/transport-costs/' . $transportCost->id . '/submit-to-finance');

        $response->assertRedirect();
        $this->assertDatabaseHas('transport_costs', [
            'id' => $transportCost->id,
            'submitted_to_finance' => true,
        ]);
    }

    /** @test */
    public function transport_cost_export_finance_button_works()
    {
        $transportCost = TransportCost::factory()->create([
            'approval_status' => 'approved',
        ]);

        $response = $this->actingAs($this->admin)
            ->get('/admin/transport-costs/' . $transportCost->id . '/export-finance');

        $response->assertStatus(200);
    }

    // ==========================================
    // REPORT BUTTONS
    // ==========================================

    /** @test */
    public function report_export_driver_history_button_works()
    {
        $response = $this->actingAs($this->admin)
            ->get('/admin/report/driver/export');

        $response->assertStatus(200);
    }

    /** @test */
    public function report_export_monthly_checklist_button_works()
    {
        $response = $this->actingAs($this->admin)
            ->get('/admin/rekap-bulanan/export-checklist');

        $response->assertStatus(200);
    }

    /** @test */
    public function report_update_km_button_works()
    {
        $attendance = Attendance::factory()->create([
            'speedo_awal' => 50000,
            'speedo_akhir' => 50150,
        ]);

        $response = $this->actingAs($this->admin)
            ->put('/admin/attendance/' . $attendance->id . '/update-km', [
                'speedo_awal' => 50010,
                'speedo_akhir' => 50160,
            ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('attendances', [
            'id' => $attendance->id,
            'speedo_awal' => 50010,
        ]);
    }

    // ==========================================
    // LOGOUT BUTTON
    // ==========================================

    /** @test */
    public function logout_button_works()
    {
        $response = $this->actingAs($this->admin)
            ->post('/admin/logout');

        $response->assertRedirect('/admin/login');
        $this->assertGuest();
    }
}
