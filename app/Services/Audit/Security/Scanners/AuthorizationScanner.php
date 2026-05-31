<?php

namespace App\Services\Audit\Security\Scanners;

use App\Contracts\Scanner;
use App\DataModels\Audit\AuditResult;

/**
 * AuthorizationScanner
 * 
 * Scans for authorization vulnerabilities including missing middleware,
 * unprotected endpoints, and inadequate role-based access control.
 * 
 * @package App\Services\Audit\Security\Scanners
 */
class AuthorizationScanner implements Scanner
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

        $this->scanRouteMiddleware();
        $this->checkRoleBasedAccess();
        $this->detectMissingAuthChecks();
        $this->validateResourceOwnership();

        return $this->findings;
    }

    public function getFindings(): array
    {
        return $this->findings;
    }

    /**
     * Scan route files for missing auth middleware
     */
    private function scanRouteMiddleware(): void
    {
        $routeFiles = [
            $this->basePath . '/routes/web.php',
            $this->basePath . '/routes/api.php',
        ];

        foreach ($routeFiles as $file) {
            if (!is_readable($file)) {
                continue;
            }

            $content = file_get_contents($file);

            // Find routes that are not within any middleware group
            // Look for Route:: calls that are outside middleware blocks
            $lines = explode("\n", $content);
            $inMiddlewareGroup = 0;

            foreach ($lines as $lineNum => $line) {
                $trimmed = trim($line);

                // Track middleware group nesting
                if (preg_match('/middleware\s*\(/', $trimmed)) {
                    $inMiddlewareGroup += substr_count($trimmed, '{');
                }

                if ($inMiddlewareGroup > 0) {
                    $inMiddlewareGroup += substr_count($trimmed, '{');
                    $inMiddlewareGroup -= substr_count($trimmed, '}');
                    continue; // Inside a middleware group, skip
                }

                // Check for unprotected data-modifying routes
                if (preg_match('/Route::(post|put|patch|delete)\s*\(/', $trimmed)) {
                    // Skip if it has inline middleware
                    if (!str_contains($trimmed, 'middleware') && !str_contains($trimmed, 'auth')) {
                        // Skip health check / fallback / login routes
                        if (str_contains($trimmed, 'health') || str_contains($trimmed, 'fallback') || str_contains($trimmed, 'login')) {
                            continue;
                        }

                        $this->findings[] = new AuditResult(
                            type: 'security',
                            severity: 'high',
                            category: 'authorization',
                            message: 'Data-modifying route without auth middleware',
                            details: [
                                'file' => $file,
                                'line' => $lineNum + 1,
                                'route' => $trimmed,
                                'recommendation' => 'Add auth middleware to protect this endpoint',
                            ],
                        );
                    }
                }
            }
        }
    }

    /**
     * Check for proper role-based access control
     */
    private function checkRoleBasedAccess(): void
    {
        $middlewarePath = $this->basePath . '/app/Http/Middleware/EnsureUserRole.php';

        if (!is_readable($middlewarePath)) {
            $this->findings[] = new AuditResult(
                type: 'security',
                severity: 'high',
                category: 'authorization',
                message: 'No role-based middleware found (EnsureUserRole.php missing)',
                details: [
                    'expected_path' => $middlewarePath,
                    'recommendation' => 'Create a role-based authorization middleware',
                ],
            );
            return;
        }

        $content = file_get_contents($middlewarePath);

        // Check if middleware validates role properly
        if (!preg_match('/abort\s*\(\s*403/i', $content) && !preg_match('/response\s*\(\s*.*403/i', $content)) {
            $this->findings[] = new AuditResult(
                type: 'security',
                severity: 'medium',
                category: 'authorization',
                message: 'Role middleware may not return proper 403 response for unauthorized access',
                details: [
                    'file' => $middlewarePath,
                    'recommendation' => 'Ensure unauthorized access returns HTTP 403',
                ],
            );
        }
    }

    /**
     * Detect controller methods that lack authorization checks
     */
    private function detectMissingAuthChecks(): void
    {
        $controllerDir = $this->basePath . '/app/Http/Controllers';

        if (!is_dir($controllerDir)) {
            return;
        }

        $iterator = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($controllerDir, \RecursiveDirectoryIterator::SKIP_DOTS)
        );

        foreach ($iterator as $file) {
            if ($file->getExtension() !== 'php') {
                continue;
            }

            $filePath = $file->getPathname();
            $content = file_get_contents($filePath);

            // Skip base Controller
            if (str_contains($filePath, 'Controller.php') && !str_contains($content, 'extends')) {
                continue;
            }

            // Check if controller has any authorization in constructor or methods
            $hasConstructorMiddleware = preg_match('/\$this->middleware/', $content);
            $hasAuthorize = preg_match('/\$this->authorize/', $content);
            $hasGateCheck = preg_match('/Gate::/', $content);

            // Check for destructive actions without explicit authorization
            if (preg_match_all('/public\s+function\s+(destroy|delete|remove)\s*\(/', $content, $matches)) {
                foreach ($matches[1] as $method) {
                    if (!$hasConstructorMiddleware && !$hasAuthorize && !$hasGateCheck) {
                        $this->findings[] = new AuditResult(
                            type: 'security',
                            severity: 'medium',
                            category: 'authorization',
                            message: "Destructive method '{$method}' in controller lacks explicit authorization check",
                            details: [
                                'file' => $filePath,
                                'method' => $method,
                                'recommendation' => 'Add $this->authorize() or Gate check before destructive operations',
                            ],
                        );
                    }
                }
            }
        }
    }

    /**
     * Validate resource ownership checks in controllers
     */
    private function validateResourceOwnership(): void
    {
        $apiControllerDir = $this->basePath . '/app/Http/Controllers/Api';

        if (!is_dir($apiControllerDir)) {
            return;
        }

        $iterator = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($apiControllerDir, \RecursiveDirectoryIterator::SKIP_DOTS)
        );

        foreach ($iterator as $file) {
            if ($file->getExtension() !== 'php') {
                continue;
            }

            $filePath = $file->getPathname();
            $content = file_get_contents($filePath);

            // Check if controller accesses user-specific data without ownership check
            if (preg_match('/\$request->user\(\)|auth\(\)->user\(\)/', $content)) {
                // Good — controller checks authenticated user
                continue;
            }

            // Check if controller loads models by ID without ownership verification
            if (preg_match('/findOrFail\s*\(\s*\$/', $content) || preg_match('/find\s*\(\s*\$/', $content)) {
                $this->findings[] = new AuditResult(
                    type: 'security',
                    severity: 'medium',
                    category: 'authorization',
                    message: 'API controller loads resources by ID without verifying ownership',
                    details: [
                        'file' => $filePath,
                        'recommendation' => 'Verify that the authenticated user owns the requested resource',
                    ],
                );
            }
        }
    }
}
