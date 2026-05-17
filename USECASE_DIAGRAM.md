# 📊 Absen-Driver - Use Case Diagram

## System Overview
Sistem manajemen absensi driver dengan fitur GPS tracking, offline mode, dan report generation.

---

## 🎭 Use Case Diagram

```
╔════════════════════════════════════════════════════════════════════════════╗
║                        ABSEN-DRIVER SYSTEM                                 ║
╠════════════════════════════════════════════════════════════════════════════╣
║                                                                              ║
║                          ┌─────────────────┐                               ║
║                          │   ADMIN/HQ      │                               ║
║                          └────────┬────────┘                               ║
║                                   │                                        ║
║                    ┌──────────────┼──────────────┐                         ║
║                    │              │              │                         ║
║              ┌─────▼─────┐ ┌─────▼──────┐ ┌────▼────────┐                ║
║              │  Manage   │ │  Generate  │ │   View      │                ║
║              │  Drivers  │ │  Reports   │ │  Dashboard  │                ║
║              └─────┬─────┘ └─────┬──────┘ └────┬────────┘                ║
║                    │              │            │                          ║
║          ┌─────────┼──────────────┼────────────┼────────┐                ║
║          │         │              │            │        │                ║
║    ┌─────▼──┐ ┌───▼──┐ ┌────┬─┐  ┌┴──┐ ┌──┬──┐ ┌─────┐ │               ║
║    │ Delete │ │ Edit │ │Add │ │  │HR │ │PM │ │ │ KPI │ │               ║
║    │ Driver │ │Driver│ │New │ │  │Rpt│ │Rpt│ │ │Data │ │               ║
║    └────────┘ └──────┘ └────┘ │  └──┘ └───┘ │ └─────┘ │               ║
║                          └────┘    └────────┘         │               ║
║                                                        │               ║
║        ╔═══════════════════════════════════════════════╩════════════╗  ║
║        ║                   DATABASE                                   ║  ║
║        ║         (MySQL: Users, Reports, Analytics)                  ║  ║
║        ╚═════════════════════════════════════════════════════════════╝  ║
║                           ▲                                            ║
║                           │ HTTP API (Laravel)                         ║
║                           │ (Sanctum Token Auth)                       ║
║                           │                                            ║
║        ┌──────────────────┴──────────────────┐                         ║
║        │                                     │                         ║
║   ┌────▼──────────────┐          ┌──────────▼────┐                    ║
║   │  MOBILE APP       │          │   WEB ADMIN   │                    ║
║   │  (Flutter/Dart)   │          │  (Laravel     │                    ║
║   │                   │          │   Blade)      │                    ║
║   └────┬──────────────┘          └──────────────┘                    ║
║        │                                                               ║
║   ┌────┴─────────────────────────────┬──────────────────┐             ║
║   │                                  │                  │             ║
║   │                                  │                  │             ║
║   ▼                                  ▼                  ▼             ║
║ ┌─────────────────────────────────────────────────────────────────┐  ║
║ │                      DRIVER USER                                │  ║
║ │         (Menggunakan Mobile App di Smartphone)                 │  ║
║ └─────────────────────────────────────────────────────────────────┘  ║
║   │                                  │                  │             ║
║   │                                  │                  │             ║
║   ├──────────────┬──────────────┬────┴────┬────────────┤             ║
║   │              │              │         │            │             ║
║   ▼              ▼              ▼         ▼            ▼             ║
║┌───────────┐┌────────────┐┌──────────┐┌────────┐┌───────────┐       ║
║│ Check In  ││ Check Out  ││   Take   ││Offline ││  Sync to  │       ║
║│  (GPS +   ││  (GPS +    ││ Selfie   ││ Mode   ││ Server    │       ║
║│Timestamp) ││Timestamp)  ││& Photo   ││(Sembast││ (When     │       ║
║│           ││            ││Evidence  ││DB)     ││ Connected)│       ║
║└─────┬─────┘└────┬───────┘└────┬─────┘└────┬───┘└─────┬────┘       ║
║      │           │             │           │          │             ║
║      │           │             │           │          │             ║
║      └───────────┴─────────────┴───────────┴──────────┘             ║
║                        │                                            ║
║                        ▼                                            ║
║          ┌──────────────────────────────┐                           ║
║          │   Validate & Store Data      │                           ║
║          │ • Location Coordinates       │                           ║
║          │ • Timestamp (accurate)       │                           ║
║          │ • Photo (compressed)         │                           ║
║          │ • Status (On-time/Late)      │                           ║
║          └──────────────────┬───────────┘                           ║
║                             │                                       ║
║          ┌──────────────────▼───────────────────┐                   ║
║          │   Send to Backend (HTTP Request)    │                   ║
║          │   with Sanctum Token Authentication │                   ║
║          └──────────────────┬───────────────────┘                   ║
║                             │                                       ║
║          ┌──────────────────▼───────────────────────────────┐       ║
║          │  Backend Processing                             │       ║
║          │  • Validate Token & User                        │       ║
║          │  • Verify Location Accuracy                     │       ║
║          │  • Compress & Store Image                       │       ║
║          │  • Update Attendance Record                     │       ║
║          │  • Calculate Overtime/Deduction                 │       ║
║          └──────────────────┬───────────────────────────────┘       ║
║                             │                                       ║
║          ┌──────────────────▼───────────────────┐                   ║
║          │   Store in Database                 │                   ║
║          │   (MySQL dengan relationship tables)│                   ║
║          └──────────────────────────────────────┘                   ║
║                                                                      ║
╚══════════════════════════════════════════════════════════════════════╝
```

