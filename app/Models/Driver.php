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
        'sim_expiry_date', // <--- TAMBAHKAN INI
        'password',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'password' => 'hashed',
        'sim_expiry_date' => 'date', // <--- TAMBAHKAN INI (Agar otomatis jadi object Carbon)
    ];

    // ... relasi lainnya ...
    public function attendances()
    {
        return $this->hasMany(Attendance::class);
    }
}