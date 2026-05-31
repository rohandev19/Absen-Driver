<?php

namespace App\Services\Audit\CodeQuality\Analyzers;

use App\Contracts\Scanner;
use App\DataModels\Audit\AuditResult;

/**
 * ComplexityAnalyzer
 * 
 * Analyzes code complexity including method length, nesting depth,
 * parameter count, and cyclomatic complexity indicators.
 * 
 * @package App\Services\Audit\CodeQuality\Analyzers
 */
class ComplexityAnalyzer implements Scanner
{
    private array $findings = [];
    private string $basePath;

    /** Maximum recommended method lines */
    private const MAX_METHOD_LINES = 50;

    /** Maximum recommended nesting depth */
    private const MAX_NESTING_DEPTH = 4;

    /** Maximum recommended parameter count */
    private const MAX_PARAMETERS = 5;

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

        $this->checkMethodLength();
        $this->checkNestingDepth();
        $this->checkParameterCount();
        $this->detectGodClasses();

        return $this->findings;
    }

    public function getFindings(): array
    {
        return $this->findings;
    }

    /**
     * Check for excessively long methods
     */
    private function checkMethodLength(): void
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

                // Find methods and estimate their length
                preg_match_all(
                    '/(?:public|protected|private)\s+function\s+(\w+)\s*\([^)]*\)/',
                    $content,
                    $methods,
                    PREG_OFFSET_CAPTURE
                );

                foreach ($methods[1] as $i => $match) {
                    $methodName = $match[0];
                    $startOffset = $match[1];

                    // Find the method's opening brace
                    $bracePos = strpos($content, '{', $startOffset);
                    if ($bracePos === false) continue;

                    // Count lines until matching closing brace
                    $depth = 0;
                    $lineCount = 0;
                    $pos = $bracePos;
                    $len = strlen($content);

                    while ($pos < $len) {
                        $char = $content[$pos];
                        if ($char === '{') $depth++;
                        if ($char === '}') {
                            $depth--;
                            if ($depth === 0) break;
                        }
                        if ($char === "\n") $lineCount++;
                        $pos++;
                    }

                    if ($lineCount > self::MAX_METHOD_LINES) {
                        $this->findings[] = new AuditResult(
                            type: 'code_quality',
                            severity: 'medium',
                            category: 'complexity',
                            message: "Method '{$methodName}' has {$lineCount} lines (max recommended: " . self::MAX_METHOD_LINES . ")",
                            details: [
                                'file' => $filePath,
                                'method' => $methodName,
                                'line_count' => $lineCount,
                                'recommendation' => 'Extract sub-routines into private helper methods or service classes',
                            ],
                        );
                    }
                }
            }
        }
    }

    /**
     * Check for excessive nesting depth
     */
    private function checkNestingDepth(): void
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
                $lines = file($filePath);
                $maxDepth = 0;
                $deepestLine = 0;

                // Count indentation level for each line as a proxy for nesting
                foreach ($lines as $lineNum => $line) {
                    if (empty(trim($line))) continue;

                    // Count leading whitespace (tabs or spaces)
                    $leadingSpaces = strlen($line) - strlen(ltrim($line));
                    $depth = intdiv($leadingSpaces, 4); // Assume 4-space indent

                    // Subtract 2 for class + method baseline nesting
                    $adjustedDepth = max(0, $depth - 2);

                    if ($adjustedDepth > $maxDepth) {
                        $maxDepth = $adjustedDepth;
                        $deepestLine = $lineNum + 1;
                    }
                }

                if ($maxDepth > self::MAX_NESTING_DEPTH) {
                    $this->findings[] = new AuditResult(
                        type: 'code_quality',
                        severity: 'medium',
                        category: 'complexity',
                        message: "Maximum nesting depth of {$maxDepth} detected at line {$deepestLine} (max recommended: " . self::MAX_NESTING_DEPTH . ")",
                        details: [
                            'file' => $filePath,
                            'max_depth' => $maxDepth,
                            'line' => $deepestLine,
                            'recommendation' => 'Use early returns, extract methods, or apply guard clauses to reduce nesting',
                        ],
                    );
                }
            }
        }
    }

    /**
     * Check for methods with too many parameters
     */
    private function checkParameterCount(): void
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

                // Find method signatures with parameters
                preg_match_all(
                    '/(?:public|protected|private)\s+function\s+(\w+)\s*\(([^)]*)\)/',
                    $content,
                    $methods
                );

                foreach ($methods[1] as $i => $methodName) {
                    $params = $methods[2][$i];
                    if (empty(trim($params))) continue;

                    $paramCount = count(array_filter(
                        explode(',', $params),
                        fn($p) => !empty(trim($p))
                    ));

                    if ($paramCount > self::MAX_PARAMETERS) {
                        $this->findings[] = new AuditResult(
                            type: 'code_quality',
                            severity: 'low',
                            category: 'complexity',
                            message: "Method '{$methodName}' has {$paramCount} parameters (max recommended: " . self::MAX_PARAMETERS . ")",
                            details: [
                                'file' => $filePath,
                                'method' => $methodName,
                                'parameter_count' => $paramCount,
                                'recommendation' => 'Use a DTO, Request object, or configuration array to reduce parameter count',
                            ],
                        );
                    }
                }
            }
        }
    }

    /**
     * Detect "God classes" with too many dependencies
     */
    private function detectGodClasses(): void
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

                // Count 'use' imports (dependency count indicator)
                $importCount = preg_match_all('/^use\s+[\w\\\\]+;$/m', $content);

                if ($importCount > 15) {
                    $className = pathinfo($file->getFilename(), PATHINFO_FILENAME);
                    $this->findings[] = new AuditResult(
                        type: 'code_quality',
                        severity: 'medium',
                        category: 'complexity',
                        message: "Class '{$className}' has {$importCount} imports — possible God class",
                        details: [
                            'file' => $filePath,
                            'import_count' => $importCount,
                            'recommendation' => 'Consider splitting into smaller, focused classes using Single Responsibility Principle',
                        ],
                    );
                }
            }
        }
    }
}