---

## 📋 Detailed Use Cases by Actor

### **1️⃣ DRIVER (Mobile App User)**

| Use Case | Description | Precondition | Flow |
|----------|-------------|--------------|------|
| **Check In** | Driver mencatat waktu masuk kerja | Connected to Internet or Offline Mode | 1. Open App → 2. Tap Check In → 3. Capture GPS Location → 4. Take Selfie → 5. Submit |
| **Check Out** | Driver mencatat waktu pulang kerja | Check In status already recorded | 1. Open App → 2. Tap Check Out → 3. Capture GPS → 4. Take Selfie → 5. Submit |
| **View Attendance History** | Driver melihat riwayat absensi | Logged In | 1. Navigate to History Tab → 2. View list (Check In/Out times) → 3. Filter by date |
| **Offline Mode** | Driver bisa absen tanpa internet | App Installed with Local DB | 1. Check In/Out locally → 2. Data saved in Sembast DB → 3. Auto-sync when internet back |
| **Sync Data** | Manual sync data ke server | Offline data exists & Internet Available | 1. Pull to refresh → 2. App syncs Sembast → MySQL → 3. Show confirmation |
| **View Location Map** | Driver melihat history lokasi GPS | Attended locations exist | 1. Navigate to Map Tab → 2. Show multiple pin locations → 3. Tap for details |

---

### **2️⃣ ADMIN/HQ (Web Dashboard)**

| Use Case | Description | Precondition | Flow |
|----------|-------------|--------------|------|
| **Manage Drivers** | CRUD driver data | Admin logged in | 1. View driver list → 2. Add/Edit/Delete → 3. Set permissions |
| **View Real-time Dashboard** | Monitor aktivitas driver saat ini | Drivers are active | 1. Dashboard shows → 2. Live map with active drivers → 3. Count & status |
| **Generate Reports** | Export laporan absensi ke Excel | Date range selected | 1. Select date range → 2. Choose report type (HR/Performance) → 3. Generate .xlsx → 4. Download |
| **View Attendance Records** | Lihat detail absensi setiap driver | Driver selected | 1. Click Driver name → 2. Show all check-in/out records → 3. View photos & GPS coordinates |
| **Manage QR Codes** | Generate QR untuk ID Card & Stiker | Driver created | 1. Select driver → 2. Generate unique QR → 3. Print atau Send |
| **View Analytics & KPI** | Dashboard KPI driver performance | Data exists | 1. Chart on-time rate → 2. Overtime hours → 3. Attendance trends |
| **Create Approval Workflow** | Approve/reject manual attendance adjustments | Request submitted by driver | 1. View pending requests → 2. Review reason → 3. Approve/Reject → 4. Update record |

