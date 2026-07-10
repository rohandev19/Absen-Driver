# Flowchart Sistem Absensi dan Manajemen Armada

Dokumen ini berisi flowchart gambaran umum sistem untuk bab awal skripsi.

## 1. Flowchart Alur Sistem Secara Umum

```mermaid
flowchart TD
    A([Mulai]) --> B{Jenis Pengguna}

    B -- Driver --> C[Login Mobile App]
    B -- Admin / Service Admin --> D[Login Web Dashboard]
    B -- Customer --> E[Login Portal Customer]

    C --> F{Pilih Fitur Driver}
    F --> G[Check-In]
    F --> H[Check-Out]
    F --> I[Laporan Darurat]
    F --> J[Service Report]
    F --> K[Transport Cost]

    G --> L{Online?}
    L -- Ya --> M[Kirim ke Server]
    L -- Tidak --> N[Simpan Offline]
    N --> O[Sinkronisasi Otomatis]
    O --> M

    H --> L

    M --> P[Server Proses dan Simpan Data]

    I --> P
    J --> P
    K --> P

    D --> Q{Pilih Menu Admin}
    Q --> R[Monitoring Absensi dan Export Laporan]
    Q --> S[Kelola Data Master]
    Q --> T[Manajemen Maintenance]
    Q --> U[Proses Approval]

    S --> P
    T --> V[Scheduler Otomatis]
    V --> W[Cek Status Komponen Kendaraan]
    W --> X{Perlu Maintenance?}
    X -- Ya --> Y[Buat Alert dan Jadwal Preventive]
    X -- Tidak --> Z[Tidak Ada Aksi]

    Y --> AA[Kirim Notifikasi ke Admin]

    E --> AB{Pilih Menu Customer}
    AB --> AC[Lihat Dashboard dan Kendaraan]
    AB --> AD[Review dan Approve Service Report]

    P --> AE[Data Tersimpan di Database]
    R --> AE
    U --> AE
    AC --> AE
    AD --> AE

    AE --> AF([Selesai])
    Z --> AF
    AA --> AF
```

## 2. Flowchart Alur Data Sistem

```mermaid
flowchart LR
    subgraph Mobile["Mobile App (Flutter)"]
        A1[Check-In / Check-Out]
        A2[Laporan Darurat]
        A3[Service Report]
        A4[Transport Cost]
        A5[Offline Database]
    end

    subgraph Server["Server (Laravel)"]
        B1[REST API]
        B2[Web Dashboard]
        B3[Scheduler]
    end

    subgraph Database["Database (MySQL)"]
        C1[(Attendance)]
        C2[(Vehicles)]
        C3[(Maintenance)]
        C4[(Reports)]
    end

    subgraph External["Layanan Eksternal"]
        D1[Firebase FCM]
        D2[GPS Location]
    end

    A1 --> B1
    A2 --> B1
    A3 --> B1
    A4 --> B1
    A1 -.->|Offline| A5
    A5 -.->|Sync| B1

    B1 --> C1
    B1 --> C2
    B1 --> C4

    B2 --> C1
    B2 --> C2
    B2 --> C3
    B2 --> C4

    B3 --> C2
    B3 --> C3
    B3 --> D1

    D2 -.-> A1
```

## Catatan

- Flowchart #1 cocok untuk **Bab 3 (Analisis Sistem)** sebagai gambaran umum sebelum masuk ke activity/sequence diagram.
- Flowchart #2 cocok untuk **Bab 4 (Perancangan)** untuk menjelaskan arsitektur alur data antar komponen.
- Kedua diagram sudah cukup ringkas untuk ditaruh di halaman A4 Word.
