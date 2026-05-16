<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MaintenanceAlert extends Model
{
    use HasFactory;

    protected $fillable = [
        'vehicle_id',
        'component_id',
        'alert_type',
        'message',
        'triggered_at',
        'acknowledged_at',
        'acknowledged_by',
        'resolved_at',
        'status',
    ];

    protected $casts = [
        'triggered_at' => 'datetime',
        'acknowledged_at' => 'datetime',
        'resolved_at' => 'datetime',
    ];

    public function vehicle(): BelongsTo
    {
        return $this->belongsTo(Vehicle::class);
    }

    public function component(): BelongsTo
    {
        return $this->belongsTo(VehicleComponent::class, 'component_id');
    }

    public function acknowledgedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'acknowledged_by');
    }

    /**
     * Acknowledge alert
     */
    public function acknowledge(User $user): void
    {
        $this->update([
            'status' => 'acknowledged',
            'acknowledged_at' => now(),
            'acknowledged_by' => $user->id,
        ]);
    }

    /**
     * Resolve alert
     */
    public function resolve(): void
    {
        $this->update([
            'status' => 'resolved',
            'resolved_at' => now(),
        ]);
    }

    /**
     * Dismiss alert
     */
    public function dismiss(): void
    {
        $this->update([
            'status' => 'dismissed',
        ]);
    }

    /**
     * Scope: Active alerts
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'active')
            ->orderBy('alert_type')
            ->orderBy('triggered_at', 'desc');
    }

    /**
     * Scope: By alert type
     */
    public function scopeByType($query, string $type)
    {
        return $query->where('alert_type', $type);
    }

    /**
     * Scope: Critical alerts
     */
    public function scopeCritical($query)
    {
        return $query->whereIn('alert_type', ['critical', 'overdue'])
            ->where('status', 'active');
    }
}
