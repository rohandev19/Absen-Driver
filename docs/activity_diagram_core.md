# Activity Diagram Alur Utama Sistem

Dokumen ini berisi activity diagram utama untuk kebutuhan skripsi: login, check-in driver, check-out driver, sinkronisasi offline, dan preventive maintenance.

## 1. Activity Diagram Login

```mermaid
flowchart TD
    A([Mulai]) --> B[User membuka halaman login]
    B --> C[User mengisi email dan password]
    C --> D[Submit form login]
    D --> E{Data input valid?}

    E -- Tidak --> F[Tampilkan error validasi]
    F --> C

    E -- Ya --> G{Percobaan login melewati batas?}
    G -- Ya --> H[Tampilkan pesan terlalu banyak percobaan]
    H --> Z([Selesai])

    G -- Tidak --> I[Proses autentikasi]
    I --> J{Email dan password cocok?}

    J -- Tidak --> K[Catat percobaan gagal]
    K --> L[Tampilkan pesan login gagal]
    L --> C

    J -- Ya --> M[Regenerasi session]
    M --> N{Role user}

    N -- Master --> O[Arahkan ke dashboard admin]
    N -- Service Admin --> P[Arahkan ke halaman service]
    N -- Customer --> Q[Arahkan ke dashboard customer]
    N -- Lainnya --> R[Arahkan ke dashboard default]

    O --> Z
    P --> Z
    Q --> Z
    R --> Z
```

## 2. Activity Diagram Check-In Driver

```mermaid
flowchart TD
    A([Mulai]) --> B[Driver membuka aplikasi mobile]
    B --> C[Driver memilih check-in]
    C --> D{Metode input kendaraan}

    D -- QR Code --> E[Scan QR kendaraan]
    D -- Manual --> F[Input plat kendaraan, alasan, dan foto kendaraan]

    E --> G[Ambil GPS, timestamp, KM awal, selfie, dan foto speedometer]
    F --> G

    G --> H[Kirim data check-in ke server]
    H --> I{Token driver valid?}

    I -- Tidak --> J[Tampilkan pesan unauthenticated]
    J --> Z([Selesai])

    I -- Ya --> K[Validasi data check-in]
    K --> L{Data valid?}

    L -- Tidak --> M[Tampilkan error validasi]
    M --> G

    L -- Ya --> N{Driver sedang bertugas?}
    N -- Ya --> O[Tolak check-in karena sudah clock-in]
    O --> Z

    N -- Tidak --> P[Cari kendaraan berdasarkan plat]
    P --> Q{Kendaraan ditemukan?}

    Q -- Tidak, metode QR --> R[Tolak karena plat tidak dikenal]
    R --> Z

    Q -- Tidak, metode manual --> S[Buat kendaraan sementara menunggu verifikasi]
    Q -- Ya --> T{Status kendaraan boleh digunakan?}

    S --> U[Optimasi dan simpan foto]
    T -- Tidak --> V[Tolak karena unit tidak dapat digunakan]
    V --> Z

    T -- Ya --> U
    U --> W[Mulai transaksi database]
    W --> X[Simpan attendance masuk]
    X --> Y[Set driver is_on_duty = true]
    Y --> AA[Commit transaksi]
    AA --> AB[Hapus cache status dan riwayat driver]
    AB --> AC[Tampilkan check-in berhasil]
    AC --> Z
```

## 3. Activity Diagram Check-Out Driver

```mermaid
flowchart TD
    A([Mulai]) --> B[Driver membuka aplikasi mobile]
    B --> C[Driver memilih check-out]
    C --> D[Input KM akhir, GPS, checklist ban, lampu, rem, catatan, dan foto speedometer]
    D --> E[Kirim data check-out ke server]
    E --> F{Token driver valid?}

    F -- Tidak --> G[Tampilkan pesan unauthenticated]
    G --> Z([Selesai])

    F -- Ya --> H[Validasi data check-out]
    H --> I{Data valid?}

    I -- Tidak --> J[Tampilkan error validasi]
    J --> D

    I -- Ya --> K[Cari data driver]
    K --> L{Driver ditemukan?}

    L -- Tidak --> M[Tampilkan error data driver tidak ditemukan]
    M --> Z

    L -- Ya --> N[Cari attendance aktif]
    N --> O{Ada tugas aktif?}

    O -- Tidak --> P[Tampilkan pesan tidak ada tugas aktif]
    P --> Z

    O -- Ya --> Q[Optimasi dan simpan foto speedometer akhir]
    Q --> R[Mulai transaksi database]
    R --> S[Update attendance dengan time_out, KM akhir, GPS, checklist, dan catatan]
    S --> T[Set driver is_on_duty = false]
    T --> U[Commit transaksi]
    U --> V[Hapus cache status dan riwayat driver]
    V --> W[Hitung durasi kerja dan total KM]
    W --> X[Tentukan status kondisi kendaraan dari checklist]
    X --> Y[Tampilkan ringkasan check-out]
    Y --> Z
```

