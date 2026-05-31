<?php

namespace App\Services\Audit\Security\Scanners;

use App\Contracts\Scanner;
use App\DataModels\Audit\AuditResult;

/**
 * CsrfXssScanner
 * 
 * Scans for CSRF and XSS vulnerabilities including missing CSRF tokens,
 * security headers, unescaped output, and JavaScript escaping.
 * 
 * @package App\Services\Audit\Security\Scanners
 */
class CsrfXssScanner implements Scanner
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

        $this->checkCsrfProtection();
        $this->checkSecurityHeaders();
        $this->detectUnescapedOutput();
        $this->checkJavaScriptEscaping();

        return $this->findings;
    }

    public function getFindings(): array
    {
        return $this->findings;
    }

    /**
     * Check CSRF protection on forms in Blade templates
     */
    private function checkCsrfProtection(): void
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

            // Find forms without @csrf
            if (preg_match_all('/<form[^>]*method\s*=\s*[\'"]post[\'"][^>]*>/i', $content, $formMatches, PREG_OFFSET_CAPTURE)) {
                foreach ($formMatches[0] as $formMatch) {
                    $formPos = $formMatch[1];
                    // Get content until next </form>
                    $endPos = strpos($content, '</form>', $formPos);
                    if ($endPos === false) {
                        $endPos = strlen($content);
                    }
                    $formContent = substr($content, $formPos, $endPos - $formPos);

                    if (!str_contains($formContent, '@csrf') && !str_contains($formContent, 'csrf_field') && !str_contains($formContent, '_token')) {
                        $this->findings[] = new AuditResult(
                            type: 'security',
                            severity: 'high',
                            category: 'csrf_xss',
                            message: 'POST form without @csrf token',
                            details: [
                                'file' => $filePath,
                                'recommendation' => 'Add @csrf directive inside the form',
                            ],
                        );
                    }
                }
            }
        }
    }

    /**
     * Check security headers middleware configuration
     */
    private function checkSecurityHeaders(): void
    {
        $middlewarePath = $this->basePath . '/app/Http/Middleware/SecurityHeaders.php';

        if (!is_readable($middlewarePath)) {
            $this->findings[] = new AuditResult(
                type: 'security',
                severity: 'medium',
                category: 'csrf_xss',
                message: 'No SecurityHeaders middleware found',
                details: [
                    'recommendation' => 'Create middleware to set X-Frame-Options, X-Content-Type-Options, CSP headers',
                ],
            );
            return;
        }

        $content = file_get_contents($middlewarePath);

        $requiredHeaders = [
            'X-Frame-Options' => 'Prevents clickjacking',
            'X-Content-Type-Options' => 'Prevents MIME sniffing',
            'X-XSS-Protection' => 'Browser XSS filter',
            'Strict-Transport-Security' => 'Forces HTTPS',
        ];

        foreach ($requiredHeaders as $header => $purpose) {
            if (!str_contains($content, $header)) {
                $this->findings[] = new AuditResult(
                    type: 'security',
                    severity: 'medium',
                    category: 'csrf_xss',
                    message: "Missing security header: {$header} ({$purpose})",
                    details: [
                        'file' => $middlewarePath,
                        'header' => $header,
                        'recommendation' => "Add '{$header}' header in SecurityHeaders middleware",
                    ],
                );
            }
        }
    }

    /**
     * Detect unescaped output in Blade templates ({!! !!} usage)
     */
    private function detectUnescapedOutput(): void
    {
        $viewsDir = $this->basePath . '/resources/views';
        if (!is_dir($viewsDir)) {
            return;
        }

        $iterator = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($viewsDir, \RecursiveDirectoryIterator::SKIP_DOTS)
        );

        $unescapedCount = 0;

        foreach ($iterator as $file) {
            if ($file->getExtension() !== 'php') {
                continue;
            }

            $content = file_get_contents($file->getPathname());

            if (preg_match_all('/\{!!\s*(.+?)\s*!!\}/', $content, $matches)) {
                foreach ($matches[1] as $expression) {
                    // Skip known safe patterns
                    if (preg_match('/csrf|method_field|__\(|trans\(|route\(|url\(|asset\(|\$slot/', $expression)) {
                        continue;
                    }
                    $unescapedCount++;
                }
            }
        }

        if ($unescapedCount > 0) {
            $this->findings[] = new AuditResult(
                type: 'security',
                severity: 'medium',
                category: 'csrf_xss',
                message: "{$unescapedCount} instance(s) of unescaped Blade output ({!! !!}) detected across views",
                details: [
                    'count' => $unescapedCount,
                    'recommendation' => 'Review each {!! !!} usage. Use {{ }} for user-generated content',
                ],
            );
        }
    }

    /**
     * Check for unsafe JavaScript variable injection in Blade
     */
    private function checkJavaScriptEscaping(): void
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

            // Detect Blade variables inside <script> blocks without @json
            if (preg_match('/<script[^>]*>.*?<\/script>/is', $content, $scriptMatches)) {
                foreach ($scriptMatches as $scriptBlock) {
                    // Check for {{ }} or {!! !!} inside script tags
                    if (preg_match('/\{\{.*\}\}/', $scriptBlock) || preg_match('/\{!!.*!!\}/', $scriptBlock)) {
                        // Check if using @json() which is safe
                        if (!str_contains($scriptBlock, '@json')) {
                            $this->findings[] = new AuditResult(
                                type: 'security',
                                severity: 'high',
                                category: 'csrf_xss',
                                message: 'Blade variables inside <script> tag without @json escaping',
                                details: [
                                    'file' => $filePath,
                                    'recommendation' => 'Use @json($variable) to safely embed PHP data in JavaScript',
                                ],
                            );
                        }
                    }
                }
            }
        }
    }
}
