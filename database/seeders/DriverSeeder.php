<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;   // <-- Import DB
use Illuminate\Support\Facades\Hash; // <-- Import Hash
use App\Models\Driver;                 // <-- Import Model Driver

class DriverSeeder extends Seeder
{
    /**
     * Jalankan seeder.
     */
    public function run(): void
    {
        // Kosongkan tabel drivers dulu agar tidak ada data ganda
        // Nonaktifkan pengecekan foreign key sementara
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        Driver::truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        $drivers = [
            // Data Daftar Driver
            ['nik' => '12345', 'nama' => 'Muhammad Rohan Sayyid', 'pass' => 'han123'],
            ['nik' => '67890', 'nama' => 'han', 'pass' => 'admin123'],
            ['nik' => '181200005', 'nama' => 'SONI', 'pass' => 'admin123'],
            ['nik' => '210300001', 'nama' => 'DARMANSYAH N.O', 'pass' => 'admin123'],
            ['nik' => '230900026', 'nama' => 'FARHAN FADILLAH', 'pass' => 'admin123'],
            ['nik' => '181200003', 'nama' => 'JAENUDIN', 'pass' => 'admin123'],
            ['nik' => '140300001', 'nama' => 'AHMAD WAHYUDIN', 'pass' => 'admin123'],
            ['nik' => '150500001', 'nama' => 'ANDRI', 'pass' => 'admin123'],
            ['nik' => '161000001', 'nama' => 'IRWAN SUNARYA', 'pass' => 'admin123'],
            ['nik' => '180700001', 'nama' => 'RANDI NUR ERYANTO', 'pass' => 'admin123'],
            ['nik' => '181200004', 'nama' => 'RIZKY WAHYU UTOMO', 'pass' => 'admin123'],
            ['nik' => '181200006', 'nama' => 'SIGIT PURNOMO', 'pass' => 'admin123'],
            ['nik' => '190600005', 'nama' => 'NURUL AL ARIEF', 'pass' => 'admin123'],
            ['nik' => '200100001', 'nama' => 'NANANG JUNAiDI', 'pass' => 'admin123'],
            ['nik' => '210700006', 'nama' => 'MUJIBBUDIN', 'pass' => 'admin123'], // <-- Tambahan baru
            ['nik' => '220400010', 'nama' => 'ROMI ROMADHONA', 'pass' => 'admin123'], // <-- Tambahan baru
            ['nik' => '221100039', 'nama' => 'MUHAMD FERI SUHENDRI', 'pass' => 'admin123'],
            ['nik' => '230100001', 'nama' => 'ALI USMAN', 'pass' => 'admin123'],
            ['nik' => '230200004', 'nama' => 'M.KADRI', 'pass' => 'admin123'],
            ['nik' => '230200006', 'nama' => 'GUNAWAN', 'pass' => 'admin123'], // <-- Tambahan baru
            ['nik' => '230300008', 'nama' => 'NURJAYA', 'pass' => 'admin123'], // <-- Tambahan baru
            ['nik' => '230700023', 'nama' => 'KOMARUDIN', 'pass' => 'admin123'], // <-- Tambahan baru
            ['nik' => '230900002', 'nama' => 'RIZKI ANDIKA NUR PRATAMA', 'pass' => 'admin123'], // <-- Tambahan baru
            ['nik' => '181200002', 'nama' => 'WARIYO', 'pass' => 'admin123'], // <-- Tambahan baru
            ['nik' => '210900009', 'nama' => 'ABRAHAM', 'pass' => 'admin123'], // <-- Tambahan baru
            ['nik' => '221000032', 'nama' => 'DOIN JAENUDIN', 'pass' => 'admin123'], // <-- Tambahan baru
            ['nik' => '190500001', 'nama' => 'RIZAL FAHMI SURYADI', 'pass' => 'admin123'], // <-- Tambahan baru
            ['nik' => '190600004', 'nama' => 'KUSNO', 'pass' => 'admin123'], // <-- Tambahan baru
            ['nik' => '210500003', 'nama' => 'CECEP MUSLIHAT', 'pass' => 'admin123'], // <-- Tambahan baru
            ['nik' => '140300002', 'nama' => 'TOMMI ', 'pass' => 'admin123'], // <-- Tambahan baru
            ['nik' => '200200002', 'nama' => 'TOHA SUPRIATMAN', 'pass' => 'admin123'], // <-- Tambahan baru
            ['nik' => '220400007', 'nama' => 'TATA RUKMANA', 'pass' => 'admin123'], // <-- Tambahan baru
            ['nik' => '220400008', 'nama' => 'ROCHMAT SETIAWAN', 'pass' => 'admin123'], // <-- Tambahan baru
            ['nik' => '220800021', 'nama' => 'YUDI APRILIYANTO', 'pass' => 'admin123'], // <-- Tambahan baru
            ['nik' => '221000031', 'nama' => 'ANDRE ARDIANSYAH', 'pass' => 'admin123'], // <-- Tambahan baru
            ['nik' => '221100036', 'nama' => 'ASENG PERMANA', 'pass' => 'admin123'], // <-- Tambahan baru
            ['nik' => '220300005', 'nama' => 'JAIMAN', 'pass' => 'admin123'], // <-- Tambahan baru
        ];

        // Looping dan masukkan ke database
        foreach ($drivers as $driver) {
            Driver::create([
                'driver_id_nik' => $driver['nik'],
                'full_name' => $driver['nama'],
                'password' => Hash::make($driver['pass']) // <-- Di-enkripsi di sini
            ]);
        }
    }
}