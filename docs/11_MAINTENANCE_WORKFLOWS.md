# 11. MAINTENANCE WORKFLOWS

> **Dokumentasi lengkap workflow preventive maintenance system**  
> **Focus**: Component lifecycle, alerts, schedules, dan workshop integration  
> **Last Updated**: 2026-05-16

---

## 📋 TABLE OF CONTENTS

1. [Component Lifecycle Management](#1-component-lifecycle-management)
2. [Alert Management Workflow](#2-alert-management-workflow)
3. [Schedule Management Workflow](#3-schedule-management-workflow)
4. [Workshop Integration Workflow](#4-workshop-integration-workflow)
5. [Reporting & Analytics Workflow](#5-reporting--analytics-workflow)
6. [Emergency Maintenance Workflow](#6-emergency-maintenance-workflow)

---

## 1. COMPONENT LIFECYCLE MANAGEMENT

### 1.1 Add New Component

**Trigger**: Admin menambahkan komponen baru ke kendaraan

**Flow**:
```
Admin → Component Form → Validation → Save to DB → 
Calculate Next Replacement → Update Vehicle Health → 
Generate Initial Alert (if needed) → Success
```

**Steps**:

1. **Admin mengakses halaman vehicle detail**
   - URL: `/admin/maintenance/components/{vehicle_id}`
   - Click "Add Component" button

2. **Fill component form**
   ```
   Required Fields:
   - Component Name (e.g., "Oli Mesin")
   - Category (dropdown: fluids, filters, brakes, etc.)
   - Replacement Interval KM (e.g., 5000)
   - Replacement Interval Days (e.g., 180)
   - Last Replacement KM (e.g., 45000)
   - Last Replacement Date (e.g., 2026-01-15)
   - Cost per Replacement (e.g., 350000)
   - Warning Threshold KM (e.g., 500)
   - Critical Threshold KM (e.g., 100)
   
   Optional Fields:
   - Notes
   ```

3. **System validation**
   ```php
   Validation Rules:
   - component_name: required, string, max:255
   - category: required, in:fluids,filters,brakes,tires,battery,lights,belts,suspension,engine,transmission
   - replacement_interval_km: required, integer, min:1
   - replacement_interval_days: nullable, integer, min:1
   - last_replacement_km: required, integer, min:0
   - last_replacement_date: required, date
   - cost_per_replacement: required, numeric, min:0
   - warning_threshold_km: required, integer, min:1
   - critical_threshold_km: required, integer, min:1
   ```

4. **Auto-calculation on save**
   ```php
   // Model: VehicleComponent.php
   protected static function booted()
   {
       static::saving(function ($component) {
           // Calculate next replacement
           $component->calculateNextReplacement();
           
           // Update status based on current KM
           $component->updateStatus();
       });
   }
   
   public function calculateNextReplacement(): void
   {
       // Next KM
       if ($this->replacement_interval_km && $this->last_replacement_km) {
           $this->next_replacement_km = 
               $this->last_replacement_km + $this->replacement_interval_km;
       }
       
       // Next Date
       if ($this->replacement_interval_days && $this->last_replacement_date) {
           $this->next_replacement_date = 
               $this->last_replacement_date->addDays($this->replacement_interval_days);
       }
   }
   ```

5. **Update vehicle health score**
   ```php
   // Service: VehicleHealthService.php
   public function updateVehicleHealth(Vehicle $vehicle): void
   {
       $healthScore = $this->calculateHealthScore($vehicle);
       
       Cache::put(
           "vehicle_health_{$vehicle->id}", 
           $healthScore, 
           now()->addHours(1)
       );
   }
   ```

6. **Generate alert if needed**
   ```php
   // Service: MaintenanceAlertService.php
   public function checkAndGenerateAlert(VehicleComponent $component): void
   {
       $kmRemaining = $component->km_remaining;
       
       if ($kmRemaining <= 0) {
           $this->createAlert($component, 'overdue');
       } elseif ($kmRemaining <= $component->critical_threshold_km) {
           $this->createAlert($component, 'critical');
       } elseif ($kmRemaining <= $component->warning_threshold_km) {
           $this->createAlert($component, 'warning');
       }
   }
   ```

**Success Response**:
```json
{
  "status": "success",
  "message": "Component added successfully",
  "data": {
    "component": {
      "id": 1,
      "component_name": "Oli Mesin",
      "status": "warning",
      "km_remaining": 450,
      "next_replacement_km": 50000
    }
  }
}
```

---

### 1.2 Update Component Status

**Trigger**: 
- Kendaraan check-in/check-out (KM bertambah)
- Cron job daily (scheduled command)
- Manual update by admin

**Flow**:
```
Trigger Event → Get All Components → 
For Each Component:
  - Calculate KM Remaining
  - Update Status (healthy/warning/critical/overdue)
  - Generate/Update Alert
→ Update Vehicle Health Score → Done
```

**Automated Update (Cron)**:
```php
// Command: UpdateComponentStatus.php
public function handle()
{
    $vehicles = Vehicle::with('components')->get();
    
    foreach ($vehicles as $vehicle) {
        foreach ($vehicle->components as $component) {
            // Update status
            $component->updateStatus();
            $component->save();
            
            // Check alert
            $this->alertService->checkAndGenerateAlert($component);
        }
        
        // Update vehicle health
        $this->healthService->updateVehicleHealth($vehicle);
    }
    
    $this->info('Component status updated successfully');
}
```

**Status Calculation Logic**:
```php
// Model: VehicleComponent.php
public function updateStatus(): void
{
    $vehicle = $this->vehicle;
    
    if (!$vehicle || !$this->next_replacement_km) {
        $this->status = 'healthy';
        return;
    }
    
    $kmRemaining = $this->next_replacement_km - $vehicle->current_km;
    
    if ($kmRemaining <= 0) {
        $this->status = 'overdue';
    } elseif ($kmRemaining <= $this->critical_threshold_km) {
        $this->status = 'critical';
    } elseif ($kmRemaining <= $this->warning_threshold_km) {
        $this->status = 'warning';
    } else {
        $this->status = 'healthy';
    }
}
```

---

### 1.3 Replace Component (Maintenance Completed)

**Trigger**: Maintenance schedule completed

**Flow**:
```
Admin → Complete Maintenance Schedule → 
Update Component:
  - last_replacement_km = current_km
  - last_replacement_date = today
  - Recalculate next_replacement
  - Reset status to 'healthy'
→ Resolve Related Alerts → 
Update Vehicle Health → 
Log to Maintenance History → Success
```

**Steps**:

1. **Admin marks schedule as completed**
   ```php
   // Controller: MaintenanceScheduleController.php
   public function complete(MaintenanceSchedule $schedule, Request $request)
   {
       $validated = $request->validate([
           'actual_cost' => 'required|numeric|min:0',
           'notes' => 'nullable|string'
       ]);
       
       DB::transaction(function () use ($schedule, $validated) {
           // Mark schedule as completed
           $schedule->markAsCompleted(
               auth()->user(), 
               $validated['actual_cost']
           );
           
           // Update component
           if ($schedule->component) {
               $this->updateComponentAfterMaintenance($schedule->component);
           }
           
           // Resolve alerts
           $this->resolveRelatedAlerts($schedule);
           
           // Log to maintenance history
           $this->logMaintenanceHistory($schedule);
       });
       
       return response()->json([
           'status' => 'success',
           'message' => 'Maintenance completed successfully'
       ]);
   }
   ```

2. **Update component after maintenance**
   ```php
   private function updateComponentAfterMaintenance(VehicleComponent $component)
   {
       $component->update([
           'last_replacement_km' => $component->vehicle->current_km,
           'last_replacement_date' => now(),
       ]);
       
       // This will trigger calculateNextReplacement() and updateStatus()
       // via model events
   }
   ```

3. **Resolve related alerts**
   ```php
   private function resolveRelatedAlerts(MaintenanceSchedule $schedule)
   {
       MaintenanceAlert::where('vehicle_id', $schedule->vehicle_id)
           ->where('component_id', $schedule->component_id)
           ->where('status', '!=', 'resolved')
           ->update([
               'status' => 'resolved',
               'resolved_at' => now()
           ]);
   }
   ```

---

### 1.4 Archive Component

**Trigger**: Komponen tidak digunakan lagi (e.g., kendaraan dijual)

**Flow**:
```
Admin → Select Component → Confirm Archive → 
Soft Delete Component → Resolve All Alerts → 
Cancel Related Schedules → Update Vehicle Health → Success
```

**Implementation**:
```php
// Controller: VehicleComponentController.php
public function destroy(Vehicle $vehicle, VehicleComponent $component)
{
    DB::transaction(function () use ($component) {
        // Resolve all alerts
        $component->alerts()->update([
            'status' => 'dismissed',
            'resolved_at' => now()
        ]);
        
        // Cancel related schedules
        $component->maintenanceSchedules()
            ->where('status', '!=', 'completed')
            ->update(['status' => 'cancelled']);
        
        // Soft delete component
        $component->delete();
        
        // Update vehicle health
        $this->healthService->updateVehicleHealth($component->vehicle);
    });
    
    return response()->json([
        'status' => 'success',
        'message' => 'Component archived successfully'
    ]);
}
```

---

## 2. ALERT MANAGEMENT WORKFLOW

### 2.1 Alert Generation Process

**Trigger**: 
- Cron job every 6 hours
- Component status update
- Manual trigger by admin

**Flow**:
```
Cron Job Start → Get All Components → 
For Each Component:
  - Check if alert already exists
  - If not exists AND needs alert:
    - Create new alert
    - Send notification (email, push, SMS)
  - If exists:
    - Update alert message
    - Escalate if needed
→ Done
```

**Implementation**:
```php
// Command: GenerateMaintenanceAlerts.php
public function handle()
{
    $components = VehicleComponent::with('vehicle', 'alerts')
        ->needsMaintenance()
        ->get();
    
    foreach ($components as $component) {
        $this->processComponentAlert($component);
    }
    
    $this->info("Generated alerts for {$components->count()} components");
}

private function processComponentAlert(VehicleComponent $component)
{
    $kmRemaining = $component->km_remaining;
    $alertType = $this->determineAlertType($kmRemaining, $component);
    
    // Check if alert already exists
    $existingAlert = $component->alerts()
        ->where('status', 'active')
        ->first();
    
    if ($existingAlert) {
        // Update existing alert
        $this->updateAlert($existingAlert, $alertType, $kmRemaining);
    } else {
        // Create new alert
        $this->createAlert($component, $alertType, $kmRemaining);
    }
}

private function determineAlertType(int $kmRemaining, VehicleComponent $component): string
{
    if ($kmRemaining <= 0) {
        return 'overdue';
    } elseif ($kmRemaining <= $component->critical_threshold_km) {
        return 'critical';
    } elseif ($kmRemaining <= $component->warning_threshold_km) {
        return 'warning';
    }
    
    return 'low';
}

private function createAlert(VehicleComponent $component, string $type, int $kmRemaining)
{
    $alert = MaintenanceAlert::create([
        'vehicle_id' => $component->vehicle_id,
        'component_id' => $component->id,
        'alert_type' => $type,
        'message' => $this->generateAlertMessage($component, $type, $kmRemaining),
        'triggered_at' => now(),
        'status' => 'active'
    ]);
    
    // Send notifications
    $this->sendNotifications($alert);
}

private function generateAlertMessage(VehicleComponent $component, string $type, int $kmRemaining): string
{
    $vehicle = $component->vehicle;
    
    $messages = [
        'overdue' => "URGENT: {$component->component_name} pada {$vehicle->plate_number} sudah melewati batas maintenance ({$kmRemaining} KM overdue)",
        'critical' => "CRITICAL: {$component->component_name} pada {$vehicle->plate_number} harus segera diganti (sisa {$kmRemaining} KM)",
        'warning' => "WARNING: {$component->component_name} pada {$vehicle->plate_number} akan segera perlu diganti (sisa {$kmRemaining} KM)",
        'low' => "INFO: {$component->component_name} pada {$vehicle->plate_number} akan perlu diganti dalam waktu dekat (sisa {$kmRemaining} KM)"
    ];
    
    return $messages[$type] ?? $messages['low'];
}
```

---

### 2.2 Alert Escalation Rules

**Escalation Matrix**:

| Alert Type | Initial | After 3 Days | After 7 Days | After 14 Days |
|-----------|---------|--------------|--------------|---------------|
| **Overdue** | Email + Push | Email + SMS | Email + SMS + Call | Escalate to Manager |
| **Critical** | Email + Push | Email + SMS | Email + SMS | Escalate to Manager |
| **Warning** | Email | Email + Push | Email + Push | Email + SMS |
| **Low** | Email | Email | Email + Push | Email + Push |

**Implementation**:
```php
// Service: MaintenanceAlertService.php
public function escalateAlerts()
{
    $alerts = MaintenanceAlert::where('status', 'active')
        ->where('acknowledged_at', null)
        ->get();
    
    foreach ($alerts as $alert) {
        $daysSinceTriggered = $alert->triggered_at->diffInDays(now());
        
        if ($daysSinceTriggered >= 14) {
            $this->escalateToManager($alert);
        } elseif ($daysSinceTriggered >= 7) {
            $this->sendEscalatedNotification($alert, 'high');
        } elseif ($daysSinceTriggered >= 3) {
            $this->sendEscalatedNotification($alert, 'medium');
        }
    }
}
```

---

### 2.3 Alert Acknowledgment Flow

**Flow**:
```
Admin Views Alert → Click "Acknowledge" → 
Update Alert:
  - status = 'acknowledged'
  - acknowledged_at = now()
  - acknowledged_by = admin_id
→ Optional: Create Schedule → Success
```

**Implementation**:
```php
// Controller: MaintenanceAlertController.php
public function acknowledge(MaintenanceAlert $alert)
{
    $alert->acknowledge(auth()->user());
    
    return response()->json([
        'status' => 'success',
        'message' => 'Alert acknowledged',
        'data' => [
            'alert' => $alert->fresh()
        ]
    ]);
}

// Model: MaintenanceAlert.php
public function acknowledge(User $user): void
{
    $this->update([
        'status' => 'acknowledged',
        'acknowledged_at' => now(),
        'acknowledged_by' => $user->id,
    ]);
    
    // Optional: Auto-create schedule
    if ($this->alert_type === 'overdue' || $this->alert_type === 'critical') {
        $this->autoCreateSchedule();
    }
}
```

---

### 2.4 Alert Resolution Process

**Flow**:
```
Maintenance Completed → System Auto-Resolve Alert → 
Update Alert:
  - status = 'resolved'
  - resolved_at = now()
→ Send Notification → Done
```

**Manual Resolution**:
```php
// Controller: MaintenanceAlertController.php
public function resolve(MaintenanceAlert $alert, Request $request)
{
    $validated = $request->validate([
        'resolution_notes' => 'nullable|string'
    ]);
    
    $alert->resolve();
    
    if ($validated['resolution_notes']) {
        $alert->update(['notes' => $validated['resolution_notes']]);
    }
    
    return response()->json([
        'status' => 'success',
        'message' => 'Alert resolved'
    ]);
}
```

---

## 3. SCHEDULE MANAGEMENT WORKFLOW

### 3.1 Auto-Generate Schedules

**Trigger**: Cron job daily at 01:00

**Flow**:
```
Cron Job Start → Get Components with Alerts → 
For Each Component:
  - Check if schedule already exists
  - If not exists:
    - Calculate optimal date
    - Estimate cost
    - Create schedule
    - Assign priority
→ Send Notification to Admin → Done
```

**Implementation**:
```php
// Command: GenerateMaintenanceSchedules.php
public function handle()
{
    $components = VehicleComponent::with('vehicle', 'maintenanceSchedules')
        ->needsMaintenance()
        ->get();
    
    foreach ($components as $component) {
        // Check if schedule already exists
        $hasActiveSchedule = $component->maintenanceSchedules()
            ->whereIn('status', ['pending', 'scheduled'])
            ->exists();
        
        if (!$hasActiveSchedule) {
            $this->createSchedule($component);
        }
    }
    
    $this->info('Maintenance schedules generated');
}

private function createSchedule(VehicleComponent $component)
{
    $scheduledDate = $this->calculateOptimalDate($component);
    $priority = $this->determinePriority($component);
    
    MaintenanceSchedule::create([
        'vehicle_id' => $component->vehicle_id,
        'component_id' => $component->id,
        'scheduled_date' => $scheduledDate,
        'scheduled_km' => $component->next_replacement_km,
        'type' => 'preventive',
        'priority' => $priority,
        'status' => 'pending',
        'estimated_cost' => $component->cost_per_replacement,
        'notes' => "Auto-generated schedule for {$component->component_name}"
    ]);
}

private function calculateOptimalDate(VehicleComponent $component): Carbon
{
    $kmRemaining = $component->km_remaining;
    $avgKmPerDay = $this->calculateAverageKmPerDay($component->vehicle);
    
    if ($avgKmPerDay > 0) {
        $daysUntilDue = ceil($kmRemaining / $avgKmPerDay);
        
        // Schedule 3 days before due date
        return now()->addDays(max(1, $daysUntilDue - 3));
    }
    
    // Fallback: schedule in 7 days
    return now()->addDays(7);
}

private function determinePriority(VehicleComponent $component): string
{
    $kmRemaining = $component->km_remaining;
    
    if ($kmRemaining <= 0) {
        return 'urgent';
    } elseif ($kmRemaining <= $component->critical_threshold_km) {
        return 'high';
    } elseif ($kmRemaining <= $component->warning_threshold_km) {
        return 'medium';
    }
    
    return 'low';
}
```

---

### 3.2 Manual Schedule Creation

**Flow**:
```
Admin → Maintenance Calendar → Click Date → 
Fill Schedule Form → Validation → 
Save Schedule → Send Notification → Success
```

**Form Fields**:
```
Required:
- Vehicle (dropdown)
- Component (dropdown - filtered by vehicle)
- Scheduled Date
- Type (preventive / corrective / inspection)
- Priority (urgent / high / medium / low)
- Estimated Cost

Optional:
- Workshop Name
- Notes
```

---

### 3.3 Schedule Modification

**Allowed Modifications**:
- Change scheduled date
- Change workshop
- Update estimated cost
- Add notes
- Change priority

**Not Allowed**:
- Change vehicle
- Change component (must create new schedule)

**Implementation**:
```php
// Controller: MaintenanceScheduleController.php
public function update(MaintenanceSchedule $schedule, Request $request)
{
    // Only allow modification if not completed
    if ($schedule->status === 'completed') {
        return response()->json([
            'status' => 'error',
            'message' => 'Cannot modify completed schedule'
        ], 400);
    }
    
    $validated = $request->validate([
        'scheduled_date' => 'required|date|after:today',
        'workshop_name' => 'nullable|string',
        'estimated_cost' => 'required|numeric|min:0',
        'priority' => 'required|in:urgent,high,medium,low',
        'notes' => 'nullable|string'
    ]);
    
    $schedule->update($validated);
    
    // Send notification about schedule change
    $this->notifyScheduleChange($schedule);
    
    return response()->json([
        'status' => 'success',
        'message' => 'Schedule updated successfully'
    ]);
}
```

---

### 3.4 Schedule Completion Process

**Flow**:
```
Admin → View Schedule → Click "Complete" → 
Fill Completion Form:
  - Actual Cost
  - Completion Notes
  - Upload Invoice (optional)
→ Validation → 
Update Schedule (status = completed) → 
Update Component (reset replacement cycle) → 
Resolve Alerts → 
Log to History → 
Send Notification → Success
```

**Completion Form**:
```php
// Request validation
$validated = $request->validate([
    'actual_cost' => 'required|numeric|min:0',
    'notes' => 'nullable|string',
    'invoice_file' => 'nullable|file|mimes:pdf,jpg,png|max:5120'
]);
```

---

## 4. WORKSHOP INTEGRATION WORKFLOW

### 4.1 Assign Maintenance to Workshop

**Flow** (Future Feature):
```
Admin → Select Schedule → Click "Assign to Workshop" → 
Select Workshop from List → 
System Sends Notification to Workshop → 
Workshop Accepts/Rejects → 
If Accepted:
  - Update schedule status = 'assigned'
  - Send confirmation to admin
If Rejected:
  - Admin selects another workshop
```

---

### 4.2 Workshop Progress Tracking

**Flow** (Future Feature):
```
Workshop Updates Progress:
  - Started (status = 'in_progress')
  - Parts Ordered (status = 'waiting_parts')
  - Work in Progress (status = 'in_progress')
  - Quality Check (status = 'quality_check')
  - Completed (status = 'completed')

Admin can view real-time progress
```

---

## 5. REPORTING & ANALYTICS WORKFLOW

### 5.1 Daily Maintenance Report

**Generated**: Every day at 06:00 AM

**Content**:
- Overdue maintenance count
- Critical alerts count
- Scheduled maintenance today
- Completed maintenance yesterday
- Total cost yesterday

**Recipients**: All admins

---

### 5.2 Monthly Compliance Report

**Generated**: 1st day of each month

**Content**:
- Maintenance compliance rate
- Cost analysis
- Vehicle health trends
- Top issues
- Recommendations

---

### 5.3 Cost Analysis Report

**On-Demand Report**

**Filters**:
- Date range
- Vehicle
- Component category
- Workshop

**Metrics**:
- Total cost
- Cost per vehicle
- Cost per KM
- Budget variance
- Cost trends

---

## 6. EMERGENCY MAINTENANCE WORKFLOW

### 6.1 Emergency Breakdown

**Trigger**: Driver reports emergency

**Flow**:
```
Driver → Submit Emergency Report → 
System Creates Emergency Alert → 
Notify Admin (SMS + Push) → 
Admin Dispatches Help → 
Create Emergency Schedule → 
Track Resolution → 
Complete & Log → Done
```

**Priority**: URGENT (bypass normal scheduling)

---

## 📊 WORKFLOW METRICS

### Key Performance Indicators

| Metric | Target | Current |
|--------|--------|---------|
| Alert Response Time | < 24 hours | - |
| Schedule Completion Rate | > 95% | - |
| Maintenance Compliance | > 90% | - |
| Average Cost per Maintenance | < Rp 500,000 | - |
| Emergency Breakdown Rate | < 5% | - |

---

## 🔄 CONTINUOUS IMPROVEMENT

### Workflow Optimization Areas
1. **Automation**: Increase auto-scheduling accuracy
2. **Prediction**: Improve optimal date calculation
3. **Integration**: Workshop portal integration
4. **Notification**: Multi-channel notification system
5. **Analytics**: Real-time dashboard updates

---

**Document Version**: 1.0  
**Last Updated**: 2026-05-16  
**Next Review**: 2026-06-16  
**Owner**: Technical Team

---

**Related Documents:**
- [10. Preventive Maintenance Strategy](./10_PREVENTIVE_MAINTENANCE_STRATEGY.md)
- [12. Component Categories Guide](./12_COMPONENT_CATEGORIES_GUIDE.md)
- [13. Maintenance API](./13_MAINTENANCE_API.md)
- [14. Health Score Algorithm](./14_HEALTH_SCORE_ALGORITHM.md)
