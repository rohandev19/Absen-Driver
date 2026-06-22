<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="description" content="Portal monitoring armada pelanggan PT Hamada Logistik Internasional. Transparansi penuh atas keandalan dan kesehatan unit kendaraan sewa.">

    <title>@yield('title', 'Portal Customer') - {{ Auth::user()->customer->name ?? 'Hamada Logistik' }}</title>

    <link rel="icon" href="{{ asset('images/logo.png') }}" type="image/png">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">

    {{-- Anti-FOUC (Flash of Unstyled Content) for Dark Mode --}}
    <script>
        (function() {
            var savedTheme = localStorage.getItem('theme');
            var systemPrefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches;
            if (savedTheme === 'dark' || (!savedTheme && systemPrefersDark)) {
                document.documentElement.setAttribute('data-bs-theme', 'dark');
            } else {
                document.documentElement.setAttribute('data-bs-theme', 'light');
            }
        })();
    </script>

    <style>
        /* ============================================
           DESIGN SYSTEM — CUSTOMER PORTAL HAMADA
           ============================================ */
        :root {
            --sidebar-width: 270px;
            --sidebar-bg: #0f1d3d;
            --sidebar-accent: #1e3a8a;
            --sidebar-hover: #2563eb;
            --sidebar-text: #94a3b8;
            --sidebar-text-active: #ffffff;
            --sidebar-border: rgba(59, 130, 246, 0.15);
            --topbar-height: 60px;
            --primary: #1e3a8a;
            --primary-light: #3b82f6;
            --safe-bottom: env(safe-area-inset-bottom, 0px);
            --body-bg: #f1f5f9;
            --blue-soft: #f0f7ff;
            --hover-bg: #f1f5f9;
        }

        /* Dark Mode variables and overrides */
        [data-bs-theme="dark"] {
            --body-bg: #0f172a;
            --topbar-bg: #1e293b;
            --sidebar-bg: #0f1d3d; /* Keep sidebar color consistent but we will style internal components nicely */
            --sidebar-border: rgba(255, 255, 255, 0.08);
            --bs-body-bg: #0f172a;
            --bs-body-color: #f8fafc;
            --primary: #3b82f6;
            --primary-light: #60a5fa;
            --blue-soft: rgba(59, 130, 246, 0.15);
            --hover-bg: rgba(255, 255, 255, 0.04);
        }

        [data-bs-theme="dark"] .topbar {
            background-color: var(--topbar-bg);
            border-bottom-color: rgba(255, 255, 255, 0.08);
            color: #f8fafc;
        }

        [data-bs-theme="dark"] .topbar #sidebar-toggle {
            color: #94a3b8;
        }

        [data-bs-theme="dark"] .topbar #sidebar-toggle:hover {
            background-color: rgba(255, 255, 255, 0.05);
            color: #f8fafc;
        }

        [data-bs-theme="dark"] .topbar-welcome-text .text-muted {
            color: #94a3b8 !important;
        }

        [data-bs-theme="dark"] .topbar-welcome-text .text-dark {
            color: #f8fafc !important;
        }

        [data-bs-theme="dark"] .topbar-user-name {
            color: #f8fafc !important;
        }

        [data-bs-theme="dark"] .card {
            background-color: var(--topbar-bg) !important;
            border-color: rgba(255, 255, 255, 0.08) !important;
            color: #f8fafc !important;
        }

        [data-bs-theme="dark"] .card-header,
        [data-bs-theme="dark"] .card-footer {
            background-color: transparent !important;
            border-color: rgba(255, 255, 255, 0.08) !important;
        }

        [data-bs-theme="dark"] .text-dark {
            color: #f8fafc !important;
        }

        [data-bs-theme="dark"] .text-muted {
            color: #94a3b8 !important;
        }

        [data-bs-theme="dark"] .table {
            --bs-table-color: #f8fafc;
            --bs-table-bg: transparent;
            --bs-table-border-color: rgba(255, 255, 255, 0.08);
            --bs-table-striped-bg: rgba(255, 255, 255, 0.02);
            --bs-table-hover-bg: rgba(255, 255, 255, 0.04);
        }

        [data-bs-theme="dark"] .table th,
        [data-bs-theme="dark"] .table td {
            color: #e2e8f0 !important;
        }

        [data-bs-theme="dark"] .table-light {
            --bs-table-bg: rgba(255, 255, 255, 0.03);
            color: #e2e8f0;
        }

        [data-bs-theme="dark"] .btn-white {
            background-color: rgba(255, 255, 255, 0.05) !important;
            border-color: rgba(255, 255, 255, 0.15) !important;
            color: #f8fafc !important;
        }

        [data-bs-theme="dark"] .btn-white:hover {
            background-color: rgba(255, 255, 255, 0.1) !important;
            border-color: rgba(255, 255, 255, 0.25) !important;
        }

        [data-bs-theme="dark"] .form-control,
        [data-bs-theme="dark"] .form-select {
            background-color: #0f172a !important;
            border-color: rgba(255, 255, 255, 0.12) !important;
            color: #f8fafc !important;
        }

        [data-bs-theme="dark"] .form-control:focus,
        [data-bs-theme="dark"] .form-select:focus {
            background-color: #0f172a !important;
            color: #f8fafc !important;
            border-color: var(--primary) !important;
        }

        [data-bs-theme="dark"] .form-control::placeholder {
            color: #4b5563 !important;
        }

        [data-bs-theme="dark"] .modal-content {
            background-color: var(--topbar-bg) !important;
            color: #f8fafc !important;
            border: 1px solid rgba(255, 255, 255, 0.1) !important;
        }

        [data-bs-theme="dark"] .modal-header,
        [data-bs-theme="dark"] .modal-footer {
            border-color: rgba(255, 255, 255, 0.08) !important;
        }

        [data-bs-theme="dark"] .modal-footer.bg-light {
            background-color: rgba(255, 255, 255, 0.02) !important;
        }

        /* Stepper UI */
        [data-bs-theme="dark"] .stepper-circle {
            background-color: #1e293b !important;
            border-color: #475569 !important;
            color: #94a3b8 !important;
        }

        [data-bs-theme="dark"] .stepper-progress {
            background-color: #334155 !important;
        }

        [data-bs-theme="dark"] .stepper-item.completed .stepper-circle {
            background-color: #10b981 !important;
            border-color: #10b981 !important;
            color: #ffffff !important;
        }

        [data-bs-theme="dark"] .stepper-item.active .stepper-circle {
            background-color: #1e293b !important;
            border-color: #3b82f6 !important;
            color: #3b82f6 !important;
        }

        [data-bs-theme="dark"] .stepper-title {
            color: #94a3b8 !important;
        }

        [data-bs-theme="dark"] .stepper-item.active .stepper-title {
            color: #3b82f6 !important;
        }

        [data-bs-theme="dark"] .stepper-item.completed .stepper-title {
            color: #f8fafc !important;
        }

        /* Badges / alerts inside details */
        [data-bs-theme="dark"] .bg-light.bg-opacity-50 {
            background-color: rgba(255, 255, 255, 0.02) !important;
        }

        [data-bs-theme="dark"] .alert-warning {
            background-color: rgba(245, 158, 11, 0.1) !important;
            border-color: rgba(245, 158, 11, 0.2) !important;
            color: #fef08a !important;
        }

        [data-bs-theme="dark"] .alert-warning .text-dark {
            color: #fef08a !important;
        }

        [data-bs-theme="dark"] .alert-primary {
            background-color: rgba(59, 130, 246, 0.1) !important;
            border-color: rgba(59, 130, 246, 0.2) !important;
            color: #93c5fd !important;
        }

        [data-bs-theme="dark"] .alert-primary .text-dark {
            color: #93c5fd !important;
        }

        [data-bs-theme="dark"] .alert-info {
            background-color: rgba(6, 182, 212, 0.1) !important;
            border-color: rgba(6, 182, 212, 0.2) !important;
            color: #67e8f9 !important;
        }

        [data-bs-theme="dark"] .alert-danger {
            background-color: rgba(239, 68, 68, 0.1) !important;
            border-color: rgba(239, 68, 68, 0.2) !important;
            color: #fca5a5 !important;
        }

        [data-bs-theme="dark"] .table-responsive-cards tr.aset-row {
            background-color: var(--topbar-bg) !important;
            border-color: rgba(255, 255, 255, 0.08) !important;
        }

        [data-bs-theme="dark"] .table-responsive-cards tr.aset-row>td {
            border-bottom-color: rgba(255, 255, 255, 0.05) !important;
        }

        [data-bs-theme="dark"] .pagination .page-link {
            background-color: var(--topbar-bg) !important;
            border-color: rgba(255, 255, 255, 0.1) !important;
            color: #cbd5e1 !important;
        }

        [data-bs-theme="dark"] .pagination .page-item.active .page-link {
            background-color: var(--primary) !important;
            border-color: var(--primary) !important;
            color: #ffffff !important;
        }

        [data-bs-theme="dark"] .pagination .page-item.disabled .page-link {
            background-color: rgba(255, 255, 255, 0.02) !important;
            color: #475569 !important;
        }

        [data-bs-theme="dark"] .sweet-alert,
        [data-bs-theme="dark"] .swal2-popup {
            background-color: var(--topbar-bg) !important;
            color: #f8fafc !important;
            border: 1px solid rgba(255, 255, 255, 0.08) !important;
        }
        
        [data-bs-theme="dark"] .swal2-title,
        [data-bs-theme="dark"] .swal2-html-container {
            color: #f8fafc !important;
        }

        /* Nav links inside sidebar */
        [data-bs-theme="dark"] #sidebar {
            border-right: 1px solid rgba(255, 255, 255, 0.05);
        }

        [data-bs-theme="dark"] .sidebar-header {
            background: rgba(0,0,0,0.2) !important;
            border-bottom: 1px solid rgba(255, 255, 255, 0.05);
        }

        [data-bs-theme="dark"] .sidebar-logo img {
            background: #1e293b;
        }

        [data-bs-theme="dark"] .bg-white {
            background-color: var(--topbar-bg) !important;
            color: #f8fafc !important;
            border-color: rgba(255, 255, 255, 0.08) !important;
        }
        
        [data-bs-theme="dark"] .bg-light {
            background-color: rgba(255, 255, 255, 0.04) !important;
            color: #f8fafc !important;
        }

        * {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
        }

        body {
            background-color: var(--body-bg);
            overflow-x: hidden;
        }

        /* ============================================
           SIDEBAR
           ============================================ */
        .wrapper {
            display: flex;
            width: 100%;
            min-height: 100vh;
        }

        #sidebar {
            width: var(--sidebar-width);
            position: fixed;
            top: 0;
            left: 0;
            height: 100vh;
            z-index: 1050;
            background: var(--sidebar-bg);
            color: var(--sidebar-text);
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            overflow: hidden;
            display: flex;
            flex-direction: column;
            box-shadow: 4px 0 24px rgba(0, 0, 0, 0.15);
        }

        /* --- Sidebar Header (Logo) --- */
        .sidebar-header {
            padding: 1.25rem 1rem;
            text-align: center;
            border-bottom: 1px solid var(--sidebar-border);
            background: rgba(255,255,255,0.03);
            flex-shrink: 0;
        }

        .sidebar-logo {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 0.5rem;
        }

        .sidebar-logo img {
            width: 64px;
            height: 64px;
            object-fit: contain;
            border-radius: 12px;
            background: #fff;
            padding: 6px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.2);
            transition: transform 0.3s ease;
        }

        .sidebar-logo img:hover {
            transform: scale(1.05);
        }

        .sidebar-brand {
            text-align: center;
        }

        .sidebar-brand-name {
            font-size: 1.1rem;
            font-weight: 800;
            letter-spacing: -0.3px;
            line-height: 1.2;
            text-transform: uppercase;
        }

        .brand-h { color: #ef4444; }
        .brand-rest { color: #ffffff; }

        .sidebar-brand-tagline {
            font-size: 0.65rem;
            color: var(--sidebar-text);
            text-transform: uppercase;
            letter-spacing: 1.5px;
            font-weight: 500;
            margin-top: 2px;
        }

        .sidebar-customer-name {
            display: block;
            margin-top: 0.5rem;
            padding: 0.35rem 0.75rem;
            background: rgba(59, 130, 246, 0.15);
            border-radius: 20px;
            font-size: 0.7rem;
            color: #93c5fd;
            font-weight: 600;
            letter-spacing: 0.3px;
        }

        /* --- Sidebar Scroll Area --- */
        .sidebar-scroll-area {
            flex-grow: 1;
            overflow-y: auto;
            overflow-x: hidden;
            padding: 0.5rem 0;
        }

        .sidebar-scroll-area::-webkit-scrollbar {
            width: 4px;
        }
        .sidebar-scroll-area::-webkit-scrollbar-track {
            background: transparent;
        }
        .sidebar-scroll-area::-webkit-scrollbar-thumb {
            background: rgba(59, 130, 246, 0.3);
            border-radius: 10px;
        }

        /* --- Menu Label --- */
        .sidebar-menu-label {
            font-size: 0.6rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 1.5px;
            color: #475569;
            padding: 1rem 1.25rem 0.4rem;
        }

        /* --- Nav Links --- */
        #sidebar .nav-pills .nav-item {
            width: 100%;
            padding: 0 0.5rem;
        }

        #sidebar .nav-pills .nav-link {
            color: var(--sidebar-text);
            padding: 0.65rem 1rem;
            display: flex;
            align-items: center;
            width: 100%;
            border-radius: 10px;
            transition: all 0.2s ease;
            font-size: 0.85rem;
            font-weight: 500;
            margin-bottom: 2px;
            min-height: 44px; /* Touch-friendly */
        }

        #sidebar .nav-pills .nav-link i {
            font-size: 1.1rem;
            width: 24px;
            text-align: center;
            transition: color 0.2s ease;
        }

        #sidebar .nav-pills .nav-link:hover {
            background: rgba(59, 130, 246, 0.12);
            color: #e2e8f0;
        }

        #sidebar .nav-pills .nav-link.active {
            background: linear-gradient(135deg, var(--sidebar-accent), var(--sidebar-hover));
            color: #fff;
            box-shadow: 0 4px 12px rgba(37, 99, 235, 0.25);
        }

        #sidebar .nav-pills .nav-link.active i {
            color: #fff;
        }

        /* --- Sidebar Logout (FIXED for mobile safe area) --- */
        .sidebar-footer {
            border-top: 1px solid var(--sidebar-border);
            background: rgba(0,0,0,0.15);
            flex-shrink: 0;
            padding: 0.5rem;
            padding-bottom: calc(0.5rem + var(--safe-bottom));
        }

        .btn-logout {
            color: #fca5a5;
            padding: 0.7rem 1rem;
            border-radius: 10px;
            text-align: left;
            background: transparent;
            border: none;
            width: 100%;
            display: flex;
            align-items: center;
            font-size: 0.85rem;
            font-weight: 500;
            transition: all 0.2s ease;
            min-height: 44px; /* Touch-friendly */
        }

        .btn-logout:hover {
            background: rgba(220, 38, 38, 0.15);
            color: #fca5a5;
        }

        .btn-logout:active {
            background: #dc2626;
            color: #fff;
        }

        .sidebar-version {
            text-align: center;
            padding: 0.25rem 0;
            font-size: 0.6rem;
            color: #334155;
            letter-spacing: 0.5px;
        }

        /* ============================================
           MAIN CONTENT
           ============================================ */
        #main-content {
            width: 100%;
            padding: 0;
            min-height: 100vh;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            padding-left: var(--sidebar-width);
        }

        /* --- Topbar --- */
        .topbar {
            background: #ffffff;
            border-bottom: 1px solid #e2e8f0;
            padding: 0 1.5rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
            height: var(--topbar-height);
            box-shadow: 0 1px 3px rgba(0,0,0,0.04);
        }

        .topbar #sidebar-toggle {
            display: flex;
            align-items: center;
            justify-content: center;
            width: 40px;
            height: 40px;
            border-radius: 10px;
            font-size: 1.4rem;
            cursor: pointer;
            color: #475569;
            transition: all 0.2s ease;
            min-width: 44px;
            min-height: 44px;
        }

        .topbar #sidebar-toggle:hover {
            background: #f1f5f9;
            color: var(--primary);
        }

        .topbar-user {
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .topbar-user-avatar {
            width: 34px;
            height: 34px;
            border-radius: 10px;
            background: linear-gradient(135deg, var(--primary), var(--primary-light));
            color: #fff;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 700;
            font-size: 0.8rem;
        }

        /* --- Content Area --- */
        .content-area {
            padding: 1.5rem;
        }

        /* ============================================
           SIDEBAR COLLAPSE (Desktop toggle)
           ============================================ */
        body.sidebar-collapsed #sidebar {
            margin-left: calc(-1 * var(--sidebar-width));
        }

        body.sidebar-collapsed #main-content {
            padding-left: 0;
        }

        /* ============================================
           OVERLAY (Mobile)
           ============================================ */
        .sidebar-overlay {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0, 0, 0, 0.5);
            backdrop-filter: blur(2px);
            z-index: 1040;
            transition: opacity 0.3s ease;
        }

        /* ============================================
           RESPONSIVE — MOBILE (≤992px)
           ============================================ */
        @media (max-width: 992px) {
            #sidebar {
                margin-left: calc(-1 * var(--sidebar-width));
            }

            #sidebar.active {
                margin-left: 0;
            }

            #main-content {
                padding-left: 0;
            }

            #sidebar.active ~ #main-content .sidebar-overlay {
                display: block;
            }

            .content-area {
                padding: 1rem 0.75rem;
            }

            .topbar {
                padding: 0 0.75rem;
            }

            .topbar-welcome-text {
                display: none;
            }
        }

        /* ============================================
           RESPONSIVE — SMALL PHONE (≤576px)
           ============================================ */
        @media (max-width: 576px) {
            :root {
                --sidebar-width: 260px;
            }

            .content-area {
                padding: 0.75rem 0.5rem;
            }

            .sidebar-header {
                padding: 1rem 0.75rem;
            }

            .sidebar-logo img {
                width: 52px;
                height: 52px;
            }

            .sidebar-brand-name {
                font-size: 1rem;
            }
        }

        /* ============================================
           RESPONSIVE TABLES (Mobile card layout)
           ============================================ */
        @media (max-width: 992px) {
            .table-responsive:not(.table-responsive-cards) {
                display: block !important;
                width: 100% !important;
                overflow-x: auto !important;
                -webkit-overflow-scrolling: touch !important;
            }

            .table-responsive:not(.table-responsive-cards) .table {
                min-width: 100%;
                width: auto !important;
                white-space: nowrap !important;
            }

            .table-responsive-cards .table {
                white-space: normal !important;
            }

            .table-responsive-cards thead {
                display: none;
            }

            .table-responsive-cards tr.aset-row {
                display: block;
                margin-bottom: 1rem;
                border: 1px solid #e2e8f0;
                border-radius: 12px;
                box-shadow: 0 2px 8px rgba(0, 0, 0, 0.04);
                background: white;
            }

            .table-responsive-cards tr.aset-row>td {
                display: block;
                text-align: left !important;
                padding: 0.85rem;
                border: none;
                border-bottom: 1px solid #f1f5f9;
            }

            .table-responsive-cards tr.aset-row>td:last-child {
                border-bottom: none;
            }

            .table-responsive-cards tr.aset-row>td[data-label]::before {
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

        /* ============================================
           UTILITY: Touch-Friendly
           ============================================ */
        @media (hover: none) and (pointer: coarse) {
            .btn, .nav-link, a {
                min-height: 44px;
                min-width: 44px;
            }
        }

        /* ============================================
           UTILITY: iOS Safe Area (Bottom bar protection)
           ============================================ */
        @supports (padding: env(safe-area-inset-bottom)) {
            .sidebar-footer {
                padding-bottom: calc(0.75rem + env(safe-area-inset-bottom));
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
            .badge {
                border: 1px solid #000 !important;
                color: #000 !important;
                background: transparent !important;
            }
            .shadow-sm, .shadow, .shadow-lg {
                box-shadow: none !important;
            }
        }

        /* ============================================
           STEPPER PROGRESS BAR
           ============================================ */
        .stepper-container {
            padding: 1.5rem 0;
        }
        .stepper-wrapper {
            display: flex;
            justify-content: space-between;
            position: relative;
            margin-bottom: 0.5rem;
        }
        .stepper-progress {
            position: absolute;
            top: 22px;
            left: 16.66%;
            right: 16.66%;
            height: 4px;
            background: #e2e8f0;
            z-index: 1;
            border-radius: 4px;
        }
        .stepper-progress-bar {
            position: absolute;
            top: 0;
            left: 0;
            height: 100%;
            background: linear-gradient(90deg, #10b981 0%, #3b82f6 100%);
            transition: width 0.4s cubic-bezier(0.4, 0, 0.2, 1);
            border-radius: 4px;
        }
        .stepper-wrapper.step-1 .stepper-progress-bar {
            width: 0%;
        }
        .stepper-wrapper.step-2 .stepper-progress-bar {
            width: 50%;
        }
        .stepper-wrapper.step-3 .stepper-progress-bar {
            width: 100%;
        }
        .stepper-item {
            position: relative;
            z-index: 2;
            display: flex;
            flex-direction: column;
            align-items: center;
            flex: 1;
        }
        .stepper-circle {
            width: 44px;
            height: 44px;
            border-radius: 50%;
            background: #ffffff;
            border: 3px solid #cbd5e1;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 700;
            font-size: 0.95rem;
            color: #64748b;
            transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
            box-shadow: 0 2px 4px rgba(0,0,0,0.02);
        }
        .stepper-item.completed .stepper-circle {
            background: #10b981;
            border-color: #10b981;
            color: #ffffff;
            box-shadow: 0 4px 10px rgba(16, 185, 129, 0.2);
        }
        .stepper-item.active .stepper-circle {
            background: #ffffff;
            border-color: #3b82f6;
            color: #3b82f6;
            font-size: 1.05rem;
            box-shadow: 0 0 0 4px rgba(59, 130, 246, 0.15), 0 4px 10px rgba(59, 130, 246, 0.1);
        }
        .stepper-title {
            font-weight: 600;
            font-size: 0.85rem;
            color: #475569;
            margin-top: 0.75rem;
            text-align: center;
            transition: color 0.3s ease;
        }
        .stepper-item.active .stepper-title {
            color: #1e3a8a;
            font-weight: 700;
        }
        .stepper-item.completed .stepper-title {
            color: #0f172a;
        }
        .stepper-subtitle {
            font-size: 0.75rem;
            color: #94a3b8;
            margin-top: 0.15rem;
            text-align: center;
        }
        .stepper-item.active .stepper-subtitle {
            color: #3b82f6;
            font-weight: 500;
        }
        .stepper-item.completed .stepper-subtitle {
            color: #10b981;
            font-weight: 500;
        }

        /* Mobile Stepper Adjustments */
        @media (max-width: 768px) {
            .stepper-circle {
                width: 32px;
                height: 32px;
                border-width: 2px;
                font-size: 0.8rem;
            }
            .stepper-progress {
                top: 14px;
                height: 3px;
                left: 16.66%;
                right: 16.66%;
            }
            .stepper-title {
                font-size: 0.75rem;
                margin-top: 0.5rem;
            }
            .stepper-subtitle {
                display: none;
            }
        }
    </style>
    @stack('styles')
</head>

<body>
    <div class="wrapper">
        {{-- SIDEBAR --}}
        <nav id="sidebar" class="d-flex flex-column">
            {{-- Logo & Brand --}}
            <div class="sidebar-header">
                <div class="sidebar-logo">
                    <img src="{{ asset('images/hamada-logo.png') }}" alt="Hamada Logistik">
                    <div class="sidebar-brand">
                        <div class="sidebar-brand-name">
                            <span class="brand-h">H</span><span class="brand-rest">amada</span>
                            <span class="brand-rest">Logistik</span>
                        </div>
                        <div class="sidebar-brand-tagline">Transport & Logistics</div>
                    </div>
                    <span class="sidebar-customer-name">
                        <i class="bi bi-building me-1"></i>{{ Auth::user()->customer->name ?? 'Customer' }}
                    </span>
                </div>
            </div>

            {{-- Scrollable Menu --}}
            <div class="sidebar-scroll-area">
                <div class="sidebar-menu-label">Menu Utama</div>
                <ul class="nav nav-pills flex-column">
                    <li class="nav-item">
                        <a href="{{ route('customer.dashboard') }}"
                            class="nav-link {{ request()->routeIs('customer.dashboard') ? 'active' : '' }}">
                            <i class="bi bi-speedometer2 me-2"></i> Dashboard
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="{{ route('customer.vehicles') }}"
                            class="nav-link {{ request()->routeIs('customer.vehicles*') ? 'active' : '' }}">
                            <i class="bi bi-truck me-2"></i> Unit Kendaraan
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="{{ route('customer.approve.index') }}"
                            class="nav-link {{ request()->routeIs('customer.approve.*') ? 'active' : '' }}">
                            <i class="bi bi-clipboard-check me-2"></i> Approve Service
                        </a>
                    </li>
                </ul>

                <div class="sidebar-menu-label">Akun</div>
                <ul class="nav nav-pills flex-column">
                    <li class="nav-item">
                        <a href="{{ route('customer.profile') }}"
                            class="nav-link {{ request()->routeIs('customer.profile') ? 'active' : '' }}">
                            <i class="bi bi-person me-2"></i> Profil
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="{{ route('customer.password.form') }}"
                            class="nav-link {{ request()->routeIs('customer.password.*') ? 'active' : '' }}">
                            <i class="bi bi-key me-2"></i> Ganti Password
                        </a>
                    </li>
                </ul>

                <div class="sidebar-menu-label">Informasi</div>
                <ul class="nav nav-pills flex-column">
                    <li class="nav-item">
                        <a href="{{ route('customer.about') }}"
                            class="nav-link {{ request()->routeIs('customer.about') ? 'active' : '' }}">
                            <i class="bi bi-info-circle me-2"></i> Tentang Kami
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="{{ route('customer.privacy') }}"
                            class="nav-link {{ request()->routeIs('customer.privacy') ? 'active' : '' }}">
                            <i class="bi bi-shield-lock me-2"></i> Kebijakan Privasi
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="{{ route('customer.panduan') }}"
                            class="nav-link {{ request()->routeIs('customer.panduan') ? 'active' : '' }}">
                            <i class="bi bi-book me-2"></i> Panduan Penggunaan
                        </a>
                    </li>
                </ul>
            </div>

            {{-- Logout (Safe area protected) --}}
            <div class="sidebar-footer">
                <form action="{{ route('admin.logout') }}" method="POST" class="form-logout-global">
                    @csrf
                    <button type="submit" class="btn-logout">
                        <i class="bi bi-box-arrow-right me-2"></i> <span>Logout</span>
                    </button>
                </form>
                <div class="sidebar-version">
                    &copy; {{ date('Y') }} Hamada Logistik
                </div>
            </div>
        </nav>

        {{-- MAIN CONTENT --}}
        <div id="main-content">
            <div class="sidebar-overlay"></div>

            <header class="topbar sticky-top">
                <div class="d-flex align-items-center">
                    <i class="bi bi-list" id="sidebar-toggle"></i>
                    <span class="ms-3 fw-semibold text-dark d-none d-lg-inline" style="font-size: 0.9rem;">
                        @yield('title', 'Portal Customer')
                    </span>
                </div>
                <div class="d-flex align-items-center gap-3">
                    {{-- DARK MODE TOGGLE --}}
                    <button id="theme-toggle" class="btn btn-sm btn-outline-secondary border-0 rounded-circle" style="width: 36px; height: 36px; display: flex; align-items: center; justify-content: center;" data-bs-toggle="tooltip" title="Toggle Dark Mode">
                        <i class="bi bi-moon-stars-fill" id="theme-icon"></i>
                    </button>

                    <div class="topbar-user">
                        <div class="d-none d-md-flex flex-column text-end topbar-welcome-text">
                            <span class="text-muted" style="font-size: 0.7rem; line-height: 1;">Selamat Datang</span>
                            <span class="fw-bold text-dark" style="font-size: 0.85rem;">{{ Auth::user()->name ?? 'Customer' }}</span>
                        </div>
                        <div class="topbar-user-avatar">
                            {{ strtoupper(substr(Auth::user()->name ?? 'C', 0, 1)) }}
                        </div>
                    </div>
                </div>
            </header>

            <main class="content-area">
                @yield('content')
            </main>

            {{-- Mobile Bottom Safe Area Spacer --}}
            <div style="height: var(--safe-bottom);"></div>
        </div>
    </div>

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

            // Close sidebar on nav click (mobile)
            if (window.innerWidth <= 992) {
                document.querySelectorAll('#sidebar .nav-link').forEach(function(link) {
                    link.addEventListener('click', function() {
                        sidebar.classList.remove('active');
                    });
                });
            }

            // Handle window resize
            var resizeTimer;
            window.addEventListener('resize', function() {
                clearTimeout(resizeTimer);
                resizeTimer = setTimeout(function() {
                    if (window.innerWidth > 992) {
                        sidebar.classList.remove('active');
                    }
                }, 150);
            });

            // DARK MODE TOGGLE LOGIC
            const themeToggleBtn = document.getElementById('theme-toggle');
            const themeIcon = document.getElementById('theme-icon');
            const htmlElement = document.documentElement;

            // Check local storage or system preference
            const savedTheme = localStorage.getItem('theme');
            const systemPrefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches;
            const initialTheme = savedTheme || (systemPrefersDark ? 'dark' : 'light');

            // Apply initial theme icon
            if (initialTheme === 'dark') {
                themeIcon.classList.replace('bi-moon-stars-fill', 'bi-sun-fill');
                themeIcon.classList.add('text-warning');
            }

            // Toggle on click
            if (themeToggleBtn) {
                themeToggleBtn.addEventListener('click', () => {
                    const currentTheme = htmlElement.getAttribute('data-bs-theme');
                    const newTheme = currentTheme === 'dark' ? 'light' : 'dark';
                    
                    htmlElement.setAttribute('data-bs-theme', newTheme);
                    localStorage.setItem('theme', newTheme);
                    
                    if (newTheme === 'dark') {
                        themeIcon.classList.replace('bi-moon-stars-fill', 'bi-sun-fill');
                        themeIcon.classList.add('text-warning');
                    } else {
                        themeIcon.classList.replace('bi-sun-fill', 'bi-moon-stars-fill');
                        themeIcon.classList.remove('text-warning');
                    }
                });
            }
        });
    </script>
</body>

</html>
