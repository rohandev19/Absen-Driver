# Bug Audit Report — absen_backend
> Tanggal audit: 4 Juni 2026  
> Total temuan: **42 issues** (7 Critical · 14 High · 12 Medium · 9 Low)

---

## Ringkasan Eksekutif

| Severity | Jumlah |
|----------|--------|
| 🔴 Critical | 7 |
| 🟠 High | 14 |
| 🟡 Medium | 12 |
| 🟢 Low | 9 |
| **Total** | **42** |

---

## 🔴 CRITICAL

### #1 — Arbitrary File Write via Signature Upload (RCE Risk)
- **File:** `app/Http/Controllers/ServiceReportController.php` ~L71–82
- **Kategori:** Security
- **Deskripsi:**  
  Method `approve()` mengekstrak `$imageType` dari data-URI base64 yang dikirim user tanpa whitelist validasi. Attacker bisa mengirim `image/php` sebagai tipe, lalu server menyimpan file PHP eksekutabel di `storage/public/signatures/`.
  ```php
  // KODE RENTAN
  $imageTypeAux = explode("image/", $imageParts[0]);
  $imageType = $imageTypeAux[1]; // Sepenuhnya dikontrol attacker
  $fileName = 'signatures/admin_' . $report->id . '_' . uniqid() . '.' . $imageType;
  Storage::disk('public')->put($fileName, $imageBase64);
  ```
- **Fix:**
  ```php
  $allowedTypes = ['png', 'jpeg', 'jpg'];
  $imageType = strtolower($imageTypeAux[1] ?? '');
  if (!in_array($imageType, $allowedTypes)) {
      return back()->with('error', 'Format tanda tangan tidak valid.');
  }
  $imageBase64 = base64_decode($imageParts[1], true);
  if ($imageBase64 === false) { abort(422, 'Invalid signature data'); }
  $fileName = 'signatures/admin_' . $report->id . '_' . uniqid() . '.png';
  ```

---

### #2 — Mass Assignment pada Vehicle Update
- **File:** `app/Http/Controllers/MaintenanceController.php` ~L380
- **Kategori:** Security
- **Deskripsi:**  
  `$vehicle->update($request->except(['plate_number']))` hanya mengecualikan `plate_number`, tapi semua field lain diteruskan langsung ke Eloquent tanpa validasi eksplisit.
- **Fix:**
  ```php
  $validated = $request->validate([
      'type'                      => 'required|string|max:50',
      'project_id'                => 'nullable|exists:projects,id',
      'service_interval_km'       => 'nullable|integer|min:0',
      'pajak_stnk_berlaku_sampai' => 'nullable|date',
      'kir_berlaku_sampai'        => 'nullable|date',
      'status'                    => 'nullable|in:active,inactive',
  ]);
  $vehicle->update($validated);
  ```

---

### #3 — Mass Assignment pada Component Store/Update
- **File:** `app/Http/Controllers/MaintenanceController.php` ~L398, L416
- **Kategori:** Security
- **Deskripsi:**  
  `$vehicle->components()->create($request->all())` dan `$component->update($request->all())` melewatkan seluruh request body tanpa filter.
- **Fix:** Ganti `.all()` → `.validated()` di kedua method. Pastikan validation rules sudah mencakup semua field yang diizinkan.

---

### #4 — Race Condition Clock-In (TOCTOU)
- **File:** `app/Http/Controllers/Api/AttendanceController.php` ~L117–131
- **Kategori:** Logic Bug / Data Integrity
- **Deskripsi:**  
  Pengecekan duplikasi absensi aktif dilakukan **di luar** `DB::transaction`. Dua request bersamaan dari driver yang sama bisa melewati cek sebelum salah satu menulis ke DB, menghasilkan dua record absensi aktif sekaligus.
