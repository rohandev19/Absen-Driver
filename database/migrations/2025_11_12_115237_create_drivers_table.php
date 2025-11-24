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
        Schema::create('drivers', function (Blueprint $table) {
            // Ini adalah ID internal baru untuk database (Primary Key)
            $table->id();

            // Ini adalah pengganti Kolom A (ID Driver)
            // Kita buat 'unique' agar tidak ada NIK yang ganda.
            $table->string('driver_id_nik')->unique();

            // Ini adalah pengganti Kolom B (Nama Driver)
            $table->string('full_name');

            // Ini adalah pengganti Kolom C (Password)
            // Nantinya, kita akan simpan password ter-enkripsi di sini.
            $table->string('password');

            // Ini kolom standar Laravel untuk login
            $table->rememberToken();

            // Ini kolom standar Laravel (created_at & updated_at)
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * Perintah jika kita ingin MENGHAPUS tabel ini.
     */
    public function down(): void
    {
        Schema::dropIfExists('drivers');
    }
};