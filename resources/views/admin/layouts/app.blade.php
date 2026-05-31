<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Dashboard') - Admin Hamada</title>

    {{-- Favicon --}}
    <link rel="icon" href="{{ asset('images/hamadalogo.png') }}?v=2" type="image/png">
    <link rel="shortcut icon" href="{{ asset('images/hamadalogo.png') }}?v=2" type="image/png">

    {{-- CDN Styles --}}
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">

    <style>
        :root {
            --sidebar-width: 255px;
            --sidebar-bg: #0f1117;
            --sidebar-border: rgba(255,255,255,0.07);
            --sidebar-hover: rgba(255,255,255,0.06);
            --sidebar-active: rgba(255,255,255,0.10);
            --sidebar-text: #9ca3af;
            --sidebar-text-active: #ffffff;
            --accent: #3b82f6;
            --accent-dim: rgba(59,130,246,0.15);
            --topbar-bg: #ffffff;
            --body-bg: #f4f6fb;
            --transition: 0.22s cubic-bezier(0.4,0,0.2,1);
        }

        * { box-sizing: border-box; }

        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            background: var(--body-bg);
            margin: 0;
        }

        /* ========== WRAPPER ========== */
        .wrapper {
            display: flex;
            width: 100%;
            min-height: 100vh;
        }

        /* ========== SIDEBAR ========== */
        #sidebar {
            width: var(--sidebar-width);
            position: fixed;
            top: 0;
            left: 0;
            height: 100vh;
            z-index: 999;
            background: var(--sidebar-bg);
            display: flex;
            flex-direction: column;
            overflow: hidden;
            transition: width var(--transition), margin var(--transition);
            border-right: 1px solid var(--sidebar-border);
        }

        /* Scrollable menu area */
        .sidebar-scroll-area {
            flex: 1;
            overflow-y: auto;
            overflow-x: hidden;
        }

        .sidebar-scroll-area::-webkit-scrollbar { width: 3px; }
        .sidebar-scroll-area::-webkit-scrollbar-track { background: transparent; }
        .sidebar-scroll-area::-webkit-scrollbar-thumb {
            background: rgba(255,255,255,0.1);
            border-radius: 4px;
        }

        /* ===== SIDEBAR HEADER / LOGO ===== */
        .sidebar-header {
            padding: 18px 18px 16px;
            border-bottom: 1px solid var(--sidebar-border);
            flex-shrink: 0;
        }

        .sidebar-logo-wrap {
            display: flex;
            flex-direction: column;
            align-items: flex-start;
            justify-content: center;
            gap: 7px;
            text-decoration: none;
        }

        .sidebar-full-logo {
            width: 175px;
            max-width: 100%;
            height: auto;
            display: block;
            object-fit: contain;
        }

        .sidebar-logo-subtitle {
            font-size: 0.72rem;
            color: var(--sidebar-text);
            font-weight: 500;
            letter-spacing: 0.02em;
            margin-left: 2px;
            line-height: 1;
        }

        /* ===== SECTION LABELS ===== */
        .sidebar-section-label {
            font-size: 0.65rem;
            font-weight: 700;
            letter-spacing: 0.1em;
            text-transform: uppercase;
            color: rgba(156,163,175,0.45);
            padding: 18px 18px 6px;
        }

        /* ===== NAV ITEMS ===== */
        #sidebar .nav-pills {
            padding: 6px 10px;
        }

        #sidebar .nav-pills .nav-item {
            width: 100%;
            margin-bottom: 1px;
        }

        #sidebar .nav-pills .nav-link {
            display: flex;
            align-items: center;
            gap: 10px;
            color: var(--sidebar-text);
            padding: 9px 10px;
            border-radius: 8px;
            font-size: 0.875rem;
            font-weight: 500;
            transition: background var(--transition), color var(--transition);
            white-space: nowrap;
            overflow: hidden;
            position: relative;
            text-decoration: none;
        }

        #sidebar .nav-pills .nav-link i {
            font-size: 1rem;
            flex-shrink: 0;
            width: 18px;
            text-align: center;
        }

        #sidebar .nav-pills .nav-link:hover {
            background: var(--sidebar-hover);
            color: var(--sidebar-text-active);
        }

        #sidebar .nav-pills .nav-link.active {
            background: var(--sidebar-active);
            color: var(--sidebar-text-active);
            font-weight: 600;
        }

        #sidebar .nav-pills .nav-link.active::before {
            content: '';
            position: absolute;
            left: 0;
            top: 20%;
            bottom: 20%;
            width: 3px;
            background: var(--accent);
            border-radius: 0 3px 3px 0;
        }

        /* ===== DROPDOWN TOGGLE BUTTON ===== */
        #sidebar .btn-toggle {
            display: flex;
            align-items: center;
            gap: 10px;
            width: 100%;
            padding: 9px 10px;
            color: var(--sidebar-text);
            background: transparent;
            border: 0;
            border-radius: 8px;
            font-size: 0.875rem;
            font-weight: 500;
            font-family: inherit;
            transition: background var(--transition), color var(--transition);
            cursor: pointer;
            text-align: left;
            position: relative;
        }

        #sidebar .btn-toggle i.menu-icon {
            font-size: 1rem;
            flex-shrink: 0;
            width: 18px;
            text-align: center;
        }

        #sidebar .btn-toggle span.menu-label {
            flex: 1;
            overflow: hidden;
            white-space: nowrap;
        }

        #sidebar .btn-toggle:hover,
        #sidebar .btn-toggle[aria-expanded="true"] {
            background: var(--sidebar-hover);
            color: var(--sidebar-text-active);
        }

        #sidebar .btn-toggle[aria-expanded="true"] {
            background: var(--sidebar-active);
            color: #fff;
            font-weight: 600;
        }

        #sidebar .btn-toggle[aria-expanded="true"]::before {
            content: '';
            position: absolute;
            left: 0;
            top: 20%;
            bottom: 20%;
            width: 3px;
            background: var(--accent);
            border-radius: 0 3px 3px 0;
        }

        /* Chevron */
        .toggle-chevron {
            font-size: 0.65rem;
            margin-left: auto;
            flex-shrink: 0;
            transition: transform 0.2s ease;
            color: rgba(156,163,175,0.5);
        }

        #sidebar .btn-toggle[aria-expanded="true"] .toggle-chevron {
            transform: rotate(90deg);
            color: rgba(156,163,175,0.8);
        }

        /* ===== SUBMENU ===== */
        #sidebar .btn-toggle-nav {
            padding: 2px 0 4px 28px;
            list-style: none;
            margin: 0;
        }

        #sidebar .btn-toggle-nav li {
            margin-bottom: 1px;
        }

        #sidebar .btn-toggle-nav a {
            display: flex;
            align-items: center;
            gap: 8px;
            padding: 7px 10px 7px 12px;
            font-size: 0.825rem;
            color: var(--sidebar-text);
            text-decoration: none;
            border-radius: 6px;
            font-weight: 400;
            transition: background var(--transition), color var(--transition);
            border-left: 2px solid transparent;
        }

        #sidebar .btn-toggle-nav a:hover {
            background: var(--sidebar-hover);
            color: var(--sidebar-text-active);
        }

        #sidebar .btn-toggle-nav a.active {
            color: #fff;
            background: rgba(59,130,246,0.12);
            border-left-color: var(--accent);
        }

        /* HR separator */
        #sidebar hr.sidebar-hr {
            border-color: var(--sidebar-border);
            margin: 8px 12px;
            opacity: 1;
        }

        /* ===== LOGOUT SECTION ===== */
        .sidebar-logout {
            padding: 10px 10px 14px;
            border-top: 1px solid var(--sidebar-border);
            flex-shrink: 0;
        }

        .btn-logout {
            display: flex;
            align-items: center;
            gap: 10px;
            width: 100%;
            padding: 9px 10px;
            color: #f87171;
            background: transparent;
            border: none;
            border-radius: 8px;
            font-size: 0.875rem;
            font-weight: 500;
            font-family: inherit;
            cursor: pointer;
            transition: background var(--transition), color var(--transition);
            text-align: left;
        }

        .btn-logout:hover {
            background: rgba(239,68,68,0.12);
            color: #fca5a5;
        }

        /* ========== MAIN CONTENT ========== */
        #main-content {
            width: 100%;
            min-height: 100vh;
            transition: padding-left var(--transition);
            padding-left: var(--sidebar-width);
        }

        /* ========== TOPBAR ========== */
        .topbar {
            background: var(--topbar-bg);
            border-bottom: 1px solid #e9ecef;
            padding: 0 24px;
            height: 58px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            position: sticky;
            top: 0;
            z-index: 100;
        }

        .topbar #sidebar-toggle {
            font-size: 1.3rem;
            cursor: pointer;
            color: #6b7280;
            transition: color 0.15s;
            line-height: 1;
        }

        .topbar #sidebar-toggle:hover { color: #111; }

        .topbar-right {
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .topbar-user {
            display: flex;
            align-items: center;
            gap: 9px;
            background: #f8fafc;
            border: 1px solid #e9ecef;
            border-radius: 50px;
            padding: 5px 14px 5px 6px;
        }

        .topbar-avatar {
            width: 28px;
            height: 28px;
            background: var(--accent);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 0.7rem;
            font-weight: 700;
            color: #fff;
        }

        .topbar-user-name {
            font-size: 0.82rem;
            font-weight: 600;
            color: #374151;
        }

        /* ========== CONTENT AREA ========== */
        .content-area { padding: 24px; }

        /* ========== COLLAPSED STATE ========== */
        body.sidebar-collapsed #sidebar { margin-left: calc(-1 * var(--sidebar-width)); }
        body.sidebar-collapsed #main-content { padding-left: 0; }

        /* Overlay */
        .sidebar-overlay {
            display: none;
            position: fixed;
            inset: 0;
            background: rgba(0,0,0,0.45);
            z-index: 998;
        }

        /* ========== RESPONSIVE ========== */
        @media (max-width: 992px) {
            #sidebar {
                margin-left: calc(-1 * var(--sidebar-width));
            }

            #sidebar.active {
                margin-left: 0;
                box-shadow: 8px 0 32px rgba(0,0,0,0.25);
            }

            #main-content {
                padding-left: 0 !important;
            }

            #sidebar.active ~ #main-content .sidebar-overlay {
                display: block;
            }

            .content-area {
                padding: 16px 12px;
            }

            .topbar {
                padding: 0 12px;
                height: 52px;
            }

            .topbar #sidebar-toggle {
                min-width: 44px;
                min-height: 44px;
                display: flex;
                align-items: center;
                justify-content: center;
            }
        }

        @media (max-width: 576px) {
            .content-area {
                padding: 12px 8px;
            }
        }

        /* ========== TABLE RESPONSIVE ========== */
        @media (max-width: 992px) {
            .table-responsive:not(.table-responsive-cards) .table {
                display: block !important;
                width: 100% !important;
                overflow-x: auto !important;
                -webkit-overflow-scrolling: touch !important;
                white-space: nowrap !important;
            }

            .table-responsive-cards .table {
                white-space: normal !important;
            }

            .table-responsive-cards thead {
                display: none;
            }

            .table-responsive-cards tr.aset-row,
            .table-responsive-cards tr.aset-detail-row {
                display: block;
                margin-bottom: 1rem;
                border: 1px solid #ddd;
                border-radius: .375rem;
                box-shadow: 0 2px 4px rgba(0,0,0,.05);
                background: white;
            }

            .table-responsive-cards tr.aset-row > td,
            .table-responsive-cards tr.aset-detail-row > td {
                display: block;
                text-align: left !important;
                padding: 0.85rem;
                border: none;
                border-bottom: 1px solid #f1f5f9;
            }

            .table-responsive-cards tr.aset-row > td:last-child,
            .table-responsive-cards tr.aset-detail-row > td:last-child {
                border-bottom: none;
            }

            .table-responsive-cards tr.aset-row > td[data-label]::before,
            .table-responsive-cards tr.aset-detail-row > td[data-label]::before {
                content: attr(data-label);
                display: block;
                font-weight: 700;
                color: #64748b;
                font-size: 0.75rem;
                text-transform: uppercase;
                letter-spacing: 0.5px;
                margin-bottom: 0.25rem;
            }
        }

        /* ========== PRINT STYLES ========== */
        @media print {
            @page {
                size: A4 portrait;
                margin: 1.5cm;
            }
            body {
                background: white !important;
                color: #000 !important;
            }
            #sidebar, 
            .topbar, 
            .filter-container, 
            .btn,
            .btn-group,
            .modal,
            .d-print-none {
                display: none !important;
            }
            #main-content {
                margin-left: 0 !important;
                padding: 0 !important;
            }
            .content-area {
                padding: 0 !important;
            }
            .card {
                border: none !important;
                box-shadow: none !important;
                background: transparent !important;
            }
            .card-header {
                background: transparent !important;
                border-bottom: 2px solid #000 !important;
                color: #000 !important;
                padding-left: 0 !important;
                padding-right: 0 !important;
            }
            .table {
                width: 100% !important;
                border-collapse: collapse !important;
            }
            .table th, .table td {
                border: 1px solid #ddd !important;
            }
            .table-responsive {
                overflow: visible !important;
            }
            .badge {
                border: 1px solid #000 !important;
                color: #000 !important;
                background: transparent !important;
            }
            .shadow-sm, .shadow, .shadow-lg {
                box-shadow: none !important;
            }
        }
    </style>
