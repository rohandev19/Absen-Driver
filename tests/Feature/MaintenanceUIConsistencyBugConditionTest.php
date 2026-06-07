<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Gate;
use Tests\TestCase;
use App\Models\User;
use App\Models\Vehicle;
use App\Models\Driver;
use App\Models\Project;
use App\Models\MaintenanceAlert;
use App\Models\MaintenanceSchedule;
use App\Models\VehicleComponent;
use Carbon\Carbon;

/**
 * Bug Condition Exploration Test for Maintenance UI Consistency
 * 
 * **Validates: Requirements 2.1, 2.2, 2.3, 2.4, 2.5, 2.6, 2.7, 2.8, 2.9, 2.10**
 * 
 * CRITICAL: This test MUST FAIL on unfixed code - failure confirms the bug exists
 * DO NOT attempt to fix the test or the code when it fails
 * 
 * This test encodes the expected behavior - it will validate the fix when it passes after implementation
 * 
 * GOAL: Surface counterexamples that demonstrate UI inconsistencies exist across maintenance pages
 * 
 * Expected Behavior:
 * - All pages SHALL use standardized CSS classes (card-metric, table-corporate, badge-corp, btn-action-corp, filter-container)
 * - All pages SHALL use consistent colors for same status values (danger, warning, success, info, primary)
 * - All pages SHALL use consistent responsive transformation patterns for mobile devices
 */
class MaintenanceUIConsistencyBugConditionTest extends TestCase
{
    use RefreshDatabase;

    private $admin;
    private $vehicle;
    private $driver;
    private $project;

    protected function setUp(): void
    {
        parent::setUp();

        // Bypass authorization
        Gate::define('is-master-admin', fn() => true);

        // Create test data
        $this->admin = User::factory()->create([
            'role' => 'master',
        ]);
        
        $this->project = Project::factory()->create([
            'name' => 'Test Project',
        ]);

        $this->vehicle = Vehicle::create([
            'plate_number' => 'B 1234 TEST',
            'type' => 'Box',
            'service_interval_km' => 5000,
            'last_service_km' => 10000,
            'current_km' => 14500,
            'project_id' => $this->project->id,
            'pajak_stnk_berlaku_sampai' => now()->addYear(),
            'kir_berlaku_sampai' => now()->addYear(),
        ]);

        $this->driver = Driver::create([
            'full_name' => 'Test Driver',
            'driver_id_nik' => '1234567890123456',
            'phone_number' => '081234567890',
            'password' => bcrypt('password'),
            'is_active' => true,
        ]);

        // Create maintenance alert for alerts page
        MaintenanceAlert::create([
            'vehicle_id' => $this->vehicle->id,
            'alert_type' => 'critical',
            'message' => 'Test alert',
            'status' => 'active',
            'triggered_at' => now(),
        ]);

        // Create maintenance schedule for schedules page
        MaintenanceSchedule::create([
            'vehicle_id' => $this->vehicle->id,
            'scheduled_date' => now()->addDays(7),
            'type' => 'preventive',
            'priority' => 'critical',
            'status' => 'pending',
            'estimated_cost' => 500000,
        ]);

        // Create vehicle component for components page
        VehicleComponent::create([
            'vehicle_id' => $this->vehicle->id,
            'category' => 'Cairan & Pelumas',
            'component_name' => 'Oli Mesin',
            'replacement_interval_km' => 5000,
            'last_replacement_km' => 10000,
            'last_replacement_date' => now()->subMonths(3),
            'cost_per_replacement' => 350000,
        ]);
    }

