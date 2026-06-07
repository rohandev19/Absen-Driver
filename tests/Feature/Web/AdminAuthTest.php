<?php

namespace Tests\Feature\Web;

use Tests\TestCase;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;

class AdminAuthTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function admin_can_view_login_page()
    {
        $response = $this->get('/admin/login');

        $response->assertStatus(200)
            ->assertViewIs('auth.admin-login');
    }

    /** @test */
    public function admin_can_login_with_valid_credentials()
    {
        $admin = User::factory()->create([
            'email' => 'admin@test.com',
            'password' => Hash::make('password'),
            'role' => 'master',
        ]);

        $response = $this->post('/admin/login', [
            'email' => 'admin@test.com',
            'password' => 'password',
        ]);

        $response->assertRedirect('/admin/dashboard');
        $this->assertAuthenticatedAs($admin);
    }

    /** @test */
    public function admin_cannot_login_with_invalid_credentials()
    {
        $admin = User::factory()->create([
            'email' => 'admin@test.com',
            'password' => Hash::make('password'),
            'role' => 'master',
        ]);

        $response = $this->post('/admin/login', [
            'email' => 'admin@test.com',
            'password' => 'wrongpassword',
        ]);

        $response->assertSessionHasErrors();
        $this->assertGuest();
    }

    /** @test */
    public function admin_login_is_rate_limited()
    {
        $admin = User::factory()->create([
            'email' => 'admin@test.com',
            'password' => Hash::make('password'),
            'role' => 'master',
        ]);

        // Attempt 6 logins (limit is 5/min)
        for ($i = 0; $i < 6; $i++) {
            $response = $this->post('/admin/login', [
                'email' => 'admin@test.com',
                'password' => 'wrongpassword',
            ]);
        }

        $response->assertStatus(429); // Too Many Requests
    }

    /** @test */
    public function admin_can_logout()
    {
        $admin = User::factory()->create(['role' => 'master']);

        $response = $this->actingAs($admin)
            ->post('/admin/logout');

        $response->assertRedirect('/admin/login');
        $this->assertGuest();
    }

    /** @test */
    public function guest_cannot_access_admin_dashboard()
    {
        $response = $this->get('/admin/dashboard');

        $response->assertRedirect('/admin/login');
    }

    /** @test */
    public function viewer_role_cannot_access_admin_features()
    {
        $viewer = User::factory()->create(['role' => 'viewer']);

        $response = $this->actingAs($viewer)
            ->get('/admin/dashboard');

        $response->assertStatus(403);
    }

    /** @test */
    public function service_admin_can_access_admin_features()
    {
        $serviceAdmin = User::factory()->create(['role' => 'service_admin']);

        $response = $this->actingAs($serviceAdmin)
            ->get('/admin/dashboard');

        $response->assertStatus(200);
    }

    /** @test */
    public function master_can_access_all_admin_features()
    {
        $master = User::factory()->create(['role' => 'master']);

        $response = $this->actingAs($master)
            ->get('/admin/dashboard');

        $response->assertStatus(200);
    }
}