- **Fix:**
  ```php
  DB::transaction(function () use ($driver, ...) {
      $isOnDuty = Attendance::where('driver_id', $driver->id)
          ->whereNull('time_out')
          ->lockForUpdate()  // Pessimistic lock
          ->exists();
      if ($isOnDuty) {
          throw new \Exception('ALREADY_ON_DUTY');
      }
      Attendance::create([...]);
      if ($driverRecord) $driverRecord->update(['is_on_duty' => true]);
  });
  ```

---

### #5 — IDOR pada Dokumen KTP/SIM Driver
- **File:** `app/Http/Controllers/DriverController.php` ~L125–142
- **Kategori:** Security
- **Deskripsi:**  
  `lihatDokumen($id, $jenis)` melayani dokumen sensitif (KTP/SIM) berdasarkan ID driver tanpa verifikasi kepemilikan dan tanpa audit log akses.
- **Fix:**
  1. Tambahkan `abort_unless(in_array($jenis, ['ktp', 'sim']), 403)`.
  2. Log setiap akses: `Log::info("Document accessed", ['admin' => Auth::id(), 'driver' => $id, 'type' => $jenis])`.
  3. Verifikasi path tidak keluar dari direktori storage driver.

---

### #6 — Idempotency Tanpa Unique Constraint di DB
- **File:** `database/migrations/2026_06_03_000001_add_offline_recovery_columns_to_attendances_table.php` ~L14
- **Kategori:** Data Integrity
- **Deskripsi:**  
  `offline_entry_id` hanya punya index biasa, bukan UNIQUE. Jika dua retry tiba bersamaan sebelum salah satu menulis, keduanya lolos cek aplikasi dan menghasilkan data duplikat. Jaminan idempotency sepenuhnya ilusif.
- **Fix:**
  ```php
  // Ganti dari:
  $table->index('offline_entry_id', 'idx_attendances_offline_entry_id');
  // Menjadi:
  $table->unique('offline_entry_id', 'uk_attendances_offline_entry_id');
  ```
  MySQL/PostgreSQL mengizinkan multiple NULL pada kolom UNIQUE, jadi record non-offline tidak terdampak.

---

### #7 — Admin Login Tanpa Account Lockout
- **File:** `app/Http/Controllers/Auth/AdminLoginController.php`, `routes/web.php`
- **Kategori:** Security
- **Deskripsi:**  
  Throttle `5,1` hanya per-IP. Attacker yang rotasi IP bisa brute-force tanpa batas. Tidak ada log gagal login, tidak ada lockout per akun.
- **Fix:**
  1. Log gagal login: `Log::warning("Failed admin login", ['email' => $request->email, 'ip' => $request->ip()])`.
  2. Gunakan `RateLimiter` Laravel dengan key `email|ip`.
  3. Pertimbangkan `users.failed_logins` counter + `locked_until` timestamp.

---

## 🟠 HIGH

### #8 — HTTP 422 untuk Submission Berhasil (Late Offline Sync)
- **File:** `app/Http/Controllers/Api/AttendanceController.php` ~L380–390
- **Kategori:** Logic Bug
- **Deskripsi:**  
  Submission terlambat (>24 jam) mengembalikan HTTP **422** meskipun data berhasil disimpan. HTTP 422 semantiknya "gagal validasi" — Flutter akan menganggap ini error dan retry terus-menerus.
  ```php
  $statusCode = $isLateSubmission ? 422 : 200; // BUG: data tersimpan tapi HTTP = error
  ```
- **Fix:** Selalu return HTTP 200. Gunakan field `warning` di body untuk mengomunikasikan keterlambatan.

---

### #9 — Timestamp Clock-In Tidak Ada Batas Bawah
- **File:** `app/Http/Controllers/Api/AttendanceController.php` ~L106–113
- **Kategori:** Logic Bug
- **Deskripsi:**  
  Validasi timestamp hanya cek jika terlalu jauh ke **depan** (+10 menit). Tidak ada cek untuk timestamp yang terlalu jauh ke **belakang** — driver bisa clock-in dengan timestamp `2020-01-01 00:00:00`.
