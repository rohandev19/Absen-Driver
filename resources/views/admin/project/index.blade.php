@extends('admin.layouts.app')

@section('title', 'Kelola Project / Kategori')

@section('content')
    <div class="container-fluid p-0">
        <div class="row">
            {{-- FORM TAMBAH (KIRI) --}}
            <div class="col-md-4">
                <div class="card shadow-sm border-0 mb-4">
                    <div class="card-header bg-white py-3">
                        <h5 class="mb-0 fw-bold text-primary"><i class="bi bi-plus-circle me-2"></i>Tambah Project</h5>
                    </div>
                    <div class="card-body">
                        <form action="{{ route('admin.project.store') }}" method="POST">
                            @csrf
                            <div class="mb-3">
                                <label class="form-label fw-bold">Nama Project / Kategori</label>
                                <input type="text" name="name" class="form-control" placeholder="Contoh: J&T Express"
                                    required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label fw-bold">Kode Singkatan (Opsional)</label>
                                <input type="text" name="code" class="form-control" placeholder="Contoh: JNT">
                            </div>
                            <div class="mb-3">
                                <label class="form-label fw-bold">Customer</label>
                                <select name="customer_id" class="form-select">
                                    <option value="">-- Pilih Customer --</option>
                                    @foreach($customers as $customer)
                                        <option value="{{ $customer->id }}">{{ $customer->name }}</option>
                                    @endforeach
                                </select>
                                <small class="text-muted">Untuk notifikasi WhatsApp approval service</small>
                            </div>
                            <button type="submit" class="btn btn-primary w-100">Simpan</button>
                        </form>
                    </div>
                </div>
            </div>

            {{-- TABEL DATA (KANAN) --}}
            <div class="col-md-8">
                <div class="card shadow-sm border-0">
                    <div class="card-header bg-white py-3">
                        <h5 class="mb-0 fw-bold"><i class="bi bi-tags me-2"></i>Daftar Project</h5>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0">
                                <thead class="bg-light">
                                    <tr>
                                        <th class="ps-4">Nama Project</th>
                                        <th>Kode</th>
                                        <th>Customer</th>
                                        <th class="text-center">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($projects as $project)
                                        <tr>
                                            <td class="ps-4 fw-bold">{{ $project->name }}</td>
                                            <td><span class="badge bg-secondary">{{ $project->code ?? '-' }}</span></td>
                                            <td>
                                                @if($project->customer)
                                                    <span class="badge bg-success">{{ $project->customer->name }}</span>
                                                @else
                                                    <span class="text-muted">-</span>
                                                @endif
                                            </td>
                                            <td class="text-center">
                                                {{-- Tombol Edit --}}
                                                <button class="btn btn-warning btn-sm" data-bs-toggle="modal" 
                                                    data-bs-target="#editModal{{ $project->id }}" title="Edit Project">
                                                    <i class="bi bi-pencil"></i>
                                                </button>

                                                {{-- LOGIKA PROTEKSI TOMBOL HAPUS --}}
                                                @can('is-master-admin')
                                                    {{-- Jika Master Admin: Tampilkan Tombol Hapus Merah --}}
                                                    <form action="{{ route('admin.project.destroy', $project->id) }}" method="POST"
                                                        class="d-inline" onsubmit="return confirm('Yakin hapus project ini?')">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button class="btn btn-danger btn-sm" title="Hapus Project">
                                                            <i class="bi bi-trash"></i>
                                                        </button>
                                                    </form>
                                                @else
                                                    {{-- Jika Admin Biasa: Tampilkan Gembok (Disabled) --}}
                                                    <button class="btn btn-secondary btn-sm" disabled title="Hanya Master Admin">
                                                        <i class="bi bi-lock-fill"></i>
                                                    </button>
                                                @endcan

                                            </td>
                                        </tr>

                                        {{-- Modal Edit --}}
                                        <div class="modal fade" id="editModal{{ $project->id }}" tabindex="-1">
                                            <div class="modal-dialog">
                                                <div class="modal-content">
                                                    <div class="modal-header">
                                                        <h5 class="modal-title">Edit Project</h5>
                                                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                    </div>
                                                    <form action="{{ route('admin.project.update', $project->id) }}" method="POST">
                                                        @csrf
                                                        @method('PUT')
                                                        <div class="modal-body">
                                                            <div class="mb-3">
                                                                <label class="form-label fw-bold">Nama Project</label>
                                                                <input type="text" name="name" class="form-control" 
                                                                    value="{{ $project->name }}" required>
                                                            </div>
                                                            <div class="mb-3">
                                                                <label class="form-label fw-bold">Kode</label>
                                                                <input type="text" name="code" class="form-control" 
                                                                    value="{{ $project->code }}">
                                                            </div>
                                                            <div class="mb-3">
                                                                <label class="form-label fw-bold">Customer</label>
                                                                <select name="customer_id" class="form-select">
                                                                    <option value="">-- Pilih Customer --</option>
                                                                    @foreach($customers as $customer)
                                                                        <option value="{{ $customer->id }}" 
                                                                            {{ $project->customer_id == $customer->id ? 'selected' : '' }}>
                                                                            {{ $customer->name }}
                                                                        </option>
                                                                    @endforeach
                                                                </select>
                                                            </div>
                                                        </div>
                                                        <div class="modal-footer">
                                                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                                                            <button type="submit" class="btn btn-primary">Update</button>
                                                        </div>
                                                    </form>
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection