@extends('admin.layouts.app')

@section('title', 'Dashboard - Kalender Maintenance')

@section('content')
    <div class="container-fluid p-0">

        {{-- TAMPILAN BANNER PERINGATAN --}}
        @if(isset($overdueCount) && $overdueCount > 0)
            <div class="alert alert-danger alert-dismissible fade show shadow-sm border-0 d-flex align-items-center mb-4 animate-fade-in"
                role="alert" style="border-left: 5px solid #bd2130 !important;">
                <i class="bi bi-exclamation-triangle-fill fs-3 text-danger me-3"></i>
                <div>
                    <h6 class="alert-heading fw-bold mb-1">Peringatan Kritis!</h6>
                    <span class="mb-0">Terdapat <strong>{{ $overdueCount }} jadwal maintenance</strong> (STNK/KIR) yang
                        sudah <u>lewat jatuh tempo</u> (Merah). Segera lakukan pengecekan!</span>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        {{-- Quick Stats Cards --}}
        <div class="row g-3 mb-4">
            <div class="col-md-3 col-sm-6">
                <div class="stat-card stat-card-danger animate-fade-in" style="animation-delay: 0.1s">
                    <div class="stat-icon">
                        <i class="bi bi-exclamation-triangle-fill"></i>
                    </div>
                    <div class="stat-content">
                        <div class="stat-value" id="stat-overdue">0</div>
                        <div class="stat-label">Lewat Jatuh Tempo</div>
                    </div>
                </div>
            </div>
            <div class="col-md-3 col-sm-6">
                <div class="stat-card stat-card-warning animate-fade-in" style="animation-delay: 0.2s">
                    <div class="stat-icon">
                        <i class="bi bi-bell-fill"></i>
                    </div>
                    <div class="stat-content">
                        <div class="stat-value" id="stat-warning">0</div>
                        <div class="stat-label">Segera (H-30)</div>
                    </div>
                </div>
            </div>
            <div class="col-md-3 col-sm-6">
                <div class="stat-card stat-card-primary animate-fade-in" style="animation-delay: 0.3s">
                    <div class="stat-icon">
                        <i class="bi bi-file-earmark-text-fill"></i>
                    </div>
                    <div class="stat-content">
                        <div class="stat-value" id="stat-stnk">0</div>
                        <div class="stat-label">Total STNK</div>
                    </div>
                </div>
            </div>
            <div class="col-md-3 col-sm-6">
                <div class="stat-card stat-card-success animate-fade-in" style="animation-delay: 0.4s">
                    <div class="stat-icon">
                        <i class="bi bi-truck"></i>
                    </div>
                    <div class="stat-content">
                        <div class="stat-value" id="stat-kir">0</div>
                        <div class="stat-label">Total KIR</div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-12">
                {{-- Kartu Kalender --}}
                <div class="card shadow-sm border-0 animate-fade-in" style="animation-delay: 0.5s">

                    {{-- Header: Judul & Filter --}}
                    <div class="card-header bg-white py-3 border-bottom">
                        <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3">

                            {{-- Judul --}}
                            <div>
                                <h5 class="mb-0 fw-bold text-dark">
                                    <i class="bi bi-calendar-week text-primary me-2"></i> Kalender Maintenance
                                </h5>
                                <small class="text-muted">Jadwal perpanjangan STNK & Uji KIR kendaraan</small>
                            </div>

                            {{-- Filter & Legenda --}}
                            <div class="d-flex flex-wrap gap-2 align-items-center">
                                {{-- Filter Tipe --}}
                                <div class="btn-group" role="group">
                                    <button type="button" class="btn btn-sm btn-outline-secondary filter-btn active" data-filter="all">
                                        <i class="bi bi-grid-3x3-gap-fill me-1"></i> Semua
                                    </button>
                                    <button type="button" class="btn btn-sm btn-outline-primary filter-btn" data-filter="stnk">
                                        <i class="bi bi-file-earmark-text me-1"></i> STNK
                                    </button>
                                    <button type="button" class="btn btn-sm btn-outline-success filter-btn" data-filter="kir">
                                        <i class="bi bi-truck me-1"></i> KIR
                                    </button>
                                </div>

                                {{-- Legenda Status (Badge) --}}
                                <div class="d-flex flex-wrap gap-2">
                                    <span class="badge bg-danger bg-opacity-10 text-danger border border-danger px-2 py-1">
                                        <i class="bi bi-circle-fill me-1" style="font-size: 0.5rem;"></i> Lewat
                                    </span>
                                    <span class="badge bg-warning bg-opacity-10 text-warning border border-warning px-2 py-1">
                                        <i class="bi bi-circle-fill me-1" style="font-size: 0.5rem;"></i> Segera
                                    </span>
                                    <span class="badge bg-primary bg-opacity-10 text-primary border border-primary px-2 py-1">
                                        <i class="bi bi-circle-fill me-1" style="font-size: 0.5rem;"></i> STNK
                                    </span>
                                    <span class="badge bg-success bg-opacity-10 text-success border border-success px-2 py-1">
                                        <i class="bi bi-circle-fill me-1" style="font-size: 0.5rem;"></i> KIR
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="card-body p-4">
                        {{-- Area Kalender --}}
                        <div id='calendar' class="modern-calendar"></div>
                    </div>
                </div>

            </div>
        </div>
    </div>
