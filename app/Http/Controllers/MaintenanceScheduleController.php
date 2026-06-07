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

    public function complete(Request $request, $scheduleId)
    {
        $schedule = MaintenanceSchedule::findOrFail($scheduleId);

        $validated = $request->validate([
            'actual_cost' => 'nullable|numeric|min:0',
            'notes' => 'nullable|string',
        ]);

        $schedule->update([
            'status' => 'completed',
            'completed_at' => now(),
            'completed_by' => Auth::id(),
            'actual_cost' => $validated['actual_cost'] ?? $schedule->estimated_cost,
            'notes' => $validated['notes'] ?? null,
        ]);

        if ($schedule->component) {
            $schedule->component->update([
                'last_replacement_date' => now(),
                'last_replacement_km' => $schedule->vehicle->computed_km,
            ]);
        }

        return back()->with('success', 'Jadwal pemeliharaan telah diselesaikan.');
    }

    public function export(Request $request)
    {
        $namaFile = 'Jadwal_Maintenance_' . Carbon::now()->format('Ymd_His') . '.xlsx';
        return Excel::download(new MaintenanceSchedulesExport($request), $namaFile);
    }
}
