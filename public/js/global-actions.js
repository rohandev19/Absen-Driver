document.addEventListener("DOMContentLoaded", function () {
    // --- 1. SETUP TOAST (Notifikasi Kecil di Pojok Kanan) ---
    const Toast = Swal.mixin({
        toast: true,
        position: "top-end",
        showConfirmButton: false,
        timer: 3000,
        timerProgressBar: true,
        didOpen: (toast) => {
            toast.addEventListener("mouseenter", Swal.stopTimer);
            toast.addEventListener("mouseleave", Swal.resumeTimer);
        },
    });

    // --- 2. CEK FLASH DATA (Pesan dari Controller Laravel) ---
    // Kode ini mencari elemen tersembunyi di app.blade.php
    const flashData = document.querySelector(".flash-data");
    if (flashData) {
        const type = flashData.getAttribute("data-type"); // success atau error
        const message = flashData.getAttribute("data-message");

        if (type === "success") {
            Toast.fire({ icon: "success", title: message });
        } else if (type === "error") {
            Toast.fire({ icon: "error", title: message });
        }
    }

    // --- 3. GLOBAL DELETE CONFIRMATION (Untuk Form Hapus) ---
    // Cari semua form dengan class 'form-delete-global'
    document.querySelectorAll(".form-delete-global").forEach((form) => {
        form.addEventListener("submit", function (e) {
            e.preventDefault(); // Tahan dulu submit-nya
            const message =
                this.getAttribute("data-message") ||
                "Data ini akan dihapus permanen!";

            Swal.fire({
                title: "Anda Yakin?",
                text: message,
                icon: "warning",
                showCancelButton: true,
                confirmButtonColor: "#d33",
                cancelButtonColor: "#6c757d",
                confirmButtonText: "Ya, Hapus!",
                cancelButtonText: "Batal",
            }).then((result) => {
                if (result.isConfirmed) {
                    this.submit(); // Lanjutkan submit jika user klik Ya
                }
            });
        });
    });

    // --- 4. GLOBAL LINK CONFIRMATION (Untuk Export/Download) ---
    // Cari semua link dengan class 'link-confirm-global'
    document.querySelectorAll(".link-confirm-global").forEach((link) => {
        link.addEventListener("click", function (e) {
            e.preventDefault();
            const url = this.getAttribute("href");
            const title = this.getAttribute("data-title") || "Konfirmasi";
            const text =
                this.getAttribute("data-text") || "Lanjutkan aksi ini?";
            const confirmText =
                this.getAttribute("data-confirm-text") || "Ya, Lanjutkan!";

            Swal.fire({
                title: title,
                text: text,
                icon: "question",
                showCancelButton: true,
                confirmButtonColor: "#198754",
                cancelButtonColor: "#6c757d",
                confirmButtonText: confirmText,
                cancelButtonText: "Batal",
            }).then((result) => {
                if (result.isConfirmed) {
                    window.location.href = url;
                    // Feedback visual kecil bahwa proses berjalan
                    Toast.fire({ icon: "info", title: "Sedang memproses..." });
                }
            });
        });
    });
});
