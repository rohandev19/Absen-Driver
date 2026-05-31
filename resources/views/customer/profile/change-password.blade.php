@extends('customer.layouts.app')

@section('title', 'Ganti Password')

@section('content')
<div class="container-fluid py-2">
    <div class="row justify-content-center">
        <div class="col-lg-6 col-md-8">
            <!-- Header -->
            <div class="mb-4">
                <h3 class="fw-bold text-dark mb-1"><i class="bi bi-key-fill text-primary me-2"></i>Ganti Password</h3>
                <p class="text-muted mb-0">Ubah password akun Anda secara berkala untuk menjaga kerahasiaan & keamanan akses.</p>
            </div>

            <!-- Form Card -->
            <div class="card border-0 shadow-sm" style="border-radius: 14px;">
                <div class="card-body p-4">
                    <form action="{{ route('customer.password.update') }}" method="POST">
                        @csrf

                        <!-- Current Password -->
                        <div class="mb-3">
                            <label for="current_password" class="form-label fw-bold text-secondary">Password Saat Ini</label>
                            <div class="input-group">
                                <input type="password" name="current_password" id="current_password" 
                                       class="form-control @error('current_password') is-invalid @enderror" 
                                       placeholder="Masukkan password lama" required>
                                <button type="button" class="btn btn-outline-secondary toggle-password" data-target="current_password">
                                    <i class="bi bi-eye"></i>
                                </button>
                                @error('current_password')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <hr class="text-muted opacity-10 my-4">

                        <!-- New Password -->
                        <div class="mb-3">
                            <label for="new_password" class="form-label fw-bold text-secondary">Password Baru</label>
                            <div class="input-group">
                                <input type="password" name="new_password" id="new_password" 
                                       class="form-control @error('new_password') is-invalid @enderror" 
                                       placeholder="Minimal 8 karakter unik" required>
                                <button type="button" class="btn btn-outline-secondary toggle-password" data-target="new_password">
                                    <i class="bi bi-eye"></i>
                                </button>
                                @error('new_password')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="form-text text-muted small mt-2">
                                <i class="bi bi-info-circle me-1"></i> Password harus terdiri minimal 8 karakter, serta memiliki kombinasi huruf besar, huruf kecil, angka, dan simbol.
                            </div>
                        </div>

                        <!-- Confirm Password -->
                        <div class="mb-4">
                            <label for="new_password_confirmation" class="form-label fw-bold text-secondary">Konfirmasi Password Baru</label>
                            <div class="input-group">
                                <input type="password" name="new_password_confirmation" id="new_password_confirmation" 
                                       class="form-control" 
                                       placeholder="Ulangi password baru" required>
                                <button type="button" class="btn btn-outline-secondary toggle-password" data-target="new_password_confirmation">
                                    <i class="bi bi-eye"></i>
                                </button>
                            </div>
                        </div>

                        <!-- Actions -->
                        <div class="d-flex gap-2 pt-3 border-top">
                            <button type="submit" class="btn btn-primary px-4" style="background-color: #1e3a8a; border: none; border-radius: 8px;">
                                <i class="bi bi-check-circle me-1"></i> Simpan Password Baru
                            </button>
                            <a href="{{ route('customer.profile') }}" class="btn btn-outline-secondary" style="border-radius: 8px;">
                                Batal
                            </a>
                        </div>

                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const toggleButtons = document.querySelectorAll('.toggle-password');
        
        toggleButtons.forEach(button => {
            button.addEventListener('click', function() {
                const targetId = this.getAttribute('data-target');
                const targetInput = document.getElementById(targetId);
                const icon = this.querySelector('i');
                
                if (targetInput.type === 'password') {
                    targetInput.type = 'text';
                    icon.classList.remove('bi-eye');
                    icon.classList.add('bi-eye-slash');
                } else {
                    targetInput.type = 'password';
                    icon.classList.remove('bi-eye-slash');
                    icon.classList.add('bi-eye');
                }
            });
        });
    });
</script>
@endpush
