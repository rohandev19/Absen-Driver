<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('vehicle_replacements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('original_vehicle_id')->nullable()->constrained('vehicles')->nullOnDelete();
            $table->foreignId('replacement_vehicle_id')->constrained('vehicles')->cascadeOnDelete();
            $table->foreignId('driver_id')->nullable()->constrained('drivers')->nullOnDelete();
            $table->foreignId('service_report_id')->nullable()->constrained('service_reports')->nullOnDelete();
            $table->dateTime('start_at');
            $table->dateTime('end_at')->nullable();
            $table->string('reason')->nullable();
            $table->string('status', 30)->default('active');
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['replacement_vehicle_id', 'status']);
            $table->index(['driver_id', 'status']);
            $table->index('start_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('vehicle_replacements');
    }
};
