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
        $afterColumn = Schema::hasColumn('drivers', 'foto_ktp')
            ? 'foto_ktp'
            : (Schema::hasColumn('drivers', 'full_name') ? 'full_name' : null);

        Schema::table('drivers', function (Blueprint $table) use ($afterColumn) {
            if (!Schema::hasColumn('drivers', 'profile_photo')) {
                $column = $table->string('profile_photo')->nullable();

                if ($afterColumn !== null) {
                    $column->after($afterColumn);
                }
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('drivers', function (Blueprint $table) {
            if (Schema::hasColumn('drivers', 'profile_photo')) {
                $table->dropColumn('profile_photo');
            }
        });
    }
};
