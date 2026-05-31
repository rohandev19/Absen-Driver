<?php

namespace App\DataModels\Audit;

use DateTime;
use InvalidArgumentException;

/**
 * AuditReport Data Model
 * 
 * Represents a complete audit report containing multiple audit results,
 * summary statistics, and actionable recommendations.
 * 
 * @package App\DataModels\Audit
 */
class AuditReport
{
    /**
     * @param string $id Unique identifier for this audit report
     * @param DateTime $timestamp When the audit was performed
     * @param array<int, AuditResult> $results Collection of audit findings
     * @param array<string, mixed> $summary Summary statistics (counts by severity, type, etc.)
     * @param array<int, string> $recommendations Prioritized list of actionable recommendations
     */
    public function __construct(
        private string $id,
        private DateTime $timestamp,
        private array $results = [],
        private array $summary = [],
        private array $recommendations = [],
    ) {
        $this->validate();
        $this->generateSummary();
    }

    /**
     * Validate the audit report data
     * 
     * @throws InvalidArgumentException If validation fails
     * @return void
     */
    private function validate(): void
    {
        if (empty(trim($this->id))) {
            throw new InvalidArgumentException('Report ID cannot be empty');
        }

        foreach ($this->results as $result) {
            if (!$result instanceof AuditResult) {
                throw new InvalidArgumentException(
                    'All results must be instances of AuditResult'
                );
            }
        }
    }

    /**
     * Generate summary statistics from results
     * 
     * @return void
     */
    private function generateSummary(): void
    {
        $this->summary = [
            'total_findings' => count($this->results),
            'by_severity' => $this->countBySeverity(),
            'by_type' => $this->countByType(),
            'by_category' => $this->countByCategory(),
            'critical_count' => $this->countCritical(),
            'high_count' => $this->countHigh(),
        ];
    }

    /**
     * Count findings by severity level
     * 
     * @return array<string, int>
     */
    private function countBySeverity(): array
    {
        $counts = [
            'critical' => 0,
            'high' => 0,
            'medium' => 0,
            'low' => 0,
            'info' => 0,
        ];

        foreach ($this->results as $result) {
            $severity = $result->getSeverity();
            if (isset($counts[$severity])) {
                $counts[$severity]++;
            }
        }

        return $counts;
    }

    /**
     * Count findings by audit type
     * 
     * @return array<string, int>
     */
    private function countByType(): array
    {
        $counts = [];

        foreach ($this->results as $result) {
            $type = $result->getType();
            $counts[$type] = ($counts[$type] ?? 0) + 1;
        }

        return $counts;
    }

    /**
     * Count findings by category
     * 
     * @return array<string, int>
     */
    private function countByCategory(): array
    {
        $counts = [];

        foreach ($this->results as $result) {
            $category = $result->getCategory();
            $counts[$category] = ($counts[$category] ?? 0) + 1;
        }

        return $counts;
    }

    /**
     * Count critical findings
     * 
     * @return int
     */
    private function countCritical(): int
    {
        return count(array_filter(
            $this->results,
            fn(AuditResult $result) => $result->isCritical()
        ));
    }

    /**
     * Count high severity findings
     * 
     * @return int
     */
    private function countHigh(): int
    {
        return count(array_filter(
            $this->results,
            fn(AuditResult $result) => $result->isHigh()
        ));
    }

    /**
     * Get the report ID
     * 
     * @return string
     */
    public function getId(): string
    {
        return $this->id;
    }

    /**
     * Get the timestamp
     * 
     * @return DateTime
     */
    public function getTimestamp(): DateTime
    {
        return $this->timestamp;
    }

    /**
     * Get all audit results
     * 
     * @return array<int, AuditResult>
     */
    public function getResults(): array
    {
        return $this->results;
    }

    /**
     * Get the summary statistics
     * 
     * @return array<string, mixed>
     */
    public function getSummary(): array
    {
        return $this->summary;
    }

    /**
     * Get the recommendations
     * 
     * @return array<int, string>
     */
    public function getRecommendations(): array
    {
        return $this->recommendations;
    }

