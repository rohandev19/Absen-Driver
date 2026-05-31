@extends('admin.layouts.app')

@section('title', 'Kelola Customer')

@section('content')
    <div class="container-fluid p-0">
        <div class="row">
            {{-- FORM TAMBAH (KIRI) --}}
            <div class="col-md-4">
                <div class="card shadow-sm border-0 mb-4">
                    <div class="card-header bg-white py-3">
                        <h5 class="mb-0 fw-bold text-primary"><i class="bi bi-plus-circle me-2"></i>Tambah Customer</h5>
                    </div>
                    <div class="card-body">
                        <form action="{{ route('admin.customer.store') }}" method="POST">
                            @csrf
                            <div class="mb-3">
                                <label class="form-label fw-bold">Nama Customer <span class="text-danger">*</span></label>
                                <input type="text" name="name" class="form-control" placeholder="Contoh: J&T Express" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label fw-bold">Contact Person</label>
                                <input type="text" name="contact_person" class="form-control" placeholder="Contoh: Pak Budi">
                            </div>
                            <div class="mb-3">
                                <label class="form-label fw-bold">Nomor WhatsApp</label>
                                <input type="text" name="phone" class="form-control" placeholder="628123456789">
                                <small class="text-muted">Format: 628xxx (tanpa + dan tanpa 0 di depan)</small>
                            </div>
                            <div class="mb-3">
                                <label class="form-label fw-bold">Email</label>
                                <input type="email" name="email" class="form-control" placeholder="customer@example.com">
                            </div>
                            <div class="mb-3">
                                <label class="form-label fw-bold">Alamat</label>
                                <textarea name="address" class="form-control" rows="3" placeholder="Alamat lengkap customer"></textarea>
                            </div>
                            <button type="submit" class="btn btn-primary w-100">Simpan</button>
                        </form>
                    </div>
                </div>
            </div>

            {{-- TABEL DATA (KANAN) --}}
            <div class="col-md-8">
                <div class="card shadow-sm border-0">
                    <div class="card-header bg-white py-3">
                        <h5 class="mb-0 fw-bold"><i class="bi bi-people me-2"></i>Daftar Customer</h5>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0">
                                <thead class="bg-light">
                                    <tr>
                                        <th class="ps-4">Nama Customer</th>
                                        <th>Contact Person</th>
                                        <th>WhatsApp</th>
                                        <th>Project</th>
                                        <th class="text-center">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($customers as $customer)
                                        <tr>
                                            <td class="ps-4 fw-bold">{{ $customer->name }}</td>
                                            <td>{{ $customer->contact_person ?? '-' }}</td>
                                            <td>
                                                @if($customer->phone)
                                                    <a href="https://wa.me/{{ $customer->phone }}" target="_blank" class="text-success">
                                                        <i class="bi bi-whatsapp"></i> {{ $customer->phone }}
                                                    </a>
                                                @else
                                                    <span class="text-muted">-</span>
                                                @endif
                                            </td>
                                            <td>
                                                <span class="badge bg-info">{{ $customer->projects->count() }} project</span>
                                            </td>
                                            <td class="text-center">
                                                {{-- Tombol Edit --}}
                                                <button class="btn btn-warning btn-sm" data-bs-toggle="modal" 
                                                    data-bs-target="#editModal{{ $customer->id }}" title="Edit Customer">
                                                    <i class="bi bi-pencil"></i>
                                                </button>

                                                {{-- Tombol Hapus (hanya jika tidak ada project) --}}
                                                @if($customer->projects->count() == 0)
                                                    <form action="{{ route('admin.customer.destroy', $customer->id) }}" method="POST"
                                                        class="d-inline" onsubmit="return confirm('Yakin hapus customer ini?')">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button class="btn btn-danger btn-sm" title="Hapus Customer">
                                                            <i class="bi bi-trash"></i>
                                                        </button>
                                                    </form>
                                                @else
                                                    <button class="btn btn-secondary btn-sm" disabled title="Customer masih terhubung dengan project">
                                                        <i class="bi bi-lock-fill"></i>
                                                    </button>
                                                @endif
                                            </td>
                                        </tr>

                                        {{-- Modal Edit --}}
                                        <div class="modal fade" id="editModal{{ $customer->id }}" tabindex="-1">
                                            <div class="modal-dialog">
                                                <div class="modal-content">
                                                    <div class="modal-header">
                                                        <h5 class="modal-title">Edit Customer</h5>
                                                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                    </div>
                                                    <form action="{{ route('admin.customer.update', $customer->id) }}" method="POST">
                                                        @csrf
                                                        @method('PUT')
                                                        <div class="modal-body">
                                                            <div class="mb-3">
                                                                <label class="form-label fw-bold">Nama Customer</label>
                                                                <input type="text" name="name" class="form-control" 
                                                                    value="{{ $customer->name }}" required>
                                                            </div>
                                                            <div class="mb-3">
                                                                <label class="form-label fw-bold">Contact Person</label>
                                                                <input type="text" name="contact_person" class="form-control" 
                                                                    value="{{ $customer->contact_person }}">
                                                            </div>
                                                            <div class="mb-3">
                                                                <label class="form-label fw-bold">Nomor WhatsApp</label>
                                                                <input type="text" name="phone" class="form-control" 
                                                                    value="{{ $customer->phone }}" placeholder="628123456789">
                                                                <small class="text-muted">Format: 628xxx (tanpa + dan tanpa 0)</small>
                                                            </div>
                                                            <div class="mb-3">
                                                                <label class="form-label fw-bold">Email</label>
                                                                <input type="email" name="email" class="form-control" 
                                                                    value="{{ $customer->email }}">
                                                            </div>
                                                            <div class="mb-3">
                                                                <label class="form-label fw-bold">Alamat</label>
                                                                <textarea name="address" class="form-control" rows="3">{{ $customer->address }}</textarea>
                                                            </div>
                                                        </div>
                                                        <div class="modal-footer">
                                                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                                                            <button type="submit" class="btn btn-primary">Update</button>
                                                        </div>
                                                    </form>
                                                </div>
                                            </div>
                                        </div>
                                    @empty
                                        <tr>
                                            <td colspan="5" class="text-center py-4 text-muted">
                                                <i class="bi bi-inbox fs-1 d-block mb-2"></i>
                                                Belum ada customer. Tambahkan customer pertama Anda!
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                {{-- Info Card --}}
                <div class="card shadow-sm border-0 mt-3">
                    <div class="card-body">
                        <h6 class="fw-bold mb-2"><i class="bi bi-info-circle text-info me-2"></i>Informasi</h6>
                        <ul class="mb-0 small">
                            <li>Nomor WhatsApp digunakan untuk notifikasi approval service report</li>
                            <li>Format nomor: <code>628123456789</code> (62 = kode Indonesia, tanpa + dan tanpa 0 di depan)</li>
                            <li>Customer yang sudah terhubung dengan project tidak bisa dihapus</li>
                            <li>Setiap project harus di-link ke customer di menu "Kelola Project"</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Alert Messages --}}
    @if(session('success'))
        <div class="position-fixed bottom-0 end-0 p-3" style="z-index: 11">
            <div class="toast show" role="alert">
                <div class="toast-header bg-success text-white">
                    <strong class="me-auto">Sukses</strong>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="toast"></button>
                </div>
                <div class="toast-body">
                    {{ session('success') }}
                </div>
            </div>
        </div>
    @endif

    @if(session('error'))
        <div class="position-fixed bottom-0 end-0 p-3" style="z-index: 11">
            <div class="toast show" role="alert">
                <div class="toast-header bg-danger text-white">
                    <strong class="me-auto">Error</strong>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="toast"></button>
                </div>
                <div class="toast-body">
                    {{ session('error') }}
                </div>
            </div>
        </div>
    @endif
@endsection
