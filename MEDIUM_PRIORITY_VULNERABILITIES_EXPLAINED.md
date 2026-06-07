# Penjelasan Detail: Vulnerability Medium Priority (CORRECTED)

> **KOREKSI PENTING**: Setelah review ulang, Transport Cost routes **SUDAH AMAN** karena dibatasi hanya untuk `master` dan `service_admin`. Vulnerability yang sebenarnya hanya ada di **Maintenance routes** untuk cross-project access.

---

## ✅ KOREKSI: Transport Cost Routes SUDAH AMAN

### Route Definition Aktual

```php
// routes/web.php Line 51
Route::middleware(['auth', 'role:master,service_admin', 'throttle:60,1'])
    ->prefix('admin')->group(function () {
    
    // Line 200: Transport Cost routes
    Route::prefix('transport-costs')
        ->controller(TransportCostAdminController::class)
        ->group(function () {
            Route::post('/{id}/approve', 'approve');
            Route::post('/{id}/reject', 'reject');
            // ...
        });
});
```

**Middleware yang aktif:**
- ✅ `auth` — Wajib login
- ✅ `role:master,service_admin` — **Hanya master & service_admin**
- ✅ `throttle:60,1` — Rate limiting

### Kesimpulan Transport Cost

**STATUS: ✅ AMAN dari perspektif role-based access**

Memang benar bahwa:
1. ✅ Hanya `master` dan `service_admin` yang bisa akses
2. ✅ Customer **TIDAK BISA** akses transport cost approval
3. ✅ Driver **TIDAK BISA** akses transport cost approval

**Namun tetap ada 1 pertimbangan:**

### 🟡 Optional Enhancement: Multi-Project Service Admin

**Jika di masa depan** sistem ini berkembang menjadi:
```
Service Admin A → Handle Project 1 & Project 2
Service Admin B → Handle Project 3 & Project 4
```

Maka baru perlu tambahan **project-level authorization**. Saat ini, jika semua service_admin bisa akses semua project, maka **tidak ada bug**.

**Current Assumption (AMAN):**
- Master → Full access semua project ✅
- Service Admin → Full access semua project ✅
- Tidak ada segregation antar service admin per project

**Future Risk (jika requirement berubah):**
- Jika ada service admin yang hanya boleh handle project tertentu
- Maka perlu tambahan check: `hasAccessToProject($trip->project_id)`

---

## 🟡 VULNERABILITY YANG SEBENARNYA: Cross-Project Authorization di Maintenance Routes

### Problem Statement

### Problem Statement

**Middleware saat ini:**
```php
// routes/web.php Line 51
Route::middleware(['auth', 'role:master,service_admin', 'throttle:60,1'])
```

**Yang dicek:**
- ✅ User harus login
- ✅ User harus role `master` atau `service_admin`

**Yang TIDAK dicek:**
- ❌ Apakah service_admin ini punya akses ke **project** kendaraan yang dia edit?
- ❌ Apakah service_admin boleh pindahkan kendaraan antar project?

---

### Skenario: Jika Ada Service Admin Per-Project (Future Risk)

**Asumsi business requirement di masa depan:**
```
Company punya 2 customer besar:
- Customer A (Project Jakarta) → Service Admin Budi
- Customer B (Project Surabaya) → Service Admin Siti

Budi seharusnya HANYA bisa manage kendaraan Project Jakarta.
Siti seharusnya HANYA bisa manage kendaraan Project Surabaya.
```

**Tapi saat ini:**
- Budi bisa edit/hapus kendaraan Project Surabaya ❌
- Siti bisa edit/hapus kendaraan Project Jakarta ❌
- Keduanya punya role `service_admin` yang sama

---

### Skenario Attack (Jika Multi-Project Service Admin Diimplementasikan)

#### Kode Saat Ini (VULNERABLE)

**File:** `app/Http/Controllers/TransportCostAdminController.php`

```php
public function approve($id)
{
    try {
        // ❌ TIDAK ADA CEK: Apakah $trip ini milik project yang boleh saya akses?
        $trip = TransportCost::findOrFail($id);
        $this->transportCostService->approve($trip, Auth::id());

        return redirect()->back()->with('success', 'Trip entry berhasil disetujui');
    } catch (\Exception $e) {
        return redirect()->back()->with('error', $e->getMessage());
    }
}
```

**Yang terjadi:**
- Controller hanya cek: "Apakah transport cost dengan ID ini exist?"
- **TIDAK CEK:** "Apakah transport cost ini milik project yang admin ini tangani?"

---

### Skenario Attack Konkret