    /**
     * Property 1: Bug Condition - Card Component Inconsistency
     * 
     * Test that metric/summary cards use inconsistent styling across pages
     * 
     * EXPECTED OUTCOME: Test FAILS (proves bug exists)
     * 
     * Counterexamples:
     * - index.blade.php uses card-metric with border-left-danger
     * - alerts.blade.php uses border-start border-4
     * - schedules.blade.php uses border-start border-4
     */
    public function test_card_components_have_inconsistent_styling_across_pages()
    {
        // Get index page
        $indexResponse = $this->actingAs($this->admin)
            ->get(route('admin.maintenance.dashboard'));
        $indexHtml = $indexResponse->getContent();

        // Get alerts page
        $alertsResponse = $this->actingAs($this->admin)
            ->get(route('admin.maintenance.alerts'));
        $alertsHtml = $alertsResponse->getContent();

        // Get schedules page
        $schedulesResponse = $this->actingAs($this->admin)
            ->get(route('admin.maintenance.schedules'));
        $schedulesHtml = $schedulesResponse->getContent();

        // EXPECTED BEHAVIOR: All pages should use "card-metric" class
        // CURRENT BEHAVIOR: Only index uses card-metric, others use different classes
        
        $indexUsesCardMetric = str_contains($indexHtml, 'card-metric');
        $alertsUsesCardMetric = str_contains($alertsHtml, 'card-metric');
        $schedulesUsesCardMetric = str_contains($schedulesHtml, 'card-metric');

        // Check if index uses border-left-{status} pattern
        $indexUsesBorderLeft = str_contains($indexHtml, 'border-left-danger') || 
                               str_contains($indexHtml, 'border-left-warning') ||
                               str_contains($indexHtml, 'border-left-success');

        // Check if alerts uses border-start border-4 pattern (Bootstrap default)
        $alertsUsesBorderStart = str_contains($alertsHtml, 'border-start border-danger border-4') ||
                                 str_contains($alertsHtml, 'border-start border-warning border-4');

        // Check if schedules uses border-start border-4 pattern (Bootstrap default)
        $schedulesUsesBorderStart = str_contains($schedulesHtml, 'border-start border-danger border-4') ||
                                    str_contains($schedulesHtml, 'border-start border-warning border-4');

        // ASSERTION: All pages should use card-metric class consistently
        $this->assertTrue($indexUsesCardMetric, 
            "Counterexample 1: index.blade.php uses card-metric class");
        
        $this->assertTrue($alertsUsesCardMetric, 
            "Counterexample 2: alerts.blade.php does NOT use card-metric class (uses border-start border-4 instead)");
        
        $this->assertTrue($schedulesUsesCardMetric, 
            "Counterexample 3: schedules.blade.php does NOT use card-metric class (uses border-start border-4 instead)");

        // ASSERTION: All pages should use border-left-{status} pattern consistently
        $this->assertTrue($indexUsesBorderLeft, 
            "Counterexample 4: index.blade.php uses border-left-{status} pattern");
        
        $this->assertFalse($alertsUsesBorderStart, 
            "Counterexample 5: alerts.blade.php uses border-start border-4 pattern (inconsistent with index)");
        
        $this->assertFalse($schedulesUsesBorderStart, 
            "Counterexample 6: schedules.blade.php uses border-start border-4 pattern (inconsistent with index)");
    }

    /**
     * Property 1: Bug Condition - Table Component Inconsistency
     * 
     * Test that tables use inconsistent styling across pages
     * 
     * EXPECTED OUTCOME: Test FAILS (proves bug exists)
     * 
     * Counterexamples:
     * - index.blade.php uses table-corporate with custom thead
     * - schedules.blade.php uses table table-hover (Bootstrap default)
     * - components.blade.php uses table table-hover (Bootstrap default)
     */
    public function test_table_components_have_inconsistent_styling_across_pages()
    {
        // Get index page
        $indexResponse = $this->actingAs($this->admin)
            ->get(route('admin.maintenance.dashboard'));
        $indexHtml = $indexResponse->getContent();

        // Get schedules page
        $schedulesResponse = $this->actingAs($this->admin)
            ->get(route('admin.maintenance.schedules'));
        $schedulesHtml = $schedulesResponse->getContent();

        // Get components page
        $componentsResponse = $this->actingAs($this->admin)
            ->get(route('admin.maintenance.components', $this->vehicle->id));
        $componentsHtml = $componentsResponse->getContent();

        // EXPECTED BEHAVIOR: All pages should use "table-corporate" class
        // CURRENT BEHAVIOR: Only index uses table-corporate, others use table table-hover
        
        $indexUsesTableCorporate = str_contains($indexHtml, 'table-corporate');
        $schedulesUsesTableCorporate = str_contains($schedulesHtml, 'table-corporate');
        $componentsUsesTableCorporate = str_contains($componentsHtml, 'table-corporate');

        // Check if schedules uses Bootstrap default table classes
        $schedulesUsesBootstrapTable = str_contains($schedulesHtml, 'table table-hover');
        
        // Check if components uses Bootstrap default table classes
        $componentsUsesBootstrapTable = str_contains($componentsHtml, 'table table-hover');

        // ASSERTION: All pages should use table-corporate class consistently
        $this->assertTrue($indexUsesTableCorporate, 
            "Counterexample 7: index.blade.php uses table-corporate class");
        
        $this->assertTrue($schedulesUsesTableCorporate, 
            "Counterexample 8: schedules.blade.php does NOT use table-corporate class (uses table table-hover instead)");
        
        $this->assertTrue($componentsUsesTableCorporate, 
            "Counterexample 9: components.blade.php does NOT use table-corporate class (uses table table-hover instead)");

        // ASSERTION: Schedules and components should NOT use Bootstrap default table classes
        $this->assertFalse($schedulesUsesBootstrapTable, 
            "Counterexample 10: schedules.blade.php uses Bootstrap default 'table table-hover' (inconsistent with index)");
        
        $this->assertFalse($componentsUsesBootstrapTable, 
            "Counterexample 11: components.blade.php uses Bootstrap default 'table table-hover' (inconsistent with index)");
    }

