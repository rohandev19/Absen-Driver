<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class Project extends Model
{
    /**
     * The attributes that are mass assignable.
     * SECURITY: Using explicit $fillable instead of $guarded for better control
     */
    protected $fillable = [
        'name',
        'description',
        'customer_id',
    ];

    public function drivers()
    {
        return $this->hasMany(Driver::class);
    }
    
    public function vehicles()
    {
        return $this->hasMany(Vehicle::class);
    }
    
    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }
    
    public function transportCosts()
    {
        return $this->hasMany(TransportCost::class);
    }
}