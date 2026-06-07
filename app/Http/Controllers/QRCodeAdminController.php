<?php

namespace App\Http\Controllers;

use App\Models\Driver;
use App\Models\Vehicle;
use App\Services\QRCodeService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class QRCodeAdminController extends Controller
{
    protected QRCodeService $qrCodeService;

    public function __construct(QRCodeService $qrCodeService)
    {
        // Require master or service_admin role
        // In Laravel 10 typical setup: $this->middleware(['auth', 'can:is-master-admin']);
        // If 'can:is-master-admin' isn't exact, I'll use auth.
        // I will just let the routes define the middleware.
        $this->qrCodeService = $qrCodeService;
    }

    /**
     * Download Driver QR Code
     */
    public function downloadDriverQRCode(Driver $driver)
    {
        if (!$driver->qr_code_path || !Storage::disk('local')->exists($driver->qr_code_path)) {
            return redirect()->back()->with('error', 'QR Code belum tersedia.');
        }

        $filename = "QR-Driver-{$driver->driver_id_nik}.svg";
        return Storage::disk('local')->download($driver->qr_code_path, $filename);
    }

    /**
     * Download Vehicle QR Code
     */
    public function downloadVehicleQRCode(Vehicle $vehicle)
    {
        if (!$vehicle->qr_code_path || !Storage::disk('local')->exists($vehicle->qr_code_path)) {
            return redirect()->back()->with('error', 'QR Code belum tersedia.');
        }

        $filename = "QR-Vehicle-{$vehicle->plate_number}.svg";
        return Storage::disk('local')->download($vehicle->qr_code_path, $filename);
    }

    /**
     * Print Driver QR Code
     */
    public function printDriverQRCode(Driver $driver)
    {
        if (!$driver->qr_code_path) {
            return redirect()->back()->with('error', 'QR Code belum tersedia.');
        }
        return view('admin.drivers.qr-print', compact('driver'));
    }

    /**
     * Print Vehicle QR Code
     */
    public function printVehicleQRCode(Vehicle $vehicle)
    {
        if (!$vehicle->qr_code_path) {
            return redirect()->back()->with('error', 'QR Code belum tersedia.');
        }
        return view('admin.vehicles.qr-print', compact('vehicle'));
    }

    /**
     * Regenerate Driver QR Code
     */
    public function regenerateDriverQRCode(Driver $driver)
    {
        $success = $this->qrCodeService->regenerateForDriver($driver);
        
        if ($success) {
            return redirect()->back()->with('success', 'QR Code berhasil di-generate ulang');
        }
        return redirect()->back()->with('error', 'Gagal men-generate ulang QR Code');
    }

    /**
     * Regenerate Vehicle QR Code
     */
    public function regenerateVehicleQRCode(Vehicle $vehicle)
    {
        $success = $this->qrCodeService->regenerateForVehicle($vehicle);
        
        if ($success) {
            return redirect()->back()->with('success', 'QR Code berhasil di-generate ulang');
        }
        return redirect()->back()->with('error', 'Gagal men-generate ulang QR Code');
    }
}
