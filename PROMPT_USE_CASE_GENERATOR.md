# PROMPT GENERATOR: USE CASE SPECIFICATION
# Sistem Tracking Operasional & Pemeliharaan Preventif Armada

---

## 🎯 TUJUAN PROMPT INI

Prompt ini digunakan untuk generate use case specification yang lengkap dan terstruktur untuk sistem Fleet Management dengan fokus pada:
- Tracking operasional driver dan kendaraan
- Manajemen pemeliharaan preventif berbasis prediksi
- Monitoring kesehatan armada dengan health scoring
- Approval workflow dan reporting

---

## 📋 TEMPLATE PROMPT UNTUK AI

Copy prompt di bawah ini dan sesuaikan dengan use case yang ingin dibuat:

---

### PROMPT TEMPLATE:

```
Saya ingin membuat dokumentasi use case lengkap untuk sistem Fleet Management.

## KONTEKS PROJECT:
- **Nama Sistem:** Sistem Tracking Operasional & Pemeliharaan Preventif Armada
- **Tech Stack:** Laravel 10 (Backend), Flutter (Mobile), MySQL (Database)
- **Arsitektur:** REST API (Sanctum Token), Web Dashboard, Mobile App
- **Metodologi:** Use Case 2.0 (Cockburn Style - Fully Dressed Format)

## AKTOR SISTEM:
1. **Driver** - Menggunakan mobile app untuk check-in/out, laporan
2. **Admin Master** - Full access ke web dashboard
3. **Service Admin** - Fokus maintenance management
4. **Customer** - Portal untuk monitoring kendaraan miliknya
5. **Scheduler** - System otomatis untuk task terjadwal

## FITUR UTAMA YANG SUDAH ADA:
- Authentication dengan Laravel Sanctum (token-based)
- Check-in/out dengan GPS, foto, speedometer
- Preventive maintenance dengan component tracking
- Health scoring system (weighted formula)
- Alert & schedule generation otomatis
- Approval workflow (service report, transport cost)
- Customer portal dengan certificate
- Export Excel untuk reporting

## USE CASE YANG INGIN DIBUAT:

**ID:** [UC-XX]
**Nama:** [Nama Use Case]
**Aktor:** [Driver / Admin Master / Service Admin / Customer / Scheduler]
**Deskripsi Singkat:** [Jelaskan apa yang dilakukan use case ini]

## FORMAT OUTPUT YANG DIINGINKAN:

Buatkan dokumentasi use case dengan format berikut:

### [ID]: [Nama Use Case]

**ID:** [UC-XX]
**Nama:** [Nama Use Case]
**Aktor Utama:** [Aktor]
**Level:** [User Goal / System Goal]

**Stakeholder & Kepentingan:**
- [Aktor 1]: [Kepentingan]
- [Aktor 2]: [Kepentingan]
- [Sistem]: [Kepentingan]

**Precondition:**
- [Kondisi yang harus terpenuhi sebelum use case dimulai]

**Postcondition (Success):**
- [Kondisi setelah use case berhasil]

**Postcondition (Failure):**
- [Kondisi setelah use case gagal]

**Main Success Scenario:**
1. [Step 1]
2. [Step 2]
3. [Step 3]
... [Detail step-by-step]

**Extensions (Alternative Flows):**

*[Step]a. [Kondisi alternatif]:*
- [Step]a1. [Action]
- [Step]a2. [Action]
- [Step]a3. [Kembali ke step X atau use case berakhir]

**Special Requirements:**
- [Requirement 1]
- [Requirement 2]

**Technology & Data Variations:**
- API Endpoint: [Method] [URL]
- Middleware: [List middleware]
- Controller: [Controller name]
- Database: [Table names]
- Storage: [Storage path jika ada]

**Frequency:** [Seberapa sering use case ini terjadi]

**Status Implementasi:** [✅ IMPLEMENTED / ⚠️ PARTIAL / ❌ NOT IMPLEMENTED]

**Open Issues:** (jika ada)
- [Issue 1]
- [Issue 2]

---

## GUIDELINES PENULISAN:

1. **Main Success Scenario** harus detail step-by-step
2. **Extensions** harus cover semua kemungkinan error/alternative
3. **Technology** harus spesifik (nama controller, route, table)
4. **Special Requirements** harus measurable (response time, file size, dll)
5. Gunakan bahasa yang jelas dan tidak ambigu
6. Setiap step harus actionable
7. Include validasi dan error handling
8. Sebutkan security considerations jika relevan

## CONTOH REFERENSI:

Gunakan format yang sama dengan use case yang sudah ada di file:
`FULL_USE_CASE_SPECIFICATION.md`

Khususnya perhatikan:
- UC-01 (Login) - untuk authentication flow
- UC-02 (Check-In) - untuk operational flow dengan foto
- UC-08 (Kelola Komponen) - untuk CRUD flow
- UC-10 (Generate Alert) - untuk scheduler flow
- UC-22 (Approve Service) - untuk approval workflow

```

