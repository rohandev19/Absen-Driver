# Sequence Diagram Alur Inti Absensi

Dokumen ini berisi sequence diagram utama untuk deskripsi sistem: login, check-in driver, check-out driver, dan sinkronisasi offline.

## 1. Login dan Role Redirect

Diagram ini menggambarkan alur login web. Role pengguna menentukan halaman tujuan setelah autentikasi berhasil.

```mermaid
sequenceDiagram
    actor User
    participant Browser as Web Browser
    participant Controller as AdminLoginController
    participant DB as Database

    User->>Browser: Buka halaman login
    Browser->>Controller: GET /admin/login
    Controller-->>Browser: Tampilkan form login

    User->>Browser: Isi email dan password
    Browser->>Controller: POST /admin/login
    Controller->>Controller: Validasi input dan cek rate limit

    alt Terlalu banyak percobaan
        Controller-->>Browser: Tampilkan pesan throttle
    else Percobaan diizinkan
        Controller->>DB: Cek email dan password
        DB-->>Controller: Hasil autentikasi

        alt Login gagal
            Controller-->>Browser: Tampilkan pesan error
        else Login berhasil
            Controller->>Controller: Regenerasi session

            alt role = master
                Controller-->>Browser: Redirect ke dashboard admin
            else role = service_admin
                Controller-->>Browser: Redirect ke halaman service
            else role = customer
                Controller-->>Browser: Redirect ke dashboard customer
            end

            Browser-->>User: Tampilkan halaman sesuai role
        end
    end
```

Catatan: login driver mobile memakai endpoint `POST /api/login`, menghasilkan token Sanctum, dan tidak melakukan redirect halaman.

## 2. Check-In Driver

Diagram ini menggambarkan alur driver melakukan check-in melalui aplikasi mobile. Sistem mendukung input kendaraan dari QR maupun manual.

```mermaid
sequenceDiagram
    actor Driver
    participant App as Mobile App
    participant API as Server API
    participant DB as Database

    Driver->>App: Scan QR driver, pilih metode kendaraan
    Driver->>App: Ambil foto selfie, speedometer, dan input KM awal
    App->>App: Ambil GPS dan timestamp
    App->>API: POST /api/submit-attendance
    API->>API: Autentikasi token dan validasi data

    alt Validasi gagal
        API-->>App: Tampilkan pesan error
    else Validasi berhasil
        API->>DB: Cek status driver (is_on_duty)
        DB-->>API: Status driver

        alt Driver sedang bertugas
            API-->>App: Tolak check-in
        else Driver belum bertugas
            API->>DB: Cari kendaraan berdasarkan plat
            DB-->>API: Kendaraan / tidak ditemukan

            alt Tidak ditemukan dan metode QR
                API-->>App: Tolak, plat tidak dikenal
            else Tidak ditemukan dan metode manual
                API->>DB: Buat kendaraan sementara (Pending Verifikasi)
                DB-->>API: Kendaraan baru
            else Kendaraan ditemukan tapi tidak tersedia
                API-->>App: Tolak, unit tidak dapat digunakan
            else Kendaraan tersedia
                API->>DB: Simpan attendance dan set driver on duty
                DB-->>API: Data tersimpan
                API-->>App: Check-in berhasil
                App-->>Driver: Tampilkan status bertugas
            end
        end
    end
```

## 3. Check-Out Driver

Diagram ini menggambarkan alur driver mengakhiri tugas. Data KM akhir dan checklist kondisi kendaraan disimpan sebagai laporan akhir tugas.

```mermaid
sequenceDiagram
    actor Driver
    participant App as Mobile App
    participant API as Server API
    participant DB as Database

    Driver->>App: Input KM akhir, checklist kondisi, catatan, foto speedometer
    App->>App: Ambil GPS
    App->>API: POST /api/submit-end-of-duty
    API->>API: Autentikasi token dan validasi data

    alt Validasi gagal
        API-->>App: Tampilkan pesan error
    else Validasi berhasil
        API->>DB: Cari attendance aktif driver
        DB-->>API: Attendance aktif / tidak ada

        alt Tidak ada tugas aktif
            API-->>App: Tidak ada tugas aktif
        else Ada tugas aktif
            API->>DB: Update attendance dan set driver off duty
            Note over DB: time_out, KM akhir, GPS,<br/>checklist ban/lampu/rem, catatan
            DB-->>API: Data tersimpan

            API->>API: Hitung durasi kerja dan total KM
            API-->>App: Ringkasan check-out
            App-->>Driver: Tampilkan durasi, total KM, dan kondisi unit
        end
    end
```

## 4. Sinkronisasi Offline

Diagram ini menggambarkan alur check-out yang disimpan di perangkat saat offline, lalu dikirim ke server setelah koneksi tersedia.

```mermaid
sequenceDiagram
    actor Driver
    participant App as Mobile App
    participant LocalDB as Local Database
    participant API as Server API
    participant DB as Database

    Driver->>App: Check-out saat offline
    App->>LocalDB: Simpan data check-out lokal
    Note over LocalDB: offline_entry_id, timestamp,<br/>KM akhir, checklist, GPS, foto

    App->>App: Tunggu koneksi internet tersedia
    App->>API: POST /api/clock-out-offline
    API->>API: Autentikasi token dan validasi data

    alt Validasi gagal
        API-->>App: Tampilkan error
        App->>LocalDB: Tandai perlu retry
    else Validasi berhasil
        API->>DB: Cek offline_entry_id sudah pernah diproses?
        DB-->>API: Hasil pengecekan

        alt Sudah pernah diproses
            API-->>App: Data sudah tersimpan (idempotent)
            App->>LocalDB: Tandai sudah sinkron
        else Belum pernah diproses
            API->>DB: Cek driver masih bertugas di server?
            DB-->>API: Status tugas driver

            alt Driver tidak bertugas
                API-->>App: Sync gagal
                App->>LocalDB: Tandai gagal
            else Driver masih bertugas
                API->>DB: Update attendance dan set driver off duty
                Note over DB: time_out dari device,<br/>tandai sebagai offline recovery
                DB-->>API: Data tersimpan
                API-->>App: Sinkronisasi berhasil
                App->>LocalDB: Tandai sudah sinkron
                App-->>Driver: Tampilkan ringkasan check-out
            end
        end
    end
```

## Catatan Implementasi

- Login web diproses oleh `app/Http/Controllers/Auth/AdminLoginController.php`.
- Login driver mobile diproses oleh `app/Http/Controllers/Api/AuthController.php` dan menghasilkan token Sanctum.
- Check-in, check-out, dan sinkronisasi offline diproses oleh `app/Http/Controllers/Api/AttendanceController.php`.
- Data offline recovery disimpan pada kolom metadata di `attendances` dan dicatat di tabel `offline_recovery_logs`.
- Nilai KM kendaraan tidak selalu disimpan ulang ke `vehicles.current_km`; accessor `Vehicle::computed_km` mengambil prioritas dari `latestAttendance.speedo_akhir`, lalu `speedo_awal`, lalu `current_km`.
