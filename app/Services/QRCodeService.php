<?php

namespace App\Services;

use SimpleSoftwareIO\QrCode\Facades\QrCode;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Illuminate\Contracts\Encryption\DecryptException;
use App\Models\Driver;
use App\Models\Vehicle;
use App\Exceptions\InvalidQRCodeException;
use Carbon\Carbon;

class QRCodeService
{
    const SIZE = 300;
    const ERROR_CORRECTION = 'H';
    const EXPIRY_YEARS = 1;

    /**
     * Create payload for driver
     */
    protected function createDriverPayload(Driver $driver): array
    {
        return [
            'driver_id' => $driver->id,
            'driver_id_nik' => $driver->driver_id_nik,
            'full_name' => $driver->full_name,
            'project_id' => $driver->project_id,
            'timestamp' => now()->timestamp,
        ];
    }

    /**
     * Create payload for vehicle
     */
    protected function createVehiclePayload(Vehicle $vehicle): array
    {
        return [
            'vehicle_id' => $vehicle->id,
            'plate_number' => $vehicle->plate_number,
            'type' => $vehicle->type,
            'project_id' => $vehicle->project_id,
            'timestamp' => now()->timestamp,
        ];
    }

    /**
     * Generate QR Code for a Driver
     */
    public function generateForDriver(Driver $driver): bool
    {
        try {
            $identifier = "DRV-{$driver->driver_id_nik}";
            $payload = $this->createDriverPayload($driver);
            $encryptedData = Crypt::encryptString(json_encode($payload));
            $path = "qrcodes/drivers/{$driver->id}.svg";

            $qrString = "{$identifier}|{$encryptedData}";
            $this->generateAndSaveQRImage($qrString, $path);

            $driver->qr_code_identifier = $identifier;
            $driver->qr_code_path = $path;
            $driver->saveQuietly();

            return true;
        } catch (\Exception $e) {
            Log::error("Failed to generate QR code for Driver ID {$driver->id}: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Generate QR Code for a Vehicle
     */
    public function generateForVehicle(Vehicle $vehicle): bool
    {
        try {
            $identifier = "CAR-{$vehicle->plate_number}";
            $payload = $this->createVehiclePayload($vehicle);
            $encryptedData = Crypt::encryptString(json_encode($payload));
            $path = "qrcodes/vehicles/{$vehicle->id}.svg";

            $qrString = "{$identifier}|{$encryptedData}";
            $this->generateAndSaveQRImage($qrString, $path);

            $vehicle->qr_code_identifier = $identifier;
            $vehicle->qr_code_path = $path;
            $vehicle->saveQuietly();

            return true;
        } catch (\Exception $e) {
            Log::error("Failed to generate QR code for Vehicle ID {$vehicle->id}: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Generate and save the QR code image
     */
    protected function generateAndSaveQRImage(string $data, string $path): void
    {
        $directory = dirname($path);
        
        if (!Storage::disk('public')->exists($directory)) {
            Storage::disk('public')->makeDirectory($directory);
        }

        $image = QrCode::format('svg')
            ->size(self::SIZE)
            ->errorCorrection(self::ERROR_CORRECTION)
            ->generate($data);

        Storage::disk('public')->put($path, $image);
    }

    /**
     * Validate the QR prefix
     */
    public function validateQRFormat(string $identifier, string $expectedPrefix): void
    {
        if (!str_starts_with($identifier, $expectedPrefix)) {
            throw new InvalidQRCodeException("Format QR code tidak sesuai", 400);
        }
    }

    /**
     * Decrypt and validate the payload
     */
    public function decryptAndValidatePayload(string $encryptedData): array
    {
        try {
            $decrypted = Crypt::decryptString($encryptedData);
            $payload = json_decode($decrypted, true);

            if (!$payload || !isset($payload['timestamp'])) {
                throw new InvalidQRCodeException("QR code tidak valid atau telah dimodifikasi", 400);
            }

            $generatedAt = Carbon::createFromTimestamp($payload['timestamp']);
            if ($generatedAt->diffInYears(now()) >= self::EXPIRY_YEARS) {
                throw new InvalidQRCodeException("QR code sudah kadaluarsa, silakan generate ulang", 400);
            }

            return $payload;
        } catch (DecryptException $e) {
            throw new InvalidQRCodeException("QR code tidak valid atau telah dimodifikasi", 400);
        }
    }

    /**
     * Verify a driver QR code
     */
    public function verifyDriverQRCode(string $identifier, string $encryptedData): array
    {
        $this->validateQRFormat($identifier, 'DRV-');
        $payload = $this->decryptAndValidatePayload($encryptedData);

        $driver = Driver::with('project')->where('qr_code_identifier', $identifier)->first();
        if (!$driver) {
            throw new InvalidQRCodeException("Driver QR code tidak ditemukan", 404);
        }

        return [
            'id' => $driver->id,
            'driver_id_nik' => $driver->driver_id_nik,
            'full_name' => $driver->full_name,
            'project_name' => $driver->project?->name,
            'is_on_duty' => $driver->is_on_duty,
        ];
    }

    /**
     * Verify a vehicle QR code
     */
    public function verifyVehicleQRCode(string $identifier, string $encryptedData): array
    {
        $this->validateQRFormat($identifier, 'CAR-');
        $payload = $this->decryptAndValidatePayload($encryptedData);

        $vehicle = Vehicle::with('project')->where('qr_code_identifier', $identifier)->first();
        if (!$vehicle) {
            throw new InvalidQRCodeException("Vehicle QR code tidak ditemukan", 404);
        }

        return [
            'id' => $vehicle->id,
            'plate_number' => $vehicle->plate_number,
            'type' => $vehicle->type,
            'project_name' => $vehicle->project?->name,
            'status' => $vehicle->status,
            'current_km' => $vehicle->computed_km,
        ];
    }

    /**
     * Delete QR code file
     */
    public function deleteQRCode(?string $path): void
    {
        if ($path && Storage::disk('public')->exists($path)) {
            Storage::disk('public')->delete($path);
        }
    }

    /**
     * Regenerate driver QR code
     */
    public function regenerateForDriver(Driver $driver): bool
    {
        $this->deleteQRCode($driver->qr_code_path);
        return $this->generateForDriver($driver);
    }

    /**
     * Regenerate vehicle QR code
     */
    public function regenerateForVehicle(Vehicle $vehicle): bool
    {
        $this->deleteQRCode($vehicle->qr_code_path);
        return $this->generateForVehicle($vehicle);
    }
}
