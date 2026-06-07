<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Driver;
use App\Services\FcmNotificationService;

class TestFcmNotification extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'test:fcm {driver_id : ID Driver/NIK yang akan dikirimkan notifikasi}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Test send FCM Notification to a specific driver';

    /**
     * Execute the console command.
     */
    public function handle(FcmNotificationService $fcmService)
    {
        $driverId = $this->argument('driver_id');
        $driver = Driver::where('driver_id_nik', $driverId)->first();

        if (!$driver) {
            $this->error("Driver dengan ID {$driverId} tidak ditemukan.");
            return;
        }

        if (empty($driver->fcm_token)) {
            $this->error("Driver {$driver->full_name} belum memiliki fcm_token (Belum login di aplikasi terbaru).");
            return;
        }

        $this->info("Mengirim notifikasi ke {$driver->full_name}...");

        $title = "Test Notifikasi Firebase";
        $body = "Halo {$driver->full_name}! Ini adalah pesan uji coba FCM dari Backend Absen Driver.";
        
        $success = $fcmService->sendToDevice($driver->fcm_token, $title, $body);

        if ($success) {
            $this->info('✅ Notifikasi berhasil dikirim!');
        } else {
            $this->error('❌ Gagal mengirim notifikasi. Cek log laravel untuk detail error.');
        }
    }
}
