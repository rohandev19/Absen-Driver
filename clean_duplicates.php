<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

$components = \App\Models\VehicleComponent::all();
$keep = [];
$deleted = 0;
foreach($components as $c) {
    $key = $c->vehicle_id . '_' . $c->component_name;
    if(isset($keep[$key])) {
        $c->delete();
        $deleted++;
    } else {
        $keep[$key] = true;
    }
}
echo "Deleted " . $deleted . " duplicate components.\n";
