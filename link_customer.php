<?php
require 'vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$v = \App\Models\Vehicle::where('plate_number', 'B 1234 CD')->first();
if ($v) {
    $v->customer_id = 1;
    $v->save();
}

$r = \App\Models\ServiceReport::find(2);
if ($r) {
    $r->customer_id = 1;
    $r->save();
}

app(\App\Services\ServiceReportDocumentService::class)->generateCustomerApprovalDocument($r);

echo "Linked to customer 1 and document regenerated!\n";
