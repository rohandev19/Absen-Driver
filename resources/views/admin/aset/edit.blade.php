@extends('admin.layouts.app')

@section('title', 'Edit Aset - ' . $vehicle->plate_number)

@section('content')
    <div class="container-fluid p-0">

        <div class="row justify-content-center">
            <div class="col-lg-8">
                <div class="card shadow-sm">
                    <div class="card-header">
                        <h2 class="h5 mb-0">
                            <i class="bi bi-pencil-fill"></i> Edit Data Aset:
                            <span class="badge bg-secondary fs-6">{{ $vehicle->plate_number }}</span>
                        </h2>
                    </div>

                    <form action="{{ route('admin.aset.update', $vehicle->id) }}" method="POST">
                        @csrf
                        @method('PUT')
                        <div class="card-body">
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label">Plat Nomor</label>
                                    <input type="text" class="form-control" value="{{ $vehicle->plate_number }}" disabled>
                                    <div class="form-text">Plat nomor tidak dapat diubah.</div>
                                </div>
                                <div class="col-md-6">
                                    <label for="type" class="form-label">Jenis Mobil</label>
                                    <input type="text" class="form-control @error('type') is-invalid @enderror" id="type"
                                        name="type" value="{{ old('type', $vehicle->type) }}" required>
                                    @error('type')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="col-md-12">
                                    <label for="project_id" class="form-label fw-bold">Project / Lokasi Unit</label>
                                    <select name="project_id" id="project_id" class="form-select">
                                        <option value="">-- Unit Pool / Umum --</option>
                                        @foreach($projects as $project)
                                            <option value="{{ $project->id }}" {{ $vehicle->project_id == $project->id ? 'selected' : '' }}>
                                                {{ $project->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>

                                <hr class="my-3">
                                <h5 class="mb-0">Data Monitoring Servis (KM)</h5>

                                <div class="col-md-6">
                                    <label for="service_interval_km" class="form-label">Interval Servis (per KM)</label>
                                    <input type="number"
                                        class="form-control @error('service_interval_km') is-invalid @enderror"
                                        id="service_interval_km" name="service_interval_km"
                                        value="{{ old('service_interval_km', $vehicle->service_interval_km) }}" required>
                                    <div class="form-text">Contoh: 10000 (untuk setiap 10.000 KM)</div>
                                    @error('service_interval_km')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="col-md-6">
                                    <label for="last_service_km" class="form-label">KM Servis Terakhir</label>
                                    <input type="number" class="form-control @error('last_service_km') is-invalid @enderror"
                                        id="last_service_km" name="last_service_km"
                                        value="{{ old('last_service_km', $vehicle->last_service_km) }}" required>
                                    <div class="form-text">Contoh: 50000</div>
                                    @error('last_service_km')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <hr class="my-3">
                                <h5 class="mb-0">Data Monitoring Tanggal</h5>

                                <div class="col-md-6">
                                    <label for="pajak_stnk_berlaku_sampai" class="form-label">STNK Berlaku
                                        Sampai</label>
                                    <input type="date"
                                        class="form-control @error('pajak_stnk_berlaku_sampai') is-invalid @enderror"
                                        id="pajak_stnk_berlaku_sampai" name="pajak_stnk_berlaku_sampai"
                                        value="{{ old('pajak_stnk_berlaku_sampai', $vehicle->pajak_stnk_berlaku_sampai ? \Carbon\Carbon::parse($vehicle->pajak_stnk_berlaku_sampai)->format('Y-m-d') : '') }}">
                                    @error('pajak_stnk_berlaku_sampai')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="col-md-6">
                                    <label for="kir_berlaku_sampai" class="form-label">KIR Berlaku Sampai</label>
                                    <input type="date"
                                        class="form-control @error('kir_berlaku_sampai') is-invalid @enderror"
                                        id="kir_berlaku_sampai" name="kir_berlaku_sampai"
                                        value="{{ old('kir_berlaku_sampai', $vehicle->kir_berlaku_sampai ? \Carbon\Carbon::parse($vehicle->kir_berlaku_sampai)->format('Y-m-d') : '') }}">
                                    @error('kir_berlaku_sampai')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>
                        <div class="card-footer text-end">
                            <a href="{{ route('admin.daftar_aset') }}" class="btn btn-secondary">
                                <i class="bi bi-x-circle"></i> Batal
                            </a>
                            <button type="submit" class="btn btn-success">
                                <i class="bi bi-save-fill"></i> Simpan Perubahan
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection