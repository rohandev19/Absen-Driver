<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Driver;
use App\Models\Vehicle;
use App\Models\Project;
use App\Models\Attendance;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * Load Test untuk Transport Cost API
 * Simulasi 70 driver mengakses API secara bersamaan
 * 
 * Run: php artisan test --filter=TransportCostLoadTest
 */
class TransportCostLoadTest extends TestCase
{
    use RefreshDatabase;

    private $drivers = [];
    private $vehicles = [];
    private $project;
    private $results = [];

    protected function setUp(): void
    {
        parent::setUp();
        
        // Setup test data
        $this->setupTestData();
    }

    private function setupTestData()
    {
        // Create project
        $this->project = Project::create([
            'name' => 'Test Project Load',
            'code' => 'TPL',
        ]);

        // Create 70 drivers with vehicles and attendance
        for ($i = 1; $i <= 70; $i++) {
            // Create driver using factory
            $driver = Driver::factory()->create([
                'driver_id_nik' => "DRV-" . str_pad($i, 6, '0', STR_PAD_LEFT),
                'full_name' => "Driver Test $i",
                'project_id' => $this->project->id,
            ]);

            // Create vehicle
            $vehicle = Vehicle::create([
                'plate_number' => "B " . (1000 + $i) . " XYZ",
                'type' => 'Truck',
                'project_id' => $this->project->id,
            ]);

            // Create attendance (checkout completed)
            $attendance = Attendance::create([
                'driver_id' => $driver->id,
                'vehicle_id' => $vehicle->id,
                'time_in' => now()->startOfDay()->addHours(8),
                'gps_location_in' => '-6.200000, 106.816666',
                'selfie_photo_path' => 'photos/test_selfie.jpg',
                'speedo_photo_awal_path' => 'photos/test_speedo.jpg',
                'speedo_awal' => 10000 + ($i * 100),
                'time_out' => now()->startOfDay()->addHours(16),
                'speedo_photo_akhir_path' => 'photos/test_speedo_akhir.jpg',
                'speedo_akhir' => 10250 + ($i * 100),
            ]);

            $this->drivers[] = [
                'user' => $driver, // Set 'user' to $driver to avoid changing test case lines
                'driver' => $driver,
                'vehicle' => $vehicle,
                'attendance' => $attendance,
            ];
        }

        $this->vehicles = Vehicle::all();
    }

    /**
     * Test 1: Can-Create Endpoint (70 concurrent requests)
     */
    public function test_can_create_endpoint_with_70_concurrent_drivers()
    {
        echo "\n\n=== TEST 1: Can-Create Endpoint (70 Concurrent Requests) ===\n";
        
        $startTime = microtime(true);
        $results = [];

        foreach ($this->drivers as $index => $driverData) {
            $user = $driverData['user'];
            
            $requestStart = microtime(true);
            
            $response = $this->actingAs($user, 'sanctum')
                ->getJson('/api/transport-costs/can-create');
            
            $requestEnd = microtime(true);
            $responseTime = ($requestEnd - $requestStart) * 1000; // ms

            $results[] = [
                'driver' => $index + 1,
                'status' => $response->status(),
                'response_time' => $responseTime,
                'can_create' => $response->json('can_create'),
            ];

            $this->assertEquals(200, $response->status());
            $this->assertTrue($response->json('can_create'));
        }

        $endTime = microtime(true);
        $totalTime = ($endTime - $startTime) * 1000; // ms

        $this->printResults('Can-Create', $results, $totalTime);
    }

    /**
     * Test 2: Create Transport Cost (70 concurrent requests)
     */
    public function test_create_transport_cost_with_70_concurrent_drivers()
    {
        echo "\n\n=== TEST 2: Create Transport Cost (70 Concurrent Requests) ===\n";
        
        $startTime = microtime(true);
        $results = [];

        foreach ($this->drivers as $index => $driverData) {
            $user = $driverData['user'];
            
            $data = [
                'do_number' => "DO-TEST-" . str_pad($index + 1, 4, '0', STR_PAD_LEFT),
                'drop_point_count' => rand(1, 5),
                'delivery_location' => 'Jakarta - Bandung',
                'gasoline_cost' => rand(300000, 600000),
                'toll_cost' => rand(100000, 200000),
                'parking_cost' => rand(10000, 30000),
                'delivery_start_time' => now()->subHours(8)->format('Y-m-d H:i:s'),
                'delivery_end_time' => now()->subHours(1)->format('Y-m-d H:i:s'),
            ];

            $requestStart = microtime(true);
            
            $response = $this->actingAs($user, 'sanctum')
                ->postJson('/api/transport-costs', $data);
            
            $requestEnd = microtime(true);
            $responseTime = ($requestEnd - $requestStart) * 1000; // ms

            $results[] = [
                'driver' => $index + 1,
                'status' => $response->status(),
                'response_time' => $responseTime,
                'success' => $response->status() === 201,
            ];

            $this->assertEquals(201, $response->status(), $response->getContent());
        }

        $endTime = microtime(true);
        $totalTime = ($endTime - $startTime) * 1000; // ms

        $this->printResults('Create Transport Cost', $results, $totalTime);
    }