    /**
     * Add a single audit result
     * 
     * @param AuditResult $result
     * @return void
     */
    public function addResult(AuditResult $result): void
    {
        $this->results[] = $result;
        $this->generateSummary();
    }

    /**
     * Add multiple audit results
     * 
     * @param array<int, AuditResult> $results
     * @return void
     */
    public function addResults(array $results): void
    {
        foreach ($results as $result) {
            if ($result instanceof AuditResult) {
                $this->results[] = $result;
            }
        }
        $this->generateSummary();
    }

    /**
     * Set recommendations
     * 
     * @param array<int, string> $recommendations
     * @return void
     */
    public function setRecommendations(array $recommendations): void
    {
        $this->recommendations = $recommendations;
    }

    /**
     * Add a single recommendation
     * 
     * @param string $recommendation
     * @return void
     */
    public function addRecommendation(string $recommendation): void
    {
        $this->recommendations[] = $recommendation;
    }

    /**
     * Get results filtered by severity
     * 
     * @param string $severity
     * @return array<int, AuditResult>
     */
    public function getResultsBySeverity(string $severity): array
    {
        return array_filter(
            $this->results,
            fn(AuditResult $result) => $result->getSeverity() === $severity
        );
    }

    /**
     * Get results filtered by type
     * 
     * @param string $type
     * @return array<int, AuditResult>
     */
    public function getResultsByType(string $type): array
    {
        return array_filter(
            $this->results,
            fn(AuditResult $result) => $result->getType() === $type
        );
    }

    /**
     * Get results filtered by category
     * 
     * @param string $category
     * @return array<int, AuditResult>
     */
    public function getResultsByCategory(string $category): array
    {
        return array_filter(
            $this->results,
            fn(AuditResult $result) => $result->getCategory() === $category
        );
    }

    /**
     * Get all critical and high severity findings
     * 
     * @return array<int, AuditResult>
     */
    public function getCriticalAndHighFindings(): array
    {
        return array_filter(
            $this->results,
            fn(AuditResult $result) => $result->isCritical() || $result->isHigh()
        );
    }

    /**
     * Sort results by severity (most severe first)
     * 
     * @return array<int, AuditResult>
     */
    public function getResultsSortedBySeverity(): array
    {
        $sorted = $this->results;
        usort($sorted, function (AuditResult $a, AuditResult $b) {
            return $b->getSeverityWeight() <=> $a->getSeverityWeight();
        });
        return $sorted;
    }

    /**
     * Check if report has any critical findings
     * 
     * @return bool
     */
    public function hasCriticalFindings(): bool
    {
        return $this->countCritical() > 0;
    }

    /**
     * Check if report has any high severity findings
     * 
     * @return bool
     */
    public function hasHighFindings(): bool
    {
        return $this->countHigh() > 0;
    }

    /**
     * Get total number of findings
     * 
     * @return int
     */
    public function getTotalFindings(): int
    {
        return count($this->results);
    }

    /**
     * Convert to array representation
     * 
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'timestamp' => $this->timestamp->format('Y-m-d H:i:s'),
            'results' => array_map(
                fn(AuditResult $result) => $result->toArray(),
                $this->results
            ),
            'summary' => $this->summary,
            'recommendations' => $this->recommendations,
        ];
    }

    /**
     * Create from array
     * 
     * @param array<string, mixed> $data
     * @return self
     * @throws InvalidArgumentException
     */
    public static function fromArray(array $data): self
    {
        $timestamp = isset($data['timestamp']) 
            ? new DateTime($data['timestamp']) 
            : new DateTime();

        $results = [];
        if (isset($data['results']) && is_array($data['results'])) {
            foreach ($data['results'] as $resultData) {
                $results[] = AuditResult::fromArray($resultData);
            }
        }

        return new self(
            id: $data['id'] ?? '',
            timestamp: $timestamp,
            results: $results,
            summary: $data['summary'] ?? [],
            recommendations: $data['recommendations'] ?? [],
        );
    }

    /**
     * Generate a unique report ID
     * 
     * @return string
     */
    public static function generateId(): string
    {
        return 'audit_' . date('Ymd_His') . '_' . bin2hex(random_bytes(4));
    }
}
