# 27. LARAVEL BEST PRACTICES

> **Panduan best practices untuk development Laravel di project ini**

---

## 📋 TABLE OF CONTENTS

1. [Project Structure](#project-structure)
2. [Naming Conventions](#naming-conventions)
3. [Controllers](#controllers)
4. [Models & Eloquent](#models--eloquent)
5. [Database & Migrations](#database--migrations)
6. [Validation](#validation)
7. [Security](#security)
8. [Performance](#performance)
9. [Testing](#testing)
10. [Code Quality](#code-quality)

---

## 1. PROJECT STRUCTURE

### Recommended Structure

```
app/
├── Console/
│   └── Commands/              # Artisan commands
├── Exceptions/
│   └── Handler.php
├── Http/
│   ├── Controllers/
│   │   ├── Api/              # API controllers
│   │   ├── Auth/             # Authentication controllers
│   │   └── Admin/            # Admin panel controllers
│   ├── Middleware/
│   ├── Requests/             # Form requests
│   └── Resources/            # API resources
├── Models/
├── Services/                 # Business logic services
├── Repositories/             # Data access layer (optional)
├── Traits/                   # Reusable traits
├── Helpers/                  # Helper functions
└── Providers/

database/
├── factories/
├── migrations/
└── seeders/

tests/
├── Feature/                  # Integration tests
└── Unit/                     # Unit tests
```

### ✅ DO: Organize by Feature (for large apps)

```
app/
├── Domains/
│   ├── Attendance/
│   │   ├── Controllers/
│   │   ├── Models/
│   │   ├── Services/
│   │   └── Requests/
│   ├── Maintenance/
│   │   ├── Controllers/
│   │   ├── Models/
│   │   └── Services/
│   └── Driver/
```

---

## 2. NAMING CONVENTIONS

### Controllers

```php
// ✅ GOOD: Singular, PascalCase, suffix with Controller
DriverController
AttendanceController
MaintenanceController

// ❌ BAD
DriversController  // Plural
driverController   // camelCase
driver_controller  // snake_case
```

### Models

```php
// ✅ GOOD: Singular, PascalCase
Driver
Attendance
Vehicle
MaintenanceLog

// ❌ BAD
Drivers           // Plural
driver            // lowercase
```

### Database Tables

```php
// ✅ GOOD: Plural, snake_case
drivers
attendances
vehicles
maintenance_logs

// ❌ BAD
Driver            // Singular
driversTable      // camelCase
```

### Migrations

```php
// ✅ GOOD: Descriptive, snake_case
2026_05_14_create_drivers_table.php
2026_05_14_add_project_id_to_drivers_table.php
2026_05_14_create_maintenance_schedules_table.php

// ❌ BAD
2026_05_14_drivers.php
2026_05_14_update.php
```

### Variables & Methods

```php
// ✅ GOOD: camelCase
$driverName
$vehicleId
public function getDriverDetails()
public function calculateHealthScore()

// ❌ BAD
$driver_name      // snake_case
$DriverName       // PascalCase
public function get_driver_details()  // snake_case
```

### Constants

```php
// ✅ GOOD: UPPER_SNAKE_CASE
const MAX_UPLOAD_SIZE = 5120;
const CACHE_DRIVER_STATUS = 'driver_status_';

// ❌ BAD
const maxUploadSize = 5120;
const CacheDriverStatus = 'driver_status_';
```

---

## 3. CONTROLLERS

### Single Responsibility Principle

```php
// ✅ GOOD: Controller handles HTTP, delegates to Service
class AttendanceController extends Controller
{
    public function __construct(
        private AttendanceService $attendanceService
    ) {}
    
    public function submitAttendance(Request $request)
    {
        $validated = $request->validate([...]);
        
        $result = $this->attendanceService->createAttendance(
            Auth::user(),
            $validated
        );
        
        return response()->json([
            'status' => 'success',
            'data' => $result
        ]);
    }
}

// ❌ BAD: Business logic in controller
class AttendanceController extends Controller
{
    public function submitAttendance(Request $request)
    {
        // 100+ lines of business logic here
        $driver = Auth::user();
        $vehicle = Vehicle::firstOrCreate([...]);
        // Image processing
        // Database operations
        // Cache clearing
        // etc.
    }
}
```

### Resource Controllers

```php
// ✅ GOOD: Use resource controllers for CRUD
Route::resource('drivers', DriverController::class);

// Generates:
// GET    /drivers           -> index()
// GET    /drivers/create    -> create()
// POST   /drivers           -> store()
// GET    /drivers/{id}      -> show()
// GET    /drivers/{id}/edit -> edit()
// PUT    /drivers/{id}      -> update()
// DELETE /drivers/{id}      -> destroy()
```

### API Resource Controllers

```php
// ✅ GOOD: Use apiResource for API (no create/edit)
Route::apiResource('drivers', DriverController::class);

// Generates:
// GET    /drivers       -> index()
// POST   /drivers       -> store()
// GET    /drivers/{id}  -> show()
// PUT    /drivers/{id}  -> update()
// DELETE /drivers/{id}  -> destroy()
```

### Controller Methods Order

```php
// ✅ GOOD: Consistent order
class DriverController extends Controller
{
    // 1. Constructor
    public function __construct() {}
    
    // 2. Resource methods (in order)
    public function index() {}
    public function create() {}
    public function store() {}
    public function show() {}
    public function edit() {}
    public function update() {}
    public function destroy() {}
    
    // 3. Custom methods
    public function activate() {}
    public function deactivate() {}
    
    // 4. Private helper methods
    private function validateDriver() {}
}
```

---

## 4. MODELS & ELOQUENT

### Mass Assignment Protection

```php
// ✅ GOOD: Use $fillable (whitelist)
class Driver extends Model
{
    protected $fillable = [
        'full_name',
        'driver_id_nik',
        'password',
        'project_id',
    ];
}

// ⚠️ CAUTION: Use $guarded (blacklist) only if you know what you're doing
class Driver extends Model
{
    protected $guarded = ['id'];  // Everything except 'id' is fillable
}

// ❌ BAD: No protection
class Driver extends Model
{
    // No $fillable or $guarded = mass assignment vulnerability
}
```

### Relationships

```php
// ✅ GOOD: Define relationships in models
class Driver extends Model
{
    public function attendances()
    {
        return $this->hasMany(Attendance::class);
    }
    
    public function project()
    {
        return $this->belongsTo(Project::class);
    }
    
    public function activeAttendance()
    {
        return $this->hasOne(Attendance::class)
            ->whereNull('time_out')
            ->latest();
    }
}

// Usage with eager loading
$drivers = Driver::with(['project', 'attendances'])->get();
```

### Scopes

```php
// ✅ GOOD: Use query scopes for reusable queries
class Driver extends Model
{
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }
    
    public function scopeOnDuty($query)
    {
        return $query->whereHas('attendances', function ($q) {
            $q->whereNull('time_out');
        });
    }
    
    public function scopeByProject($query, $projectId)
    {
        return $query->where('project_id', $projectId);
    }
}

// Usage
$drivers = Driver::active()->onDuty()->byProject(1)->get();
```

### Accessors & Mutators

```php
// ✅ GOOD: Use accessors for computed attributes
class Vehicle extends Model
{
    // Accessor (Laravel 9+)
    protected function currentKm(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->latestAttendance?->speedo_akhir ?? 0,
        );
    }
    
    // Mutator
    protected function plateNumber(): Attribute
    {
        return Attribute::make(
            get: fn ($value) => strtoupper($value),
            set: fn ($value) => strtoupper($value),
        );
    }
}

// Usage
$vehicle->current_km;  // Automatically calculated
$vehicle->plate_number = 'b1234xyz';  // Automatically uppercased
```

### Avoid N+1 Queries

```php
// ❌ BAD: N+1 query problem
$drivers = Driver::all();
foreach ($drivers as $driver) {
    echo $driver->project->name;  // Query executed for each driver
}

// ✅ GOOD: Eager loading
$drivers = Driver::with('project')->get();
foreach ($drivers as $driver) {
    echo $driver->project->name;  // No additional queries
}

// ✅ BETTER: Lazy eager loading (if you forgot)
$drivers = Driver::all();
$drivers->load('project');
```

---

## 5. DATABASE & MIGRATIONS

### Migration Best Practices

```php
// ✅ GOOD: Descriptive, reversible migrations
public function up()
{
    Schema::create('maintenance_schedules', function (Blueprint $table) {
        $table->id();
        $table->foreignId('vehicle_id')->constrained()->onDelete('cascade');
        $table->foreignId('component_id')->constrained('vehicle_components');
        $table->date('scheduled_date');
        $table->integer('scheduled_km');
        $table->enum('type', ['preventive', 'corrective', 'predictive']);
        $table->enum('priority', ['low', 'medium', 'high', 'critical']);
        $table->enum('status', ['pending', 'scheduled', 'completed', 'cancelled'])
            ->default('pending');
        $table->decimal('estimated_cost', 10, 2)->nullable();
        $table->decimal('actual_cost', 10, 2)->nullable();
        $table->text('notes')->nullable();
        $table->timestamp('completed_at')->nullable();
        $table->timestamps();
        
        // Indexes
        $table->index('scheduled_date');
        $table->index('status');
        $table->index(['vehicle_id', 'status']);
    });
}

public function down()
{
    Schema::dropIfExists('maintenance_schedules');
}
```

### Foreign Keys

```php
// ✅ GOOD: Use foreignId with constraints
$table->foreignId('vehicle_id')
    ->constrained()
    ->onDelete('cascade');

// ✅ GOOD: Custom foreign key
$table->foreignId('recorded_by_user_id')
    ->constrained('users')
    ->onDelete('set null');

// ❌ BAD: No constraints
$table->unsignedBigInteger('vehicle_id');
```

### Indexes

```php
// ✅ GOOD: Add indexes for frequently queried columns
$table->index('driver_id');
$table->index('vehicle_id');
$table->index('time_in');
$table->index(['driver_id', 'time_in']);  // Composite index

// ✅ GOOD: Unique indexes
$table->unique('driver_id_nik');
$table->unique('plate_number');
```

### Seeders

```php
// ✅ GOOD: Use factories in seeders
class DatabaseSeeder extends Seeder
{
    public function run()
    {
        // Create admin user
        User::factory()->create([
            'name' => 'Admin',
            'email' => 'admin@example.com',
            'role' => 'master_admin',
        ]);
        
        // Create test data
        Driver::factory(50)->create();
        Vehicle::factory(30)->create();
    }
}
```

---

## 6. VALIDATION

### Form Request Validation

```php
// ✅ GOOD: Use Form Requests for complex validation
class StoreDriverRequest extends FormRequest
{
    public function authorize()
    {
        return $this->user()->can('create', Driver::class);
    }
    
    public function rules()
    {
        return [
            'full_name' => ['required', 'string', 'max:255'],
            'driver_id_nik' => ['required', 'string', 'unique:drivers'],
            'nik_ktp' => ['nullable', 'string', 'size:16', 'unique:drivers'],
            'sim_expiry_date' => ['required', 'date', 'after:today'],
            'sim_type' => ['required', 'in:A,B1,B2,C'],
            'password' => ['required', 'min:6', 'confirmed'],
            'project_id' => ['nullable', 'exists:projects,id'],
        ];
    }
    
    public function messages()
    {
        return [
            'driver_id_nik.unique' => 'ID Driver sudah terdaftar.',
            'sim_expiry_date.after' => 'Tanggal berlaku SIM harus di masa depan.',
        ];
    }
}

// Usage in controller
public function store(StoreDriverRequest $request)
{
    // $request is already validated
    Driver::create($request->validated());
}
```

### Custom Validation Rules

```php
// ✅ GOOD: Create custom validation rules
class GpsCoordinateRule implements Rule
{
    public function passes($attribute, $value)
    {
        return preg_match('/^[-]?\d+(\.\d+)?,\s*[-]?\d+(\.\d+)?$/', $value);
    }
    
    public function message()
    {
        return 'Format koordinat GPS tidak valid. Contoh: -6.2088, 106.8456';
    }
}

// Usage
$request->validate([
    'gps_location' => ['required', new GpsCoordinateRule],
]);
```

---

## 7. SECURITY

### Authentication

```php
// ✅ GOOD: Use Laravel Sanctum for API
// config/sanctum.php
'expiration' => null,  // Tokens don't expire
'middleware' => [
    'encrypt_cookies',
    'verify_csrf_token',
],

// Route protection
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/submit-attendance', [AttendanceController::class, 'submitAttendance']);
});
```

### Authorization

```php
// ✅ GOOD: Use Gates and Policies
// app/Providers/AuthServiceProvider.php
Gate::define('is-master-admin', function (User $user) {
    return $user->role === 'master_admin';
});

// Usage in controller
public function __construct()
{
    $this->middleware('can:is-master-admin')->only(['destroy']);
}

// ✅ GOOD: Use Policy for model authorization
class DriverPolicy
{
    public function update(User $user, Driver $driver)
    {
        return $user->role === 'master_admin' || $user->id === $driver->id;
    }
}

// Usage
$this->authorize('update', $driver);
```

### SQL Injection Prevention

```php
// ✅ GOOD: Use Eloquent or Query Builder (auto-escapes)
Driver::where('driver_id_nik', $request->driver_id)->first();

// ✅ GOOD: Use parameter binding
DB::select('SELECT * FROM drivers WHERE driver_id_nik = ?', [$driverId]);

// ❌ BAD: Raw SQL with concatenation
DB::select("SELECT * FROM drivers WHERE driver_id_nik = '$driverId'");
```

### XSS Prevention

```php
// ✅ GOOD: Blade auto-escapes
{{ $driver->full_name }}  // Auto-escaped

// ⚠️ CAUTION: Only use {!! !!} for trusted HTML
{!! $trustedHtml !!}

// ✅ GOOD: Sanitize user input
use Illuminate\Support\Str;
$clean = Str::of($request->input)->stripTags();
```

### CSRF Protection

```php
// ✅ GOOD: CSRF token in forms
<form method="POST" action="/drivers">
    @csrf
    <!-- form fields -->
</form>

// ✅ GOOD: Exclude API routes from CSRF
// app/Http/Middleware/VerifyCsrfToken.php
protected $except = [
    'api/*',
];
```

---

## 8. PERFORMANCE

### Database Query Optimization

```php
// ✅ GOOD: Select only needed columns
Driver::select('id', 'full_name', 'driver_id_nik')->get();

// ❌ BAD: Select all columns
Driver::all();

// ✅ GOOD: Use chunk for large datasets
Driver::chunk(100, function ($drivers) {
    foreach ($drivers as $driver) {
        // Process driver
    }
});

// ✅ GOOD: Use cursor for memory efficiency
foreach (Driver::cursor() as $driver) {
    // Process driver
}
```

### Caching

```php
// ✅ GOOD: Cache expensive queries
$drivers = Cache::remember('drivers.active', 3600, function () {
    return Driver::with('project')->active()->get();
});

// ✅ GOOD: Cache tags (Redis/Memcached only)
Cache::tags(['drivers', 'active'])->put('drivers.active', $drivers, 3600);

// Invalidate
Cache::tags(['drivers'])->flush();

// ✅ GOOD: Cache driver status
$status = Cache::remember("driver.status.{$driverId}", 60, function () use ($driverId) {
    return Driver::find($driverId)->isOnDuty();
});
```

### Queue Jobs

```php
// ✅ GOOD: Queue time-consuming tasks
// app/Jobs/SendMaintenanceReminder.php
class SendMaintenanceReminder implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    
    public function __construct(
        public Vehicle $vehicle,
        public MaintenanceSchedule $schedule
    ) {}
    
    public function handle()
    {
        // Send email/SMS notification
        Mail::to($this->vehicle->driver->email)
            ->send(new MaintenanceReminderMail($this->schedule));
    }
}

// Dispatch job
SendMaintenanceReminder::dispatch($vehicle, $schedule);

// Dispatch with delay
SendMaintenanceReminder::dispatch($vehicle, $schedule)
    ->delay(now()->addHours(24));
```

### Eager Loading

```php
// ✅ GOOD: Eager load relationships
$attendances = Attendance::with([
    'driver:id,full_name',
    'vehicle:id,plate_number',
    'vehicle.project:id,name'
])->get();

// ✅ GOOD: Conditional eager loading
$attendances = Attendance::with([
    'driver' => function ($query) {
        $query->select('id', 'full_name')->where('status', 'active');
    }
])->get();
```

---

## 9. TESTING

### Feature Tests

```php
// ✅ GOOD: Test API endpoints
class AttendanceTest extends TestCase
{
    use RefreshDatabase;
    
    public function test_driver_can_check_in()
    {
        $driver = Driver::factory()->create();
        $vehicle = Vehicle::factory()->create();
        
        $response = $this->actingAs($driver, 'sanctum')
            ->postJson('/api/submit-attendance', [
                'plate_number' => $vehicle->plate_number,
                'gps_location' => '-6.2088, 106.8456',
                'timestamp' => now()->toDateTimeString(),
                'speedometer_manual' => 45000,
            ]);
        
        $response->assertStatus(200)
            ->assertJson([
                'status' => 'success',
            ]);
        
        $this->assertDatabaseHas('attendances', [
            'driver_id' => $driver->id,
            'vehicle_id' => $vehicle->id,
        ]);
    }
}
```

### Unit Tests

```php
// ✅ GOOD: Test business logic
class VehicleHealthServiceTest extends TestCase
{
    public function test_calculates_health_score_correctly()
    {
        $vehicle = Vehicle::factory()->create([
            'current_km' => 45000,
            'last_service_km' => 40000,
            'service_interval_km' => 5000,
        ]);
        
        $service = new VehicleHealthService();
        $score = $service->calculateHealthScore($vehicle);
        
        $this->assertGreaterThan(0, $score);
        $this->assertLessThanOrEqual(100, $score);
    }
}
```

---

## 10. CODE QUALITY

### Use Type Hints

```php
// ✅ GOOD: Use type hints
public function createAttendance(Driver $driver, array $data): Attendance
{
    return Attendance::create([
        'driver_id' => $driver->id,
        ...$data,
    ]);
}

// ❌ BAD: No type hints
public function createAttendance($driver, $data)
{
    return Attendance::create([
        'driver_id' => $driver->id,
        ...$data,
    ]);
}
```

### Use PHP 8+ Features

```php
// ✅ GOOD: Constructor property promotion (PHP 8.0+)
class AttendanceService
{
    public function __construct(
        private AttendanceRepository $repository,
        private VehicleService $vehicleService,
        private CacheService $cacheService,
    ) {}
}

// ✅ GOOD: Named arguments (PHP 8.0+)
Driver::create(
    full_name: 'John Doe',
    driver_id_nik: 'DRV001',
    password: Hash::make('password'),
);

// ✅ GOOD: Match expression (PHP 8.0+)
$status = match ($vehicle->health_status_code) {
    'healthy' => 'Sehat',
    'warning' => 'Perlu Perhatian',
    'critical' => 'Kritis',
    default => 'Tidak Diketahui',
};
```

### Use Laravel Collections

```php
// ✅ GOOD: Use collection methods
$totalKm = $attendances->sum(fn($a) => $a->speedo_akhir - $a->speedo_awal);

$grouped = $attendances->groupBy('driver_id')
    ->map(fn($group) => $group->count());

// ❌ BAD: Use loops
$totalKm = 0;
foreach ($attendances as $attendance) {
    $totalKm += $attendance->speedo_akhir - $attendance->speedo_awal;
}
```

### Code Comments

```php
// ✅ GOOD: Comment complex logic
/**
 * Calculate vehicle health score based on multiple factors.
 * 
 * Formula:
 * Health Score = (
 *     Component_Health_Average * 0.40 +
 *     Maintenance_Compliance * 0.30 +
 *     Daily_Check_Score * 0.20 +
 *     Age_Factor * 0.10
 * ) * 100
 * 
 * @param Vehicle $vehicle
 * @return float Score between 0-100
 */
public function calculateHealthScore(Vehicle $vehicle): float
{
    // Implementation
}

// ❌ BAD: Obvious comments
// Get driver
$driver = Driver::find($id);

// Loop through attendances
foreach ($attendances as $attendance) {
    // ...
}
```

---

**Document Version**: 1.0  
**Last Updated**: 2026-05-14  
**Owner**: Development Team

---

**Related Documents:**
- [28. Code Standards](./28_CODE_STANDARDS.md)
- [29. Security Best Practices](./29_SECURITY_BEST_PRACTICES.md)
- [30. Performance Optimization](./30_PERFORMANCE_OPTIMIZATION.md)
- [31. Testing Strategy](./31_TESTING_STRATEGY.md)
