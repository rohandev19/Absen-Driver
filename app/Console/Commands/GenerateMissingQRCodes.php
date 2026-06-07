<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Driver;
use App\Models\Vehicle;
use App\Services\QRCodeService;
use Illuminate\Support\Facades\Log;

class GenerateMissingQRCodes extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'qrcode:generate-missing';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate QR codes for existing drivers and vehicles without QR codes';

    protected QRCodeService $qrCodeService;

    /**
     * Create a new command instance.
     */
    public function __construct(QRCodeService $qrCodeService)
    {
        parent::__construct();
        $this->qrCodeService = $qrCodeService;
    }

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Starting QR Code generation process...');

        $driverSuccess = $this->generateForDrivers();
        $vehicleSuccess = $this->generateForVehicles();

        if ($driverSuccess && $vehicleSuccess) {
            $this->info('Process completed successfully.');
            return Command::SUCCESS;
        } else {
            $this->error('Process completed with some errors. Please check the logs.');
            return Command::FAILURE;
        }
    }

    private function generateForDrivers(): bool
    {
        $drivers = Driver::whereNull('qr_code_path')->get();
        $count = $drivers->count();
        
        $this->info("Found {$count} drivers without QR codes.");

        if ($count === 0) return true;

        $bar = $this->output->createProgressBar($count);
        $bar->start();

        $successCount = 0;
        $failCount = 0;

        foreach ($drivers as $driver) {
            try {
                $success = $this->qrCodeService->generateForDriver($driver);
                if ($success) {
                    $successCount++;
                } else {
                    $failCount++;
                }
            } catch (\Exception $e) {
                Log::error("Bulk generate failed for Driver ID {$driver->id}: " . $e->getMessage());
                $failCount++;
            }
            $bar->advance();
        }

        $bar->finish();
        $this->newLine();
        $this->info("Generated {$successCount} driver QR codes.");
        
        if ($failCount > 0) {
            $this->error("Failed to generate {$failCount} driver QR codes.");
            return false;
        }

        return true;
    }

    private function generateForVehicles(): bool
    {
        $vehicles = Vehicle::whereNull('qr_code_path')->get();
        $count = $vehicles->count();
        
        $this->info("Found {$count} vehicles without QR codes.");

        if ($count === 0) return true;

        $bar = $this->output->createProgressBar($count);
        $bar->start();

        $successCount = 0;
        $failCount = 0;

        foreach ($vehicles as $vehicle) {
            try {
                $success = $this->qrCodeService->generateForVehicle($vehicle);
                if ($success) {
                    $successCount++;
                } else {
                    $failCount++;
                }
            } catch (\Exception $e) {
                Log::error("Bulk generate failed for Vehicle ID {$vehicle->id}: " . $e->getMessage());
                $failCount++;
            }
            $bar->advance();
        }

        $bar->finish();
        $this->newLine();
        $this->info("Generated {$successCount} vehicle QR codes.");
        
        if ($failCount > 0) {
            $this->error("Failed to generate {$failCount} vehicle QR codes.");
            return false;
        }

        return true;
    }
}
