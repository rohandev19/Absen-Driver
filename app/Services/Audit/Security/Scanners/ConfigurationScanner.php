<?php

namespace App\Services\Audit\Security\Scanners;

use App\Contracts\Scanner;
use App\DataModels\Audit\AuditResult;

/**
 * ConfigurationScanner
 * 
 * Scans application configuration for security issues including
 * debug mode, APP_KEY, database defaults, and security headers.
 * 
 * @package App\Services\Audit\Security\Scanners
 */
class ConfigurationScanner implements Scanner
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

        $this->checkProductionConfig();
        $this->checkAppKey();
        $this->checkDatabaseDefaults();
        $this->checkSecurityHeaders();
        $this->checkErrorReporting();

        return $this->findings;
    }

    public function getFindings(): array
    {
        return $this->findings;
    }

    /**
     * Check production-critical configuration
     */
    private function checkProductionConfig(): void
    {
        $envFile = $this->basePath . '/.env';

        if (!is_readable($envFile)) {
            $this->findings[] = new AuditResult(
                type: 'security',
                severity: 'info',
                category: 'configuration',
                message: '.env file not readable (may not exist in deployment)',
                details: [],
            );
            return;
        }

        $content = file_get_contents($envFile);
        $envVars = $this->parseEnvFile($content);

        // Check APP_DEBUG
        $appDebug = $envVars['APP_DEBUG'] ?? '';
        $appEnv = $envVars['APP_ENV'] ?? 'local';

        if (strtolower($appDebug) === 'true') {
            // Critical only in production; warning in dev (since debug is expected)
            $isProduction = in_array($appEnv, ['production', 'staging', 'prod']);
            $severity = $isProduction ? 'critical' : 'medium';
            $this->findings[] = new AuditResult(
                type: 'security',
                severity: $severity,
                category: 'configuration',
                message: 'APP_DEBUG is enabled — exposes sensitive error details to users',
                details: [
                    'current_value' => 'true',
                    'app_env' => $appEnv,
                    'recommendation' => 'Set APP_DEBUG=false in production',
                ],
            );
        }

        // Check APP_ENV
        if ($appEnv === 'local' || $appEnv === 'development') {
            $this->findings[] = new AuditResult(
                type: 'security',
                severity: 'medium',
                category: 'configuration',
                message: "APP_ENV is set to '{$appEnv}' — should be 'production' in production",
                details: [
                    'current_value' => $appEnv,
                    'recommendation' => "Set APP_ENV=production",
                ],
            );
        }

        // Check LOG_LEVEL
        $logLevel = $envVars['LOG_LEVEL'] ?? 'debug';
        if ($logLevel === 'debug') {
            $this->findings[] = new AuditResult(
                type: 'security',
                severity: 'low',
                category: 'configuration',
                message: 'LOG_LEVEL is set to debug — may log sensitive data in production',
                details: [
                    'current_value' => $logLevel,
                    'recommendation' => "Set LOG_LEVEL=warning or LOG_LEVEL=error in production",
                ],
            );
        }
    }

    /**
     * Check APP_KEY configuration
     */
    private function checkAppKey(): void
    {
        $envFile = $this->basePath . '/.env';

        if (!is_readable($envFile)) {
            return;
        }

        $content = file_get_contents($envFile);
        $envVars = $this->parseEnvFile($content);

        $appKey = $envVars['APP_KEY'] ?? '';

        if (empty($appKey)) {
            $this->findings[] = new AuditResult(
                type: 'security',
                severity: 'critical',
                category: 'configuration',
                message: 'APP_KEY is empty — encryption, sessions, and cookies will not work securely',
                details: [
                    'recommendation' => 'Run "php artisan key:generate" to set APP_KEY',
                ],
            );
        }

        // Check for default/common keys
        $defaultKeys = [
            'base64:aaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaa=',
            'SomeRandomString',
        ];

        if (in_array($appKey, $defaultKeys, true)) {
            $this->findings[] = new AuditResult(
                type: 'security',
                severity: 'critical',
                category: 'configuration',
                message: 'APP_KEY appears to be a default/placeholder value',
                details: [
                    'recommendation' => 'Generate a unique key with "php artisan key:generate"',
                ],
            );
        }
    }

    /**
     * Check for default database credentials
     */
    private function checkDatabaseDefaults(): void
    {
        $envFile = $this->basePath . '/.env';

        if (!is_readable($envFile)) {
            return;
        }

        $content = file_get_contents($envFile);
        $envVars = $this->parseEnvFile($content);

        $dbPassword = $envVars['DB_PASSWORD'] ?? '';
        $dbUsername = $envVars['DB_USERNAME'] ?? 'root';

        if (empty($dbPassword)) {
            $this->findings[] = new AuditResult(
                type: 'security',
                severity: 'high',
                category: 'configuration',
                message: 'Database password is empty',
                details: [
                    'recommendation' => 'Set a strong password for the database user in production',
                ],
            );
        }

        $weakPasswords = ['password', 'root', '123456', 'admin', 'secret'];
        if (in_array(strtolower($dbPassword), $weakPasswords, true)) {
            $this->findings[] = new AuditResult(
                type: 'security',
                severity: 'critical',
                category: 'configuration',
                message: 'Database password is a commonly known weak password',
                details: [
                    'recommendation' => 'Use a strong, unique password for the database',
                ],
            );
        }

        if ($dbUsername === 'root') {
            $this->findings[] = new AuditResult(
                type: 'security',
                severity: 'medium',
                category: 'configuration',
                message: 'Database user is "root" — use a dedicated database user with limited privileges',
                details: [
                    'recommendation' => 'Create a dedicated database user with only necessary permissions',
                ],
            );
        }
    }

    /**
     * Check security headers configuration
     */
    private function checkSecurityHeaders(): void
    {
        // Check if HTTPS is enforced
        $envFile = $this->basePath . '/.env';
        if (is_readable($envFile)) {
            $content = file_get_contents($envFile);
            $envVars = $this->parseEnvFile($content);

            $appUrl = $envVars['APP_URL'] ?? '';
            if (str_starts_with($appUrl, 'http://') && !str_contains($appUrl, 'localhost') && !str_contains($appUrl, '127.0.0.1')) {
                $this->findings[] = new AuditResult(
                    type: 'security',
                    severity: 'high',
                    category: 'configuration',
                    message: 'APP_URL uses HTTP instead of HTTPS',
                    details: [
                        'current_value' => $appUrl,
                        'recommendation' => 'Use HTTPS for APP_URL in production',
                    ],
                );
            }
        }
    }

    /**
     * Check error reporting configuration
     */
    private function checkErrorReporting(): void
    {
        $appConfig = $this->basePath . '/config/app.php';

        if (!is_readable($appConfig)) {
            return;
        }

        $content = file_get_contents($appConfig);

        // Check if error detail level is safe
        if (preg_match("/['\"]debug['\"]\s*=>\s*true/", $content)) {
            $this->findings[] = new AuditResult(
                type: 'security',
                severity: 'high',
                category: 'configuration',
                message: 'Debug mode is hardcoded to true in config/app.php',
                details: [
                    'file' => $appConfig,
                    'recommendation' => "Use env('APP_DEBUG', false) to control debug mode via environment",
                ],
            );
        }
    }

    /**
     * Parse .env file into key-value pairs
     * 
     * @param string $content
     * @return array<string, string>
     */
    private function parseEnvFile(string $content): array
    {
        $vars = [];
        $lines = explode("\n", $content);

        foreach ($lines as $line) {
            $line = trim($line);
            if (empty($line) || str_starts_with($line, '#')) {
                continue;
            }

            $parts = explode('=', $line, 2);
            if (count($parts) === 2) {
                $key = trim($parts[0]);
                $value = trim($parts[1], " \t\n\r\0\x0B\"'");
                $vars[$key] = $value;
            }
        }

        return $vars;
    }
}
