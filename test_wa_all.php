<?php
require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\Http;

$token = config('services.wa.gateway_token', env('WA_GATEWAY_TOKEN'));
$gatewayUrl = config('services.wa.gateway_url', env('WA_GATEWAY_URL'));

$phone = '6285817535645';

// 1. Pesan Driver ke Admin (Laporan Service Baru)
$driverName = 'Budi (Sopir Test)';
$plateNumber = 'B 1234 TEST';
$timestamp = now()->format('d-m-Y H:i'); 
$customerName = 'PT Pelanggan Uji Coba';
$description = 'Mesin susah nyala (Pesan Uji Coba Local)';
$urlAdmin = url('/admin/service/100');

$msg1 = "🔧 LAPORAN SERVICE BARU\n";
$msg1 .= "─────────────────────────\n";
$msg1 .= "Driver  : {$driverName}\n";
$msg1 .= "Plat    : {$plateNumber}\n";
$msg1 .= "Tanggal : {$timestamp}\n"; // Akan mencetak waktu live
$msg1 .= "Customer: {$customerName}\n";
$msg1 .= "Masalah : {$description}\n\n";
$msg1 .= "Mohon segera review:\n{$urlAdmin}";

// 2. Pesan Admin ke Customer (Persetujuan Service Kendaraan)
$contactPerson = 'Bapak/Ibu Pelanggan';
$urlCustomer = url('/customer/approve/100');

$msg2 = "📋 PERSETUJUAN SERVICE KENDARAAN\n";
$msg2 .= "─────────────────────────\n";
$msg2 .= "Yth. {$contactPerson},\n\n";
$msg2 .= "PT Hamada Logistik telah menyelesaikan\n";
$msg2 .= "service untuk unit {$plateNumber}.\n";
$msg2 .= "Mohon kesediaan Bapak/Ibu untuk\n";
$msg2 .= "meninjau dan menyetujui:\n\n";
$msg2 .= "{$urlCustomer}\n\n";
$msg2 .= "Terima kasih.";

function sendWA($url, $token, $target, $message, $label) {
    echo "Mencoba mengirim [$label] ke $target...\n";
    try {
        $response = Http::withHeaders([
            'Authorization' => $token,
        ])->timeout(10)->post($url, [
            'target' => $target,
            'message' => $message,
        ]);
        echo "Status Code: " . $response->status() . "\n";
        echo "Response: " . $response->body() . "\n\n";
    } catch (\Exception $e) {
        echo "Error: " . $e->getMessage() . "\n\n";
    }
}

echo "=== MEMULAI TEST WA ===\n";
sendWA($gatewayUrl, $token, $phone, $msg1, "Driver -> Admin");
sleep(3); // Jeda agar tidak spam
sendWA($gatewayUrl, $token, $phone, $msg2, "Admin -> Customer");
echo "=== SELESAI ===\n";
