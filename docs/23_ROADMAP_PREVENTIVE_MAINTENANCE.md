# 23. ROADMAP FITUR PREVENTIVE MAINTENANCE

> **Roadmap implementasi fitur-fitur preventive maintenance untuk 12 bulan ke depan**

---

## 📅 TIMELINE OVERVIEW

```
2026
├─ Q2 (Apr-Jun): Foundation & Core Features
├─ Q3 (Jul-Sep): Automation & Intelligence
├─ Q4 (Oct-Dec): Advanced Features & IoT
└─ 2027 Q1 (Jan-Mar): AI/ML & Optimization
```

---

## 🎯 PHASE 1: FOUNDATION (Month 1-3)

### **Priority: 🔴 CRITICAL**

### 1.1 Component Tracking System

**Status**: 🟡 To Build  
**Effort**: 3 weeks  
**Dependencies**: None

**Features:**
- ✅ CRUD untuk vehicle components
- ✅ Component categories (10 categories)
- ✅ Replacement interval tracking (KM & Date based)
- ✅ Cost tracking per component
- ✅ Component health status calculation

**Database Tables:**
```sql
- vehicle_components
- component_categories
- component_replacement_history
```

**API Endpoints:**
```
POST   /api/admin/vehicles/{id}/components
GET    /api/admin/vehicles/{id}/components
PUT    /api/admin/components/{id}
DELETE /api/admin/components/{id}
GET    /api/admin/components/{id}/history
```

**UI Screens:**
- Admin: Component management page
- Admin: Component detail & history
- Mobile: View component status (read-only)

---

### 1.2 Maintenance Scheduling System

**Status**: 🟡 To Build  
**Effort**: 4 weeks  
**Dependencies**: Component Tracking

**Features:**
- ✅ Auto-generate maintenance schedules
- ✅ Manual schedule creation
- ✅ Schedule status tracking (pending, scheduled, completed)
- ✅ Workshop assignment
- ✅ Cost estimation & actual cost tracking
- ✅ Calendar view integration

**Database Tables:**
```sql
- maintenance_schedules
- workshops
- maintenance_schedule_items
```

**Laravel Commands:**
```bash
php artisan maintenance:generate-schedules
php artisan maintenance:send-reminders
php artisan maintenance:check-overdue
```

**API Endpoints:**
```
GET    /api/admin/maintenance/schedules
POST   /api/admin/maintenance/schedules
PUT    /api/admin/maintenance/schedules/{id}
DELETE /api/admin/maintenance/schedules/{id}
GET    /api/admin/maintenance/calendar
POST   /api/admin/maintenance/schedules/{id}/complete
```

**UI Screens:**
- Admin: Maintenance calendar (FullCalendar.js)
- Admin: Schedule list with filters
- Admin: Schedule detail & completion form
- Mobile: Upcoming maintenance notifications

---

### 1.3 Alert & Notification System

**Status**: 🟡 To Build  
**Effort**: 3 weeks  
**Dependencies**: Component Tracking, Scheduling

**Features:**
- ✅ Multi-level alerts (warning, critical, overdue)
- ✅ Alert generation based on thresholds
- ✅ Alert acknowledgment workflow
- ✅ Email notifications
- ✅ Push notifications (mobile)
- ✅ SMS notifications (optional)
- ✅ Alert escalation

**Database Tables:**
```sql
- maintenance_alerts
- notification_logs
- notification_preferences
```

**Notification Channels:**
```php
- Mail: Laravel Mail
- Push: Firebase Cloud Messaging (FCM)
- SMS: Twilio / Vonage (optional)
- Database: In-app notifications
```

**API Endpoints:**
```
GET    /api/admin/alerts
GET    /api/admin/alerts/active
POST   /api/admin/alerts/{id}/acknowledge
POST   /api/admin/alerts/{id}/resolve
GET    /api/driver/alerts
```

**UI Screens:**
- Admin: Alert dashboard widget
- Admin: Alert list with filters
- Admin: Alert detail & action buttons
- Mobile: Alert notifications
- Mobile: Alert history

---

### 1.4 Enhanced Daily Check System

**Status**: 🟡 To Build  
**Effort**: 2 weeks  
**Dependencies**: Component Tracking

**Features:**
- ✅ Expanded checklist (15+ items)
- ✅ Photo evidence for each check
- ✅ Severity rating (OK, Minor Issue, Major Issue)
- ✅ Auto-generate alerts from daily checks
- ✅ Trend analysis of daily check results

