# Sequence Diagram Alur Inti Absensi

Dokumen ini berisi empat sequence diagram utama untuk deskripsi sistem: login dan role redirect, check-in driver, check-out dan update KM, serta sinkronisasi offline.

## 1. Login dan Role Redirect

Diagram ini menggambarkan alur login web untuk admin, service admin, dan customer. Role menentukan halaman tujuan setelah autentikasi berhasil.

```mermaid
sequenceDiagram
    actor User
    participant Browser as Web Browser
    participant Route as Laravel Route
    participant Controller as AdminLoginController
    participant RateLimiter as RateLimiter
    participant Auth as Auth Guard
    participant Session as Session
    participant DB as Database

    User->>Browser: Buka halaman login
    Browser->>Route: GET /admin/login
    Route->>Controller: showLoginForm()
    Controller-->>Browser: Render auth.admin-login

    User->>Browser: Isi email dan password
    Browser->>Route: POST /admin/login
    Route->>Controller: login(request)
    Controller->>Controller: Validasi email dan password
    Controller->>RateLimiter: Cek percobaan login

    alt Terlalu banyak percobaan
        RateLimiter-->>Controller: Locked out
        Controller-->>Browser: Redirect back + pesan throttle
        Browser-->>User: Tampilkan error login
    else Percobaan masih diizinkan
        Controller->>Auth: attempt(credentials, remember)
        Auth->>DB: SELECT user by email
        DB-->>Auth: User record / null
        Auth-->>Controller: Login berhasil / gagal

        alt Login gagal
            Controller->>RateLimiter: Hit attempt gagal
            Controller-->>Browser: Redirect back + error generic
            Browser-->>User: Email atau password salah
        else Login berhasil
            Controller->>RateLimiter: Clear throttle key
            Controller->>Session: Regenerate session ID
            Controller->>Auth: Ambil user login

            alt role = master
                Controller-->>Browser: Redirect ke /admin/dashboard
            else role = service_admin
                Controller-->>Browser: Redirect ke /admin/service
            else role = customer
                Controller-->>Browser: Redirect ke customer dashboard
            else role lain
                Controller-->>Browser: Redirect default ke /admin/dashboard
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
    participant API as AttendanceController
    participant Auth as Sanctum Auth
    participant ImageSvc as ImageProcessingService
    participant Attendance as Attendance Model
    participant DriverModel as Driver Model
    participant Vehicle as Vehicle Model
    participant DB as Database

    Driver->>App: Input plat/QR, GPS, timestamp, KM awal, foto
    App->>API: POST /api/submit-attendance
    API->>Auth: Ambil driver dari token
    Auth-->>API: Driver aktif

    API->>API: Normalisasi plat dan metode kendaraan
    API->>API: Validasi request

    alt Data tidak valid
        API-->>App: 422 Validation error
        App-->>Driver: Tampilkan pesan validasi
    else Data valid
        API->>DB: Cek drivers.is_on_duty
        DB-->>API: Status driver
        API->>Attendance: Cek attendance aktif
        Attendance->>DB: SELECT where driver_id and time_out is null
        DB-->>Attendance: Ada / tidak ada

        alt Driver sudah bertugas
            API-->>App: 409 Driver sudah clock-in
            App-->>Driver: Check-in ditolak
        else Driver belum bertugas
            API->>Vehicle: Cari vehicle by plate_number
            Vehicle->>DB: SELECT vehicles
            DB-->>Vehicle: Vehicle / null
            Vehicle-->>API: Hasil pencarian

            alt Kendaraan tidak ditemukan dan metode QR
                API-->>App: 404 Plat tidak dikenal
                App-->>Driver: Hubungi admin
            else Kendaraan manual atau valid
                API->>API: Cek status kendaraan bisa digunakan

                alt Kendaraan tidak dapat digunakan
                    API-->>App: 409 Unit tidak dapat digunakan
                    App-->>Driver: Check-in ditolak
                else Kendaraan dapat digunakan
                    API->>ImageSvc: Optimize selfie dan foto speedometer
                    ImageSvc-->>API: Path foto tersimpan
                    opt Input kendaraan manual
                        API->>ImageSvc: Optimize foto kendaraan manual
                        ImageSvc-->>API: Path foto manual
                    end

                    API->>DB: BEGIN TRANSACTION
                    API->>Attendance: Lock attendance aktif driver
                    Attendance->>DB: SELECT FOR UPDATE
                    DB-->>Attendance: Tidak ada attendance aktif
                    API->>Vehicle: Lock vehicle by plate_number
                    Vehicle->>DB: SELECT FOR UPDATE
                    DB-->>Vehicle: Vehicle / null

                    opt Kendaraan manual belum ada
                        API->>Vehicle: Create temporary vehicle
                        Vehicle->>DB: INSERT vehicles status Pending Verifikasi
                        DB-->>Vehicle: Vehicle baru
                    end

                    API->>Attendance: Create attendance
                    Attendance->>DB: INSERT attendances
                    Note over DB: time_in, gps_location_in,<br/>speedo_awal, foto awal,<br/>vehicle_entry_method

                    API->>DriverModel: Update is_on_duty=true
                    DriverModel->>DB: UPDATE drivers
                    API->>DB: COMMIT

                    API->>API: Clear cache status/history driver
                    API-->>App: 200 Check-in berhasil
                    App-->>Driver: Tampilkan status bertugas
                end
            end
        end
    end
```

