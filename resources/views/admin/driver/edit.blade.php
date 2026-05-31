@extends('admin.layouts.app')

@section('title', 'Dashboard - Edit Driver')

@section('content')
    <div class="container-fluid p-0">

        <div class="row justify-content-center">
            <div class="col-lg-8">
                <div class="card shadow-sm border-0">
                    <div class="card-header bg-white py-3">
                        <h5 class="mb-0 fw-bold text-warning"><i class="bi bi-pencil-square me-2"></i> Edit Data Driver</h5>
                    </div>
                    <form action="{{ route('admin.driver.update', $driver->id) }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        @method('PUT')
                        <div class="card-body">
                            <div class="row g-3">
                                {{-- Nama Lengkap --}}
                                <div class="col-md-6 col-12">
                                    <label for="full_name" class="form-label fw-bold">Nama Lengkap</label>
                                    <input type="text" class="form-control @error('full_name') is-invalid @enderror"
                                        id="full_name" name="full_name" value="{{ old('full_name', $driver->full_name) }}"
                                        required>
                                    @error('full_name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>

                                {{-- Project / Divisi (BARU) --}}
                                <div class="col-md-6 col-12">
                                    <label class="form-label fw-bold">Penempatan Project / Divisi</label>
                                    <select name="project_id" class="form-select">
                                        <option value="">-- Tidak Ada / Pool --</option>
                                        @foreach($projects as $project)
                                            <option value="{{ $project->id }}" {{ $driver->project_id == $project->id ? 'selected' : '' }}>
                                                {{ $project->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>

                                {{-- NIK --}}
                                <div class="col-md-6 col-12">
                                    <label for="driver_id_nik" class="form-label fw-bold">ID Driver (NIK)</label>
                                    <input type="text" class="form-control @error('driver_id_nik') is-invalid @enderror"
                                        id="driver_id_nik" name="driver_id_nik"
                                        value="{{ old('driver_id_nik', $driver->driver_id_nik) }}" required>
                                    @error('driver_id_nik') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                                <div class="col-md-6 col-12">
                                    <label for="nik_ktp" class="form-label fw-bold">NIK (KTP)</label>
                                    <input type="text" class="form-control" name="nik_ktp"
                                        value="{{ old('nik_ktp', $driver->nik_ktp) }}">
                                </div>

                                {{-- Jenis SIM (BARU) --}}
                                <div class="col-md-6 col-12">
                                    <label for="sim_type" class="form-label fw-bold">Jenis SIM</label>
                                    <select name="sim_type" id="sim_type" class="form-select" required>
                                        <option value="">-- Pilih Jenis SIM --</option>
                                        @foreach(['SIM A', 'SIM A Umum', 'SIM B1', 'SIM B1 Umum', 'SIM B2', 'SIM B2 Umum'] as $tipe)
                                            <option value="{{ $tipe }}" {{ old('sim_type', $driver->sim_type) == $tipe ? 'selected' : '' }}>
                                                {{ $tipe }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>

                                {{-- Masa Berlaku --}}
                                <div class="col-md-6 col-12">
                                    <label for="sim_expiry_date" class="form-label fw-bold">Masa Berlaku SIM</label>
                                    <input type="date" class="form-control @error('sim_expiry_date') is-invalid @enderror"
                                        id="sim_expiry_date" name="sim_expiry_date"
                                        value="{{ old('sim_expiry_date', $driver->sim_expiry_date ? \Carbon\Carbon::parse($driver->sim_expiry_date)->format('Y-m-d') : '') }}"
                                        required>
                                    @error('sim_expiry_date') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>

                                <div class="col-12">
                                    <hr class="text-muted">
                                </div>

                                {{-- Upload Dokumen (Opsional) --}}
                                <div class="col-md-6 col-12">
                                    <label for="foto_ktp" class="form-label fw-bold">Foto KTP <span class="text-muted fw-normal">(Opsional)</span></label>
                                    @if($driver->foto_ktp)
                                        <div class="mb-2">
                                            <a href="{{ route('admin.driver.dokumen', ['id' => $driver->id, 'jenis' => 'ktp']) }}" target="_blank">
                                                <img src="{{ route('admin.driver.dokumen', ['id' => $driver->id, 'jenis' => 'ktp']) }}" alt="Foto KTP" class="img-thumbnail" style="max-height: 100px;">
                                            </a>
                                        </div>
                                    @endif
                                    <input type="file" class="form-control @error('foto_ktp') is-invalid @enderror"
                                        id="foto_ktp" name="foto_ktp" accept="image/jpeg,image/png,image/jpg">
                                    @error('foto_ktp') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                    <small class="text-muted">Upload ulang untuk mengganti. Max: 2MB.</small>
                                </div>

                                <div class="col-md-6 col-12">
                                    <label for="foto_sim" class="form-label fw-bold">Foto SIM <span class="text-muted fw-normal">(Opsional)</span></label>
                                    @if($driver->foto_sim)
                                        <div class="mb-2">
                                            <a href="{{ route('admin.driver.dokumen', ['id' => $driver->id, 'jenis' => 'sim']) }}" target="_blank">
                                                <img src="{{ route('admin.driver.dokumen', ['id' => $driver->id, 'jenis' => 'sim']) }}" alt="Foto SIM" class="img-thumbnail" style="max-height: 100px;">
                                            </a>
                                        </div>
                                    @endif
                                    <input type="file" class="form-control @error('foto_sim') is-invalid @enderror"
                                        id="foto_sim" name="foto_sim" accept="image/jpeg,image/png,image/jpg">
                                    @error('foto_sim') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                    <small class="text-muted">Upload ulang untuk mengganti. Max: 2MB.</small>
                                </div>

                                <div class="col-12">
                                    <hr class="my-3 text-muted">
                                </div>

                                {{-- Reset Password --}}
                                <h6 class="fw-bold mb-2 text-dark"><i class="bi bi-key me-1"></i> Reset Password (Opsional)
                                </h6>
                                <div class="col-md-6 col-12">
                                    <label for="password" class="form-label">Password Baru</label>
                                    <input type="password" class="form-control @error('password') is-invalid @enderror"
                                        id="password" name="password" autocomplete="new-password"
                                        placeholder="Biarkan kosong jika tidak diganti">
                                    @error('password') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                                <div class="col-md-6 col-12">
                                    <label for="password_confirmation" class="form-label">Konfirmasi Password Baru</label>
                                    <input type="password" class="form-control" id="password_confirmation"
                                        name="password_confirmation" autocomplete="new-password"
                                        placeholder="Ulangi password baru">
                                </div>
                            </div>
                        </div>
                        <div class="card-footer bg-light text-end py-3">
                            <a href="{{ route('admin.driver.index') }}" class="btn btn-outline-secondary me-2">
                                <i class="bi bi-x-lg"></i> Batal
                            </a>
                            <button type="submit" class="btn btn-success px-4">
                                <i class="bi bi-check-lg me-1"></i> Simpan Perubahan
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection