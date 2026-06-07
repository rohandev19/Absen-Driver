@extends('admin.layouts.app')

@section('title', 'Dashboard - Aktivitas Driver')

@section('content')
    {{-- MENGGUNAKAN JSDELIVR UNTUK KESTABILAN MAKSIMAL --}}
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/leaflet@1.9.4/dist/leaflet.css" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/leaflet.markercluster@1.5.3/dist/MarkerCluster.css" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/leaflet.markercluster@1.5.3/dist/MarkerCluster.Default.css" />

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

            /* PENTING: Mencegah peta tumpah */
            position: relative;
            overflow: hidden;
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

        /* =============================================
           STAT CARDS (dari _design-system)
        ============================================= */
        .stat-card {
            background: white;
            border-radius: 12px;
            padding: 1.5rem;
            display: flex;
            align-items: center;
            gap: 1rem;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.04);
            transition: all 0.3s cubic-bezier(0.25, 0.46, 0.45, 0.94);
            border: 1px solid #f8f9fa;
            position: relative;
            overflow: hidden;
            text-decoration: none !important;
            color: inherit !important;
        }

        .stat-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 12px 28px rgba(0, 0, 0, 0.08);
            border-color: #e9ecef;
            color: inherit !important;
        }

        .stat-card-danger:hover .stat-icon { box-shadow: 0 8px 24px rgba(220, 53, 69, 0.3); transform: scale(1.05); }
        .stat-card-warning:hover .stat-icon { box-shadow: 0 8px 24px rgba(255, 193, 7, 0.3); transform: scale(1.05); }
        .stat-card-primary:hover .stat-icon { box-shadow: 0 8px 24px rgba(13, 110, 253, 0.3); transform: scale(1.05); }
        .stat-card-success:hover .stat-icon { box-shadow: 0 8px 24px rgba(25, 135, 84, 0.3); transform: scale(1.05); }
        .stat-card-info:hover .stat-icon { box-shadow: 0 8px 24px rgba(13, 202, 240, 0.3); transform: scale(1.05); }

        .stat-icon {
            width: 60px;
            height: 60px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.8rem;
            flex-shrink: 0;
        }

        .stat-card-danger .stat-icon { background: linear-gradient(135deg, #dc3545 0%, #c82333 100%); color: white; }
        .stat-card-warning .stat-icon { background: linear-gradient(135deg, #ffc107 0%, #e0a800 100%); color: white; }
        .stat-card-primary .stat-icon { background: linear-gradient(135deg, #0d6efd 0%, #0b5ed7 100%); color: white; }
        .stat-card-success .stat-icon { background: linear-gradient(135deg, #198754 0%, #157347 100%); color: white; }
        .stat-card-info .stat-icon { background: linear-gradient(135deg, #0dcaf0 0%, #0bacce 100%); color: white; }

        .stat-content { flex: 1; }
        .stat-value { font-size: 2rem; font-weight: 700; line-height: 1; margin-bottom: 0.25rem; color: #2c3e50; }
        .stat-label { font-size: 0.875rem; color: #6c757d; font-weight: 500; }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }
        .animate-fade-in { animation: fadeIn 0.5s ease-out forwards; }
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
                <a href="{{ route('admin.riwayat_driver') }}" class="stat-card stat-card-danger animate-fade-in h-100" style="animation-delay: 0.1s" id="kpi-card-driver">
                    <div class="stat-icon">
                        <i class="bi bi-broadcast"></i>
                    </div>
                    <div class="stat-content">
                        <div class="stat-value" id="kpi-driver-bertugas">{{ count($onDutyDrivers) }}</div>
                        <div class="stat-label">Driver Bertugas</div>
                    </div>
                </a>
            </div>

            <div class="col-lg-3 col-md-6">
                <a href="{{ route('admin.daftar_aset') }}" class="stat-card stat-card-success animate-fade-in h-100" style="animation-delay: 0.2s" id="kpi-card-aset">
                    <div class="stat-icon">
                        <i class="bi bi-p-circle-fill"></i>
                    </div>
                    <div class="stat-content">
                        <div class="stat-value">
                            <span id="kpi-aset-tersedia">{{ $totalAsetTersedia }}</span>
                            <span style="font-size:1rem;color:#6c757d">/ <span id="kpi-total-aset">{{ $totalAset }}</span></span>
                        </div>
                        <div class="stat-label">Aset Tersedia</div>
                    </div>
                </a>
            </div>

            <div class="col-lg-3 col-md-6">
                <a href="{{ route('admin.riwayat_unit') }}" class="stat-card stat-card-info animate-fade-in h-100" style="animation-delay: 0.3s" id="kpi-card-jarak">
                    <div class="stat-icon">
                        <i class="bi bi-sign-turn-right-fill"></i>
                    </div>
                    <div class="stat-content">
                        <div class="stat-value">
                            <span id="kpi-jarak-bulanan">{{ number_format($totalJarakBulanIni) }}</span>
                            <span style="font-size:1rem;color:#6c757d">Km</span>
                        </div>
                        <div class="stat-label">Jarak Bulan Ini</div>
                    </div>
                </a>
            </div>

            <div class="col-lg-3 col-md-6">
                <a href="{{ route('admin.laporan_darurat') }}" class="stat-card stat-card-warning animate-fade-in h-100" style="animation-delay: 0.4s" id="kpi-card-laporan">
                    <div class="stat-icon">
                        <i class="bi bi-exclamation-triangle-fill"></i>
                    </div>
                    <div class="stat-content">
                        <div class="stat-value" id="kpi-laporan-hari-ini">{{ $totalLaporan }}</div>
                        <div class="stat-label">Laporan Hari Ini</div>
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
                                                        onclick="focusMapTo('{{ $driver['latitude'] }}', '{{ $driver['longitude'] }}', '{{ $driver['id'] }}')"
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
    {{-- MENGGUNAKAN JSDELIVR & MEMASTIKAN CHART.JS KEMBALI DIMUAT --}}
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/leaflet@1.9.4/dist/leaflet.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/leaflet.markercluster@1.5.3/dist/leaflet.markercluster.js"></script>

    <script>
        // ================================================================
        // STATE GLOBAL
        // ================================================================
        let fleetMap;
        let markerCluster;
        let activityChartInstance = null;

        const markerRegistry = {};
        let lastReportId = null;
        let countdownInterval;
        let isFetching = false;

        const REFRESH_SECONDS = 30;
        let activeDrivers = @json($onDutyDrivers);

        // ================================================================
        // INIT (DENGAN TRY...CATCH ISOLATION)
        // ================================================================
        document.addEventListener('DOMContentLoaded', function () {
            // Isolasi Peta
            try {
                initFleetMap();
            } catch (error) {
                console.error("Peta gagal dimuat:", error);
            }

            // Isolasi Grafik
            try {
                initActivityChart();
            } catch (error) {
                console.error("Grafik gagal dimuat:", error);
            }

            // Isolasi Auto Refresh
            try {
                startAutoRefresh();
            } catch (error) {
                console.error("Auto Refresh gagal dimuat:", error);
            }
        });

        // ================================================================
        // AUTO-REFRESH
        // ================================================================
        function startAutoRefresh() {
            fetchLiveData(false, true).then(() => {
                scheduleNextRefresh();
            });
            startCountdown();
        }

        function scheduleNextRefresh() {
            setTimeout(async () => {
                await fetchLiveData();
                startCountdown();
                scheduleNextRefresh();
            }, REFRESH_SECONDS * 1000);
        }

        function manualRefresh() {
            if (isFetching) return;
            clearInterval(countdownInterval);
            fetchLiveData(true).then(() => {
                startCountdown();
            });
        }

        async function fetchLiveData(isManual = false, silent = false) {
            if (isFetching) return;
            isFetching = true;
            setRefreshSpinner(true);

            try {
                const response = await fetch('{{ route("admin.dashboard.status") }}', {
                    headers: { 'X-Requested-With': 'XMLHttpRequest' }
                });

                if (!response.ok) throw new Error('HTTP ' + response.status);

                const data = await response.json();

                updateKpiCards(data.kpi);
                updateMapMarkers(data.onDutyDrivers);
                updateDriverList(data.onDutyDrivers);
                updateChart(data.chartLabels, data.chartData);
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
                isFetching = false;
                setRefreshSpinner(false);
            }
        }

        // ================================================================
        // UPDATE UI HELPER
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
            if (valueEl.textContent.trim() === newStr) return;

            valueEl.textContent = newStr;

            if (cardElId) {
                const cardEl = document.getElementById(cardElId);
                if (cardEl) {
                    cardEl.classList.remove('kpi-updated');
                    void cardEl.offsetWidth;
                    cardEl.classList.add('kpi-updated');
                }
            }
        }

        function updateMapMarkers(drivers) {
            if (!fleetMap || !markerCluster) return;

            const currentDriverIds = new Set(drivers.map(d => String(d.id)));

            Object.keys(markerRegistry).forEach(id => {
                if (!currentDriverIds.has(id)) {
                    markerCluster.removeLayer(markerRegistry[id]);
                    delete markerRegistry[id];
                }
            });

            drivers.forEach(driver => {
                const lat = Number(driver.latitude);
                const lng = Number(driver.longitude);
                if (isNaN(lat) || isNaN(lng) || (lat === 0 && lng === 0)) return;

                const driverId = String(driver.id);

                if (markerRegistry[driverId]) {
                    markerRegistry[driverId].setLatLng([lat, lng]);
                    markerRegistry[driverId].setPopupContent(buildPopupContent(driver));
                } else {
                    const marker = L.marker([lat, lng], { icon: buildTruckIcon() });
                    marker.bindPopup(buildPopupContent(driver), {
                        autoPan: true,
                        autoPanPadding: [5, 5],
                    });
                    markerRegistry[driverId] = marker;
                    markerCluster.addLayer(marker);
                }
            });

            activeDrivers = drivers;
        }

        function updateDriverList(drivers) {
            const container = document.getElementById('driver-list-container');
            if (!container) return;

            const scrollArea = document.getElementById('driver-scroll-area');
            const savedScrollTop = scrollArea ? scrollArea.scrollTop : 0;

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

            container.innerHTML = `
                                    <div id="driver-scroll-area" style="max-height:350px; overflow-y:auto;" class="p-3">
                                        <div class="vstack gap-3">${itemsHtml}</div>
                                    </div>`;

            const newScrollArea = document.getElementById('driver-scroll-area');
            if (newScrollArea) newScrollArea.scrollTop = savedScrollTop;

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
                                                   onclick="focusMapTo('${driver.latitude}', '${driver.longitude}', '${driver.id}')"
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

        function updateChart(newLabels, newData) {
            if (!activityChartInstance) return;
            if (!newLabels || !newData) return;

            activityChartInstance.data.labels = newLabels;
            activityChartInstance.data.datasets[0].data = newData;
            activityChartInstance.update('none');
        }

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

        function startCountdown() {
            clearInterval(countdownInterval);
            const bar = document.getElementById('refresh-progress');
            let remaining = REFRESH_SECONDS;
            bar.style.transition = 'none';
            bar.style.width = '100%';
            void bar.offsetWidth;
            bar.style.transition = 'width 1s linear';

            countdownInterval = setInterval(() => {
                remaining--;
                bar.style.width = ((remaining / REFRESH_SECONDS) * 100) + '%';
                if (remaining <= 0) clearInterval(countdownInterval);
            }, 1000);
        }

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
                    item.classList.remove('d-none');
                    item.classList.add('d-flex');
                } else {
                    item.classList.remove('d-flex');
                    item.classList.add('d-none');
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
        // INIT PETA
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
                marker.bindPopup(buildPopupContent(driver), { autoPan: true, autoPanPadding: [5, 5] });

                if (driver.id) markerRegistry[String(driver.id)] = marker;
                markerCluster.addLayer(marker);
                bounds.push([lat, lng]);
            });

            fleetMap.addLayer(markerCluster);

            if (bounds.length === 1) {
                fleetMap.setView(bounds, 13);
            } else if (bounds.length > 1) {
                fleetMap.fitBounds(bounds, { padding: [50, 50], maxZoom: 14, minZoom: 5 });
            }

            // PERBAIKAN ABU-ABU
            setTimeout(function () {
                if (fleetMap) fleetMap.invalidateSize();
            }, 500);
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
                    animation: { duration: 400 }
                }
            });
        }
    </script>
@endpush