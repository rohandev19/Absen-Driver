<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Vehicle; // <-- Import Model Vehicle

class VehicleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Data lengkap dari Google Sheet 'Daftar Mobil'
        $vehicles = [
            ['plat' => 'B 1', 'jenis' => 'Porsche'],
            ['plat' => 'B9094BXC', 'jenis' => 'BLIN VAN'],
            ['plat' => 'B9613PCZ', 'jenis' => 'BLIN VAN'],
            ['plat' => 'B9167BXC', 'jenis' => 'BLIN VAN'],
            ['plat' => 'B9577TRU', 'jenis' => 'CDE'],
            ['plat' => 'B9591TXS', 'jenis' => 'CDE'],
            ['plat' => 'B9274UCE', 'jenis' => 'CDE'],
            ['plat' => 'B9331FCK', 'jenis' => 'BLINDVAN'],
            ['plat' => 'B9098FCK', 'jenis' => 'BLINDVAN'],
            ['plat' => 'B9728FCJ', 'jenis' => 'BLINDVAN'],
            ['plat' => 'B9262FCK', 'jenis' => 'BLINDVAN'],
            ['plat' => 'H9129SI', 'jenis' => 'BLINDVAN'],
            ['plat' => 'B9590TXS', 'jenis' => 'CDE'],
            ['plat' => 'B9675BCN', 'jenis' => 'BLINDVAN'],
            ['plat' => 'B4480FWG', 'jenis' => 'MOTOR BIKE'],
            ['plat' => 'B9676BCN', 'jenis' => 'BLINDVAN'],
            ['plat' => 'B9707BXU', 'jenis' => 'GRANDMAX BOX'],
            ['plat' => 'B9632FCK', 'jenis' => 'BLINDVAN'],
            ['plat' => 'B9463FCK', 'jenis' => 'BLINDVAN'],
            ['plat' => 'B9491FCK', 'jenis' => 'BLINDVAN'],
            ['plat' => 'B9941FCJ', 'jenis' => 'BLINDVAN'],
            ['plat' => 'B9581FCJ', 'jenis' => 'BLINDVAN'],
            ['plat' => 'B9919UCZ', 'jenis' => 'CDE'],
            ['plat' => 'B9841VRU', 'jenis' => 'CDE'],
            ['plat' => 'B9458FCK', 'jenis' => 'BLIN VAN'],
            ['plat' => 'B9964BXA', 'jenis' => 'BLIN VAN'],
            ['plat' => 'B9270BCP', 'jenis' => 'BLIN VAN'],
            ['plat' => 'B9786UXA', 'jenis' => 'BLIN VAN'],
            ['plat' => 'B9784UCZ', 'jenis' => 'CDE'],
            ['plat' => 'B9885UCZ', 'jenis' => 'CDE'],
            ['plat' => 'B9899UCZ', 'jenis' => 'CDE'],
            ['plat' => 'B9774UCZ', 'jenis' => 'CDE'],
            ['plat' => 'B9897UCZ', 'jenis' => 'CDE'],
            ['plat' => 'B9776UCZ', 'jenis' => 'CDE'],
            ['plat' => 'B9174HBC', 'jenis' => 'CDD'],
            ['plat' => 'B9589NCG', 'jenis' => 'CDD'],
            ['plat' => 'H8353CQ', 'jenis' => 'CDD'],
            ['plat' => 'B9363UCW', 'jenis' => 'CDD'],
            ['plat' => 'B9617UCY', 'jenis' => 'CDD'],
            ['plat' => 'B9830FCK', 'jenis' => 'CDD'],
            ['plat' => 'B9148SXU', 'jenis' => 'CDD'],
            ['plat' => 'B9847PCI', 'jenis' => 'CDD'],
            ['plat' => 'B9879UXA', 'jenis' => 'CDD'],
            ['plat' => 'B9264BXU', 'jenis' => 'CDD'],
            ['plat' => 'B9415PXT', 'jenis' => 'CDE'],
            ['plat' => 'B9376PXT', 'jenis' => 'CDE'],
            ['plat' => 'B9643BXT', 'jenis' => 'CDE'],
            ['plat' => 'B9671PXR', 'jenis' => 'CDE'],
        ];

        // Gunakan updateOrCreate()
        // Ini artinya: "Cari mobil berdasarkan plat. 
        // Jika ketemu, UPDATE jenisnya. 
        // Jika tidak ketemu, CREATE mobil baru."
        foreach ($vehicles as $vehicle) {
            Vehicle::updateOrCreate(
                ['plate_number' => $vehicle['plat']], // Kunci pencarian
                ['type' => $vehicle['jenis']]         // Data yang di-update/create
            );
        }
    }
}