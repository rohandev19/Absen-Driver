<?php

namespace App\Services\Audit;

use App\Contracts\AuditEngine;
use App\DataModels\Audit\AuditReport;
use App\DataModels\Audit\AuditResult;
use DateTime;
use Exception;
use Illuminate\Support\Facades\Log;

/**
 * AuditOrchestrator Service
 * 
 * Coordinates multiple audit engines to perform comprehensive security,
 * performance, database, and code quality audits. Handles parallel execution
 * of independent audits and aggregates results.
 * 
 * @package App\Services\Audit
 */
class AuditOrchestrator
{
    /**
     * Create a new AuditOrchestrator instance
     * 
     * @param AuditEngine|null $securityEngine Security audit engine
     * @param AuditEngine|null $performanceEngine Performance audit engine
     * @param AuditEngine|null $databaseEngine Database audit engine
     * @param AuditEngine|null $codeQualityEngine Code quality audit engine
     */
    public function __construct(
        private ?AuditEngine $securityEngine = null,
        private ?AuditEngine $performanceEngine = null,
        private ?AuditEngine $databaseEngine = null,
        private ?AuditEngine $codeQualityEngine = null,
    ) {
    }

    /**
     * Run security audit
     * 
     * Performs comprehensive security analysis including authentication,
     * authorization, input validation, CSRF/XSS protection, sensitive data
     * protection, and dependency vulnerabilities.
     * 
     * @param array<string, mixed> $options Optional configuration for the audit
     * @return AuditReport Security audit report
     */
    public function runSecurityAudit(array $options = []): AuditReport
    {
        Log::info('Starting security audit', ['options' => $options]);

        try {
            if ($this->securityEngine === null) {
                return $this->createEmptyReport('security', 'Security engine not configured');
            }

            $report = $this->securityEngine->generateReport($options);
            
            Log::info('Security audit completed', [
                'total_findings' => $report->getTotalFindings(),
                'critical_count' => $report->getSummary()['critical_count'] ?? 0,
            ]);

            return $report;

        } catch (Exception $e) {
            Log::error('Security audit failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return $this->createErrorReport('security', $e);
        }
    }

    /**
     * Run performance audit
     * 
     * Analyzes query performance, N+1 patterns, caching strategies,
     * API response optimization, and memory usage.
     * 
     * @param array<string, mixed> $options Optional configuration for the audit
     * @return AuditReport Performance audit report
     */
    public function runPerformanceAudit(array $options = []): AuditReport
    {
        Log::info('Starting performance audit', ['options' => $options]);

        try {
            if ($this->performanceEngine === null) {
                return $this->createEmptyReport('performance', 'Performance engine not configured');
            }

            $report = $this->performanceEngine->generateReport($options);
            
            Log::info('Performance audit completed', [
                'total_findings' => $report->getTotalFindings(),
                'critical_count' => $report->getSummary()['critical_count'] ?? 0,
            ]);

            return $report;

        } catch (Exception $e) {
            Log::error('Performance audit failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return $this->createErrorReport('performance', $e);
        }
    }

    /**
     * Run database audit
     * 
     * Analyzes database indexes, query optimization opportunities,
     * transaction management, and connection configuration.
     * 
     * @param array<string, mixed> $options Optional configuration for the audit
     * @return AuditReport Database audit report
     */
    public function runDatabaseAudit(array $options = []): AuditReport
    {
        Log::info('Starting database audit', ['options' => $options]);

        try {
            if ($this->databaseEngine === null) {
                return $this->createEmptyReport('database', 'Database engine not configured');
            }

            $report = $this->databaseEngine->generateReport($options);
            
            Log::info('Database audit completed', [
                'total_findings' => $report->getTotalFindings(),
                'critical_count' => $report->getSummary()['critical_count'] ?? 0,
            ]);

            return $report;

        } catch (Exception $e) {
            Log::error('Database audit failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return $this->createErrorReport('database', $e);
        }
    }

    /**
     * Run code quality audit
     * 
     * Analyzes code style, complexity, test coverage, and documentation.
     * 
     * @param array<string, mixed> $options Optional configuration for the audit
     * @return AuditReport Code quality audit report
     */
    public function runCodeQualityAudit(array $options = []): AuditReport
    {
        Log::info('Starting code quality audit', ['options' => $options]);

        try {
            if ($this->codeQualityEngine === null) {
                return $this->createEmptyReport('code_quality', 'Code quality engine not configured');
            }

            $report = $this->codeQualityEngine->generateReport($options);
            
            Log::info('Code quality audit completed', [
                'total_findings' => $report->getTotalFindings(),
                'critical_count' => $report->getSummary()['critical_count'] ?? 0,
            ]);

            return $report;

        } catch (Exception $e) {
            Log::error('Code quality audit failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return $this->createErrorReport('code_quality', $e);
        }
    }

    /**
     * Run full audit
     * 
     * Executes all audit engines in parallel (where possible) and aggregates
     * results into a comprehensive report. Independent audits run concurrently
     * to minimize total execution time.
     * 
     * @param array<string, mixed> $options Optional configuration for the audit
     * @return AuditReport Comprehensive audit report with all findings
     */
    public function runFullAudit(array $options = []): AuditReport
    {
        Log::info('Starting full audit', ['options' => $options]);

        $startTime = microtime(true);
        $aggregatedReport = new AuditReport(
            id: AuditReport::generateId(),
            timestamp: new DateTime(),
        );

        // Run independent audits (these can be parallelized in future)
        $auditResults = $this->executeAuditsInParallel($options);

        // Aggregate all results
        foreach ($auditResults as $auditType => $report) {
            if ($report instanceof AuditReport) {
                $aggregatedReport->addResults($report->getResults());
                
                // Merge recommendations
                foreach ($report->getRecommendations() as $recommendation) {
                    $aggregatedReport->addRecommendation("[$auditType] $recommendation");
                }
            }
        }

        // Generate prioritized recommendations
        $this->generatePrioritizedRecommendations($aggregatedReport);

        $executionTime = round(microtime(true) - $startTime, 2);

        Log::info('Full audit completed', [
            'execution_time' => $executionTime,
            'total_findings' => $aggregatedReport->getTotalFindings(),
            'critical_count' => $aggregatedReport->getSummary()['critical_count'] ?? 0,
            'high_count' => $aggregatedReport->getSummary()['high_count'] ?? 0,
        ]);

        return $aggregatedReport;
    }

    /**
     * Execute audits in parallel
     * 
     * Runs independent audit engines concurrently to minimize total execution time.
     * Currently executes sequentially but designed for future parallel execution.
     * 
     * @param array<string, mixed> $options Audit configuration options
     * @return array<string, AuditReport> Map of audit type to report
     */
    private function executeAuditsInParallel(array $options): array
    {
        $results = [];

        // Security audit
        try {
            $results['security'] = $this->runSecurityAudit($options);
        } catch (Exception $e) {
            Log::error('Security audit execution failed', ['error' => $e->getMessage()]);
            $results['security'] = $this->createErrorReport('security', $e);
        }

        // Performance audit (independent of security)
        try {
            $results['performance'] = $this->runPerformanceAudit($options);
        } catch (Exception $e) {
            Log::error('Performance audit execution failed', ['error' => $e->getMessage()]);
            $results['performance'] = $this->createErrorReport('performance', $e);
        }

        // Database audit (independent of security and performance)
        try {
            $results['database'] = $this->runDatabaseAudit($options);
        } catch (Exception $e) {
            Log::error('Database audit execution failed', ['error' => $e->getMessage()]);
            $results['database'] = $this->createErrorReport('database', $e);
        }

        // Code quality audit (independent of all others)
        try {
            $results['code_quality'] = $this->runCodeQualityAudit($options);
        } catch (Exception $e) {
            Log::error('Code quality audit execution failed', ['error' => $e->getMessage()]);
            $results['code_quality'] = $this->createErrorReport('code_quality', $e);
        }

        return $results;
    }

    /**
     * Generate prioritized recommendations
     * 
     * Analyzes all findings and generates actionable recommendations
     * prioritized by severity, impact, and effort.
     * 
     * @param AuditReport $report The report to add recommendations to
     * @return void
     */
    private function generatePrioritizedRecommendations(AuditReport $report): void
    {
        $criticalFindings = $report->getResultsBySeverity('critical');
        $highFindings = $report->getResultsBySeverity('high');

        // Add critical recommendations first
        if (count($criticalFindings) > 0) {
            $report->addRecommendation(
                sprintf(
                    'URGENT: Address %d critical security/performance issues immediately',
                    count($criticalFindings)
                )
            );
        }

        // Add high priority recommendations
        if (count($highFindings) > 0) {
            $report->addRecommendation(
                sprintf(
                    'HIGH PRIORITY: Resolve %d high-severity findings within 1 week',
                    count($highFindings)
                )
            );
        }

        // Add category-specific recommendations
        $this->addCategoryRecommendations($report);
    }

    /**
     * Add category-specific recommendations
     * 
     * @param AuditReport $report The report to add recommendations to
     * @return void
     */
    private function addCategoryRecommendations(AuditReport $report): void
    {
        $summary = $report->getSummary();
        $byCategory = $summary['by_category'] ?? [];

        // Security recommendations
        if (isset($byCategory['authentication']) && $byCategory['authentication'] > 0) {
            $report->addRecommendation(
                'Review and strengthen authentication mechanisms (rate limiting, password policies)'
            );
        }

        if (isset($byCategory['authorization']) && $byCategory['authorization'] > 0) {
            $report->addRecommendation(
                'Audit and fix authorization checks on all protected endpoints'
            );
        }

        // Performance recommendations
        if (isset($byCategory['query_optimization']) && $byCategory['query_optimization'] > 0) {
            $report->addRecommendation(
                'Optimize database queries (add eager loading, fix N+1 patterns)'
            );
        }

        if (isset($byCategory['caching']) && $byCategory['caching'] > 0) {
            $report->addRecommendation(
                'Implement caching strategy for frequently accessed data'
            );
        }

        // Database recommendations
        if (isset($byCategory['index_optimization']) && $byCategory['index_optimization'] > 0) {
            $report->addRecommendation(
                'Add database indexes to improve query performance'
            );
        }

        // Code quality recommendations
        if (isset($byCategory['testing']) && $byCategory['testing'] > 0) {
            $report->addRecommendation(
                'Increase test coverage for critical business logic'
            );
        }
    }

    /**
     * Create an empty report when engine is not configured
     * 
     * @param string $type The audit type
     * @param string $message The message to include
     * @return AuditReport
     */
    private function createEmptyReport(string $type, string $message): AuditReport
    {
        $report = new AuditReport(
            id: AuditReport::generateId(),
            timestamp: new DateTime(),
        );

        $result = new AuditResult(
            type: $type,
            severity: 'info',
            category: 'configuration',
            message: $message,
            details: [
                'reason' => 'Engine not configured in service container',
                'action' => 'Configure the audit engine in AppServiceProvider',
            ],
        );

        $report->addResult($result);

        return $report;
    }

    /**
     * Create an error report when audit fails
     * 
     * @param string $type The audit type
     * @param Exception $exception The exception that occurred
     * @return AuditReport
     */
    private function createErrorReport(string $type, Exception $exception): AuditReport
    {
        $report = new AuditReport(
            id: AuditReport::generateId(),
            timestamp: new DateTime(),
        );

        $result = new AuditResult(
            type: $type,
            severity: 'critical',
            category: 'configuration',
            message: "Audit execution failed: {$exception->getMessage()}",
            details: [
                'exception' => get_class($exception),
                'message' => $exception->getMessage(),
                'file' => $exception->getFile(),
                'line' => $exception->getLine(),
                'trace' => $exception->getTraceAsString(),
            ],
        );

        $report->addResult($result);
        $report->addRecommendation('Review error logs and fix audit engine configuration');

        return $report;
    }

    /**
     * Set the security audit engine
     * 
     * @param AuditEngine $engine
     * @return void
     */
    public function setSecurityEngine(AuditEngine $engine): void
    {
        $this->securityEngine = $engine;
    }

    /**
     * Set the performance audit engine
     * 
     * @param AuditEngine $engine
     * @return void
     */
    public function setPerformanceEngine(AuditEngine $engine): void
    {
        $this->performanceEngine = $engine;
    }

    /**
     * Set the database audit engine
     * 
     * @param AuditEngine $engine
     * @return void
     */
    public function setDatabaseEngine(AuditEngine $engine): void
    {
        $this->databaseEngine = $engine;
    }

    /**
     * Set the code quality audit engine
     * 
     * @param AuditEngine $engine
     * @return void
     */
    public function setCodeQualityEngine(AuditEngine $engine): void
    {
        $this->codeQualityEngine = $engine;
    }
}
