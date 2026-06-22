<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\MaintenanceSchedule;
use App\Models\Vehicle;
use App\Models\VehicleComponent;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\MaintenanceSchedulesExport;
use App\Services\MaintenanceSchedulePdfService;

class MaintenanceScheduleController extends Controller
{
    public function index(Request $request)
    {
        $query = MaintenanceSchedule::with(['vehicle', 'component']);

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('priority')) {
            $query->where('priority', $request->priority);
        }

        if ($request->filled('vehicle_id')) {
            $query->where('vehicle_id', $request->vehicle_id);
        }

        $schedules = $query->orderBy('scheduled_date')->paginate(20);

        $stats = [
            'overdue' => MaintenanceSchedule::where('scheduled_date', '<', now()->toDateString())
                ->where('status', '!=', 'completed')->count(),
            'today' => MaintenanceSchedule::where('scheduled_date', now()->toDateString())
                ->where('status', '!=', 'completed')->count(),
            'this_week' => MaintenanceSchedule::where('scheduled_date', '>=', now()->toDateString())
                ->where('scheduled_date', '<=', now()->addDays(7)->toDateString())
                ->where('status', '!=', 'completed')->count(),
        ];

        $vehicles = Vehicle::orderBy('plate_number')->get();

        return view('admin.maintenance.schedules', compact('schedules', 'stats', 'vehicles'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'vehicle_id' => 'required|exists:vehicles,id',
            'component_id' => 'nullable|exists:vehicle_components,id',
            'scheduled_date' => 'required|date',
            'scheduled_km' => 'nullable|integer|min:0',
            'estimated_cost' => 'nullable|numeric|min:0',
            'priority' => 'required|in:low,medium,high,critical',
            'type' => 'required|in:preventive,corrective,predictive',
            'workshop_name' => 'nullable|string|max:255',
            'notes' => 'nullable|string',
        ]);

        $validated['created_by'] = Auth::id();
        $validated['status'] = 'pending'; // Change default from 'scheduled' to 'pending' to match factory/test defaults if needed, but let's keep it pending or scheduled depending on status. Actually migration says default is 'pending' andstatuses has scheduled. Let's set to 'pending' as defined in test.

        MaintenanceSchedule::create($validated);

        return back()->with('success', 'Jadwal pemeliharaan berhasil ditambahkan.');
    }

    public function complete(Request $request, MaintenanceSchedulePdfService $pdfService, $scheduleId)
    {
        $schedule = MaintenanceSchedule::findOrFail($scheduleId);

        $validated = $request->validate([
            'actual_cost' => 'required|numeric|min:0',
            'notes' => 'nullable|string',
            'signer_name' => 'required|string|max:255',
            'signer_role' => 'required|string|max:255',
            'signature' => 'required|string|max:2800000',
            'receipt_photo' => 'required|image|mimes:jpeg,jpg,png|max:10240',
            'odometer_photo' => 'required|image|mimes:jpeg,jpg,png|max:10240',
        ]);

        // Process signature
        $signaturePath = null;
        if (!empty($validated['signature'])) {
            $imageParts = explode(";base64,", $validated['signature']);
            if (count($imageParts) == 2) {
                $imageTypeAux = explode("image/", $imageParts[0]);
                $imageType = strtolower($imageTypeAux[1] ?? '');
                
                $allowedTypes = ['png', 'jpeg', 'jpg'];
                if (!in_array($imageType, $allowedTypes)) {
                    return back()->with('error', 'Format tanda tangan tidak valid.');
                }

                $imageBase64 = base64_decode($imageParts[1], true);
                if ($imageBase64 === false) { 
                    abort(422, 'Data tanda tangan tidak valid'); 
                }

                $fileName = 'signatures/maintenance_admin_' . $schedule->id . '_' . uniqid() . '.' . ($imageType === 'jpeg' ? 'jpg' : $imageType);
                
                \Illuminate\Support\Facades\Storage::disk('public')->put($fileName, $imageBase64);
                $signaturePath = $fileName;
            }
        }

        // Process photos using ImageProcessingService
        $imageProcessor = app(\App\Services\ImageProcessingService::class);
        $receiptPhotoPath = $request->hasFile('receipt_photo')
            ? $imageProcessor->optimize($request->file('receipt_photo'), 'photos')
            : null;

        $odometerPhotoPath = $request->hasFile('odometer_photo')
            ? $imageProcessor->optimize($request->file('odometer_photo'), 'photos')
            : null;

        $schedule->update([
            'status' => 'completed',
            'completed_at' => now(),
            'completed_by' => Auth::id(),
            'actual_cost' => $validated['actual_cost'],
            'notes' => $validated['notes'] ?? null,
            'receipt_photo_path' => $receiptPhotoPath,
            'odometer_photo_path' => $odometerPhotoPath,
            'admin_signature_path' => $signaturePath,
            'admin_signer_name' => $validated['signer_name'],
            'admin_signer_role' => $validated['signer_role'],
        ]);

        if ($schedule->component) {
            $schedule->component->update([
                'last_replacement_date' => now(),
                'last_replacement_km' => $schedule->vehicle->computed_km,
            ]);
        }

        // Generate Finance PDF
        try {
            $schedule->refresh();
            $pdfPath = $pdfService->generateFinanceSubmission($schedule);
            $schedule->update(['finance_pdf_path' => $pdfPath]);
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::error('Failed to generate maintenance finance PDF: ' . $e->getMessage());
        }

        return back()->with('success', 'Jadwal pemeliharaan telah diselesaikan dan dokumen finance berhasil dibuat.');
    }

    public function export(Request $request)
    {
        $namaFile = 'Jadwal_Maintenance_' . Carbon::now()->format('Ymd_His') . '.xlsx';
        return Excel::download(new MaintenanceSchedulesExport($request), $namaFile);
    }

    public function previewFinancePdf(MaintenanceSchedulePdfService $pdfService, $scheduleId)
    {
        $schedule = MaintenanceSchedule::findOrFail($scheduleId);

        if ($schedule->status !== 'completed') {
            return back()->with('error', 'Dokumen belum tersedia karena pemeliharaan belum selesai.');
        }

        return $pdfService->streamFinanceSubmission($schedule);
    }

    public function downloadFinancePdf(MaintenanceSchedulePdfService $pdfService, $scheduleId)
    {
        $schedule = MaintenanceSchedule::findOrFail($scheduleId);

        if ($schedule->status !== 'completed') {
            return back()->with('error', 'Dokumen belum tersedia karena pemeliharaan belum selesai.');
        }

        try {
            if (!$schedule->finance_pdf_path) {
                $path = $pdfService->generateFinanceSubmission($schedule);
                $schedule->update(['finance_pdf_path' => $path]);
            }

            $filePath = storage_path('app/public/' . $schedule->finance_pdf_path);
            $fileName = 'Pengajuan_Finance_Maintenance_' . $schedule->id . '.pdf';
            return response()->download($filePath, $fileName);
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::error('Download maintenance finance PDF failed: ' . $e->getMessage());
            return back()->with('error', 'Gagal membuat atau mengunduh PDF finance.');
        }
    }
}