**Enhanced Checklist:**
```
1. Tires (Pressure, Tread Depth, Damage)
2. Brakes (Pedal Feel, Noise, Performance)
3. Lights (All lights functional)
4. Fluids (Oil, Coolant, Brake Fluid, Wiper Fluid)
5. Battery (Voltage, Corrosion)
6. Belts & Hoses (Visual inspection)
7. Wipers (Condition, Performance)
8. Horn (Functional)
9. Mirrors (Condition, Adjustment)
10. Seat Belts (Functional)
11. Dashboard Warnings (Any warning lights)
12. Unusual Sounds (Engine, Transmission, Suspension)
13. Unusual Vibrations (Steering, Brakes)
14. Unusual Smells (Burning, Fuel, Coolant)
15. Overall Vehicle Cleanliness
```

**API Endpoints:**
```
POST   /api/driver/daily-check
GET    /api/driver/daily-check/history
GET    /api/admin/daily-checks
GET    /api/admin/daily-checks/trends
```

**UI Screens:**
- Mobile: Enhanced daily check form
- Mobile: Photo capture for each item
- Admin: Daily check results dashboard
- Admin: Trend analysis charts

---

## 🚀 PHASE 2: AUTOMATION (Month 4-6)

### **Priority: 🟠 HIGH**

### 2.1 Automated Maintenance Scheduler

**Status**: 🟡 To Build  
**Effort**: 3 weeks  
**Dependencies**: Phase 1 complete

**Features:**
- ✅ Smart scheduling algorithm
- ✅ Conflict detection (vehicle already scheduled)
- ✅ Workshop capacity management
- ✅ Driver availability check
- ✅ Optimal scheduling (minimize downtime)
- ✅ Batch scheduling for multiple vehicles

**Algorithm:**
```php
// Scheduling Priority Score
Priority Score = (
    Urgency * 0.40 +           // How overdue is the maintenance
    Safety_Impact * 0.30 +     // Safety-critical components
    Cost_Impact * 0.20 +       // Cost of delay
    Vehicle_Utilization * 0.10 // How often vehicle is used
)

// Schedule when:
- Priority Score > 0.7: Schedule within 24 hours
- Priority Score > 0.5: Schedule within 3 days
- Priority Score > 0.3: Schedule within 7 days
- Priority Score <= 0.3: Schedule within 14 days
```

**Laravel Commands:**
```bash
php artisan maintenance:auto-schedule --days=7
php artisan maintenance:optimize-schedule
php artisan maintenance:check-conflicts
```

---

### 2.2 Workshop Management System

**Status**: 🟡 To Build  
**Effort**: 4 weeks  
**Dependencies**: Scheduling System

**Features:**
- ✅ Workshop database (name, location, specialization)
- ✅ Workshop rating & reviews
- ✅ Service history per workshop
- ✅ Cost comparison
- ✅ Workshop capacity tracking
- ✅ Preferred workshop per vehicle/project
- ✅ Workshop performance metrics

**Database Tables:**
```sql
- workshops
- workshop_services
- workshop_ratings
- workshop_capacity
```

**API Endpoints:**
```
GET    /api/admin/workshops
POST   /api/admin/workshops
PUT    /api/admin/workshops/{id}
GET    /api/admin/workshops/{id}/performance
POST   /api/admin/workshops/{id}/rate
GET    /api/admin/workshops/compare
```

**UI Screens:**
- Admin: Workshop list
- Admin: Workshop detail & performance
- Admin: Workshop comparison tool
- Admin: Workshop rating form

---

### 2.3 Parts Inventory Integration

**Status**: 🟡 To Build  
**Effort**: 3 weeks  
**Dependencies**: Component Tracking

**Features:**
- ✅ Parts catalog
- ✅ Stock level tracking
- ✅ Low stock alerts
- ✅ Parts ordering workflow
- ✅ Parts cost tracking
- ✅ Supplier management
- ✅ Parts usage history

**Database Tables:**
```sql
- parts_catalog
- parts_inventory
- parts_orders
- suppliers
```

**API Endpoints:**
```
GET    /api/admin/parts
POST   /api/admin/parts
GET    /api/admin/parts/low-stock
POST   /api/admin/parts/order
GET    /api/admin/parts/{id}/usage-history
```

---

### 2.4 Cost Tracking & Budgeting

**Status**: 🟡 To Build  
**Effort**: 2 weeks  
**Dependencies**: Scheduling, Parts Inventory

**Features:**
- ✅ Maintenance cost per vehicle
- ✅ Cost per KM calculation
- ✅ Budget allocation per vehicle/project
- ✅ Budget vs actual tracking
- ✅ Cost forecasting
- ✅ ROI calculation

