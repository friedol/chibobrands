@extends('layouts.admin')

@section('title', 'Customer Insights - ' . $customer->name)

@push('styles')
<style>
    .insight-card {
        border-radius: 24px;
        border: none;
        overflow: hidden;
    }
    .product-row {
        transition: all 0.2s ease;
        border-radius: 12px;
    }
    .product-row:hover {
        background-color: rgba(13, 110, 253, 0.03);
    }
    .timeline-item {
        position: relative;
        padding-left: 24px;
        padding-bottom: 24px;
        border-left: 2px solid rgba(0,0,0,0.05);
    }
    .timeline-item:last-child {
        border-left: none;
    }
    .timeline-dot {
        position: absolute;
        left: -7px;
        top: 0;
        width: 12px;
        height: 12px;
        border-radius: 50%;
        background: #0d6efd;
        border: 2px solid white;
        box-shadow: 0 0 0 2px rgba(13, 110, 253, 0.1);
    }
    .prediction-badge {
        font-size: 24px;
        font-weight: 800;
    }
</style>
@endpush

@section('content')
<div class="container-fluid px-4 py-4">
    <!-- Breadcrumb & Actions -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="{{ route('admin.customers.index') }}">Customers</a></li>
                <li class="breadcrumb-item"><a href="{{ route('admin.customer-data-center.index') }}">Data Center</a></li>
                <li class="breadcrumb-item active">{{ $customer->name }}</li>
            </ol>
        </nav>
        <div class="d-flex gap-2">
            <a href="{{ route('admin.customers.show', $customer) }}" class="btn btn-white btn-sm rounded-pill px-3 shadow-xs">
                <i class="fas fa-user-circle me-1"></i> Full Profile
            </a>
            <form action="{{ route('admin.customer-data-center.refresh', $customer) }}" method="POST">
                @csrf
                <button type="submit" class="btn btn-primary btn-sm rounded-pill px-3 shadow-sm">
                    <i class="fas fa-sync-alt me-1"></i> Refresh Data
                </button>
            </form>
        </div>
    </div>

    <div class="row g-4">
        <!-- Main Analytics Column -->
        <div class="col-lg-8">
            <!-- Lifetime Value & High Level Stats -->
            <div class="card insight-card shadow-sm mb-4 border-0 bg-primary text-white p-2">
                <div class="card-body p-4">
                    <div class="row align-items-center">
                        <div class="col-md-7">
                            <h6 class="text-white-50 text-uppercase x-small fw-bold ls-1 mb-2">Customer Lifetime Performance</h6>
                            <h2 class="fw-800 mb-0">TZS {{ number_format($customer->total_spent, 2) }}</h2>
                            <p class="mb-0 text-white-50 small mt-1">Generated from {{ $customer->total_orders }} design tasks</p>
                        </div>
                        <div class="col-md-5 text-md-end mt-3 mt-md-0">
                            <div class="d-inline-block text-center p-3 rounded-4 bg-white bg-opacity-10 border border-white border-opacity-10">
                                <span class="d-block text-white-50 x-small fw-bold">PRIORITY SCORE</span>
                                <span class="prediction-badge">{{ $customer->priority_ranking }}</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Recent Design Tasks -->
            <div class="card insight-card shadow-sm border-0 mb-4">
                <div class="card-header bg-white py-4 border-0 d-flex justify-content-between align-items-center">
                    <h5 class="fw-bold mb-0">Recent Design Tasks</h5>
                    <span class="badge bg-light text-dark rounded-pill fw-bold">{{ $customer->designTasks->count() }} Tasks</span>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table align-middle mb-0">
                            <thead class="bg-light">
                                <tr class="x-small text-uppercase fw-bold text-muted">
                                    <th class="ps-4">Task Title</th>
                                    <th>Date</th>
                                    <th>Status</th>
                                    <th class="pe-4 text-end">Amount</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($customer->designTasks as $task)
                                <tr class="product-row">
                                    <td class="ps-4">
                                        <div class="fw-bold text-dark">{{ $task->title }}</div>
                                        <span class="text-muted extra-small">{{ $task->task_code }}</span>
                                    </td>
                                    <td>
                                        <div class="small">{{ $task->created_at->format('M d, Y') }}</div>
                                    </td>
                                    <td>
                                        <span class="badge bg-{{ $task->status === 'completed' || $task->status === 'super_completed' ? 'success' : ($task->status === 'pending' ? 'warning' : 'primary') }} rounded-pill extra-small">
                                            {{ $task->status_label }}
                                        </span>
                                    </td>
                                    <td class="pe-4 text-end">
                                        <div class="fw-bold text-dark">
                                            TZS {{ number_format($task->requires_receipt ? $task->price * 1.18 : $task->price, 2) }}
                                        </div>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="4" class="text-center py-4 text-muted small">No design tasks found.</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Per-Service Smart Prediction -->
            <div class="card insight-card shadow-sm border-0 mb-4">
                <div class="card-header bg-white py-4 border-0">
                    <h5 class="fw-bold mb-0">Per-Service Smart Prediction</h5>
                    <p class="text-muted small mb-0">Predicted reorder dates based on specific service patterns</p>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table align-middle mb-0">
                            <thead class="bg-light">
                                <tr class="x-small text-uppercase fw-bold text-muted">
                                    <th class="ps-4">Service Type</th>
                                    <th>Avg Cycle</th>
                                    <th>Last Order</th>
                                    <th class="pe-4">Next Expected</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($customer->taskTypeAnalytics as $analytic)
                                <tr class="product-row">
                                    <td class="ps-4">
                                        <div class="fw-bold text-dark">{{ $analytic->designTaskType->name }}</div>
                                        <span class="text-muted extra-small">Total Qty: {{ number_format($analytic->total_quantity_bought, 1) }}</span>
                                    </td>
                                    <td>
                                        <div class="small fw-bold">{{ $analytic->avg_reorder_interval ?: '??' }} Days</div>
                                    </td>
                                    <td>
                                        <div class="small">{{ $analytic->last_purchase_date ? $analytic->last_purchase_date->format('M d, Y') : 'N/A' }}</div>
                                    </td>
                                    <td class="pe-4">
                                        @if($analytic->next_expected_purchase_date)
                                            <div class="badge bg-{{ $analytic->next_expected_purchase_date->isPast() ? 'danger' : 'success' }}-subtle text-{{ $analytic->next_expected_purchase_date->isPast() ? 'danger' : 'success' }} rounded-pill px-3">
                                                {{ $analytic->next_expected_purchase_date->format('M d, Y') }}
                                            </div>
                                            <div class="extra-small text-muted mt-1">{{ $analytic->next_expected_purchase_date->diffForHumans() }}</div>
                                        @else
                                            <span class="text-muted small">Insufficient Data</span>
                                        @endif
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="4" class="text-center py-4 text-muted small">Not enough historical data for per-service prediction.</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Follow-up Activity History -->
            <div class="card insight-card shadow-sm border-0">
                <div class="card-header bg-white py-4 border-0">
                    <h5 class="fw-bold mb-0">Activity History</h5>
                </div>
                <div class="card-body">
                    <div class="timeline">
                        @forelse($customer->followUps as $activity)
                        <div class="timeline-item">
                            <div class="timeline-dot"></div>
                            <div class="d-flex justify-content-between mb-1">
                                <h6 class="fw-bold text-dark mb-0">{{ $activity->action }}</h6>
                                <span class="text-muted extra-small">{{ $activity->follow_up_date->format('M d, Y') }}</span>
                            </div>
                            <p class="text-muted small mb-0">{{ $activity->notes ?: 'No notes recorded.' }}</p>
                            <span class="x-small text-primary fw-bold">By: {{ $activity->user->name }}</span>
                        </div>
                        @empty
                        <div class="text-center py-4">
                            <p class="text-muted small">No activity recorded yet.</p>
                        </div>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>

        <!-- Right Side: Predictions & Actions -->
        <div class="col-lg-4">
            <!-- Smart Prediction Card -->
            <div class="card insight-card shadow-sm border-0 mb-4 overflow-hidden">
                <div class="p-1 bg-{{ $customer->status_color }}"></div>
                <div class="card-body p-4">
                    <h6 class="text-muted text-uppercase x-small fw-bold ls-1 mb-3">SMART PREDICTION</h6>
                    <div class="d-flex align-items-center mb-4">
                        <div class="rounded-pill bg-{{ $customer->status_color }}-subtle text-{{ $customer->status_color }} px-3 py-1 small fw-bold">
                            {{ $customer->follow_up_status }}
                        </div>
                    </div>
                    
                    <div class="mb-4">
                        <label class="d-block text-muted small mb-1">Expected Next Task</label>
                        @if($customer->effective_follow_up_date)
                            <h3 class="fw-bold mb-0 {{ $customer->effective_follow_up_date->isPast() ? 'text-danger' : 'text-dark' }}">
                                {{ $customer->effective_follow_up_date->format('l, M d') }}
                            </h3>
                            <span class="text-muted small">{{ $customer->effective_follow_up_date->diffForHumans() }}</span>
                        @else
                            <h3 class="fw-bold text-muted">Awaiting Data</h3>
                        @endif
                    </div>

                    <div class="p-3 bg-light rounded-4 mb-4">
                        <div class="d-flex align-items-center gap-2 mb-2">
                            <i class="fas fa-info-circle text-primary"></i>
                            <span class="fw-bold small">Insights</span>
                        </div>
                        <p class="x-small text-muted mb-0">
                            Based on historical patterns, this customer reorders every <strong>{{ $customer->avg_reorder_interval ?: '??' }} days</strong>.
                            Current purchasing power is ranked at <strong>#{{ $customer->priority_ranking }}</strong>.
                        </p>
                    </div>

                    <button class="btn btn-primary w-100 rounded-pill py-2 shadow-sm mb-2" 
                            data-bs-toggle="modal" data-bs-target="#quickContactModal">
                        <i class="fab fa-whatsapp me-2"></i> Contact Customer
                    </button>
                    <button class="btn btn-outline-secondary w-100 rounded-pill py-2 border-0"
                            data-bs-toggle="modal" data-bs-target="#scheduleModal">
                        <i class="fas fa-calendar-plus me-2"></i> Set Manual Date
                    </button>
                </div>
            </div>

        </div>
    </div>
