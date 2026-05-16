<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('vehicle_components', function (Blueprint $table) {
            $table->id();
            $table->foreignId('vehicle_id')->constrained()->onDelete('cascade');
            $table->string('component_name', 100);
            $table->string('category', 50);
            $table->integer('replacement_interval_km')->nullable();
            $table->integer('replacement_interval_days')->nullable();
            $table->integer('last_replacement_km')->nullable();
            $table->date('last_replacement_date')->nullable();
            $table->integer('next_replacement_km')->nullable();
            $table->date('next_replacement_date')->nullable();
            $table->decimal('cost_per_replacement', 10, 2)->default(0);
            $table->integer('warning_threshold_km')->default(500);
            $table->integer('critical_threshold_km')->default(100);
            $table->enum('status', ['healthy', 'warning', 'critical', 'overdue'])->default('healthy');
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['vehicle_id', 'status']);
            $table->index('category');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('vehicle_components');
    }
};