</head>

<body>
<div class="wrapper">

    {{-- ===================== SIDEBAR ===================== --}}
    <nav id="sidebar" class="d-flex flex-column">

        {{-- HEADER / LOGO --}}
        <div class="sidebar-header">
            <a href="{{ route('admin.dashboard') }}" class="sidebar-logo-wrap">
                <img src="{{ asset('images/hamadalogo.png') }}?v=2"
                     alt="Hamada Logistic Logo"
                     class="sidebar-full-logo"
                     onerror="this.style.display='none'; this.nextElementSibling.style.display='block'">
                <span class="sidebar-logo-subtitle" style="display:none; color:#fff; font-weight:700;">HAMADA Admin Panel</span>
                <span class="sidebar-logo-subtitle">Admin Panel</span>
            </a>
        </div>

        {{-- SCROLL AREA --}}
        <div class="sidebar-scroll-area">
            <ul class="nav nav-pills flex-column mb-auto" style="padding: 10px 10px 6px;">

                {{-- 1. DASHBOARD (Master only) --}}
                @if(Auth::user()->isMaster())
                <li class="nav-item">
                    <a href="{{ route('admin.dashboard') }}"
                        class="nav-link {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
                        <i class="bi bi-speedometer2"></i>
                        <span>Dashboard</span>
                    </a>
                </li>

                <div class="sidebar-section-label">Operasional</div>

                {{-- 2. MANAJEMEN DRIVER --}}
                <li class="nav-item">
                    <button class="btn btn-toggle collapsed"
                        data-bs-toggle="collapse" data-bs-target="#driver-collapse"
                        aria-expanded="{{ request()->routeIs('admin.driver.*', 'admin.riwayat_driver', 'admin.rekap_harian', 'admin.rekap_bulanan') ? 'true' : 'false' }}">
                        <i class="bi bi-person-badge menu-icon"></i>
                        <span class="menu-label">Manajemen Driver</span>
                        <i class="bi bi-chevron-right toggle-chevron"></i>
                    </button>
                    <div class="collapse {{ request()->routeIs('admin.driver.*', 'admin.riwayat_driver', 'admin.rekap_harian', 'admin.rekap_bulanan') ? 'show' : '' }}"
                        id="driver-collapse">
                        <ul class="btn-toggle-nav list-unstyled">
                            <li><a href="{{ route('admin.driver.index') }}" class="{{ request()->routeIs('admin.driver.index') ? 'active' : '' }}">Kelola Driver</a></li>
                            <li><a href="{{ route('admin.riwayat_driver') }}" class="{{ request()->routeIs('admin.riwayat_driver') ? 'active' : '' }}">Riwayat Driver</a></li>
                            <li><a href="{{ route('admin.rekap_harian') }}" class="{{ request()->routeIs('admin.rekap_harian') ? 'active' : '' }}">Rekap Harian</a></li>
                            <li><a href="{{ route('admin.rekap_bulanan') }}" class="{{ request()->routeIs('admin.rekap_bulanan') ? 'active' : '' }}">Rekap Bulanan</a></li>
                        </ul>
                    </div>
                </li>
                @endif

                {{-- 3. MANAJEMEN UNIT (Master & Service Admin) --}}
                @if(Auth::user()->isMaster() || Auth::user()->isServiceAdmin())
                <li class="nav-item">
                    <button class="btn btn-toggle collapsed"
                        data-bs-toggle="collapse" data-bs-target="#unit-collapse"
                        aria-expanded="{{ request()->routeIs('admin.daftar_aset', 'admin.riwayat_unit') ? 'true' : 'false' }}">
                        <i class="bi bi-truck menu-icon"></i>
                        <span class="menu-label">Manajemen Unit</span>
                        <i class="bi bi-chevron-right toggle-chevron"></i>
                    </button>
                    <div class="collapse {{ request()->routeIs('admin.daftar_aset', 'admin.riwayat_unit') ? 'show' : '' }}"
                        id="unit-collapse">
                        <ul class="btn-toggle-nav list-unstyled">
                            <li><a href="{{ route('admin.daftar_aset') }}" class="{{ request()->routeIs('admin.daftar_aset') ? 'active' : '' }}">Daftar Aset</a></li>
                            <li><a href="{{ route('admin.riwayat_unit') }}" class="{{ request()->routeIs('admin.riwayat_unit') ? 'active' : '' }}">Riwayat Unit</a></li>
                        </ul>
                    </div>
                </li>

                {{-- 4. MAINTENANCE MOBIL --}}
                <li class="nav-item">
                    <button class="btn btn-toggle collapsed"
                        data-bs-toggle="collapse" data-bs-target="#maintenance-collapse"
                        aria-expanded="{{ request()->routeIs('admin.maintenance.*', 'admin.aset.visual', 'admin.aset.riwayat', 'admin.maintenance') ? 'true' : 'false' }}">
                        <i class="bi bi-wrench-adjustable menu-icon"></i>
                        <span class="menu-label">Maintenance Mobil</span>
                        <i class="bi bi-chevron-right toggle-chevron"></i>
                    </button>
                    <div class="collapse {{ request()->routeIs('admin.maintenance.*', 'admin.aset.visual', 'admin.aset.riwayat', 'admin.maintenance') ? 'show' : '' }}"
                        id="maintenance-collapse">
                        <ul class="btn-toggle-nav list-unstyled">
                            <li><a href="{{ route('admin.maintenance.dashboard') }}" class="{{ request()->routeIs('admin.maintenance.dashboard', 'admin.maintenance.components', 'admin.aset.visual', 'admin.aset.riwayat') ? 'active' : '' }}">Monitoring Servis</a></li>
                            <li><a href="{{ route('admin.maintenance') }}" class="{{ request()->routeIs('admin.maintenance') ? 'active' : '' }}">Kalender Servis</a></li>
                            <li>
                                <a href="{{ route('admin.maintenance.alerts') }}" class="{{ request()->routeIs('admin.maintenance.alerts') ? 'active' : '' }}">
                                    Peringatan (Alerts)
                                    @php $activeAlerts = \App\Models\MaintenanceAlert::where('status', 'active')->count(); @endphp
                                    @if($activeAlerts > 0)
                                        <span class="badge rounded-pill bg-danger ms-auto" style="font-size:9px;padding:2px 6px;">{{ $activeAlerts }}</span>
                                    @endif
                                </a>
                            </li>
                            <li>
                                <a href="{{ route('admin.maintenance.schedules') }}" class="{{ request()->routeIs('admin.maintenance.schedules') ? 'active' : '' }}">
                                    Jadwal Servis
                                    @php $upcomingSchedules = \App\Models\MaintenanceSchedule::where('status','pending')->where('scheduled_date','<=',now()->addDays(3))->count(); @endphp
                                    @if($upcomingSchedules > 0)
                                        <span class="badge rounded-pill bg-primary ms-auto" style="font-size:9px;padding:2px 6px;">{{ $upcomingSchedules }}</span>
                                    @endif
                                </a>
                            </li>
                        </ul>
                    </div>
                </li>

                {{-- 5. SERVICE DARURAT --}}
                <li class="nav-item">
                    <a href="{{ route('admin.service.index') }}"
                        class="nav-link {{ request()->routeIs('admin.service.*') ? 'active' : '' }}">
                        <i class="bi bi-tools"></i>
                        <span>Service Darurat</span>
                    </a>
                </li>
                @endif

                @if(Auth::user()->isMaster())
                <hr class="sidebar-hr">
                <div class="sidebar-section-label">Keuangan & Laporan</div>

                {{-- 6. UANG JALAN --}}
                <li class="nav-item">
                    <button class="btn btn-toggle collapsed"
                        data-bs-toggle="collapse" data-bs-target="#transport-cost-collapse"
                        aria-expanded="{{ request()->routeIs('admin.transport-costs.*') ? 'true' : 'false' }}">
                        <i class="bi bi-cash-coin menu-icon"></i>
                        <span class="menu-label">Uang Jalan</span>
                        <i class="bi bi-chevron-right toggle-chevron"></i>
                    </button>
                    <div class="collapse {{ request()->routeIs('admin.transport-costs.*') ? 'show' : '' }}"
                        id="transport-cost-collapse">
                        <ul class="btn-toggle-nav list-unstyled">
                            <li><a href="{{ route('admin.transport-costs.dashboard') }}" class="{{ request()->routeIs('admin.transport-costs.dashboard') ? 'active' : '' }}">Dashboard</a></li>
                            <li>
                                <a href="{{ route('admin.transport-costs.index') }}" class="{{ request()->routeIs('admin.transport-costs.index', 'admin.transport-costs.show') ? 'active' : '' }}">
                                    Daftar Laporan
                                    @php $pendingTrips = \App\Models\TransportCost::where('approval_status', 'pending')->count(); @endphp
                                    @if($pendingTrips > 0)
                                        <span class="badge rounded-pill bg-warning text-dark ms-auto" style="font-size:9px;padding:2px 6px;">{{ $pendingTrips }}</span>
                                    @endif
                                </a>
                            </li>
                            <li><a href="{{ route('admin.transport-costs.recap') }}" class="{{ request()->routeIs('admin.transport-costs.recap') ? 'active' : '' }}">Rekap Bulanan</a></li>
                        </ul>
                    </div>
                </li>

                {{-- 7. LAPORAN DARURAT --}}
                <li class="nav-item">
                    <a href="{{ route('admin.laporan_darurat') }}"
                        class="nav-link {{ request()->routeIs('admin.laporan_darurat') ? 'active' : '' }}">
                        <i class="bi bi-exclamation-triangle"></i>
                        <span>Laporan Darurat</span>
                    </a>
                </li>

                <hr class="sidebar-hr">
                <div class="sidebar-section-label">Manajemen</div>

                {{-- 8. KELOLA PROJECT --}}
                <li class="nav-item">
                    <a href="{{ route('admin.project.index') }}"
                        class="nav-link {{ request()->routeIs('admin.project.*') ? 'active' : '' }}">
                        <i class="bi bi-tags-fill"></i>
                        <span>Kelola Project</span>
                    </a>
                </li>

                {{-- 9. KELOLA CUSTOMER --}}
                <li class="nav-item">
                    <a href="{{ route('admin.customer.index') }}"
                        class="nav-link {{ request()->routeIs('admin.customer.*') ? 'active' : '' }}">
                        <i class="bi bi-building"></i>
                        <span>Kelola Customer</span>
                    </a>
                </li>

                {{-- 10. KELOLA PENGGUNA --}}
                <li class="nav-item">
                    <a href="{{ route('admin.pengguna.index') }}"
                        class="nav-link {{ request()->routeIs('admin.pengguna.*') ? 'active' : '' }}">
                        <i class="bi bi-people-fill"></i>
                        <span>Kelola Pengguna</span>
                    </a>
                </li>
                @endif

            </ul>
        </div>{{-- end sidebar-scroll-area --}}

        {{-- LOGOUT (Fixed bawah) --}}
        <div class="sidebar-logout">
            <form action="{{ route('admin.logout') }}" method="POST" class="form-logout-global">
                @csrf
                <button type="submit" class="btn-logout">
                    <i class="bi bi-box-arrow-right" style="font-size:1rem; flex-shrink:0"></i>
                    <span>Logout</span>
                </button>
            </form>
        </div>
    </nav>{{-- end #sidebar --}}

    {{-- ===================== MAIN CONTENT ===================== --}}
    <div id="main-content">
        <div class="sidebar-overlay"></div>

        {{-- TOPBAR --}}
        <header class="topbar shadow-sm">
            <div class="d-flex align-items-center gap-3">
                <i class="bi bi-list" id="sidebar-toggle"></i>
            </div>
            <div class="topbar-right">
                <div class="topbar-user">
                    <div class="topbar-avatar">
                        {{ strtoupper(substr(Auth::user()->name ?? 'A', 0, 1)) }}
                    </div>
                    <span class="topbar-user-name d-none d-md-block">{{ Auth::user()->name ?? 'Admin' }}</span>
                </div>
            </div>
        </header>

        {{-- PAGE CONTENT --}}
        <main class="content-area">
            @yield('content')
        </main>
    </div>
</div>

{{-- Toast --}}
<div class="toast-container position-fixed bottom-0 end-0 p-3" style="z-index:1100">
    <div id="emergencyToast" class="toast hide" role="alert" aria-live="assertive" aria-atomic="true">
        <div class="toast-header bg-danger text-white">
            <strong class="me-auto"><i class="bi bi-exclamation-triangle-fill"></i> Laporan Darurat Baru!</strong>
            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="toast" aria-label="Close"></button>
        </div>
        <div class="toast-body bg-white">
            <p class="mb-1"><strong>Driver:</strong> <span id="toastDriverName">-</span></p>
            <p class="mb-2"><strong>Laporan:</strong> <span id="toastDescription">-</span></p>
            <a href="{{ route('admin.laporan_darurat') }}" class="btn btn-outline-danger btn-sm w-100">Lihat Detail</a>
        </div>
    </div>
</div>

{{-- Flash Messages --}}
@if(session('success'))
    <div class="flash-data" data-type="success" data-message="{{ session('success') }}"></div>
@endif
@if(session('error'))
    <div class="flash-data" data-type="error" data-message="{{ session('error') }}"></div>
@endif

{{-- Scripts --}}
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="{{ asset('js/global-actions.js') }}"></script>

@stack('scripts')

<script>
    document.addEventListener('DOMContentLoaded', function () {
        var toggle = document.getElementById('sidebar-toggle');
        var sidebar = document.getElementById('sidebar');
        var body = document.body;
        var overlay = document.querySelector('.sidebar-overlay');

        if (toggle) {
            toggle.addEventListener('click', function () {
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
