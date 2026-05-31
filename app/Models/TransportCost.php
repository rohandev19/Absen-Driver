<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TransportCost extends Model
{
    protected $fillable = [
        'driver_id',
        'vehicle_id',
        'project_id',
        'attendance_id',
        'trip_date',
        'do_number',
        'drop_point_count',
        'delivery_location',
        'odometer_start',
        'odometer_end',
        'gasoline_cost',
        'toll_cost',
        'parking_cost',
        'gasoline_price_per_liter',
        'fuel_consumed',
        'fuel_efficiency_ratio',
        'delivery_start_time',
        'delivery_end_time',
        'actual_delivery_hours',
        'overtime_hours',
        'overtime_rate_per_hour',
        'overtime_payment',
        'bonus_driver',
        'bonus_notes',
        'approval_status',
        'approved_by',
        'approved_at',
        'rejection_reason',
        'submitted_to_finance',
        'submitted_to_finance_at',
        'submitted_to_finance_by',
        'finance_word_path',
        'gasoline_receipt_path',
        'toll_receipt_path',
        'parking_receipt_path',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'trip_date' => 'date',
        'gasoline_cost' => 'decimal:2',
        'toll_cost' => 'decimal:2',
        'parking_cost' => 'decimal:2',
        'gasoline_price_per_liter' => 'decimal:2',
        'fuel_consumed' => 'decimal:2',
        'fuel_efficiency_ratio' => 'decimal:2',
        'delivery_start_time' => 'datetime',
        'delivery_end_time' => 'datetime',
        'actual_delivery_hours' => 'decimal:2',
        'overtime_hours' => 'decimal:2',
        'overtime_rate_per_hour' => 'decimal:2',
        'overtime_payment' => 'decimal:2',
        'bonus_driver' => 'decimal:2',
        'approved_at' => 'datetime',
        'submitted_to_finance' => 'boolean',
        'submitted_to_finance_at' => 'datetime',
    ];

    // Relationships
    public function driver(): BelongsTo
    {
        return $this->belongsTo(Driver::class);
    }

    public function vehicle(): BelongsTo
    {
        return $this->belongsTo(Vehicle::class);
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function attendance(): BelongsTo
    {
        return $this->belongsTo(Attendance::class);
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updater(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public function financeSubmitter(): BelongsTo
    {
        return $this->belongsTo(User::class, 'submitted_to_finance_by');
    }

    // Scopes
    public function scopePending($query)
    {
        return $query->where('approval_status', 'pending');
    }

    public function scopeApproved($query)
    {
        return $query->where('approval_status', 'approved');
    }

    public function scopeForMonth($query, $year, $month)
    {
        return $query->whereYear('trip_date', $year)
                     ->whereMonth('trip_date', $month);
    }

    public function scopeForDriver($query, $driverId)
    {
        return $query->where('driver_id', $driverId);
    }

    public function scopeForProject($query, $projectId)
    {
        return $query->where('project_id', $projectId);
    }

    public function scopeSubmittedToFinance($query)
    {
        return $query->where('submitted_to_finance', true);
    }

    public function scopeNotSubmittedToFinance($query)
    {
        return $query->where('submitted_to_finance', false);
    }

    // Accessors
    public function getTotalCostAttribute()
    {
        return $this->gasoline_cost + $this->toll_cost + $this->parking_cost;
    }

    public function getOdometerDifferenceAttribute()
    {
        return $this->odometer_end - $this->odometer_start;
    }

    public function getIsEditableAttribute(): bool
    {
        return $this->approval_status === 'pending';
    }

    public function getIsApprovedAttribute(): bool
    {
        return $this->approval_status === 'approved';
    }
}
