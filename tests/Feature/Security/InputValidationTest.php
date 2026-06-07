<?php

namespace Tests\Feature\Security;

use Tests\TestCase;
use App\Models\Driver;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class InputValidationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('public');
    }

    /** @test */
    public function sql_injection_is_prevented_in_search()
    {
        $admin = User::factory()->create(['role' => 'master']);

        $response = $this->actingAs($admin)
            ->get('/admin/daftar-aset?search=' . urlencode("'; DROP TABLE vehicles; --"));

        $response->assertStatus(200);
        $this->assertTrue(\Illuminate\Support\Facades\Schema::hasTable('vehicles')); // Table should still exist
    }

    /** @test */
    public function xss_is_prevented_in_user_input()
    {
        $driver = Driver::factory()->create();

        $response = $this->actingAs($driver, 'sanctum')
            ->postJson('/api/submit-emergency-report', [
                'gps_location' => '-6.200000, 106.816666',
                'timestamp' => now()->format('Y-m-d H:i:s'),
                'description' => '<script>alert("XSS")</script>',
                'photo' => UploadedFile::fake()->image('emergency.jpg'),
            ]);

        // Should either sanitize or reject
        if ($response->status() === 200) {
            $this->assertDatabaseMissing('emergency_reports', [
                'description' => '<script>alert("XSS")</script>',
            ]);
        } else {
            $this->assertNotEquals(200, $response->status());
        }
    }

    /** @test */
    public function file_upload_validates_mime_type()
    {
        $driver = Driver::factory()->create();

        $response = $this->actingAs($driver, 'sanctum')
            ->postJson('/api/submit-attendance', [
                'plate_number' => 'B1234XYZ',
                'gps_location' => '-6.200000, 106.816666',
                'timestamp' => now()->format('Y-m-d H:i:s'),
                'speedometer_manual' => 50000,
                'selfie_photo' => UploadedFile::fake()->create('malicious.exe', 1000),
                'speedometer_photo' => UploadedFile::fake()->image('speedo.jpg'),
            ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors('selfie_photo');
    }

    /** @test */
    public function file_upload_validates_file_size()
    {
        $driver = Driver::factory()->create();

        $response = $this->actingAs($driver, 'sanctum')
            ->postJson('/api/submit-attendance', [
                'plate_number' => 'B1234XYZ',
                'gps_location' => '-6.200000, 106.816666',
                'timestamp' => now()->format('Y-m-d H:i:s'),
                'speedometer_manual' => 50000,
                'selfie_photo' => UploadedFile::fake()->image('selfie.jpg')->size(3000), // 3MB
                'speedometer_photo' => UploadedFile::fake()->image('speedo.jpg'),
            ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors('selfie_photo');
    }

    /** @test */
    public function gps_coordinates_are_validated()
    {
        $driver = Driver::factory()->create();

        // Invalid format
        $response = $this->actingAs($driver, 'sanctum')
            ->postJson('/api/submit-attendance', [
                'plate_number' => 'B1234XYZ',
                'gps_location' => 'invalid-coordinates',
                'timestamp' => now()->format('Y-m-d H:i:s'),
                'speedometer_manual' => 50000,
                'selfie_photo' => UploadedFile::fake()->image('selfie.jpg'),
                'speedometer_photo' => UploadedFile::fake()->image('speedo.jpg'),
            ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors('gps_location');
    }

    /** @test */
    public function email_format_is_validated()
    {
        $admin = User::factory()->create(['role' => 'master']);

        $response = $this->actingAs($admin)
            ->post('/admin/pengguna', [
                'name' => 'Test User',
                'username' => 'testuser',
                'email' => 'invalid-email',
                'password' => 'password',
                'role' => 'viewer',
            ]);

        $response->assertSessionHasErrors('email');
    }

    /** @test */
    public function numeric_fields_are_validated()
    {
        $admin = User::factory()->create(['role' => 'master']);

        $response = $this->actingAs($admin)
            ->post('/admin/aset/simpan', [
                'plate_number' => 'B1234XYZ',
                'type' => 'Truck',
                'status' => 'Aktif',
                'current_km' => 'not-a-number',
                'service_interval_km' => 5000,
            ]);

        $response->assertSessionHasErrors('current_km');
    }

    /** @test */
    public function date_format_is_validated()
    {
        $admin = User::factory()->create(['role' => 'master']);

        $response = $this->actingAs($admin)
            ->post('/admin/aset/simpan', [
                'plate_number' => 'B1234XYZ',
                'type' => 'Truck',
                'status' => 'Aktif',
                'current_km' => 50000,
                'pajak_stnk_berlaku_sampai' => 'invalid-date',
            ]);

        $response->assertSessionHasErrors('pajak_stnk_berlaku_sampai');
    }

    /** @test */
    public function required_fields_are_enforced()
    {
        $driver = Driver::factory()->create();

        $response = $this->actingAs($driver, 'sanctum')
            ->postJson('/api/submit-attendance', [
                // Missing required fields
            ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors([
                'plate_number',
                'gps_location',
                'timestamp',
                'speedometer_manual',
                'selfie_photo',
                'speedometer_photo',
            ]);
    }

    /** @test */
    public function password_strength_is_enforced()
    {
        $driver = Driver::factory()->create();

        $response = $this->actingAs($driver, 'sanctum')
            ->postJson('/api/change-password', [
                'current_password' => 'OldPassword123!@#',
                'new_password' => 'weak',
                'new_password_confirmation' => 'weak',
            ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors('new_password');
    }

    /** @test */
    public function path_traversal_is_prevented_in_file_access()
    {
        $admin = User::factory()->create(['role' => 'master']);

        $response = $this->actingAs($admin)
            ->get('/storage/photos/../../../etc/passwd');

        $response->assertStatus(404); // Should not allow access
    }
}
