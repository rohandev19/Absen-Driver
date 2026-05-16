<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\Vehicle;
use App\Models\VehicleComponent;
use App\Services\VehicleHealthService;
use App\Services\MaintenanceAlertService;
use Illuminate\Support\Facades\DB;

echo "=== SIMULASI CRITICAL SCENARIO ===\n\n";

// 1. Ambil kendaraan
$vehicle = Vehicle::with('components')->first();
echo "📍 Vehicle: {$vehicle->plate_number}\n";
echo "   Current KM: " . number_format($vehicle->current_km) . " KM\n\n";

// 2. Simulasi: Ubah komponen Engine Oil menjadi hampir overdue
echo "📍 STEP 1: Simulasi Engine Oil Hampir Habis\n";
$engineOil = $vehicle->components()->where('component_name', 'Engine Oil')->first();

echo "   BEFORE:\n";
echo "   - Last replaced: " . number_format($engineOil->last_replacement_km) . " KM\n";
echo "   - Next replacement: " . number_format($engineOil->next_replacement_km) . " KM\n";
echo "   - Status: {$engineOil->status}\n";
echo "   - KM remaining: " . number_format($engineOil->km_remaining) . " KM\n\n";

// Update: Set last replacement jauh ke belakang
$newLastKm = $vehicle->current_km - 4900; // Sisa 100 KM lagi
$engineOil->update([
    'last_replacement_km' => $newLastKm,
]);

$engineOil->fresh();
echo "   AFTER (Simulasi sudah jalan 4900 KM sejak ganti oli):\n";
echo "   - Last replaced: " . number_format($engineOil->last_replacement_km) . " KM\n";
echo "   - Next replacement: " . number_format($engineOil->next_replacement_km) . " KM\n";
echo "   - Status: {$engineOil->status}\n";
echo "   - KM remaining: " . number_format($engineOil->km_remaining) . " KM\n\n";

// 3. Simulasi: Brake Pads sudah overdue
echo "📍 STEP 2: Simulasi Brake Pads Overdue\n";
$brakePads = $vehicle->components()->where('component_name', 'Brake Pads')->first();

echo "   BEFORE:\n";
echo "   - Status: {$brakePads->status}\n";
echo "   - KM remaining: " . number_format($brakePads->km_remaining) . " KM\n\n";

// Set sudah lewat
$brakePads->update([
    'last_replacement_km' => $vehicle->current_km - 31000, // Lewat 1000 KM
]);

$brakePads->fresh();
echo "   AFTER (Simulasi sudah lewat 1000 KM):\n";
echo "   - Status: {$brakePads->status}\n";
echo "   - KM remaining: " . ($brakePads->km_remaining ?? 'OVERDUE') . " KM\n\n";

// 4. Generate alerts
echo "📍 STEP 3: Generate Alerts\n";
$alertService = new MaintenanceAlertService();
$alerts = $alertService->generateAlertsForVehicle($vehicle->fresh());

echo "   Total alerts: " . count($alerts) . "\n\n";
foreach ($alerts as $alert) {
    $icon = match($alert['alert_type']) {
        'overdue' => '🔴',
        'critical' => '🟠',
        'warning' => '🟡',
        default => '⚪'
    };
    echo "   {$icon} [{$alert['alert_type']}]\n";
    echo "      {$alert['message']}\n\n";
}

// 5. Calculate health score
echo "📍 STEP 4: Calculate Health Score\n";
$healthService = new VehicleHealthService();
$report = $healthService->getHealthReport($vehicle->fresh());

$icon = $report['status']['icon'];
echo "   Health Score: {$report['health_score']}/100 {$icon}\n";
echo "   Status: {$report['status']['label']}\n";
echo "   Action: {$report['status']['action']}\n\n";

echo "   Breakdown:\n";
echo "   - Component Health: {$report['breakdown']['component_health']}%\n";
echo "   - Maintenance Compliance: {$report['breakdown']['maintenance_compliance']}%\n";
echo "   - Daily Check Score: {$report['breakdown']['daily_check_score']}%\n";
echo "   - Age Factor: {$report['breakdown']['age_factor']}%\n\n";

// 6. Komponen yang perlu perhatian
echo "📍 STEP 5: Komponen yang Perlu Perhatian\n";
$needsAttention = $vehicle->fresh()->components()->needsMaintenance()->get();
echo "   Total: {$needsAttention->count()} komponen\n\n";

foreach ($needsAttention as $comp) {
    $icon = match($comp->status) {
        'overdue' => '🔴',
        'critical' => '🟠',
        'warning' => '🟡',
        default => '🟢'
    };
    $kmText = $comp->km_remaining > 0 ? "sisa {$comp->km_remaining} KM" : "OVERDUE!";
    echo "   {$icon} {$comp->component_name}\n";
    echo "      Status: {$comp->status}\n";
    echo "      {$kmText}\n";
    echo "      Cost: Rp " . number_format($comp->cost_per_replacement, 0, ',', '.') . "\n\n";
}

// 7. Generate schedules
echo "📍 STEP 6: Auto-Generate Maintenance Schedules\n";
$schedulesCreated = 0;

foreach ($needsAttention as $comp) {
    // Check if schedule already exists
    $exists = DB::table('maintenance_schedules')
        ->where('vehicle_id', $vehicle->id)
        ->where('component_id', $comp->id)
        ->whereIn('status', ['pending', 'scheduled'])
        ->exists();
    
    if (!$exists) {
        $priority = match($comp->status) {
            'overdue' => 'critical',
            'critical' => 'high',
            'warning' => 'medium',
            default => 'low'
        };
        
        $scheduledDate = match($comp->status) {
            'overdue' => now()->addDays(1),
            'critical' => now()->addDays(2),
            'warning' => now()->addDays(7),
            default => now()->addDays(14)
        };
        
        DB::table('maintenance_schedules')->insert([
            'vehicle_id' => $vehicle->id,
            'component_id' => $comp->id,
            'scheduled_date' => $scheduledDate,
            'scheduled_km' => $comp->next_replacement_km,
            'type' => 'preventive',
            'priority' => $priority,
            'status' => 'pending',
            'estimated_cost' => $comp->cost_per_replacement,
            'notes' => "Auto-generated for {$comp->component_name}",
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        
        $schedulesCreated++;
        echo "   ✅ Created schedule for {$comp->component_name}\n";
        echo "      Priority: {$priority}\n";
        echo "      Scheduled: {$scheduledDate->format('Y-m-d')}\n\n";
    }
}

echo "   Total schedules created: {$schedulesCreated}\n\n";

// 8. Summary
echo "📍 STEP 7: Summary\n";
$activeAlerts = DB::table('maintenance_alerts')
    ->where('vehicle_id', $vehicle->id)
    ->where('status', 'active')
    ->count();

$pendingSchedules = DB::table('maintenance_schedules')
    ->where('vehicle_id', $vehicle->id)
    ->where('status', 'pending')
    ->count();

echo "   Vehicle: {$vehicle->plate_number}\n";
echo "   Health Score: {$report['health_score']}/100 {$icon}\n";
echo "   Active Alerts: {$activeAlerts}\n";
echo "   Pending Schedules: {$pendingSchedules}\n";
echo "   Components Needing Attention: {$needsAttention->count()}\n";

echo "\n=== SIMULASI SELESAI ===\n";
