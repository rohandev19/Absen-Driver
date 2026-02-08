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
            // Menambah kolom nik_ktp setelah id
            $table->string('nik_ktp', 20)->nullable()->after('id');
        });
    }

    public function down()
    {
        Schema::table('drivers', function (Blueprint $table) {
            $table->dropColumn('nik_ktp');
        });
    }
};
