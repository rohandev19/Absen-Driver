<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class QrCodeController extends Controller
{
    /**
     * Menampilkan halaman utama generator QR code.
     */
    public function show()
    {
        return view('qr_generator');
    }

    /**
     * Membuat QR code berdasarkan input dari form dan menampilkannya di halaman.
     */
    public function generate(Request $request)
    {
        // Validasi input
        $request->validate([
            'type' => 'required|in:DRV,CAR',
            'data' => 'required|string|max:255',
        ]);

        // Gabungkan tipe dan data menjadi format yang benar (misal: "DRV-12345")
        $inputData = $request->input('type') . '-' . $request->input('data');

        // Generate QR code sebagai gambar SVG yang bisa langsung ditampilkan
        $qrCodeImage = QrCode::size(250)->generate($inputData);

        // Kirim kembali ke halaman view dengan data QR code
        return view('qr_generator', [
            'qrCodeImage' => $qrCodeImage,
            'inputData' => $inputData
        ]);
    }
}

