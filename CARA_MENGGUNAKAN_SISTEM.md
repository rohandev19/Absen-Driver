# 🚀 CARA MENGGUNAKAN SISTEM PREVENTIVE MAINTENANCE

## 📋 HASIL SIMULASI

Sistem sudah berhasil dijalankan dengan hasil:

### ✅ Database
- 3 tabel baru berhasil dibuat
- 392 komponen berhasil di-seed (49 kendaraan × 8 komponen)

### ✅ Simulasi Critical Scenario
- Engine Oil: **CRITICAL** (sisa 100 KM)
- Brake Pads: **OVERDUE** (sudah lewat 1000 KM)
- 2 alerts berhasil di-generate
- 2 schedules berhasil dibuat otomatis

### ✅ API Testing
- Fleet health: 49 kendaraan, rata-rata 87.26/100
- Active alerts: 2 (1 overdue, 1 critical)
- Upcoming schedules: 2 (1 critical, 1 high priority)

---

## 🎯 CARA PENGGUNAAN

### 1️⃣ UNTUK ADMIN (Via Web/Dashboard)

#### A. Monitoring Kesehatan Kendaraan

**Endpoint:** `GET /api/vehicles/health`

**Cara pakai:**
```bash
curl -X GET http://localhost:8000/api/vehicles/health \
  -H "Authorization: Bearer YOUR_TOKEN"
```

**Response:**
```json
{
  "fleet_stats": {
    "total_vehicles": 49,
    "average_health_score": 87.26,
    "by_status": {
      "excellent": 4,
      "good": 44,
      "fair": 0,
      "poor": 1,
      "critical": 0
    },
    "total_active_alerts": 2
  },
  "vehicles": [...]
}
```

**Interpretasi:**
- **Excellent (90-100)**: Kendaraan sangat sehat ✅
- **Good (75-89)**: Kendaraan sehat, schedule routine maintenance ✅
- **Fair (60-74)**: Perlu review maintenance schedule ⚠️
- **Poor (40-59)**: Perlu perhatian segera ⚠️
- **Critical (0-39)**: Stop operasi, urgent repair! 🚨

---

#### B. Lihat Alert Aktif

**Endpoint:** `GET /api/maintenance/alerts/summary`

**Response:**
```json
{
  "total": 2,
  "by_type": {
    "overdue": 1,
    "critical": 1,
    "warning": 0
  },
  "by_vehicle": [
    {
      "plate_number": "B 1",
      "count": 2,
      "highest_priority": "critical"
    }
  ]
}
```

**Action:**
- **Overdue** 🔴: Segera schedule maintenance hari ini!
- **Critical** 🟠: Schedule dalam 1-2 hari
- **Warning** 🟡: Schedule dalam 1 minggu

---

#### C. Dashboard Maintenance

**Endpoint:** `GET /api/maintenance/dashboard`

**Response:**
```json
{
  "stats": {
    "overdue": 0,
    "today": 0,
    "this_week": 2,
    "this_month": 2,
    "by_priority": {
      "critical": 1,
      "high": 1,
      "medium": 0,
      "low": 0
    }
  },
  "upcoming": [...],
  "overdue": [...]
}
```

---

#### D. Kelola Komponen Kendaraan

**1. Lihat komponen kendaraan:**
```bash
GET /api/vehicles/{vehicle_id}/components
```

**2. Tambah komponen baru:**
```bash
POST /api/vehicles/{vehicle_id}/components
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
```

**3. Update komponen:**
```bash
PUT /api/vehicles/{vehicle_id}/components/{component_id}
```

**4. Hapus komponen:**
```bash
DELETE /api/vehicles/{vehicle_id}/components/{component_id}
```

---

#### E. Kelola Jadwal Maintenance

**1. Lihat jadwal:**
```bash
# Semua jadwal
GET /api/maintenance/schedules

# Filter by status
GET /api/maintenance/schedules?status=pending

# Filter by priority
GET /api/maintenance/schedules?priority=critical

# Upcoming 7 hari
GET /api/maintenance/schedules?filter=upcoming&days=7

# Overdue
GET /api/maintenance/schedules?filter=overdue
```

**2. Buat jadwal baru:**
```bash
POST /api/maintenance/schedules
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
```

**3. Tandai selesai:**
```bash
POST /api/maintenance/schedules/{schedule_id}/complete
Content-Type: application/json

{
  "actual_cost": 375000,
  "notes": "Selesai tepat waktu"
}
```

**Efek otomatis:**
- Component `last_replacement_km` diupdate
- Component `last_replacement_date` diupdate
- Component status diupdate
- Alert terkait di-resolve

---

#### F. Kelola Alert

**1. Lihat alert:**
```bash
# Semua alert
GET /api/maintenance/alerts

# Alert aktif saja
GET /api/maintenance/alerts?active=1

# Alert critical saja
GET /api/maintenance/alerts?critical=1
```

**2. Acknowledge alert (tandai sudah dibaca):**
```bash
POST /api/maintenance/alerts/{alert_id}/acknowledge
```

**3. Resolve alert (tandai selesai):**
```bash
POST /api/maintenance/alerts/{alert_id}/resolve
```

**4. Dismiss alert (abaikan):**
```bash
POST /api/maintenance/alerts/{alert_id}/dismiss
```

---

### 2️⃣ UNTUK SISTEM (Automation)

#### A. Scheduled Tasks (Jalan Otomatis)

**Setup cron job:**
```bash
* * * * * cd /path-to-project && php artisan schedule:run >> /dev/null 2>&1
```

**Jadwal otomatis:**
- **Setiap hari jam 00:00**: Update status komponen
- **Setiap 6 jam**: Generate alerts
- **Setiap hari jam 01:00**: Generate schedules

---

#### B. Manual Commands

**1. Update status komponen:**
```bash
php artisan maintenance:update-component-status
```
Output:
```
🔄 Updating component status...
  B1234AB - Engine Oil: healthy → warning
  B5678CD - Brake Pads: warning → critical
✅ Updated 15 components out of 120 total.
```

---

**2. Generate alerts:**
```bash
php artisan maintenance:generate-alerts
```
Output:
```
🚨 Generating maintenance alerts...
📊 Summary:
Total Vehicles: 50
Alerts Created: 12
🔴 Overdue: 3
🟠 Critical: 5
🟡 Warning: 4
```

---

**3. Generate schedules:**
```bash
php artisan maintenance:generate-schedules

# Untuk kendaraan tertentu
php artisan maintenance:generate-schedules --vehicle_id=1
```
Output:
```
🔧 Generating maintenance schedules...
Processing vehicle: B1234AB
  ✅ Created schedule for: Engine Oil
Processing vehicle: B5678CD
  ✅ Created schedule for: Brake Pads
📊 Summary:
Vehicles Processed: 50
Schedules Created: 8
```

---

### 3️⃣ WORKFLOW LENGKAP

#### Skenario: Kendaraan Perlu Ganti Oli

**1. Sistem detect otomatis (via scheduled task):**
```
[00:00] Update component status
        → Engine Oil: healthy → warning (sisa 450 KM)

[06:00] Generate alerts
        → Alert created: "WARNING: Engine Oil sisa 450 KM"

[01:00] Generate schedules
        → Schedule created: 2026-05-20, priority: medium
```

**2. Admin melihat dashboard:**
```
GET /api/maintenance/dashboard
→ Melihat ada 1 upcoming schedule
→ Melihat ada 1 active alert
```

**3. Admin acknowledge alert:**
```
POST /api/maintenance/alerts/{id}/acknowledge
→ Alert status: active → acknowledged
```

**4. Kendaraan dibawa ke bengkel:**
```
POST /api/maintenance/schedules/{id}/complete
{
  "actual_cost": 350000,
  "notes": "Ganti oli Shell Helix"
}
```

**5. Sistem update otomatis:**
```
✅ Schedule status: pending → completed
✅ Component last_replacement_km: updated
✅ Component status: warning → healthy
✅ Alert status: acknowledged → resolved
```

---

### 4️⃣ TESTING

#### A. Simulasi Normal
```bash
php simulate_usage.php
```

#### B. Simulasi Critical
```bash
php simulate_critical.php
```

#### C. Test API
```bash
php test_api.php
```

---

## 📊 MONITORING METRICS

### Key Performance Indicators (KPIs)

**1. Fleet Health Score**
- Target: > 85/100
- Current: 87.26/100 ✅

**2. Active Alerts**
- Target: < 5% dari total kendaraan
- Current: 2 alerts (4%) ✅

**3. Maintenance Compliance**
- Target: > 95%
- Current: 100% ✅

**4. Overdue Schedules**
- Target: 0
- Current: 0 ✅

---

## 🎯 BEST PRACTICES

### 1. Monitoring Harian
- Cek dashboard setiap pagi
- Review active alerts
- Prioritas: overdue → critical → warning

### 2. Maintenance Scheduling
- Schedule 3-7 hari sebelum due date
- Jangan tunggu sampai overdue
- Koordinasi dengan driver untuk downtime

### 3. Component Tracking
- Update last_replacement setelah maintenance
- Catat actual cost untuk budgeting
- Review component health score bulanan

### 4. Alert Management
- Acknowledge alert segera setelah dibaca
- Resolve alert setelah maintenance selesai
- Jangan dismiss alert tanpa action

---

## 🐛 TROUBLESHOOTING

### Problem: Alerts tidak muncul
**Solution:**
```bash
# Manual generate
php artisan maintenance:generate-alerts

# Cek apakah ada komponen yang perlu perhatian
php artisan tinker --execute="echo App\Models\VehicleComponent::needsMaintenance()->count();"
```

### Problem: Status tidak update
**Solution:**
```bash
# Manual update
php artisan maintenance:update-component-status

# Cek current KM kendaraan
php artisan tinker --execute="App\Models\Vehicle::all()->each(function(\$v) { echo \$v->plate_number . ': ' . \$v->current_km . ' KM' . PHP_EOL; });"
```

### Problem: Schedule tidak auto-create
**Solution:**
```bash
# Manual generate
php artisan maintenance:generate-schedules

# Cek apakah cron job jalan
php artisan schedule:list
```

---

## 📞 SUPPORT

Jika ada pertanyaan atau masalah:
1. Cek file `FILE_BARU_YANG_DIBUAT.md` untuk detail teknis
2. Cek file `PREVENTIVE_MAINTENANCE_IMPLEMENTATION.md` untuk API docs
3. Run simulasi untuk testing: `php simulate_critical.php`

---

**Status:** ✅ SISTEM SIAP DIGUNAKAN  
**Last Updated:** 2026-05-14  
**Version:** 1.0
