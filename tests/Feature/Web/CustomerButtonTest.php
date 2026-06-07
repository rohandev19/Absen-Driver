<?php

namespace Tests\Feature\Web;

use Tests\TestCase;
use App\Models\User;
use App\Models\Customer;
use App\Models\Vehicle;
use App\Models\Project;
use App\Models\ServiceReport;
use App\Models\VehicleComponent;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;

class CustomerButtonTest extends TestCase
{
    use RefreshDatabase;

    protected $customer;
    protected $customerUser;
    protected $project;
    protected $vehicle;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('public');

        $this->customer = Customer::factory()->create();
        $this->customerUser = User::factory()->create([
            'role' => 'customer',
            'customer_id' => $this->customer->id,
        ]);
        $this->project = Project::factory()->create([
            'customer_id' => $this->customer->id,
        ]);
        $this->vehicle = Vehicle::factory()->create([
            'project_id' => $this->project->id,
        ]);
    }

    // ==========================================
    // DASHBOARD BUTTONS
    // ==========================================

    /** @test */
    public function customer_dashboard_view_all_vehicles_button_works()
    {
        $response = $this->actingAs($this->customerUser)
            ->get('/customer/dashboard');

        $response->assertStatus(200)
            ->assertSee('Lihat Semua Unit')
            ->assertSee(route('customer.vehicles'));
    }

    /** @test */
    public function customer_dashboard_view_vehicle_detail_button_works()
    {
        $response = $this->actingAs($this->customerUser)
            ->get('/customer/dashboard');

        $response->assertStatus(200)
            ->assertSee('Detail');
    }

    // ==========================================
    // VEHICLE LIST BUTTONS
    // ==========================================

    /** @test */
    public function customer_vehicle_list_search_button_works()
    {
        $response = $this->actingAs($this->customerUser)
            ->get('/customer/vehicles?search=' . $this->vehicle->plate_number);

        $response->assertStatus(200)
            ->assertSee($this->vehicle->plate_number);
    }

    /** @test */
    public function customer_vehicle_list_reset_search_button_works()
    {
        $response = $this->actingAs($this->customerUser)
            ->get('/customer/vehicles?search=test');

        $response->assertStatus(200)
            ->assertSee('Reset Pencarian');
    }

    /** @test */
    public function customer_vehicle_list_view_detail_button_works()
    {
        $response = $this->actingAs($this->customerUser)
            ->get('/customer/vehicles/' . $this->vehicle->id);

        $response->assertStatus(200);
    }

    /** @test */
    public function customer_vehicle_list_certificate_button_works_when_healthy()
    {
        // Set vehicle health score >= 75
        $this->vehicle->update(['current_km' => 45000]);

        $response = $this->actingAs($this->customerUser)
            ->get('/customer/vehicles');

        $response->assertStatus(200)
            ->assertSee('Sertifikat');
    }

    /** @test */
    public function customer_vehicle_list_certificate_button_disabled_when_unhealthy()
    {
        // Set vehicle health score < 75
        $this->vehicle->update(['current_km' => 60000]);
        VehicleComponent::factory()->create([
            'vehicle_id' => $this->vehicle->id,
            'last_replacement_km' => 40000,
            'replacement_interval_km' => 10000,
        ]);

        $response = $this->actingAs($this->customerUser)
            ->get('/customer/vehicles');

        $response->assertStatus(200)
            ->assertSee('disabled');
    }

    // ==========================================
    // VEHICLE DETAIL BUTTONS
    // ==========================================

    /** @test */
    public function customer_vehicle_detail_back_button_works()
    {
        $response = $this->actingAs($this->customerUser)
            ->get('/customer/vehicles/' . $this->vehicle->id);

        $response->assertStatus(200)
            ->assertSee('Kembali')
            ->assertSee(route('customer.vehicles'));
    }

    /** @test */
    public function customer_vehicle_detail_download_certificate_button_works()
    {
        // Set vehicle healthy
        $this->vehicle->update(['current_km' => 45000]);

        $response = $this->actingAs($this->customerUser)
            ->get('/customer/vehicles/' . $this->vehicle->id);

        $response->assertStatus(200)
            ->assertSee('Unduh Sertifikat');
    }

    /** @test */
    public function customer_vehicle_certificate_print_button_works()
    {
        $response = $this->actingAs($this->customerUser)
            ->get('/customer/vehicles/' . $this->vehicle->id . '/certificate');

        $response->assertStatus(200)
            ->assertSee('Cetak / Simpan PDF')
            ->assertSee('window.print()');
    }

    // ==========================================
    // APPROVAL BUTTONS
    // ==========================================

    /** @test */
    public function customer_approval_list_view_detail_button_works()
    {
        $serviceReport = ServiceReport::factory()->create([
            'customer_id' => $this->customer->id,
            'vehicle_id' => $this->vehicle->id,
            'status' => ServiceReport::STATUS_PENDING_CUSTOMER,
        ]);

        $response = $this->actingAs($this->customerUser)
            ->get('/customer/approve');

        $response->assertStatus(200)
            ->assertSee('Detail');
    }

    /** @test */
    public function customer_approval_detail_back_button_works()
    {
        $serviceReport = ServiceReport::factory()->create([
            'customer_id' => $this->customer->id,
            'vehicle_id' => $this->vehicle->id,
            'status' => ServiceReport::STATUS_PENDING_CUSTOMER,
        ]);

        $response = $this->actingAs($this->customerUser)
            ->get('/customer/approve/' . $serviceReport->id);

        $response->assertStatus(200)
            ->assertSee('Kembali')
            ->assertSee(route('customer.approve.index'));
    }

    /** @test */
    public function customer_approval_detail_print_button_works()
    {
        $serviceReport = ServiceReport::factory()->create([
            'customer_id' => $this->customer->id,
            'vehicle_id' => $this->vehicle->id,
            'status' => ServiceReport::STATUS_PENDING_CUSTOMER,
        ]);

        $response = $this->actingAs($this->customerUser)
            ->get('/customer/approve/' . $serviceReport->id);

        $response->assertStatus(200)
            ->assertSee('Cetak Ringkasan')
            ->assertSee('window.print()');
    }

    /** @test */
    public function customer_approval_approve_button_works()
    {
        $serviceReport = ServiceReport::factory()->create([
            'customer_id' => $this->customer->id,
            'vehicle_id' => $this->vehicle->id,
            'status' => ServiceReport::STATUS_PENDING_CUSTOMER,
        ]);

        $response = $this->actingAs($this->customerUser)
            ->get('/customer/approve/' . $serviceReport->id);

        $response->assertStatus(200)
            ->assertSee('Konfirmasi Laporan');
    }

    /** @test */
    public function customer_approval_download_document_button_works()
    {
        $serviceReport = ServiceReport::factory()->create([
            'customer_id' => $this->customer->id,
            'vehicle_id' => $this->vehicle->id,
            'status' => ServiceReport::STATUS_PENDING_CUSTOMER,
            'customer_word_path' => 'service_reports/customer_doc.docx',
        ]);

        $response = $this->actingAs($this->customerUser)
            ->get('/customer/approve/' . $serviceReport->id . '/download');

        $response->assertStatus(200);
    }

    /** @test */
    public function customer_approval_upload_signed_document_button_works()
    {
        $serviceReport = ServiceReport::factory()->create([
            'customer_id' => $this->customer->id,
            'vehicle_id' => $this->vehicle->id,
            'status' => ServiceReport::STATUS_PENDING_CUSTOMER,
        ]);

        $response = $this->actingAs($this->customerUser)
            ->post('/customer/approve/' . $serviceReport->id . '/upload', [
                'signer_name' => 'John Doe',
                'signer_role' => 'Manager',
                'signature' => 'data:image/png;base64,' . base64_encode('signature'),
            ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('service_reports', [
            'id' => $serviceReport->id,
            'status' => 'approved_customer',
        ]);
    }

    // ==========================================
    // PROFILE BUTTONS
    // ==========================================

    /** @test */
    public function customer_profile_view_button_works()
    {
        $response = $this->actingAs($this->customerUser)
            ->get('/customer/profile');

        $response->assertStatus(200)
            ->assertSee($this->customerUser->name);
    }

    /** @test */
    public function customer_profile_change_password_button_works()
    {
        $response = $this->actingAs($this->customerUser)
            ->get('/customer/profile/change-password');

        $response->assertStatus(200)
            ->assertSee('Ganti Password');
    }

    /** @test */
    public function customer_profile_update_password_button_works()
    {
        $response = $this->actingAs($this->customerUser)
            ->post('/customer/profile/change-password', [
                'current_password' => 'password',
                'new_password' => 'NewPassword123!@#',
                'new_password_confirmation' => 'NewPassword123!@#',
            ]);

        $response->assertRedirect('/customer/profile');
    }

    // ==========================================
    // NAVIGATION BUTTONS
    // ==========================================

    /** @test */
    public function customer_sidebar_dashboard_link_works()
    {
        $response = $this->actingAs($this->customerUser)
            ->get('/customer/dashboard');

        $response->assertStatus(200)
            ->assertSee('Dashboard');
    }

    /** @test */
    public function customer_sidebar_vehicles_link_works()
    {
        $response = $this->actingAs($this->customerUser)
            ->get('/customer/vehicles');

        $response->assertStatus(200);
    }

    /** @test */
    public function customer_sidebar_approvals_link_works()
    {
        $response = $this->actingAs($this->customerUser)
            ->get('/customer/approve');

        $response->assertStatus(200);
    }

    /** @test */
    public function customer_sidebar_profile_link_works()
    {
        $response = $this->actingAs($this->customerUser)
            ->get('/customer/profile');

        $response->assertStatus(200);
    }

    /** @test */
    public function customer_sidebar_about_link_works()
    {
        $response = $this->actingAs($this->customerUser)
            ->get('/customer/about');

        $response->assertStatus(200);
    }

    /** @test */
    public function customer_sidebar_privacy_link_works()
    {
        $response = $this->actingAs($this->customerUser)
            ->get('/customer/privacy');

        $response->assertStatus(200);
    }

    /** @test */
    public function customer_logout_button_works()
    {
        $response = $this->actingAs($this->customerUser)
            ->post('/admin/logout');

        $response->assertRedirect('/admin/login');
        $this->assertGuest();
    }

    // ==========================================
    // FILTER & SORT BUTTONS
    // ==========================================

    /** @test */
    public function customer_vehicle_filter_by_status_works()
    {
        $response = $this->actingAs($this->customerUser)
            ->get('/customer/vehicles?status=Aktif');

        $response->assertStatus(200);
    }

    /** @test */
    public function customer_vehicle_sort_by_health_works()
    {
        $response = $this->actingAs($this->customerUser)
            ->get('/customer/vehicles?sort=health');

        $response->assertStatus(200);
    }

    // ==========================================
    // MODAL BUTTONS
    // ==========================================

    /** @test */
    public function customer_approval_modal_close_button_works()
    {
        $serviceReport = ServiceReport::factory()->create([
            'customer_id' => $this->customer->id,
            'vehicle_id' => $this->vehicle->id,
            'status' => ServiceReport::STATUS_PENDING_CUSTOMER,
        ]);

        $response = $this->actingAs($this->customerUser)
            ->get('/customer/approve/' . $serviceReport->id);

        $response->assertStatus(200)
            ->assertSee('data-bs-dismiss="modal"', false);
    }

    // ==========================================
    // LANGUAGE TOGGLE BUTTON
    // ==========================================

    /** @test */
    public function customer_privacy_language_toggle_button_works()
    {
        $response = $this->actingAs($this->customerUser)
            ->get('/customer/privacy');

        $response->assertStatus(200)
            ->assertSee('toggleLanguage()');
    }

    // ==========================================
    // AUTHORIZATION TESTS
    // ==========================================

    /** @test */
    public function customer_cannot_access_other_customer_vehicles()
    {
        $otherCustomer = Customer::factory()->create();
        $otherProject = Project::factory()->create(['customer_id' => $otherCustomer->id]);
        $otherVehicle = Vehicle::factory()->create(['project_id' => $otherProject->id]);

        $response = $this->actingAs($this->customerUser)
            ->get('/customer/vehicles/' . $otherVehicle->id);

        $response->assertStatus(403);
    }

    /** @test */
    public function customer_cannot_approve_other_customer_service_reports()
    {
        $otherCustomer = Customer::factory()->create();
        $otherServiceReport = ServiceReport::factory()->create([
            'customer_id' => $otherCustomer->id,
        ]);

        $response = $this->actingAs($this->customerUser)
            ->get('/customer/approve/' . $otherServiceReport->id);

        $response->assertStatus(404);
    }
}
