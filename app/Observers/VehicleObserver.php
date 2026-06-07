<?php

namespace App\Observers;

use App\Models\Vehicle;
use App\Services\QRCodeService;
use Illuminate\Support\Facades\Log;

class VehicleObserver
{
    protected QRCodeService $qrCodeService;

    public function __construct(QRCodeService $qrCodeService)
    {
        $this->qrCodeService = $qrCodeService;
    }

    /**
     * Handle the Vehicle "created" event.
     */
    public function created(Vehicle $vehicle): void
    {
        try {
            $this->qrCodeService->generateForVehicle($vehicle);
        } catch (\Exception $e) {
            Log::error("VehicleObserver failed to generate QR code for vehicle {$vehicle->id}: " . $e->getMessage());
        }
    }

    /**
     * Handle the Vehicle "deleting" event.
     */
    public function deleting(Vehicle $vehicle): void
    {
        try {
            if ($vehicle->qr_code_path) {
                $this->qrCodeService->deleteQRCode($vehicle->qr_code_path);
            }
        } catch (\Exception $e) {
            Log::error("VehicleObserver failed to delete QR code for vehicle {$vehicle->id}: " . $e->getMessage());
        }
    }
}