---


## 🔧 CARA MENGGUNAKAN PROMPT INI

### Langkah 1: Identifikasi Use Case
Tentukan use case apa yang ingin dibuat. Contoh:
- "Saya ingin buat use case untuk fitur notifikasi push ke driver"
- "Saya ingin buat use case untuk export PDF laporan maintenance"
- "Saya ingin buat use case untuk dashboard analytics"

### Langkah 2: Isi Template
Copy template prompt di atas, lalu isi bagian:
- **ID:** Berikan nomor urut (UC-25, UC-26, dst)
- **Nama:** Nama use case yang deskriptif
- **Aktor:** Pilih dari 5 aktor yang ada
- **Deskripsi Singkat:** 1-2 kalimat tentang use case

### Langkah 3: Submit ke AI
Paste prompt yang sudah diisi ke AI assistant (ChatGPT, Claude, dll)

### Langkah 4: Review & Refine
Review hasil output, pastikan:
- ✅ Format sesuai dengan template
- ✅ Step-by-step jelas dan actionable
- ✅ Extensions cover semua error case
- ✅ Technology details akurat
- ✅ Sesuai dengan arsitektur project

---

## 📝 CONTOH PENGGUNAAN

### Contoh 1: Use Case Baru (Notifikasi Push)

```
[Copy template prompt di atas, lalu tambahkan:]

## USE CASE YANG INGIN DIBUAT:

**ID:** UC-25
**Nama:** Kirim Notifikasi Push ke Driver
**Aktor:** Scheduler (System), Driver (Receiver)
**Deskripsi Singkat:** 
Sistem mengirim notifikasi push ke driver saat ada alert maintenance critical 
atau saat ada approval service report yang perlu diketahui driver.

[Lalu submit ke AI]
```

### Contoh 2: Use Case Baru (Export PDF)

```
[Copy template prompt di atas, lalu tambahkan:]

## USE CASE YANG INGIN DIBUAT:

**ID:** UC-26
**Nama:** Export Laporan Maintenance ke PDF
**Aktor:** Admin Master, Service Admin
**Deskripsi Singkat:** 
Admin dapat export laporan maintenance bulanan dalam format PDF yang 
professional untuk presentasi ke management atau customer.

[Lalu submit ke AI]
```

### Contoh 3: Use Case Baru (Dashboard Analytics)

```
[Copy template prompt di atas, lalu tambahkan:]

## USE CASE YANG INGIN DIBUAT:

**ID:** UC-27
**Nama:** Dashboard Analytics & Insights
**Aktor:** Admin Master
**Deskripsi Singkat:** 
Dashboard yang menampilkan analytics mendalam tentang performa armada, 
trend maintenance cost, prediksi kerusakan, dan insights untuk decision making.

[Lalu submit ke AI]
```

---

## 🎨 VARIASI PROMPT UNTUK KEBUTUHAN SPESIFIK

### A. Untuk Use Case dengan Approval Workflow