#### Setup Awal

**Database State:**

```sql
-- Table: users
| id | name          | role          | project_id (kalau ada) |
|----|---------------|---------------|------------------------|
| 1  | Admin Master  | master        | NULL (akses semua)     |
| 2  | Admin Jakarta | service_admin | 1 (Project Jakarta)    |
| 3  | Admin Surabaya| service_admin | 2 (Project Surabaya)   |

-- Table: projects
| id | name               | customer_id |
|----|--------------------|-------------|
| 1  | Logistik Jakarta   | 100         |
| 2  | Distribusi Surabaya| 200         |

-- Table: transport_costs
| id | driver_id | project_id | trip_date  | gasoline_cost | approval_status |
|----|-----------|------------|------------|---------------|-----------------|
| 50 | 5         | 1          | 2026-06-01 | 500,000       | pending         |
| 51 | 10        | 2          | 2026-06-02 | 750,000       | pending         |
```

---

#### Attack Step-by-Step

**1. Admin Jakarta (service_admin untuk Project 1) login:**
```http
POST /admin/login
{
  "email": "admin.jakarta@company.com",
  "password": "password123"
}
```

**2. Admin Jakarta buka halaman Transport Cost:**
```http
GET /admin/transport-costs
```

Response menampilkan list transport cost **hanya dari Project 1** (Jakarta):
```
- ID 50: Driver 5, Project Jakarta, Rp 500.000 [Pending]
```

**3. Admin Jakarta TIDAK BISA lihat transport cost Project 2 di UI.**

**4. Tapi... Admin Jakarta bisa langsung akses URL approval ID 51 (milik Project 2):**

```http
POST /admin/transport-costs/51/approve
CSRF-Token: xxx
Cookie: laravel_session=yyy
```

**5. Response:**
```json
{
  "success": "Trip entry berhasil disetujui"
}
```

✅ **BERHASIL!** Admin Jakarta baru saja meng-approve transport cost **Project Surabaya** (ID 51) yang seharusnya tidak boleh dia akses!

---

### Business Impact

#### 1. **Fraud Risk**
- Admin nakal dari Project A bisa approve transport cost **fiktif** di Project B
- Customer Project B bayar uang jalan yang tidak valid
- Tidak ada segregation of duty

#### 2. **Compliance Risk**
- Audit trail rusak: "Siapa yang approve ini?" → Admin dari project lain!
- Melanggar prinsip least privilege
- Tidak bisa trace accountability per project

#### 3. **Financial Loss**
Contoh skenario nyata:
```
Driver X (Project Surabaya) mengajukan uang jalan Rp 10 juta (inflated/fiktif).
Admin Surabaya menolak karena tidak wajar.

Tapi Driver X berkolusi dengan Admin Jakarta (service_admin dari project lain).
Admin Jakarta approve langsung via direct URL → Uang cair.
```

#### 4. **Multi-Customer Environment**
Jika 1 service_admin menangani beberapa customer:
```
Service Admin A → Project 1 (Customer X) + Project 3 (Customer Y)
Service Admin B → Project 2 (Customer Z)

Admin B bisa approve transport cost Project 1 & 3 (Customer X dan Y)
tanpa authorization → Data breach across customers!
```

---

### Attack Vector Lainnya

**Vulnerability yang sama ada di:**

1. **Reject Transport Cost**
   ```http
   POST /admin/transport-costs/51/reject
   { "rejection_reason": "Tidak valid" }
   ```
   → Admin Jakarta bisa reject transport cost Project Surabaya

2. **Submit to Finance**
   ```http
   POST /admin/transport-costs/51/submit-to-finance
   ```
   → Admin Jakarta bisa submit ke finance untuk Project Surabaya

3. **Export Finance Document**
   ```http
   GET /admin/transport-costs/51/export-finance
   ```
   → Admin Jakarta bisa download dokumen keuangan Project Surabaya

4. **Bulk Submit to Finance**
   ```http
   POST /admin/transport-costs/bulk-submit-to-finance
   { "ids": [51, 52, 53] }  // Semua ID dari Project Surabaya
   ```
   → Admin Jakarta bisa bulk submit transport cost project lain!

---

### Fix yang Diperlukan

#### Option 1: Authorization Check per Action (RECOMMENDED)