## 4. Activity Diagram Sinkronisasi Offline

```mermaid
flowchart TD
    A([Mulai]) --> B[Driver melakukan check-out saat offline]
    B --> C[Simpan data check-out ke local offline database]
    C --> D[Tandai data sebagai belum sinkron]
    D --> E{Koneksi internet tersedia?}

    E -- Tidak --> F[Tunggu dan cek koneksi kembali]
    F --> E

    E -- Ya --> G[Kirim data ke endpoint clock-out offline]
    G --> H{Token driver valid?}

    H -- Tidak --> I[Tandai sync gagal karena token tidak valid]
    I --> Z([Selesai])

    H -- Ya --> J[Validasi payload offline]
    J --> K{Payload valid?}

    K -- Tidak --> L[Tandai data perlu diperbaiki atau dikirim ulang]
    L --> Z

    K -- Ya --> M[Cek offline_entry_id]
    M --> N{offline_entry_id sudah pernah diproses?}

    N -- Ya, data sudah selesai --> O[Kembalikan idempotent success]
    O --> P[Tandai data lokal sudah sinkron]
    P --> Z

    N -- Ya, konflik --> Q[Tandai konflik sinkronisasi]
    Q --> Z

    N -- Belum --> R[Cari attendance aktif driver]
    R --> S{Attendance aktif ditemukan?}

    S -- Tidak --> T[Catat log recovery gagal]
    T --> U[Tandai sync gagal]
    U --> Z

    S -- Ya --> V{Driver masih on duty menurut server?}
    V -- Tidak --> W[Catat log recovery gagal]
    W --> U

    V -- Ya --> X[Parse device_timestamp]
    X --> Y[Hitung delay dan status late submission]
    Y --> AA[Optimasi foto speedometer akhir]
    AA --> AB[Mulai transaksi database]
    AB --> AC[Update attendance dengan data offline]
    AC --> AD[Set driver is_on_duty = false]
    AD --> AE[Commit transaksi]
    AE --> AF[Catat log recovery sukses]
    AF --> AG[Hapus cache status dan riwayat driver]
    AG --> AH[Tandai data lokal sudah sinkron]
    AH --> AI[Tampilkan ringkasan sync berhasil]
    AI --> Z
```

## 5. Activity Diagram Preventive Maintenance

```mermaid
flowchart TD
    A([Mulai]) --> B[Scheduler menjalankan proses preventive maintenance]
    B --> C[Ambil data kendaraan beserta komponen]
    C --> D[Periksa setiap komponen kendaraan]
    D --> E[Hitung status komponen dari KM atau tanggal penggantian]
    E --> F{Status komponen}

    F -- Healthy --> G[Tidak perlu maintenance]
    G --> H{Masih ada komponen lain?}

    F -- Warning --> I[Tandai komponen perlu perhatian]
    F -- Critical --> J[Tandai komponen perlu segera diganti]
    F -- Overdue --> K[Tandai komponen terlambat diganti]

    I --> L[Cek alert aktif untuk komponen]
    J --> L
    K --> L

    L --> M{Alert aktif sudah ada?}
    M -- Tidak --> N[Buat maintenance alert]
    M -- Ya --> O[Lewati pembuatan alert duplikat]

    N --> P[Cek jadwal maintenance aktif]
    O --> P

    P --> Q{Jadwal pending atau scheduled sudah ada?}
    Q -- Ya --> R[Lewati pembuatan jadwal duplikat]
    Q -- Tidak --> S[Tentukan prioritas jadwal]

    S --> T[Hitung tanggal jadwal preventive]
    T --> U[Buat maintenance schedule type preventive]
    U --> V[Simpan jadwal dengan status pending]

    R --> H
    V --> H

    H -- Ya --> D
    H -- Tidak --> W[Hitung ringkasan alert dan jadwal]
    W --> X[Tampilkan atau simpan hasil proses]
    X --> Z([Selesai])
```

## Catatan Implementasi

- Login web diproses oleh `app/Http/Controllers/Auth/AdminLoginController.php`.
- Check-in, check-out, dan sinkronisasi offline diproses oleh `app/Http/Controllers/Api/AttendanceController.php`.
- Preventive maintenance melibatkan `VehicleComponent`, `MaintenanceAlertService`, dan command `maintenance:generate-schedules`.
- Activity diagram ini dibuat sebagai versi ringkas untuk skripsi, sehingga fokus pada keputusan bisnis utama dan tidak menampilkan detail teknis kecil seperti semua field request.
