# Preventive Maintenance System - Implementation Guide

## 📋 Overview

Implementasi **Phase 1: Foundation** dari Preventive Maintenance Strategy yang mencakup:
- ✅ Database schema (3 tabel utama)
- ✅ Models dengan relationships
- ✅ Health scoring system
- ✅ Alert generation system
- ✅ Maintenance scheduling
- ✅ RESTful API endpoints
- ✅ Automated commands

---

## 🗄️ Database Schema

### 1. vehicle_components
Tracking komponen kendaraan dan jadwal penggantiannya.

**Key Fields:**
- `replacement_interval_km`: Interval penggantian berdasarkan KM
- `replacement_interval_days`: Interval penggantian berdasarkan hari
- `next_replacement_km`: Kapan harus diganti (KM)
- `next_replacement_date`: Kapan harus diganti (tanggal)
- `status`: healthy, warning, critical, overdue

### 2. maintenance_schedules
Jadwal maintenance yang sudah direncanakan.

**Key Fields:**
- `type`: preventive, corrective, predictive
- `priority`: low, medium, high, critical
- `status`: pending, scheduled, in_progress, completed, cancelled

### 3. maintenance_alerts
Alert otomatis untuk komponen yang perlu perhatian.

**Key Fields:**
- `alert_type`: warning, critical, overdue
- `status`: active, acknowledged, resolved, dismissed

---

## 🚀 Installation Steps

### 1. Run Migrations

```bash
php artisan migrate
```

Ini akan membuat 3 tabel baru:
- `vehicle_components`
- `maintenance_schedules`
- `maintenance_alerts`

### 2. Seed Sample Data (Optional)

```bash
php artisan db:seed --class=VehicleComponentSeeder
```

Ini akan membuat sample components untuk semua kendaraan yang ada.

### 3. Setup Scheduled Tasks

Tambahkan ke crontab (Linux/Mac) atau Task Scheduler (Windows):

```bash
* * * * * cd /path-to-your-project && php artisan schedule:run >> /dev/null 2>&1
```

Atau jalankan manual untuk testing:

```bash
# Update component status
php artisan maintenance:update-component-status

# Generate alerts
php artisan maintenance:generate-alerts

# Generate schedules
php artisan maintenance:generate-schedules
```

---

## 📡 API Endpoints

### Vehicle Health

```http
# Get health summary for all vehicles
GET /api/vehicles/health
Authorization: Bearer {token}

# Get health report for specific vehicle
GET /api/vehicles/{vehicle_id}/health
Authorization: Bearer {token}
```

**Response Example:**
```json
{
  "success": true,
  "data": {
    "vehicle_id": 1,
    "plate_number": "B 1234 XYZ",
    "health_score": 75.5,
    "status": {
      "label": "Good",
      "color": "green",
      "icon": "🟢",
      "action": "Schedule routine maintenance"
    },
    "breakdown": {
      "component_health": 70.0,
      "maintenance_compliance": 85.0,
      "daily_check_score": 90.0,
      "age_factor": 80.0
    },
    "components_needing_attention": [
      {
        "name": "Engine Oil",
        "status": "warning",
        "km_remaining": 450,
        "days_remaining": 15
      }
    ],
    "active_alerts": 2,
    "upcoming_maintenance": 1
  }
}
```

### Vehicle Components

```http
# Get all components for a vehicle
GET /api/vehicles/{vehicle_id}/components
Authorization: Bearer {token}

# Add new component
POST /api/vehicles/{vehicle_id}/components
Authorization: Bearer {token}
Content-Type: application/json

{
  "component_name": "Engine Oil",
  "category": "Fluids",
  "replacement_interval_km": 5000,
  "replacement_interval_days": 180,
  "last_replacement_km": 45000,
  "last_replacement_date": "2026-03-15",
  "cost_per_replacement": 350000,
  "warning_threshold_km": 500,
  "critical_threshold_km": 100
}

# Update component
PUT /api/vehicles/{vehicle_id}/components/{component_id}
Authorization: Bearer {token}

# Delete component
DELETE /api/vehicles/{vehicle_id}/components/{component_id}
Authorization: Bearer {token}

# Get component categories
GET /api/component-categories
Authorization: Bearer {token}
```

### Maintenance Schedules

```http
# Get all schedules (with filters)
GET /api/maintenance/schedules?status=pending&priority=high
Authorization: Bearer {token}

# Get upcoming schedules
GET /api/maintenance/schedules?filter=upcoming&days=7
Authorization: Bearer {token}

# Get overdue schedules
GET /api/maintenance/schedules?filter=overdue
Authorization: Bearer {token}

# Create schedule
POST /api/maintenance/schedules
Authorization: Bearer {token}
Content-Type: application/json

{
  "vehicle_id": 1,
  "component_id": 5,
  "scheduled_date": "2026-05-20",
  "scheduled_km": 50000,
  "type": "preventive",
  "priority": "high",
  "estimated_cost": 350000,
  "workshop_name": "Bengkel Jaya",
  "notes": "Ganti oli mesin"
}

# Update schedule
PUT /api/maintenance/schedules/{schedule_id}
Authorization: Bearer {token}

# Mark as completed
POST /api/maintenance/schedules/{schedule_id}/complete
Authorization: Bearer {token}
Content-Type: application/json

{
  "actual_cost": 375000,
  "notes": "Selesai tepat waktu"
}

# Get dashboard summary
GET /api/maintenance/dashboard
Authorization: Bearer {token}
```

