<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (DB::getDriverName() === 'sqlite') {
            return;
        }

        Schema::table('service_reports', function (Blueprint $table) {
            $table->string('status')->default('pending_admin')->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (DB::getDriverName() === 'sqlite') {
            return;
        }

        Schema::table('service_reports', function (Blueprint $table) {
            $table->enum('status', [
                'pending',
                'pending_admin',
                'approved',
                'rejected',
                'completed',
            ])->default('pending')->change();
        });
    }
};
