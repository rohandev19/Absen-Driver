<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations. (Perintah "TAMBAHKAN")
     */
    public function up(): void
    {
        Schema::table('vehicles', function (Blueprint $table) {
            // Kolom untuk interval (setiap berapa KM)
            // Kita set default 10000 (artinya setiap 10.000 KM)
            $table->integer('service_interval_km')->default(10000)->after('type');

            // Kolom untuk KM saat servis terakhir
            $table->integer('last_service_km')->default(0)->after('service_interval_km');
        });
    }

    /**
     * Reverse the migrations. (Perintah "HAPUS TAMBAHAN")
     */
    public function down(): void
    {
        Schema::table('vehicles', function (Blueprint $table) {
            $table->dropColumn('service_interval_km');
            $table->dropColumn('last_service_km');
        });
    }
};