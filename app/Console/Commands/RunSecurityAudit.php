<?php

namespace App\Console\Commands;

use App\Services\Audit\Security\SecurityAuditEngine;
use App\Services\Audit\Reporting\ReportGenerator;
use App\Services\Audit\Security\Scanners\AuthenticationScanner;
use App\Services\Audit\Security\Scanners\AuthorizationScanner;
use App\Services\Audit\Security\Scanners\InputValidationScanner;
use App\Services\Audit\Security\Scanners\CsrfXssScanner;
use App\Services\Audit\Security\Scanners\SensitiveDataScanner;
use App\Services\Audit\Security\Scanners\DependencyScanner;
use App\Services\Audit\Security\Scanners\ConfigurationScanner;
use Illuminate\Console\Command;

/**
 * RunSecurityAudit Command
 * 
 * Runs security-specific audit with all security scanners.
 * 
 * Usage:
 *   php artisan audit:security
 *   php artisan audit:security --format=json
 *   php artisan audit:security --format=markdown --output=security-report.md
 */
class RunSecurityAudit extends Command
{
    protected $signature = 'audit:security 
        {--format=html : Output format (html, json, markdown)}
        {--output= : Output file path}';

    protected $description = 'Run security audit with all security scanners';

    public function handle(): int
    {
        $this->info('');
        $this->info('🔒 Running Security Audit...');
        $this->info('');

        $format = $this->option('format');
        $outputPath = $this->option('output');

        // Build engine with all scanners
        $engine = new SecurityAuditEngine();

        $scanners = [
            'Authentication' => new AuthenticationScanner(),
            'Authorization' => new AuthorizationScanner(),
            'Input Validation' => new InputValidationScanner(),
            'CSRF/XSS' => new CsrfXssScanner(),
            'Sensitive Data' => new SensitiveDataScanner(),
            'Dependencies' => new DependencyScanner(),
            'Configuration' => new ConfigurationScanner(),
        ];

        foreach ($scanners as $name => $scanner) {
            $this->components->task("Loading {$name} Scanner", function () use ($engine, $scanner) {
                $engine->addScanner($scanner);
                return true;
            });
        }

        $this->newLine();

        $startTime = microtime(true);
        $report = $engine->generateReport();
        $executionTime = round(microtime(true) - $startTime, 2);

        // Display summary
        $summary = $report->getSummary();

        $this->components->info("Security audit completed in {$executionTime}s");
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
            $outputPath = $dir . '/security-audit-' . date('Y-m-d_His') . '.' . $extension;
        }

        file_put_contents($outputPath, $content);

        $this->newLine();
        $this->components->info("Report saved to: {$outputPath}");

        if ($report->hasCriticalFindings()) {
            $this->newLine();
            $this->error('⚠️  Critical security findings detected!');
            return self::FAILURE;
        }

        return self::SUCCESS;
    }
}
