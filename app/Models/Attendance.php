<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Attendance extends Model
{
    use HasFactory;

    /**
     * Atribut yang boleh diisi secara massal (Mass Assignable).
     */
    protected $fillable = [
        // Kolom Check-in
        'driver_id',
        'vehicle_id',
        'vehicle_entry_method',
        'manual_vehicle_plate',
        'manual_vehicle_reason',
        'manual_vehicle_photo_path',
        'vehicle_verification_status',
        'time_in',
        'gps_location_in',
        'selfie_photo_path',
        'speedo_photo_awal_path',
        'condition_photo_1_path',
        'condition_photo_2_path',
        'speedo_awal',

        // Kolom Check-out
        'time_out',
        'gps_location_out', // <-- Ditambahkan agar aman jika ada data yang masuk
        'speedo_photo_akhir_path',
        'catatan',
        'check_ban',
        'check_lampu',
        'check_rem',
        'speedo_akhir',

        // Offline Recovery Metadata
        'is_offline_recovery',
        'recovery_timestamp',
        'offline_entry_id',
        'is_late_submission',
    ];

    /**
     * The attributes that should be cast.
     */
    protected $casts = [
        'is_offline_recovery' => 'boolean',
        'is_late_submission' => 'boolean',
        'recovery_timestamp' => 'datetime',
        'time_in' => 'datetime',
        'time_out' => 'datetime',
    ];

    public function driver()
    {
        return $this->belongsTo(Driver::class);
    }

    public function vehicle()
    {
        return $this->belongsTo(Vehicle::class);
    }

    public function transportCost()
    {
        return $this->hasOne(TransportCost::class);
    }

    /**
     * Get the offline recovery log associated with this attendance.
     */
    public function offlineRecoveryLog()
    {
        return $this->hasOne(OfflineRecoveryLog::class);
    }
}
