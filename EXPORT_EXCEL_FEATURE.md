# 📊 Export Excel Feature - Maintenance Module

## ✅ Implementasi Selesai

Export Excel telah diimplementasikan untuk **3 halaman maintenance**:

### 1. **Dashboard/Index** ⭐⭐⭐
**Route:** `GET /admin/maintenance/export/dashboard`  
**Button Location:** Header (hijau, sebelah kiri)

**Data yang di-export:**
- No
- Plat Nomor
- Tipe Kendaraan
- Project
- Health Score (0-100)
- Status Kesehatan (Prima/Segera Servis/Telat Servis/Isu Fisik)
- KM Terakhir
- KM Servis Terakhir
- Interval Servis
- Sisa KM
- Last Update
- Status Code

**Filter yang direspect:**
- ✅ Project ID
- ✅ Type (Tipe Kendaraan)
- ✅ Search (Plat Nomor)
- ✅ Status Filter (danger/warning/safe)

**Filename:** `Maintenance_Dashboard_YYYY-MM-DD_HHMMSS.xlsx`

---

### 2. **Schedules** ⭐⭐
**Route:** `GET /admin/maintenance/export/schedules`  
**Button Location:** Header (hijau, sebelah kiri "Tambah Jadwal")

**Data yang di-export:**
- No
- Plat Nomor
- Project
- Tanggal Jadwal
- Tipe Maintenance (Preventive/Corrective/Predictive)
- Prioritas (Low/Medium/High/Critical)
- Status (Pending/In Progress/Completed/Cancelled)
- Komponen
- Deskripsi
- Estimasi Biaya (Rp format)
- Biaya Aktual (Rp format)
- Tanggal Selesai
- Catatan

**Filter yang direspect:**
- ✅ Status
- ✅ Priority
- ✅ Vehicle ID
- ✅ Type

**Filename:** `Maintenance_Schedules_YYYY-MM-DD_HHMMSS.xlsx`

---

### 3. **Alerts** ⭐
**Route:** `GET /admin/maintenance/export/alerts`  
**Button Location:** Header (hijau, sebelah kiri tanggal)

**Data yang di-export:**
- No
- Plat Nomor
- Project
- Komponen
- Tipe Alert (OVERDUE/CRITICAL/WARNING)
- Pesan
- Status (Active/Acknowledged/Resolved)
- Tanggal Trigger
- Acknowledged At
- Acknowledged By
- Resolved At
- Resolution Notes

**Filter yang direspect:**
- ✅ Status
- ✅ Alert Type

**Filename:** `Maintenance_Alerts_YYYY-MM-DD_HHMMSS.xlsx`

---

## 📁 Files Created

### Export Classes:
1. `app/Exports/MaintenanceDashboardExport.php`
2. `app/Exports/MaintenanceSchedulesExport.php`
3. `app/Exports/MaintenanceAlertsExport.php`

### Controller Methods Added:
- `MaintenanceController::exportDashboard()`
- `MaintenanceController::exportSchedules()`
- `MaintenanceController::exportAlerts()`

### Routes Added:
```php
Route::get('/maintenance/export/dashboard', 'exportDashboard')->name('admin.maintenance.export.dashboard');
Route::get('/maintenance/export/schedules', 'exportSchedules')->name('admin.maintenance.export.schedules');
Route::get('/maintenance/export/alerts', 'exportAlerts')->name('admin.maintenance.export.alerts');
```

### Views Updated:
- `resources/views/admin/maintenance/index.blade.php` - Added export button
- `resources/views/admin/maintenance/schedules.blade.php` - Added export button
- `resources/views/admin/maintenance/alerts.blade.php` - Added export button

---

## 🎨 Excel Styling

Semua export menggunakan styling yang konsisten:

