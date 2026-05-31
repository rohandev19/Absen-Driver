<?php

namespace App\Services\Audit\Performance\Analyzers;

use App\Contracts\Scanner;
use App\DataModels\Audit\AuditResult;

/**
 * QueryAnalyzer
 * 
 * Detects N+1 query patterns, missing eager loading, SELECT * usage,
 * and missing pagination in Eloquent queries.
 */
class QueryAnalyzer implements Scanner
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

        $this->detectNPlusOneQueries();
        $this->checkEagerLoading();
        $this->detectSelectStar();
        $this->checkPagination();

        return $this->findings;
    }

    public function getFindings(): array
    {
        return $this->findings;
    }

    /**
     * Detect N+1 query patterns: loops accessing relationships without eager loading
     */
    private function detectNPlusOneQueries(): void
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

                // Pattern: foreach loop accessing a relationship (->relation without with())
                // e.g.: foreach ($drivers as $driver) { $driver->vehicle->... }
                if (preg_match('/foreach\s*\(\s*\$\w+\s+as\s+\$(\w+)\s*\)/', $content, $loopMatch)) {
                    $varName = $loopMatch[1];
                    // Check if inside the loop there's a relationship access
                    if (preg_match('/\$' . preg_quote($varName) . '->(\w+)->/', $content, $relMatch)) {
                        // Check if with() or load() is used before the loop
                        if (!preg_match('/->with\s*\(|->load\s*\(/', $content)) {
                            $this->findings[] = new AuditResult(
                                type: 'performance',
                                severity: 'high',
                                category: 'query_optimization',
                                message: "Potential N+1 query: \${$varName}->{$relMatch[1]} accessed in loop without eager loading",
                                details: [
                                    'file' => $filePath,
                                    'variable' => $varName,
                                    'relationship' => $relMatch[1],
                                    'recommendation' => "Add ->with('{$relMatch[1]}') to the initial query",
                                ],
                            );
                        }
                    }
                }
            }
        }
    }

    /**
     * Check for models loaded without eager loading where relationships exist
     */
    private function checkEagerLoading(): void
    {
        $modelsDir = $this->basePath . '/app/Models';
        if (!is_dir($modelsDir)) return;

        // First, find models that have relationships defined
        $modelsWithRelations = [];
        $modelIterator = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($modelsDir, \RecursiveDirectoryIterator::SKIP_DOTS)
        );

        foreach ($modelIterator as $file) {
            if ($file->getExtension() !== 'php') continue;
            $content = file_get_contents($file->getPathname());

            if (preg_match_all('/public\s+function\s+(\w+)\s*\(\s*\).*(?:hasMany|hasOne|belongsTo|belongsToMany|morphTo|morphMany)/s', $content, $matches)) {
                $modelName = pathinfo($file->getFilename(), PATHINFO_FILENAME);
                $modelsWithRelations[$modelName] = $matches[1];
            }
        }

        // Check controllers for ::all() calls on models with relationships
        $controllerDir = $this->basePath . '/app/Http/Controllers';
        if (!is_dir($controllerDir)) return;

        $iterator = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($controllerDir, \RecursiveDirectoryIterator::SKIP_DOTS)
        );

        foreach ($iterator as $file) {
            if ($file->getExtension() !== 'php') continue;
            $content = file_get_contents($file->getPathname());

            foreach ($modelsWithRelations as $model => $relations) {
                // Check for Model::all() without with()
                if (preg_match("/{$model}::all\s*\(/", $content)) {
                    $this->findings[] = new AuditResult(
                        type: 'performance',
                        severity: 'medium',
                        category: 'query_optimization',
                        message: "{$model}::all() used without eager loading (model has " . count($relations) . " relationship(s))",
                        details: [
                            'file' => $file->getPathname(),
                            'model' => $model,
                            'relationships' => $relations,
                            'recommendation' => "Use {$model}::with(['" . implode("','", $relations) . "'])->get() instead",
                        ],
                    );
                }
            }
        }
    }

    /**
     * Detect SELECT * patterns (Model::all() or ->get() without ->select())
     */
    private function detectSelectStar(): void
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

                // Count ::all() usages
                $allCount = preg_match_all('/\w+::all\s*\(/', $content);
                if ($allCount > 0) {
                    $this->findings[] = new AuditResult(
                        type: 'performance',
                        severity: 'low',
                        category: 'query_optimization',
                        message: "{$allCount} Model::all() call(s) - fetches all columns",
                        details: [
                            'file' => $filePath,
                            'count' => $allCount,
                            'recommendation' => 'Use ->select() to fetch only needed columns for large tables',
                        ],
                    );
                }
            }
        }
    }

    /**
     * Check for missing pagination on list endpoints
     */
    private function checkPagination(): void
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

            // Check for index() methods that return collections without paginate()
            if (preg_match('/public\s+function\s+index\s*\(/', $content)) {
                $hasPaginate = preg_match('/->paginate\s*\(|->simplePaginate\s*\(|->cursorPaginate\s*\(/', $content);

                if (!$hasPaginate && preg_match('/->get\s*\(\s*\)|::all\s*\(/', $content)) {
                    $this->findings[] = new AuditResult(
                        type: 'performance',
                        severity: 'medium',
                        category: 'query_optimization',
                        message: 'Index method returns unpaginated collection',
                        details: [
                            'file' => $filePath,
                            'recommendation' => 'Use ->paginate(15) or ->simplePaginate(15) for list endpoints',
                        ],
                    );
                }
            }
        }
    }
}
