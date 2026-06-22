<?php

namespace Tests\Feature;

use App\Models\Customer;
use App\Models\Driver;
use App\Models\EmergencyReport;
use App\Models\Project;
use App\Models\ServiceReport;
use App\Models\User;
use App\Models\Vehicle;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EmergencyReportWorkflowTest extends TestCase
{
    use RefreshDatabase;

    private User $serviceAdmin;
    private Customer $customer;
    private Project $project;
    private Driver $driver;
    private Vehicle $vehicle;

    protected function setUp(): void
    {
        parent::setUp();

        $this->serviceAdmin = User::factory()->create([
            'role' => 'service_admin',
        ]);

        $this->customer = Customer::create([
            'name' => 'PT Customer Darurat',
            'code' => 'CUST-DAR',
            'contact_person' => 'PIC Customer',
            'email' => 'customer-darurat@example.com',
            'phone' => '081234567890',
            'address' => 'Jakarta',
        ]);

        $this->project = Project::create([
            'name' => 'Project Darurat',
            'code' => 'PD',
            'customer_id' => $this->customer->id,
        ]);

        $this->driver = Driver::create([
            'full_name' => 'Driver Darurat',
            'driver_id_nik' => 'DRV-DAR',
            'nik_ktp' => '1234567890123456',
            'password' => bcrypt('password'),
            'project_id' => $this->project->id,
        ]);

        $this->vehicle = Vehicle::create([
            'plate_number' => 'B 9090 DAR',
            'type' => 'CDE',
            'project_id' => $this->project->id,
            'status' => 'Aktif',
            'current_km' => 10000,
            'service_interval_km' => 5000,
            'last_service_km' => 5000,
        ]);
    }

    public function test_admin_can_convert_emergency_report_to_service_report(): void
    {
        $emergencyReport = $this->createEmergencyReport();

        $response = $this->actingAs($this->serviceAdmin)
            ->post(route('admin.laporan_darurat.create_service', $emergencyReport), [
                'follow_up_notes' => 'Rem bermasalah, perlu masuk alur service resmi.',
            ]);

        $serviceReport = ServiceReport::firstOrFail();

        $response->assertRedirect(route('admin.service.show', $serviceReport->id));

        $this->assertDatabaseHas('service_reports', [
            'id' => $serviceReport->id,
            'driver_id' => $this->driver->id,
            'vehicle_id' => $this->vehicle->id,
            'customer_id' => $this->customer->id,
            'status' => ServiceReport::STATUS_WAITING_COMPLETION,
            'report_source' => 'emergency_report',
            'service_type' => 'Darurat',
            'problem_category' => 'Darurat',
            'vehicle_condition_photo_path' => 'photos/emergency.jpg',
        ]);

        $this->assertDatabaseHas('emergency_reports', [
            'id' => $emergencyReport->id,
            'follow_up_status' => EmergencyReport::STATUS_SERVICE_CREATED,
            'service_report_id' => $serviceReport->id,
            'processed_by' => $this->serviceAdmin->id,
        ]);

        $this->assertDatabaseHas('vehicles', [
            'id' => $this->vehicle->id,
            'status' => 'Rusak',
        ]);
    }

    public function test_admin_can_resolve_emergency_report_as_info_only(): void
    {
        $emergencyReport = $this->createEmergencyReport();

        $response = $this->actingAs($this->serviceAdmin)
            ->post(route('admin.laporan_darurat.resolve_info', $emergencyReport), [
                'follow_up_notes' => 'Driver sudah dihubungi dan diarahkan kembali ke pool.',
            ]);

        $response->assertRedirect(route('admin.laporan_darurat'));

        $this->assertDatabaseHas('emergency_reports', [
            'id' => $emergencyReport->id,
            'follow_up_status' => EmergencyReport::STATUS_INFO_RESOLVED,
            'follow_up_notes' => 'Driver sudah dihubungi dan diarahkan kembali ke pool.',
            'processed_by' => $this->serviceAdmin->id,
            'service_report_id' => null,
        ]);

        $this->assertDatabaseCount('service_reports', 0);
    }

    private function createEmergencyReport(): EmergencyReport
    {
        return EmergencyReport::create([
            'driver_id' => $this->driver->id,
            'vehicle_id' => $this->vehicle->id,
            'timestamp' => now(),
            'gps_location' => '-6.200000,106.816666',
            'description' => 'Rem kadang hilang saat turunan dan perlu tindakan cepat.',
            'proof_photo_path' => 'photos/emergency.jpg',
        ]);
    }
}
