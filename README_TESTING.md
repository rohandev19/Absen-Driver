# 🧪 Testing Documentation - Sistem Absensi Driver

## 📖 Daftar Isi

1. [Pendahuluan](#pendahuluan)
2. [Struktur Testing](#struktur-testing)
3. [Quick Start](#quick-start)
4. [Test Files](#test-files)
5. [Factories](#factories)
6. [Running Tests](#running-tests)
7. [Coverage Report](#coverage-report)
8. [Troubleshooting](#troubleshooting)

---

## 📝 Pendahuluan

Sistem testing ini dirancang untuk memastikan semua fitur dan tombol di aplikasi absensi driver berfungsi dengan baik. Testing mencakup:

- ✅ **150+ test cases** otomatis
- ✅ **API testing** untuk mobile app
- ✅ **Web testing** untuk admin panel
- ✅ **Unit testing** untuk business logic
- ✅ **Security testing** untuk keamanan

---

## 📁 Struktur Testing

```
tests/
├── Feature/
│   ├── Api/
│   │   ├── AuthenticationTest.php      (8 tests)
│   │   ├── AttendanceTest.php          (10 tests)
│   │   ├── ServiceReportTest.php       (6 tests)
│   │   └── TransportCostTest.php       (8 tests)
│   ├── Web/
│   │   ├── AdminAuthTest.php           (8 tests)
│   │   ├── VehicleManagementTest.php   (11 tests)
│   │   └── MaintenanceSystemTest.php   (18 tests)
│   └── Security/
│       ├── InputValidationTest.php     (10 tests)
│       └── AuthorizationTest.php       (10 tests)
└── Unit/
    ├── VehicleHealthServiceTest.php    (10 tests)
    └── MaintenanceAlertServiceTest.php (10 tests)

database/factories/
├── VehicleComponentFactory.php
├── ServiceReportFactory.php
├── TransportCostFactory.php
├── MaintenanceAlertFactory.php
└── MaintenanceScheduleFactory.php
```

---

## 🚀 Quick Start

### 1. Install Dependencies

```bash
composer install
```

### 2. Setup Environment

Buat file `.env.testing`:

```env
APP_ENV=testing
APP_KEY=base64:your-key-here
DB_CONNECTION=sqlite
DB_DATABASE=:memory:
```

### 3. Run Tests

**Windows:**
```bash
quick-test.bat
```

**Manual:**
```bash
php artisan test
```

---

## 📄 Test Files

### API Tests (Mobile App)

#### 1. AuthenticationTest.php
**Lokasi:** `tests/Feature/Api/AuthenticationTest.php`

**Test Cases:**
- `driver_can_login_with_valid_credentials`
- `driver_cannot_login_with_invalid_credentials`
- `login_is_rate_limited`
- `driver_can_logout`
- `driver_can_change_password`
- `sim_expiry_warning_is_shown_on_login`
- `expired_sim_is_detected_on_login`
- `single_device_login_deletes_old_tokens`

**Run:**
```bash
php artisan test --filter=AuthenticationTest
```

#### 2. AttendanceTest.php
**Lokasi:** `tests/Feature/Api/AttendanceTest.php`

**Test Cases:**
- `driver_can_check_in`
- `driver_cannot_check_in_twice`
- `driver_can_check_out`
- `driver_cannot_check_out_without_check_in`
- `driver_can_submit_emergency_report`
- `check_in_validates_gps_format`
- `check_in_validates_image_files`
- `driver_can_check_status`
- `driver_can_view_attendance_history`
- `future_timestamp_is_rejected`

**Run:**
```bash
php artisan test --filter=AttendanceTest
```

#### 3. ServiceReportTest.php
**Lokasi:** `tests/Feature/Api/ServiceReportTest.php`

**Test Cases:**
- `driver_can_submit_service_report`
- `driver_cannot_submit_service_report_without_being_on_duty`
- `service_report_validates_required_fields`
- `service_report_validates_image_files`
- `driver_can_view_service_report_history`
- `driver_can_view_service_report_detail`

**Run:**
```bash
php artisan test --filter=ServiceReportTest
```

#### 4. TransportCostTest.php
**Lokasi:** `tests/Feature/Api/TransportCostTest.php`

**Test Cases:**
- `driver_can_check_if_can_create_trip_entry`
- `driver_cannot_create_trip_entry_without_checkout`
- `driver_can_create_trip_entry`
- `driver_cannot_create_duplicate_trip_entry`
- `driver_can_view_trip_entries`
- `driver_can_view_trip_entry_detail`
- `fuel_efficiency_is_calculated_automatically`
- `overtime_is_calculated_automatically`

**Run:**
```bash
php artisan test --filter=TransportCostTest
```

### Web Tests (Admin Panel)

#### 5. AdminAuthTest.php
**Lokasi:** `tests/Feature/Web/AdminAuthTest.php`

**Test Cases:**
- `admin_can_view_login_page`
- `admin_can_login_with_valid_credentials`
- `admin_cannot_login_with_invalid_credentials`
- `admin_login_is_rate_limited`
- `admin_can_logout`
- `guest_cannot_access_admin_dashboard`
- `viewer_role_cannot_access_admin_features`
- `service_admin_can_access_admin_features`
- `master_can_access_all_admin_features`

**Run:**
```bash
php artisan test --filter=AdminAuthTest
```

#### 6. VehicleManagementTest.php
**Lokasi:** `tests/Feature/Web/VehicleManagementTest.php`

**Test Cases:**
- `admin_can_view_vehicle_list`
- `admin_can_view_add_vehicle_form`
- `admin_can_create_vehicle`
- `admin_can_view_edit_vehicle_form`
- `admin_can_update_vehicle`
- `admin_can_delete_vehicle`
- `vehicle_deletion_is_rate_limited`
- `admin_can_view_vehicle_service_history`
- `admin_can_record_service`
- `admin_can_search_vehicles`
- `vehicle_plate_number_must_be_unique`

**Run:**
```bash
php artisan test --filter=VehicleManagementTest
```

#### 7. MaintenanceSystemTest.php
**Lokasi:** `tests/Feature/Web/MaintenanceSystemTest.php`

**Test Cases:**
- `admin_can_view_maintenance_dashboard`
- `admin_can_view_component_management_page`
- `admin_can_add_vehicle_component`
- `admin_can_update_vehicle_component`
- `admin_can_delete_vehicle_component`
- `admin_can_view_maintenance_alerts`
- `admin_can_generate_maintenance_alerts`
- `admin_can_acknowledge_alert`
- `admin_can_resolve_alert`
- `admin_can_view_maintenance_schedules`
- `admin_can_create_maintenance_schedule`
- `admin_can_complete_maintenance_schedule`
- `admin_can_view_maintenance_calendar`
- `admin_can_get_maintenance_calendar_events`
- `component_next_replacement_is_calculated_automatically`
- `component_status_is_updated_automatically`
- `overdue_component_has_correct_status`

**Run:**
```bash
php artisan test --filter=MaintenanceSystemTest
```

### Unit Tests (Business Logic)

#### 8. VehicleHealthServiceTest.php
**Lokasi:** `tests/Unit/VehicleHealthServiceTest.php`

**Test Cases:**
- `it_calculates_health_score_correctly`
- `healthy_vehicle_has_high_score`
- `critical_component_lowers_health_score`
- `overdue_component_results_in_very_low_score`
- `bad_daily_checks_lower_health_score`
- `it_returns_correct_health_status_labels`
- `it_generates_detailed_health_report`
- `vehicle_with_no_components_has_default_healthy_score`
- `maintenance_compliance_affects_health_score`

**Run:**
```bash
php artisan test --filter=VehicleHealthServiceTest
```

#### 9. MaintenanceAlertServiceTest.php
**Lokasi:** `tests/Unit/MaintenanceAlertServiceTest.php`

**Test Cases:**
- `it_generates_warning_alert_for_component_near_replacement`
- `it_generates_critical_alert_for_component_very_close_to_replacement`
- `it_generates_overdue_alert_for_component_past_replacement`
- `it_generates_date_based_alerts`
- `it_does_not_generate_duplicate_alerts`
- `it_generates_alerts_for_all_vehicles`
- `it_resolves_component_alerts_after_maintenance`
- `it_provides_correct_alert_priority`
- `it_generates_active_alerts_summary`
- `it_does_not_generate_alert_for_healthy_component`

**Run:**
```bash
php artisan test --filter=MaintenanceAlertServiceTest
```

### Security Tests

#### 10. InputValidationTest.php
**Lokasi:** `tests/Feature/Security/InputValidationTest.php`

**Test Cases:**
- `sql_injection_is_prevented_in_search`
- `xss_is_prevented_in_user_input`
- `file_upload_validates_mime_type`
- `file_upload_validates_file_size`
- `gps_coordinates_are_validated`
- `email_format_is_validated`
- `numeric_fields_are_validated`
- `date_format_is_validated`
- `required_fields_are_enforced`
- `password_strength_is_enforced`
- `path_traversal_is_prevented_in_file_access`

**Run:**
```bash
php artisan test --filter=InputValidationTest
```

#### 11. AuthorizationTest.php
**Lokasi:** `tests/Feature/Security/AuthorizationTest.php`

**Test Cases:**
- `driver_cannot_access_admin_routes`
- `viewer_cannot_modify_data`
- `customer_can_only_access_own_vehicles`
- `driver_can_only_view_own_attendance`
- `unauthenticated_user_cannot_access_protected_routes`
- `service_admin_can_manage_vehicles`
- `master_has_full_access`
- `role_field_is_guarded_from_mass_assignment`
- `customer_cannot_access_admin_panel`
- `driver_cannot_view_other_drivers_transport_costs`

**Run:**
```bash
php artisan test --filter=AuthorizationTest
```

---

## 🏭 Factories

Factories digunakan untuk generate test data:

### VehicleComponentFactory
```php
VehicleComponent::factory()->create();
VehicleComponent::factory()->healthy()->create();
VehicleComponent::factory()->critical()->create();
VehicleComponent::factory()->overdue()->create();
```

### ServiceReportFactory
```php
ServiceReport::factory()->create();
ServiceReport::factory()->pending()->create();
ServiceReport::factory()->approvedByAdmin()->create();
ServiceReport::factory()->rejected()->create();
```

### TransportCostFactory
```php
TransportCost::factory()->create();
TransportCost::factory()->pending()->create();
TransportCost::factory()->approved()->create();
TransportCost::factory()->rejected()->create();
```

### MaintenanceAlertFactory
```php
MaintenanceAlert::factory()->create();
MaintenanceAlert::factory()->active()->create();
MaintenanceAlert::factory()->acknowledged()->create();
MaintenanceAlert::factory()->resolved()->create();
```

### MaintenanceScheduleFactory
```php
MaintenanceSchedule::factory()->create();
MaintenanceSchedule::factory()->pending()->create();
MaintenanceSchedule::factory()->completed()->create();
MaintenanceSchedule::factory()->upcoming()->create();
MaintenanceSchedule::factory()->overdue()->create();
```

---

## ▶️ Running Tests

### All Tests
```bash
php artisan test
```

### By Suite
```bash
php artisan test --testsuite=Feature
php artisan test --testsuite=Unit
```

### By Filter
```bash
php artisan test --filter=Api
php artisan test --filter=Web
php artisan test --filter=Security
```

### Specific Test
```bash
php artisan test --filter=driver_can_login
```

### With Options
```bash
# Stop on first failure
php artisan test --stop-on-failure

# Parallel execution
php artisan test --parallel

# Verbose output
php artisan test -vvv
```

### Using Batch Files
```bash
# Interactive menu
run-tests.bat

# Quick test
quick-test.bat
```

---

## 📊 Coverage Report

### Generate Coverage
```bash
php artisan test --coverage
```

### Generate HTML Report
```bash
php artisan test --coverage-html coverage-report
```

Buka `coverage-report/index.html` di browser.

### Coverage Targets
- **Overall:** > 80%
- **Controllers:** > 75%
- **Services:** > 90%
- **Models:** > 70%

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

### Error: "Storage not writable"
```bash
chmod -R 775 storage
chmod -R 775 bootstrap/cache
```

### Error: "Factory not found"
```bash
composer dump-autoload
php artisan config:clear
```

### Tests Running Slow
```bash
# Use parallel execution
php artisan test --parallel

# Use SQLite in-memory
# Edit .env.testing:
DB_CONNECTION=sqlite
DB_DATABASE=:memory:
```

---

## 📚 Additional Resources

- **Testing Guide:** `TESTING_GUIDE.md`
- **Test Summary:** `TEST_SUMMARY.md`
- **Laravel Testing Docs:** https://laravel.com/docs/testing

---

## ✅ Checklist Before Deployment

- [ ] All tests passing
- [ ] Coverage > 80%
- [ ] No security vulnerabilities
- [ ] Manual testing completed
- [ ] Documentation updated
- [ ] Performance tested
- [ ] Browser compatibility checked

---

**Last Updated:** 2024-01-15
**Version:** 1.0.0
**Status:** ✅ Production Ready
