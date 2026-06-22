<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\ServiceReport;
use App\Models\Vehicle;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class ServiceReportController extends Controller
{
    public function index()
    {
        $reports = ServiceReport::with('vehicle:id,plate_number,type,status')
            ->where('driver_id', Auth::id())
            ->latest('timestamp')
            ->limit(30)
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
     * Backward-compatible endpoint.
     *
     * App lama mengirim "laporan service" lengkap dengan kuitansi.
     * App baru sebaiknya memakai:
     * - submitVehicleDamageReport untuk laporan kendaraan rusak.
     * - completeServiceReport untuk service selesai.
     */
    public function submitServiceReport(Request $request)
    {
        return $this->storeDamageReport($request, allowCompletionPayload: true);
    }

    public function submitVehicleDamageReport(Request $request)
    {
        return $this->storeDamageReport($request, allowCompletionPayload: false);
    }

    public function completeServiceReport(Request $request, ServiceReport $serviceReport)
    {
        if ($serviceReport->driver_id !== Auth::id()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Laporan kendaraan tidak ditemukan.',
            ], 404);
        }

        if (!in_array($serviceReport->status, [
            ServiceReport::STATUS_WAITING_COMPLETION,
            ServiceReport::STATUS_PENDING,
            ServiceReport::STATUS_PENDING_ADMIN,
        ], true)) {
            return response()->json([
                'status' => 'error',
                'message' => 'Laporan ini sudah diproses dan tidak bisa diubah dari aplikasi driver.',
            ], 422);
        }

        $validated = $request->validate([
            'timestamp' => 'nullable|date_format:Y-m-d H:i:s',
            'service_action' => 'required|string|min:5|max:2000',
            'unit_status_after_service' => 'required|string|max:255',
            'odometer' => 'nullable|integer|min:0',
            'additional_notes' => 'nullable|string|max:2000',
            'after_service_photo' => 'required|image|mimes:jpeg,jpg,png|max:4096',
            'receipt_photo' => 'required|image|mimes:jpeg,jpg,png|max:4096',
            'odometer_photo' => 'nullable|image|mimes:jpeg,jpg,png|max:4096',
            'gps_location' => ['nullable', 'string', 'regex:/^[-]?\d+(\.\d+)?,\s*[-]?\d+(\.\d+)?$/'],
        ]);

        try {
            $afterServicePhotoPath = $this->optimizePhoto($request, 'after_service_photo');
            $receiptPhotoPath = $this->optimizePhoto($request, 'receipt_photo');
            $odometerPhotoPath = $this->optimizePhoto($request, 'odometer_photo');

            $updateData = [
                'status' => ServiceReport::STATUS_PENDING,
                'service_action' => $validated['service_action'],
                'unit_status_after_service' => $validated['unit_status_after_service'],
                'service_completed_at' => $validated['timestamp'] ?? now(),
                'completed_by_driver_id' => Auth::id(),
                'after_service_photo_path' => $afterServicePhotoPath,
                'after_service_photo_taken_at' => now(),
                'receipt_photo_path' => $receiptPhotoPath,
                'receipt_photo_taken_at' => now(),
                'additional_notes' => $validated['additional_notes'] ?? $serviceReport->additional_notes,
            ];

            if (array_key_exists('odometer', $validated)) {
                $updateData['odometer'] = $validated['odometer'];
            }

            if ($odometerPhotoPath) {
                $updateData['odometer_photo_path'] = $odometerPhotoPath;
                $updateData['odometer_photo_taken_at'] = now();
            }

            $serviceReport->update($updateData);
            $serviceReport->loadMissing('vehicle');

            $this->syncVehicleStatusAfterService($serviceReport->vehicle, $serviceReport->unit_status_after_service);

            try {
                app(\App\Services\WhatsAppNotificationService::class)->notifyServiceAdmin($serviceReport->fresh());
            } catch (\Throwable $e) {
                Log::warning("WhatsApp notification failed for service completion {$serviceReport->id}: " . $e->getMessage());
            }

            return response()->json([
                'success' => true,
                'status' => 'success',
                'message' => 'Data service selesai terkirim',
                'data' => $this->serializeForDriver($serviceReport->fresh('vehicle')),
            ]);
        } catch (\Throwable $e) {
            Log::error('CompleteServiceReport Error: ' . $e->getMessage());

            return response()->json([
                'status' => 'error',
                'message' => 'Gagal mengirim data service selesai',
            ], 500);
        }
    }

    private function storeDamageReport(Request $request, bool $allowCompletionPayload)
    {
        $this->mergeActiveAttendanceVehicleWhenMissing($request);

        $validator = Validator::make($request->all(), [
            'plate_number' => 'required|string|max:30',
            'gps_location' => ['nullable', 'string', 'regex:/^[-]?\d+(\.\d+)?,\s*[-]?\d+(\.\d+)?$/'],
            'manual_location' => 'nullable|string|max:255',
            'timestamp' => 'required|date_format:Y-m-d H:i:s',
            'description' => 'required|string|min:10|max:2000',
            'vehicle_condition_photo' => 'required|image|mimes:jpeg,jpg,png|max:4096',
            'receipt_photo' => ($allowCompletionPayload ? 'nullable' : 'prohibited') . '|image|mimes:jpeg,jpg,png|max:4096',
            'after_service_photo' => ($allowCompletionPayload ? 'nullable' : 'prohibited') . '|image|mimes:jpeg,jpg,png|max:4096',
            'service_type' => 'nullable|string|max:255',
            'problem_category' => 'nullable|string|max:255',
            'odometer' => 'nullable|integer|min:0',
            'service_action' => 'nullable|string|max:2000',
            'unit_status_after_service' => 'nullable|string|max:255',
            'additional_notes' => 'nullable|string|max:2000',
            'before_service_photo_source' => 'nullable|in:camera,gallery',
            'odometer_photo' => 'nullable|image|mimes:jpeg,jpg,png|max:4096',
        ]);

        $validator->after(function ($validator) use ($request) {
            if (!$request->filled('gps_location') && !$request->filled('manual_location')) {
                $validator->errors()->add('gps_location', 'GPS atau lokasi manual wajib diisi untuk laporan kendaraan rusak.');
            }
        });

        $validated = $validator->validate();

        try {
            $driver = Auth::user();
            $plateNumber = $this->normalizePlateNumber($validated['plate_number']);
            $vehicle = Vehicle::where('plate_number', $plateNumber)->first();

            if (!$vehicle) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Plat nomor tidak dikenal di sistem. Silakan hubungi admin.',
                ], 404);
            }

            $vehicleConditionPhotoPath = $this->optimizePhoto($request, 'vehicle_condition_photo');
            $receiptPhotoPath = $this->optimizePhoto($request, 'receipt_photo');
            $afterServicePhotoPath = $this->optimizePhoto($request, 'after_service_photo');
            $odometerPhotoPath = $this->optimizePhoto($request, 'odometer_photo');
            $hasCompletionPayload = $allowCompletionPayload && (
                $receiptPhotoPath ||
                $afterServicePhotoPath ||
                !empty($validated['service_action']) ||
                !empty($validated['unit_status_after_service'])
            );

            $serviceReport = ServiceReport::create([
                'ticket_number' => $this->generateTicketNumber(),
                'driver_id' => $driver->id,
                'vehicle_id' => $vehicle->id,
                'customer_id' => $vehicle->project?->customer_id,
                'timestamp' => $validated['timestamp'],
                'gps_location' => $validated['gps_location'] ?? $validated['manual_location'],
                'location_source' => !empty($validated['gps_location']) ? 'gps' : 'manual',
                'description' => $validated['description'],
                'report_source' => $hasCompletionPayload ? 'driver_service_completion' : 'driver_damage',
                'service_type' => $validated['service_type'] ?? null,
                'problem_category' => $validated['problem_category'] ?? null,
                'odometer' => $validated['odometer'] ?? null,
                'service_action' => $validated['service_action'] ?? null,
                'unit_status_after_service' => $validated['unit_status_after_service'] ?? null,
                'service_completed_at' => $hasCompletionPayload ? now() : null,
                'completed_by_driver_id' => $hasCompletionPayload ? $driver->id : null,
                'additional_notes' => $validated['additional_notes'] ?? null,
                'before_service_photo_source' => $validated['before_service_photo_source'] ?? null,
                'before_service_photo_uploaded_at' => now(),
                'vehicle_condition_photo_path' => $vehicleConditionPhotoPath,
                'after_service_photo_path' => $afterServicePhotoPath,
                'after_service_photo_taken_at' => $afterServicePhotoPath ? now() : null,
                'odometer_photo_path' => $odometerPhotoPath,
                'odometer_photo_taken_at' => $odometerPhotoPath ? now() : null,
                'receipt_photo_path' => $receiptPhotoPath,
                'receipt_photo_taken_at' => $receiptPhotoPath ? now() : null,
                'status' => $hasCompletionPayload
                    ? ServiceReport::STATUS_PENDING
                    : ServiceReport::STATUS_WAITING_COMPLETION,
            ]);

            if ($hasCompletionPayload) {
                $this->syncVehicleStatusAfterService($vehicle, $serviceReport->unit_status_after_service);
            } else {
                $this->markVehicleAsProblem($vehicle);
            }

            try {
                app(\App\Services\WhatsAppNotificationService::class)->notifyServiceAdmin($serviceReport);
            } catch (\Throwable $e) {
                Log::warning("WhatsApp notification failed for service report {$serviceReport->id}: " . $e->getMessage());
            }

            return response()->json([
                'success' => true,
                'status' => 'success',
                'message' => $hasCompletionPayload
                    ? 'Laporan service terkirim'
                    : 'Laporan kendaraan rusak terkirim',
                'data' => [
                    'id' => $serviceReport->id,
                    'ticket_number' => $serviceReport->ticket_number,
                    'status' => $serviceReport->status,
                    'report_source' => $serviceReport->report_source,
                    'next_action' => $serviceReport->status === ServiceReport::STATUS_WAITING_COMPLETION
                        ? 'Isi menu Service Selesai setelah perbaikan selesai.'
                        : 'Menunggu review admin service.',
                ],
            ]);
        } catch (\Throwable $e) {
            Log::error('SubmitServiceReport Error: ' . $e->getMessage());

            return response()->json([
                'status' => 'error',
                'message' => 'Gagal mengirim laporan service',
            ], 500);
        }
    }

    private function mergeActiveAttendanceVehicleWhenMissing(Request $request): void
    {
        if ($request->filled('plate_number')) {
            $request->merge([
                'plate_number' => $this->normalizePlateNumber($request->input('plate_number')),
            ]);

            return;
        }

        $activeAttendance = Attendance::with('vehicle')
            ->where('driver_id', Auth::id())
            ->whereNull('time_out')
            ->latest('time_in')
            ->first();

        if ($activeAttendance?->vehicle?->plate_number) {
            $request->merge([
                'plate_number' => $activeAttendance->vehicle->plate_number,
            ]);
        }
    }

    private function optimizePhoto(Request $request, string $key): ?string
    {
        if (!$request->hasFile($key)) {
            return null;
        }

        return app(\App\Services\ImageProcessingService::class)->optimize($request->file($key));
    }

    /**
     * Generate unique ticket number LS-YYYY-XXXXXX.
     */
    private function generateTicketNumber(): string
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

        return 'LS-' . $year . '-' . str_pad((string) $nextId, 6, '0', STR_PAD_LEFT);
    }

    private function normalizePlateNumber(string $plateNumber): string
    {
        return strtoupper(preg_replace('/\s+/', ' ', trim($plateNumber)));
    }

    private function markVehicleAsProblem(?Vehicle $vehicle): void
    {
        if (!$vehicle) {
            return;
        }

        $currentStatus = strtolower(trim((string) $vehicle->status));

        if (in_array($currentStatus, ['aktif', 'active'], true)) {
            $vehicle->update(['status' => 'Rusak']);
        }
    }

    private function syncVehicleStatusAfterService(?Vehicle $vehicle, ?string $unitStatus): void
    {
        if (!$vehicle || !$unitStatus) {
            return;
        }

        $status = strtolower($unitStatus);

        if (str_contains($status, 'aman') ||
            str_contains($status, 'selesai') ||
            str_contains($status, 'jalan') ||
            str_contains($status, 'aktif') ||
            str_contains($status, 'baik')) {
            $vehicle->update(['status' => 'Aktif']);
            return;
        }

        if (str_contains($status, 'servis') || str_contains($status, 'bengkel')) {
            $vehicle->update(['status' => 'Servis']);
            return;
        }

        $vehicle->update(['status' => 'Rusak']);
    }

    private function serializeForDriver(ServiceReport $report): array
    {
        return [
            'id' => $report->id,
            'ticket_number' => $report->ticket_number,
            'timestamp' => optional($report->timestamp)->toDateTimeString(),
            'service_completed_at' => optional($report->service_completed_at)->toDateTimeString(),
            'description' => $report->description,
            'status' => $report->status,
            'report_source' => $report->report_source,
            'location_source' => $report->location_source,
            'gps_location' => $report->gps_location,
            'service_type' => $report->service_type,
            'problem_category' => $report->problem_category,
            'odometer' => $report->odometer,
            'service_action' => $report->service_action,
            'unit_status_after_service' => $report->unit_status_after_service,
            'additional_notes' => $report->additional_notes,
            'vehicle' => $report->vehicle ? [
                'id' => $report->vehicle->id,
                'plate_number' => $report->vehicle->plate_number,
                'type' => $report->vehicle->type,
                'status' => $report->vehicle->status,
            ] : null,
            'vehicle_condition_photo_path' => $report->vehicle_condition_photo_path,
            'after_service_photo_path' => $report->after_service_photo_path,
            'odometer_photo_path' => $report->odometer_photo_path,
            'receipt_photo_path' => $report->receipt_photo_path,
        ];
    }
}
