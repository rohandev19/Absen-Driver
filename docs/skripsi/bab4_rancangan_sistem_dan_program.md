# BAB IV
# RANCANGAN SISTEM DAN PROGRAM

## 4.1 Rancangan Sistem Usulan

Perancangan sistem usulan ini disusun dengan tujuan menggantikan mekanisme pencatatan manual yang selama ini diterapkan di PT Hamada Global Jaya dengan suatu sistem terkomputerisasi yang mampu beroperasi secara otomatis dan real-time. Pada bagian ini, seluruh kebutuhan fungsional serta perilaku sistem dimodelkan menggunakan notasi Unified Modeling Language (UML) yang mencakup Use Case Diagram, Activity Diagram, Sequence Diagram, dan Class Diagram.

### 4.1.1 Use Case Diagram
Sistem usulan dikelompokkan ke dalam dua area Use Case utama agar lebih mudah dipahami serta menyesuaikan dengan tata letak pencetakan pada halaman dokumen berukuran A4.

#### A. Use Case Diagram - Sisi Driver
Diagram berikut memperlihatkan keseluruhan interaksi yang dapat dilaksanakan oleh aktor **Driver** melalui aplikasi mobile berbasis Flutter.

```mermaid
graph LR
    D((Driver))

    UC1[Login Mobile App]
    UC2[Check-In Absen Masuk]
    UC3[Check-Out Absen Pulang]
    UC4[Check-Out Offline]
    UC5[Sinkronisasi Offline]
    UC6[Lihat Riwayat Absensi]
    UC7[Laporan Darurat]
    UC8[Input Biaya Transportasi]
    UC9[Laporan Service Kendaraan]
    UC10[Scan QR Code Kendaraan]
    UC11[Lihat Panduan Driver]
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

#### B. Use Case Diagram - Sisi Admin, Customer & Scheduler
Diagram berikut memperlihatkan interaksi yang dilaksanakan oleh aktor **Admin Master**, **Service Admin**, **Customer**, serta aktor otomasi sistem **Scheduler** melalui Web Dashboard.

```mermaid
graph LR
    A((Admin Master))
    SA((Service Admin))
    C((Customer))
    S((Scheduler / Cron))

    UC13[Login Web Dashboard]
    UC14[Kelola Data Driver]
    UC15[Kelola Data Kendaraan]
    UC16[Kelola Komponen Kendaraan]
    UC17[Kelola Jadwal Maintenance]
    UC18[Selesaikan Maintenance]
    UC19[Dashboard Maintenance]
    UC20[Approval Service Report]
    UC21[Dashboard Admin & Monitoring]
    UC22[Export Laporan Absensi]
    UC23[Generate Alert Otomatis]
    UC24[Update Status Komponen]
    UC25[Generate Jadwal Otomatis]

    A --- UC13
    A --- UC14
    A --- UC15
    A --- UC16
    A --- UC17
    A --- UC18
    A --- UC19
    A --- UC20
    A --- UC21
    A --- UC22

    SA --- UC13
    SA --- UC16
    SA --- UC17
    SA --- UC18
    SA --- UC19
    SA --- UC20

    C --- UC13
    C --- UC20
    C --- UC21

    S --- UC23
    S --- UC24
    S --- UC25
```

#### C. Deskripsi Use Case Utama
Tabel berikut menyajikan penjelasan ringkas dari masing-masing Use Case utama pada sistem:

| No. Use Case | Nama Use Case | Aktor Utama | Deskripsi Singkat |
| :--- | :--- | :--- | :--- |
| UC-02 | Check-In Absen Masuk | Driver | Pengemudi mengawali tugas harian dengan memindai QR kendaraan, mengambil foto wajah, foto panel speedometer awal, dan memasukkan angka kilometer awal. |
| UC-03 | Check-Out Absen Pulang | Driver | Pengemudi menyelesaikan tugas harian dengan memasukkan angka kilometer akhir, mengambil foto panel speedometer akhir, serta mengisi daftar periksa kondisi kendaraan (rem, ban, lampu). |
| UC-04 | Check-Out Offline | Driver | Pengemudi menyimpan data check-out ke memori internal perangkat handphone dikarenakan tidak tersedianya sinyal internet pada saat itu. |
| UC-05 | Sinkronisasi Offline | Driver | Sistem secara otomatis mendeteksi ketersediaan koneksi internet dan mengirimkan seluruh data antrean absensi offline dari penyimpanan lokal ke server database Laravel. |
| UC-16 | Kelola Komponen Kendaraan | Admin Master, Service Admin | Tim pemeliharaan mengonfigurasi parameter interval penggantian suku cadang (berdasarkan KM atau hari) beserta ambang batas peringatan (*threshold*) untuk setiap unit armada. |
| UC-18 | Selesaikan Maintenance | Admin Master, Service Admin | Admin mendokumentasikan biaya perawatan aktual, mengunggah foto kuitansi bengkel, foto panel odometer terbaru, serta membubuhkan tanda tangan digital melalui kanvas untuk menandai selesainya tugas perawatan. |
| UC-20 | Approval Service Report | Customer, Admin | Customer mengevaluasi laporan perbaikan kendaraan dan membubuhkan tanda tangan digital pada dokumen persetujuan agar unit dapat kembali dioperasikan atau biayanya diajukan ke bagian keuangan. |
| UC-23 | Generate Alert Otomatis | Scheduler | Scheduler pada server melakukan pemeriksaan akumulasi kilometer kendaraan setiap 6 jam guna mengidentifikasi komponen yang telah memasuki status warning atau critical, kemudian menghasilkan notifikasi peringatan. |

---

### 4.1.2 Activity Diagram
Activity diagram digunakan untuk memodelkan alur kerja sistem secara menyeluruh dari titik awal hingga titik akhir, termasuk percabangan keputusan (*decisions*) serta proses yang berjalan secara paralel.

#### A. Activity Diagram Check-In Driver (Absen Masuk)
Diagram ini menguraikan rangkaian langkah yang dilalui pengemudi ketika mendaftarkan kendaraan dinasnya, baik dalam kondisi online maupun offline.

```mermaid
flowchart TD
    A([Mulai]) --> B{Sudah login?}
    B -- Tidak --> C[Login dengan NIK & password]
    C --> B
    B -- Ya --> D[Tekan tombol Check-In]
    D --> E{Metode input kendaraan}
    E -- QR Code --> F[Scan QR Code pada armada]
    E -- Manual --> G[Input plat nomor + alasan manual + unggah foto]
    F --> H[Ambil GPS, foto selfie, foto speedo, & input KM awal]
    G --> H
    H --> I{Ada internet?}
    I -- Ya --> J[Kirim data check-in ke server]
    J --> K{Driver sudah on duty?}
    K -- Ya --> L[Error: Anda masih on duty]
    K -- Tidak --> M{Kendaraan valid?}
    M -- Tidak --> N[Error: Kendaraan tidak terdaftar]
    M -- Ya --> O[Simpan Attendance + set driver on duty]
    O --> P[Tampilkan Pesan Sukses]
    I -- Tidak --> Q[Simpan ke Sembast DB lokal]
    Q --> R[Masukkan antrean sinkronisasi offline]
    P --> S([Selesai])
    R --> S
    L --> S
    N --> S
