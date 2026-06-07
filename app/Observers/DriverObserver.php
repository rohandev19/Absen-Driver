<?php

namespace App\Observers;

use App\Models\Driver;
use App\Services\QRCodeService;
use Illuminate\Support\Facades\Log;

class DriverObserver
{
    protected QRCodeService $qrCodeService;

    public function __construct(QRCodeService $qrCodeService)
    {
        $this->qrCodeService = $qrCodeService;
    }

    /**
     * Handle the Driver "created" event.
     */
    public function created(Driver $driver): void
    {
        try {
            $this->qrCodeService->generateForDriver($driver);
        } catch (\Exception $e) {
            Log::error("DriverObserver failed to generate QR code for driver {$driver->id}: " . $e->getMessage());
        }
    }

    /**
     * Handle the Driver "deleting" event.
     */
    public function deleting(Driver $driver): void
    {
        try {
            if ($driver->qr_code_path) {
                $this->qrCodeService->deleteQRCode($driver->qr_code_path);
            }
        } catch (\Exception $e) {
            Log::error("DriverObserver failed to delete QR code for driver {$driver->id}: " . $e->getMessage());
        }
    }
}
