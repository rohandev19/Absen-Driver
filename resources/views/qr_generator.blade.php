<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>QR Code Generator</title>
    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            flex-direction: column;
            background-color: #f4f7f6;
            margin: 0;
            color: #333;
        }
        .container {
            text-align: center;
            background-color: #fff;
            padding: 40px;
            border-radius: 12px;
            box-shadow: 0 8px 24px rgba(0, 0, 0, 0.1);
            max-width: 420px;
            width: 90%;
        }
        h1 {
            color: #2c3e50;
        }
        input, select {
            width: 100%;
            padding: 12px;
            border: 1px solid #ccc;
            border-radius: 8px;
            font-size: 16px;
            margin-bottom: 20px;
            box-sizing: border-box;
        }
        button {
            background-color: #3498db;
            color: white;
            padding: 14px;
            border: none;
            border-radius: 8px;
            font-size: 16px;
            font-weight: bold;
            cursor: pointer;
            width: 100%;
            transition: background-color 0.3s;
        }
        button:hover {
            background-color: #2980b9;
        }
        .qr-code {
            margin-top: 30px;
        }
        .qr-code p {
            margin-top: 15px;
            color: #7f8c8d;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>QR Code Generator</h1>
        <p>Pilih tipe dan masukkan data untuk membuat QR Code.</p>

        <form action="{{ route('qr.generate') }}" method="POST">
            @csrf
            <select name="type" required>
                <option value="DRV">QR Code Driver</option>
                <option value="CAR">QR Code Mobil</option>
            </select>
            <input type="text" name="data" placeholder="Contoh: 12345 atau B 1234 ABC" required>
            <button type="submit">Buat QR Code</button>
        </form>

        {{-- Jika ada hasil QR Code --}}
        @isset($qrCodeImage)
            <div class="qr-code">
                <h3>Hasil untuk: <strong>{{ $inputData }}</strong></h3>
                <div>{!! $qrCodeImage !!}</div>
                {{-- Tombol unduh dihapus dan diganti instruksi --}}
                <p><small>Untuk menyimpan, klik kanan pada gambar QR Code di atas, lalu pilih "Simpan gambar sebagai...".</small></p>
            </div>
        @endisset

        {{-- Menampilkan error validasi --}}
        @if ($errors->any())
            <div style="color: #e74c3c; margin-top: 15px;">
                <strong>{{ $errors->first() }}</strong>
            </div>
        @endif
    </div>
</body>
</html>

