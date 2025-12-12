@extends('admin.layouts.app')

@section('title', 'Dashboard - Kelola Pengguna')

@section('content')
    <div class="container-fluid p-0">

        <div class="card shadow-sm border-0">
            <div class="card-header bg-white py-3">
                <div class="row g-3 align-items-center justify-content-between">
                    {{-- Judul --}}
                    <div class="col-12 col-md-4">
                        <h5 class="mb-0 fw-bold text-primary"><i class="bi bi-people-fill me-2"></i> Daftar Pengguna Admin
                        </h5>
                    </div>

                    {{-- Search & Tombol Tambah --}}
                    <div class="col-12 col-md-8">
                        <div class="d-flex justify-content-md-end align-items-center gap-2">
                            {{-- Form Pencarian --}}
                            <form action="{{ route('admin.pengguna.index') }}" method="GET" class="d-flex">
                                <div class="input-group">
                                    <input type="text" name="search" class="form-control form-control-sm"
                                        placeholder="Cari Nama / Email..." value="{{ request('search') }}">
                                    <button class="btn btn-outline-secondary btn-sm" type="submit">
                                        <i class="bi bi-search"></i>
                                    </button>
                                </div>
                            </form>

                            {{-- Tombol Tambah --}}
                            @can('is-master-admin')
                                <a href="{{ route('admin.pengguna.create') }}" class="btn btn-primary btn-sm text-nowrap">
                                    <i class="bi bi-plus-circle-fill me-1"></i> Tambah Baru
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
                        <i class="bi bi-info-circle me-1"></i> Hasil pencarian: <strong>"{{ request('search') }}"</strong>
                        <a href="{{ route('admin.pengguna.index') }}" class="fw-bold ms-2 text-decoration-none">Reset</a>
                    </div>
                @endif

                <div class="table-responsive">
                    <table class="table table-hover table-striped table-sm align-middle">
                        <thead class="table-light">
                            <tr>
                                <th style="width: 5%;">No</th>
                                <th style="width: 25%;">Nama Lengkap</th>
                                <th style="width: 25%;">Email</th>
                                <th style="width: 15%;">Role</th>
                                <th style="width: 15%;">Tgl Dibuat</th>
                                @can('is-master-admin')
                                    <th class="text-center" style="width: 15%;">Aksi</th>
                                @endcan
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($users as $user)
                                <tr>
                                    <td>{{ $loop->iteration + ($users->currentPage() - 1) * $users->perPage() }}</td>
                                    <td class="fw-bold">{{ $user->name }}</td>
                                    <td>{{ $user->email }}</td>
                                    <td>
                                        @if($user->role === 'master')
                                            <span class="badge bg-danger"><i class="bi bi-shield-lock-fill me-1"></i> Master</span>
                                        @else
                                            <span class="badge bg-info text-dark"><i class="bi bi-person-badge me-1"></i>
                                                Admin</span>
                                        @endif
                                    </td>
                                    <td class="small text-muted">{{ $user->created_at->format('d M Y') }}</td>

                                    @can('is-master-admin')
                                        <td class="text-center">
                                            <div class="d-flex justify-content-center gap-1">
                                                {{-- Tombol Edit --}}
                                                <a href="{{ route('admin.pengguna.edit', $user->id) }}"
                                                    class="btn btn-warning btn-sm" title="Edit Pengguna">
                                                    <i class="bi bi-pencil-fill"></i>
                                                </a>

                                                {{-- Tombol Hapus --}}
                                                @if($user->id !== Auth::id())
                                                    <form action="{{ route('admin.pengguna.destroy', $user->id) }}" method="POST"
                                                        class="form-delete-global"
                                                        data-message="Hapus pengguna <strong>{{ $user->name }}</strong>? Data tidak bisa dikembalikan.">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="btn btn-danger btn-sm" title="Hapus Pengguna">
                                                            <i class="bi bi-trash-fill"></i>
                                                        </button>
                                                    </form>
                                                @else
                                                    <button class="btn btn-secondary btn-sm" disabled
                                                        title="Tidak bisa menghapus diri sendiri">
                                                        <i class="bi bi-trash"></i>
                                                    </button>
                                                @endif
                                            </div>
                                        </td>
                                    @endcan
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="text-center py-4 text-muted">
                                        <i class="bi bi-people display-6 d-block mb-2 opacity-50"></i>
                                        Belum ada data pengguna admin.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                {{-- Pagination --}}
                <div class="mt-3">
                    {{ $users->withQueryString()->links() }}
                </div>
            </div>
        </div>
    </div>
@endsection