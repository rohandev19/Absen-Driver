<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class Project extends Model
{
    protected $guarded = ['id'];

    public function drivers()
    {
        return $this->hasMany(Driver::class);
    }
    public function vehicles()
    {
        return $this->hasMany(Vehicle::class);
    }
}