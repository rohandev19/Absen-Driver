<?php

namespace App\Services\Audit\Security;

use App\Contracts\AuditEngine;
use App\Contracts\Scanner;
use App\DataModels\Audit\AuditReport;
use App\DataModels\Audit\AuditResult;
use DateTime;
use Exception;
use Illuminate\Support\Facades\Log;

/**
 * SecurityAuditEngine
 * 
 * Orchestrates all security scanners to perform comprehensive security analysis
 * including authentication, authorization, input validation, CSRF/XSS, 
 * sensitive data, dependencies, and configuration checks.
 * 
 * @package App\Services\Audit\Security
 */
class SecurityAuditEngine implements AuditEngine
{
    /**
     * @var array<int, Scanner> Registered security scanners
     */
    private array $scanners = [];

    /**
     * @var array<int, AuditResult> Collected findings
     */
    private array $findings = [];

    /**
     * Register a scanner
     * 
     * @param Scanner $scanner
     * @return void
     */
    public function addScanner(Scanner $scanner): void
    {
        $this->scanners[] = $scanner;
    }

    /**
     * Analyze the codebase for security issues
     * 
     * Runs all registered security scanners and collects findings.
     * Each scanner failure is caught independently.
     * 
     * @param array<string, mixed> $options
     * @return array<int, AuditResult>
     */
    public function analyze(array $options = []): array
    {
        $this->findings = [];

        Log::info('SecurityAuditEngine: Starting security analysis', [
            'scanner_count' => count($this->scanners),
        ]);

        foreach ($this->scanners as $scanner) {
            $scannerClass = get_class($scanner);

            try {
                Log::debug("SecurityAuditEngine: Running scanner {$scannerClass}");

                $results = $scanner->scan($options);

                foreach ($results as $result) {
                    if ($result instanceof AuditResult) {
                        $this->findings[] = $result;
                    }
                }

                Log::debug("SecurityAuditEngine: Scanner {$scannerClass} completed", [
                    'findings_count' => count($results),
                ]);

            } catch (Exception $e) {
                Log::error("SecurityAuditEngine: Scanner {$scannerClass} failed", [
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString(),
                ]);

                // Add an error finding so the report shows scanner failure
                $this->findings[] = new AuditResult(
                    type: 'security',
                    severity: 'medium',
                    category: 'configuration',
                    message: "Security scanner failed: {$scannerClass}",
                    details: [
                        'scanner' => $scannerClass,
                        'error' => $e->getMessage(),
                        'file' => $e->getFile(),
                        'line' => $e->getLine(),
                    ],
                );
            }
        }

        Log::info('SecurityAuditEngine: Analysis completed', [
            'total_findings' => count($this->findings),
        ]);

        return $this->findings;
    }

    /**
     * Generate a complete security audit report
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

        // Add security-specific recommendations
        $this->addSecurityRecommendations($report);

        return $report;
    }

    /**
     * Add security-specific recommendations based on findings
     * 
     * @param AuditReport $report
     * @return void
     */
    private function addSecurityRecommendations(AuditReport $report): void
    {
        $summary = $report->getSummary();
        $byCategory = $summary['by_category'] ?? [];

        if (isset($byCategory['authentication']) && $byCategory['authentication'] > 0) {
            $report->addRecommendation('Strengthen authentication mechanisms: enforce strong passwords, configure token expiration, implement MFA');
        }

        if (isset($byCategory['authorization']) && $byCategory['authorization'] > 0) {
            $report->addRecommendation('Review and fix authorization: ensure all endpoints have proper middleware and role checks');
        }

        if (isset($byCategory['input_validation']) && $byCategory['input_validation'] > 0) {
            $report->addRecommendation('Fix input validation: use parameterized queries, validate all user inputs, sanitize file uploads');
        }

        if (isset($byCategory['csrf_xss']) && $byCategory['csrf_xss'] > 0) {
            $report->addRecommendation('Address CSRF/XSS vulnerabilities: verify CSRF tokens, escape all output, add security headers');
        }

        if (isset($byCategory['sensitive_data']) && $byCategory['sensitive_data'] > 0) {
            $report->addRecommendation('Protect sensitive data: remove hardcoded secrets, encrypt sensitive fields, secure file storage');
        }

        if (isset($byCategory['dependency']) && $byCategory['dependency'] > 0) {
            $report->addRecommendation('Update dependencies: patch vulnerable packages, replace deprecated libraries');
        }

        if (isset($byCategory['configuration']) && $byCategory['configuration'] > 0) {
            $report->addRecommendation('Fix configuration: disable debug mode in production, rotate app key, secure database credentials');
        }
    }
}
