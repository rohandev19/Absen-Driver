<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

/**
 * Class Vehicle
 * * @property int $id
 * @property string $plate_number
 * @property string|null $type
 * @property int $service_interval_km
 * @property int $last_service_km
 * * // Kolom Tanggal (Otomatis jadi Carbon karena casts)
 * @property Carbon|null $pajak_stnk_berlaku_sampai
 * @property Carbon|null $kir_berlaku_sampai
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * * // Custom Attributes (Accessors)
 * @property-read int $current_km
 * @property-read int|null $sisa_km
 * @property-read string $health_status_code
 * * // Relationships
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Attendance[] $attendances
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\MaintenanceLog[] $maintenanceLogs
 * @property-read \App\Models\Attendance|null $latestAttendance
 */
class Vehicle extends Model
{
    use HasFactory;

    protected $fillable = [
        'plate_number',
        'type',
        'service_interval_km',
        'last_service_km',
        'pajak_stnk_berlaku_sampai',
        'kir_berlaku_sampai',
    ];

    // Casting agar field tanggal otomatis jadi object Carbon (bisa diformat tanggalnya)
    protected $casts = [
        'pajak_stnk_berlaku_sampai' => 'date',
        'kir_berlaku_sampai' => 'date',
    ];

    /* =========================================================================
     * RELATIONS (HUBUNGAN ANTAR TABEL)
     * ========================================================================= */

    public function attendances()
    {
        return $this->hasMany(Attendance::class);
    }

    public function maintenanceLogs()
    {
        return $this->hasMany(MaintenanceLog::class)->orderBy('service_date', 'desc');
    }

    /**
     * Relasi helper: Mengambil SATU data absensi terakhir.
     * Penting untuk tahu KM terakhir mobil ada di angka berapa.
     */
    public function latestAttendance()
    {
        return $this->hasOne(Attendance::class)->latest('time_out');
    }

    /* =========================================================================
     * BUSINESS LOGIC (LOGIKA BISNIS) - OTAKNYA DI SINI
     * ========================================================================= */

    /**
     * Hitung KM Saat Ini secara otomatis.
     * Logika: Cek speedo_akhir (kalau sudah pulang) atau speedo_awal (kalau baru berangkat).
     * @return int
     */
    public function getCurrentKmAttribute()
    {
        return $this->latestAttendance?->speedo_akhir
            ?? $this->latestAttendance?->speedo_awal
            ?? 0;
    }

    /**
     * Hitung Sisa KM menuju servis berikutnya.
     * Rumus: (KM Servis Terakhir + Interval) - KM Saat Ini.
     * @return int|null
     */
    public function getSisaKmAttribute()
    {
        if ($this->service_interval_km <= 0)
            return null;

        $nextService = $this->last_service_km + $this->service_interval_km;
        return $nextService - $this->current_km;
    }

    /**
     * Tentukan Status Kesehatan Mobil (Return KODE, bukan HTML).
     * Frontend yang akan menentukan warnanya nanti berdasarkan kode ini.
     * * Output: 'physical_issue', 'service_due', 'warning', atau 'healthy'.
     */
    public function getHealthStatusCodeAttribute()
    {
        // 1. Cek Fisik (Ban/Rem/Lampu) - Prioritas Utama
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

    /**
     * Scope untuk pencarian (Search).
     * Biar Controller bersih dari query 'LIKE %...%'.
     */
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