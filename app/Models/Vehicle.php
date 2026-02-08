<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

/**
 * Class Vehicle
 * Mengelola data aset kendaraan.
 */
class Vehicle extends Model
{
    use HasFactory;

    /**
     * SATU-SATUNYA TEMPAT UNTUK $fillable.
     * Pastikan semua kolom yang boleh diedit ada di sini.
     */
    protected $fillable = [
        'plate_number',
        'type',
        'project_id', // <--- Sudah ditambahkan
        'status',
        'current_km',
        'service_interval_km',
        'last_service_km',
        'pajak_stnk_berlaku_sampai',
        'kir_berlaku_sampai',
    ];

    // Casting agar field tanggal otomatis jadi object Carbon
    protected $casts = [
        'pajak_stnk_berlaku_sampai' => 'date',
        'kir_berlaku_sampai' => 'date',
    ];

    /* =========================================================================
     * RELATIONS (HUBUNGAN ANTAR TABEL)
     * ========================================================================= */

    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    public function attendances()
    {
        return $this->hasMany(Attendance::class);
    }

    public function maintenanceLogs()
    {
        return $this->hasMany(MaintenanceLog::class)->orderBy('service_date', 'desc');
    }

    public function latestAttendance()
    {
        return $this->hasOne(Attendance::class)->latest('time_out');
    }

    /* =========================================================================
     * BUSINESS LOGIC (LOGIKA BISNIS)
     * ========================================================================= */

    public function getCurrentKmAttribute()
    {
        return $this->latestAttendance?->speedo_akhir
            ?? $this->latestAttendance?->speedo_awal
            ?? 0;
    }

    public function getSisaKmAttribute()
    {
        if ($this->service_interval_km <= 0)
            return null;

        $nextService = $this->last_service_km + $this->service_interval_km;
        return $nextService - $this->current_km;
    }

    public function getHealthStatusCodeAttribute()
    {
        // 1. Cek Fisik
        $lastLog = $this->latestAttendance;
        if (
            $lastLog && (
                $lastLog->check_ban === 'Bermasalah' ||
                $lastLog->check_rem === 'Bermasalah' ||
                $lastLog->check_lampu === 'Bermasalah'
            )
        ) {
            return 'physical_issue';
        }

        // 2. Cek Mesin (Sisa KM)
        $sisa = $this->sisa_km;
        if ($sisa !== null) {
            if ($sisa <= 0)
                return 'service_due';
            if ($sisa <= 1000)
                return 'warning';
        }

        return 'healthy';
    }

    public function scopeSearch($query, $keyword)
    {
        if (!$keyword)
            return $query;

        return $query->where(function ($q) use ($keyword) {
            $q->where('plate_number', 'like', "%{$keyword}%")
                ->orWhere('type', 'like', "%{$keyword}%");
        });
    }
}