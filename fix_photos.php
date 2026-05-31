<?php
require 'vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$dir = storage_path('app/public/service_reports');
if (!is_dir($dir)) {
    mkdir($dir, 0755, true);
}

// Download dummy images using picsum
$carImage = 'https://picsum.photos/800/600?random=1';
$receiptImage = 'https://picsum.photos/800/600?random=2';

$carData = file_get_contents($carImage);
$receiptData = file_get_contents($receiptImage);

if ($carData && $receiptData) {
    file_put_contents($dir . '/dummy.jpg', $carData);
    file_put_contents($dir . '/dummy2.jpg', $receiptData);

    $r = \App\Models\ServiceReport::find(4);
    $r->vehicle_condition_photo_path = 'service_reports/dummy.jpg';
    $r->receipt_photo_path = 'service_reports/dummy2.jpg';
    $r->save();

    echo "Images downloaded and DB updated!\n";
} else {
    echo "Failed to download images.\n";
}
