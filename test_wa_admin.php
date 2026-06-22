<?php
require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\Http;

$token = config('services.wa.gateway_token', env('WA_GATEWAY_TOKEN'));
$gatewayUrl = config('services.wa.gateway_url', env('WA_GATEWAY_URL'));

$adminPhone = '6285817535645';

$driverName = 'Sopir Uji Coba (Testing)';
$plateNumber = 'B 9999 TEST';
$timestamp = date('d-m-Y H:i');
$customerName = 'PT Pelanggan Testing';
$description = 'Rem blong dan mesin bunyi kasar (Pesan Uji Coba Local)';
$url = url('/admin/service/999');

$message = "🔧 LAPORAN SERVICE BARU (TESTING)\n";
$message .= "─────────────────────────\n";
$message .= "Driver  : {$driverName}\n";
$message .= "Plat    : {$plateNumber}\n";
$message .= "Tanggal : {$timestamp}\n";
$message .= "Customer: {$customerName}\n";
$message .= "Masalah : {$description}\n\n";
$message .= "Mohon segera review:\n{$url}";

echo "Mengirim WA (Driver ke Admin Service) ke: $adminPhone\n";
echo "Token: " . substr($token, 0, 5) . "...\n";

try {
    $response = Http::withHeaders([
        'Authorization' => $token,
    ])->timeout(10)->post($gatewayUrl, [
        'target' => $adminPhone,
        'message' => $message,
    ]);

    echo "Status Code: " . $response->status() . "\n";
    echo "Response Body: " . $response->body() . "\n";
} catch (\Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
