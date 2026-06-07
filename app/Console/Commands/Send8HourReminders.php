<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Attendance;
use App\Services\FcmNotificationService;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class Send8HourReminders extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'notify:8-hours';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send FCM notification to drivers who have been working for more than 8 hours.';

    /**
     * Execute the console command.
     */
    public function handle(FcmNotificationService $fcmService)
    {
        $eightHoursAgo = Carbon::now()->subHours(8);

        // Cari absensi yang masuk lebih dari 8 jam lalu, belum keluar, dan belum diingatkan
        $attendances = Attendance::with('driver')
            ->whereNotNull('time_in')
            ->whereNull('time_out')
            ->where('time_in', '<=', $eightHoursAgo)
            ->where('reminded_8_hours', false)
            ->get();

        $count = 0;
        foreach ($attendances as $attendance) {
            $driver = $attendance->driver;
            
            if ($driver && !empty($driver->fcm_token)) {
                $title = "Peringatan Waktu Kerja";
                $body = "Halo {$driver->full_name}, Anda sudah bertugas lebih dari 8 Jam hari ini. Jangan lupa istirahat yang cukup!";
                
                $success = $fcmService->sendToDevice($driver->fcm_token, $title, $body);

                if ($success) {
                    $attendance->reminded_8_hours = true;
                    $attendance->save();
                    $count++;
                    Log::info("Sent 8-hour reminder to driver {$driver->full_name}");
                }
            }
        }

        $this->info("Successfully sent 8-hour reminder to {$count} drivers.");
    }
}
