@extends('layouts.admin')

@section('title', 'Gatekeeper - Product Movements')

@section('content')
<div class="container-fluid px-4 py-4">
    <!-- Modern Header & Actions -->
    <div class="row mb-3 align-items-end">
        <div class="col-lg-6 mb-2 mb-lg-0">
            <h4 class="fw-bold mb-0">Gatekeeper Logs</h4>
            <p class="text-muted small mb-0">Monitor all product movements (IN/OUT) and personnel access</p>
        </div>
        <div class="col-lg-6 text-lg-end">
            <div class="d-flex flex-wrap justify-content-lg-end gap-2">
                <button class="btn btn-outline-primary btn-sm px-3 fw-bold" type="button" data-bs-toggle="collapse" data-bs-target="#filterCollapse">
                    <i class="fas fa-filter me-1"></i> FILTERS 
                    @if(request()->anyFilled(['type', 'product_name', 'person_type', 'date_from', 'date_to']))
                        <span class="badge bg-primary ms-1">Active</span>
                    @endif
                </button>
                
                <a href="{{ route('gatekeeper.movements.create', ['type' => 'in']) }}" class="btn btn-success btn-sm px-3 fw-bold shadow-sm">
                    <i class="fas fa-arrow-down me-1"></i> RECORD IN
                </a>
                <a href="{{ route('gatekeeper.movements.create', ['type' => 'out']) }}" class="btn btn-warning btn-sm px-3 fw-bold shadow-sm text-dark">
                    <i class="fas fa-arrow-up me-1"></i> RECORD OUT
                </a>
            </div>
        </div>
    </div>

    <!-- Modern Collapsable Filters -->
    <div class="collapse {{ request()->anyFilled(['type', 'product_name', 'person_type', 'date_from', 'date_to']) ? 'show' : '' }} mb-4" id="filterCollapse">
        <div class="card border-0 shadow-sm border-top border-4 border-primary">
            <div class="card-body bg-light p-3">
                <form action="{{ route('gatekeeper.movements.index') }}" method="GET" class="row g-2" data-no-global-handler>
                    <div class="col-12 col-md-3">
                        <label class="form-label fw-bold x-small text-uppercase mb-1">Product / Item Name</label>
                        <div class="input-group input-group-sm">
                            <span class="input-group-text bg-white border-end-0"><i class="fas fa-search text-muted"></i></span>
                            <input type="text" name="product_name" class="form-control border-start-0" placeholder="Search product..." value="{{ request('product_name') }}">
                        </div>
                    </div>
                    
                    <div class="col-6 col-md-2">
                        <label class="form-label fw-bold x-small text-uppercase mb-1">Direction</label>
                        <select name="type" class="form-select form-select-sm">
                            <option value="">All Direction</option>
                            <option value="in" {{ request('type') == 'in' ? 'selected' : '' }}>Incoming (IN)</option>
                            <option value="out" {{ request('type') == 'out' ? 'selected' : '' }}>Outgoing (OUT)</option>
                        </select>
                    </div>

                    <div class="col-6 col-md-2">
                        <label class="form-label fw-bold x-small text-uppercase mb-1">Person Type</label>
                        <select name="person_type" class="form-select form-select-sm">
                            <option value="">All Types</option>
                            <option value="Staff" {{ request('person_type') == 'Staff' ? 'selected' : '' }}>Staff</option>
                            <option value="Registered Delivery" {{ request('person_type') == 'Registered Delivery' ? 'selected' : '' }}>Registered Delivery</option>
                            <option value="Customer" {{ request('person_type') == 'Customer' ? 'selected' : '' }}>Customer</option>
                            <option value="External Person" {{ request('person_type') == 'External Person' ? 'selected' : '' }}>External Person</option>
                        </select>
                    </div>

                    <div class="col-6 col-md-2">
                        <label class="form-label fw-bold x-small text-uppercase mb-1">From Date</label>
                        <input type="date" name="date_from" class="form-control form-control-sm" value="{{ request('date_from') }}">
                    </div>

                    <div class="col-6 col-md-1">
                        <label class="form-label fw-bold x-small text-uppercase mb-1">To Date</label>
                        <input type="date" name="date_to" class="form-control form-control-sm" value="{{ request('date_to') }}">
                    </div>

                    <div class="col-12 d-md-none mt-2 d-flex gap-2 align-items-stretch">
                        <button type="submit" class="btn btn-primary btn-sm fw-bold flex-grow-1">APPLY FILTERS</button>
                        <x-report-export-menu
                            :print-url="route('gatekeeper.movements.print-filtered', request()->all())"
                            :pdf-url="route('gatekeeper.movements.pdf', request()->all())"
                            :excel-url="route('gatekeeper.movements.excel', request()->all())"
                        />
                        <a href="{{ route('gatekeeper.movements.index') }}" class="btn btn-light btn-sm">RESET</a>
                    </div>

                    <div class="col-md-auto d-none d-md-flex align-items-end ms-auto gap-2">
                        <div class="btn-group shadow-sm">
                            <button type="submit" class="btn btn-primary btn-sm px-3 fw-bold">APPLY</button>
                            <a href="{{ route('gatekeeper.movements.index') }}" class="btn btn-light btn-sm px-3 fw-bold border">RESET</a>
                        </div>
                        <x-report-export-menu
                            :print-url="route('gatekeeper.movements.print-filtered', request()->all())"
                            :pdf-url="route('gatekeeper.movements.pdf', request()->all())"
                            :excel-url="route('gatekeeper.movements.excel', request()->all())"
                        />
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <style>
        .container-fluid { font-size: 13px; }
        h4 { font-size: 1.25rem !important; font-weight: 700; }
        .table th { font-size: 11px; text-transform: uppercase; letter-spacing: 0.8px; font-weight: 700; color: #666; }
        .table td { font-size: 13px; }
        .btn { font-weight: 600; letter-spacing: 0.2px; }
        .form-control, .form-select { font-size: 13px !important; border-radius: 6px; }
        .x-small { font-size: 10px !important; }
        .card { border-radius: 10px; }
        
        .transition-icon {
            transition: transform 0.3s ease;
        }
        [aria-expanded="true"] .transition-icon {
            transform: rotate(180deg);
        }
    </style>

    <!-- Movements Table -->
    <div class="card border-0 shadow-lg overflow-hidden" style="border-radius: 16px;">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="bg-light sticky-top">
                    <tr>
                        <th class="ps-4 py-3 text-secondary text-uppercase border-0" style="font-size: 11px; font-weight: 600;">Type</th>
                        <th class="py-3 text-secondary text-uppercase border-0" style="font-size: 11px; font-weight: 600;">Date & Time</th>
                        <th class="py-3 text-secondary text-uppercase border-0" style="font-size: 11px; font-weight: 600;">Product</th>
                        <th class="py-3 text-secondary text-uppercase border-0" style="font-size: 11px; font-weight: 600;">Qty</th>
                        <th class="py-3 text-secondary text-uppercase border-0" style="font-size: 11px; font-weight: 600;">Handler (Who)</th>
                        <th class="py-3 text-secondary text-uppercase border-0" style="font-size: 11px; font-weight: 600;">Context (Source/Dest)</th>
                        <th class="pe-4 py-3 text-secondary text-uppercase border-0 text-end" style="font-size: 11px; font-weight: 600;">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($movements as $movement)
                    <tr>
                        <td class="ps-4">
                            @if($movement->type === 'in')
                                <span class="badge bg-success-subtle text-success border border-success-subtle rounded-3 px-3" style="font-size: 11px;">
                                    <i class="fas fa-arrow-down me-1"></i> IN
                                </span>
                            @else
                                <span class="badge bg-warning-subtle text-warning border border-warning-subtle rounded-3 px-3" style="font-size: 11px;">
                                    <i class="fas fa-arrow-up me-1"></i> OUT
                                </span>
                            @endif
                        </td>
                        <td>
                            <div class="d-flex flex-column">
                                <span class="text-dark" style="font-size: 13px; font-weight: 500;">{{ $movement->movement_date->format('M d, Y') }}</span>
                                <span class="text-muted" style="font-size: 11px;">{{ $movement->movement_date->format('h:i A') }}</span>
                            </div>
                        </td>
                        <td>
                            <span class="text-dark d-block" style="font-size: 13px; font-weight: 600;">{{ $movement->product_name }}</span>
                            @if($movement->unit_price)
                            <span class="text-muted" style="font-size: 11px;">{{ number_format($movement->unit_price, 2) }} / unit</span>
                            @endif
                        </td>
                        <td>
                            <span style="font-size: 14px; font-weight: 600;">{{ $movement->quantity }}</span>
                        </td>
                        <td class="text-start">
                            <div class="d-flex align-items-center">
                                <div class="avatar-circle bg-light text-secondary rounded-circle d-flex align-items-center justify-content-center me-2" style="width:28px; height:28px;">
                                    <i class="fas fa-user" style="font-size: 10px;"></i>
                                </div>
                                <div>
                                    <div style="font-size: 13px; font-weight: 500;">{{ $movement->handler_name }}</div>
                                    <div class="text-muted" style="font-size: 10px;">{{ $movement->handler_type }}</div>
                                </div>
                            </div>
                        </td>
                        <td>
                            <!-- Context displays Source for IN, Recipient for OUT -->
                            @if($movement->type === 'in')
                                <div style="font-size: 12px;">
                                    <span class="text-muted">From:</span>
                                    <span style="font-weight: 500;">{{ $movement->source_name ?: '-' }}</span>
                                    @if($movement->source_identifier)
                                        <div class="text-muted mt-1" style="font-size:10px;">{{ $movement->source_identifier }}</div>
                                    @endif
                                    @if($movement->source_type)
                                    <span class="badge bg-light text-secondary border ms-1" style="font-size:10px;">{{ $movement->source_type }}</span>
                                    @endif
                                </div>
                            @else
                                <div style="font-size: 12px;">
                                    <span class="text-muted">To:</span>
                                    <span style="font-weight: 500;">{{ $movement->recipient_name ?: '-' }}</span>
                                    @if($movement->recipient_identifier)
                                        <div class="text-muted mt-1" style="font-size:10px;">{{ $movement->recipient_identifier }}</div>
                                    @endif
                                    @if($movement->recipient_type)
                                    <span class="badge bg-light text-secondary border ms-1" style="font-size:10px;">{{ $movement->recipient_type }}</span>
                                    @endif
                                    @if($movement->delivery_method)
                                    <div class="text-muted fst-italic mt-1" style="font-size:10px;">via {{ $movement->delivery_method }}</div>
                                    @endif
                                </div>
                            @endif
                        </td>
                        <td class="pe-4 text-end">
                            <a href="{{ route('gatekeeper.movements.show', $movement) }}" class="btn btn-sm btn-outline-primary rounded-circle" style="font-size: 11px;" title="View movement details">
                                <i class="fas fa-eye"></i>
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="text-center py-5">
                            <div class="d-flex flex-column align-items-center text-muted">
                                <i class="fas fa-clipboard-list fa-3x mb-3 opacity-25"></i>
                                <span class="fw-medium">No movement records found</span>
                                <span class="small">Try adjusting your filters or record a new movement.</span>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($movements->hasPages())
        <div class="card-footer bg-white border-top border-light py-3">
            {{ $movements->links() }}
        </div>
        @endif
    </div>

    <!-- Movement Details Modal -->
    <div class="modal fade" id="movementModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden bg-white">
                <div class="modal-header border-0 pb-0 pt-4 px-4">
                    <h5 class="modal-title fw-bold text-dark">
                        <i class="fas fa-info-circle me-2 text-primary"></i>Movement Details
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4 pt-3">
                    <div class="text-center mb-4">
                        <div id="modal-type-badge" class="mb-2"></div>
                        <h4 id="modal-product" class="fw-bold text-dark mb-0"></h4>
                        <div class="text-muted small" id="modal-date"></div>
                    </div>

                    <div class="row g-3">
                        <div class="col-6">
                            <div class="p-3 bg-light rounded-3 h-100">
                                <label class="small text-muted text-uppercase fw-bold ls-1 mb-1">Quantity</label>
                                <div id="modal-qty" class="fs-5 fw-bold text-dark"></div>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="p-3 bg-light rounded-3 h-100">
                                <label class="small text-muted text-uppercase fw-bold ls-1 mb-1">Value</label>
                                <div id="modal-value" class="fs-5 fw-bold text-dark"></div>
                            </div>
                        </div>
                    </div>

                    <div class="mt-4">
                        <h6 class="fw-bold border-bottom pb-2 mb-3">Handlers & Context</h6>
                        <div class="d-flex align-items-center mb-3">
                            <div class="avatar-circle bg-primary bg-opacity-10 text-primary rounded-circle d-flex align-items-center justify-content-center me-3" style="width:40px; height:40px;">
                                <i class="fas fa-user"></i>
                            </div>
                            <div>
                                <small class="text-muted d-block">Handled By</small>
                                <span id="modal-handler" class="fw-medium text-dark"></span>
                                <span id="modal-handler-type" class="badge bg-secondary-subtle text-secondary ms-1 small"></span>
                            </div>
                        </div>
                        
                        <div class="d-flex align-items-center">
                            <div class="avatar-circle bg-warning bg-opacity-10 text-warning rounded-circle d-flex align-items-center justify-content-center me-3" style="width:40px; height:40px;">
                                <i class="fas fa-map-marker-alt"></i>
                            </div>
                            <div>
                                <small class="text-muted d-block" id="modal-context-label">Context</small>
                                <span id="modal-context-name" class="fw-medium text-dark"></span>
                                <div id="modal-context-meta" class="small text-muted fst-italic"></div>
                            </div>
                        </div>
                    </div>

                    <div class="mt-4 p-3 border rounded-3 border-dashed" id="modal-notes-container">
                        <label class="small text-muted text-uppercase fw-bold ls-1 mb-1"><i class="fas fa-sticky-note me-1"></i> Notes</label>
                        <p id="modal-notes" class="mb-0 text-dark small"></p>
                    </div>
                </div>
                <div class="modal-footer border-0 px-4 pb-4">
                    <button type="button" class="btn btn-light rounded-3 px-4 fw-bold" style="font-size: 13px;" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const modal = new bootstrap.Modal(document.getElementById('movementModal'));
        
        document.querySelectorAll('.view-movement-btn').forEach(btn => {
            btn.addEventListener('click', function() {
                const data = JSON.parse(this.getAttribute('data-movement'));
                const formattedDate = this.getAttribute('data-formatted-date');
                
                // Populate Modal Data
                document.getElementById('modal-product').textContent = data.product_name;
                document.getElementById('modal-date').textContent = formattedDate;
                document.getElementById('modal-qty').textContent = data.quantity;
                document.getElementById('modal-value').textContent = data.unit_price ? (parseFloat(data.unit_price).toLocaleString() + ' TZS') : '-';
                
                // Type Badge
                const typeContainer = document.getElementById('modal-type-badge');
                if (data.type === 'in') {
                    typeContainer.innerHTML = '<span class="badge bg-success text-white rounded-pill px-3 py-2"><i class="fas fa-arrow-down me-1"></i> INCOMING</span>';
                } else {
                    typeContainer.innerHTML = '<span class="badge bg-warning text-dark rounded-pill px-3 py-2"><i class="fas fa-arrow-up me-1"></i> OUTGOING</span>';
                }

                // Handler
                document.getElementById('modal-handler').textContent = data.handler_name;
                document.getElementById('modal-handler-type').textContent = data.handler_type;

                // Context
                const contextLabel = document.getElementById('modal-context-label');
                const contextName = document.getElementById('modal-context-name');
                const contextMeta = document.getElementById('modal-context-meta');
                
                if (data.type === 'in') {
                    contextLabel.textContent = 'Source (' + (data.source_type || 'Unknown') + ')';
                    contextName.textContent = data.source_name || '-';
                    contextMeta.textContent = '';
                } else {
                    contextLabel.textContent = 'Recipient (' + (data.recipient_type || 'Unknown') + ')';
                    contextName.textContent = data.recipient_name || '-';
                    const metaParts = [];
                    if (data.delivery_method) metaParts.push('via ' + data.delivery_method);
                    if (data.recipient_identifier) metaParts.push('ID/Phone: ' + data.recipient_identifier);
                    contextMeta.textContent = metaParts.join(' | ');
                }

                // Notes
                const notesContainer = document.getElementById('modal-notes-container');
                const notesEl = document.getElementById('modal-notes');
                if (data.notes) {
                    notesContainer.style.display = 'block';
                    notesEl.textContent = data.notes;
                } else {
                    notesContainer.style.display = 'none';
                }
                
                modal.show();
            });
        });
    });
</script>
@endsection
