<?php

namespace App\Contracts;

use App\DataModels\Audit\AuditReport;

/**
 * AuditEngine Interface
 * 
 * Defines the contract for audit engine implementations that analyze
 * the codebase for security, performance, and quality issues.
 * 
 * @package App\Contracts
 */
interface AuditEngine
{
    /**
     * Analyze the codebase and generate findings
     * 
     * Performs a comprehensive analysis of the application code,
     * configuration, and dependencies to identify issues.
     * 
     * @param array<string, mixed> $options Optional configuration for the analysis
     * @return array<int, \App\DataModels\Audit\AuditResult> Array of audit findings
     */
    public function analyze(array $options = []): array;

    /**
     * Generate a complete audit report
     * 
     * Runs the analysis and compiles results into a structured report
     * with summary statistics and recommendations.
     * 
     * @param array<string, mixed> $options Optional configuration for the report
     * @return AuditReport Complete audit report with findings and recommendations
     */
    public function generateReport(array $options = []): AuditReport;
}
