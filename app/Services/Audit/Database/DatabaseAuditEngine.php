<?php

namespace App\Services\Audit\Database;

use App\Contracts\AuditEngine;
use App\Contracts\Scanner;
use App\DataModels\Audit\AuditReport;
use App\DataModels\Audit\AuditResult;
use DateTime;
use Exception;
use Illuminate\Support\Facades\Log;

/**
 * DatabaseAuditEngine
 * 
 * Orchestrates database analyzers for index optimization, query patterns,
 * transaction management, and connection configuration analysis.
 * 
 * @package App\Services\Audit\Database
 */
class DatabaseAuditEngine implements AuditEngine
{
    /** @var array<int, Scanner> */
    private array $analyzers = [];

    /** @var array<int, AuditResult> */
    private array $findings = [];

    /**
     * Register a database analyzer
     * 
     * @param Scanner $analyzer
     * @return void
     */
    public function addAnalyzer(Scanner $analyzer): void
    {
        $this->analyzers[] = $analyzer;
    }

    /**
     * Analyze database configuration and usage patterns
     * 
     * @param array<string, mixed> $options
     * @return array<int, AuditResult>
     */
    public function analyze(array $options = []): array
    {
        $this->findings = [];

        Log::info('DatabaseAuditEngine: Starting database analysis', [
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

                Log::debug("DatabaseAuditEngine: Analyzer {$analyzerClass} completed", [
                    'findings_count' => count($results),
                ]);

            } catch (Exception $e) {
                Log::error("DatabaseAuditEngine: Analyzer {$analyzerClass} failed", [
                    'error' => $e->getMessage(),
                ]);

                $this->findings[] = new AuditResult(
                    type: 'database',
                    severity: 'medium',
                    category: 'configuration',
                    message: "Database analyzer failed: {$analyzerClass}",
                    details: ['error' => $e->getMessage()],
                );
            }
        }

        Log::info('DatabaseAuditEngine: Analysis completed', [
            'total_findings' => count($this->findings),
        ]);

        return $this->findings;
    }

    /**
     * Generate a complete database audit report
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

        $this->addDatabaseRecommendations($report);

        return $report;
    }

    /**
     * Add database-specific recommendations based on findings
     * 
     * @param AuditReport $report
     * @return void
     */
    private function addDatabaseRecommendations(AuditReport $report): void
    {
        $summary = $report->getSummary();
        $byCategory = $summary['by_category'] ?? [];

        if (isset($byCategory['index_optimization']) && $byCategory['index_optimization'] > 0) {
            $report->addRecommendation('Add missing database indexes to improve query performance');
        }

        if (isset($byCategory['query_optimization']) && $byCategory['query_optimization'] > 0) {
            $report->addRecommendation('Optimize raw SQL queries: use parameterized queries, avoid SELECT *, add proper WHERE clauses');
        }

        if (isset($byCategory['transaction_management']) && $byCategory['transaction_management'] > 0) {
            $report->addRecommendation('Review transaction management: wrap related operations in DB::transaction(), configure proper isolation levels');
        }

        if (isset($byCategory['configuration']) && $byCategory['configuration'] > 0) {
            $report->addRecommendation('Review database connection configuration: pool sizing, timeouts, and failover settings');
        }
    }
}