    /**
     * Property 1: Bug Condition - Badge Component Inconsistency
     * 
     * Test that badges use inconsistent styling and colors across pages
     * 
     * EXPECTED OUTCOME: Test FAILS (proves bug exists)
     * 
     * Counterexamples:
     * - index.blade.php uses badge-corp-warning for critical status
     * - schedules.blade.php uses badge bg-danger for critical priority
     * - Different color mapping for same status values
     */
    public function test_badge_components_have_inconsistent_styling_and_colors_across_pages()
    {
        // Get index page
        $indexResponse = $this->actingAs($this->admin)
            ->get(route('admin.maintenance.dashboard'));
        $indexHtml = $indexResponse->getContent();

        // Get alerts page
        $alertsResponse = $this->actingAs($this->admin)
            ->get(route('admin.maintenance.alerts'));
        $alertsHtml = $alertsResponse->getContent();

        // Get schedules page
        $schedulesResponse = $this->actingAs($this->admin)
            ->get(route('admin.maintenance.schedules'));
        $schedulesHtml = $schedulesResponse->getContent();

        // EXPECTED BEHAVIOR: All pages should use "badge-corp" classes
        // CURRENT BEHAVIOR: Only index uses badge-corp, others use Bootstrap default badges
        
        $indexUsesBadgeCorp = str_contains($indexHtml, 'badge-corp');
        $alertsUsesBadgeCorp = str_contains($alertsHtml, 'badge-corp');
        $schedulesUsesBadgeCorp = str_contains($schedulesHtml, 'badge-corp');

        // Check if alerts uses Bootstrap default badge classes
        $alertsUsesBootstrapBadge = str_contains($alertsHtml, 'badge bg-danger') ||
                                    str_contains($alertsHtml, 'badge bg-warning');
        
        // Check if schedules uses Bootstrap default badge classes
        $schedulesUsesBootstrapBadge = str_contains($schedulesHtml, 'badge bg-danger') ||
                                       str_contains($schedulesHtml, 'badge bg-warning');

        // ASSERTION: All pages should use badge-corp class consistently
        $this->assertTrue($indexUsesBadgeCorp, 
            "Counterexample 12: index.blade.php uses badge-corp classes");
        
        $this->assertTrue($alertsUsesBadgeCorp, 
            "Counterexample 13: alerts.blade.php does NOT use badge-corp classes (uses badge bg-{color} instead)");
        
        $this->assertTrue($schedulesUsesBadgeCorp, 
            "Counterexample 14: schedules.blade.php does NOT use badge-corp classes (uses badge bg-{color} instead)");

        // ASSERTION: Alerts and schedules should NOT use Bootstrap default badge classes
        $this->assertFalse($alertsUsesBootstrapBadge, 
            "Counterexample 15: alerts.blade.php uses Bootstrap default 'badge bg-{color}' (inconsistent with index)");
        
        $this->assertFalse($schedulesUsesBootstrapBadge, 
            "Counterexample 16: schedules.blade.php uses Bootstrap default 'badge bg-{color}' (inconsistent with index)");
    }

