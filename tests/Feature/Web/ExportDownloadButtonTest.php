<?php

namespace Tests\Feature\Web;

use Tests\TestCase;
use App\Models\User;
use App\Models\Driver;
use App\Models\Vehicle;
use App\Models\Attendance;
use App\Models\ServiceReport;
use App\Models\TransportCost;
use App\Models\MaintenanceSchedule;
use App\Models\MaintenanceAlert;
use App\Models\Customer;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;

class ExportDownloadButtonTest extends TestCase
{
    use RefreshDatabase;

    protected $admin;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('local');
        Storage::fake('public');
        $this->admin = User::factory()->create(['role' => 'master']);
    }

    // ==========================================
    // ATTENDANCE EXPORT BUTTONS
    // ==========================================

    /** @test */
    public function export_driver_history_button_works()
    {
        $driver = Driver::factory()->create();
        Attendance::factory()->count(5)->create(['driver_id' => $driver->id]);

        $response = $this->actingAs($this->admin)
            ->get('/admin/report/driver/export?driver_id=' . $driver->id);

        $response->assertStatus(200)
            ->assertHeader('Content-Type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    }

    /** @test */
    public function export_driver_history_with_date_filter_button_works()
    {
        $driver = Driver::factory()->create();
        Attendance::factory()->count(5)->create([
            'driver_id' => $driver->id,
            'time_in' => Carbon::now()->subDays(5),
        ]);

        $response = $this->actingAs($this->admin)
            ->get('/admin/report/driver/export?driver_id=' . $driver->id . '&start_date=' . Carbon::now()->subDays(10)->toDateString() . '&end_date=' . Carbon::now()->toDateString());

        $response->assertStatus(200);
    }

    /** @test */
    public function export_monthly_checklist_button_works()
    {
        $response = $this->actingAs($this->admin)
            ->get('/admin/rekap-bulanan/export-checklist?month=1&year=2024');

        $response->assertStatus(200)
            ->assertHeader('Content-Type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    }

    /** @test */
    public function export_attendance_recap_button_works()
    {
        $response = $this->actingAs($this->admin)
            ->get('/admin/absensi/rekap-export?month=1&year=2024');

        $response->assertStatus(200);
    }

    // ==========================================
    // MAINTENANCE EXPORT BUTTONS
    // ==========================================

    /** @test */
    public function export_maintenance_dashboard_button_works()
    {
        $response = $this->actingAs($this->admin)
            ->get('/admin/maintenance/export/dashboard');

        $response->assertStatus(200)
            ->assertHeader('Content-Type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    }

    /** @test */
    public function export_maintenance_schedules_button_works()
    {
        MaintenanceSchedule::factory()->count(5)->create();

        $response = $this->actingAs($this->admin)
            ->get('/admin/maintenance/export/schedules');

        $response->assertStatus(200)
            ->assertHeader('Content-Type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    }

    /** @test */
    public function export_maintenance_alerts_button_works()
    {
        MaintenanceAlert::factory()->count(5)->create();

        $response = $this->actingAs($this->admin)
            ->get('/admin/maintenance/export/alerts');

        $response->assertStatus(200)
            ->assertHeader('Content-Type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    }

    /** @test */
    public function export_service_history_button_works()
    {
        $vehicle = Vehicle::factory()->create();

        $response = $this->actingAs($this->admin)
            ->get('/admin/aset/riwayat/' . $vehicle->id . '/export');

        $response->assertStatus(200);
    }

    // ==========================================
    // SERVICE REPORT EXPORT BUTTONS
    // ==========================================

    /** @test */
    public function export_service_report_for_finance_button_works()
    {
        $serviceReport = ServiceReport::factory()->create([
            'status' => 'pending_customer',
        ]);

        $response = $this->actingAs($this->admin)
            ->get('/admin/service/' . $serviceReport->id . '/finance-pdf/download');

        $response->assertStatus(200)
            ->assertHeader('Content-Type', 'application/pdf');
    }

    /** @test */
    public function download_service_report_customer_document_button_works()
    {
        $customer = Customer::factory()->create();
        $serviceReport = ServiceReport::factory()->create([
            'customer_id' => $customer->id,
            'status' => 'pending_customer',
            'customer_pdf_path' => 'service_reports/customer_doc.pdf',
        ]);
        File::ensureDirectoryExists(storage_path('app/public/service_reports'));
        File::put(storage_path('app/public/service_reports/customer_doc.pdf'), 'PDF');

        $customerUser = User::factory()->create([
            'role' => 'customer',
            'customer_id' => $customer->id,
        ]);

        $response = $this->actingAs($customerUser)
            ->get('/customer/approve/' . $serviceReport->id . '/download');

        $response->assertStatus(200);
    }

    // ==========================================
    // TRANSPORT COST EXPORT BUTTONS
    // ==========================================

    /** @test */
    public function export_transport_cost_for_finance_button_works()
    {
        $transportCost = TransportCost::factory()->create([
            'approval_status' => 'approved',
        ]);

        $response = $this->actingAs($this->admin)
            ->get('/admin/transport-costs/' . $transportCost->id . '/export-finance');

        $response->assertStatus(200)
            ->assertHeader('Content-Type', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document');
    }

    /** @test */
    public function export_transport_cost_monthly_recap_button_works()
    {
        $driver = Driver::factory()->create();
        for ($i = 0; $i < 5; $i++) {
            TransportCost::factory()->create([
                'driver_id' => $driver->id,
                'approval_status' => 'approved',
                'trip_date' => Carbon::now()->subDays($i),
            ]);
        }

        $response = $this->actingAs($this->admin)
            ->get('/admin/transport-costs/recap/export-finance?month=' . Carbon::now()->format('Y-m') . '&driver_id=' . $driver->id);

        $response->assertStatus(200);
    }

    // ==========================================
    // VEHICLE CERTIFICATE DOWNLOAD BUTTONS
    // ==========================================

    /** @test */
    public function download_vehicle_certificate_button_works()
    {
        $customer = Customer::factory()->create();
        $customerUser = User::factory()->create([
            'role' => 'customer',
            'customer_id' => $customer->id,
        ]);

        $project = \App\Models\Project::factory()->create(['customer_id' => $customer->id]);
        $vehicle = Vehicle::factory()->create([
            'project_id' => $project->id,
            'current_km' => 45000, // Healthy vehicle
        ]);

        $response = $this->actingAs($customerUser)
            ->get('/customer/vehicles/' . $vehicle->id . '/certificate');

        $response->assertStatus(200)
            ->assertSee('Sertifikat Kelayakan Unit');
    }

    // ==========================================
    // PRINT BUTTONS
    // ==========================================

    /** @test */
    public function print_service_report_button_works()
    {
        $serviceReport = ServiceReport::factory()->create();

        $response = $this->actingAs($this->admin)
            ->get('/admin/service/' . $serviceReport->id);

        $response->assertStatus(200)
            ->assertSee('Detail Laporan Service');
    }

    /** @test */
    public function print_vehicle_certificate_button_works()
    {
        $customer = Customer::factory()->create();
        $customerUser = User::factory()->create([
            'role' => 'customer',
            'customer_id' => $customer->id,
        ]);

        $project = \App\Models\Project::factory()->create(['customer_id' => $customer->id]);
        $vehicle = Vehicle::factory()->create([
            'project_id' => $project->id,
            'current_km' => 45000,
        ]);

        $response = $this->actingAs($customerUser)
            ->get('/customer/vehicles/' . $vehicle->id . '/certificate');

        $response->assertStatus(200)
            ->assertSee('Cetak / Simpan PDF');
    }

    /** @test */
    public function print_customer_approval_button_works()
    {
        $customer = Customer::factory()->create();
        $customerUser = User::factory()->create([
            'role' => 'customer',
            'customer_id' => $customer->id,
        ]);

        $serviceReport = ServiceReport::factory()->create([
            'customer_id' => $customer->id,
            'status' => ServiceReport::STATUS_PENDING_CUSTOMER,
        ]);

        $response = $this->actingAs($customerUser)
            ->get('/customer/approve/' . $serviceReport->id);

        $response->assertStatus(200)
            ->assertSee('Cetak Ringkasan');
    }

    // ==========================================
    // DOWNLOAD DRIVER DOCUMENTS BUTTONS
    // ==========================================

    /** @test */
    public function download_driver_ktp_button_works()
    {
        $driver = Driver::factory()->create([
            'foto_ktp' => 'drivers/ktp_test.jpg',
        ]);
        Storage::disk('local')->put('drivers/ktp_test.jpg', 'image');

        $response = $this->actingAs($this->admin)
            ->get('/admin/driver/dokumen/' . $driver->id . '/ktp');

        $response->assertStatus(200);
    }

    /** @test */
    public function download_driver_sim_button_works()
    {
        $driver = Driver::factory()->create([
            'foto_sim' => 'drivers/sim_test.jpg',
        ]);
        Storage::disk('local')->put('drivers/sim_test.jpg', 'image');

        $response = $this->actingAs($this->admin)
            ->get('/admin/driver/dokumen/' . $driver->id . '/sim');

        $response->assertStatus(200);
    }

    // ==========================================
    // BULK EXPORT BUTTONS
    // ==========================================

    /** @test */
    public function bulk_export_transport_costs_button_works()
    {
        $driver = Driver::factory()->create();
        for ($i = 0; $i < 10; $i++) {
            TransportCost::factory()->create([
                'driver_id' => $driver->id,
                'approval_status' => 'approved',
                'trip_date' => Carbon::now()->subDays($i),
            ]);
        }

        $response = $this->actingAs($this->admin)
            ->get('/admin/transport-costs/recap/export-finance?month=' . Carbon::now()->format('Y-m') . '&driver_id=' . $driver->id);

        $response->assertStatus(200);
    }

    /** @test */
    public function bulk_submit_to_finance_button_works()
    {
        $transportCosts = TransportCost::factory()->count(5)->create([
            'approval_status' => 'approved',
            'submitted_to_finance' => false,
        ]);

        $ids = $transportCosts->pluck('id')->toArray();

        $response = $this->actingAs($this->admin)
            ->post('/admin/transport-costs/bulk-submit-to-finance', [
                'ids' => $ids,
            ]);

        $response->assertRedirect();

        foreach ($ids as $id) {
            $this->assertDatabaseHas('transport_costs', [
                'id' => $id,
                'submitted_to_finance' => true,
            ]);
        }
    }

    // ==========================================
    // EXPORT WITH FILTERS
    // ==========================================

    /** @test */
    public function export_with_date_range_filter_works()
    {
        $driver = Driver::factory()->create();
        Attendance::factory()->count(5)->create([
            'driver_id' => $driver->id,
            'time_in' => Carbon::now()->subDays(5),
        ]);

        $response = $this->actingAs($this->admin)
            ->get('/admin/report/driver/export', [
                'driver_id' => $driver->id,
                'start_date' => Carbon::now()->subDays(10)->toDateString(),
                'end_date' => Carbon::now()->toDateString(),
            ]);

        $response->assertStatus(200);
    }

    /** @test */
    public function export_with_vehicle_filter_works()
    {
        $vehicle = Vehicle::factory()->create();

        $response = $this->actingAs($this->admin)
            ->get('/admin/aset/riwayat/' . $vehicle->id . '/export');

        $response->assertStatus(200);
    }

    /** @test */
    public function export_with_status_filter_works()
    {
        MaintenanceAlert::factory()->count(3)->create(['status' => 'active']);
        MaintenanceAlert::factory()->count(2)->create(['status' => 'resolved']);

        $response = $this->actingAs($this->admin)
            ->get('/admin/maintenance/export/alerts?status=active');

        $response->assertStatus(200);
    }

    // ==========================================
    // AUTHORIZATION TESTS
    // ==========================================

    /** @test */
    public function viewer_cannot_export_data()
    {
        $viewer = User::factory()->create(['role' => 'viewer']);

        $response = $this->actingAs($viewer)
            ->get('/admin/report/driver/export');

        $response->assertStatus(403);
    }

    /** @test */
    public function customer_cannot_export_admin_reports()
    {
        $customer = Customer::factory()->create();
        $customerUser = User::factory()->create([
            'role' => 'customer',
            'customer_id' => $customer->id,
        ]);

        $response = $this->actingAs($customerUser)
            ->get('/admin/report/driver/export');

        $response->assertStatus(403);
    }
}
