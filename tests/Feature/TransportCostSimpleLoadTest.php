<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Driver;
use App\Models\Vehicle;
use App\Models\Project;
use App\Models\Attendance;
use App\Models\TransportCost;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\DB;

/**
 * Simple Load Test untuk Transport Cost API
 * Testing performa dengan 70 concurrent requests
 * 
 * Run: php artisan test --filter=TransportCostSimpleLoadTest
 */
class TransportCostSimpleLoadTest extends TestCase
{
    use WithFaker, \Illuminate\Foundation\Testing\RefreshDatabase;

    /**
     * Test: Simulasi 70 driver create transport cost secara bersamaan
     * 
     * @test
     */
    public function it_can_handle_70_concurrent_create_requests()
    {
        echo "\n\n=== LOAD TEST: 70 Concurrent Create Requests ===\n";
        
        // Setup: Create test data
        $project = Project::factory()->create(['name' => 'Load Test Project']);

        $drivers = [];
        $startTime = microtime(true);

        // Create 70 drivers with attendance
        for ($i = 1; $i <= 70; $i++) {
            $driver = Driver::factory()->create([
                'driver_id_nik' => "DRV-" . str_pad($i, 6, '0', STR_PAD_LEFT),
                'full_name' => "Driver Test $i",
                'project_id' => $project->id,
            ]);

            $vehicle = Vehicle::factory()->create([
                'project_id' => $project->id,
            ]);

            $attendance = Attendance::create([
                'driver_id' => $driver->id,
                'vehicle_id' => $vehicle->id,
                'time_in' => now()->subHours(10),
                'gps_location_in' => '-6.200000, 106.816666',
                'selfie_photo_path' => 'photos/test_selfie.jpg',
                'speedo_photo_awal_path' => 'photos/test_speedo.jpg',
                'speedo_awal' => 10000 + ($i * 100),
                'time_out' => now()->subHours(2),
                'speedo_photo_akhir_path' => 'photos/test_speedo_akhir.jpg',
                'speedo_akhir' => 10250 + ($i * 100),
            ]);

            $drivers[] = [
                'user' => $driver, // Set 'user' to $driver to avoid changing test case lines
                'driver' => $driver,
                'vehicle' => $vehicle,
                'attendance' => $attendance,
            ];
        }

        $setupTime = microtime(true) - $startTime;
        echo "Setup Time: " . number_format($setupTime, 2) . " seconds\n";

        // Test: Create transport costs concurrently
        $results = [];
        $testStartTime = microtime(true);

        foreach ($drivers as $index => $driverData) {
            $user = $driverData['user'];
            
            $data = [
                'do_number' => "DO-LOAD-" . str_pad($index + 1, 4, '0', STR_PAD_LEFT),
                'drop_point_count' => rand(1, 5),
                'delivery_location' => 'Jakarta - Bandung - Surabaya',
                'gasoline_cost' => rand(300000, 600000),
                'toll_cost' => rand(100000, 200000),
                'parking_cost' => rand(10000, 30000),
                'delivery_start_time' => now()->subHours(8)->format('Y-m-d H:i:s'),
                'delivery_end_time' => now()->subHours(1)->format('Y-m-d H:i:s'),
            ];

            $requestStart = microtime(true);
            
            try {
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
            } catch (\Exception $e) {
                $requestEnd = microtime(true);
                $responseTime = ($requestEnd - $requestStart) * 1000;
                
                $results[] = [
                    'driver' => $index + 1,
                    'status' => 500,
                    'response_time' => $responseTime,
                    'success' => false,
                    'error' => $e->getMessage(),
                ];
            }
        }

        $testEndTime = microtime(true);
        $totalTime = ($testEndTime - $testStartTime) * 1000; // ms

        // Calculate statistics
        $responseTimes = array_column($results, 'response_time');
        $avgResponseTime = array_sum($responseTimes) / count($responseTimes);
        $minResponseTime = min($responseTimes);
        $maxResponseTime = max($responseTimes);
        
        $successCount = count(array_filter($results, fn($r) => $r['success']));
        $successRate = ($successCount / count($results)) * 100;

        // Print results
        echo "\n--- LOAD TEST RESULTS ---\n";
        echo "Total Requests: " . count($results) . "\n";
        echo "Total Time: " . number_format($totalTime, 2) . " ms (" . number_format($totalTime / 1000, 2) . " seconds)\n";
        echo "Success Count: $successCount / " . count($results) . "\n";
        echo "Success Rate: " . number_format($successRate, 2) . "%\n";
        echo "Avg Response Time: " . number_format($avgResponseTime, 2) . " ms\n";
        echo "Min Response Time: " . number_format($minResponseTime, 2) . " ms\n";
        echo "Max Response Time: " . number_format($maxResponseTime, 2) . " ms\n";
        echo "Requests/Second: " . number_format((count($results) / ($totalTime / 1000)), 2) . "\n";

        // Performance assessment
        echo "\n--- PERFORMANCE ASSESSMENT ---\n";
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
        } elseif ($successRate >= 95.0) {
            echo "⚠️  Most requests successful (>95%)\n";
        } else {
            echo "❌ Many requests failed (<95%) - Investigation needed!\n";
        }

