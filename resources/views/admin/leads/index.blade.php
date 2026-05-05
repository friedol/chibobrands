@extends('layouts.admin')

@section('title', 'Leads Management')

@section('content')
    <div class="container-fluid py-3">
        <!-- Modern Header & Actions -->
        <div class="row mb-3 align-items-end">
            <div class="col-lg-6 mb-2 mb-lg-0">
                <h4 class="fw-bold mb-0">Leads Management</h4>
            </div>
            <div class="col-lg-6 text-lg-end">
                <div class="d-flex flex-wrap justify-content-lg-end gap-2">

                    <button class="btn btn-outline-primary btn-sm px-3 fw-bold" type="button" data-bs-toggle="collapse"
                        data-bs-target="#filterCollapse">
                        <i class="fas fa-filter me-1"></i> Filter
                        @if(request()->anyFilled(['search', 'status', 'interest_level', 'date_from', 'date_to', 'saler_id']))
                            <span class="badge bg-primary ms-1">Active</span>
                        @endif
                    </button>
                    <button type="button" onclick="printDirect('{{ route('admin.leads.print', request()->all()) }}')"
                        class="btn btn-dark btn-sm px-3 fw-bold shadow-sm">
                        <i class="fas fa-print me-1"></i>Print
                    </button>

                    @if(in_array(auth()->user()->role, ['admin', 'super_admin', 'saler']))
                        <button class="btn btn-primary btn-sm px-3 fw-bold shadow-sm" data-bs-toggle="modal"
                            data-bs-target="#addLeadModal" data-no-global-handler>
                            <i class="fas fa-plus-circle me-1"></i> Log Interaction / Lead
                        </button>
                        <button class="btn btn-success btn-sm px-3 fw-bold shadow-sm" data-bs-toggle="modal"
                            data-bs-target="#messageCustomersModal" data-no-global-handler>
                            <i class="fab fa-whatsapp me-1"></i> Send Message
                        </button>
                    @endif
                </div>
            </div>
        </div>

        <!-- Modern Collapsable Filters -->
        <div class="collapse {{ request()->anyFilled(['search', 'status', 'interest_level', 'date_from', 'date_to', 'saler_id']) ? 'show' : '' }} mb-4"
            id="filterCollapse">
            <div class="card border-0 shadow-sm border-top border-4 border-primary">
                <div class="card-body bg-light p-3">
                    <form action="{{ route('admin.leads.index') }}" method="GET" class="row g-2" data-no-global-handler>
                        <div class="col-12 col-md-3">
                            <label class="form-label fw-bold x-small text-uppercase mb-1">Search Lead</label>
                            <div class="input-group input-group-sm">
                                <span class="input-group-text bg-white border-end-0"><i
                                        class="fas fa-search text-muted"></i></span>
                                <input type="text" name="search" class="form-control border-start-0"
                                    placeholder="Name, phone, or email..." value="{{ request('search') }}">
                            </div>
                        </div>

                        <div class="col-6 col-md-2">
                            <label class="form-label fw-bold x-small text-uppercase mb-1">Status</label>
                            <select name="status" class="form-select form-select-sm">
                                <option value="all">All Stages</option>
                                <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pending
                                </option>
                                <option value="converted" {{ request('status') == 'converted' ? 'selected' : '' }}>Won
                                    (Converted)</option>
                                <option value="not_interested" {{ request('status') == 'not_interested' ? 'selected' : '' }}>
                                    Lost (Lost)</option>
                            </select>
                        </div>

                        <div class="col-6 col-md-2">
                            <label class="form-label fw-bold x-small text-uppercase mb-1">Interest</label>
                            <select name="interest_level" class="form-select form-select-sm">
                                <option value="all">All Levels</option>
                                <option value="cold" {{ request('interest_level') == 'cold' ? 'selected' : '' }}>Cold</option>
                                <option value="warm" {{ request('interest_level') == 'warm' ? 'selected' : '' }}>Warm</option>
                                <option value="hot" {{ request('interest_level') == 'hot' ? 'selected' : '' }}>Hot</option>
                            </select>
                        </div>

                        @if(in_array(auth()->user()->role, ['admin', 'super_admin', 'accountant']))
                            <div class="col-12 col-md-2">
                                <label class="form-label fw-bold x-small text-uppercase mb-1">Salesperson</label>
                                <select name="saler_id" class="form-select form-select-sm">
                                    <option value="all">All Sellers</option>
                                    @foreach($sellers as $s)
                                        <option value="{{ $s->id }}" {{ request('saler_id') == $s->id ? 'selected' : '' }}>
                                            {{ $s->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        @endif

                        <div class="col-6 col-md-2">
                            <label class="form-label fw-bold x-small text-uppercase mb-1">Created From</label>
                            <input type="date" name="date_from" class="form-control form-control-sm"
                                value="{{ request('date_from') }}">
                        </div>

                        <div class="col-6 col-md-1">
                            <label class="form-label fw-bold x-small text-uppercase mb-1">Created To</label>
                            <input type="date" name="date_to" class="form-control form-control-sm"
                                value="{{ request('date_to') }}">
                        </div>

                        <div class="col-12 d-md-none mt-2">
                            <button type="submit" class="btn btn-primary btn-sm w-100 fw-bold">APPLY FILTERS</button>
                            <a href="{{ route('admin.leads.index') }}" class="btn btn-light btn-sm w-100 mt-2">RESET</a>
                        </div>

                        <div class="col-md-auto d-none d-md-flex align-items-end ms-auto">
                            <div class="btn-group shadow-sm">
                                <button type="submit" class="btn btn-primary btn-sm px-3 fw-bold">APPLY</button>
                                <a href="{{ route('admin.leads.index') }}"
                                    class="btn btn-dark btn-sm px-3 fw-bold">RESET</a>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Main Content Card -->
        <div class="card border-0 shadow-sm overflow-hidden bg-white">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0 custom-compact-table">
                        <thead class="bg-light">
                            <tr>
                                <th class="ps-4 py-2">Customer</th>
                                <th>Interest</th>
                                <th>Ownership</th>
                                <th>Follow-up</th>
                                <th class="text-center">Status</th>
                                <th class="text-end pe-4">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($leads as $lead)
                                <tr>
                                    <td class="ps-4 py-2">
                                        <div class="fw-bold text-dark">{{ $lead->customer_name }}</div>
                                        <div class="text-muted" style="font-size: 11px;"><i
                                                class="fas fa-phone-alt me-1 opacity-50"></i>{{ $lead->phone }}</div>
                                    </td>
                                    <td><span class="text-truncate d-inline-block"
                                            style="max-width: 150px;">{{ $lead->product_requested ?: '---' }}</span></td>
                                    <td class="text-muted">{{ $lead->seller->name ?? '---' }}</td>
                                    <td>
                                        @if($lead->follow_up_date)
                                            <span
                                                class="{{ $lead->follow_up_date->isToday() ? 'text-danger fw-bold' : 'text-dark' }}">
                                                {{ $lead->follow_up_date->format('d/m/Y') }}
                                            </span>
                                        @else
                                            <span class="text-muted opacity-50">---</span>
                                        @endif
                                    </td>
                                    <td class="text-center">
                                        @php
                                            $statusBadge = [
                                                'converted' => 'bg-success bg-opacity-10 text-success',
                                                'not_interested' => 'bg-danger bg-opacity-10 text-danger',
                                                'pending' => 'bg-warning bg-opacity-10 text-warning'
                                            ][$lead->status] ?? 'bg-secondary bg-opacity-10 text-secondary';
                                        @endphp
                                        <span class="badge {{ $statusBadge }} px-3 py-1 rounded-pill text-uppercase"
                                            style="font-size: 10px;">
                                            {{ $lead->status }}
                                        </span>
                                    </td>
                                    <td class="text-end pe-4">
                                        <div class="d-flex justify-content-end gap-1">
                                            @if(in_array(auth()->user()->role, ['admin', 'super_admin']) || (auth()->user()->role === 'saler' && auth()->user()->id === $lead->assigned_seller_id))
                                                <button class="btn btn-sm btn-primary px-3 d-flex align-items-center gap-1"
                                                    data-bs-toggle="modal" data-bs-target="#manageLeadModal{{ $lead->id }}"
                                                    data-no-global-handler>
                                                    <i class="fas fa-edit"></i> Manage
                                                </button>
                                            @else
                                                <button class="btn btn-sm btn-outline-secondary px-2" data-bs-toggle="modal"
                                                    data-bs-target="#manageLeadModal{{ $lead->id }}" data-no-global-handler
                                                    title="View Lead Details">
                                                    <i class="fas fa-eye"></i> View
                                                </button>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="text-center py-5 text-muted">No leads available in the pipeline.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="card-footer bg-white border-0 py-3">
                    {{ $leads->appends(request()->query())->links() }}
                </div>
            </div>
        </div>
    </div>

    @foreach($leads as $lead)
        <!-- Unified Manage Lead Modal -->
        <div class="modal fade" id="manageLeadModal{{ $lead->id }}" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-lg">
                <div class="modal-content border-0 shadow-lg">
                    <form action="{{ route('admin.leads.update', $lead->id) }}" method="POST" data-no-global-handler>
                        @csrf @method('PUT')

                        <div class="modal-header bg-white text-dark border-bottom">
                            <div class="d-flex align-items-center">
                                <div class="avatar-circle bg-primary me-2" style="width: 40px; height: 40px; font-size: 16px;">
                                    {{ substr($lead->customer_name, 0, 1) }}
                                </div>
                                <div>
                                    <h5 class="modal-title mb-0">{{ $lead->customer_name }}</h5>
                                    <div class="small text-muted">{{ $lead->phone }}</div>
                                </div>
                            </div>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>

                        <div class="modal-body p-0">
                            <div class="row g-0">
                                <!-- Left Side: Update Form -->
                                <div class="col-md-7 p-4 border-end">
                                    <h6 class="fw-bold mb-3 text-uppercase" style="font-size: 11px; letter-spacing: 1px;">Update
                                        Lead Status</h6>

                                    <div class="row g-3 mb-4">
                                        <div class="col-6">
                                            <label class="form-label fw-bold">Lead Stage</label>
                                            <select name="status" class="form-select bg-light" {{ !in_array(auth()->user()->role, ['admin', 'super_admin']) && auth()->user()->id !== $lead->assigned_seller_id ? 'disabled' : '' }}>
                                                <option value="pending" {{ $lead->status == 'pending' ? 'selected' : '' }}>Pending
                                                </option>
                                                <option value="converted" {{ $lead->status == 'converted' ? 'selected' : '' }}>Won
                                                    (Converted)</option>
                                                <option value="not_interested" {{ $lead->status == 'not_interested' ? 'selected' : '' }}>Lost (Not Interested)</option>
                                            </select>
                                        </div>
                                        <div class="col-6">
                                            <label class="form-label fw-bold">Interest Level</label>
                                            <select name="interest_level" class="form-select bg-light" {{ !in_array(auth()->user()->role, ['admin', 'super_admin']) && auth()->user()->id !== $lead->assigned_seller_id ? 'disabled' : '' }}>
                                                <option value="cold" {{ $lead->interest_level == 'cold' ? 'selected' : '' }}>Cold
                                                </option>
                                                <option value="warm" {{ $lead->interest_level == 'warm' ? 'selected' : '' }}>Warm
                                                </option>
                                                <option value="hot" {{ $lead->interest_level == 'hot' ? 'selected' : '' }}>Hot
                                                </option>
                                            </select>
                                        </div>

                                        @if(in_array(auth()->user()->role, ['admin', 'super_admin']) || auth()->user()->id === $lead->assigned_seller_id)
                                            <div class="col-12 mt-3">
                                                <label class="form-label fw-bold">Reassign to Salesperson</label>
                                                <select name="assigned_seller_id" class="form-select bg-light">
                                                    <option value="">-- Unassigned --</option>
                                                    @foreach($sellers as $seller)
                                                        <option value="{{ $seller->id }}" {{ $lead->assigned_seller_id == $seller->id ? 'selected' : '' }}>
                                                            {{ $seller->name }}
                                                        </option>
                                                    @endforeach
                                                </select>
                                            </div>
                                        @endif
                                        <div class="col-12">
                                            <label class="form-label fw-bold">Saler's feedback / Summary</label>
                                            <textarea name="customer_response" class="form-control bg-light" rows="2" {{ !in_array(auth()->user()->role, ['admin', 'super_admin']) && auth()->user()->id !== $lead->assigned_seller_id ? 'disabled' : '' }}>{{ $lead->customer_response }}</textarea>
                                        </div>
                                    </div>

                                    <h6 class="fw-bold mb-3 text-uppercase" style="font-size: 11px; letter-spacing: 1px;">New
                                        Interaction / Follow-up</h6>

                                    <div class="mb-3">
                                        <label class="form-label fw-bold">Interaction Details</label>
                                        <textarea name="follow_up_notes" class="form-control" rows="4"
                                            placeholder="Log details of your recent contact with this lead..." {{ !in_array(auth()->user()->role, ['admin', 'super_admin']) && auth()->user()->id !== $lead->assigned_seller_id ? 'disabled' : '' }}></textarea>
                                    </div>

                                    <div class="mb-4">
                                        <label class="form-label fw-bold">Next Follow-up Date</label>
                                        <input type="date" name="next_follow_up_date" class="form-control"
                                            value="{{ $lead->follow_up_date ? $lead->follow_up_date->format('Y-m-d') : '' }}" {{ !in_array(auth()->user()->role, ['admin', 'super_admin']) && auth()->user()->id !== $lead->assigned_seller_id ? 'disabled' : '' }}>
                                        <small class="text-muted">A notification reminder will be sent on this date.</small>
                                    </div>

                                    @if(in_array(auth()->user()->role, ['admin', 'super_admin']) || (auth()->user()->role === 'saler' && auth()->user()->id === $lead->assigned_seller_id))
                                        <div class="text-end border-top pt-3">
                                            <button type="button" class="btn btn-light px-4" data-bs-dismiss="modal">Cancel</button>
                                            <button type="submit" class="btn btn-primary px-5" data-no-global-handler>Save All
                                                Updates</button>
                                        </div>
                                    @endif
                                </div>

                                <!-- Right Side: History Timeline -->
                                <div class="col-md-5 p-4 bg-light">
                                    <h6 class="fw-bold mb-3 text-uppercase" style="font-size: 11px; letter-spacing: 1px;">Lead
                                        History</h6>

                                    <div class="timeline-simple"
                                        style="max-height: 450px; overflow-y: auto; padding-right: 10px;">
                                        @forelse($lead->followUps->sortByDesc('created_at') as $history)
                                            <div class="mb-4 ps-3 border-start border-2 border-primary position-relative">
                                                <div class="position-absolute start-0 translate-middle-x bg-white"
                                                    style="margin-left: -1px; margin-top: 5px;">
                                                    <i class="fas fa-dot-circle text-primary" style="font-size: 10px;"></i>
                                                </div>
                                                <div class="d-flex justify-content-between align-items-start mb-1">
                                                    <div class="fw-bold" style="font-size: 11px;">
                                                        {{ $history->created_at->format('M d, Y') }}
                                                    </div>
                                                    <span class="badge bg-white text-dark shadow-sm border small"
                                                        style="font-size: 9px;">{{ $history->user->name ?? 'System' }}</span>
                                                </div>
                                                <div class="text-muted mb-2" style="font-size: 11px;">{{ $history->notes }}</div>

                                                @if($history->follow_up_date)
                                                    <div class="x-small text-danger fw-bold">
                                                        <i class="fas fa-calendar-alt me-1"></i>Next:
                                                        {{ $history->follow_up_date->format('d M Y') }}
                                                    </div>
                                                @endif
                                            </div>
                                        @empty
                                            <div class="text-center py-5 text-muted small">
                                                <i class="fas fa-history fa-3x mb-3 opacity-25"></i>
                                                <p>No interaction history yet.</p>
                                            </div>
                                        @endforelse
                                    </div>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endforeach

    <!-- Message Customers Modal -->
    <div class="modal fade" id="messageCustomersModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <form action="{{ route('admin.message-templates.send') }}" method="POST" id="messageForm"
                data-no-global-handler>
                @csrf
                <div class="modal-content border-0 shadow-lg">
                    <div class="modal-header bg-success text-white">
                        <h5 class="modal-title fw-bold"><i class="fab fa-whatsapp me-2"></i>Send Message</h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                            aria-label="Close"></button>
                    </div>
                    <div class="modal-body p-4">
                        <div class="mb-4">
                            <label class="form-label fw-bold">Recipients</label>
                            <select name="recipient_type" id="msg_recipient_type" class="form-select mb-2"
                                onchange="toggleRecipientType()">
                                <option value="assigned_leads">All My Assigned Leads</option>
                                @if(in_array(auth()->user()->role, ['admin', 'super_admin', 'manager', 'saler']))
                                    <option value="all_leads">All Leads (System-wide)</option>
                                    <option value="all_customers">All Customers (System-wide)</option>
                                @endif
                                <option value="selected">Specific Customer(s)</option>
                            </select>

                            <!-- Search for Specific -->
                            <div id="specific_customer_search" class="d-none mt-2">
                                <label class="form-label small text-muted">Search & Add Customers</label>
                                <div class="position-relative">
                                    <input type="text" id="msgCustomerSearch" class="form-control"
                                        placeholder="Type name to search...">
                                    <div id="msgCustomerResults" class="list-group position-absolute w-100 shadow-sm d-none"
                                        style="z-index: 1060;"></div>
                                </div>
                                <div id="selected_customers_list" class="mt-2 d-flex flex-wrap gap-2"></div>
                                <!-- Hidden inputs for selected IDs will be appended here -->
                                <div id="selected_ids_container"></div>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-bold">Message Template (Optional)</label>
                            <select name="template_id" id="msg_template_id" class="form-select bg-light"
                                onchange="applyTemplate()">
                                <option value="">-- Write Custom Message --</option>
                                @foreach($templates as $tpl)
                                    <option value="{{ $tpl->id }}" data-content="{{ $tpl->content }}">{{ $tpl->title }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-bold">Message Content</label>
                            <textarea name="custom_content" id="msg_content" class="form-control" rows="5" required
                                placeholder="Type your message here..."></textarea>
                            <div class="form-text text-end" id="charCount">0 characters</div>
                        </div>
                    </div>
                    <div class="modal-footer bg-light">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-success px-4 fw-bold">
                            <i class="fas fa-paper-plane me-2"></i>Send Message
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- New Lead Modal -->
    <div class="modal fade" id="addLeadModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <form action="{{ route('admin.leads.store') }}" method="POST" data-no-global-handler>
                @csrf
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Add New Sales Lead</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body p-4">
                        <div class="mb-3">
                            <label class="form-label fw-bold">Search Existing Customers</label>
                            <div class="position-relative">
                                <input type="text" id="customerSearch" class="form-control bg-light"
                                    placeholder="Search by name or phone to auto-fill...">
                                <div id="customerResults" class="list-group position-absolute w-100 shadow-sm d-none"
                                    style="z-index: 1050; border: 1px solid #dee2e6;"></div>
                            </div>
                        </div>

                        <div class="row g-3">
                            <div class="col-12">
                                <hr class="my-2">
                            </div>
                            <div class="col-12">
                                <label class="form-label fw-bold">Customer Full Name</label>
                                <input type="text" name="customer_name" id="lead_customer_name" class="form-control"
                                    required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold">Phone Number</label>
                                <input type="text" name="phone" id="lead_phone" class="form-control" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold">Email Address</label>
                                <input type="email" name="email" id="lead_email" class="form-control">
                            </div>
                            <div class="col-md-12">
                                <label class="form-label fw-bold">Lead Source (Where did they get our info?)</label>
                                <input type="text" name="source" class="form-control"
                                    placeholder="e.g. Website, Instagram, Referral...">
                            </div>
                            <div class="col-12">
                                <label class="form-label fw-bold">Product / Service Interest</label>
                                <input type="text" name="product_requested" class="form-control"
                                    placeholder="What are they looking for?">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold">Assign to Seller</label>
                                @if(auth()->user()->role === 'saler')
                                    <input type="text" class="form-control bg-light" value="{{ auth()->user()->name }}"
                                        readonly>
                                    <input type="hidden" name="assigned_seller_id" value="{{ auth()->id() }}">
                                @else
                                    <select name="assigned_seller_id" class="form-select">
                                        <option value="">--- UNASSIGNED ---</option>
                                        @foreach($sellers as $seller)
                                            <option value="{{ $seller->id }}">{{ $seller->name }}</option>
                                        @endforeach
                                    </select>
                                @endif
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold">Scheduled Follow-up</label>
                                <input type="date" name="follow_up_date" class="form-control"
                                    value="{{ date('Y-m-d', strtotime('+3 days')) }}">
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-light border" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary" data-no-global-handler>Create Lead</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    @push('styles')
        <style>
            .container-fluid {
                font-size: 12px;
            }

            .table thead th {
                font-weight: 700;
                font-size: 12px;
                color: #495057;
                background: #f8f9fa;
                border-top: none;
            }

            .form-label {
                font-size: 12px;
                margin-bottom: 0.4rem;
            }

            .form-control,
            .form-select {
                font-size: 12px;
            }

            .btn {
                font-size: 12px;
            }

            .badge {
                font-weight: 600;
                letter-spacing: 0.2px;
            }

            .modal-title {
                font-size: 16px;
                font-weight: 700;
            }

            .avatar-circle {
                width: 32px;
                height: 32px;
                background-color: #0d6efd;
                color: white;
                border-radius: 50%;
                display: flex;
                align-items: center;
                justify-content: center;
                font-weight: 700;
                font-size: 12px;
            }

            .x-small {
                font-size: 10px !important;
            }
        </style>
    @endpush

    @push('scripts')
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                const searchInput = document.getElementById('customerSearch');
                const resultsContainer = document.getElementById('customerResults');
                const nameInput = document.getElementById('lead_customer_name');
                const phoneInput = document.getElementById('lead_phone');
                const emailInput = document.getElementById('lead_email');

                let timeout = null;

                searchInput.addEventListener('input', function () {
                    clearTimeout(timeout);
                    const query = this.value.trim();

                    if (query.length < 2) {
                        resultsContainer.classList.add('d-none');
                        return;
                    }

                    timeout = setTimeout(() => {
                        fetch(`{{ route('admin.pos.customers.search') }}?query=${encodeURIComponent(query)}`)
                            .then(response => response.json())
                            .then(data => {
                                resultsContainer.innerHTML = '';
                                if (data.length > 0) {
                                    data.forEach(customer => {
                                        const item = document.createElement('button');
                                        item.type = 'button';
                                        item.className = 'list-group-item list-group-item-action border-0 py-2 small';
                                        item.innerHTML = `
                                                                                                <div class="d-flex justify-content-between align-items-center">
                                                                                                    <div class="fw-bold text-dark">${customer.name}</div>
                                                                                                    <div class="text-muted" style="font-size: 11px;">${customer.phone}</div>
                                                                                                </div>
                                                                                            `;
                                        item.onclick = () => {
                                            nameInput.value = customer.name;
                                            phoneInput.value = customer.phone;
                                            emailInput.value = customer.email || '';
                                            resultsContainer.classList.add('d-none');
                                            searchInput.value = customer.name;
                                        };
                                        resultsContainer.appendChild(item);
                                    });
                                    resultsContainer.classList.remove('d-none');
                                } else {
                                    resultsContainer.classList.add('d-none');
                                }
                            });
                    }, 300);
                });

                document.addEventListener('click', function (e) {
                    if (!searchInput.contains(e.target) && !resultsContainer.contains(e.target)) {
                        resultsContainer.classList.add('d-none');
                    }
                });

                // Auto-prefill logic from URL parameters (e.g., from Cash Flow)
                const urlParams = new URLSearchParams(window.location.search);
                if (urlParams.has('auto_create')) {
                    const name = urlParams.get('name');
                    const phone = urlParams.get('phone');
                    const email = urlParams.get('email');

                    if (name) nameInput.value = name;
                    if (phone) phoneInput.value = phone;
                    if (email) emailInput.value = email;

                    // Open the modal automatically
                    const modalEl = document.getElementById('addLeadModal');
                    const addLeadModal = new bootstrap.Modal(modalEl);
                    addLeadModal.show();

                    // Clean up URL parameters to prevent re-opening on refresh
                    window.history.replaceState({}, document.title, window.location.pathname);
                }

                // Generic form processing handler
                document.querySelectorAll('form').forEach(form => {
                    form.addEventListener('submit', function () {
                        const btn = form.querySelector('button[type="submit"]');
                        if (btn && btn.querySelector('.loading-state')) {
                            const normal = btn.querySelector('.normal-state');
                            const loading = btn.querySelector('.loading-state');
                            normal.classList.add('d-none');
                            loading.classList.remove('d-none');
                            btn.disabled = true;
                        }
                    });
                });

                // Message Modal Logic
                const msgSearchInput = document.getElementById('msgCustomerSearch');
                const msgResultsContainer = document.getElementById('msgCustomerResults');
                const selectedList = document.getElementById('selected_customers_list');
                const idsContainer = document.getElementById('selected_ids_container');
                let msgTimeout = null;

                window.toggleRecipientType = function () {
                    const type = document.getElementById('msg_recipient_type').value;
                    const specificDiv = document.getElementById('specific_customer_search');
                    if (type === 'selected') {
                        specificDiv.classList.remove('d-none');
                    } else {
                        specificDiv.classList.add('d-none');
                    }
                };

                window.applyTemplate = function () {
                    const select = document.getElementById('msg_template_id');
                    const content = document.getElementById('msg_content');
                    const selectedOption = select.options[select.selectedIndex];
                    if (selectedOption.dataset.content) {
                        content.value = selectedOption.dataset.content;
                        updateCharCount();
                    }
                };

                document.getElementById('msg_content').addEventListener('input', updateCharCount);

                function updateCharCount() {
                    const len = document.getElementById('msg_content').value.length;
                    document.getElementById('charCount').innerText = len + ' characters';
                }

                // Customer Search for Messaging
                if (msgSearchInput) {
                    msgSearchInput.addEventListener('input', function () {
                        clearTimeout(msgTimeout);
                        const query = this.value.trim();
                        if (query.length < 2) {
                            msgResultsContainer.classList.add('d-none');
                            return;
                        }

                        msgTimeout = setTimeout(() => {
                            fetch(`{{ route('admin.pos.customers.search') }}?query=${encodeURIComponent(query)}`)
                                .then(r => r.json())
                                .then(data => {
                                    msgResultsContainer.innerHTML = '';
                                    if (data.length > 0) {
                                        data.forEach(c => {
                                            const btn = document.createElement('button');
                                            btn.type = 'button';
                                            btn.className = 'list-group-item list-group-item-action py-2';
                                            btn.innerHTML = `<strong>${c.name}</strong> <small class='text-muted'>${c.phone}</small>`;
                                            btn.onclick = () => addCustomerToMessage(c);
                                            msgResultsContainer.appendChild(btn);
                                        });
                                        msgResultsContainer.classList.remove('d-none');
                                    } else {
                                        msgResultsContainer.classList.add('d-none');
                                    }
                                });
                        }, 300);
                    });
                }

                function addCustomerToMessage(customer) {
                    // Check if already added
                    if (document.querySelector(`input[name="customer_ids[]"][value="${customer.id}"]`)) return;

                    // Add hidden input
                    const input = document.createElement('input');
                    input.type = 'hidden';
                    input.name = 'customer_ids[]';
                    input.value = customer.id;
                    idsContainer.appendChild(input);

                    // Add visual tag
                    const tag = document.createElement('div');
                    tag.className = 'badge bg-light text-dark border p-2 d-flex align-items-center gap-2';
                    tag.innerHTML = `<span>${customer.name}</span> <i class="fas fa-times text-danger cursor-pointer" onclick="removeCustomerFromMessage('${customer.id}', this)"></i>`;
                    selectedList.appendChild(tag);

                    msgSearchInput.value = '';
                    msgResultsContainer.classList.add('d-none');
                }

                window.removeCustomerFromMessage = function (id, el) {
                    el.parentElement.remove();
                    const input = document.querySelector(`input[name="customer_ids[]"][value="${id}"]`);
                    if (input) input.remove();
                };

            });
        </script>
    @endpush
@endsection