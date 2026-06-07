@extends('admin.layouts.app')

@section('title', 'Dashboard - Tambah Pengguna')

@section('content')
    <div class="container-fluid p-0">

        <div class="row justify-content-center">
            <div class="col-lg-8">
                <div class="card shadow-sm border-0">
                    <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                        <h5 class="mb-0 fw-bold text-primary"><i class="bi bi-person-plus-fill me-2"></i> Tambah Admin Baru</h5>
                        <a href="javascript:history.back()" class="btn btn-outline-secondary btn-sm rounded-pill shadow-sm">
                            <i class="bi bi-arrow-left"></i> Kembali
                        </a>
                    </div>
                    <form action="{{ route('admin.pengguna.store') }}" method="POST">
                        @csrf
                        <div class="card-body">
                            <div class="alert alert-light border-start border-4 border-info small mb-4">
                                <strong>Info:</strong> Pengguna baru akan memiliki akses Admin standar. Password bisa diubah
                                nanti oleh pengguna.
                            </div>

                            <div class="row g-3">
                                <div class="col-md-6 col-12">
                                    <label for="name" class="form-label fw-bold">Nama Lengkap</label>
                                    <input type="text" class="form-control @error('name') is-invalid @enderror" id="name"
                                        name="name" value="{{ old('name') }}" placeholder="Contoh: Admin Logistik" required>
                                    @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                                <div class="col-md-6 col-12">
                                    <label for="email" class="form-label fw-bold">Alamat Email</label>
                                    <input type="email" class="form-control @error('email') is-invalid @enderror" id="email"
                                        name="email" value="{{ old('email') }}" placeholder="admin@perusahaan.com" required>
                                    @error('email') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>

                                <div class="col-md-6 col-12">
                                    <label for="role" class="form-label fw-bold">Role</label>
                                    <select class="form-select @error('role') is-invalid @enderror" id="role" name="role" required>
                                        <option value="">Pilih Role</option>
                                        <option value="master" {{ old('role') == 'master' ? 'selected' : '' }}>Master (Full Access)</option>
                                        <option value="service_admin" {{ old('role') == 'service_admin' ? 'selected' : '' }}>Service Admin</option>
                                        <option value="customer" {{ old('role') == 'customer' ? 'selected' : '' }}>Customer</option>
                                    </select>
                                    @error('role') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>

                                <div class="col-md-6 col-12" id="customer-field" style="display: none;">
                                    <label for="customer_id" class="form-label fw-bold">Customer</label>
                                    <select class="form-select @error('customer_id') is-invalid @enderror" id="customer_id" name="customer_id">
                                        <option value="">Pilih Customer</option>
                                        @foreach($customers as $customer)
                                            <option value="{{ $customer->id }}" {{ old('customer_id') == $customer->id ? 'selected' : '' }}>
                                                {{ $customer->name }} ({{ $customer->code }})
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('customer_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>

                                <div class="col-12">
                                    <hr class="text-muted">
                                </div>

                                <div class="col-md-6 col-12">
                                    <label for="password" class="form-label fw-bold">Password</label>
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
                            <a href="{{ route('admin.pengguna.index') }}" class="btn btn-outline-secondary me-2">
                                <i class="bi bi-arrow-left"></i> Batal
                            </a>
                            <button type="submit" class="btn btn-primary px-4">
                                <i class="bi bi-save-fill me-1"></i> Simpan Pengguna
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const roleSelect = document.getElementById('role');
        const customerField = document.getElementById('customer-field');
        const customerSelect = document.getElementById('customer_id');

        roleSelect.addEventListener('change', function() {
            if (this.value === 'customer') {
                customerField.style.display = 'block';
                customerSelect.required = true;
            } else {
                customerField.style.display = 'none';
                customerSelect.required = false;
                customerSelect.value = '';
            }
        });

        // Trigger on page load if old value exists
        if (roleSelect.value === 'customer') {
            customerField.style.display = 'block';
            customerSelect.required = true;
        }
    });
</script>
@endpush