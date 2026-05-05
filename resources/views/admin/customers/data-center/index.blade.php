@extends('layouts.admin')

@section('title', 'Customer Data Center - Predictive Sales')

@section('content')
<div class="container-fluid px-4 py-4">
    <!-- Header Area -->
    <div class="row align-items-center mb-4 g-3">
        <div class="col-md-6">
            <h2 class="fw-bold mb-1">Customer Data Center</h2>
            <p class="text-muted small mb-0">Follow-up Intelligence & Purchase Pattern Prediction</p>
        </div>
        <div class="col-md-6 d-flex justify-content-md-end align-items-center gap-2">
            <div class="btn-group p-1 bg-light rounded-pill shadow-sm">
                <a href="{{ route('admin.customer-data-center.index', ['status' => 'due']) }}" 
                   class="btn btn-sm rounded-pill px-3 {{ $statusFilter == 'due' ? 'btn-primary shadow-sm' : 'btn-light border-0' }} x-small fw-bold">
                    DUE TODAY / OVERDUE
                </a>
                <a href="{{ route('admin.customer-data-center.index', ['status' => 'upcoming']) }}" 
                   class="btn btn-sm rounded-pill px-3 {{ $statusFilter == 'upcoming' ? 'btn-primary shadow-sm' : 'btn-light border-0' }} x-small fw-bold">
                    UPCOMING
                </a>
                <a href="{{ route('admin.customer-data-center.index', ['status' => 'new']) }}" 
                   class="btn btn-sm rounded-pill px-3 {{ $statusFilter == 'new' ? 'btn-primary shadow-sm' : 'btn-light border-0' }} x-small fw-bold">
                    NEW CUSTOMERS
                </a>
            </div>
            <button class="btn btn-outline-dark btn-sm rounded-pill px-3 x-small fw-bold ms-2" onclick="window.location.reload()">
                <i class="fas fa-sync-alt me-1"></i> REFRESH
            </button>
        </div>
    </div>

    <!-- Stats Cards (Matching Admin Dashboard Style) -->
    <div class="row g-2 g-md-3 mb-4">
        <!-- Overdue -->
        <div class="col-6 col-lg-3">
            <div class="card shadow-sm h-100 border-0 border-start border-4 border-danger hover-lift">
                <div class="card-body p-3">
                    <div class="d-flex align-items-center mb-2">
                        <div class="icon-circle bg-danger bg-opacity-10 text-danger me-2">
                            <i class="fas fa-exclamation-triangle"></i>
                        </div>
                        <span class="text-uppercase x-small fw-bold text-muted">Overdue Follow-ups</span>
                    </div>
                    <div class="h3 mb-0 fw-bold text-dark">{{ number_format($stats['overdue']) }}</div>
                    <div class="x-small text-danger mt-2">Needs immediate contact</div>
                </div>
            </div>
        </div>

        <!-- Due Today -->
        <div class="col-6 col-lg-3">
            <div class="card shadow-sm h-100 border-0 border-start border-4 border-warning hover-lift">
                <div class="card-body p-3">
                    <div class="d-flex align-items-center mb-2">
                        <div class="icon-circle bg-warning bg-opacity-10 text-warning me-2">
                            <i class="fas fa-clock"></i>
                        </div>
                        <span class="text-uppercase x-small fw-bold text-muted">Due Today</span>
                    </div>
                    <div class="h3 mb-0 fw-bold text-dark">{{ number_format($stats['due_today']) }}</div>
                    <div class="x-small text-warning mt-2">Optimal reorder time</div>
                </div>
            </div>
        </div>

        <!-- Upcoming -->
        <div class="col-6 col-lg-3">
            <div class="card shadow-sm h-100 border-0 border-start border-4 border-primary hover-lift">
                <div class="card-body p-3">
                    <div class="d-flex align-items-center mb-2">
                        <div class="icon-circle bg-primary bg-opacity-10 text-primary me-2">
                            <i class="fas fa-calendar-alt"></i>
                        </div>
                        <span class="text-uppercase x-small fw-bold text-muted">Upcoming (3 Days)</span>
                    </div>
                    <div class="h3 mb-0 fw-bold text-dark">{{ number_format($stats['upcoming']) }}</div>
                    <div class="x-small text-primary mt-2">Potential pipelines</div>
                </div>
            </div>
        </div>

        <!-- Total Analyzed -->
        <div class="col-6 col-lg-3">
            <div class="card shadow-sm h-100 border-0 border-start border-4 border-success hover-lift">
                <div class="card-body p-3">
                    <div class="d-flex align-items-center mb-2">
                        <div class="icon-circle bg-success bg-opacity-10 text-success me-2">
                            <i class="fas fa-brain"></i>
                        </div>
                        <span class="text-uppercase x-small fw-bold text-muted">Total Analyzed</span>
                    </div>
                    <div class="h3 mb-0 fw-bold text-dark">{{ number_format($stats['total_customers']) }}</div>
                    <div class="x-small text-success mt-2">Customers with patterns</div>
                </div>
            </div>
        </div>
    </div>

    <!-- Main List Table -->
    <div class="card border-0 shadow-sm rounded-4 overflow-hidden mb-4">
        <div class="card-header bg-white py-4 border-0 d-flex justify-content-between align-items-center">
            <div>
                <h5 class="fw-bold mb-0">Follow-up Intelligence List</h5>
                <p class="text-muted small mb-0">Daily prioritized outreach based on purchase frequency</p>
            </div>
            <div class="d-flex gap-2">
                <input type="text" id="customerSearch" class="form-control form-control-sm rounded-pill px-3 border-0 bg-light" placeholder="Search customer..." style="width: 250px;">
            </div>
        </div>
        <div class="table-responsive">
            <table class="table align-middle mb-0 table-hover" id="followUpTable">
                <thead class="bg-light border-0">
                    <tr class="x-small text-uppercase fw-bold text-muted">
                        <th class="ps-4 border-0 py-3">Customer Details</th>
                        <th class="border-0 py-3">Follow-up Status</th>
                        <th class="border-0 py-3">Purchase Cycle</th>
                        <th class="border-0 py-3">Next Expected</th>
                        <th class="border-0 py-3">Priority Ranking</th>
                        <th class="pe-4 text-end border-0 py-3">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($customers as $customer)
                    <tr class="border-bottom border-light">
                        <td class="ps-4 py-3">
                            <div class="d-flex align-items-center gap-3">
                                @php
                                    $customerProfileImage = $customer->profile_image ?? null;
                                    $avatarSrc = !empty($customerProfileImage) ? asset('storage/' . $customerProfileImage) : asset('img/avatars/placeholder.png');
                                @endphp
                                <img
                                    src="{{ $avatarSrc }}"
                                    alt="{{ $customer->name }}"
                                    class="rounded-circle shadow-sm"
                                    style="width: 42px; height: 42px; object-fit: cover;"
                                    onerror="this.onerror=null; this.src='{{ asset('img/avatars/placeholder.png') }}';"
                                >
                                <div>
                                    <h6 class="fw-bold mb-1 text-dark">{{ $customer->name }}</h6>
                                    <div class="d-flex align-items-center gap-2">
                                        <span class="text-muted x-small"><i class="fas fa-phone-alt me-1"></i>{{ $customer->phone }}</span>
                                        @if($customer->company_name)
                                            <span class="badge bg-light text-primary x-small border-0 px-2">{{ $customer->company_name }}</span>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </td>
                        <td>
                            <span class="badge rounded-pill px-3 py-2 bg-{{ $customer->status_color }}-subtle text-{{ $customer->status_color }} x-small fw-bold text-uppercase border-0">
                                {{ $customer->follow_up_status }}
                            </span>
                        </td>
                        <td>
                            @if($customer->avg_reorder_interval)
                                <div class="fw-bold text-dark small">{{ number_format($customer->avg_reorder_interval, 1) }} Days</div>
                                <span class="text-muted extra-small">Average frequency</span>
                            @else
                                <span class="text-muted x-small">N/A</span>
                            @endif
                        </td>
                        <td>
                            @php
                                $nextDate = $customer->effective_follow_up_date;
                            @endphp
                            @if($nextDate)
                                <div class="fw-bold {{ $nextDate->isPast() ? 'text-danger' : 'text-dark' }} small">
                                    {{ $nextDate->format('M d, Y') }}
                                </div>
                                <div class="extra-small text-muted d-flex align-items-center">
                                    {{ $nextDate->diffForHumans() }}
                                    @if($customer->manual_follow_up_date && $customer->manual_follow_up_date->isFuture())
                                        <i class="fas fa-hand-pointer text-info ms-1" title="Manually scheduled"></i>
                                    @endif
                                </div>
                            @else
                                <span class="text-muted extra-small">Insufficient Data</span>
                            @endif
                        </td>
                        <td>
                            <div class="d-flex align-items-center gap-2" style="max-width: 120px;">
                                <div class="progress flex-grow-1" style="height: 4px;">
                                    <div class="progress-bar bg-primary shadow-sm" role="progressbar" 
                                         style="width: {{ min(100, $customer->priority_ranking / 3) }}%"></div>
                                </div>
                                <span class="fw-bold x-small text-dark">#{{ $customer->priority_ranking }}</span>
                            </div>
                        </td>
                        <td class="pe-4 text-end">
                            <div class="dropdown">
                                <button class="btn btn-light btn-sm rounded-pill px-3 x-small dropdown-toggle shadow-sm" type="button" data-bs-toggle="dropdown">
                                    ACTIONS
                                </button>
                                <ul class="dropdown-menu dropdown-menu-end border-0 shadow-lg rounded-3">
                                    <li>
                                        <a href="{{ route('admin.customer-data-center.show', $customer) }}" class="dropdown-item py-2 px-3 small">
                                            <i class="fas fa-chart-line me-2 text-primary"></i>View Insights
                                        </a>
                                    </li>
                                    <li>
                                        <button type="button" class="dropdown-item py-2 px-3 small" data-bs-toggle="modal" data-bs-target="#followUpModal{{ $customer->id }}">
                                            <i class="fab fa-whatsapp me-2 text-success"></i>Direct Contact
                                        </button>
                                    </li>
                                    <li><hr class="dropdown-divider"></li>
                                    <li>
                                        <a href="{{ route('admin.customers.show', $customer->id) }}" class="dropdown-item py-2 px-3 small">
                                            <i class="fas fa-user-circle me-2 text-muted"></i>Full Profile
                                        </a>
                                    </li>
                                </ul>
                            </div>
                        </td>
                    </tr>

                    @empty
                    <tr>
                        <td colspan="6" class="text-center py-5">
                            <div class="py-5">
                                <i class="fas fa-check-circle fa-4x text-success opacity-10 mb-3"></i>
                                <h6 class="fw-bold text-dark">No customers need attention!</h6>
                                <p class="text-muted small">You've cleared the outreach list for this filter.</p>
                                <a href="{{ route('admin.customer-data-center.index', ['status' => 'all']) }}" class="btn btn-sm btn-primary rounded-pill px-4 mt-2">VIEW ALL CUSTOMERS</a>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($customers->hasPages())
        <div class="card-footer bg-white border-top-0 py-3">
            {{ $customers->links() }}
        </div>
        @endif
    </div>
