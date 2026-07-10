# 4.1.3 Unified Modeling Language (UML)

Semua diagram sudah disesuaikan untuk ukuran **Microsoft Word (A4)**. Diagram besar dipecah menjadi beberapa gambar.

> [!TIP]
> **Total: 13 gambar** — siap di-screenshot/export satu per satu ke Word.

---

## 4.1.3.1 Use Case Diagram

Dipecah menjadi **2 gambar** agar tidak terlalu lebar di Word.

### Gambar 4.1 — Use Case Diagram (Sisi Driver)

```mermaid
graph LR
    D((Driver))

    UC1[Login]
    UC2[Check-In]
    UC3[Check-Out]
    UC4[Check-Out Offline]
    UC5[Sinkronisasi Offline]
    UC6[Lihat Riwayat Absensi]
    UC7[Laporan Darurat]
    UC8[Input Biaya Transportasi]
    UC9[Laporan Service Kendaraan]
    UC10[Scan QR Code Kendaraan]
    UC11[Lihat Panduan]
    UC12[Ganti Password]

    D --- UC1
    D --- UC2
    D --- UC3
    D --- UC4
    D --- UC5
    D --- UC6
    D --- UC7
    D --- UC8
    D --- UC9
    D --- UC10
    D --- UC11
    D --- UC12
```

### Gambar 4.2 — Use Case Diagram (Sisi Admin, Customer & Scheduler)

```mermaid
graph LR
    A((Admin))
    SA((Service Admin))
    C((Customer))
    S((Scheduler))

    UC1[Login]
    UC10[Kelola Driver]
    UC11[Kelola Kendaraan]
    UC12[Kelola Komponen]
    UC13[Kelola Jadwal Maintenance]
    UC14[Selesaikan Maintenance]
    UC15[Dashboard Maintenance]
    UC16[Approval Service Report]
    UC17[Dashboard Admin]
    UC18[Export Laporan]
    UC19[Generate Alert]
    UC20[Update Status Komponen]
    UC21[Generate Jadwal Otomatis]
    UC22[Notifikasi Push]

    A --- UC1
    A --- UC10
    A --- UC11
    A --- UC12
    A --- UC13
    A --- UC14
    A --- UC15
    A --- UC16
    A --- UC17
    A --- UC18

    SA --- UC1
    SA --- UC14
    SA --- UC16

    C --- UC1
    C --- UC16

    S --- UC19
    S --- UC20
    S --- UC21
    S --- UC22
```

---

## 4.1.3.2 Activity Diagram Check-In dan Check-Out Driver

### Gambar 4.3 — Activity Diagram Check-In

```mermaid
flowchart TD
    A([Start]) --> B{Sudah login?}
    B -- Tidak --> C[Login dengan NIK & password]
    C --> B
    B -- Ya --> D[Tekan tombol Check-In]
    D --> E{Metode input kendaraan}
    E -- QR Code --> F[Scan QR Code]
    E -- Manual --> G[Input plat + alasan + foto]
    F --> H[Ambil GPS, selfie, foto speedo, input KM]
    G --> H
    H --> I{Ada internet?}
    I -- Ya --> J[Kirim ke server]
    J --> K{Driver sudah on duty?}
    K -- Ya --> L[Error: Sudah clock-in]
    K -- Tidak --> M{Kendaraan valid?}
    M -- Tidak --> N[Error: Kendaraan tidak ditemukan]
    M -- Ya --> O[Simpan Attendance + set on_duty=true]
    O --> P[Sukses]
    I -- Tidak --> Q[Simpan ke local DB offline]
    Q --> R[Masuk offline queue]
    P --> S([End])
    R --> S
    L --> S
    N --> S
```

### Gambar 4.4 — Activity Diagram Check-Out

```mermaid
flowchart TD
    A([Start]) --> B[Tekan tombol Check-Out]
    B --> C[Input KM akhir + foto speedo]
    C --> D[Checklist: Ban, Lampu, Rem]
    D --> E[Input catatan opsional]
    E --> F{Ada internet?}
    F -- Ya --> G[Kirim ke server]
    G --> H{Ada attendance aktif?}
    H -- Tidak --> I[Error: Tidak ada tugas aktif]
    H -- Ya --> J[Update attendance + set on_duty=false]
    J --> K[Hitung durasi & total KM]
    K --> L[Tampilkan Duty Summary]
    F -- Tidak --> M[Simpan ke local DB]
    M --> N[Generate UUID offline_entry_id]
    N --> O[Catat device_timestamp]
    L --> P([End])
    O --> P
    I --> P
```

---

