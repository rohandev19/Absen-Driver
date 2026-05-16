<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\MaintenanceAlertService;

class GenerateMaintenanceAlerts extends Command
{
    protected $signature = 'maintenance:generate-alerts';
    protected $description = 'Generate maintenance alerts for vehicles needing attention';

    public function __construct(
        private MaintenanceAlertService $alertService
    ) {
        parent::__construct();
    }

    public function handle()
    {
        $this->info('🚨 Generating maintenance alerts...');

        $stats = $this->alertService->generateAlertsForAllVehicles();

        $this->newLine();
        $this->info('📊 Summary:');
        $this->table(
            ['Metric', 'Count'],
            [
                ['Total Vehicles', $stats['total_vehicles']],
                ['Alerts Created', $stats['alerts_created']],
                ['🔴 Overdue', $stats['overdue']],
                ['🟠 Critical', $stats['critical']],
                ['🟡 Warning', $stats['warning']],
            ]
        );

        if ($stats['alerts_created'] > 0) {
            $this->warn("⚠️  {$stats['alerts_created']} new alerts generated!");
        } else {
            $this->info('✅ No new alerts needed.');
        }

        return Command::SUCCESS;
    }
}
