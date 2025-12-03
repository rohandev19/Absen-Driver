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
        // 1. Optimasi Tabel Absensi (Paling Penting)
        Schema::table('attendances', function (Blueprint $table) {
            // Indexing Foreign Keys (Biasanya Laravel sudah otomatis, tapi untuk memastikan)
            // Cek dulu, kalau belum ada baru tambah. Tapi aman ditimpa index.
            $table->index('driver_id'); 
            $table->index('vehicle_id');
            
            // Indexing Kolom Tanggal (Sering dipakai range filter)
            $table->index('time_in');
            $table->index('time_out');
        });

        // 2. Optimasi Tabel Driver (Untuk Login Cepat)
        Schema::table('drivers', function (Blueprint $table) {
            $table->index('driver_id_nik');
            $table->index('full_name'); // Agar list dropdown driver cepat
        });

        // 3. Optimasi Tabel Kendaraan (Untuk Pencarian Plat)
        Schema::table('vehicles', function (Blueprint $table) {
            $table->index('plate_number');
        });

        // 4. Optimasi Laporan Darurat
        Schema::table('emergency_reports', function (Blueprint $table) {
            $table->index('timestamp');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Hapus index jika rollback (Wajib ada urutan array)
        Schema::table('attendances', function (Blueprint $table) {
            $table->dropIndex(['driver_id']);
            $table->dropIndex(['vehicle_id']);
            $table->dropIndex(['time_in']);
            $table->dropIndex(['time_out']);
        });

        Schema::table('drivers', function (Blueprint $table) {
            $table->dropIndex(['driver_id_nik']);
            $table->dropIndex(['full_name']);
        });

        Schema::table('vehicles', function (Blueprint $table) {
            $table->dropIndex(['plate_number']);
        });

        Schema::table('emergency_reports', function (Blueprint $table) {
            $table->dropIndex(['timestamp']);
        });
    }
};