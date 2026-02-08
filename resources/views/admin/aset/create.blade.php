@extends('admin.layouts.app')

@section('title', 'Tambah Aset Baru')

@section('content')
<div class="container-fluid p-0">
    <div class="row justify-content-center">
        <div class="col-md-8 col-lg-6">
            <div class="card shadow-sm border-0">
                <div class="card-header bg-white py-3">
                    <h5 class="mb-0 fw-bold text-dark"><i class="bi bi-plus-circle text-primary me-2"></i> Tambah Aset Mobil</h5>
                </div>
                <div class="card-body p-4">
                    <form action="{{ route('admin.aset.store') }}" method="POST">
                        @csrf
                        
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
                        <div class="mb-4">
                            <label for="type" class="form-label fw-bold">Jenis Mobil / Tipe</label>
                            <input type="text" class="form-control @error('type') is-invalid @enderror" 
                                id="type" name="type" placeholder="Contoh: BLINDVAN / CDE / GRANDMAX" 
                                value="{{ old('type') }}" required>
                            @error('type')
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