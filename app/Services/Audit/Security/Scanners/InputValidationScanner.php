<?php

namespace App\Services\Audit\Security\Scanners;

use App\Contracts\Scanner;
use App\DataModels\Audit\AuditResult;

/**
 * InputValidationScanner
 * 
 * Scans for input validation vulnerabilities including raw SQL injection,
 * file upload issues, missing validation rules, output escaping, and
 * dangerous function usage.
 * 
 * @package App\Services\Audit\Security\Scanners
 */
class InputValidationScanner implements Scanner
{
    private array $findings = [];
    private string $basePath;

    public function __construct(?string $basePath = null)
    {
        $this->basePath = $basePath ?? base_path();
    }

    public function scan(array $options = []): array
    {
        $this->findings = [];

        $this->scanDatabaseQueries();
        $this->checkFileUploadValidation();
        $this->scanValidationRules();
        $this->checkOutputEscaping();
        $this->detectDangerousFunctions();

        return $this->findings;
    }

    public function getFindings(): array
    {
        return $this->findings;
    }

    /**
     * Detect raw SQL with string concatenation (SQL injection risk)
     */
    private function scanDatabaseQueries(): void
    {
        $dirs = [
            $this->basePath . '/app/Http/Controllers',
            $this->basePath . '/app/Services',
        ];

        $patterns = [
            '/DB::raw\s*\(\s*[\'"].*\$/' => 'DB::raw() with variable interpolation',
            '/DB::select\s*\(\s*[\'"].*\$/' => 'DB::select() with variable interpolation',
            '/DB::statement\s*\(\s*[\'"].*\$/' => 'DB::statement() with variable interpolation',
            '/whereRaw\s*\(\s*[\'"].*\$/' => 'whereRaw() with variable interpolation',
            '/selectRaw\s*\(\s*[\'"].*\$/' => 'selectRaw() with variable interpolation',
            '/orderByRaw\s*\(\s*[\'"].*\$/' => 'orderByRaw() with variable interpolation',
        ];

        foreach ($dirs as $dir) {
            $this->scanDirectoryForPatterns($dir, $patterns, 'input_validation', 'critical',
                'Potential SQL injection: raw query with variable interpolation');
        }
    }

    /**
     * Check file upload validation
     */
    private function checkFileUploadValidation(): void
    {
        $controllerDir = $this->basePath . '/app/Http/Controllers';

        if (!is_dir($controllerDir)) {
            return;
        }

        $iterator = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($controllerDir, \RecursiveDirectoryIterator::SKIP_DOTS)
        );

