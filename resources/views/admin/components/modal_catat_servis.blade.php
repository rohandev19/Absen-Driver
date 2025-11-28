{{--
FILE: resources/views/admin/components/modal_catat_servis.blade.php
--}}
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
                            <small>Masukkan angka odometer saat servis dilakukan. Angka ini akan mereset hitungan "Sisa
                                Jarak Servis".</small>
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

                // Isi input dengan KM terakhir sebagai referensi
                if (kmSaatIni) {
                    catatServisModal.querySelector('#km_servis_saat_ini').value = kmSaatIni;
                }
            });
        }
    });
</script>