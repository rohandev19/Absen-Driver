@extends('admin.layouts.app')

@section('title', 'Komponen Kendaraan')

@push('styles')
<style>
/* ── Page Header ── */
.vehicle-header {
    background: linear-gradient(135deg, #1e293b 0%, #0f172a 100%);
    border-radius: 16px;
    padding: 24px 28px;
    color: #fff;
    margin-bottom: 24px;
}
.vehicle-header .breadcrumb-item a { color: #94a3b8; text-decoration: none; }
.vehicle-header .breadcrumb-item a:hover { color: #fff; }
.vehicle-header .breadcrumb-item.active { color: #64748b; }
.vehicle-header .breadcrumb-item + .breadcrumb-item::before { color: #475569; }
.vehicle-header h2 { font-size: 1.75rem; font-weight: 700; letter-spacing: -.5px; }
.vehicle-header .meta { color: #94a3b8; font-size: .875rem; }
.odometer-badge {
    background: rgba(255,255,255,.08);
    border: 1px solid rgba(255,255,255,.12);
    border-radius: 8px;
    padding: 8px 16px;
    font-size: 1.1rem;
    font-weight: 600;
    color: #e2e8f0;
}
.health-pill {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    border-radius: 50px;
    padding: 6px 16px;
    font-weight: 600;
    font-size: .875rem;
}
.health-pill.good    { background: rgba(34,197,94,.15);  color: #4ade80; border: 1px solid rgba(34,197,94,.25); }
.health-pill.warning { background: rgba(234,179, 8,.15); color: #facc15; border: 1px solid rgba(234,179,8,.25); }
.health-pill.danger  { background: rgba(239, 68,68,.15); color: #f87171; border: 1px solid rgba(239,68,68,.25); }

/* ── Metric Cards ── */
.metric-card {
    border: none;
    border-radius: 14px;
    background: #fff;
    box-shadow: 0 1px 3px rgba(0,0,0,.06), 0 4px 12px rgba(0,0,0,.04);
    transition: transform .15s, box-shadow .15s;
}
.metric-card:hover { transform: translateY(-2px); box-shadow: 0 4px 16px rgba(0,0,0,.09); }
.metric-card .metric-icon {
    width: 44px; height: 44px;
    border-radius: 10px;
    display: flex; align-items: center; justify-content: center;
    font-size: 1.25rem;
}
.metric-card .metric-value { font-size: 1.5rem; font-weight: 700; color: #0f172a; }
.metric-card .metric-label { font-size: .75rem; color: #94a3b8; font-weight: 500; text-transform: uppercase; letter-spacing: .5px; }

/* ── Interval Guide Modal ── */
#intervalGuideModal .modal-header {
    background: linear-gradient(135deg, #1e293b 0%, #0f172a 100%);
    color: #fff;
    border-bottom: none;
    border-radius: 12px 12px 0 0;
}
#intervalGuideModal .modal-header .btn-close { filter: invert(1) grayscale(1); }
#intervalGuideModal .vehicle-tabs .nav-link {
    border-radius: 8px;
    color: #64748b;
    font-weight: 500;
    padding: 7px 20px;
}
#intervalGuideModal .vehicle-tabs .nav-link.active {
    background: #0f172a;
    color: #fff;
}
#intervalGuideModal .accordion-button {
    font-weight: 600;
    font-size: .875rem;
    color: #1e293b;
    background: #f8fafc;
    min-height: 44px;
}
#intervalGuideModal .accordion-button:not(.collapsed) {
    color: #2563eb;
    background: #eff6ff;
    box-shadow: none;
}
#intervalGuideModal .table th { font-size: .75rem; text-transform: uppercase; letter-spacing: .5px; color: #94a3b8; }
#intervalGuideModal .table td { font-size: .8125rem; vertical-align: middle; }
.km-badge  { background: #dbeafe; color: #1d4ed8; border-radius: 6px; padding: 2px 8px; font-size: .75rem; font-weight: 600; }
.day-badge { background: #dcfce7; color: #15803d; border-radius: 6px; padding: 2px 8px; font-size: .75rem; font-weight: 600; }
.na-badge  { background: #f1f5f9; color: #94a3b8; border-radius: 6px; padding: 2px 8px; font-size: .75rem; }

/* Component Groups */
.component-toolbar {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 16px;
    margin-bottom: 14px;
}
.component-toolbar h5 {
    font-size: 1rem;
    font-weight: 700;
    color: #0f172a;
}
.component-toolbar .subtitle {
    color: #64748b;
    font-size: .8125rem;
}
.component-category-grid {
    display: grid;
    gap: 16px;
}
.component-category-card {
    background: #fff;
    border: 1px solid #e5e7eb;
    border-radius: 12px;
    box-shadow: 0 1px 2px rgba(15, 23, 42, .04);
    overflow: hidden;
}
.component-category-head {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 12px;
    padding: 14px 16px;
    background: #f8fafc;
    border-bottom: 1px solid #e5e7eb;
}
.component-category-title {
    display: flex;
    align-items: center;
    gap: 10px;
    min-width: 0;
}
.component-category-icon {
    width: 36px;
    height: 36px;
    border-radius: 10px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
}
.component-category-name {
    font-weight: 700;
    color: #0f172a;
    line-height: 1.2;
}
.component-category-count {
    font-size: .75rem;
    color: #64748b;
}
.component-items {
    display: grid;
    gap: 0;
}
.component-item {
    display: grid;
    grid-template-columns: minmax(190px, 1.4fr) repeat(5, minmax(110px, 1fr)) auto;
    gap: 12px;
    align-items: center;
    padding: 14px 16px;
}
.component-item + .component-item {
    border-top: 1px solid #f1f5f9;
}
.component-name {
    font-weight: 700;
    color: #0f172a;
}
.component-meta {
    font-size: .75rem;
    color: #64748b;
}
.component-field-label {
    display: block;
    color: #94a3b8;
    font-size: .68rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: .04em;
    margin-bottom: 3px;
}
.component-field-value {
    color: #0f172a;
    font-size: .875rem;
    font-weight: 600;
}
.component-actions {
    display: flex;
    justify-content: flex-end;
    gap: 6px;
}
.component-empty {
    background: #fff;
    border: 1px dashed #cbd5e1;
    border-radius: 12px;
    padding: 38px 18px;
    text-align: center;
    color: #64748b;
}
.form-autofill-note {
    background: #f8fafc;
    border: 1px solid #e2e8f0;
    border-radius: 8px;
    color: #475569;
    display: none;
    font-size: .8rem;
    padding: 9px 12px;
}
.form-autofill-note.show {
    display: block;
}

[data-bs-theme="dark"] .container-fluid .metric-card.card {
    background: #1e293b !important;
    border: 1px solid rgba(148, 163, 184, .16) !important;
    box-shadow: 0 10px 26px rgba(2, 6, 23, .25);
}
[data-bs-theme="dark"] .metric-card:hover {
    box-shadow: 0 16px 34px rgba(2, 6, 23, .34);
}
[data-bs-theme="dark"] .metric-card .metric-value,
[data-bs-theme="dark"] .component-toolbar h5 {
    color: #f8fafc !important;
}
[data-bs-theme="dark"] .metric-card .metric-label,
[data-bs-theme="dark"] .component-toolbar .subtitle,
[data-bs-theme="dark"] .component-category-count,
[data-bs-theme="dark"] .component-meta,
[data-bs-theme="dark"] .component-field-label {
    color: #94a3b8 !important;
}
[data-bs-theme="dark"] .component-category-card {
    background: #111827;
    border-color: rgba(148, 163, 184, .18);
    box-shadow: 0 14px 34px rgba(2, 6, 23, .28);
}
[data-bs-theme="dark"] .component-category-head {
    background: #1e293b;
    border-bottom-color: rgba(148, 163, 184, .18);
}
[data-bs-theme="dark"] .component-item + .component-item {
    border-top-color: rgba(148, 163, 184, .14);
}
[data-bs-theme="dark"] .component-item:hover {
    background: rgba(15, 23, 42, .42);
}
[data-bs-theme="dark"] .component-category-name,
[data-bs-theme="dark"] .component-name,
[data-bs-theme="dark"] .component-field-value {
    color: #f8fafc !important;
}
[data-bs-theme="dark"] .component-empty,
[data-bs-theme="dark"] .form-autofill-note {
    background: #111827;
    border-color: rgba(148, 163, 184, .22);
    color: #cbd5e1;
}
[data-bs-theme="dark"] #addComponentModal .modal-content,
[data-bs-theme="dark"] [id^="editComponentModal"] .modal-content {
    background: #1e293b !important;
    border: 1px solid rgba(148, 163, 184, .18);
}
[data-bs-theme="dark"] #addComponentModal .modal-header,
[data-bs-theme="dark"] #addComponentModal .modal-footer,
[data-bs-theme="dark"] [id^="editComponentModal"] .modal-header,
[data-bs-theme="dark"] [id^="editComponentModal"] .modal-footer {
    background: #111827 !important;
    border-color: rgba(148, 163, 184, .18) !important;
}
[data-bs-theme="dark"] #addComponentModal .text-muted,
[data-bs-theme="dark"] [id^="editComponentModal"] .text-muted {
    color: #94a3b8 !important;
}
[data-bs-theme="dark"] #intervalGuideModal .modal-content {
    background: #111827 !important;
    border: 1px solid rgba(148, 163, 184, .18) !important;
    box-shadow: 0 24px 80px rgba(2, 6, 23, .68) !important;
}
[data-bs-theme="dark"] #intervalGuideModal .modal-content > .px-4,
[data-bs-theme="dark"] #intervalGuideModal .modal-footer {
    background: #111827 !important;
    border-color: rgba(148, 163, 184, .18) !important;
}
[data-bs-theme="dark"] #intervalGuideModal .vehicle-tabs .nav-link {
    color: #cbd5e1 !important;
}
[data-bs-theme="dark"] #intervalGuideModal .vehicle-tabs .nav-link.active {
    background: #2563eb !important;
    color: #fff !important;
}
[data-bs-theme="dark"] #intervalGuideModal .accordion-item,
[data-bs-theme="dark"] #intervalGuideModal .accordion-body {
    background: #111827 !important;
    border-color: rgba(148, 163, 184, .16) !important;
}
[data-bs-theme="dark"] #intervalGuideModal .accordion-button {
    background: #1e293b !important;
    color: #f8fafc !important;
    border-color: rgba(148, 163, 184, .16) !important;
}
[data-bs-theme="dark"] #intervalGuideModal .accordion-button:not(.collapsed) {
    background: #172554 !important;
    color: #bfdbfe !important;
}
[data-bs-theme="dark"] #intervalGuideModal .table {
    --bs-table-color: #e2e8f0;
    --bs-table-bg: transparent;
    --bs-table-hover-bg: rgba(59, 130, 246, .08);
    --bs-table-border-color: rgba(148, 163, 184, .14);
}
[data-bs-theme="dark"] #intervalGuideModal .table thead tr,
[data-bs-theme="dark"] #intervalGuideModal .table th {
    background: #0f172a !important;
    color: #94a3b8 !important;
}
[data-bs-theme="dark"] #intervalGuideModal .table td,
[data-bs-theme="dark"] #intervalGuideModal .table td[style] {
    color: #e2e8f0 !important;
    border-color: rgba(148, 163, 184, .14) !important;
}
[data-bs-theme="dark"] #intervalGuideModal .km-badge {
    background: rgba(37, 99, 235, .22);
    color: #bfdbfe;
}
[data-bs-theme="dark"] #intervalGuideModal .day-badge {
    background: rgba(22, 163, 74, .22);
    color: #bbf7d0;
}
[data-bs-theme="dark"] #intervalGuideModal .na-badge {
    background: rgba(100, 116, 139, .25);
    color: #cbd5e1;
}

