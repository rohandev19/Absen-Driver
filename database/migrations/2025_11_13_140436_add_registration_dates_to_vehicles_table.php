<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     * (Perintah "TAMBAHKAN")
     */
    public function up(): void
    {
        Schema::table('vehicles', function (Blueprint $table) {
            // Kolom untuk tanggal STNK
            // 'nullable' berarti boleh kosong
            // 'after' berarti ditaruh setelah kolom last_service_km
            $table->date('pajak_stnk_berlaku_sampai')->nullable()->after('last_service_km');

            // Kolom untuk tanggal KIR
            $table->date('kir_berlaku_sampai')->nullable()->after('pajak_stnk_berlaku_sampai');
        });
    }

    /**
     * Reverse the migrations.
     * (Perintah "HAPUS TAMBAHAN")
     */
    public function down(): void
    {
        Schema::table('vehicles', function (Blueprint $table) {
            $table->dropColumn('pajak_stnk_berlaku_sampai');
            $table->dropColumn('kir_berlaku_sampai');
        });
    }
};