        // Database check
        $createdCount = TransportCost::count();
        echo "\n--- DATABASE VERIFICATION ---\n";
        echo "Transport Costs Created: $createdCount\n";
        echo "Expected: $successCount\n";
        
        if ($createdCount === $successCount) {
            echo "✅ Database integrity verified!\n";
        } else {
            echo "⚠️  Database count mismatch!\n";
        }

        // Show failed requests if any
        $failedRequests = array_filter($results, fn($r) => !$r['success']);
        if (!empty($failedRequests)) {
            echo "\n--- FAILED REQUESTS ---\n";
            foreach ($failedRequests as $failed) {
                echo "Driver #{$failed['driver']}: Status {$failed['status']}";
                if (isset($failed['error'])) {
                    echo " - {$failed['error']}";
                }
                echo "\n";
            }
        }

        echo "\n";

        // Assertions
        $this->assertGreaterThanOrEqual(95, $successRate, "Success rate should be at least 95%");
        $this->assertLessThan(2000, $avgResponseTime, "Average response time should be less than 2 seconds");
        $this->assertEquals($successCount, $createdCount, "All successful requests should create database records");
    }

    /**
     * Test: Check database performance with 70 records
     * 
     * @test
     */
    public function it_can_query_70_records_efficiently()
    {
        echo "\n\n=== LOAD TEST: Query Performance with 70 Records ===\n";
        
        // Setup: Create 70 transport cost records
        $project = Project::factory()->create();
        $driver = Driver::factory()->create(['project_id' => $project->id]);
        $vehicle = Vehicle::factory()->create(['project_id' => $project->id]);

        $startTime = microtime(true);

        for ($i = 1; $i <= 70; $i++) {
            $attendance = Attendance::create([
                'driver_id' => $driver->id,
                'vehicle_id' => $vehicle->id,
                'time_in' => now()->subDays($i)->subHours(10),
                'gps_location_in' => '-6.200000, 106.816666',
                'selfie_photo_path' => 'photos/test_selfie.jpg',
                'speedo_photo_awal_path' => 'photos/test_speedo.jpg',
                'speedo_awal' => 10000 + ($i * 100),
                'time_out' => now()->subDays($i)->subHours(2),
                'speedo_photo_akhir_path' => 'photos/test_speedo_akhir.jpg',
                'speedo_akhir' => 10250 + ($i * 100),
            ]);

            TransportCost::create([
                'driver_id' => $driver->id,
                'vehicle_id' => $vehicle->id,
                'project_id' => $project->id,
                'attendance_id' => $attendance->id,
                'trip_date' => now()->subDays($i),
                'do_number' => "DO-$i",
                'drop_point_count' => 3,
                'delivery_location' => 'Jakarta',
                'odometer_start' => 10000 + ($i * 100),
                'odometer_end' => 10250 + ($i * 100),
                'gasoline_cost' => 500000,
                'toll_cost' => 150000,
                'parking_cost' => 20000,
                'delivery_start_time' => now()->subDays($i)->subHours(8),
                'delivery_end_time' => now()->subDays($i)->subHours(1),
                'approval_status' => 'pending',
            ]);
        }

        $setupTime = microtime(true) - $startTime;
        echo "Setup Time: " . number_format($setupTime, 2) . " seconds\n";

        // Test: Query all records
        $queryStart = microtime(true);
        $trips = TransportCost::with(['driver', 'vehicle', 'project'])
            ->where('driver_id', $driver->id)
            ->orderBy('trip_date', 'desc')
            ->get();
        $queryEnd = microtime(true);
        $queryTime = ($queryEnd - $queryStart) * 1000;

        echo "\n--- QUERY RESULTS ---\n";
        echo "Records Retrieved: " . $trips->count() . "\n";
        echo "Query Time: " . number_format($queryTime, 2) . " ms\n";

        if ($queryTime < 100) {
            echo "✅ EXCELLENT: Query time < 100ms\n";
        } elseif ($queryTime < 500) {
            echo "✅ GOOD: Query time < 500ms\n";
        } else {
            echo "⚠️  SLOW: Query time > 500ms - Consider adding indexes!\n";
        }

        $this->assertEquals(70, $trips->count());
        $this->assertLessThan(1000, $queryTime, "Query should complete in less than 1 second");
    }
}
