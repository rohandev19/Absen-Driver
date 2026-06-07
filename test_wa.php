<?php

require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Services\WhatsAppService;

echo "=== TEST WHATSAPP NOTIFICATION ===\n\n";

$waService = new WhatsAppService();
$phone = $argv[1] ?? '085817535645'; // Default ke nomor yang tadi digunakan jika tidak ada argumen

echo "Mengirim ke Nomor: $phone\n\n";

// 1. Admin Notification
echo "1. Testing Admin Notification...\n";
$adminMsg = "🔧 *LAPORAN SERVICE BARU*\n─────────────────────────\nDriver  : Budi Santoso\nPlat    : B 1234 CD\nTanggal : 02-06-2026 14:30\nCustomer: PT Contoh Logistik\nMasalah : Rem blong, ganti oli, dan pengecekan mesin...\n\nMohon segera review:\nhttp://localhost/admin/service/123";
$adminResult = $waService->send($phone, $adminMsg);
echo $adminResult ? "✅ Berhasil!\n" : "❌ Gagal.\n";

// 2. Customer Notification
echo "\n2. Testing Customer Notification...\n";
$customerMsg = "📋 *PERSETUJUAN SERVICE KENDARAAN*\n─────────────────────────\nYth. Bapak/Ibu,\n\nPT Hamada Logistik telah menyelesaikan\nservice untuk unit B 1234 CD.\nMohon kesediaan Bapak/Ibu untuk\nmeninjau dan menyetujui:\n\nhttp://localhost/customer/approve/123\n\nTerima kasih.";
$customerResult = $waService->send($phone, $customerMsg);
echo $customerResult ? "✅ Berhasil!\n" : "❌ Gagal.\n";

// 3. Driver Notification
echo "\n3. Testing Driver Notification (Approval Uang Jalan)...\n";
$driverMsg = "✅ *LAPORAN UANG JALAN DISETUJUI*\n\nTanggal: 02/06/2026\nTotal Biaya: Rp 500.000\nLembur: Rp 50.000\nBonus: Rp 100.000\nGrand Total: Rp 650.000\n\nTerima kasih atas laporan Anda!";
$driverResult = $waService->send($phone, $driverMsg);
echo $driverResult ? "✅ Berhasil!\n" : "❌ Gagal.\n";

echo "\nSelesai.\n";