## 4.1.3.3 Activity Diagram Preventive Maintenance Armada

### Gambar 4.5 — Activity Diagram Preventive Maintenance

```mermaid
flowchart TD
    A([Start]) --> B[Cron trigger harian 00:00]
    B --> C[Ambil semua komponen kendaraan]
    C --> D{Untuk setiap komponen}
    D --> E[Hitung sisa KM dan sisa hari]

    E --> F{Sisa KM <= 0?}
    F -- Ya --> G[Status = OVERDUE]
    F -- Tidak --> H{Sisa KM <= critical?}
    H -- Ya --> I[Status = CRITICAL]
    H -- Tidak --> J{Sisa KM <= warning?}
    J -- Ya --> K[Status = WARNING]
    J -- Tidak --> L[Status = HEALTHY]

    G --> M[Simpan status]
    I --> M
    K --> M
    L --> M

    M --> N{Komponen lain?}
    N -- Ya --> D
    N -- Tidak --> O[Generate alert untuk komponen tidak sehat]
    O --> P[Generate jadwal maintenance otomatis]
    P --> Q[Kirim notifikasi ke Admin]
    Q --> R([End])
```

---

## 4.1.3.4 Activity Diagram Sinkronisasi Offline

### Gambar 4.6 — Activity Diagram Sinkronisasi Offline

```mermaid
flowchart TD
    A([Start]) --> B{Trigger sync}
    B --> B1[Online terdeteksi]
    B --> B2[Timer 2 menit]
    B --> B3[App dibuka]
    B --> B4[Manual refresh]

    B1 --> C{Ada internet?}
    B2 --> C
    B3 --> C
    B4 --> C
    C -- Tidak --> D[Sync ditunda]
    D --> Z([End])
    C -- Ya --> E{Ada data pending?}
    E -- Tidak --> F[Tidak ada data]
    F --> Z
    E -- Ya --> G[Acquire sync lock]

    G --> H[Proses 5 queue berurutan]
    H --> H1["1. Check-In → /submit-attendance"]
    H1 --> H2["2. Check-Out → /submit-end-of-duty"]
    H2 --> H3["3. Emergency → /submit-emergency-report"]
    H3 --> H4["4. Service → /submit-service-report"]
    H4 --> H5["5. Offline Clock-Out → /clock-out-offline"]

    H5 --> I{Untuk setiap item}
    I --> J{Dalam backoff?}
    J -- Ya --> K[Skip item]
    J -- Tidak --> L[Kirim ke server]
    L --> M{Response?}
    M -- Sukses --> N[Hapus dari local DB]
    M -- 409 Duplikat --> N
    M -- Gagal --> O{Retry >= 3?}
    O -- Ya --> P[Mark FAILED]
    O -- Tidak --> Q[Retry + backoff]

    N --> R{Item lain?}
    K --> R
    P --> R
    Q --> R
    R -- Ya --> I
    R -- Tidak --> S[Release sync lock]
    S --> Z
```

---

## 4.1.3.5 Sequence Diagram Check-Out sampai Update KM

### Gambar 4.7 — Sequence Diagram Check-Out & Update KM

```mermaid
sequenceDiagram
    actor Driver
    participant App as Mobile App
    participant Server as API Server
    participant DB as Database

    Driver->>App: Input KM akhir + foto + checklist
    App->>Server: POST /submit-end-of-duty

    Server->>DB: Cari attendance aktif
    DB-->>Server: Attendance record

    alt Tidak ada tugas aktif
        Server-->>App: 404 Error
        App-->>Driver: Tidak ada tugas aktif
    else Ada tugas aktif
        Server->>Server: Optimize foto speedo
        Server->>DB: BEGIN TRANSACTION
        Server->>DB: UPDATE attendance (speedo_akhir, kondisi)
        Server->>DB: UPDATE driver (is_on_duty=false)
        Server->>DB: COMMIT

        Note over DB: Vehicle.computed_km<br/>otomatis ambil speedo_akhir<br/>dari latestAttendance

        Server->>Server: Hitung durasi & total KM
        Server-->>App: 200 OK + Summary
        App-->>Driver: Tampilkan ringkasan tugas
    end
```

---

## 4.1.3.6 Sequence Diagram Generate Maintenance Alert

### Gambar 4.8 — Sequence Diagram Generate Maintenance Alert

```mermaid
sequenceDiagram
    participant Cron as Scheduler
    participant Svc as AlertService
    participant DB as Database

    Note over Cron: Trigger setiap 6 jam
    Cron->>Svc: generate-alerts

    Svc->>DB: Ambil semua komponen + kendaraan
    DB-->>Svc: Daftar komponen

    loop Setiap komponen
        Svc->>Svc: Hitung sisa KM

        alt Sisa KM <= 0
            Svc->>DB: Cek alert overdue aktif?
            DB-->>Svc: Tidak ada
            Svc->>DB: INSERT alert (type=overdue)
        else Sisa KM <= critical
            Svc->>DB: Cek alert critical aktif?
            DB-->>Svc: Tidak ada
            Svc->>DB: INSERT alert (type=critical)
        else Sisa KM <= warning
            Svc->>DB: Cek alert warning aktif?
            DB-->>Svc: Tidak ada
            Svc->>DB: INSERT alert (type=warning)
        end
    end

    Svc-->>Cron: Selesai, log jumlah alert
```

---

## 4.1.3.7 Sequence Diagram Penyelesaian Maintenance

### Gambar 4.9 — Sequence Diagram Penyelesaian Maintenance

```mermaid
sequenceDiagram
    actor Admin
    participant Web as Web Browser
    participant Ctrl as Controller
    participant DB as Database
    participant PDF as PDF Service

    Admin->>Web: Klik Selesaikan Maintenance
    Web->>Ctrl: POST /schedules/{id}/complete
    Note over Web: Data: biaya, foto kuitansi,<br/>foto odometer, tanda tangan

    Ctrl->>Ctrl: Validasi input
    Ctrl->>Ctrl: Simpan signature & optimize foto

    Ctrl->>DB: UPDATE schedule (status=completed)
    Note over DB: actual_cost, completed_at,<br/>foto paths, signature path

    Ctrl->>DB: UPDATE komponen
    Note over DB: last_replacement_km,<br/>last_replacement_date,<br/>recalculate next replacement

    Ctrl->>PDF: Generate dokumen finance
    PDF-->>Ctrl: PDF path
    Ctrl->>DB: UPDATE schedule (finance_pdf_path)

    Ctrl-->>Web: Redirect + sukses
    Web-->>Admin: Maintenance selesai
```

---

## 4.1.3.8 Class Diagram

Dipecah menjadi **3 gambar** berdasarkan domain.

### Gambar 4.10 — Class Diagram: Domain Absensi (Core)

```mermaid
classDiagram
    class Driver {
        +int id
        +string full_name
        +string driver_id_nik
        +string nik_ktp
        +date sim_expiry_date
        +string password
        +int project_id
        +bool is_on_duty
        +string fcm_token
        +isOnDuty() bool
    }

    class Vehicle {
        +int id
        +string plate_number
        +string type
        +int project_id
        +string status
        +int current_km
        +int service_interval_km
        +getComputedKmAttribute() int
        +getHealthStatusCodeAttribute() string
    }

    class Attendance {
        +int id
        +int driver_id
        +int vehicle_id
        +string vehicle_entry_method
        +datetime time_in
        +datetime time_out
        +int speedo_awal
        +int speedo_akhir
        +string check_ban
        +string check_lampu
        +string check_rem
        +bool is_offline_recovery
        +string offline_entry_id
    }

    class Project {
        +int id
        +string name
        +string code
        +int customer_id
    }

    class Customer {
        +int id
        +string name
        +string code
        +string contact_person
        +string email
    }

    Customer "1" --> "*" Project : memiliki
    Project "1" --> "*" Driver : memiliki
    Project "1" --> "*" Vehicle : memiliki
    Driver "1" --> "*" Attendance : melakukan
    Vehicle "1" --> "*" Attendance : digunakan
```

### Gambar 4.11 — Class Diagram: Domain Maintenance

```mermaid
classDiagram
    class Vehicle {
        +int id
        +string plate_number
        +int current_km
        +int service_interval_km
    }

    class VehicleComponent {
        +int id
        +int vehicle_id
        +string component_name
        +string category
        +int replacement_interval_km
        +int replacement_interval_days
        +int last_replacement_km
        +date last_replacement_date
        +int next_replacement_km
        +date next_replacement_date
        +int warning_threshold_km
        +int critical_threshold_km
        +string status
        +calculateNextReplacement() void
        +updateStatus() void
        +needsMaintenance() bool
        +getHealthScoreAttribute() float
    }

    class MaintenanceSchedule {
        +int id
        +int vehicle_id
        +int component_id
        +date scheduled_date
        +string type
        +string priority
        +string status
        +decimal estimated_cost
        +decimal actual_cost
        +string finance_pdf_path
        +datetime completed_at
        +int completed_by
        +markAsCompleted() void
        +isOverdue() bool
    }

    class MaintenanceAlert {
        +int id
        +int vehicle_id
        +int component_id
        +string alert_type
        +string message
        +string status
        +datetime triggered_at
        +acknowledge() void
        +resolve() void
        +dismiss() void
    }

    Vehicle "1" --> "*" VehicleComponent : memiliki
    VehicleComponent "1" --> "*" MaintenanceSchedule : dijadwalkan
    VehicleComponent "1" --> "*" MaintenanceAlert : memicu
    Vehicle "1" --> "*" MaintenanceSchedule : terjadwal
    Vehicle "1" --> "*" MaintenanceAlert : menerima
```