---

### **3️⃣ SYSTEM (Backend Process)**

| Use Case | Description | Trigger | Process |
|----------|-------------|---------|---------|
| **Validate Attendance** | Sistem validasi data absensi | Check In/Out submitted | 1. Verify token with Sanctum → 2. Check GPS accuracy → 3. Validate timestamp → 4. Confirm data integrity |
| **Compress Image** | Kompres foto selfie/bukti | Photo uploaded | 1. Use Intervention Image → 2. Resize to optimal size → 3. Reduce file size → 4. Store in storage/uploads |
| **Generate QR Code** | Generate QR untuk driver identification | Driver created | 1. Create unique code for driver → 2. Generate QR image → 3. Store reference in DB |
| **Calculate Overtime** | Hitung jam overtime otomatis | Check Out submitted | 1. Get scheduled end time → 2. Compare with actual end time → 3. Calculate diff → 4. Log overtime hours |
| **Auto-sync Offline Data** | Background sync Sembast → MySQL | App reconnects to internet | 1. Detect connection → 2. Fetch unsync records from local DB → 3. Send batch to API → 4. Clear local cache → 5. Confirm |
| **Generate Attendance Report** | Create Excel report with formatting | Admin requests | 1. Query attendance data → 2. Format with Maatwebsite Excel → 3. Add charts → 4. Generate & serve file |
| **Send Notifications** | Push notification untuk reminders | Specific time or event | 1. Check trigger time → 2. Queue notification job → 3. Send to mobile app → 4. Display alert |

---

## 🔄 System Architecture Flow

