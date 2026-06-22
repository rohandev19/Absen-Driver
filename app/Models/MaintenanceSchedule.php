<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Carbon\Carbon;

class MaintenanceSchedule extends Model
{
    use HasFactory;

    protected $fillable = [
        'vehicle_id',
        'component_id',
        'scheduled_date',
        'scheduled_km',
        'type',
        'priority',
        'status',
        'estimated_cost',
        'actual_cost',
        'workshop_name',
        'notes',
        'completed_at',
        'completed_by',
        'receipt_photo_path',
        'odometer_photo_path',
        'finance_pdf_path',
        'admin_signature_path',
        'admin_signer_name',
        'admin_signer_role',
    ];

    protected $casts = [
        'scheduled_date' => 'date',
        'estimated_cost' => 'decimal:2',
        'actual_cost' => 'decimal:2',
        'completed_at' => 'datetime',
    ];

    public function vehicle(): BelongsTo
    {
        return $this->belongsTo(Vehicle::class);
    }

    public function component(): BelongsTo
    {
        return $this->belongsTo(VehicleComponent::class, 'component_id');
    }

    public function completedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'completed_by');
    }

    /**
     * Mark schedule as completed
     */
    public function markAsCompleted(User $user, float $actualCost = null): void
    {
        $this->update([
            'status' => 'completed',
            'completed_at' => now(),
            'completed_by' => $user->id,
            'actual_cost' => $actualCost ?? $this->estimated_cost,
        ]);

        // Update component last replacement
        if ($this->component) {
            $this->component->update([
                'last_replacement_km' => $this->vehicle->current_km,
                'last_replacement_date' => now(),
            ]);
        }
    }

    /**
     * Check if schedule is overdue
     */
    public function isOverdue(): bool
    {
        return $this->status !== 'completed' 
            && $this->scheduled_date < Carbon::today();
    }

    /**
     * Get days until scheduled
     */
    public function getDaysUntilAttribute(): int
    {
        return Carbon::now()->diffInDays($this->scheduled_date, false);
    }

    /**
     * Scope: Filter by status
     */
    public function scopeByStatus($query, string $status)
    {
        return $query->where('status', $status);
    }

    /**
     * Scope: Filter by priority
     */
    public function scopeByPriority($query, string $priority)
    {
        return $query->where('priority', $priority);
    }

    /**
     * Scope: Upcoming schedules
     */
    public function scopeUpcoming($query, int $days = 7)
    {
        return $query->where('status', '!=', 'completed')
            ->where('scheduled_date', '>=', Carbon::today())
            ->where('scheduled_date', '<=', Carbon::today()->addDays($days))
            ->orderBy('scheduled_date');
    }

    /**
     * Scope: Overdue schedules
     */
    public function scopeOverdue($query)
    {
        return $query->where('status', '!=', 'completed')
            ->where('scheduled_date', '<', Carbon::today())
            ->orderBy('scheduled_date');
    }

    /**
     * Scope: By type
     */
    public function scopeByType($query, string $type)
    {
        return $query->where('type', $type);
    }

    /**
     * Get the receipt photo URL.
     */
    public function getReceiptPhotoUrlAttribute(): ?string
    {
        return $this->receipt_photo_path ? asset('storage/' . $this->receipt_photo_path) : null;
    }

    /**
     * Get the odometer photo URL.
     */
    public function getOdometerPhotoUrlAttribute(): ?string
    {
        return $this->odometer_photo_path ? asset('storage/' . $this->odometer_photo_path) : null;
    }

    /**
     * Get the finance PDF URL.
     */
    public function getFinancePdfUrlAttribute(): ?string
    {
        return $this->finance_pdf_path ? asset('storage/' . $this->finance_pdf_path) : null;
    }
}
