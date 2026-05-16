# 🔗 PANDUAN INTEGRASI PREVENTIVE MAINTENANCE KE WEBSITE

## 📊 SITUASI SAAT INI

### ✅ Yang Sudah Ada:
1. **Website Admin** dengan halaman maintenance (`resources/views/admin/maintenance/index.blade.php`)
2. **MaintenanceController** yang menampilkan monitoring kendaraan
3. **Routes** di `routes/web.php` untuk maintenance
4. **Sistem lama** yang tracking KM dan service interval

### ✅ Yang Baru Dibuat:
1. **3 Tabel Baru**: `vehicle_components`, `maintenance_schedules`, `maintenance_alerts`
2. **3 Models Baru**: `VehicleComponent`, `MaintenanceSchedule`, `MaintenanceAlert`
3. **2 Services**: `VehicleHealthService`, `MaintenanceAlertService`
4. **4 Controllers API**: Untuk API endpoints
5. **3 Commands**: Untuk automation

---

## 🎯 STRATEGI INTEGRASI

### **Opsi 1: INTEGRASI PENUH (Recommended)**
Tambahkan fitur preventive maintenance ke website admin yang sudah ada.

**Keuntungan:**
- ✅ Admin bisa kelola komponen dari web
- ✅ Admin bisa lihat alerts dari web
- ✅ Admin bisa kelola schedules dari web
- ✅ Sistem lama tetap jalan
- ✅ Sistem baru melengkapi sistem lama

**Yang Perlu Ditambahkan:**
1. Menu baru di sidebar
2. 3-4 halaman baru (components, alerts, schedules)
3. Update MaintenanceController untuk pakai VehicleHealthService

---

### **Opsi 2: HYBRID (Mudah)**
Sistem lama tetap jalan, sistem baru hanya untuk API (mobile app).

**Keuntungan:**
- ✅ Tidak perlu ubah website
- ✅ Sistem lama tetap jalan
- ✅ API siap untuk mobile app

**Kekurangan:**
- ❌ Admin tidak bisa kelola dari web
- ❌ Harus pakai API atau command line

---

## 📝 IMPLEMENTASI OPSI 1 (INTEGRASI PENUH)

### STEP 1: Update MaintenanceController

Tambahkan method baru di `app/Http/Controllers/MaintenanceController.php`:

```php
use App\Services\VehicleHealthService;
use App\Services\MaintenanceAlertService;
use App\Models\VehicleComponent;
use App\Models\MaintenanceSchedule;
use App\Models\MaintenanceAlert;

// Di dalam class MaintenanceController

protected $healthService;
protected $alertService;

public function __construct()
{
    parent::__construct();
    $this->healthService = new VehicleHealthService();
    $this->alertService = new MaintenanceAlertService();
}

// Method baru untuk halaman components
public function components($vehicleId)
{
    $vehicle = Vehicle::with('components')->findOrFail($vehicleId);
    $healthReport = $this->healthService->getHealthReport($vehicle);
    
    return view('admin.maintenance.components', compact('vehicle', 'healthReport'));
}

// Method baru untuk halaman alerts
public function alerts()
{
    $alerts = MaintenanceAlert::with(['vehicle', 'component'])
        ->active()
        ->orderBy('alert_type')
        ->orderBy('triggered_at', 'desc')
        ->paginate(20);
    
    $summary = $this->alertService->getActiveAlertsSummary();
    
    return view('admin.maintenance.alerts', compact('alerts', 'summary'));
}

// Method baru untuk halaman schedules
public function schedules(Request $request)
{
    $query = MaintenanceSchedule::with(['vehicle', 'component']);
    
    if ($request->filled('status')) {
        $query->where('status', $request->status);
    }
    
    if ($request->filled('priority')) {
        $query->where('priority', $request->priority);
    }
    
    $schedules = $query->orderBy('scheduled_date')->paginate(20);
    
    $stats = [
        'overdue' => MaintenanceSchedule::overdue()->count(),
        'today' => MaintenanceSchedule::where('scheduled_date', now()->toDateString())
            ->where('status', '!=', 'completed')->count(),
        'this_week' => MaintenanceSchedule::upcoming(7)->count(),
    ];
    
    return view('admin.maintenance.schedules', compact('schedules', 'stats'));
}
```

