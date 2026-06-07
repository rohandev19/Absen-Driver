<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('service_reports', function (Blueprint $table) {
            $table->string('ticket_number')->nullable()->unique();
            $table->string('service_type')->nullable();
            $table->string('problem_category')->nullable();
            $table->unsignedInteger('odometer')->nullable();
            $table->text('service_action')->nullable();
            $table->string('unit_status_after_service')->nullable();
            $table->text('additional_notes')->nullable();
            $table->string('before_service_photo_source')->nullable();
            $table->timestamp('before_service_photo_uploaded_at')->nullable();
            $table->string('after_service_photo_path')->nullable();
            $table->timestamp('after_service_photo_taken_at')->nullable();
            $table->string('odometer_photo_path')->nullable();
            $table->timestamp('odometer_photo_taken_at')->nullable();
            $table->timestamp('receipt_photo_taken_at')->nullable();
            $table->string('customer_pdf_path')->nullable();
            $table->string('customer_signed_pdf_path')->nullable();
            $table->string('internal_pdf_path')->nullable();
            $table->string('rejected_by_role')->nullable();
            $table->text('customer_rejection_reason')->nullable();
            $table->text('customer_revision_notes')->nullable();
            $table->timestamp('rejected_at')->nullable();
            $table->timestamp('revision_requested_at')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('service_reports', function (Blueprint $table) {
            $table->dropColumn([
                'ticket_number',
                'service_type',
                'problem_category',
                'odometer',
                'service_action',
                'unit_status_after_service',
                'additional_notes',
                'before_service_photo_source',
                'before_service_photo_uploaded_at',
                'after_service_photo_path',
                'after_service_photo_taken_at',
                'odometer_photo_path',
                'odometer_photo_taken_at',
                'receipt_photo_taken_at',
                'customer_pdf_path',
                'customer_signed_pdf_path',
                'internal_pdf_path',
                'rejected_by_role',
                'customer_rejection_reason',
                'customer_revision_notes',
                'rejected_at',
                'revision_requested_at',
            ]);
        });
    }
};