```php
public function approve($id)
{
    try {
        $trip = TransportCost::findOrFail($id);
        
        // ✅ TAMBAHKAN CEK INI
        if (Auth::user()->role === 'service_admin') {
            // Cek apakah admin ini punya akses ke project ini
            if (!Auth::user()->hasAccessToProject($trip->project_id)) {
                \Log::warning('Unauthorized transport cost approval attempt', [
                    'admin_id' => Auth::id(),
                    'admin_name' => Auth::user()->name,
                    'trip_id' => $trip->id,
                    'trip_project_id' => $trip->project_id,
                    'ip' => request()->ip(),
                ]);
                
                abort(403, 'Anda tidak memiliki akses ke project ini.');
            }
        }
        // Master tetap bisa approve semua
        
        $this->transportCostService->approve($trip, Auth::id());
        return redirect()->back()->with('success', 'Trip entry berhasil disetujui');
    } catch (\Exception $e) {
        return redirect()->back()->with('error', $e->getMessage());
    }
}
```

#### Option 2: Global Scope di Model (Alternatif)

```php
// app/Models/TransportCost.php

protected static function booted()
{
    static::addGlobalScope('project_access', function (Builder $builder) {
        $user = Auth::user();
        
        if ($user && $user->role === 'service_admin') {
            // Restrict ke project yang dia handle
            $builder->whereIn('project_id', $user->accessibleProjectIds());
        }
        // Master tidak terkena scope ini
    });
}
```

---

## 🟡 VULNERABILITY #2: Tidak Ada Cross-Project Authorization di Maintenance Routes

### Konsep Vulnerability

Vulnerability ini **serupa** dengan #1, tapi terjadi di **Vehicle Management & Maintenance**:

```
Project Jakarta → Vehicle A, B, C → Components, Alerts, Schedules
Project Surabaya → Vehicle D, E, F → Components, Alerts, Schedules
```

**Yang terjadi saat ini:**
- Middleware hanya cek: `role:master,service_admin` ✅
- **TIDAK CEK:** "Apakah kendaraan ini milik project yang admin ini tangani?" ❌

---

### Skenario Attack Konkret

#### Setup Database

```sql
-- Table: vehicles
| id | plate_number | type  | project_id | current_km | status |
|----|--------------|-------|------------|------------|--------|
| 10 | B 1234 XYZ   | Truck | 1          | 45000      | active |
| 20 | L 5678 ABC   | Van   | 2          | 30000      | active |

-- Table: vehicle_components
| id | vehicle_id | component_name | status   | next_replacement_km |
|----|------------|----------------|----------|---------------------|
| 1  | 10         | Brake Pad      | healthy  | 50000               |
| 2  | 20         | Oil Filter     | warning  | 32000               |
```

---

#### Attack 1: Update Vehicle Data Cross-Project

**Admin Jakarta login → buka vehicle edit:**
```http
GET /admin/aset/10/edit
```
Response: Form edit vehicle B 1234 XYZ (Project Jakarta) ✅

**Tapi... Admin Jakarta bisa langsung submit form untuk vehicle Project Surabaya:**

```http
PUT /admin/aset/20/update
{
  "type": "Truck",
  "project_id": 1,  // ❌ UBAH PROJECT DARI 2 KE 1!
  "current_km": 30000,
  "pajak_stnk_berlaku_sampai": "2027-01-01"
}
```

**Kode saat ini (VULNERABLE):**

```php
// app/Http/Controllers/MaintenanceController.php

public function update(Request $request, $id)
{
    $vehicle = Vehicle::findOrFail($id);  // ❌ Tidak cek project_id
    
    $validated = $request->validate([
        'type' => 'required|string|max:50',
        'project_id' => 'nullable|exists:projects,id',  // ❌ Boleh diubah!
        'current_km' => 'nullable|numeric|min:0',
        'pajak_stnk_berlaku_sampai' => 'nullable|date',
        'kir_berlaku_sampai' => 'nullable|date',
    ]);
    
    $vehicle->update($validated);  // ❌ Langsung update tanpa cek
    return redirect()->route('admin.daftar_aset')->with('success', 'Data aset diperbarui.');
}
```

**Impact:**
- ✅ Admin Jakarta berhasil pindahkan Vehicle L 5678 ABC dari Project Surabaya ke Project Jakarta
- Customer Surabaya kehilangan asset di laporan
- Customer Jakarta dapat asset yang bukan miliknya
- **Asset hijacking antar project!**

---

#### Attack 2: Manipulate Vehicle Components Cross-Project

**Admin Jakarta akses component management vehicle Project Surabaya:**

```http
POST /admin/maintenance/components/20/store
{
  "component_name": "Fake Battery",
  "category": "electrical",
  "last_maintenance_km": 30000,
  "maintenance_interval_km": 5000,
  "notes": "Komponen palsu"
}
```

**Kode saat ini (VULNERABLE):**

