<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Vehicle extends Model
{
    use HasFactory;

    protected $fillable = [
        'plate_number',
        'type',
        // Pastikan kolom-kolom ini ada di $fillable
        'service_interval_km',
        'last_service_km',
        'pajak_stnk_berlaku_sampai',
        'kir_berlaku_sampai',
    ];

    public function attendances()
    {
        return $this->hasMany(Attendance::class);
    }
    public function maintenanceLogs()
    {
        // Mengurutkan dari servis terbaru ke terlama
        return $this->hasMany(MaintenanceLog::class)->orderBy('service_date', 'desc');
    }
}