```
┌─────────────────────────────────────────────────────────────┐
│                    DRIVER (Smartphone)                      │
│  ┌──────────────────────────────────────────────────────┐   │
│  │  Flutter App (Dart)                                  │   │
│  │  ┌──────────────┐      ┌──────────────────┐         │   │
│  │  │ UI Layer     │◄────►│ State Management │         │   │
│  │  │ • Check In   │      │ (SetState)       │         │   │
│  │  │ • Check Out  │      │                  │         │   │
│  │  │ • Settings   │      └────────┬─────────┘         │   │
│  │  └────┬─────────┘               │                   │   │
│  │       │                         ▼                   │   │
│  │       │              ┌──────────────────┐           │   │
│  │       │              │ Services Layer   │           │   │
│  │       │              │ • http (API)     │           │   │
│  │       │              │ • geolocator     │           │   │
│  │       │              │ • image_picker   │           │   │
│  │       │              │ • mobile_scanner │           │   │
│  │       │              └────────┬─────────┘           │   │
│  │       │                       │                     │   │
│  │       └───────────────────────┼─────────┐           │   │
│  │                               │         │           │   │
│  │              ┌────────────────▼────┐    │           │   │
│  │              │  Sembast (Local DB) │    │           │   │
│  │              │ (Offline Storage)   │    │           │   │
│  │              └────────────────────┘    │           │   │
│  │                                        │           │   │
│  │        ┌───────────────────────────────▼───┐       │   │
│  │        │ Connectivity Check                │       │   │
│  │        │ • connectivity_plus               │       │   │
│  │        │ • Auto-sync when online           │       │   │
│  │        └───────────────┬───────────────────┘       │   │
│  │                        │                           │   │
│  └────────────────────────┼───────────────────────────┘   │
│                           │                               │
│                           │ HTTP/HTTPS                    │
│                           │ (Sanctum Token in Header)     │
│                           ▼                               │
├─────────────────────────────────────────────────────────────┤
│                                                             │
│            🖥️  BACKEND (Laravel Server)                    │
│                                                             │
│  ┌────────────────────────────────────────────────────┐    │
│  │ API Routes (app/Http/Controllers)                 │    │
│  │                                                    │    │
│  │ POST   /api/attendance/check-in                   │    │
│  │ POST   /api/attendance/check-out                  │    │
│  │ GET    /api/attendance/history                    │    │
│  │ POST   /api/upload/photo                          │    │
│  │ GET    /api/driver/qrcode/:id                     │    │
│  │                                                    │    │
│  └──────────────┬─────────────────────────────────────┘    │
│                 │                                          │
│  ┌──────────────▼────────────────────────────────────┐    │
│  │ Middleware & Validation                          │    │
│  │ • Sanctum Authentication                         │    │
│  │ • Token Verification                             │    │
│  │ • Rate Limiting                                  │    │
│  │ • Input Validation                               │    │
│  └──────────────┬─────────────────────────────────────┘    │
│                 │                                          │
│  ┌──────────────▼────────────────────────────────────┐    │
│  │ Business Logic Layer                             │    │
│  │ • AttendanceService                              │    │
│  │ • LocationValidator                              │    │
│  │ • ImageProcessor (Intervention Image)            │    │
│  │ • ReportGenerator (Maatwebsite Excel)            │    │
│  │ • QRCodeGenerator                                │    │
│  └──────────────┬─────────────────────────────────────┘    │
│                 │                                          │
│  ┌──────────────▼────────────────────────────────────┐    │
│  │ Database Layer (Eloquent ORM)                    │    │
│  │ • Attendance Model                               │    │
│  │ • Driver Model                                   │    │
│  │ • User Model                                     │    │
│  │ • Photo Model                                    │    │
│  └──────────────┬─────────────────────────────────────┘    │
│                 │                                          │
│  ┌──────────────▼────────────────────────────────────┐    │
│  │ MySQL Database                                   │    │
│  │ • drivers table                                  │    │
│  │ • attendances table                              │    │
│  │ • attendance_photos table                        │    │
│  │ • users table (admin/HQ)                         │    │
│  │ • reports table                                  │    │
│  │ • qrcodes table                                  │    │
│  └──────────────────────────────────────────────────┘    │
│                                                             │
│  ┌────────────────────────────────────────────────────┐    │
│  │ Storage & External Services                      │    │
│  │ • /storage/uploads/photos (compressed images)    │    │
│  │ • Google Vision API (optional OCR)               │    │
│  │ • Google Maps API (location validation)          │    │
│  │ • Queue System (async jobs)                      │    │
│  └────────────────────────────────────────────────────┘    │
│                                                             │
├─────────────────────────────────────────────────────────────┤
│                                                             │
│            🌐 WEB ADMIN DASHBOARD (Laravel Blade)          │
│                                                             │
│  ┌────────────────────────────────────────────────────┐    │
│  │ Pages & Views                                    │    │
│  │ • Dashboard (Real-time map & stats)              │    │
│  │ • Driver Management (CRUD)                       │    │
│  │ • Attendance Records (Filter & search)           │    │
│  │ • Reports (Generate & download)                  │    │
│  │ • QR Code Management                             │    │
│  │ • Analytics & KPI                                │    │
│  │                                                  │    │
│  │ Frontend Stack:                                  │    │
│  │ • Bootstrap 5 (CSS Framework)                    │    │
│  │ • Chart.js (Real-time charts)                    │    │
│  │ • FullCalendar (Schedule calendar)               │    │
│  │ • SweetAlert2 (Notifications)                    │    │
│  │ • Vanilla JS + ES6                               │    │
│  └────────────────────────────────────────────────────┘    │
│                                                             │
└─────────────────────────────────────────────────────────────┘
```

---

## 📊 Data Flow Diagram

