@extends('admin.layouts.app')

@section('title', 'Edit Aset - ' . $vehicle->plate_number)

@section('content')
    <div class="container-fluid p-0">

        <div class="row justify-content-center">
            <div class="col-lg-8">
                <div class="card shadow-sm">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h2 class="h5 mb-0">
                            <i class="bi bi-pencil-fill"></i> Edit Data Aset:
                            <span class="badge bg-secondary fs-6">{{ $vehicle->plate_number }}</span>
                        </h2>
                        <a href="javascript:history.back()" class="btn btn-outline-secondary btn-sm rounded-pill shadow-sm">
                            <i class="bi bi-arrow-left"></i> Kembali
                        </a>
                    </div>

                    <form action="{{ route('admin.aset.update', $vehicle->id) }}" method="POST">
                        @csrf
                        @method('PUT')
                        <div class="card-body">
                            <div class="row g-3">

                                {{-- IDENTITAS KENDARAAN --}}
                                <div class="col-md-6">
                                    <label class="form-label">Plat Nomor</label>
                                    <input type="text" class="form-control" value="{{ $vehicle->plate_number }}" disabled>
                                    <input type="hidden" name="plate_number" value="{{ $vehicle->plate_number }}">
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
                                <div class="col-md-6">
                                    <label for="tahun_pembuatan" class="form-label">Tahun Pembuatan (Opsional)</label>
                                    <input type="number" class="form-control @error('tahun_pembuatan') is-invalid @enderror" id="tahun_pembuatan"
                                        name="tahun_pembuatan" value="{{ old('tahun_pembuatan', $vehicle->tahun_pembuatan) }}" placeholder="Contoh: 2018" min="1900" max="{{ date('Y') }}">
                                    @error('tahun_pembuatan')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                    <div class="form-text">Digunakan untuk akurasi Health Score.</div>
                                </div>
                                <div class="col-md-6">
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
                                <div class="col-md-6">
                                    <label for="status" class="form-label fw-bold">Status Operasional</label>
                                    <select name="status" id="status" class="form-select">
                                        @foreach(['Aktif', 'Pending Verifikasi', 'Servis', 'Rusak', 'Tidak Aktif'] as $status)
                                            <option value="{{ $status }}" {{ old('status', $vehicle->status) === $status ? 'selected' : '' }}>
                                                {{ $status }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label for="verification_status" class="form-label fw-bold">Status Verifikasi</label>
                                    <select name="verification_status" id="verification_status" class="form-select">
                                        <option value="verified" {{ old('verification_status', $vehicle->verification_status) === 'verified' ? 'selected' : '' }}>Terverifikasi</option>
                                        <option value="pending" {{ old('verification_status', $vehicle->verification_status) === 'pending' ? 'selected' : '' }}>Pending Verifikasi</option>
                                        <option value="rejected" {{ old('verification_status', $vehicle->verification_status) === 'rejected' ? 'selected' : '' }}>Ditolak</option>
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-check mt-4">
                                        <input class="form-check-input" type="checkbox" value="1" id="is_temporary" name="is_temporary"
                                            {{ old('is_temporary', $vehicle->is_temporary) ? 'checked' : '' }}>
                                        <label class="form-check-label fw-bold" for="is_temporary">
                                            Unit pengganti / sementara
                                        </label>
                                    </div>
                                </div>
                                <div class="col-12">
                                    <label for="notes" class="form-label fw-bold">Catatan Unit</label>
                                    <textarea name="notes" id="notes" class="form-control" rows="3">{{ old('notes', $vehicle->notes) }}</textarea>
                                </div>

                                <hr class="my-3">
                                <h5 class="mb-0">Data Monitoring Dokumen (Surat-Surat)</h5>

                                {{-- LEGALITAS / SURAT-SURAT --}}
                                <div class="col-md-6">
                                    <label for="pajak_stnk_berlaku_sampai" class="form-label">STNK Berlaku Sampai</label>
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

                <!-- QR CODE CARD -->
                <div class="card shadow-sm mt-4">
                    <div class="card-header">
                        <h5 class="mb-0 fw-bold text-dark"><i class="bi bi-qr-code me-2"></i> QR Code Kendaraan</h5>
                    </div>
                    <div class="card-body text-center">
                        @if($vehicle->qr_code_path)
                            <div class="mb-3">
                                <img src="{{ $vehicle->qr_code_url }}" alt="QR Code" class="img-thumbnail" style="width: 200px; height: 200px;">
                                <div class="mt-2 text-muted small fw-bold">{{ $vehicle->qr_code_identifier }}</div>
                            </div>
                            <div class="d-flex justify-content-center gap-2">
                                <a href="{{ route('admin.vehicles.qrcode.download', $vehicle->id) }}" class="btn btn-outline-primary">
                                    <i class="bi bi-download"></i> Download
                                </a>
                                <a href="{{ route('admin.vehicles.qr-print', $vehicle->id) }}" target="_blank" class="btn btn-outline-secondary">
                                    <i class="bi bi-printer"></i> Print
                                </a>
                                <form action="{{ route('admin.vehicles.qrcode.regenerate', $vehicle->id) }}" method="POST" class="d-inline">
                                    @csrf
                                    <button type="submit" class="btn btn-outline-warning" onclick="return confirm('Yakin ingin generate ulang QR Code? QR Code lama tidak akan berlaku lagi.')">
                                        <i class="bi bi-arrow-clockwise"></i> Regenerate
                                    </button>
                                </form>
                            </div>
                        @else
                            <div class="alert alert-warning">
                                <i class="bi bi-exclamation-triangle me-1"></i> QR Code belum tersedia.
                            </div>
                            <form action="{{ route('admin.vehicles.qrcode.regenerate', $vehicle->id) }}" method="POST">
                                @csrf
                                <button type="submit" class="btn btn-primary">
                                    <i class="bi bi-qr-code"></i> Generate QR Code
                                </button>
                            </form>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
