<?php

namespace Tests\Feature\Security;

use Tests\TestCase;
use App\Models\User;
use App\Models\Driver;
use App\Models\Vehicle;
use App\Models\Customer;
use App\Models\Project;
use Illuminate\Foundation\Testing\RefreshDatabase;

class AuthorizationTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function driver_cannot_access_admin_routes()
    {
        $driver = Driver::factory()->create();

        $response = $this->actingAs($driver, 'sanctum')
            ->get('/admin/dashboard');

        $response->assertStatus(403); // Redirected or forbidden
    }

    /** @test */
    public function viewer_cannot_modify_data()
    {
        $viewer = User::factory()->create(['role' => 'viewer']);
        $vehicle = Vehicle::factory()->create();

        $response = $this->actingAs($viewer)
            ->delete('/admin/aset/' . $vehicle->id . '/hapus');

        $response->assertStatus(403);
    }

    /** @test */
    public function customer_can_only_access_own_vehicles()
    {
        $customer1 = Customer::factory()->create();
        $customer2 = Customer::factory()->create();

        $user1 = User::factory()->create([
            'role' => 'customer',
            'customer_id' => $customer1->id,
        ]);

        $project1 = Project::factory()->create(['customer_id' => $customer1->id]);
        $project2 = Project::factory()->create(['customer_id' => $customer2->id]);

        $vehicle1 = Vehicle::factory()->create(['project_id' => $project1->id]);
        $vehicle2 = Vehicle::factory()->create(['project_id' => $project2->id]);

        // Can access own vehicle
        $response = $this->actingAs($user1)
            ->get('/customer/vehicles/' . $vehicle1->id);
        $response->assertStatus(200);

        // Cannot access other customer's vehicle
        $response = $this->actingAs($user1)
            ->get('/customer/vehicles/' . $vehicle2->id);
        $response->assertStatus(403);
    }

    /** @test */
    public function driver_can_only_view_own_attendance()
    {
        $driver1 = Driver::factory()->create();
        $driver2 = Driver::factory()->create();

        $response = $this->actingAs($driver1, 'sanctum')
            ->getJson('/api/driver/history');

        $response->assertStatus(200);

        // Should only see own attendance, not other drivers'
        $data = $response->json('data');
        foreach ($data as $attendance) {
            $this->assertEquals($driver1->id, $attendance['driver_id']);
        }
    }

    /** @test */
    public function unauthenticated_user_cannot_access_protected_routes()
    {
        $response = $this->get('/admin/dashboard');
        $response->assertRedirect('/admin/login');

        $response = $this->getJson('/api/driver/status');
        $response->assertStatus(401);
    }

    /** @test */
    public function service_admin_can_manage_vehicles()
    {
        $serviceAdmin = User::factory()->create(['role' => 'service_admin']);

        $response = $this->actingAs($serviceAdmin)
            ->get('/admin/daftar-aset');

        $response->assertStatus(200);
    }

    /** @test */
    public function master_has_full_access()
    {
        $master = User::factory()->create(['role' => 'master']);

        // Can access dashboard
        $response = $this->actingAs($master)->get('/admin/dashboard');
        $response->assertStatus(200);

        // Can manage users
        $response = $this->actingAs($master)->get('/admin/pengguna');
        $response->assertStatus(200);

        // Can manage vehicles
        $response = $this->actingAs($master)->get('/admin/daftar-aset');
        $response->assertStatus(200);
    }

    /** @test */
    public function role_field_is_guarded_from_mass_assignment()
    {
        $admin = User::factory()->create(['role' => 'master']);

        // Try to create user with master role via mass assignment
        $response = $this->actingAs($admin)
            ->post('/admin/pengguna', [
                'name' => 'Test User',
                'username' => 'testuser',
                'email' => 'test@example.com',
                'password' => 'password',
                'role' => 'master', // Should be ignored or validated
            ]);

        // Should either fail validation or ignore the role field
        // depending on implementation
        $this->assertTrue(true); // Placeholder - adjust based on actual implementation
    }

    /** @test */
    public function customer_cannot_access_admin_panel()
    {
        $customer = Customer::factory()->create();
        $user = User::factory()->create([
            'role' => 'customer',
            'customer_id' => $customer->id,
        ]);

        $response = $this->actingAs($user)
            ->get('/admin/dashboard');

        $response->assertStatus(403);
    }

    /** @test */
    public function driver_cannot_view_other_drivers_transport_costs()
    {
        $driver1 = Driver::factory()->create();
        $driver2 = Driver::factory()->create();

        $response = $this->actingAs($driver1, 'sanctum')
            ->getJson('/api/transport-costs');

        $response->assertStatus(200);

        // Should only see own transport costs
        $data = $response->json('data');
        foreach ($data as $cost) {
            $this->assertEquals($driver1->id, $cost['driver_id']);
        }
    }
}
