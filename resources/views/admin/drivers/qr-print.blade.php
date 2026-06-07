<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Print QR Code - {{ $driver->full_name }}</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            text-align: center;
            padding: 20px;
            background: #fff;
        }
        .card {
            border: 2px solid #333;
            border-radius: 10px;
            display: inline-block;
            padding: 30px;
            width: 300px;
        }
        .name {
            font-size: 24px;
            font-weight: bold;
            margin-bottom: 5px;
        }
        .nik {
            font-size: 16px;
            color: #666;
            margin-bottom: 20px;
        }
        .qr-code {
            width: 250px;
            height: 250px;
        }
        @media print {
            body {
                padding: 0;
            }
            .card {
                border: none;
            }
            /* Hide print button in print dialog */
            .no-print {
                display: none;
            }
        }
    </style>
</head>
<body>
    <button class="no-print" onclick="window.print()" style="margin-bottom: 20px; padding: 10px 20px; cursor: pointer;">Print Document</button>
    
    <div>
        <div class="card">
            <div class="name">{{ $driver->full_name }}</div>
            <div class="nik">{{ $driver->driver_id_nik }}</div>
            <img src="{{ $driver->qr_code_url }}" alt="QR Code" class="qr-code">
        </div>
    </div>
    
    <script>
        // Auto trigger print when page loads
        window.onload = function() {
            setTimeout(function() {
                window.print();
            }, 500);
        };
    </script>
</body>
</html>
