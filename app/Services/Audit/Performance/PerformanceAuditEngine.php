<?php

namespace App\Services\Audit\Performance;

use App\Contracts\AuditEngine;
use App\Contracts\Scanner;
use App\DataModels\Audit\AuditReport;
use App\DataModels\Audit\AuditResult;
use DateTime;
use Exception;
use Illuminate\Support\Facades\Log;

/**
 * PerformanceAuditEngine
 * 
 * Orchestrates performance analyzers for query optimization, caching,
 * API response analysis, and memory usage.
 * 
 * @package App\Services\Audit\Performance
 */
class PerformanceAuditEngine implements AuditEngine
{
    /** @var array<int, Scanner> */
    private array $analyzers = [];

    /** @var array<int, AuditResult> */
    private array $findings = [];

    public function addAnalyzer(Scanner $analyzer): void
    {
        $this->analyzers[] = $analyzer;
    }

    public function analyze(array $options = []): array
    {
        $this->findings = [];

        Log::info('PerformanceAuditEngine: Starting performance analysis', [
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

            } catch (Exception $e) {
                Log::error("PerformanceAuditEngine: Analyzer {$analyzerClass} failed", [
                    'error' => $e->getMessage(),
                ]);

                $this->findings[] = new AuditResult(
                    type: 'performance',
                    severity: 'medium',
                    category: 'configuration',
                    message: "Performance analyzer failed: {$analyzerClass}",
                    details: ['error' => $e->getMessage()],
                );
            }
        }

        return $this->findings;
    }

    public function generateReport(array $options = []): AuditReport
    {
        $findings = $this->analyze($options);

        $report = new AuditReport(
            id: AuditReport::generateId(),
            timestamp: new DateTime(),
            results: $findings,
        );

        $this->addPerformanceRecommendations($report);

        return $report;
    }

    private function addPerformanceRecommendations(AuditReport $report): void
    {
        $summary = $report->getSummary();
        $byCategory = $summary['by_category'] ?? [];

        if (isset($byCategory['query_optimization']) && $byCategory['query_optimization'] > 0) {
            $report->addRecommendation('Optimize database queries: add eager loading, fix N+1 patterns, use pagination');
        }

        if (isset($byCategory['caching']) && $byCategory['caching'] > 0) {
            $report->addRecommendation('Implement caching: use Cache::remember() for frequently accessed data, consider Redis');
        }

        if (isset($byCategory['memory_usage']) && $byCategory['memory_usage'] > 0) {
            $report->addRecommendation('Reduce memory usage: use chunk() for batch processing, lazy collections for large datasets');
        }
    }
}
