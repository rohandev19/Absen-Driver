<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class Driver extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $fillable = [
        'full_name',
        'driver_id_nik',
        'sim_expiry_date',
        'password',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'password' => 'hashed',
        'sim_expiry_date' => 'date',
    ];

    public function attendances()
    {
        return $this->hasMany(Attendance::class);
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