### **Check In Process:**
```
Driver Opens App
        ↓
    Location Enabled (GPS)
        ↓
    Take Selfie / Photo
        ↓
    Validate Data (Client-side)
        ↓
    ┌─────────────────┐
    │ Has Internet?   │
    └────────┬────────┘
      Yes ↙     ↖ No
        │         │
        ▼         ▼
    HTTP POST   Save in
    to Backend  Sembast
        │       (Local DB)
        │         │
        ▼         ▼
    Sanctum      Queue for
    Auth         Sync
        │         │
        ▼         ▼
    Backend    Waiting for
    Validates  Connection...
        │       
        ▼       
    Compress   
    Image      
        │       
        ▼       
    Store in   
    Database   
        │       
        ▼       
    Send JSON  
    Response   
        │       
        ▼       
    Show Success ← ← ← ← ─ ─ When Online:
    Notification    Auto-Sync Data
                    & Delete Local Cache
```

---

## 🔐 Authentication Flow

```
┌────────────────────────────────────────────────────────┐
│  Mobile App (Flutter)                                  │
│  1. User Login: username + password                    │
│  2. POST /api/login                                    │
│  3. Backend validates credentials                      │
│  4. Sanctum generates token: "1|a1b2c3d4e5f6g7h8"      │
│  5. Token stored in SharedPreferences                  │
│  6. Every API call includes:                           │
│     Header: Authorization: Bearer 1|a1b2c3d4e5f6g7h8  │
│  7. Backend verifies token with Sanctum                │
│  8. If valid → Process request                         │
│  9. If invalid → Return 401 Unauthorized               │
└────────────────────────────────────────────────────────┘
```

---

## 🎯 Key Features Mapped to Use Cases

| Feature | Primary Actors | Use Case |
|---------|---------------|----|
| **Real-time GPS Tracking** | Driver, Admin | Check In/Out, View Location Map |
| **Photo Evidence** | Driver | Check In/Out (Selfie capture) |
| **Offline Mode (Sembast)** | Driver, System | Offline Mode, Auto-sync |
| **QR Code Generation** | Admin, System | Manage QR Codes |
| **Attendance Reports** | Admin, System | Generate Reports (HR/Performance) |
| **Mobile App** | Driver | All driver use cases |
| **Web Dashboard** | Admin | All admin use cases |
| **Token Authentication** | System | Secure API access |
| **Image Compression** | System | Photo storage optimization |
| **Data Validation** | System | Maintain data integrity |

---

## 📱 Technologies Used

### **Backend:**
- Laravel 12 (PHP 8.2+)
- MySQL Database
- Laravel Sanctum (API Authentication)
- Maatwebsite Excel (Report generation)
- Intervention Image (Image compression)
- Simple QRCode (QR generation)
- Google Cloud Vision (optional OCR)
- Google Maps API (location validation)

### **Mobile:**
- Flutter (Dart)
- HTTP (API calls)
- Geolocator (GPS)
- Image Picker (Camera)
- Mobile Scanner (QR scan)
- Sembast (Local NoSQL DB)
- Connectivity Plus (Internet detection)
- Shared Preferences (Token storage)

### **Frontend:**
- Bootstrap 5
- Chart.js (Analytics)
- FullCalendar (Scheduling)
- SweetAlert2 (Notifications)
- JavaScript ES6

---

## ✅ Acceptance Criteria

All use cases must satisfy:
- **Performance:** API response < 2 seconds
- **Availability:** 99.5% uptime
- **Security:** All data encrypted in transit (HTTPS)
- **Accuracy:** GPS accuracy ±10 meters
- **Offline:** 100% functionality without internet
- **Sync:** Auto-sync within 5 minutes after reconnection
- **Scalability:** Support 1000+ concurrent drivers
- **Compliance:** GDPR-compliant data handling

---

**Created:** 2026-04-30  
**Version:** 1.0  
**Status:** Draft

