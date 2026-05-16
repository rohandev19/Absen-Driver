# 03. DATABASE SCHEMA

> **Complete database schema dengan ERD dan relationship documentation**

---

## 📋 TABLE OF CONTENTS

1. [ERD Diagram](#erd-diagram)
2. [Table Definitions](#table-definitions)
3. [Relationships](#relationships)
4. [Indexes & Performance](#indexes--performance)
5. [Migration Files](#migration-files)

---

## 1. ERD DIAGRAM

### Current Schema (Existing)

```
┌─────────────┐         ┌──────────────┐         ┌─────────────┐
│   users     │         │   drivers    │         │  projects   │
├─────────────┤         ├──────────────┤         ├─────────────┤
│ id          │         │ id           │    ┌────│ id          │
│ name        │         │ driver_id_nik│    │    │ name        │
│ email       │         │ nik_ktp      │    │    │ code        │
│ password    │         │ full_name    │    │    │ created_at  │
│ role        │         │ password     │    │    │ updated_at  │
│ created_at  │         │ sim_expiry   │    │    └─────────────┘
│ updated_at  │         │ sim_type     │    │            │
└─────────────┘         │ project_id   │────┘            │
                        │ created_at   │                 │
                        │ updated_at   │                 │
                        └──────────────┘                 │
                               │                         │
                               │                         │
                        ┌──────┴──────┐                 │
                        │             │                 │
                        ▼             ▼                 │
              ┌──────────────┐  ┌─────────────┐       │
              │ attendances  │  │  vehicles   │◄──────┘
              ├──────────────┤  ├─────────────┤
              │ id           │  │ id          │
              │ driver_id    │──│ plate_number│
              │ vehicle_id   │──│ type        │
              │ time_in      │  │ project_id  │
              │ time_out     │  │ status      │
              │ gps_in       │  │ current_km  │
              │ gps_out      │  │ service_int │
              │ speedo_awal  │  │ last_service│
              │ speedo_akhir │  │ pajak_stnk  │
              │ check_ban    │  │ kir_berlaku │
              │ check_lampu  │  │ created_at  │
              │ check_rem    │  │ updated_at  │
              │ photos...    │  └─────────────┘
              │ created_at   │         │
              │ updated_at   │         │
              └──────────────┘         │
                                       │
                        ┌──────────────┴──────────────┐
                        │                             │
                        ▼                             ▼
              ┌──────────────────┐         ┌──────────────────┐
              │ emergency_reports│         │ maintenance_logs │
              ├──────────────────┤         ├──────────────────┤
              │ id               │         │ id               │
              │ driver_id        │         │ vehicle_id       │
              │ vehicle_id       │         │ service_date     │
              │ timestamp        │         │ km_at_service    │
              │ gps_location     │         │ description      │
              │ description      │         │ workshop_name    │
              │ proof_photo      │         │ recorded_by      │
              │ created_at       │         │ created_at       │
              │ updated_at       │         │ updated_at       │
              └──────────────────┘         └──────────────────┘
```

### Proposed Schema (Preventive Maintenance)

```
                        ┌─────────────┐
                        │  vehicles   │
                        ├─────────────┤
                        │ id          │
                        │ ...         │
                        └──────┬──────┘
                               │
                ┌──────────────┼──────────────┐
                │              │              │
                ▼              ▼              ▼
    ┌────────────────────┐  ┌──────────────────────┐  ┌──────────────────────┐
    │ vehicle_components │  │ maintenance_schedules│  │  maintenance_alerts  │
    ├────────────────────┤  ├──────────────────────┤  ├──────────────────────┤
    │ id                 │  │ id                   │  │ id                   │
    │ vehicle_id         │──│ vehicle_id           │──│ vehicle_id           │
    │ component_name     │  │ component_id         │──│ component_id         │
    │ category           │  │ scheduled_date       │  │ alert_type           │
    │ interval_km        │  │ scheduled_km         │  │ message              │
    │ interval_days      │  │ type                 │  │ triggered_at         │
    │ last_replace_km    │  │ priority             │  │ acknowledged_at      │
    │ last_replace_date  │  │ status               │  │ resolved_at          │
    │ next_replace_km    │  │ estimated_cost       │  │ status               │
    │ next_replace_date  │  │ actual_cost          │  │ created_at           │
    │ cost_per_replace   │  │ workshop_id          │  └──────────────────────┘
    │ warning_threshold  │  │ notes                │
    │ critical_threshold │  │ completed_at         │
    │ status             │  │ created_at           │
    │ created_at         │  │ updated_at           │
    │ updated_at         │  └──────────────────────┘
    └────────────────────┘           │
                                     │
                                     ▼
                            ┌──────────────┐
                            │  workshops   │
                            ├──────────────┤
                            │ id           │
                            │ name         │
                            │ address      │
                            │ phone        │
                            │ email        │
                            │ specialization│
                            │ rating       │
                            │ created_at   │
                            │ updated_at   │
                            └──────────────┘
```

---

## 2. TABLE DEFINITIONS

### 2.1 Existing Tables

#### users
```sql
CREATE TABLE users (
    id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(255) NOT NULL,
    email VARCHAR(255) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    role ENUM('admin', 'master_admin') DEFAULT 'admin',
    remember_token VARCHAR(100),
    email_verified_at TIMESTAMP NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    
    INDEX idx_email (email),
    INDEX idx_role (role)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

#### drivers
```sql
CREATE TABLE drivers (
    id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    driver_id_nik VARCHAR(255) UNIQUE NOT NULL COMMENT 'ID Badge/Absen',
    nik_ktp VARCHAR(20) UNIQUE COMMENT 'NIK KTP',
    full_name VARCHAR(255) NOT NULL,
    password VARCHAR(255) NOT NULL,
    sim_expiry_date DATE,
    sim_type VARCHAR(10) COMMENT 'A, B1, B2, C',
    project_id BIGINT UNSIGNED,
    remember_token VARCHAR(100),
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    
    FOREIGN KEY (project_id) REFERENCES projects(id) ON DELETE SET NULL,
    
    INDEX idx_driver_id_nik (driver_id_nik),
    INDEX idx_project_id (project_id),
    INDEX idx_sim_expiry (sim_expiry_date)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

#### projects
```sql
CREATE TABLE projects (
    id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(255) NOT NULL,
    code VARCHAR(50),
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    
    INDEX idx_name (name)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

#### vehicles
```sql
CREATE TABLE vehicles (
    id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    plate_number VARCHAR(20) UNIQUE NOT NULL,
    type VARCHAR(100) COMMENT 'Jenis kendaraan',
    project_id BIGINT UNSIGNED,
    status ENUM('active', 'maintenance', 'inactive') DEFAULT 'active',
    current_km INT UNSIGNED DEFAULT 0,
    service_interval_km INT UNSIGNED DEFAULT 5000,
    last_service_km INT UNSIGNED DEFAULT 0,
    pajak_stnk_berlaku_sampai DATE,
    kir_berlaku_sampai DATE,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    
    FOREIGN KEY (project_id) REFERENCES projects(id) ON DELETE SET NULL,
    
    INDEX idx_plate_number (plate_number),
    INDEX idx_project_id (project_id),
    INDEX idx_status (status),
    INDEX idx_pajak_stnk (pajak_stnk_berlaku_sampai),
    INDEX idx_kir (kir_berlaku_sampai)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

#### attendances
```sql
CREATE TABLE attendances (
    id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    driver_id BIGINT UNSIGNED NOT NULL,
    vehicle_id BIGINT UNSIGNED NOT NULL,
    
    -- Check-in data
    time_in DATETIME NOT NULL,
    gps_location_in VARCHAR(255),
    selfie_photo_path VARCHAR(255),
    speedo_photo_awal_path VARCHAR(255),
    condition_photo_1_path VARCHAR(255),
    condition_photo_2_path VARCHAR(255),
    speedo_awal INT UNSIGNED,
    
    -- Check-out data
    time_out DATETIME,
    gps_location_out VARCHAR(255),
    speedo_photo_akhir_path VARCHAR(255),
    catatan TEXT,
    check_ban VARCHAR(50),
    check_lampu VARCHAR(50),
    check_rem VARCHAR(50),
    speedo_akhir INT UNSIGNED,
    
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    
    FOREIGN KEY (driver_id) REFERENCES drivers(id) ON DELETE CASCADE,
    FOREIGN KEY (vehicle_id) REFERENCES vehicles(id) ON DELETE CASCADE,
    
    INDEX idx_driver_id (driver_id),
    INDEX idx_vehicle_id (vehicle_id),
    INDEX idx_time_in (time_in),
    INDEX idx_time_out (time_out),
    INDEX idx_driver_time (driver_id, time_in),
    INDEX idx_vehicle_time (vehicle_id, time_out)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

#### emergency_reports
```sql
CREATE TABLE emergency_reports (
    id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    driver_id BIGINT UNSIGNED NOT NULL,
    vehicle_id BIGINT UNSIGNED NOT NULL,
    timestamp DATETIME NOT NULL,
    gps_location VARCHAR(255),
    description TEXT,
    proof_photo_path VARCHAR(255),
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    
    FOREIGN KEY (driver_id) REFERENCES drivers(id) ON DELETE CASCADE,
    FOREIGN KEY (vehicle_id) REFERENCES vehicles(id) ON DELETE CASCADE,
    
    INDEX idx_driver_id (driver_id),
    INDEX idx_vehicle_id (vehicle_id),
    INDEX idx_timestamp (timestamp)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

#### maintenance_logs
```sql
CREATE TABLE maintenance_logs (
    id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    vehicle_id BIGINT UNSIGNED NOT NULL,
    service_date DATE NOT NULL,
    km_at_service INT UNSIGNED,
    description TEXT,
    workshop_name VARCHAR(255),
    recorded_by_user_id BIGINT UNSIGNED,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    
    FOREIGN KEY (vehicle_id) REFERENCES vehicles(id) ON DELETE CASCADE,
    FOREIGN KEY (recorded_by_user_id) REFERENCES users(id) ON DELETE SET NULL,
    
    INDEX idx_vehicle_id (vehicle_id),
    INDEX idx_service_date (service_date)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

### 2.2 Proposed Tables (Preventive Maintenance)

#### vehicle_components
```sql
CREATE TABLE vehicle_components (
    id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    vehicle_id BIGINT UNSIGNED NOT NULL,
    component_name VARCHAR(100) NOT NULL COMMENT 'Engine Oil, Brake Pads, etc',
    category VARCHAR(50) NOT NULL COMMENT 'Fluids, Filters, Brakes, etc',
    
    -- Replacement intervals
    replacement_interval_km INT UNSIGNED COMMENT 'KM interval',
    replacement_interval_days INT UNSIGNED COMMENT 'Days interval',
    
    -- Last replacement
    last_replacement_km INT UNSIGNED,
    last_replacement_date DATE,
    
    -- Next replacement (calculated)
    next_replacement_km INT UNSIGNED,
    next_replacement_date DATE,
    
    -- Cost
    cost_per_replacement DECIMAL(10,2),
    
    -- Thresholds
    warning_threshold_km INT UNSIGNED DEFAULT 500,
    critical_threshold_km INT UNSIGNED DEFAULT 100,
    
    -- Status
    status ENUM('healthy', 'warning', 'critical', 'overdue') DEFAULT 'healthy',
    
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    
    FOREIGN KEY (vehicle_id) REFERENCES vehicles(id) ON DELETE CASCADE,
    
    INDEX idx_vehicle_id (vehicle_id),
    INDEX idx_category (category),
    INDEX idx_status (status),
    INDEX idx_next_replacement_km (next_replacement_km),
    INDEX idx_next_replacement_date (next_replacement_date)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

#### maintenance_schedules
```sql
CREATE TABLE maintenance_schedules (
    id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    vehicle_id BIGINT UNSIGNED NOT NULL,
    component_id BIGINT UNSIGNED,
    
    -- Schedule info
    scheduled_date DATE NOT NULL,
    scheduled_km INT UNSIGNED,
    type ENUM('preventive', 'corrective', 'predictive') DEFAULT 'preventive',
    priority ENUM('low', 'medium', 'high', 'critical') DEFAULT 'medium',
    status ENUM('pending', 'scheduled', 'in_progress', 'completed', 'cancelled') DEFAULT 'pending',
    
    -- Cost
    estimated_cost DECIMAL(10,2),
    actual_cost DECIMAL(10,2),
    
    -- Workshop
    workshop_id BIGINT UNSIGNED,
    
    -- Notes
    notes TEXT,
    
    -- Completion
    completed_at TIMESTAMP NULL,
    
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    
    FOREIGN KEY (vehicle_id) REFERENCES vehicles(id) ON DELETE CASCADE,
    FOREIGN KEY (component_id) REFERENCES vehicle_components(id) ON DELETE SET NULL,
    FOREIGN KEY (workshop_id) REFERENCES workshops(id) ON DELETE SET NULL,
    
    INDEX idx_vehicle_id (vehicle_id),
    INDEX idx_component_id (component_id),
    INDEX idx_scheduled_date (scheduled_date),
    INDEX idx_status (status),
    INDEX idx_priority (priority),
    INDEX idx_workshop_id (workshop_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

#### maintenance_alerts
```sql
CREATE TABLE maintenance_alerts (
    id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    vehicle_id BIGINT UNSIGNED NOT NULL,
    component_id BIGINT UNSIGNED,
    
    -- Alert info
    alert_type ENUM('warning', 'critical', 'overdue') NOT NULL,
    message TEXT NOT NULL,
    
    -- Timestamps
    triggered_at TIMESTAMP NOT NULL,
    acknowledged_at TIMESTAMP NULL,
    acknowledged_by BIGINT UNSIGNED,
    resolved_at TIMESTAMP NULL,
    
    -- Status
    status ENUM('active', 'acknowledged', 'resolved', 'dismissed') DEFAULT 'active',
    
    created_at TIMESTAMP NULL,
    
    FOREIGN KEY (vehicle_id) REFERENCES vehicles(id) ON DELETE CASCADE,
    FOREIGN KEY (component_id) REFERENCES vehicle_components(id) ON DELETE SET NULL,
    FOREIGN KEY (acknowledged_by) REFERENCES users(id) ON DELETE SET NULL,
    
    INDEX idx_vehicle_id (vehicle_id),
    INDEX idx_component_id (component_id),
    INDEX idx_alert_type (alert_type),
    INDEX idx_status (status),
    INDEX idx_triggered_at (triggered_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

#### workshops
```sql
CREATE TABLE workshops (
    id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(255) NOT NULL,
    address TEXT,
    phone VARCHAR(20),
    email VARCHAR(255),
    specialization VARCHAR(255) COMMENT 'Engine, Transmission, Body, etc',
    rating DECIMAL(3,2) DEFAULT 0.00 COMMENT '0.00 - 5.00',
    total_jobs INT UNSIGNED DEFAULT 0,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    
    INDEX idx_name (name),
    INDEX idx_rating (rating)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

---

## 3. RELATIONSHIPS

### One-to-Many Relationships

```php
// Project has many Drivers
Project::hasMany(Driver::class)
Driver::belongsTo(Project::class)

// Project has many Vehicles
Project::hasMany(Vehicle::class)
Vehicle::belongsTo(Project::class)

// Driver has many Attendances
Driver::hasMany(Attendance::class)
Attendance::belongsTo(Driver::class)

// Vehicle has many Attendances
Vehicle::hasMany(Attendance::class)
Attendance::belongsTo(Vehicle::class)

// Vehicle has many Components
Vehicle::hasMany(VehicleComponent::class)
VehicleComponent::belongsTo(Vehicle::class)

// Vehicle has many MaintenanceSchedules
Vehicle::hasMany(MaintenanceSchedule::class)
MaintenanceSchedule::belongsTo(Vehicle::class)

// Vehicle has many MaintenanceAlerts
Vehicle::hasMany(MaintenanceAlert::class)
MaintenanceAlert::belongsTo(Vehicle::class)

// Workshop has many MaintenanceSchedules
Workshop::hasMany(MaintenanceSchedule::class)
MaintenanceSchedule::belongsTo(Workshop::class)
```

### Special Relationships

```php
// Driver's active attendance (whereNull time_out)
Driver::hasOne(Attendance::class)->whereNull('time_out')->latest()

// Vehicle's latest attendance
Vehicle::hasOne(Attendance::class)->latest('time_out')

// Vehicle's overdue components
Vehicle::hasMany(VehicleComponent::class)->where('status', 'overdue')

// Active alerts
Vehicle::hasMany(MaintenanceAlert::class)->where('status', 'active')
```

---

## 4. INDEXES & PERFORMANCE

### Primary Indexes (Already Created)
- All `id` columns (PRIMARY KEY)
- All foreign keys (FOREIGN KEY)

### Composite Indexes (Recommended)

```sql
-- Attendance queries
ALTER TABLE attendances 
ADD INDEX idx_driver_time_out (driver_id, time_out);

ALTER TABLE attendances 
ADD INDEX idx_vehicle_time_out (vehicle_id, time_out);

-- Component queries
ALTER TABLE vehicle_components 
ADD INDEX idx_vehicle_status (vehicle_id, status);

-- Schedule queries
ALTER TABLE maintenance_schedules 
ADD INDEX idx_vehicle_status_date (vehicle_id, status, scheduled_date);

-- Alert queries
ALTER TABLE maintenance_alerts 
ADD INDEX idx_vehicle_status_type (vehicle_id, status, alert_type);
```

### Full-Text Search (Optional)

```sql
-- Search in maintenance notes
ALTER TABLE maintenance_schedules 
ADD FULLTEXT INDEX ft_notes (notes);

-- Search in emergency reports
ALTER TABLE emergency_reports 
ADD FULLTEXT INDEX ft_description (description);
```

---

## 5. MIGRATION FILES

### Create Migrations

```bash
# Existing tables (already created)
php artisan make:migration create_users_table
php artisan make:migration create_drivers_table
php artisan make:migration create_projects_table
php artisan make:migration create_vehicles_table
php artisan make:migration create_attendances_table
php artisan make:migration create_emergency_reports_table
php artisan make:migration create_maintenance_logs_table

# New tables (to be created)
php artisan make:migration create_vehicle_components_table
php artisan make:migration create_maintenance_schedules_table
php artisan make:migration create_maintenance_alerts_table
php artisan make:migration create_workshops_table

# Add indexes
php artisan make:migration add_performance_indexes_to_tables
```

### Sample Migration: vehicle_components

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('vehicle_components', function (Blueprint $table) {
            $table->id();
            $table->foreignId('vehicle_id')->constrained()->onDelete('cascade');
            $table->string('component_name', 100);
            $table->string('category', 50);
            
            // Intervals
            $table->unsignedInteger('replacement_interval_km')->nullable();
            $table->unsignedInteger('replacement_interval_days')->nullable();
            
            // Last replacement
            $table->unsignedInteger('last_replacement_km')->nullable();
            $table->date('last_replacement_date')->nullable();
            
            // Next replacement (calculated)
            $table->unsignedInteger('next_replacement_km')->nullable();
            $table->date('next_replacement_date')->nullable();
            
            // Cost
            $table->decimal('cost_per_replacement', 10, 2)->nullable();
            
            // Thresholds
            $table->unsignedInteger('warning_threshold_km')->default(500);
            $table->unsignedInteger('critical_threshold_km')->default(100);
            
            // Status
            $table->enum('status', ['healthy', 'warning', 'critical', 'overdue'])
                ->default('healthy');
            
            $table->timestamps();
            
            // Indexes
            $table->index('vehicle_id');
            $table->index('category');
            $table->index('status');
            $table->index('next_replacement_km');
            $table->index('next_replacement_date');
            $table->index(['vehicle_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('vehicle_components');
    }
};
```

---

**Document Version**: 1.0  
**Last Updated**: 2026-05-14  
**Owner**: Database Team

---

**Related Documents:**
- [01. System Overview](./01_SYSTEM_OVERVIEW.md)
- [02. System Architecture](./02_SYSTEM_ARCHITECTURE.md)
- [10. Preventive Maintenance Strategy](./10_PREVENTIVE_MAINTENANCE_STRATEGY.md)
