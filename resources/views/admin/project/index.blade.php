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
                        <table class="table table-hover align-middle mb-0">
                            <thead class="bg-light">
                                <tr>
                                    <th class="ps-4">Nama Project</th>
                                    <th>Kode</th>
                                    <th class="text-center">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($projects as $project)
                                    <tr>
                                        <td class="ps-4 fw-bold">{{ $project->name }}</td>
                                        <td><span class="badge bg-secondary">{{ $project->code ?? '-' }}</span></td>
                                        <td class="text-center">

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
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection