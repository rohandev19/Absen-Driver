@extends('admin.layouts.app')

@section('title', 'Design System Test')

@section('content')
@include('admin.maintenance.partials._design-system')

<div class="container-fluid py-4">
    <div class="page-header d-flex justify-content-between align-items-center">
        <div>
            <h3>Design System Test Page</h3>
            <p>Testing all components in isolation</p>
        </div>
    </div>

    <!-- Test Card Metric Components -->
    {{-- BAGIAN 1: METRIC CARDS --}}
    <div class="row mb-4 g-3">
        <div class="col-xl-3 col-md-6">
            <div class="stat-card card-metric stat-card-danger border-left-danger animate-fade-in" style="animation-delay: 0.1s">
                <div class="stat-icon card-icon">
                    <i class="bi bi-exclamation-octagon-fill"></i>
                </div>
                <div class="stat-content metric-content">
                    <div class="stat-value metric-value">12</div>
                    <div class="stat-label metric-label">Perlu Perhatian</div>
                    <div class="stat-desc metric-desc">Unit Rusak / Telat Servis</div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6">
            <div class="stat-card card-metric stat-card-warning border-left-warning animate-fade-in" style="animation-delay: 0.2s">
                <div class="stat-icon card-icon">
                    <i class="bi bi-cone-striped"></i>
                </div>
                <div class="stat-content metric-content">
                    <div class="stat-value metric-value">5</div>
                    <div class="stat-label metric-label">Segera Servis</div>
                    <div class="stat-desc metric-desc">Sisa KM &lt; 1.000</div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6">
            <div class="stat-card card-metric stat-card-success border-left-success animate-fade-in" style="animation-delay: 0.3s">
                <div class="stat-icon card-icon">
                    <i class="bi bi-check-circle-fill"></i>
                </div>
                <div class="stat-content metric-content">
                    <div class="stat-value metric-value">45</div>
                    <div class="stat-label metric-label">Kondisi Prima</div>
                    <div class="stat-desc metric-desc">Siap Operasi</div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6">
            <div class="stat-card card-metric stat-card-primary border-left-primary animate-fade-in" style="animation-delay: 0.4s">
                <div class="stat-icon card-icon">
                    <i class="bi bi-truck"></i>
                </div>
                <div class="stat-content metric-content">
                    <div class="stat-value metric-value">62</div>
                    <div class="stat-label metric-label">Total Armada</div>
                    <div class="stat-desc metric-desc">Unit Terdaftar</div>
                </div>
            </div>
        </div>
    </div>

    <!-- Test Badge Components -->
    <h4 class="mt-4 mb-3">Badge Components</h4>
    <div class="mb-4">
        <span class="badge-corp badge-corp-danger me-2"><i class="bi bi-x-circle"></i> Danger</span>
        <span class="badge-corp badge-corp-warning me-2"><i class="bi bi-exclamation-triangle"></i> Warning</span>
        <span class="badge-corp badge-corp-success me-2"><i class="bi bi-check-circle"></i> Success</span>
        <span class="badge-corp badge-corp-info me-2"><i class="bi bi-info-circle"></i> Info</span>
        <span class="badge-corp badge-corp-primary me-2"><i class="bi bi-star"></i> Primary</span>
    </div>

    <!-- Test Button Components -->
    <h4 class="mt-4 mb-3">Button Components</h4>
    <div class="mb-4">
        <button class="btn-action-corp me-2"><i class="bi bi-eye"></i> Action</button>
        <button class="btn-primary-corp me-2"><i class="bi bi-check"></i> Primary</button>
        <button class="btn-danger-corp me-2"><i class="bi bi-trash"></i> Danger</button>
        <button class="btn-action-corp me-2" disabled><i class="bi bi-eye"></i> Disabled</button>
    </div>

    <!-- Test Table Corporate Component -->
    <h4 class="mt-4 mb-3">Table Corporate Component</h4>
    <div class="card">
        <div class="card-body">
            <table class="table-corporate">
                <thead>
                    <tr>
                        <th>Vehicle</th>
                        <th>Status</th>
                        <th>Health Score</th>
                        <th>Last Update</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td data-label="Vehicle">
                            <strong>B 1234 ABC</strong><br>
                            <small class="text-muted">Box Truck</small>
                        </td>
                        <td data-label="Status">
                            <span class="badge-corp badge-corp-danger"><i class="bi bi-exclamation-triangle"></i> Critical</span>
                        </td>
                        <td data-label="Health Score">
                            <div class="progress-corp-bg">
                                <div class="progress-corp-fill bg-danger" style="width: 35%"></div>
                            </div>
                            <small class="text-muted">35%</small>
                        </td>
                        <td data-label="Last Update">2 days ago</td>
                        <td data-label="Actions">
                            <div class="d-flex gap-2">
                                <button class="btn-action-corp"><i class="bi bi-eye"></i> View</button>
                                <button class="btn-primary-corp"><i class="bi bi-wrench"></i> Service</button>
                            </div>
                        </td>
                    </tr>
                    <tr>
                        <td data-label="Vehicle">
                            <strong>B 5678 XYZ</strong><br>
                            <small class="text-muted">Pickup</small>
                        </td>
                        <td data-label="Status">
                            <span class="badge-corp badge-corp-success"><i class="bi bi-check-circle"></i> Healthy</span>
                        </td>
                        <td data-label="Health Score">
                            <div class="progress-corp-bg">
                                <div class="progress-corp-fill bg-success" style="width: 92%"></div>
                            </div>
                            <small class="text-muted">92%</small>
                        </td>
                        <td data-label="Last Update">1 hour ago</td>
                        <td data-label="Actions">
                            <div class="d-flex gap-2">
                                <button class="btn-action-corp"><i class="bi bi-eye"></i> View</button>
                                <button class="btn-primary-corp"><i class="bi bi-wrench"></i> Service</button>
                            </div>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Test Filter Container -->
    <h4 class="mt-4 mb-3">Filter Container Component</h4>
    <div class="filter-container">
        <form class="row g-3">
            <div class="col-md-3">
                <label class="form-label">Project</label>
                <select class="form-select form-select-sm">
                    <option>All Projects</option>
                    <option>Project Alpha</option>
                    <option>Project Beta</option>
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label">Status</label>
                <select class="form-select form-select-sm">
                    <option>All Status</option>
                    <option>Active</option>
                    <option>Inactive</option>
                </select>
            </div>
            <div class="col-md-4">
                <label class="form-label">Search</label>
                <input type="text" class="form-control form-control-sm" placeholder="Search...">
            </div>
            <div class="col-md-2 d-flex align-items-end">
                <button type="submit" class="btn-primary-corp w-100"><i class="bi bi-search"></i> Filter</button>
            </div>
        </form>
    </div>

    <!-- Test Empty State -->
    <h4 class="mt-4 mb-3">Empty State Component</h4>
    <div class="card">
        <div class="card-body">
            <div class="empty-state">
                <i class="bi bi-inbox"></i>
                <h5>No Data Available</h5>
                <p>There are no items to display at this time.</p>
            </div>
        </div>
    </div>

    <!-- Test Loading State -->
    <h4 class="mt-4 mb-3">Loading State Component</h4>
    <div class="mb-4">
        <button class="btn-primary-corp btn-loading me-2">Loading...</button>
        <button class="btn-action-corp btn-loading">Processing...</button>
    </div>

    <!-- CSS Variables Test -->
    <h4 class="mt-4 mb-3">CSS Variables Test</h4>
    <div class="card">
        <div class="card-body">
            <p><strong>Typography:</strong> Using var(--font-size-base), var(--font-weight-bold)</p>
            <p><strong>Spacing:</strong> Using var(--spacing-xs) to var(--spacing-xl)</p>
            <p><strong>Colors:</strong> Using var(--color-danger), var(--color-success), etc.</p>
            <p><strong>Transitions:</strong> Using var(--transition-smooth)</p>
            <p class="mb-0"><strong>Shadows:</strong> Using var(--shadow-sm) to var(--shadow-xl)</p>
        </div>
    </div>

    <!-- Responsive Test Instructions -->
    <h4 class="mt-4 mb-3">Responsive Test Instructions</h4>
    <div class="alert alert-info">
        <h6><i class="bi bi-info-circle"></i> Testing Responsive Behavior</h6>
        <ul class="mb-0">
            <li>Resize browser window to below 768px to test mobile view</li>
            <li>Table should transform to card layout</li>
            <li>Buttons should stack and expand to full width</li>
            <li>Filter container should stack vertically</li>
            <li>Card metrics should stack in single column</li>
        </ul>
    </div>
</div>
@endsection
