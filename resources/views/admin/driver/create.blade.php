@extends('admin.layouts.app')

@section('title', 'Dashboard - Tambah Driver')

@section('content')
    <div class="container-fluid p-0">

        <div class="row justify-content-center">
            <div class="col-lg-8">
                <div class="card shadow-sm border-0">
                    <div class="card-header bg-white py-3">
                        <h5 class="mb-0 fw-bold text-primary"><i class="bi bi-plus-circle-fill me-2"></i> Tambah Driver Baru
                        </h5>
                    </div>
                    <form action="{{ route('admin.driver.store') }}" method="POST">
                        @csrf
                        <div class="card-body">
                            <div class="alert alert-light border-start border-4 border-info small mb-4">
                                <strong>Catatan:</strong> Pastikan NIK dan data diri sesuai dengan KTP/SIM Driver.
                            </div>

                            <div class="row g-3">
                                <div class="col-md-6 col-12">
                                    <label for="full_name" class="form-label fw-bold">Nama Lengkap</label>
                                    <input type="text" class="form-control @error('full_name') is-invalid @enderror"
                                        id="full_name" name="full_name" value="{{ old('full_name') }}"
                                        placeholder="Contoh: Budi Santoso" required>
                                    @error('full_name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                                <div class="col-md-6 col-12">
                                    <label for="driver_id_nik" class="form-label fw-bold">ID Driver (NIK)</label>
                                    <input type="number" class="form-control @error('driver_id_nik') is-invalid @enderror"
                                        id="driver_id_nik" name="driver_id_nik" value="{{ old('driver_id_nik') }}"
                                        placeholder="16 digit NIK" required>
                                    @error('driver_id_nik') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                                <div class="col-md-6 col-12">
                                    <label for="sim_expiry_date" class="form-label fw-bold">Masa Berlaku SIM</label>
                                    <input type="date" class="form-control @error('sim_expiry_date') is-invalid @enderror"
                                        id="sim_expiry_date" name="sim_expiry_date" value="{{ old('sim_expiry_date') }}"
                                        required>
                                    @error('sim_expiry_date') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>

                                <div class="col-12">
                                    <hr class="text-muted">
                                </div>

                                <div class="col-md-6 col-12">
                                    <label for="password" class="form-label fw-bold">Password Akun</label>
                                    <input type="password" class="form-control @error('password') is-invalid @enderror"
                                        id="password" name="password" autocomplete="new-password"
                                        placeholder="Minimal 6 karakter" required>
                                    @error('password') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                                <div class="col-md-6 col-12">
                                    <label for="password_confirmation" class="form-label fw-bold">Konfirmasi
                                        Password</label>
                                    <input type="password" class="form-control" id="password_confirmation"
                                        name="password_confirmation" autocomplete="new-password"
                                        placeholder="Ulangi password" required>
                                </div>
                            </div>
                        </div>
                        <div class="card-footer bg-light text-end py-3">
                            <a href="{{ route('admin.driver.index') }}" class="btn btn-outline-secondary me-2">
                                <i class="bi bi-arrow-left"></i> Batal
                            </a>
                            <button type="submit" class="btn btn-primary px-4">
                                <i class="bi bi-save-fill me-1"></i> Simpan Driver
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection