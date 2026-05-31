<?php

namespace App\Services\Audit\Database\Analyzers;

use App\Contracts\Scanner;
use App\DataModels\Audit\AuditResult;

/**
 * ConnectionAnalyzer
 * 
 * Analyzes database connection configuration including pool settings,
 * timeout configuration, multiple connection setup, and production readiness.
 * 
 * @package App\Services\Audit\Database\Analyzers
 */
class ConnectionAnalyzer implements Scanner
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

        $this->checkDatabaseConfig();
        $this->checkEnvDatabaseSettings();
        $this->checkMultipleConnections();

        return $this->findings;
    }

    public function getFindings(): array
    {
        return $this->findings;
    }

    /**
     * Check database.php configuration for production readiness
     */
    private function checkDatabaseConfig(): void
    {
        $configFile = $this->basePath . '/config/database.php';
        if (!is_readable($configFile)) {
            $this->findings[] = new AuditResult(
                type: 'database',
                severity: 'info',
                category: 'configuration',
                message: 'No database.php config file found — using Laravel defaults',
                details: ['file' => $configFile],
            );
            return;
        }

        $content = file_get_contents($configFile);

        // Check for strict mode in MySQL
        if (preg_match('/[\'"]mysql[\'"]\s*=>\s*\[/s', $content)) {
            if (!preg_match('/[\'"]strict[\'"]\s*=>\s*true/', $content)) {
                $this->findings[] = new AuditResult(
                    type: 'database',
                    severity: 'medium',
                    category: 'configuration',
                    message: 'MySQL strict mode is not enabled — may allow invalid data insertion',
                    details: [
                        'file' => $configFile,
                        'recommendation' => "Set 'strict' => true in MySQL connection config for data integrity",
                    ],
                );
            }
        }

        // Check for persistent connections
        if (!preg_match('/[\'"]options[\'"]\s*=>\s*\[/', $content)) {
            $this->findings[] = new AuditResult(
                type: 'database',
                severity: 'low',
                category: 'configuration',
                message: 'No PDO options configured — consider persistent connections for high-traffic apps',
                details: [
                    'file' => $configFile,
                    'recommendation' => "Add PDO::ATTR_PERSISTENT => true for connection reuse (evaluate per deployment)",
                ],
            );
        }

        // Check for query logging config warning
        if (preg_match('/DB::enableQueryLog|DB::listen/', $content)) {
            $this->findings[] = new AuditResult(
                type: 'database',
                severity: 'medium',
                category: 'configuration',
                message: 'Query logging is enabled in database config — may impact performance in production',
                details: [
                    'file' => $configFile,
                    'recommendation' => 'Disable query logging in production or use conditional logging based on environment',
                ],
            );
        }
    }

    /**
     * Check .env file for database configuration issues
     */
    private function checkEnvDatabaseSettings(): void
    {
        $envFile = $this->basePath . '/.env';
        if (!is_readable($envFile)) return;

        $content = file_get_contents($envFile);

        // Check if using SQLite in what looks like a non-local env
        if (preg_match('/DB_CONNECTION\s*=\s*sqlite/i', $content)) {
            if (!preg_match('/APP_ENV\s*=\s*(?:local|testing)/i', $content)) {
                $this->findings[] = new AuditResult(
                    type: 'database',
                    severity: 'high',
                    category: 'configuration',
                    message: 'SQLite database used in non-local environment — not suitable for production',
                    details: [
                        'recommendation' => 'Use MySQL or PostgreSQL for production deployments',
                    ],
                );
            }
        }

        // Check for default database credentials
        if (preg_match('/DB_PASSWORD\s*=\s*$/m', $content) || preg_match('/DB_PASSWORD\s*=\s*password/i', $content)) {
            $this->findings[] = new AuditResult(
                type: 'database',
                severity: 'critical',
                category: 'configuration',
                message: 'Database password is empty or set to default — critical security risk',
                details: [
                    'recommendation' => 'Set a strong, unique database password',
                ],
            );
        }

        // Check for default root user
        if (preg_match('/DB_USERNAME\s*=\s*root/i', $content)) {
            $this->findings[] = new AuditResult(
                type: 'database',
                severity: 'medium',
                category: 'configuration',
                message: 'Database using root user — should use a limited-privilege user',
                details: [
                    'recommendation' => 'Create a dedicated database user with only required permissions',
                ],
            );
        }
    }

    /**
     * Check if read/write splitting or multiple connections are configured
     */
    private function checkMultipleConnections(): void
    {
        $configFile = $this->basePath . '/config/database.php';
        if (!is_readable($configFile)) return;

        $content = file_get_contents($configFile);

        // Check for read/write splitting
        $hasReadWrite = preg_match('/[\'"]read[\'"]\s*=>\s*\[/', $content);

        if (!$hasReadWrite) {
            $this->findings[] = new AuditResult(
                type: 'database',
                severity: 'info',
                category: 'configuration',
                message: 'No read/write database splitting configured — consider for high-traffic applications',
                details: [
                    'recommendation' => "Configure 'read' and 'write' arrays in database connection for load distribution",
                ],
            );
        }
    }
}