**Reports:**
- Monthly maintenance cost report
- Cost per vehicle comparison
- Budget variance report
- Cost trend analysis
- ROI dashboard

**API Endpoints:**
```
GET    /api/admin/costs/summary
GET    /api/admin/costs/per-vehicle
GET    /api/admin/costs/forecast
GET    /api/admin/costs/budget-variance
```

---

## 🧠 PHASE 3: INTELLIGENCE (Month 7-9)

### **Priority: 🟡 MEDIUM**

### 3.1 Predictive Maintenance (ML Model)

**Status**: 🟡 To Build  
**Effort**: 6 weeks  
**Dependencies**: 6 months of historical data

**Features:**
- ✅ Failure prediction model
- ✅ Pattern recognition from daily checks
- ✅ Anomaly detection
- ✅ Remaining useful life (RUL) estimation
- ✅ Maintenance recommendation engine

**ML Models:**
```python
# Model 1: Failure Prediction
- Algorithm: Random Forest / XGBoost
- Input Features:
  - Vehicle age
  - Total KM
  - Daily check scores (last 30 days)
  - Maintenance history
  - Component age
  - Usage patterns
- Output: Probability of failure (next 30 days)

# Model 2: Remaining Useful Life (RUL)
- Algorithm: LSTM / GRU
- Input: Time series of component health
- Output: Estimated KM/days until replacement

# Model 3: Anomaly Detection
- Algorithm: Isolation Forest
- Input: Daily check data
- Output: Anomaly score
```

**Implementation:**
```php
// Laravel Integration
- Python microservice for ML inference
- API endpoint: POST /api/ml/predict
- Scheduled job: php artisan ml:update-predictions
- Store predictions in database
```

**API Endpoints:**
```
POST   /api/ml/predict-failure
GET    /api/admin/predictions
GET    /api/admin/vehicles/{id}/rul
GET    /api/admin/anomalies
```

---

### 3.2 Advanced Analytics Dashboard

**Status**: 🟡 To Build  
**Effort**: 3 weeks  
**Dependencies**: Cost Tracking, ML Model

**Features:**
- ✅ Fleet health overview
- ✅ Maintenance trends
- ✅ Cost analysis
- ✅ Predictive insights
- ✅ Performance benchmarking
- ✅ Custom reports

**Dashboard Widgets:**
1. Fleet Health Score (Gauge chart)
2. Maintenance Compliance Rate (Progress bar)
3. Upcoming Maintenance (Timeline)
4. Cost Trends (Line chart)
5. Top Issues (Bar chart)
6. Vehicle Comparison (Radar chart)
7. Predictive Alerts (List)
8. ROI Metrics (Cards)

**Technologies:**
- Chart.js / ApexCharts
- Real-time updates via WebSocket
- Export to PDF/Excel

---

### 3.3 Mobile App Enhancements

**Status**: 🟡 To Build  
**Effort**: 4 weeks  
**Dependencies**: Phase 1 & 2 complete

**New Features:**
- ✅ Vehicle health score display
- ✅ Upcoming maintenance schedule
- ✅ Maintenance history
- ✅ Enhanced daily check with photos
- ✅ Push notifications for alerts
- ✅ Offline mode for daily checks
- ✅ QR code scanning for vehicle identification

**UI Screens:**
- Home: Vehicle health card
- Maintenance: Schedule & history
- Daily Check: Enhanced form
- Alerts: Notification center
- Profile: Driver performance metrics

---

## 🌐 PHASE 4: ADVANCED FEATURES (Month 10-12)

### **Priority: 🟢 LOW**

### 4.1 IoT Sensor Integration

**Status**: 🔵 Future  
**Effort**: 8 weeks  
**Dependencies**: Hardware procurement

**Sensors:**
- ✅ OBD-II reader (Engine diagnostics)
- ✅ GPS tracker (Location & usage)
- ✅ Fuel sensor (Fuel consumption)
- ✅ Temperature sensor (Engine temp)
- ✅ Tire pressure monitoring system (TPMS)
- ✅ Battery voltage monitor

**Data Collection:**
```
Real-time data stream:
- Engine RPM
- Speed
- Fuel level
- Coolant temperature
- Oil pressure
- Battery voltage
- Tire pressure (4 tires)
- GPS coordinates
- Diagnostic trouble codes (DTCs)
```

**Implementation:**
```
IoT Device → MQTT Broker → Laravel Queue → Database
                                ↓
                         Real-time Dashboard
                                ↓
                         ML Model Training
```

