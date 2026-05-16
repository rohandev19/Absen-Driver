# Maintenance UI/UX Consistency Bugfix Design

## Overview

Modul Maintenance saat ini memiliki inkonsistensi desain yang signifikan di 4 halaman utama (index, alerts, schedules, components). Setiap halaman menggunakan pattern yang berbeda untuk cards, tables, buttons, badges, dan styling lainnya, menciptakan pengalaman pengguna yang membingungkan dan tidak profesional.

**Bug Impact:**
- User Experience: Admin kesulitan beradaptasi karena setiap halaman terlihat berbeda
- Professional Appearance: Sistem terlihat tidak terintegrasi dengan baik
- Mobile Usability: Inkonsistensi responsive design menyulitkan akses mobile
- User Confidence: Inkonsistensi mengurangi kepercayaan terhadap sistem

**Fix Approach:**
Membuat design system yang komprehensif dengan standardisasi komponen UI, color palette, typography, dan responsive patterns. Implementasi menggunakan reusable CSS classes yang dapat diterapkan di semua halaman maintenance dengan mudah, tanpa mengubah functional behavior yang sudah ada.

**Technology Stack:**
- Laravel Blade Templates
- Bootstrap 5.3.3 (existing framework)
- Bootstrap Icons 1.11.3
- Custom CSS (corporate design system)
- SweetAlert2 (existing alert library)

## Glossary

- **Bug_Condition (C)**: Kondisi dimana halaman maintenance menampilkan styling yang berbeda untuk komponen yang sama (cards, tables, buttons, badges)
- **Property (P)**: Desired behavior dimana semua halaman maintenance menggunakan styling yang konsisten dari design system
- **Preservation**: Functional behavior (filtering, form submission, navigation, data display) yang harus tetap tidak berubah
- **Design System**: Kumpulan standardized components, colors, typography, dan patterns yang digunakan konsisten di seluruh modul
- **Component Library**: Reusable CSS classes yang dapat digunakan untuk membangun UI konsisten
- **card-metric**: Standardized card component untuk menampilkan metrics/statistics dengan border-left colored
- **table-corporate**: Standardized table styling dengan professional appearance
- **badge-corp**: Standardized badge component dengan consistent colors dan borders
- **btn-action-corp**: Standardized button untuk secondary actions
- **filter-container**: Standardized container untuk filter forms
- **Responsive Transformation**: Pattern untuk mengubah table menjadi card layout di mobile devices
- **Health Score**: Calculated score (0-100) yang menunjukkan kondisi kendaraan berdasarkan komponen
- **Status Code**: Enum value yang menentukan kondisi kendaraan (physical_issue, overdue, critical, warning, healthy)

## Bug Details

### Bug Condition

Bug terjadi ketika admin membuka halaman-halaman di modul maintenance dan melihat styling yang berbeda untuk komponen yang seharusnya sama. Setiap halaman (index, alerts, schedules, components) menggunakan pattern CSS yang berbeda, menyebabkan pengalaman yang tidak kohesif.

**Formal Specification:**
```
FUNCTION isBugCondition(input)
  INPUT: input of type PageView
  OUTPUT: boolean
  
  RETURN input.page IN ['index', 'alerts', 'schedules', 'components']
         AND input.module == 'maintenance'
         AND (
           hasInconsistentCardStyling(input.page) OR
           hasInconsistentTableStyling(input.page) OR
           hasInconsistentButtonStyling(input.page) OR
           hasInconsistentBadgeStyling(input.page) OR
           hasInconsistentColorScheme(input.page) OR
           hasInconsistentMobileResponsiveness(input.page) OR
           hasInconsistentLoadingStates(input.page) OR
           hasInconsistentEmptyStates(input.page) OR
           hasInconsistentFilterContainer(input.page) OR
           hasInconsistentHeaderLayout(input.page)
         )
END FUNCTION
```

### Examples

**Example 1: Inconsistent Card Design**
- **index.blade.php**: Uses `card-metric` with border-left colored (5px), hover effect translateY(-2px), icon positioned absolute
- **alerts.blade.php**: Uses `border-start border-4` without hover effect, different padding
- **Expected**: All pages should use `card-metric` with consistent styling

