<?php

namespace App\Services\Audit\CodeQuality\Analyzers;

use App\Contracts\Scanner;
use App\DataModels\Audit\AuditResult;

/**
 * DocumentationAnalyzer
 * 
 * Analyzes documentation completeness including PHPDoc blocks,
 * README presence, API documentation, and changelog.
 * 
 * @package App\Services\Audit\CodeQuality\Analyzers
 */
class DocumentationAnalyzer implements Scanner
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

        $this->checkPhpDocBlocks();
        $this->checkProjectDocumentation();
        $this->checkApiDocumentation();

        return $this->findings;
    }

    public function getFindings(): array
    {
        return $this->findings;
    }

    /**
     * Check for missing PHPDoc blocks on public methods
     */
    private function checkPhpDocBlocks(): void
    {
        $dirs = [
            $this->basePath . '/app/Http/Controllers',
            $this->basePath . '/app/Services',
            $this->basePath . '/app/Models',
        ];

        $totalPublicMethods = 0;
        $documentedMethods = 0;
        $undocumentedFiles = [];

        foreach ($dirs as $dir) {
            if (!is_dir($dir)) continue;

            $iterator = new \RecursiveIteratorIterator(
                new \RecursiveDirectoryIterator($dir, \RecursiveDirectoryIterator::SKIP_DOTS)
            );

            foreach ($iterator as $file) {
                if ($file->getExtension() !== 'php') continue;

                $filePath = $file->getPathname();
                $content = file_get_contents($filePath);

                // Find public methods
                preg_match_all(
                    '/(?:\/\*\*[\s\S]*?\*\/\s*)?public\s+function\s+(\w+)\s*\(/',
                    $content,
                    $matches,
                    PREG_OFFSET_CAPTURE
                );

                $filePublic = count($matches[1]);
                $fileDocumented = 0;

                foreach ($matches[0] as $match) {
                    $fullMatch = $match[0];
                    $totalPublicMethods++;

                    if (str_contains($fullMatch, '/**')) {
                        $documentedMethods++;
                        $fileDocumented++;
                    }
                }

                if ($filePublic > 0 && $fileDocumented === 0) {
                    $undocumentedFiles[] = $filePath;
                }
            }
        }

        if ($totalPublicMethods > 0) {
            $docRatio = round(($documentedMethods / $totalPublicMethods) * 100);

            if ($docRatio < 50) {
                $this->findings[] = new AuditResult(
                    type: 'code_quality',
                    severity: 'medium',
                    category: 'documentation',
                    message: "Only {$docRatio}% of public methods have PHPDoc ({$documentedMethods}/{$totalPublicMethods})",
                    details: [
                        'documented' => $documentedMethods,
                        'total' => $totalPublicMethods,
                        'ratio' => $docRatio,
                        'recommendation' => 'Add /** @return */ and @param annotations to public methods',
                    ],
                );
            }
        }

        // Report completely undocumented files (max 5)
        $reportCount = min(count($undocumentedFiles), 5);
        for ($i = 0; $i < $reportCount; $i++) {
            $this->findings[] = new AuditResult(
                type: 'code_quality',
                severity: 'low',
                category: 'documentation',
                message: 'File has no PHPDoc on any public method: ' . basename($undocumentedFiles[$i]),
                details: [
                    'file' => $undocumentedFiles[$i],
                    'recommendation' => 'Add PHPDoc blocks with @param, @return, and description',
                ],
            );
        }
    }

    /**
     * Check for project-level documentation
     */
    private function checkProjectDocumentation(): void
    {
        // README
        $readmeFiles = ['README.md', 'readme.md', 'README.txt', 'README'];
        $hasReadme = false;
        foreach ($readmeFiles as $readme) {
            if (file_exists($this->basePath . '/' . $readme)) {
                $hasReadme = true;

                // Check README quality (is it just a default Laravel readme?)
                $content = file_get_contents($this->basePath . '/' . $readme);
                if (strlen($content) < 200 || str_contains($content, 'Laravel is a web application framework')) {
                    $this->findings[] = new AuditResult(
                        type: 'code_quality',
                        severity: 'low',
                        category: 'documentation',
                        message: 'README appears to be default Laravel readme — customize with project-specific info',
                        details: [
                            'file' => $this->basePath . '/' . $readme,
                            'recommendation' => 'Add project setup instructions, API overview, environment requirements, and deployment guide',
                        ],
                    );
                }
                break;
            }
        }

        if (!$hasReadme) {
            $this->findings[] = new AuditResult(
                type: 'code_quality',
                severity: 'medium',
                category: 'documentation',
                message: 'No README file found in project root',
                details: [
                    'recommendation' => 'Add README.md with project description, setup instructions, and API documentation',
                ],
            );
        }

        // CHANGELOG
        if (!file_exists($this->basePath . '/CHANGELOG.md')) {
            $this->findings[] = new AuditResult(
                type: 'code_quality',
                severity: 'info',
                category: 'documentation',
                message: 'No CHANGELOG.md found — consider maintaining a changelog for version tracking',
                details: [
                    'recommendation' => 'Create CHANGELOG.md following Keep a Changelog format',
                ],
            );
        }
    }

    /**
     * Check for API documentation
     */
    private function checkApiDocumentation(): void
    {
        $apiDocFiles = [
            'docs/api.md',
            'docs/api.yaml',
            'docs/api.json',
            'resources/docs/api.md',
            'swagger.json',
            'swagger.yaml',
            'openapi.json',
            'openapi.yaml',
        ];

        $hasApiDocs = false;
        foreach ($apiDocFiles as $file) {
            if (file_exists($this->basePath . '/' . $file)) {
                $hasApiDocs = true;
                break;
            }
        }

        // Also check for Scribe or L5-Swagger
        $composerJson = $this->basePath . '/composer.json';
        if (is_readable($composerJson)) {
            $composer = file_get_contents($composerJson);
            if (str_contains($composer, 'knuckleswtf/scribe') || str_contains($composer, 'l5-swagger')) {
                $hasApiDocs = true;
            }
        }

        if (!$hasApiDocs) {
            $this->findings[] = new AuditResult(
                type: 'code_quality',
                severity: 'medium',
                category: 'documentation',
                message: 'No API documentation found (Swagger/OpenAPI, Scribe, or docs/ folder)',
                details: [
                    'recommendation' => 'Add API documentation using Scribe (composer require --dev knuckleswtf/scribe) or write OpenAPI spec',
                ],
            );
        }
    }
}
