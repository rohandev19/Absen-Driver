<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Vehicle extends Model
{
    use HasFactory;

    /**
     * Atribut yang boleh diisi secara massal (Mass Assignable).
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'plate_number',
        'type',
    ];

    /**
     * Relasi: Satu mobil bisa dimiliki oleh banyak absensi.
     */
    public function attendances()
    {
        return $this->hasMany(Attendance::class);
    }
}