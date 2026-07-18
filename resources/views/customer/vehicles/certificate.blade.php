<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sertifikat Kelayakan Unit - {{ $vehicle->plate_number }}</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Cinzel:wght@500;700;800&family=Inter:wght@300;400;600;700&display=swap');
        
        body {
            background-color: #f1f5f9;
            font-family: 'Inter', sans-serif;
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;
        }

        /* Certificate Container */
        .cert-container {
            width: 297mm;
            height: 210mm;
            margin: 20px auto;
            background-color: #fff;
            position: relative;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
            overflow: hidden;
            border-radius: 8px;
            padding: 15mm;
        }

        /* Luxury Double Borders */
        .cert-border-outer {
            width: 100%;
            height: 100%;
            border: 8px solid #1e3a8a;
            padding: 3px;
            box-sizing: border-box;
            position: relative;
        }

        .cert-border-inner {
            width: 100%;
            height: 100%;
            border: 2px solid #b45309; /* Amber border */
            padding: 10mm;
            box-sizing: border-box;
            position: relative;
        }

        /* Watermark Background */
        .cert-watermark {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%) rotate(-30deg);
            font-size: 8.5rem;
            color: rgba(30, 58, 138, 0.03);
            font-family: 'Cinzel', serif;
            font-weight: 800;
            white-space: nowrap;
            user-select: none;
            pointer-events: none;
            z-index: 0;
        }

        /* Header Styling */
        .cert-header {
            text-align: center;
            margin-bottom: 5mm;
            position: relative;
            z-index: 1;
        }

        .cert-logo {
            font-size: 2.2rem;
            color: #1e3a8a;
            font-weight: bold;
            margin-bottom: 2px;
        }

        .cert-company {
            font-size: 1.1rem;
            color: #475569;
            text-uppercase: uppercase;
            letter-spacing: 2px;
            font-weight: 600;
        }

        /* Content Styling */
        .cert-title-area {
            text-align: center;
            margin-bottom: 6mm;
            position: relative;
            z-index: 1;
        }

        .cert-title {
            font-family: 'Cinzel', serif;
            font-size: 2.4rem;
            color: #1e3a8a;
            font-weight: 700;
            letter-spacing: 1px;
            margin-bottom: 2px;
        }

        .cert-subtitle {
            font-size: 0.9rem;
            color: #b45309;
            letter-spacing: 4px;
            font-weight: bold;
            text-transform: uppercase;
            border-bottom: 2px solid #e2e8f0;
            display: inline-block;
            padding-bottom: 8px;
            width: 60%;
        }

        .cert-body {
            text-align: center;
            font-size: 1.05rem;
            color: #334155;
            line-height: 1.6;
            margin-bottom: 6mm;
            position: relative;
            z-index: 1;
            padding: 0 40px;
        }

        .cert-recipient {
            font-size: 1.5rem;
            font-weight: bold;
            color: #1e293b;
            margin: 4px 0;
            text-decoration: underline;
        }

        /* Specifications Table */
        .specs-grid {
            background-color: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 6px;
            padding: 12px 24px;
            margin-bottom: 8mm;
            position: relative;
            z-index: 1;
        }

        .spec-item {
            text-align: center;
        }

        .spec-label {
            font-size: 0.75rem;
            color: #64748b;
            text-transform: uppercase;
            letter-spacing: 1px;
            margin-bottom: 3px;
        }

        .spec-value {
            font-size: 1.1rem;
            font-weight: 700;
            color: #1e293b;
        }

        /* Footer & Signatures */
        .cert-footer {
            display: flex;
            justify-content: space-between;
            align-items: flex-end;
            margin-top: 5mm;
            position: relative;
            z-index: 1;
        }

        .cert-qr {
            text-align: left;
        }

        .cert-signature {
            text-align: center;
            width: 220px;
        }

        .signature-line {
            border-bottom: 2px solid #475569;
            margin-bottom: 5px;
            padding-top: 30px;
        }

        /* Print Controls Floating */
        .print-controls {
            position: fixed;
            bottom: 20px;
            right: 20px;
            z-index: 9999;
        }

        /* Print Style Overrides */
        @media print {
            body {
                background-color: #fff;
                margin: 0;
                padding: 0;
            }
            .cert-container {
                margin: 0;
                box-shadow: none;
                width: 297mm;
                height: 210mm;
                page-break-after: avoid;
                page-break-before: avoid;
            }
            .print-controls {
                display: none;
            }
        }
    </style>
