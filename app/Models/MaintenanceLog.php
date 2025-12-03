<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MaintenanceLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'vehicle_id',
        'service_date',
        'km_at_service',
        'description',
        'workshop_name',
        'recorded_by_user_id'
    ];

    // Relasi balik ke Kendaraan
    public function vehicle()
    {
        return $this->belongsTo(Vehicle::class);
    }

    // Relasi ke Admin yang mencatat
    public function recorder()
    {
        return $this->belongsTo(User::class, 'recorded_by_user_id');
    }
}