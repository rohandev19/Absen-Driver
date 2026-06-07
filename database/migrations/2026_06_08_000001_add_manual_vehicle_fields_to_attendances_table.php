<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('attendances', function (Blueprint $table) {
            $table->string('vehicle_entry_method', 20)->default('qr')->after('vehicle_id');
            $table->string('manual_vehicle_plate')->nullable()->after('vehicle_entry_method');
            $table->string('manual_vehicle_reason')->nullable()->after('manual_vehicle_plate');
            $table->string('manual_vehicle_photo_path')->nullable()->after('manual_vehicle_reason');
            $table->string('vehicle_verification_status', 30)->default('verified')->after('manual_vehicle_photo_path');
        });
    }

    public function down(): void
    {
        Schema::table('attendances', function (Blueprint $table) {
            $table->dropColumn([
                'vehicle_entry_method',
                'manual_vehicle_plate',
                'manual_vehicle_reason',
                'manual_vehicle_photo_path',
                'vehicle_verification_status',
            ]);
        });
    }
};
