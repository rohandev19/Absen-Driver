<?php
require 'vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$r = \App\Models\ServiceReport::find(2);
if ($r) {
    $r->status = 'pending_customer';
    $r->save();
    echo "Status fixed!\n";
} else {
    echo "Report not found!\n";
}
