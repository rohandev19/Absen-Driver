<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\QRCodeService;
use App\Exceptions\InvalidQRCodeException;

class QRCodeScanController extends Controller
{
    protected QRCodeService $qrCodeService;

    public function __construct(QRCodeService $qrCodeService)
    {
        $this->qrCodeService = $qrCodeService;
    }

    /**
     * Scan Driver QR Code
     */
    public function scanDriver(Request $request)
    {
        $request->validate([
            'qr_data' => 'required|string',
        ]);

        try {
            $qrData = $request->input('qr_data', '');
            $parts = explode('|', $qrData, 2);
            if (count($parts) !== 2) {
                throw new InvalidQRCodeException("Format QR code tidak sesuai", 400);
            }
            $identifier = $parts[0];
            $encryptedData = $parts[1];
            
            $data = $this->qrCodeService->verifyDriverQRCode($identifier, $encryptedData);

            return response()->json([
                'status' => 'success',
                'data' => $data,
            ]);
        } catch (InvalidQRCodeException $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage()
            ], $e->getCode() ?: 400);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Terjadi kesalahan pada server'
            ], 500);
        }
    }

    /**
     * Scan Vehicle QR Code
     */
    public function scanVehicle(Request $request)
    {
        $request->validate([
            'qr_data' => 'required|string',
        ]);

        try {
            $qrData = $request->input('qr_data', '');
            $parts = explode('|', $qrData, 2);
            if (count($parts) !== 2) {
                throw new InvalidQRCodeException("Format QR code tidak sesuai", 400);
            }
            $identifier = $parts[0];
            $encryptedData = $parts[1];

            $data = $this->qrCodeService->verifyVehicleQRCode($identifier, $encryptedData);

            return response()->json([
                'status' => 'success',
                'data' => $data,
            ]);
        } catch (InvalidQRCodeException $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage()
            ], $e->getCode() ?: 400);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Terjadi kesalahan pada server'
            ], 500);
        }
    }
}
