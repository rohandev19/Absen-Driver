<?php

namespace App\Services\Audit\Performance\Analyzers;

use App\Contracts\Scanner;
use App\DataModels\Audit\AuditResult;

/**
 * CacheAnalyzer
 * 
 * Analyzes caching strategy, identifies cacheable data,
 * and recommends cache driver improvements.
 */
class CacheAnalyzer implements Scanner
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

        $this->identifyCacheableData();
        $this->recommendCacheDriver();
        $this->analyzeCacheInvalidation();

        return $this->findings;
    }

    public function getFindings(): array
    {
        return $this->findings;
    }

    /**
     * Identify frequently accessed data that could be cached
     */
    private function identifyCacheableData(): void
    {
        $controllerDir = $this->basePath . '/app/Http/Controllers';
        if (!is_dir($controllerDir)) return;

        $iterator = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($controllerDir, \RecursiveDirectoryIterator::SKIP_DOTS)
        );

        foreach ($iterator as $file) {
            if ($file->getExtension() !== 'php') continue;

            $filePath = $file->getPathname();
            $content = file_get_contents($filePath);

            // Check for GET endpoints that query the database without caching
            $hasDbQuery = preg_match('/::all\s*\(|::where\s*\(|->get\s*\(|->first\s*\(|->count\s*\(/', $content);
            $hasCache = preg_match('/Cache::|cache\(\)|remember\(|rememberForever\(/', $content);

            if ($hasDbQuery && !$hasCache) {
                // Check if it's a read-heavy controller (dashboard, report, list)
                $basename = strtolower(basename($filePath, '.php'));
                $readHeavyPatterns = ['dashboard', 'report', 'rekap', 'riwayat', 'index', 'list'];

                foreach ($readHeavyPatterns as $pattern) {
                    if (str_contains($basename, $pattern)) {
                        $this->findings[] = new AuditResult(
                            type: 'performance',
                            severity: 'medium',
                            category: 'caching',
                            message: "Read-heavy controller '{$basename}' has no caching strategy",
                            details: [
                                'file' => $filePath,
                                'recommendation' => "Use Cache::remember('key', ttl, fn() => query) for frequently accessed data",
                            ],
                        );
                        break;
                    }
                }
            }
        }
    }

    /**
     * Check and recommend cache driver configuration
     */
    private function recommendCacheDriver(): void
    {
        $cacheConfig = $this->basePath . '/config/cache.php';
        $envFile = $this->basePath . '/.env';

        // Check current cache driver
        if (is_readable($envFile)) {
            $envContent = file_get_contents($envFile);
            if (preg_match('/CACHE_STORE\s*=\s*(.+)/', $envContent, $match) ||
                preg_match('/CACHE_DRIVER\s*=\s*(.+)/', $envContent, $match)) {
                $driver = trim($match[1]);

                if ($driver === 'file') {
                    $this->findings[] = new AuditResult(
                        type: 'performance',
                        severity: 'medium',
                        category: 'caching',
                        message: 'Cache driver is "file" — consider Redis or Memcached for production',
                        details: [
                            'current_driver' => 'file',
                            'recommendation' => 'Redis provides better performance, atomic operations, and cache tags support',
                        ],
                    );
                } elseif ($driver === 'array') {
                    $this->findings[] = new AuditResult(
                        type: 'performance',
                        severity: 'high',
                        category: 'caching',
                        message: 'Cache driver is "array" — cache is not persisted between requests',
                        details: [
                            'current_driver' => 'array',
                            'recommendation' => 'Use Redis or file cache driver for production',
                        ],
                    );
                }
            }
        }
    }

    /**
     * Analyze cache invalidation patterns
     */
    private function analyzeCacheInvalidation(): void
    {
        $dirs = [
            $this->basePath . '/app/Http/Controllers',
            $this->basePath . '/app/Services',
        ];

        $hasCache = false;
        $hasCacheForget = false;

        foreach ($dirs as $dir) {
            if (!is_dir($dir)) continue;

            $iterator = new \RecursiveIteratorIterator(
                new \RecursiveDirectoryIterator($dir, \RecursiveDirectoryIterator::SKIP_DOTS)
            );

            foreach ($iterator as $file) {
                if ($file->getExtension() !== 'php') continue;

                $content = file_get_contents($file->getPathname());

                if (preg_match('/Cache::remember|Cache::put/', $content)) {
                    $hasCache = true;
                }
                if (preg_match('/Cache::forget|Cache::flush|Cache::clear/', $content)) {
                    $hasCacheForget = true;
                }
            }
        }

        if ($hasCache && !$hasCacheForget) {
            $this->findings[] = new AuditResult(
                type: 'performance',
                severity: 'low',
                category: 'caching',
                message: 'Cache is used but no cache invalidation logic found',
                details: [
                    'recommendation' => 'Add Cache::forget() in update/delete operations to prevent stale data',
                ],
            );
        }
    }
}
