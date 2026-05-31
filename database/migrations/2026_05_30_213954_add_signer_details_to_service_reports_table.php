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
        Schema::table('service_reports', function (Blueprint $table) {
            $table->string('admin_signer_name')->nullable()->after('admin_signature_path');
            $table->string('admin_signer_role')->nullable()->after('admin_signer_name');
            $table->string('customer_signer_name')->nullable()->after('customer_signature_path');
            $table->string('customer_signer_role')->nullable()->after('customer_signer_name');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('service_reports', function (Blueprint $table) {
            $table->dropColumn(['admin_signer_name', 'admin_signer_role', 'customer_signer_name', 'customer_signer_role']);
        });
    }
};
