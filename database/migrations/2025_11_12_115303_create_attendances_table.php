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
        Schema::create('attendances', function (Blueprint $table) {
            $table->id();

            // Ini adalah "Kunci Asing"
            // Menghubungkan baris ini ke tabel 'drivers'
            $table->foreignId('driver_id')->constrained('drivers');

            // Menghubungkan baris ini ke tabel 'vehicles' (mobil)
            $table->foreignId('vehicle_id')->constrained('vehicles');

            // --- DATA CHECK-IN (dari Tab Absensi) ---

            // Kolom A: Timestamp Masuk
            $table->dateTime('time_in');

            // Kolom F: Lokasi Masuk
            $table->string('gps_location_in');

            // Kolom H: Link Foto Selfie
            $table->string('selfie_photo_path');

            // Kolom I: Link Speedometer Awal
            $table->string('speedo_photo_awal_path');

            // Kolom K: Link Foto Kondisi 1 (boleh kosong)
            $table->string('condition_photo_1_path')->nullable();

            // Kolom L: Link Foto Kondisi 2 (boleh kosong)
            $table->string('condition_photo_2_path')->nullable();

            // Kolom Q: Speedo Manual (Awal)
            $table->integer('speedo_awal');


            // --- DATA CHECK-OUT (dari Tab Absensi) ---

            // Kolom B: Timestamp Keluar (boleh kosong, karena driver mungkin masih bertugas)
            $table->dateTime('time_out')->nullable();

            // Kolom J: Link Speedometer Akhir (boleh kosong)
            $table->string('speedo_photo_akhir_path')->nullable();

            // Kolom M: Catatan Pulang (boleh kosong)
            $table->text('catatan')->nullable();

            // Kolom N: Pengecekan Ban (boleh kosong)
            $table->string('check_ban')->nullable();

            // Kolom O: Pengecekan Lampu (boleh kosong)
            $table->string('check_lampu')->nullable();

            // Kolom P: Pengecekan Rem (boleh kosong)
            $table->string('check_rem')->nullable();

            // Kolom R: Speedo Manual (Akhir) (boleh kosong)
            $table->integer('speedo_akhir')->nullable();

            // Kolom standar Laravel
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('attendances');
    }
};