    /**
     * Property 1: Bug Condition - Button Component Inconsistency
     * 
     * Test that buttons use inconsistent styling across pages
     * 
     * EXPECTED OUTCOME: Test FAILS (proves bug exists)
     * 
     * Counterexamples:
     * - index.blade.php uses btn-action-corp, btn-primary-corp, btn-danger-corp
     * - alerts.blade.php uses btn btn-sm btn-primary (Bootstrap default)
     * - schedules.blade.php uses btn btn-sm btn-success (Bootstrap default)
     */
    public function test_button_components_have_inconsistent_styling_across_pages()
    {
        // Get index page
        $indexResponse = $this->actingAs($this->admin)
            ->get(route('admin.maintenance.dashboard'));
        $indexHtml = $indexResponse->getContent();

        // Get alerts page
        $alertsResponse = $this->actingAs($this->admin)
            ->get(route('admin.maintenance.alerts'));
        $alertsHtml = $alertsResponse->getContent();

        // Get schedules page
        $schedulesResponse = $this->actingAs($this->admin)
            ->get(route('admin.maintenance.schedules'));
        $schedulesHtml = $schedulesResponse->getContent();

        // EXPECTED BEHAVIOR: All pages should use custom button classes (btn-action-corp, btn-primary-corp, btn-danger-corp)
        // CURRENT BEHAVIOR: Only index uses custom classes, others use Bootstrap default
        
        $indexUsesCustomButtons = str_contains($indexHtml, 'btn-action-corp') ||
                                  str_contains($indexHtml, 'btn-primary-corp') ||
                                  str_contains($indexHtml, 'btn-danger-corp');
        
        $alertsUsesCustomButtons = str_contains($alertsHtml, 'btn-action-corp') ||
                                   str_contains($alertsHtml, 'btn-primary-corp');
        
        $schedulesUsesCustomButtons = str_contains($schedulesHtml, 'btn-action-corp') ||
                                      str_contains($schedulesHtml, 'btn-primary-corp');

        // Check if alerts uses Bootstrap default button classes
        $alertsUsesBootstrapButtons = str_contains($alertsHtml, 'btn btn-sm btn-primary') ||
                                      str_contains($alertsHtml, 'btn btn-sm btn-success');
        
        // Check if schedules uses Bootstrap default button classes
        $schedulesUsesBootstrapButtons = str_contains($schedulesHtml, 'btn btn-sm btn-success') ||
                                         str_contains($schedulesHtml, 'btn btn-primary');

        // ASSERTION: All pages should use custom button classes consistently
        $this->assertTrue($indexUsesCustomButtons, 
            "Counterexample 17: index.blade.php uses custom button classes (btn-action-corp, btn-primary-corp, btn-danger-corp)");
        
        $this->assertTrue($alertsUsesCustomButtons, 
            "Counterexample 18: alerts.blade.php does NOT use custom button classes (uses Bootstrap default instead)");
        
        $this->assertTrue($schedulesUsesCustomButtons, 
            "Counterexample 19: schedules.blade.php does NOT use custom button classes (uses Bootstrap default instead)");

        // ASSERTION: Alerts and schedules should NOT use Bootstrap default button classes
        $this->assertFalse($alertsUsesBootstrapButtons, 
            "Counterexample 20: alerts.blade.php uses Bootstrap default button classes (inconsistent with index)");
        
        $this->assertFalse($schedulesUsesBootstrapButtons, 
            "Counterexample 21: schedules.blade.php uses Bootstrap default button classes (inconsistent with index)");
    }

