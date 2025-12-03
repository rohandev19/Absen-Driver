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
        Schema::create('maintenance_logs', function (Blueprint $table) {
            $table->id();
            // Relasi ke tabel vehicles
            $table->foreignId('vehicle_id')->constrained()->cascadeOnDelete();

            // Data Servis
            $table->date('service_date');           // Tanggal servis dilakukan
            $table->integer('km_at_service');       // KM saat servis (Odometer)
            $table->text('description')->nullable(); // Keterangan (Ganti Oli, Ganti Ban, dll)
            $table->string('workshop_name')->nullable(); // Nama Bengkel (Opsional)

            // Siapa yang mencatat (Admin)
            $table->foreignId('recorded_by_user_id')->nullable()->constrained('users')->nullOnDelete();

            $table->timestamps();
        });
    }

};