```

#### B. Activity Diagram Check-Out Driver (Absen Keluar)
Diagram ini menguraikan rangkaian langkah yang dilalui pengemudi ketika mengakhiri tugas harian dan melaporkan kondisi akhir armada.

```mermaid
flowchart TD
    A([Mulai]) --> B[Tekan tombol Check-Out]
    B --> C[Input KM akhir + unggah foto speedometer akhir]
    C --> D[Isi checklist kondisi armada: Ban, Lampu, Rem]
    D --> E[Isi catatan tambahan / keluhan mekanis jika ada]
    E --> F{Ada internet?}
    F -- Ya --> G[Kirim data check-out ke server]
    G --> H{Ada attendance aktif?}
    H -- Tidak --> I[Tampilkan error: Tidak ada tugas aktif]
    H -- Ya --> J[Update attendance + set driver off duty]
    J --> K[Hitung durasi tugas & selisih KM perjalanan]
    K --> L[Tampilkan Ringkasan Tugas / Duty Summary]
    F -- Tidak --> M[Simpan data check-out ke Sembast DB lokal]
    M --> N[Generate UUID offline_entry_id]
    N --> O[Catat timestamp asli dari handphone]
    L --> P([Selesai])
    O --> P
    I --> P
```

#### C. Activity Diagram Scheduler Preventive Maintenance (Otomasi Server)
Diagram ini memperlihatkan alur proses otomatis yang dieksekusi oleh server di latar belakang untuk memperbarui status kesehatan komponen armada serta memicu pembuatan jadwal pemeliharaan.

```mermaid
flowchart TD
    A([Mulai]) --> B[Cron Job Server trigger harian jam 00:00]
    B --> C[Ambil semua data komponen aktif kendaraan]
    C --> D{Untuk setiap komponen}
    D --> E[Hitung sisa KM servis: next_replacement_km - current_km]
    E --> F{Sisa KM <= 0?}
    F -- Ya --> G[Ubah Status Komponen = OVERDUE]
    F -- Tidak --> H{Sisa KM <= critical_threshold?}
    H -- Ya --> I[Ubah Status Komponen = CRITICAL]
    H -- Tidak --> J{Sisa KM <= warning_threshold?}
    J -- Ya --> K[Ubah Status Komponen = WARNING]
    J -- Tidak --> L[Ubah Status Komponen = HEALTHY]
    G --> M[Simpan status terbaru ke database]
    I --> M
    K --> M
    L --> M
    M --> N{Ada komponen lain?}
    N -- Ya --> D
    N -- Tidak --> O[Generate alert untuk komponen bermasalah]
    O --> P[Buat Jadwal Maintenance otomatis status Pending]
    P --> Q[Kirim notifikasi push ke Admin]
    Q --> R([Selesai])
```

#### D. Activity Diagram Sinkronisasi Offline
Diagram ini mengilustrasikan bagaimana aplikasi mobile secara berkala ataupun secara manual melakukan sinkronisasi data yang sebelumnya tertahan pengirimannya karena ketiadaan koneksi internet.

```mermaid
flowchart TD
    A([Mulai]) --> B{Trigger Sinkronisasi}
    B --> B1[Koneksi kembali terdeteksi]
    B --> B2[Interval timer 2 menit]
    B --> B3[Aplikasi baru dibuka]
    B --> B4[User menekan tombol Refresh]
    
    B1 & B2 & B3 & B4 --> C{Apakah internet aktif?}
    C -- Tidak --> D[Tunda sinkronisasi, kembali idle]
    D --> Z([Selesai])
    C -- Ya --> E{Ada data antrean di local DB?}
    E -- Tidak --> F[Tampilkan status sinkron]
    F --> Z
    E -- Ya --> G[Acquire Sync Lock]
    G --> H[Proses antrean berurutan: Check-In -> Check-Out -> Emergency -> Service]
    H --> I{Untuk setiap item antrean}
    I --> J{Dalam masa backoff?}
    J -- Ya --> K[Lewati item ini sementara]
    J -- Tidak --> L[Kirim HTTP Multipart Request ke Server]
    L --> M{Response Server?}
    M -- "200/201 Sukses" --> N[Hapus item dari local DB]
    M -- "409 Duplikat / 404 Kadaluwarsa" --> N
    M -- "500 Server Error / Putus Sinyal" --> O{Retry >= 3?}
    O -- Ya --> P[Tandai status GAGAL di lokal]
    O -- Tidak --> Q[Tingkatkan retry count + hitung backoff]
    
    N & K & P & Q --> R{Ada item lain di antrean?}
    R -- Ya --> I
    R -- Tidak --> S[Release Sync Lock]
    S --> Z
