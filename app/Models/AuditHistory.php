<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

/**
 * AuditHistory Model
 * 
 * Stores audit run history in the database for tracking trends,
 * comparing results over time, and monitoring audit compliance.
 * 
 * @property int $id
 * @property string $report_id
 * @property string $type
 * @property string $status
 * @property int $total_findings
 * @property int $critical_count
 * @property int $high_count
 * @property int $medium_count
 * @property int $low_count
 * @property int $info_count
 * @property array|null $summary
 * @property array|null $report_data
 * @property string|null $report_path
 * @property string $format
 * @property float|null $execution_time_seconds
 * @property string $triggered_by
 * @property string|null $error_message
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 * 
 * @package App\Models
 */
class AuditHistory extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'report_id',
        'type',
        'status',
        'total_findings',
        'critical_count',
        'high_count',
        'medium_count',
        'low_count',
        'info_count',
        'summary',
        'report_data',
        'report_path',
        'format',
        'execution_time_seconds',
        'triggered_by',
        'error_message',
    ];

    /**
     * The attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'summary' => 'array',
            'report_data' => 'array',
            'total_findings' => 'integer',
            'critical_count' => 'integer',
            'high_count' => 'integer',
            'medium_count' => 'integer',
            'low_count' => 'integer',
            'info_count' => 'integer',
            'execution_time_seconds' => 'float',
        ];
    }

    /**
     * Scope: Filter by audit type
     */
    public function scopeOfType(Builder $query, string $type): Builder
    {
        return $query->where('type', $type);
    }

    /**
     * Scope: Filter completed audits
     */
    public function scopeCompleted(Builder $query): Builder
    {
        return $query->where('status', 'completed');
    }

    /**
     * Scope: Filter failed audits
     */
    public function scopeFailed(Builder $query): Builder
    {
        return $query->where('status', 'failed');
    }

    /**
     * Scope: Filter audits with critical findings
     */
    public function scopeWithCritical(Builder $query): Builder
    {
        return $query->where('critical_count', '>', 0);
    }

    /**
     * Scope: Recent audits (last N days)
     */
    public function scopeRecent(Builder $query, int $days = 30): Builder
    {
        return $query->where('created_at', '>=', now()->subDays($days));
    }

    /**
     * Check if this audit has critical findings
     */
    public function hasCriticalFindings(): bool
    {
        return $this->critical_count > 0;
    }

    /**
     * Check if this audit has high or critical findings
     */
    public function hasSevereFindings(): bool
    {
        return $this->critical_count > 0 || $this->high_count > 0;
    }

    /**
     * Get the severity distribution as a formatted array
     * 
     * @return array<string, int>
     */
    public function getSeverityDistribution(): array
    {
        return [
            'critical' => $this->critical_count,
            'high' => $this->high_count,
            'medium' => $this->medium_count,
            'low' => $this->low_count,
            'info' => $this->info_count,
        ];
    }

    /**
     * Mark as completed with report data
     * 
     * @param array<string, mixed> $reportArray
     * @param float $executionTime
     * @param string|null $reportPath
     * @return void
     */
    public function markCompleted(array $reportArray, float $executionTime, ?string $reportPath = null): void
    {
        $summary = $reportArray['summary'] ?? [];
        $bySeverity = $summary['by_severity'] ?? [];

        $this->update([
            'status' => 'completed',
            'total_findings' => $summary['total_findings'] ?? 0,
            'critical_count' => $bySeverity['critical'] ?? 0,
            'high_count' => $bySeverity['high'] ?? 0,
            'medium_count' => $bySeverity['medium'] ?? 0,
            'low_count' => $bySeverity['low'] ?? 0,
            'info_count' => $bySeverity['info'] ?? 0,
            'summary' => $summary,
            'report_data' => $reportArray,
            'report_path' => $reportPath,
            'execution_time_seconds' => $executionTime,
        ]);
    }

    /**
     * Mark as failed
     * 
     * @param string $errorMessage
     * @param float $executionTime
     * @return void
     */
    public function markFailed(string $errorMessage, float $executionTime): void
    {
        $this->update([
            'status' => 'failed',
            'error_message' => $errorMessage,
            'execution_time_seconds' => $executionTime,
        ]);
    }
}