```php
// app/Http/Controllers/MaintenanceComponentController.php (line ~53)

public function store(Request $request, $vehicleId)
{
    $validated = $request->validate([
        'component_name' => 'required|string|max:100',
        // ...
    ]);
    
    $vehicle = Vehicle::findOrFail($vehicleId);  // ❌ Tidak cek project_id
    
    $component = $vehicle->components()->create($validated);  // ❌ Langsung create
    
    return redirect()->back()->with('success', 'Komponen berhasil ditambahkan.');
}
```

**Impact:**
- ✅ Admin Jakarta berhasil tambah komponen **palsu** di vehicle Project Surabaya
- Maintenance schedule Project Surabaya jadi kacau
- Report kesehatan kendaraan Project Surabaya tidak akurat

---

#### Attack 3: Delete Components Cross-Project

```http
DELETE /admin/maintenance/components/2/delete
```

**Impact:**
- Admin Jakarta hapus component "Oil Filter" dari vehicle Project Surabaya
- Maintenance alert hilang
- Risiko kendaraan tidak terawat karena alert tidak muncul

---

#### Attack 4: Manipulate Maintenance Alerts

```http
POST /admin/maintenance/alerts/15/resolve
{
  "resolution_notes": "Sudah diperbaiki (palsu)"
}
```

**Impact:**
- Admin Jakarta resolve alert **critical** di vehicle Project Surabaya tanpa benar-benar memperbaiki
- Customer Surabaya percaya kendaraan sudah aman, padahal belum
- **Safety risk!**

---

### Vulnerable Routes

**File:** `routes/web.php` Lines 131-177

```php
Route::middleware(['auth', 'role:master,service_admin', 'throttle:60,1'])->group(function () {
    
    // ❌ SEMUA ROUTE INI VULNERABLE:
    
    Route::put('/aset/{vehicle}/update', 'update');  // Update vehicle
    Route::delete('/aset/{vehicle}/hapus', 'destroy');  // Delete vehicle
    Route::post('/daftar-aset/{vehicle}/catat-servis', 'catatServis');  // Log service
    Route::post('/aset/{vehicle}/resolve-issue', 'resolveIssue');  // Resolve issue
    
    // Component management
    Route::post('/maintenance/components/{vehicle}/store', 'store');
    Route::put('/maintenance/components/{component}/update', 'update');
    Route::delete('/maintenance/components/{component}/delete', 'destroy');
    
    // Alert management
    Route::post('/maintenance/alerts/{alert}/acknowledge', 'acknowledge');
    Route::post('/maintenance/alerts/{alert}/resolve', 'resolve');
    
    // Schedule management
    Route::post('/maintenance/schedules/{schedule}/complete', 'complete');
});
```

**Semua route di atas:**
- ✅ Cek role `master` atau `service_admin`
- ❌ **TIDAK CEK** apakah admin punya akses ke project kendaraan tersebut

---

### Business Impact

#### 1. **Asset Hijacking**
```
Admin Jakarta ubah vehicle.project_id dari 2 → 1
Customer Surabaya lapor: "Kendaraan saya hilang dari sistem!"
Customer Jakarta tanya: "Kok ada kendaraan baru yang saya tidak beli?"
```

#### 2. **Data Integrity Corruption**
```
Admin nakal tambah fake components → Alert palsu
Maintenance schedule jadi tidak akurat
Report kesehatan kendaraan misleading
```

#### 3. **Safety Risk**
```
Admin Jakarta resolve "critical brake failure alert" di vehicle Project Surabaya
tanpa benar-benar memperbaiki.

Driver Project Surabaya pakai kendaraan → Kecelakaan karena rem tidak berfungsi.
Liability issue: Sistem mengatakan "sudah diperbaiki" tapi tidak ada bukti.
```

#### 4. **Audit Trail Rusak**
```
Maintenance log menunjukkan:
"Vehicle L 5678 ABC diperbaiki oleh Admin Jakarta"

Tapi vehicle ini milik Project Surabaya!
Admin Jakarta seharusnya tidak punya akses ke sana.
→ Compliance failure untuk ISO, audit keuangan, dll.
```

---

### Fix yang Diperlukan

#### Option 1: Authorization Middleware (RECOMMENDED)

