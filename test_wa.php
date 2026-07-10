<?php
require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\Http;

$token = config('services.wa.gateway_token', env('WA_GATEWAY_TOKEN'));
$gatewayUrl = config('services.wa.gateway_url', env('WA_GATEWAY_URL'));

$customerPhone = '6285817535645';
$message = "Halo! Ini adalah pesan uji coba (Test Local) perbaikan notifikasi WhatsApp Gateway PT Hamada Global Jaya. Jika Anda menerima pesan ini, berarti perbaikan telah berhasil.\n\nTerima kasih!";

echo "Mengirim WA ke: $customerPhone\n";
echo "Token: " . substr($token, 0, 5) . "...\n";

try {
    $response = Http::withHeaders([
        'Authorization' => $token,
    ])->timeout(10)->post($gatewayUrl, [
        'target' => $customerPhone,
        'message' => $message,
    ]);

    echo "Status Code: " . $response->status() . "\n";
    echo "Response Body: " . $response->body() . "\n";
} catch (\Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
