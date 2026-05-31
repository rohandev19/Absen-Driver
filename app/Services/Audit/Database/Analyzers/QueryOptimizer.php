<?php

namespace App\Services\Audit\Database\Analyzers;

use App\Contracts\Scanner;
use App\DataModels\Audit\AuditResult;

/**
 * QueryOptimizer
 * 
 * Detects raw SQL injection risks, missing parameter binding,
 * SELECT * patterns in raw queries, and suboptimal Eloquent patterns.
 * 
 * @package App\Services\Audit\Database\Analyzers
 */
class QueryOptimizer implements Scanner
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

        $this->detectRawSqlInjection();
        $this->detectIneffientPatterns();
        $this->checkSoftDeleteUsage();

        return $this->findings;
    }

    public function getFindings(): array
    {
        return $this->findings;
    }

    /**
     * Detect raw SQL queries that may be vulnerable to injection
     * or miss parameter binding
     */
    private function detectRawSqlInjection(): void
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

                // Skip audit system's own files to avoid false positives
                if (str_contains($filePath, 'Services' . DIRECTORY_SEPARATOR . 'Audit')) {
                    continue;
                }

                // Detect DB::raw() with string concatenation or variable interpolation
                if (preg_match('/DB::raw\s*\(\s*"[^"]*\$/', $content)
                    || preg_match('/DB::raw\s*\(\s*[\'"].*[\'"]\s*\.\s*\$/', $content)) {
                    $this->findings[] = new AuditResult(
                        type: 'database',
                        severity: 'critical',
                        category: 'query_optimization',
                        message: 'DB::raw() with variable interpolation — potential SQL injection',
                        details: [
                            'file' => $filePath,
                            'recommendation' => 'Use parameterized queries: DB::raw("column = ?", [$value])',
                        ],
                    );
                }

                // Detect DB::select/statement with string concatenation
                if (preg_match('/DB::(?:select|statement|insert|update|delete)\s*\(\s*["\'].*["\']\s*\.\s*\$/', $content)
                    || preg_match('/DB::(?:select|statement|insert|update|delete)\s*\(\s*"[^"]*\$/', $content)) {
                    $this->findings[] = new AuditResult(
                        type: 'database',
                        severity: 'critical',
                        category: 'query_optimization',
                        message: 'Raw SQL query with string concatenation — SQL injection risk',
                        details: [
                            'file' => $filePath,
                            'recommendation' => 'Use parameter binding: DB::select("SELECT * FROM table WHERE id = ?", [$id])',
                        ],
                    );
                }

                // Detect whereRaw with concatenation
                if (preg_match('/whereRaw\s*\(\s*["\'][^"\']*\s*\.\s*\$/', $content)) {
                    $this->findings[] = new AuditResult(
                        type: 'database',
                        severity: 'high',
                        category: 'query_optimization',
                        message: 'whereRaw() with string concatenation — use parameter binding',
                        details: [
                            'file' => $filePath,
                            'recommendation' => 'Use ->whereRaw("column = ?", [$value]) with binding',
                        ],
                    );
                }
            }
        }
    }

    /**
     * Detect inefficient Eloquent patterns
     */
    private function detectIneffientPatterns(): void
    {
        $dirs = [
            $this->basePath . '/app/Http/Controllers',
            $this->basePath . '/app/Services',
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

                // Skip audit system's own files to avoid false positives
                if (str_contains($filePath, 'Services' . DIRECTORY_SEPARATOR . 'Audit')) {
                    continue;
                }

                // Detect count() after get() — should use ->count() directly
                if (preg_match('/->\s*get\s*\(\s*\)\s*->\s*count\s*\(/', $content)) {
                    $this->findings[] = new AuditResult(
                        type: 'database',
                        severity: 'medium',
                        category: 'query_optimization',
                        message: 'get()->count() fetches all records just to count — use ->count() directly',
                        details: [
                            'file' => $filePath,
                            'recommendation' => 'Replace ->get()->count() with ->count() to use SQL COUNT()',
                        ],
                    );
                }

                // Detect multiple separate queries that could be one
                if (preg_match_all('/(\w+)::where\s*\(\s*[\'"](\w+)[\'"]\s*,/', $content, $matches)) {
                    $modelQueries = [];
                    foreach ($matches[1] as $i => $model) {
                        $modelQueries[$model] = ($modelQueries[$model] ?? 0) + 1;
                    }
                    foreach ($modelQueries as $model => $count) {
                        if ($count >= 4) {
                            $this->findings[] = new AuditResult(
                                type: 'database',
                                severity: 'low',
                                category: 'query_optimization',
                                message: "Model '{$model}' queried {$count} times in same file — consider consolidating queries",
                                details: [
                                    'file' => $filePath,
                                    'model' => $model,
                                    'query_count' => $count,
                                    'recommendation' => 'Use scopes, relationships, or combined queries to reduce database round-trips',
                                ],
                            );
                        }
                    }
                }

                // Detect ->first() without ->select() on large responses
                $firstCount = preg_match_all('/->\s*first\s*\(\s*\)/', $content);
                $hasSelect = preg_match('/->\s*select\s*\(/', $content);
                if ($firstCount >= 3 && !$hasSelect) {
                    $this->findings[] = new AuditResult(
                        type: 'database',
                        severity: 'low',
                        category: 'query_optimization',
                        message: "Multiple ->first() calls without ->select() — may fetch unnecessary columns",
                        details: [
                            'file' => $filePath,
                            'recommendation' => 'Add ->select() to limit columns fetched',
                        ],
                    );
                }
            }
        }
    }

    /**
     * Check for models with SoftDeletes that may accumulate soft-deleted records
     */
    private function checkSoftDeleteUsage(): void
    {
        $modelsDir = $this->basePath . '/app/Models';
        if (!is_dir($modelsDir)) return;

        $iterator = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($modelsDir, \RecursiveDirectoryIterator::SKIP_DOTS)
        );

        $softDeleteModels = [];

        foreach ($iterator as $file) {
            if ($file->getExtension() !== 'php') continue;

            $content = file_get_contents($file->getPathname());

            if (str_contains($content, 'SoftDeletes')) {
                $modelName = pathinfo($file->getFilename(), PATHINFO_FILENAME);
                $softDeleteModels[] = $modelName;
            }
        }

        if (count($softDeleteModels) > 3) {
            $this->findings[] = new AuditResult(
                type: 'database',
                severity: 'info',
                category: 'query_optimization',
                message: count($softDeleteModels) . ' models use SoftDeletes — ensure periodic cleanup of soft-deleted records',
                details: [
                    'models' => $softDeleteModels,
                    'recommendation' => 'Schedule a command to permanently delete old soft-deleted records (e.g., older than 90 days)',
                ],
            );
        }
    }
}
