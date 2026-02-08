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