<?php

namespace App\Services\Audit\CodeQuality;

use App\Contracts\AuditEngine;
use App\Contracts\Scanner;
use App\DataModels\Audit\AuditReport;
use App\DataModels\Audit\AuditResult;
use DateTime;
use Exception;
use Illuminate\Support\Facades\Log;

/**
 * CodeQualityEngine
 * 
 * Orchestrates code quality analyzers for style checking, complexity analysis,
 * test coverage evaluation, and documentation completeness assessment.
 * 
 * @package App\Services\Audit\CodeQuality
 */
class CodeQualityEngine implements AuditEngine
{
    /** @var array<int, Scanner> */
    private array $analyzers = [];

    /** @var array<int, AuditResult> */
    private array $findings = [];

    /**
     * Register a code quality analyzer
     * 
     * @param Scanner $analyzer
     * @return void
     */
    public function addAnalyzer(Scanner $analyzer): void
    {
        $this->analyzers[] = $analyzer;
    }

    /**
     * Analyze codebase for quality issues
     * 
     * @param array<string, mixed> $options
     * @return array<int, AuditResult>
     */
    public function analyze(array $options = []): array
    {
        $this->findings = [];

        Log::info('CodeQualityEngine: Starting code quality analysis', [
            'analyzer_count' => count($this->analyzers),
        ]);

        foreach ($this->analyzers as $analyzer) {
            $analyzerClass = get_class($analyzer);

            try {
                $results = $analyzer->scan($options);

                foreach ($results as $result) {
                    if ($result instanceof AuditResult) {
                        $this->findings[] = $result;
                    }
                }

                Log::debug("CodeQualityEngine: Analyzer {$analyzerClass} completed", [
                    'findings_count' => count($results),
                ]);

            } catch (Exception $e) {
                Log::error("CodeQualityEngine: Analyzer {$analyzerClass} failed", [
                    'error' => $e->getMessage(),
                ]);

                $this->findings[] = new AuditResult(
                    type: 'code_quality',
                    severity: 'medium',
                    category: 'configuration',
                    message: "Code quality analyzer failed: {$analyzerClass}",
                    details: ['error' => $e->getMessage()],
                );
            }
        }

        Log::info('CodeQualityEngine: Analysis completed', [
            'total_findings' => count($this->findings),
        ]);

        return $this->findings;
    }

    /**
     * Generate a complete code quality audit report
     * 
     * @param array<string, mixed> $options
     * @return AuditReport
     */
    public function generateReport(array $options = []): AuditReport
    {
        $findings = $this->analyze($options);

        $report = new AuditReport(
            id: AuditReport::generateId(),
            timestamp: new DateTime(),
            results: $findings,
        );

        $this->addCodeQualityRecommendations($report);

        return $report;
    }

    /**
     * Add code quality recommendations
     * 
     * @param AuditReport $report
     * @return void
     */
    private function addCodeQualityRecommendations(AuditReport $report): void
    {
        $summary = $report->getSummary();
        $byCategory = $summary['by_category'] ?? [];

        if (isset($byCategory['code_style']) && $byCategory['code_style'] > 0) {
            $report->addRecommendation('Enforce consistent code style: configure PHP-CS-Fixer or Laravel Pint for automated formatting');
        }

        if (isset($byCategory['complexity']) && $byCategory['complexity'] > 0) {
            $report->addRecommendation('Reduce code complexity: extract methods, apply Single Responsibility Principle, use strategy patterns');
        }

        if (isset($byCategory['testing']) && $byCategory['testing'] > 0) {
            $report->addRecommendation('Improve test coverage: add feature tests for critical endpoints, unit tests for business logic');
        }

        if (isset($byCategory['documentation']) && $byCategory['documentation'] > 0) {
            $report->addRecommendation('Add missing documentation: PHPDoc blocks on public methods, README updates, API documentation');
        }
    }
}
