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
        Schema::table('attendances', function (Blueprint $table) {
            // Add offline recovery metadata columns
            $table->boolean('is_offline_recovery')->default(false)->after('time_out');
            $table->dateTime('recovery_timestamp')->nullable()->after('is_offline_recovery');
            $table->string('offline_entry_id', 255)->nullable()->after('recovery_timestamp');
            $table->boolean('is_late_submission')->default(false)->after('offline_entry_id');
            $table->string('gps_location_out', 255)->nullable()->after('is_late_submission');

            // Add indexes for offline recovery queries
            $table->index('is_offline_recovery', 'idx_attendances_offline_recovery');
            $table->index('offline_entry_id', 'idx_attendances_offline_entry');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('attendances', function (Blueprint $table) {
            // Drop indexes first
            $table->dropIndex('idx_attendances_offline_recovery');
            $table->dropIndex('idx_attendances_offline_entry');

            // Drop columns
            $table->dropColumn([
                'is_offline_recovery',
                'recovery_timestamp',
                'offline_entry_id',
                'is_late_submission',
                'gps_location_out'
            ]);
        });
    }
};