---

### STEP 2: Tambah Routes

Tambahkan di `routes/web.php` di dalam group maintenance:

```php
Route::controller(MaintenanceController::class)->group(function () {
    // ... routes yang sudah ada ...
    
    // PREVENTIVE MAINTENANCE (BARU)
    Route::get('/maintenance/components/{vehicle}', 'components')->name('admin.maintenance.components');
    Route::get('/maintenance/alerts', 'alerts')->name('admin.maintenance.alerts');
    Route::get('/maintenance/schedules', 'schedules')->name('admin.maintenance.schedules');
    
    // AJAX untuk kelola komponen
    Route::post('/maintenance/components/{vehicle}/store', 'storeComponent')->name('admin.maintenance.components.store');
    Route::put('/maintenance/components/{component}/update', 'updateComponent')->name('admin.maintenance.components.update');
    Route::delete('/maintenance/components/{component}/delete', 'deleteComponent')->name('admin.maintenance.components.delete');
    
    // AJAX untuk kelola alerts
    Route::post('/maintenance/alerts/{alert}/acknowledge', 'acknowledgeAlert')->name('admin.maintenance.alerts.acknowledge');
    Route::post('/maintenance/alerts/{alert}/resolve', 'resolveAlert')->name('admin.maintenance.alerts.resolve');
    
    // AJAX untuk kelola schedules
    Route::post('/maintenance/schedules/store', 'storeSchedule')->name('admin.maintenance.schedules.store');
    Route::post('/maintenance/schedules/{schedule}/complete', 'completeSchedule')->name('admin.maintenance.schedules.complete');
});
```

---

### STEP 3: Update Sidebar Menu

Cari file layout sidebar (biasanya `resources/views/admin/layouts/sidebar.blade.php` atau `app.blade.php`).

Tambahkan menu baru:

```html
<!-- Menu Maintenance yang sudah ada -->
<li class="nav-item">
    <a class="nav-link" href="{{ route('admin.maintenance.dashboard') }}">
        <i class="bi bi-speedometer2"></i> Monitoring
    </a>
</li>

<!-- MENU BARU -->
<li class="nav-item">
    <a class="nav-link" href="{{ route('admin.maintenance.alerts') }}">
        <i class="bi bi-bell"></i> Alerts
        @if($activeAlertsCount > 0)
            <span class="badge bg-danger">{{ $activeAlertsCount }}</span>
        @endif
    </a>
</li>

<li class="nav-item">
    <a class="nav-link" href="{{ route('admin.maintenance.schedules') }}">
        <i class="bi bi-calendar-check"></i> Jadwal Maintenance
    </a>
</li>
```

---

### STEP 4: Buat View untuk Components

Buat file `resources/views/admin/maintenance/components.blade.php`:

