<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$s = app(App\Services\MaintenanceAlertService::class);
$c = App\Models\VehicleComponent::where('component_name', 'Oli Mesin')->first();
try {
    $res = ReflectionMethod::createFromMethodName(App\Services\MaintenanceAlertService::class, 'checkComponentAlert')->invoke($s, $c);
    var_dump($res);
} catch (Exception $e) {
    echo $e->getMessage();
}
