<div class="modal fade" id="catatServisModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title"><i class="bi bi-wrench-adjustable-circle me-2"></i> Catat Riwayat Servis</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>

            <form id="formCatatServis" action="" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="alert alert-success border-0 bg-success bg-opacity-10 d-flex align-items-center mb-3">
                        <i class="bi bi-info-circle-fill text-success me-2 fs-4"></i>
                        <div>
                            Mencatat servis untuk: <strong id="modalPlatNomor">...</strong>
                        </div>
                    </div>

                    {{-- Tanggal Servis --}}
                    <div class="mb-3">
                        <label class="form-label fw-bold">Tanggal Servis</label>
                        <input type="date" class="form-control" name="service_date" value="{{ date('Y-m-d') }}"
                            required>
                    </div>

                    {{-- KM --}}
                    <div class="mb-3">
                        <label class="form-label fw-bold">KM Saat Servis (Odometer)</label>
                        <div class="input-group">
                            <input type="number" class="form-control" id="km_servis_saat_ini" name="km_servis_saat_ini"
                                required>
                            <span class="input-group-text">Km</span>
                        </div>
                    </div>

                    {{-- Keterangan (Deskripsi) --}}
                    <div class="mb-3">
                        <label class="form-label fw-bold">Keterangan Pengerjaan</label>
                        <textarea class="form-control" name="description" rows="3"
                            placeholder="Contoh: Ganti Oli Mesin, Filter Oli, Cek Rem..." required></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-success px-4">Simpan Riwayat</button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- Script JS tetap sama, tidak perlu diubah --}}
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

                catatServisModal.querySelector('#modalPlatNomor').textContent = platNomor;
                catatServisModal.querySelector('#formCatatServis').setAttribute('action', actionUrl);

                if (kmSaatIni) {
                    catatServisModal.querySelector('#km_servis_saat_ini').value = kmSaatIni;
                }
            });
        }
    });
</script>