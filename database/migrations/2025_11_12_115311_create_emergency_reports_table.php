<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('emergency_reports', function (Blueprint $table) {
            $table->id();

            // Menghubungkan ke driver yang melapor
            $table->foreignId('driver_id')->constrained('drivers');

            // Menghubungkan ke mobil yang bermasalah
            $table->foreignId('vehicle_id')->constrained('vehicles');

            // Kolom A: Timestamp
            $table->dateTime('timestamp');

            // Kolom D: Lokasi
            $table->string('gps_location');

            // Kolom E: Deskripsi Masalah
            $table->text('description');

            // Kolom F: Link Foto Bukti (boleh kosong)
            $table->string('proof_photo_path')->nullable();

            // Kolom standar Laravel
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('emergency_reports');
    }
};