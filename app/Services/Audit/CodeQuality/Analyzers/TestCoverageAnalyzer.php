<?php

namespace App\Services\Audit\CodeQuality\Analyzers;

use App\Contracts\Scanner;
use App\DataModels\Audit\AuditResult;

/**
 * TestCoverageAnalyzer
 * 
 * Evaluates test coverage by analyzing the presence and quality of test files
 * corresponding to controllers, services, and models. Checks for testing
 * framework configuration and critical business logic coverage.
 * 
 * @package App\Services\Audit\CodeQuality\Analyzers
 */
class TestCoverageAnalyzer implements Scanner
{
    private array $findings = [];
    private string $basePath;

    public function __construct(?string $basePath = null)
    {
        $this->basePath = $basePath ?? base_path();
    }

    /**
     * @param array<string, mixed> $options
     * @return array<int, AuditResult>
     */
    public function scan(array $options = []): array
    {
        $this->findings = [];

        $this->checkTestExistence();
        $this->checkTestFrameworkConfig();
        $this->analyzeCriticalPathCoverage();

        return $this->findings;
    }

    public function getFindings(): array
    {
        return $this->findings;
    }

    /**
     * Check if test files exist for controllers and services
     */
    private function checkTestExistence(): void
    {
        $testDir = $this->basePath . '/tests';
        if (!is_dir($testDir)) {
            $this->findings[] = new AuditResult(
                type: 'code_quality',
                severity: 'critical',
                category: 'testing',
                message: 'No tests directory found — application has no automated tests',
                details: [
                    'recommendation' => 'Create tests/ directory and add Feature and Unit tests',
                ],
            );
            return;
        }

        // Count test files
        $testFiles = $this->countPhpFiles($testDir);

        // Count source files (controllers + services)
        $sourceFiles = 0;
        foreach (['app/Http/Controllers', 'app/Services'] as $subDir) {
            $dir = $this->basePath . '/' . $subDir;
            if (is_dir($dir)) {
                $sourceFiles += $this->countPhpFiles($dir);
            }
        }

        if ($sourceFiles > 0) {
            $ratio = round(($testFiles / $sourceFiles) * 100);

            if ($ratio < 30) {
                $severity = $ratio < 10 ? 'high' : 'medium';
                $this->findings[] = new AuditResult(
                    type: 'code_quality',
                    severity: $severity,
                    category: 'testing',
                    message: "Test-to-source ratio is {$ratio}% ({$testFiles} tests / {$sourceFiles} source files)",
                    details: [
                        'test_files' => $testFiles,
                        'source_files' => $sourceFiles,
                        'ratio' => $ratio,
                        'recommendation' => 'Aim for at least 1 test file per controller/service (minimum 50% ratio)',
                    ],
                );
            }
        }

        // Check if Feature and Unit directories exist
        if (!is_dir($testDir . '/Feature')) {
            $this->findings[] = new AuditResult(
                type: 'code_quality',
                severity: 'medium',
                category: 'testing',
                message: 'No Feature tests directory — feature/integration tests are missing',
                details: [
                    'recommendation' => 'Add tests/Feature/ with tests for critical API endpoints and user flows',
                ],
            );
        }

        if (!is_dir($testDir . '/Unit')) {
            $this->findings[] = new AuditResult(
                type: 'code_quality',
                severity: 'low',
                category: 'testing',
                message: 'No Unit tests directory — unit tests are missing',
                details: [
                    'recommendation' => 'Add tests/Unit/ with tests for service and model business logic',
                ],
            );
        }
    }

    /**
     * Check test framework configuration
     */
    private function checkTestFrameworkConfig(): void
    {
        $phpunitConfig = $this->basePath . '/phpunit.xml';
        $phpunitDist = $this->basePath . '/phpunit.xml.dist';

        if (!file_exists($phpunitConfig) && !file_exists($phpunitDist)) {
            $this->findings[] = new AuditResult(
                type: 'code_quality',
                severity: 'medium',
                category: 'testing',
                message: 'No PHPUnit configuration file found (phpunit.xml or phpunit.xml.dist)',
                details: [
                    'recommendation' => 'Add phpunit.xml configuration for consistent test execution',
                ],
            );
            return;
        }

        // Check for test database configuration
        $configFile = file_exists($phpunitConfig) ? $phpunitConfig : $phpunitDist;
        $content = file_get_contents($configFile);

        if (!str_contains($content, 'DB_CONNECTION') && !str_contains($content, 'testing')) {
            $this->findings[] = new AuditResult(
                type: 'code_quality',
                severity: 'medium',
                category: 'testing',
                message: 'No test database configuration in PHPUnit config — tests may affect development database',
                details: [
                    'file' => $configFile,
                    'recommendation' => 'Add <env name="DB_CONNECTION" value="sqlite"/> and <env name="DB_DATABASE" value=":memory:"/> for test isolation',
                ],
            );
        }
    }

    /**
     * Analyze test coverage for critical business paths
     */
    private function analyzeCriticalPathCoverage(): void
    {
        $criticalPatterns = [
            'Auth' => 'Authentication',
            'Login' => 'Login',
            'Absen' => 'Attendance',
            'Payment' => 'Payment',
        ];

        $testDir = $this->basePath . '/tests';
        if (!is_dir($testDir)) return;

        foreach ($criticalPatterns as $pattern => $description) {
            // Check if critical controllers exist
            $controllerDir = $this->basePath . '/app/Http/Controllers';
            if (!is_dir($controllerDir)) continue;

            $hasController = $this->findFileContaining($controllerDir, $pattern);

            if ($hasController) {
                $hasTest = $this->findFileContaining($testDir, $pattern);

                if (!$hasTest) {
                    $this->findings[] = new AuditResult(
                        type: 'code_quality',
                        severity: 'high',
                        category: 'testing',
                        message: "Critical '{$description}' functionality has no tests",
                        details: [
                            'pattern' => $pattern,
                            'recommendation' => "Add Feature tests for {$description} controllers and their edge cases",
                        ],
                    );
                }
            }
        }
    }

    /**
     * Count PHP files recursively in a directory
     */
    private function countPhpFiles(string $dir): int
    {
        $count = 0;
        $iterator = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($dir, \RecursiveDirectoryIterator::SKIP_DOTS)
        );

        foreach ($iterator as $file) {
            if ($file->getExtension() === 'php') $count++;
        }

        return $count;
    }

    /**
     * Check if any file in directory contains a pattern in its name
     */
    private function findFileContaining(string $dir, string $pattern): bool
    {
        $iterator = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($dir, \RecursiveDirectoryIterator::SKIP_DOTS)
        );

        foreach ($iterator as $file) {
            if ($file->getExtension() === 'php' && str_contains($file->getFilename(), $pattern)) {
                return true;
            }
        }

        return false;
    }
}