- **Fix:**
  ```php
  if ($clientTime->lt(Carbon::now()->subMinutes(30))) {
      return response()->json(['status' => 'error', 'message' => 'Waktu terlalu jauh di masa lalu.'], 422);
  }
  ```

---

### #10 — Accessor `getCurrentKmAttribute()` Override Kolom DB
- **File:** `app/Models/Vehicle.php` ~L85–97
- **Kategori:** Logic Bug
- **Deskripsi:**  
  `$vehicle->current_km` tidak membaca kolom DB — ia menjalankan query ke relasi `latestAttendance`. Ini menyebabkan N+1 di model events dan membuat nilai DB vs accessor tidak konsisten.
- **Fix:** Rename accessor ke `getComputedKmAttribute()`, update semua caller, biarkan `current_km` sebagai kolom DB biasa.

---

### #11 — N+1 Query di `MaintenanceController::index()`
- **File:** `app/Http/Controllers/MaintenanceController.php` ~L60–90
- **Kategori:** Performance
- **Deskripsi:**  
  Loop di `index()` memanggil `healthService->calculateHealthScore($vehicle)` yang kembali mengakses relasi `latestAttendance` sudah di-eager-load, namun `current_km` accessor memicunya lagi. `components()` juga di-query ulang di `getComponentStatus()` per kendaraan.
- **Fix:** Pre-load semua data yang dibutuhkan. Cache hasil health score. Gunakan `withCount()` jika hanya butuh jumlah.

---

### #12 — `resolveIssue()` Korupsi Audit Trail
- **File:** `app/Http/Controllers/MaintenanceController.php` ~L404
- **Kategori:** Logic Bug
- **Deskripsi:**  
  Saat resolve 1 issue, method ini set **semua** field `check_ban`, `check_rem`, `check_lampu` ke `'Aman'` — termasuk yang tidak bermasalah. Data historis berubah secara tidak akurat.
- **Fix:** Hanya update field spesifik yang di-resolve. Terima parameter `field` dan update field tersebut saja.

---

### #13 — Tidak Ada Authorization Check di `VehicleHealthController`
- **File:** `app/Http/Controllers/VehicleHealthController.php`
- **Kategori:** Security
- **Deskripsi:**  
  `index()` dan `show()` tidak memanggil `$this->authorize()`. Jika route misconfigured, user dengan role `customer` bisa mengakses data kesehatan semua kendaraan termasuk milik customer lain.
- **Fix:** Tambahkan explicit authorization check di setiap method.

---

### #14 — Missing `use Str` Import → Fatal Error di Production
- **File:** `app/Http/Controllers/TransportCostAdminController.php` ~L175
- **Kategori:** Code Quality / Runtime Error
- **Deskripsi:**  
  `Str::slug($driver->full_name)` dipanggil tapi `Str` tidak di-import. Akan throw `Class "App\Http\Controllers\Str" not found` saat admin export rekap bulanan.
- **Fix:**
  ```php
  use Illuminate\Support\Str; // Tambahkan di bagian use imports
  ```

---

### #15 — Validasi KM Servis Dilewati Jika `latestAttendance` Null
- **File:** `app/Http/Controllers/MaintenanceController.php` ~L336–364
- **Kategori:** Data Integrity
- **Deskripsi:**  
  Jika driver belum pernah absen (`latestAttendance` null), seluruh cek batas KM dilewati — nilai KM berapapun bisa disimpan.
- **Fix:** Selalu validasi terhadap `$vehicle->attributes['current_km'] ?? 0`.

---

### #16 — Total Jarak Bisa Negatif di Dashboard
- **File:** `app/Http/Controllers/DashboardController.php` ~L24, L92
- **Kategori:** Logic Bug
- **Deskripsi:**  
  `SUM(speedo_akhir - speedo_awal)` menghasilkan nilai negatif jika `speedo_akhir` NULL (di-cast ke 0 oleh MySQL) atau data entry salah. Dashboard KPI menampilkan total jarak negatif.
- **Fix:**
  ```sql
  SUM(GREATEST(0, CAST(speedo_akhir AS SIGNED) - CAST(speedo_awal AS SIGNED)))
  ```
  Tambahkan `WHERE speedo_akhir IS NOT NULL`.

---

### #17 — N+1: 1.550+ Query per Export Rekap
- **File:** `app/Http/Controllers/MaintenanceController.php` ~L445–475 (export function)
- **Kategori:** Performance
- **Deskripsi:**  
  Loop 50 driver × 30 hari = query individu per driver per tanggal. Export untuk 50 driver sebulan menghasilkan >1.550 query.
- **Fix:** Pre-load semua attendance data untuk range tanggal dalam 1 query, lalu index dengan `Collection::groupBy('driver_id')`.

---

### #18 — Dual Source of Truth: `is_on_duty` vs `attendances`
- **File:** `app/Models/Driver.php`, `app/Http/Controllers/Api/AttendanceController.php`
- **Kategori:** Data Integrity
- **Deskripsi:**  
  Sistem memiliki dua sumber status tugas: `drivers.is_on_duty` dan `attendances WHERE time_out IS NULL`. Jika `$driverRecord` null di `submitEndOfDutyReport()`, attendance terclock-out tapi `is_on_duty` tetap `true` selamanya — driver terkunci permanen.
  ```php
  if ($driverRecord) {
      $driverRecord->update(['is_on_duty' => false]);
  }
  // Jika $driverRecord null → flag tidak di-reset → driver terkunci
  ```
- **Fix:** Jika `$driverRecord` null, throw exception dan abort transaction. Atau eliminasi flag redundan dan derive status dari `attendances` saja.

---

### #19 — `updateComponent()` Tidak Verifikasi Kepemilikan
- **File:** `app/Http/Controllers/MaintenanceController.php` ~L409
- **Kategori:** Security
- **Deskripsi:**  
  `VehicleComponent::findOrFail($componentId)` tidak memverifikasi komponen tersebut milik kendaraan yang boleh diakses admin. Dalam setup multi-tenant, admin proyek A bisa memodifikasi komponen proyek B.
- **Fix:**
  ```php
  abort_unless(
      $component->vehicle->project_id === Auth::user()->project_id
      || Auth::user()->role === 'master',
      403
  );
  ```

---

### #20 — Accessor Foto Return Broken URL Jika Path Null
- **File:** `app/Models/ServiceReport.php` ~L80–88
- **Kategori:** Logic Bug
- **Deskripsi:**  
  `getVehicleConditionPhotoUrlAttribute()` memanggil `asset('storage/' . $this->path)` tanpa null check. Jika path null, return `asset('storage/')` — URL rusak di API response.
- **Fix:**
  ```php
  public function getVehicleConditionPhotoUrlAttribute(): ?string
  {
      return $this->vehicle_condition_photo_path
          ? asset('storage/' . $this->vehicle_condition_photo_path)
          : null;
  }
  ```

---

### #21 — `clockOutOffline` Tidak Ada Rate Limiting Khusus
- **File:** `routes/api.php`
- **Kategori:** Security / Performance
- **Deskripsi:**  
  Endpoint ini menerima upload file hingga 2MB + image processing + DB write. Tanpa rate limiting khusus, token yang valid bisa memflooding server.
- **Fix:** Tambahkan `throttle:10,1` khusus untuk route ini.

---

## 🟡 MEDIUM

### #22 — Stale Cache `checkDriverStatus()` hingga 60 Detik
- **File:** `app/Http/Controllers/Api/AttendanceController.php` ~L41
- **Deskripsi:** TTL 60 detik bisa menyajikan status "tidak bertugas" padahal driver baru clock-in. Kurangi TTL ke 15 detik atau gunakan cache invalidation yang lebih agresif.

### #23 — `optimizedImageProcessing()` Copy-Paste di 3 Controller
- **File:** `AttendanceController.php`, `Api/ServiceReportController.php`, `Api/TransportCostController.php`
- **Deskripsi:** Logika identik di-copy-paste verbatim. Bug fix harus diterapkan di 3 tempat.
- **Fix:** Ekstrak ke `app/Services/ImageProcessingService.php`.

### #24 — `Vehicle::firstOrCreate()` Auto-Create Tanpa Approval Admin
- **File:** `AttendanceController.php` L139, L378; `Api/ServiceReportController.php` L31
- **Deskripsi:** Plat nomor tidak dikenal otomatis dibuat sebagai kendaraan baru dengan tipe `'Otomatis Ditambah'`. Tidak ada notifikasi admin, tidak ada validasi format plat.
- **Fix:** Reject plat tidak dikenal dengan HTTP 422, minta admin registrasi kendaraan lebih dulu.

### #25 — Validasi `type` Kendaraan Tidak Ada Enum
- **File:** `app/Http/Controllers/MaintenanceController.php` ~L376
- **Deskripsi:** `'type' => 'required'` hanya cek keberadaan, bukan nilai yang valid. Nilai sembarang bisa disimpan.
- **Fix:** Tambahkan `'type' => 'required|string|max:50|in:Truk,Pickup,Van,...'`.

### #26 — `MaintenanceController` adalah God Class (800+ Baris, 30+ Method)
- **File:** `app/Http/Controllers/MaintenanceController.php`
- **Deskripsi:** Satu controller menangani vehicle CRUD, service logging, visual check, component management, alert, schedule, calendar, dan Excel export. Melanggar Single Responsibility Principle.
- **Fix:** Pecah menjadi controller terpisah per domain.

### #27 — `VehicleComponent::updateStatus()` Akses Relasi Tidak Di-Load
- **File:** `app/Models/VehicleComponent.php` ~L65–83
- **Deskripsi:** `saving` event memanggil `$this->vehicle->current_km` — jika relasi belum di-load, return `null` dan status komponen salah dihitung.
- **Fix:** `$vehicle = $this->vehicle ?? $this->load('vehicle')->vehicle;`

### #28 — `getDaysRemainingAttribute()` Tidak Bisa Bedakan "Jatuh Tempo" vs "Terlambat"
- **File:** `app/Models/VehicleComponent.php` ~L103
- **Deskripsi:** `max(0, ...)` membuat komponen terlambat 45 hari dan jatuh tempo hari ini sama-sama menampilkan "0 hari". UI tidak bisa membedakan.
- **Fix:** Hapus `max(0, ...)`, biarkan nilai negatif propagate. Update view untuk handle nilai negatif.

### #29 — `total_cost` Computed Accessor Tidak Disimpan di DB
- **File:** `app/Models/TransportCost.php` ~L88–91
- **Deskripsi:** `total_cost` adalah accessor, bukan kolom DB. `SUM('total_cost')` di level DB akan gagal atau return salah.
- **Fix:** Buat sebagai generated column di DB, atau dokumentasikan bahwa tidak boleh diakses via raw SQL.

### #30 — `rejected_reason` Disimpan Tanpa Sanitasi
- **File:** `ServiceReportController.php` L104; `TransportCostAdminController.php` L97
- **Deskripsi:** Jika view menggunakan `{!! !!}` untuk menampilkan rejection reason, ini menjadi stored XSS vector.
- **Fix:** Pastikan semua view menggunakan `{{ }}` Blade syntax untuk output ini.

