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
        Schema::create('audit_histories', function (Blueprint $table) {
            $table->id();
            $table->string('report_id')->unique()->comment('Unique audit report identifier');
            $table->enum('type', ['full', 'security', 'performance', 'database', 'code_quality'])
                  ->default('full')
                  ->comment('Type of audit performed');
            $table->enum('status', ['running', 'completed', 'failed'])
                  ->default('running')
                  ->comment('Audit execution status');
            $table->unsignedInteger('total_findings')->default(0);
            $table->unsignedInteger('critical_count')->default(0);
            $table->unsignedInteger('high_count')->default(0);
            $table->unsignedInteger('medium_count')->default(0);
            $table->unsignedInteger('low_count')->default(0);
            $table->unsignedInteger('info_count')->default(0);
            $table->json('summary')->nullable()->comment('Full summary statistics');
            $table->json('report_data')->nullable()->comment('Complete audit report JSON');
            $table->string('report_path')->nullable()->comment('Path to generated report file');
            $table->string('format')->default('json')->comment('Report output format');
            $table->float('execution_time_seconds')->nullable()->comment('Total execution time');
            $table->string('triggered_by')->default('manual')->comment('manual, scheduled, or ci');
            $table->text('error_message')->nullable()->comment('Error message if audit failed');
            $table->timestamps();

            $table->index('type');
            $table->index('status');
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('audit_histories');
    }
};
