<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('emergency_reports', function (Blueprint $table) {
            if (!Schema::hasColumn('emergency_reports', 'follow_up_status')) {
                $table->string('follow_up_status', 30)->default('new')->after('proof_photo_path');
            }

            if (!Schema::hasColumn('emergency_reports', 'follow_up_notes')) {
                $table->text('follow_up_notes')->nullable()->after('follow_up_status');
            }

            if (!Schema::hasColumn('emergency_reports', 'service_report_id')) {
                $table->foreignId('service_report_id')
                    ->nullable()
                    ->after('follow_up_notes')
                    ->constrained('service_reports')
                    ->nullOnDelete();
            }

            if (!Schema::hasColumn('emergency_reports', 'processed_by')) {
                $table->foreignId('processed_by')
                    ->nullable()
                    ->after('service_report_id')
                    ->constrained('users')
                    ->nullOnDelete();
            }

            if (!Schema::hasColumn('emergency_reports', 'processed_at')) {
                $table->timestamp('processed_at')->nullable()->after('processed_by');
            }
        });
    }

    public function down(): void
    {
        Schema::table('emergency_reports', function (Blueprint $table) {
            foreach (['service_report_id', 'processed_by'] as $column) {
                if (Schema::hasColumn('emergency_reports', $column)) {
                    $table->dropConstrainedForeignId($column);
                }
            }

            foreach (['processed_at', 'follow_up_notes', 'follow_up_status'] as $column) {
                if (Schema::hasColumn('emergency_reports', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
