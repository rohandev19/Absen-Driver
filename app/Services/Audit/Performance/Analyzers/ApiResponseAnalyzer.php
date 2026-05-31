<?php

namespace App\Services\Audit\Performance\Analyzers;

use App\Contracts\Scanner;
use App\DataModels\Audit\AuditResult;

/**
 * ApiResponseAnalyzer
 * 
 * Analyzes API response patterns including payload sizes,
 * API resource usage, pagination, and compression.
 */
class ApiResponseAnalyzer implements Scanner
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

        $this->measureResponseSizes();
        $this->checkApiResources();
        $this->checkPagination();
        $this->checkCompression();

        return $this->findings;
    }

    public function getFindings(): array
    {
        return $this->findings;
    }

    /**
     * Estimate API response payload sizes
     */
    private function measureResponseSizes(): void
    {
        $apiControllerDir = $this->basePath . '/app/Http/Controllers/Api';
        if (!is_dir($apiControllerDir)) return;

        $iterator = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($apiControllerDir, \RecursiveDirectoryIterator::SKIP_DOTS)
        );

        foreach ($iterator as $file) {
            if ($file->getExtension() !== 'php') continue;

            $filePath = $file->getPathname();
            $content = file_get_contents($filePath);

            // Check for responses returning large datasets without field limiting
            if (preg_match('/response\(\)->json\(.*::all\s*\(\)/s', $content) ||
                preg_match('/return\s+.*::all\s*\(/', $content)) {
                $this->findings[] = new AuditResult(
                    type: 'performance',
                    severity: 'medium',
                    category: 'query_optimization',
                    message: 'API endpoint returns all records without field selection',
                    details: [
                        'file' => $filePath,
                        'recommendation' => 'Use ->select() or API Resources to limit response payload',
                    ],
                );
            }
        }
    }

    /**
     * Check if API controllers use API Resources for response transformation
     */
    private function checkApiResources(): void
    {
        $apiControllerDir = $this->basePath . '/app/Http/Controllers/Api';
        if (!is_dir($apiControllerDir)) return;

        $iterator = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($apiControllerDir, \RecursiveDirectoryIterator::SKIP_DOTS)
        );

        foreach ($iterator as $file) {
            if ($file->getExtension() !== 'php') continue;

            $filePath = $file->getPathname();
            $content = file_get_contents($filePath);

            // Check if controller uses API Resources
            $usesResource = preg_match('/Resource::collection|new\s+\w+Resource/', $content);
            $returnsJson = preg_match('/response\(\)->json\(|return\s+response\(/', $content);

            if ($returnsJson && !$usesResource) {
                $this->findings[] = new AuditResult(
                    type: 'performance',
                    severity: 'low',
                    category: 'query_optimization',
                    message: 'API controller returns raw JSON without API Resources',
                    details: [
                        'file' => $filePath,
                        'recommendation' => 'Use Laravel API Resources to standardize response format and control payload size',
                    ],
                );
            }
        }
    }

    /**
     * Check pagination on API list endpoints
     */
    private function checkPagination(): void
    {
        $apiControllerDir = $this->basePath . '/app/Http/Controllers/Api';
        if (!is_dir($apiControllerDir)) return;

        $iterator = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($apiControllerDir, \RecursiveDirectoryIterator::SKIP_DOTS)
        );

        foreach ($iterator as $file) {
            if ($file->getExtension() !== 'php') continue;

            $filePath = $file->getPathname();
            $content = file_get_contents($filePath);

            // Check index/list methods for pagination
            if (preg_match('/public\s+function\s+(?:index|list|getAll)\s*\(/', $content)) {
                if (!preg_match('/paginate\s*\(|simplePaginate\s*\(/', $content)) {
                    if (preg_match('/->get\s*\(\s*\)|::all\s*\(/', $content)) {
                        $this->findings[] = new AuditResult(
                            type: 'performance',
                            severity: 'medium',
                            category: 'query_optimization',
                            message: 'API list endpoint without pagination',
                            details: [
                                'file' => $filePath,
                                'recommendation' => 'Use ->paginate() for list endpoints to limit data transfer',
                            ],
                        );
                    }
                }
            }
        }
    }

    /**
     * Check if response compression is configured
     */
    private function checkCompression(): void
    {
        // Check for gzip middleware or configuration
        $middlewareDir = $this->basePath . '/app/Http/Middleware';
        if (!is_dir($middlewareDir)) return;

        $hasCompression = false;

        $iterator = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($middlewareDir, \RecursiveDirectoryIterator::SKIP_DOTS)
        );

        foreach ($iterator as $file) {
            if ($file->getExtension() !== 'php') continue;
            $content = file_get_contents($file->getPathname());

            if (str_contains($content, 'gzip') || str_contains($content, 'compress') || str_contains($content, 'Content-Encoding')) {
                $hasCompression = true;
                break;
            }
        }

        if (!$hasCompression) {
            $this->findings[] = new AuditResult(
                type: 'performance',
                severity: 'low',
                category: 'caching',
                message: 'No response compression middleware detected',
                details: [
                    'recommendation' => 'Enable gzip compression at the web server level (Nginx/Apache) or add middleware',
                ],
            );
        }
    }
}