## 3. Check-Out dan Update KM

Diagram ini menggambarkan alur driver mengakhiri tugas. Data KM akhir disimpan di attendance, lalu nilai KM kendaraan dibaca dari attendance terakhir melalui accessor `computed_km`.

```mermaid
sequenceDiagram
    actor Driver
    participant App as Mobile App
    participant API as AttendanceController
    participant Auth as Sanctum Auth
    participant ImageSvc as ImageProcessingService
    participant Attendance as Attendance Model
    participant DriverModel as Driver Model
    participant Vehicle as Vehicle Model
    participant DB as Database

    Driver->>App: Input KM akhir, GPS, checklist, catatan, foto speedometer
    App->>API: POST /api/submit-end-of-duty
    API->>Auth: Ambil driver dari token
    Auth-->>API: Driver aktif
    API->>API: Validasi request

    alt Data tidak valid
        API-->>App: 422 Validation error
        App-->>Driver: Tampilkan error
    else Data valid
        API->>DriverModel: Cari driver
        DriverModel->>DB: SELECT drivers
        DB-->>DriverModel: Driver record

        API->>Attendance: Cari attendance aktif dengan vehicle
        Attendance->>DB: SELECT where driver_id and time_out is null
        DB-->>Attendance: Active attendance / null

        alt Tidak ada tugas aktif
            API-->>App: 404 Tidak ada tugas aktif
            App-->>Driver: Check-out ditolak
        else Ada tugas aktif
            API->>ImageSvc: Optimize foto speedometer akhir
            ImageSvc-->>API: Path foto akhir

            API->>DB: BEGIN TRANSACTION
            API->>Attendance: Update laporan akhir tugas
            Attendance->>DB: UPDATE attendances
            Note over DB: time_out, gps_location_out,<br/>speedo_akhir, checklist ban/lampu/rem,<br/>catatan, foto akhir

            API->>DriverModel: Update is_on_duty=false
            DriverModel->>DB: UPDATE drivers
            API->>DB: COMMIT

            API->>API: Clear cache status/history driver
            API->>API: Hitung durasi kerja dan total KM

            opt Saat data kendaraan atau maintenance dibaca
                API->>Vehicle: Akses computed_km
                Vehicle->>DB: Load latestAttendance
                DB-->>Vehicle: Attendance terakhir
                Note over Vehicle: computed_km memakai speedo_akhir<br/>dari attendance terakhir
            end

            API-->>App: 200 Ringkasan check-out
            App-->>Driver: Tampilkan durasi, total KM, dan kondisi unit
        end
    end
```

