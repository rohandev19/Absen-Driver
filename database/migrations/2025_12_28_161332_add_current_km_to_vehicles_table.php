<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::table('vehicles', function (Blueprint $table) {
            // PERBAIKAN: Ganti 'status' menjadi 'type'
            $table->bigInteger('current_km')->default(0)->nullable()->after('type');
        });
    }

    public function down()
    {
        Schema::table('vehicles', function (Blueprint $table) {
            $table->dropColumn('current_km');
        });
    }
};