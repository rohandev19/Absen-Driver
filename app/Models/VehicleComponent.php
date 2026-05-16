<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Carbon\Carbon;

class VehicleComponent extends Model
{
    use HasFactory;

    protected $fillable = [
        'vehicle_id',
        'component_name',
        'category',
        'replacement_interval_km',
        'replacement_interval_days',
        'last_replacement_km',
        'last_replacement_date',
        'next_replacement_km',
        'next_replacement_date',
        'cost_per_replacement',
        'warning_threshold_km',
        'critical_threshold_km',
        'status',
        'notes',
    ];

    protected $casts = [
        'last_replacement_date' => 'date',
        'next_replacement_date' => 'date',
        'cost_per_replacement' => 'decimal:2',
        'replacement_interval_days' => 'integer',
        'replacement_interval_km' => 'integer',
    ];

    protected static function booted()
    {
        static::saving(function ($component) {
            $component->calculateNextReplacement();
            $component->updateStatus();
        });
    }

    public function vehicle(): BelongsTo
    {
        return $this->belongsTo(Vehicle::class);
    }

    public function maintenanceSchedules(): HasMany
    {
        return $this->hasMany(MaintenanceSchedule::class, 'component_id');
    }

    public function alerts(): HasMany
    {
        return $this->hasMany(MaintenanceAlert::class, 'component_id');
    }

    /**
     * Calculate next replacement KM and date
     */
    public function calculateNextReplacement(): void
    {
        // Calculate next KM
        if ($this->replacement_interval_km && $this->last_replacement_km) {
            $this->next_replacement_km = $this->last_replacement_km + $this->replacement_interval_km;
        }

        // Calculate next date
        if ($this->replacement_interval_days && $this->last_replacement_date) {
            $this->next_replacement_date = $this->last_replacement_date
                ->copy()
                ->addDays((int) $this->replacement_interval_days);
        }
    }

    /**
     * Update component status based on current vehicle KM
     */
    public function updateStatus(): void
    {
        $vehicle = $this->vehicle;

        if (!$vehicle || !$this->next_replacement_km) {
            $this->status = 'healthy';
            return;
        }

        $kmRemaining = $this->next_replacement_km - $vehicle->current_km;

        if ($kmRemaining <= 0) {
            $this->status = 'overdue';
        } elseif ($kmRemaining <= $this->critical_threshold_km) {
            $this->status = 'critical';
        } elseif ($kmRemaining <= $this->warning_threshold_km) {
            $this->status = 'warning';
        } else {
            $this->status = 'healthy';
        }
    }

    /**
     * Get KM remaining until next replacement
     */
    public function getKmRemainingAttribute(): ?int
    {
        if (!$this->next_replacement_km || !$this->vehicle) {
            return null;
        }

        return max(0, $this->next_replacement_km - $this->vehicle->current_km);
    }

    /**
     * Get days remaining until next replacement
     */
    public function getDaysRemainingAttribute(): ?int
    {
        if (!$this->next_replacement_date) {
            return null;
        }

        return max(0, Carbon::now()->diffInDays($this->next_replacement_date, false));
    }

    /**
     * Calculate component health score (0-1)
     */
    public function getHealthScoreAttribute(): float
    {
        if (!$this->next_replacement_km || !$this->vehicle || !$this->replacement_interval_km) {
            return 1.0;
        }

        $kmRemaining = $this->km_remaining;

        if ($kmRemaining <= 0) {
            return 0.0; // Overdue
        }

        if ($kmRemaining <= $this->critical_threshold_km) {
            return 0.2; // Critical
        }

        if ($kmRemaining <= $this->warning_threshold_km) {
            return 0.5; // Warning
        }

        // Healthy: Linear interpolation
        return min(1.0, $kmRemaining / $this->replacement_interval_km);
    }

    /**
     * Check if component needs maintenance
     */
    public function needsMaintenance(): bool
    {
        return in_array($this->status, ['warning', 'critical', 'overdue']);
    }

    /**
     * Scope: Filter by status
     */
    public function scopeByStatus($query, string $status)
    {
        return $query->where('status', $status);
    }

    /**
     * Scope: Filter by category
     */
    public function scopeByCategory($query, string $category)
    {
        return $query->where('category', $category);
    }

    /**
     * Scope: Components needing maintenance
     */
    public function scopeNeedsMaintenance($query)
    {
        return $query->whereIn('status', ['warning', 'critical', 'overdue']);
    }
}
