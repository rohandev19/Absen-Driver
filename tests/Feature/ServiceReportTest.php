<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Customer;
use App\Models\Project;
use App\Models\Driver;
use App\Models\Vehicle;
use App\Models\ServiceReport;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ServiceReportTest extends TestCase
{
    use RefreshDatabase;

    private $customer;
    private $project;
    private $driver;
    private $vehicle;
    private $report;
    private $serviceAdmin;
    private $customerUser;

    protected function setUp(): void
    {
        parent::setUp();

        // Create testing services mocks
        $this->mock(\App\Services\ServiceReportDocumentService::class, function ($mock) {
            $mock->shouldReceive('generateCustomerApprovalDocument')->andReturn('service_docs/test_customer.docx');
            $mock->shouldReceive('generateFinanceSubmission')->andReturn('service_docs/test_finance.docx');
        });

        $this->mock(\App\Services\WhatsAppNotificationService::class, function ($mock) {
            $mock->shouldReceive('notifyCustomer')->andReturn(null);
            $mock->shouldReceive('notifyServiceAdmin')->andReturn(null);
        });

        // Set up relation data
        $this->customer = Customer::create([
            'name' => 'PT Test Customer',
            'code' => 'TEST_CUST',
            'contact_person' => 'John Doe',
            'email' => 'customer@test.com',
            'phone' => '08123456789',
            'address' => 'Test Address',
        ]);

        $this->project = Project::create([
            'name' => 'Test Project',
            'code' => 'TP',
            'customer_id' => $this->customer->id,
        ]);

        $this->driver = Driver::create([
            'full_name' => 'Test Driver',
            'driver_id_nik' => 'DRV001',
            'nik_ktp' => '1234567890123456',
            'password' => bcrypt('password'),
            'project_id' => $this->project->id,
        ]);

        $this->vehicle = Vehicle::create([
            'plate_number' => 'B 1234 ABC',
            'type' => 'Truck',
            'project_id' => $this->project->id,
            'current_km' => 10000,
            'service_interval_km' => 5000,
            'last_service_km' => 5000,
        ]);

        $this->report = ServiceReport::create([
            'driver_id' => $this->driver->id,
            'vehicle_id' => $this->vehicle->id,
            'customer_id' => $this->customer->id,
            'timestamp' => now(),
            'gps_location' => '-6.200000,106.816666',
            'description' => 'Ban bocor dan perlu ganti oli',
            'vehicle_condition_photo_path' => 'photos/test_condition.png',
            'receipt_photo_path' => 'receipts/test_receipt.png',
            'status' => 'pending',
        ]);

        // Users
        $this->serviceAdmin = User::factory()->create([
            'role' => 'service_admin',
            'email' => 'admin@test.com',
        ]);

        $this->customerUser = User::factory()->create([
            'role' => 'customer',
            'email' => 'customer_user@test.com',
            'customer_id' => $this->customer->id,
        ]);
    }

    /**
     * Test admin can view service reports.
     */
    public function test_admin_can_view_service_reports_index_and_show()
    {
        $this->actingAs($this->serviceAdmin);

        $response = $this->get(route('admin.service.index'));
        $response->assertStatus(200);
        $response->assertViewHas('reports');

        $response = $this->get(route('admin.service.show', $this->report->id));
        $response->assertStatus(200);
        $response->assertViewHas('report');

        echo "\n✅ Admin can view service reports index and show pages (CORRECT)\n";
    }

    /**
     * Test admin can approve a service report with signature and signer details.
     */
    public function test_admin_can_approve_service_report()
    {
        $this->actingAs($this->serviceAdmin);

        // Dummy base64 signature
        $dummySignature = 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mNk+M9QDwADhgGAWjR9awAAAABJRU5ErkJggg==';

        $response = $this->post(route('admin.service.approve', $this->report->id), [
            'admin_notes' => 'Tolong customer segera tanda tangan.',
            'signature' => $dummySignature,
            'signer_name' => 'Alex Admin',
            'signer_role' => 'Service Supervisor',
            'workshop_name' => 'Bengkel Test',
            'invoice_number' => 'INV-123',
            'service_cost' => 150000,
            'sparepart_cost' => 350000,
            'total_cost' => 500000,
        ]);

        $response->assertRedirect(route('admin.service.index'));
        $response->assertSessionHas('success');

        $this->report->refresh();
        $this->assertEquals(ServiceReport::STATUS_PENDING_CUSTOMER, $this->report->status);
        $this->assertEquals($this->serviceAdmin->id, $this->report->approved_by_admin_id);
        $this->assertEquals('Alex Admin', $this->report->admin_signer_name);
        $this->assertEquals('Service Supervisor', $this->report->admin_signer_role);
        $this->assertNotNull($this->report->admin_signature_path);

        echo "\n✅ Admin can approve a service report with digital signature (CORRECT)\n";
    }

    /**
     * Test admin can reject a service report.
     */
    public function test_admin_can_reject_service_report()
    {
        $this->actingAs($this->serviceAdmin);

        $response = $this->post(route('admin.service.reject', $this->report->id), [
            'rejected_reason' => 'Foto kuitansi buram.',
        ]);

        $response->assertRedirect(route('admin.service.index'));
        $response->assertSessionHas('success');

        $this->report->refresh();
        $this->assertEquals(ServiceReport::STATUS_REJECTED, $this->report->status);
        $this->assertEquals('Foto kuitansi buram.', $this->report->rejected_reason);

        echo "\n✅ Admin can reject a service report with a reason (CORRECT)\n";
    }

    /**
     * Test admin cannot approve already processed service reports.
     */
    public function test_admin_cannot_approve_non_pending_service_report()
    {
        $this->actingAs($this->serviceAdmin);

        $this->report->update(['status' => ServiceReport::STATUS_PENDING_CUSTOMER]);

        $dummySignature = 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mNk+M9QDwADhgGAWjR9awAAAABJRU5ErkJggg==';

        $response = $this->post(route('admin.service.approve', $this->report->id), [
            'admin_notes' => 'Double approve.',
            'signature' => $dummySignature,
            'signer_name' => 'Alex Admin',
            'signer_role' => 'Service Supervisor',
            'workshop_name' => 'Bengkel Test',
            'invoice_number' => 'INV-123',
            'service_cost' => 150000,
            'sparepart_cost' => 350000,
            'total_cost' => 500000,
        ]);

        $response->assertStatus(302);
        $response->assertSessionHas('error', 'Laporan ini sudah diproses sebelumnya.');

        echo "\n✅ Admin cannot double-process a service report (CORRECT)\n";
    }

    /**
     * Test customer can see their approvals list and details.
     */
    public function test_customer_can_view_approvals()
    {
        $this->actingAs($this->customerUser);

        // Put report in STATUS_PENDING_CUSTOMER so it shows on customer portal
        $this->report->update(['status' => ServiceReport::STATUS_PENDING_CUSTOMER]);

        $response = $this->get(route('customer.approve.index'));
        $response->assertStatus(200);
        $response->assertViewHas('reports');

        $response = $this->get(route('customer.approve.show', $this->report->id));
        $response->assertStatus(200);
        $response->assertViewHas('report');

        echo "\n✅ Customer can view their pending approvals index and show pages (CORRECT)\n";
    }

    /**
     * Test customer can upload digital signature to approve.
     */
    public function test_customer_can_upload_signature_to_approve()
    {
        $this->actingAs($this->customerUser);

        // Pre-approve by admin
        $this->report->update([
            'status' => ServiceReport::STATUS_PENDING_CUSTOMER,
            'admin_signer_name' => 'Alex Admin',
            'admin_signer_role' => 'Admin Service',
        ]);

        // Dummy base64 signature
        $dummySignature = 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mNk+M9QDwADhgGAWjR9awAAAABJRU5ErkJggg==';

        $response = $this->post(route('customer.approve.upload', $this->report->id), [
            'signature' => $dummySignature,
            'signer_name' => 'Charlie Customer',
            'signer_role' => 'Fleet Manager',
        ]);

        $response->assertRedirect(route('customer.approve.success', $this->report->id));
        $response->assertSessionHas('success');

        $this->report->refresh();
        $this->assertEquals(ServiceReport::STATUS_APPROVED_CUSTOMER, $this->report->status);
        $this->assertEquals($this->customerUser->id, $this->report->approved_by_customer_id);
        $this->assertEquals('Charlie Customer', $this->report->customer_signer_name);
        $this->assertEquals('Fleet Manager', $this->report->customer_signer_role);
        $this->assertNotNull($this->report->customer_signature_path);

        echo "\n✅ Customer can approve service report with digital signature (CORRECT)\n";
    }

    /**
     * Test customer cannot access another customer's service report.
     */
    public function test_customer_cannot_access_other_customers_report()
    {
        // Create another customer and customer user
        $otherCustomer = Customer::create([
            'name' => 'PT Another Customer',
            'code' => 'ANOTHER',
        ]);

        $otherCustomerUser = User::factory()->create([
            'role' => 'customer',
            'email' => 'other@test.com',
            'customer_id' => $otherCustomer->id,
        ]);

        $this->actingAs($otherCustomerUser);

        $response = $this->get(route('customer.approve.show', $this->report->id));
        $response->assertStatus(404); // Should not be found because query is filtered by customer_id

        echo "\n✅ Customer cannot view/approve other customer's service reports (CORRECT)\n";
    }

    /**
     * Test driver cannot access admin service pages.
     */
    public function test_driver_denied_from_admin_service_pages()
    {
        $driverUser = User::factory()->create([
            'role' => 'driver',
            'email' => 'driver_user@test.com',
        ]);

        $this->actingAs($driverUser);

        $response = $this->get(route('admin.service.index'));
        $response->assertStatus(403);

        $response = $this->get(route('admin.service.show', $this->report->id));
        $response->assertStatus(403);

        echo "\n✅ Driver is blocked from entering admin service pages (CORRECT)\n";
    }
}