Tambahkan di bagian deskripsi:
```
**Workflow:** [Initiator] → [Approver 1] → [Approver 2] → [Final State]
**Status Transitions:** pending → approved/rejected → completed
**Notification:** Setiap stage perlu notifikasi ke stakeholder terkait
```

### B. Untuk Use Case dengan File Upload

Tambahkan di bagian deskripsi:
```
**File Requirements:**
- Format: [PDF, JPG, PNG, dll]
- Max Size: [2MB, 5MB, dll]
- Validation: [File type check, virus scan, dll]
- Storage: [Path storage]
- Security: [Authenticated access, encryption, dll]
```

### C. Untuk Use Case dengan Real-time Data

Tambahkan di bagian deskripsi:
```
**Real-time Requirements:**
- Update Frequency: [Setiap X detik/menit]
- Technology: [WebSocket, Polling, Server-Sent Events]
- Fallback: [Jika real-time gagal]
- Performance: [Max latency, concurrent users]
```

### D. Untuk Use Case dengan Scheduler/Cron

Tambahkan di bagian deskripsi:
```
**Scheduler Configuration:**
- Frequency: [Hourly, Daily, Weekly, Custom cron]
- Command: [Artisan command name]
- Timeout: [Max execution time]
- Overlap Prevention: [Yes/No]
- Error Handling: [Retry logic, notification]
```

### E. Untuk Use Case dengan External API

Tambahkan di bagian deskripsi:
```
**External Integration:**
- API Provider: [Nama provider]
- Authentication: [API Key, OAuth, dll]
- Endpoints: [List endpoints yang digunakan]
- Rate Limiting: [Request limits]
- Error Handling: [Timeout, retry, fallback]
```

---


## 📚 REFERENSI ARSITEKTUR PROJECT

### Tech Stack Details:
```yaml
Backend:
  Framework: Laravel 10
  Authentication: Laravel Sanctum (Token-based)
  Database: MySQL
  Cache: File/Redis
  Queue: Database/Redis
  Storage: Local (storage/app/)
  
Frontend Web:
  Framework: Blade Templates
  CSS: Bootstrap 5 / Tailwind
  JavaScript: Vanilla JS / Alpine.js
  Charts: Chart.js / ApexCharts
  Calendar: FullCalendar.js
  
Mobile:
  Framework: Flutter
  State Management: Provider / Bloc
  Local Storage: sqflite
  HTTP Client: Dio
  Image: image_picker, cached_network_image
  
API:
  Protocol: REST
  Format: JSON
  Authentication: Bearer Token (Sanctum)
  Rate Limiting: Throttle middleware
  Versioning: URL-based (/api/v1/)
```

### Database Schema (Key Tables):
```sql
-- Core Tables
users (id, username, password, role, ...)
drivers (id, user_id, nik_ktp, full_name, project_id, ...)
vehicles (id, plate_number, type, project_id, current_km, ...)
projects (id, name, customer_id, ...)
customers (id, name, email, phone, ...)

-- Operational Tables
attendances (id, driver_id, vehicle_id, time_in, time_out, 
             speedo_awal, speedo_akhir, check_ban, check_rem, 
             check_lampu, gps_location_in, gps_location_out, ...)
emergency_reports (id, driver_id, vehicle_id, timestamp, 
                   gps_location, description, proof_photo_path, ...)
service_reports (id, driver_id, vehicle_id, description, 
                 estimated_cost, status, photos, ...)
transport_costs (id, driver_id, attendance_id, amount, 
                 receipt_photo, status, ...)

-- Maintenance Tables
vehicle_components (id, vehicle_id, component_name, category,
                    replacement_interval_km, replacement_interval_days,
                    last_replacement_km, last_replacement_date,
                    status, cost_per_replacement, ...)
maintenance_schedules (id, vehicle_id, component_id, scheduled_date,
                       scheduled_km, type, priority, status,
                       estimated_cost, actual_cost, ...)
maintenance_alerts (id, vehicle_id, component_id, alert_type,
                    status, triggered_at, acknowledged_at, ...)

-- Audit Tables
audit_histories (id, user_id, action, table_name, record_id, ...)
```

