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
    ];

    public function driver()
    {
        return $this->belongsTo(Driver::class);
    }

    public function vehicle()
    {
        return $this->belongsTo(Vehicle::class);
    }
}