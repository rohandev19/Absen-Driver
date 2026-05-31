<?php

namespace App\Services\Audit\Performance\Analyzers;

use App\Contracts\Scanner;
use App\DataModels\Audit\AuditResult;

/**
 * MemoryAnalyzer
 * 
 * Detects memory-intensive operations, missing chunk processing,
 * lazy collection opportunities, and export streaming.
 */
class MemoryAnalyzer implements Scanner
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

        $this->detectMemoryIntensiveOps();
        $this->checkChunkProcessing();
        $this->identifyLazyCollectionOpportunities();
        $this->checkExportStreaming();

        return $this->findings;
    }

    public function getFindings(): array
    {
        return $this->findings;
    }

    /**
     * Detect operations that load large datasets into memory
     */
    private function detectMemoryIntensiveOps(): void
    {
        $dirs = [
            $this->basePath . '/app/Http/Controllers',
            $this->basePath . '/app/Services',
            $this->basePath . '/app/Console/Commands',
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

                // Detect file_get_contents on potentially large files
                if (preg_match('/file_get_contents\s*\(\s*\$/', $content)) {
                    $this->findings[] = new AuditResult(
                        type: 'performance',
                        severity: 'low',
                        category: 'memory_usage',
                        message: 'file_get_contents() with variable path may load large files into memory',
                        details: [
                            'file' => $filePath,
                            'recommendation' => 'Consider streaming for large files or limit file size before reading',
                        ],
                    );
                }

                // Detect toArray() or toJson() on large collections
                if (preg_match('/::all\s*\(\s*\)\s*->\s*toArray\s*\(/', $content)) {
                    $this->findings[] = new AuditResult(
                        type: 'performance',
                        severity: 'medium',
                        category: 'memory_usage',
                        message: 'Model::all()->toArray() loads entire table into memory',
                        details: [
                            'file' => $filePath,
                            'recommendation' => 'Use pagination or chunking for large datasets',
                        ],
                    );
                }
            }
        }
    }

    /**
     * Check for batch operations that should use chunk()
     */
    private function checkChunkProcessing(): void
    {
        $dirs = [
            $this->basePath . '/app/Services',
            $this->basePath . '/app/Console/Commands',
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

                // Check for foreach over all records without chunk
                if (preg_match('/::all\s*\(\s*\)/', $content) && preg_match('/foreach/', $content)) {
                    if (!preg_match('/->chunk\s*\(|->chunkById\s*\(|->lazy\s*\(/', $content)) {
                        $this->findings[] = new AuditResult(
                            type: 'performance',
                            severity: 'medium',
                            category: 'memory_usage',
                            message: 'Iterating over all records without chunk() processing',
                            details: [
                                'file' => $filePath,
                                'recommendation' => 'Use Model::chunk(100, function($records) {}) for batch processing',
                            ],
                        );
                    }
                }
            }
        }
    }

    /**
     * Identify opportunities to use lazy collections
     */
    private function identifyLazyCollectionOpportunities(): void
    {
        $dirs = [
            $this->basePath . '/app/Services',
            $this->basePath . '/app/Exports',
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

                // Check for cursor() opportunities
                if (preg_match('/->get\s*\(\s*\)/', $content) && preg_match('/foreach|->map\s*\(|->each\s*\(/', $content)) {
                    if (!preg_match('/->cursor\s*\(|->lazy\s*\(/', $content)) {
                        $this->findings[] = new AuditResult(
                            type: 'performance',
                            severity: 'low',
                            category: 'memory_usage',
                            message: 'Consider using ->cursor() or ->lazy() instead of ->get() for iteration',
                            details: [
                                'file' => $filePath,
                                'recommendation' => 'cursor() uses PHP generators for memory-efficient iteration',
                            ],
                        );
                    }
                }
            }
        }
    }

    /**
     * Check export classes for streaming
     */
    private function checkExportStreaming(): void
    {
        $exportsDir = $this->basePath . '/app/Exports';
        if (!is_dir($exportsDir)) return;

        $iterator = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($exportsDir, \RecursiveDirectoryIterator::SKIP_DOTS)
        );

        foreach ($iterator as $file) {
            if ($file->getExtension() !== 'php') continue;

            $filePath = $file->getPathname();
            $content = file_get_contents($filePath);

            // Check if export implements FromQuery (streaming-friendly) vs FromCollection
            if (str_contains($content, 'FromCollection') && !str_contains($content, 'FromQuery')) {
                $this->findings[] = new AuditResult(
                    type: 'performance',
                    severity: 'medium',
                    category: 'memory_usage',
                    message: 'Export uses FromCollection instead of FromQuery — loads all data into memory',
                    details: [
                        'file' => $filePath,
                        'recommendation' => 'Use FromQuery interface for memory-efficient exports',
                    ],
                );
            }
        }
    }
}
