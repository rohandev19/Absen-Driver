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
    padding: 6px 14px;
    font-size: .8125rem;
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

        {{-- Components Table --}}
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white d-flex justify-content-between align-items-center py-3">
                <h5 class="mb-0 fw-bold">Daftar Komponen</h5>
                <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#addComponentModal">
                    <i class="bi bi-plus-lg me-1"></i> Tambah Komponen
                </button>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table-corporate mb-0">
                        <thead class="table-light">
                            <tr>
                                <th class="ps-4">Komponen</th>
                                <th>Status</th>
                                <th>Sisa KM</th>
                                <th>Next Replacement</th>
                                <th>Interval</th>
                                <th>Biaya</th>
                                <th class="text-end pe-4">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($vehicle->components as $comp)
                                <tr>
                                    <td class="ps-4">
                                        <div class="fw-bold">{{ $comp->component_name }}</div>
                                        <small class="text-muted">{{ $comp->category }}</small>
                                    </td>
                                    <td>
                                        @if($comp->status == 'overdue')
                                            <span class="badge-corp badge-corp-danger"><i class="bi bi-exclamation-triangle-fill"></i>
                                                Overdue</span>
                                        @elseif($comp->status == 'critical')
                                            <span class="badge-corp badge-corp-warning"><i
                                                    class="bi bi-exclamation-circle-fill"></i> Critical</span>
                                        @elseif($comp->status == 'warning')
                                            <span class="badge-corp badge-corp-info"><i class="bi bi-info-circle-fill"></i> Warning</span>
                                        @else
                                            <span class="badge-corp badge-corp-success"><i class="bi bi-check-circle-fill"></i>
                                                Healthy</span>
                                        @endif
                                    </td>
                                    <td>
                                        <span class="fw-bold">{{ number_format($comp->km_remaining) }}</span> KM
                                    </td>
                                    <td>{{ number_format($comp->next_replacement_km) }} KM</td>
                                    <td>
                                        <small class="text-muted">
                                            @if($comp->replacement_interval_km)
                                                {{ number_format($comp->replacement_interval_km) }} KM
                                            @endif
                                            @if($comp->replacement_interval_km && $comp->replacement_interval_days)
                                                /
                                            @endif
                                            @if($comp->replacement_interval_days)
                                                {{ $comp->replacement_interval_days }} hari
                                            @endif
                                        </small>
                                    </td>
                                    <td>Rp {{ number_format($comp->cost_per_replacement, 0, ',', '.') }}</td>
                                    <td class="text-end pe-4">
                                        <button class="btn btn-sm btn-outline-primary" data-bs-toggle="modal"
                                            data-bs-target="#editComponentModal{{ $comp->id }}">
                                            <i class="bi bi-pencil"></i>
                                        </button>
                                        <form action="{{ route('admin.maintenance.components.delete', $comp->id) }}" method="POST"
                                            class="d-inline form-delete-global" data-message="Anda yakin ingin menghapus komponen <b>{{ $comp->component_name }}</b>?">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-outline-danger">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="text-center py-5 text-muted">
                                        <i class="bi bi-inbox fs-2 d-block mb-3 opacity-25"></i>
                                        <p class="mb-0">Belum ada komponen. Klik "Tambah Komponen" untuk mulai tracking.</p>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
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
                                    placeholder="Contoh: 5000" min="0" step="100">
                                <small class="text-muted">Ganti setiap berapa KM</small>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Interval Hari</label>
                                <input type="number" name="replacement_interval_days" class="form-control"
                                    placeholder="Contoh: 180" min="0" step="1">
                                <small class="text-muted">Atau ganti setiap berapa hari</small>
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
                                    placeholder="Contoh: 350000" required min="0" step="1000">
                                <small class="text-muted">Wajib diisi. Masukkan estimasi biaya penggantian
                                    komponen.</small>
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
            'Oli Mesin': { km: 5000, days: 180, cost: 350000 },
            'Filter Oli': { km: 10000, days: 180, cost: 75000 },
            'Filter Udara': { km: 20000, days: 365, cost: 150000 },
            'Kampas Rem': { km: 20000, days: 365, cost: 450000 },
            'Ban Depan Kiri': { km: 40000, days: 1095, cost: 850000 },
            'Ban Depan Kanan': { km: 40000, days: 1095, cost: 850000 },
            'Ban Belakang Kiri': { km: 40000, days: 1095, cost: 850000 },
            'Ban Belakang Kanan': { km: 40000, days: 1095, cost: 850000 },
            'Busi': { km: 30000, days: null, cost: 150000 },
            'Timing Belt': { km: 80000, days: 1825, cost: 1500000 },
            'Aki': { km: null, days: 730, cost: 950000 },
            'Kampas Kopling': { km: 50000, days: null, cost: 1800000 },
        };

        const intervalGuideData = {
            "Isuzu Elf": {
                "Cairan & Pelumas": [
                    { name: "Oli Mesin",            km: 10000,  days: 180,  note: null },
                    { name: "Air Radiator (flush)", km: 40000,  days: 730,  note: null },
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
                    { name: "Alternator",           km: null,   days: 1460, note: null },
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
                    { name: "Air Radiator (flush)", km: 20000,  days: 730,  note: null },
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
                    { name: "Alternator",           km: null,   days: 1460, note: null },
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
                        <span class="badge bg-primary bg-opacity-10 text-primary ms-2 fw-semibold">${items.length}</span>
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

            document.querySelector('input[name="replacement_interval_km"]').value = '';
            document.querySelector('input[name="replacement_interval_days"]').value = '';
            document.querySelector('input[name="cost_per_replacement"]').value = '';

            if (presetValues[componentName]) {
                const preset = presetValues[componentName];

                if (preset.km) {
                    document.querySelector('input[name="replacement_interval_km"]').value = preset.km;
                }
                if (preset.days) {
                    document.querySelector('input[name="replacement_interval_days"]').value = preset.days;
                }
                if (preset.cost) {
                    document.querySelector('input[name="cost_per_replacement"]').value = preset.cost;
                }
            }
        });
    </script>
@endpush