### #31 — GET Endpoint dengan Side Effect (Auto-Submit ke Finance)
- **File:** `app/Http/Controllers/TransportCostAdminController.php` ~L132–138
- **Deskripsi:** `exportFinance()` adalah GET request yang otomatis men-set `submitted_to_finance = true` tanpa konfirmasi. Melanggar prinsip idempotency HTTP GET.
- **Fix:** Pisahkan submission action (POST) dari download dokumen (GET).

### #32 — `VehicleHealthController::index()` Load Seluruh Armada Tanpa Paginasi
- **File:** `app/Http/Controllers/VehicleHealthController.php` ~L35
- **Deskripsi:** `Vehicle::with(['components', 'maintenanceAlerts'])->get()` memuat semua data ke memory. Untuk 500+ kendaraan, akan OOM atau timeout.
- **Fix:** Tambahkan paginasi: `->paginate($request->input('per_page', 20))`.

### #33 — Pesan Warning Late Submission Tidak Informatif
- **File:** `app/Http/Controllers/Api/AttendanceController.php` ~L380
- **Deskripsi:** Pesan hanya bilang ">24 jam" tanpa menyebut delay aktual.
- **Fix:** `"Data berhasil disimpan (terlambat " . round($delayMinutes / 60) . " jam)."`.

---

## 🟢 LOW

### #34 — Inkonsistensi FQCN vs `use` Import
- **File:** `app/Http/Controllers/Api/AttendanceController.php`
- **Deskripsi:** Campuran `\App\Models\Driver::find()` inline dan kelas yang sudah di-import via `use`. Tidak konsisten dan sulit dibaca.
- **Fix:** Import semua kelas via `use` di bagian atas file.

### #35 — `Driver::isOnDuty()` Method dan Kolom `is_on_duty` Redundan
- **File:** `app/Models/Driver.php`
- **Deskripsi:** Dua mekanisme yang bisa return nilai berbeda. Kode pemanggil menggunakan keduanya secara bergantian.
- **Fix:** Pilih satu sumber kebenaran. Hapus atau rename method yang tidak dipakai.

### #36 — Magic String Status Value di Seluruh Codebase
- **File:** `TransportCost.php`, `ServiceReport.php`, `MaintenanceSchedule.php`, `MaintenanceAlert.php`
- **Deskripsi:** `'pending'`, `'approved'`, `'rejected'` di-hardcode sebagai raw string di query dan conditions. `ServiceReport` punya constants, `TransportCost` tidak.
- **Fix:** Tambahkan `const STATUS_*` di `TransportCost` dan gunakan konsisten.

### #37 — `MaintenanceController` Constructor Menggunakan `new` Bukan DI
- **File:** `app/Http/Controllers/MaintenanceController.php` ~L34–35
- **Deskripsi:** `$this->healthService = new VehicleHealthService()` — tidak bisa di-mock untuk testing, bypass service container binding.
- **Fix:**
  ```php
  public function __construct(VehicleHealthService $healthService, MaintenanceAlertService $alertService) {
      $this->healthService = $healthService;
      $this->alertService = $alertService;
  }
  ```

### #38 — Pesan Error Login Bisa Dikembangkan Menjadi User Enumeration
- **File:** `app/Http/Controllers/Auth/AdminLoginController.php` ~L51
- **Deskripsi:** Pesan saat ini sudah generic (aman). Tambahkan komentar eksplisit bahwa ini disengaja agar developer berikutnya tidak mengubahnya.

### #39 — `delay_minutes` Selalu Positif, Tidak Bisa Deteksi Clock Skew
- **File:** `app/Http/Controllers/Api/AttendanceController.php` (method `logOfflineRecovery()`)
- **Deskripsi:** `$now->diffInMinutes($deviceTimestamp)` selalu absolute. Timestamp device di masa depan (clock skew) tetap disimpan sebagai positif.
- **Fix:** Gunakan `$now->diffInMinutes($deviceTimestamp, false)` untuk signed value.

