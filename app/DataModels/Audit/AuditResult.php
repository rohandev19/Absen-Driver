<?php

namespace App\DataModels\Audit;

use DateTime;
use InvalidArgumentException;

/**
 * AuditResult Data Model
 * 
 * Represents a single audit finding from security, performance, or code quality scans.
 * 
 * @package App\DataModels\Audit
 */
class AuditResult
{
    /**
     * Valid audit types
     */
    private const VALID_TYPES = [
        'security',
        'performance',
        'database',
        'code_quality',
        'configuration',
        'dependency',
    ];

    /**
     * Valid severity levels
     */
    private const VALID_SEVERITIES = [
        'critical',
        'high',
        'medium',
        'low',
        'info',
    ];

    /**
     * Valid categories
     */
    private const VALID_CATEGORIES = [
        'authentication',
        'authorization',
        'input_validation',
        'csrf_xss',
        'sensitive_data',
        'file_upload',
        'api_security',
        'session_management',
        'query_optimization',
        'caching',
        'memory_usage',
        'index_optimization',
        'transaction_management',
        'code_style',
        'complexity',
        'testing',
        'documentation',
        'configuration',
        'dependency',
    ];

    /**
     * @param string $type The type of audit (security, performance, database, code_quality)
     * @param string $severity The severity level (critical, high, medium, low, info)
     * @param string $category The specific category of the finding
     * @param string $message A brief description of the finding
     * @param array<string, mixed> $details Additional details about the finding
     * @param DateTime $timestamp When the finding was detected
     */
    public function __construct(
        private string $type,
        private string $severity,
        private string $category,
        private string $message,
        private array $details = [],
        private DateTime $timestamp = new DateTime(),
    ) {
        $this->validate();
    }

    /**
     * Validate the audit result data
     * 
     * @throws InvalidArgumentException If validation fails
     * @return void
     */
    private function validate(): void
    {
        if (!in_array($this->type, self::VALID_TYPES, true)) {
            throw new InvalidArgumentException(
                "Invalid type '{$this->type}'. Must be one of: " . implode(', ', self::VALID_TYPES)
            );
        }

        if (!in_array($this->severity, self::VALID_SEVERITIES, true)) {
            throw new InvalidArgumentException(
                "Invalid severity '{$this->severity}'. Must be one of: " . implode(', ', self::VALID_SEVERITIES)
            );
        }

        if (!in_array($this->category, self::VALID_CATEGORIES, true)) {
            throw new InvalidArgumentException(
                "Invalid category '{$this->category}'. Must be one of: " . implode(', ', self::VALID_CATEGORIES)
            );
        }

        if (empty(trim($this->message))) {
            throw new InvalidArgumentException('Message cannot be empty');
        }
    }

    /**
     * Get the audit type
     * 
     * @return string
     */
    public function getType(): string
    {
        return $this->type;
    }

    /**
     * Get the severity level
     * 
     * @return string
     */
    public function getSeverity(): string
    {
        return $this->severity;
    }

    /**
     * Get the category
     * 
     * @return string
     */
    public function getCategory(): string
    {
        return $this->category;
    }

    /**
     * Get the message
     * 
     * @return string
     */
    public function getMessage(): string
    {
        return $this->message;
    }

    /**
     * Get the details
     * 
     * @return array<string, mixed>
     */
    public function getDetails(): array
    {
        return $this->details;
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
     * Convert to array representation
     * 
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'type' => $this->type,
            'severity' => $this->severity,
            'category' => $this->category,
            'message' => $this->message,
            'details' => $this->details,
            'timestamp' => $this->timestamp->format('Y-m-d H:i:s'),
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

        return new self(
            type: $data['type'] ?? '',
            severity: $data['severity'] ?? '',
            category: $data['category'] ?? '',
            message: $data['message'] ?? '',
            details: $data['details'] ?? [],
            timestamp: $timestamp,
        );
    }

    /**
     * Check if this is a critical finding
     * 
     * @return bool
     */
    public function isCritical(): bool
    {
        return $this->severity === 'critical';
    }

    /**
     * Check if this is a high severity finding
     * 
     * @return bool
     */
    public function isHigh(): bool
    {
        return $this->severity === 'high';
    }

    /**
     * Get severity weight for sorting (higher = more severe)
     * 
     * @return int
     */
    public function getSeverityWeight(): int
    {
        return match ($this->severity) {
            'critical' => 5,
            'high' => 4,
            'medium' => 3,
            'low' => 2,
            'info' => 1,
            default => 0,
        };
    }
}
