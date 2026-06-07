# 📋 Panduan Testing Lengkap - Sistem Absensi Driver

## 🎯 Tujuan Testing

Dokumen ini berisi panduan lengkap untuk melakukan testing pada semua fitur dan tombol di aplikasi absensi driver. Testing mencakup:

1. **API Testing** - Endpoint mobile app (Flutter)
2. **Web Testing** - Admin panel & customer portal
3. **Unit Testing** - Business logic & services
4. **Security Testing** - Input validation & authorization

---

## 📦 Persiapan Testing

### 1. Install Dependencies

```bash
composer install
```

### 2. Setup Database Testing

Edit file `.env.testing`:

```env
DB_CONNECTION=sqlite
DB_DATABASE=:memory:
```

Atau gunakan database terpisah:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=absen_testing
DB_USERNAME=root
DB_PASSWORD=
```

### 3. Generate Application Key

```bash
php artisan key:generate --env=testing
```

---

## 🚀 Menjalankan Testing

### Jalankan Semua Test

```bash
php artisan test
```

### Jalankan Test Spesifik

```bash
# API Tests
php artisan test --testsuite=Feature --filter=Api

# Web Tests
php artisan test --testsuite=Feature --filter=Web

# Unit Tests
php artisan test --testsuite=Unit

# Security Tests
php artisan test --testsuite=Feature --filter=Security
```

### Jalankan Test dengan Coverage

```bash
php artisan test --coverage
```

### Jalankan Test Parallel (Lebih Cepat)

```bash
php artisan test --parallel
```

---

## 📱 API Testing (Mobile App)

### 1. Authentication Tests (`tests/Feature/Api/AuthenticationTest.php`)

**Fitur yang Ditest:**
- ✅ Login dengan kredensial valid
- ✅ Login dengan kredensial invalid
- ✅ Rate limiting pada login (10 req/min)
- ✅ Logout
- ✅ Change password
- ✅ SIM expiry warning
- ✅ SIM expired detection
- ✅ Single device login

**Cara Menjalankan:**
```bash
php artisan test --filter=AuthenticationTest
```

### 2. Attendance Tests (`tests/Feature/Api/AttendanceTest.php`)

**Fitur yang Ditest:**
- ✅ Check-in dengan foto
- ✅ Validasi tidak bisa check-in 2x
- ✅ Check-out dengan inspeksi
- ✅ Validasi tidak bisa check-out tanpa check-in
- ✅ Submit emergency report
- ✅ Validasi format GPS
- ✅ Validasi file gambar
- ✅ View attendance history
- ✅ Check driver status
- ✅ Reject future timestamp

**Cara Menjalankan:**
```bash
php artisan test --filter=AttendanceTest
```

### 3. Service Report Tests (`tests/Feature/Api/ServiceReportTest.php`)

**Fitur yang Ditest:**
- ✅ Submit service report
- ✅ Validasi harus on-duty
- ✅ Validasi required fields
- ✅ Validasi image files
- ✅ View service report history
- ✅ View service report detail

**Cara Menjalankan:**
```bash
php artisan test --filter=ServiceReportTest
```

### 4. Transport Cost Tests (`tests/Feature/Api/TransportCostTest.php`)

**Fitur yang Ditest:**
- ✅ Check can create trip entry
- ✅ Validasi harus sudah checkout
- ✅ Create trip entry
- ✅ Validasi tidak bisa duplicate
- ✅ View trip entries
- ✅ View trip entry detail
- ✅ Auto-calculate fuel efficiency
- ✅ Auto-calculate overtime

**Cara Menjalankan:**
```bash
php artisan test --filter=TransportCostTest
```

---

## 🖥️ Web Testing (Admin Panel)

### 1. Admin Auth Tests (`tests/Feature/Web/AdminAuthTest.php`)

**Fitur yang Ditest:**
- ✅ View login page
- ✅ Login dengan kredensial valid
- ✅ Login dengan kredensial invalid
- ✅ Rate limiting (5 req/min)
- ✅ Logout
- ✅ Guest redirect
- ✅ Role-based access (viewer, service_admin, master)

**Cara Menjalankan:**
```bash
php artisan test --filter=AdminAuthTest
```

### 2. Vehicle Management Tests (`tests/Feature/Web/VehicleManagementTest.php`)

**Fitur yang Ditest:**
- ✅ View vehicle list
- ✅ View add vehicle form
- ✅ Create vehicle
- ✅ View edit vehicle form
- ✅ Update vehicle
- ✅ Delete vehicle
- ✅ Delete rate limiting
- ✅ View service history
- ✅ Record service
- ✅ Search vehicles
- ✅ Unique plate number validation

**Cara Menjalankan:**
```bash
php artisan test --filter=VehicleManagementTest
```

### 3. Maintenance System Tests (`tests/Feature/Web/MaintenanceSystemTest.php`)

**Fitur yang Ditest:**
- ✅ View maintenance dashboard
- ✅ View component management
- ✅ Add vehicle component
- ✅ Update vehicle component
- ✅ Delete vehicle component
- ✅ View maintenance alerts
- ✅ Generate maintenance alerts
- ✅ Acknowledge alert
- ✅ Resolve alert
- ✅ View maintenance schedules
- ✅ Create maintenance schedule
- ✅ Complete maintenance schedule
- ✅ View maintenance calendar
- ✅ Get calendar events
- ✅ Auto-calculate next replacement
- ✅ Auto-update component status
- ✅ Overdue component detection

**Cara Menjalankan:**
```bash
php artisan test --filter=MaintenanceSystemTest
```

---

## 🧪 Unit Testing (Business Logic)

### 1. Vehicle Health Service Tests (`tests/Unit/VehicleHealthServiceTest.php`)

**Fitur yang Ditest:**
- ✅ Calculate health score (0-100)
- ✅ Healthy vehicle high score
- ✅ Critical component lowers score
- ✅ Overdue component very low score
- ✅ Bad daily checks lower score
- ✅ Correct health status labels
- ✅ Detailed health report
- ✅ No components default score
- ✅ Maintenance compliance affects score

**Cara Menjalankan:**
```bash
php artisan test --filter=VehicleHealthServiceTest
```

### 2. Maintenance Alert Service Tests (`tests/Unit/MaintenanceAlertServiceTest.php`)

**Fitur yang Ditest:**
- ✅ Generate warning alert
- ✅ Generate critical alert
- ✅ Generate overdue alert
- ✅ Date-based alerts
- ✅ No duplicate alerts
- ✅ Generate alerts for all vehicles
- ✅ Resolve component alerts
- ✅ Alert priority levels
- ✅ Active alerts summary
- ✅ No alert for healthy component

**Cara Menjalankan:**
```bash
php artisan test --filter=MaintenanceAlertServiceTest
```

---

## 🔒 Security Testing

### 1. Input Validation Tests (`tests/Feature/Security/InputValidationTest.php`)

**Fitur yang Ditest:**
- ✅ SQL injection prevention
- ✅ XSS prevention
- ✅ File mime type validation
- ✅ File size validation
- ✅ GPS coordinates validation
- ✅ Email format validation
- ✅ Numeric fields validation
- ✅ Date format validation
- ✅ Required fields enforcement
- ✅ Password strength enforcement
- ✅ Path traversal prevention

**Cara Menjalankan:**
```bash
php artisan test --filter=InputValidationTest
```

### 2. Authorization Tests (`tests/Feature/Security/AuthorizationTest.php`)

**Fitur yang Ditest:**
- ✅ Driver cannot access admin routes
- ✅ Viewer cannot modify data
- ✅ Customer can only access own vehicles
- ✅ Driver can only view own attendance
- ✅ Unauthenticated user redirect
- ✅ Service admin permissions
- ✅ Master full access
- ✅ Role field guarded
- ✅ Customer cannot access admin panel
- ✅ Driver cannot view other drivers' data

**Cara Menjalankan:**
```bash
php artisan test --filter=AuthorizationTest
```

---

## 📊 Test Coverage Report

### Generate HTML Coverage Report

```bash
php artisan test --coverage-html coverage-report
```

Buka file `coverage-report/index.html` di browser.

### Target Coverage

- **Overall**: > 80%
- **Controllers**: > 75%
- **Services**: > 90%
- **Models**: > 70%

---

## 🐛 Debugging Tests

### Run Test dengan Verbose Output

```bash
php artisan test --filter=AttendanceTest -vvv
```

### Stop on First Failure

```bash
php artisan test --stop-on-failure
```

### Run Specific Test Method

```bash
php artisan test --filter=driver_can_check_in
```

---

## 📝 Checklist Testing Manual

### Mobile App (Flutter)

#### Authentication
- [ ] Login dengan ID & password benar
- [ ] Login dengan ID & password salah
- [ ] Logout
- [ ] Change password
- [ ] SIM expiry warning muncul

#### Attendance
- [ ] Check-in dengan foto selfie
- [ ] Check-in dengan foto speedometer
- [ ] Check-in dengan foto kondisi kendaraan
- [ ] Check-out dengan inspeksi (ban, lampu, rem)
- [ ] View attendance history
- [ ] Emergency report

#### Service Report
- [ ] Submit service report dengan foto
- [ ] View service report history
- [ ] View service report detail

#### Transport Cost
- [ ] Check can create trip entry
- [ ] Create trip entry dengan receipt
- [ ] View trip entries
- [ ] View trip entry detail

### Admin Panel

#### Dashboard
- [ ] View dashboard statistics
- [ ] View recent activities
- [ ] View alerts

#### Vehicle Management
- [ ] View vehicle list
- [ ] Add new vehicle
- [ ] Edit vehicle
- [ ] Delete vehicle
- [ ] Search vehicle
- [ ] View service history
- [ ] Record service

#### Maintenance System
- [ ] View maintenance dashboard
- [ ] Add vehicle component
- [ ] Edit vehicle component
- [ ] Delete vehicle component
- [ ] View maintenance alerts
- [ ] Generate alerts
- [ ] Acknowledge alert
- [ ] Resolve alert
- [ ] View maintenance schedules
- [ ] Create schedule
- [ ] Complete schedule
- [ ] View maintenance calendar

#### Reports
- [ ] View driver history
- [ ] Export driver history
- [ ] View vehicle history
- [ ] View emergency reports
- [ ] View daily recap
- [ ] View monthly recap
- [ ] Export monthly recap

#### Master Data
- [ ] Manage drivers
- [ ] Manage users
- [ ] Manage projects
- [ ] Manage customers

#### Service Reports
- [ ] View service report list
- [ ] Approve service report
- [ ] Reject service report
- [ ] Export for finance

#### Transport Costs
- [ ] View transport cost dashboard
- [ ] View trip entries
- [ ] Approve trip entry
- [ ] Reject trip entry
- [ ] View monthly recap
- [ ] Submit to finance
- [ ] Export finance document

### Customer Portal

- [ ] View dashboard
- [ ] View vehicle list
- [ ] View vehicle detail
- [ ] View vehicle certificate
- [ ] View approval list
- [ ] Download approval document
- [ ] Upload signed document
- [ ] View profile
- [ ] Change password

---

## 🎯 Test Execution Plan

### Phase 1: Unit Tests (30 menit)
```bash
php artisan test --testsuite=Unit
```

### Phase 2: API Tests (45 menit)
```bash
php artisan test --testsuite=Feature --filter=Api
```

### Phase 3: Web Tests (60 menit)
```bash
php artisan test --testsuite=Feature --filter=Web
```

### Phase 4: Security Tests (30 menit)
```bash
php artisan test --testsuite=Feature --filter=Security
```

### Phase 5: Manual Testing (120 menit)
- Test semua tombol di UI
- Test semua form submission
- Test semua file upload
- Test semua export/download

---

## 📈 Continuous Integration

### GitHub Actions Example

```yaml
name: Tests

on: [push, pull_request]

jobs:
  test:
    runs-on: ubuntu-latest
    steps:
      - uses: actions/checkout@v2
      - name: Setup PHP
        uses: shivammathur/setup-php@v2
        with:
          php-version: '8.2'
      - name: Install Dependencies
        run: composer install
      - name: Run Tests
        run: php artisan test --coverage
```

---

## 🔧 Troubleshooting

### Error: "Database not found"
```bash
php artisan config:clear
php artisan migrate:fresh --env=testing
```

### Error: "Class not found"
```bash
composer dump-autoload
```

### Error: "Storage directory not writable"
```bash
chmod -R 775 storage
chmod -R 775 bootstrap/cache
```

---

## 📞 Support

Jika menemukan bug atau issue saat testing:
1. Catat error message lengkap
2. Catat steps to reproduce
3. Screenshot jika perlu
4. Buat issue report

---

**Last Updated:** 2024-01-15
**Version:** 1.0.0
