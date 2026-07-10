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
        'tahun_pembuatan',
        'project_id', // <--- Sudah ditambahkan
        'status',
        'is_temporary',
        'verification_status',
        'verified_by',
        'verified_at',
        'source',
        'notes',
        'current_km',
        'service_interval_km',
        'last_service_km',
        'pajak_stnk_berlaku_sampai',
        'kir_berlaku_sampai',
        'qr_code_path',
        'qr_code_identifier',
    ];

    protected $appends = [
        'qr_code_url',
    ];

    // Casting agar field tanggal otomatis jadi object Carbon
    protected $casts = [
        'pajak_stnk_berlaku_sampai' => 'date',
        'kir_berlaku_sampai' => 'date',
        'is_temporary' => 'boolean',
        'verified_at' => 'datetime',
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

    public function components()
    {
        return $this->hasMany(VehicleComponent::class);
    }

    public function maintenanceSchedules()
    {
        return $this->hasMany(MaintenanceSchedule::class);
    }

    public function maintenanceAlerts()
    {
        return $this->hasMany(MaintenanceAlert::class);
    }

    public function replacementUsages()
    {
        return $this->hasMany(VehicleReplacement::class, 'replacement_vehicle_id');
    }

    public function replacementHistories()
    {
        return $this->hasMany(VehicleReplacement::class, 'original_vehicle_id');
    }

    public function transportCosts()
    {
        return $this->hasMany(TransportCost::class);
    }

    /* =========================================================================
     * BUSINESS LOGIC (LOGIKA BISNIS)
     * ========================================================================= */

    public function getComputedKmAttribute()
    {
        // Prioritas 1: Odometer dari absen keluar supir terakhir
        // Prioritas 2: Odometer dari absen masuk supir terakhir
        // Prioritas 3 (BARU): Nilai current_km dari database (inputan awal admin)
        // Prioritas 4: Angka 0

        return $this->latestAttendance?->speedo_akhir
            ?? $this->latestAttendance?->speedo_awal
            ?? $this->attributes['current_km']
            ?? 0;
    }
    public function getSisaKmAttribute()
    {
        if ($this->service_interval_km <= 0)
            return null;

        $nextService = $this->last_service_km + $this->service_interval_km;
        return $nextService - $this->computed_km;
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

    /**
     * Accessor for full QR code URL.
     *
     * @return string|null
     */
    public function getQrCodeUrlAttribute()
    {
        if ($this->qr_code_path) {
            return \Illuminate\Support\Facades\Storage::url($this->qr_code_path) . '?v=' . ($this->updated_at ? $this->updated_at->timestamp : now()->timestamp);
        }
        return null;
    }
}