### API Endpoints Pattern:
```
Authentication:
POST   /api/login
POST   /api/logout
POST   /api/change-password

Driver Operations:
POST   /api/submit-attendance
POST   /api/submit-end-of-duty
POST   /api/submit-emergency-report
POST   /api/submit-service-report
GET    /api/driver-details
GET    /api/driver/status
GET    /api/driver/history

Transport Cost:
GET    /api/transport-costs/can-create
POST   /api/transport-costs
GET    /api/transport-costs
GET    /api/transport-costs/{id}

Maintenance (Admin API):
GET    /api/vehicles/health
GET    /api/vehicles/{vehicle}/health
GET    /api/vehicles/{vehicle}/components
POST   /api/vehicles/{vehicle}/components
PUT    /api/vehicles/{vehicle}/components/{component}
DELETE /api/vehicles/{vehicle}/components/{component}
GET    /api/maintenance/schedules
POST   /api/maintenance/schedules
POST   /api/maintenance/schedules/{schedule}/complete
GET    /api/maintenance/alerts
POST   /api/maintenance/alerts/{alert}/acknowledge
POST   /api/maintenance/alerts/{alert}/resolve
```

### Middleware Stack:
```php
// Authentication
'auth:sanctum' - Require valid token
'auth' - Require web session

// Authorization
'role:driver' - Only driver role
'role:master,service_admin' - Admin roles
'role:customer' - Customer role
'customer.vehicle' - Customer owns vehicle

// Rate Limiting
'throttle:10,1' - 10 requests per minute (login)
'throttle:60,1' - 60 requests per minute (API)
'throttle:30,1' - 30 requests per minute (data modification)
'throttle:10,1' - 10 requests per minute (destructive actions)
```

### Security Patterns:
```php
// Input Validation
- Server-side validation (Laravel Request)
- SQL injection prevention (Eloquent ORM)
- XSS prevention (Blade escaping)
- CSRF protection (Web routes)

// File Upload Security
- File type whitelist (jpg, jpeg, png, pdf)
- Max file size (2MB for images)
- Path traversal prevention (basename, realpath)
- Authenticated access only
- Secure storage (storage/app/, not public)

// Authentication Security
- Password hashing (bcrypt)
- Token-based auth (Sanctum)
- Token expiry (30 days)
- Rate limiting (throttle)
- HTTPS required (production)

// Authorization Security
- Role-based access control
- Resource ownership check
- Middleware protection
- API token scope
```

---

## 🎯 CHECKLIST KUALITAS USE CASE

Sebelum finalisasi use case, pastikan:

### ✅ Completeness (Kelengkapan)
- [ ] Semua section terisi (ID, Nama, Aktor, dll)
- [ ] Main scenario minimal 5 steps
- [ ] Extensions cover minimal 3 error cases
- [ ] Technology details lengkap (endpoint, controller, table)
- [ ] Precondition & postcondition jelas

### ✅ Clarity (Kejelasan)
- [ ] Setiap step actionable (ada subjek & verb)
- [ ] Tidak ada ambiguitas
- [ ] Istilah teknis konsisten
- [ ] Bahasa mudah dipahami
- [ ] Flow logic masuk akal

### ✅ Consistency (Konsistensi)
- [ ] Format sesuai template
- [ ] Naming convention konsisten
- [ ] Technology stack sesuai project
- [ ] Database schema sesuai existing
- [ ] API pattern sesuai convention

### ✅ Correctness (Kebenaran)
- [ ] Flow sesuai business logic
- [ ] Validasi sesuai requirement
- [ ] Error handling lengkap
- [ ] Security consideration ada
- [ ] Performance requirement realistis

### ✅ Traceability (Ketertelusuran)
- [ ] Bisa trace ke code (controller, route)
- [ ] Bisa trace ke database (table, column)
- [ ] Bisa trace ke UI (screen, button)
- [ ] Bisa trace ke test case
- [ ] Bisa trace ke requirement