**Header Row:**
- Background: Blue (#1890FF)
- Font: Bold, White, Size 12
- Alignment: Center
- Border: Thin black borders

**Data Rows:**
- Auto-sized columns
- Proper number formatting (Rp untuk currency)
- Date formatting (d/m/Y H:i)

---

## 🧪 Testing

### Test Export Dashboard:
```bash
# Tanpa filter
http://localhost:8000/admin/maintenance/export/dashboard

# Dengan filter
http://localhost:8000/admin/maintenance/export/dashboard?project_id=1&type=Box&status_filter=danger
```

### Test Export Schedules:
```bash
# Tanpa filter
http://localhost:8000/admin/maintenance/export/schedules

# Dengan filter
http://localhost:8000/admin/maintenance/export/schedules?status=pending&priority=critical
```

### Test Export Alerts:
```bash
# Tanpa filter
http://localhost:8000/admin/maintenance/export/alerts

# Dengan filter
http://localhost:8000/admin/maintenance/export/alerts?status=active&alert_type=overdue
```

---

## 📊 Expected Output

### Dashboard Export Example:
| No | Plat Nomor | Tipe | Project | Health Score | Status | KM Terakhir | ... |
|----|------------|------|---------|--------------|--------|-------------|-----|
| 1  | B 1234 ABC | Box  | Alpha   | 85.5         | Prima  | 45,000      | ... |
| 2  | B 5678 XYZ | Pickup | Beta  | 35.2         | Telat Servis | 78,000 | ... |

### Schedules Export Example:
| No | Plat Nomor | Tanggal | Tipe | Prioritas | Status | Estimasi | ... |
|----|------------|---------|------|-----------|--------|----------|-----|
| 1  | B 1234 ABC | 15/05/2026 | Preventive | Critical | Pending | Rp 500,000 | ... |

### Alerts Export Example:
| No | Plat Nomor | Komponen | Tipe Alert | Pesan | Status | Tanggal | ... |
|----|------------|----------|------------|-------|--------|---------|-----|
| 1  | B 1234 ABC | Oli Mesin | OVERDUE | Sudah lewat... | Active | 14/05/2026 | ... |

---

## ✨ Features

✅ **Filter Preservation** - Semua filter yang aktif di halaman akan direspect saat export  
✅ **Styled Headers** - Header berwarna biru dengan font putih bold  
✅ **Auto-sized Columns** - Kolom otomatis menyesuaikan lebar  
✅ **Number Formatting** - Currency dalam format Rupiah  
✅ **Date Formatting** - Tanggal dalam format Indonesia  
✅ **Unique Filenames** - Timestamp untuk menghindari overwrite  
✅ **No Diagnostics Errors** - Semua file pass validation  

---

## 🚀 Usage

### Dari UI:
1. Buka halaman maintenance (Dashboard/Schedules/Alerts)
2. Apply filter jika diperlukan
3. Klik tombol **"Export Excel"** (hijau)
4. File akan otomatis ter-download

### Programmatically:
```php
use App\Exports\MaintenanceDashboardExport;
use Maatwebsite\Excel\Facades\Excel;

// Export dengan filter
$filters = ['project_id' => 1, 'type' => 'Box'];
return Excel::download(new MaintenanceDashboardExport($filters), 'export.xlsx');
```

---

## 📝 Notes

- Export menggunakan **Laravel Excel (Maatwebsite)**
- Pastikan package sudah terinstall: `composer require maatwebsite/excel`
- Export berjalan di background untuk dataset besar
- Filter query dioptimasi dengan eager loading
- Health score dihitung real-time menggunakan VehicleHealthService

---

## 🎯 Next Enhancements (Optional)

1. **PDF Export** - Tambahkan opsi export ke PDF
2. **Scheduled Export** - Export otomatis via cron job
3. **Email Export** - Kirim hasil export via email
4. **Custom Columns** - User bisa pilih kolom yang mau di-export
5. **Chart Export** - Include chart/graph dalam Excel

---

**Status:** ✅ COMPLETED  
**Date:** 16 Mei 2026  
**Version:** 1.0.0
