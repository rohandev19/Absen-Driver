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
            overflow-y: auto;
            display: flex;
            flex-direction: column;
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
            /* Indikator visual tambahan */
        }

        #sidebar .nav-pills .dropdown-menu {
            background: #343a40;
            border: none;
        }

        #sidebar .nav-pills .dropdown-item {
            color: #adb5bd;
        }

        #sidebar .nav-pills .dropdown-item:hover,
        #sidebar .nav-pills .dropdown-item.active {
            background: #495057;
            color: #fff;
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

        /* Mobile Responsive */
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
            <div>
                <div class="sidebar-header">
                    <h3 class="fw-bold"><i class="bi bi-speedometer2"></i> Admin Hamada</h3>
                </div>

                <ul class="nav nav-pills flex-column mb-auto p-2">
                    <li class="nav-item"><a href="{{ route('admin.dashboard') }}"
                            class="nav-link {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}"><i
                                class="bi bi-person-workspace me-2"></i> Aktivitas Driver</a></li>

                    <li class="nav-item"><a href="{{ route('admin.riwayat_driver') }}"
                            class="nav-link {{ request()->routeIs('admin.riwayat_driver') ? 'active' : '' }}"><i
                                class="bi bi-clock-history me-2"></i> Riwayat Driver</a></li>

                    <li class="nav-item"><a href="{{ route('admin.laporan_darurat') }}"
                            class="nav-link {{ request()->routeIs('admin.laporan_darurat') ? 'active' : '' }}"><i
                                class="bi bi-exclamation-triangle me-2"></i> Laporan Darurat</a></li>

                    <li class="nav-item"><a href="{{ route('admin.riwayat_unit') }}"
                            class="nav-link {{ request()->routeIs('admin.riwayat_unit') ? 'active' : '' }}"><i
                                class="bi bi-card-checklist me-2"></i> Riwayat Unit</a></li>

                    <hr class="border-secondary my-2">

                    <li class="nav-item"><a href="{{ route('admin.driver.index') }}"
                            class="nav-link {{ request()->routeIs('admin.driver.*') ? 'active' : '' }}"><i
                                class="bi bi-person-badge me-2"></i> Kelola Driver</a></li>

                    <li class="nav-item"><a href="{{ route('admin.pengguna.index') }}"
                            class="nav-link {{ request()->routeIs('admin.pengguna.*') ? 'active' : '' }}"><i
                                class="bi bi-people-fill me-2"></i> Kelola Pengguna</a></li>

                    <li class="nav-item"><a href="{{ route('admin.daftar_aset') }}"
                            class="nav-link {{ request()->routeIs('admin.daftar_aset') ? 'active' : '' }}"><i
                                class="bi bi-truck me-2"></i> Daftar Aset</a></li>

                    <hr class="border-secondary my-2">

                    <li class="nav-item">
                        <a href="{{ route('admin.maintenance.dashboard') }}"
                            class="nav-link {{ request()->routeIs('admin.maintenance.dashboard', 'admin.aset.visual', 'admin.aset.riwayat') ? 'active' : '' }}">
                            <i class="bi bi-wrench-adjustable-circle me-2"></i> Monitoring & Servis
                        </a>
                    </li>

                    <li class="nav-item"><a href="{{ route('admin.maintenance') }}"
                            class="nav-link {{ request()->routeIs('admin.maintenance') ? 'active' : '' }}"><i
                                class="bi bi-calendar-week me-2"></i> Kalender Servis</a></li>

                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle {{ request()->routeIs('admin.rekap_harian', 'admin.rekap_bulanan') ? 'active' : '' }}"
                            href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false"> <i
                                class="bi bi-journal-check me-2"></i> Rekap Laporan
                        </a>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item {{ request()->routeIs('admin.rekap_harian') ? 'active' : '' }}"
                                    href="{{ route('admin.rekap_harian') }}">Rekap Harian</a>
                            </li>
                            <li><a class="dropdown-item {{ request()->routeIs('admin.rekap_bulanan') ? 'active' : '' }}"
                                    href="{{ route('admin.rekap_bulanan') }}">Rekap Bulanan</a></li>
                        </ul>
                    </li>
                </ul>
            </div>

            <div class="sidebar-logout p-2 mt-auto">
                {{-- Form Logout dengan Class Global (Tanpa Onclick Manual) --}}
                <form action="{{ route('admin.logout') }}" method="POST" class="form-logout-global">
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
                {{-- User Profile Area --}}
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

    {{-- Toast Live Update (Wadah Saja) --}}
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

    {{-- 1. Detektor Flash Message (Untuk SweetAlert Global) --}}
    @if(session('success'))
        <div class="flash-data" data-type="success" data-message="{{ session('success') }}"></div>
    @endif
    @if(session('error'))
        <div class="flash-data" data-type="error" data-message="{{ session('error') }}"></div>
    @endif

    {{-- 2. Library --}}
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    {{-- 3. Custom JS Global (Menghandle Toast, Delete, Logout, Link Confirm) --}}
    <script src="{{ asset('js/global-actions.js') }}"></script>

    @stack('scripts')

    {{-- 4. Layout Logic (Sidebar Toggle) --}}
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            // Toggle Sidebar
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

            // Note: Script confirmLogout() sudah dihapus karena digantikan oleh global-actions.js
        });
    </script>
</body>

</html>