<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('service_reports', function (Blueprint $table) {
            $table->string('receipt_photo_path')->nullable()->change();

            if (!Schema::hasColumn('service_reports', 'report_source')) {
                $table->string('report_source', 40)->default('driver_damage')->after('ticket_number');
            }

            if (!Schema::hasColumn('service_reports', 'location_source')) {
                $table->string('location_source', 20)->nullable()->after('gps_location');
            }

            if (!Schema::hasColumn('service_reports', 'service_completed_at')) {
                $table->timestamp('service_completed_at')->nullable()->after('unit_status_after_service');
            }

            if (!Schema::hasColumn('service_reports', 'completed_by_driver_id')) {
                $table->foreignId('completed_by_driver_id')
                    ->nullable()
                    ->after('service_completed_at')
                    ->constrained('drivers')
                    ->nullOnDelete();
            }
        });
    }

    public function down(): void
    {
        Schema::table('service_reports', function (Blueprint $table) {
            if (Schema::hasColumn('service_reports', 'completed_by_driver_id')) {
                $table->dropConstrainedForeignId('completed_by_driver_id');
            }

            foreach (['service_completed_at', 'location_source', 'report_source'] as $column) {
                if (Schema::hasColumn('service_reports', $column)) {
                    $table->dropColumn($column);
                }
            }

            $table->string('receipt_photo_path')->nullable(false)->change();
        });
    }
};
