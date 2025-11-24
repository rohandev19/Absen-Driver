<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EmergencyReport extends Model
{
    use HasFactory;

    protected $table = 'emergency_reports';

    /**
     * PERBAIKAN:
     * Hapus 'created_at' dan 'updated_at' dari $fillable.
     * Kolom ini diurus otomatis oleh Laravel.
     */
    protected $fillable = [
        'driver_id',
        'vehicle_id',
        'timestamp',
        'gps_location',
        'description',
        'proof_photo_path',
    
    ];

    protected $casts = [
        'timestamp' => 'datetime',
    ];

    public function driver()
    {
        return $this->belongsTo(Driver::class, 'driver_id');
    }

    public function vehicle()
    {
        return $this->belongsTo(Vehicle::class, 'vehicle_id');
    }

    public function getProofPhotoUrlAttribute()
    {
        if (!$this->proof_photo_path) {
            return null;
        }
        return asset('storage/' . $this->proof_photo_path);
    }
}