<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

/**
 * Design System Validation Test
 * 
 * This test validates that the design system partial (_design-system.blade.php)
 * contains all required CSS classes, variables, and components as specified
 * in the maintenance-ui-consistency bugfix design document.
 * 
 * Task: 3.12 Test design system partial in isolation
 */
class DesignSystemValidationTest extends TestCase
{
    protected string $designSystemPath;
    protected string $designSystemContent;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->designSystemPath = resource_path('views/admin/maintenance/partials/_design-system.blade.php');
        
        if (!file_exists($this->designSystemPath)) {
            $this->fail("Design system partial not found at: {$this->designSystemPath}");
        }
        
        $this->designSystemContent = file_get_contents($this->designSystemPath);
    }

    /**
     * Test that design system partial file exists
     */
    public function test_design_system_partial_exists(): void
    {
        $this->assertFileExists($this->designSystemPath);
    }

    /**
     * Test that all CSS variables are defined in :root
     */
    public function test_css_variables_are_defined(): void
    {
        // Typography variables
        $this->assertStringContainsString('--font-size-base:', $this->designSystemContent);
        $this->assertStringContainsString('--font-size-small:', $this->designSystemContent);
        $this->assertStringContainsString('--font-size-medium:', $this->designSystemContent);
        $this->assertStringContainsString('--font-weight-normal:', $this->designSystemContent);
        $this->assertStringContainsString('--font-weight-bold:', $this->designSystemContent);
        $this->assertStringContainsString('--font-weight-extra-bold:', $this->designSystemContent);
        $this->assertStringContainsString('--letter-spacing-wide:', $this->designSystemContent);
        $this->assertStringContainsString('--letter-spacing-wider:', $this->designSystemContent);

        // Spacing variables
        $this->assertStringContainsString('--spacing-xs:', $this->designSystemContent);
        $this->assertStringContainsString('--spacing-sm:', $this->designSystemContent);
        $this->assertStringContainsString('--spacing-md:', $this->designSystemContent);
        $this->assertStringContainsString('--spacing-lg:', $this->designSystemContent);
        $this->assertStringContainsString('--spacing-xl:', $this->designSystemContent);

        // Border radius variables
        $this->assertStringContainsString('--border-radius-sm:', $this->designSystemContent);
        $this->assertStringContainsString('--border-radius-md:', $this->designSystemContent);
        $this->assertStringContainsString('--border-radius-lg:', $this->designSystemContent);

        // Transition variables
        $this->assertStringContainsString('--transition-smooth:', $this->designSystemContent);
        $this->assertStringContainsString('--transition-fast:', $this->designSystemContent);

        // Color variables (status)
        $this->assertStringContainsString('--color-danger:', $this->designSystemContent);
        $this->assertStringContainsString('--color-danger-light:', $this->designSystemContent);
        $this->assertStringContainsString('--color-danger-border:', $this->designSystemContent);
        $this->assertStringContainsString('--color-danger-text:', $this->designSystemContent);
        
        $this->assertStringContainsString('--color-warning:', $this->designSystemContent);
        $this->assertStringContainsString('--color-warning-light:', $this->designSystemContent);
        $this->assertStringContainsString('--color-warning-border:', $this->designSystemContent);
        $this->assertStringContainsString('--color-warning-text:', $this->designSystemContent);
        
        $this->assertStringContainsString('--color-success:', $this->designSystemContent);
        $this->assertStringContainsString('--color-success-light:', $this->designSystemContent);
        $this->assertStringContainsString('--color-success-border:', $this->designSystemContent);
        $this->assertStringContainsString('--color-success-text:', $this->designSystemContent);
        
        $this->assertStringContainsString('--color-info:', $this->designSystemContent);
        $this->assertStringContainsString('--color-info-light:', $this->designSystemContent);
        $this->assertStringContainsString('--color-info-border:', $this->designSystemContent);
        $this->assertStringContainsString('--color-info-text:', $this->designSystemContent);
        
        $this->assertStringContainsString('--color-primary:', $this->designSystemContent);
        $this->assertStringContainsString('--color-primary-light:', $this->designSystemContent);
        $this->assertStringContainsString('--color-primary-hover:', $this->designSystemContent);
        $this->assertStringContainsString('--color-primary-active:', $this->designSystemContent);

        // Color variables (neutral)
        $this->assertStringContainsString('--color-gray-50:', $this->designSystemContent);
        $this->assertStringContainsString('--color-gray-100:', $this->designSystemContent);
        $this->assertStringContainsString('--color-gray-200:', $this->designSystemContent);
        $this->assertStringContainsString('--color-gray-300:', $this->designSystemContent);
        $this->assertStringContainsString('--color-gray-400:', $this->designSystemContent);
        $this->assertStringContainsString('--color-gray-500:', $this->designSystemContent);
        $this->assertStringContainsString('--color-gray-600:', $this->designSystemContent);
        $this->assertStringContainsString('--color-gray-700:', $this->designSystemContent);
        $this->assertStringContainsString('--color-gray-800:', $this->designSystemContent);

        // Border color variables
        $this->assertStringContainsString('--border-color-light:', $this->designSystemContent);
        $this->assertStringContainsString('--border-color-medium:', $this->designSystemContent);
        $this->assertStringContainsString('--border-color-dark:', $this->designSystemContent);

        // Shadow variables
        $this->assertStringContainsString('--shadow-sm:', $this->designSystemContent);
        $this->assertStringContainsString('--shadow-md:', $this->designSystemContent);
        $this->assertStringContainsString('--shadow-lg:', $this->designSystemContent);
        $this->assertStringContainsString('--shadow-xl:', $this->designSystemContent);
    }

    /**
     * Test that card-metric component CSS is defined
     */
    public function test_card_metric_component_is_defined(): void
    {
        $this->assertStringContainsString('.card-metric', $this->designSystemContent);
        $this->assertStringContainsString('.border-left-danger', $this->designSystemContent);
        $this->assertStringContainsString('.border-left-warning', $this->designSystemContent);
        $this->assertStringContainsString('.border-left-success', $this->designSystemContent);
        $this->assertStringContainsString('.border-left-primary', $this->designSystemContent);
        $this->assertStringContainsString('.border-left-info', $this->designSystemContent);
        $this->assertStringContainsString('.metric-value', $this->designSystemContent);
        $this->assertStringContainsString('.metric-label', $this->designSystemContent);
        $this->assertStringContainsString('.metric-desc', $this->designSystemContent);
        $this->assertStringContainsString('.card-icon', $this->designSystemContent);
    }

    /**
     * Test that table-corporate component CSS is defined
     */
    public function test_table_corporate_component_is_defined(): void
    {
        $this->assertStringContainsString('.table-corporate', $this->designSystemContent);
        $this->assertStringContainsString('.table-corporate thead th', $this->designSystemContent);
        $this->assertStringContainsString('.table-corporate tbody td', $this->designSystemContent);
        $this->assertStringContainsString('.table-corporate tbody tr:hover', $this->designSystemContent);
    }

    /**
     * Test that badge-corp component CSS is defined
     */
    public function test_badge_corp_component_is_defined(): void
    {
        $this->assertStringContainsString('.badge-corp', $this->designSystemContent);
        $this->assertStringContainsString('.badge-corp-danger', $this->designSystemContent);
        $this->assertStringContainsString('.badge-corp-warning', $this->designSystemContent);
        $this->assertStringContainsString('.badge-corp-success', $this->designSystemContent);
        $this->assertStringContainsString('.badge-corp-info', $this->designSystemContent);
        $this->assertStringContainsString('.badge-corp-primary', $this->designSystemContent);
    }

    /**
     * Test that button components CSS are defined
     */
    public function test_button_components_are_defined(): void
    {
        $this->assertStringContainsString('.btn-action-corp', $this->designSystemContent);
        $this->assertStringContainsString('.btn-primary-corp', $this->designSystemContent);
        $this->assertStringContainsString('.btn-danger-corp', $this->designSystemContent);
        $this->assertStringContainsString('.btn-loading', $this->designSystemContent);
    }

    /**
     * Test that progress-corp component CSS is defined
     */
    public function test_progress_corp_component_is_defined(): void
    {
        $this->assertStringContainsString('.progress-corp-bg', $this->designSystemContent);
        $this->assertStringContainsString('.progress-corp-fill', $this->designSystemContent);
    }

    /**
     * Test that filter-container component CSS is defined
     */
    public function test_filter_container_component_is_defined(): void
    {
        $this->assertStringContainsString('.filter-container', $this->designSystemContent);
        $this->assertStringContainsString('.filter-container .form-label', $this->designSystemContent);
    }

    /**
     * Test that empty-state component CSS is defined
     */
    public function test_empty_state_component_is_defined(): void
    {
        $this->assertStringContainsString('.empty-state', $this->designSystemContent);
        $this->assertStringContainsString('.empty-state i', $this->designSystemContent);
        $this->assertStringContainsString('.empty-state h5', $this->designSystemContent);
        $this->assertStringContainsString('.empty-state p', $this->designSystemContent);
    }

    /**
     * Test that page-header component CSS is defined
     */
    public function test_page_header_component_is_defined(): void
    {
        $this->assertStringContainsString('.page-header', $this->designSystemContent);
        $this->assertStringContainsString('.page-header h3', $this->designSystemContent);
        $this->assertStringContainsString('.page-header p', $this->designSystemContent);
    }

    /**
     * Test that mobile responsive styles are defined
     */
    public function test_mobile_responsive_styles_are_defined(): void
    {
        $this->assertStringContainsString('@media (max-width: 768px)', $this->designSystemContent);
        $this->assertStringContainsString('.table-corporate thead', $this->designSystemContent);
        $this->assertStringContainsString('display: none', $this->designSystemContent);
    }

    /**
     * Test that CSS uses variables instead of hardcoded values
     */
    public function test_css_uses_variables(): void
    {
        // Check that var() is used extensively
        $varCount = substr_count($this->designSystemContent, 'var(--');
        $this->assertGreaterThan(50, $varCount, 'CSS should use CSS variables extensively (found: ' . $varCount . ')');
    }

    /**
     * Test that test page exists and can be rendered
     */
    public function test_test_page_exists_and_renders(): void
    {
        $testPagePath = resource_path('views/admin/maintenance/test-design-system.blade.php');
        $this->assertFileExists($testPagePath);
        
        $testPageContent = file_get_contents($testPagePath);
        
        // Verify test page includes design system partial
        $this->assertStringContainsString("@include('admin.maintenance.partials._design-system')", $testPageContent);
        
        // Verify test page has component examples
        $this->assertStringContainsString('card-metric', $testPageContent);
        $this->assertStringContainsString('badge-corp', $testPageContent);
        $this->assertStringContainsString('btn-action-corp', $testPageContent);
        $this->assertStringContainsString('table-corporate', $testPageContent);
        $this->assertStringContainsString('filter-container', $testPageContent);
        $this->assertStringContainsString('empty-state', $testPageContent);
    }

    /**
     * Test that design system has proper section organization
     */
    public function test_design_system_has_proper_sections(): void
    {
        // Check for section comments
        $this->assertStringContainsString('SECTION 1: CSS VARIABLES', $this->designSystemContent);
        $this->assertStringContainsString('SECTION 2: CARD METRIC COMPONENT', $this->designSystemContent);
        $this->assertStringContainsString('SECTION 3: TABLE CORPORATE COMPONENT', $this->designSystemContent);
        $this->assertStringContainsString('SECTION 4: BADGE COMPONENT', $this->designSystemContent);
        $this->assertStringContainsString('SECTION 5: BUTTON COMPONENTS', $this->designSystemContent);
        $this->assertStringContainsString('SECTION 6: PROGRESS BAR COMPONENT', $this->designSystemContent);
        $this->assertStringContainsString('SECTION 7: FILTER CONTAINER COMPONENT', $this->designSystemContent);
        $this->assertStringContainsString('SECTION 8: EMPTY STATE COMPONENT', $this->designSystemContent);
        $this->assertStringContainsString('SECTION 9: PAGE HEADER COMPONENT', $this->designSystemContent);
        $this->assertStringContainsString('SECTION 10: MOBILE RESPONSIVE STYLES', $this->designSystemContent);
    }

    /**
     * Test that card-metric has hover and active states
     */
    public function test_card_metric_has_interactive_states(): void
    {
        $this->assertStringContainsString('.card-metric:hover', $this->designSystemContent);
        $this->assertStringContainsString('.card-metric.active', $this->designSystemContent);
        $this->assertStringContainsString('transform: translateY(-2px)', $this->designSystemContent);
        $this->assertStringContainsString('transform: translateY(-4px)', $this->designSystemContent);
    }

    /**
     * Test that buttons have hover states
     */
    public function test_buttons_have_hover_states(): void
    {
        $this->assertStringContainsString('.btn-action-corp:hover', $this->designSystemContent);
        $this->assertStringContainsString('.btn-primary-corp:hover', $this->designSystemContent);
        $this->assertStringContainsString('.btn-danger-corp:hover', $this->designSystemContent);
    }

    /**
     * Test that buttons have disabled states
     */
    public function test_buttons_have_disabled_states(): void
    {
        $this->assertStringContainsString(':disabled', $this->designSystemContent);
        $this->assertStringContainsString('opacity: 0.6', $this->designSystemContent);
        $this->assertStringContainsString('cursor: not-allowed', $this->designSystemContent);
    }

    /**
     * Test that color consistency is maintained
     */
    public function test_color_consistency(): void
    {
        // Danger color should be #dc3545
        $this->assertStringContainsString('--color-danger: #dc3545', $this->designSystemContent);
        
        // Warning color should be #ffc107
        $this->assertStringContainsString('--color-warning: #ffc107', $this->designSystemContent);
        
        // Success color should be #198754
        $this->assertStringContainsString('--color-success: #198754', $this->designSystemContent);
        
        // Info color should be #0dcaf0
        $this->assertStringContainsString('--color-info: #0dcaf0', $this->designSystemContent);
        
        // Primary color should be #0d6efd
        $this->assertStringContainsString('--color-primary: #0d6efd', $this->designSystemContent);
    }
}
