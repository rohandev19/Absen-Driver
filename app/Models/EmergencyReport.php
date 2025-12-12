<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EmergencyReport extends Model
{
    use HasFactory;

    protected $table = 'emergency_reports';

    protected $fillable = [
        'driver_id',
        'vehicle_id',
        'timestamp',
        'gps_location',
        'description',
        'proof_photo_path'
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

    /* =========================================================================
     * ACCESSORS (FORMATTING DATA)
     * ========================================================================= */

    /**
     * Buat Link Google Maps otomatis dari koordinat GPS.
     */
    public function getGoogleMapsLinkAttribute()
    {
        if ($this->gps_location) {
            // PERBAIKAN: Menggunakan URL standard Google Maps
            // Format: https://maps.google.com/?q=LATITUDE,LONGITUDE
            return 'https://maps.google.com/?q=' . $this->gps_location;
        }
        return '#';
    }

    public function getProofPhotoUrlAttribute()
    {
        return $this->proof_photo_path ? asset('storage/' . $this->proof_photo_path) : null;
    }
}