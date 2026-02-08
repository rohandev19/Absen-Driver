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
        // Tambah kolom ke tabel drivers
        Schema::table('drivers', function (Blueprint $table) {
            $table->foreignId('project_id')->nullable()->after('id')->constrained('projects')->onDelete('set null');
        });

        // Tambah kolom ke tabel vehicles
        Schema::table('vehicles', function (Blueprint $table) {
            $table->foreignId('project_id')->nullable()->after('id')->constrained('projects')->onDelete('set null');
        });
    }

    public function down()
    {
        Schema::table('drivers', function (Blueprint $table) {
            $table->dropForeign(['project_id']);
            $table->dropColumn('project_id');
        });
        Schema::table('vehicles', function (Blueprint $table) {
            $table->dropForeign(['project_id']);
            $table->dropColumn('project_id');
        });
    }
};