```blade
@extends('admin.layouts.app')

@section('title', 'Komponen Kendaraan')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h3 class="fw-bold">Komponen: {{ $vehicle->plate_number }}</h3>
            <p class="text-muted">{{ $vehicle->type }}</p>
        </div>
        <div>
            <span class="badge bg-{{ $healthReport['status']['color'] }} fs-5">
                Health Score: {{ $healthReport['health_score'] }}/100
            </span>
        </div>
    </div>

    <!-- Health Breakdown -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card">
                <div class="card-body">
                    <h6>Component Health</h6>
                    <h3>{{ $healthReport['breakdown']['component_health'] }}%</h3>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card">
                <div class="card-body">
                    <h6>Maintenance Compliance</h6>
                    <h3>{{ $healthReport['breakdown']['maintenance_compliance'] }}%</h3>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card">
                <div class="card-body">
                    <h6>Daily Check Score</h6>
                    <h3>{{ $healthReport['breakdown']['daily_check_score'] }}%</h3>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card">
                <div class="card-body">
                    <h6>Age Factor</h6>
                    <h3>{{ $healthReport['breakdown']['age_factor'] }}%</h3>
                </div>
            </div>
        </div>
    </div>

    <!-- Components Table -->
    <div class="card">
        <div class="card-header d-flex justify-content-between">
            <h5>Daftar Komponen</h5>
            <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#addComponentModal">
                <i class="bi bi-plus"></i> Tambah Komponen
            </button>
        </div>
        <div class="card-body">
            <table class="table">
                <thead>
                    <tr>
                        <th>Komponen</th>
                        <th>Kategori</th>
                        <th>Status</th>
                        <th>Sisa KM</th>
                        <th>Next Replacement</th>
                        <th>Biaya</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($vehicle->components as $comp)
                    <tr>
                        <td>{{ $comp->component_name }}</td>
                        <td>{{ $comp->category }}</td>
                        <td>
                            @if($comp->status == 'overdue')
                                <span class="badge bg-danger">Overdue</span>
                            @elseif($comp->status == 'critical')
                                <span class="badge bg-warning">Critical</span>
                            @elseif($comp->status == 'warning')
                                <span class="badge bg-info">Warning</span>
                            @else
                                <span class="badge bg-success">Healthy</span>
                            @endif
                        </td>
                        <td>{{ number_format($comp->km_remaining) }} KM</td>
                        <td>{{ number_format($comp->next_replacement_km) }} KM</td>
                        <td>Rp {{ number_format($comp->cost_per_replacement, 0, ',', '.') }}</td>
                        <td>
                            <button class="btn btn-sm btn-warning">Edit</button>
                            <button class="btn btn-sm btn-danger">Hapus</button>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
```

---

### STEP 5: Buat View untuk Alerts

Buat file `resources/views/admin/maintenance/alerts.blade.php`:

```blade
@extends('admin.layouts.app')

@section('title', 'Maintenance Alerts')

@section('content')
<div class="container-fluid">
    <h3 class="fw-bold mb-4">Maintenance Alerts</h3>

    <!-- Summary Cards -->
    <div class="row mb-4">
        <div class="col-md-4">
            <div class="card border-danger">
                <div class="card-body">
                    <h6>🔴 Overdue</h6>
                    <h2>{{ $summary['by_type']['overdue'] }}</h2>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-warning">
                <div class="card-body">
                    <h6>🟠 Critical</h6>
                    <h2>{{ $summary['by_type']['critical'] }}</h2>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-info">
                <div class="card-body">
                    <h6>🟡 Warning</h6>
                    <h2>{{ $summary['by_type']['warning'] }}</h2>
                </div>
            </div>
        </div>
    </div>

    <!-- Alerts List -->
    <div class="card">
        <div class="card-body">
            @foreach($alerts as $alert)
            <div class="alert alert-{{ $alert->alert_type == 'overdue' ? 'danger' : ($alert->alert_type == 'critical' ? 'warning' : 'info') }} d-flex justify-content-between align-items-center">
                <div>
                    <strong>{{ $alert->vehicle->plate_number }}</strong> - {{ $alert->component->component_name ?? 'General' }}
                    <p class="mb-0">{{ $alert->message }}</p>
                    <small class="text-muted">{{ $alert->triggered_at->diffForHumans() }}</small>
                </div>
                <div>
                    @if($alert->status == 'active')
                        <form action="{{ route('admin.maintenance.alerts.acknowledge', $alert) }}" method="POST" class="d-inline">
                            @csrf
                            <button class="btn btn-sm btn-primary">Acknowledge</button>
                        </form>
                        <form action="{{ route('admin.maintenance.alerts.resolve', $alert) }}" method="POST" class="d-inline">
                            @csrf
                            <button class="btn btn-sm btn-success">Resolve</button>
                        </form>
                    @else
                        <span class="badge bg-secondary">{{ ucfirst($alert->status) }}</span>
                    @endif
                </div>
            </div>
            @endforeach

            {{ $alerts->links() }}
        </div>
    </div>
</div>
@endsection
```

---

### STEP 6: Buat View untuk Schedules

Buat file `resources/views/admin/maintenance/schedules.blade.php`:

