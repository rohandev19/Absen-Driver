@extends('admin.layouts.app')

@section('title', 'Monitoring & Maintenance')

@section('content')
    <style>
        .maintenance-card {
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }

        .maintenance-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.15) !important;
        }
    </style>

    <div class="container-fluid p-0">
        {{-- Header --}}
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h1 class="h3 fw-bold text-dark mb-0"><i
                        class="bi bi-wrench-adjustable-circle text-primary me-2"></i>Maintenance Monitor</h1>
                <span class="text-muted">Pantau kesehatan mesin dan jadwal servis berkala</span>
            </div>
            <form action="{{ route('admin.maintenance.dashboard') }}" method="GET" class="d-flex">
                <input type="text" name="search" class="form-control me-2" placeholder="Cari Plat Nomor..."
                    value="{{ $searchKeyword }}">
                <button class="btn btn-primary" type="submit">Cari</button>
            </form>
        </div>

        <div class="row">
            @forelse($maintenanceData as $item)
                <div class="col-lg-4 col-md-6 mb-4">
                    {{-- Logic Border Merah jika Sisa KM habis ATAU Status Rusak --}}
                    <div
                        class="card shadow border-0 h-100 maintenance-card {{ ($item['sisa_km'] <= 0 || $item['status_kesehatan'] == 'Perlu Perbaikan Fisik') ? 'border-start border-5 border-danger' : '' }}">
                        <div class="card-body">
                            {{-- Judul Kartu --}}
                            <div class="d-flex justify-content-between align-items-start mb-3">
                                <div>
                                    <h5 class="fw-bold mb-0">{{ $item['plat'] }}</h5>
                                    <small class="text-muted">{{ $item['jenis'] }}</small>
                                </div>
                                <span class="badge bg-{{ $item['warna_status'] }}">{{ $item['status_kesehatan'] }}</span>
                            </div>

                            {{-- Progress Bar --}}
                            <div class="mb-3">
                                <div class="d-flex justify-content-between small mb-1">
                                    <span>Sisa Jarak Servis</span>
                                    <span class="fw-bold {{ $item['sisa_km'] < 1000 ? 'text-danger' : 'text-success' }}">
                                        {{ number_format($item['sisa_km']) }} Km
                                    </span>
                                </div>
                                @php
                                    $percent = 100;
                                    if ($item['km_servis_berikutnya'] > 0) {
                                        $jarakTempuhSejakServis = $item['km_saat_ini'] - $item['km_servis_terakhir'];
                                        $interval = $item['km_servis_berikutnya'] - $item['km_servis_terakhir'];
                                        if ($interval > 0)
                                            $percent = 100 - (($jarakTempuhSejakServis / $interval) * 100);
                                    }
                                    if ($percent < 0)
                                        $percent = 0;
                                @endphp
                                <div class="progress" style="height: 8px;">
                                    <div class="progress-bar {{ $percent < 20 ? 'bg-danger' : 'bg-success' }}"
                                        role="progressbar" style="width: {{ $percent }}%"></div>
                                </div>
                            </div>

                            {{-- Detail Info --}}
                            <div class="row g-2 small text-muted mb-4">
                                <div class="col-6"><i class="bi bi-speedometer2 me-1"></i> KM Saat Ini:<br><strong
                                        class="text-dark">{{ number_format($item['km_saat_ini']) }}</strong></div>
                                <div class="col-6"><i class="bi bi-calendar-check me-1"></i> Update:<br><strong
                                        class="text-dark">{{ $item['update_terakhir'] }}</strong></div>
                            </div>

                            {{-- Tombol Aksi --}}
                            <div class="d-grid gap-2">
                                <a href="{{ route('admin.aset.visual', $item['id']) }}" class="btn btn-outline-primary btn-sm">
                                    <i class="bi bi-eye-fill me-2"></i> Visual Check (3D)
                                </a>

                                @can('is-master-admin')
                                    @if ($item['status_kesehatan'] == 'Perlu Perbaikan Fisik')
                                        {{-- JIKA RUSAK: Tampilkan Tombol Reset (Merah) dengan SweetAlert --}}
                                        <form action="{{ route('admin.aset.resolveIssue', $item['id']) }}" method="POST"
                                            class="d-grid form-confirm-repair">
                                            @csrf
                                            <button type="submit" class="btn btn-danger btn-sm">
                                                <i class="bi bi-bandaid-fill me-2"></i> Tandai Selesai Perbaikan
                                            </button>
                                        </form>
                                    @else
                                        {{-- JIKA AMAN: Tampilkan Tombol Catat Servis (Hijau) --}}
                                        <button class="btn btn-outline-success btn-sm" data-bs-toggle="modal"
                                            data-bs-target="#catatServisModal" data-plat-nomor="{{ $item['plat'] }}"
                                            data-km-saat-ini="{{ $item['km_saat_ini'] }}"
                                            data-action-url="{{ route('admin.aset.catatServis', $item['id']) }}">
                                            <i class="bi bi-wrench-adjustable me-2"></i> Catat Servis Baru
                                        </button>
                                    @endif
                                @endcan
                            </div>
                        </div>
                    </div>
                </div>
            @empty
                <div class="col-12 text-center text-muted py-5">
                    <i class="bi bi-inbox fs-1"></i>
                    <p>Data aset tidak ditemukan.</p>
                </div>
            @endforelse
        </div>
    </div>

    {{--
    ========================================
    MODAL CATAT SERVIS (EMBEDDED)
    ========================================
    --}}
    @can('is-master-admin')
        <div class="modal fade" id="catatServisModal" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header bg-success text-white">
                        <h5 class="modal-title"><i class="bi bi-wrench-adjustable-circle me-2"></i> Catat Servis Selesai</h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                    </div>

                    <form id="formCatatServis" action="" method="POST">
                        @csrf
                        <div class="modal-body">
                            <div class="alert alert-success border-0 bg-success bg-opacity-10 d-flex align-items-center">
                                <i class="bi bi-info-circle-fill text-success me-2 fs-4"></i>
                                <div>
                                    Anda akan mencatat servis untuk: <br>
                                    <strong id="modalPlatNomor" class="fs-5">...</strong>
                                </div>
                            </div>

                            <div class="mb-3">
                                <label for="km_servis_saat_ini" class="form-label fw-bold">KM Saat Ini (Setelah Servis)</label>
                                <div class="input-group">
                                    <input type="number" class="form-control" id="km_servis_saat_ini" name="km_servis_saat_ini"
                                        placeholder="Contoh: 50000" required>
                                    <span class="input-group-text">Km</span>
                                </div>
                                <div class="form-text text-muted">
                                    <small>Masukkan angka odometer saat mobil selesai diservis. Angka ini akan mereset hitungan
                                        "Sisa Jarak Servis".</small>
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-light" data-bs-dismiss="modal">Batal</button>
                            <button type="submit" class="btn btn-success px-4">Simpan Data</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endcan

