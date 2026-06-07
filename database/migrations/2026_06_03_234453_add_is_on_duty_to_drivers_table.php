<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Add is_on_duty column to drivers table
        Schema::table('drivers', function (Blueprint $table) {
            $table->boolean('is_on_duty')->default(false)->after('fcm_token');
            $table->index('is_on_duty', 'idx_drivers_is_on_duty');
        });

        // Set initial values based on existing active attendances
        // Set is_on_duty = true WHERE driver has attendance with time_out IS NULL
        // Use database-agnostic query that works for both MySQL and SQLite
        DB::table('drivers')
            ->whereExists(function ($query) {
                $query->select(DB::raw(1))
                      ->from('attendances')
                      ->whereColumn('attendances.driver_id', 'drivers.id')
                      ->whereNull('attendances.time_out');
            })
            ->update(['is_on_duty' => true]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('drivers', function (Blueprint $table) {
            $table->dropIndex('idx_drivers_is_on_duty');
            $table->dropColumn('is_on_duty');
        });
    }
};
