@extends('admin.layouts.app')

@section('title', 'Dashboard - Kelola Pengguna')

@section('content')
    <div class="container-fluid p-0">

        @if (session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif
        @if (session('error'))
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                {{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        <div class="card shadow-sm">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h2 class="h5 mb-0"><i class="bi bi-people-fill"></i> Daftar Pengguna Admin</h2>

                {{-- HANYA MASTER ADMIN --}}
                @can('is-master-admin')
                    <a href="{{ route('admin.pengguna.create') }}" class="btn btn-primary btn-sm">
                        <i class="bi bi-plus-circle-fill"></i> Tambah Pengguna Baru
                    </a>
                @endcan
            </div>
            <div class="card-body">

                <div class="table-responsive">
                    <table class="table table-hover table-striped table-sm align-middle">
                        <thead class="table-light">
                            <tr>
                                <th>ID</th>
                                <th>Nama</th>
                                <th>Email</th>
                                <th>Role</th> {{-- Tambahan Info Role --}}
                                <th>Tgl Dibuat</th>

                                {{-- HANYA MASTER ADMIN --}}
                                @can('is-master-admin')
                                    <th class="text-center">Aksi</th>
                                @endcan
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($users as $user)
                                <tr>
                                    <td>{{ $user->id }}</td>
                                    <td>{{ $user->name }}</td>
                                    <td>{{ $user->email }}</td>
                                    <td>
                                        @if($user->role === 'master')
                                            <span class="badge bg-danger">Master</span>
                                        @else
                                            <span class="badge bg-info text-dark">Admin/Viewer</span>
                                        @endif
                                    </td>
                                    <td>{{ $user->created_at->format('Y-m-d H:i') }}</td>

                                    {{-- HANYA MASTER ADMIN --}}
                                    @can('is-master-admin')
                                        <td class="text-center">
                                            <div class="d-inline-flex flex-nowrap" style="gap: 5px;">
                                                <a href="{{ route('admin.pengguna.edit', $user->id) }}"
                                                    class="btn btn-warning btn-sm">
                                                    <i class="bi bi-pencil-fill"></i> Edit
                                                </a>

                                                <form action="{{ route('admin.pengguna.destroy', $user->id) }}" method="POST"
                                                    class="d-inline"
                                                    onsubmit="return confirm('Anda yakin ingin menghapus pengguna ini?');">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-danger btn-sm" {{ $user->id === Auth::id() ? 'disabled' : '' }}
                                                        title="{{ $user->id === Auth::id() ? 'Tidak dapat menghapus diri sendiri' : '' }}">
                                                        <i class="bi bi-trash-fill"></i> Hapus
                                                    </button>
                                                </form>
                                            </div>
                                        </td>
                                    @endcan
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="@can('is-master-admin') 6 @else 5 @endcan" class="text-center">Belum ada data
                                        pengguna admin.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
@endsection