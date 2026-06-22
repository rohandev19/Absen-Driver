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
    const STATUS_PENDING_ADMIN = 'pending_admin';
    const STATUS_WAITING_COMPLETION = 'waiting_completion';
    const STATUS_APPROVED_ADMIN = 'approved_admin';
    const STATUS_PENDING_CUSTOMER = 'pending_customer';
    const STATUS_APPROVED_CUSTOMER = 'approved_customer';
    const STATUS_REJECTED = 'rejected';
    const STATUS_REJECTED_ADMIN = 'rejected_admin';
    const STATUS_REJECTED_CUSTOMER = 'rejected_customer';
    const STATUS_REVISION_REQUESTED = 'revision_requested';

    protected $fillable = [
        'driver_id',
        'vehicle_id',
        'customer_id',
        'timestamp',
        'gps_location',
        'description',
        'ticket_number',
        'report_source',
        'location_source',
        'service_type',
        'problem_category',
        'odometer',
        'service_action',
        'unit_status_after_service',
        'service_completed_at',
        'completed_by_driver_id',
        'additional_notes',
        'before_service_photo_source',
        'before_service_photo_uploaded_at',
        'vehicle_condition_photo_path',
        'after_service_photo_path',
        'after_service_photo_taken_at',
        'odometer_photo_path',
        'odometer_photo_taken_at',
        'receipt_photo_path',
        'receipt_photo_taken_at',
        'status',
        'admin_notes',
        'approved_by_admin_id',
        'approved_at_admin',
        'admin_signature_path',
        'admin_signer_name',
        'admin_signer_role',
        'finance_word_path',
        'customer_word_path',
        'customer_pdf_path',
        'customer_signed_pdf_path',
        'admin_internal_pdf_path',
        'finance_pdf_path',
        'workshop_name',
        'invoice_number',
        'service_cost',
        'sparepart_cost',
        'other_cost',
        'total_cost',
        'finance_notes',
        'customer_signed_document_path',
        'customer_signature_path',
        'customer_signer_name',
        'customer_signer_role',
        'approved_by_customer_id',
        'approved_at_customer',
        'rejected_reason',
        'rejected_by_role',
        'customer_rejection_reason',
        'customer_revision_notes',
        'rejected_at',
        'revision_requested_at',
    ];

    protected $casts = [
        'timestamp' => 'datetime',
        'service_completed_at' => 'datetime',
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

    public function vehicleReplacements()
    {
        return $this->hasMany(VehicleReplacement::class);
    }

    /**
     * Get the admin user who approved this report.
     */
    public function approvedByAdmin(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by_admin_id');
    }

    public function completedByDriver(): BelongsTo
    {
        return $this->belongsTo(Driver::class, 'completed_by_driver_id');
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
