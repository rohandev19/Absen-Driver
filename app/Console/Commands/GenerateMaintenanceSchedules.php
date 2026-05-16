<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Vehicle;
use App\Models\MaintenanceSchedule;
use Carbon\Carbon;

class GenerateMaintenanceSchedules extends Command
{
    protected $signature = 'maintenance:generate-schedules {--vehicle_id=}';
    protected $description = 'Generate maintenance schedules based on component status';

    public function handle()
    {
        $this->info('🔧 Generating maintenance schedules...');

        $vehicleId = $this->option('vehicle_id');
        
        $query = Vehicle::with('components');
        
        if ($vehicleId) {
            $query->where('id', $vehicleId);
        }
        
        $vehicles = $query->get();

        $stats = [
            'vehicles_processed' => 0,
            'schedules_created' => 0,
            'schedules_skipped' => 0,
        ];

        foreach ($vehicles as $vehicle) {
            $this->info("Processing vehicle: {$vehicle->plate_number}");
            
            foreach ($vehicle->components as $component) {
                $result = $this->createScheduleIfNeeded($vehicle, $component);
                
                if ($result) {
                    $stats['schedules_created']++;
                    $this->line("  ✅ Created schedule for: {$component->component_name}");
                } else {
                    $stats['schedules_skipped']++;
                }
            }
            
            $stats['vehicles_processed']++;
        }

        $this->newLine();
        $this->info('📊 Summary:');
        $this->table(
            ['Metric', 'Count'],
            [
                ['Vehicles Processed', $stats['vehicles_processed']],
                ['Schedules Created', $stats['schedules_created']],
                ['Schedules Skipped', $stats['schedules_skipped']],
            ]
        );

        return Command::SUCCESS;
    }

    private function createScheduleIfNeeded($vehicle, $component): bool
    {
        // Skip if component doesn't need maintenance
        if (!$component->needsMaintenance()) {
            return false;
        }

        // Check if schedule already exists
        $existingSchedule = MaintenanceSchedule::where('vehicle_id', $vehicle->id)
            ->where('component_id', $component->id)
            ->whereIn('status', ['pending', 'scheduled'])
            ->first();

        if ($existingSchedule) {
            return false; // Schedule already exists
        }

        // Determine priority based on component status
        $priority = match($component->status) {
            'overdue' => 'critical',
            'critical' => 'high',
            'warning' => 'medium',
            default => 'low',
        };

        // Calculate scheduled date
        $scheduledDate = $this->calculateScheduledDate($component);

        // Create schedule
        MaintenanceSchedule::create([
            'vehicle_id' => $vehicle->id,
            'component_id' => $component->id,
            'scheduled_date' => $scheduledDate,
            'scheduled_km' => $component->next_replacement_km,
            'type' => 'preventive',
            'priority' => $priority,
            'status' => 'pending',
            'estimated_cost' => $component->cost_per_replacement,
            'notes' => "Auto-generated schedule for {$component->component_name}",
        ]);

        return true;
    }

    private function calculateScheduledDate($component): Carbon
    {
        $vehicle = $component->vehicle;
        
        // If overdue or critical, schedule ASAP
        if (in_array($component->status, ['overdue', 'critical'])) {
            return Carbon::today()->addDays(2);
        }

        // Calculate based on KM remaining
        $kmRemaining = $component->km_remaining ?? 0;
        
        // Assume average 100 KM per day
        $daysUntilDue = max(1, floor($kmRemaining / 100));
        
        // Schedule 3 days before due date
        $scheduledDate = Carbon::today()->addDays($daysUntilDue - 3);
        
        // Don't schedule more than 30 days out
        if ($scheduledDate->diffInDays(Carbon::today()) > 30) {
            $scheduledDate = Carbon::today()->addDays(30);
        }

        return $scheduledDate;
    }
}
