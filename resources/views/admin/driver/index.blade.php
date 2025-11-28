@extends('admin.layouts.app')

@section('title', 'Dashboard - Kelola Driver')

@section('content')
    <div class="container-fluid p-0">

        <div class="card shadow-sm">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h2 class="h5 mb-0"><i class="bi bi-person-badge"></i> Daftar Driver</h2>

                <div>
                    <span class="badge bg-primary rounded-pill me-2">
                        {{ $drivers->total() }} Total Driver
                    </span>

                    @can('is-master-admin')
                        <a href="{{ route('admin.driver.create') }}" class="btn btn-primary btn-sm">
                            <i class="bi bi-plus-circle-fill"></i> Tambah Driver Baru
                        </a>
                    @endcan
                </div>
            </div>

            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover table-striped table-sm align-middle">
                        <thead class="table-light">
                            <tr>
                                <th style="width: 1%;">No.</th>
                                <th>ID (NIK)</th>
                                <th>Nama Lengkap</th>
                                <th>Status SIM</th>
                                <th>Tgl Dibuat</th>
                                @can('is-master-admin')
                                    <th class="text-center">Aksi</th>
                                @endcan
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($drivers as $driver)
                                <tr>
                                    <td>{{ $drivers->firstItem() + $loop->index }}</td>
                                    <td><span class="badge bg-secondary">{{ $driver->driver_id_nik }}</span></td>
                                    <td>{{ $driver->full_name }}</td>
                                    <td>
                                        @if($driver->sim_expiry_date)
                                            @php
                                                $expiry = \Carbon\Carbon::parse($driver->sim_expiry_date)->startOfDay();
                                                $today = \Carbon\Carbon::now()->startOfDay();
                                                $diff = $today->diffInDays($expiry, false);
                                            @endphp
                                            @if($diff < 0)
                                                <span class="badge bg-danger"><i class="bi bi-exclamation-octagon-fill"></i> MATI
                                                    ({{ $expiry->format('d-m-Y') }})</span>
                                            @elseif($diff <= 30)
                                                <span class="badge bg-warning text-dark"><i class="bi bi-exclamation-triangle-fill"></i>
                                                    Expire {{ $diff }} Hari Lagi</span>
                                            @else
                                                <span class="badge bg-success"><i class="bi bi-check-circle-fill"></i> Aman
                                                    ({{ $expiry->format('d-m-Y') }})</span>
                                            @endif
                                        @else
                                            <span class="badge bg-secondary">Belum Diisi</span>
                                        @endif
                                    </td>
                                    <td>{{ $driver->created_at->format('Y-m-d H:i') }}</td>

                                    @can('is-master-admin')
                                        <td class="text-center">
                                            {{-- TOMBOL HAPUS VERSI PRO (Pakai Class Global) --}}
                                            <form action="{{ route('admin.driver.destroy', $driver->id) }}" method="POST"
                                                class="d-inline form-delete-global"
                                                data-message="Apakah Anda yakin ingin menghapus driver {{ $driver->full_name }}?">

                                                @csrf
                                                @method('DELETE')

                                                <div class="d-inline-flex flex-nowrap" style="gap: 5px;">
                                                    <a href="{{ route('admin.driver.edit', $driver->id) }}"
                                                        class="btn btn-warning btn-sm">
                                                        <i class="bi bi-pencil-fill"></i> Edit
                                                    </a>
                                                    <button type="submit" class="btn btn-danger btn-sm">
                                                        <i class="bi bi-trash-fill"></i> Hapus
                                                    </button>
                                                </div>
                                            </form>
                                        </td>
                                    @endcan
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="@can('is-master-admin') 6 @else 5 @endcan" class="text-center">
                                        Belum ada data driver.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="mt-3">
                    {{ $drivers->links() }}
                </div>
            </div>
        </div>
    </div>
@endsection