### Gambar 4.12 — Class Diagram: Domain Pendukung

```mermaid
classDiagram
    class User {
        +int id
        +string name
        +string email
        +string role
        +int customer_id
        +isMaster() bool
        +isServiceAdmin() bool
        +isCustomer() bool
    }

    class TransportCost {
        +int id
        +int driver_id
        +int vehicle_id
        +int attendance_id
        +date trip_date
        +decimal gasoline_cost
        +decimal toll_cost
        +decimal parking_cost
        +string approval_status
    }

    class ServiceReport {
        +int id
        +int driver_id
        +int vehicle_id
        +int customer_id
        +string ticket_number
        +string service_type
        +string status
        +decimal total_cost
    }

    class OfflineRecoveryLog {
        +int id
        +int driver_id
        +int attendance_id
        +string offline_entry_id
        +datetime device_timestamp
        +datetime recovery_timestamp
        +int delay_minutes
        +string result
        +int retry_count
    }

    class EmergencyReport {
        +int id
        +int driver_id
        +int vehicle_id
        +string gps_location
        +string description
        +string follow_up_status
    }

    class VehicleReplacement {
        +int id
        +int original_vehicle_id
        +int replacement_vehicle_id
        +int driver_id
        +string status
    }

    ServiceReport "1" --> "*" VehicleReplacement : menghasilkan
    User "1" --> "*" ServiceReport : approve
    User "1" --> "*" TransportCost : approve
```

---

## 4.1.3.9 Deployment Diagram

### Gambar 4.13 — Deployment Diagram

```mermaid
graph TB
    subgraph Client
        A["Flutter Mobile App<br/>Dart, Sembast, GPS, Camera"]
        B["Web Dashboard<br/>Blade, Bootstrap, Chart.js"]
    end

    subgraph Server
        C["Nginx / Apache<br/>Reverse Proxy, SSL"]
        D["Laravel 11<br/>Sanctum Auth, REST API<br/>Blade Views, Artisan"]
        E["Scheduler<br/>Cron Jobs Maintenance<br/>& Push Notification"]
    end

    subgraph Data
        F["MySQL Database<br/>16 Tabel, Eloquent ORM<br/>Transaction Support"]
        G["File Storage<br/>Foto, Tanda Tangan, PDF"]
    end

    subgraph External
        H["Firebase FCM<br/>Push Notification"]
        I["Google Maps<br/>GPS Location"]
    end

    A -- "HTTPS REST API<br/>JSON + Multipart" --> C
    B -- "HTTPS" --> C
    C --> D
    D --> F
    D --> G
    D --> H
    D --> E
    A -.-> H
    A -.-> I
```

---

> [!IMPORTANT]
> **Panduan Penggunaan di Word:**
> - Setiap blok `mermaid` = **1 gambar terpisah** di Word
> - Judul gambar sudah disiapkan (Gambar 4.1 s/d Gambar 4.13)
> - Ukuran optimal di Word: **lebar 14–16 cm**, height auto
> - Untuk screenshot: gunakan browser zoom 100%, lalu snipping tool
> - Total: **13 gambar** yang pas di halaman A4

| No | Bagian | Jumlah Gambar | Keterangan |
|---|---|---|---|
| 4.1.3.1 | Use Case | 2 | Dipecah: Driver vs Admin/Customer/Scheduler |
| 4.1.3.2 | Activity Check-In/Out | 2 | Masing-masing 1 gambar |
| 4.1.3.3 | Activity Maintenance | 1 | Sudah ringkas |
| 4.1.3.4 | Activity Sinkronisasi | 1 | Sudah ringkas |
| 4.1.3.5 | Seq Check-Out → KM | 1 | 4 participant |
| 4.1.3.6 | Seq Generate Alert | 1 | 3 participant |
| 4.1.3.7 | Seq Penyelesaian | 1 | 5 participant |
| 4.1.3.8 | Class Diagram | 3 | Dipecah: Core, Maintenance, Pendukung |
| 4.1.3.9 | Deployment | 1 | Sudah ringkas |
| | **Total** | **13** | |
