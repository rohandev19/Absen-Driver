# 4.2.2. Logical Record Structure (LRS)

Dokumen ini berisi struktur **Logical Record Structure (LRS)** untuk **Sistem Absensi dan Manajemen Armada Driver** yang mencakup 16 tabel relasional.

LRS digambarkan dalam bentuk struktur rekaman logika tabel (kotak rekaman) lengkap dengan penanda **Primary Key `*(primary)`**, **Foreign Key `**(foreign)`**, serta kardinalitas relasi **`1`** ke **`M`** (One to Many).

---

## Visualisasi Interactive & Export File
Untuk melihat dan mengunduh format gambar LRS siap cetak A4 Skripsi (Hitam-Putih/B&W):
- Buka file **[docs/lrs_canvas_only.html](file:///c:/Users/ACER/absen_backend/docs/lrs_canvas_only.html)** di browser untuk mengunduh/mencetak dokumen LRS.

---

## 1. Diagram LRS (Mermaid Format)

```mermaid
erDiagram
    customers {
        int id PK "*(primary)"
        string name
        string code
        string contact_person
        string email
        string phone
    }

    users {
        int id PK "*(primary)"
        int customer_id FK "**(foreign)"
        string name
        string email
        string password
        string role
    }

    projects {
        int id PK "*(primary)"
        int customer_id FK "**(foreign)"
        string name
        string code
        string description
    }

    drivers {
        int id PK "*(primary)"
        int project_id FK "**(foreign)"
        string driver_id_nik
        string full_name
        string nik_ktp
        date sim_expiry_date
        string sim_type
        bool is_on_duty
    }

    vehicles {
        int id PK "*(primary)"
        int project_id FK "**(foreign)"
        int customer_id FK "**(foreign)"
        string plate_number
        string type
        string status
        int current_km
    }

    attendances {
        int id PK "*(primary)"
        int driver_id FK "**(foreign)"
        int vehicle_id FK "**(foreign)"
        datetime time_in
        datetime time_out
        string gps_location_in
        int speedo_awal
        int speedo_akhir
        bool is_offline_recovery
    }

    transport_costs {
        int id PK "*(primary)"
        int driver_id FK "**(foreign)"
        int vehicle_id FK "**(foreign)"
        int project_id FK "**(foreign)"
        int attendance_id FK "**(foreign)"
        date trip_date
        decimal gasoline_cost
        decimal toll_cost
        string approval_status
    }

    emergency_reports {
        int id PK "*(primary)"
        int driver_id FK "**(foreign)"
        int vehicle_id FK "**(foreign)"
        int service_report_id FK "**(foreign)"
        datetime timestamp
        string description
        string follow_up_status
    }

    service_reports {
        int id PK "*(primary)"
        int driver_id FK "**(foreign)"
        int vehicle_id FK "**(foreign)"
        int customer_id FK "**(foreign)"
        int approved_by_admin_id FK "**(foreign)"
        string ticket_number
        string service_type
        decimal total_cost
        string status
    }

    vehicle_replacements {
        int id PK "*(primary)"
        int original_vehicle_id FK "**(foreign)"
        int replacement_vehicle_id FK "**(foreign)"
        int driver_id FK "**(foreign)"
        int service_report_id FK "**(foreign)"
        string reason
        string status
    }

    audit_histories {
        int id PK "*(primary)"
        string report_id
        string type
        string status
        int total_findings
        int critical_count
        float execution_time_seconds
    }

    offline_recovery_logs {
        int id PK "*(primary)"
        int driver_id FK "**(foreign)"
        int attendance_id FK "**(foreign)"
        string offline_entry_id
        datetime device_timestamp
        datetime recovery_timestamp
        string result
    }

    vehicle_components {
        int id PK "*(primary)"
        int vehicle_id FK "**(foreign)"
        string component_name
        string category
        int replacement_interval_km
        int last_replacement_km
        int next_replacement_km
        string status
    }

    maintenance_logs {
        int id PK "*(primary)"
        int vehicle_id FK "**(foreign)"
        int recorded_by_user_id FK "**(foreign)"
        date service_date
        int km_at_service
        string description
        string workshop_name
    }

    maintenance_schedules {
        int id PK "*(primary)"
        int vehicle_id FK "**(foreign)"
        int component_id FK "**(foreign)"
        int completed_by FK "**(foreign)"
        date scheduled_date
        int scheduled_km
        string priority
        string status
    }

    maintenance_alerts {
        int id PK "*(primary)"
        int vehicle_id FK "**(foreign)"
        int component_id FK "**(foreign)"
        int acknowledged_by FK "**(foreign)"
        string alert_type
        string message
        datetime triggered_at
        string status
    }

    customers ||--o{ users : "1 : M"
    customers ||--o{ projects : "1 : M"
    customers ||--o{ vehicles : "1 : M"
    projects ||--o{ drivers : "1 : M"
    projects ||--o{ vehicles : "1 : M"
    drivers ||--o{ attendances : "1 : M"
    vehicles ||--o{ attendances : "1 : M"
    attendances ||--|| transport_costs : "1 : 1"
    drivers ||--o{ emergency_reports : "1 : M"
    vehicles ||--o{ emergency_reports : "1 : M"
    drivers ||--o{ service_reports : "1 : M"
    vehicles ||--o{ service_reports : "1 : M"
    emergency_reports }o--|| service_reports : "M : 1"
    service_reports ||--o{ vehicle_replacements : "1 : M"
    attendances ||--|| offline_recovery_logs : "1 : 1"
    vehicles ||--o{ vehicle_components : "1 : M"
    vehicle_components ||--o{ maintenance_schedules : "1 : M"
    vehicle_components ||--o{ maintenance_alerts : "1 : M"
    vehicles ||--o{ maintenance_logs : "1 : M"
```

---

## 2. Rincian Relasi & Kardinalitas LRS

| Tabel Asal (From) | Tabel Tujuan (To) | Kardinalitas | Penjelasan Relasi |
| :--- | :--- | :---: | :--- |
| `customers` | `users` | `1 : M` | Satu customer dapat memiliki banyak pengguna/staf. |
| `customers` | `projects` | `1 : M` | Satu customer dapat memiliki banyak proyek. |
| `customers` | `vehicles` | `1 : M` | Satu customer dapat memiliki banyak unit armada. |
| `projects` | `drivers` | `1 : M` | Satu proyek membawahi banyak pengemudi. |
| `projects` | `vehicles` | `1 : M` | Satu proyek membawahi banyak unit kendaraan. |
| `drivers` | `attendances` | `1 : M` | Satu driver melakukan banyak transaksi absensi. |
| `vehicles` | `attendances` | `1 : M` | Satu kendaraan digunakan dalam banyak transaksi absensi. |
| `attendances` | `transport_costs` | `1 : 1` | Satu absensi dapat menghasilkan 1 klaim biaya perjalanan. |
| `drivers` | `emergency_reports` | `1 : M` | Satu driver dapat melaporkan banyak kejadian darurat. |
| `vehicles` | `emergency_reports` | `1 : M` | Satu kendaraan dapat terlibat banyak laporan darurat. |
| `drivers` | `service_reports` | `1 : M` | Satu driver dapat mengajukan banyak perbaikan servis. |
| `vehicles` | `service_reports` | `1 : M` | Satu kendaraan dapat menjalani banyak perbaikan servis. |
| `emergency_reports` | `service_reports` | `M : 1` | Banyak laporan darurat dapat ditindaklanjuti ke 1 laporan servis. |
| `service_reports` | `vehicle_replacements` | `1 : M` | Satu laporan servis dapat membutuhkan penggantian kendaraan. |
| `attendances` | `offline_recovery_logs` | `1 : 1` | Satu absensi offline memiliki 1 catatan log pemulihan data. |
| `vehicles` | `vehicle_components` | `1 : M` | Satu kendaraan memiliki banyak komponen yang dipantau. |
| `vehicle_components` | `maintenance_schedules` | `1 : M` | Satu komponen memiliki banyak jadwal pemeliharaan. |
| `vehicle_components` | `maintenance_alerts` | `1 : M` | Satu komponen dapat memicu banyak peringatan servis. |
| `vehicles` | `maintenance_logs` | `1 : M` | Satu kendaraan memiliki banyak riwayat pencatatan servis. |
