# Sequence Diagram Preventive Maintenance

Dokumen ini berisi sequence diagram untuk alur preventive maintenance pada modul maintenance kendaraan.

## 1. Generate Jadwal Preventive Otomatis

Diagram ini menggambarkan alur scheduler membuat jadwal preventive berdasarkan status komponen kendaraan.

```mermaid
sequenceDiagram
    participant Scheduler as Laravel Scheduler
    participant Command as GenerateSchedules
    participant DB as Database

    Scheduler->>Command: Jalankan maintenance:generate-schedules
    Command->>DB: Ambil data kendaraan beserta komponen
    DB-->>Command: Daftar kendaraan dan komponen

    loop Setiap komponen kendaraan
        Command->>Command: Hitung status komponen (KM / tanggal)

        alt Komponen tidak perlu maintenance
            Command->>Command: Lewati
        else Komponen perlu maintenance
            Command->>DB: Cek jadwal pending atau scheduled
            DB-->>Command: Jadwal aktif / tidak ada

            alt Jadwal aktif sudah ada
                Command->>Command: Lewati agar tidak duplikat
            else Belum ada jadwal
                Command->>Command: Tentukan prioritas dan tanggal jadwal
                Note over Command: overdue → critical<br/>critical → high<br/>warning → medium
                Command->>DB: Buat jadwal preventive (status pending)
                DB-->>Command: Jadwal tersimpan
            end
        end
    end

    Command-->>Scheduler: Ringkasan jadwal dibuat / dilewati
```

## 2. Buat Jadwal Preventive Manual

Diagram ini menggambarkan alur admin ketika menambahkan jadwal preventive dari halaman maintenance schedule.

```mermaid
sequenceDiagram
    actor Admin
    participant Browser as Web Browser
    participant Controller as MaintenanceScheduleController
    participant Schedule as MaintenanceSchedule Model
    participant DB as Database

    Admin->>Browser: Buka halaman Jadwal Maintenance
    Browser->>Controller: GET /admin/maintenance/schedules
    Controller->>DB: SELECT schedules with vehicle and component
    DB-->>Controller: Data jadwal
    Controller-->>Browser: Render admin.maintenance.schedules
    Browser-->>Admin: Tampilkan daftar jadwal

    Admin->>Browser: Isi form jadwal preventive
    Note over Browser: vehicle_id, component_id,<br/>scheduled_date, scheduled_km,<br/>priority, type=preventive,<br/>estimated_cost, workshop_name, notes

    Browser->>Controller: POST /admin/maintenance/schedules/store
    Controller->>Controller: Validasi request

    alt Validasi gagal
        Controller-->>Browser: Redirect back + error validasi
        Browser-->>Admin: Tampilkan pesan error
    else Validasi berhasil
        Controller->>Schedule: Create schedule
        Schedule->>DB: INSERT maintenance_schedules
        Note over DB: status=pending<br/>type=preventive<br/>priority dan schedule data tersimpan
        DB-->>Schedule: Schedule created
        Schedule-->>Controller: OK

        Controller-->>Browser: Redirect back + success
        Browser-->>Admin: Jadwal pemeliharaan berhasil ditambahkan
    end
```

## 3. Selesaikan Preventive Maintenance

Diagram ini menggambarkan alur penyelesaian jadwal preventive. Setelah selesai, sistem memperbarui schedule, mengubah data penggantian komponen, dan membuat dokumen PDF finance.

```mermaid
sequenceDiagram
    actor Admin
    participant Browser as Web Browser
    participant Controller as MaintenanceScheduleController
    participant ImageSvc as ImageProcessingService
    participant PdfSvc as MaintenanceSchedulePdfService
    participant Schedule as MaintenanceSchedule Model
    participant Component as VehicleComponent Model
    participant Storage as Public Storage
    participant DB as Database

    Admin->>Browser: Klik Selesaikan Maintenance
    Browser->>Controller: POST /admin/maintenance/schedules/{id}/complete
    Note over Browser: actual_cost, notes,<br/>signer_name, signer_role,<br/>signature, receipt_photo,<br/>odometer_photo

    Controller->>Schedule: findOrFail(scheduleId)
    Schedule->>DB: SELECT maintenance_schedules WHERE id
    DB-->>Schedule: Schedule record
    Schedule-->>Controller: Schedule

    Controller->>Controller: Validasi input penyelesaian

    alt Input tidak valid
        Controller-->>Browser: Redirect back + error
        Browser-->>Admin: Tampilkan error
    else Input valid
        Controller->>Controller: Decode base64 signature
        Controller->>Storage: Simpan signature file
        Storage-->>Controller: signature path

        Controller->>ImageSvc: Optimize receipt_photo
        ImageSvc-->>Controller: receipt_photo_path
        Controller->>ImageSvc: Optimize odometer_photo
        ImageSvc-->>Controller: odometer_photo_path

        Controller->>Schedule: Update completion data
        Schedule->>DB: UPDATE maintenance_schedules
        Note over DB: status=completed<br/>completed_at=now<br/>completed_by=Auth::id()<br/>actual_cost and photo paths
        DB-->>Schedule: Updated

        alt Schedule punya component
            Controller->>Component: Update last replacement
            Component->>DB: UPDATE vehicle_components
            Note over Component: saving hook menghitung ulang<br/>next_replacement_km/date dan status
            DB-->>Component: Updated
        end

        Controller->>PdfSvc: generateFinanceSubmission(schedule)
        PdfSvc->>Storage: Simpan PDF finance
        Storage-->>PdfSvc: finance_pdf_path
        PdfSvc-->>Controller: finance_pdf_path

        Controller->>Schedule: Simpan finance_pdf_path
        Schedule->>DB: UPDATE maintenance_schedules SET finance_pdf_path
        DB-->>Schedule: Updated

        Controller-->>Browser: Redirect back + success
        Browser-->>Admin: Maintenance selesai dan dokumen finance dibuat
    end
```

## Catatan Implementasi

- Jadwal otomatis dibuat oleh `app/Console/Commands/GenerateMaintenanceSchedules.php`.
- Jadwal manual dan penyelesaian jadwal diproses oleh `app/Http/Controllers/MaintenanceScheduleController.php`.
- Status komponen dihitung di `app/Models/VehicleComponent.php` melalui `needsMaintenance()`, `updateStatus()`, dan hook `saving`.
- Setelah jadwal selesai, alert maintenance tidak otomatis di-resolve oleh controller ini; resolve alert dilakukan melalui endpoint alert terpisah.
- Controller mengisi `created_by` saat membuat jadwal manual, tetapi schema/model yang terbaca di project ini belum menyimpan kolom tersebut pada `maintenance_schedules`.