</head>
<body>

    <!-- Floating Print Controls -->
    <div class="print-controls">
        <button onclick="window.print()" class="btn btn-lg btn-primary shadow-lg d-flex align-items-center gap-2" style="background-color: #1e3a8a; border: none; border-radius: 30px; padding: 12px 24px;">
            <i class="bi bi-printer-fill fs-5"></i>
            <span class="fw-bold">Cetak / Simpan PDF</span>
        </button>
    </div>

    <!-- Landscape Certificate Layout -->
    <div class="cert-container">
        <div class="cert-border-outer">
            <div class="cert-border-inner">
                
                <!-- Watermark -->
                <div class="cert-watermark">HAMADA GLOBAL JAYA</div>
                
                <!-- Certificate Header -->
                <div class="cert-header">
                    <div class="cert-logo"><i class="bi bi-shield-check"></i> PT Hamada Global Jaya</div>
                    <div class="cert-company">Fleet Management Division</div>
                </div>

                <!-- Certificate Title -->
                <div class="cert-title-area">
                    <h1 class="cert-title">Sertifikat Kelayakan Unit</h1>
                    <span class="cert-subtitle">Certificate of Vehicle Roadworthiness</span>
                </div>

                <!-- Certificate Body Statement -->
                <div class="cert-body">
                    Dengan ini menyatakan bahwa unit sewa kendaraan dengan plat nomor resmi
                    <div class="cert-recipient font-monospace">{{ $vehicle->plate_number }}</div>
                    telah lulus serangkaian audit kepatuhan dokumen, pemeriksaan fisik harian pengemudi, 
                    serta program pemeliharaan preventif berkala dengan hasil pencapaian yang memenuhi standar mutu operasi.
                </div>

                <!-- Specs Grid -->
                <div class="specs-grid">
                    <div class="row align-items-center">
                        <div class="col border-end spec-item">
                            <div class="spec-label">Tipe Kendaraan</div>
                            <div class="spec-value">{{ $vehicle->type }}</div>
                        </div>
                        <div class="col border-end spec-item">
                            <div class="spec-label">Projek Operasional</div>
                            <div class="spec-value">{{ $vehicle->project->name ?? '-' }}</div>
                        </div>
                        <div class="col border-end spec-item">
                            <div class="spec-label">Indeks Kelayakan</div>
                            <div class="spec-value text-{{ $healthStatus['color'] }}">{{ round($healthReport['health_score']) }}% ({{ $healthStatus['label'] }})</div>
                        </div>
                        <div class="col spec-item">
                            <div class="spec-label">Pembacaan Odometer</div>
                            <div class="spec-value">{{ number_format($vehicle->current_km, 0, ',', '.') }} KM</div>
                        </div>
                    </div>
                </div>

                <!-- Footer Signatures and Verification -->
                <div class="cert-footer">
                    <!-- QR Code verification -->
                    <div class="cert-qr d-flex align-items-center gap-3">
                        <div class="p-1.5 bg-white border rounded">
                            {!! QrCode::size(85)->generate($qrCodeData) !!}
                        </div>
                        <div>
                            <div class="fw-bold text-dark text-uppercase small" style="letter-spacing: 0.5px;">Verifikasi Digital</div>
                            <small class="text-muted d-block" style="font-size: 0.7rem; max-width: 180px;">Scan untuk memverifikasi keaslian dan status kesehatan unit secara real-time.</small>
                        </div>
                    </div>

                    <!-- Company Stamp or Date -->
                    <div class="text-center">
                        <div class="small text-muted mb-1 text-uppercase tracking-wider">Tanggal Terbit</div>
                        <div class="fw-semibold text-dark">{{ now()->format('d F Y') }}</div>
                    </div>

                    <!-- Inspector Signature -->
                    <div class="cert-signature">
                        <div class="small text-muted text-uppercase tracking-wider">Mengesahkan,</div>
                        <div class="signature-line"></div>
                        <div class="fw-bold text-dark mb-0">Manajemen PT Hamada Global Jaya</div>
                        <small class="text-muted">Divisi Fleet & Maintenance</small>
                    </div>
                </div>

            </div>
        </div>
    </div>

</body>
</html>
