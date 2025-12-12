@extends('admin.layouts.app')

@section('title', 'Dashboard - Kelola Driver')

@section('content')
    <div class="container-fluid p-0">

        <div class="card shadow-sm">
            <div class="card-header">
                <div class="row g-3 align-items-center justify-content-between">
                    {{-- Judul --}}
                    <div class="col-12 col-md-4">
                        <h2 class="h5 mb-0"><i class="bi bi-person-badge me-2"></i>Daftar Driver</h2>
                    </div>

                    {{-- Search Form & Tombol Tambah --}}
                    <div class="col-12 col-md-8">
                        <div class="d-flex justify-content-md-end align-items-center gap-2">
                            {{-- Form Pencarian --}}
                            <form action="{{ route('admin.driver.index') }}" method="GET" class="d-flex">
                                <div class="input-group">
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
                @if(request('search'))
                    <div class="alert alert-info py-2 mb-3 small">
                        <i class="bi bi-info-circle me-1"></i> Menampilkan hasil pencarian untuk:
                        <strong>"{{ request('search') }}"</strong>
                        <a href="{{ route('admin.driver.index') }}" class="text-decoration-none ms-2 fw-bold">Reset</a>
                    </div>
                @endif

                <div class="table-responsive">
                    <table class="table table-hover table-striped table-sm align-middle">
                        <thead class="table-light">
                            <tr>
                                <th style="width: 5%;">No.</th>
                                <th style="width: 15%;">ID (NIK)</th>
                                <th style="width: 30%;">Nama Lengkap</th>
                                <th style="width: 25%;">Status SIM</th>
                                <th style="width: 15%;">Tgl Dibuat</th>
                                @can('is-master-admin')
                                    <th class="text-center" style="width: 10%;">Aksi</th>
                                @endcan
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($drivers as $driver)
                                <tr>
                                    {{-- Logic Nomor Urut mengikuti Pagination --}}
                                    <td>{{ $drivers->firstItem() + $loop->index }}</td>
                                    <td><span class="badge bg-secondary font-monospace">{{ $driver->driver_id_nik }}</span></td>
                                    <td class="fw-bold">{{ $driver->full_name }}</td>
                                    <td>
                                        @if($driver->sim_expiry_date)
                                            @php
                                                $expiry = \Carbon\Carbon::parse($driver->sim_expiry_date)->startOfDay();
                                                $today = \Carbon\Carbon::now()->startOfDay();
                                                $diff = $today->diffInDays($expiry, false);
                                            @endphp
                                            @if($diff < 0)
                                                <span class="badge bg-danger">
                                                    <i class="bi bi-exclamation-octagon-fill me-1"></i> MATI
                                                    ({{ $expiry->format('d/m/y') }})
                                                </span>
                                            @elseif($diff <= 30)
                                                <span class="badge bg-warning text-dark">
                                                    <i class="bi bi-exclamation-triangle-fill me-1"></i> Expire {{ $diff }} Hari
                                                </span>
                                            @else
                                                <span class="badge bg-success">
                                                    <i class="bi bi-check-circle-fill me-1"></i> Aman ({{ $expiry->format('d/m/y') }})
                                                </span>
                                            @endif
                                        @else
                                            <span class="badge bg-secondary opacity-50">- Belum Set -</span>
                                        @endif
                                    </td>
                                    <td class="small text-muted">{{ $driver->created_at->format('d M Y') }}</td>

                                    @can('is-master-admin')
                                        <td class="text-center">
                                            <div class="d-flex justify-content-center gap-1">
                                                {{-- Tombol Edit (DIPISAH dari Form) --}}
                                                <a href="{{ route('admin.driver.edit', $driver->id) }}"
                                                    class="btn btn-warning btn-sm" title="Edit Data">
                                                    <i class="bi bi-pencil-fill"></i>
                                                </a>

                                                {{-- Tombol Hapus --}}
                                                <form action="{{ route('admin.driver.destroy', $driver->id) }}" method="POST"
                                                    class="form-delete-global"
                                                    data-message="Apakah Anda yakin ingin menghapus driver <strong>{{ $driver->full_name }}</strong>?">
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

                {{-- Pagination dengan Query String (Agar search tidak hilang saat pindah hal) --}}
                <div class="mt-3">
                    {{ $drivers->withQueryString()->links() }}
                </div>
            </div>
        </div>
    </div>
@endsection