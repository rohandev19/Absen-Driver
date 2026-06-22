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
        Schema::table('drivers', function (Blueprint $table) {
            if (!Schema::hasColumn('drivers', 'qr_code_path')) {
                $table->string('qr_code_path')->nullable()->after('profile_photo');
            }

            if (!Schema::hasColumn('drivers', 'qr_code_identifier')) {
                $table->string('qr_code_identifier')->unique()->nullable()->after('qr_code_path');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('drivers', function (Blueprint $table) {
            $columns = array_filter([
                Schema::hasColumn('drivers', 'qr_code_path') ? 'qr_code_path' : null,
                Schema::hasColumn('drivers', 'qr_code_identifier') ? 'qr_code_identifier' : null,
            ]);

            if ($columns !== []) {
                $table->dropColumn($columns);
            }
        });
    }
};
