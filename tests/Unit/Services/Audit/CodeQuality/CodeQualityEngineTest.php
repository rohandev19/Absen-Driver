<?php

namespace Tests\Unit\Services\Audit\CodeQuality;

use App\DataModels\Audit\AuditResult;
use App\Services\Audit\CodeQuality\CodeQualityEngine;
use App\Services\Audit\CodeQuality\Analyzers\StyleChecker;
use App\Services\Audit\CodeQuality\Analyzers\ComplexityAnalyzer;
use App\Services\Audit\CodeQuality\Analyzers\TestCoverageAnalyzer;
use App\Services\Audit\CodeQuality\Analyzers\DocumentationAnalyzer;
use Tests\TestCase;

/**
 * Test suite for CodeQualityEngine and its analyzers
 */
class CodeQualityEngineTest extends TestCase
{
    private string $testBasePath;

    protected function setUp(): void
    {
        parent::setUp();
        $this->testBasePath = sys_get_temp_dir() . '/audit_test_quality_' . uniqid();
        mkdir($this->testBasePath, 0755, true);
        mkdir($this->testBasePath . '/app/Http/Controllers', 0755, true);
        mkdir($this->testBasePath . '/app/Services', 0755, true);
        mkdir($this->testBasePath . '/app/Models', 0755, true);
        mkdir($this->testBasePath . '/tests/Feature', 0755, true);
        mkdir($this->testBasePath . '/tests/Unit', 0755, true);
    }

    protected function tearDown(): void
    {
        $this->deleteDir($this->testBasePath);
        parent::tearDown();
    }

    /**
     * Test engine runs with no analyzers
     */
    public function test_engine_runs_with_no_analyzers(): void
    {
        $engine = new CodeQualityEngine();
        $results = $engine->analyze();

        $this->assertIsArray($results);
        $this->assertCount(0, $results);
    }

    /**
     * Test engine generates a report
     */
    public function test_engine_generates_report(): void
    {
        $engine = new CodeQualityEngine();
        $report = $engine->generateReport();

        $this->assertNotNull($report);
        $this->assertStringStartsWith('audit_', $report->getId());
    }

    /**
     * Test StyleChecker detects non-PascalCase controller names
     */
    public function test_style_checker_detects_bad_naming(): void
    {
        file_put_contents(
            $this->testBasePath . '/app/Http/Controllers/bad_controller.php',
            '<?php class bad_controller {}'
        );

        $checker = new StyleChecker($this->testBasePath);
        $results = $checker->scan();

        $namingFindings = array_filter(
            $results,
            fn(AuditResult $r) => str_contains($r->getMessage(), 'PascalCase')
        );

        $this->assertNotEmpty($namingFindings, 'Should detect non-PascalCase names');
    }

    /**
     * Test StyleChecker detects missing Controller suffix
     */
    public function test_style_checker_detects_missing_controller_suffix(): void
    {
        file_put_contents(
            $this->testBasePath . '/app/Http/Controllers/UserManager.php',
            '<?php class UserManager {}'
        );

        $checker = new StyleChecker($this->testBasePath);
        $results = $checker->scan();

        $suffixFindings = array_filter(
            $results,
            fn(AuditResult $r) => str_contains($r->getMessage(), 'Controller')
        );

        $this->assertNotEmpty($suffixFindings, 'Should detect missing Controller suffix');
    }

    /**
     * Test StyleChecker detects large commented-out code blocks
     */
    public function test_style_checker_detects_dead_code(): void
    {
        $code = "<?php\nclass TestController {\n    public function index() {\n";
        for ($i = 0; $i < 6; $i++) {
            $code .= "        // \$old = SomeModel::find(\$id);\n";
        }
        $code .= "    }\n}\n";

        file_put_contents(
            $this->testBasePath . '/app/Http/Controllers/TestController.php',
            $code
        );

        $checker = new StyleChecker($this->testBasePath);
        $results = $checker->scan();

        $deadCodeFindings = array_filter(
            $results,
            fn(AuditResult $r) => str_contains($r->getMessage(), 'commented-out')
        );

        $this->assertNotEmpty($deadCodeFindings, 'Should detect dead code blocks');
    }