```php
// app/Http/Middleware/EnsureVehicleProjectAccess.php

public function handle(Request $request, Closure $next)
{
    $user = Auth::user();
    
    // Master bypass semua
    if ($user->role === 'master') {
        return $next($request);
    }
    
    // Ambil vehicle_id dari route parameter
    $vehicleId = $request->route('vehicle') 
        ?? $request->route('id')
        ?? $this->extractVehicleIdFromComponent($request);
    
    if ($vehicleId) {
        $vehicle = Vehicle::find($vehicleId);
        
        if ($vehicle && !$user->hasAccessToProject($vehicle->project_id)) {
            \Log::warning('Unauthorized vehicle access attempt', [
                'admin_id' => $user->id,
                'vehicle_id' => $vehicleId,
                'vehicle_project' => $vehicle->project_id,
                'ip' => $request->ip(),
            ]);
            
            abort(403, 'Anda tidak memiliki akses ke kendaraan project ini.');
        }
    }
    
    return $next($request);
}
```

**Apply di route:**
```php
Route::middleware(['auth', 'role:master,service_admin', 'vehicle.project.access'])
    ->group(function () {
        // Semua maintenance routes
    });
```

#### Option 2: Guard di Controller

```php
public function update(Request $request, $id)
{
    $vehicle = Vehicle::findOrFail($id);
    
    // ✅ TAMBAHKAN CEK INI
    $this->authorizeProjectAccess($vehicle->project_id);
    
    $validated = $request->validate([...]);
    
    // ✅ LARANG UBAH project_id jika service_admin
    if (Auth::user()->role === 'service_admin') {
        unset($validated['project_id']);
    }
    
    $vehicle->update($validated);
    return redirect()->route('admin.daftar_aset')->with('success', 'Data aset diperbarui.');
}

private function authorizeProjectAccess($projectId)
{
    if (Auth::user()->role === 'service_admin') {
        if (!Auth::user()->hasAccessToProject($projectId)) {
            abort(403, 'Akses ditolak ke project ini.');
        }
    }
}
```

---

## 📊 Comparison Table

| Aspek | Transport Cost IDOR | Maintenance Cross-Project |
|-------|---------------------|---------------------------|
| **Target** | Financial approval | Asset & maintenance data |
| **Attack** | Approve/reject uang jalan | Modify vehicles, components, alerts |
| **Impact** | Financial fraud | Asset hijacking, safety risk |
| **Severity** | MEDIUM | MEDIUM |
| **Exploitability** | Easy (direct URL) | Easy (direct URL) |
| **Detection** | Sulit (no log) | Sulit (no log) |

---

## ✅ Rekomendasi Fix Priority

### Week 1 (High Priority)
1. ✅ Tambah project authorization check di `TransportCostAdminController`:
   - `approve()`
   - `reject()`
   - `submitToFinance()`
   - `bulkSubmitToFinance()`
   - `exportFinance()`

2. ✅ Tambah project authorization check di `MaintenanceController`:
   - `update()`
   - `destroy()`
   - `catatServis()`
   - `resolveIssue()`

3. ✅ Guard `project_id` field:
   - Service admin **tidak boleh** ubah `project_id`
   - Hanya master yang boleh pindahkan asset antar project

### Week 2 (Medium Priority)
4. ✅ Tambah authorization di component/alert/schedule controllers
5. ✅ Audit log setiap cross-boundary access attempt
6. ✅ Create `hasAccessToProject()` method di User model

### Week 3 (Testing)
7. ✅ Unit test untuk authorization logic
8. ✅ Integration test simulasi attack
9. ✅ Penetration test oleh security team

---

## 🔍 Cara Testing Fix

### Test Case 1: Transport Cost Cross-Project
```bash
# Login sebagai service_admin Project 1
POST /admin/login { "email": "admin.jakarta@..." }

# Coba approve transport cost Project 2
POST /admin/transport-costs/51/approve

# Expected: HTTP 403 Forbidden
# Log: "Unauthorized transport cost approval attempt"
```

### Test Case 2: Vehicle Update Cross-Project
```bash
# Login sebagai service_admin Project 1
POST /admin/login { "email": "admin.jakarta@..." }

# Coba update vehicle Project 2
PUT /admin/aset/20/update { "type": "Truck", "project_id": 1 }

# Expected: HTTP 403 Forbidden
# Log: "Unauthorized vehicle access attempt"
```

### Test Case 3: Master Bypass
```bash
# Login sebagai master
POST /admin/login { "email": "master@..." }

# Master BISA approve/update apapun
POST /admin/transport-costs/51/approve  → Success ✅
PUT /admin/aset/20/update → Success ✅
```

---

**Kesimpulan:** Kedua vulnerability ini sama-sama tentang **missing authorization boundary** di multi-tenant/multi-project environment. Fix-nya juga sama: tambah project access check di setiap action yang manipulate data.
