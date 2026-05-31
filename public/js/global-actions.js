// Jadikan Toast variabel global (window.Toast) agar bisa dipanggil dari file blade manapun
window.Toast = Swal.mixin({
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

document.addEventListener("DOMContentLoaded", function () {
    // --- 1. CEK FLASH DATA (Pesan dari Controller Laravel) ---
    const flashData = document.querySelector(".flash-data");
    if (flashData) {
        const type = flashData.getAttribute("data-type"); // success atau error
        const message = flashData.getAttribute("data-message");

        if (type === "success") {
            window.Toast.fire({ icon: "success", title: message });
        } else if (type === "error") {
            window.Toast.fire({ icon: "error", title: message });
        }
    }

    // --- 2. GLOBAL DELETE CONFIRMATION (Untuk Form Hapus) ---
    // Gunakan class="form-delete-global" pada form delete
    document.querySelectorAll(".form-delete-global").forEach((form) => {
        form.addEventListener("submit", function (e) {
            e.preventDefault();
            const message =
                this.getAttribute("data-message") ||
                "Data ini akan dihapus permanen!";

            Swal.fire({
                title: "Anda Yakin?",
                html: message, // Pakai HTML agar bisa bold teks
                icon: "warning",
                showCancelButton: true,
                confirmButtonColor: "#d33", // Merah
                cancelButtonColor: "#6c757d",
                confirmButtonText: "Ya, Hapus!",
                cancelButtonText: "Batal",
            }).then((result) => {
                if (result.isConfirmed) {
                    this.submit();
                }
            });
        });
    });

    // --- 3. GLOBAL LOGOUT CONFIRMATION (Khusus Logout) ---
    // Gunakan class="form-logout-global" pada form logout di sidebar/navbar
    // Ini membedakan visual warningnya dengan tombol hapus data (Merah vs Warning Biasa)
    document.querySelectorAll(".form-logout-global").forEach((form) => {
        form.addEventListener("submit", function (e) {
            e.preventDefault();

            Swal.fire({
                title: "Konfirmasi Logout",
                text: "Apakah Anda yakin ingin keluar dari sistem?",
                icon: "question",
                showCancelButton: true,
                confirmButtonColor: "#dc3545", // Merah logout
                cancelButtonColor: "#6c757d",
                confirmButtonText: "Ya, Keluar",
                cancelButtonText: "Batal",
            }).then((result) => {
                if (result.isConfirmed) {
                    this.submit();
                }
            });
        });
    });

    // --- 4. GLOBAL LINK CONFIRMATION (Untuk Tombol Link Aksi) ---
    // Gunakan class="link-confirm-global" pada tag <a>
    document.querySelectorAll(".link-confirm-global").forEach((link) => {
        link.addEventListener("click", function (e) {
            e.preventDefault();
            const url = this.getAttribute("href");
            const title = this.getAttribute("data-title") || "Konfirmasi";
            const text =
                this.getAttribute("data-text") || "Lanjutkan aksi ini?";
            const confirmText =
                this.getAttribute("data-confirm-text") || "Ya, Lanjutkan!";
            const iconType = this.getAttribute("data-icon") || "question"; // Bisa custom icon

            Swal.fire({
                title: title,
                text: text,
                icon: iconType,
                showCancelButton: true,
                confirmButtonColor: "#198754", // Hijau
                cancelButtonColor: "#6c757d",
                confirmButtonText: confirmText,
                cancelButtonText: "Batal",
            }).then((result) => {
                if (result.isConfirmed) {
                    window.location.href = url;
                    // Feedback visual kecil
                    window.Toast.fire({
                        icon: "info",
                        title: "Memproses permintaan...",
                    });
                }
            });
        });
    });
});
