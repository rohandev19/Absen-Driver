@extends('admin.layouts.app')

@section('title', 'Komponen Kendaraan')

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
        {{-- Header --}}
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb mb-2">
                        <li class="breadcrumb-item"><a href="{{ route('admin.maintenance.dashboard') }}">Maintenance</a>
                        </li>
                        <li class="breadcrumb-item active">Komponen</li>
                    </ol>
                </nav>
                <h3 class="fw-bold mb-1">{{ $vehicle->plate_number }}</h3>
                <p class="text-muted mb-0 d-flex align-items-center gap-2">
                    <span>{{ $vehicle->type }} • {{ $vehicle->project->name ?? 'Pool' }}</span>
                    <span class="badge bg-dark border border-secondary px-3 py-2 ms-2 fs-6">
                        <i class="bi bi-speedometer2 text-info me-1"></i> Odometer: {{ number_format($vehicle->current_km) }} KM
                    </span>
                </p>
            </div>  
            <div class="text-end">
                <div class="mb-2">
                    <span class="badge bg-{{ $healthReport['status']['color'] }} fs-5 px-4 py-2">
                        {!! $healthReport['status']['icon'] !!} Health Score: {{ $healthReport['health_score'] }}/100
                    </span>
                </div>
                <small class="text-muted">{{ $healthReport['status']['label'] }} -
                    {{ $healthReport['status']['action'] }}</small>
            </div>
        </div>

        {{-- Health Breakdown --}}
        <div class="row mb-4 g-3">
            <div class="col-md-3">
                <div class="card border-0 shadow-sm">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="text-muted mb-1 small">Component Health</h6>
                                <h3 class="mb-0">{{ $healthReport['breakdown']['component_health'] }}%</h3>
                            </div>
                            <div class="text-primary opacity-25">
                                <i class="bi bi-gear-fill fs-1"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card border-0 shadow-sm">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="text-muted mb-1 small">Maintenance Compliance</h6>
                                <h3 class="mb-0">{{ $healthReport['breakdown']['maintenance_compliance'] }}%</h3>
                            </div>
                            <div class="text-success opacity-25">
                                <i class="bi bi-check-circle-fill fs-1"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card border-0 shadow-sm">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="text-muted mb-1 small">Daily Check Score</h6>
                                <h3 class="mb-0">{{ $healthReport['breakdown']['daily_check_score'] }}%</h3>
                            </div>
                            <div class="text-info opacity-25">
                                <i class="bi bi-clipboard-check-fill fs-1"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card border-0 shadow-sm">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="text-muted mb-1 small">Age Factor</h6>
                                <h3 class="mb-0">{{ $healthReport['breakdown']['age_factor'] }}%</h3>
                            </div>
                            <div class="text-warning opacity-25">
                                <i class="bi bi-calendar-fill fs-1"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
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
                                            class="d-inline">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-outline-danger"
                                                onclick="return confirm('Yakin hapus komponen ini?')">
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