```

---

### 4.1.3 Sequence Diagram
Sequence diagram digunakan untuk memvisualisasikan interaksi antar-objek yang diorganisasikan berdasarkan urutan waktu, dengan titik fokus pada pertukaran pesan antara aktor dan antarmuka kelas dalam sistem.

#### A. Sequence Diagram Check-Out Driver dan Pembaruan Jarak Tempuh
Diagram ini mendeskripsikan interaksi yang terjadi saat pengemudi melaksanakan absensi pulang dinas, serta bagaimana server Laravel memproses transaksi tersebut dan melakukan pembaruan terhadap data jarak tempuh kendaraan.

```mermaid
sequenceDiagram
    actor Driver
    participant App as Mobile App (Flutter)
    participant Server as Laravel API
    participant AttCtrl as AttendanceController
    participant DB as MySQL Database

    Driver->>App: Input KM akhir, foto speedometer, isi checklist
    App->>App: Validasi format input lokal
    App->>Server: POST /api/submit-end-of-duty (JSON + Image Multipart)
    Server->>AttCtrl: submitEndOfDuty(Request)
    
    AttCtrl->>AttCtrl: Validasi token & server-side validation
    AttCtrl->>DB: Cari record attendance aktif driver
    DB-->>AttCtrl: Record ditemukan
    
    alt Skenario Valid
        AttCtrl->>AttCtrl: Kompres & simpan foto speedometer (GD Library)
        AttCtrl->>DB: BEGIN TRANSACTION
        AttCtrl->>DB: UPDATE attendances SET speedo_akhir, check_ban, check_rem, check_lampu, time_out
        AttCtrl->>DB: UPDATE drivers SET is_on_duty = 0
        AttCtrl->>DB: COMMIT TRANSACTION
        Note over DB: Accessor Vehicle.computed_km otomatis<br/>menggunakan speedo_akhir dari attendance terbaru
        AttCtrl->>AttCtrl: Hitung selisih jarak & durasi kerja
        AttCtrl-->>App: 200 OK (Summary data & Status Off Duty)
        App-->>Driver: Tampilkan ringkasan tugas (Total KM & Waktu Kerja)
    else Skenario Invalid (Tidak ada absen masuk)
        AttCtrl-->>App: 404 Not Found (Error: Tidak ada tugas aktif)
        App-->>Driver: Tampilkan pesan error
    end
```

#### B. Sequence Diagram Pengiriman Alert Maintenance Preventif
Diagram ini memperlihatkan interaksi antara scheduler dalam memicu proses deteksi suku cadang yang perlu diperhatikan, kemudian mengirimkan notifikasi kepada Admin.

```mermaid
sequenceDiagram
    participant Scheduler as Laravel Kernel Scheduler
    participant Command as Artisan Command
    participant Service as MaintenanceAlertService
    participant DB as MySQL Database
    participant FCM as Firebase Notification Service

    Note over Scheduler: Terjadwal setiap 6 jam
    Scheduler->>Command: Run command: maintenance:generate-alerts
    Command->>Service: generateAlerts()
    
    Service->>DB: Query seluruh komponen aktif dengan relasi kendaraan
    DB-->>Service: List komponen (oli, rem, ban, dll)
    
    loop Untuk setiap komponen
        Service->>Service: Hitung next_replacement_km - current_km
        alt Sisa KM <= warning/critical/overdue threshold
            Service->>DB: Cek apakah alert aktif untuk komponen ini sudah ada?
            DB-->>Service: Hasil cek (Belum ada)
            Service->>DB: INSERT INTO maintenance_alerts (vehicle_id, component_id, alert_type, status=active)
            Service->>FCM: Kirim FCM Payload Notifikasi ke Admin Master
            FCM-->>Service: Notifikasi terkirim
        end
    end
    
    Service-->>Command: Return jumlah alert yang dibuat
    Command-->>Scheduler: Task complete & log execution status
```

#### C. Sequence Diagram Penyelesaian Jadwal Pemeliharaan
Diagram ini menunjukkan alur penutupan tugas servis oleh admin beserta proses pembaruan usia pakai suku cadang kembali ke titik awal.

```mermaid
sequenceDiagram
    actor Admin
    participant Dashboard as Web Dashboard
    participant Controller as MaintenanceScheduleController
    participant DB as MySQL Database
    participant Storage as Laravel File Storage
    participant PDF as PDFGenerator Service

    Admin->>Dashboard: Klik tombol Selesaikan Maintenance
    Dashboard->>Dashboard: Tampilkan formulir biaya, foto invoice, odometer, & signature pad
    Admin->>Dashboard: Tanda tangan di kanvas, unggah foto, isi biaya, submit
    Dashboard->>Controller: POST /admin/maintenance/schedules/{id}/complete
    
    Controller->>Controller: Validasi berkas input
    Controller->>Storage: Simpan gambar tanda tangan (PNG) & foto invoice terkompresi
    Storage-->>Controller: Path gambar disimpan
    
    Controller->>DB: BEGIN TRANSACTION
    Controller->>DB: UPDATE maintenance_schedules SET status=completed, actual_cost, admin_signature_path
    
    Controller->>DB: UPDATE vehicle_components SET last_replacement_km = current_km, last_replacement_date = now()
    Note over DB: Memicu event saving: tanggal/KM<br/>pergantian berikutnya dihitung ulang otomatis
    
    Controller->>PDF: generateSubmissionPdf(schedule_id)
    PDF->>Storage: Simpan berkas PDF bukti servis logistik
    Storage-->>PDF: File PDF path
    PDF-->>Controller: Return PDF path
    
    Controller->>DB: UPDATE maintenance_schedules SET finance_pdf_path
    Controller->>DB: COMMIT TRANSACTION
    
    Controller-->>Dashboard: Redirect dengan pesan sukses
    Dashboard-->>Admin: Tampilkan pemberitahuan data berhasil disimpan
```

---

### 4.1.4 Class Diagram
Class diagram berikut mendokumentasikan pemodelan struktur kelas pada entitas Eloquent ORM di Laravel, lengkap dengan relasi kardinalitas antar-kelas yang dikelompokkan berdasarkan domain fungsionalnya.

```mermaid
classDiagram
    %% Domain Absensi (Core)
    class Driver {
        +int id
        +string full_name
        +string driver_id_nik
        +string nik_ktp
        +string sim_type
        +date sim_expiry_date
        +bool is_on_duty
        +string fcm_token
        +string qr_code_path
        +isOnDuty() bool
    }

    class Vehicle {
        +int id
        +string plate_number
        +string type
        +int current_km
        +int service_interval_km
        +string status
        +getComputedKmAttribute() int
        +getHealthStatusCodeAttribute() string
    }

    class Attendance {
        +int id
        +int driver_id
        +int vehicle_id
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

    %% Domain Maintenance
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
        +decimal cost_per_replacement
        +int warning_threshold_km
        +int critical_threshold_km
        +string status
        +calculateNextReplacement() void
        +updateStatus() void
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
        +datetime completed_at
        +int completed_by
        +markAsCompleted() void
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
    }

    %% Domain Pendukung
    class User {
        +int id
        +string name
        +string email
        +string role
        +int customer_id
        +isMaster() bool
        +isServiceAdmin() bool
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
        +string finance_pdf_path
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
    }

    Customer "1" --> "*" Project : memiliki
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
    Vehicle "1" --> "*" MaintenanceSchedule : terjadwal
    Vehicle "1" --> "*" MaintenanceAlert : menerima
    User "1" --> "*" MaintenanceSchedule : menyelesaikan
    User "1" --> "*" ServiceReport : menyetujui

