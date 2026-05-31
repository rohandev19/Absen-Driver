@extends('admin.layouts.app')

@section('title', 'Dashboard - Kelola Driver')

@section('content')
    <div class="container-fluid p-0">

        <div class="card shadow-sm">
            <div class="card-header">
                <div class="row g-3 align-items-center justify-content-between">
                    {{-- Judul --}}
                    <div class="col-12 col-md-3">
                        <h2 class="h5 mb-0"><i class="bi bi-person-badge me-2"></i>Daftar Driver</h2>
                    </div>

                    {{-- Search Form & Tombol Tambah --}}
                    <div class="col-12 col-md-9">
                        <div class="d-flex justify-content-md-end align-items-center gap-2 flex-wrap">
                            
                            {{-- Form Filter & Pencarian (DIGABUNG) --}}
                            <form action="{{ route('admin.driver.index') }}" method="GET" class="d-flex gap-2 grow justify-content-end">
                                
                                {{-- 1. Dropdown Filter Project (BARU) --}}
                                <select name="project_id" class="form-select form-select-sm" style="max-width: 180px;" onchange="this.form.submit()">
                                    <option value="">-- Semua Project --</option>
                                    @foreach($projects as $project)
                                        <option value="{{ $project->id }}" {{ request('project_id') == $project->id ? 'selected' : '' }}>
                                            {{ $project->name }}
                                        </option>
                                    @endforeach
                                </select>

                                {{-- 2. Input Search Teks --}}
                                <div class="input-group" style="max-width: 250px;">
                                    <input type="text" name="search" class="form-control form-control-sm"
                                        placeholder="Cari Nama / NIK..." value="{{ request('search') }}">
                                    <button class="btn btn-outline-secondary btn-sm" type="submit">
                                        <i class="bi bi-search"></i>
                                    </button>
                                </div>
                            </form>

                            @can('is-master-admin')
                                <a href="{{ route('admin.driver.create') }}" class="btn btn-primary btn-sm text-nowrap">
                                    <i class="bi bi-plus-circle-fill me-1"></i> Tambah Driver
                                </a>
                            @endcan
                        </div>
                    </div>
                </div>
            </div>

            <div class="card-body">
                {{-- Info Hasil Pencarian --}}
                @if(request('search') || request('project_id'))
                    <div class="alert alert-info py-2 mb-3 small d-flex align-items-center justify-content-between">
                        <div>
                            <i class="bi bi-info-circle me-1"></i> Menampilkan hasil filter:
                            @if(request('project_id'))
                                <span class="badge bg-primary ms-1">{{ $projects->find(request('project_id'))->name ?? 'Project' }}</span>
                            @endif
                            @if(request('search'))
                                <strong class="ms-1">"{{ request('search') }}"</strong>
                            @endif
                        </div>
                        <a href="{{ route('admin.driver.index') }}" class="btn btn-sm btn-link text-decoration-none fw-bold">Reset Filter</a>
                    </div>
                @endif

                <div class="table-responsive">
                    <table class="table table-hover table-striped table-sm align-middle">
                        <thead class="table-light">
                            <tr>
                                <th style="width: 5%;">No.</th>
                                <th style="width: 15%;">ID (NIK)</th>
                                <th style="width: 25%;">Nama Lengkap</th>
                                <th style="width: 15%;">Project / Divisi</th>
                                <th style="width: 20%;">Info SIM</th>
                                @can('is-master-admin')
                                    <th class="text-center" style="width: 10%;">Aksi</th>
                                @endcan
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($drivers as $driver)
                                <tr>
                                    <td>{{ $drivers->firstItem() + $loop->index }}</td>
                                    <td><span class="badge bg-secondary font-monospace">{{ $driver->driver_id_nik }}</span></td>
                                    <td class="fw-bold">{{ $driver->full_name }}</td>
                                    
                                    {{-- Kolom Project --}}
                                    <td>
                                        @if($driver->project)
                                            <span class="badge bg-primary bg-opacity-10 text-primary border border-primary">
                                                {{ $driver->project->name }}
                                            </span>
                                        @else
                                            <span class="text-muted small">- Non Project -</span>
                                        @endif
                                    </td>

                                    {{-- Kolom SIM --}}
                                    <td>
                                        <div class="d-flex flex-column gap-1">
                                            <span class="fw-bold text-dark small">
                                                <i class="bi bi-card-heading me-1"></i> {{ $driver->sim_type ?? '??' }}
                                            </span>
                                            @if($driver->sim_expiry_date)
                                                @php
                                                    $expiry = \Carbon\Carbon::parse($driver->sim_expiry_date)->startOfDay();
                                                    $today = \Carbon\Carbon::now()->startOfDay();
                                                    $diff = $today->diffInDays($expiry, false);
                                                @endphp
                                                @if($diff < 0)
                                                    <span class="badge bg-danger" style="width: fit-content;">Mati ({{ $expiry->format('d/m/y') }})</span>
                                                @elseif($diff <= 30)
                                                    <span class="badge bg-warning text-dark" style="width: fit-content;">Exp {{ $diff }} Hr</span>
                                                @else
                                                    <span class="badge bg-success" style="width: fit-content;">Aktif</span>
                                                @endif
                                            @else
                                                <span class="badge bg-secondary opacity-50">-</span>
                                            @endif
                                        </div>
                                    </td>

                                    @can('is-master-admin')
                                        <td class="text-center">
                                            <div class="d-flex justify-content-center gap-1">
                                                <button type="button" class="btn btn-info btn-sm text-white" title="Lihat Dokumen"
                                                    data-bs-toggle="modal" data-bs-target="#dokumenModal"
                                                    data-nama="{{ $driver->full_name }}"
                                                    data-ktp="{{ $driver->foto_ktp ? route('admin.driver.dokumen', ['id' => $driver->id, 'jenis' => 'ktp']) : '' }}"
                                                    data-sim="{{ $driver->foto_sim ? route('admin.driver.dokumen', ['id' => $driver->id, 'jenis' => 'sim']) : '' }}">
                                                    <i class="bi bi-person-vcard"></i>
                                                </button>
                                                <a href="{{ route('admin.driver.edit', $driver->id) }}" class="btn btn-warning btn-sm" title="Edit Data">
                                                    <i class="bi bi-pencil-fill"></i>
                                                </a>
                                                <form action="{{ route('admin.driver.destroy', $driver->id) }}" method="POST" class="form-delete-global" data-message="Hapus {{ $driver->full_name }}?">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-danger btn-sm" title="Hapus Driver">
                                                        <i class="bi bi-trash-fill"></i>
                                                    </button>
                                                </form>
                                            </div>
                                        </td>
                                    @endcan
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="@can('is-master-admin') 6 @else 5 @endcan" class="text-center py-4 text-muted">
                                        <i class="bi bi-person-x display-6 d-block mb-2 opacity-50"></i>
                                        Data driver tidak ditemukan.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                {{-- Pagination dengan Query String agar filter tidak hilang saat pindah halaman --}}
                <div class="mt-3">
                    {{ $drivers->withQueryString()->links() }}
                </div>
            </div>
        </div>
    </div>

    {{-- Modal Dokumen --}}
    <div class="modal fade" id="dokumenModal" tabindex="-1" aria-labelledby="dokumenModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title fw-bold" id="dokumenModalLabel">Dokumen Driver: <span id="modalDriverName"></span></h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-md-6 text-center">
                            <h6 class="fw-bold">Foto KTP</h6>
                            <div id="ktpContainer" class="border rounded p-2 bg-light d-flex align-items-center justify-content-center" style="min-height: 200px;">
                                <span class="text-muted">Tidak ada foto KTP</span>
                            </div>
                        </div>
                        <div class="col-md-6 text-center">
                            <h6 class="fw-bold">Foto SIM</h6>
                            <div id="simContainer" class="border rounded p-2 bg-light d-flex align-items-center justify-content-center" style="min-height: 200px;">
                                <span class="text-muted">Tidak ada foto SIM</span>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-0">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
                </div>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            var dokumenModal = document.getElementById('dokumenModal')
            if (dokumenModal) {
                dokumenModal.addEventListener('show.bs.modal', function (event) {
                    var button = event.relatedTarget
                    var nama = button.getAttribute('data-nama')
                    var ktp = button.getAttribute('data-ktp')
                    var sim = button.getAttribute('data-sim')
                    
                    document.getElementById('modalDriverName').textContent = nama
                    
                    var ktpContainer = document.getElementById('ktpContainer')
                    if (ktp) {
                        ktpContainer.innerHTML = '<a href="' + ktp + '" target="_blank"><img src="' + ktp + '" class="img-fluid rounded" alt="Foto KTP"></a>'
                    } else {
                        ktpContainer.innerHTML = '<span class="text-muted">Tidak ada foto KTP</span>'
                    }
                    
                    var simContainer = document.getElementById('simContainer')
                    if (sim) {
                        simContainer.innerHTML = '<a href="' + sim + '" target="_blank"><img src="' + sim + '" class="img-fluid rounded" alt="Foto SIM"></a>'
                    } else {
                        simContainer.innerHTML = '<span class="text-muted">Tidak ada foto SIM</span>'
                    }
                })
            }
        })
    </script>
@endsection