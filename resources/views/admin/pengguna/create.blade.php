@extends('admin.layouts.app')

@section('title', 'Dashboard - Tambah Pengguna')

@section('content')
    <div class="container-fluid p-0">

        <div class="row justify-content-center">
            <div class="col-lg-8">
                <div class="card shadow-sm">
                    <div class="card-header">
                        <h2 class="h5 mb-0"><i class="bi bi-plus-circle-fill"></i> Tambah Pengguna Baru</h2>
                    </div>
                    <form action="{{ route('admin.pengguna.store') }}" method="POST">
                        @csrf
                        <div class="card-body">
                            <div class="row g-3">
                                <div class="col-md-6 col-12">
                                    <label for="name" class="form-label">Nama Lengkap</label>
                                    <input type="text" class="form-control @error('name') is-invalid @enderror" id="name"
                                        name="name" value="{{ old('name') }}" required>
                                    @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                                <div class="col-md-6 col-12">
                                    <label for="email" class="form-label">Email</label>
                                    <input type="email" class="form-control @error('email') is-invalid @enderror" id="email"
                                        name="email" value="{{ old('email') }}" required>
                                    @error('email') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                                <div class="col-md-6 col-12">
                                    <label for="password" class_label">Password</label>
                                    <input type="password" class="form-control @error('password') is-invalid @enderror"
                                        id="password" name="password" required>
                                    @error('password') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                                <div class="col-md-6 col-12">
                                    <label for="password_confirmation" class="form-label">Konfirmasi Password</label>
                                    <input type="password" class="form-control" id="password_confirmation"
                                        name="password_confirmation" required>
                                </div>
                            </div>
                        </div>
                        <div class="card-footer text-end">
                            <a href="{{ route('admin.pengguna.index') }}" class="btn btn-secondary"><i
                                    class="bi bi-x-circle"></i> Batal</a>
                            <button type="submit" class="btn btn-primary"><i class="bi bi-save-fill"></i> Simpan
                                Pengguna</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection