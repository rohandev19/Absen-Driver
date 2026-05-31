<?php

namespace App\Services\Audit\Database\Analyzers;

use App\Contracts\Scanner;
use App\DataModels\Audit\AuditResult;

/**
 * IndexAnalyzer
 * 
 * Analyzes database migrations for missing indexes on foreign keys,
 * frequently queried columns, and composite index opportunities.
 * 
 * @package App\Services\Audit\Database\Analyzers
 */
class IndexAnalyzer implements Scanner
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

        $this->checkForeignKeyIndexes();
        $this->checkFrequentlyQueriedColumns();
        $this->detectMissingCompositeIndexes();

        return $this->findings;
    }

    public function getFindings(): array
    {
        return $this->findings;
    }

    /**
     * Check migrations for foreign keys without explicit indexes
     */
    private function checkForeignKeyIndexes(): void
    {
        $migrationsDir = $this->basePath . '/database/migrations';
        if (!is_dir($migrationsDir)) return;

        $iterator = new \DirectoryIterator($migrationsDir);

        foreach ($iterator as $file) {
            if ($file->isDot() || $file->getExtension() !== 'php') continue;

            $filePath = $file->getPathname();
            $content = file_get_contents($filePath);

            // Find foreign key columns (e.g., $table->foreignId('user_id') or $table->unsignedBigInteger('driver_id'))
            preg_match_all(
                '/\$table\s*->\s*(?:foreignId|unsignedBigInteger|unsignedInteger)\s*\(\s*[\'"](\w+_id)[\'"]\s*\)/',
                $content,
                $foreignKeyMatches
            );

            if (empty($foreignKeyMatches[1])) continue;

            foreach ($foreignKeyMatches[1] as $column) {
                // Check if there's an explicit index for this column
                $hasIndex = preg_match(
                    '/\$table\s*->\s*(?:index|unique)\s*\(\s*[\'"]' . preg_quote($column) . '[\'"]/',
                    $content
                );

                // Also check if constrained() is used (which auto-creates index in some DBs)
                $hasConstraint = preg_match(
                    '/foreignId\s*\(\s*[\'"]' . preg_quote($column) . '[\'"]\s*\)\s*->\s*constrained/',
                    $content
                );

                if (!$hasIndex && !$hasConstraint) {
                    $this->findings[] = new AuditResult(
                        type: 'database',
                        severity: 'medium',
                        category: 'index_optimization',
                        message: "Foreign key column '{$column}' may lack an explicit index",
                        details: [
                            'file' => $filePath,
                            'column' => $column,
                            'recommendation' => "Add \$table->index('{$column}') or use ->constrained() which auto-creates an index",
                        ],
                    );
                }
            }
        }
    }

    /**
     * Check controllers/services for frequently queried columns without indexes
     */
    private function checkFrequentlyQueriedColumns(): void
    {
        $dirs = [
            $this->basePath . '/app/Http/Controllers',
            $this->basePath . '/app/Services',
        ];

        $queriedColumns = [];

        foreach ($dirs as $dir) {
            if (!is_dir($dir)) continue;

            $iterator = new \RecursiveIteratorIterator(
                new \RecursiveDirectoryIterator($dir, \RecursiveDirectoryIterator::SKIP_DOTS)
            );

            foreach ($iterator as $file) {
                if ($file->getExtension() !== 'php') continue;

                $content = file_get_contents($file->getPathname());

                // Find ->where('column', ...) patterns
                preg_match_all(
                    '/->\s*where\s*\(\s*[\'"](\w+)[\'"]\s*,/',
                    $content,
                    $whereMatches
                );

                foreach ($whereMatches[1] as $column) {
                    $queriedColumns[$column] = ($queriedColumns[$column] ?? 0) + 1;
                }

                // Find ->whereIn('column', ...) patterns
                preg_match_all(
                    '/->\s*whereIn\s*\(\s*[\'"](\w+)[\'"]\s*,/',
                    $content,
                    $whereInMatches
                );

                foreach ($whereInMatches[1] as $column) {
                    $queriedColumns[$column] = ($queriedColumns[$column] ?? 0) + 1;
                }

                // Find ->orderBy('column') patterns
                preg_match_all(
                    '/->\s*orderBy\s*\(\s*[\'"](\w+)[\'"]/',
                    $content,
                    $orderByMatches
                );

                foreach ($orderByMatches[1] as $column) {
                    $queriedColumns[$column] = ($queriedColumns[$column] ?? 0) + 1;
                }
            }
        }

        // Report columns queried 3+ times as candidates for indexing
        $commonColumns = ['id', 'created_at', 'updated_at', 'deleted_at'];
        foreach ($queriedColumns as $column => $count) {
            if ($count >= 3 && !in_array($column, $commonColumns)) {
                $this->findings[] = new AuditResult(
                    type: 'database',
                    severity: 'low',
                    category: 'index_optimization',
                    message: "Column '{$column}' is queried {$count} times — consider adding an index",
                    details: [
                        'column' => $column,
                        'query_count' => $count,
                        'recommendation' => "Add index in migration: \$table->index('{$column}')",
                    ],
                );
            }
        }
    }

    /**
     * Detect opportunities for composite indexes based on multi-column WHERE clauses
     */
    private function detectMissingCompositeIndexes(): void
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

                $content = file_get_contents($file->getPathname());

                // Detect chained ->where() calls (potential composite index)
                if (preg_match_all(
                    '/->\s*where\s*\(\s*[\'"](\w+)[\'"]\s*,\s*[^)]+\)\s*->\s*where\s*\(\s*[\'"](\w+)[\'"]\s*,/',
                    $content,
                    $chainedWheres,
                    PREG_SET_ORDER
                )) {
                    foreach ($chainedWheres as $match) {
                        $col1 = $match[1];
                        $col2 = $match[2];

                        $this->findings[] = new AuditResult(
                            type: 'database',
                            severity: 'low',
                            category: 'index_optimization',
                            message: "Chained WHERE on '{$col1}' + '{$col2}' — consider composite index",
                            details: [
                                'file' => $file->getPathname(),
                                'columns' => [$col1, $col2],
                                'recommendation' => "Add composite index: \$table->index(['{$col1}', '{$col2}'])",
                            ],
                        );
                    }
                }
            }
        }
    }
}
