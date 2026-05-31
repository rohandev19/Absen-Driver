<?php

namespace App\Console\Commands;

use App\Services\Audit\AuditHistoryService;
use Illuminate\Console\Command;

/**
 * AuditCleanup Command
 * 
 * Purges old audit reports and history records.
 * 
 * Usage:
 *   php artisan audit:cleanup
 *   php artisan audit:cleanup --days=60
 * 
 * Scheduler:
 *   $schedule->command('audit:cleanup --days=30')->monthly();
 */
class AuditCleanup extends Command
{
    protected $signature = 'audit:cleanup 
        {--days=30 : Number of days to retain audit history}
        {--reports : Also delete old report files from storage}';

    protected $description = 'Cleanup old audit history records and report files';

    public function handle(AuditHistoryService $historyService): int
    {
        $days = (int) $this->option('days');
        $cleanReports = $this->option('reports');

        $this->info('');
        $this->info('🧹 Cleaning up audit data...');
        $this->info("   Retaining last {$days} days");
        $this->info('');

        // Cleanup database records
        $this->components->task("Cleaning audit history records", function () use ($historyService, $days) {
            $deleted = $historyService->cleanup($days);
            $this->line("   Deleted {$deleted} records");
            return true;
        });

        // Cleanup report files
        if ($cleanReports) {
            $this->components->task("Cleaning old report files", function () use ($days) {
                $this->cleanupReportFiles($days);
                return true;
            });
        }

        $this->newLine();
        $this->components->info('✅ Cleanup completed.');

        return self::SUCCESS;
    }

    /**
     * Delete old report files from storage
     * 
     * @param int $days
     * @return void
     */
    private function cleanupReportFiles(int $days): void
    {
        $dir = storage_path('app/audit-reports');
        if (!is_dir($dir)) return;

        $threshold = now()->subDays($days)->timestamp;
        $deleted = 0;

        $iterator = new \DirectoryIterator($dir);
        foreach ($iterator as $file) {
            if ($file->isDot() || $file->isDir()) continue;

            if ($file->getMTime() < $threshold) {
                unlink($file->getPathname());
                $deleted++;
            }
        }

        $this->line("   Deleted {$deleted} report files");
    }
}