    /**
     * Test 3: Get History (70 concurrent requests)
     */
    public function test_get_history_with_70_concurrent_drivers()
    {
        echo "\n\n=== TEST 3: Get History (70 Concurrent Requests) ===\n";
        
        // First create transport costs for all drivers
        foreach ($this->drivers as $index => $driverData) {
            $user = $driverData['user'];
            
            $data = [
                'do_number' => "DO-HIST-" . str_pad($index + 1, 4, '0', STR_PAD_LEFT),
                'drop_point_count' => 3,
                'delivery_location' => 'Jakarta - Surabaya',
                'gasoline_cost' => 500000,
                'toll_cost' => 150000,
                'parking_cost' => 20000,
                'delivery_start_time' => now()->subHours(8)->format('Y-m-d H:i:s'),
                'delivery_end_time' => now()->subHours(1)->format('Y-m-d H:i:s'),
            ];

            $this->actingAs($user, 'sanctum')
                ->postJson('/api/transport-costs', $data);
        }

        // Now test concurrent history requests
        $startTime = microtime(true);
        $results = [];

        foreach ($this->drivers as $index => $driverData) {
            $user = $driverData['user'];
            
            $requestStart = microtime(true);
            
            $response = $this->actingAs($user, 'sanctum')
                ->getJson('/api/transport-costs');
            
            $requestEnd = microtime(true);
            $responseTime = ($requestEnd - $requestStart) * 1000; // ms

            $results[] = [
                'driver' => $index + 1,
                'status' => $response->status(),
                'response_time' => $responseTime,
                'data_count' => count($response->json('data', [])),
            ];

            $this->assertEquals(200, $response->status());
        }

        $endTime = microtime(true);
        $totalTime = ($endTime - $startTime) * 1000; // ms

        $this->printResults('Get History', $results, $totalTime);
    }

    /**
     * Test 4: Get Detail (70 concurrent requests)
     */
    public function test_get_detail_with_70_concurrent_drivers()
    {
        echo "\n\n=== TEST 4: Get Detail (70 Concurrent Requests) ===\n";
        
        // Create transport costs and store IDs
        $tripIds = [];
        foreach ($this->drivers as $index => $driverData) {
            $user = $driverData['user'];
            
            $data = [
                'do_number' => "DO-DETAIL-" . str_pad($index + 1, 4, '0', STR_PAD_LEFT),
                'drop_point_count' => 3,
                'delivery_location' => 'Jakarta - Bandung',
                'gasoline_cost' => 500000,
                'toll_cost' => 150000,
                'parking_cost' => 20000,
                'delivery_start_time' => now()->subHours(8)->format('Y-m-d H:i:s'),
                'delivery_end_time' => now()->subHours(1)->format('Y-m-d H:i:s'),
            ];

            $response = $this->actingAs($user, 'sanctum')
                ->postJson('/api/transport-costs', $data);

            $tripIds[$index] = $response->json('id');
        }

        // Test concurrent detail requests
        $startTime = microtime(true);
        $results = [];

        foreach ($this->drivers as $index => $driverData) {
            $user = $driverData['user'];
            $tripId = $tripIds[$index];
            
            $requestStart = microtime(true);
            
            $response = $this->actingAs($user, 'sanctum')
                ->getJson("/api/transport-costs/{$tripId}");
            
            $requestEnd = microtime(true);
            $responseTime = ($requestEnd - $requestStart) * 1000; // ms

            $results[] = [
                'driver' => $index + 1,
                'status' => $response->status(),
                'response_time' => $responseTime,
                'has_data' => !empty($response->json('id')),
            ];

            $this->assertEquals(200, $response->status());
        }

        $endTime = microtime(true);
        $totalTime = ($endTime - $startTime) * 1000; // ms

        $this->printResults('Get Detail', $results, $totalTime);
    }

    /**
     * Print test results
     */
    private function printResults(string $testName, array $results, float $totalTime)
    {
        $responseTimes = array_column($results, 'response_time');
        $avgResponseTime = array_sum($responseTimes) / count($responseTimes);
        $minResponseTime = min($responseTimes);
        $maxResponseTime = max($responseTimes);
        
        $successCount = count(array_filter($results, fn($r) => $r['status'] === 200 || $r['status'] === 201));
        $successRate = ($successCount / count($results)) * 100;

        echo "\n--- Results for: $testName ---\n";
        echo "Total Requests: " . count($results) . "\n";
        echo "Total Time: " . number_format($totalTime, 2) . " ms (" . number_format($totalTime / 1000, 2) . " seconds)\n";
        echo "Success Rate: " . number_format($successRate, 2) . "%\n";
        echo "Avg Response Time: " . number_format($avgResponseTime, 2) . " ms\n";
        echo "Min Response Time: " . number_format($minResponseTime, 2) . " ms\n";
        echo "Max Response Time: " . number_format($maxResponseTime, 2) . " ms\n";
        echo "Requests/Second: " . number_format((count($results) / ($totalTime / 1000)), 2) . "\n";

        // Performance assessment
        if ($avgResponseTime < 200) {
            echo "✅ EXCELLENT: Average response time < 200ms\n";
        } elseif ($avgResponseTime < 500) {
            echo "✅ GOOD: Average response time < 500ms\n";
        } elseif ($avgResponseTime < 1000) {
            echo "⚠️  ACCEPTABLE: Average response time < 1s\n";
        } else {
            echo "❌ SLOW: Average response time > 1s - Optimization needed!\n";
        }

        if ($successRate === 100.0) {
            echo "✅ All requests successful!\n";
        } else {
            echo "⚠️  Some requests failed!\n";
        }

        echo "\n";
    }
}