```

---

## 4.2 Rancangan Basis Data

Sistem usulan memanfaatkan basis data relasional (RDBMS) MySQL sebagai media penyimpanan data. Rancangan struktur basis data di bawah ini memetakan relasi antar entitas bisnis dalam sistem.

### 4.2.1 Entity Relationship Diagram (ERD)
ERD sistem terdiri atas entitas-entitas utama dengan hubungan relasi sebagai berikut:
1. **Customer (1) -> Project (N):** Satu customer dapat memiliki beberapa proyek logistik yang sedang berjalan.
2. **Project (1) -> Driver (N) & Vehicle (N):** Unit armada dan pengemudi dialokasikan secara eksklusif ke dalam satu proyek operasional.
3. **Driver (1) -> Attendance (N):** Pengemudi melaksanakan pencatatan absensi secara berkala pada setiap hari kerja.
4. **Vehicle (1) -> Attendance (N):** Setiap kendaraan terhubung dengan data absensi pengemudi yang mengoperasikannya.
5. **Vehicle (1) -> VehicleComponent (N):** Satu unit kendaraan terdiri atas sejumlah komponen suku cadang yang dipantau kesehatannya (meliputi oli mesin, rem, ban depan, dan ban belakang).
6. **VehicleComponent (1) -> MaintenanceSchedule (N):** Proses penggantian komponen yang terjadwal saling terhubung dengan riwayat pemeliharaannya.
7. **Attendance (1) -> TransportCost (0..1):** Setiap catatan absensi harian berpotensi memiliki klaim biaya perjalanan (bahan bakar, parkir, tol) yang memerlukan verifikasi.
8. **ServiceReport (1) -> VehicleReplacement (N):** Apabila proses penanganan service report membutuhkan waktu yang relatif lama, admin dapat mencatatkan unit kendaraan pengganti sementara (*replacement*) bagi pengemudi.

---

### 4.2.2 Logical Record Structure (LRS)
Logical Record Structure (LRS) menyajikan visualisasi tabel beserta *primary key* (PK), *foreign key* (FK), tipe kolom, serta kardinalitas relasi fisik antar-tabel di dalam database.

```text
+------------------------+        +------------------------+
|       CUSTOMERS        |        |        PROJECTS        |
+------------------------+        +------------------------+
| PK | id (INT)          |        | PK | id (INT)          |
|    | name (VARCHAR)    |        | FK | customer_id (INT) |<---+
|    | code (VARCHAR)    |        |    | name (VARCHAR)    |    |
|    | contact (VARCHAR) |<--+    |    | code (VARCHAR)    |    |
+------------------------+   |    +------------------------+    |
                             |                |                 |
+------------------------+   |                v                 |
|         USERS          |   |    +------------------------+    |
+------------------------+   |    |        DRIVERS         |    |
| PK | id (INT)          |   |    +------------------------+    |
| FK | customer_id (INT) |---+    | PK | id (INT)          |    |
|    | name (VARCHAR)    |        | FK | project_id (INT)  |----+
|    | email (VARCHAR)   |        |    | driver_id_nik(VAR)|    |
|    | role (VARCHAR)    |        |    | full_name(VARCHAR)|    |
+------------------------+        |    | is_on_duty(BOOL)  |    |
                                  +------------------------+    |
                                              |                 |
                                              v                 |
+------------------------+        +------------------------+    |
|      ATTENDANCES       |        |        VEHICLES        |    |
+------------------------+        +------------------------+    |
| PK | id (INT)          |        | PK | id (INT)          |    |
| FK | driver_id (INT)   |<-------| FK | project_id (INT)  |----+
| FK | vehicle_id (INT)  |=======>| FK | customer_id (INT) |----+
|    | time_in (DATETIME)|        |    | plate_number(VAR) |
|    | speedo_awal (INT) |        |    | current_km (INT)  |
|    | speedo_akhir(INT) |        |    | status (VARCHAR)  |
+------------------------+        +------------------------+
            |                                 ||
            v                                 |v
+------------------------+        +------------------------+
|    TRANSPORT_COSTS     |        |   VEHICLE_COMPONENTS   |
+------------------------+        +------------------------+
| PK | id (INT)          |        | PK | id (INT)          |
| FK | attendance_id(INT)|<---+   | FK | vehicle_id (INT)  |
| FK | driver_id (INT)   |    |   |    | component_name(VAR)|
|    | gasoline_cost(DEC)|    |   |    | next_rep_km (INT) |
|    | toll_cost (DEC)   |    |   |    | status (VARCHAR)  |
+------------------------+    |   +------------------------+
                              |               ||
+------------------------+    |               |v
| OFFLINE_RECOVERY_LOGS  |    |   +------------------------+
+------------------------+    |   | MAINTENANCE_SCHEDULES  |
| PK | id (INT)          |    |   +------------------------+
| FK | attendance_id(INT)|----+   | PK | id (INT)          |
| FK | driver_id (INT)   |        | FK | vehicle_id (INT)  |
|    | delay_minutes(INT)|        | FK | component_id (INT) |
+------------------------+        |    | scheduled_date(DAT)|
                                  |    | actual_cost (DEC)  |
                                  +------------------------+
