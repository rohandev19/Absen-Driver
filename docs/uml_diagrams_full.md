# 4.1.3 UML Diagrams (Versi Full / Tidak Dipisah)

---

## 4.1.3.1 Use Case Diagram

```mermaid
graph LR
    D((Driver))
    A((Admin / Master))
    SA((Service Admin))
    C((Customer))
    S((Scheduler / Cron))

    subgraph "Sistem Absensi Driver & Preventive Maintenance Armada"
        UC1[Login / Autentikasi]
        UC2[Check-In Absen Masuk]
        UC3[Check-Out Absen Pulang]
        UC4[Check-Out Offline]
        UC5[Sinkronisasi Data Offline]
        UC6[Lihat Riwayat Absensi]
        UC7[Laporan Darurat]
        UC8[Input Biaya Transportasi]
        UC9[Laporan Service Kendaraan]
        UC10[Kelola Driver]
        UC11[Kelola Kendaraan / Aset]
        UC12[Kelola Komponen Kendaraan]
        UC13[Kelola Jadwal Maintenance]
        UC14[Selesaikan Maintenance]
        UC15[Dashboard Maintenance]
        UC16[Generate Maintenance Alert]
        UC17[Update Status Komponen]
        UC18[Generate Jadwal Otomatis]
        UC19[Approval Service Report]
        UC20[Dashboard Admin]
        UC21[Export Laporan]
        UC22[Scan QR Code Kendaraan]
        UC23[Lihat Panduan Driver]
        UC24[Ganti Password]
        UC25[Notifikasi Push FCM]
    end

    D --- UC1
    D --- UC2
    D --- UC3
    D --- UC4
    D --- UC5
    D --- UC6
    D --- UC7
    D --- UC8
    D --- UC9
    D --- UC22
    D --- UC23
    D --- UC24

    A --- UC1
    A --- UC10
    A --- UC11
    A --- UC12
    A --- UC13
    A --- UC14
    A --- UC15
    A --- UC19
    A --- UC20
    A --- UC21

    SA --- UC1
    SA --- UC14
    SA --- UC19

    C --- UC1
    C --- UC19

    S --- UC16
    S --- UC17
    S --- UC18
    S --- UC25
```

---

## 4.1.3.2 Activity Diagram Check-In dan Check-Out Driver

```mermaid
flowchart TD
    A([Start]) --> B{Sudah login?}
    B -- Tidak --> C[Login dengan NIK & password]
    C --> D{Autentikasi berhasil?}
    D -- Tidak --> C
    D -- Ya --> E[Tampilkan Home Screen]
    B -- Ya --> E

    E --> F[Driver tekan tombol Check-In]
    F --> G{Metode input kendaraan}
    G -- QR Code --> H[Scan QR Code kendaraan]
    G -- Manual --> I[Input plat nomor + alasan + foto bukti]
    H --> J[Ambil plat nomor dari QR]
    I --> K[Validasi input manual]
    K --> J

    J --> L[Ambil lokasi GPS]
    L --> M[Ambil foto selfie]
    M --> N[Ambil foto speedometer + input KM manual]
    N --> O[Ambil foto kondisi kendaraan opsional]
    O --> P{Koneksi internet tersedia?}

    P -- Ya --> Q[Kirim data ke server via API]
    Q --> R{Server: Driver sudah on duty?}
    R -- Ya --> S[Error: Sudah clock-in]
    R -- Tidak --> T{Server: Kendaraan valid dan tersedia?}
    T -- Tidak valid --> U[Error: Kendaraan tidak ditemukan]
    T -- Manual dan tidak ada --> V[Buat record kendaraan baru status Pending]
    V --> W
    T -- Valid --> W[Simpan Attendance record dalam DB Transaction]
    W --> X[Update Driver is_on_duty = true]
    X --> Y[Clear cache driver]
    Y --> Z[Tampilkan pesan sukses]

    P -- Tidak --> AA[Simpan data ke local storage Sembast]
    AA --> AB[Masukkan ke offline queue]
    AB --> AC[Tampilkan pesan: Data tersimpan offline]

    Z --> AD([End])
    AC --> AD
    S --> AD
    U --> AD
```

---

## 4.1.3.2b Activity Diagram Check-Out Driver