## 4. Sinkronisasi Offline

Diagram ini menggambarkan alur clock-out yang disimpan di perangkat saat offline, lalu dikirim ke server setelah koneksi kembali tersedia.

```mermaid
sequenceDiagram
    actor Driver
    participant App as Mobile App
    participant LocalDB as Local Offline DB
    participant API as AttendanceController
    participant Auth as Sanctum Auth
    participant ImageSvc as ImageProcessingService
    participant Attendance as Attendance Model
    participant DriverModel as Driver Model
    participant RecoveryLog as OfflineRecoveryLog Model
    participant DB as Database

    Driver->>App: Check-out saat koneksi offline
    App->>LocalDB: Simpan data offline
    Note over LocalDB: offline_entry_id,<br/>device_timestamp,<br/>KM akhir, checklist,<br/>GPS, foto speedometer

    App->>App: Pantau koneksi internet
    App->>API: POST /api/clock-out-offline
    API->>Auth: Validasi token Sanctum
    Auth-->>API: Driver aktif
    API->>API: Validasi payload offline

    alt Payload tidak valid
        API-->>App: 422 Validation error
        App->>LocalDB: Tandai perlu diperbaiki / retry
    else Payload valid
        API->>Attendance: Cek offline_entry_id
        Attendance->>DB: SELECT by offline_entry_id
        DB-->>Attendance: Existing attendance / null

        alt Data sudah pernah tersimpan dan time_out terisi
            API-->>App: 200 Idempotent success
            App->>LocalDB: Tandai data sudah sinkron
        else offline_entry_id konflik
            API-->>App: 409 Duplicate conflict
            App->>LocalDB: Tandai konflik sinkronisasi
        else Belum pernah diproses
            API->>Attendance: Cari attendance aktif driver
            Attendance->>DB: SELECT where driver_id and time_out is null
            DB-->>Attendance: Active attendance / null

            alt Tidak ada attendance aktif
                API->>RecoveryLog: Catat failed NO_ACTIVE_ATTENDANCE
                RecoveryLog->>DB: INSERT offline_recovery_logs
                API-->>App: 404 Tidak ada tugas aktif
                App->>LocalDB: Tandai gagal sinkron
            else Attendance aktif ditemukan
                API->>DriverModel: Cek is_on_duty
                DriverModel->>DB: SELECT drivers
                DB-->>DriverModel: Driver status

                alt Driver tidak sedang bertugas menurut server
                    API->>RecoveryLog: Catat failed DRIVER_NOT_ON_DUTY
                    RecoveryLog->>DB: INSERT offline_recovery_logs
                    API-->>App: 409 Driver not on duty
                    App->>LocalDB: Tandai gagal sinkron
                else Status valid
                    API->>API: Parse device_timestamp
                    API->>API: Hitung delay dan late submission
                    API->>ImageSvc: Optimize foto speedometer akhir
                    ImageSvc-->>API: Path foto akhir

                    API->>DB: BEGIN TRANSACTION
                    API->>Attendance: Update attendance dengan data offline
                    Attendance->>DB: UPDATE attendances
                    Note over DB: time_out=device_timestamp,<br/>is_offline_recovery=true,<br/>recovery_timestamp=server time,<br/>offline_entry_id, is_late_submission

                    API->>DriverModel: Update is_on_duty=false
                    DriverModel->>DB: UPDATE drivers
                    API->>DB: COMMIT

                    API->>RecoveryLog: Catat success
                    RecoveryLog->>DB: INSERT offline_recovery_logs
                    API->>API: Clear cache status/history driver
                    API-->>App: 200 Sinkronisasi berhasil
                    App->>LocalDB: Hapus / tandai entry sudah sinkron
                    App-->>Driver: Tampilkan ringkasan check-out offline
                end
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
