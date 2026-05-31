<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ServiceReport extends Model
{
    use HasFactory;

    // Status constants
    const STATUS_PENDING = 'pending';
    const STATUS_APPROVED_ADMIN = 'approved_admin';
    const STATUS_PENDING_CUSTOMER = 'pending_customer';
    const STATUS_APPROVED_CUSTOMER = 'approved_customer';
    const STATUS_REJECTED = 'rejected';

    protected $fillable = [
        'driver_id',
        'vehicle_id',
        'customer_id',
        'timestamp',
        'gps_location',
        'description',
        'vehicle_condition_photo_path',
        'receipt_photo_path',
        'status',
        'admin_notes',
        'approved_by_admin_id',
        'approved_at_admin',
        'admin_signature_path',
        'admin_signer_name',
        'admin_signer_role',
        'finance_word_path',
        'customer_word_path',
        'customer_signed_document_path',
        'customer_signature_path',
        'customer_signer_name',
        'customer_signer_role',
        'approved_by_customer_id',
        'approved_at_customer',
        'rejected_reason',
    ];

    protected $casts = [
        'timestamp' => 'datetime',
        'approved_at_admin' => 'datetime',
        'approved_at_customer' => 'datetime',
    ];

    /**
     * Get the driver that submitted this report.
     */
    public function driver(): BelongsTo
    {
        return $this->belongsTo(Driver::class);
    }

    /**
     * Get the vehicle associated with this report.
     */
    public function vehicle(): BelongsTo
    {
        return $this->belongsTo(Vehicle::class);
    }

    /**
     * Get the customer associated with this report.
     */
    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    /**
     * Get the admin user who approved this report.
     */
    public function approvedByAdmin(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by_admin_id');
    }

    /**
     * Get the customer user who approved this report.
     */
    public function approvedByCustomer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by_customer_id');
    }

    /**
     * Get the vehicle condition photo URL.
     */
    public function getVehicleConditionPhotoUrlAttribute(): string
    {
        return asset('storage/' . $this->vehicle_condition_photo_path);
    }

    /**
     * Get the receipt photo URL.
     */
    public function getReceiptPhotoUrlAttribute(): string
    {
        return asset('storage/' . $this->receipt_photo_path);
    }

    /**
     * Get customer from vehicle's project.
     * This is the primary way to determine which customer to notify.
     */
    public function getProjectCustomer()
    {
        return $this->vehicle?->project?->customer;
    }
}
