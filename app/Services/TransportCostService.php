<?php

namespace App\Services;

use App\Models\TransportCost;
use App\Models\Attendance;
use App\Models\Driver;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class TransportCostService
{
    public function __construct(
        private FuelEfficiencyService $fuelEfficiencyService,
        private OvertimeService $overtimeService,
        private WhatsAppNotificationService $notificationService
    ) {}

    /**
     * Check if driver can create trip entry for today
     */
    public function canCreateTripEntry(Driver $driver, string $date): array
    {
        // Check if attendance with check-out exists
        $attendance = Attendance::where('driver_id', $driver->id)
            ->whereDate('time_in', $date)
            ->whereNotNull('time_out')
            ->first();

        if (!$attendance) {
            return [
                'can_create' => false,
                'reason' => 'no_checkout',
                'message' => 'Anda belum melakukan check-out untuk hari ini',
            ];
        }

        // Check if trip entry already exists
        $existingTrip = TransportCost::where('driver_id', $driver->id)
            ->whereDate('trip_date', $date)
            ->exists();

        if ($existingTrip) {
            return [
                'can_create' => false,
                'reason' => 'already_exists',
                'message' => 'Anda sudah membuat laporan uang jalan untuk hari ini',
            ];
        }

        return [
            'can_create' => true,
            'reason' => null,
            'attendance_id' => $attendance->id,
            'odometer_start' => $attendance->speedo_awal,
            'odometer_end' => $attendance->speedo_akhir,
        ];
    }

    /**
     * Create new trip entry with automatic calculations
     */
    public function createTripEntry(Driver $driver, array $data): TransportCost
    {
        return DB::transaction(function () use ($driver, $data) {
            // Get attendance data
            $attendance = Attendance::where('driver_id', $driver->id)
                ->whereDate('time_in', $data['trip_date'] ?? now())
                ->whereNotNull('time_out')
                ->firstOrFail();

            // Auto-fill odometer from attendance
            $data['odometer_start'] = $attendance->speedo_awal;
            $data['odometer_end'] = $attendance->speedo_akhir;
            $data['attendance_id'] = $attendance->id;
            $data['driver_id'] = $driver->id;
            $data['vehicle_id'] = $attendance->vehicle_id;
            $data['project_id'] = $driver->project_id;
            $data['created_by'] = $driver->id;
            $data['trip_date'] = $data['trip_date'] ?? now()->toDateString();

            // Calculate fuel efficiency
            $fuelData = $this->fuelEfficiencyService->calculate(
                $data['odometer_end'] - $data['odometer_start'],
                $data['gasoline_cost'],
                $data['gasoline_price_per_liter'] ?? null
            );
            $data = array_merge($data, $fuelData);

            // Calculate overtime
            $overtimeData = $this->overtimeService->calculate(
                $data['delivery_start_time'],
                $data['delivery_end_time'],
                $driver->project_id
            );
            $data = array_merge($data, $overtimeData);

            // Set default bonus values (bonus configuration removed)
            $data['bonus_driver'] = 0.00;
            $data['bonus_notes'] = 'Fitur bonus telah dihapus';

            // Create trip entry
            $tripEntry = TransportCost::create($data);

            return $tripEntry;
        });
    }

    /**
     * Approve trip entry
     */
    public function approve(TransportCost $tripEntry, int $approverId): void
    {
        if ($tripEntry->approval_status !== 'pending') {
            throw new \Exception('Trip entry sudah diproses sebelumnya');
        }

        $tripEntry->update([
            'approval_status' => 'approved',
            'approved_by' => $approverId,
            'approved_at' => now(),
        ]);

        $this->notificationService->sendApprovalNotification($tripEntry);
    }

    /**
     * Reject trip entry
     */
    public function reject(TransportCost $tripEntry, int $approverId, string $reason): void
    {
        if ($tripEntry->approval_status !== 'pending') {
            throw new \Exception('Trip entry sudah diproses sebelumnya');
        }

        $tripEntry->update([
            'approval_status' => 'rejected',
            'approved_by' => $approverId,
            'approved_at' => now(),
            'rejection_reason' => $reason,
        ]);

        $this->notificationService->sendRejectionNotification($tripEntry);
    }

    /**
     * Get monthly recap for driver
     */
    public function getMonthlyRecap(int $driverId, int $year, int $month): array
    {
        $trips = TransportCost::forDriver($driverId)
            ->forMonth($year, $month)
            ->approved()
            ->get();

        return [
            'total_trips' => $trips->count(),
            'total_gasoline_cost' => $trips->sum('gasoline_cost'),
            'total_toll_cost' => $trips->sum('toll_cost'),
            'total_parking_cost' => $trips->sum('parking_cost'),
            'total_km_traveled' => $trips->sum('odometer_difference'),
            'total_overtime_payment' => $trips->sum('overtime_payment'),
            'total_bonus_earned' => $trips->sum('bonus_driver'),
            'grand_total' => $trips->sum('total_cost') + $trips->sum('overtime_payment') + $trips->sum('bonus_driver'),
            'average_fuel_efficiency' => $trips->avg('fuel_efficiency_ratio'),
            'trips' => $trips,
        ];
    }
}
