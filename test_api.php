<?php

use Illuminate\Support\Facades\Http;
use App\Models\Driver;

require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

// Cari driver pertama
$driver = Driver::first();
if (!$driver) {
    echo "Error: Tidak ada driver di database.\n";
    exit;
}

echo "Menggunakan driver: {$driver->name}\n";

// Buat token sementara
$token = $driver->createToken('test-token')->plainTextToken;

// Buat file dummy sementara berupa gambar valid (1x1 PNG)
$dummyImageBase64 = base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mNkYAAAAAYAAjCB0C8AAAAASUVORK5CYII=');
file_put_contents('dummy1.png', $dummyImageBase64);
file_put_contents('dummy2.png', $dummyImageBase64);

echo "Mengirim request ke API...\n";

$vehicle = \App\Models\Vehicle::first();
if (!$vehicle) {
    echo "Error: Tidak ada kendaraan di database.\n";
    exit;
}

// Simulasikan request
$response = Http::withToken($token)
    ->acceptJson()
    ->attach('vehicle_condition_photo', file_get_contents('dummy1.png'), 'before.png')
    ->attach('after_service_photo', file_get_contents('dummy2.png'), 'after.png')
    ->attach('odometer_photo', file_get_contents('dummy1.png'), 'odometer.png')
    ->attach('receipt_photo', file_get_contents('dummy2.png'), 'receipt.png')
    ->post('http://127.0.0.1:8000/api/submit-service-report', [
        'plate_number' => $vehicle->plate_number,
        'gps_location' => '-6.200000,106.816666',
        'description' => 'Testing aliran data dari Aplikasi ke Admin menggunakan field baru.',
        'timestamp' => date('Y-m-d H:i:s'),
        'odometer' => '155000',
        'service_type' => 'Perbaikan',
        'problem_category' => 'Mesin',
        'service_action' => 'Penggantian oli, filter udara, dan tune up',
        'unit_status_after_service' => 'Aman, Siap Jalan',
        'additional_notes' => 'Tolong cek ulang bulan depan',
        'before_service_photo_source' => 'gallery'
    ]);

echo "\n--- RESPONSE ---\n";
echo "Status: " . $response->status() . "\n";
echo "Body:\n";
echo json_encode(json_decode($response->body()), JSON_PRETTY_PRINT) ?: $response->body();
echo "\n----------------\n";

// Bersihkan file sementara
// Bersihkan file sementara
unlink('dummy1.png');
unlink('dummy2.png');
$driver->tokens()->where('name', 'test-token')->delete();
