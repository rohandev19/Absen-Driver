@extends('admin.layouts.app')

@section('title', 'Dashboard - Daftar Aset Mobil')

@section('content')
    <div class="container-fluid p-0">

        @if (session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif
        @if (session('error'))
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                {{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif
        @if ($errors->any())
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <strong>Gagal!</strong> Periksa input Anda.
                <ul>
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        <div class="row justify-content-center">
            <div class="col-lg-12">
                <div class="card shadow-sm">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h2 class="h5 mb-0"><i class="bi bi-truck"></i> Daftar Aset Mobil (Status Terakhir)</h2>
                        <span class="badge bg-primary rounded-pill">
                            {{ count($daftarMobil) }} Total Aset
                        </span>
                    </div>
                    <div class="card-body">

                        {{-- Form Pencarian --}}
                        <form action="{{ route('admin.daftar_aset') }}" method="GET" class="mb-3">
                            <div class="input-group">
                                <input type="search" class="form-control" name="search"
                                    placeholder="Cari plat, jenis, atau driver..." value="{{ $searchKeyword ?? '' }}"
                                    aria-label="Cari Aset">
                                <button class="btn btn-primary" type="submit">
                                    <i class="bi bi-search"></i> Cari
                                </button>
                                <a href="{{ route('admin.daftar_aset') }}" class="btn btn-outline-secondary"
                                    title="Reset Pencarian">
                                    <i class="bi bi-arrow-counterclockwise"></i>
                                </a>
                            </div>
                        </form>

                        <div class="table-responsive table-responsive-cards">
                            <table class="table table-hover table-striped table-sm align-middle">
                                <thead class="table-light">
                                    <tr>
                                        <th style="width: 1%;">No.</th>
                                        <th>Plat Nomor</th>
                                        <th>Jenis Mobil</th>
                                        <th>Status</th>
                                        <th>Driver</th>
                                        <th class="text-end pe-3">KM Terakhir</th>
                                        <th>Update Terakhir</th>
                                        <th class="text-center">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse ($daftarMobil as $mobil)
                                        <tr class="aset-row">
                                            <td data-label="No.">{{ $loop->iteration }}</td>
                                            <td data-label="Plat Nomor"><span
                                                    class="badge bg-secondary fs-6">{{ $mobil['plat_nomor'] }}</span></td>
                                            <td data-label="Jenis Mobil">{{ $mobil['jenis_mobil'] }}</td>
                                            <td data-label="Status">
                                                @if ($mobil['status'] == 'Sedang Dipakai')
                                                    <span class="badge bg-danger"><i class="bi bi-broadcast"></i>
                                                        {{ $mobil['status'] }}</span>
                                                @else
                                                    <span class="badge bg-success"><i class="bi bi-p-circle-fill"></i>
                                                        {{ $mobil['status'] }}</span>
                                                @endif
                                            </td>
                                            <td data-label="Driver"><i class="bi bi-person-fill"></i>
                                                {{ $mobil['driver_terakhir'] }}</td>
                                            <td data-label="KM Terakhir" class="text-end pe-3">
                                                {{ number_format($mobil['km_terakhir']) }} Km
                                            </td>
                                            <td data-label="Update Terakhir"><small>{{ $mobil['tgl_terakhir'] }}</small></td>

                                            <td data-label="Aksi" class="text-center">
                                                <div class="d-inline-flex flex-nowrap" style="gap: 5px;">

                                                    {{-- TOMBOL UMUM (SEMUA ADMIN BISA LIHAT) --}}
                                                    <button class="btn btn-info btn-sm" data-bs-toggle="collapse"
                                                        data-bs-target="#detail-{{ $mobil['id'] }}" aria-expanded="false"
                                                        title="Tampilkan Detail">
                                                        <i class="bi bi-info-circle"></i>
                                                    </button>

                                                    <a href="{{ route('admin.riwayat_unit', ['plate_number' => $mobil['plat_nomor']]) }}"
                                                        class="btn btn-secondary btn-sm" title="Lihat Riwayat Unit">
                                                        <i class="bi bi-clock-history"></i>
                                                    </a>

                                                    {{-- TOMBOL KHUSUS MASTER ADMIN --}}
                                                    @can('is-master-admin')
                                                        <button class="btn btn-primary btn-sm" data-bs-toggle="modal"
                                                            data-bs-target="#catatServisModal"
                                                            data-plat-nomor="{{ $mobil['plat_nomor'] }}"
                                                            data-km-saat-ini="{{ $mobil['km_terakhir'] }}"
                                                            data-action-url="{{ route('admin.aset.catatServis', $mobil['id']) }}"
                                                            title="Catat Servis Selesai">
                                                            <i class="bi bi-wrench-adjustable"></i>
                                                        </button>

                                                        {{-- Tombol Log Operasional (Jika sudah dibuat) --}}
                                                        {{--
                                                        <button class="btn btn-secondary btn-sm" data-bs-toggle="modal" ...>
                                                            <i class="bi bi-fuel-pump"></i>
                                                        </button>
                                                        --}}

                                                        <a href="{{ route('admin.aset.edit', $mobil['id']) }}"
                                                            class="btn btn-warning btn-sm" title="Edit Data Mobil">
                                                            <i class="bi bi-pencil-fill"></i>
                                                        </a>
                                                    @endcan
                                                </div>
                                            </td>
                                        </tr>

                                        {{-- Baris detail yang tersembunyi --}}
                                        <tr class="aset-detail-row collapse" id="detail-{{ $mobil['id'] }}">
                                            <td colspan="8">
                                                <div class="p-3 bg-light-subtle border rounded m-1">
                                                    <h6 class="mb-3 fw-bold">Detail Kendaraan: {{ $mobil['plat_nomor'] }}</h6>
                                                    <div class="row g-3">
                                                        <div class="col-md-4">
                                                            <strong>Servis Berikutnya (KM):</strong><br>
                                                            {{ $mobil['km_servis_berikutnya'] == '-' ? '-' : number_format($mobil['km_servis_berikutnya']) . ' Km' }}
                                                        </div>
                                                        <div class="col-md-4">
                                                            <strong>Sisa Jarak Servis:</strong><br>
                                                            <b
                                                                class="fs-6">{{ $mobil['sisa_km'] == '-' ? '-' : number_format($mobil['sisa_km']) . ' Km' }}</b>
                                                        </div>
                                                        <div class="col-md-4">
                                                            <strong>Status Servis:</strong><br>
                                                            <span class="badge bg-{{ $mobil['status_servis']['badge'] }}">
                                                                {{ $mobil['status_servis']['text'] }}
                                                            </span>
                                                        </div>
                                                        <div class="col-md-4">
                                                            <strong>Status STNK:</strong><br>
                                                            <span class="badge bg-{{ $mobil['status_stnk']['badge'] }}">
                                                                {{ $mobil['status_stnk']['text'] }}
                                                            </span>
                                                        </div>
                                                        <div class="col-md-4">
                                                            <strong>Status KIR:</strong><br>
                                                            <span class="badge bg-{{ $mobil['status_kir']['badge'] }}">
                                                                {{ $mobil['status_kir']['text'] }}
                                                            </span>
                                                        </div>
                                                    </div>
                                                </div>
                                            </td>
                                        </tr>

                                    @empty
                                        <tr>
                                            <td colspan="8" class="text-center">
                                                Data mobil tidak ditemukan.
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- MODAL SERVIS (HANYA MASTER ADMIN) --}}
    @can('is-master-admin')
        <div class="modal fade" id="catatServisModal" tabindex="-1" aria-labelledby="catatServisModalLabel" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="catatServisModalLabel">Catat Servis Selesai</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <form id="formCatatServis" action="" method="POST">
                        @csrf
                        <div class="modal-body">
                            <p>Anda akan mencatat servis selesai untuk mobil: <br>
                                <strong id="modalPlatNomor" class="fs-5"></strong>
                            </p>
                            <div class="mb-3">
                                <label for="km_servis_saat_ini" class="form-label">KM Servis Saat Ini (Wajib)</label>
                                <input type="number" class="form-control" id="km_servis_saat_ini" name="km_servis_saat_ini"
                                    placeholder="Masukkan angka KM" required>
                                <div class="form-text">Data ini akan menjadi "KM Servis Terakhir" yang baru.</div>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                            <button type="submit" class="btn btn-primary"><i class="bi bi-save"></i> Simpan Data Servis</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endcan
@endsection

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            var catatServisModal = document.getElementById('catatServisModal');
            if (catatServisModal) {
                catatServisModal.addEventListener('show.bs.modal', function (event) {
                    var button = event.relatedTarget;
                    if (!button) return;
                    var platNomor = button.getAttribute('data-plat-nomor');
                    var kmSaatIni = button.getAttribute('data-km-saat-ini');
                    var actionUrl = button.getAttribute('data-action-url');
                    var modalTitle = catatServisModal.querySelector('#catatServisModalLabel');
                    var modalPlatNomor = catatServisModal.querySelector('#modalPlatNomor');
                    var modalForm = catatServisModal.querySelector('#formCatatServis');
                    var modalInputKM = catatServisModal.querySelector('#km_servis_saat_ini');
                    if (modalTitle) modalTitle.textContent = 'Catat Servis Selesai: ' + platNomor;
                    if (modalPlatNomor) modalPlatNomor.textContent = platNomor;
                    if (modalForm) modalForm.setAttribute('action', actionUrl);
                    if (modalInputKM) modalInputKM.value = kmSaatIni;
                });
            }
            @if ($errors->has('km_servis_saat_ini'))
                var errorModal = new bootstrap.Modal(document.getElementById('catatServisModal'));
                errorModal.show();
            @endif
                });
    </script>
@endpush