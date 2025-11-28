@extends('admin.layouts.app')

@section('title', 'Dashboard - Kalender Maintenance')

@section('content')
    <div class="container-fluid p-0">

        <div class="row">
            <div class="col-12">

                {{-- Kartu Kalender --}}
                <div class="card shadow-sm border-0">

                    {{-- Header: Judul & Legenda --}}
                    <div class="card-header bg-white py-3">
                        <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3">

                            {{-- Judul --}}
                            <div>
                                <h5 class="mb-0 fw-bold text-dark">
                                    <i class="bi bi-calendar-week text-primary me-2"></i> Kalender Maintenance
                                </h5>
                                <small class="text-muted">Jadwal perpanjangan STNK & Uji KIR kendaraan</small>
                            </div>

                            {{-- Legenda Status (Badge) --}}
                            <div class="d-flex flex-wrap gap-2">
                                <span class="badge bg-danger bg-opacity-10 text-danger border border-danger px-3 py-2">
                                    <i class="bi bi-exclamation-circle-fill me-1"></i> Lewat Jatuh Tempo
                                </span>
                                <span class="badge bg-warning bg-opacity-10 text-warning border border-warning px-3 py-2">
                                    <i class="bi bi-bell-fill me-1"></i> Segera (H-30)
                                </span>
                                <span class="badge bg-primary bg-opacity-10 text-primary border border-primary px-3 py-2">
                                    <i class="bi bi-file-earmark-text-fill me-1"></i> STNK Aman
                                </span>
                                <span class="badge bg-success bg-opacity-10 text-success border border-success px-3 py-2">
                                    <i class="bi bi-truck me-1"></i> KIR Aman
                                </span>
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

            var calendar = new FullCalendar.Calendar(calendarEl, {
                initialView: 'dayGridMonth',
                locale: 'id',
                themeSystem: 'bootstrap5', // Menggunakan tema Bootstrap
                height: 'auto',            // Tinggi otomatis menyesuaikan konten
                contentHeight: 600,

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
                events: '{{ route("api.maintenance.events") }}',

                // Interaksi
                editable: false,
                dayMaxEvents: true, // Tampilkan "+more" jika terlalu banyak event

                // Saat event diklik
                eventClick: function (info) {
                    info.jsEvent.preventDefault(); // Jangan redirect langsung
                    if (info.event.url) {
                        // Buka di tab baru
                        window.open(info.event.url, '_blank');
                    }
                },

                // Kustomisasi Tampilan Event (Opsional: Menambahkan Tooltip/Icon)
                eventContent: function (arg) {
                    let icon = '';
                    if (arg.event.title.includes('STNK')) icon = '<i class="bi bi-file-text me-1"></i>';
                    else if (arg.event.title.includes('KIR')) icon = '<i class="bi bi-truck me-1"></i>';

                    return { html: `<div class="fc-event-title text-truncate">${icon} ${arg.event.title}</div>` };
                }
            });

            calendar.render();
        });
    </script>

    <style>
        /* === CSS KUSTOM UNTUK MEMPERCANTIK KALENDER === */

        /* Header Toolbar */
        .fc-header-toolbar {
            margin-bottom: 1.5rem !important;
        }

        .fc-toolbar-title {
            font-size: 1.5rem !important;
            font-weight: 700;
            color: #343a40;
        }

        /* Tombol Navigasi (Prev/Next/Today) */
        .fc-button-primary {
            background-color: #fff !important;
            border-color: #dee2e6 !important;
            color: #495057 !important;
            font-weight: 600;
            text-transform: capitalize;
            box-shadow: none !important;
            transition: all 0.2s;
        }

        .fc-button-primary:hover {
            background-color: #f8f9fa !important;
            color: #0d6efd !important;
            border-color: #0d6efd !important;
        }

        .fc-button-active {
            background-color: #e7f1ff !important;
            color: #0d6efd !important;
            border-color: #0d6efd !important;
        }

        /* Grid Kalender */
        .fc-theme-standard td,
        .fc-theme-standard th {
            border-color: #f0f0f0;
            /* Garis lebih halus */
        }

        .fc-col-header-cell {
            background-color: #f8f9fa;
            padding: 10px 0;
            color: #6c757d;
            text-transform: uppercase;
            font-size: 0.85rem;
        }

        /* Hari Ini */
        .fc-day-today {
            background-color: #fff !important;
            /* Reset warna default */
        }

        .fc-day-today .fc-daygrid-day-number {
            background-color: #0d6efd;
            color: white;
            border-radius: 50%;
            width: 28px;
            height: 28px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 4px;
        }

        /* Event (Kotak Jadwal) */
        .fc-event {
            border: none !important;
            border-radius: 6px !important;
            padding: 3px 6px !important;
            font-size: 0.85rem !important;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
            cursor: pointer;
            transition: transform 0.1s;
        }

        .fc-event:hover {
            transform: translateY(-1px);
            filter: brightness(95%);
        }

        /* Angka Tanggal */
        .fc-daygrid-day-number {
            color: #495057;
            font-weight: 500;
            text-decoration: none !important;
            padding: 8px;
        }
    </style>
@endpush