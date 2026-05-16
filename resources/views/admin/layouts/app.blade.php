<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    {{-- PENTING: CSRF Token untuk request AJAX (wajib ada untuk SweetAlert/Axios) --}}
    <meta name="csrf-token" content="{{ csrf_token() }}">

    {{-- BAGIAN JUDUL DINAMIS --}}
    <title>@yield('title', 'Dashboard') - Admin Hamada</title>

    {{-- BAGIAN FAVICON / LOGO --}}
    <link rel="icon" href="{{ asset('images/logo.png') }}" type="image/png">

    {{-- CDN Styles --}}
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

    {{-- Custom CSS --}}
    <style>
        body {
            background-color: #f8f9fa;
        }

        .wrapper {
            display: flex;
            width: 100%;
            min-height: 100vh;
        }

        #sidebar {
            width: 260px;
            position: fixed;
            top: 0;
            left: 0;
            height: 100vh;
            z-index: 999;
            background: #212529;
            color: #fff;
            transition: all 0.3s;
            /* FIX 1: Hapus overflow-y: auto di sini agar logout tidak ikut terscroll */
            overflow: hidden;
            display: flex;
            flex-direction: column;
        }

        /* FIX 2: Class baru untuk area menu yang bisa discroll */
        .sidebar-scroll-area {
            flex-grow: 1;
            /* Mengisi sisa ruang yang ada */
            overflow-y: auto;
            /* Hanya area ini yang discroll */
            overflow-x: hidden;
        }

        /* Opsional: Mempercantik Scrollbar Sidebar */
        .sidebar-scroll-area::-webkit-scrollbar {
            width: 5px;
        }

        .sidebar-scroll-area::-webkit-scrollbar-track {
            background: #212529;
        }

        .sidebar-scroll-area::-webkit-scrollbar-thumb {
            background: #495057;
            border-radius: 10px;
        }

        #main-content {
            width: 100%;
            padding: 0;
            min-height: 100vh;
            transition: all 0.3s;
            padding-left: 260px;
        }

        .topbar {
            background: #fff;
            border-bottom: 1px solid #dee2e6;
            padding: 0.75rem 1.5rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .topbar #sidebar-toggle {
            display: block;
            font-size: 1.5rem;
            cursor: pointer;
        }

        .content-area {
            padding: 1.5rem;
        }

        .sidebar-header {
            padding: 1.25rem;
            text-align: center;
            border-bottom: 1px solid #495057;
        }

        .sidebar-header h3 {
            color: #fff;
            margin-bottom: 0;
            font-size: 1.5rem;
        }

        /* --- STYLING LINK BIASA --- */
        #sidebar .nav-pills .nav-item {
            width: 100%;
        }

        #sidebar .nav-pills .nav-link {
            color: #adb5bd;
            padding: 0.75rem 1.25rem;
            display: block;
            width: 100%;
            border-radius: 0;
            transition: 0.2s;
        }

        #sidebar .nav-pills .nav-link:hover,
        #sidebar .nav-pills .nav-link.active {
            background: #495057;
            color: #fff;
            border-left: 4px solid #0d6efd;
        }

        /* --- STYLING DROPDOWN / COLLAPSE --- */
        #sidebar .btn-toggle {
            display: flex;
            align-items: center;
            justify-content: space-between;
            width: 100%;
            padding: 0.75rem 1.25rem;
            color: #adb5bd;
            background: transparent;
            border: 0;
            text-align: left;
            transition: 0.2s;
            font-size: 1rem;
            border-radius: 0 !important;
        }

        #sidebar .btn-toggle:hover,
        #sidebar .btn-toggle[aria-expanded="true"] {
            color: #fff;
            background: #495057;
        }

        #sidebar .btn-toggle[aria-expanded="true"] {
            border-left: 4px solid #0d6efd;
        }

        #sidebar .btn-toggle::after {
            width: 1.25em;
            line-height: 0;
            content: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' width='16' height='16' viewBox='0 0 16 16'%3e%3cpath fill='none' stroke='%23adb5bd' stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='M5 14l6-6-6-6'/%3e%3c/svg%3e");
            transition: transform 0.35s ease;
            transform-origin: .5em 50%;
        }

        #sidebar .btn-toggle[aria-expanded="true"]::after {
            transform: rotate(90deg);
        }

        #sidebar .btn-toggle-nav {
            background-color: #2c3034;
        }

        #sidebar .btn-toggle-nav a {
            padding: 0.5rem 1.25rem 0.5rem 2.8rem;
            font-size: 0.95rem;
            color: #adb5bd;
            text-decoration: none;
            display: block;
            border-left: 4px solid transparent;
        }

        #sidebar .btn-toggle-nav a:hover,
        #sidebar .btn-toggle-nav a.active {
            background-color: #343a40;
            color: #fff;
        }

        #sidebar .btn-toggle-nav a.active {
            border-left: 4px solid #0d6efd;
        }

        /* --- LOGOUT BUTTON --- */
        .sidebar-logout {
            /* Pastikan border atas ada pemisah */
            border-top: 1px solid #495057;
            background: #212529;
            /* Pastikan background solid agar tidak transparan saat scroll */
        }

        .btn-logout {
            color: #dc3545;
            padding: 0.75rem 1.25rem;
            border-radius: 0;
            text-align: left;
            background: transparent;
            border: none;
            width: 100%;
        }

        .btn-logout:hover {
            background: #dc3545;
            color: #fff;
        }

        /* Sidebar Collapsed State */
        body.sidebar-collapsed #sidebar {
            margin-left: -260px;
        }

        body.sidebar-collapsed #main-content {
            padding-left: 0;
        }

        .sidebar-overlay {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0, 0, 0, 0.5);
            z-index: 998;
        }

        @media (max-width: 992px) {
            #sidebar {
                margin-left: -260px;
            }

            #sidebar.active {
                margin-left: 0;
            }

            #main-content {
                padding-left: 0;
            }

            #sidebar.active+#main-content .sidebar-overlay {
                display: block;
            }
        }

        /* Mobile Table Responsive Cards */
        @media (max-width: 992px) {
            .table-responsive-cards thead {
                display: none;
            }

            .table-responsive-cards tr.aset-row {
                display: block;
                margin-bottom: 1rem;
                border: 1px solid #ddd;
                border-radius: .375rem;
                box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
                background: white;
            }

            .table-responsive-cards tr.aset-row>td {
                display: block;
                text-align: right !important;
                padding: 0.75rem;
                border: none;
                border-bottom: 1px solid #eee;
            }

            .table-responsive-cards tr.aset-row>td:last-child {
                border-bottom: none;
            }

            .table-responsive-cards tr.aset-row>td[data-label]::before {
                content: attr(data-label);
                float: left;
                font-weight: bold;
                text-align: left;
                padding-right: 1rem;
                color: #6c757d;
            }
        }
    </style>