```

---

### 4.2.3 Spesifikasi File / Tabel

Spesifikasi tabel berikut menguraikan secara terperinci metadata, tipe data, panjang field, kunci relasi, serta keterangan untuk 10 tabel utama sistem yang rancangannya bersumber dari file migrasi database pada framework Laravel.

#### 1. Spesifikasi Tabel `users`
Tabel ini berfungsi menyimpan data otentikasi bagi admin, service admin, dan perwakilan customer yang memiliki hak akses ke Web Dashboard.

* **Nama Database:** `absen_driver`
* **Nama Tabel:** `users`
* **Primary Key:** `id`

| No | Nama Field | Tipe Data | Size | Null | Key | Keterangan |
| :--- | :--- | :--- | :--- | :--- | :--- | :--- |
| 1 | `id` | Bigint | 20 | No | PK | Auto increment, ID User unik |
| 2 | `name` | Varchar | 255 | No | - | Nama lengkap user |
| 3 | `email` | Varchar | 255 | No | Unique | Alamat email otentikasi login |
| 4 | `password` | Varchar | 255 | No | - | Password ter-hash (bcrypt) |
| 5 | `role` | Varchar | 50 | No | - | Role: `master`, `service_admin`, `customer` |
| 6 | `customer_id` | Bigint | 20 | Yes | FK | Relasi ke tabel `customers.id` |
| 7 | `created_at` | Timestamp | - | Yes | - | Waktu pembuatan baris |
| 8 | `updated_at` | Timestamp | - | Yes | - | Waktu pembaruan baris |

#### 2. Spesifikasi Tabel `drivers`
Tabel ini memuat data pengemudi, status penugasan aktif (*on-duty*), NIK KTP, serta token notifikasi push Firebase.

* **Nama Database:** `absen_driver`
* **Nama Tabel:** `drivers`
* **Primary Key:** `id`

| No | Nama Field | Tipe Data | Size | Null | Key | Keterangan |
| :--- | :--- | :--- | :--- | :--- | :--- | :--- |
| 1 | `id` | Bigint | 20 | No | PK | Auto increment |
| 2 | `driver_id_nik` | Varchar | 255 | No | Unique | NIK Karyawan Driver / ID Driver |
| 3 | `full_name` | Varchar | 255 | No | - | Nama Lengkap Driver |
| 4 | `nik_ktp` | Varchar | 255 | No | Unique | NIK KTP Pengemudi |
| 5 | `sim_type` | Varchar | 50 | No | - | Tipe SIM (A, B1, B2 Umum) |
| 6 | `sim_expiry_date` | Date | - | No | - | Tanggal kedaluwarsa SIM |
| 7 | `password` | Varchar | 255 | No | - | Password login driver mobile |
| 8 | `project_id` | Bigint | 20 | Yes | FK | Relasi ke tabel `projects.id` |
| 9 | `is_on_duty` | Tinyint | 1 | No | - | Flag status dinas (0 = off, 1 = on) |
| 10 | `fcm_token` | Text | - | Yes | - | Firebase token untuk Push Notification |
| 11 | `qr_code_path` | Varchar | 255 | Yes | - | Path berkas QR Code Driver |
| 12 | `profile_photo` | Varchar | 255 | Yes | - | Path foto profil pengemudi |

#### 3. Spesifikasi Tabel `vehicles`
Tabel ini digunakan untuk mengelola data armada kendaraan, memantau akumulasi odometer, mencatat status kesehatan unit, serta memvalidasi kelayakan operasional kendaraan.

* **Nama Database:** `absen_driver`
* **Nama Tabel:** `vehicles`
* **Primary Key:** `id`

| No | Nama Field | Tipe Data | Size | Null | Key | Keterangan |
| :--- | :--- | :--- | :--- | :--- | :--- | :--- |
| 1 | `id` | Bigint | 20 | No | PK | Auto increment |
| 2 | `plate_number` | Varchar | 255 | No | Unique | Nomor plat kendaraan dinas |
| 3 | `type` | Varchar | 255 | Yes | - | Jenis armada (misalnya CDD, Fuso, Tronton) |
| 4 | `status` | Varchar | 50 | No | - | Status unit: `Aktif`, `Servis`, `Rusak` |
| 5 | `current_km` | Int | 11 | No | - | Akumulasi kilometer tempuh terkini |
| 6 | `service_interval_km`| Int | 11 | No | - | Batas kelipatan KM untuk servis rutin |
| 7 | `last_service_km` | Int | 11 | Yes | - | Posisi KM saat servis terakhir |
| 8 | `pajak_stnk_berlaku_sampai`| Date | - | Yes | - | Batas tanggal aktif STNK |
| 9 | `kir_berlaku_sampai`| Date | - | Yes | - | Batas tanggal aktif KIR DLLAJ |
| 10 | `project_id` | Bigint | 20 | Yes | FK | Relasi ke tabel `projects.id` |
| 11 | `customer_id` | Bigint | 20 | Yes | FK | Relasi ke tabel `customers.id` |
| 12 | `qr_code_path` | Varchar | 255 | Yes | - | Path berkas QR Code kendaraan |
| 13 | `is_verified` | Tinyint | 1 | No | - | Status verifikasi unit manual (0/1) |

#### 4. Spesifikasi Tabel `attendances`
Tabel transaksi utama yang berfungsi merekam seluruh riwayat absensi masuk dan pulang, foto odometer awal/akhir, koordinat GPS, hasil checklist kondisi ban/rem/lampu, serta data sinkronisasi offline.

* **Nama Database:** `absen_driver`
* **Nama Tabel:** `attendances`
* **Primary Key:** `id`

| No | Nama Field | Tipe Data | Size | Null | Key | Keterangan |
| :--- | :--- | :--- | :--- | :--- | :--- | :--- |
| 1 | `id` | Bigint | 20 | No | PK | Auto increment |
| 2 | `driver_id` | Bigint | 20 | No | FK | Relasi ke tabel `drivers.id` |
| 3 | `vehicle_id` | Bigint | 20 | No | FK | Relasi ke tabel `vehicles.id` |
| 4 | `time_in` | Datetime | - | No | - | Waktu absen masuk / Check-In |
| 5 | `time_out` | Datetime | - | Yes | - | Waktu absen pulang / Check-Out |
| 6 | `gps_location_in` | Varchar | 255 | No | - | Koordinat GPS Check-In (lat,long) |
| 7 | `gps_location_out` | Varchar | 255 | Yes | - | Koordinat GPS Check-Out (lat,long) |
| 8 | `speedo_awal` | Int | 11 | No | - | Kilometer awal odometer saat Check-In |
| 9 | `speedo_akhir` | Int | 11 | Yes | - | Kilometer akhir odometer saat Check-Out |
| 10 | `selfie_photo_path`| Varchar | 255 | No | - | Path foto selfie driver saat masuk |
| 11 | `speedo_photo_awal_path`| Varchar | 255 | No | - | Path foto speedometer awal |
| 12 | `speedo_photo_akhir_path`| Varchar | 255 | Yes | - | Path foto speedometer akhir |
| 13 | `check_ban` | Varchar | 50 | Yes | - | Hasil cek kondisi ban: `Aman`/`Bermasalah` |
| 14 | `check_lampu` | Varchar | 50 | Yes | - | Hasil cek kondisi lampu: `Aman`/`Bermasalah`|
| 15 | `check_rem` | Varchar | 50 | Yes | - | Hasil cek kondisi rem: `Aman`/`Bermasalah` |
| 16 | `catatan` | Text | - | Yes | - | Catatan kerusakan tambahan driver |
| 17 | `is_offline_recovery`| Tinyint | 1 | No | - | Flag apakah data disinkronkan secara offline |
| 18 | `offline_entry_id` | Varchar | 255 | Yes | Unique | UUID antrean offline dari device |

#### 5. Spesifikasi Tabel `vehicle_components`
Tabel ini berfungsi untuk memantau status kesehatan setiap suku cadang (seperti oli mesin, kampas rem, ban) pada masing-masing unit armada secara preventif.

* **Nama Database:** `absen_driver`
* **Nama Tabel:** `vehicle_components`
* **Primary Key:** `id`

| No | Nama Field | Tipe Data | Size | Null | Key | Keterangan |
| :--- | :--- | :--- | :--- | :--- | :--- | :--- |
| 1 | `id` | Bigint | 20 | No | PK | Auto increment |
| 2 | `vehicle_id` | Bigint | 20 | No | FK | Relasi ke tabel `vehicles.id` |
| 3 | `component_name` | Varchar | 100 | No | - | Nama komponen suku cadang |
| 4 | `category` | Varchar | 50 | No | - | Kategori: `mesin`, `transmisi`, `rem`, `ban` |
| 5 | `replacement_interval_km` | Int | 11 | Yes | - | Batas penggantian berdasarkan jarak KM |
| 6 | `replacement_interval_days` | Int | 11 | Yes | - | Batas penggantian berdasarkan hari/umur |
| 7 | `last_replacement_km` | Int | 11 | Yes | - | Angka KM ketika diganti terakhir kali |
| 8 | `last_replacement_date` | Date | - | Yes | - | Tanggal ketika diganti terakhir kali |
| 9 | `next_replacement_km` | Int | 11 | Yes | - | Estimasi KM servis berikutnya |
| 10 | `next_replacement_date` | Date | - | Yes | - | Estimasi tanggal servis berikutnya |
| 11 | `warning_threshold_km` | Int | 11 | No | - | Jarak aman sebelum alert Warning dipicu |
| 12 | `critical_threshold_km`| Int | 11 | No | - | Jarak aman sebelum alert Critical dipicu |
| 13 | `status` | Enum | - | No | - | Status: `healthy`, `warning`, `critical`, `overdue` |
| 14 | `cost_per_replacement`| Decimal | 10,2 | No | - | Estimasi biaya per pergantian unit |

#### 6. Spesifikasi Tabel `maintenance_schedules`
Tabel ini mengelola data pemeliharaan, baik yang bersifat rutin preventif maupun perbaikan korektif, termasuk pencatatan biaya, berkas PDF pengajuan keuangan, serta otorisasi tanda tangan digital penutupan tugas servis.

* **Nama Database:** `absen_driver`
* **Nama Tabel:** `maintenance_schedules`
* **Primary Key:** `id`

| No | Nama Field | Tipe Data | Size | Null | Key | Keterangan |
| :--- | :--- | :--- | :--- | :--- | :--- | :--- |
| 1 | `id` | Bigint | 20 | No | PK | Auto increment |
| 2 | `vehicle_id` | Bigint | 20 | No | FK | Relasi ke tabel `vehicles.id` |
| 3 | `component_id` | Bigint | 20 | Yes | FK | Relasi ke tabel `vehicle_components.id` |
| 4 | `scheduled_date` | Date | - | No | - | Tanggal rencana pemeliharaan |
| 5 | `scheduled_km` | Int | 11 | Yes | - | Target KM kendaraan saat harus masuk bengkel |
| 6 | `type` | Enum | - | No | - | Jenis: `preventive`, `corrective`, `predictive` |
| 7 | `priority` | Enum | - | No | - | Prioritas: `low`, `medium`, `high`, `critical` |
| 8 | `status` | Enum | - | No | - | Status: `pending`, `scheduled`, `in_progress`, `completed`, `cancelled` |
| 9 | `estimated_cost` | Decimal | 10,2 | No | - | Estimasi biaya awal |
| 10 | `actual_cost` | Decimal | 10,2 | Yes | - | Biaya riil/aktual setelah selesai perawatan |
| 11 | `workshop_name` | Varchar | 255 | Yes | - | Nama bengkel pengerjaan |
| 12 | `finance_pdf_path` | Varchar | 255 | Yes | - | Path berkas PDF pengajuan anggaran ke finance |
| 13 | `admin_signature_path`| Varchar | 255 | Yes | - | Path gambar tanda tangan digital admin penyelesai |
| 14 | `completed_at` | Timestamp | - | Yes | - | Waktu penyelesaian tugas servis |
| 15 | `completed_by` | Bigint | 20 | Yes | FK | Relasi ke tabel `users.id` (Admin penyelesai) |

#### 7. Spesifikasi Tabel `maintenance_alerts`
Tabel ini mencatat log peringatan (*alert*) komponen kendaraan yang terdeteksi secara otomatis oleh proses cron job.

* **Nama Database:** `absen_driver`
* **Nama Tabel:** `maintenance_alerts`
* **Primary Key:** `id`

| No | Nama Field | Tipe Data | Size | Null | Key | Keterangan |
| :--- | :--- | :--- | :--- | :--- | :--- | :--- |
| 1 | `id` | Bigint | 20 | No | PK | Auto increment |
| 2 | `vehicle_id` | Bigint | 20 | No | FK | Relasi ke tabel `vehicles.id` |
| 3 | `component_id` | Bigint | 20 | No | FK | Relasi ke `vehicle_components.id` |
| 4 | `alert_type` | Varchar | 50 | No | - | Jenis alert: `warning`, `critical`, `overdue` |
| 5 | `message` | Text | - | No | - | Detail pesan deskripsi alert |
| 6 | `status` | Varchar | 50 | No | - | Status alert: `active`, `acknowledged`, `resolved` |
| 7 | `triggered_at` | Timestamp | - | No | - | Waktu pembuatan alert otomatis |
| 8 | `acknowledged_at`| Timestamp | - | Yes | - | Waktu alert di-acknowledge oleh admin |
| 9 | `resolved_at` | Timestamp | - | Yes | - | Waktu alert diselesaikan/resolve |

#### 8. Spesifikasi Tabel `transport_costs`
Tabel ini merekam pengajuan uang jalan pengemudi yang mencakup biaya bahan bakar, tol, dan parkir, beserta status persetujuannya.

* **Nama Database:** `absen_driver`
* **Nama Tabel:** `transport_costs`
* **Primary Key:** `id`

| No | Nama Field | Tipe Data | Size | Null | Key | Keterangan |
| :--- | :--- | :--- | :--- | :--- | :--- | :--- |
| 1 | `id` | Bigint | 20 | No | PK | Auto increment |
| 2 | `driver_id` | Bigint | 20 | No | FK | Relasi ke tabel `drivers.id` |
| 3 | `vehicle_id` | Bigint | 20 | No | FK | Relasi ke tabel `vehicles.id` |
| 4 | `attendance_id` | Bigint | 20 | No | FK | Relasi ke tabel `attendances.id` |
| 5 | `trip_date` | Date | - | No | - | Tanggal pelaksanaan perjalanan |
| 6 | `gasoline_cost` | Decimal | 10,2 | No | - | Biaya konsumsi BBM |
| 7 | `toll_cost` | Decimal | 10,2 | No | - | Biaya penggunaan jalan tol |
| 8 | `parking_cost` | Decimal | 10,2 | No | - | Biaya parkir kendaraan |
| 9 | `approval_status` | Varchar | 50 | No | - | Status: `pending`, `approved`, `rejected` |

#### 9. Spesifikasi Tabel `offline_recovery_logs`
Tabel ini berfungsi sebagai metrik audit sinkronisasi guna mengukur efisiensi penanganan data offline yang dikirimkan dari aplikasi mobile.

* **Nama Database:** `absen_driver`
* **Nama Tabel:** `offline_recovery_logs`
* **Primary Key:** `id`

| No | Nama Field | Tipe Data | Size | Null | Key | Keterangan |
| :--- | :--- | :--- | :--- | :--- | :--- | :--- |
| 1 | `id` | Bigint | 20 | No | PK | Auto increment |
| 2 | `driver_id` | Bigint | 20 | No | FK | Relasi ke tabel `drivers.id` |
| 3 | `attendance_id` | Bigint | 20 | Yes | FK | Relasi ke tabel `attendances.id` |
| 4 | `offline_entry_id` | Varchar | 255 | No | - | UUID dari perangkat mobile |
| 5 | `device_timestamp` | Datetime | - | No | - | Waktu transaksi dilakukan di device supir |
| 6 | `recovery_timestamp`| Datetime | - | No | - | Waktu data diterima di server |
| 7 | `delay_minutes` | Int | 11 | No | - | Selisih waktu delay (dalam menit) |
| 8 | `result` | Varchar | 50 | No | - | Status sync: `success`, `failed`, `duplicate` |
| 9 | `retry_count` | Int | 11 | No | - | Jumlah percobaan pengiriman ulang |

#### 10. Spesifikasi Tabel `emergency_reports`
Tabel ini merekam laporan insiden darurat (seperti mogok mesin atau kecelakaan lalu lintas) yang dilaporkan oleh pengemudi ketika sedang dalam perjalanan.

* **Nama Database:** `absen_driver`
* **Nama Tabel:** `emergency_reports`
* **Primary Key:** `id`

| No | Nama Field | Tipe Data | Size | Null | Key | Keterangan |
| :--- | :--- | :--- | :--- | :--- | :--- | :--- |
| 1 | `id` | Bigint | 20 | No | PK | Auto increment |
| 2 | `driver_id` | Bigint | 20 | No | FK | Relasi ke tabel `drivers.id` |
| 3 | `vehicle_id` | Bigint | 20 | No | FK | Relasi ke tabel `vehicles.id` |
| 4 | `timestamp` | Datetime | - | No | - | Waktu terjadinya kejadian darurat |
| 5 | `gps_location` | Varchar | 255 | No | - | Titik koordinat lokasi darurat (lat,long) |
| 6 | `description` | Text | - | No | - | Narasi detail kejadian darurat |
| 7 | `proof_photo_path` | Varchar | 255 | No | - | Path foto bukti kondisi di lapangan |
| 8 | `follow_up_status` | Varchar | 50 | No | - | Status penanganan: `pending`, `processed`, `resolved` |

---

## 4.3 Rancangan Antarmuka

Rancangan antarmuka ini menyajikan tata letak serta struktur navigasi layar yang dirancang guna memudahkan pengguna dalam berinteraksi dengan sistem usulan.

### 4.3.1 Struktur Navigasi

#### A. Struktur Navigasi Aplikasi Mobile (Driver)
Aplikasi mobile menerapkan pola navigasi berbasis tab dan tumpukan (*stack navigation*) dengan hirarki alur sebagai berikut:

```text
[Splash Screen]
       │
       ▼ (Check Auth)
  [Login Screen]
       │
       ▼ (Sukses Login)
  [Home Dashboard (Main Tab)]
       ├── Tab 1: Home (Status tugas, Tombol Check-In / Check-Out)
       │     ├── Button Check-In ──► [Camera / QR Scanner Screen] ──► [Check-In Form]
       │     └── Button Check-Out ──► [Check-Out Form] ──► [Duty Summary Screen]
       ├── Tab 2: History (Riwayat absensi dan status sinkronisasi offline)
       │     └── Item Click ──► [Detail Attendance Screen]
       ├── Tab 3: Reports (Form Lapor Darurat & Service Report)
       │     ├── Button Emergency ──► [Form Lapor Darurat Screen]
       │     └── Button Service ──► [Form Service Report Screen]
       └── Tab 4: Profile (Data SIM, Ganti Password, Panduan, Logout)
