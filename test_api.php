<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Http\Request;
use App\Http\Controllers\VehicleHealthController;
use App\Http\Controllers\MaintenanceAlertController;
use App\Http\Controllers\MaintenanceScheduleController;
use App\Services\VehicleHealthService;
use App\Services\MaintenanceAlertService;

echo "=== TEST API ENDPOINTS ===\n\n";

// 1. Test Vehicle Health API
echo "📍 API 1: GET /api/vehicles/health\n";
echo "   (Fleet Health Summary)\n\n";

$healthController = new VehicleHealthController(new VehicleHealthService());
$request = Request::create('/api/vehicles/health', 'GET');
$response = $healthController->index($request);
$data = json_decode($response->getContent(), true);

echo "   Response:\n";
echo "   - Total Vehicles: {$data['data']['fleet_stats']['total_vehicles']}\n";
echo "   - Average Health: {$data['data']['fleet_stats']['average_health_score']}/100\n";
echo "   - Excellent: {$data['data']['fleet_stats']['by_status']['excellent']}\n";
echo "   - Good: {$data['data']['fleet_stats']['by_status']['good']}\n";
echo "   - Fair: {$data['data']['fleet_stats']['by_status']['fair']}\n";
echo "   - Poor: {$data['data']['fleet_stats']['by_status']['poor']}\n";
echo "   - Critical: {$data['data']['fleet_stats']['by_status']['critical']}\n";
echo "   - Active Alerts: {$data['data']['fleet_stats']['total_active_alerts']}\n\n";

// 2. Test Maintenance Alerts API
echo "📍 API 2: GET /api/maintenance/alerts/summary\n";
echo "   (Alerts Summary)\n\n";

$alertController = new MaintenanceAlertController(new MaintenanceAlertService());
$request = Request::create('/api/maintenance/alerts/summary', 'GET');
$response = $alertController->summary();
$data = json_decode($response->getContent(), true);

echo "   Response:\n";
echo "   - Total Alerts: {$data['data']['total']}\n";
echo "   - 🔴 Overdue: {$data['data']['by_type']['overdue']}\n";
echo "   - 🟠 Critical: {$data['data']['by_type']['critical']}\n";
echo "   - 🟡 Warning: {$data['data']['by_type']['warning']}\n\n";

if (!empty($data['data']['by_vehicle'])) {
    echo "   Alerts by Vehicle:\n";
    foreach (array_slice($data['data']['by_vehicle'], 0, 3) as $vehicle) {
        echo "   - {$vehicle['plate_number']}: {$vehicle['count']} alerts (highest: {$vehicle['highest_priority']})\n";
    }
    echo "\n";
}

// 3. Test Maintenance Dashboard API
echo "📍 API 3: GET /api/maintenance/dashboard\n";
echo "   (Maintenance Dashboard)\n\n";

$scheduleController = new MaintenanceScheduleController();
$request = Request::create('/api/maintenance/dashboard', 'GET');
$response = $scheduleController->dashboard();
$data = json_decode($response->getContent(), true);

echo "   Response:\n";
echo "   - Overdue: {$data['data']['stats']['overdue']}\n";
echo "   - Today: {$data['data']['stats']['today']}\n";
echo "   - This Week: {$data['data']['stats']['this_week']}\n";
echo "   - This Month: {$data['data']['stats']['this_month']}\n\n";

echo "   By Priority:\n";
echo "   - 🔴 Critical: {$data['data']['stats']['by_priority']['critical']}\n";
echo "   - 🟠 High: {$data['data']['stats']['by_priority']['high']}\n";
echo "   - 🟡 Medium: {$data['data']['stats']['by_priority']['medium']}\n";
echo "   - 🟢 Low: {$data['data']['stats']['by_priority']['low']}\n\n";

if (!empty($data['data']['upcoming'])) {
    echo "   Upcoming Schedules:\n";
    foreach (array_slice($data['data']['upcoming'], 0, 3) as $schedule) {
        $componentName = isset($schedule['component']['component_name']) ? $schedule['component']['component_name'] : 'General';
        echo "   - {$schedule['scheduled_date']}: {$schedule['vehicle']['plate_number']} - {$componentName}\n";
    }
    echo "\n";
}

if (!empty($data['data']['overdue'])) {
    echo "   Overdue Schedules:\n";
    foreach (array_slice($data['data']['overdue'], 0, 3) as $schedule) {
        $componentName = isset($schedule['component']['component_name']) ? $schedule['component']['component_name'] : 'General';
        echo "   - {$schedule['scheduled_date']}: {$schedule['vehicle']['plate_number']} - {$componentName}\n";
    }
    echo "\n";
}

echo "=== TEST API SELESAI ===\n";