        foreach ($iterator as $file) {
            if ($file->getExtension() !== 'php') {
                continue;
            }

            $filePath = $file->getPathname();
            $content = file_get_contents($filePath);

            // Check if controller handles file uploads
            if (preg_match('/\$request->file\s*\(|\$request->hasFile\s*\(/', $content)) {
                // Check for MIME type validation
                if (!preg_match('/mimes:|mimetypes:|image|file/', $content)) {
                    $this->findings[] = new AuditResult(
                        type: 'security',
                        severity: 'high',
                        category: 'file_upload',
                        message: 'File upload without MIME type validation',
                        details: [
                            'file' => $filePath,
                            'recommendation' => "Add 'mimes:jpg,png,pdf' validation rule for file uploads",
                        ],
                    );
                }

                // Check for file size validation
                if (!preg_match('/max:\d+|size:\d+/', $content)) {
                    $this->findings[] = new AuditResult(
                        type: 'security',
                        severity: 'medium',
                        category: 'file_upload',
                        message: 'File upload without size validation',
                        details: [
                            'file' => $filePath,
                            'recommendation' => "Add 'max:2048' (2MB) validation rule for file uploads",
                        ],
                    );
                }
            }
        }
    }

    /**
     * Scan controllers for proper Laravel validation usage
     */
    private function scanValidationRules(): void
    {
        $controllerDir = $this->basePath . '/app/Http/Controllers';

        if (!is_dir($controllerDir)) {
            return;
        }

        $iterator = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($controllerDir, \RecursiveDirectoryIterator::SKIP_DOTS)
        );

        foreach ($iterator as $file) {
            if ($file->getExtension() !== 'php') {
                continue;
            }

            $filePath = $file->getPathname();
            $content = file_get_contents($filePath);

            // Skip base controller
            $basename = basename($filePath);
            if ($basename === 'Controller.php') {
                continue;
            }

            // Check for POST/PUT handler methods without validation
            if (preg_match_all('/public\s+function\s+(store|update|create|save)\s*\(/i', $content, $methods)) {
                $hasValidation = preg_match('/\$request->validate\s*\(|\$this->validate\s*\(|Validator::make/', $content);
                $hasFormRequest = preg_match('/FormRequest|Request\s+\$request/', $content);

                if (!$hasValidation && !$hasFormRequest) {
                    foreach ($methods[1] as $method) {
                        $this->findings[] = new AuditResult(
                            type: 'security',
                            severity: 'medium',
                            category: 'input_validation',
                            message: "Method '{$method}()' may lack input validation",
                            details: [
                                'file' => $filePath,
                                'method' => $method,
                                'recommendation' => 'Use $request->validate() or FormRequest for all data-modifying endpoints',
                            ],
                        );
                    }
                }
            }
        }
    }

    /**
     * Check Blade templates for proper output escaping
     */
    private function checkOutputEscaping(): void
    {
        $viewsDir = $this->basePath . '/resources/views';

        if (!is_dir($viewsDir)) {
            return;
        }

        $iterator = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($viewsDir, \RecursiveDirectoryIterator::SKIP_DOTS)
        );

        foreach ($iterator as $file) {
            if ($file->getExtension() !== 'php') {
                continue;
            }

            $filePath = $file->getPathname();
            $content = file_get_contents($filePath);

            // Detect unescaped output {!! !!}
            if (preg_match_all('/\{!!\s*(.+?)\s*!!\}/', $content, $matches)) {
                foreach ($matches[1] as $expression) {
                    // Skip known safe usages (CSRF, method fields, etc.)
                    if (preg_match('/csrf|method_field|__\(|trans\(|route\(|url\(|asset\(/', $expression)) {
                        continue;
                    }

                    $this->findings[] = new AuditResult(
                        type: 'security',
                        severity: 'medium',
                        category: 'csrf_xss',
                        message: 'Unescaped Blade output detected ({!! !!})',
                        details: [
                            'file' => $filePath,
                            'expression' => $expression,
                            'recommendation' => 'Use {{ }} for escaped output, or verify the data is safe HTML',
                        ],
                    );
                }
            }
        }
    }

    /**
     * Detect usage of dangerous PHP functions
     */
    private function detectDangerousFunctions(): void
    {
        $dangerousFunctions = [
            'eval(' => 'eval() allows arbitrary code execution',
            'exec(' => 'exec() allows shell command execution',
            'shell_exec(' => 'shell_exec() allows shell command execution',
            'system(' => 'system() allows shell command execution',
            'passthru(' => 'passthru() allows shell command execution',
            'popen(' => 'popen() allows process execution',
            'proc_open(' => 'proc_open() allows process execution',
        ];

        $dirs = [
            $this->basePath . '/app',
        ];

        foreach ($dirs as $dir) {
            if (!is_dir($dir)) {
                continue;
            }

            $iterator = new \RecursiveIteratorIterator(
                new \RecursiveDirectoryIterator($dir, \RecursiveDirectoryIterator::SKIP_DOTS)
            );

            foreach ($iterator as $file) {
                if ($file->getExtension() !== 'php') {
                    continue;
                }

                $filePath = $file->getPathname();
                $content = file_get_contents($filePath);

                // Skip audit system's own files to avoid false positives
                if (str_contains($filePath, 'Services' . DIRECTORY_SEPARATOR . 'Audit')
                    || str_contains($filePath, 'Middleware' . DIRECTORY_SEPARATOR . 'PerformanceMonitor')
                    || str_contains($filePath, 'Middleware' . DIRECTORY_SEPARATOR . 'SlowQueryLogger')) {
                    continue;
                }

                foreach ($dangerousFunctions as $func => $description) {
                    if (str_contains($content, $func)) {
                        // Skip if in comments
                        $lines = explode("\n", $content);
                        foreach ($lines as $lineNum => $line) {
                            $trimmed = trim($line);
                            if (str_contains($trimmed, $func) &&
                                !str_starts_with($trimmed, '//') &&
                                !str_starts_with($trimmed, '*') &&
                                !str_starts_with($trimmed, '/*')) {

                                $this->findings[] = new AuditResult(
                                    type: 'security',
                                    severity: 'critical',
                                    category: 'input_validation',
                                    message: "Dangerous function used: {$description}",
                                    details: [
                                        'file' => $filePath,
                                        'line' => $lineNum + 1,
                                        'function' => trim($func, '('),
                                        'recommendation' => 'Avoid using dangerous functions; use Laravel abstractions instead',
                                    ],
                                );
                                break; // Only report once per file per function
                            }
                        }
                    }
                }
            }
        }
    }

    /**
     * Helper to scan a directory for regex patterns
     */
    private function scanDirectoryForPatterns(string $dir, array $patterns, string $category, string $severity, string $message): void
    {
        if (!is_dir($dir)) {
            return;
        }

        $iterator = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($dir, \RecursiveDirectoryIterator::SKIP_DOTS)
        );

        foreach ($iterator as $file) {
            if ($file->getExtension() !== 'php') {
                continue;
            }

            $filePath = $file->getPathname();
            $content = file_get_contents($filePath);

            // Skip audit system's own files to avoid false positives
            if (str_contains($filePath, 'Services' . DIRECTORY_SEPARATOR . 'Audit')
                || str_contains($filePath, 'Middleware' . DIRECTORY_SEPARATOR . 'PerformanceMonitor')
                || str_contains($filePath, 'Middleware' . DIRECTORY_SEPARATOR . 'SlowQueryLogger')) {
                continue;
            }

            foreach ($patterns as $pattern => $description) {
                if (preg_match($pattern, $content)) {
                    $this->findings[] = new AuditResult(
                        type: 'security',
                        severity: $severity,
                        category: $category,
                        message: $message . ': ' . $description,
                        details: [
                            'file' => $filePath,
                            'pattern' => $description,
                        ],
                    );
                }
            }
        }
    }
}