```mermaid
flowchart TD
    A([Start]) --> B[Driver tekan tombol Check-Out]
    B --> C[Input KM speedometer akhir]
    C --> D[Ambil foto speedometer akhir]
    D --> E[Checklist kondisi kendaraan]
    E --> F["Check Ban: Aman / Bermasalah"]
    F --> G["Check Lampu: Aman / Bermasalah"]
    G --> H["Check Rem: Aman / Bermasalah"]
    H --> I[Input catatan opsional]
    I --> J{Koneksi internet tersedia?}

    J -- Ya --> K[Kirim data ke server via API]
    K --> L{Server: Ada attendance aktif?}
    L -- Tidak --> M[Tampilkan error: Tidak ada tugas aktif]
    L -- Ya --> N[Update Attendance record dalam DB Transaction]
    N --> O["Set time_out, speedo_akhir, kondisi kendaraan"]
    O --> P[Update Driver is_on_duty = false]
    P --> Q[Clear cache driver]
    Q --> R["Hitung summary: durasi kerja, total KM, status kendaraan"]
    R --> S[Tampilkan Duty Summary Screen]

    J -- Tidak --> T[Simpan data ke local storage Sembast]
    T --> U["Simpan ke Offline Queue dengan offline_entry_id UUID"]
    U --> V[Catat device_timestamp sebagai waktu asli]
    V --> W[Tampilkan pesan: Data tersimpan offline]

    S --> X([End])
    W --> X
    M --> X
```

---

## 4.1.3.3 Activity Diagram Preventive Maintenance Armada

```mermaid
flowchart TD
    A([Start]) --> B["Scheduler Cron trigger harian jam 00:00"]
    B --> C["Jalankan Command: maintenance:update-component-status"]
    C --> D[Ambil semua VehicleComponent dengan relasinya]
    D --> E{Untuk setiap komponen}

    E --> F["Hitung next_replacement_km = last_replacement_km + interval_km"]
    F --> G["Hitung next_replacement_date = last_replacement_date + interval_days"]
    G --> H["Hitung km_remaining = next_replacement_km - current_km kendaraan"]

    H --> I{"km_remaining <= 0?"}
    I -- Ya --> J["Set status = OVERDUE"]
    I -- Tidak --> K{"km_remaining <= critical_threshold_km?"}
    K -- Ya --> L["Set status = CRITICAL"]
    K -- Tidak --> M{"km_remaining <= warning_threshold_km?"}
    M -- Ya --> N["Set status = WARNING"]
    M -- Tidak --> O["Set status = HEALTHY"]

    J --> P[Simpan status komponen]
    L --> P
    N --> P
    O --> P

    P --> Q{Masih ada komponen lain?}
    Q -- Ya --> E
    Q -- Tidak --> R["Jalankan Command: maintenance:generate-alerts setiap 6 jam"]

    R --> S["Cek komponen berstatus warning / critical / overdue"]
    S --> T{Ada komponen perlu alert?}
    T -- Ya --> U[Generate MaintenanceAlert record]
    U --> V["Set alert_type sesuai status komponen"]
    V --> W[Kirim notifikasi ke Admin]
    T -- Tidak --> X[Tidak ada alert baru]

    W --> Y["Jalankan Command: maintenance:generate-schedules harian jam 01:00"]
    X --> Y
    Y --> Z[Generate MaintenanceSchedule otomatis untuk komponen yang perlu maintenance]
    Z --> AA[Set priority berdasarkan status komponen]
    AA --> AB([End])
```

---

## 4.1.3.4 Activity Diagram Sinkronisasi Offline

