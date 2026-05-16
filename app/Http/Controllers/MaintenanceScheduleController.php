<?php

namespace App\Http\Controllers;

use App\Models\Vehicle;
use App\Models\MaintenanceSchedule;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;

class MaintenanceScheduleController extends Controller
{
    /**
     * Get all maintenance schedules
     */
    public function index(Request $request)
    {
        $query = MaintenanceSchedule::with(['vehicle', 'component']);

        // Filter by vehicle
        if ($request->has('vehicle_id')) {
            $query->where('vehicle_id', $request->vehicle_id);
        }

        // Filter by status
        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        // Filter by priority
        if ($request->has('priority')) {
            $query->where('priority', $request->priority);
        }

        // Filter by date range
        if ($request->has('start_date')) {
            $query->where('scheduled_date', '>=', $request->start_date);
        }

        if ($request->has('end_date')) {
            $query->where('scheduled_date', '<=', $request->end_date);
        }

        // Get upcoming or overdue
        if ($request->has('filter')) {
            if ($request->filter === 'upcoming') {
                $query->upcoming($request->input('days', 7));
            } elseif ($request->filter === 'overdue') {
                $query->overdue();
            }
        }

        $schedules = $query->orderBy('scheduled_date')->paginate(20);

        return response()->json([
            'success' => true,
            'data' => $schedules,
        ]);
    }

    /**
     * Store new schedule
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'vehicle_id' => 'required|exists:vehicles,id',
            'component_id' => 'nullable|exists:vehicle_components,id',
            'scheduled_date' => 'required|date',
            'scheduled_km' => 'nullable|integer|min:0',
            'type' => 'required|in:preventive,corrective,predictive',
            'priority' => 'required|in:low,medium,high,critical',
            'estimated_cost' => 'nullable|numeric|min:0',
            'workshop_name' => 'nullable|string|max:255',
            'notes' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        $schedule = MaintenanceSchedule::create($validator->validated());

        return response()->json([
            'success' => true,
            'message' => 'Schedule created successfully',
            'data' => $schedule->load(['vehicle', 'component']),
        ], 201);
    }

    /**
     * Update schedule
     */
    public function update(Request $request, MaintenanceSchedule $schedule)
    {
        $validator = Validator::make($request->all(), [
            'scheduled_date' => 'sometimes|date',
            'scheduled_km' => 'nullable|integer|min:0',
            'type' => 'sometimes|in:preventive,corrective,predictive',
            'priority' => 'sometimes|in:low,medium,high,critical',
            'status' => 'sometimes|in:pending,scheduled,in_progress,completed,cancelled',
            'estimated_cost' => 'nullable|numeric|min:0',
            'actual_cost' => 'nullable|numeric|min:0',
            'workshop_name' => 'nullable|string|max:255',
            'notes' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        $schedule->update($validator->validated());

        return response()->json([
            'success' => true,
            'message' => 'Schedule updated successfully',
            'data' => $schedule->fresh(['vehicle', 'component']),
        ]);
    }

    /**
     * Mark schedule as completed
     */
    public function complete(Request $request, MaintenanceSchedule $schedule)
    {
        $validator = Validator::make($request->all(), [
            'actual_cost' => 'nullable|numeric|min:0',
            'notes' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        if ($request->has('notes')) {
            $schedule->notes = $request->notes;
        }

        $schedule->markAsCompleted(Auth::user(), $request->actual_cost);

        return response()->json([
            'success' => true,
            'message' => 'Schedule marked as completed',
            'data' => $schedule->fresh(['vehicle', 'component', 'completedBy']),
        ]);
    }

    /**
     * Delete schedule
     */
    public function destroy(MaintenanceSchedule $schedule)
    {
        $schedule->delete();

        return response()->json([
            'success' => true,
            'message' => 'Schedule deleted successfully',
        ]);
    }

    /**
     * Get upcoming maintenance dashboard
     */
    public function dashboard()
    {
        $today = now();

        $stats = [
            'overdue' => MaintenanceSchedule::overdue()->count(),
            'today' => MaintenanceSchedule::where('scheduled_date', $today->toDateString())
                ->where('status', '!=', 'completed')
                ->count(),
            'this_week' => MaintenanceSchedule::upcoming(7)->count(),
            'this_month' => MaintenanceSchedule::upcoming(30)->count(),
            'by_priority' => [
                'critical' => MaintenanceSchedule::byPriority('critical')
                    ->where('status', '!=', 'completed')
                    ->count(),
                'high' => MaintenanceSchedule::byPriority('high')
                    ->where('status', '!=', 'completed')
                    ->count(),
                'medium' => MaintenanceSchedule::byPriority('medium')
                    ->where('status', '!=', 'completed')
                    ->count(),
                'low' => MaintenanceSchedule::byPriority('low')
                    ->where('status', '!=', 'completed')
                    ->count(),
            ],
        ];

        $upcomingSchedules = MaintenanceSchedule::with(['vehicle', 'component'])
            ->upcoming(7)
            ->get();

        $overdueSchedules = MaintenanceSchedule::with(['vehicle', 'component'])
            ->overdue()
            ->get();

        return response()->json([
            'success' => true,
            'data' => [
                'stats' => $stats,
                'upcoming' => $upcomingSchedules,
                'overdue' => $overdueSchedules,
            ],
        ]);
    }
}
