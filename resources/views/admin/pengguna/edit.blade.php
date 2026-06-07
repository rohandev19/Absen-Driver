@extends('admin.layouts.app')

@section('title', 'Dashboard - Edit Pengguna')

@section('content')
    <div class="container-fluid p-0">

        <div class="row justify-content-center">
            <div class="col-lg-8">
                <div class="card shadow-sm border-0">
                    <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                        <h5 class="mb-0 fw-bold text-warning"><i class="bi bi-pencil-square me-2"></i> Edit Data Pengguna</h5>
                        <a href="javascript:history.back()" class="btn btn-outline-secondary btn-sm rounded-pill shadow-sm">
                            <i class="bi bi-arrow-left"></i> Kembali
                        </a>
                    </div>
                    <form action="{{ route('admin.pengguna.update', $pengguna->id) }}" method="POST">
                        @csrf
                        @method('PUT')
                        <div class="card-body">
                            <div class="row g-3">
                                <div class="col-md-6 col-12">
                                    <label for="name" class="form-label fw-bold">Nama Lengkap</label>
                                    <input type="text" class="form-control @error('name') is-invalid @enderror" id="name"
                                        name="name" value="{{ old('name', $pengguna->name) }}" required>
                                    @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                                <div class="col-md-6 col-12">
                                    <label for="email" class="form-label fw-bold">Alamat Email</label>
                                    <input type="email" class="form-control @error('email') is-invalid @enderror" id="email"
                                        name="email" value="{{ old('email', $pengguna->email) }}" required>
                                    @error('email') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>

                                <div class="col-md-6 col-12">
                                    <label for="role" class="form-label fw-bold">Role</label>
                                    <select class="form-select @error('role') is-invalid @enderror" id="role" name="role" required>
                                        <option value="">Pilih Role</option>
                                        <option value="master" {{ old('role', $pengguna->role) == 'master' ? 'selected' : '' }}>Master (Full Access)</option>
                                        <option value="service_admin" {{ old('role', $pengguna->role) == 'service_admin' ? 'selected' : '' }}>Service Admin</option>
                                        <option value="customer" {{ old('role', $pengguna->role) == 'customer' ? 'selected' : '' }}>Customer</option>
                                    </select>
                                    @error('role') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>

                                <div class="col-md-6 col-12" id="customer-field" style="display: none;">
                                    <label for="customer_id" class="form-label fw-bold">Customer</label>
                                    <select class="form-select @error('customer_id') is-invalid @enderror" id="customer_id" name="customer_id">
                                        <option value="">Pilih Customer</option>
                                        @foreach($customers as $customer)
                                            <option value="{{ $customer->id }}" {{ old('customer_id', $pengguna->customer_id) == $customer->id ? 'selected' : '' }}>
                                                {{ $customer->name }} ({{ $customer->code }})
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('customer_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>

                                <div class="col-12">
                                    <hr class="my-3 text-muted">
                                </div>

                                <h6 class="fw-bold mb-2 text-dark"><i class="bi bi-key me-1"></i> Reset Password (Opsional)
                                </h6>
                                <div class="col-md-6 col-12">
                                    <label for="password" class="form-label">Password Baru</label>
                                    <input type="password" class="form-control @error('password') is-invalid @enderror"
                                        id="password" name="password" autocomplete="new-password"
                                        placeholder="Kosongkan jika tidak diganti">
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
                            <a href="{{ route('admin.pengguna.index') }}" class="btn btn-outline-secondary me-2">
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