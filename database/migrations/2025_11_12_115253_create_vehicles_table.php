<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     *
     * Perintah untuk MEMBUAT tabel.
     */
    public function up(): void
    {
        Schema::create('vehicles', function (Blueprint $table) {
            // ID internal database
            $table->id();

            // Pengganti Kolom A (Plat Nomor)
            // 'unique' agar plat nomor tidak ada yang ganda.
            $table->string('plate_number')->unique();

            // Pengganti Kolom B (Jenis Mobil)
            // 'nullable' berarti boleh dikosongkan.
            $table->string('type')->nullable();

            // Status kendaraan (e.g., Aktif, Servis, Rusak)
            $table->string('status')->default('Aktif');

            // Kolom standar Laravel
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * Perintah untuk MENGHAPUS tabel.
     */
    public function down(): void
    {
        Schema::dropIfExists('vehicles');
    }
};