<?php

namespace Tests\Feature\Api;

use App\Models\Attendance;
use App\Models\Driver;
use App\Models\Vehicle;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class DriverGuidanceTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function guidance_prompts_driver_to_start_check_in_when_not_on_duty(): void
    {
        $driver = Driver::factory()->create();

        $response = $this->actingAs($driver, 'sanctum')
            ->getJson('/api/driver/guidance');

        $response->assertOk()
            ->assertJsonPath('status', 'success')
            ->assertJsonPath('data.duty.is_on_duty', false)
            ->assertJsonPath('data.primary_instruction.type', 'check_in')
            ->assertJsonPath('data.low_device_mode.recommended', true);

        $this->assertEquals('start_check_in', $response->json('data.quick_actions.0.action'));
    }

    #[Test]
    public function guidance_warns_driver_to_clock_out_after_eight_hours(): void
    {
        $driver = Driver::factory()->create(['is_on_duty' => true]);
        $vehicle = Vehicle::factory()->create(['plate_number' => 'B1234XYZ']);

        Attendance::factory()->create([
            'driver_id' => $driver->id,
            'vehicle_id' => $vehicle->id,
            'time_in' => now()->subHours(9),
            'time_out' => null,
        ]);

        $response = $this->actingAs($driver, 'sanctum')
            ->getJson('/api/driver/guidance');

        $response->assertOk()
            ->assertJsonPath('data.duty.is_on_duty', true)
            ->assertJsonPath('data.duty.plate_number', 'B1234XYZ')
            ->assertJsonPath('data.primary_instruction.type', 'clock_out_reminder')
            ->assertJsonPath('data.quick_actions.0.action', 'open_clock_out');

        $alertTypes = collect($response->json('data.alerts'))->pluck('type');
        $this->assertTrue($alertTypes->contains('clock_out_reminder'));
    }

    #[Test]
    public function guidance_explains_manual_vehicle_pending_verification(): void
    {
        $driver = Driver::factory()->create(['is_on_duty' => true]);
        $vehicle = Vehicle::factory()->create([
            'plate_number' => 'B9804UCY',
            'status' => 'Pending Verifikasi',
            'is_temporary' => true,
            'verification_status' => 'pending',
            'source' => 'driver_manual',
        ]);

        Attendance::factory()->create([
            'driver_id' => $driver->id,
            'vehicle_id' => $vehicle->id,
            'vehicle_entry_method' => 'manual',
            'vehicle_verification_status' => 'pending',
            'manual_vehicle_plate' => 'B9804UCY',
            'time_in' => now()->subHour(),
            'time_out' => null,
        ]);

        $response = $this->actingAs($driver, 'sanctum')
            ->getJson('/api/driver/guidance');

        $response->assertOk()
            ->assertJsonPath('data.duty.vehicle_entry_method', 'manual')
            ->assertJsonPath('data.duty.vehicle_verification_status', 'pending');

        $alertTypes = collect($response->json('data.alerts'))->pluck('type');
        $this->assertTrue($alertTypes->contains('manual_vehicle_pending'));
    }

    #[Test]
    public function guidance_prompts_transport_cost_after_driver_checked_out_today(): void
    {
        $driver = Driver::factory()->create(['is_on_duty' => false]);
        $vehicle = Vehicle::factory()->create();

        Attendance::factory()->create([
            'driver_id' => $driver->id,
            'vehicle_id' => $vehicle->id,
            'time_in' => now()->subHours(4),
            'time_out' => now()->subMinutes(15),
        ]);

        $response = $this->actingAs($driver, 'sanctum')
            ->getJson('/api/driver/guidance');

        $response->assertOk()
            ->assertJsonPath('data.duty.is_on_duty', false)
            ->assertJsonPath('data.primary_instruction.type', 'transport_cost');

        $alertTypes = collect($response->json('data.alerts'))->pluck('type');
        $this->assertTrue($alertTypes->contains('transport_cost_ready'));
    }
}
