<style>
    /* ========================================================================
       MAINTENANCE MODULE - DESIGN SYSTEM
       Centralized CSS for consistent UI/UX across all maintenance pages
       (index, alerts, schedules, components)
       ======================================================================== */

    /* === SECTION 1: CSS VARIABLES & RESET === */
    :root {
        /* Typography */
        --font-size-base: 14px;
        --font-size-small: 0.75rem;
        --font-size-medium: 0.85rem;
        --font-size-large: 0.95rem;
        --font-weight-normal: 500;
        --font-weight-bold: 600;
        --font-weight-extra-bold: 700;
        --letter-spacing-wide: 0.5px;
        --letter-spacing-wider: 0.8px;
        
        /* Spacing */
        --spacing-xs: 6px;
        --spacing-sm: 12px;
        --spacing-md: 16px;
        --spacing-lg: 20px;
        --spacing-xl: 24px;
        
        /* Border Radius */
        --border-radius-sm: 6px;
        --border-radius-md: 8px;
        --border-radius-lg: 12px;
        --border-radius-comfort: 8px;
        
        /* Transitions */
        --transition-smooth: 0.2s ease-in-out;
        --transition-fast: 0.15s ease-in-out;
        
        /* Colors - Status */
        --color-danger: #dc3545;
        --color-danger-light: #fff5f5;
        --color-danger-border: #ffcdd2;
        --color-danger-text: #c62828;
        
        --color-warning: #ffc107;
        --color-warning-light: #fffbf0;
        --color-warning-border: #ffe58f;
        --color-warning-text: #f57f17;
        
        --color-success: #198754;
        --color-success-light: #f6ffed;
        --color-success-border: #b7eb8f;
        --color-success-text: #389e0d;
        
        --color-info: #0dcaf0;
        --color-info-light: #e7f7ff;
        --color-info-border: #91d5ff;
        --color-info-text: #0891b2;
        
        --color-primary: #0d6efd;
        --color-primary-light: #e6f7ff;
        --color-primary-hover: #40a9ff;
        --color-primary-active: #096dd9;
        
        /* Colors - Neutral */
        --color-gray-50: #f8f9fa;
        --color-gray-100: #f0f0f0;
        --color-gray-200: #e9ecef;
        --color-gray-300: #dee2e6;
        --color-gray-400: #adb5bd;
        --color-gray-500: #6c757d;
        --color-gray-600: #495057;
        --color-gray-700: #333;
        --color-gray-800: #212529;
        
        /* Colors - Borders */
        --border-color-light: #e0e0e0;
        --border-color-medium: #d9d9d9;
        --border-color-dark: #b0b0b0;
        
        /* Shadows */
        --shadow-sm: 0 2px 4px rgba(0, 0, 0, 0.05);
        --shadow-md: 0 4px 10px rgba(0, 0, 0, 0.05);
        --shadow-lg: 0 4px 15px rgba(0, 0, 0, 0.05);
        --shadow-xl: 0 8px 20px rgba(0, 0, 0, 0.08);
    }

    body {
        font-size: var(--font-size-base);
    }

    /* === SECTION 2: CARD METRIC COMPONENT === */
    /* Purpose: Display statistics/metrics with visual hierarchy and status indication */
    
    .card-metric {
        background: #fff;
        border: 1px solid var(--border-color-light);
        border-radius: var(--border-radius-comfort);
        padding: var(--spacing-lg) var(--spacing-xl);
        transition: all var(--transition-smooth);
        position: relative;
        overflow: hidden;
        height: 100%;
        border-left: 5px solid transparent;
    }

    .card-metric:hover {
        border-color: var(--border-color-dark);
        box-shadow: var(--shadow-lg);
        transform: translateY(-2px);
    }

    .card-metric.active {
        background-color: #f8fbff;
        box-shadow: var(--shadow-xl);
        transform: translateY(-4px);
    }

    /* Border-left color variants */
    .border-left-danger {
        border-left-color: var(--color-danger);
    }

    .border-left-warning {
        border-left-color: var(--color-warning);
    }

    .border-left-success {
        border-left-color: var(--color-success);
    }

    .border-left-primary {
        border-left-color: var(--color-primary);
    }

    .border-left-info {
        border-left-color: var(--color-info);
    }

    /* Metric card elements */
    .metric-value {
        font-size: 2.2rem;
        font-weight: var(--font-weight-extra-bold);
        color: var(--color-gray-800);
        line-height: 1.2;
        margin-top: 10px;
        margin-bottom: 5px;
    }

    .metric-label {
        font-size: 0.8rem;
        font-weight: var(--font-weight-extra-bold);
        text-transform: uppercase;
        letter-spacing: var(--letter-spacing-wider);
        color: var(--color-gray-500);
    }

    .metric-desc {
        font-size: var(--font-size-medium);
        color: #888;
        margin-top: 4px;
    }

    .stat-link {
        text-decoration: none;
        display: block;
        height: 100%;
        color: inherit;
    }

    .card-icon {
        position: absolute;
        top: var(--spacing-lg);
        right: var(--spacing-lg);
        font-size: 2.5rem;
        opacity: 0.15;
    }

    /* === SECTION 3: TABLE CORPORATE COMPONENT === */
    /* Purpose: Display tabular data with professional styling and mobile responsiveness */
    
    .table-corporate {
        width: 100%;
        border-collapse: collapse;
        background: white;
    }

    .table-corporate thead th {
        background-color: var(--color-gray-50);
        color: var(--color-gray-600);
        font-weight: var(--font-weight-bold);
        font-size: var(--font-size-small);
        text-transform: uppercase;
        letter-spacing: var(--letter-spacing-wide);
        border-bottom: 2px solid var(--color-gray-300);
        border-top: 1px solid var(--color-gray-300);
        padding: 14px var(--spacing-lg);
    }

    .table-corporate tbody td {
        padding: var(--spacing-md) var(--spacing-lg);
        vertical-align: middle;
        border-bottom: 1px solid var(--color-gray-200);
        color: var(--color-gray-700);
    }

    .table-corporate tbody tr:hover {
        background-color: #fdfdfd;
    }

    .table-corporate tbody tr:last-child td {
        border-bottom: none;
    }

    /* === SECTION 4: BADGE COMPONENT === */
    /* Purpose: Display status indicators with consistent colors and styling */
    
    .badge-corp {
        padding: var(--spacing-xs) var(--spacing-sm);
        border-radius: var(--border-radius-sm);
        font-weight: var(--font-weight-bold);
        font-size: var(--font-size-small);
        display: inline-flex;
        align-items: center;
        gap: var(--spacing-xs);
        border: 1px solid transparent;
    }

    .badge-corp-danger {
        background-color: var(--color-danger-light);
        color: var(--color-danger-text);
        border-color: var(--color-danger-border);
    }

    .badge-corp-warning {
        background-color: var(--color-warning-light);
        color: var(--color-warning-text);
        border-color: var(--color-warning-border);
    }

    .badge-corp-success {
        background-color: var(--color-success-light);
        color: var(--color-success-text);
        border-color: var(--color-success-border);
    }

    .badge-corp-info {
        background-color: var(--color-info-light);
        color: var(--color-info-text);
        border-color: var(--color-info-border);
    }

    .badge-corp-primary {
        background-color: var(--color-primary-light);
        color: var(--color-primary-active);
        border-color: var(--color-primary-hover);
    }

    /* === SECTION 5: BUTTON COMPONENTS === */
    /* Purpose: Consistent action buttons across all pages */
    
    /* Secondary Action Button */
    .btn-action-corp {
        background: white;
        border: 1px solid var(--border-color-medium);
        color: var(--color-gray-700);
        padding: var(--spacing-xs) var(--spacing-md);
        font-size: var(--font-size-medium);
        border-radius: var(--border-radius-sm);
        font-weight: var(--font-weight-normal);
        transition: all var(--transition-smooth);
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        gap: var(--spacing-xs);
    }

    .btn-action-corp:hover {
        border-color: var(--color-primary-hover);
        color: var(--color-primary-active);
        background-color: var(--color-primary-light);
    }

    /* Primary Action Button */
    .btn-primary-corp {
        background: #1890ff;
        border: 1px solid #1890ff;
        color: white;
        padding: var(--spacing-xs) var(--spacing-md);
        font-size: var(--font-size-medium);
        border-radius: var(--border-radius-sm);
        font-weight: var(--font-weight-normal);
        display: inline-flex;
        align-items: center;
        gap: var(--spacing-xs);
        transition: all var(--transition-smooth);
    }

    .btn-primary-corp:hover {
        background: var(--color-primary-hover);
        border-color: var(--color-primary-hover);
        color: white;
    }

    /* Destructive Action Button */
    .btn-danger-corp {
        background: #ff4d4f;
        border: 1px solid #ff4d4f;
        color: white;
        padding: var(--spacing-xs) var(--spacing-md);
        font-size: var(--font-size-medium);
        border-radius: var(--border-radius-sm);
        font-weight: var(--font-weight-normal);
        display: inline-flex;
        align-items: center;
        gap: var(--spacing-xs);
        transition: all var(--transition-smooth);
    }

    .btn-danger-corp:hover {
        background: #ff7875;
        border-color: #ff7875;
        color: white;
    }

    /* Button Loading State */
    .btn-action-corp:disabled,
    .btn-primary-corp:disabled,
    .btn-danger-corp:disabled {
        opacity: 0.6;
        cursor: not-allowed;
    }

    .btn-loading {
        position: relative;
        pointer-events: none;
    }

    .btn-loading::after {
        content: "";
        position: absolute;
        width: 16px;
        height: 16px;
        top: 50%;
        left: 50%;
        margin-left: -8px;
        margin-top: -8px;
        border: 2px solid transparent;
        border-top-color: currentColor;
        border-radius: 50%;
        animation: spinner-border 0.75s linear infinite;
    }

    /* === SECTION 6: PROGRESS BAR COMPONENT === */
    /* Purpose: Display health score or progress indicators */
    
    .progress-corp-bg {
        background-color: #eee;
        height: 8px;
        width: 100px;
        border-radius: 2px;
        overflow: hidden;
    }

    .progress-corp-fill {
        height: 100%;
        transition: width var(--transition-smooth);
    }

    /* === SECTION 7: FILTER CONTAINER COMPONENT === */
    /* Purpose: Consistent filter form styling across pages */
    
    .filter-container {
        background: #fbfbfb;
        border: 1px solid #e5e5e5;
        padding: var(--spacing-md) var(--spacing-xl);
        border-radius: var(--border-radius-md);
        margin-bottom: var(--spacing-xl);
    }

    .filter-container .form-label {
        font-size: var(--font-size-small);
        font-weight: var(--font-weight-bold);
        color: var(--color-gray-500);
        margin-bottom: 4px;
        text-transform: uppercase;
        letter-spacing: var(--letter-spacing-wide);
    }

    /* === SECTION 8: EMPTY STATE COMPONENT === */
    /* Purpose: Consistent empty state messaging */
    
    .empty-state {
        text-align: center;
        padding: var(--spacing-xl) 0;
        color: var(--color-gray-500);
    }

    .empty-state i {
        display: block;
        font-size: 3rem;
        opacity: 0.25;
        margin-bottom: 1rem;
    }

    .empty-state h5 {
        font-weight: var(--font-weight-bold);
        margin-bottom: 0.5rem;
        color: var(--color-gray-600);
    }

    .empty-state p {
        font-size: var(--font-size-small);
        color: var(--color-gray-500);
        margin-bottom: 0;
    }

    /* === SECTION 9: PAGE HEADER COMPONENT === */
    /* Purpose: Consistent page header across all maintenance pages */
    
    .page-header {
        margin-bottom: var(--spacing-xl);
    }

    .page-header h3 {
        font-weight: var(--font-weight-extra-bold);
        color: var(--color-gray-800);
        margin-bottom: 4px;
        font-size: 1.5rem;
    }

    .page-header p {
        color: var(--color-gray-500);
        margin-bottom: 0;
        font-size: var(--font-size-medium);
    }

    /* === SECTION 10: MOBILE RESPONSIVE STYLES === */
    /* Breakpoint: max-width 768px (tablets and phones) */
    
    @media (max-width: 768px) {
        /* Table Responsive Transformation */
        .table-corporate thead {
            display: none;
        }

        .table-corporate,
        .table-corporate tbody,
        .table-corporate tr,
        .table-corporate td {
            display: block;
            width: 100%;
        }

        .table-corporate tbody tr {
            margin-bottom: var(--spacing-lg);
            border: 1px solid var(--border-color-light);
            border-radius: var(--border-radius-lg);
            box-shadow: var(--shadow-md);
            background: #fff;
            overflow: hidden;
        }

        .table-corporate td {
            padding: var(--spacing-sm) var(--spacing-md);
            text-align: left;
            border-bottom: 1px solid var(--color-gray-100);
            position: relative;
        }

        .table-corporate td:first-child {
            background-color: var(--color-gray-50);
            border-bottom: 2px solid var(--color-gray-200);
            padding: 15px;
        }

        .table-corporate td:nth-of-type(2)::before {
            content: "STATUS KESEHATAN";
            display: block;
            font-size: 0.7rem;
            font-weight: bold;
            color: var(--color-gray-400);
            margin-bottom: 5px;
        }

        .table-corporate td:nth-of-type(3)::before {
            content: "HEALTH SCORE";
            display: block;
            font-size: 0.7rem;
            font-weight: bold;
            color: var(--color-gray-400);
            margin-bottom: 5px;
        }

        .table-corporate td:nth-of-type(4)::before {
            content: "UPDATE TERAKHIR";
            display: block;
            font-size: 0.7rem;
            font-weight: bold;
            color: var(--color-gray-400);
            margin-bottom: 5px;
        }

        .table-corporate td:last-child {
            border-bottom: none;
            background-color: #fff;
            padding: 15px;
        }

        .table-corporate td:last-child .d-flex {
            justify-content: space-between !important;
            width: 100%;
            gap: 10px;
        }

        .table-corporate td[data-label]::before {
            content: attr(data-label);
            display: block;
            font-size: var(--font-size-small);
            font-weight: var(--font-weight-bold);
            color: var(--color-gray-400);
            margin-bottom: 5px;
            text-transform: uppercase;
        }

        /* Button Responsive */
        .btn-action-corp,
        .btn-primary-corp,
        .btn-danger-corp {
            flex: 1;
            justify-content: center;
            padding: 10px;
        }

        /* Progress Bar Responsive */
        .progress-corp-bg {
            width: 100%;
        }

        /* Filter Container Responsive */
        .filter-container {
            padding: 15px;
        }

        .filter-container select,
        .filter-container input,
        .filter-container button {
            width: 100%;
        }

        .filter-container .d-flex {
            flex-direction: column;
            width: 100%;
        }

        .filter-container .input-group {
            width: 100% !important;
            max-width: 100% !important;
        }

        /* Page Header Responsive */
        .page-header {
            flex-direction: column;
            align-items: flex-start !important;
            gap: 1rem;
        }

        .page-header .text-end {
            width: 100%;
            justify-content: flex-start !important;
        }
    }
</style>
