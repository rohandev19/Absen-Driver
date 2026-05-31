<?php

namespace App\Contracts;

/**
 * Scanner Interface
 * 
 * Defines the contract for scanner implementations that perform
 * specific security, performance, or quality checks.
 * 
 * @package App\Contracts
 */
interface Scanner
{
    /**
     * Scan the codebase for specific issues
     * 
     * Performs targeted scanning for a specific type of issue
     * (e.g., authentication vulnerabilities, N+1 queries, etc.)
     * 
     * @param array<string, mixed> $options Optional configuration for the scan
     * @return array<int, \App\DataModels\Audit\AuditResult> Array of findings from this scanner
     */
    public function scan(array $options = []): array;

    /**
     * Get all findings from the most recent scan
     * 
     * Returns cached findings from the last scan execution.
     * If no scan has been performed, returns an empty array.
     * 
     * @return array<int, \App\DataModels\Audit\AuditResult> Array of findings
     */
    public function getFindings(): array;
}
