<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Customer extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'code',
        'contact_person',
        'email',
        'phone',
        'address',
    ];

    /**
     * Get the users associated with this customer.
     */
    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    /**
     * Get the vehicles associated with this customer.
     */
    public function vehicles(): HasMany
    {
        return $this->hasMany(Vehicle::class);
    }

    /**
     * Get the service reports associated with this customer.
     */
    public function serviceReports(): HasMany
    {
        return $this->hasMany(ServiceReport::class);
    }

    /**
     * Get the projects associated with this customer.
     */
    public function projects(): HasMany
    {
        return $this->hasMany(Project::class);
    }
}