---

## 💡 TIPS & BEST PRACTICES

### 1. Gunakan Active Voice
❌ Bad: "Data akan divalidasi oleh sistem"
✅ Good: "Sistem memvalidasi data"

### 2. Spesifik dalam Error Handling
❌ Bad: "Jika error, tampilkan pesan error"
✅ Good: "Jika validasi gagal, sistem mengirim error 422 dengan message spesifik per field"

### 3. Include Security Considerations
✅ Always mention:
- Authentication requirement
- Authorization check
- Input validation
- Rate limiting
- File upload security

### 4. Measurable Requirements
❌ Bad: "Response harus cepat"
✅ Good: "Response time < 2 detik untuk 95% request"

### 5. Technology Specific
❌ Bad: "Simpan ke database"
✅ Good: "Simpan ke table `attendances` via Eloquent ORM"

### 6. Consider Edge Cases
Always think about:
- What if data not found?
- What if network error?
- What if concurrent access?
- What if file too large?
- What if invalid input?

### 7. Link to Existing Use Cases
Jika use case baru depend on existing:
"Precondition: User sudah login (lihat UC-01)"

### 8. Version Control
Jika update existing use case:
"**Version:** 1.1 (Updated: 2026-06-01)"
"**Changes:** Added offline sync capability"

---

## 📖 GLOSSARY PROJECT-SPECIFIC

**Attendance:** Record absensi driver (check-in & check-out)
**Component:** Komponen kendaraan yang perlu maintenance berkala
**Health Score:** Skor kesehatan kendaraan (0-100) berdasarkan weighted formula
**Alert:** Notifikasi otomatis saat komponen perlu maintenance
**Schedule:** Jadwal maintenance yang sudah direncanakan
**Hybrid Approach:** Kombinasi data operasional (driver checklist) dan prediktif (komponen)
**Throttling:** Rate limiting untuk mencegah spam/abuse
**Sanctum:** Laravel authentication system untuk API token
**Scheduler:** Laravel task scheduler untuk menjalankan command otomatis
**Speedo:** Speedometer / Odometer kendaraan (KM)
**Plat Nomor:** Nomor polisi kendaraan (plate number)
**NIK:** Nomor Induk Kependudukan (ID card number)
**STNK:** Surat Tanda Nomor Kendaraan (vehicle registration)
**KIR:** Keur Inspeksi Rancangan (vehicle inspection)

---

## 🚀 QUICK START EXAMPLES

### Example 1: Simple CRUD Use Case
```
ID: UC-28
Nama: Kelola Kategori Komponen
Aktor: Admin Master
Deskripsi: Admin dapat menambah, edit, dan hapus kategori komponen kendaraan

[Submit dengan template prompt di atas]
```

### Example 2: Complex Workflow Use Case
```
ID: UC-29
Nama: Approval Cascade Service Report
Aktor: Driver, Service Admin, Customer, Finance
Deskripsi: Service report melalui approval bertingkat dari service admin, 
customer, hingga finance dengan notifikasi di setiap stage

[Submit dengan template prompt + tambahan workflow di atas]
```

### Example 3: Scheduler Use Case
```
ID: UC-30
Nama: Auto Backup Database Harian
Aktor: Scheduler
Deskripsi: Sistem otomatis backup database setiap hari jam 02:00 dan 
upload ke cloud storage dengan retention 30 hari

[Submit dengan template prompt + tambahan scheduler di atas]
```

---

## 📞 SUPPORT & FEEDBACK

Jika ada pertanyaan atau butuh bantuan:
1. Review file `FULL_USE_CASE_SPECIFICATION.md` untuk referensi
2. Check file `USE_CASE_COVERAGE_ANALYSIS.md` untuk gap analysis
3. Konsultasi dengan team lead atau architect

---

**Document Version:** 1.0
**Last Updated:** 2026-06-01
**Maintained By:** Development Team
