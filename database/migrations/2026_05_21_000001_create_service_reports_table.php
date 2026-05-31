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
        Schema::create('service_reports', function (Blueprint $table) {
            $table->id();
            $table->foreignId('driver_id')->constrained('drivers')->onDelete('cascade');
            $table->foreignId('vehicle_id')->constrained('vehicles')->onDelete('cascade');
            $table->unsignedBigInteger('customer_id')->nullable(); // Foreign key will be added later
            
            $table->dateTime('timestamp');
            $table->string('gps_location');
            $table->text('description');
            
            $table->string('vehicle_condition_photo_path');
            $table->string('receipt_photo_path');
            
            $table->enum('status', ['pending', 'approved_admin', 'pending_customer', 'approved_customer', 'rejected'])
                  ->default('pending');
            
            $table->text('admin_notes')->nullable();
            $table->foreignId('approved_by_admin_id')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamp('approved_at_admin')->nullable();
            
            $table->string('finance_word_path')->nullable();
            $table->string('customer_word_path')->nullable();
            $table->string('customer_signed_document_path')->nullable();
            
            $table->foreignId('approved_by_customer_id')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamp('approved_at_customer')->nullable();
            
            $table->text('rejected_reason')->nullable();
            
            $table->timestamps();
            
            // Indexes
            $table->index('status');
            $table->index('vehicle_id');
            $table->index(['customer_id', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('service_reports');
    }
};