</head>

<body>
    <div class="wrapper">
        {{-- SIDEBAR --}}
        <nav id="sidebar" class="d-flex flex-column">

            {{-- WRAPPER UNTUK AREA YANG BISA DISROLL (Header + Menu) --}}
            <div class="sidebar-scroll-area">
                <div class="sidebar-header">
                    <h3 class="fw-bold"><i class="bi bi-speedometer2"></i> Admin Hamada</h3>
                </div>

                <ul class="nav nav-pills flex-column mb-auto p-2">

                    {{-- 1. DASHBOARD UTAMA --}}
                    <li class="nav-item">
                        <a href="{{ route('admin.dashboard') }}"
                            class="nav-link {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
                            <i class="bi bi-speedometer me-2"></i> Dashboard
                        </a>
                    </li>

                    <hr class="border-secondary my-2">

                    {{-- 2. GRUP: MANAJEMEN DRIVER --}}
                    <li class="nav-item">
                        <button class="btn btn-toggle d-flex align-items-center rounded collapsed"
                            data-bs-toggle="collapse" data-bs-target="#driver-collapse"
                            aria-expanded="{{ request()->routeIs('admin.driver.*', 'admin.riwayat_driver', 'admin.rekap_harian', 'admin.rekap_bulanan') ? 'true' : 'false' }}">
                            <span><i class="bi bi-person-badge me-2"></i> Manajemen Driver</span>
                        </button>

                        <div class="collapse {{ request()->routeIs('admin.driver.*', 'admin.riwayat_driver', 'admin.rekap_harian', 'admin.rekap_bulanan') ? 'show' : '' }}"
                            id="driver-collapse">
                            <ul class="btn-toggle-nav list-unstyled fw-normal pb-1 small">
                                <li><a href="{{ route('admin.driver.index') }}"
                                        class="{{ request()->routeIs('admin.driver.index') ? 'active' : '' }}">
                                        <i class="bi bi-circle me-2"
                                            style="font-size: 6px; vertical-align: middle;"></i> Kelola Driver</a>
                                </li>
                                <li><a href="{{ route('admin.riwayat_driver') }}"
                                        class="{{ request()->routeIs('admin.riwayat_driver') ? 'active' : '' }}">
                                        <i class="bi bi-circle me-2"
                                            style="font-size: 6px; vertical-align: middle;"></i> Riwayat Driver</a>
                                </li>
                                <li><a href="{{ route('admin.rekap_harian') }}"
                                        class="{{ request()->routeIs('admin.rekap_harian') ? 'active' : '' }}">
                                        <i class="bi bi-circle me-2"
                                            style="font-size: 6px; vertical-align: middle;"></i> Rekap Harian</a>
                                </li>
                                <li><a href="{{ route('admin.rekap_bulanan') }}"
                                        class="{{ request()->routeIs('admin.rekap_bulanan') ? 'active' : '' }}">
                                        <i class="bi bi-circle me-2"
                                            style="font-size: 6px; vertical-align: middle;"></i> Rekap Bulanan</a>
                                </li>
                            </ul>
                        </div>
                    </li>

                    {{-- 3. GRUP: MANAJEMEN UNIT (Aset & Riwayat) --}}
                    <li class="nav-item">
                        <button class="btn btn-toggle d-flex align-items-center rounded collapsed"
                            data-bs-toggle="collapse" data-bs-target="#unit-collapse"
                            aria-expanded="{{ request()->routeIs('admin.daftar_aset', 'admin.riwayat_unit') ? 'true' : 'false' }}">
                            <span><i class="bi bi-truck me-2"></i> Manajemen Unit</span>
                        </button>

                        <div class="collapse {{ request()->routeIs('admin.daftar_aset', 'admin.riwayat_unit') ? 'show' : '' }}"
                            id="unit-collapse">
                            <ul class="btn-toggle-nav list-unstyled fw-normal pb-1 small">
                                <li><a href="{{ route('admin.daftar_aset') }}"
                                        class="{{ request()->routeIs('admin.daftar_aset') ? 'active' : '' }}">
                                        <i class="bi bi-circle me-2"
                                            style="font-size: 6px; vertical-align: middle;"></i> Daftar Aset</a>
                                </li>
                                <li><a href="{{ route('admin.riwayat_unit') }}"
                                        class="{{ request()->routeIs('admin.riwayat_unit') ? 'active' : '' }}">
                                        <i class="bi bi-circle me-2"
                                            style="font-size: 6px; vertical-align: middle;"></i> Riwayat Unit</a>
                                </li>
                            </ul>
                        </div>
                    </li>

                    {{-- 4. GRUP BARU: MAINTENANCE MOBIL (Semua Fitur Perawatan) --}}
                    <li class="nav-item">
                        <button class="btn btn-toggle d-flex align-items-center rounded collapsed"
                            data-bs-toggle="collapse" data-bs-target="#maintenance-collapse"
                            aria-expanded="{{ request()->routeIs('admin.maintenance.*', 'admin.aset.visual', 'admin.aset.riwayat', 'admin.maintenance') ? 'true' : 'false' }}">
                            <span><i class="bi bi-wrench-adjustable me-2"></i> Maintenance Mobil</span>
                        </button>

                        <div class="collapse {{ request()->routeIs('admin.maintenance.*', 'admin.aset.visual', 'admin.aset.riwayat', 'admin.maintenance') ? 'show' : '' }}"
                            id="maintenance-collapse">
                            <ul class="btn-toggle-nav list-unstyled fw-normal pb-1 small">
                                {{-- Link ke Dashboard Monitoring & Health Score --}}
                                <li><a href="{{ route('admin.maintenance.dashboard') }}"
                                        class="{{ request()->routeIs('admin.maintenance.dashboard', 'admin.maintenance.components', 'admin.aset.visual', 'admin.aset.riwayat') ? 'active' : '' }}">
                                        <i class="bi bi-circle me-2"
                                            style="font-size: 6px; vertical-align: middle;"></i> Monitoring Servis</a>
                                </li>
                                {{-- Link ke Kalender Pajak & KIR --}}
                                <li><a href="{{ route('admin.maintenance') }}"
                                        class="{{ request()->routeIs('admin.maintenance') ? 'active' : '' }}">
                                        <i class="bi bi-circle me-2"
                                            style="font-size: 6px; vertical-align: middle;"></i> Kalender Servis</a>
                                </li>
                                {{-- Link ke Peringatan Otomatis --}}
                                <li><a href="{{ route('admin.maintenance.alerts') }}"
                                        class="{{ request()->routeIs('admin.maintenance.alerts') ? 'active' : '' }}">
                                        <i class="bi bi-circle me-2"
                                            style="font-size: 6px; vertical-align: middle;"></i> Peringatan (Alerts)
                                        @php $activeAlerts = \App\Models\MaintenanceAlert::where('status', 'active')->count(); @endphp
                                        @if($activeAlerts > 0)
                                            <span class="badge rounded-pill bg-danger float-end mt-1"
                                                style="font-size: 10px;">{{ $activeAlerts }}</span>
                                        @endif
                                    </a>
                                </li>
                                {{-- Link ke Agenda Jadwal Bengkel --}}
                                <li><a href="{{ route('admin.maintenance.schedules') }}"
                                        class="{{ request()->routeIs('admin.maintenance.schedules') ? 'active' : '' }}">
                                        <i class="bi bi-circle me-2"
                                            style="font-size: 6px; vertical-align: middle;"></i> Jadwal Servis
                                        @php 
                                                                            $upcomingSchedules = \App\Models\MaintenanceSchedule::where('status', 'pending')
                                            ->where('scheduled_date', '<=', now()->addDays(3))
                                            ->count(); 
                                        @endphp
                                        @if($upcomingSchedules > 0)
                                            <span class="badge rounded-pill bg-primary float-end mt-1" style="font-size: 10px;">{{ $upcomingSchedules }}</span>
                                        @endif
                                    </a>
                        </li>
                            </ul>
                </div>
                    </li>
    
                   <hr      class="border-secondary my-2">
 
                         {{-- 5.   LAPORAN & MASTER DATA LAINNYA --}}
                <li     class="nav-item">
                        <a href="{{ route('admin.laporan_darurat') }}"
                    class="nav-link {{ request()->routeIs('admin.laporan_darurat') ? 'active' : '' }}">
                            <i class="bi bi-exclamation-triangle me-2"></i> Laporan Darurat
                        </a>
                </li>
       
                  <li        class="nav-item">
                        <a href="{{ route('admin.project.index') }}"
                    class="nav-link {{ request()->routeIs('admin.project.*') ? 'active' : '' }}">
                            <i class="bi bi-tags-fill me-2"></i> Kelola Project
                        </a>
                    </li>

                         <li    class="nav-item">
                        <a href="{{ route('admin.pengguna.index') }}"
                            class="nav-link {{ request()->routeIs('admin.pengguna.*') ? 'active' : '' }}">
                            <i class="bi bi-people-fill me-2"></i> Kelola Pengguna
                </a>
                    </li>
                </ul>
            </div>

                 {{-- TOM   BOL LOGOUT (Fixed di bawah) --}}
        <div class="    sidebar-logout p-2 mt-auto">
          <for      m action="{{ route('admin.logout') }}" method="POST" class="form-logout-global">
                    @csrf
                    <button type="submit" class="btn btn-logout w-100">
                        <i class="bi bi-box-arrow-right me-2"></i> <span>Logout</span>
            </button>
        </form>
    </div>
