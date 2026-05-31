<?php

namespace App\Services\Audit\Security\Scanners;

use App\Contracts\Scanner;
use App\DataModels\Audit\AuditResult;
use Illuminate\Support\Facades\Log;

/**
 * AuthenticationScanner
 * 
 * Scans authentication mechanisms for vulnerabilities including
 * rate limiting, password hashing, token policies, hardcoded credentials,
 * and session configuration.
 * 
 * @package App\Services\Audit\Security\Scanners
 */
class AuthenticationScanner implements Scanner
{
    /**
     * @var array<int, AuditResult>
     */
    private array $findings = [];

    /**
     * @var string Base path of the application
     */
    private string $basePath;

    public function __construct(?string $basePath = null)
    {
        $this->basePath = $basePath ?? base_path();
    }

    /**
     * Run all authentication security checks
     * 
     * @param array<string, mixed> $options
     * @return array<int, AuditResult>
     */
    public function scan(array $options = []): array
    {
        $this->findings = [];

        $this->scanAuthEndpoints();
        $this->checkPasswordHashing();
        $this->checkTokenPolicies();
        $this->detectHardcodedCredentials();
        $this->checkSessionConfig();

        return $this->findings;
    }

    /**
     * @return array<int, AuditResult>
     */
    public function getFindings(): array
    {
        return $this->findings;
    }

    /**
     * Scan authentication endpoints for rate limiting
     */
    private function scanAuthEndpoints(): void
    {
        $apiRoutesFile = $this->basePath . '/routes/api.php';
        $webRoutesFile = $this->basePath . '/routes/web.php';

        foreach (['api' => $apiRoutesFile, 'web' => $webRoutesFile] as $type => $file) {
            if (!is_readable($file)) {
                $this->findings[] = new AuditResult(
                    type: 'security',
                    severity: 'info',
                    category: 'authentication',
                    message: "Cannot read {$type} routes file",
                    details: ['file' => $file],
                );
                continue;
            }

            $content = file_get_contents($file);

            // Check if login routes have rate limiting
            if (preg_match('/login/i', $content)) {
                if (!preg_match('/throttle.*login|login.*throttle/i', $content)
                    && !preg_match("/Route::.*login.*middleware.*throttle/is", $content)
                    && !preg_match("/throttle.*\n.*login/is", $content)) {
                    
                    // More refined check: see if login is within a throttle group
                    if (!preg_match("/middleware\(.*throttle.*\).*\{[^}]*login/is", $content)) {
                        $this->findings[] = new AuditResult(
                            type: 'security',
                            severity: 'high',
                            category: 'authentication',
                            message: "Login endpoint in {$type} routes may lack rate limiting",
                            details: [
                                'file' => $file,
                                'recommendation' => "Add ->middleware('throttle:5,1') to login routes",
                            ],
                        );
                    }
                }
            }
        }
    }

    /**
     * Check password hashing configuration
     */
    private function checkPasswordHashing(): void
    {
        $hashingConfig = $this->basePath . '/config/hashing.php';

        if (!is_readable($hashingConfig)) {
            // Laravel 12 may not have explicit hashing config
            $this->findings[] = new AuditResult(
                type: 'security',
                severity: 'info',
                category: 'authentication',
                message: 'No explicit hashing configuration file found (using Laravel defaults)',
                details: ['file' => $hashingConfig],
            );
            return;
        }

        $content = file_get_contents($hashingConfig);

        // Check bcrypt rounds
        if (preg_match("/['\"](rounds|cost)['\"]\\s*=>\\s*(\\d+)/", $content, $matches)) {
            $rounds = (int) $matches[2];
            if ($rounds < 10) {
                $this->findings[] = new AuditResult(
                    type: 'security',
                    severity: 'medium',
                    category: 'authentication',
                    message: "Bcrypt cost factor is low ({$rounds}). Recommended: 12+",
                    details: [
                        'file' => $hashingConfig,
                        'current_rounds' => $rounds,
                        'recommended' => 12,
                    ],
                );
            }
        }
    }

