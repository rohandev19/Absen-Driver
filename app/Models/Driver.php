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
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'password' => 'hashed',
        'sim_expiry_date' => 'date',
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