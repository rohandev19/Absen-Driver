<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OfflineRecoveryLog extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     */
    protected $table = 'offline_recovery_logs';

    /**
     * Indicates if the model should use the updated_at timestamp.
     */
    public $timestamps = false;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'driver_id',
        'attendance_id',
        'offline_entry_id',
        'device_timestamp',
        'recovery_timestamp',
        'delay_minutes',
        'result',
        'error_code',
        'error_message',
        'retry_count',
        'photo_size_kb',
    ];

    /**
     * The attributes that should be cast.
     */
    protected $casts = [
        'device_timestamp' => 'datetime',
        'recovery_timestamp' => 'datetime',
        'delay_minutes' => 'integer',
        'retry_count' => 'integer',
        'photo_size_kb' => 'integer',
        'result' => 'string',
        'created_at' => 'datetime',
    ];

    /**
     * Get the driver that owns the recovery log.
     */
    public function driver()
    {
        return $this->belongsTo(Driver::class);
    }

    /**
     * Get the attendance that this recovery log is associated with.
     */
    public function attendance()
    {
        return $this->belongsTo(Attendance::class);
    }
}