```

#### B. Struktur Navigasi Web Dashboard (Admin & Customer)
Web Dashboard menerapkan pola navigasi Sidebar bertingkat yang disesuaikan berdasarkan otorisasi peran pengguna (*role-based redirection*):

```text
[Login Page]
       │
       ▼ (Redirection sesuai Role)
[Sidebar Menu]
       ├── Dashboard (Metrik armada aktif, komponen critical, alert terbaru)
       ├── Data Master (Hanya Role Master Admin)
       │     ├── Kelola Driver (Tambah, edit, cetak QR Driver)
       │     ├── Kelola Kendaraan (Tambah, edit, verifikasi unit manual, cetak QR Plat)
       │     ├── Kelola Project & Customer
       │     └── Kelola User System (Admin, Service Admin)
       ├── Monitoring Absensi
       │     ├── Log Absensi Real-time (Peta GPS, detail foto selfie & odometer)
       │     ├── Audit Sinkronisasi Offline (Log Delay recovery)
       │     └── Export Laporan (Excel & PDF)
       ├── Manajemen Pemeliharaan (Preventive Maintenance)
       │     ├── Status Kesehatan Komponen (Filter: Warning, Critical, Overdue)
       │     ├── Jadwal Servis (Buat SPK, Selesaikan tugas, upload invoice)
       │     └── Log Riwayat Servis Kendaraan
       └── Approval Alur Kerja
             ├── Approval Biaya Transport (Toll/Bensin)
             └── Approval Laporan Perbaikan (Ditujukan untuk Customer & Service Admin)