---

### 4.2 Driver Performance Scoring

**Status**: 🔵 Future  
**Effort**: 3 weeks  
**Dependencies**: IoT Integration

**Metrics:**
- ✅ Harsh braking events
- ✅ Rapid acceleration
- ✅ Speeding incidents
- ✅ Idle time
- ✅ Fuel efficiency
- ✅ Daily check compliance
- ✅ Maintenance compliance

**Scoring Formula:**
```php
Driver Score = (
    Safe_Driving * 0.30 +      // No harsh events
    Fuel_Efficiency * 0.20 +   // Optimal fuel consumption
    Daily_Check_Compliance * 0.20 +
    Maintenance_Compliance * 0.15 +
    Vehicle_Care * 0.15        // Cleanliness, damage reports
) * 100
```

**Gamification:**
- Leaderboard
- Badges & achievements
- Monthly rewards
- Performance improvement tips

---

### 4.3 Vendor Portal

**Status**: 🔵 Future  
**Effort**: 4 weeks  
**Dependencies**: Workshop Management

**Features:**
- ✅ Workshop login portal
- ✅ View assigned maintenance schedules
- ✅ Update maintenance status
- ✅ Upload service reports
- ✅ Invoice submission
- ✅ Performance dashboard

**Portal Sections:**
- Dashboard: Upcoming jobs
- Schedule: Calendar view
- Jobs: Job list & details
- Invoices: Invoice management
- Reports: Performance metrics

---

### 4.4 Mobile Mechanic Dispatch

**Status**: 🔵 Future  
**Effort**: 3 weeks  
**Dependencies**: Workshop Management

**Features:**
- ✅ On-site repair request
- ✅ Mechanic assignment
- ✅ Real-time tracking
- ✅ Service completion workflow
- ✅ Customer rating

**Use Cases:**
- Emergency breakdown
- Minor repairs at driver location
- Pre-delivery inspection
- Routine checks at parking lot

---

## 📊 FEATURE PRIORITY MATRIX

```
┌─────────────────────────────────────────────────────────┐
│  IMPACT vs EFFORT MATRIX                                 │
├─────────────────────────────────────────────────────────┤
│                                                          │
│  HIGH IMPACT                                             │
│  │                                                       │
│  │  [1.1]     [1.2]                    [3.1]           │
│  │  Component Scheduling               ML Model         │
│  │  Tracking                                            │
│  │                                                       │
│  │  [1.3]     [2.1]        [2.2]                       │
│  │  Alerts    Auto-Sched   Workshop                     │
│  │                                                       │
│  │  [1.4]     [2.3]        [2.4]       [3.2]           │
│  │  Daily     Parts        Cost        Analytics        │
│  │  Check     Inventory    Tracking                     │
│  │                                                       │
│  │            [3.3]        [4.1]       [4.2]           │
│  │            Mobile       IoT         Driver           │
│  │            App          Sensors     Scoring          │
│  │                                                       │
│  │                        [4.3]       [4.4]            │
│  │                        Vendor      Mobile            │
│  │                        Portal      Mechanic          │
│  │                                                       │
│  LOW IMPACT                                              │
│  └────────────────────────────────────────────────────  │
│     LOW EFFORT              HIGH EFFORT                  │
└─────────────────────────────────────────────────────────┘

Legend:
[1.x] = Phase 1 (Month 1-3)
[2.x] = Phase 2 (Month 4-6)
[3.x] = Phase 3 (Month 7-9)
[4.x] = Phase 4 (Month 10-12)
```

---

## 🎯 RECOMMENDED IMPLEMENTATION ORDER

### **MUST HAVE (Build First)**
1. ✅ Component Tracking System (1.1)
2. ✅ Maintenance Scheduling System (1.2)
3. ✅ Alert & Notification System (1.3)
4. ✅ Enhanced Daily Check System (1.4)

### **SHOULD HAVE (Build Next)**
5. ✅ Automated Maintenance Scheduler (2.1)
6. ✅ Workshop Management System (2.2)
7. ✅ Cost Tracking & Budgeting (2.4)
8. ✅ Parts Inventory Integration (2.3)

### **NICE TO HAVE (Build Later)**
9. ✅ Predictive Maintenance ML Model (3.1)
10. ✅ Advanced Analytics Dashboard (3.2)
11. ✅ Mobile App Enhancements (3.3)

### **FUTURE ENHANCEMENTS**
12. ✅ IoT Sensor Integration (4.1)
13. ✅ Driver Performance Scoring (4.2)
14. ✅ Vendor Portal (4.3)
15. ✅ Mobile Mechanic Dispatch (4.4)

