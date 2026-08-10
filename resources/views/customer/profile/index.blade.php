@extends('customer.layouts.app')

@section('title', 'Profil Saya')

@section('content')
<div class="container-fluid py-2">
    <div class="row justify-content-center">
        <div class="col-lg-8 col-xl-7">
            <!-- Header -->
            <div class="mb-4">
                <h3 class="fw-bold text-dark mb-1"><i class="bi bi-person text-primary me-2"></i>Profil Saya</h3>
                <p class="text-muted mb-0">Informasi detail akun pengguna dan perusahaan rekanan.</p>
            </div>

            <!-- Profile Card -->
            <div class="card border-0 shadow-sm overflow-hidden" style="border-radius: 14px;">
                <div class="bg-primary bg-opacity-10 p-4 d-flex align-items-center">
                    <div class="rounded-circle bg-primary text-white d-flex align-items-center justify-content-center shadow-sm me-4" style="width: 70px; height: 70px;">
                        <i class="bi bi-person-badge fs-1"></i>
                    </div>
                    <div>
                        <h4 class="fw-bold text-dark mb-0">{{ $user->name }}</h4>
                        <span class="badge bg-primary text-uppercase px-2.5 py-1 mt-1" style="font-size: 0.75rem;">{{ $user->role }}</span>
                    </div>
                </div>

                <div class="card-body p-4">
                    <div class="space-y-4">
                        <!-- Account Details -->
                        <h5 class="fw-bold text-dark mb-3">Rincian Akun</h5>
                        
                        <div class="row mb-3 border-bottom pb-2">
                            <div class="col-sm-4 text-muted small">Nama Lengkap</div>
                            <div class="col-sm-8 fw-semibold text-dark">{{ $user->name }}</div>
                        </div>

                        <div class="row mb-3 border-bottom pb-2">
                            <div class="col-sm-4 text-muted small">Alamat Email</div>
                            <div class="col-sm-8 fw-semibold text-dark">{{ $user->email }}</div>
                        </div>

                        <div class="row mb-4 pb-2 border-bottom">
                            <div class="col-sm-4 text-muted small">Terdaftar Sejak</div>
                            <div class="col-sm-8 fw-semibold text-dark">{{ $user->created_at ? $user->created_at->format('d M Y') : '-' }}</div>
                        </div>

                        <!-- Partner Details -->
                        <h5 class="fw-bold text-dark mb-3 mt-4">Informasi Rekanan / Customer</h5>
                        
                        <div class="row mb-3 border-bottom pb-2">
                            <div class="col-sm-4 text-muted small">Nama Perusahaan</div>
                            <div class="col-sm-8 fw-bold text-primary">{{ $customer->name ?? '-' }}</div>
                        </div>

                        <div class="row mb-3 border-bottom pb-2">
                            <div class="col-sm-4 text-muted small">Alamat Perusahaan</div>
                            <div class="col-sm-8 fw-semibold text-dark">{{ $customer->alamat ?? '-' }}</div>
                        </div>

                        <div class="row mb-4 pb-2">
                            <div class="col-sm-4 text-muted small">Projek Terhubung</div>
                            <div class="col-sm-8">
                                @if($customer && $customer->projects)
                                    <div class="d-flex flex-wrap gap-1.5 mt-1">
                                        @forelse($customer->projects as $proj)
                                            <span class="badge bg-secondary-subtle text-secondary-emphasis border border-secondary-subtle border-opacity-25 px-2.5 py-1.5" style="font-size: 0.75rem;">
                                                <i class="bi bi-folder-fill me-1"></i>{{ $proj->name }}
                                            </span>
                                        @empty
                                            <span class="text-muted small">Tidak ada projek terhubung</span>
                                        @endforelse
                                    </div>
                                @else
                                    <span class="text-muted small">-</span>
                                @endif
                            </div>
                        </div>

                        <!-- Actions -->
                        <div class="d-flex gap-2 mt-4 pt-3 border-top">
                            <a href="{{ route('customer.password.form') }}" class="btn btn-outline-primary" style="border-radius: 8px;">
                                <i class="bi bi-key-fill me-1"></i> Ganti Password Akun
                            </a>
                            <a href="{{ route('customer.dashboard') }}" class="btn btn-primary" style="background-color: #1e3a8a; border: none; border-radius: 8px;">
                                <i class="bi bi-speedometer2 me-1"></i> Dashboard Utama
                            </a>
                        </div>

                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
