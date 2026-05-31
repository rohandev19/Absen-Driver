<?php

namespace App\Services\Audit\Security\Scanners;

use App\Contracts\Scanner;
use App\DataModels\Audit\AuditResult;

/**
 * SensitiveDataScanner
 * 
 * Scans for sensitive data exposure including .env protection,
 * hardcoded secrets, database credential security, encrypted fields,
 * and file upload storage security.
 * 
 * @package App\Services\Audit\Security\Scanners
 */
class SensitiveDataScanner implements Scanner
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

        $this->checkEnvFileProtection();
        $this->detectSecretsInCode();
        $this->checkDatabaseCredentials();
        $this->checkEncryptedFields();
        $this->checkFileUploadSecurity();

        return $this->findings;
    }

    public function getFindings(): array
    {
        return $this->findings;
    }

    /**
     * Check that .env file is protected via .gitignore
     */
    private function checkEnvFileProtection(): void
    {
        $gitignore = $this->basePath . '/.gitignore';

        if (!is_readable($gitignore)) {
            $this->findings[] = new AuditResult(
                type: 'security',
                severity: 'critical',
                category: 'sensitive_data',
                message: 'No .gitignore file found — .env may be committed to version control',
                details: ['recommendation' => 'Create .gitignore with .env entry'],
            );
            return;
        }

        $content = file_get_contents($gitignore);

        if (!preg_match('/^\.env$/m', $content)) {
            $this->findings[] = new AuditResult(
                type: 'security',
                severity: 'critical',
                category: 'sensitive_data',
                message: '.env file is not listed in .gitignore — credentials may be in version control',
                details: [
                    'file' => $gitignore,
                    'recommendation' => 'Add ".env" to .gitignore immediately',
                ],
            );
        }
    }

    /**
     * Detect secrets/tokens/keys in source code
     */
    private function detectSecretsInCode(): void
    {
        $secretPatterns = [
            '/[A-Za-z0-9_]*(?:API_KEY|APIKEY)\s*=\s*[\'"][A-Za-z0-9+\/=]{20,}[\'"]/' => 'API key',
            '/[A-Za-z0-9_]*(?:SECRET|PRIVATE_KEY)\s*=\s*[\'"][A-Za-z0-9+\/=]{20,}[\'"]/' => 'Secret/Private key',
            '/Bearer\s+[A-Za-z0-9\-._~+\/]+=*/' => 'Bearer token',
            '/(?:sk|pk)_(?:live|test)_[A-Za-z0-9]{20,}/' => 'Stripe-like API key',
            '/ghp_[A-Za-z0-9]{36}/' => 'GitHub personal access token',
            '/AIza[0-9A-Za-z\-_]{35}/' => 'Google API key',
        ];

        $dirs = [
            $this->basePath . '/app',
            $this->basePath . '/config',
            $this->basePath . '/resources',
        ];

        foreach ($dirs as $dir) {
            if (!is_dir($dir)) {
                continue;
            }

            $iterator = new \RecursiveIteratorIterator(
                new \RecursiveDirectoryIterator($dir, \RecursiveDirectoryIterator::SKIP_DOTS)
            );

            foreach ($iterator as $file) {
                if (!in_array($file->getExtension(), ['php', 'js', 'json'])) {
                    continue;
                }

                // Skip vendor, node_modules, compiled
                $path = $file->getPathname();
                if (str_contains($path, 'vendor') || str_contains($path, 'node_modules')) {
                    continue;
                }

                // Skip audit system's own files to avoid false positives
                if (str_contains($path, 'Services' . DIRECTORY_SEPARATOR . 'Audit')) {
                    continue;
                }

                $content = file_get_contents($path);

                // Skip if content uses env() — safe pattern
                foreach ($secretPatterns as $pattern => $type) {
                    if (preg_match($pattern, $content, $matches)) {
                        $matchLine = $matches[0];
                        if (str_contains($matchLine, 'env(') || str_contains($matchLine, 'config(')) {
                            continue;
                        }

                        $this->findings[] = new AuditResult(
                            type: 'security',
                            severity: 'critical',
                            category: 'sensitive_data',
                            message: "Potential {$type} found in source code",
                            details: [
                                'file' => $path,
                                'type' => $type,
                                'recommendation' => 'Move secrets to .env and use env() helper',
                            ],
                        );
                    }
                }
            }
        }
    }

    /**
     * Check database credentials are in environment variables
     */
    private function checkDatabaseCredentials(): void
    {
        $dbConfig = $this->basePath . '/config/database.php';

        if (!is_readable($dbConfig)) {
            return;
        }

        $content = file_get_contents($dbConfig);

        // Check for hardcoded database credentials
        $credentialPatterns = [
            "/'host'\s*=>\s*'(?!localhost|127\.0\.0\.1)[^']+[^env]'/" => 'Database host',
            "/'username'\s*=>\s*'(?!root|homestead)[^']+[^env]'/" => 'Database username',
            "/'password'\s*=>\s*'[^']{3,}[^env]'/" => 'Database password',
        ];

        foreach ($credentialPatterns as $pattern => $type) {
            if (preg_match($pattern, $content)) {
                // Double-check it's not using env()
                if (!preg_match("/{$type}.*env\(/i", $content)) {
                    $this->findings[] = new AuditResult(
                        type: 'security',
                        severity: 'high',
                        category: 'sensitive_data',
                        message: "{$type} may be hardcoded in database config",
                        details: [
                            'file' => $dbConfig,
                            'recommendation' => 'Use env() to read database credentials from .env file',
                        ],
                    );
                }
            }
        }
    }

    /**
     * Check if sensitive model fields use encryption
     */
    private function checkEncryptedFields(): void
    {
        $modelsDir = $this->basePath . '/app/Models';

        if (!is_dir($modelsDir)) {
            return;
        }

        $sensitiveFields = ['password', 'token', 'secret', 'api_key', 'credit_card', 'ssn', 'nik', 'ktp'];

        $iterator = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($modelsDir, \RecursiveDirectoryIterator::SKIP_DOTS)
        );

        foreach ($iterator as $file) {
            if ($file->getExtension() !== 'php') {
                continue;
            }

            $filePath = $file->getPathname();
            $content = file_get_contents($filePath);

            // Check if model has $hidden for sensitive fields
            $hasSensitiveField = false;
            foreach ($sensitiveFields as $field) {
                if (preg_match("/['\"]" . preg_quote($field) . "['\"]/", $content)) {
                    $hasSensitiveField = true;

                    // Check if it's in $hidden
                    if (!preg_match('/\$hidden\s*=\s*\[.*' . preg_quote($field) . '/s', $content)) {
                        $this->findings[] = new AuditResult(
                            type: 'security',
                            severity: 'medium',
                            category: 'sensitive_data',
                            message: "Sensitive field '{$field}' may not be in \$hidden array",
                            details: [
                                'file' => $filePath,
                                'field' => $field,
                                'recommendation' => "Add '{$field}' to the model's \$hidden array",
                            ],
                        );
                    }
                }
            }
        }
    }

    /**
     * Check file upload storage security
     */
    private function checkFileUploadSecurity(): void
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

            // Check if files are stored in public directory
            if (preg_match('/->store\s*\(\s*[\'"]public/', $content) ||
                preg_match('/->storeAs\s*\(\s*[\'"]public/', $content)) {
                $this->findings[] = new AuditResult(
                    type: 'security',
                    severity: 'medium',
                    category: 'sensitive_data',
                    message: 'File uploads stored directly in public directory',
                    details: [
                        'file' => $filePath,
                        'recommendation' => 'Store uploads in private storage and serve via authenticated route',
                    ],
                );
            }
        }
    }
}
