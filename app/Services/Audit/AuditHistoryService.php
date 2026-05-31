<?php

namespace App\Services\Audit;

use App\DataModels\Audit\AuditReport;
use App\Models\AuditHistory;
use App\Services\Audit\Reporting\ReportGenerator;
use Exception;
use Illuminate\Support\Facades\Log;

/**
 * AuditHistoryService
 * 
 * Manages audit history persistence: creates records for new audit runs,
 * updates with results on completion, and provides trend analysis.
 * 
 * @package App\Services\Audit
 */
class AuditHistoryService
{
    public function __construct(
        private ReportGenerator $reportGenerator,
    ) {
    }

    /**
     * Start a new audit history record
     * 
     * @param string $type Audit type (full, security, performance, database, code_quality)
     * @param string $format Report format (json, html, markdown)
     * @param string $triggeredBy How the audit was triggered (manual, scheduled, ci)
     * @return AuditHistory
     */
    public function startAudit(string $type = 'full', string $format = 'json', string $triggeredBy = 'manual'): AuditHistory
    {
        return AuditHistory::create([
            'report_id' => AuditReport::generateId(),
            'type' => $type,
            'status' => 'running',
            'format' => $format,
            'triggered_by' => $triggeredBy,
        ]);
    }

    /**
     * Complete an audit with report data
     * 
     * @param AuditHistory $history
     * @param AuditReport $report
     * @param float $executionTime
     * @param string|null $reportPath
     * @return void
     */
    public function completeAudit(
        AuditHistory $history,
        AuditReport $report,
        float $executionTime,
        ?string $reportPath = null
    ): void {
        $history->markCompleted(
            $report->toArray(),
            $executionTime,
            $reportPath
        );

        Log::info('Audit history saved', [
            'id' => $history->id,
            'report_id' => $history->report_id,
            'type' => $history->type,
            'total_findings' => $history->total_findings,
            'execution_time' => $executionTime,
        ]);
    }

    /**
     * Mark an audit as failed
     * 
     * @param AuditHistory $history
     * @param string $errorMessage
     * @param float $executionTime
     * @return void
     */
    public function failAudit(AuditHistory $history, string $errorMessage, float $executionTime): void
    {
        $history->markFailed($errorMessage, $executionTime);

        Log::error('Audit failed', [
            'id' => $history->id,
            'report_id' => $history->report_id,
            'error' => $errorMessage,
        ]);
    }

    /**
     * Get the latest audit of a specific type
     * 
     * @param string $type
     * @return AuditHistory|null
     */
    public function getLatest(string $type = 'full'): ?AuditHistory
    {
        return AuditHistory::ofType($type)
            ->completed()
            ->latest()
            ->first();
    }

    /**
     * Get audit trend data (findings over time)
     * 
     * @param string $type
     * @param int $limit
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getTrend(string $type = 'full', int $limit = 10)
    {
        return AuditHistory::ofType($type)
            ->completed()
            ->latest()
            ->limit($limit)
            ->get(['id', 'report_id', 'total_findings', 'critical_count', 'high_count', 'execution_time_seconds', 'created_at']);
    }

    /**
     * Compare current audit with previous
     * 
     * @param AuditHistory $current
     * @return array<string, mixed>|null
     */
    public function compareWithPrevious(AuditHistory $current): ?array
    {
        $previous = AuditHistory::ofType($current->type)
            ->completed()
            ->where('id', '<', $current->id)
            ->latest()
            ->first();

        if (!$previous) {
            return null;
        }

        return [
            'previous_id' => $previous->id,
            'previous_date' => $previous->created_at->toDateTimeString(),
            'findings_delta' => $current->total_findings - $previous->total_findings,
            'critical_delta' => $current->critical_count - $previous->critical_count,
            'high_delta' => $current->high_count - $previous->high_count,
            'improved' => $current->total_findings < $previous->total_findings,
            'new_critical' => $current->critical_count > $previous->critical_count,
        ];
    }

    /**
     * Cleanup old audit history records
     * 
     * @param int $keepDays Number of days to retain
     * @return int Number of records deleted
     */
    public function cleanup(int $keepDays = 30): int
    {
        $count = AuditHistory::where('created_at', '<', now()->subDays($keepDays))->count();

        AuditHistory::where('created_at', '<', now()->subDays($keepDays))->delete();

        Log::info("Audit history cleanup: deleted {$count} records older than {$keepDays} days");

        return $count;
    }
}
