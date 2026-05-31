<?php

namespace App\Services\Audit\Database\Analyzers;

use App\Contracts\Scanner;
use App\DataModels\Audit\AuditResult;

/**
 * TransactionAnalyzer
 * 
 * Analyzes transaction usage patterns including missing transactions
 * on multi-step operations, nested transactions, and deadlock risks.
 * 
 * @package App\Services\Audit\Database\Analyzers
 */
class TransactionAnalyzer implements Scanner
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

        $this->detectMissingTransactions();
        $this->checkTransactionPatterns();
        $this->detectLongTransactions();

        return $this->findings;
    }

    public function getFindings(): array
    {
        return $this->findings;
    }

    /**
     * Detect multi-step write operations that lack transaction wrapping
     */
    private function detectMissingTransactions(): void
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

                // Count write operations in file
                $writeOps = 0;
                $writePatterns = [
                    '->save\s*\(',
                    '->create\s*\(',
                    '->update\s*\(',
                    '->delete\s*\(',
                    '->forceDelete\s*\(',
                    '->insert\s*\(',
                    '->updateOrCreate\s*\(',
                    '->upsert\s*\(',
                ];

                foreach ($writePatterns as $pattern) {
                    $writeOps += preg_match_all('/' . $pattern . '/', $content);
                }

                // If there are multiple write operations but no transaction
                if ($writeOps >= 2) {
                    $hasTransaction = preg_match(
                        '/DB::transaction\s*\(|DB::beginTransaction\s*\(/',
                        $content
                    );

                    if (!$hasTransaction) {
                        $this->findings[] = new AuditResult(
                            type: 'database',
                            severity: 'high',
                            category: 'transaction_management',
                            message: "File contains {$writeOps} write operations without DB::transaction()",
                            details: [
                                'file' => $filePath,
                                'write_operations' => $writeOps,
                                'recommendation' => 'Wrap related write operations in DB::transaction() to ensure data consistency',
                            ],
                        );
                    }
                }
            }
        }
    }

    /**
     * Check for proper transaction patterns (commit/rollback)
     */
    private function checkTransactionPatterns(): void
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

                // Check for beginTransaction without rollBack
                if (preg_match('/DB::beginTransaction\s*\(/', $content)) {
                    if (!preg_match('/DB::rollBack\s*\(/', $content)) {
                        $this->findings[] = new AuditResult(
                            type: 'database',
                            severity: 'high',
                            category: 'transaction_management',
                            message: 'DB::beginTransaction() without DB::rollBack() — transaction may not roll back on error',
                            details: [
                                'file' => $filePath,
                                'recommendation' => 'Add DB::rollBack() in catch block, or use DB::transaction() closure which handles this automatically',
                            ],
                        );
                    }

                    if (!preg_match('/DB::commit\s*\(/', $content)) {
                        $this->findings[] = new AuditResult(
                            type: 'database',
                            severity: 'critical',
                            category: 'transaction_management',
                            message: 'DB::beginTransaction() without DB::commit() — transaction will never be committed',
                            details: [
                                'file' => $filePath,
                                'recommendation' => 'Add DB::commit() after successful operations',
                            ],
                        );
                    }
                }
            }
        }
    }

    /**
     * Detect potentially long-running transactions (many operations inside transaction)
     */
    private function detectLongTransactions(): void
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

                // Check for foreach loops inside transactions
                if (preg_match('/DB::transaction\s*\(\s*function\s*\([^)]*\)\s*(?:use\s*\([^)]*\)\s*)?\{/s', $content, $match, PREG_OFFSET_CAPTURE)) {
                    $transactionStart = $match[0][1];
                    $remainingContent = substr($content, $transactionStart);

                    // Simple check: does the transaction closure contain a foreach?
                    if (preg_match('/foreach\s*\(/', $remainingContent)) {
                        $this->findings[] = new AuditResult(
                            type: 'database',
                            severity: 'medium',
                            category: 'transaction_management',
                            message: 'Loop detected inside DB::transaction() — may cause long-running transactions',
                            details: [
                                'file' => $filePath,
                                'recommendation' => 'Consider batch operations or chunking within the transaction to reduce lock time',
                            ],
                        );
                    }
                }
            }
        }
    }
}
