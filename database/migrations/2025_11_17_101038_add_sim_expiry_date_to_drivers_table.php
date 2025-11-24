<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::table('drivers', function (Blueprint $table) {
            // Menambahkan kolom tanggal kadaluwarsa SIM setelah kolom NIK
            $table->date('sim_expiry_date')->nullable()->after('driver_id_nik');
        });
    }

    public function down()
    {
        Schema::table('drivers', function (Blueprint $table) {
            $table->dropColumn('sim_expiry_date');
        });
    }
};
