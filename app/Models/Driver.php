<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class Driver extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * Daftar kolom yang boleh diisi (Mass Assignment).
     * Pastikan semua kolom baru ada di sini.
     */
    protected $fillable = [
        'full_name',
        'driver_id_nik',    // ID Badge/Absen
        'nik_ktp',          // <--- SUDAH BENAR (Data NIK KTP akan bisa tersimpan sekarang)
        'sim_expiry_date',
        'sim_type',         // <--- SUDAH BENAR
        'password',
        'project_id',       // <--- SUDAH BENAR
        'foto_sim',
        'foto_ktp',
        'profile_photo',
        'fcm_token',
        'is_on_duty',       // Offline Attendance Recovery: Server-side duty status tracking
        'qr_code_path',
        'qr_code_identifier',
    ];

    protected $appends = [
        'qr_code_url',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'password' => 'hashed',
        'sim_expiry_date' => 'date',
        'is_on_duty' => 'boolean',
    ];

    /* =========================================================================
     * ACCESSORS
     * ========================================================================= */

    /**
     * Role accessor — selalu mengembalikan 'driver'.
     *
     * Tabel `drivers` tidak memiliki kolom `role`, tetapi middleware
     * `EnsureUserRole` (alias 'role') memeriksa `Auth::user()->role`.
     * Tanpa accessor ini, `$driver->role` bernilai null dan middleware
     * langsung menolak request dengan 403, menyebabkan error NET-003
     * di aplikasi mobile.
     */
    public function getRoleAttribute(): string
    {
        return 'driver';
    }

    /**
     * Accessor for is_on_duty field to ensure type consistency.
     * Ensures boolean casting even if database returns integer.
     *
     * @return bool
     */
    public function getIsOnDutyAttribute($value): bool
    {
        return (bool) $value;
    }

    /**
     * Accessor for full QR code URL.
     *
     * @return string|null
     */
    public function getQrCodeUrlAttribute()
    {
        if ($this->qr_code_path) {
            return \Illuminate\Support\Facades\Storage::url($this->qr_code_path);
        }
        return null;
    }

    /* =========================================================================
     * RELASI (RELATIONSHIPS)
     * ========================================================================= */

    public function attendances()
    {
        return $this->hasMany(Attendance::class);
    }

    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    public function transportCosts()
    {
        return $this->hasMany(TransportCost::class);
    }

    public function vehicleReplacements()
    {
        return $this->hasMany(VehicleReplacement::class);
    }

    /* =========================================================================
     * QUERY SCOPES
     * ========================================================================= */

    /**
     * Scope a query to only include drivers currently on duty.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeOnDuty($query)
    {
        return $query->where('is_on_duty', true);
    }

    /* =========================================================================
     * BUSINESS LOGIC
     * ========================================================================= */

    /**
     * Cek apakah driver sedang bertugas (On Duty).
     * Definisi: Sudah Check-in (time_in ada), tapi belum Check-out (time_out NULL).
     * @return bool
     */
    public function isOnDuty(): bool
    {
        return $this->attendances()->whereNull('time_out')->exists();
    }
}
