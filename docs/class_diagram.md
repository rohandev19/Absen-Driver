# Class Diagram Sistem Absensi dan Manajemen Armada

Dokumen ini berisi class diagram yang dibuat berdasarkan model aktual di project. Dipecah menjadi 3 bagian agar tidak terlalu besar di Word.

## 1. Class Diagram Domain Absensi (Core)

Menggambarkan kelas-kelas utama yang terlibat dalam proses absensi driver.

```mermaid
classDiagram
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

    class Project {
        +int id
        +string name
        +string code
        +string description
        +int customer_id
    }

    class Customer {
        +int id
        +string name
        +string code
        +string contact_person
        +string email
        +string phone
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

    Customer "1" --> "*" Project : memiliki
    Project "1" --> "*" Driver : memiliki
    Project "1" --> "*" Vehicle : memiliki
    Driver "1" --> "*" Attendance : melakukan
    Vehicle "1" --> "*" Attendance : digunakan
    Customer "1" --> "*" User : memiliki
```

## 2. Class Diagram Domain Maintenance

Menggambarkan kelas-kelas yang terlibat dalam preventive maintenance kendaraan.

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
        +int scheduled_km
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

## 3. Class Diagram Domain Pendukung

Menggambarkan kelas-kelas pendukung: transport cost, service report, laporan darurat, dan offline recovery.

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
        +string workshop_name
        +int approved_by_admin_id
        +int approved_by_customer_id
    }

    class EmergencyReport {
        +int id
        +int driver_id
        +int vehicle_id
        +string gps_location
        +string description
        +string follow_up_status
        +int service_report_id
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

    class VehicleReplacement {
        +int id
        +int original_vehicle_id
        +int replacement_vehicle_id
        +int driver_id
        +int service_report_id
        +string status
    }

    ServiceReport "1" --> "*" VehicleReplacement : menghasilkan
    EmergencyReport "*" --> "0..1" ServiceReport : ditindaklanjuti
```

## Catatan

- Class diagram dibuat berdasarkan model di `app/Models/*.php`.
- Untuk skripsi, atribut foto path (selfie_photo_path, dll) tidak ditampilkan agar diagram tetap ringkas.
- Method yang ditampilkan hanya method bisnis utama, bukan getter/setter atau scope Eloquent.
- Relasi antar class ditampilkan dengan multiplicity (1 ke banyak, dll).
