# 01. SYSTEM OVERVIEW

> **Gambaran umum sistem Absensi & Manajemen Kendaraan dengan fokus Preventive Maintenance**

---

## 📋 TABLE OF CONTENTS

1. [Executive Summary](#executive-summary)
2. [Business Context](#business-context)
3. [System Purpose](#system-purpose)
4. [Key Features](#key-features)
5. [User Roles](#user-roles)
6. [System Components](#system-components)
7. [Technology Stack](#technology-stack)
8. [Integration Points](#integration-points)

---

## 1. EXECUTIVE SUMMARY

### Nama Sistem
**Fleet Management & Attendance System with Preventive Maintenance**

### Deskripsi Singkat
Sistem terintegrasi untuk mengelola absensi driver, monitoring kendaraan, dan preventive maintenance berbasis data untuk mencegah kerusakan kendaraan sebelum terjadi.

### Target Users
- **50+ Drivers** (Mobile App)
- **5-10 Admin** (Web Dashboard)
- **3-5 Master Admin** (Full Access)
- **10+ Workshop Partners** (Vendor Portal - Future)

### Key Metrics
- **Fleet Size**: 50+ vehicles
- **Daily Attendance**: 100+ check-in/check-out per day
- **Maintenance Schedules**: 200+ per month
- **Cost Savings Target**: 60% reduction in emergency repairs
- **Uptime Target**: 95% vehicle availability

---

## 2. BUSINESS CONTEXT

### Problem Statement

**Current Challenges:**
1. ❌ **Reactive Maintenance** - Mobil diperbaiki setelah rusak
2. ❌ **High Downtime** - 15 hari/tahun per kendaraan tidak operasional
3. ❌ **Unpredictable Costs** - Biaya perbaikan darurat tinggi (Rp 15jt/tahun/unit)
4. ❌ **Manual Tracking** - Pencatatan manual rawan error
5. ❌ **No Early Warning** - Tidak ada sistem deteksi dini kerusakan
6. ❌ **Poor Visibility** - Admin tidak tahu kondisi real-time kendaraan

### Business Impact
```
Annual Cost per Vehicle (Current State):
├─ Emergency Repairs:     Rp 15,000,000
├─ Downtime Cost:         Rp 10,000,000
├─ Towing Services:       Rp  2,000,000
└─ Lost Productivity:     Rp  8,000,000
   ─────────────────────────────────────
   TOTAL:                 Rp 35,000,000

For 50 Vehicles:          Rp 1,750,000,000/year
```

### Solution Approach

**Preventive Maintenance Strategy:**
1. ✅ **Proactive Monitoring** - Track komponen kendaraan real-time
2. ✅ **Automated Scheduling** - Generate jadwal maintenance otomatis
3. ✅ **Early Warning System** - Alert sebelum komponen rusak
4. ✅ **Data-Driven Decisions** - Analisis berbasis data historis
5. ✅ **Cost Optimization** - Maintenance terencana lebih murah
6. ✅ **Real-time Visibility** - Dashboard monitoring 24/7

### Expected ROI
```
Annual Cost per Vehicle (Target State):
├─ Scheduled Maintenance: Rp 12,000,000
├─ System Cost:          Rp  1,000,000
├─ Training:             Rp    500,000
└─ Monitoring:           Rp    500,000
   ─────────────────────────────────────
   TOTAL:                Rp 14,000,000

SAVINGS:                 Rp 21,000,000 (60% reduction)
For 50 Vehicles:         Rp 1,050,000,000/year

ROI Period: 17 days
```

---

## 3. SYSTEM PURPOSE

### Primary Objectives

1. **Attendance Management**
   - Digital check-in/check-out dengan GPS & foto
   - Real-time tracking driver on-duty
   - Automatic KM calculation
   - Daily vehicle condition checks

2. **Fleet Monitoring**
   - Real-time vehicle location
   - Vehicle health scoring
   - Usage analytics (KM, hours, fuel)
   - Driver performance tracking

3. **Preventive Maintenance** ⭐ (FOKUS UTAMA)
   - Component-level tracking (10 categories)
   - Automated maintenance scheduling
   - Multi-level alert system
   - Predictive failure detection (ML)
   - Cost tracking & optimization

4. **Reporting & Analytics**
   - Daily/monthly attendance reports
   - Maintenance compliance reports
   - Cost analysis & forecasting
   - Performance benchmarking

### Secondary Objectives

- Emergency reporting system
- Workshop management
- Parts inventory tracking
- Vendor portal integration
- Mobile mechanic dispatch

---

## 4. KEY FEATURES

### 4.1 Mobile App (Driver)

**Attendance Features:**
- ✅ Login dengan ID Badge & Password
- ✅ Check-in dengan foto selfie, speedometer, kondisi mobil
- ✅ GPS location tracking
- ✅ Check-out dengan checklist (ban, rem, lampu)
- ✅ Attendance history (30 records)

**Maintenance Features:**
- ✅ View vehicle health score
- ✅ Upcoming maintenance schedule
- ✅ Daily check enhanced (15+ items)
- ✅ Report issues dengan foto
- ✅ Maintenance history

**Emergency Features:**
- ✅ Emergency report dengan GPS & foto
- ✅ One-tap emergency call
- ✅ Real-time location sharing

**Notifications:**
- ✅ SIM expiry alert
- ✅ Maintenance reminder
- ✅ Vehicle health warning
- ✅ Schedule changes

### 4.2 Web Admin Panel

**Dashboard:**
- ✅ KPI cards (driver on-duty, aset tersedia, jarak bulan ini)
- ✅ Live map driver locations (Leaflet.js)
- ✅ Chart aktivitas 7 hari
- ✅ Latest emergency reports
- ✅ Upcoming maintenance widget

**Attendance Management:**
- ✅ Riwayat driver (filter: tanggal, driver, project)
- ✅ Riwayat unit (filter: plat, project, type)
- ✅ Rekap harian & bulanan
- ✅ Export Excel
- ✅ Koreksi KM (Master Admin only)

**Fleet Management:**
- ✅ CRUD vehicles
- ✅ Vehicle health monitoring
- ✅ Visual check kondisi
- ✅ Resolve issues
- ✅ Riwayat servis per unit

**Maintenance Management:** ⭐
- ✅ Component tracking (10 categories)
- ✅ Maintenance calendar (FullCalendar.js)
- ✅ Auto-generate schedules
- ✅ Alert dashboard
- ✅ Workshop management
- ✅ Cost tracking & budgeting

**Master Data:**
- ✅ CRUD drivers (filter: project, search)
- ✅ CRUD admin users
- ✅ CRUD projects
- ✅ CRUD workshops (future)

**Reports & Analytics:**
- ✅ Maintenance compliance report
- ✅ Cost analysis per vehicle
- ✅ Budget variance report
- ✅ Performance benchmarking
- ✅ Predictive insights (ML)

### 4.3 Preventive Maintenance System ⭐

**Component Tracking:**
```
10 Component Categories:
1. Fluids (Oli, Coolant, Brake Fluid, Power Steering)
2. Filters (Oil, Air, Fuel, Cabin)
3. Brakes (Pads, Discs, Fluid)
4. Tires (4 tires + spare)
5. Battery & Alternator
6. Lights (Headlights, Tail, Turn Signals)
7. Belts & Hoses (Timing, Serpentine, Radiator)
8. Suspension (Shock Absorbers, Struts, Ball Joints)
9. Engine (Spark Plugs, Ignition Coils, Fuel Injectors)
10. Transmission (Fluid, Clutch)
```

**Maintenance Scheduling:**
- Time-based (every X days)
- KM-based (every X km)
- Condition-based (health score threshold)
- Predictive (ML model)

**Alert System:**
```
Alert Levels:
🔴 CRITICAL  - Overdue, immediate action
🟠 HIGH      - Within critical threshold (100 KM)
🟡 MEDIUM    - Within warning threshold (500 KM)
🟢 LOW       - Routine reminder
```

**Health Scoring:**
```
Vehicle Health Score = (
    Component_Health_Average * 0.40 +
    Maintenance_Compliance * 0.30 +
    Daily_Check_Score * 0.20 +
    Age_Factor * 0.10
) * 100

Score Interpretation:
90-100: Excellent (🟢)
75-89:  Good (🟢)
60-74:  Fair (🟡)
40-59:  Poor (🟠)
0-39:   Critical (🔴)
```

---

## 5. USER ROLES

### 5.1 Driver (Mobile App)

**Permissions:**
- ✅ Check-in/check-out
- ✅ View own attendance history
- ✅ Submit emergency report
- ✅ View vehicle health (assigned vehicle)
- ✅ View upcoming maintenance
- ✅ Change own password
- ❌ Cannot view other drivers' data
- ❌ Cannot modify system data

**Typical Workflow:**
```
1. Login → 2. Check-in (foto selfie, speedo, kondisi) →
3. Bekerja → 4. Check-out (foto speedo, checklist) →
5. Logout
```

### 5.2 Admin (Web Dashboard)

**Permissions:**
- ✅ View all attendance data
- ✅ View all vehicle data
- ✅ Generate reports
- ✅ Export Excel
- ✅ View maintenance schedules
- ✅ Acknowledge alerts
- ✅ CRUD drivers (view only)
- ❌ Cannot delete drivers
- ❌ Cannot delete vehicles
- ❌ Cannot modify KM data

**Typical Workflow:**
```
1. Login → 2. Check dashboard →
3. Review alerts → 4. Schedule maintenance →
5. Generate reports → 6. Logout
```

### 5.3 Master Admin (Web Dashboard)

**Permissions:**
- ✅ All Admin permissions
- ✅ CRUD drivers (full access)
- ✅ CRUD vehicles (full access)
- ✅ CRUD projects
- ✅ CRUD admin users
- ✅ Modify KM data (koreksi)
- ✅ Delete records
- ✅ System configuration
- ✅ Access all reports

**Typical Workflow:**
```
1. Login → 2. Review KPIs →
3. Manage master data → 4. Review compliance →
5. Approve budgets → 6. Strategic decisions
```

### 5.4 Workshop Partner (Future)

**Permissions:**
- ✅ View assigned maintenance jobs
- ✅ Update job status
- ✅ Upload service reports
- ✅ Submit invoices
- ✅ View performance metrics
- ❌ Cannot view other workshops' data

---

## 6. SYSTEM COMPONENTS

### 6.1 Architecture Overview

```
┌─────────────────────────────────────────────────────────┐
│                    PRESENTATION LAYER                    │
├─────────────────────────────────────────────────────────┤
│  Mobile App (Flutter)    │    Web Admin (Blade + Vite)  │
│  - Driver Interface      │    - Admin Dashboard          │
│  - Offline Support       │    - Real-time Updates        │
│  - Push Notifications    │    - Charts & Analytics       │
└─────────────────────────────────────────────────────────┘
                          ↓
┌─────────────────────────────────────────────────────────┐
│                    APPLICATION LAYER                     │
├─────────────────────────────────────────────────────────┤
│  Laravel 12 Backend                                      │
│  ├─ REST API (Sanctum Auth)                            │
│  ├─ Business Logic Services                             │
│  ├─ Queue Jobs (Maintenance, Notifications)             │
│  ├─ Scheduled Commands (Cron)                           │
│  └─ Event Broadcasting (WebSocket)                      │
└─────────────────────────────────────────────────────────┘
                          ↓
┌─────────────────────────────────────────────────────────┐
│                      DATA LAYER                          │
├─────────────────────────────────────────────────────────┤
│  MySQL/PostgreSQL        │    Redis Cache               │
│  - Relational Data       │    - Session Storage         │
│  - Transactions          │    - Queue Jobs              │
│  - Full-text Search      │    - Real-time Data          │
└─────────────────────────────────────────────────────────┘
                          ↓
┌─────────────────────────────────────────────────────────┐
│                   EXTERNAL SERVICES                      │
├─────────────────────────────────────────────────────────┤
│  - Google Cloud Vision (OCR Speedometer)                │
│  - Firebase Cloud Messaging (Push Notifications)        │
│  - Twilio/Vonage (SMS - Optional)                       │
│  - Google Maps API (Location Services)                  │
│  - ML Microservice (Predictive Analytics - Future)      │
└─────────────────────────────────────────────────────────┘
```

### 6.2 Core Modules

**1. Authentication Module**
- Driver login (Sanctum token)
- Admin login (Session-based)
- Password management
- Single device policy

**2. Attendance Module**
- Check-in/check-out
- GPS tracking
- Photo upload & processing
- KM calculation
- Daily checks

**3. Fleet Management Module**
- Vehicle CRUD
- Health monitoring
- Usage tracking
- Visual inspection

**4. Preventive Maintenance Module** ⭐
- Component tracking
- Maintenance scheduling
- Alert generation
- Cost tracking
- Predictive analytics

**5. Reporting Module**
- Attendance reports
- Maintenance reports
- Cost analysis
- Performance metrics

**6. Emergency Module**
- Emergency reporting
- Real-time alerts
- Location tracking

---

## 7. TECHNOLOGY STACK

### Backend
- **Framework**: Laravel 12
- **PHP Version**: 8.2+
- **Authentication**: Laravel Sanctum
- **Database**: MySQL 8.0 / PostgreSQL 14
- **Cache**: Redis
- **Queue**: Redis / Database
- **Storage**: Local / S3

### Frontend (Web)
- **Template Engine**: Blade
- **CSS Framework**: Bootstrap 5 / Tailwind CSS
- **JavaScript**: Vanilla JS + Alpine.js
- **Build Tool**: Vite
- **Charts**: Chart.js / ApexCharts
- **Maps**: Leaflet.js
- **Calendar**: FullCalendar.js

### Mobile App
- **Framework**: Flutter 3.x
- **State Management**: Provider / Riverpod
- **HTTP Client**: Dio
- **Local Storage**: Hive / SQLite
- **Secure Storage**: Flutter Secure Storage
- **Image Processing**: Image Picker + Compressor
- **Maps**: Google Maps Flutter
- **Notifications**: Firebase Cloud Messaging

### DevOps
- **Version Control**: Git
- **CI/CD**: GitHub Actions / GitLab CI
- **Server**: Ubuntu 22.04 LTS
- **Web Server**: Nginx
- **Process Manager**: Supervisor
- **Monitoring**: Laravel Telescope + Horizon
- **Logging**: Monolog + Papertrail

### Third-party Services
- **OCR**: Google Cloud Vision API
- **Push Notifications**: Firebase Cloud Messaging
- **SMS**: Twilio / Vonage (optional)
- **Email**: SMTP / SendGrid
- **Maps**: Google Maps API
- **Storage**: AWS S3 / DigitalOcean Spaces

---

## 8. INTEGRATION POINTS

### 8.1 Internal Integrations

**Mobile App ↔ Backend API**
```
Protocol: HTTPS REST API
Auth: Bearer Token (Sanctum)
Format: JSON
Rate Limit: 60 req/min
```

**Web Admin ↔ Backend**
```
Protocol: HTTPS
Auth: Session-based
Format: HTML + JSON (AJAX)
Real-time: WebSocket (Laravel Echo)
```

**Backend ↔ Database**
```
Protocol: MySQL Protocol
Connection Pool: 10-50 connections
ORM: Eloquent
Migrations: Laravel Migrations
```

**Backend ↔ Cache**
```
Protocol: Redis Protocol
Driver: PhpRedis
TTL: 60s - 3600s
Tags: Supported
```

### 8.2 External Integrations

**Google Cloud Vision API**
```
Purpose: OCR speedometer photos
Endpoint: https://vision.googleapis.com/v1
Auth: Service Account JSON
Rate Limit: 1800 req/min
Cost: $1.50 per 1000 images
```

**Firebase Cloud Messaging**
```
Purpose: Push notifications to mobile
Endpoint: https://fcm.googleapis.com/fcm/send
Auth: Server Key
Rate Limit: Unlimited
Cost: Free
```

**Google Maps API**
```
Purpose: Location services, geocoding
Endpoint: https://maps.googleapis.com/maps/api
Auth: API Key
Rate Limit: 25,000 req/day (free tier)
Cost: $5 per 1000 requests (after free tier)
```

### 8.3 Future Integrations

**ML Microservice (Python)**
```
Purpose: Predictive maintenance
Protocol: REST API / gRPC
Framework: FastAPI / Flask
Models: Scikit-learn, TensorFlow
```

**IoT Devices (OBD-II)**
```
Purpose: Real-time vehicle diagnostics
Protocol: MQTT
Broker: Mosquitto / AWS IoT Core
Data Format: JSON
```

**Vendor Portal**
```
Purpose: Workshop self-service
Protocol: HTTPS REST API
Auth: OAuth 2.0
Format: JSON
```

---

## 9. DATA FLOW

### 9.1 Check-in Flow

```
Driver (Mobile)
    ↓ POST /api/submit-attendance
    ↓ (plate_number, gps, timestamp, photos)
Backend API
    ↓ Validate request
    ↓ Authenticate driver (Sanctum)
    ↓ Check if already on-duty
    ↓ Process images (resize, compress)
    ↓ Store in storage (S3/local)
    ↓ Create attendance record
    ↓ Update vehicle current_km
    ↓ Clear driver cache
    ↓ Return success response
Mobile App
    ↓ Show success message
    ↓ Update UI (on-duty status)
```

### 9.2 Maintenance Alert Flow

```
Scheduled Command (Cron)
    ↓ php artisan maintenance:check-due
    ↓ Query vehicles with components due
    ↓ Calculate remaining KM/days
    ↓ Generate alerts (warning/critical)
    ↓ Store in maintenance_alerts table
    ↓ Dispatch notification jobs
Queue Worker
    ↓ Process notification jobs
    ↓ Send email (Mail)
    ↓ Send push notification (FCM)
    ↓ Send SMS (Twilio - optional)
    ↓ Mark notification as sent
Admin Dashboard
    ↓ Display alert widget
    ↓ Real-time update via WebSocket
    ↓ Admin acknowledges alert
    ↓ Create maintenance schedule
```

---

## 10. SECURITY CONSIDERATIONS

### Authentication
- ✅ Password hashing (bcrypt)
- ✅ Token-based auth (Sanctum)
- ✅ Single device policy
- ✅ Token revocation on logout

### Authorization
- ✅ Role-based access control (RBAC)
- ✅ Gates & Policies
- ✅ Middleware protection
- ✅ API rate limiting

### Data Protection
- ✅ HTTPS only (TLS 1.3)
- ✅ SQL injection prevention (Eloquent)
- ✅ XSS prevention (Blade escaping)
- ✅ CSRF protection (web routes)
- ✅ Input validation
- ✅ File upload validation

### Privacy
- ✅ GPS data encryption
- ✅ Photo storage security
- ✅ Personal data masking
- ✅ GDPR compliance (future)

---

## 11. SCALABILITY

### Current Capacity
- **Users**: 50 drivers + 10 admins
- **Vehicles**: 50 units
- **Requests**: ~10,000 req/day
- **Storage**: ~10 GB/month (photos)
- **Database**: ~1 GB

### Scaling Strategy

**Horizontal Scaling:**
- Load balancer (Nginx)
- Multiple app servers
- Database read replicas
- Redis cluster

**Vertical Scaling:**
- Increase server resources
- Optimize queries
- Implement caching
- CDN for static assets

**Target Capacity (Year 2):**
- **Users**: 200 drivers + 50 admins
- **Vehicles**: 200 units
- **Requests**: ~50,000 req/day
- **Storage**: ~50 GB/month
- **Database**: ~5 GB

---

## 12. SUCCESS METRICS

### Technical Metrics
- **Uptime**: 99.5% (target 99.9%)
- **Response Time**: <500ms (API)
- **Page Load**: <2s (web)
- **Error Rate**: <0.1%
- **Database Query Time**: <100ms

### Business Metrics
- **Maintenance Compliance**: 95%
- **Vehicle Availability**: 95%
- **Cost Reduction**: 60%
- **MTBF Increase**: 40%
- **User Satisfaction**: 4.5/5

---

**Document Version**: 1.0  
**Last Updated**: 2026-05-14  
**Next Review**: 2026-08-14  
**Owner**: Product Team

---

**Related Documents:**
- [02. System Architecture](./02_SYSTEM_ARCHITECTURE.md)
- [03. Database Schema](./03_DATABASE_SCHEMA.md)
- [10. Preventive Maintenance Strategy](./10_PREVENTIVE_MAINTENANCE_STRATEGY.md)
- [23. Roadmap](./23_ROADMAP_PREVENTIVE_MAINTENANCE.md)