    /**
     * Check Sanctum token expiration policies
     */
    private function checkTokenPolicies(): void
    {
        $sanctumConfig = $this->basePath . '/config/sanctum.php';

        if (!is_readable($sanctumConfig)) {
            $this->findings[] = new AuditResult(
                type: 'security',
                severity: 'medium',
                category: 'authentication',
                message: 'Sanctum configuration file not found — token expiration may not be configured',
                details: ['file' => $sanctumConfig],
            );
            return;
        }

        $content = file_get_contents($sanctumConfig);

        // Check expiration
        if (preg_match("/['\"]expiration['\"]\\s*=>\\s*(null|0)/", $content)) {
            $this->findings[] = new AuditResult(
                type: 'security',
                severity: 'high',
                category: 'authentication',
                message: 'Sanctum tokens never expire — tokens should have an expiration time',
                details: [
                    'file' => $sanctumConfig,
                    'recommendation' => "Set 'expiration' to a reasonable value (e.g., 1440 = 24 hours)",
                ],
            );
        }
    }

    /**
     * Detect hardcoded credentials in codebase
     */
    private function detectHardcodedCredentials(): void
    {
        $patterns = [
            '/password\s*=\s*[\'"][^\'"]{3,}[\'"]/' => 'Hardcoded password',
            '/api[_-]?key\s*=\s*[\'"][a-zA-Z0-9]{16,}[\'"]/' => 'Hardcoded API key',
            '/secret\s*=\s*[\'"][a-zA-Z0-9]{16,}[\'"]/' => 'Hardcoded secret',
            '/token\s*=\s*[\'"][a-zA-Z0-9]{20,}[\'"]/' => 'Hardcoded token',
        ];

        $directories = [
            $this->basePath . '/app',
            $this->basePath . '/config',
        ];

        foreach ($directories as $dir) {
            if (!is_dir($dir)) {
                continue;
            }

            $iterator = new \RecursiveIteratorIterator(
                new \RecursiveDirectoryIterator($dir, \RecursiveDirectoryIterator::SKIP_DOTS)
            );

            foreach ($iterator as $file) {
                if ($file->getExtension() !== 'php') {
                    continue;
                }

                $filePath = $file->getPathname();
                $content = file_get_contents($filePath);

                // Skip test files and config that references env()
                if (str_contains($filePath, 'test') || str_contains($filePath, 'Test')) {
                    continue;
                }

                foreach ($patterns as $pattern => $description) {
                    if (preg_match($pattern, $content, $matches)) {
                        // Skip if it's referencing env() or config()
                        $line = $matches[0];
                        if (str_contains($line, 'env(') || str_contains($line, 'config(')) {
                            continue;
                        }

                        $this->findings[] = new AuditResult(
                            type: 'security',
                            severity: 'critical',
                            category: 'authentication',
                            message: "{$description} detected in source code",
                            details: [
                                'file' => $filePath,
                                'pattern' => $pattern,
                                'recommendation' => 'Move credentials to .env file and use env() helper',
                            ],
                        );
                    }
                }
            }
        }
    }

    /**
     * Check session configuration for security
     */
    private function checkSessionConfig(): void
    {
        $sessionConfig = $this->basePath . '/config/session.php';

        if (!is_readable($sessionConfig)) {
            return;
        }

        $content = file_get_contents($sessionConfig);

        // Check secure cookie setting
        if (preg_match("/['\"]secure['\"]\\s*=>\\s*false/", $content)) {
            $this->findings[] = new AuditResult(
                type: 'security',
                severity: 'medium',
                category: 'session_management',
                message: 'Session cookies are not set to secure-only — vulnerable to interception over HTTP',
                details: [
                    'file' => $sessionConfig,
                    'recommendation' => "Set 'secure' => env('SESSION_SECURE_COOKIE', true)",
                ],
            );
        }

        // Check httpOnly
        if (preg_match("/['\"]http_only['\"]\\s*=>\\s*false/", $content)) {
            $this->findings[] = new AuditResult(
                type: 'security',
                severity: 'high',
                category: 'session_management',
                message: 'Session cookies are not httpOnly — vulnerable to XSS cookie theft',
                details: [
                    'file' => $sessionConfig,
                    'recommendation' => "Set 'http_only' => true",
                ],
            );
        }

        // Check same_site
        if (preg_match("/['\"]same_site['\"]\\s*=>\\s*['\"]none['\"]/i", $content)) {
            $this->findings[] = new AuditResult(
                type: 'security',
                severity: 'medium',
                category: 'session_management',
                message: 'Session cookie SameSite is set to "none" — vulnerable to CSRF',
                details: [
                    'file' => $sessionConfig,
                    'recommendation' => "Set 'same_site' => 'lax' or 'strict'",
                ],
            );
        }
    }
}
