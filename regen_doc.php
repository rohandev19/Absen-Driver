<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$report = \App\Models\ServiceReport::find(1);
if ($report) {
    $path = app(\App\Services\ServiceReportDocumentService::class)->generateCustomerApprovalDocument($report);
    $report->update(['customer_word_path' => $path]);
    echo "Generated: " . $path . "\n";
} else {
    echo "Report ID 1 not found.\n";
}
