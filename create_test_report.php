<?php
require 'vendor/autoload.php';

use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Utils;

// 1. Download another real car image
$imageUrl = 'https://images.unsplash.com/photo-1494976388531-d1058494cdd8?auto=format&fit=crop&w=800&q=80';
$tempImage = tempnam(sys_get_temp_dir(), 'car') . '.jpg';
file_put_contents($tempImage, file_get_contents($imageUrl));

// 2. Prepare API Request
$client = new Client(['base_uri' => 'http://127.0.0.1:8000']);

try {
    $response = $client->request('POST', '/api/submit-service-report', [
        'headers' => [
            'Accept' => 'application/json',
        ],
        'multipart' => [
            [
                'name'     => 'driver_id',
                'contents' => '1'
            ],
            [
                'name'     => 'plate_number',
                'contents' => 'B 9999 XYZ'
            ],
            [
                'name'     => 'timestamp',
                'contents' => date('Y-m-d H:i:s')
            ],
            [
                'name'     => 'gps_location',
                'contents' => '-6.200000,106.816666'
            ],
            [
                'name'     => 'description',
                'contents' => 'Test dari Agent: Kaca spion patah terserempet di jalan tol.'
            ],
            [
                'name'     => 'vehicle_condition_photo',
                'contents' => Utils::tryFopen($tempImage, 'r'),
                'filename' => 'kaca_spion.jpg'
            ],
            [
                'name'     => 'receipt_photo',
                'contents' => Utils::tryFopen($tempImage, 'r'),
                'filename' => 'receipt_dummy.jpg'
            ]
        ]
    ]);

    echo "Status Code: " . $response->getStatusCode() . "\n";
    echo "Response: " . $response->getBody() . "\n";
    
    // Set customer_id explicitly using Tinker script (since API doesn't know J&T right away)
    exec('php artisan tinker --execute="$v = \App\Models\Vehicle::where(\'plate_number\', \'B 9999 XYZ\')->first(); if($v){ $v->customer_id = 1; $v->save(); } $r = \App\Models\ServiceReport::latest()->first(); if($r){ $r->customer_id = 1; $r->save(); echo \'Linked to customer 1!\'; }"');

} catch (\Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}

unlink($tempImage);