@endsection

@push('scripts')
    {{-- Pastikan SweetAlert2 sudah diload di layout utama (app.blade.php) --}}

    <script>
        document.addEventListener('DOMContentLoaded', function () {

            // --- 1. LOGIKA MODAL CATAT SERVIS ---
            var catatServisModal = document.getElementById('catatServisModal');
            if (catatServisModal) {
                catatServisModal.addEventListener('show.bs.modal', function (event) {
                    var button = event.relatedTarget;
                    if (!button) return;

                    var platNomor = button.getAttribute('data-plat-nomor');
                    var kmSaatIni = button.getAttribute('data-km-saat-ini');
                    var actionUrl = button.getAttribute('data-action-url');

                    catatServisModal.querySelector('#modalPlatNomor').textContent = platNomor;
                    catatServisModal.querySelector('#formCatatServis').setAttribute('action', actionUrl);

                    if (kmSaatIni) {
                        catatServisModal.querySelector('#km_servis_saat_ini').value = kmSaatIni;
                    }
                });
            }

            // --- 2. LOGIKA SWEETALERT KONFIRMASI PERBAIKAN ---
            const repairForms = document.querySelectorAll('.form-confirm-repair');
            repairForms.forEach(form => {
                form.addEventListener('submit', function (event) {
                    event.preventDefault(); // Mencegah submit langsung

                    Swal.fire({
                        title: 'Selesai Perbaikan?',
                        text: "Status mobil akan dikembalikan menjadi 'Aman' dan siap beroperasi.",
                        icon: 'question',
                        showCancelButton: true,
                        confirmButtonColor: '#198754', // Hijau
                        cancelButtonColor: '#6c757d', // Abu
                        confirmButtonText: 'Ya, Selesai!',
                        cancelButtonText: 'Batal'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            this.submit(); // Lanjutkan submit jika user klik Ya
                        }
                    });
                });
            });

        });
    </script>
@endpush