```mermaid
flowchart TD
    A([Start]) --> B{Trigger sinkronisasi}
    B --> C1["Connectivity listener: Offline ke Online"]
    B --> C2["Timer periodik setiap 2 menit"]
    B --> C3["App baru dibuka"]
    B --> C4["Manual sync dari tombol refresh UI"]

    C1 --> D
    C2 --> D
    C3 --> D
    C4 --> D

    D{"Cek koneksi internet"} --> |Tidak ada| E["Sync ditunda, kembali idle"]
    D --> |Ada koneksi| F{Ada data pending di local storage?}
    F -- Tidak --> G["Nothing to sync, kembali idle"]
    F -- Ya --> H["Acquire sync lock untuk cegah concurrent sync"]

    H --> I[Hitung total item pending]
    I --> J[Mulai progress tracker]

    J --> K["1. Proses Queue Check-In ke /submit-attendance"]
    K --> L["2. Proses Queue Check-Out ke /submit-end-of-duty"]
    L --> M["3. Proses Queue Emergency Report ke /submit-emergency-report"]
    M --> N["4. Proses Queue Service Report ke /submit-service-report"]
    N --> O["5. Proses Offline Clock-Out ke /clock-out-offline"]

    O --> P{Untuk setiap item dalam queue}
    P --> Q{Masih dalam backoff window?}
    Q -- Ya --> R[Skip item, coba di cycle berikutnya]
    Q -- Tidak --> S[Build MultipartRequest dengan foto]
    S --> T[Kirim ke server]

    T --> U{Response status}
    U -- "200/201 Success" --> V[Hapus record dari local DB]
    U -- "409 Duplicate" --> W[Idempotent: data sudah ada, hapus dari queue]
    U -- "404 Not Found" --> X[Tugas tidak ada lagi, hapus dari queue]
    U -- "401 Auth Expired" --> Y[Catat failure: auth expired]
    U -- "5xx Server Error" --> Z{Retry count >= 3?}
    Z -- Ya --> AA[Mark sebagai FAILED]
    Z -- Tidak --> AB[Increment retry count + exponential backoff]

    V --> AC{Masih ada item lain?}
    W --> AC
    X --> AC
    R --> AC
    Y --> AC
    AA --> AC
    AB --> AC

    AC -- Ya --> P
    AC -- Tidak --> AD[Finish progress tracker]
    AD --> AE[Release sync lock]
    AE --> AF([End])
    E --> AF
    G --> AF
```

---

## 4.1.3.5 Sequence Diagram Check-Out sampai Update KM

```mermaid
sequenceDiagram
    actor Driver
    participant App as Flutter Mobile App
    participant API as Laravel API Server
    participant AttCtrl as AttendanceController
    participant DB as MySQL Database
    participant Cache as Cache Store

    Driver->>App: Tekan tombol Check-Out
    App->>App: Tampilkan End of Duty Screen
    Driver->>App: Input KM akhir, foto speedo, checklist kondisi
    App->>App: Validasi input lokal

    App->>API: POST /api/submit-end-of-duty
    Note over App,API: Body: speedometer_manual_akhir,<br/>timestamp, check_ban, check_lampu,<br/>check_rem, catatan, foto speedometer

    API->>AttCtrl: submitEndOfDutyReport(Request)
    AttCtrl->>AttCtrl: Validasi input server-side
    AttCtrl->>DB: SELECT FROM drivers WHERE id = driver_id
    DB-->>AttCtrl: Driver record

    AttCtrl->>DB: SELECT FROM attendances WHERE driver_id AND time_out IS NULL
    DB-->>AttCtrl: Active Attendance record

    alt Tidak ada attendance aktif
        AttCtrl-->>App: 404 Tidak ada tugas aktif
        App-->>Driver: Tampilkan error
    else Ada attendance aktif
        AttCtrl->>AttCtrl: Optimize foto speedometer via ImageProcessingService
        AttCtrl->>DB: BEGIN TRANSACTION
        AttCtrl->>DB: UPDATE attendances SET time_out, speedo_akhir, check_ban, check_lampu, check_rem
        Note over DB: speedo_akhir = KM akhir dari driver
        AttCtrl->>DB: UPDATE drivers SET is_on_duty = false
        AttCtrl->>DB: COMMIT TRANSACTION

        Note over DB: Vehicle.computed_km otomatis<br/>mengambil speedo_akhir dari<br/>latestAttendance via Accessor

        AttCtrl->>Cache: Invalidate driver_status dan attendance_history cache
        AttCtrl->>AttCtrl: Hitung summary durasi kerja dan total KM
        AttCtrl-->>API: 200 OK + Summary data
        API-->>App: JSON Response
        App->>App: Tampilkan Duty Summary Screen
        App-->>Driver: Durasi kerja, Total KM, Status kendaraan
    end
```

---

## 4.1.3.6 Sequence Diagram Generate Maintenance Alert