@media (max-width: 991.98px) {
    .component-item {
        grid-template-columns: 1fr 1fr;
    }
    .component-actions {
        grid-column: 1 / -1;
        justify-content: flex-start;
    }
}

@media (max-width: 575.98px) {
    .component-toolbar {
        align-items: stretch;
        flex-direction: column;
    }
    .component-toolbar .btn {
        width: 100%;
    }
    .component-item {
        grid-template-columns: 1fr;
    }
    .odometer-badge {
        font-size: .875rem;
        padding: 6px 12px;
    }
}
</style>
@endpush

@section('content')
{{-- Include centralized design system for consistent UI/UX --}}
@include('admin.maintenance.partials._design-system')

    {{-- PERBAIKAN 1: Pindahkan definisi array ke luar agar bisa dibaca oleh script JS di bawah --}}
    @php
        $kategoriIndo = [
            'Cairan & Pelumas' => ['Oli Mesin', 'Air Radiator', 'Minyak Rem', 'Oli Power Steering', 'Oli Transmisi'],
            'Filter' => ['Filter Oli', 'Filter Udara', 'Filter Bahan Bakar', 'Filter AC / Kabin'],
            'Rem' => ['Kampas Rem', 'Cakram Rem', 'Minyak Rem'],
            'Ban' => ['Ban Depan Kiri', 'Ban Depan Kanan', 'Ban Belakang Kiri', 'Ban Belakang Kanan', 'Ban Serep'],
            'Aki & Kelistrikan' => ['Aki', 'Alternator / Dinamo Ampere'],
            'Lampu' => ['Lampu Utama', 'Lampu Belakang', 'Lampu Sein', 'Lampu Rem'],
            'Fan Belt & Selang' => ['Timing Belt', 'V-Belt / Fan Belt', 'Selang Radiator'],
            'Kaki-kaki & Suspensi' => ['Shockbreaker', 'Ball Joint', 'Tie Rod'],
            'Mesin' => ['Busi', 'Koil Pengapian', 'Injektor'],
            'Transmisi' => ['Oli Transmisi', 'Kampas Kopling']
        ];
    @endphp

    <div class="container-fluid">

        {{-- ── Vehicle Header Card ── --}}
        <div class="vehicle-header d-flex flex-column flex-sm-row justify-content-between align-items-sm-center gap-3">
            <div>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb mb-2">
                        <li class="breadcrumb-item">
                            <a href="{{ route('admin.maintenance.dashboard') }}">Maintenance</a>
                        </li>
                        <li class="breadcrumb-item active">Komponen</li>
                    </ol>
                </nav>
                <h2 class="mb-1">{{ $vehicle->plate_number }}</h2>
                <div class="d-flex flex-wrap align-items-center gap-2 meta">
                    <span><i class="bi bi-truck me-1"></i>{{ $vehicle->type }}</span>
                    <span class="opacity-40">·</span>
                    <span><i class="bi bi-folder me-1"></i>{{ $vehicle->project->name ?? 'Pool' }}</span>
                    <span class="odometer-badge ms-1">
                        <i class="bi bi-speedometer2 me-1 text-cyan-400" style="color:#67e8f9"></i>
                        {{ number_format($vehicle->current_km) }} KM
                    </span>
                </div>
            </div>
            <div class="text-start text-sm-end">
                @php
                    $score = $healthReport['health_score'];
                    $pillClass = $score >= 70 ? 'good' : ($score >= 40 ? 'warning' : 'danger');
                    $pillIcon  = $score >= 70 ? 'bi-shield-check' : ($score >= 40 ? 'bi-shield-exclamation' : 'bi-shield-x');
                @endphp
                <div class="health-pill {{ $pillClass }} mb-2">
                    <i class="bi {{ $pillIcon }}"></i>
                    Health Score &nbsp;<strong>{{ $score }}/100</strong>
                </div>
                <a href="javascript:history.back()" class="btn btn-outline-secondary btn-sm rounded-pill ms-2 mb-2 shadow-sm border-0 bg-white text-dark">
                    <i class="bi bi-arrow-left"></i> Kembali
                </a>
                <div style="color:#64748b;font-size:.8125rem;">{{ $healthReport['status']['label'] }} — {{ $healthReport['status']['action'] }}</div>
            </div>
        </div>

        {{-- ── Metric Cards ── --}}
        <div class="row mb-4 g-3">
            @php
                $metrics = [
                    ['label'=>'Component Health',      'value'=>$healthReport['breakdown']['component_health'].'%',      'icon'=>'bi-gear-fill',            'bg'=>'#eff6ff','color'=>'#2563eb'],
                    ['label'=>'Maintenance Compliance','value'=>$healthReport['breakdown']['maintenance_compliance'].'%', 'icon'=>'bi-check-circle-fill',    'bg'=>'#f0fdf4','color'=>'#16a34a'],
                    ['label'=>'Daily Check Score',     'value'=>$healthReport['breakdown']['daily_check_score'].'%',     'icon'=>'bi-clipboard-check-fill', 'bg'=>'#ecfeff','color'=>'#0891b2'],
                    ['label'=>'Age Factor',            'value'=>$healthReport['breakdown']['age_factor'].'%',            'icon'=>'bi-calendar-fill',        'bg'=>'#fefce8','color'=>'#ca8a04'],
                ];
            @endphp
            @foreach($metrics as $m)
            <div class="col-6 col-md-3">
                <div class="metric-card card p-3">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <div class="metric-label mb-1">{{ $m['label'] }}</div>
                            <div class="metric-value">{{ $m['value'] }}</div>
                        </div>
                        <div class="metric-icon" style="background:{{ $m['bg'] }};color:{{ $m['color'] }}">
                            <i class="bi {{ $m['icon'] }}"></i>
                        </div>
                    </div>
                </div>
            </div>
            @endforeach
        </div>

        {{-- Components grouped by category --}}
        @php
            $categoryIcons = [
                'Cairan & Pelumas' => ['icon' => 'bi-droplet-half', 'bg' => '#eff6ff', 'color' => '#2563eb'],
                'Filter' => ['icon' => 'bi-funnel', 'bg' => '#f0fdf4', 'color' => '#16a34a'],
                'Rem' => ['icon' => 'bi-disc', 'bg' => '#fef2f2', 'color' => '#dc2626'],
                'Ban' => ['icon' => 'bi-circle', 'bg' => '#f8fafc', 'color' => '#475569'],
                'Aki & Kelistrikan' => ['icon' => 'bi-lightning-charge', 'bg' => '#fefce8', 'color' => '#ca8a04'],
                'Lampu' => ['icon' => 'bi-lightbulb', 'bg' => '#fff7ed', 'color' => '#ea580c'],
                'Fan Belt & Selang' => ['icon' => 'bi-link-45deg', 'bg' => '#f5f3ff', 'color' => '#7c3aed'],
                'Kaki-kaki & Suspensi' => ['icon' => 'bi-sliders2', 'bg' => '#ecfeff', 'color' => '#0891b2'],
                'Mesin' => ['icon' => 'bi-cpu', 'bg' => '#f1f5f9', 'color' => '#0f172a'],
                'Transmisi' => ['icon' => 'bi-gear-wide-connected', 'bg' => '#fdf2f8', 'color' => '#db2777'],
            ];
            $componentsByCategory = $vehicle->components->groupBy('category');
            $orderedCategories = collect(array_keys($kategoriIndo))
                ->merge($componentsByCategory->keys())
                ->unique()
                ->filter(fn ($category) => $componentsByCategory->has($category));
        @endphp

        <div class="component-toolbar">
            <div>
                <h5 class="mb-1">Daftar Komponen</h5>
                <div class="subtitle">Dikelompokkan per sistem kendaraan agar lebih mudah dipantau.</div>
            </div>
            <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#addComponentModal">
                <i class="bi bi-plus-lg me-1"></i> Tambah Komponen
            </button>
        </div>

        @if($vehicle->components->isEmpty())
            <div class="component-empty">
                <i class="bi bi-inbox fs-2 d-block mb-3 opacity-25"></i>
                <p class="mb-0">Belum ada komponen. Klik "Tambah Komponen" untuk mulai tracking.</p>
            </div>
        @else
            <div class="component-category-grid">
                @foreach($orderedCategories as $category)
                    @php
                        $meta = $categoryIcons[$category] ?? ['icon' => 'bi-box', 'bg' => '#f8fafc', 'color' => '#64748b'];
                        $items = $componentsByCategory[$category];
                    @endphp
                    <section class="component-category-card">
                        <div class="component-category-head">
                            <div class="component-category-title">
                                <span class="component-category-icon" style="background:{{ $meta['bg'] }};color:{{ $meta['color'] }}">
                                    <i class="bi {{ $meta['icon'] }}"></i>
                                </span>
                                <div>
                                    <div class="component-category-name">{{ $category }}</div>
                                    <div class="component-category-count">{{ $items->count() }} komponen aktif</div>
                                </div>
                            </div>
                        </div>
                        <div class="component-items">
                            @foreach($items->sortBy('component_name') as $comp)
                                <article class="component-item">
                                    <div>
                                        <div class="component-name">{{ $comp->component_name }}</div>
                                        <div class="component-meta">{{ $comp->notes ?: 'Tidak ada catatan' }}</div>
                                    </div>
                                    <div>
                                        <span class="component-field-label">Status</span>
                                        @if($comp->status == 'overdue')
                                            <span class="badge-corp badge-corp-danger"><i class="bi bi-exclamation-triangle-fill"></i> Overdue</span>
                                        @elseif($comp->status == 'critical')
                                            <span class="badge-corp badge-corp-warning"><i class="bi bi-exclamation-circle-fill"></i> Critical</span>
                                        @elseif($comp->status == 'warning')
                                            <span class="badge-corp badge-corp-info"><i class="bi bi-info-circle-fill"></i> Warning</span>
                                        @else
                                            <span class="badge-corp badge-corp-success"><i class="bi bi-check-circle-fill"></i> Healthy</span>
                                        @endif
                                    </div>
                                    <div>
                                        <span class="component-field-label">Sisa KM</span>
                                        <span class="component-field-value">{{ $comp->km_remaining !== null ? number_format($comp->km_remaining) . ' KM' : '-' }}</span>
                                    </div>
                                    <div>
                                        <span class="component-field-label">Next</span>
                                        <span class="component-field-value">{{ $comp->next_replacement_km ? number_format($comp->next_replacement_km) . ' KM' : '-' }}</span>
                                    </div>
                                    <div>
                                        <span class="component-field-label">Interval</span>
                                        <span class="component-field-value">
                                            @if($comp->replacement_interval_km)
                                                {{ number_format($comp->replacement_interval_km) }} KM
                                            @endif
                                            @if($comp->replacement_interval_km && $comp->replacement_interval_days)
                                                /
                                            @endif
                                            @if($comp->replacement_interval_days)
                                                {{ $comp->replacement_interval_days }} hari
                                            @endif
                                            @if(!$comp->replacement_interval_km && !$comp->replacement_interval_days)
                                                -
                                            @endif
                                        </span>
                                    </div>
                                    <div>
                                        <span class="component-field-label">Biaya</span>
                                        <span class="component-field-value">Rp {{ number_format($comp->cost_per_replacement, 0, ',', '.') }}</span>
                                    </div>
                                    <div class="component-actions">
                                        <button class="btn btn-sm btn-outline-primary" data-bs-toggle="modal"
                                            data-bs-target="#editComponentModal{{ $comp->id }}" title="Edit komponen">
                                            <i class="bi bi-pencil"></i>
                                        </button>
                                        <form action="{{ route('admin.maintenance.components.delete', $comp->id) }}" method="POST"
                                            class="d-inline form-delete-global" data-message="Anda yakin ingin menghapus komponen <b>{{ $comp->component_name }}</b>?">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-outline-danger" title="Hapus komponen">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </form>
                                    </div>
                                </article>
                            @endforeach
                        </div>
                    </section>
                @endforeach
            </div>
        @endif
    </div>

    {{-- PERBAIKAN 2: Pindahkan perulangan Modal Edit KELUAR dari tag tabel agar valid dan terbaca --}}
    @foreach($vehicle->components as $comp)
        <div class="modal fade" id="editComponentModal{{ $comp->id }}" tabindex="-1">
            <div class="modal-dialog">
                <div class="modal-content">
                    <form action="{{ route('admin.maintenance.components.update', $comp->id) }}"
                        method="POST">
                        @csrf
                        @method('PUT')
                        <div class="modal-header">
                            <h5 class="modal-title">Edit: {{ $comp->component_name }}</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>
                        <div class="modal-body">
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label">Interval KM</label>
                                    <input type="number" name="replacement_interval_km"
                                        class="form-control"
                                        value="{{ $comp->replacement_interval_km }}">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Interval Hari</label>
                                    <input type="number" name="replacement_interval_days"
                                        class="form-control"
                                        value="{{ $comp->replacement_interval_days }}">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Last Replacement KM</label>
                                    <input type="number" name="last_replacement_km" class="form-control"
                                        value="{{ $comp->last_replacement_km }}">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Last Replacement Date</label>
                                    <input type="date" name="last_replacement_date" class="form-control"
                                        value="{{ $comp->last_replacement_date?->format('Y-m-d') }}">
                                </div>
                                <div class="col-12">
                                    <label class="form-label">Biaya Penggantian (Rp)</label>
                                    <input type="number" name="cost_per_replacement"
                                        class="form-control"
                                        value="{{ round($comp->cost_per_replacement) }}" step="any"
                                        min="0">
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary"
                                data-bs-dismiss="modal">Batal</button>
                            <button type="submit" class="btn btn-primary">Simpan</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endforeach

    {{-- Add Component Modal --}}
    <div class="modal fade" id="addComponentModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <form action="{{ route('admin.maintenance.components.store', $vehicle->id) }}" method="POST">
                    @csrf
                    <div class="modal-header">
                        <h5 class="modal-title">Tambah Komponen Baru</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>

                    <div class="modal-body">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">Kategori <span class="text-danger">*</span></label>
                                <select name="category" class="form-select" required id="categorySelect">
                                    <option value="">Pilih Kategori</option>
                                    @foreach($kategoriIndo as $cat => $items)
                                        <option value="{{ $cat }}">{{ $cat }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Nama Komponen <span class="text-danger">*</span></label>
                                <select name="component_name" class="form-select" required id="componentSelect">
                                    <option value="">Pilih kategori dulu</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Interval KM</label>
                                <input type="number" name="replacement_interval_km" class="form-control"
                                    id="replacementIntervalKm" placeholder="Contoh: 5000" min="0" step="100">
                                <small class="text-muted">Ganti setiap berapa KM</small>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Interval Hari</label>
                                <input type="number" name="replacement_interval_days" class="form-control"
                                    id="replacementIntervalDays" placeholder="Contoh: 180" min="0" step="1">
                                <small class="text-muted">Atau ganti setiap berapa hari</small>
                            </div>
                            <div class="col-12">
                                <div class="form-autofill-note" id="autofillNote">
                                    <i class="bi bi-magic me-1 text-primary"></i>
                                    <span id="autofillNoteText">Form otomatis mengikuti panduan komponen.</span>
                                </div>
                            </div>
                            <div class="col-12">
                                <button type="button"
                                        class="btn btn-outline-primary btn-sm"
                                        data-bs-toggle="modal"
                                        data-bs-target="#intervalGuideModal"
                                        id="btnPanduanInterval">
                                    <i class="bi bi-journal-text me-1"></i> Lihat Panduan Interval
                                </button>
                                <small class="text-muted ms-2">Referensi resmi dealer</small>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Last Replacement KM</label>
                                <input type="number" name="last_replacement_km" class="form-control"
                                    value="{{ $vehicle->current_km }}">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Last Replacement Date</label>
                                <input type="date" name="last_replacement_date" class="form-control"
                                    value="{{ now()->format('Y-m-d') }}">
                            </div>
                            <div class="col-12">
                                <label class="form-label">Biaya Penggantian (Rp) <span class="text-danger">*</span></label>
                                <input type="number" name="cost_per_replacement" class="form-control"
                                    id="replacementCost" placeholder="Contoh: 350000" required min="0" step="1000">
                                <small class="text-muted">Estimasi harga pasar Indonesia, tetap sesuaikan dengan vendor/bengkel.</small>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-primary">Tambah Komponen</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

{{-- ── Interval Guide Modal (layered, opens above #addComponentModal) ── --}}
<div class="modal fade" id="intervalGuideModal" tabindex="-1"
     aria-labelledby="intervalGuideModalLabel"
     data-bs-backdrop="false"
     style="z-index: 1060;">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <div class="modal-content" style="border-radius:12px;border:none;box-shadow:0 20px 60px rgba(0,0,0,.3);">

            {{-- Header --}}
            <div class="modal-header" style="background:linear-gradient(135deg,#1e293b,#0f172a);color:#fff;border-radius:12px 12px 0 0;border-bottom:none;">
                <div>
                    <h5 class="modal-title mb-0" id="intervalGuideModalLabel">
                        <i class="bi bi-journal-text me-2" style="color:#60a5fa"></i>
                        Panduan Interval Perawatan Komponen
                    </h5>
                    <div style="color:#94a3b8;font-size:.8rem;margin-top:2px">Referensi resmi dealer Isuzu &amp; Daihatsu</div>
                </div>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Tutup"></button>
            </div>

            {{-- Vehicle Type Tabs --}}
            <div class="px-4 pt-3 pb-0 border-bottom" style="background:#f8fafc;">
                <ul class="nav nav-pills vehicle-tabs gap-1" id="guideVehicleTabs">
                    <li class="nav-item">
                        <button class="nav-link active" id="tab-elf" data-vehicle="Isuzu Elf" onclick="switchGuideVehicle(this)">
                            <i class="bi bi-truck me-1"></i> Isuzu Elf
                        </button>
                    </li>
                    <li class="nav-item">
                        <button class="nav-link" id="tab-grandmax" data-vehicle="Grand Max" onclick="switchGuideVehicle(this)">
                            <i class="bi bi-truck me-1"></i> Grand Max
                        </button>
                    </li>
                </ul>
            </div>

            {{-- Body: Accordion filled by JS --}}
            <div class="modal-body p-0" id="intervalGuideBody" style="max-height:55vh;overflow-y:auto;">
            </div>

            {{-- Footer Disclaimer + Back Button --}}
            <div class="modal-footer py-2 px-4 d-flex align-items-center justify-content-between flex-wrap gap-2"
                 style="background:#f8fafc;border-top:1px solid #e2e8f0;border-radius:0 0 12px 12px;">
                <small class="text-muted" style="flex:1;min-width:0">
                    <i class="bi bi-info-circle me-1 text-primary"></i>
                    <strong>Sumber:</strong> Dealer Resmi Isuzu &amp; Daihatsu Indonesia.
                    Interval merupakan rekomendasi umum — sesuaikan dengan kondisi operasional dan buku manual kendaraan.
                </small>
                <button type="button"
                        class="btn btn-primary btn-sm flex-shrink-0"
                        id="btnBackToForm"
                        style="white-space:nowrap">
                    <i class="bi bi-arrow-left me-1"></i> Kembali ke Form
                </button>
            </div>
        </div>
    </div>
</div>

@push('scripts')
    <script>
        const categories = @json($kategoriIndo);

        const presetValues = {
            'Oli Mesin': { cost: 590000, source: 'Kisaran oli diesel 10L Pertamina/Shell' },
            'Air Radiator': { cost: 120000, source: 'Kisaran coolant 5L atau coolant premium 1L' },
            'Minyak Rem': { cost: 122000, source: 'Kisaran brake fluid DOT 4 1L' },
            'Oli Power Steering': { cost: 120000, source: 'Estimasi ATF/power steering fluid 1L' },
            'Oli Transmisi': { cost: 250000, source: 'Estimasi oli transmisi 4L' },
            'Filter Oli': { cost: 75000, source: 'Kisaran filter oli diesel umum' },
            'Filter Udara': { cost: 180000, source: 'Estimasi filter udara kendaraan niaga ringan' },
            'Filter Bahan Bakar': { cost: 90000, source: 'Kisaran filter solar L300/Canter' },
            'Filter AC / Kabin': { cost: 80000, source: 'Estimasi filter kabin' },
            'Kampas Rem': { cost: 180000, source: 'Kisaran kampas rem truk ringan/Gran Max' },
            'Cakram Rem': { cost: 450000, source: 'Estimasi disc rotor aftermarket' },
            'Ban Depan Kiri': { cost: 850000, source: 'Estimasi ban niaga ringan per pcs' },
            'Ban Depan Kanan': { cost: 850000, source: 'Estimasi ban niaga ringan per pcs' },
            'Ban Belakang Kiri': { cost: 850000, source: 'Estimasi ban niaga ringan per pcs' },
            'Ban Belakang Kanan': { cost: 850000, source: 'Estimasi ban niaga ringan per pcs' },
            'Ban Serep': { cost: 850000, source: 'Estimasi ban niaga ringan per pcs' },
            'Aki': { cost: 950000, source: 'Estimasi aki mobil niaga ringan' },
            'Alternator / Dinamo Ampere': { cost: 1500000, source: 'Estimasi alternator/dinamo ampere' },
            'Lampu Utama': { cost: 120000, source: 'Estimasi bohlam/headlamp' },
            'Lampu Belakang': { cost: 100000, source: 'Estimasi lampu belakang' },
            'Lampu Sein': { cost: 50000, source: 'Estimasi lampu sein' },
            'Lampu Rem': { cost: 50000, source: 'Estimasi lampu rem' },
            'Timing Belt': { cost: 1500000, source: 'Estimasi paket timing belt dan jasa' },
            'V-Belt / Fan Belt': { cost: 180000, source: 'Estimasi v-belt/fan belt' },
            'Selang Radiator': { cost: 150000, source: 'Estimasi selang radiator' },
            'Shockbreaker': { cost: 650000, source: 'Estimasi shockbreaker per sisi' },
            'Ball Joint': { cost: 250000, source: 'Estimasi ball joint' },
            'Tie Rod': { cost: 250000, source: 'Estimasi tie rod' },
            'Busi': { cost: 150000, source: 'Estimasi set busi bensin' },
            'Koil Pengapian': { cost: 350000, source: 'Estimasi koil pengapian' },
            'Injektor': { cost: 1200000, source: 'Estimasi servis/penggantian injektor' },
            'Kampas Kopling': { cost: 1800000, source: 'Estimasi paket kampas kopling dan jasa' },
        };

        const intervalGuideData = {
            "Isuzu Elf": {
                "Cairan & Pelumas": [
                    { name: "Oli Mesin",            km: 10000,  days: 180,  note: null },
                    { name: "Air Radiator",         km: 40000,  days: 730,  note: null },
                    { name: "Minyak Rem",           km: 40000,  days: 730,  note: null },
                    { name: "Oli Power Steering",   km: 40000,  days: 730,  note: null },
                    { name: "Oli Transmisi",        km: 40000,  days: 730,  note: null },
                ],
                "Filter": [
                    { name: "Filter Oli",           km: 10000,  days: 180,  note: null },
                    { name: "Filter Udara",         km: 20000,  days: 365,  note: null },
                    { name: "Filter Bahan Bakar",   km: 20000,  days: 365,  note: null },
                    { name: "Filter AC / Kabin",    km: 20000,  days: 365,  note: null },
                ],
                "Rem": [
                    { name: "Kampas Rem",           km: 40000,  days: 730,  note: null },
                    { name: "Cakram Rem",           km: 80000,  days: 1460, note: null },
                    { name: "Minyak Rem",           km: 40000,  days: 730,  note: null },
                ],
                "Ban": [
                    { name: "Ban Depan Kiri",       km: 80000,  days: 1460, note: "Rotasi setiap 10.000 km" },
                    { name: "Ban Depan Kanan",      km: 80000,  days: 1460, note: "Rotasi setiap 10.000 km" },
                    { name: "Ban Belakang Kiri",    km: 80000,  days: 1460, note: "Rotasi setiap 10.000 km" },
                    { name: "Ban Belakang Kanan",   km: 80000,  days: 1460, note: "Rotasi setiap 10.000 km" },
                    { name: "Ban Serep",            km: 80000,  days: 1460, note: null },
                ],
                "Aki & Kelistrikan": [
                    { name: "Aki",                  km: null,   days: 730,  note: null },
                    { name: "Alternator / Dinamo Ampere", km: null, days: 1460, note: null },
                ],
                "Lampu": [
                    { name: "Lampu Utama",          km: null,   days: 730,  note: null },
                    { name: "Lampu Belakang",       km: null,   days: 730,  note: null },
                    { name: "Lampu Sein",           km: null,   days: 730,  note: null },
                    { name: "Lampu Rem",            km: null,   days: 730,  note: null },
                ],
                "Fan Belt & Selang": [
                    { name: "Timing Belt",          km: 100000, days: 1825, note: null },
                    { name: "V-Belt / Fan Belt",    km: 40000,  days: 730,  note: null },
                    { name: "Selang Radiator",      km: 80000,  days: 1460, note: null },
                ],
                "Kaki-kaki & Suspensi": [
                    { name: "Shockbreaker",         km: 80000,  days: 1460, note: null },
                    { name: "Ball Joint",           km: 80000,  days: 1460, note: null },
                    { name: "Tie Rod",              km: 80000,  days: 1460, note: null },
                ],
                "Mesin": [
                    { name: "Busi",                 km: null,   days: null, note: "Tidak berlaku (diesel)" },
                    { name: "Koil Pengapian",       km: null,   days: null, note: "Tidak berlaku (diesel)" },
                    { name: "Injektor",             km: 80000,  days: 1460, note: null },
                ],
                "Transmisi": [
                    { name: "Oli Transmisi",        km: 40000,  days: 730,  note: null },
                    { name: "Kampas Kopling",       km: 80000,  days: null, note: null },
                ],
            },

            "Grand Max": {
                "Cairan & Pelumas": [
                    { name: "Oli Mesin",            km: 5000,   days: 180,  note: null },
                    { name: "Air Radiator",         km: 20000,  days: 730,  note: null },
                    { name: "Minyak Rem",           km: 20000,  days: 730,  note: null },
                    { name: "Oli Power Steering",   km: 20000,  days: 730,  note: null },
                    { name: "Oli Transmisi",        km: 20000,  days: 730,  note: null },
                ],
                "Filter": [
                    { name: "Filter Oli",           km: 10000,  days: 180,  note: null },
                    { name: "Filter Udara",         km: 10000,  days: 365,  note: null },
                    { name: "Filter Bahan Bakar",   km: 20000,  days: 730,  note: null },
                    { name: "Filter AC / Kabin",    km: 15000,  days: 365,  note: null },
                ],
                "Rem": [
                    { name: "Kampas Rem",           km: 30000,  days: 730,  note: null },
                    { name: "Cakram Rem",           km: 60000,  days: 1460, note: null },
                    { name: "Minyak Rem",           km: 20000,  days: 730,  note: null },
                ],
                "Ban": [
                    { name: "Ban Depan Kiri",       km: 60000,  days: 1095, note: "Rotasi setiap 10.000 km" },
                    { name: "Ban Depan Kanan",      km: 60000,  days: 1095, note: "Rotasi setiap 10.000 km" },
                    { name: "Ban Belakang Kiri",    km: 60000,  days: 1095, note: "Rotasi setiap 10.000 km" },
                    { name: "Ban Belakang Kanan",   km: 60000,  days: 1095, note: "Rotasi setiap 10.000 km" },
                    { name: "Ban Serep",            km: 60000,  days: 1095, note: null },
                ],
                "Aki & Kelistrikan": [
                    { name: "Aki",                  km: null,   days: 730,  note: null },
                    { name: "Alternator / Dinamo Ampere", km: null, days: 1460, note: null },
                ],
                "Lampu": [
                    { name: "Lampu Utama",          km: null,   days: 730,  note: null },
                    { name: "Lampu Belakang",       km: null,   days: 730,  note: null },
                    { name: "Lampu Sein",           km: null,   days: 730,  note: null },
                    { name: "Lampu Rem",            km: null,   days: 730,  note: null },
                ],
                "Fan Belt & Selang": [
                    { name: "Timing Belt",          km: 60000,  days: 1460, note: null },
                    { name: "V-Belt / Fan Belt",    km: 30000,  days: 730,  note: null },
                    { name: "Selang Radiator",      km: 60000,  days: 1460, note: null },
                ],
                "Kaki-kaki & Suspensi": [
                    { name: "Shockbreaker",         km: 60000,  days: 1460, note: null },
                    { name: "Ball Joint",           km: 60000,  days: 1460, note: null },
                    { name: "Tie Rod",              km: 60000,  days: 1460, note: null },
                ],
                "Mesin": [
                    { name: "Busi",                 km: 20000,  days: 365,  note: null },
                    { name: "Koil Pengapian",       km: 40000,  days: 730,  note: null },
                    { name: "Injektor",             km: 60000,  days: 1460, note: null },
                ],
                "Transmisi": [
                    { name: "Oli Transmisi",        km: 20000,  days: 730,  note: null },
                    { name: "Kampas Kopling",       km: 60000,  days: null, note: null },
                ],
            }
        };

        function renderIntervalGuide(vehicleType) {
            const data = intervalGuideData[vehicleType];
            const container = document.getElementById('intervalGuideBody');
            if (!data || !container) return;

            const categoryOrder = [
                'Cairan & Pelumas', 'Filter', 'Rem', 'Ban',
                'Aki & Kelistrikan', 'Lampu', 'Fan Belt & Selang',
                'Kaki-kaki & Suspensi', 'Mesin', 'Transmisi'
            ];

            let html = '<div class="accordion accordion-flush" id="guideAccordion">';

            categoryOrder.forEach((cat, idx) => {
                const items = data[cat];
                if (!items || items.length === 0) return;

                html += `
            <div class="accordion-item" style="border-bottom:1px solid #f1f5f9;">
                <h2 class="accordion-header">
                    <button class="accordion-button ${idx !== 0 ? 'collapsed' : ''} py-2 px-4"
                            type="button" data-bs-toggle="collapse"
                            data-bs-target="#guidecat${idx}">
                        ${cat}
                        <span class="badge bg-primary-subtle text-primary-emphasis border border-primary-subtle ms-2 fw-semibold">${items.length}</span>
                    </button>
                </h2>
                <div id="guidecat${idx}" class="accordion-collapse collapse ${idx === 0 ? 'show' : ''}">
                    <div class="accordion-body p-0">
                        <table class="table table-sm table-hover mb-0">
                            <thead>
                                <tr style="background:#f8fafc;">
                                    <th class="ps-4 py-2">Komponen</th>
                                    <th class="text-end py-2">Interval KM</th>
                                    <th class="text-end pe-4 py-2">Interval Hari</th>
                                </tr>
                            </thead>
                            <tbody>`;

                items.forEach(item => {
                    const kmVal  = item.km   ? item.km.toLocaleString('id-ID') + ' km'   : null;
                    const dayVal = item.days ? item.days + ' hari' : null;
                    const kmHtml  = kmVal  ? `<span class="km-badge">${kmVal}</span>`   : (item.note ? `<span class="na-badge">${item.note}</span>` : `<span class="na-badge">—</span>`);
                    const dayHtml = dayVal ? `<span class="day-badge">${dayVal}</span>` : (item.note ? `<span class="na-badge">${item.note}</span>` : `<span class="na-badge">—</span>`);
                    html += `
                <tr>
                    <td class="ps-4 fw-medium" style="color:#1e293b">${item.name}</td>
                    <td class="text-end">${kmHtml}</td>
                    <td class="text-end pe-4">${dayHtml}</td>
                </tr>`;
                });

                html += '</tbody></table></div></div></div>';
            });

            html += '</div>';
            container.innerHTML = html;
        }

        // Interval Guide — initialize on modal show
        const KNOWN_TYPES = ['Isuzu Elf', 'Grand Max'];
        const phpVehicleType = @json($vehicle->type ?? null);
        const defaultGuideType = KNOWN_TYPES.includes(phpVehicleType) ? phpVehicleType : 'Isuzu Elf';

        document.getElementById('intervalGuideModal').addEventListener('show.bs.modal', function () {
            // Set active tab
            document.querySelectorAll('#guideVehicleTabs .nav-link').forEach(tab => {
                tab.classList.toggle('active', tab.dataset.vehicle === defaultGuideType);
            });
            renderIntervalGuide(defaultGuideType);
        });

        function switchGuideVehicle(el) {
            document.querySelectorAll('#guideVehicleTabs .nav-link').forEach(t => t.classList.remove('active'));
            el.classList.add('active');
            renderIntervalGuide(el.dataset.vehicle);
        }

        function getGuideType() {
            const type = (phpVehicleType || '').toLowerCase();
            if (type.includes('grand') || type.includes('max') || type.includes('gran')) {
                return 'Grand Max';
            }
            return 'Isuzu Elf';
        }

        function findGuidePreset(componentName) {
            const guideType = getGuideType();
            const data = intervalGuideData[guideType] || {};

            for (const items of Object.values(data)) {
                const match = items.find(item => item.name === componentName);
                if (match) {
                    return { ...match, guideType };
                }
            }

            return null;
        }

        function setAddFormValue(id, value) {
            const input = document.getElementById(id);
            if (!input) return;
            input.value = value ?? '';
        }

        function updateAutofillNote(componentName, guidePreset, pricePreset) {
            const note = document.getElementById('autofillNote');
            const text = document.getElementById('autofillNoteText');
            if (!note || !text) return;

            if (!componentName) {
                note.classList.remove('show');
                text.textContent = '';
                return;
            }

            const guideText = guidePreset
                ? `Interval mengikuti panduan ${guidePreset.guideType}.`
                : 'Interval belum ada di panduan, silakan isi manual.';
            const priceText = pricePreset?.source
                ? `Harga: ${pricePreset.source}.`
                : 'Harga belum tersedia, silakan isi manual.';

            text.textContent = `${guideText} ${priceText}`;
            note.classList.add('show');
        }

        // "Kembali ke Form" — tutup panduan, buka kembali form tambah komponen
        document.getElementById('btnBackToForm').addEventListener('click', function () {
            const guideModal = bootstrap.Modal.getInstance(document.getElementById('intervalGuideModal'));
            if (guideModal) guideModal.hide();
            // Tunggu modal guide selesai menutup, lalu tampilkan form kembali
            document.getElementById('intervalGuideModal').addEventListener('hidden.bs.modal', function reopen() {
                document.getElementById('intervalGuideModal').removeEventListener('hidden.bs.modal', reopen);
                const addModal = bootstrap.Modal.getOrCreateInstance(document.getElementById('addComponentModal'));
                addModal.show();
            });
        });

        document.getElementById('categorySelect').addEventListener('change', function () {
            const category = this.value;
            const componentSelect = document.getElementById('componentSelect');

            componentSelect.innerHTML = '<option value="">Pilih Komponen</option>';
            setAddFormValue('replacementIntervalKm', '');
            setAddFormValue('replacementIntervalDays', '');
            setAddFormValue('replacementCost', '');
            updateAutofillNote('', null, null);

            if (category && categories[category]) {
                categories[category].forEach(item => {
                    const option = document.createElement('option');
                    option.value = item;
                    option.textContent = item;
                    componentSelect.appendChild(option);
                });
            }
        });

        document.getElementById('componentSelect').addEventListener('change', function () {
            const componentName = this.value;
            const guidePreset = findGuidePreset(componentName);
            const pricePreset = presetValues[componentName];

            setAddFormValue('replacementIntervalKm', guidePreset?.km ?? '');
            setAddFormValue('replacementIntervalDays', guidePreset?.days ?? '');
            setAddFormValue('replacementCost', pricePreset?.cost ?? '');
            updateAutofillNote(componentName, guidePreset, pricePreset);
        });
    </script>
@endpush