</div>

<!-- Modals Container -->
@foreach($customers as $customer)
    <div class="modal fade" id="followUpModal{{ $customer->id }}" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden">
                <div class="modal-header bg-light border-0 py-3 px-4">
                    <h5 class="fw-bold mb-0 text-dark">
                        <i class="fab fa-whatsapp text-success me-2"></i>Smart Contact: {{ $customer->name }}
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form action="{{ route('admin.customer-data-center.follow-up.store', $customer) }}" method="POST">
                    @csrf
                    <div class="modal-body p-4">
                        <div class="d-flex align-items-center gap-3 mb-4 p-3 bg-success bg-opacity-10 rounded-4">
                            <div class="stat-icon bg-success text-white rounded-3 p-2 d-flex align-items-center justify-content-center" style="width: 48px; height: 48px;">
                                <i class="fab fa-whatsapp fa-2x"></i>
                            </div>
                            <div>
                                <div class="fw-bold text-dark small">WhatsApp Marketing</div>
                                <div class="extra-small text-muted">Automated connection to {{ $customer->phone }}</div>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label x-small fw-bold text-uppercase text-muted">Action Channel</label>
                            <select name="action" class="form-select rounded-3 border-light py-2 small">
                                <option value="WhatsApp">WhatsApp Business</option>
                                <option value="Phone Call">Direct Phone Call</option>
                                <option value="Email">Email Communication</option>
                                <option value="In Person">Face-to-Face Meeting</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label x-small fw-bold text-uppercase text-muted">Conversation Notes</label>
                            <textarea name="notes" class="form-control rounded-3 border-light py-2 small" rows="3" 
                                      placeholder="Brief summary of the outcome..."></textarea>
                        </div>
                        <div class="mb-0">
                            <label class="form-label x-small fw-bold text-uppercase text-muted">Manual Follow-up Overide</label>
                            <input type="date" name="next_follow_up_date" class="form-control rounded-3 border-light py-2 small">
                            <p class="extra-small text-muted mt-2 mb-0">
                                <i class="fas fa-info-circle me-1"></i> Selecting a date here will override the smart prediction algorithm.
                            </p>
                        </div>
                    </div>
                    <div class="modal-footer border-0 bg-light p-3 px-4 d-flex justify-content-between">
                        <button type="button" class="btn btn-outline-dark rounded-pill px-4 x-small fw-bold" data-bs-dismiss="modal">CANCEL</button>
                        <div class="d-flex gap-2">
                             <a href="https://wa.me/{{ preg_replace('/[^0-9]/', '', $customer->phone) }}" target="_blank" 
                               class="btn btn-success rounded-pill px-4 x-small fw-bold">
                                <i class="fab fa-whatsapp me-2"></i>MESSAGE
                            </a>
                            <button type="submit" class="btn btn-primary rounded-pill px-4 x-small fw-bold">SAVE LOG</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endforeach

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const searchInput = document.getElementById('customerSearch');
        const table = document.getElementById('followUpTable');
        const rows = table.getElementsByTagName('tr');

        searchInput.addEventListener('input', function() {
            const filter = searchInput.value.toLowerCase();
            for (let i = 1; i < rows.length; i++) {
                const text = rows[i].textContent.toLowerCase();
                rows[i].style.display = text.includes(filter) ? '' : 'none';
            }
        });
    });
</script>
@endpush

@push('styles')
<style>
    .icon-circle {
        width: 32px;
        height: 32px;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 8px;
        font-size: 0.8rem;
    }
    .hover-lift {
        transition: transform 0.2s ease-in-out, box-shadow 0.2s ease-in-out;
    }
    .hover-lift:hover {
        transform: translateY(-5px);
        box-shadow: 0 10px 20px rgba(0,0,0,0.05) !important;
    }
    .extra-small { font-size: 11px; }
    .x-small { font-size: 10px; }
    .table-hover tbody tr:hover {
        background-color: rgba(0,0,0,0.015) !important;
    }
    .dropdown-item:hover {
        background-color: #f8f9fa;
        color: #0d6efd;
    }
</style>
@endpush
@endsection
