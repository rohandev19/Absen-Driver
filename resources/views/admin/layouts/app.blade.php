<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Dashboard Admin')</title>

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
        }

        #sidebar .nav-pills .nav-link:hover,
        #sidebar .nav-pills .nav-link.active {
            background: #495057;
            color: #fff;
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
        }

        .btn-logout:hover {
            background: #495057;
            color: #f8d7da;
        }

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

        /* Mobile Table Responsive */
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
            }
        }
    </style>
</head>

<body>
    <div class="wrapper">
        <nav id="sidebar" class="d-flex flex-column">
            <div>
                <div class="sidebar-header">
                    <h3><i class=""></i> Admin Hamada</h3>
                </div>

                <ul class="nav nav-pills flex-column mb-auto p-2">
                    <li class="nav-item"><a href="{{ route('admin.dashboard') }}"
                            class="nav-link {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}"><i
                                class="bi bi-person-workspace"></i> Aktivitas Driver</a></li>
                    <li class="nav-item"><a href="{{ route('admin.riwayat_driver') }}"
                            class="nav-link {{ request()->routeIs('admin.riwayat_driver') ? 'active' : '' }}"><i
                                class="bi bi-clock-history"></i> Riwayat Driver</a></li>
                    <li class="nav-item"><a href="{{ route('admin.laporan_darurat') }}"
                            class="nav-link {{ request()->routeIs('admin.laporan_darurat') ? 'active' : '' }}"><i
                                class="bi bi-exclamation-triangle"></i> Laporan Darurat</a></li>
                    <li class="nav-item"><a href="{{ route('admin.riwayat_unit') }}"
                            class="nav-link {{ request()->routeIs('admin.riwayat_unit') ? 'active' : '' }}"><i
                                class="bi bi-card-checklist"></i> Riwayat Unit</a></li>
                    <li class="nav-item"><a href="{{ route('admin.driver.index') }}"
                            class="nav-link {{ request()->routeIs('admin.driver.*') ? 'active' : '' }}"><i
                                class="bi bi-person-badge"></i> Kelola Driver</a></li>
                    <li class="nav-item"><a href="{{ route('admin.pengguna.index') }}"
                            class="nav-link {{ request()->routeIs('admin.pengguna.*') ? 'active' : '' }}"><i
                                class="bi bi-people-fill"></i> Kelola Pengguna</a></li>
                    <li class="nav-item"><a href="{{ route('admin.daftar_aset') }}"
                            class="nav-link {{ request()->routeIs('admin.daftar_aset', 'admin.aset.*') ? 'active' : '' }}"><i
                                class="bi bi-truck"></i> Daftar Aset</a></li>
                    <li class="nav-item"><a href="{{ route('admin.maintenance') }}"
                            class="nav-link {{ request()->routeIs('admin.maintenance') ? 'active' : '' }}"><i
                                class="bi bi-calendar-week"></i> Kalender</a></li>
                    <li class="nav-item">
                        <a href="{{ route('admin.maintenance.dashboard') }}"
                            class="nav-link {{ request()->routeIs('admin.maintenance.dashboard') ? 'active' : '' }}">
                            <i class="bi bi-wrench-adjustable-circle"></i> Monitoring & Servis
                        </a>
                    </li>

                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle {{ request()->routeIs('admin.rekap_harian', 'admin.rekap_bulanan') ? 'active' : '' }}"
                            href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false"> <i
                                class="bi bi-journal-check"></i> Rekap
                        </a>
                        <ul class=" dropdown-menu">
                            <li><a class="dropdown-item {{ request()->routeIs('admin.rekap_harian') ? 'active' : '' }}"
                                    href="{{ route('admin.rekap_harian') }}">Rekap Harian</a>
                            </li>
                            <li><a class="dropdown-item {{ request()->routeIs('admin.rekap_bulanan') ? 'active' : '' }}"
                                    href="{{ route('admin.rekap_bulanan') }}">Rekap Bulanan</a></li>
                        </ul>
                    </li>
                </ul>
            </div>

            <div class="sidebar-logout p-2">
                {{-- Form Logout dengan Class Global --}}
                <form action="{{ route('admin.logout') }}" method="POST" class="form-delete-global"
                    data-message="Anda yakin ingin keluar sistem?">
                    @csrf
                    <button type="submit" class="btn btn-logout w-100">
                        <i class="bi bi-box-arrow-right"></i> <span>Logout</span>
                    </button>
                </form>
            </div>
        </nav>

        <div id="main-content">
            <div class="sidebar-overlay"></div>
            <header class="topbar shadow-sm">
                <i class="bi bi-list" id="sidebar-toggle"></i>
            </header>
            <main class="content-area">
                @yield('content')
            </main>
        </div>
    </div>

    {{-- Toast Live Update (Manual) --}}
    <div class="toast-container position-fixed bottom-0 end-0 p-3" style="z-index: 1100">
        <div id="emergencyToast" class="toast hide" role="alert" aria-live="assertive" aria-atomic="true">
            <div class="toast-header bg-danger text-white">
                <strong class="me-auto"><i class="bi bi-exclamation-triangle-fill"></i> Laporan Darurat Baru!</strong>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="toast"
                    aria-label="Close"></button>
            </div>
            <div class="toast-body">
                <p class="mb-1"><strong>Driver:</strong> <span id="toastDriverName"></span></p>
                <p class="mb-2"><strong>Laporan:</strong> <span id="toastDescription"></span></p>
                <a href="{{ route('admin.laporan_darurat') }}" class="btn btn-danger btn-sm w-100">Lihat Semua
                    Laporan</a>
            </div>
        </div>
    </div>

    {{-- === AREA WAJIB UNTUK SISTEM GLOBAL JS === --}}

    {{-- 1. Detektor Flash Message dari Controller --}}
    @if(session('success'))
        <div class="flash-data" data-type="success" data-message="{{ session('success') }}"></div>
    @endif
    @if(session('error'))
        <div class="flash-data" data-type="error" data-message="{{ session('error') }}"></div>
    @endif
    @if(isset($error)) {{-- Jika error dikirim via variabel view --}}
        <div class="flash-data" data-type="error" data-message="{{ $error }}"></div>
    @endif

    {{-- 2. Library & Scripts --}}
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    {{-- 3. Panggil File JS Baru --}}
    <script src="{{ asset('js/global-actions.js') }}"></script>

    @stack('scripts')

    @auth
        <script>
            // Script Sidebar & Live Dashboard (Spesifik Layout)
            document.addEventListener('DOMContentLoaded', function () {
                var sidebarToggle = document.getElementById('sidebar-toggle');
                var sidebar = document.getElementById('sidebar');
                var body = document.body;
                var overlay = document.querySelector('.sidebar-overlay');

                if (sidebarToggle) {
                    sidebarToggle.addEventListener('click', function () {
                        if (window.innerWidth > 992) body.classList.toggle('sidebar-collapsed');
                        else sidebar.classList.toggle('active');
                    });
                }
                if (overlay) {
                    overlay.addEventListener('click', function () { sidebar.classList.remove('active'); });
                }

                // (Logika Live Dashboard Anda tetap di sini...)
                // ...
            });
        </script>
    @endauth
</body>

</html>