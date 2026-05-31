<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ServiceReport;
use App\Models\Vehicle;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Intervention\Image\Drivers\Gd\Driver as GdDriver;
use Intervention\Image\ImageManager;

class ServiceReportController extends Controller
{
    /**
     * Submit service report from driver mobile app.
     */
    public function submitServiceReport(Request $request)
    {
        $validated = $request->validate([
            'plate_number' => 'required|string',
            'gps_location' => ['required', 'string', 'regex:/^[-]?\d+(\.\d+)?,\s*[-]?\d+(\.\d+)?$/'],
            'timestamp' => 'required|date_format:Y-m-d H:i:s',
            'description' => 'required|string|min:10|max:2000',
            'vehicle_condition_photo' => 'required|image|mimes:jpeg,jpg,png|max:5120',
            'receipt_photo' => 'required|image|mimes:jpeg,jpg,png|max:5120',
        ]);

        try {
            $driver = Auth::user();

            // Get or create vehicle
            $vehicle = Vehicle::firstOrCreate(
                ['plate_number' => strtoupper($validated['plate_number'])],
                ['type' => 'Service']
            );

            // Get customer_id from vehicle's project (if exists)
            $customerId = $vehicle->project?->customer_id;

            // Process photos
            $vehicleConditionPhotoPath = $this->optimizedImageProcessing($request->file('vehicle_condition_photo'));
            $receiptPhotoPath = $this->optimizedImageProcessing($request->file('receipt_photo'));

            // Create service report
            $serviceReport = ServiceReport::create([
                'driver_id' => $driver->id,
                'vehicle_id' => $vehicle->id,
                'customer_id' => $customerId,
                'timestamp' => $validated['timestamp'],
                'gps_location' => $validated['gps_location'],
                'description' => $validated['description'],
                'vehicle_condition_photo_path' => $vehicleConditionPhotoPath,
                'receipt_photo_path' => $receiptPhotoPath,
                'status' => ServiceReport::STATUS_PENDING,
            ]);

            // Send WhatsApp notification to service admin
            try {
                app(\App\Services\WhatsAppNotificationService::class)->notifyServiceAdmin($serviceReport);
            } catch (\Exception $e) {
                Log::warning("WhatsApp notification failed for service report {$serviceReport->id}: " . $e->getMessage());
            }

            return response()->json([
                'status' => 'success',
                'message' => 'Laporan service terkirim',
                'id' => $serviceReport->id,
            ]);

        } catch (\Throwable $e) {
            Log::error("SubmitServiceReport Error: " . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'Gagal mengirim laporan service',
            ], 500);
        }
    }

    /**
     * Optimized image processing using Intervention Image.
     */
    private function optimizedImageProcessing($file): string
    {
        $fileName = 'photos/' . Str::uuid() . '.jpg';

        try {
            $manager = new ImageManager(new GdDriver());
            $image = $manager->read($file);
            $image->scaleDown(width: 1200);
            Storage::disk('public')->put($fileName, $image->encodeByMediaType('image/jpeg', 70));
        } catch (\Throwable $e) {
            \Log::error('ServiceReport Image Processing Failed: ' . $e->getMessage());
            // Fallback: simpan file asli jika Intervention Image gagal
            Storage::disk('public')->put($fileName, file_get_contents($file->getRealPath()));
        }

        return $fileName;
    }
}
