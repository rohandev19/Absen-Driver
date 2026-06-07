<?php

namespace Tests\Feature\Api;

use Tests\TestCase;
use App\Models\Driver;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;

class AuthenticationTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function driver_can_login_with_valid_credentials()
    {
        $driver = Driver::factory()->create([
            'driver_id_nik' => 'DRV001',
            'password' => Hash::make('password123'),
        ]);

        $response = $this->postJson('/api/login', [
            'driver_id' => 'DRV001',
            'password' => 'password123',
        ]);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'status',
                'message',
                'data' => ['driver_id', 'full_name'],
                'token',
                'sim_alert',
            ]);
    }

    /** @test */
    public function driver_cannot_login_with_invalid_credentials()
    {
        $driver = Driver::factory()->create([
            'driver_id_nik' => 'DRV001',
            'password' => Hash::make('password123'),
        ]);

        $response = $this->postJson('/api/login', [
            'driver_id' => 'DRV001',
            'password' => 'wrongpassword',
        ]);

        $response->assertStatus(422)
            ->assertJson([
                'status' => 'error',
            ]);
    }

    /** @test */
    public function login_is_rate_limited()
    {
        $driver = Driver::factory()->create([
            'driver_id_nik' => 'DRV001',
            'password' => Hash::make('password123'),
        ]);

        // Attempt 11 logins (limit is 10/min)
        for ($i = 0; $i < 11; $i++) {
            $response = $this->postJson('/api/login', [
                'driver_id' => 'DRV001',
                'password' => 'wrongpassword',
            ]);
        }

        $response->assertStatus(429); // Too Many Requests
    }

    /** @test */
    public function driver_can_logout()
    {
        $driver = Driver::factory()->create();
        $token = $driver->createToken('test-token')->plainTextToken;

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->postJson('/api/logout');

        $response->assertStatus(200)
            ->assertJson(['status' => 'success']);

        // Token should be deleted
        $this->assertDatabaseMissing('personal_access_tokens', [
            'tokenable_id' => $driver->id,
        ]);
    }

    /** @test */
    public function driver_can_change_password()
    {
        $driver = Driver::factory()->create([
            'password' => Hash::make('OldPassword123!@#'),
        ]);
        $token = $driver->createToken('test-token')->plainTextToken;

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->postJson('/api/change-password', [
                'current_password' => 'OldPassword123!@#',
                'new_password' => 'NewPassword456!@#',
                'new_password_confirmation' => 'NewPassword456!@#',
            ]);

        $response->assertStatus(200)
            ->assertJson(['status' => 'success']);

        // Verify password was changed
        $driver->refresh();
        $this->assertTrue(Hash::check('NewPassword456!@#', $driver->password));
    }

    /** @test */
    public function sim_expiry_warning_is_shown_on_login()
    {
        $driver = Driver::factory()->create([
            'driver_id_nik' => 'DRV001',
            'password' => Hash::make('password123'),
            'sim_expiry_date' => Carbon::now()->addDays(15), // 15 days left
        ]);

        $response = $this->postJson('/api/login', [
            'driver_id' => 'DRV001',
            'password' => 'password123',
        ]);

        $response->assertStatus(200)
            ->assertJsonPath('sim_alert.status', 'warning');
    }

    /** @test */
    public function expired_sim_is_detected_on_login()
    {
        $driver = Driver::factory()->create([
            'driver_id_nik' => 'DRV001',
            'password' => Hash::make('password123'),
            'sim_expiry_date' => Carbon::now()->subDays(5), // Expired 5 days ago
        ]);

        $response = $this->postJson('/api/login', [
            'driver_id' => 'DRV001',
            'password' => 'password123',
        ]);

        $response->assertStatus(200)
            ->assertJsonPath('sim_alert.status', 'danger')
            ->assertJsonPath('sim_alert.is_expired', true);
    }

    /** @test */
    public function single_device_login_deletes_old_tokens()
    {
        $driver = Driver::factory()->create([
            'driver_id_nik' => 'DRV001',
            'password' => Hash::make('password123'),
        ]);

        // Create old token
        $oldToken = $driver->createToken('old-device')->plainTextToken;

        // Login from new device
        $response = $this->postJson('/api/login', [
            'driver_id' => 'DRV001',
            'password' => 'password123',
        ]);

        $response->assertStatus(200);

        // Old token should be deleted
        $this->assertCount(1, $driver->tokens);
    }
}