---

## 💰 BUDGET ESTIMATION

### Phase 1: Foundation (Month 1-3)
```
Development:         Rp 30,000,000
Testing:            Rp  5,000,000
Infrastructure:     Rp  3,000,000
Training:           Rp  2,000,000
─────────────────────────────────
Total Phase 1:       Rp 40,000,000
```

### Phase 2: Automation (Month 4-6)
```
Development:         Rp 25,000,000
Testing:            Rp  4,000,000
Infrastructure:     Rp  2,000,000
Training:           Rp  1,000,000
─────────────────────────────────
Total Phase 2:       Rp 32,000,000
```

### Phase 3: Intelligence (Month 7-9)
```
Development:         Rp 35,000,000
ML Infrastructure:  Rp 10,000,000
Testing:            Rp  5,000,000
Training:           Rp  2,000,000
─────────────────────────────────
Total Phase 3:       Rp 52,000,000
```

### Phase 4: Advanced (Month 10-12)
```
Development:         Rp 30,000,000
IoT Hardware:       Rp 50,000,000 (100 devices @ 500k)
Testing:            Rp  5,000,000
Training:           Rp  2,000,000
─────────────────────────────────
Total Phase 4:       Rp 87,000,000
```

### **TOTAL 12-MONTH BUDGET: Rp 211,000,000**

---

## 📈 SUCCESS METRICS

### Phase 1 Success Criteria
- ✅ 100% of vehicles have component tracking
- ✅ 90% of maintenance schedules auto-generated
- ✅ 95% of alerts delivered within 5 minutes
- ✅ 80% of drivers complete enhanced daily checks

### Phase 2 Success Criteria
- ✅ 95% maintenance compliance rate
- ✅ 50% reduction in manual scheduling time
- ✅ 30% cost reduction through workshop comparison
- ✅ 100% parts availability for scheduled maintenance

### Phase 3 Success Criteria
- ✅ 85% accuracy in failure prediction
- ✅ 40% increase in MTBF
- ✅ 25% reduction in maintenance costs
- ✅ 90% user satisfaction with mobile app

### Phase 4 Success Criteria
- ✅ 100% real-time vehicle monitoring
- ✅ 50% reduction in emergency breakdowns
- ✅ 20% improvement in driver behavior
- ✅ 95% vendor portal adoption

---

## 🚧 RISKS & MITIGATION

| Risk | Impact | Probability | Mitigation |
|------|--------|-------------|------------|
| **Insufficient historical data for ML** | High | Medium | Start with rule-based system, collect data for 6 months |
| **Driver resistance to enhanced checks** | Medium | High | Gamification, incentives, training |
| **Workshop integration challenges** | Medium | Medium | Start with manual entry, build API later |
| **IoT hardware costs** | High | Low | Phased rollout, start with 10 vehicles |
| **Budget overrun** | High | Medium | Prioritize Phase 1-2, defer Phase 4 |
| **Technical complexity** | Medium | Medium | Hire ML specialist, use proven libraries |

---

## 📝 NEXT ACTIONS

### Week 1-2: Planning
- [ ] Review and approve roadmap
- [ ] Allocate budget
- [ ] Assign development team
- [ ] Setup project management tool (Jira/Trello)
- [ ] Create detailed user stories for Phase 1

### Week 3-4: Design
- [ ] Database schema design
- [ ] API endpoint design
- [ ] UI/UX mockups
- [ ] Technical architecture review
- [ ] Security review

### Month 2-3: Development
- [ ] Implement Phase 1 features
- [ ] Unit testing
- [ ] Integration testing
- [ ] User acceptance testing (UAT)
- [ ] Documentation

### Month 4: Deployment
- [ ] Deploy to staging
- [ ] Pilot with 10 vehicles
- [ ] Collect feedback
- [ ] Fix bugs
- [ ] Deploy to production

---

**Document Version**: 1.0  
**Last Updated**: 2026-05-14  
**Next Review**: 2026-06-14  
**Owner**: Product Management Team

---

**Related Documents:**
- [10. Preventive Maintenance Strategy](./10_PREVENTIVE_MAINTENANCE_STRATEGY.md)
- [11. Vehicle Health Monitoring](./11_VEHICLE_HEALTH_MONITORING.md)
- [12. Maintenance Scheduling System](./12_MAINTENANCE_SCHEDULING.md)
- [27. Laravel Best Practices](./27_LARAVEL_BEST_PRACTICES.md)