```mermaid
sequenceDiagram
    participant Cron as Laravel Scheduler Cron
    participant Cmd as Artisan Command
    participant AlertSvc as MaintenanceAlertService
    participant CompModel as VehicleComponent Model
    participant VehModel as Vehicle Model
    participant AlertModel as MaintenanceAlert Model
    participant DB as MySQL Database

    Note over Cron: Setiap 6 jam via console.php
    Cron->>Cmd: maintenance:generate-alerts

    Cmd->>AlertSvc: generateAlerts()
    AlertSvc->>DB: SELECT FROM vehicle_components WITH vehicle
    DB-->>AlertSvc: Daftar semua komponen kendaraan

    loop Untuk setiap VehicleComponent
        AlertSvc->>CompModel: Ambil data komponen
        CompModel->>VehModel: Ambil current_km kendaraan
        VehModel-->>CompModel: current_km

        CompModel->>CompModel: Hitung km_remaining = next_replacement_km - current_km

        alt km_remaining <= 0 yaitu Overdue
            AlertSvc->>DB: Cek apakah alert overdue sudah ada dan aktif
            DB-->>AlertSvc: Existing alert check

            alt Belum ada alert aktif
                AlertSvc->>AlertModel: Create MaintenanceAlert
                Note over AlertModel: alert_type = overdue<br/>status = active<br/>triggered_at = now()
                AlertModel->>DB: INSERT INTO maintenance_alerts
                DB-->>AlertModel: Alert ID
            end

        else km_remaining <= critical_threshold_km yaitu Critical
            AlertSvc->>DB: Cek apakah alert critical sudah ada
            DB-->>AlertSvc: Existing alert check

            alt Belum ada alert aktif
                AlertSvc->>AlertModel: Create MaintenanceAlert
                Note over AlertModel: alert_type = critical<br/>status = active
                AlertModel->>DB: INSERT INTO maintenance_alerts
            end

        else km_remaining <= warning_threshold_km yaitu Warning
            AlertSvc->>DB: Cek apakah alert warning sudah ada
            DB-->>AlertSvc: Existing alert check

            alt Belum ada alert aktif
                AlertSvc->>AlertModel: Create MaintenanceAlert
                Note over AlertModel: alert_type = warning<br/>status = active
                AlertModel->>DB: INSERT INTO maintenance_alerts
            end
        end
    end

    AlertSvc-->>Cmd: Proses selesai
    Cmd->>Cmd: Log jumlah alert baru yang digenerate
```

---

## 4.1.3.7 Sequence Diagram Penyelesaian Maintenance

```mermaid
sequenceDiagram
    actor Admin
    participant Browser as Web Browser
    participant SchCtrl as MaintenanceScheduleController
    participant ImgSvc as ImageProcessingService
    participant PdfSvc as MaintenanceSchedulePdfService
    participant SchModel as MaintenanceSchedule Model
    participant CompModel as VehicleComponent Model
    participant DB as MySQL Database
    participant Storage as File Storage

    Admin->>Browser: Buka halaman Jadwal Maintenance
    Browser->>SchCtrl: GET /admin/maintenance/schedules
    SchCtrl->>DB: SELECT maintenance_schedules WITH vehicle dan component
    DB-->>SchCtrl: Daftar jadwal
    SchCtrl-->>Browser: Render view schedules

    Admin->>Browser: Klik Selesaikan pada jadwal tertentu
    Browser->>Browser: Tampilkan modal form penyelesaian
    Admin->>Browser: Input data penyelesaian

    Note over Browser: Biaya aktual, foto kuitansi,<br/>foto odometer, nama penandatangan,<br/>jabatan, tanda tangan digital canvas

    Admin->>Browser: Submit form
    Browser->>SchCtrl: POST /admin/maintenance/schedules/id/complete

    SchCtrl->>DB: SELECT FROM maintenance_schedules WHERE id
    DB-->>SchCtrl: MaintenanceSchedule record

    SchCtrl->>SchCtrl: Validasi input
    SchCtrl->>SchCtrl: Decode base64 signature
    SchCtrl->>Storage: Simpan file signature PNG
    Storage-->>SchCtrl: signature path

    SchCtrl->>ImgSvc: optimize receipt_photo
    ImgSvc-->>SchCtrl: receipt photo path
    SchCtrl->>ImgSvc: optimize odometer_photo
    ImgSvc-->>SchCtrl: odometer photo path

    SchCtrl->>DB: UPDATE maintenance_schedules SET status completed
    Note over DB: completed_at, completed_by,<br/>actual_cost, receipt_photo_path,<br/>odometer_photo_path, admin_signature_path

    SchCtrl->>CompModel: Update VehicleComponent
    Note over CompModel: last_replacement_date = now<br/>last_replacement_km = computed_km
    CompModel->>CompModel: Trigger saving: recalculate next replacement
    CompModel->>DB: UPDATE vehicle_components
    DB-->>CompModel: Updated

    SchCtrl->>PdfSvc: generateFinanceSubmission schedule
    PdfSvc->>Storage: Simpan file PDF
    Storage-->>PdfSvc: PDF path
    PdfSvc-->>SchCtrl: finance_pdf_path

    SchCtrl->>DB: UPDATE maintenance_schedules SET finance_pdf_path
    SchCtrl-->>Browser: Redirect with success message
    Browser-->>Admin: Jadwal pemeliharaan telah diselesaikan
```