</nav>

        {{-- MAIN CONTENT --}}
        <div id="main-content">
            <div class="sidebar-overlay"></div>

            <header class="topbar shadow-sm sticky-top">
                <div class="d-flex align-items-center">
                    <i class="bi bi-list" id="sidebar-toggle"></i>
                </div>
                <div class="d-none d-md-block">
                    <span class="text-muted small">Selamat Datang, </span>
                    <span class="fw-bold">{{ Auth::user()->name ?? 'Admin' }}</span>
                </div>
            </header>

            <main class="content-area">
                @yield('content')
            </main>
        </div>
    </div>

    {{-- Toast Live Update --}}
    <div class="toast-container position-fixed bottom-0 end-0 p-3" style="z-index: 1100">
        <div id="emergencyToast" class="toast hide" role="alert" aria-live="assertive" aria-atomic="true">
            <div class="toast-header bg-danger text-white">
                <strong class="me-auto"><i class="bi bi-exclamation-triangle-fill"></i> Laporan Darurat Baru!</strong>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="toast"
                    aria-label="Close"></button>
            </div>
            <div class="toast-body bg-white">
                <p class="mb-1"><strong>Driver:</strong> <span id="toastDriverName">-</span></p>
                <p class="mb-2"><strong>Laporan:</strong> <span id="toastDescription">-</span></p>
                <a href="{{ route('admin.laporan_darurat') }}" class="btn btn-outline-danger btn-sm w-100">Lihat
                    Detail</a>
            </div>
        </div>
</div>

    {{-- === SYSTEM SCRIPTS === --}}
@if(session('success'))
    <div class="flash-data" data-type="success" data-message="{{ session('success') }}"></div>
@endif
    @if(session('error'))
        <div class="flash-data" data-type="error" data-message="{{ session('error') }}"></div>
    @endif

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="{{ asset('js/global-actions.js') }}"></script>

    @stack('scripts')

    {{-- Layout Logic --}}
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            var sidebarToggle = document.getElementById('sidebar-toggle');
            var sidebar = document.getElementById('sidebar');
            var body = document.body;
            var overlay = document.querySelector('.sidebar-overlay');

            if (sidebarToggle) {
                sidebarToggle.addEventListener('click', function () {
                    if (window.innerWidth > 992) {
                        body.classList.toggle('sidebar-collapsed');
                    } else {
                        sidebar.classList.toggle('active');
                    }
                });
            }
            if (overlay) {
                overlay.addEventListener('click', function () {
                    sidebar.classList.remove('active');
                });
            }
        });
    </script>
</body>

</html>