### #40 — Tidak Ada Caching untuk `getEvents()` Calendar
- **File:** `app/Http/Controllers/MaintenanceController.php` (method `getEvents()`)
- **Deskripsi:** Full table scan setiap request. Jika calendar auto-refresh setiap beberapa detik, ini boros query.
- **Fix:** `Cache::remember('maintenance_events', 300, fn() => ...)`.

### #41 — `EnsureUserRole` Middleware Return HTML 403 untuk API Routes
- **File:** `app/Http/Middleware/EnsureUserRole.php` ~L20
- **Deskripsi:** `abort(403)` return HTML. Mobile client menerima HTML dan gagal parse JSON — menampilkan error tak terbaca.
- **Fix:**
  ```php
  if ($request->expectsJson()) {
      return response()->json(['status' => 'error', 'message' => 'Akses ditolak'], 403);
  }
  abort(403);
  ```

### #42 — `OfflineRecoveryLog` Model Tidak Punya `updated_at` tapi Tidak Disable Timestamps
- **File:** `database/migrations/2026_06_03_234453_create_offline_recovery_logs_table.php` ~L38, `app/Models/OfflineRecoveryLog.php`
- **Deskripsi:** Migration hanya membuat `created_at`, tidak `updated_at`. Eloquent default akan mencoba update `updated_at` jika record pernah di-save, menyebabkan DB error.
- **Fix:**
  ```php
  // Di model OfflineRecoveryLog:
  public $timestamps = false;
  ```

---

## Temuan Schema / Migration

| Issue | Migration | Catatan |
|-------|-----------|---------|
| `gps_location_in` NOT NULL tanpa default | `2025_11_12_115303` | Breaks record jika GPS unavailable |
| `offline_entry_id` hanya INDEX bukan UNIQUE | `2026_06_03_000001` | Idempotency tidak terjamin di DB level |
| `delay_minutes` INTEGER NOT NULL di schema | `2026_06_03_234453` | Tapi kode bisa pass `null` — schema mismatch |
| Tidak ada soft-delete di `Driver`, `Vehicle`, `Attendance` | Multiple | Hard delete merusak audit trail absensi |
| `attendance_id` UNIQUE di `transport_costs` dengan `onDelete('restrict')` | `2026_05_22_214442` | Pertimbangkan `onDelete('cascade')` |

---

## Urutan Prioritas Perbaikan

### Batch 1 — Segera (Production Risk)
1. **#1** Fix signature upload — whitelist ekstensi
2. **#6** Tambah UNIQUE constraint `offline_entry_id` (new migration)
3. **#4** Pindahkan race condition check ke dalam transaction + `lockForUpdate()`
4. **#14** Tambah `use Illuminate\Support\Str` di `TransportCostAdminController` (**fatal error**)
5. **#8** Fix HTTP 422 → 200 untuk late submission berhasil
6. **#18** Handle `$driverRecord` null dengan abort transaction

### Batch 2 — Minggu Ini (Security Hardening)
7. **#2 / #3** Ganti `$request->all()` / `$request->except()` → `$request->validated()`
8. **#7** Tambah per-account login lockout + audit log
9. **#9** Tambah batas bawah validasi timestamp clock-in
10. **#41** Fix middleware return JSON untuk API routes

### Batch 3 — Sprint Berikutnya (Stability & Performance)
11. **#11** Fix N+1 di `MaintenanceController::index()`
12. **#17** Fix N+1 di export rekap
13. **#12** Fix `resolveIssue()` corrupting audit trail
14. **#32** Tambah paginasi di `VehicleHealthController::index()`
15. **#10** Rename `getCurrentKmAttribute()` accessor
16. **#23** Ekstrak `optimizedImageProcessing()` ke service

### Batch 4 — Refactor & Code Quality
17. **#26** Pecah `MaintenanceController` god class
18. **#37** Ganti manual instantiation → constructor injection
19. **#36** Tambah status constants di `TransportCost`
20. **#24** Tolak plat nomor tidak dikenal alih-alih auto-create
