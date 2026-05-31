<?php

namespace App\Console\Commands;

use App\Services\Audit\Performance\PerformanceAuditEngine;
use App\Services\Audit\Performance\Analyzers\QueryAnalyzer;
use App\Services\Audit\Performance\Analyzers\CacheAnalyzer;
use App\Services\Audit\Performance\Analyzers\ApiResponseAnalyzer;
use App\Services\Audit\Performance\Analyzers\MemoryAnalyzer;
use App\Services\Audit\Reporting\ReportGenerator;
use Illuminate\Console\Command;

/**
 * RunPerformanceAudit Command
 * 
 * Runs performance-specific audit with all analyzers.
 */
class RunPerformanceAudit extends Command
{
    protected $signature = 'audit:performance 
        {--format=html : Output format (html, json, markdown)}
        {--output= : Output file path}';

    protected $description = 'Run performance audit analyzing queries, caching, API responses, and memory usage';

    public function handle(): int
    {
        $this->info('');
        $this->info('⚡ Running Performance Audit...');
        $this->info('');

        $format = $this->option('format');
        $outputPath = $this->option('output');

        $engine = new PerformanceAuditEngine();

        $analyzers = [
            'Query' => new QueryAnalyzer(),
            'Cache' => new CacheAnalyzer(),
            'API Response' => new ApiResponseAnalyzer(),
            'Memory' => new MemoryAnalyzer(),
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

        $this->components->info("Performance audit completed in {$executionTime}s");
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
            $outputPath = $dir . '/performance-audit-' . date('Y-m-d_His') . '.' . $extension;
        }

        file_put_contents($outputPath, $content);

        $this->newLine();
        $this->components->info("Report saved to: {$outputPath}");

        return self::SUCCESS;
    }
}
