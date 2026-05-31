<?php
require 'vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$r = \App\Models\ServiceReport::find(6);
var_dump($r->admin_signature_path);
var_dump(file_exists(storage_path('app/public/' . $r->admin_signature_path)));
