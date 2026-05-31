<?php

namespace App\Console\Commands;

use App\Models\AuditHistory;
use App\Services\Audit\AuditHistoryService;
use App\Services\Audit\AuditOrchestrator;
use App\Services\Audit\Reporting\ReportGenerator;
use Exception;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

/**
 * ScheduledAudit Command
 * 
 * Runs a full audit with history persistence. Designed to be called
 * by Laravel's task scheduler for periodic automated auditing.
 * 
 * Usage:
 *   php artisan audit:scheduled
 *   php artisan audit:scheduled --type=security
 *   php artisan audit:scheduled --type=full --format=json
 * 
 * Scheduler example (routes/console.php or app/Console/Kernel.php):
 *   $schedule->command('audit:scheduled')->weekly()->sundays()->at('02:00');
 *   $schedule->command('audit:scheduled --type=security')->daily()->at('03:00');
 */
class ScheduledAudit extends Command
{
    protected $signature = 'audit:scheduled 
        {--type=full : Audit type (full, security, performance, database, code_quality)}
        {--format=json : Output format (html, json, markdown)}';

    protected $description = 'Run scheduled audit with history tracking (for cron/scheduler)';

    public function handle(
        AuditOrchestrator $orchestrator,
        ReportGenerator $reportGenerator,
        AuditHistoryService $historyService,
    ): int {
        $type = $this->option('type');
        $format = $this->option('format');

        $this->info('');
        $this->info('📅 Running Scheduled Audit...');
        $this->info("   Type: {$type} | Format: {$format}");
        $this->info('');

        // Start history tracking
        $history = $historyService->startAudit($type, $format, 'scheduled');
        $startTime = microtime(true);

        try {
            // Run appropriate audit
            $report = match ($type) {
                'security' => $orchestrator->runSecurityAudit(),
                'performance' => $orchestrator->runPerformanceAudit(),
                'database' => $orchestrator->runDatabaseAudit(),
                'code_quality' => $orchestrator->runCodeQualityAudit(),
                default => $orchestrator->runFullAudit(),
            };

            $executionTime = round(microtime(true) - $startTime, 2);

            // Generate report file
            $content = $reportGenerator->generateFullReport($report, $format);

            $extension = match ($format) {
                'json' => 'json',
                'markdown', 'md' => 'md',
                default => 'html',
            };

            $dir = storage_path('app/audit-reports');
            if (!is_dir($dir)) {
                mkdir($dir, 0755, true);
            }

            $reportPath = $dir . "/scheduled-{$type}-" . date('Y-m-d_His') . ".{$extension}";
            file_put_contents($reportPath, $content);

            // Save to history
            $historyService->completeAudit($history, $report, $executionTime, $reportPath);

            // Display summary
            $summary = $report->getSummary();
            $this->components->info("Audit completed in {$executionTime}s");
            $this->newLine();

            $this->table(
                ['Severity', 'Count'],
                [
                    ['🔴 Critical', $summary['by_severity']['critical'] ?? 0],
                    ['🟠 High', $summary['by_severity']['high'] ?? 0],
                    ['🟡 Medium', $summary['by_severity']['medium'] ?? 0],
                    ['🟢 Low', $summary['by_severity']['low'] ?? 0],
                    ['🔵 Info', $summary['by_severity']['info'] ?? 0],
                    ['──────────', '─────'],
                    ['📊 Total', $summary['total_findings'] ?? 0],
                ]
            );

            // Compare with previous run
            $comparison = $historyService->compareWithPrevious($history->fresh());
            if ($comparison) {
                $this->newLine();
                $delta = $comparison['findings_delta'];
                $trend = $delta > 0 ? "📈 +{$delta}" : ($delta < 0 ? "📉 {$delta}" : "➡️ 0");
                $this->components->info("Compared to previous: {$trend} findings");

                if ($comparison['new_critical']) {
                    $this->components->warn('⚠️  New critical findings detected since last audit!');
                }

                if ($comparison['improved']) {
                    $this->components->info('✅ Overall improvement since last audit');
                }
            }

            $this->newLine();
            $this->components->info("Report: {$reportPath}");
            $this->components->info("History ID: {$history->id}");

            if ($report->hasCriticalFindings()) {
                $this->newLine();
                $this->error('⚠️  Critical findings detected!');
                return self::FAILURE;
            }

            return self::SUCCESS;

        } catch (Exception $e) {
            $executionTime = round(microtime(true) - $startTime, 2);
            $historyService->failAudit($history, $e->getMessage(), $executionTime);

            $this->error("Scheduled audit failed: {$e->getMessage()}");
            Log::error('Scheduled audit failed', [
                'type' => $type,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return self::FAILURE;
        }
    }
}
