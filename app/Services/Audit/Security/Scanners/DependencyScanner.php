<?php

namespace App\Services\Audit\Security\Scanners;

use App\Contracts\Scanner;
use App\DataModels\Audit\AuditResult;

/**
 * DependencyScanner
 * 
 * Scans composer dependencies for known vulnerabilities, outdated packages,
 * and deprecated libraries.
 * 
 * @package App\Services\Audit\Security\Scanners
 */
class DependencyScanner implements Scanner
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

        $this->scanVulnerabilities();
        $this->checkOutdatedPackages();
        $this->detectDeprecatedPackages();

        return $this->findings;
    }

    public function getFindings(): array
    {
        return $this->findings;
    }

    /**
     * Scan composer.lock for known vulnerabilities
     */
    private function scanVulnerabilities(): void
    {
        $composerLock = $this->basePath . '/composer.lock';

        if (!is_readable($composerLock)) {
            $this->findings[] = new AuditResult(
                type: 'security',
                severity: 'medium',
                category: 'dependency',
                message: 'composer.lock not found — cannot check for dependency vulnerabilities',
                details: ['recommendation' => 'Run "composer install" to generate composer.lock'],
            );
            return;
        }

        $lockData = json_decode(file_get_contents($composerLock), true);
        if ($lockData === null) {
            return;
        }

        $packages = array_merge(
            $lockData['packages'] ?? [],
            $lockData['packages-dev'] ?? []
        );

        // Check for packages with known security issues (basic check based on version)
        $knownVulnerable = [
            'laravel/framework' => ['min_safe' => '11.0.0', 'message' => 'Older Laravel versions may have unpatched vulnerabilities'],
            'guzzlehttp/guzzle' => ['min_safe' => '7.4.5', 'message' => 'Older Guzzle versions have SSRF vulnerabilities'],
            'symfony/http-kernel' => ['min_safe' => '6.3.0', 'message' => 'Older Symfony HTTP kernel versions may have security patches'],
        ];

        foreach ($packages as $package) {
            $name = $package['name'] ?? '';
            $version = ltrim($package['version'] ?? '', 'v');

            if (isset($knownVulnerable[$name])) {
                $vuln = $knownVulnerable[$name];
                if (version_compare($version, $vuln['min_safe'], '<')) {
                    $this->findings[] = new AuditResult(
                        type: 'security',
                        severity: 'high',
                        category: 'dependency',
                        message: "Potentially vulnerable package: {$name} v{$version}",
                        details: [
                            'package' => $name,
                            'current_version' => $version,
                            'min_safe_version' => $vuln['min_safe'],
                            'issue' => $vuln['message'],
                            'recommendation' => "Update to at least v{$vuln['min_safe']}",
                        ],
                    );
                }
            }
        }
    }

    /**
     * Check for outdated packages by analyzing version constraints
     */
    private function checkOutdatedPackages(): void
    {
        $composerJson = $this->basePath . '/composer.json';

        if (!is_readable($composerJson)) {
            return;
        }

        $composerData = json_decode(file_get_contents($composerJson), true);
        if ($composerData === null) {
            return;
        }

        $require = $composerData['require'] ?? [];

        // Check for very permissive version constraints
        foreach ($require as $package => $constraint) {
            if ($package === 'php') {
                continue;
            }

            // Warn about wildcard constraints
            if ($constraint === '*') {
                $this->findings[] = new AuditResult(
                    type: 'security',
                    severity: 'medium',
                    category: 'dependency',
                    message: "Package '{$package}' has wildcard version constraint (*)",
                    details: [
                        'package' => $package,
                        'constraint' => $constraint,
                        'recommendation' => 'Pin to a specific major version range (e.g., ^1.0)',
                    ],
                );
            }

            // Warn about dev-main/dev-master
            if (str_starts_with($constraint, 'dev-')) {
                $this->findings[] = new AuditResult(
                    type: 'security',
                    severity: 'high',
                    category: 'dependency',
                    message: "Package '{$package}' uses unstable dev branch ({$constraint})",
                    details: [
                        'package' => $package,
                        'constraint' => $constraint,
                        'recommendation' => 'Use a tagged release version instead of dev branch',
                    ],
                );
            }
        }
    }

    /**
     * Detect deprecated packages
     */
    private function detectDeprecatedPackages(): void
    {
        $composerLock = $this->basePath . '/composer.lock';

        if (!is_readable($composerLock)) {
            return;
        }

        $lockData = json_decode(file_get_contents($composerLock), true);
        if ($lockData === null) {
            return;
        }

        $packages = array_merge(
            $lockData['packages'] ?? [],
            $lockData['packages-dev'] ?? []
        );

        foreach ($packages as $package) {
            $name = $package['name'] ?? '';

            // Check for abandoned flag in composer.lock
            if (isset($package['abandoned']) && $package['abandoned']) {
                $replacement = is_string($package['abandoned']) ? $package['abandoned'] : 'none specified';

                $this->findings[] = new AuditResult(
                    type: 'security',
                    severity: 'medium',
                    category: 'dependency',
                    message: "Abandoned package detected: {$name}",
                    details: [
                        'package' => $name,
                        'replacement' => $replacement,
                        'recommendation' => "Replace '{$name}' with '{$replacement}'",
                    ],
                );
            }

            // Check for very old packages (description mentions deprecated)
            $description = strtolower($package['description'] ?? '');
            if (str_contains($description, 'deprecated') || str_contains($description, 'abandoned')) {
                $this->findings[] = new AuditResult(
                    type: 'security',
                    severity: 'low',
                    category: 'dependency',
                    message: "Package '{$name}' description mentions deprecation",
                    details: [
                        'package' => $name,
                        'description' => $package['description'] ?? '',
                        'recommendation' => "Review if '{$name}' should be replaced",
                    ],
                );
            }
        }
    }
}