---

## 4.1.3.8 Class Diagram

```mermaid
classDiagram
    class User {
        +int id
        +string name
        +string email
        +string password
        +string role
        +int customer_id
        +isMaster() bool
        +isServiceAdmin() bool
        +isCustomer() bool
        +customer() BelongsTo
    }

    class Driver {
        +int id
        +string full_name
        +string driver_id_nik
        +string nik_ktp
        +date sim_expiry_date
        +string sim_type
        +string password
        +int project_id
        +string fcm_token
        +bool is_on_duty
        +string qr_code_path
        +isOnDuty() bool
        +attendances() HasMany
        +project() BelongsTo
        +transportCosts() HasMany
    }

    class Vehicle {
        +int id
        +string plate_number
        +string type
        +int project_id
        +string status
        +int current_km
        +int service_interval_km
        +int last_service_km
        +date pajak_stnk_berlaku_sampai
        +date kir_berlaku_sampai
        +string qr_code_path
        +getComputedKmAttribute() int
        +getSisaKmAttribute() int
        +getHealthStatusCodeAttribute() string
        +components() HasMany
        +maintenanceSchedules() HasMany
        +maintenanceAlerts() HasMany
    }

    class Attendance {
        +int id
        +int driver_id
        +int vehicle_id
        +string vehicle_entry_method
        +datetime time_in
        +datetime time_out
        +string gps_location_in
        +string gps_location_out
        +int speedo_awal
        +int speedo_akhir
        +string check_ban
        +string check_lampu
        +string check_rem
        +bool is_offline_recovery
        +string offline_entry_id
        +driver() BelongsTo
        +vehicle() BelongsTo
        +transportCost() HasOne
        +offlineRecoveryLog() HasOne
    }

    class Project {
        +int id
        +string name
        +string code
        +string description
        +int customer_id
        +drivers() HasMany
        +vehicles() HasMany
        +customer() BelongsTo
    }

    class Customer {
        +int id
        +string name
        +string code
        +string contact_person
        +string email
        +string phone
        +users() HasMany
        +projects() HasMany
        +serviceReports() HasMany
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
        +vehicle() BelongsTo
        +maintenanceSchedules() HasMany
        +alerts() HasMany
    }

    class MaintenanceSchedule {
        +int id
        +int vehicle_id
        +int component_id
        +date scheduled_date
        +int scheduled_km
        +string type
        +string priority
        +string status
        +decimal estimated_cost
        +decimal actual_cost
        +string workshop_name
        +string finance_pdf_path
        +string admin_signature_path
        +datetime completed_at
        +int completed_by
        +markAsCompleted() void
        +isOverdue() bool
        +vehicle() BelongsTo
        +component() BelongsTo
        +completedBy() BelongsTo
    }

    class MaintenanceAlert {
        +int id
        +int vehicle_id
        +int component_id
        +string alert_type
        +string message
        +datetime triggered_at
        +datetime acknowledged_at
        +int acknowledged_by
        +datetime resolved_at
        +string status
        +acknowledge() void
        +resolve() void
        +dismiss() void
        +vehicle() BelongsTo
        +component() BelongsTo
        +acknowledgedBy() BelongsTo
    }

    class TransportCost {
        +int id
        +int driver_id
        +int vehicle_id
        +int project_id
        +int attendance_id
        +date trip_date
        +string do_number
        +decimal gasoline_cost
        +decimal toll_cost
        +decimal parking_cost
        +string approval_status
        +driver() BelongsTo
        +vehicle() BelongsTo
        +attendance() BelongsTo
    }

    class ServiceReport {
        +int id
        +int driver_id
        +int vehicle_id
        +int customer_id
        +string ticket_number
        +string service_type
        +string problem_category
        +string status
        +decimal total_cost
        +string finance_pdf_path
        +driver() BelongsTo
        +vehicle() BelongsTo
        +customer() BelongsTo
        +approvedByAdmin() BelongsTo
        +vehicleReplacements() HasMany
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
        +string error_code
        +int retry_count
        +driver() BelongsTo
        +attendance() BelongsTo
    }

    class EmergencyReport {
        +int id
        +int driver_id
        +int vehicle_id
        +datetime timestamp
        +string gps_location
        +string description
        +string proof_photo_path
        +string follow_up_status
        +driver() BelongsTo
        +vehicle() BelongsTo
    }

    class VehicleReplacement {
        +int id
        +int original_vehicle_id
        +int replacement_vehicle_id
        +int driver_id
        +int service_report_id
        +string status
        +originalVehicle() BelongsTo
        +replacementVehicle() BelongsTo
        +driver() BelongsTo
        +serviceReport() BelongsTo
    }

    Customer "1" --> "*" Project : memiliki
    Customer "1" --> "*" User : memiliki
    Project "1" --> "*" Driver : memiliki
    Project "1" --> "*" Vehicle : memiliki
    Driver "1" --> "*" Attendance : melakukan
    Vehicle "1" --> "*" Attendance : digunakan
    Attendance "1" --> "0..1" TransportCost : memiliki
    Attendance "1" --> "0..1" OfflineRecoveryLog : dicatat
    Driver "1" --> "*" TransportCost : mengajukan
    Driver "1" --> "*" ServiceReport : melaporkan
    Vehicle "1" --> "*" VehicleComponent : memiliki
    VehicleComponent "1" --> "*" MaintenanceSchedule : dijadwalkan
    VehicleComponent "1" --> "*" MaintenanceAlert : memicu
    Vehicle "1" --> "*" MaintenanceSchedule : dijadwalkan
    Vehicle "1" --> "*" MaintenanceAlert : menerima
    User "1" --> "*" MaintenanceSchedule : menyelesaikan
    ServiceReport "1" --> "*" VehicleReplacement : menghasilkan
    Vehicle "1" --> "*" EmergencyReport : dilaporkan
    Driver "1" --> "*" EmergencyReport : melaporkan
```

