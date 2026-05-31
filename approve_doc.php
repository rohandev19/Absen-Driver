<?php
require 'vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$report = \App\Models\ServiceReport::find(2);
$report->status = 'approved_admin';
$report->approved_by_admin_id = 1;
$report->approved_at_admin = now();
$report->admin_notes = 'Komponen mesin diganti';
$report->save();
app(\App\Services\ServiceReportDocumentService::class)->generateCustomerApprovalDocument($report);
echo "Approved and Document Generated!\n";
