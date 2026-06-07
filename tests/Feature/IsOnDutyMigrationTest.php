<?php

namespace Tests\Feature;

use App\Models\Attendance;
use App\Models\Driver;
use App\Models\Vehicle;
use App\Models\Project;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class IsOnDutyMigrationTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Helper method to simulate migration logic with SQLite-compatible syntax
     */
    private function simulateMigrationLogic()
    {
        // SQLite-compatible UPDATE statement
        DB::statement('
            UPDATE drivers
            SET is_on_duty = 1
            WHERE id IN (
                SELECT DISTINCT driver_id
                FROM attendances
                WHERE time_out IS NULL
            )
        ');
    }

    /**
     * Test that is_on_duty column exists in drivers table
     *
     * @return void
     */
    public function test_is_on_duty_column_exists()
    {
        $this->assertTrue(
            Schema::hasColumn('drivers', 'is_on_duty'),
            'is_on_duty column should exist in drivers table'
        );
    }

    /**
     * Test that idx_drivers_on_duty index exists
     *
     * @return void
     */
    public function test_idx_drivers_on_duty_index_exists()
    {
        $indexes = DB::select("SELECT * FROM sqlite_master WHERE type = 'index' AND tbl_name = 'drivers' AND name LIKE '%is_on_duty%'");

        $this->assertNotEmpty($indexes, 'An index on is_on_duty should exist');
    }

    /**
     * Test that is_on_duty has default value of false
     *
     * @return void
     */
    public function test_is_on_duty_default_value_is_false()
    {
        $project = Project::factory()->create();
        
        $driver = Driver::create([
            'full_name' => 'Test Driver',
            'driver_id_nik' => 'TEST001',
            'password' => bcrypt('password'),
            'project_id' => $project->id,
        ]);

        $this->assertEquals(0, $driver->is_on_duty, 'is_on_duty should default to false (0)');
    }

    /**
     * Test that migration sets is_on_duty=true for drivers with active attendances
     *
     * @return void
     */
    public function test_migration_sets_is_on_duty_for_active_attendances()
    {
        $project = Project::factory()->create();
        $vehicle = Vehicle::factory()->create(['project_id' => $project->id]);

        // Create driver with active attendance (time_out IS NULL)
        $driverWithActiveAttendance = Driver::create([
            'full_name' => 'Driver On Duty',
            'driver_id_nik' => 'ACTIVE001',
            'password' => bcrypt('password'),
            'project_id' => $project->id,
            'is_on_duty' => false, // Start with false
        ]);

        // Create attendance with time_out = NULL
        Attendance::factory()->create([
            'driver_id' => $driverWithActiveAttendance->id,
            'vehicle_id' => $vehicle->id,
            'time_in' => now(),
            'time_out' => null, // Active attendance
        ]);

        // Create driver without active attendance
        $driverWithoutAttendance = Driver::create([
            'full_name' => 'Driver Not On Duty',
            'driver_id_nik' => 'INACTIVE001',
            'password' => bcrypt('password'),
            'project_id' => $project->id,
            'is_on_duty' => false,
        ]);

        // Simulate the migration logic
        $this->simulateMigrationLogic();

        // Refresh from database
        $driverWithActiveAttendance->refresh();
        $driverWithoutAttendance->refresh();

        // Assert
        $this->assertEquals(1, $driverWithActiveAttendance->is_on_duty, 'Driver with active attendance should have is_on_duty=true');
        $this->assertEquals(0, $driverWithoutAttendance->is_on_duty, 'Driver without active attendance should have is_on_duty=false');
    }

    /**
     * Test that migration does not set is_on_duty for drivers with completed attendances only
     *
     * @return void
     */
    public function test_migration_does_not_set_is_on_duty_for_completed_attendances()
    {
        $project = Project::factory()->create();
        $vehicle = Vehicle::factory()->create(['project_id' => $project->id]);

        $driverWithCompletedAttendance = Driver::create([
            'full_name' => 'Driver with Completed Attendance',
            'driver_id_nik' => 'COMPLETED001',
            'password' => bcrypt('password'),
            'project_id' => $project->id,
            'is_on_duty' => false,
        ]);

        // Create attendance with time_out (completed)
        Attendance::factory()->create([
            'driver_id' => $driverWithCompletedAttendance->id,
            'vehicle_id' => $vehicle->id,
            'time_in' => now()->subHours(8),
            'time_out' => now(), // Completed attendance
        ]);

        // Simulate the migration logic
        $this->simulateMigrationLogic();

        // Refresh from database
        $driverWithCompletedAttendance->refresh();

        // Assert
        $this->assertEquals(0, $driverWithCompletedAttendance->is_on_duty, 'Driver with only completed attendance should have is_on_duty=false');
    }

    /**
     * Test that migration handles multiple drivers correctly
     *
     * @return void
     */
    public function test_migration_handles_multiple_drivers_correctly()
    {
        $project = Project::factory()->create();
        $vehicle = Vehicle::factory()->create(['project_id' => $project->id]);

        // Create multiple drivers with different states
        $drivers = [];
        
        // Driver 1: Active attendance
        $drivers[0] = Driver::create([
            'full_name' => 'Driver 1 Active',
            'driver_id_nik' => 'DRV001',
            'password' => bcrypt('password'),
            'project_id' => $project->id,
            'is_on_duty' => false,
        ]);
        Attendance::factory()->create([
            'driver_id' => $drivers[0]->id,
            'vehicle_id' => $vehicle->id,
            'time_in' => now(),
            'time_out' => null,
        ]);

        // Driver 2: No attendance
        $drivers[1] = Driver::create([
            'full_name' => 'Driver 2 No Attendance',
            'driver_id_nik' => 'DRV002',
            'password' => bcrypt('password'),
            'project_id' => $project->id,
            'is_on_duty' => false,
        ]);

        // Driver 3: Active attendance
        $drivers[2] = Driver::create([
            'full_name' => 'Driver 3 Active',
            'driver_id_nik' => 'DRV003',
            'password' => bcrypt('password'),
            'project_id' => $project->id,
            'is_on_duty' => false,
        ]);
        Attendance::factory()->create([
            'driver_id' => $drivers[2]->id,
            'vehicle_id' => $vehicle->id,
            'time_in' => now(),
            'time_out' => null,
        ]);

        // Driver 4: Completed attendance
        $drivers[3] = Driver::create([
            'full_name' => 'Driver 4 Completed',
            'driver_id_nik' => 'DRV004',
            'password' => bcrypt('password'),
            'project_id' => $project->id,
            'is_on_duty' => false,
        ]);
        Attendance::factory()->create([
            'driver_id' => $drivers[3]->id,
            'vehicle_id' => $vehicle->id,
            'time_in' => now()->subHours(8),
            'time_out' => now(),
        ]);

        // Simulate the migration logic
        $this->simulateMigrationLogic();

        // Refresh from database
        foreach ($drivers as $driver) {
            $driver->refresh();
        }

        // Assert
        $this->assertEquals(1, $drivers[0]->is_on_duty, 'Driver 1 with active attendance should have is_on_duty=true');
        $this->assertEquals(0, $drivers[1]->is_on_duty, 'Driver 2 without attendance should have is_on_duty=false');
        $this->assertEquals(1, $drivers[2]->is_on_duty, 'Driver 3 with active attendance should have is_on_duty=true');
        $this->assertEquals(0, $drivers[3]->is_on_duty, 'Driver 4 with completed attendance should have is_on_duty=false');
    }
}
