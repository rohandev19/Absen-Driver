# Class Diagram Sistem Absensi dan Manajemen Armada

Dokumen ini berisi Class Diagram lengkap yang dibuat berdasarkan **16 Model Eloquent** aktual di project backend (`app/Models/*.php`).

Dipecah menjadi 3 domain fungsional agar rapi dan mudah dibaca di dokumen skripsi:

---

## 1. Class Diagram Domain Master & Absensi (Core)

Menggambarkan kelas-kelas utama master data dan proses absensi driver.

```mermaid
classDiagram
    class Customer {
        +int id
        +string name
        +string code
        +string contact_person
        +string email
        +string phone
        +string address
    }

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

    class Project {
        +int id
        +string name
        +string code
        +string description
        +int customer_id
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
        +bool is_temporary
        +string verification_status
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

    Customer "1" --> "*" User : memiliki
    Customer "1" --> "*" Project : memiliki
    Project "1" --> "*" Driver : memiliki
    Project "1" --> "*" Vehicle : mengalokasikan
    Driver "1" --> "*" Attendance : melakukan
    Vehicle "1" --> "*" Attendance : digunakan
```

---

## 2. Class Diagram Domain Operasional & Layanan

Menggambarkan kelas-kelas operasional: pengajuan biaya, laporan darurat, laporan servis, penggantian kendaraan, audit, dan sinkronisasi offline.

```mermaid
classDiagram
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
        +int approved_by
        +getTotalCostAttribute() decimal
    }

    class EmergencyReport {
        +int id
        +int driver_id
        +int vehicle_id
        +int service_report_id
        +string gps_location
        +datetime timestamp
        +string description
        +string follow_up_status
    }

    class ServiceReport {
        +int id
        +int driver_id
        +int vehicle_id
        +int customer_id
        +int approved_by_admin_id
        +int approved_by_customer_id
        +string ticket_number
        +string service_type
        +string problem_category
        +decimal total_cost
        +string workshop_name
        +string status
    }

    class VehicleReplacement {
        +int id
        +int original_vehicle_id
        +int replacement_vehicle_id
        +int driver_id
        +int service_report_id
        +string reason
        +string status
    }

    class AuditHistory {
        +int id
        +string report_id
        +string type
        +string status
        +int total_findings
        +int critical_count
        +float execution_time_seconds
        +string triggered_by
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
    }

    Attendance "1" --> "0..1" TransportCost : mengajukan
    Driver "1" --> "*" EmergencyReport : melaporkan
    Vehicle "1" --> "*" EmergencyReport : mengalami
    EmergencyReport "*" --> "0..1" ServiceReport : ditindaklanjuti
    Driver "1" --> "*" ServiceReport : mengajukan
    ServiceReport "1" --> "*" VehicleReplacement : menghasilkan
    Attendance "1" --> "0..1" OfflineRecoveryLog : dicatat
```

---

## 3. Class Diagram Domain Preventive Maintenance

Menggambarkan kelas-kelas perawatan preventif kendaraan.

```mermaid
classDiagram
    class Vehicle {
        +int id
        +string plate_number
        +int current_km
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

    class MaintenanceLog {
        +int id
        +int vehicle_id
        +int recorded_by_user_id
        +date service_date
        +int km_at_service
        +string description
        +string workshop_name
    }

    class MaintenanceSchedule {
        +int id
        +int vehicle_id
        +int component_id
        +int completed_by
        +date scheduled_date
        +int scheduled_km
        +string type
        +string priority
        +string status
        +decimal estimated_cost
        +decimal actual_cost
        +string finance_pdf_path
        +datetime completed_at
        +markAsCompleted() void
        +isOverdue() bool
    }

    class MaintenanceAlert {
        +int id
        +int vehicle_id
        +int component_id
        +int acknowledged_by
        +string alert_type
        +string message
        +datetime triggered_at
        +string status
        +acknowledge() void
        +resolve() void
        +dismiss() void
    }

    Vehicle "1" --> "*" VehicleComponent : memiliki
    Vehicle "1" --> "*" MaintenanceLog : memiliki_riwayat
    VehicleComponent "1" --> "*" MaintenanceSchedule : dijadwalkan
    VehicleComponent "1" --> "*" MaintenanceAlert : memicu
```

---

## Ringkasan 16 Kelas Domain (Model Eloquent)

| No | Nama Kelas (Model) | Fungsi Utama |
|---|---|---|
| 1 | `Customer` | Kelola data pelanggan/klien |
| 2 | `User` | Kelola akun pengguna sistem & hak akses |
| 3 | `Project` | Kelola proyek operasional pelanggan |
| 4 | `Driver` | Kelola data pengemudi/driver |
| 5 | `Vehicle` | Kelola data armada kendaraan |
| 6 | `Attendance` | Catatan absensi & inspeksi awal/akhir driver |
| 7 | `TransportCost` | Pengajuan biaya bensin, tol, & operasional |
| 8 | `EmergencyReport` | Laporan kendala darurat di jalan |
| 9 | `ServiceReport` | Laporan pengajuan servis kendaraan |
| 10 | `VehicleReplacement` | Catatan unit kendaraan pengganti sementara |
| 11 | `AuditHistory` | Rekam riwayat inspeksi & audit otomatis sistem |
| 12 | `OfflineRecoveryLog` | Log sinkronisasi data absensi mode offline |
| 13 | `VehicleComponent` | Tracking komponen & suku cadang armada |
| 14 | `MaintenanceLog` | Riwayat fisik servis & perawatan kendaraan |
| 15 | `MaintenanceSchedule` | Jadwal perawatan preventif berkala |
| 16 | `MaintenanceAlert` | Peringatan otomatis batas KM/servis |