```blade
@extends('admin.layouts.app')

@section('title', 'Jadwal Maintenance')

@section('content')
<div class="container-fluid">
    <h3 class="fw-bold mb-4">Jadwal Maintenance</h3>

    <!-- Stats -->
    <div class="row mb-4">
        <div class="col-md-4">
            <div class="card border-danger">
                <div class="card-body">
                    <h6>Overdue</h6>
                    <h2>{{ $stats['overdue'] }}</h2>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-warning">
                <div class="card-body">
                    <h6>Today</h6>
                    <h2>{{ $stats['today'] }}</h2>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-info">
                <div class="card-body">
                    <h6>This Week</h6>
                    <h2>{{ $stats['this_week'] }}</h2>
                </div>
            </div>
        </div>
    </div>

    <!-- Schedules Table -->
    <div class="card">
        <div class="card-header">
            <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#addScheduleModal">
                <i class="bi bi-plus"></i> Tambah Jadwal
            </button>
        </div>
        <div class="card-body">
            <table class="table">
                <thead>
                    <tr>
                        <th>Tanggal</th>
                        <th>Kendaraan</th>
                        <th>Komponen</th>
                        <th>Priority</th>
                        <th>Status</th>
                        <th>Biaya</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($schedules as $schedule)
                    <tr>
                        <td>{{ $schedule->scheduled_date->format('d M Y') }}</td>
                        <td>{{ $schedule->vehicle->plate_number }}</td>
                        <td>{{ $schedule->component->component_name ?? 'General' }}</td>
                        <td>
                            <span class="badge bg-{{ $schedule->priority == 'critical' ? 'danger' : ($schedule->priority == 'high' ? 'warning' : 'info') }}">
                                {{ ucfirst($schedule->priority) }}
                            </span>
                        </td>
                        <td>
                            <span class="badge bg-{{ $schedule->status == 'completed' ? 'success' : 'secondary' }}">
                                {{ ucfirst($schedule->status) }}
                            </span>
                        </td>
                        <td>Rp {{ number_format($schedule->estimated_cost, 0, ',', '.') }}</td>
                        <td>
                            @if($schedule->status != 'completed')
                                <form action="{{ route('admin.maintenance.schedules.complete', $schedule) }}" method="POST">
                                    @csrf
                                    <button class="btn btn-sm btn-success">Complete</button>
                                </form>
                            @endif
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>

            {{ $schedules->links() }}
        </div>
    </div>
</div>
@endsection
```

---

## 🚀 CARA IMPLEMENTASI

### Langkah 1: Backup
```bash
# Backup database
php artisan db:backup

# Backup code
git add .
git commit -m "Before preventive maintenance integration"
```

### Langkah 2: Update Controller
Copy code dari STEP 1 ke `MaintenanceController.php`

### Langkah 3: Update Routes
Copy code dari STEP 2 ke `routes/web.php`

### Langkah 4: Buat Views
Buat 3 file view dari STEP 4, 5, 6

### Langkah 5: Update Sidebar
Update sidebar menu dari STEP 3

### Langkah 6: Test
```bash
# Jalankan server
php artisan serve

# Buka browser
http://localhost:8000/admin/maintenance/alerts
http://localhost:8000/admin/maintenance/schedules
```

---

## 📝 CATATAN PENTING

### Sistem Lama vs Baru

**Sistem Lama (Tetap Jalan):**
- ✅ Monitoring KM kendaraan
- ✅ Service interval tracking
- ✅ Visual check (ban, rem, lampu)
- ✅ Kalender STNK/KIR

**Sistem Baru (Tambahan):**
- ✅ Component-level tracking
- ✅ Health scoring (0-100)
- ✅ Automated alerts
- ✅ Maintenance scheduling
- ✅ Predictive maintenance

**Keduanya SALING MELENGKAPI, tidak saling mengganggu!**

---

## 🎯 REKOMENDASI

Saya sarankan **OPSI 1 (INTEGRASI PENUH)** karena:
1. Admin bisa kelola semua dari web
2. Sistem lebih lengkap
3. Tidak perlu pakai API manual
4. User experience lebih baik

Apakah Anda ingin saya buatkan file-file view lengkapnya sekarang?