    /**
     * Property 1: Bug Condition - Filter Container Inconsistency
     * 
     * Test that filter containers use inconsistent styling across pages
     * 
     * EXPECTED OUTCOME: Test FAILS (proves bug exists)
     * 
     * Counterexamples:
     * - index.blade.php uses filter-container class with custom styling
     * - alerts.blade.php uses card with different styling
     * - schedules.blade.php uses card with different styling
     */
    public function test_filter_containers_have_inconsistent_styling_across_pages()
    {
        // Get index page
        $indexResponse = $this->actingAs($this->admin)
            ->get(route('admin.maintenance.dashboard'));
        $indexHtml = $indexResponse->getContent();

        // Get alerts page
        $alertsResponse = $this->actingAs($this->admin)
            ->get(route('admin.maintenance.alerts'));
        $alertsHtml = $alertsResponse->getContent();

        // Get schedules page
        $schedulesResponse = $this->actingAs($this->admin)
            ->get(route('admin.maintenance.schedules'));
        $schedulesHtml = $schedulesResponse->getContent();

        // EXPECTED BEHAVIOR: All pages should use "filter-container" class
        // CURRENT BEHAVIOR: Only index uses filter-container, others use card
        
        $indexUsesFilterContainer = str_contains($indexHtml, 'filter-container');
        $alertsUsesFilterContainer = str_contains($alertsHtml, 'filter-container');
        $schedulesUsesFilterContainer = str_contains($schedulesHtml, 'filter-container');

        // ASSERTION: All pages should use filter-container class consistently
        $this->assertTrue($indexUsesFilterContainer, 
            "Counterexample 22: index.blade.php uses filter-container class");
        
        $this->assertTrue($alertsUsesFilterContainer, 
            "Counterexample 23: alerts.blade.php does NOT use filter-container class (uses card instead)");
        
        $this->assertTrue($schedulesUsesFilterContainer, 
            "Counterexample 24: schedules.blade.php does NOT use filter-container class (uses card instead)");
    }

    /**
     * Property 1: Bug Condition - Mobile Responsive Inconsistency
     * 
     * Test that mobile responsive patterns are inconsistent across pages
     * 
     * EXPECTED OUTCOME: Test FAILS (proves bug exists)
     * 
     * Counterexamples:
     * - index.blade.php has @media (max-width: 768px) with table transformation
     * - schedules.blade.php does NOT have mobile responsive table transformation
     * - components.blade.php does NOT have mobile responsive table transformation
     */
    public function test_mobile_responsive_patterns_are_inconsistent_across_pages()
    {
        // Get index page
        $indexResponse = $this->actingAs($this->admin)
            ->get(route('admin.maintenance.dashboard'));
        $indexHtml = $indexResponse->getContent();

        // Get schedules page
        $schedulesResponse = $this->actingAs($this->admin)
            ->get(route('admin.maintenance.schedules'));
        $schedulesHtml = $schedulesResponse->getContent();

        // Get components page
        $componentsResponse = $this->actingAs($this->admin)
            ->get(route('admin.maintenance.components', $this->vehicle->id));
        $componentsHtml = $componentsResponse->getContent();

        // EXPECTED BEHAVIOR: All pages should have mobile responsive CSS with @media (max-width: 768px)
        // CURRENT BEHAVIOR: Only index has comprehensive mobile responsive styles
        
        $indexHasMobileResponsive = str_contains($indexHtml, '@media (max-width: 768px)') &&
                                    str_contains($indexHtml, 'table-corporate thead') &&
                                    str_contains($indexHtml, 'display: none');
        
        // Check if schedules has mobile responsive styles
        $schedulesHasMobileResponsive = str_contains($schedulesHtml, '@media (max-width: 768px)') &&
                                        str_contains($schedulesHtml, 'table') &&
                                        str_contains($schedulesHtml, 'display: block');
        
        // Check if components has mobile responsive styles
        $componentsHasMobileResponsive = str_contains($componentsHtml, '@media (max-width: 768px)') &&
                                         str_contains($componentsHtml, 'table') &&
                                         str_contains($componentsHtml, 'display: block');

        // ASSERTION: All pages should have mobile responsive table transformation
        $this->assertTrue($indexHasMobileResponsive, 
            "Counterexample 25: index.blade.php has mobile responsive table transformation");
        
        $this->assertTrue($schedulesHasMobileResponsive, 
            "Counterexample 26: schedules.blade.php does NOT have mobile responsive table transformation (table overflows horizontally)");
        
        $this->assertTrue($componentsHasMobileResponsive, 
            "Counterexample 27: components.blade.php does NOT have mobile responsive table transformation (table overflows horizontally)");
    }
}
