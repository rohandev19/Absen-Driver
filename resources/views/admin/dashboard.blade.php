@extends('admin.layouts.app')

@section('title', 'Dashboard - Aktivitas Driver')

@section('content')
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <link rel="stylesheet" href="https://unpkg.com/leaflet.markercluster@1.5.3/dist/MarkerCluster.css" />
    <link rel="stylesheet" href="https://unpkg.com/leaflet.markercluster@1.5.3/dist/MarkerCluster.Default.css" />

    <style>
        /* =============================================
                                       KPI CARDS
                                    ============================================= */
        .card-hover {
            transition: transform 0.2s, box-shadow 0.2s;
            cursor: pointer;
        }

        .card-hover:hover {
            transform: translateY(-5px);
            box-shadow: 0 .5rem 1rem rgba(0, 0, 0, .15) !important;
            border-color: #0d6efd;
        }

        /* Flash biru saat nilai KPI berubah */
        @keyframes kpi-flash {
            0% {
                background-color: transparent;
            }

            30% {
                background-color: rgba(13, 110, 253, 0.12);
            }

            100% {
                background-color: transparent;
            }
        }

        .kpi-updated {
            animation: kpi-flash 1s ease-out;
            border-radius: 8px;
        }

        /* =============================================
                                       PETA
                                    ============================================= */
        #fleet-map {
            height: 450px;
            width: 100%;
            border-bottom-left-radius: var(--bs-border-radius);
            border-bottom-right-radius: var(--bs-border-radius);
            z-index: 1;
        }

        .leaflet-popup-content-wrapper {
            border-radius: 8px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
        }

        .leaflet-popup-content {
            margin: 15px;
            line-height: 1.5;
        }

        .marker-cluster-small,
        .marker-cluster-medium,
        .marker-cluster-large {
            background-color: rgba(220, 53, 69, 0.2) !important;
        }

        .marker-cluster-small div,
        .marker-cluster-medium div,
        .marker-cluster-large div {
            background-color: rgba(220, 53, 69, 0.8) !important;
            color: white !important;
            font-weight: bold;
        }

        /* =============================================
                                       STATUS BAR & PROGRESS
                                    ============================================= */
        #refresh-spinner {
            display: none;
        }

        #refresh-spinner.active {
            display: inline-block;
        }

        #refresh-progress {
            height: 3px;
            transition: width 1s linear;
        }

        /* =============================================
                                       EMERGENCY TOAST
                                    ============================================= */
        #emergency-toast-container {
            position: fixed;
            top: 20px;
            right: 20px;
            z-index: 9999;
            min-width: 320px;
        }

        .emergency-toast {
            border-left: 4px solid #dc3545;
        }

        /* Flash hijau pada item driver baru */
        @keyframes driver-new-flash {
            0% {
                background-color: #f8f9fa;
            }

            40% {
                background-color: rgba(25, 135, 84, 0.12);
            }

            100% {
                background-color: #f8f9fa;
            }
        }

        .driver-item-new {
            animation: driver-new-flash 1.2s ease-out;
        }
    </style>

    <div class="container-fluid p-0">

        @if (isset($error))
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <strong>Error:</strong> {{ $error }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif


        {{-- =============================================
        1. KPI CARDS
        ============================================= --}}
        <div class="row g-3 mb-4">

            <div class="col-lg-3 col-md-6">
                <a href="{{ route('admin.riwayat_driver') }}"
                    class="card shadow-sm card-hover text-decoration-none text-reset h-100">
                    <div class="card-body d-flex flex-column flex-sm-row justify-content-sm-between align-items-center">
                        <div class="text-center text-sm-start" id="kpi-card-driver">
                            <h2 class="h3 fw-bold mb-0" id="kpi-driver-bertugas">{{ count($onDutyDrivers) }}</h2>
                            <span class="text-muted">Driver Bertugas</span>
                        </div>
                        <i class="bi bi-broadcast fs-1 text-danger opacity-50 mt-2 mt-sm-0"></i>
                    </div>
                </a>
            </div>

            <div class="col-lg-3 col-md-6">
                <a href="{{ route('admin.daftar_aset') }}"
                    class="card shadow-sm card-hover text-decoration-none text-reset h-100">
                    <div class="card-body d-flex flex-column flex-sm-row justify-content-sm-between align-items-center">
                        <div class="text-center text-sm-start" id="kpi-card-aset">
                            <h2 class="h3 fw-bold mb-0">
                                <span id="kpi-aset-tersedia">{{ $totalAsetTersedia }}</span>
                                <span class="h5 text-muted">/ <span id="kpi-total-aset">{{ $totalAset }}</span></span>
                            </h2>
                            <span class="text-muted">Aset Tersedia / Total</span>
                        </div>
                        <i class="bi bi-p-circle-fill fs-1 text-success opacity-50 mt-2 mt-sm-0"></i>
                    </div>
                </a>
            </div>

            <div class="col-lg-3 col-md-6">
                <a href="{{ route('admin.riwayat_unit') }}"
                    class="card shadow-sm card-hover text-decoration-none text-reset h-100">
                    <div class="card-body d-flex flex-column flex-sm-row justify-content-sm-between align-items-center">
                        <div class="text-center text-sm-start" id="kpi-card-jarak">
                            <h2 class="h3 fw-bold mb-0">
                                <span id="kpi-jarak-bulanan">{{ number_format($totalJarakBulanIni) }}</span>
                                <span class="h5 text-muted">Km</span>
                            </h2>
                            <span class="text-muted">Jarak Bulan Ini</span>
                        </div>
                        <i class="bi bi-sign-turn-right-fill fs-1 text-info opacity-50 mt-2 mt-sm-0"></i>
                    </div>
                </a>
            </div>

            <div class="col-lg-3 col-md-6">
                <a href="{{ route('admin.laporan_darurat') }}"
                    class="card shadow-sm card-hover text-decoration-none text-reset h-100">
                    <div class="card-body d-flex flex-column flex-sm-row justify-content-sm-between align-items-center">
                        <div class="text-center text-sm-start" id="kpi-card-laporan">
                            <h2 class="h3 fw-bold mb-0" id="kpi-laporan-hari-ini">{{ $totalLaporan }}</h2>
                            <span class="text-muted">Laporan Hari Ini</span>
                        </div>
                        <i class="bi bi-exclamation-triangle-fill fs-1 text-warning opacity-50 mt-2 mt-sm-0"></i>
                    </div>
                </a>
            </div>

        </div>


        {{-- =============================================
        2. FLEET COMMAND CENTER (LIVE MAP)
        ============================================= --}}
        <div class="card shadow-sm border-0 mb-4">
            <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center flex-wrap gap-2">
                <div>
                    <h2 class="h5 mb-0 fw-bold text-dark">
                        <i class="bi bi-geo-alt-fill text-danger me-2"></i>Fleet Command Center
                    </h2>
                    <small class="text-muted">Titik koordinat armada yang sedang bertugas saat ini.</small>
                </div>
                <div class="d-flex align-items-center gap-3 flex-wrap">
                    <div class="text-muted d-flex align-items-center gap-2" style="font-size:0.78rem;">
                        <span class="spinner-border spinner-border-sm text-primary" id="refresh-spinner" role="status"
                            style="width:14px;height:14px;"></span>
                        <i class="bi bi-arrow-clockwise" id="refresh-icon"></i>
                        <span>Diperbarui: <strong id="last-updated-time">baru saja</strong></span>
                    </div>
                    <button class="btn btn-sm btn-outline-secondary" onclick="manualRefresh()" title="Refresh sekarang">
                        <i class="bi bi-arrow-clockwise me-1"></i>Refresh
                    </button>
                    <span class="badge bg-danger bg-opacity-10 text-danger border border-danger rounded-pill px-3 py-2">
                        <span class="spinner-grow spinner-grow-sm me-1" role="status" aria-hidden="true"
                            style="width:10px;height:10px;"></span>
                        <span id="badge-unit-live">{{ count($onDutyDrivers) }}</span> Unit Live
                    </span>
                </div>
            </div>

            {{-- Progress bar countdown --}}
            <div class="bg-light" style="height:3px;">
                <div id="refresh-progress" class="bg-primary" style="width:100%;"></div>
            </div>

            <div class="card-body p-0">
                <div id="fleet-map"></div>
            </div>
        </div>


        <div class="row g-4 mb-4">

            {{-- =============================================
            3. CHART
            ============================================= --}}
            <div class="col-lg-7">
                <div class="card shadow-sm border-0 h-100">
                    <div class="card-header bg-white py-3">
                        <h2 class="h5 mb-0 fw-bold">
                            <i class="bi bi-bar-chart-line-fill text-primary me-2"></i>Aktivitas 7 Hari Terakhir
                        </h2>
                    </div>
                    <div class="card-body">
                        <canvas id="activityChart" style="min-height:300px;"></canvas>
                    </div>
                </div>
            </div>

            {{-- =============================================
            4. LIST DRIVER AKTIF
            ============================================= --}}
            <div class="col-lg-5">
                <div class="card shadow-sm border-0 h-100">
                    <div class="card-header bg-white py-3">
                        <h2 class="h5 mb-0 fw-bold">
                            <i class="bi bi-person-lines-fill text-success me-2"></i>Log Driver Aktif
                        </h2>
                    </div>
                 
                    <div class="card-body p-0">
                        <div id="driver-list-container">
                            @if (count($onDutyDrivers) > 0)
                                <div id="driver-scroll-area" style="max-height:350px; overflow-y:auto;" class="p-3">
                                    <div class="vstack gap-3" id="driver-list-inner">
                                        @foreach ($onDutyDrivers as $driver)
                                            <div class="driver-list-item d-flex justify-content-between align-items-center p-3 border rounded bg-light"
                                                data-id="{{ $driver['id'] }}" data-name="{{ $driver['driver_name'] }}"
                                                data-plate="{{ $driver['plate_number'] }}">
                                                <div class="d-flex align-items-center gap-3">
                                                    <div class="bg-danger bg-opacity-10 text-danger rounded-circle d-flex align-items-center justify-content-center"
                                                        style="width:40px;height:40px;flex-shrink:0;">
                                                        <i class="bi bi-truck fs-5"></i>
                                                    </div>
                                                    <div>
                                                        <h6 class="mb-1 fw-bold text-dark">{{ $driver['driver_name'] }}</h6>
                                                        <div class="d-flex align-items-center gap-2 small text-muted">
                                                            <span
                                                                class="badge bg-dark font-monospace">{{ $driver['plate_number'] }}</span>
                                                            <span><i class="bi bi-clock-history"></i>
                                                                {{ $driver['timestamp_masuk'] }}</span>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="d-flex gap-1">
                                                    <button class="btn btn-sm btn-outline-danger"
                                                        onclick="focusMapTo({{ $driver['latitude'] ?? 0 }}, {{ $driver['longitude'] ?? 0 }}, {{ $driver['id'] ?? 'null' }})"
                                                        title="Fokus di Peta">
                                                        <i class="bi bi-crosshair"></i>
                                                    </button>
                                                    <a href="{{ $driver['link_selfie'] }}" target="_blank"
                                                        class="btn btn-sm btn-outline-primary" title="Foto Selfie">
                                                        <i class="bi bi-person-bounding-box"></i>
                                                    </a>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            @else
                                <div class="p-5 text-center text-muted" id="driver-empty-state">
                                    <i class="bi bi-moon-stars-fill display-4 d-block mb-3 opacity-25"></i>
                                    <h5 class="mb-0 fw-bold">Tidak Ada Driver Aktif</h5>
                                    <p class="small">Semua armada sedang parkir atau beristirahat.</p>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>

    {{-- Emergency Toast Container --}}
    <div id="emergency-toast-container" aria-live="polite" aria-atomic="true"></div>

@endsection


@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <script src="https://unpkg.com/leaflet.markercluster@1.5.3/dist/leaflet.markercluster.js"></script>

    <script>
        // ================================================================
        // STATE GLOBAL
        // ================================================================
        let fleetMap;
        let markerCluster;
        let activityChartInstance = null; // Referensi Chart.js untuk update data (Fix #3)

        const markerRegistry = {};   // { driverId: markerInstance }
        let lastReportId = null;
        let countdownInterval;
        let isFetching = false; // Flag cegah race condition

        const REFRESH_SECONDS = 30;

        let activeDrivers = @json($onDutyDrivers);


        // ================================================================
        // INIT
        // ================================================================
        document.addEventListener('DOMContentLoaded', function () {
            initFleetMap();
            initActivityChart();
            startAutoRefresh();
        });




        // ================================================================
        // AUTO-REFRESH
        //
        // Masalah lama: setInterval tidak peduli apakah fetch sebelumnya
        // sudah selesai. Jika server lambat (> 30 detik), request menumpuk
        // dan bisa menyebabkan race condition / memory leak.
        //
        // Solusi: Gunakan setTimeout rekursif + flag `isFetching`.
        // Request berikutnya baru dijadwalkan SETELAH request sebelumnya
        // selesai (baik sukses maupun error).
        // ================================================================
        function startAutoRefresh() {
            // Panggil pertama kali diam-diam untuk set lastReportId
            fetchLiveData(false, true).then(() => {
                scheduleNextRefresh();
            });

            startCountdown();
        }

        function scheduleNextRefresh() {
            setTimeout(async () => {
                await fetchLiveData();
                startCountdown();
                scheduleNextRefresh(); // Jadwalkan lagi SETELAH selesai
            }, REFRESH_SECONDS * 1000);
        }

        function manualRefresh() {
            if (isFetching) return; // Jangan tumpuk request
            clearInterval(countdownInterval);
            fetchLiveData(true).then(() => {
                startCountdown();
            });
        }

        async function fetchLiveData(isManual = false, silent = false) {
            if (isFetching) return; // FIX #5: Guard race condition
            isFetching = true;
            setRefreshSpinner(true);

            try {
                const response = await fetch('{{ route("admin.dashboard.status") }}', {
                    headers: { 'X-Requested-With': 'XMLHttpRequest' }
                });

                if (!response.ok) throw new Error('HTTP ' + response.status);

                const data = await response.json();

                updateKpiCards(data.kpi);
                updateMapMarkers(data.onDutyDrivers);   // FIX #1 di dalam fungsi ini
                updateDriverList(data.onDutyDrivers);   // FIX #2 di dalam fungsi ini
                updateChart(data.chartLabels, data.chartData); // FIX #3
                updateBadge(data.kpi.driverBertugas);
                updateLastUpdatedTime();

                if (data.latestEmergencyReport) {
                    if (silent) {
                        lastReportId = data.latestEmergencyReport.id;
                    } else {
                        checkEmergencyReport(data.latestEmergencyReport);
                    }
                }

            } catch (error) {
                console.error('[AutoRefresh] Gagal:', error);
                const el = document.getElementById('last-updated-time');
                if (el) el.textContent = 'gagal memuat';
            } finally {
                isFetching = false; // FIX #5: Reset flag setelah selesai
                setRefreshSpinner(false);
            }
        }


        // ================================================================
        // UPDATE KPI CARDS
        // ================================================================
        function updateKpiCards(kpi) {
            updateKpiValue('kpi-driver-bertugas', 'kpi-card-driver', kpi.driverBertugas);
            updateKpiValue('kpi-aset-tersedia', 'kpi-card-aset', kpi.asetTersedia);
            updateKpiValue('kpi-total-aset', null, kpi.totalAset);
            updateKpiValue('kpi-jarak-bulanan', 'kpi-card-jarak', kpi.totalJarakBulanIni);
            updateKpiValue('kpi-laporan-hari-ini', 'kpi-card-laporan', kpi.totalLaporan);
        }

        function updateKpiValue(valueElId, cardElId, newValue) {
            const valueEl = document.getElementById(valueElId);
            if (!valueEl) return;

            const newStr = String(newValue);
            if (valueEl.textContent.trim() === newStr) return; // Tidak berubah, skip

            valueEl.textContent = newStr;

            if (cardElId) {
                const cardEl = document.getElementById(cardElId);
                if (cardEl) {
                    cardEl.classList.remove('kpi-updated');
                    void cardEl.offsetWidth; // Force reflow
                    cardEl.classList.add('kpi-updated');
                }
            }
        }


        // ================================================================
        // FIX #1: UPDATE MARKER PETA — TANPA clearLayers()
        //
        // Masalah lama: clearLayers() lalu buat ulang semua marker → popup
        // yang sedang dibuka admin akan tertutup paksa, peta "berkedip".
        //
        // Solusi baru:
        // Step 1 — Hapus HANYA marker driver yang sudah offline (tidak ada di data baru)
        // Step 2 — Jika driver sudah punya marker → update posisi dengan setLatLng()
        //          (popup tidak akan tertutup!)
        // Step 3 — Jika driver belum punya marker → buat marker baru
        // ================================================================
        function updateMapMarkers(drivers) {
            if (!fleetMap || !markerCluster) return;

            const currentDriverIds = new Set(drivers.map(d => String(d.id)));

            // STEP 1: Hapus marker driver yang sudah tidak on-duty
            Object.keys(markerRegistry).forEach(id => {
                if (!currentDriverIds.has(id)) {
                    markerCluster.removeLayer(markerRegistry[id]);
                    delete markerRegistry[id];
                }
            });

            // STEP 2 & 3: Update posisi atau buat marker baru
            drivers.forEach(driver => {
                const lat = Number(driver.latitude);
                const lng = Number(driver.longitude);
                if (isNaN(lat) || isNaN(lng) || (lat === 0 && lng === 0)) return;

                const driverId = String(driver.id);

                if (markerRegistry[driverId]) {
                    // STEP 2: Driver sudah ada → update posisi saja
                    // Popup yang sedang terbuka TIDAK akan tertutup
                    markerRegistry[driverId].setLatLng([lat, lng]);

                    // Update juga konten popup agar data tetap fresh
                    markerRegistry[driverId].setPopupContent(buildPopupContent(driver));

                } else {
                    // STEP 3: Driver baru → buat marker baru
                    const marker = L.marker([lat, lng], { icon: buildTruckIcon() });
                    marker.bindPopup(buildPopupContent(driver), {
                        autoPan: true,
                        autoPanPadding: [20, 20],
                    });
                    markerRegistry[driverId] = marker;
                    markerCluster.addLayer(marker);
                }
            });

            activeDrivers = drivers;
        }


        // ================================================================
        // FIX #2: UPDATE DRIVER LIST — SIMPAN POSISI SCROLL
        //
        // Masalah lama: innerHTML = ... langsung → scrollTop reset ke 0
        // setiap 30 detik. Admin yang scroll ke bawah akan terganggu.
        //
        // Solusi:
        // 1. Simpan scrollTop sebelum render ulang
        // 2. Render HTML baru
        // 3. Kembalikan scrollTop setelah render
        // ================================================================
        function updateDriverList(drivers) {
            const container = document.getElementById('driver-list-container');
            if (!container) return;

            // FIX #2 STEP 1: Simpan posisi scroll saat ini
            const scrollArea = document.getElementById('driver-scroll-area');
            const savedScrollTop = scrollArea ? scrollArea.scrollTop : 0;

            // Kumpulkan ID yang sudah ada untuk deteksi driver baru
            const existingIds = new Set(
                [...container.querySelectorAll('.driver-list-item')].map(el => el.dataset.id)
            );

            if (drivers.length === 0) {
                container.innerHTML = `
                                                <div class="p-5 text-center text-muted">
                                                    <i class="bi bi-moon-stars-fill display-4 d-block mb-3 opacity-25"></i>
                                                    <h5 class="mb-0 fw-bold">Tidak Ada Driver Aktif</h5>
                                                    <p class="small">Semua armada sedang parkir atau beristirahat.</p>
                                                </div>`;
                return;
            }

            let itemsHtml = '';
            drivers.forEach(driver => {
                const isNew = !existingIds.has(String(driver.id));
                itemsHtml += buildDriverItemHtml(driver, isNew);
            });

            // FIX #2 STEP 2: Render HTML baru
            container.innerHTML = `
                                            <div id="driver-scroll-area" style="max-height:350px; overflow-y:auto;" class="p-3">
                                                <div class="vstack gap-3">${itemsHtml}</div>
                                            </div>`;

            // FIX #2 STEP 3: Kembalikan posisi scroll
            const newScrollArea = document.getElementById('driver-scroll-area');
            if (newScrollArea) newScrollArea.scrollTop = savedScrollTop;

            // Re-apply filter jika search sedang aktif
            const keyword = document.getElementById('driver-search')?.value;
            if (keyword) filterDriverList(keyword);
        }

        function buildDriverItemHtml(driver, isNew) {
            const newClass = isNew ? 'driver-item-new' : '';
            return `
                                            <div class="driver-list-item d-flex justify-content-between align-items-center p-3 border rounded bg-light ${newClass}"
                                                 data-id="${driver.id}"
                                                 data-name="${escapeHtml(driver.driver_name)}"
                                                 data-plate="${escapeHtml(driver.plate_number)}">
                                                <div class="d-flex align-items-center gap-3">
                                                    <div class="bg-danger bg-opacity-10 text-danger rounded-circle d-flex align-items-center justify-content-center"
                                                         style="width:40px;height:40px;flex-shrink:0;">
                                                        <i class="bi bi-truck fs-5"></i>
                                                    </div>
                                                    <div>
                                                        <h6 class="mb-1 fw-bold text-dark">${escapeHtml(driver.driver_name)}</h6>
                                                        <div class="d-flex align-items-center gap-2 small text-muted">
                                                            <span class="badge bg-dark font-monospace">${escapeHtml(driver.plate_number)}</span>
                                                            <span><i class="bi bi-clock-history"></i> ${escapeHtml(driver.timestamp_masuk)}</span>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="d-flex gap-1">
                                                    <button class="btn btn-sm btn-outline-danger"
                                                            onclick="focusMapTo(${driver.latitude}, ${driver.longitude}, ${driver.id})"
                                                            title="Fokus di Peta">
                                                        <i class="bi bi-crosshair"></i>
                                                    </button>
                                                    <a href="${escapeHtml(driver.link_selfie)}" target="_blank"
                                                       class="btn btn-sm btn-outline-primary" title="Foto Selfie">
                                                        <i class="bi bi-person-bounding-box"></i>
                                                    </a>
                                                </div>
                                            </div>`;
        }

        function updateBadge(count) {
            const el = document.getElementById('badge-unit-live');
            if (el) el.textContent = count;
        }


        // ================================================================
        // FIX #3: UPDATE CHART TANPA RELOAD HALAMAN
        //
        // Masalah lama: Data grafik hanya di-render sekali dari Blade.
        // Grafik menjadi basi walau KPI & peta sudah update.
        //
        // Solusi: Endpoint getStatus() sekarang mengembalikan chartLabels &
        // chartData terbaru. Fungsi ini mengupdate data pada instance Chart.js
        // lalu memanggil chart.update() untuk re-render tanpa buat ulang canvas.
        // ================================================================
        function updateChart(newLabels, newData) {
            if (!activityChartInstance) return;
            if (!newLabels || !newData) return;

            activityChartInstance.data.labels = newLabels;
            activityChartInstance.data.datasets[0].data = newData;
            activityChartInstance.update('none'); // 'none' = update tanpa animasi (lebih mulus)
        }


        // ================================================================
        // EMERGENCY TOAST
        // ================================================================
        function checkEmergencyReport(report) {
            if (!report || report.id === lastReportId) return;
            lastReportId = report.id;
            showEmergencyToast(report);
        }

        function showEmergencyToast(report) {
            const toastId = 'toast-' + Date.now();
            const mapsHtml = report.maps_link
                ? `<a href="${escapeHtml(report.maps_link)}" target="_blank"
                                                  class="btn btn-sm btn-danger text-white mt-2 w-100">
                                                   <i class="bi bi-geo-alt-fill me-1"></i>Lihat Lokasi
                                               </a>` : '';

            document.getElementById('emergency-toast-container').insertAdjacentHTML('beforeend', `
                                            <div id="${toastId}" class="toast emergency-toast show shadow-lg mb-2 bg-white" role="alert">
                                                <div class="toast-header bg-danger text-white">
                                                    <i class="bi bi-exclamation-triangle-fill me-2"></i>
                                                    <strong class="me-auto">Laporan Darurat Masuk!</strong>
                                                    <small class="me-2">baru saja</small>
                                                    <button type="button" class="btn-close btn-close-white"
                                                            onclick="document.getElementById('${toastId}').remove()"></button>
                                                </div>
                                                <div class="toast-body">
                                                    <div class="fw-bold">${escapeHtml(report.driver_name)}</div>
                                                    <div class="small text-muted mt-1">${escapeHtml(report.description)}</div>
                                                    ${mapsHtml}
                                                </div>
                                            </div>`);

            setTimeout(() => document.getElementById(toastId)?.remove(), 12000);
        }


        // ================================================================
        // PROGRESS BAR COUNTDOWN
        // ================================================================
        function startCountdown() {
            clearInterval(countdownInterval);

            const bar = document.getElementById('refresh-progress');
            let remaining = REFRESH_SECONDS;

            bar.style.transition = 'none';
            bar.style.width = '100%';
            void bar.offsetWidth; // Force reflow

            bar.style.transition = 'width 1s linear';

            countdownInterval = setInterval(() => {
                remaining--;
                bar.style.width = ((remaining / REFRESH_SECONDS) * 100) + '%';
                if (remaining <= 0) clearInterval(countdownInterval);
            }, 1000);
        }


        // ================================================================
        // HELPER UI
        // ================================================================
        function setRefreshSpinner(show) {
            document.getElementById('refresh-spinner')?.classList.toggle('active', show);
            const icon = document.getElementById('refresh-icon');
            if (icon) icon.style.display = show ? 'none' : '';
        }

        function updateLastUpdatedTime() {
            const el = document.getElementById('last-updated-time');
            if (el) el.textContent = new Date().toLocaleTimeString('id-ID', {
                hour: '2-digit', minute: '2-digit', second: '2-digit'
            });
        }

        function filterDriverList(keyword) {
            document.querySelectorAll('.driver-list-item').forEach(item => {
                const text = (item.dataset.name + ' ' + item.dataset.plate).toLowerCase();

                if (text.includes(keyword.toLowerCase())) {
                    // Tampilkan driver yang sesuai pencarian
                    item.classList.remove('d-none');
                    item.classList.add('d-flex');
                } else {
                    // Sembunyikan driver yang tidak cocok
                    item.classList.remove('d-flex');
                    item.classList.add('d-none'); // d-none akan memaksa elemen disembunyikan
                }
            });
        }

        function escapeHtml(str) {
            if (str == null) return '';
            return String(str)
                .replace(/&/g, '&amp;')
                .replace(/</g, '&lt;')
                .replace(/>/g, '&gt;')
                .replace(/"/g, '&quot;')
                .replace(/'/g, '&#039;');
        }


        // ================================================================
        // INIT FLEET MAP
        // ================================================================
        function initFleetMap() {
            fleetMap = L.map('fleet-map').setView([-6.2088, 106.8456], 10);

            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                attribution: '&copy; OpenStreetMap contributors',
                maxZoom: 18,
            }).addTo(fleetMap);

            markerCluster = L.markerClusterGroup({
                maxClusterRadius: 60,
                showCoverageOnHover: false,
                disableClusteringAtZoom: 16,
            });

            const bounds = [];

            activeDrivers.forEach(driver => {
                const lat = Number(driver.latitude);
                const lng = Number(driver.longitude);
                if (isNaN(lat) || isNaN(lng) || (lat === 0 && lng === 0)) return;

                const marker = L.marker([lat, lng], { icon: buildTruckIcon() });
                marker.bindPopup(buildPopupContent(driver), { autoPan: true, autoPanPadding: [20, 20] });

                if (driver.id) markerRegistry[String(driver.id)] = marker;
                markerCluster.addLayer(marker);
                bounds.push([lat, lng]);
            });

            fleetMap.addLayer(markerCluster);

            if (bounds.length === 1) {
                fleetMap.setView(bounds[0], 13);
            } else if (bounds.length > 1) {
                fleetMap.fitBounds(bounds, { padding: [50, 50], maxZoom: 14, minZoom: 5 });
            }
        }

        function buildTruckIcon() {
            return L.divIcon({
                className: 'custom-div-icon',
                html: `<div style="background-color:#dc3545;color:white;width:30px;height:30px;
                                                        border-radius:50%;display:flex;align-items:center;justify-content:center;
                                                        border:2px solid white;box-shadow:0 2px 5px rgba(0,0,0,0.3);">
                                                        <i class="bi bi-truck"></i></div>`,
                iconSize: [30, 30], iconAnchor: [15, 15], popupAnchor: [0, -15],
            });
        }

        function buildPopupContent(driver) {
            return `
                                            <div class="text-center" style="min-width:190px;">
                                                <h6 class="fw-bold text-dark mb-1">${escapeHtml(driver.driver_name)}</h6>
                                                <span class="badge bg-dark font-monospace mb-2">${escapeHtml(driver.plate_number)}</span>
                                                <div class="small text-muted mb-2">
                                                    <i class="bi bi-clock-history"></i> Mulai: ${escapeHtml(driver.timestamp_masuk)}
                                                </div>
                                                <div class="d-grid gap-1 mt-2">
                                                    <a href="${escapeHtml(driver.gps_masuk)}" target="_blank"
                                                       class="btn btn-sm btn-danger text-white">
                                                        <i class="bi bi-geo-alt-fill me-1"></i>Buka di Google Maps
                                                    </a>
                                                    <a href="${escapeHtml(driver.link_speedo_awal)}" target="_blank"
                                                       class="btn btn-sm btn-outline-secondary">
                                                        <i class="bi bi-speedometer me-1"></i>Cek Speedo Awal
                                                    </a>
                                                </div>
                                            </div>`;
        }

        function focusMapTo(lat, lng, driverId) {
            const latNum = Number(lat);
            const lngNum = Number(lng);
            if (isNaN(latNum) || isNaN(lngNum) || (latNum === 0 && lngNum === 0) || !fleetMap) {
                alert('Koordinat driver tidak valid / belum dikirim dari aplikasi.');
                return;
            }
            document.getElementById('fleet-map').scrollIntoView({ behavior: 'smooth', block: 'center' });
            fleetMap.flyTo([latNum, lngNum], 16, { animate: true, duration: 1.5 });
            fleetMap.once('moveend', function () {
                const key = String(driverId);
                if (driverId && markerRegistry[key]) {
                    markerCluster.zoomToShowLayer(markerRegistry[key], function () {
                        markerRegistry[key].openPopup();
                    });
                }
            });
        }


        // ================================================================
        // INIT CHART
        // ================================================================
        function initActivityChart() {
            const ctx = document.getElementById('activityChart');
            if (!ctx) return;

            // Simpan instance ke variabel global agar bisa di-update (FIX #3)
            activityChartInstance = new Chart(ctx.getContext('2d'), {
                type: 'bar',
                data: {
                    labels: @json($chartLabels),
                    datasets: [{
                        label: 'Total Jarak (Km)',
                        data: @json($chartData),
                        backgroundColor: 'rgba(13,110,253,0.2)',
                        borderColor: 'rgba(13,110,253,1)',
                        borderWidth: 2,
                        borderRadius: 6,
                        borderSkipped: false,
                    }]
                },
                options: {
                    scales: {
                        y: {
                            beginAtZero: true,
                            grid: { color: '#f0f0f0', drawBorder: false },
                            ticks: { callback: v => v + ' Km' }
                        },
                        x: { grid: { display: false, drawBorder: false } }
                    },
                    plugins: {
                        legend: { display: false },
                        tooltip: {
                            backgroundColor: 'rgba(0,0,0,0.8)',
                            padding: 10,
                            callbacks: { label: c => ' ' + c.parsed.y + ' Km' }
                        }
                    },
                    responsive: true,
                    maintainAspectRatio: false,
                    animation: { duration: 400 } // Animasi ringan saat chart update
                }
            });
        }
    </script>
@endpush