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
    A([Mulai]) --> B[Driver membuka aplikasi dan scan QR driver]
    B --> C{Metode input kendaraan}

    C -- QR Code --> D[Scan QR kendaraan]
    C -- Manual --> E[Input plat dan alasan penggantian unit]

    D --> F[Ambil GPS, KM awal, selfie, dan foto speedometer]
    E --> F

    F --> G[Kirim data check-in ke server]
    G --> H{Autentikasi dan validasi berhasil?}

    H -- Tidak --> I[Tampilkan pesan error]
    I --> F

    H -- Ya --> J{Driver sedang bertugas?}
    J -- Ya --> K[Tolak check-in]
    K --> Z([Selesai])

    J -- Tidak --> L[Cari kendaraan berdasarkan plat]
    L --> M{Kendaraan ditemukan?}

    M -- Tidak, QR --> N[Tolak karena plat tidak dikenal]
    N --> Z

    M -- Tidak, Manual --> O[Buat kendaraan sementara]
    M -- Ya --> P{Status kendaraan tersedia?}

    P -- Tidak --> Q[Tolak karena unit tidak dapat digunakan]
    Q --> Z

    P -- Ya --> R[Simpan data attendance dan update status driver]
    O --> R
    R --> S[Tampilkan check-in berhasil]
    S --> Z
```

## 3. Activity Diagram Check-Out Driver

```mermaid
flowchart TD
    A([Mulai]) --> B[Driver membuka aplikasi dan memilih check-out]
    B --> C[Input KM akhir, checklist kondisi kendaraan, catatan, dan foto speedometer]
    C --> D[Ambil GPS dan kirim data ke server]
    D --> E{Autentikasi dan validasi berhasil?}

    E -- Tidak --> F[Tampilkan pesan error]
    F --> C

    E -- Ya --> G{Ada tugas aktif?}
    G -- Tidak --> H[Tampilkan pesan tidak ada tugas aktif]
    H --> Z([Selesai])

    G -- Ya --> I[Update attendance dan set driver off duty]
    I --> J[Hitung durasi kerja, total KM, dan status kendaraan]
    J --> K[Tampilkan ringkasan check-out]
    K --> Z
```

## 4. Activity Diagram Sinkronisasi Offline

```mermaid
flowchart TD
    A([Mulai]) --> B[Driver check-out saat offline]
    B --> C[Simpan data ke local database]
    C --> D{Koneksi internet tersedia?}

    D -- Tidak --> E[Tunggu koneksi]
    E --> D

    D -- Ya --> F[Kirim data ke server]
    F --> G{Autentikasi dan validasi berhasil?}

    G -- Tidak --> H[Tandai sync gagal]
    H --> Z([Selesai])

    G -- Ya --> I{Data sudah pernah diproses?}
    I -- Ya --> J[Tandai data lokal sudah sinkron]
    J --> Z

    I -- Belum --> K{Driver masih bertugas di server?}
    K -- Tidak --> L[Tandai sync gagal]
    L --> Z

    K -- Ya --> M[Update attendance dan set driver off duty]
    M --> N[Tandai data lokal sudah sinkron]
    N --> O[Tampilkan ringkasan sync berhasil]
    O --> Z
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