@endsection

@push('scripts')
    {{-- Load FullCalendar via CDN --}}
    <script src='https://cdn.jsdelivr.net/npm/fullcalendar@6.1.10/index.global.min.js'></script>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            var calendarEl = document.getElementById('calendar');
            var allEvents = [];
            var currentFilter = 'all';

            var calendar = new FullCalendar.Calendar(calendarEl, {
                initialView: 'dayGridMonth',
                locale: 'id',
                themeSystem: 'bootstrap5',
                height: 'auto',
                contentHeight: 650,

                // Konfigurasi Header Toolbar
                headerToolbar: {
                    left: 'prev,next today',
                    center: 'title',
                    right: 'dayGridMonth,listMonth'
                },

                // Teks Tombol Bahasa Indonesia
                buttonText: {
                    today: 'Hari Ini',
                    month: 'Bulan',
                    list: 'List Agenda'
                },

                // Ambil Data dari API
                events: function(info, successCallback, failureCallback) {
                    fetch('{{ route("api.maintenance.events") }}')
                        .then(response => response.json())
                        .then(data => {
                            allEvents = data;
                            updateStats(data);
                            applyFilter(currentFilter);
                            successCallback(filterEvents(data, currentFilter));
                        })
                        .catch(error => {
                            console.error('Error loading events:', error);
                            failureCallback(error);
                        });
                },

                // Interaksi
                editable: false,
                dayMaxEvents: 3,

                // Saat event diklik - tampilkan modal detail
                eventClick: function (info) {
                    info.jsEvent.preventDefault();
                    showEventDetail(info.event);
                },

                // Kustomisasi Tampilan Event
                eventContent: function (arg) {
                    let icon = '';
                    if (arg.event.title.includes('STNK')) icon = '<i class="bi bi-file-text me-1"></i>';
                    else if (arg.event.title.includes('KIR')) icon = '<i class="bi bi-truck me-1"></i>';

                    return { html: `<div class="fc-event-title text-truncate">${icon} ${arg.event.title}</div>` };
                },

                // Hover tooltip
                eventMouseEnter: function(info) {
                    const event = info.event;
                    const tooltip = document.createElement('div');
                    tooltip.className = 'event-tooltip';
                    tooltip.innerHTML = `
                        <strong>${event.title}</strong><br>
                        <small>${formatDate(event.start)}</small>
                    `;
                    tooltip.style.position = 'absolute';
                    tooltip.style.zIndex = '9999';
                    document.body.appendChild(tooltip);
                    
                    const rect = info.el.getBoundingClientRect();
                    tooltip.style.left = rect.left + 'px';
                    tooltip.style.top = (rect.top - tooltip.offsetHeight - 5) + 'px';
                    
                    info.el.tooltip = tooltip;
                },

                eventMouseLeave: function(info) {
                    if (info.el.tooltip) {
                        info.el.tooltip.remove();
                        delete info.el.tooltip;
                    }
                }
            });

            calendar.render();

            // Filter buttons
            document.querySelectorAll('.filter-btn').forEach(btn => {
                btn.addEventListener('click', function() {
                    document.querySelectorAll('.filter-btn').forEach(b => b.classList.remove('active'));
                    this.classList.add('active');
                    currentFilter = this.dataset.filter;
                    applyFilter(currentFilter);
                });
            });

            function filterEvents(events, filter) {
                if (filter === 'all') return events;
                return events.filter(event => {
                    if (filter === 'stnk') return event.id.startsWith('stnk_');
                    if (filter === 'kir') return event.id.startsWith('kir_');
                    return true;
                });
            }

            function applyFilter(filter) {
                const filteredEvents = filterEvents(allEvents, filter);
                calendar.removeAllEvents();
                calendar.addEventSource(filteredEvents);
            }

            function updateStats(events) {
                const today = new Date();
                const warningDate = new Date();
                warningDate.setDate(today.getDate() + 30);

                let overdue = 0, warning = 0, stnk = 0, kir = 0;

                events.forEach(event => {
                    const eventDate = new Date(event.start);
                    
                    if (event.id.startsWith('stnk_')) stnk++;
                    if (event.id.startsWith('kir_')) kir++;
                    
                    if (eventDate < today) {
                        overdue++;
                    } else if (eventDate <= warningDate) {
                        warning++;
                    }
                });

                animateValue('stat-overdue', overdue);
                animateValue('stat-warning', warning);
                animateValue('stat-stnk', stnk);
                animateValue('stat-kir', kir);
            }

            function animateValue(id, value) {
                const element = document.getElementById(id);
                const duration = 1000;
                const start = 0;
                const increment = value / (duration / 16);
                let current = start;

                const timer = setInterval(() => {
                    current += increment;
                    if (current >= value) {
                        element.textContent = value;
                        clearInterval(timer);
                    } else {
                        element.textContent = Math.floor(current);
                    }
                }, 16);
            }

            function formatDate(date) {
                const options = { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' };
                return new Date(date).toLocaleDateString('id-ID', options);
            }

            function showEventDetail(event) {
                const modal = document.createElement('div');
                modal.className = 'modal fade';
                modal.innerHTML = `
                    <div class="modal-dialog modal-dialog-centered">
                        <div class="modal-content">
                            <div class="modal-header border-0 pb-0">
                                <h5 class="modal-title fw-bold">${event.title}</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                            </div>
                            <div class="modal-body">
                                <div class="d-flex align-items-center mb-3">
                                    <i class="bi bi-calendar-event text-primary fs-4 me-3"></i>
                                    <div>
                                        <small class="text-muted d-block">Tanggal Jatuh Tempo</small>
                                        <strong>${formatDate(event.start)}</strong>
                                    </div>
                                </div>
                                <div class="d-flex align-items-center mb-3">
                                    <i class="bi bi-circle-fill fs-6 me-3" style="color: ${event.backgroundColor}"></i>
                                    <div>
                                        <small class="text-muted d-block">Status</small>
                                        <strong>${getStatusText(event)}</strong>
                                    </div>
                                </div>
                            </div>
                            <div class="modal-footer border-0 pt-0">
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
                                <a href="${event.url}" class="btn btn-primary">
                                    <i class="bi bi-pencil-square me-1"></i> Edit Kendaraan
                                </a>
                            </div>
                        </div>
                    </div>
                `;
                document.body.appendChild(modal);
                const bsModal = new bootstrap.Modal(modal);
                bsModal.show();
                modal.addEventListener('hidden.bs.modal', () => modal.remove());
            }

            function getStatusText(event) {
                const today = new Date();
                const eventDate = new Date(event.start);
                const warningDate = new Date();
                warningDate.setDate(today.getDate() + 30);

                if (eventDate < today) return 'Lewat Jatuh Tempo';
                if (eventDate <= warningDate) return 'Segera (H-30)';
                return 'Aman';
            }
        });
    </script>

    <style>
        /* === ANIMATIONS === */
        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .animate-fade-in {
            animation: fadeIn 0.5s ease-out forwards;
        }

        /* === STAT CARDS === */
        .stat-card {
            background: white;
            border-radius: 12px;
            padding: 1.5rem;
            display: flex;
            align-items: center;
            gap: 1rem;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
            transition: all 0.3s ease;
            border: 1px solid #f0f0f0;
            position: relative;
            overflow: hidden;
        }

        .stat-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 4px;
            height: 100%;
            transition: width 0.3s ease;
        }

        .stat-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.12);
        }

        .stat-card:hover::before {
            width: 100%;
            opacity: 0.05;
        }

        .stat-card-danger::before { background: #dc3545; }
        .stat-card-warning::before { background: #ffc107; }
        .stat-card-primary::before { background: #0d6efd; }
        .stat-card-success::before { background: #198754; }

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

        .stat-card-danger .stat-icon {
            background: linear-gradient(135deg, #dc3545 0%, #c82333 100%);
            color: white;
        }

        .stat-card-warning .stat-icon {
            background: linear-gradient(135deg, #ffc107 0%, #e0a800 100%);
            color: white;
        }

        .stat-card-primary .stat-icon {
            background: linear-gradient(135deg, #0d6efd 0%, #0b5ed7 100%);
            color: white;
        }

        .stat-card-success .stat-icon {
            background: linear-gradient(135deg, #198754 0%, #157347 100%);
            color: white;
        }

        .stat-content {
            flex: 1;
        }

        .stat-value {
            font-size: 2rem;
            font-weight: 700;
            line-height: 1;
            margin-bottom: 0.25rem;
            color: #2c3e50;
        }

        .stat-label {
            font-size: 0.875rem;
            color: #6c757d;
            font-weight: 500;
        }

        /* === FILTER BUTTONS === */
        .filter-btn {
            transition: all 0.2s ease;
            font-weight: 500;
        }

        .filter-btn.active {
            transform: scale(1.05);
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.15);
        }

        /* === EVENT TOOLTIP === */
        .event-tooltip {
            background: rgba(0, 0, 0, 0.9);
            color: white;
            padding: 8px 12px;
            border-radius: 6px;
            font-size: 0.85rem;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.2);
            pointer-events: none;
            backdrop-filter: blur(10px);
        }

        /* === CSS KUSTOM UNTUK MEMPERCANTIK KALENDER === */

        /* Header Toolbar */
        .fc-header-toolbar {
            margin-bottom: 1.5rem !important;
            padding: 0.5rem;
            background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
            border-radius: 8px;
        }

        .fc-toolbar-title {
            font-size: 1.5rem !important;
            font-weight: 700;
            color: #2c3e50;
            text-shadow: 0 1px 2px rgba(0, 0, 0, 0.05);
        }

        /* Tombol Navigasi (Prev/Next/Today) */
        .fc-button-primary {
            background-color: #fff !important;
            border-color: #dee2e6 !important;
            color: #495057 !important;
            font-weight: 600;
            text-transform: capitalize;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05) !important;
            transition: all 0.2s;
            border-radius: 6px !important;
        }

        .fc-button-primary:hover {
            background-color: #f8f9fa !important;
            color: #0d6efd !important;
            border-color: #0d6efd !important;
            transform: translateY(-1px);
            box-shadow: 0 4px 8px rgba(13, 110, 253, 0.2) !important;
        }

        .fc-button-active {
            background-color: #0d6efd !important;
            color: white !important;
            border-color: #0d6efd !important;
        }

        /* Grid Kalender */
        .fc-theme-standard td,
        .fc-theme-standard th {
            border-color: #e9ecef;
        }

        .fc-col-header-cell {
            background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
            padding: 12px 0;
            color: #495057;
            text-transform: uppercase;
            font-size: 0.85rem;
            font-weight: 600;
            letter-spacing: 0.5px;
        }

        /* Hari Ini */
        .fc-day-today {
            background-color: #fff !important;
            position: relative;
        }

        .fc-day-today::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: linear-gradient(135deg, rgba(13, 110, 253, 0.05) 0%, rgba(13, 110, 253, 0.02) 100%);
            pointer-events: none;
        }

        .fc-day-today .fc-daygrid-day-number {
            background: linear-gradient(135deg, #0d6efd 0%, #0b5ed7 100%);
            color: white;
            border-radius: 50%;
            width: 32px;
            height: 32px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 4px;
            font-weight: 600;
            box-shadow: 0 2px 8px rgba(13, 110, 253, 0.3);
        }

        /* Event (Kotak Jadwal) */
        .fc-event {
            border: none !important;
            border-radius: 6px !important;
            padding: 5px 8px !important;
            font-size: 0.82rem !important;
            box-shadow: 0 2px 6px rgba(0, 0, 0, 0.08);
            cursor: pointer;
            transition: all 0.2s ease;
            margin-bottom: 3px !important;
            font-weight: 500;
        }

        .fc-event:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
            filter: brightness(105%);
        }

        /* Angka Tanggal */
        .fc-daygrid-day-number {
            color: #495057;
            font-weight: 600;
            text-decoration: none !important;
            padding: 8px;
            transition: all 0.2s;
        }

        .fc-daygrid-day-number:hover {
            color: #0d6efd;
            transform: scale(1.1);
        }

        /* === STYLING UNTUK TOMBOL +MORE & POP-UP === */

        /* Tombol +more (Berapa banyak yang disembunyikan) */
        .fc-daygrid-more-link {
            display: block;
            text-align: center;
            background: linear-gradient(135deg, #e7f1ff 0%, #d0e7ff 100%);
            color: #0d6efd !important;
            font-weight: 600;
            font-size: 0.75rem;
            padding: 4px 0;
            border-radius: 4px;
            margin: 2px 4px;
            text-decoration: none !important;
            transition: all 0.2s;
            box-shadow: 0 1px 3px rgba(13, 110, 253, 0.1);
        }

        .fc-daygrid-more-link:hover {
            background: linear-gradient(135deg, #0d6efd 0%, #0b5ed7 100%);
            color: #fff !important;
            transform: translateY(-1px);
            box-shadow: 0 3px 8px rgba(13, 110, 253, 0.3);
        }

        /* Kotak Pop-up (Saat tombol +more diklik) */
        .fc-popover {
            border: none !important;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.2) !important;
            border-radius: 12px !important;
            overflow: hidden;
            z-index: 1050 !important;
            backdrop-filter: blur(10px);
        }

        /* Header Pop-up (Berisi nama tanggal) */
        .fc-popover-header {
            background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%) !important;
            padding: 12px 16px !important;
            font-weight: bold;
            color: #2c3e50;
            border-bottom: 2px solid #dee2e6;
        }

        /* Body Pop-up (Berisi list event) */
        .fc-popover-body {
            padding: 12px !important;
            max-height: 350px;
            overflow-y: auto;
            background: white;
        }

        /* Jarak antar item di dalam pop-up */
        .fc-popover-body .fc-event {
            margin-bottom: 8px !important;
            white-space: normal !important;
        }

        /* Custom scrollbar untuk pop-up */
        .fc-popover-body::-webkit-scrollbar {
            width: 6px;
        }

        .fc-popover-body::-webkit-scrollbar-track {
            background: #f1f1f1;
            border-radius: 10px;
        }

        .fc-popover-body::-webkit-scrollbar-thumb {
            background: #888;
            border-radius: 10px;
        }

        .fc-popover-body::-webkit-scrollbar-thumb:hover {
            background: #555;
        }

        /* Responsive adjustments */
        @media (max-width: 768px) {
            .stat-card {
                padding: 1rem;
            }

            .stat-icon {
                width: 50px;
                height: 50px;
                font-size: 1.5rem;
            }

            .stat-value {
                font-size: 1.5rem;
            }

            .fc-toolbar-title {
                font-size: 1.2rem !important;
            }
        }
    </style>
@endpush