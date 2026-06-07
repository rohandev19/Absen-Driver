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
        Schema::create('offline_recovery_logs', function (Blueprint $table) {
            $table->id();
            
            // Foreign key to drivers table
            $table->foreignId('driver_id')->constrained('drivers')->onDelete('cascade');
            
            // Foreign key to attendances table (nullable since it may not exist yet)
            $table->foreignId('attendance_id')->nullable()->constrained('attendances')->onDelete('set null');
            
            // Offline entry ID from mobile app for idempotency
            $table->string('offline_entry_id');
            
            // Timestamps
            $table->dateTime('device_timestamp');
            $table->dateTime('recovery_timestamp');
            
            // Delay calculation
            $table->integer('delay_minutes');
            
            // Result and error tracking
            $table->enum('result', ['success', 'failed']);
            $table->string('error_code', 100)->nullable();
            $table->text('error_message')->nullable();
            
            // Retry tracking
            $table->integer('retry_count')->default(0);
            
            // Photo metadata
            $table->integer('photo_size_kb')->nullable();
            
            // Standard timestamp
            $table->timestamp('created_at')->useCurrent();
            
            // Indexes
            $table->index('driver_id', 'idx_recovery_logs_driver');
            $table->index('result', 'idx_recovery_logs_result');
            $table->index('created_at', 'idx_recovery_logs_created');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('offline_recovery_logs');
    }
};
