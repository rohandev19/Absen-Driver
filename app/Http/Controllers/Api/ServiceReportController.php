<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
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
    public function index()
    {
        $reports = ServiceReport::with('vehicle')
            ->where('driver_id', Auth::id())
            ->latest('timestamp')
            ->get()
            ->map(fn (ServiceReport $report) => $this->serializeForDriver($report));

        return response()->json([
            'status' => 'success',
            'data' => $reports,
        ]);
    }

    public function show(ServiceReport $serviceReport)
    {
        if ($serviceReport->driver_id !== Auth::id()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Laporan service tidak ditemukan.',
            ], 404);
        }

        $serviceReport->load('vehicle');

        return response()->json([
            'status' => 'success',
            'data' => $this->serializeForDriver($serviceReport),
        ]);
    }

    /**
     * Submit service report from driver mobile app.
     */
    public function submitServiceReport(Request $request)
    {
        $activeAttendance = Attendance::with('vehicle')
            ->where('driver_id', Auth::id())
            ->whereNull('time_out')
            ->latest('time_in')
            ->first();

        if (! $activeAttendance) {
            return response()->json(['status' => 'error', 'message' => 'Tidak ada tugas aktif.'], 404);
        }

        if (! $request->filled('plate_number') && $activeAttendance->vehicle?->plate_number) {
            $request->merge([
                'plate_number' => $activeAttendance->vehicle->plate_number,
            ]);
        }

        $validated = $request->validate([
            'plate_number' => 'required|string',
            'gps_location' => ['required', 'string', 'regex:/^[-]?\d+(\.\d+)?,\s*[-]?\d+(\.\d+)?$/'],
            'timestamp' => 'required|date_format:Y-m-d H:i:s',
            'description' => 'required|string|min:10|max:2000',
            'vehicle_condition_photo' => 'required|image|mimes:jpeg,jpg,png|max:10240',
            'receipt_photo' => 'required|image|mimes:jpeg,jpg,png|max:10240',
            'service_type' => 'nullable|string',
            'problem_category' => 'nullable|string',
            'odometer' => 'nullable|integer',
            'service_action' => 'nullable|string',
            'unit_status_after_service' => 'nullable|string',
            'additional_notes' => 'nullable|string',
            'before_service_photo_source' => 'nullable|in:camera,gallery',
            'after_service_photo' => 'nullable|image|mimes:jpeg,jpg,png|max:10240',
            'odometer_photo' => 'nullable|image|mimes:jpeg,jpg,png|max:10240',
        ]);

        try {
            $driver = Auth::user();

            $vehicle = Vehicle::where('plate_number', strtoupper($validated['plate_number']))->first();
            if (!$vehicle) {
                return response()->json(['status' => 'error', 'message' => 'Plat nomor tidak dikenal di sistem. Silakan hubungi admin.'], 404);
            }

            // Get customer_id from vehicle's project (if exists)
            $customerId = $vehicle->project?->customer_id;

            // Process photos
            $vehicleConditionPhotoPath = app(\App\Services\ImageProcessingService::class)->optimize($request->file('vehicle_condition_photo'));
            $receiptPhotoPath = app(\App\Services\ImageProcessingService::class)->optimize($request->file('receipt_photo'));
            
            $afterServicePhotoPath = null;
            if ($request->hasFile('after_service_photo')) {
                $afterServicePhotoPath = app(\App\Services\ImageProcessingService::class)->optimize($request->file('after_service_photo'));
            }

            $odometerPhotoPath = null;
            if ($request->hasFile('odometer_photo')) {
                $odometerPhotoPath = app(\App\Services\ImageProcessingService::class)->optimize($request->file('odometer_photo'));
            }

            $ticketNumber = $this->generateTicketNumber();

            // Create service report
            $serviceReport = ServiceReport::create([
                'ticket_number' => $ticketNumber,
                'driver_id' => $driver->id,
                'vehicle_id' => $vehicle->id,
                'customer_id' => $customerId,
                'timestamp' => $validated['timestamp'],
                'gps_location' => $validated['gps_location'],
                'description' => $validated['description'],
                'service_type' => $validated['service_type'] ?? null,
                'problem_category' => $validated['problem_category'] ?? null,
                'odometer' => $validated['odometer'] ?? null,
                'service_action' => $validated['service_action'] ?? null,
                'unit_status_after_service' => $validated['unit_status_after_service'] ?? null,
                'additional_notes' => $validated['additional_notes'] ?? null,
                'before_service_photo_source' => $validated['before_service_photo_source'] ?? null,
                'vehicle_condition_photo_path' => $vehicleConditionPhotoPath,
                'after_service_photo_path' => $afterServicePhotoPath,
                'odometer_photo_path' => $odometerPhotoPath,
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
                'success' => true,
                'status' => 'success',
                'message' => 'Laporan service terkirim',
                'data' => [
                    'id' => $serviceReport->id,
                    'ticket_number' => $serviceReport->ticket_number,
                    'status' => $serviceReport->status,
                ],
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
     * Generate unique ticket number LS-YYYY-XXXXXX
     */
    private function generateTicketNumber()
    {
        $year = date('Y');
        $latestReport = ServiceReport::whereYear('created_at', $year)
            ->where('ticket_number', 'like', "LS-{$year}-%")
            ->orderBy('id', 'desc')
            ->first();

        if ($latestReport && preg_match('/LS-' . $year . '-(\d+)/', $latestReport->ticket_number, $matches)) {
            $nextId = intval($matches[1]) + 1;
        } else {
            $nextId = 1;
        }

        return 'LS-' . $year . '-' . str_pad($nextId, 6, '0', STR_PAD_LEFT);
    }

    private function serializeForDriver(ServiceReport $report): array
    {
        return [
            'id' => $report->id,
            'ticket_number' => $report->ticket_number,
            'timestamp' => optional($report->timestamp)->toDateTimeString(),
            'description' => $report->description,
            'status' => $report->status,
            'service_type' => $report->service_type,
            'problem_category' => $report->problem_category,
            'vehicle' => $report->vehicle ? [
                'id' => $report->vehicle->id,
                'plate_number' => $report->vehicle->plate_number,
                'type' => $report->vehicle->type,
            ] : null,
            'vehicle_condition_photo_path' => $report->vehicle_condition_photo_path,
            'receipt_photo_path' => $report->receipt_photo_path,
        ];
    }
}
