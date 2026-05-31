<?php

namespace App\Console\Commands;

use App\Services\Audit\AuditOrchestrator;
use App\Services\Audit\Reporting\ReportGenerator;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

/**
 * RunFullAudit Command
 * 
 * Artisan command to run a comprehensive audit of the entire application,
 * covering security, performance, database, and code quality.
 * 
 * Usage:
 *   php artisan audit:run
 *   php artisan audit:run --format=json --output=storage/audit-report.json
 *   php artisan audit:run --format=markdown
 * 
 * @package App\Console\Commands
 */
class RunFullAudit extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'audit:run 
        {--format=html : Output format (html, json, markdown)}
        {--output= : Output file path (defaults to storage/app/audit-reports/)}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Run comprehensive security, performance, database, and code quality audit';

    /**
     * Execute the console command.
     */
    public function handle(AuditOrchestrator $orchestrator, ReportGenerator $reportGenerator): int
    {
        $this->info('');
        $this->info('╔══════════════════════════════════════════════════╗');
        $this->info('║   🛡️  COMPREHENSIVE AUDIT SYSTEM                ║');
        $this->info('║   Security • Performance • Database • Quality   ║');
        $this->info('╚══════════════════════════════════════════════════╝');
        $this->info('');

        $format = $this->option('format');
        $outputPath = $this->option('output');

        $this->components->info('Starting full audit...');

        $startTime = microtime(true);

        // Run the full audit
        $this->components->task('Running Security Audit', function () {
            // Progress indicator — actual work happens in orchestrator
            return true;
        });

        $this->components->task('Running Performance Audit', function () {
            return true;
        });

        $this->components->task('Running Database Audit', function () {
            return true;
        });

        $this->components->task('Running Code Quality Audit', function () {
            return true;
        });

        try {
            $report = $orchestrator->runFullAudit();
        } catch (\Exception $e) {
            $this->error('Audit failed: ' . $e->getMessage());
            Log::error('Full audit command failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            return self::FAILURE;
        }

        $executionTime = round(microtime(true) - $startTime, 2);

        // Display summary
        $this->newLine();
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

        // Generate report
        $this->newLine();
        $this->components->task('Generating report', function () use ($reportGenerator, $report, $format, &$outputPath) {
            $content = $reportGenerator->generateFullReport($report, $format);

            if (empty($outputPath)) {
                $extension = match ($format) {
                    'json' => 'json',
                    'markdown', 'md' => 'md',
                    default => 'html',
                };
                $dir = storage_path('app/audit-reports');
                if (!is_dir($dir)) {
                    mkdir($dir, 0755, true);
                }
                $outputPath = $dir . '/audit-report-' . date('Y-m-d_His') . '.' . $extension;
            }

            file_put_contents($outputPath, $content);
            return true;
        });

        $this->newLine();
        $this->components->info("Report saved to: {$outputPath}");

        // Show critical/high findings in console
        $criticalAndHigh = $report->getCriticalAndHighFindings();
        if (!empty($criticalAndHigh)) {
            $this->newLine();
            $this->components->warn(
                sprintf('%d critical/high finding(s) require attention:', count($criticalAndHigh))
            );

            foreach ($criticalAndHigh as $finding) {
                $icon = $finding->isCritical() ? '🔴' : '🟠';
                $this->line("  {$icon} [{$finding->getSeverity()}] {$finding->getMessage()}");
            }
        }

        // Return exit code based on findings
        if ($report->hasCriticalFindings()) {
            $this->newLine();
            $this->error('⚠️  Critical findings detected! Review the report immediately.');
            return self::FAILURE;
        }

        $this->newLine();
        $this->components->info('✅ Audit completed successfully.');

        return self::SUCCESS;
    }
}
