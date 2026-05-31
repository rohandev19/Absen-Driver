<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * Standardize user roles for service workflow:
     * - master: full access (existing admin users)
     * - service_admin: access to Unit Management + Maintenance + Service Reports
     * - customer: access to Approve Service only
     * - viewer: legacy role, retained for compatibility
     */
    public function up(): void
    {
        // Data fix: rename existing 'admin' role to 'master'
        DB::table('users')
            ->where('role', 'admin')
            ->update(['role' => 'master']);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Revert master back to admin
        DB::table('users')
            ->where('role', 'master')
            ->update(['role' => 'admin']);
    }
};
