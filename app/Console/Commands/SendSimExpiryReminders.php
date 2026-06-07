<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Driver;
use App\Services\FcmNotificationService;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class SendSimExpiryReminders extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'notify:sim-expiry';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send FCM notifications to drivers whose SIM is expiring in 7, 3, or 1 days.';

    /**
     * Execute the console command.
     */
    public function handle(FcmNotificationService $fcmService)
    {
        $today = Carbon::today();
        
        $drivers = Driver::whereNotNull('sim_expiry_date')->get();
        $count = 0;

        foreach ($drivers as $driver) {
            if (empty($driver->fcm_token)) {
                continue;
            }

            $expiryDate = Carbon::parse($driver->sim_expiry_date)->startOfDay();
            $diffDays = $today->diffInDays($expiryDate, false); // false agar bisa negatif kalau sudah lewat

            if (in_array($diffDays, [7, 3, 1])) {
                $title = "Peringatan Masa Berlaku SIM";
                $body = "Halo {$driver->full_name}, SIM {$driver->sim_type} Anda akan kadaluarsa dalam {$diffDays} hari. Segera perpanjang agar tidak terkendala tugas.";
                
                $success = $fcmService->sendToDevice($driver->fcm_token, $title, $body);

                if ($success) {
                    $count++;
                    Log::info("Sent SIM expiry reminder to {$driver->full_name} ({$diffDays} days left)");
                }
            } else if ($diffDays == 0) {
                 $title = "Peringatan Masa Berlaku SIM";
                 $body = "Halo {$driver->full_name}, masa berlaku SIM {$driver->sim_type} Anda habis HARI INI. Harap lapor ke admin.";
                 
                 $success = $fcmService->sendToDevice($driver->fcm_token, $title, $body);
 
                 if ($success) {
                     $count++;
                     Log::info("Sent SIM expiry reminder to {$driver->full_name} (Expires today)");
                 }
            }
        }

        $this->info("Successfully sent SIM expiry reminder to {$count} drivers.");
    }
}