---

## 4.1.3.9 Deployment Diagram

```mermaid
graph TB
    subgraph "Client Layer"
        subgraph "Mobile Device Android"
            FlutterApp["Flutter Mobile App<br/>---<br/>Dart / Flutter Framework<br/>Provider State Management<br/>Sembast Local DB<br/>Connectivity Plus<br/>GPS / Camera / QR Scanner"]
        end

        subgraph "Web Browser"
            WebDashboard["Web Dashboard<br/>---<br/>Laravel Blade Templates<br/>Bootstrap / JavaScript<br/>Chart.js / FullCalendar<br/>Signature Pad"]
        end
    end

    subgraph "Server Layer VPS / Cloud"
        subgraph "Web Server"
            Nginx["Nginx / Apache<br/>---<br/>Reverse Proxy<br/>SSL Termination<br/>Static File Serving"]
        end

        subgraph "Application Server"
            Laravel["Laravel 11 PHP 8.2+<br/>---<br/>Sanctum Authentication<br/>RESTful API JSON<br/>Blade View Engine<br/>Artisan Console Commands<br/>Task Scheduler Cron"]
        end

        subgraph "Background Services"
            Scheduler["Laravel Scheduler<br/>---<br/>update-component-status daily<br/>generate-alerts 6 jam<br/>generate-schedules daily 01:00<br/>notify 8-hours 15 menit<br/>notify sim-expiry daily 09:00"]
        end
    end

    subgraph "Data Layer"
        subgraph "Database Server"
            MySQL["MySQL / MariaDB<br/>---<br/>16 tabel utama<br/>Eloquent ORM<br/>Database Transactions<br/>Migration dan Seeder"]
        end

        subgraph "File Storage"
            FileStorage["Laravel Storage public<br/>---<br/>Foto Selfie dan Speedometer<br/>Foto Kondisi Kendaraan<br/>Foto Kuitansi dan Odometer<br/>Tanda Tangan Digital PNG<br/>Dokumen PDF Finance"]
        end
    end

    subgraph "External Services"
        FCM["Firebase Cloud Messaging<br/>---<br/>Push Notifications<br/>Driver dan Admin alerts"]

        GoogleMaps["Google Maps API<br/>---<br/>GPS Location Display"]
    end

    FlutterApp -- "HTTPS REST API<br/>JSON + Multipart" --> Nginx
    WebDashboard -- "HTTPS<br/>HTML + AJAX" --> Nginx
    Nginx -- "Reverse Proxy" --> Laravel
    Laravel -- "Eloquent ORM<br/>PDO MySQL" --> MySQL
    Laravel -- "File System<br/>Storage Facade" --> FileStorage
    Laravel -- "HTTP Client<br/>FCM SDK" --> FCM
    Laravel --> Scheduler
    FlutterApp -- "FCM SDK" --> FCM
    FlutterApp -. "Maps SDK" .-> GoogleMaps
```
