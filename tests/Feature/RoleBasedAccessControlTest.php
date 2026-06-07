<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

class RoleBasedAccessControlTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test that customer cannot access admin routes
     */
    public function test_customer_cannot_access_admin_routes()
    {
        // Create a customer user
        $customer = User::factory()->create([
            'role' => 'customer',
            'email' => 'customer@test.com',
        ]);

        // Login as customer
        $this->actingAs($customer);

        // Try to access admin dashboard
        $response = $this->get('/admin/dashboard');
        $response->assertStatus(403); // Should be forbidden

        // Try to access admin pengguna
        $response = $this->get('/admin/pengguna');
        $response->assertStatus(403); // Should be forbidden

        // Try to access admin project
        $response = $this->get('/admin/project');
        $response->assertStatus(403); // Should be forbidden

        echo "\n✅ Customer CANNOT access admin routes (CORRECT)\n";
    }

    /**
     * Test that master can access admin routes
     */
    public function test_master_can_access_admin_routes()
    {
        // Create a master user
        $master = User::factory()->create([
            'role' => 'master',
            'email' => 'master@test.com',
        ]);

        // Login as master
        $this->actingAs($master);

        // Try to access admin dashboard
        $response = $this->get('/admin/dashboard');
        $response->assertStatus(200); // Should be OK

        echo "\n✅ Master CAN access admin routes (CORRECT)\n";
    }

    /**
     * Test that service_admin can access admin routes
     */
    public function test_service_admin_can_access_admin_routes()
    {
        // Create a service_admin user
        $serviceAdmin = User::factory()->create([
            'role' => 'service_admin',
            'email' => 'serviceadmin@test.com',
        ]);

        // Login as service_admin
        $this->actingAs($serviceAdmin);

        // Try to access admin dashboard
        $response = $this->get('/admin/dashboard');
        $response->assertStatus(200); // Should be OK

        echo "\n✅ Service Admin CAN access admin routes (CORRECT)\n";
    }

    /**
     * Test that driver cannot access admin routes
     */
    public function test_driver_cannot_access_admin_routes()
    {
        // Create a driver user
        $driver = User::factory()->create([
            'role' => 'driver',
            'email' => 'driver@test.com',
        ]);

        // Login as driver
        $this->actingAs($driver);

        // Try to access admin dashboard
        $response = $this->get('/admin/dashboard');
        $response->assertStatus(403); // Should be forbidden

        echo "\n✅ Driver CANNOT access admin routes (CORRECT)\n";
    }

    /**
     * Test that master/service_admin cannot access customer routes
     */
    public function test_admin_cannot_access_customer_routes()
    {
        // Create a master user
        $master = User::factory()->create([
            'role' => 'master',
            'email' => 'master@test.com',
        ]);

        // Login as master
        $this->actingAs($master);

        // Try to access customer approve
        $response = $this->get('/customer/approve');
        $response->assertStatus(403); // Should be forbidden

        echo "\n✅ Admin CANNOT access customer routes (CORRECT)\n";
    }

    /**
     * Test that customer can access customer routes
     */
    public function test_customer_can_access_customer_routes()
    {
        // Create customer record
        $customerRecord = \App\Models\Customer::factory()->create();

        // Create a customer user
        $customer = User::factory()->create([
            'role' => 'customer',
            'email' => 'customer@test.com',
            'customer_id' => $customerRecord->id,
        ]);

        // Login as customer
        $this->actingAs($customer);

        // Try to access customer approve
        $response = $this->get('/customer/approve');
        $response->assertStatus(200); // Should be OK

        echo "\n✅ Customer CAN access customer routes (CORRECT)\n";
    }
}
