<?php
require 'vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$driverId = \App\Models\Driver::first()->id ?? 1;

$v = \App\Models\Vehicle::firstOrCreate(
    ['plate_number' => 'B 7777 ABC'],
    ['driver_id' => $driverId, 'type' => 'Blind Van', 'customer_id' => 1]
);

$r = new \App\Models\ServiceReport();
$r->driver_id = $driverId;
$r->vehicle_id = $v->id;
$r->customer_id = 1;
$r->timestamp = now();
$r->gps_location = '-6.2,106.8';
$r->description = 'Ban depan kiri meledak di jalan tol, butuh penggantian ban serep dan velg.';
$r->vehicle_condition_photo_path = 'service_reports/dummy.jpg';
$r->receipt_photo_path = 'service_reports/dummy2.jpg';
$r->status = 'pending';
$r->save();

echo "Report ID " . $r->id . " Created!\n";
