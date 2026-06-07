@extends('admin.layouts.app')

@section('title', 'Tambah Aset Baru')

@section('content')
<div class="container-fluid p-0">
    <div class="row justify-content-center">
        <div class="col-md-8 col-lg-6">
            <div class="card shadow-sm border-0">
                <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                    <h5 class="mb-0 fw-bold text-dark"><i class="bi bi-plus-circle text-primary me-2"></i> Tambah Aset Mobil</h5>
                    <a href="javascript:history.back()" class="btn btn-outline-secondary btn-sm rounded-pill shadow-sm">
                        <i class="bi bi-arrow-left"></i> Kembali
                    </a>
                </div>
                <div class="card-body p-4">
                    <form action="{{ route('admin.aset.store') }}" method="POST">
                        @csrf
                        
                        {{-- Input Plat Nomor --}}
                       {{-- Input Plat Nomor --}}
                        <div class="mb-3">
                            <label for="plate_number" class="form-label fw-bold">Plat Nomor</label>
                            <input type="text" class="form-control @error('plate_number') is-invalid @enderror" 
                                id="plate_number" name="plate_number" placeholder="Contoh: B 1234 XYZ" 
                                value="{{ old('plate_number') }}" required>
                            @error('plate_number')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <div class="form-text">Gunakan format tanpa spasi jika diperlukan, pastikan unik.</div>
                        </div>

                        {{-- Input Jenis Mobil --}}
                        <div class="mb-3">
                            <label for="type" class="form-label fw-bold">Jenis Mobil / Tipe</label>
                            <input type="text" class="form-control @error('type') is-invalid @enderror" 
                                id="type" name="type" placeholder="Contoh: BLINDVAN / CDE / GRANDMAX" 
                                value="{{ old('type') }}" required>
                            @error('type')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        {{-- TAMBAHAN BARU: Input Tahun Pembuatan --}}
                        <div class="mb-3">
                            <label for="tahun_pembuatan" class="form-label fw-bold">Tahun Pembuatan (Opsional)</label>
                            <input type="number" class="form-control @error('tahun_pembuatan') is-invalid @enderror" 
                                id="tahun_pembuatan" name="tahun_pembuatan" placeholder="Contoh: 2018" 
                                value="{{ old('tahun_pembuatan') }}" min="1900" max="{{ date('Y') }}">
                            @error('tahun_pembuatan')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <div class="form-text">Digunakan untuk akurasi perhitungan Health Score kendaraan.</div>
                        </div>

                        {{-- TAMBAHAN BARU: Input Odometer Awal --}}
                        <div class="mb-3">
                            <label for="current_km" class="form-label fw-bold">Odometer Awal (KM Saat Ini)</label>
                            <div class="input-group">
                                <input type="number" class="form-control @error('current_km') is-invalid @enderror" 
                                    id="current_km" name="current_km" placeholder="Contoh: 50000" 
                                    value="{{ old('current_km', 0) }}" required min="0">
                                <span class="input-group-text">Km</span>
                            </div>
                            <div class="form-text">Masukkan angka di speedometer mobil saat ini. Biarkan 0 jika mobil baru (dealer).</div>
                            @error('current_km')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-4">
                            <label for="project_id" class="form-label fw-bold">Project / Lokasi Unit</label>
                            <select name="project_id" id="project_id" class="form-select">
                                <option value="">-- Unit Pool / Umum --</option>
                                @foreach($projects as $project)
                                    <option value="{{ $project->id }}">{{ $project->name }}</option>
                                @endforeach
                            </select>
                            <div class="form-text">Pilih project jika mobil ini didedikasikan khusus.</div>
                        </div>

                        <div class="mb-3">
                            <label for="status" class="form-label fw-bold">Status Operasional</label>
                            <select name="status" id="status" class="form-select">
                                <option value="Aktif" {{ old('status', 'Aktif') === 'Aktif' ? 'selected' : '' }}>Aktif</option>
                                <option value="Pending Verifikasi" {{ old('status') === 'Pending Verifikasi' ? 'selected' : '' }}>Pending Verifikasi</option>
                                <option value="Servis" {{ old('status') === 'Servis' ? 'selected' : '' }}>Servis</option>
                                <option value="Rusak" {{ old('status') === 'Rusak' ? 'selected' : '' }}>Rusak</option>
                                <option value="Tidak Aktif" {{ old('status') === 'Tidak Aktif' ? 'selected' : '' }}>Tidak Aktif</option>
                            </select>
                        </div>

                        <div class="mb-3 form-check">
                            <input class="form-check-input" type="checkbox" value="1" id="is_temporary" name="is_temporary" {{ old('is_temporary') ? 'checked' : '' }}>
                            <label class="form-check-label fw-bold" for="is_temporary">
                                Unit pengganti / sementara
                            </label>
                        </div>

                        <div class="mb-4">
                            <label for="notes" class="form-label fw-bold">Catatan Unit (Opsional)</label>
                            <textarea name="notes" id="notes" class="form-control" rows="3">{{ old('notes') }}</textarea>
                        </div>

                        {{-- Tombol Aksi --}}
                        <div class="d-flex justify-content-end gap-2">
                            <a href="{{ route('admin.daftar_aset') }}" class="btn btn-light">Batal</a>
                            <button type="submit" class="btn btn-primary px-4">Simpan Aset</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
