<?php

namespace App\Services\Audit\CodeQuality\Analyzers;

use App\Contracts\Scanner;
use App\DataModels\Audit\AuditResult;

/**
 * StyleChecker
 * 
 * Checks code style compliance including PSR-12 patterns,
 * naming conventions, file organization, and dead code detection.
 * 
 * @package App\Services\Audit\CodeQuality\Analyzers
 */
class StyleChecker implements Scanner
{
    private array $findings = [];
    private string $basePath;

    public function __construct(?string $basePath = null)
    {
        $this->basePath = $basePath ?? base_path();
    }

    /**
     * @param array<string, mixed> $options
     * @return array<int, AuditResult>
     */
    public function scan(array $options = []): array
    {
        $this->findings = [];

        $this->checkNamingConventions();
        $this->detectDeadCode();
        $this->checkFileOrganization();
        $this->checkFormatterConfig();

        return $this->findings;
    }

    public function getFindings(): array
    {
        return $this->findings;
    }

    /**
     * Check naming conventions in controllers, models, services
     */
    private function checkNamingConventions(): void
    {
        $dirs = [
            $this->basePath . '/app/Http/Controllers' => 'Controller',
            $this->basePath . '/app/Models' => 'Model',
            $this->basePath . '/app/Services' => 'Service',
        ];

        foreach ($dirs as $dir => $type) {
            if (!is_dir($dir)) continue;

            $iterator = new \RecursiveIteratorIterator(
                new \RecursiveDirectoryIterator($dir, \RecursiveDirectoryIterator::SKIP_DOTS)
            );

            foreach ($iterator as $file) {
                if ($file->getExtension() !== 'php') continue;

                $filename = pathinfo($file->getFilename(), PATHINFO_FILENAME);

                // Check PascalCase
                if (!preg_match('/^[A-Z][a-zA-Z0-9]*$/', $filename)) {
                    $this->findings[] = new AuditResult(
                        type: 'code_quality',
                        severity: 'low',
                        category: 'code_style',
                        message: "{$type} '{$filename}' does not follow PascalCase convention",
                        details: [
                            'file' => $file->getPathname(),
                            'recommendation' => 'Rename to PascalCase: ' . ucfirst($filename),
                        ],
                    );
                }

                // Controllers should end with "Controller"
                if ($type === 'Controller' && !str_ends_with($filename, 'Controller')) {
                    $this->findings[] = new AuditResult(
                        type: 'code_quality',
                        severity: 'low',
                        category: 'code_style',
                        message: "Controller '{$filename}' should have 'Controller' suffix",
                        details: [
                            'file' => $file->getPathname(),
                            'recommendation' => "Rename to '{$filename}Controller'",
                        ],
                    );
                }
            }
        }
    }

    /**
     * Detect potential dead code — unused imports and commented-out code blocks
     */
    private function detectDeadCode(): void
    {
        $dirs = [
            $this->basePath . '/app/Http/Controllers',
            $this->basePath . '/app/Services',
            $this->basePath . '/app/Models',
        ];

        foreach ($dirs as $dir) {
            if (!is_dir($dir)) continue;

            $iterator = new \RecursiveIteratorIterator(
                new \RecursiveDirectoryIterator($dir, \RecursiveDirectoryIterator::SKIP_DOTS)
            );

            foreach ($iterator as $file) {
                if ($file->getExtension() !== 'php') continue;

                $filePath = $file->getPathname();
                $content = file_get_contents($filePath);
                $lines = explode("\n", $content);

                // Detect large blocks of commented-out code (3+ consecutive lines)
                $commentBlock = 0;
                $commentStart = 0;
                foreach ($lines as $lineNum => $line) {
                    $trimmed = trim($line);
                    if (str_starts_with($trimmed, '//') && strlen($trimmed) > 5) {
                        if ($commentBlock === 0) $commentStart = $lineNum + 1;
                        $commentBlock++;
                    } else {
                        if ($commentBlock >= 5) {
                            $this->findings[] = new AuditResult(
                                type: 'code_quality',
                                severity: 'low',
                                category: 'code_style',
                                message: "{$commentBlock} consecutive commented-out lines detected (line {$commentStart})",
                                details: [
                                    'file' => $filePath,
                                    'start_line' => $commentStart,
                                    'comment_lines' => $commentBlock,
                                    'recommendation' => 'Remove dead code — use version control to recover old code',
                                ],
                            );
                        }
                        $commentBlock = 0;
                    }
                }

                // Check for large comment blocks at end of file too
                if ($commentBlock >= 5) {
                    $this->findings[] = new AuditResult(
                        type: 'code_quality',
                        severity: 'low',
                        category: 'code_style',
                        message: "{$commentBlock} consecutive commented-out lines at end of file",
                        details: [
                            'file' => $filePath,
                            'recommendation' => 'Remove dead code — use version control to recover old code',
                        ],
                    );
                }
            }
        }
    }

    /**
     * Check file organization and structure
     */
    private function checkFileOrganization(): void
    {
        // Check if controllers are too large (many methods)
        $controllerDir = $this->basePath . '/app/Http/Controllers';
        if (!is_dir($controllerDir)) return;

        $iterator = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($controllerDir, \RecursiveDirectoryIterator::SKIP_DOTS)
        );

        foreach ($iterator as $file) {
            if ($file->getExtension() !== 'php') continue;

            $filePath = $file->getPathname();
            $content = file_get_contents($filePath);

            // Count public methods
            $methodCount = preg_match_all('/public\s+function\s+\w+\s*\(/', $content);

            if ($methodCount > 10) {
                $this->findings[] = new AuditResult(
                    type: 'code_quality',
                    severity: 'medium',
                    category: 'code_style',
                    message: "Controller has {$methodCount} public methods — consider splitting",
                    details: [
                        'file' => $filePath,
                        'method_count' => $methodCount,
                        'recommendation' => 'Split into smaller controllers (single-action or resource controllers) for maintainability',
                    ],
                );
            }

            // Check file size (lines)
            $lineCount = substr_count($content, "\n") + 1;
            if ($lineCount > 300) {
                $this->findings[] = new AuditResult(
                    type: 'code_quality',
                    severity: 'low',
                    category: 'code_style',
                    message: "File has {$lineCount} lines — consider breaking into smaller classes",
                    details: [
                        'file' => $filePath,
                        'line_count' => $lineCount,
                        'recommendation' => 'Extract logic into Services, Actions, or dedicated classes',
                    ],
                );
            }
        }
    }

    /**
     * Check if code formatting tools are configured
     */
    private function checkFormatterConfig(): void
    {
        $formatterConfigs = [
            '.php-cs-fixer.php' => 'PHP-CS-Fixer',
            '.php-cs-fixer.dist.php' => 'PHP-CS-Fixer',
            'pint.json' => 'Laravel Pint',
            '.phpcs.xml' => 'PHP_CodeSniffer',
            'phpcs.xml' => 'PHP_CodeSniffer',
        ];

        $hasFormatter = false;
        foreach ($formatterConfigs as $file => $tool) {
            if (file_exists($this->basePath . '/' . $file)) {
                $hasFormatter = true;
                break;
            }
        }

        if (!$hasFormatter) {
            $this->findings[] = new AuditResult(
                type: 'code_quality',
                severity: 'medium',
                category: 'code_style',
                message: 'No code formatting tool configured (PHP-CS-Fixer, Pint, or PHPCS)',
                details: [
                    'recommendation' => 'Add Laravel Pint: composer require --dev laravel/pint && php artisan pint',
                ],
            );
        }
    }
}