**Dashboard Response:**
```json
{
  "success": true,
  "data": {
    "stats": {
      "overdue": 2,
      "today": 1,
      "this_week": 5,
      "this_month": 12,
      "by_priority": {
        "critical": 2,
        "high": 3,
        "medium": 5,
        "low": 2
      }
    },
    "upcoming": [...],
    "overdue": [...]
  }
}
```

### Maintenance Alerts

```http
# Get all alerts
GET /api/maintenance/alerts?status=active
Authorization: Bearer {token}

# Get alerts summary
GET /api/maintenance/alerts/summary
Authorization: Bearer {token}

# Acknowledge alert
POST /api/maintenance/alerts/{alert_id}/acknowledge
Authorization: Bearer {token}

# Resolve alert
POST /api/maintenance/alerts/{alert_id}/resolve
Authorization: Bearer {token}

# Dismiss alert
POST /api/maintenance/alerts/{alert_id}/dismiss
Authorization: Bearer {token}

# Generate alerts manually
POST /api/maintenance/alerts/generate
Authorization: Bearer {token}
```

---

## 🧮 Health Score Calculation

**Formula:**
```
Health Score = (
    Component_Health_Average * 0.40 +
    Maintenance_Compliance * 0.30 +
    Daily_Check_Score * 0.20 +
    Age_Factor * 0.10
) * 100
```

**Interpretation:**
- **90-100**: Excellent 🟢 - Continue monitoring
- **75-89**: Good 🟢 - Schedule routine maintenance
- **60-74**: Fair 🟡 - Review maintenance schedule
- **40-59**: Poor 🟠 - Immediate attention needed
- **0-39**: Critical 🔴 - Stop operations, urgent repair

---

## 🔔 Alert System

### Alert Levels

| Level | Trigger | Channels | Escalation |
|-------|---------|----------|------------|
| 🔴 **Overdue** | Past replacement date/km | Email, SMS, Push, Dashboard | Immediate |
| 🟠 **Critical** | Within critical threshold | Email, SMS, Push, Dashboard | 24 hours |
| 🟡 **Warning** | Within warning threshold | Email, Push, Dashboard | 3 days |

### Alert Generation Logic

1. **KM-based**: Triggered when `current_km` approaches `next_replacement_km`
2. **Date-based**: Triggered when current date approaches `next_replacement_date`
3. **Duplicate prevention**: Won't create duplicate active alerts
4. **Auto-resolve**: Alerts resolved when maintenance completed

---

## 🔧 Artisan Commands

### Update Component Status
```bash
php artisan maintenance:update-component-status
```
Updates status of all components based on current vehicle KM.

### Generate Alerts
```bash
php artisan maintenance:generate-alerts
```
Scans all vehicles and creates alerts for components needing attention.

### Generate Schedules
```bash
php artisan maintenance:generate-schedules

# For specific vehicle
php artisan maintenance:generate-schedules --vehicle_id=1
```
Auto-creates maintenance schedules for components in warning/critical/overdue status.

---

## 📊 Usage Examples

### Example 1: Add Components to Vehicle

```php
use App\Models\Vehicle;

$vehicle = Vehicle::find(1);

// Add Engine Oil component
$vehicle->components()->create([
    'component_name' => 'Engine Oil',
    'category' => 'Fluids',
    'replacement_interval_km' => 5000,
    'replacement_interval_days' => 180,
    'last_replacement_km' => 45000,
    'last_replacement_date' => now()->subDays(90),
    'cost_per_replacement' => 350000,
    'warning_threshold_km' => 500,
    'critical_threshold_km' => 100,
]);
```

### Example 2: Get Vehicle Health Report

```php
use App\Services\VehicleHealthService;

$healthService = new VehicleHealthService();
$report = $healthService->getHealthReport($vehicle);

echo "Health Score: {$report['health_score']}";
echo "Status: {$report['status']['label']}";
```

### Example 3: Generate Alerts

```php
use App\Services\MaintenanceAlertService;

$alertService = new MaintenanceAlertService();
$stats = $alertService->generateAlertsForAllVehicles();

echo "Created {$stats['alerts_created']} alerts";
echo "Critical: {$stats['critical']}";
```

---

## 🎯 Next Steps (Phase 2)

1. **Notification System**
   - Email notifications
   - SMS alerts
   - Push notifications to mobile app

2. **Dashboard Widgets**
   - Real-time health monitoring
   - Upcoming maintenance calendar
   - Cost tracking charts

3. **Mobile App Integration**
   - Driver can view vehicle health
   - Maintenance reminders
   - Report issues directly

4. **Vendor Management**
   - Workshop database
   - Service history per vendor
   - Cost comparison

---

## 🐛 Troubleshooting

### Components not updating status
```bash
# Manually trigger update
php artisan maintenance:update-component-status
```

### Alerts not generating
```bash
# Check if components exist
php artisan tinker
>>> App\Models\VehicleComponent::count()

# Manually generate alerts
php artisan maintenance:generate-alerts
```

### Schedules not auto-creating
```bash
# Check component status
php artisan tinker
>>> App\Models\VehicleComponent::needsMaintenance()->count()

# Manually generate schedules
php artisan maintenance:generate-schedules
```

---

## 📝 Notes

- **Auto-calculation**: `next_replacement_km` dan `next_replacement_date` dihitung otomatis saat save
- **Status update**: Component status diupdate otomatis saat save berdasarkan current vehicle KM
- **Alert deduplication**: System tidak akan membuat duplicate alert untuk component yang sama
- **Schedule completion**: Saat schedule completed, component `last_replacement_*` otomatis diupdate

---

**Version**: 1.0  
**Date**: 2026-05-14  
**Author**: Fleet Management Team