    /**
     * Test ComplexityAnalyzer detects long methods
     */
    public function test_complexity_analyzer_detects_long_methods(): void
    {
        $code = "<?php\nclass TestService {\n    public function longMethod() {\n";
        for ($i = 0; $i < 60; $i++) {
            $code .= "        \$var{$i} = {$i};\n";
        }
        $code .= "    }\n}\n";

        file_put_contents(
            $this->testBasePath . '/app/Services/TestService.php',
            $code
        );

        $analyzer = new ComplexityAnalyzer($this->testBasePath);
        $results = $analyzer->scan();

        $longMethodFindings = array_filter(
            $results,
            fn(AuditResult $r) => str_contains($r->getMessage(), 'longMethod')
        );

        $this->assertNotEmpty($longMethodFindings, 'Should detect long methods');
    }

    /**
     * Test ComplexityAnalyzer detects too many parameters
     */
    public function test_complexity_analyzer_detects_many_parameters(): void
    {
        $code = <<<'PHP'
<?php
class TestService {
    public function tooManyParams($a, $b, $c, $d, $e, $f, $g) {
        return $a + $b + $c + $d + $e + $f + $g;
    }
}
PHP;

        file_put_contents(
            $this->testBasePath . '/app/Services/TestService.php',
            $code
        );

        $analyzer = new ComplexityAnalyzer($this->testBasePath);
        $results = $analyzer->scan();

        $paramFindings = array_filter(
            $results,
            fn(AuditResult $r) => str_contains($r->getMessage(), 'tooManyParams')
        );

        $this->assertNotEmpty($paramFindings, 'Should detect excessive parameters');
    }

    /**
     * Test TestCoverageAnalyzer detects missing tests directory
     */
    public function test_coverage_analyzer_with_missing_tests_dir(): void
    {
        // Remove tests directory
        $this->deleteDir($this->testBasePath . '/tests');

        $analyzer = new TestCoverageAnalyzer($this->testBasePath);
        $results = $analyzer->scan();

        $missingTests = array_filter(
            $results,
            fn(AuditResult $r) => str_contains($r->getMessage(), 'no automated tests')
        );

        $this->assertNotEmpty($missingTests, 'Should detect missing tests directory');
    }

    /**
     * Test DocumentationAnalyzer detects missing README
     */
    public function test_documentation_analyzer_detects_missing_readme(): void
    {
        $analyzer = new DocumentationAnalyzer($this->testBasePath);
        $results = $analyzer->scan();

        $readmeFindings = array_filter(
            $results,
            fn(AuditResult $r) => str_contains($r->getMessage(), 'README')
        );

        $this->assertNotEmpty($readmeFindings, 'Should detect missing README');
    }

    /**
     * Test full engine with all analyzers
     */
    public function test_full_engine_with_analyzers(): void
    {
        // Create a controller with issues
        $code = "<?php\nclass bad_name {\n    public function index() {\n";
        for ($i = 0; $i < 60; $i++) {
            $code .= "        \$var{$i} = {$i};\n";
        }
        $code .= "    }\n}\n";

        file_put_contents(
            $this->testBasePath . '/app/Http/Controllers/bad_name.php',
            $code
        );

        $engine = new CodeQualityEngine();
        $engine->addAnalyzer(new StyleChecker($this->testBasePath));
        $engine->addAnalyzer(new ComplexityAnalyzer($this->testBasePath));
        $engine->addAnalyzer(new TestCoverageAnalyzer($this->testBasePath));
        $engine->addAnalyzer(new DocumentationAnalyzer($this->testBasePath));

        $report = $engine->generateReport();

        $this->assertGreaterThan(0, $report->getTotalFindings());
        $this->assertNotEmpty($report->getRecommendations());
    }

    /**
     * Recursively delete directory
     */
    private function deleteDir(string $dir): void
    {
        if (!is_dir($dir)) return;

        $items = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($dir, \RecursiveDirectoryIterator::SKIP_DOTS),
            \RecursiveIteratorIterator::CHILD_FIRST
        );

        foreach ($items as $item) {
            $item->isDir() ? rmdir($item->getPathname()) : unlink($item->getPathname());
        }

        rmdir($dir);
    }
}
