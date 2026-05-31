<?php

namespace App\Console\Commands;

use App\Services\Audit\CodeQuality\CodeQualityEngine;
use App\Services\Audit\CodeQuality\Analyzers\StyleChecker;
use App\Services\Audit\CodeQuality\Analyzers\ComplexityAnalyzer;
use App\Services\Audit\CodeQuality\Analyzers\TestCoverageAnalyzer;
use App\Services\Audit\CodeQuality\Analyzers\DocumentationAnalyzer;
use App\Services\Audit\Reporting\ReportGenerator;
use Illuminate\Console\Command;

/**
 * RunCodeQualityAudit Command
 * 
 * Runs code quality audit with all analyzers.
 * 
 * Usage:
 *   php artisan audit:quality
 *   php artisan audit:quality --format=json
 *   php artisan audit:quality --format=markdown --output=quality-report.md
 */
class RunCodeQualityAudit extends Command
{
    protected $signature = 'audit:quality 
        {--format=html : Output format (html, json, markdown)}
        {--output= : Output file path}';

    protected $description = 'Run code quality audit analyzing style, complexity, tests, and documentation';

    public function handle(): int
    {
        $this->info('');
        $this->info('📐 Running Code Quality Audit...');
        $this->info('');

        $format = $this->option('format');
        $outputPath = $this->option('output');

        $engine = new CodeQualityEngine();

        $analyzers = [
            'Style' => new StyleChecker(),
            'Complexity' => new ComplexityAnalyzer(),
            'Test Coverage' => new TestCoverageAnalyzer(),
            'Documentation' => new DocumentationAnalyzer(),
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

        $this->components->info("Code quality audit completed in {$executionTime}s");
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
            $outputPath = $dir . '/quality-audit-' . date('Y-m-d_His') . '.' . $extension;
        }

        file_put_contents($outputPath, $content);

        $this->newLine();
        $this->components->info("Report saved to: {$outputPath}");

        return self::SUCCESS;
    }
}
