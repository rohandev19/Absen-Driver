<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VehicleReplacement extends Model
{
    use HasFactory;

    const STATUS_ACTIVE = 'active';
    const STATUS_COMPLETED = 'completed';
    const STATUS_CANCELLED = 'cancelled';

    protected $fillable = [
        'original_vehicle_id',
        'replacement_vehicle_id',
        'driver_id',
        'service_report_id',
        'start_at',
        'end_at',
        'reason',
        'status',
        'notes',
        'created_by',
    ];

    protected $casts = [
        'start_at' => 'datetime',
        'end_at' => 'datetime',
    ];

    public function originalVehicle()
    {
        return $this->belongsTo(Vehicle::class, 'original_vehicle_id');
    }

    public function replacementVehicle()
    {
        return $this->belongsTo(Vehicle::class, 'replacement_vehicle_id');
    }

    public function driver()
    {
        return $this->belongsTo(Driver::class);
    }

    public function serviceReport()
    {
        return $this->belongsTo(ServiceReport::class);
    }
}
