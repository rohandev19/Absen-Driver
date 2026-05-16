<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\VehicleComponent;

class UpdateComponentStatus extends Command
{
    protected $signature = 'maintenance:update-component-status';
    protected $description = 'Update status of all vehicle components based on current KM';

    public function handle()
    {
        $this->info('🔄 Updating component status...');

        $components = VehicleComponent::with('vehicle')->get();
        $updated = 0;

        foreach ($components as $component) {
            $oldStatus = $component->status;
            $component->updateStatus();
            $component->save();

            if ($oldStatus !== $component->status) {
                $updated++;
                $this->line("  {$component->vehicle->plate_number} - {$component->component_name}: {$oldStatus} → {$component->status}");
            }
        }

        $this->newLine();
        $this->info("✅ Updated {$updated} components out of {$components->count()} total.");

        return Command::SUCCESS;
    }
}
