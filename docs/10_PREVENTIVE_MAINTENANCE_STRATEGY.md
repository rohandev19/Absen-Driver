# 10. PREVENTIVE MAINTENANCE STRATEGY

> **Fokus**: Mencegah kerusakan kendaraan sebelum terjadi melalui maintenance proaktif berbasis data

---

## 📋 TABLE OF CONTENTS

1. [Overview](#overview)
2. [Current State Analysis](#current-state-analysis)
3. [Preventive Maintenance Framework](#preventive-maintenance-framework)
4. [Implementation Strategy](#implementation-strategy)
5. [Maintenance Types](#maintenance-types)
6. [Health Scoring System](#health-scoring-system)
7. [Alert Levels](#alert-levels)
8. [Cost-Benefit Analysis](#cost-benefit-analysis)

---

## 1. OVERVIEW

### 1.1 Tujuan Preventive Maintenance

**Primary Goals:**
- ✅ Mencegah breakdown kendaraan di jalan
- ✅ Memperpanjang umur kendaraan
- ✅ Mengurangi biaya perbaikan darurat
- ✅ Meningkatkan keselamatan driver
- ✅ Optimasi biaya operasional

**Success Metrics:**
- Reduce unplanned downtime by **30%**
- Increase vehicle lifespan by **25%**
- Reduce emergency repair costs by **40%**
- Achieve **95%** maintenance compliance rate

### 1.2 Prinsip Dasar

```
┌─────────────────────────────────────────────────────────┐
│  PREVENTIVE MAINTENANCE PYRAMID                         │
├─────────────────────────────────────────────────────────┤
│                                                          │
│              ▲ PREDICTIVE (AI/ML)                       │
│             ███  - Failure prediction                   │
│            █████  - Pattern recognition                 │
│           ███████                                        │
│          ▲ CONDITION-BASED                              │
│         ███████████  - Real-time monitoring             │
│        █████████████  - Sensor data                     │
│       ███████████████                                    │
│      ▲ TIME-BASED                                       │
│     █████████████████████  - Scheduled maintenance      │
│    ███████████████████████  - KM-based intervals        │
│   █████████████████████████                             │
│  ▲ REACTIVE (Current State)                             │
│ █████████████████████████████  - Fix when broken        │
│███████████████████████████████                          │
└─────────────────────────────────────────────────────────┘
```

---

## 2. CURRENT STATE ANALYSIS

### 2.1 Existing Features

**✅ Already Implemented:**
```php
// Vehicle Model
- current_km: Tracking odometer
- service_interval_km: Maintenance interval
- last_service_km: Last service record
- pajak_stnk_berlaku_sampai: Tax expiry
- kir_berlaku_sampai: Inspection expiry

// Attendance Model (Daily Checks)
- check_ban: Tire condition
- check_lampu: Light condition
- check_rem: Brake condition
- speedo_awal/akhir: KM tracking

// Maintenance Log
- service_date: Service history
- km_at_service: Service mileage
- description: Service details
```

**❌ Missing Critical Features:**
- ⚠️ Automated maintenance scheduling
- ⚠️ Predictive failure detection
- ⚠️ Multi-level alert system
- ⚠️ Component-level tracking
- ⚠️ Maintenance cost tracking
- ⚠️ Vendor management
- ⚠️ Parts inventory integration
- ⚠️ Mobile notification system

### 2.2 Current Pain Points

| Problem | Impact | Priority |
|---------|--------|----------|
| Manual maintenance scheduling | High admin workload | 🔴 Critical |
| No early warning system | Unexpected breakdowns | 🔴 Critical |
| Limited health monitoring | Reactive maintenance only | 🔴 Critical |
| No cost tracking | Budget overruns | 🟡 High |
| No vendor management | Inconsistent service quality | 🟡 High |
| No parts tracking | Delays in repairs | 🟢 Medium |

---

## 3. PREVENTIVE MAINTENANCE FRAMEWORK

### 3.1 Three-Tier Maintenance System

```
┌──────────────────────────────────────────────────────────┐
│  TIER 1: DAILY CHECKS (Driver Responsibility)           │
├──────────────────────────────────────────────────────────┤
│  • Visual inspection (ban, lampu, rem)                   │
│  • Fluid levels (oli, air radiator, wiper)              │
│  • Dashboard warning lights                              │
│  • Unusual sounds/vibrations                             │
│  • Frequency: Every check-in/check-out                   │
└──────────────────────────────────────────────────────────┘
                          ↓
┌──────────────────────────────────────────────────────────┐
│  TIER 2: SCHEDULED MAINTENANCE (Time/KM Based)          │
├──────────────────────────────────────────────────────────┤
│  • Oil change: Every 5,000 KM                           │
│  • Tire rotation: Every 10,000 KM                       │
│  • Brake inspection: Every 15,000 KM                    │
│  • Major service: Every 20,000 KM                       │
│  • Frequency: Based on manufacturer guidelines          │
└──────────────────────────────────────────────────────────┘
                          ↓
┌──────────────────────────────────────────────────────────┐
│  TIER 3: PREDICTIVE MAINTENANCE (Condition Based)       │
├──────────────────────────────────────────────────────────┤
│  • Pattern analysis from daily checks                    │
│  • Failure prediction using ML                          │
│  • Cost optimization                                     │
│  • Frequency: Continuous monitoring                      │
└──────────────────────────────────────────────────────────┘
```

### 3.2 Maintenance Components Tracking

**Critical Components to Monitor:**

```php
// Proposed: vehicle_components table
[
    'component_name' => 'Engine Oil',
    'category' => 'Fluids',
    'replacement_interval_km' => 5000,
    'replacement_interval_days' => 180,
    'last_replacement_km' => 45000,
    'last_replacement_date' => '2026-03-15',
    'cost_per_replacement' => 350000,
    'warning_threshold_km' => 500, // Alert 500 KM before due
    'critical_threshold_km' => 100,
]

// Component Categories:
1. Fluids (Oli, Coolant, Brake Fluid, Power Steering)
2. Filters (Oil Filter, Air Filter, Fuel Filter, Cabin Filter)
3. Brakes (Brake Pads, Brake Discs, Brake Fluid)
4. Tires (Front Left, Front Right, Rear Left, Rear Right, Spare)
5. Battery (Battery Health, Alternator)
6. Lights (Headlights, Tail Lights, Turn Signals)
7. Belts & Hoses (Timing Belt, Serpentine Belt, Radiator Hoses)
8. Suspension (Shock Absorbers, Struts, Ball Joints)
9. Engine (Spark Plugs, Ignition Coils, Fuel Injectors)
10. Transmission (Transmission Fluid, Clutch)
```

---

## 4. IMPLEMENTATION STRATEGY

### 4.1 Phase 1: Foundation (Month 1-2)

**Goal**: Establish basic preventive maintenance infrastructure

**Tasks:**
1. ✅ Create `vehicle_components` table
2. ✅ Create `maintenance_schedules` table
3. ✅ Create `maintenance_alerts` table
4. ✅ Implement component tracking CRUD
5. ✅ Build maintenance calendar with auto-scheduling
6. ✅ Create alert generation system

**Database Schema:**

```sql
-- vehicle_components
CREATE TABLE vehicle_components (
    id BIGINT PRIMARY KEY,
    vehicle_id BIGINT,
    component_name VARCHAR(100),
    category VARCHAR(50),
    replacement_interval_km INT,
    replacement_interval_days INT,
    last_replacement_km INT,
    last_replacement_date DATE,
    next_replacement_km INT, -- Calculated
    next_replacement_date DATE, -- Calculated
    cost_per_replacement DECIMAL(10,2),
    warning_threshold_km INT DEFAULT 500,
    critical_threshold_km INT DEFAULT 100,
    status ENUM('healthy', 'warning', 'critical', 'overdue'),
    created_at TIMESTAMP,
    updated_at TIMESTAMP,
    FOREIGN KEY (vehicle_id) REFERENCES vehicles(id)
);

-- maintenance_schedules
CREATE TABLE maintenance_schedules (
    id BIGINT PRIMARY KEY,
    vehicle_id BIGINT,
    component_id BIGINT,
    scheduled_date DATE,
    scheduled_km INT,
    type ENUM('preventive', 'corrective', 'predictive'),
    priority ENUM('low', 'medium', 'high', 'critical'),
    status ENUM('pending', 'scheduled', 'in_progress', 'completed', 'cancelled'),
    estimated_cost DECIMAL(10,2),
    actual_cost DECIMAL(10,2),
    workshop_id BIGINT,
    notes TEXT,
    completed_at TIMESTAMP,
    created_at TIMESTAMP,
    updated_at TIMESTAMP
);

-- maintenance_alerts
CREATE TABLE maintenance_alerts (
    id BIGINT PRIMARY KEY,
    vehicle_id BIGINT,
    component_id BIGINT,
    alert_type ENUM('warning', 'critical', 'overdue'),
    message TEXT,
    triggered_at TIMESTAMP,
    acknowledged_at TIMESTAMP,
    acknowledged_by BIGINT,
    resolved_at TIMESTAMP,
    status ENUM('active', 'acknowledged', 'resolved', 'dismissed'),
    created_at TIMESTAMP
);
```

### 4.2 Phase 2: Automation (Month 3-4)

**Goal**: Automate scheduling and notifications

**Tasks:**
1. ✅ Build Laravel Command untuk auto-generate schedules
2. ✅ Implement notification system (Email, SMS, Push)
3. ✅ Create dashboard widget untuk upcoming maintenance
4. ✅ Build mobile app notification
5. ✅ Implement reminder escalation (7 days, 3 days, 1 day before)

**Laravel Command Example:**

```php
// app/Console/Commands/GenerateMaintenanceSchedules.php
class GenerateMaintenanceSchedules extends Command
{
    protected $signature = 'maintenance:generate-schedules';
    
    public function handle()
    {
        $vehicles = Vehicle::with('components')->get();
        
        foreach ($vehicles as $vehicle) {
            foreach ($vehicle->components as $component) {
                // Calculate next maintenance based on KM
                $kmRemaining = $component->next_replacement_km - $vehicle->current_km;
                
                // Calculate next maintenance based on Date
                $daysRemaining = Carbon::parse($component->next_replacement_date)
                    ->diffInDays(Carbon::now());
                
                // Determine which comes first
                $triggerBy = ($kmRemaining < $daysRemaining * 50) ? 'km' : 'date';
                
                // Generate schedule if within threshold
                if ($kmRemaining <= $component->warning_threshold_km) {
                    $this->createMaintenanceSchedule($vehicle, $component, 'km');
                }
                
                if ($daysRemaining <= 30) {
                    $this->createMaintenanceSchedule($vehicle, $component, 'date');
                }
            }
        }
    }
}
```

### 4.3 Phase 3: Intelligence (Month 5-6)

**Goal**: Add predictive analytics

**Tasks:**
1. ✅ Collect historical failure data
2. ✅ Build ML model untuk failure prediction
3. ✅ Implement pattern recognition
4. ✅ Create cost optimization algorithm
5. ✅ Build predictive dashboard

---

## 5. MAINTENANCE TYPES

### 5.1 Preventive Maintenance Matrix

| Component | Interval (KM) | Interval (Days) | Cost (IDR) | Priority |
|-----------|---------------|-----------------|------------|----------|
| **Engine Oil** | 5,000 | 180 | 350,000 | 🔴 Critical |
| **Oil Filter** | 5,000 | 180 | 75,000 | 🔴 Critical |
| **Air Filter** | 10,000 | 365 | 150,000 | 🟡 High |
| **Fuel Filter** | 20,000 | 730 | 200,000 | 🟡 High |
| **Brake Pads** | 30,000 | - | 800,000 | 🔴 Critical |
| **Brake Fluid** | 20,000 | 730 | 150,000 | 🔴 Critical |
| **Coolant** | 40,000 | 730 | 250,000 | 🟡 High |
| **Transmission Fluid** | 40,000 | 1095 | 500,000 | 🟡 High |
| **Spark Plugs** | 30,000 | - | 400,000 | 🟡 High |
| **Timing Belt** | 80,000 | 1825 | 2,500,000 | 🔴 Critical |
| **Battery** | - | 1095 | 1,200,000 | 🟡 High |
| **Tires** | 50,000 | - | 3,000,000 | 🔴 Critical |

### 5.2 Maintenance Workflow

```
┌─────────────────────────────────────────────────────────┐
│  MAINTENANCE WORKFLOW                                    │
└─────────────────────────────────────────────────────────┘

1. DETECTION
   ├─ Automated: System detects component due for maintenance
   ├─ Manual: Driver reports issue during daily check
   └─ Predictive: ML model predicts potential failure

2. ALERT GENERATION
   ├─ Create maintenance_alert record
   ├─ Calculate priority (warning/critical/overdue)
   └─ Send notification to admin & driver

3. SCHEDULING
   ├─ Admin reviews alert
   ├─ Create maintenance_schedule
   ├─ Assign workshop & technician
   └─ Notify driver of scheduled date

4. EXECUTION
   ├─ Vehicle taken to workshop
   ├─ Maintenance performed
   ├─ Update component records
   └─ Record actual cost

5. VERIFICATION
   ├─ Admin verifies completion
   ├─ Update vehicle health score
   ├─ Generate next schedule
   └─ Close alert

6. ANALYSIS
   ├─ Track maintenance costs
   ├─ Analyze failure patterns
   ├─ Update ML model
   └─ Generate reports
```

---

## 6. HEALTH SCORING SYSTEM

### 6.1 Vehicle Health Score Calculation

**Formula:**
```php
Health Score = (
    Component_Health_Average * 0.40 +
    Maintenance_Compliance * 0.30 +
    Daily_Check_Score * 0.20 +
    Age_Factor * 0.10
) * 100

Where:
- Component_Health_Average: Average health of all components (0-1)
- Maintenance_Compliance: % of scheduled maintenance completed on time
- Daily_Check_Score: Average score from driver daily checks
- Age_Factor: 1 - (vehicle_age_years / expected_lifespan_years)
```

**Implementation:**

```php
// app/Services/VehicleHealthService.php
class VehicleHealthService
{
    public function calculateHealthScore(Vehicle $vehicle): float
    {
        $componentHealth = $this->getComponentHealthAverage($vehicle);
        $maintenanceCompliance = $this->getMaintenanceCompliance($vehicle);
        $dailyCheckScore = $this->getDailyCheckScore($vehicle);
        $ageFactor = $this->getAgeFactor($vehicle);
        
        $score = (
            $componentHealth * 0.40 +
            $maintenanceCompliance * 0.30 +
            $dailyCheckScore * 0.20 +
            $ageFactor * 0.10
        ) * 100;
        
        return round($score, 2);
    }
    
    private function getComponentHealthAverage(Vehicle $vehicle): float
    {
        $components = $vehicle->components;
        
        if ($components->isEmpty()) {
            return 1.0;
        }
        
        $totalHealth = $components->sum(function ($component) {
            return $this->calculateComponentHealth($component);
        });
        
        return $totalHealth / $components->count();
    }
    
    private function calculateComponentHealth(VehicleComponent $component): float
    {
        $kmRemaining = $component->next_replacement_km - $component->vehicle->current_km;
        $kmInterval = $component->replacement_interval_km;
        
        if ($kmRemaining <= 0) {
            return 0.0; // Overdue
        }
        
        if ($kmRemaining <= $component->critical_threshold_km) {
            return 0.2; // Critical
        }
        
        if ($kmRemaining <= $component->warning_threshold_km) {
            return 0.5; // Warning
        }
        
        // Healthy: Linear interpolation
        return min(1.0, $kmRemaining / $kmInterval);
    }
}
```

### 6.2 Health Score Interpretation

| Score Range | Status | Color | Action Required |
|-------------|--------|-------|-----------------|
| 90-100 | Excellent | 🟢 Green | Continue monitoring |
| 75-89 | Good | 🟢 Green | Schedule routine maintenance |
| 60-74 | Fair | 🟡 Yellow | Review maintenance schedule |
| 40-59 | Poor | 🟠 Orange | Immediate attention needed |
| 0-39 | Critical | 🔴 Red | Stop operations, urgent repair |

---

## 7. ALERT LEVELS

### 7.1 Alert Priority Matrix

```
┌─────────────────────────────────────────────────────────┐
│  ALERT PRIORITY MATRIX                                   │
├─────────────────────────────────────────────────────────┤
│                                                          │
│  CRITICAL (🔴)                                          │
│  ├─ Component overdue (past replacement date/km)        │
│  ├─ Safety-critical component in warning zone           │
│  ├─ Multiple components in warning zone                 │
│  └─ Action: Immediate maintenance required              │
│                                                          │
│  HIGH (🟠)                                              │
│  ├─ Component within critical threshold                 │
│  ├─ Scheduled maintenance due within 3 days             │
│  ├─ Driver reported issue during daily check            │
│  └─ Action: Schedule maintenance within 48 hours        │
│                                                          │
│  MEDIUM (🟡)                                            │
│  ├─ Component within warning threshold                  │
│  ├─ Scheduled maintenance due within 7 days             │
│  ├─ Minor issue reported                                │
│  └─ Action: Schedule maintenance within 1 week          │
│                                                          │
│  LOW (🟢)                                               │
│  ├─ Routine maintenance reminder                        │
│  ├─ Component health declining but not urgent           │
│  └─ Action: Plan maintenance within 2 weeks             │
│                                                          │
└─────────────────────────────────────────────────────────┘
```

### 7.2 Notification Channels

| Alert Level | Email | SMS | Push | Dashboard | Escalation |
|-------------|-------|-----|------|-----------|------------|
| 🔴 Critical | ✅ | ✅ | ✅ | ✅ | Immediate |
| 🟠 High | ✅ | ✅ | ✅ | ✅ | 24 hours |
| 🟡 Medium | ✅ | ❌ | ✅ | ✅ | 3 days |
| 🟢 Low | ✅ | ❌ | ❌ | ✅ | 7 days |

---

## 8. COST-BENEFIT ANALYSIS

### 8.1 Current State (Reactive Maintenance)

**Annual Costs (Per Vehicle):**
```
Emergency Repairs:        Rp 15,000,000
Downtime Cost:           Rp 10,000,000
Towing Services:         Rp  2,000,000
Lost Productivity:       Rp  8,000,000
─────────────────────────────────────
Total Annual Cost:       Rp 35,000,000
```

### 8.2 Target State (Preventive Maintenance)

**Annual Costs (Per Vehicle):**
```
Scheduled Maintenance:   Rp 12,000,000
System Cost:            Rp  1,000,000
Training:               Rp    500,000
Monitoring:             Rp    500,000
─────────────────────────────────────
Total Annual Cost:       Rp 14,000,000

SAVINGS:                 Rp 21,000,000 (60% reduction)
```

### 8.3 ROI Calculation

**For Fleet of 50 Vehicles:**
```
Implementation Cost:     Rp 50,000,000 (one-time)
Annual Savings:          Rp 1,050,000,000
ROI Period:              0.57 months (17 days)
5-Year Savings:          Rp 5,250,000,000
```

---

## 9. SUCCESS METRICS

### 9.1 Key Performance Indicators (KPIs)

| Metric | Current | Target | Measurement |
|--------|---------|--------|-------------|
| **Maintenance Compliance Rate** | 60% | 95% | % of scheduled maintenance completed on time |
| **Unplanned Downtime** | 15 days/year | 5 days/year | Days vehicle unavailable due to breakdown |
| **Mean Time Between Failures** | 3 months | 12 months | Average time between breakdowns |
| **Maintenance Cost per KM** | Rp 500 | Rp 300 | Total maintenance cost / total KM |
| **Vehicle Availability** | 80% | 95% | % of time vehicle is operational |
| **Early Detection Rate** | 20% | 85% | % of issues detected before failure |

### 9.2 Monitoring Dashboard

**Real-time Metrics:**
- Total vehicles in fleet
- Vehicles due for maintenance (next 7 days)
- Active critical alerts
- Maintenance compliance rate (current month)
- Average vehicle health score
- Total maintenance cost (current month)

---

## 10. NEXT STEPS

### Immediate Actions (Week 1-2)
1. ✅ Review and approve this strategy document
2. ✅ Allocate budget for implementation
3. ✅ Assign development team
4. ✅ Create project timeline
5. ✅ Setup development environment

### Short-term (Month 1-2)
1. ✅ Implement Phase 1 (Foundation)
2. ✅ Create database migrations
3. ✅ Build component tracking system
4. ✅ Develop maintenance scheduling
5. ✅ Test with pilot vehicles (5-10 units)

### Medium-term (Month 3-6)
1. ✅ Implement Phase 2 (Automation)
2. ✅ Roll out to full fleet
3. ✅ Train admin staff
4. ✅ Collect data for ML model
5. ✅ Begin Phase 3 (Intelligence)

### Long-term (Month 7-12)
1. ✅ Full predictive maintenance implementation
2. ✅ IoT sensor integration
3. ✅ Advanced analytics
4. ✅ Continuous improvement

---

**Document Version**: 1.0  
**Last Updated**: 2026-05-14  
**Next Review**: 2026-08-14  
**Owner**: Fleet Management Team

---

**Related Documents:**
- [11. Vehicle Health Monitoring](./11_VEHICLE_HEALTH_MONITORING.md)
- [12. Maintenance Scheduling System](./12_MAINTENANCE_SCHEDULING.md)
- [13. Predictive Analytics](./13_PREDICTIVE_ANALYTICS.md)
- [23. Roadmap Fitur Preventive Maintenance](./23_ROADMAP_PREVENTIVE_MAINTENANCE.md)