**Example 2: Inconsistent Table Styling**
- **index.blade.php**: Uses `table-corporate` with custom thead styling (#f8f9fa background, uppercase text, letter-spacing)
- **schedules.blade.php**: Uses `table table-hover` with Bootstrap default styling
- **Expected**: All pages should use `table-corporate` with consistent styling

**Example 3: Inconsistent Status Colors**
- **index.blade.php**: "critical" status shows warning color (#ffc107)
- **schedules.blade.php**: "critical" priority shows danger color (#dc3545)
- **Expected**: "critical" should consistently use warning color (#ffc107) across all pages

**Example 4: Inconsistent Mobile Responsiveness**
- **index.blade.php**: Table transforms to card layout on mobile with data labels
- **schedules.blade.php**: Table overflows horizontally without transformation
- **Expected**: All tables should transform to card layout on mobile consistently


## Expected Behavior

### Preservation Requirements

**Unchanged Behaviors:**
- All filtering functionality must continue to work with same query parameters
- Form submissions must continue to validate and save data correctly
- Action buttons (acknowledge, resolve, complete) must continue to update database status
- Navigation links must continue to route to correct pages
- Health score calculations must continue to use same VehicleHealthService logic
- Alert generation must continue to use same MaintenanceAlertService logic
- Pagination must continue to work correctly
- AJAX requests must continue to function (component dropdown, vehicle select)
- Authentication and authorization middleware must continue to protect routes
- CSRF protection must continue to validate tokens
- SweetAlert2 confirmations must continue to work for destructive actions

**Scope:**
All functional behavior (data processing, business logic, database operations, navigation) should be completely unaffected by this fix. This fix ONLY changes visual presentation (CSS styling, HTML structure for consistency) without touching:
- Controller logic
- Service layer logic
- Model relationships
- Route definitions
- JavaScript functionality (except adding loading states)
- Form validation rules
- Database queries

## Hypothesized Root Cause

Based on the bug description and code analysis, the root causes are:

1. **Incremental Development Without Design System**: Each page was developed at different times without a unified design system, leading developers to create custom styles for each page independently.

2. **Copy-Paste Inconsistency**: Developers copied styles from index.blade.php to other pages but modified them slightly, creating variations instead of reusing exact same classes.

3. **Missing Component Library**: No centralized CSS component library exists, forcing developers to write inline styles or create page-specific classes.

4. **Bootstrap Default Fallback**: Some pages (alerts, schedules) use Bootstrap default classes while index.blade.php has custom corporate styling, creating visual mismatch.

5. **Lack of Mobile-First Approach**: Mobile responsiveness was added to index.blade.php but not systematically applied to other pages.

6. **No Loading State Pattern**: Loading states were never implemented across the module, leaving users without feedback during async operations.

7. **Inconsistent Status Mapping**: Different pages map same status values to different colors (e.g., "critical" → warning vs danger).

## Correctness Properties

Property 1: Bug Condition - UI Consistency Across Maintenance Pages

_For any_ page in the maintenance module (index, alerts, schedules, components), the fixed implementation SHALL use standardized CSS classes from the design system (card-metric, table-corporate, badge-corp, btn-action-corp, filter-container) with consistent styling (colors, spacing, typography, hover effects, responsive behavior), ensuring all pages have the same visual language and user experience patterns.

**Validates: Requirements 2.1, 2.2, 2.3, 2.4, 2.5, 2.6, 2.7, 2.8, 2.9, 2.10**

Property 2: Preservation - Functional Behavior Unchanged

_For any_ user interaction (filtering, form submission, navigation, data display, AJAX requests) on any maintenance page, the fixed implementation SHALL produce exactly the same functional result as the original implementation, preserving all business logic, data processing, database operations, authentication, authorization, and JavaScript functionality.

**Validates: Requirements 3.1, 3.2, 3.3, 3.4, 3.5, 3.6, 3.7, 3.8, 3.9, 3.10**


## Fix Implementation

### 1. Design System Foundation

#### 1.1 CSS Variables & Reset

Create centralized CSS variables for consistent theming:

```css
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
```

#### 1.2 Color Palette

**Status Colors (Consistent Mapping):**

| Status | Background | Text Color | Border | Use Case |
|--------|-----------|------------|--------|----------|
| **Danger/Overdue** | `#fff5f5` | `#c62828` | `#ffcdd2` | Telat servis, rusak, overdue |
| **Warning/Critical** | `#fffbf0` | `#f57f17` | `#ffe58f` | Segera servis, critical alert |
| **Info/Warning** | `#e7f7ff` | `#0891b2` | `#91d5ff` | Warning alert, perlu perhatian |
| **Success/Healthy** | `#f6ffed` | `#389e0d` | `#b7eb8f` | Prima, completed, healthy |
| **Primary** | `#e6f7ff` | `#096dd9` | `#40a9ff` | Total count, general info |

**Border-Left Colors (Card Metrics):**

| Status | Color | Hex Code |
|--------|-------|----------|
| Danger | Red | `#dc3545` |
| Warning | Yellow | `#ffc107` |
| Success | Green | `#198754` |
| Primary | Blue | `#0d6efd` |
| Info | Cyan | `#0dcaf0` |

#### 1.3 Typography System

**Font Sizes:**
- **Extra Small**: `0.7rem` (11.2px) - Data labels, helper text
- **Small**: `0.75rem` (12px) - Form labels, badges, table headers
- **Medium**: `0.85rem` (13.6px) - Body text, buttons, table cells
- **Base**: `0.95rem` (15.2px) - Primary content
- **Large**: `2.2rem` (35.2px) - Metric values

**Font Weights:**
- **Normal**: `500` - Body text
- **Bold**: `600` - Labels, table headers
- **Extra Bold**: `700` - Metric values, headings

**Letter Spacing:**
- **Normal**: `0.5px` - Table headers, form labels
- **Wide**: `0.8px` - Metric labels (uppercase)

**Text Transforms:**
- **Uppercase**: Metric labels, table headers, form labels
- **Normal**: Body text, descriptions


### 2. Component Library

#### 2.1 Card Metric Component

**Purpose**: Display statistics/metrics with visual hierarchy and status indication

**CSS Class**: `.card-metric`

**Structure**:
```html
<div class="card-metric border-left-{status}">
    <div class="metric-label text-{status}">LABEL TEXT</div>
    <div class="metric-value">123</div>
    <div class="metric-desc">Description text</div>
    <div class="card-icon"><i class="bi bi-icon-name text-{status}"></i></div>
</div>
```

**CSS Implementation**:
```css
.card-metric {
    background: #fff;
    border: 1px solid var(--border-color-light);
    border-radius: var(--border-radius-md);
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
    background-color: var(--color-primary-light);
    box-shadow: var(--shadow-xl);
    transform: translateY(-4px);
}

.border-left-danger { border-left-color: var(--color-danger); }
.border-left-warning { border-left-color: var(--color-warning); }
.border-left-success { border-left-color: var(--color-success); }
.border-left-primary { border-left-color: var(--color-primary); }
.border-left-info { border-left-color: var(--color-info); }

.metric-value {
    font-size: 2.2rem;
    font-weight: var(--font-weight-extra-bold);
    color: var(--color-gray-800);
    line-height: 1.2;
    margin-top: 10px;
    margin-bottom: 5px;
}

.metric-label {
    font-size: var(--font-size-small);
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

.card-icon {
    position: absolute;
    top: var(--spacing-lg);
    right: var(--spacing-lg);
    font-size: 2.5rem;
    opacity: 0.15;
}
```

**Usage Examples**:
- **index.blade.php**: Perlu Perhatian, Segera Servis, Kondisi Prima, Total Armada
- **alerts.blade.php**: Overdue, Critical, Warning, Total Alerts
- **schedules.blade.php**: Pending, In Progress, Completed, Total Schedules
- **components.blade.php**: Critical Components, Warning Components, Healthy Components, Total Components

#### 2.2 Table Corporate Component

**Purpose**: Display tabular data with professional styling and mobile responsiveness

**CSS Class**: `.table-corporate`

**Structure**:
```html
<table class="table-corporate">
    <thead>
        <tr>
            <th>Column 1</th>
            <th>Column 2</th>
        </tr>
    </thead>
    <tbody>
        <tr>
            <td data-label="Column 1">Data 1</td>
            <td data-label="Column 2">Data 2</td>
        </tr>
    </tbody>
</table>
```

**CSS Implementation**:
```css
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

/* Mobile Responsive Transformation */
@media (max-width: 768px) {
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

    .table-corporate td:last-child {
        border-bottom: none;
        background-color: #fff;
        padding: 15px;
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

    .table-corporate td:last-child .d-flex {
        justify-content: space-between !important;
        width: 100%;
        gap: 10px;
    }
}
```

**Usage**: All tables in index, alerts (converted from alert boxes), schedules, components


#### 2.3 Badge Component

**Purpose**: Display status indicators with consistent colors and styling

**CSS Class**: `.badge-corp`, `.badge-corp-{status}`

**Structure**:
```html
<span class="badge-corp badge-corp-danger">
    <i class="bi bi-icon-name"></i> Status Text
</span>
```

**CSS Implementation**:
```css
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
```

**Status Mapping**:
- **Overdue/Telat Servis/Rusak**: `badge-corp-danger`
- **Critical/Segera Servis**: `badge-corp-warning`
- **Warning/Perlu Perhatian**: `badge-corp-info`
- **Healthy/Prima/Completed**: `badge-corp-success`
- **General Info**: `badge-corp-primary`

#### 2.4 Button Components

**Purpose**: Consistent action buttons across all pages

**CSS Classes**: `.btn-action-corp`, `.btn-primary-corp`, `.btn-danger-corp`

**Structure**:
```html
<!-- Secondary Action -->
<a href="#" class="btn-action-corp">
    <i class="bi bi-icon"></i> Action Text
</a>

<!-- Primary Action -->
<button class="btn-primary-corp">
    <i class="bi bi-icon"></i> Primary Action
</button>

<!-- Destructive Action -->
<button class="btn-danger-corp">
    <i class="bi bi-icon"></i> Delete
</button>
```

**CSS Implementation**:
```css
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

/* Loading State */
.btn-action-corp:disabled,
.btn-primary-corp:disabled,
.btn-danger-corp:disabled {
    opacity: 0.6;
    cursor: not-allowed;
}

/* Mobile Responsive */
@media (max-width: 768px) {
    .btn-action-corp,
    .btn-primary-corp,
    .btn-danger-corp {
        flex: 1;
        justify-content: center;
        padding: 10px;
    }
}
```

#### 2.5 Filter Container Component

**Purpose**: Consistent filter form styling across pages

**CSS Class**: `.filter-container`

**Structure**:
```html
<div class="filter-container">
    <form action="#" method="GET" class="d-flex flex-wrap gap-3 align-items-end">
        <div class="flex-grow-1">
            <div class="row g-3">
                <div class="col-md-3">
                    <label class="form-label small fw-bold text-muted mb-1">LABEL</label>
                    <select name="filter" class="form-select form-select-sm" onchange="this.form.submit()">
                        <option value="">All</option>
                    </select>
                </div>
            </div>
        </div>
        <div class="mb-1">
            <a href="#" class="btn btn-sm btn-link text-danger text-decoration-none fw-bold">
                <i class="bi bi-x-lg me-1"></i>Reset Filter
            </a>
        </div>
    </form>
</div>
```

**CSS Implementation**:
```css
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

/* Mobile Responsive */
@media (max-width: 768px) {
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
}
```


#### 2.6 Progress Bar Component

**Purpose**: Display health score or progress indicators

**CSS Class**: `.progress-corp-bg`, `.progress-corp-fill`

**Structure**:
```html
<div class="d-flex align-items-center gap-3">
    <div class="progress-corp-bg">
        <div class="progress-corp-fill" style="width: 75%; background-color: #52c41a;"></div>
    </div>
    <div style="min-width: 80px;">
        <div class="fw-bold text-dark" style="font-size: 0.85rem;">75/100</div>
        <div class="text-muted" style="font-size: 0.7rem;">Score</div>
    </div>
</div>
```

**CSS Implementation**:
```css
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

/* Mobile Responsive */
@media (max-width: 768px) {
    .progress-corp-bg {
        width: 100%;
    }
}
```

**Color Mapping for Health Score**:
- **75-100**: Green `#52c41a` (Healthy)
- **40-74**: Yellow `#faad14` (Warning)
- **0-39**: Red `#ff4d4f` (Critical)

#### 2.7 Empty State Component

**Purpose**: Consistent empty state messaging

**CSS Class**: `.empty-state`

**Structure**:
```html
<div class="empty-state">
    <i class="bi bi-inbox display-4 opacity-25 mb-3"></i>
    <h5 class="fw-bold mb-0">No Data Found</h5>
    <p class="small text-muted">Try adjusting your filters</p>
</div>
```

**CSS Implementation**:
```css
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
```

**Icon Mapping**:
- **General/No Data**: `bi-inbox`
- **Success State**: `bi-check-circle`
- **Schedules**: `bi-calendar-check`
- **Alerts**: `bi-bell-slash`
- **Components**: `bi-gear`

#### 2.8 Loading State Component

**Purpose**: Provide visual feedback during async operations

**CSS Class**: `.spinner-border`, `.btn-loading`

**Structure**:
```html
<!-- Button Loading State -->
<button class="btn-primary-corp" disabled>
    <span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>
    Loading...
</button>

<!-- Page Loading State -->
<div class="text-center py-5">
    <div class="spinner-border text-primary" role="status">
        <span class="visually-hidden">Loading...</span>
    </div>
    <p class="text-muted mt-3">Loading data...</p>
</div>
```

**CSS Implementation**:
```css
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
```

**Usage**:
- Form submission buttons
- AJAX requests
- Page data loading
- Modal content loading

#### 2.9 Header Layout Component

**Purpose**: Consistent page header across all maintenance pages

**CSS Class**: `.page-header`

**Structure**:
```html
<div class="page-header d-flex justify-content-between align-items-center mb-4">
    <div>
        <h3 class="fw-bold text-dark mb-1">Page Title</h3>
        <p class="text-muted mb-0 small">Page description</p>
    </div>
    <div class="text-end d-flex justify-content-end align-items-center gap-2">
        <a href="#" class="btn btn-primary">
            <i class="bi bi-icon me-1"></i> Action
        </a>
        <span class="badge bg-light text-dark border px-3 py-2">
            <i class="bi bi-calendar3 me-2"></i>{{ now()->format('d F Y') }}
        </span>
    </div>
</div>
```

**CSS Implementation**:
```css
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

/* Mobile Responsive */
@media (max-width: 768px) {
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
```


### 3. Responsive Design Strategy

#### 3.1 Breakpoints

Following Bootstrap 5.3.3 breakpoints with mobile-first approach:

| Breakpoint | Size | Device | Layout Strategy |
|------------|------|--------|-----------------|
| **xs** | <576px | Small phones | Single column, stacked cards |
| **sm** | ≥576px | Phones | Single column, larger touch targets |
| **md** | ≥768px | Tablets | 2-column grid, table → card transformation |
| **lg** | ≥992px | Desktops | 3-4 column grid, full table view |
| **xl** | ≥1200px | Large desktops | 4 column grid, expanded spacing |

#### 3.2 Mobile-First Principles

**1. Touch-Friendly Targets**
- Minimum button size: 44x44px (iOS/Android standard)
- Spacing between interactive elements: 8px minimum
- Larger padding on mobile: 10px vs 6px desktop

**2. Content Priority**
- Most important content first (metric cards, critical alerts)
- Progressive disclosure (collapse filters, expandable sections)
- Reduce visual noise (hide decorative icons, simplify badges)

**3. Performance**
- Lazy load images and heavy content
- Minimize CSS animations on mobile
- Use CSS transforms instead of position changes

#### 3.3 Table Responsive Transformation

**Desktop View (≥768px)**:
```
┌─────────────────────────────────────────────────────┐
│ Header 1    │ Header 2    │ Header 3    │ Actions  │
├─────────────────────────────────────────────────────┤
│ Data 1      │ Data 2      │ Data 3      │ [Buttons]│
│ Data 1      │ Data 2      │ Data 3      │ [Buttons]│
└─────────────────────────────────────────────────────┘
```

**Mobile View (<768px)**:
```
┌─────────────────────────────┐
│ Data 1 (emphasized)         │
├─────────────────────────────┤
│ HEADER 2                    │
│ Data 2                      │
├─────────────────────────────┤
│ HEADER 3                    │
│ Data 3                      │
├─────────────────────────────┤
│ [Button 1] [Button 2]       │
└─────────────────────────────┘
```

**Implementation**:
- Hide `<thead>` on mobile
- Convert `<tr>` to block-level cards
- Use `data-label` attribute for column headers
- Display labels using `::before` pseudo-element
- Stack action buttons horizontally with equal width

#### 3.4 Grid Layout Responsive

**Metric Cards**:
```html
<div class="row mb-4 g-3">
    <div class="col-xl-3 col-md-6 col-sm-6 col-12">
        <!-- Card 1 -->
    </div>
    <div class="col-xl-3 col-md-6 col-sm-6 col-12">
        <!-- Card 2 -->
    </div>
    <div class="col-xl-3 col-md-6 col-sm-6 col-12">
        <!-- Card 3 -->
    </div>
    <div class="col-xl-3 col-md-6 col-sm-6 col-12">
        <!-- Card 4 -->
    </div>
</div>
```

**Responsive Behavior**:
- **XL (≥1200px)**: 4 columns (25% each)
- **MD (≥768px)**: 2 columns (50% each)
- **SM (≥576px)**: 2 columns (50% each)
- **XS (<576px)**: 1 column (100%)

#### 3.5 Filter Container Responsive

**Desktop**: Horizontal layout with inline filters
**Mobile**: Vertical stack with full-width inputs

```css
@media (max-width: 768px) {
    .filter-container .row {
        flex-direction: column;
    }
    
    .filter-container .col-md-3,
    .filter-container .col-md-4 {
        width: 100%;
        margin-bottom: 1rem;
    }
}
```

### 4. Code Organization

#### 4.1 File Structure

```
resources/
├── views/
│   ├── admin/
│   │   ├── layouts/
│   │   │   └── app.blade.php (main layout)
│   │   ├── maintenance/
│   │   │   ├── index.blade.php
│   │   │   ├── alerts.blade.php
│   │   │   ├── schedules.blade.php
│   │   │   ├── components.blade.php
│   │   │   └── partials/
│   │   │       ├── _design-system.blade.php (NEW)
│   │   │       ├── _metric-card.blade.php (NEW)
│   │   │       ├── _empty-state.blade.php (NEW)
│   │   │       └── _loading-state.blade.php (NEW)
```

#### 4.2 Design System Blade Partial

**File**: `resources/views/admin/maintenance/partials/_design-system.blade.php`

**Purpose**: Centralized CSS that can be included in all maintenance pages

**Structure**:
```blade
<style>
    /* === DESIGN SYSTEM: MAINTENANCE MODULE === */
    
    /* 1. CSS Variables */
    :root {
        /* ... all CSS variables ... */
    }
    
    /* 2. Card Metric Component */
    .card-metric { /* ... */ }
    
    /* 3. Table Corporate Component */
    .table-corporate { /* ... */ }
    
    /* 4. Badge Component */
    .badge-corp { /* ... */ }
    
    /* 5. Button Components */
    .btn-action-corp { /* ... */ }
    
    /* 6. Filter Container */
    .filter-container { /* ... */ }
    
    /* 7. Progress Bar */
    .progress-corp-bg { /* ... */ }
    
    /* 8. Empty State */
    .empty-state { /* ... */ }
    
    /* 9. Loading State */
    .btn-loading { /* ... */ }
    
    /* 10. Header Layout */
    .page-header { /* ... */ }
    
    /* 11. Mobile Responsive */
    @media (max-width: 768px) {
        /* ... all mobile styles ... */
    }
</style>
```

**Usage in Pages**:
```blade
@extends('admin.layouts.app')

@section('title', 'Page Title')

{{-- Include Design System --}}
@include('admin.maintenance.partials._design-system')

@section('content')
    <!-- Page content using design system classes -->
@endsection
```

#### 4.3 Reusable Blade Components

**Metric Card Component**: `_metric-card.blade.php`
```blade
<div class="card-metric border-left-{{ $status }} {{ $active ? 'active' : '' }}">
    <div class="metric-label text-{{ $status }}">{{ $label }}</div>
    <div class="metric-value">{{ $value }}</div>
    <div class="metric-desc">{{ $description }}</div>
    <div class="card-icon"><i class="bi bi-{{ $icon }} text-{{ $status }}"></i></div>
</div>
```

**Empty State Component**: `_empty-state.blade.php`
```blade
<div class="empty-state">
    <i class="bi bi-{{ $icon }} display-4 opacity-25 mb-3"></i>
    <h5 class="fw-bold mb-0">{{ $title }}</h5>
    <p class="small text-muted">{{ $message }}</p>
</div>
```

**Loading State Component**: `_loading-state.blade.php`
```blade
<div class="text-center py-5">
    <div class="spinner-border text-primary" role="status">
        <span class="visually-hidden">Loading...</span>
    </div>
    <p class="text-muted mt-3">{{ $message ?? 'Loading data...' }}</p>
</div>
```


### 5. Specific Page Implementations

#### 5.1 index.blade.php (Maintenance Monitor)

**Current State**: Already has good design system, needs minor adjustments

**Changes Required**:
1. Extract inline `<style>` to `_design-system.blade.php` partial
2. Ensure all CSS classes match design system naming
3. Add loading states to form submissions
4. Verify mobile responsiveness matches standard

**No Functional Changes**: Keep all existing logic for health score calculation, filtering, status display

#### 5.2 alerts.blade.php (Maintenance Alerts)

**Current State**: Uses Bootstrap default styling, alert boxes instead of table

**Changes Required**:

1. **Replace Summary Cards**:
```blade
<!-- OLD -->
<div class="card border-0 shadow-sm border-start border-danger border-4">
    <div class="card-body">...</div>
</div>

<!-- NEW -->
<div class="card-metric border-left-danger">
    <div class="metric-label text-danger">🔴 OVERDUE</div>
    <div class="metric-value">{{ $summary['by_type']['overdue'] }}</div>
    <div class="metric-desc">Sudah lewat batas</div>
    <div class="card-icon"><i class="bi bi-exclamation-octagon-fill text-danger"></i></div>
</div>
```

2. **Convert Alert Boxes to Table**:
```blade
<!-- OLD -->
<div class="alert alert-danger m-3">...</div>

<!-- NEW -->
<table class="table-corporate">
    <thead>
        <tr>
            <th>Vehicle</th>
            <th>Component</th>
            <th>Alert Type</th>
            <th>Message</th>
            <th>Triggered</th>
            <th class="text-end">Actions</th>
        </tr>
    </thead>
    <tbody>
        @foreach($alerts as $alert)
        <tr>
            <td data-label="Vehicle">
                <span class="badge-corp badge-corp-primary">{{ $alert->vehicle->plate_number }}</span>
            </td>
            <td data-label="Component">
                @if($alert->component)
                    <span class="badge-corp badge-corp-info">{{ $alert->component->component_name }}</span>
                @else
                    <span class="text-muted">-</span>
                @endif
            </td>
            <td data-label="Alert Type">
                <span class="badge-corp badge-corp-{{ $alert->alert_type == 'overdue' ? 'danger' : ($alert->alert_type == 'critical' ? 'warning' : 'info') }}">
                    <i class="bi bi-{{ $alert->alert_type == 'overdue' ? 'exclamation-octagon' : ($alert->alert_type == 'critical' ? 'exclamation-triangle' : 'info-circle') }}"></i>
                    {{ strtoupper($alert->alert_type) }}
                </span>
            </td>
            <td data-label="Message">{{ $alert->message }}</td>
            <td data-label="Triggered">
                <small class="text-muted">
                    <i class="bi bi-clock me-1"></i>{{ $alert->triggered_at->diffForHumans() }}
                </small>
            </td>
            <td class="text-end" data-label="Actions">
                <div class="d-flex justify-content-end gap-2">
                    @if($alert->status == 'active')
                        <form action="{{ route('admin.maintenance.alerts.acknowledge', $alert) }}" method="POST" class="d-inline form-acknowledge">
                            @csrf
                            <button type="submit" class="btn-action-corp" title="Tandai sudah dibaca">
                                <i class="bi bi-check"></i> Acknowledge
                            </button>
                        </form>
                        <form action="{{ route('admin.maintenance.alerts.resolve', $alert) }}" method="POST" class="d-inline form-resolve">
                            @csrf
                            <button type="submit" class="btn-primary-corp" title="Tandai sudah selesai">
                                <i class="bi bi-check-circle"></i> Resolve
                            </button>
                        </form>
                    @else
                        <span class="badge-corp badge-corp-success">{{ ucfirst($alert->status) }}</span>
                    @endif
                </div>
            </td>
        </tr>
        @endforeach
    </tbody>
</table>
```

3. **Update Filter Container**:
```blade
<div class="filter-container">
    <form action="{{ route('admin.maintenance.alerts') }}" method="GET" class="d-flex flex-wrap gap-3 align-items-end">
        <div class="flex-grow-1">
            <div class="row g-3">
                <div class="col-md-3">
                    <label class="form-label small fw-bold text-muted mb-1">STATUS</label>
                    <select name="status" class="form-select form-select-sm" onchange="this.form.submit()">
                        <option value="">Semua Status</option>
                        <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>Active</option>
                        <option value="acknowledged" {{ request('status') == 'acknowledged' ? 'selected' : '' }}>Acknowledged</option>
                        <option value="resolved" {{ request('status') == 'resolved' ? 'selected' : '' }}>Resolved</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label small fw-bold text-muted mb-1">TIPE ALERT</label>
                    <select name="alert_type" class="form-select form-select-sm" onchange="this.form.submit()">
                        <option value="">Semua Tipe</option>
                        <option value="overdue" {{ request('alert_type') == 'overdue' ? 'selected' : '' }}>Overdue</option>
                        <option value="critical" {{ request('alert_type') == 'critical' ? 'selected' : '' }}>Critical</option>
                        <option value="warning" {{ request('alert_type') == 'warning' ? 'selected' : '' }}>Warning</option>
                    </select>
                </div>
            </div>
        </div>
        @if(request()->hasAny(['status', 'alert_type']))
            <div class="mb-1">
                <a href="{{ route('admin.maintenance.alerts') }}" class="btn btn-sm btn-link text-danger text-decoration-none fw-bold">
                    <i class="bi bi-x-lg me-1"></i>Reset Filter
                </a>
            </div>
        @endif
    </form>
</div>
```

4. **Add Loading States**:
```javascript
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Acknowledge form loading state
    document.querySelectorAll('.form-acknowledge').forEach(form => {
        form.addEventListener('submit', function(e) {
            const btn = this.querySelector('button');
            btn.disabled = true;
            btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Processing...';
        });
    });
    
    // Resolve form loading state
    document.querySelectorAll('.form-resolve').forEach(form => {
        form.addEventListener('submit', function(e) {
            const btn = this.querySelector('button');
            btn.disabled = true;
            btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Resolving...';
        });
    });
});
</script>
```

**No Functional Changes**: Keep all existing filtering logic, form submission, status updates

#### 5.3 schedules.blade.php (Maintenance Schedules)

**Current State**: Uses Bootstrap default styling, inconsistent with index

**Changes Required**:

1. **Add Metric Cards** (if not present):
```blade
<div class="row mb-4 g-3">
    <div class="col-xl-3 col-md-6">
        <div class="card-metric border-left-warning">
            <div class="metric-label text-warning">PENDING</div>
            <div class="metric-value">{{ $stats['pending'] ?? 0 }}</div>
            <div class="metric-desc">Menunggu Jadwal</div>
            <div class="card-icon"><i class="bi bi-clock-history text-warning"></i></div>
        </div>
    </div>
    <div class="col-xl-3 col-md-6">
        <div class="card-metric border-left-info">
            <div class="metric-label text-info">IN PROGRESS</div>
            <div class="metric-value">{{ $stats['in_progress'] ?? 0 }}</div>
            <div class="metric-desc">Sedang Dikerjakan</div>
            <div class="card-icon"><i class="bi bi-gear-fill text-info"></i></div>
        </div>
    </div>
    <div class="col-xl-3 col-md-6">
        <div class="card-metric border-left-success">
            <div class="metric-label text-success">COMPLETED</div>
            <div class="metric-value">{{ $stats['completed'] ?? 0 }}</div>
            <div class="metric-desc">Selesai</div>
            <div class="card-icon"><i class="bi bi-check-circle-fill text-success"></i></div>
        </div>
    </div>
    <div class="col-xl-3 col-md-6">
        <div class="card-metric border-left-primary">
            <div class="metric-label text-primary">TOTAL</div>
            <div class="metric-value">{{ $stats['total'] ?? 0 }}</div>
            <div class="metric-desc">Jadwal Servis</div>
            <div class="card-icon"><i class="bi bi-calendar-check text-primary"></i></div>
        </div>
    </div>
</div>
```

2. **Update Table Styling**:
```blade
<!-- OLD -->
<table class="table table-hover">

<!-- NEW -->
<table class="table-corporate">
```

3. **Standardize Badges**:
```blade
<!-- OLD -->
<span class="badge bg-warning">{{ $schedule->priority }}</span>

<!-- NEW -->
<span class="badge-corp badge-corp-{{ $schedule->priority == 'high' ? 'danger' : ($schedule->priority == 'medium' ? 'warning' : 'info') }}">
    <i class="bi bi-{{ $schedule->priority == 'high' ? 'exclamation-triangle' : ($schedule->priority == 'medium' ? 'clock' : 'info-circle') }}"></i>
    {{ ucfirst($schedule->priority) }}
</span>
```

4. **Standardize Buttons**:
```blade
<!-- OLD -->
<a href="#" class="btn btn-sm btn-primary">Edit</a>

<!-- NEW -->
<a href="#" class="btn-action-corp">
    <i class="bi bi-pencil"></i> Edit
</a>
```

**No Functional Changes**: Keep all existing scheduling logic, form validation, date handling


#### 5.4 components.blade.php (Vehicle Components)

**Current State**: Minimal styling, different layout from other pages

**Changes Required**:

1. **Add Health Breakdown Cards**:
```blade
<div class="row mb-4 g-3">
    <div class="col-xl-3 col-md-6">
        <div class="card-metric border-left-danger">
            <div class="metric-label text-danger">CRITICAL</div>
            <div class="metric-value">{{ $healthBreakdown['critical'] ?? 0 }}</div>
            <div class="metric-desc">Perlu Segera</div>
            <div class="card-icon"><i class="bi bi-exclamation-octagon-fill text-danger"></i></div>
        </div>
    </div>
    <div class="col-xl-3 col-md-6">
        <div class="card-metric border-left-warning">
            <div class="metric-label text-warning">WARNING</div>
            <div class="metric-value">{{ $healthBreakdown['warning'] ?? 0 }}</div>
            <div class="metric-desc">Perlu Perhatian</div>
            <div class="card-icon"><i class="bi bi-exclamation-triangle-fill text-warning"></i></div>
        </div>
    </div>
    <div class="col-xl-3 col-md-6">
        <div class="card-metric border-left-success">
            <div class="metric-label text-success">HEALTHY</div>
            <div class="metric-value">{{ $healthBreakdown['healthy'] ?? 0 }}</div>
            <div class="metric-desc">Kondisi Baik</div>
            <div class="card-icon"><i class="bi bi-check-circle-fill text-success"></i></div>
        </div>
    </div>
    <div class="col-xl-3 col-md-6">
        <div class="card-metric border-left-primary">
            <div class="metric-label text-primary">TOTAL</div>
            <div class="metric-value">{{ $healthBreakdown['total'] ?? 0 }}</div>
            <div class="metric-desc">Komponen</div>
            <div class="card-icon"><i class="bi bi-gear text-primary"></i></div>
        </div>
    </div>
</div>
```

2. **Add Filter Container**:
```blade
<div class="filter-container">
    <form action="{{ route('admin.maintenance.components', $vehicle->id) }}" method="GET" class="d-flex flex-wrap gap-3 align-items-end">
        <div class="flex-grow-1">
            <div class="row g-3">
                <div class="col-md-3">
                    <label class="form-label small fw-bold text-muted mb-1">KATEGORI</label>
                    <select name="category" class="form-select form-select-sm" onchange="this.form.submit()">
                        <option value="">Semua Kategori</option>
                        <option value="engine" {{ request('category') == 'engine' ? 'selected' : '' }}>Engine</option>
                        <option value="transmission" {{ request('category') == 'transmission' ? 'selected' : '' }}>Transmission</option>
                        <option value="brake" {{ request('category') == 'brake' ? 'selected' : '' }}>Brake</option>
                        <option value="suspension" {{ request('category') == 'suspension' ? 'selected' : '' }}>Suspension</option>
                        <option value="electrical" {{ request('category') == 'electrical' ? 'selected' : '' }}>Electrical</option>
                        <option value="body" {{ request('category') == 'body' ? 'selected' : '' }}>Body</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label small fw-bold text-muted mb-1">STATUS</label>
                    <select name="status" class="form-select form-select-sm" onchange="this.form.submit()">
                        <option value="">Semua Status</option>
                        <option value="critical" {{ request('status') == 'critical' ? 'selected' : '' }}>Critical</option>
                        <option value="warning" {{ request('status') == 'warning' ? 'selected' : '' }}>Warning</option>
                        <option value="healthy" {{ request('status') == 'healthy' ? 'selected' : '' }}>Healthy</option>
                    </select>
                </div>
            </div>
        </div>
        @if(request()->hasAny(['category', 'status']))
            <div class="mb-1">
                <a href="{{ route('admin.maintenance.components', $vehicle->id) }}" class="btn btn-sm btn-link text-danger text-decoration-none fw-bold">
                    <i class="bi bi-x-lg me-1"></i>Reset Filter
                </a>
            </div>
        @endif
    </form>
</div>
```

3. **Update Table Styling**:
```blade
<!-- OLD -->
<table class="table table-hover">

<!-- NEW -->
<table class="table-corporate">
    <thead>
        <tr>
            <th>Komponen</th>
            <th>Kategori</th>
            <th>Status</th>
            <th>KM Terakhir</th>
            <th>Interval</th>
            <th>Sisa KM</th>
            <th class="text-end">Aksi</th>
        </tr>
    </thead>
    <tbody>
        @foreach($components as $component)
        <tr>
            <td data-label="Komponen">
                <div class="fw-bold">{{ $component->component_name }}</div>
            </td>
            <td data-label="Kategori">
                <span class="badge-corp badge-corp-info">
                    <i class="bi bi-tag"></i> {{ ucfirst($component->category) }}
                </span>
            </td>
            <td data-label="Status">
                @php
                    $statusClass = $component->status == 'critical' ? 'danger' : ($component->status == 'warning' ? 'warning' : 'success');
                    $statusIcon = $component->status == 'critical' ? 'exclamation-octagon' : ($component->status == 'warning' ? 'exclamation-triangle' : 'check-circle');
                @endphp
                <span class="badge-corp badge-corp-{{ $statusClass }}">
                    <i class="bi bi-{{ $statusIcon }}"></i> {{ ucfirst($component->status) }}
                </span>
            </td>
            <td data-label="KM Terakhir">{{ number_format($component->last_service_km) }} km</td>
            <td data-label="Interval">{{ number_format($component->service_interval_km) }} km</td>
            <td data-label="Sisa KM">
                @php
                    $remaining = $component->next_service_km - $vehicle->current_km;
                    $remainingClass = $remaining < 0 ? 'danger' : ($remaining < 1000 ? 'warning' : 'success');
                @endphp
                <span class="badge-corp badge-corp-{{ $remainingClass }}">
                    {{ number_format($remaining) }} km
                </span>
            </td>
            <td class="text-end" data-label="Aksi">
                <div class="d-flex justify-content-end gap-2">
                    <button type="button" class="btn-action-corp" data-bs-toggle="modal" data-bs-target="#editModal{{ $component->id }}">
                        <i class="bi bi-pencil"></i> Edit
                    </button>
                    <form action="{{ route('admin.maintenance.components.update', [$vehicle->id, $component->id]) }}" method="POST" class="d-inline form-update-component">
                        @csrf
                        @method('PUT')
                        <input type="hidden" name="mark_serviced" value="1">
                        <button type="submit" class="btn-primary-corp">
                            <i class="bi bi-check-circle"></i> Servis
                        </button>
                    </form>
                </div>
            </td>
        </tr>
        @endforeach
    </tbody>
</table>
```

4. **Add Loading States**:
```javascript
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Update component form loading state
    document.querySelectorAll('.form-update-component').forEach(form => {
        form.addEventListener('submit', function(e) {
            e.preventDefault();
            const btn = this.querySelector('button');
            
            Swal.fire({
                title: 'Konfirmasi Servis',
                text: 'Tandai komponen ini sudah diservis?',
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#1890ff',
                confirmButtonText: 'Ya, Servis',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (result.isConfirmed) {
                    btn.disabled = true;
                    btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Processing...';
                    this.submit();
                }
            });
        });
    });
});
</script>
```

**No Functional Changes**: Keep all existing component management logic, health score calculation, service tracking

### 6. Implementation Checklist

#### 6.1 Phase 1: Create Design System Partial

- [ ] Create `resources/views/admin/maintenance/partials/_design-system.blade.php`
- [ ] Move all CSS from index.blade.php to design system partial
- [ ] Add CSS variables for theming
- [ ] Add all component classes (card-metric, table-corporate, badge-corp, etc.)
- [ ] Add mobile responsive styles
- [ ] Test design system partial in isolation

#### 6.2 Phase 2: Update index.blade.php

- [ ] Replace inline `<style>` with `@include('admin.maintenance.partials._design-system')`
- [ ] Verify all existing functionality works
- [ ] Test mobile responsiveness
- [ ] Test filtering and sorting
- [ ] Test health score calculation
- [ ] Test action buttons (Komponen, Fisik, Riwayat, Jadwal, Selesai)

#### 6.3 Phase 3: Update alerts.blade.php

- [ ] Include design system partial
- [ ] Replace summary cards with card-metric components
- [ ] Convert alert boxes to table-corporate
- [ ] Update filter container styling
- [ ] Add loading states to acknowledge/resolve forms
- [ ] Update badges to badge-corp
- [ ] Update buttons to btn-action-corp/btn-primary-corp
- [ ] Test filtering functionality
- [ ] Test acknowledge/resolve actions
- [ ] Test mobile responsiveness

#### 6.4 Phase 4: Update schedules.blade.php

- [ ] Include design system partial
- [ ] Add metric cards for schedule stats
- [ ] Update table to table-corporate
- [ ] Update filter container styling
- [ ] Update badges to badge-corp
- [ ] Update buttons to btn-action-corp/btn-primary-corp
- [ ] Add loading states to form submissions
- [ ] Test schedule creation/editing
- [ ] Test filtering functionality
- [ ] Test mobile responsiveness

#### 6.5 Phase 5: Update components.blade.php

- [ ] Include design system partial
- [ ] Add health breakdown metric cards
- [ ] Add filter container
- [ ] Update table to table-corporate
- [ ] Update badges to badge-corp
- [ ] Update buttons to btn-action-corp/btn-primary-corp
- [ ] Add loading states to component updates
- [ ] Test component management functionality
- [ ] Test filtering by category/status
- [ ] Test mobile responsiveness

#### 6.6 Phase 6: Create Reusable Blade Components

- [ ] Create `_metric-card.blade.php` partial
- [ ] Create `_empty-state.blade.php` partial
- [ ] Create `_loading-state.blade.php` partial
- [ ] Refactor pages to use reusable components
- [ ] Test all pages with new components

#### 6.7 Phase 7: Testing & Validation

- [ ] Visual regression testing (compare before/after screenshots)
- [ ] Functional testing (all features work as before)
- [ ] Mobile responsiveness testing (all breakpoints)
- [ ] Cross-browser testing (Chrome, Firefox, Safari, Edge)
- [ ] Performance testing (no degradation in load times)
- [ ] Accessibility testing (keyboard navigation, screen readers)


## Testing Strategy

### Validation Approach

The testing strategy follows a two-phase approach: first, document the current inconsistencies through visual comparison and user testing, then verify the fix achieves consistency while preserving all functional behavior.

### Exploratory Bug Condition Checking

**Goal**: Document and demonstrate the UI inconsistencies BEFORE implementing the fix. Create visual evidence of the bug through screenshots and user testing.

**Test Plan**: 
1. Take screenshots of all 4 pages (index, alerts, schedules, components) side-by-side
2. Document specific inconsistencies in a comparison table
3. Conduct user testing with 2-3 admin users to gather feedback on confusion points
4. Create a "before" baseline for visual regression testing

**Test Cases**:

1. **Card Design Inconsistency Test**
   - Navigate to index.blade.php → Observe card-metric with border-left and hover effects
   - Navigate to alerts.blade.php → Observe different card styling with border-start
   - Navigate to schedules.blade.php → Observe yet another card variation
   - Navigate to components.blade.php → Observe minimal card styling
   - **Expected**: Screenshots show 4 different card designs (demonstrates bug)

2. **Table Styling Inconsistency Test**
   - View table in index.blade.php → Observe table-corporate with custom styling
   - View table in schedules.blade.php → Observe Bootstrap default table
   - View alerts in alerts.blade.php → Observe alert boxes instead of table
   - View table in components.blade.php → Observe Bootstrap default table
   - **Expected**: Screenshots show 3 different table styles + alert boxes (demonstrates bug)

3. **Button Styling Inconsistency Test**
   - Click action buttons in index.blade.php → Observe btn-action-corp custom styling
   - Click action buttons in alerts.blade.php → Observe Bootstrap default buttons
   - Click action buttons in schedules.blade.php → Observe different button sizes
   - Click action buttons in components.blade.php → Observe btn-outline-primary
   - **Expected**: Screenshots show 4 different button styles (demonstrates bug)

4. **Badge Styling Inconsistency Test**
   - View status badges in index.blade.php → Observe badge-corp with borders
   - View status badges in alerts.blade.php → Observe Bootstrap default badges
   - View status badges in schedules.blade.php → Observe different colors for same status
   - View status badges in components.blade.php → Observe badges with icons but different styling
   - **Expected**: Screenshots show inconsistent badge styling (demonstrates bug)

5. **Color Scheme Inconsistency Test**
   - Find "critical" status in index.blade.php → Observe warning color (#ffc107)
   - Find "critical" status in schedules.blade.php → Observe danger color (#dc3545)
   - **Expected**: Same status shows different colors (demonstrates bug)

6. **Mobile Responsiveness Inconsistency Test**
   - Open index.blade.php on mobile → Observe table transforms to cards
   - Open alerts.blade.php on mobile → Observe alert boxes without optimization
   - Open schedules.blade.php on mobile → Observe table overflows horizontally
   - Open components.blade.php on mobile → Observe table overflows horizontally
   - **Expected**: Only index has proper mobile transformation (demonstrates bug)

7. **Loading State Absence Test**
   - Submit form in index.blade.php → Observe no loading indicator
   - Click acknowledge in alerts.blade.php → Observe no loading state
   - Submit schedule in schedules.blade.php → Observe no loading spinner
   - Update component in components.blade.php → Observe no loading feedback
   - **Expected**: No loading states anywhere (demonstrates bug)

8. **Empty State Inconsistency Test**
   - Filter to show no results in index.blade.php → Observe empty state with bi-inbox
   - Filter to show no results in alerts.blade.php → Observe empty state with bi-check-circle
   - Filter to show no results in schedules.blade.php → Observe empty state with bi-calendar-check
   - Filter to show no results in components.blade.php → Observe empty state with bi-inbox but different text
   - **Expected**: Different empty state designs (demonstrates bug)

**Expected Counterexamples**:
- Visual evidence of 4 different design patterns across pages
- User feedback indicating confusion and difficulty adapting between pages
- Inconsistent color mapping for same status values
- Missing mobile optimization on 3 out of 4 pages
- No loading states across entire module

### Fix Checking

**Goal**: Verify that after implementing the design system, all pages use consistent styling and components.

**Pseudocode:**
```
FOR ALL page IN ['index', 'alerts', 'schedules', 'components'] DO
  result := renderPage(page)
  
  // Check card consistency
  ASSERT result.cards.all(card => card.hasClass('card-metric'))
  ASSERT result.cards.all(card => card.hasBorderLeft())
  ASSERT result.cards.all(card => card.hasHoverEffect())
  
  // Check table consistency
  ASSERT result.tables.all(table => table.hasClass('table-corporate'))
  ASSERT result.tables.all(table => table.thead.hasCustomStyling())
  ASSERT result.tables.all(table => table.isMobileResponsive())
  
  // Check button consistency
  ASSERT result.buttons.all(btn => btn.hasClass('btn-action-corp' OR 'btn-primary-corp' OR 'btn-danger-corp'))
  ASSERT result.buttons.all(btn => btn.hasIconAndText())
  
  // Check badge consistency
  ASSERT result.badges.all(badge => badge.hasClass('badge-corp'))
  ASSERT result.badges.all(badge => badge.hasConsistentColors())
  
  // Check color scheme consistency
  FOR ALL status IN ['danger', 'warning', 'success', 'info'] DO
    ASSERT result.getColorForStatus(status) == DESIGN_SYSTEM.getColorForStatus(status)
  END FOR
  
  // Check mobile responsiveness
  ASSERT result.isMobileResponsive() == TRUE
  ASSERT result.tables.all(table => table.transformsToCardsOnMobile())
  
  // Check loading states
  ASSERT result.forms.all(form => form.hasLoadingState())
  
  // Check empty states
  ASSERT result.emptyState.hasClass('empty-state')
  ASSERT result.emptyState.hasConsistentStyling()
END FOR
```

**Test Cases**:

1. **Visual Regression Test**: Compare before/after screenshots to verify consistency
2. **Component Audit Test**: Inspect HTML/CSS to verify all pages use design system classes
3. **Color Consistency Test**: Verify same status shows same color across all pages
4. **Mobile Responsiveness Test**: Test all pages on mobile devices (iPhone, Android)
5. **Loading State Test**: Verify all forms show loading indicators
6. **Empty State Test**: Verify all pages show consistent empty states

### Preservation Checking

**Goal**: Verify that all functional behavior remains unchanged after implementing the design system.

**Pseudocode:**
```
FOR ALL page IN ['index', 'alerts', 'schedules', 'components'] DO
  FOR ALL interaction IN page.interactions DO
    originalResult := originalImplementation(interaction)
    fixedResult := fixedImplementation(interaction)
    
    ASSERT originalResult.data == fixedResult.data
    ASSERT originalResult.databaseState == fixedResult.databaseState
    ASSERT originalResult.navigation == fixedResult.navigation
    ASSERT originalResult.validation == fixedResult.validation
  END FOR
END FOR
```

**Testing Approach**: Manual testing and automated functional tests to ensure no regressions.

**Test Plan**: 
1. Create test checklist for all functional features
2. Test each feature on UNFIXED code and document expected behavior
3. Test each feature on FIXED code and verify behavior matches
4. Use Laravel Dusk for automated browser testing (optional)

**Test Cases**:

1. **Filtering Preservation Test**
   - Test filtering by project, type, search in index.blade.php
   - Test filtering by status, alert_type in alerts.blade.php
   - Test filtering by vehicle, status in schedules.blade.php
   - Test filtering by category, status in components.blade.php
   - **Expected**: All filters work exactly as before, returning same results

2. **Form Submission Preservation Test**
   - Submit "Selesai" form in index.blade.php → Verify status updates correctly
   - Submit acknowledge/resolve forms in alerts.blade.php → Verify status updates correctly
   - Submit schedule creation form in schedules.blade.php → Verify schedule is created
   - Submit component update form in components.blade.php → Verify component is updated
   - **Expected**: All forms process correctly, database updates match original behavior

3. **Navigation Preservation Test**
   - Click "Kelola Komponen" in index.blade.php → Verify navigates to components page
   - Click "Lihat Detail" in alerts.blade.php → Verify navigates to vehicle detail
   - Click "Edit" in schedules.blade.php → Verify opens edit modal
   - Click breadcrumb in components.blade.php → Verify navigates back to index
   - **Expected**: All navigation works exactly as before

4. **Data Display Preservation Test**
   - Verify health score calculation in index.blade.php matches VehicleHealthService
   - Verify alert counts in alerts.blade.php match database queries
   - Verify schedule dates in schedules.blade.php display correctly
   - Verify component status in components.blade.php calculates correctly
   - **Expected**: All data displays accurately, no calculation changes

5. **AJAX Functionality Preservation Test**
   - Test component dropdown in components.blade.php → Verify populates based on category
   - Test vehicle select in schedules.blade.php → Verify fetches components via AJAX
   - **Expected**: All AJAX requests work exactly as before

6. **Authentication Preservation Test**
   - Try accessing pages without authentication → Verify redirects to login
   - Try accessing pages with wrong role → Verify shows 403 error
   - **Expected**: All authentication/authorization works exactly as before

7. **Performance Preservation Test**
   - Measure page load time for all pages before fix
   - Measure page load time for all pages after fix
   - **Expected**: No significant performance degradation (< 10% difference)

8. **Validation Preservation Test**
   - Submit invalid form data in schedules.blade.php → Verify shows validation errors
   - Submit invalid form data in components.blade.php → Verify shows validation errors
   - **Expected**: All validation rules work exactly as before

### Unit Tests

- Test design system CSS classes render correctly
- Test metric card component with different status values
- Test table corporate component with different data sets
- Test badge component with different status values
- Test button components with different actions
- Test filter container with different filter combinations
- Test empty state component with different messages
- Test loading state component in different contexts

### Property-Based Tests

**Not applicable for this bugfix** - This is a UI/UX consistency fix that doesn't involve complex business logic or data transformations that would benefit from property-based testing. Visual regression testing and manual testing are more appropriate.

### Integration Tests

- Test complete user flow: Dashboard → Filter → View Details → Update Component → Back to Dashboard
- Test complete alert flow: View Alerts → Acknowledge → Resolve → Verify Status Update
- Test complete schedule flow: Create Schedule → Edit Schedule → Complete Schedule → Verify History
- Test mobile user flow: Navigate all pages on mobile device → Verify responsive behavior
- Test cross-browser compatibility: Test all pages on Chrome, Firefox, Safari, Edge
- Test accessibility: Test keyboard navigation, screen reader compatibility, ARIA labels

### Manual Testing Checklist

**Before Fix (Document Current State)**:
- [ ] Screenshot all 4 pages on desktop
- [ ] Screenshot all 4 pages on mobile
- [ ] Document all inconsistencies in comparison table
- [ ] Conduct user testing session (2-3 users)
- [ ] Document user feedback and pain points

**After Fix (Verify Consistency)**:
- [ ] Screenshot all 4 pages on desktop → Compare with before
- [ ] Screenshot all 4 pages on mobile → Compare with before
- [ ] Verify all pages use same card design
- [ ] Verify all pages use same table design
- [ ] Verify all pages use same button design
- [ ] Verify all pages use same badge design
- [ ] Verify all pages use same color scheme
- [ ] Verify all pages have mobile responsiveness
- [ ] Verify all pages have loading states
- [ ] Verify all pages have consistent empty states

**Functional Preservation (Verify No Regressions)**:
- [ ] Test all filtering functionality
- [ ] Test all form submissions
- [ ] Test all navigation links
- [ ] Test all action buttons
- [ ] Test all AJAX requests
- [ ] Test all validation rules
- [ ] Test authentication/authorization
- [ ] Test performance (page load times)
- [ ] Test on multiple browsers
- [ ] Test on multiple devices

### Success Criteria

**Fix is successful if:**
1. All 4 pages use identical design system components (card-metric, table-corporate, badge-corp, btn-action-corp)
2. All status values map to consistent colors across all pages
3. All pages have mobile-responsive table transformations
4. All forms have loading states
5. All pages have consistent empty states
6. All functional behavior remains unchanged (filtering, forms, navigation, data display)
7. No performance degradation (< 10% difference in load times)
8. User testing shows improved consistency and reduced confusion
9. Visual regression tests pass (before/after comparison shows consistency)
10. All manual testing checklist items pass


## Appendix

### A. Quick Reference Guide

#### A.1 Component Usage Matrix

| Component | Class Name | Use Case | Example |
|-----------|-----------|----------|---------|
| Metric Card | `.card-metric` | Display statistics/counts | Total vehicles, alert counts |
| Table | `.table-corporate` | Display tabular data | Vehicle list, alert list, schedules |
| Badge | `.badge-corp-{status}` | Display status indicators | Danger, warning, success, info |
| Button (Secondary) | `.btn-action-corp` | Secondary actions | View, Edit, Details |
| Button (Primary) | `.btn-primary-corp` | Primary actions | Save, Submit, Confirm |
| Button (Danger) | `.btn-danger-corp` | Destructive actions | Delete, Remove, Resolve |
| Filter Container | `.filter-container` | Filter forms | Search, dropdown filters |
| Progress Bar | `.progress-corp-bg` | Health score display | 0-100 score visualization |
| Empty State | `.empty-state` | No data message | No results found |
| Loading State | `.spinner-border` | Async operation feedback | Form submission, AJAX |

#### A.2 Status Color Mapping

| Status Value | Badge Class | Border Color | Use Case |
|--------------|-------------|--------------|----------|
| overdue, telat_servis, rusak | `badge-corp-danger` | `#dc3545` | Urgent attention needed |
| critical, segera_servis | `badge-corp-warning` | `#ffc107` | Immediate action required |
| warning, perlu_perhatian | `badge-corp-info` | `#0dcaf0` | Attention needed |
| healthy, prima, completed | `badge-corp-success` | `#198754` | Good condition |
| general, total | `badge-corp-primary` | `#0d6efd` | General information |

#### A.3 Responsive Breakpoint Behavior

| Breakpoint | Cards Layout | Table Display | Filter Layout | Button Size |
|------------|--------------|---------------|---------------|-------------|
| XS (<576px) | 1 column | Card transformation | Vertical stack | Full width |
| SM (≥576px) | 2 columns | Card transformation | Vertical stack | Full width |
| MD (≥768px) | 2 columns | Card transformation | Horizontal | Normal |
| LG (≥992px) | 4 columns | Table view | Horizontal | Normal |
| XL (≥1200px) | 4 columns | Table view | Horizontal | Normal |

### B. Migration Guide

#### B.1 Converting Existing Pages

**Step 1: Include Design System**
```blade
{{-- Add at the top of your blade file --}}
@include('admin.maintenance.partials._design-system')
```

**Step 2: Replace Cards**
```blade
{{-- OLD --}}
<div class="card border-0 shadow-sm">
    <div class="card-body">
        <h6>Label</h6>
        <h2>123</h2>
    </div>
</div>

{{-- NEW --}}
<div class="card-metric border-left-danger">
    <div class="metric-label text-danger">LABEL</div>
    <div class="metric-value">123</div>
    <div class="metric-desc">Description</div>
    <div class="card-icon"><i class="bi bi-icon text-danger"></i></div>
</div>
```

**Step 3: Replace Tables**
```blade
{{-- OLD --}}
<table class="table table-hover">

{{-- NEW --}}
<table class="table-corporate">
```

**Step 4: Replace Badges**
```blade
{{-- OLD --}}
<span class="badge bg-danger">Status</span>

{{-- NEW --}}
<span class="badge-corp badge-corp-danger">
    <i class="bi bi-icon"></i> Status
</span>
```

**Step 5: Replace Buttons**
```blade
{{-- OLD --}}
<a href="#" class="btn btn-sm btn-primary">Action</a>

{{-- NEW --}}
<a href="#" class="btn-action-corp">
    <i class="bi bi-icon"></i> Action
</a>
```

**Step 6: Add Loading States**
```javascript
<script>
document.querySelectorAll('form').forEach(form => {
    form.addEventListener('submit', function(e) {
        const btn = this.querySelector('button[type="submit"]');
        btn.disabled = true;
        btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Loading...';
    });
});
</script>
```

#### B.2 Common Pitfalls

1. **Forgetting data-label attributes**: Required for mobile table transformation
   ```blade
   {{-- WRONG --}}
   <td>Data</td>
   
   {{-- CORRECT --}}
   <td data-label="Column Name">Data</td>
   ```

2. **Inconsistent status mapping**: Use the status color mapping table
   ```blade
   {{-- WRONG --}}
   <span class="badge-corp badge-corp-danger">Critical</span>
   
   {{-- CORRECT (critical = warning color) --}}
   <span class="badge-corp badge-corp-warning">Critical</span>
   ```

3. **Missing icon in badges**: Always include icon for consistency
   ```blade
   {{-- WRONG --}}
   <span class="badge-corp badge-corp-success">Healthy</span>
   
   {{-- CORRECT --}}
   <span class="badge-corp badge-corp-success">
       <i class="bi bi-check-circle"></i> Healthy
   </span>
   ```

4. **Not using flex gap for buttons**: Use d-flex with gap-2
   ```blade
   {{-- WRONG --}}
   <a href="#" class="btn-action-corp"></a>
   <a href="#" class="btn-primary-corp"></a>
   
   {{-- CORRECT --}}
   <div class="d-flex gap-2">
       <a href="#" class="btn-action-corp"></a>
       <a href="#" class="btn-primary-corp"></a>
   </div>
   ```

### C. Design System Maintenance

#### C.1 Adding New Components

When adding new components to the design system:

1. **Define in CSS Variables**: Add colors, spacing, etc. to `:root`
2. **Create Component Class**: Follow naming convention `.component-name`
3. **Add Mobile Responsive**: Include `@media (max-width: 768px)` styles
4. **Document Usage**: Add to Quick Reference Guide
5. **Create Blade Partial**: Create reusable component in `partials/`
6. **Test Across Pages**: Verify works in all maintenance pages

#### C.2 Updating Colors

To update the color scheme:

1. **Update CSS Variables**: Modify `:root` variables in `_design-system.blade.php`
2. **Update Status Mapping**: Update the status color mapping table
3. **Test All Pages**: Verify colors are consistent across all pages
4. **Update Documentation**: Update Quick Reference Guide

#### C.3 Version Control

**Design System Version**: 1.0.0

**Changelog**:
- **v1.0.0** (Initial Release): Complete design system with all components, responsive design, and loading states

**Future Enhancements**:
- Dark mode support
- Accessibility improvements (WCAG 2.1 AA compliance)
- Animation library for micro-interactions
- Print stylesheet for reports
- RTL (Right-to-Left) language support

### D. Browser Compatibility

| Browser | Version | Support Level | Notes |
|---------|---------|---------------|-------|
| Chrome | 90+ | Full | Recommended browser |
| Firefox | 88+ | Full | Fully supported |
| Safari | 14+ | Full | Fully supported |
| Edge | 90+ | Full | Chromium-based, fully supported |
| Opera | 76+ | Full | Chromium-based, fully supported |
| IE 11 | - | Not Supported | Use modern browser |

**CSS Features Used**:
- CSS Grid (supported in all modern browsers)
- CSS Flexbox (supported in all modern browsers)
- CSS Variables (supported in all modern browsers)
- CSS Transforms (supported in all modern browsers)
- Media Queries (supported in all modern browsers)

### E. Performance Considerations

**CSS Size**: ~15KB (minified)
**Load Time Impact**: < 50ms
**Render Performance**: 60fps animations
**Mobile Performance**: Optimized for 3G networks

**Optimization Techniques**:
- Use CSS transforms instead of position changes
- Minimize repaints with `will-change` property
- Use `contain` property for layout isolation
- Lazy load images and heavy content
- Minimize CSS animations on mobile

### F. Accessibility Checklist

- [ ] All interactive elements have focus states
- [ ] Color contrast meets WCAG 2.1 AA standards (4.5:1 for text)
- [ ] All images have alt text
- [ ] All forms have labels
- [ ] All buttons have descriptive text or aria-labels
- [ ] Keyboard navigation works for all interactive elements
- [ ] Screen reader announces status changes
- [ ] Loading states are announced to screen readers
- [ ] Error messages are associated with form fields
- [ ] Skip links provided for keyboard users

### G. Contact & Support

**Design System Owner**: Development Team
**Last Updated**: {{ now()->format('d F Y') }}
**Documentation Version**: 1.0.0

**For Questions or Issues**:
- Create issue in project repository
- Contact development team
- Refer to Laravel Blade documentation
- Refer to Bootstrap 5.3.3 documentation

---

**End of Design Document**

