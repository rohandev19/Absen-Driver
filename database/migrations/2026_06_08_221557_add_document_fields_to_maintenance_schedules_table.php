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
        Schema::table('maintenance_schedules', function (Blueprint $table) {
            $table->string('receipt_photo_path')->nullable()->after('notes');
            $table->string('odometer_photo_path')->nullable()->after('receipt_photo_path');
            $table->string('finance_pdf_path')->nullable()->after('odometer_photo_path');
            $table->string('admin_signature_path')->nullable()->after('finance_pdf_path');
            $table->string('admin_signer_name')->nullable()->after('admin_signature_path');
            $table->string('admin_signer_role')->nullable()->after('admin_signer_name');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('maintenance_schedules', function (Blueprint $table) {
            $table->dropColumn([
                'receipt_photo_path',
                'odometer_photo_path',
                'finance_pdf_path',
                'admin_signature_path',
                'admin_signer_name',
                'admin_signer_role'
            ]);
        });
    }
};