</div>

<!-- Manual Schedule Modal -->
<div class="modal fade" id="scheduleModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg rounded-4">
            <div class="modal-header border-0 pb-0">
                <h5 class="fw-bold mb-0">Schedule Next Contact</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ route('admin.customer-data-center.follow-up-date.update', $customer) }}" method="POST">
                @csrf
                @method('PUT')
                <div class="modal-body p-4">
                    <label class="form-label small fw-bold">Manual Follow-up Date</label>
                    <input type="date" name="manual_follow_up_date" class="form-control border-0 bg-light rounded-3" 
                           value="{{ $customer->manual_follow_up_date ? $customer->manual_follow_up_date->format('Y-m-d') : '' }}" required>
                    <p class="x-small text-muted mt-2">Setting a manual date will override our smart prediction until the date passes.</p>
                </div>
                <div class="modal-footer border-0 pt-0">
                    <button type="button" class="btn btn-light rounded-pill px-4" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary rounded-pill px-4" data-no-global-handler>Update Schedule</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Quick Contact Modal -->
<div class="modal fade" id="quickContactModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg rounded-4">
            <div class="modal-header border-0 pb-0">
                <h5 class="fw-bold mb-0">
                    <i class="fab fa-whatsapp text-success me-2"></i>Contact {{ $customer->name }}
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ route('admin.customer-data-center.follow-up.store', $customer) }}" method="POST">
                @csrf
                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label class="form-label small fw-bold">Action Taken</label>
                        <select name="action" class="form-select border-0 bg-light rounded-3 shadow-none">
                            <option value="WhatsApp Message">WhatsApp Message</option>
                            <option value="Phone Call">Phone Call</option>
                            <option value="Direct Meeting">Direct Meeting</option>
                        </select>
                    </div>
                    <div class="mb-0">
                        <label class="form-label small fw-bold">Outcome / Notes</label>
                        <textarea name="notes" class="form-control border-0 bg-light rounded-3 shadow-none" rows="3" placeholder="Enter details about the interaction..."></textarea>
                    </div>
                </div>
                <div class="modal-footer border-0 pt-0 gap-2">
                    <a href="https://wa.me/{{ preg_replace('/[^0-9]/', '', $customer->phone) }}" target="_blank" 
                       class="btn btn-success rounded-pill px-4">
                        <i class="fab fa-whatsapp me-2"></i>Open WhatsApp
                    </a>
                    <button type="submit" class="btn btn-primary rounded-pill px-4">Save & Close</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
