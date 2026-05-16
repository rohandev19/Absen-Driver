<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\Vehicle;
use App\Models\VehicleComponent;
use App\Services\VehicleHealthService;
use App\Services\MaintenanceAlertService;

echo "=== SIMULASI PREVENTIVE MAINTENANCE SYSTEM ===\n\n";

// 1. Ambil kendaraan pertama
$vehicle = Vehicle::with('components')->first();
echo "📍 STEP 1: Pilih Kendaraan\n";
echo "   Vehicle: {$vehicle->plate_number}\n";
echo "   Type: {$vehicle->type}\n";
echo "   Current KM: " . number_format($vehicle->current_km) . " KM\n\n";

// 2. Lihat komponen
echo "📍 STEP 2: Lihat Komponen Kendaraan\n";
$components = $vehicle->components()->take(3)->get();
foreach ($components as $comp) {
    echo "   - {$comp->component_name}\n";
    echo "     Last replaced: " . number_format($comp->last_replacement_km) . " KM\n";
    echo "     Next replacement: " . number_format($comp->next_replacement_km) . " KM\n";
    echo "     Status: {$comp->status}\n";
    echo "     KM remaining: " . number_format($comp->km_remaining) . " KM\n\n";
}

// 3. Simulasi kendaraan sudah jalan 4500 KM
echo "📍 STEP 3: Simulasi Kendaraan Jalan 4500 KM\n";
$oldKm = $vehicle->current_km;
$vehicle->update(['current_km' => $vehicle->current_km + 4500]);
echo "   Old KM: " . number_format($oldKm) . " KM\n";
echo "   New KM: " . number_format($vehicle->current_km) . " KM\n\n";

// 4. Update status komponen
echo "📍 STEP 4: Update Status Komponen\n";
$vehicle->load('components');
foreach ($vehicle->components as $comp) {
    $oldStatus = $comp->status;
    $comp->updateStatus();
    $comp->save();
    
    if ($oldStatus !== $comp->status) {
        echo "   ⚠️  {$comp->component_name}: {$oldStatus} → {$comp->status}\n";
    }
}
echo "   ✅ Status updated\n\n";

// 5. Generate alerts
echo "📍 STEP 5: Generate Alerts\n";
$alertService = new MaintenanceAlertService();
$alerts = $alertService->generateAlertsForVehicle($vehicle);
echo "   Alerts created: " . count($alerts) . "\n";
foreach ($alerts as $alert) {
    echo "   🚨 [{$alert['alert_type']}] {$alert['message']}\n";
}
echo "\n";

// 6. Calculate health score
echo "📍 STEP 6: Calculate Health Score\n";
$healthService = new VehicleHealthService();
$report = $healthService->getHealthReport($vehicle->fresh());
echo "   Health Score: {$report['health_score']}/100\n";
echo "   Status: {$report['status']['label']} {$report['status']['icon']}\n";
echo "   Action: {$report['status']['action']}\n\n";

echo "   Breakdown:\n";
echo "   - Component Health: {$report['breakdown']['component_health']}%\n";
echo "   - Maintenance Compliance: {$report['breakdown']['maintenance_compliance']}%\n";
echo "   - Daily Check Score: {$report['breakdown']['daily_check_score']}%\n";
echo "   - Age Factor: {$report['breakdown']['age_factor']}%\n\n";

// 7. Lihat komponen yang perlu perhatian
echo "📍 STEP 7: Komponen yang Perlu Perhatian\n";
$needsAttention = $vehicle->components()->needsMaintenance()->get();
echo "   Total: {$needsAttention->count()} komponen\n";
foreach ($needsAttention as $comp) {
    echo "   - {$comp->component_name}: {$comp->status} (sisa {$comp->km_remaining} KM)\n";
}

echo "\n=== SIMULASI SELESAI ===\n";
