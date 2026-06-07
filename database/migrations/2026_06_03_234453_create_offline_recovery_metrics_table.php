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
        Schema::create('offline_recovery_metrics', function (Blueprint $table) {
            $table->id();
            $table->date('metric_date')->unique();
            $table->integer('total_recoveries')->default(0);
            $table->integer('successful_recoveries')->default(0);
            $table->integer('failed_recoveries')->default(0);
            $table->decimal('average_delay_minutes', 10, 2)->default(0);
            $table->timestamps();

            // Add index for metric_date
            $table->index('metric_date', 'idx_metrics_date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('offline_recovery_metrics');
    }
};
