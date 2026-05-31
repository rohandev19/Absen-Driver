<?php

namespace App\Console\Commands;

use App\Services\Audit\Database\DatabaseAuditEngine;
use App\Services\Audit\Database\Analyzers\IndexAnalyzer;
use App\Services\Audit\Database\Analyzers\QueryOptimizer;
use App\Services\Audit\Database\Analyzers\TransactionAnalyzer;
use App\Services\Audit\Database\Analyzers\ConnectionAnalyzer;
use App\Services\Audit\Reporting\ReportGenerator;
use Illuminate\Console\Command;

/**
 * RunDatabaseAudit Command
 * 
 * Runs database-specific audit with all database analyzers.
 * 
 * Usage:
 *   php artisan audit:database
 *   php artisan audit:database --format=json
 *   php artisan audit:database --format=markdown --output=db-report.md
 */
class RunDatabaseAudit extends Command
{
    protected $signature = 'audit:database 
        {--format=html : Output format (html, json, markdown)}
        {--output= : Output file path}';

    protected $description = 'Run database audit analyzing indexes, queries, transactions, and connections';

    public function handle(): int
    {
        $this->info('');
        $this->info('🗄️  Running Database Audit...');
        $this->info('');

        $format = $this->option('format');
        $outputPath = $this->option('output');

        $engine = new DatabaseAuditEngine();

        $analyzers = [
            'Index' => new IndexAnalyzer(),
            'Query' => new QueryOptimizer(),
            'Transaction' => new TransactionAnalyzer(),
            'Connection' => new ConnectionAnalyzer(),
        ];

        foreach ($analyzers as $name => $analyzer) {
            $this->components->task("Loading {$name} Analyzer", function () use ($engine, $analyzer) {
                $engine->addAnalyzer($analyzer);
                return true;
            });
        }

        $this->newLine();

        $startTime = microtime(true);
        $report = $engine->generateReport();
        $executionTime = round(microtime(true) - $startTime, 2);

        $summary = $report->getSummary();

        $this->components->info("Database audit completed in {$executionTime}s");
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

        $generator = new ReportGenerator();
        $content = $generator->generateFullReport($report, $format);

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
            $outputPath = $dir . '/database-audit-' . date('Y-m-d_His') . '.' . $extension;
        }

        file_put_contents($outputPath, $content);

        $this->newLine();
        $this->components->info("Report saved to: {$outputPath}");

        if ($report->hasCriticalFindings()) {
            $this->newLine();
            $this->error('⚠️  Critical database findings detected!');
            return self::FAILURE;
        }

        return self::SUCCESS;
    }
}