```

---

### 4.3.2 Desain Input dan Output (Mockup Layout)

#### A. Mockup Form Check-In Aplikasi Mobile
Formulir masukan digital pada perangkat handphone pengemudi dirancang dengan tata letak yang bersih (*clean layout*) serta dilengkapi panduan petunjuk visual yang jelas dan intuitif:

```text
+------------------------------------------+
|  <- ABSEN CHECK-IN (MULAI TUGAS)         |
+------------------------------------------+
|  [ FOTO SPEEDOMETER AWAL ]               |
|  +------------------------------------+  |
|  |             [Kamera]               |  |
|  |     Ambil foto fisik odometer      |  |
|  +------------------------------------+  |
|                                          |
|  [ FOTO SELFIE DENGAN UNIT ]             |
|  +------------------------------------+  |
|  |             [Kamera]               |  |
|  |        Ambil foto selfie           |  |
|  +------------------------------------+  |
|                                          |
|  PLAT KENDARAAN                          |
|  [ B-9243-UXX (Terdeteksi via QR Scan) ] |
|                                          |
|  ANGKA SPEEDOMETER MANUAL (KM)           |
|  [ 124500                              ] |
|                                          |
|  +------------------------------------+  |
|  |          SUBMIT CHECK-IN           |  |
|  +------------------------------------+  |
+------------------------------------------+
```

#### B. Mockup Monitoring Dashboard Web Admin
Tata letak antarmuka dashboard utama web admin yang dirancang untuk memantau kondisi kesehatan armada PT Hamada Global Jaya secara langsung:

```text
+-------------------------------------------------------------------------+
| [LOGO] PT HAMADA GLOBAL JAYA                               (Halo, Admin)|
+-------------------------------------------------------------------------+
| (Sidebar)  |  KESEHATAN ARMADA KENDARAAN (REAL-TIME)                    |
| > Dash     |  +-------------+  +-------------+  +-------------+         |
| > Drivers  |  | UNIT AKTIF  |  | ALERT KRITIS|  | SERVIS BULAN|         |
| > Vehicles |  |   124 Unit  |  |   8 Komponen|  |   14 Jadwal |         |
| > Absen    |  +-------------+  +-------------+  +-------------+         |
| > Servis   |                                                            |
| > Approval |  DAFTAR ANTRIAN ALERT TERBARU                              |
|            |  +------------+-----------------+----------+-------------+ |
|            |  | Plat No    | Komponen        | Status   | Aksi        | |
|            |  +------------+-----------------+----------+-------------+ |
|            |  | B-9211-TXX | Oli Mesin       | Overdue  | [Buat SPK]  | |
|            |  | B-1024-FXX | Kampas Rem Depan| Critical | [Bengkel]   | |
|            |  | B-8302-UXX | Ban Kiri Depan  | Warning  | [Pantau]    | |
|            |  +------------+-----------------+----------+-------------+ |
+-------------------------------------------------------------------------+
```

---

## 4.4 Spesifikasi Hardware dan Software

Guna menunjang proses pengembangan serta pengoperasian sistem usulan agar dapat berjalan secara optimal, diperlukan spesifikasi minimal perangkat keras (*hardware*) dan perangkat lunak (*software*) sebagai berikut:

### 4.4.1 Lingkungan Pengembangan (Development Environment)
Perangkat yang dipergunakan oleh pengembang untuk memprogram bagian backend dan aplikasi mobile:
* **Perangkat Keras (Hardware):**
  * Laptop atau PC dengan prosesor Intel Core i5 / AMD Ryzen 5 atau setara ke atas (minimum 4 Cores)
  * Kapasitas RAM minimum 16 GB (disarankan agar Emulator Android dan VS Code dapat berjalan bersamaan secara lancar)
  * Media penyimpanan berjenis SSD dengan kapasitas minimum 512 GB (untuk mempercepat pembacaan data SDK)
* **Perangkat Lunak (Software):**
  * Sistem Operasi: Windows 10/11 64-bit atau macOS Sequoia
  * IDE: Visual Studio Code (untuk Flutter & Laravel) dan Android Studio (untuk pengelolaan SDK)
  * Bahasa Pemrograman: PHP 8.2 (Backend Laravel) dan Dart 3.x (Frontend Flutter)
  * Framework: Laravel 11 dan Flutter SDK 3.22.x
  * Database Engine: MySQL / MariaDB (lingkungan pengembangan lokal melalui XAMPP/Laragon)

### 4.4.2 Lingkungan Server Produksi (Production Server Environment)
Perangkat server berbasis cloud yang menjadi tempat aplikasi di-deploy untuk digunakan oleh PT Hamada Global Jaya:
* **Perangkat Keras (Hardware VPS Cloud):**
  * Virtual Private Server (VPS) dengan minimum 2 vCPU
  * Kapasitas RAM 4 GB
  * Media penyimpanan SSD berkapasitas 80 GB (menyesuaikan dengan volume unggahan foto odometer harian)
  * Bandwidth tidak terbatas (*unmetered*) 100 Mbps
* **Perangkat Lunak (Software Server):**
  * Sistem Operasi: Linux Ubuntu Server 22.04 LTS 64-bit
  * Web Server: Nginx (berperan sebagai Reverse Proxy dan SSL Termination)
  * FastCGI Process Manager: PHP 8.2-FPM
  * Database Server: MySQL Server 8.0
  * Task Scheduler: Linux System Cron Utility (mengeksekusi perintah `php artisan schedule:run` setiap menit)
  * SSL Certificate: Let's Encrypt Certbot (untuk koneksi HTTPS yang aman)

### 4.4.3 Lingkungan Pengguna Akhir (Client Device Requirement)
Perangkat genggam yang digunakan oleh pengemudi armada di lapangan:
* **Aplikasi Driver Mobile (Client Android):**
  * Smartphone Android dengan Sistem Operasi Android 8.0 (Oreo) atau versi yang lebih baru
  * Kapasitas RAM minimum 3 GB
  * Dilengkapi sensor GPS (*Global Positioning System*) yang aktif
  * Dilengkapi kamera belakang minimum 8 MP dengan fitur autofokus (untuk keperluan pemindaian QR Code dan pengambilan foto speedometer)
  * Memiliki koneksi jaringan internet seluler (minimum 3G/HSPA, disarankan 4G LTE/5G)
