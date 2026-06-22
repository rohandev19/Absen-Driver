<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EmergencyReport extends Model
{
    use HasFactory;

    public const STATUS_NEW = 'new';
    public const STATUS_SERVICE_CREATED = 'service_created';
    public const STATUS_INFO_RESOLVED = 'info_resolved';

    protected $table = 'emergency_reports';

    protected $fillable = [
        'driver_id',
        'vehicle_id',
        'timestamp',
        'gps_location',
        'description',
        'proof_photo_path',
        'follow_up_status',
        'follow_up_notes',
        'service_report_id',
        'processed_by',
        'processed_at',
    ];

    protected $casts = [
        'timestamp' => 'datetime',
        'processed_at' => 'datetime',
    ];

    public function driver()
    {
        return $this->belongsTo(Driver::class, 'driver_id');
    }

    public function vehicle()
    {
        return $this->belongsTo(Vehicle::class, 'vehicle_id');
    }

    public function serviceReport()
    {
        return $this->belongsTo(ServiceReport::class, 'service_report_id');
    }

    public function processedBy()
    {
        return $this->belongsTo(User::class, 'processed_by');
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
