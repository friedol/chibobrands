@extends('layouts.admin')

@section('page-title', 'Orders Management')

@push('styles')
<style>
    /* Mobile Responsive - Font Size Reductions */
    @media (max-width: 768px) {
        .container-fluid { padding: 0.5rem; }
        h2 { font-size: 1.25rem !important; }
        .text-muted { font-size: 0.75rem !important; }
        .card-header h6 { font-size: 0.8rem !important; }
        .badge { font-size: 0.65rem !important; padding: 0.3rem 0.5rem !important; }
        .btn { font-size: 0.75rem !important; padding: 0.375rem 0.625rem !important; }
        .btn-sm { font-size: 0.7rem !important; padding: 0.25rem 0.5rem !important; }
        .table th, .table td { font-size: 0.75rem !important; padding: 0.375rem 0.5rem !important; }
        strong { font-size: 0.85rem !important; }
        small { font-size: 0.65rem !important; }
    }
    @media (max-width: 575.98px) {
        .container-fluid { padding: 0.25rem; }
        h1, h2 { font-size: 1.1rem !important; }
        .text-muted { font-size: 0.7rem !important; }
        .card-header { padding: 0.375rem 0.5rem !important; }
        .card-body { padding: 0.75rem !important; }
        .form-label, .form-control, .form-select { font-size: 0.75rem !important; }
        .modal-body, .modal-header { padding: 0.75rem !important; }
    }
</style>
@endpush

@section('content')
<div class="container-fluid px-4 py-4">
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex flex-row justify-content-between align-items-center mb-3 gap-2">
                <div class="flex-grow-1">
                    <h1 class="h5 mb-0 text-dark fw-bold" style="font-family: 'Nunito Sans', sans-serif;">Orders Management</h1>
                    <p class="text-muted mb-0 small" style="font-size: 13px;">Manage customer orders and processing</p>
                </div>
                <div class="d-flex gap-2">
                    <a href="{{ route('admin.pos.index') }}" class="btn btn-dark btn-sm rounded-pill px-3 shadow-sm" style="font-size: 12px;">
                        <i class="fas fa-plus me-1"></i>ADD NEW
                    </a>
                    <button type="button" class="btn btn-outline-success btn-sm rounded-pill px-3 shadow-none" onclick="exportToExcel()" style="font-size: 12px;">
                        <i class="fas fa-file-excel me-1"></i>Export
                    </button>
                    <a href="{{ route('admin.orders.index') }}" class="btn btn-outline-dark btn-sm rounded-pill px-3 shadow-none" style="font-size: 12px;">
                        <i class="fas fa-undo me-1"></i>Reset
                    </a>
                    @if(auth()->user()->hasPermission('delete_all_orders'))
                    <button type="button" class="btn btn-danger btn-sm rounded-pill px-3 shadow-none" style="font-size: 12px;"
                            data-bs-toggle="modal" data-bs-target="#deleteAllOrdersModal">
                        <i class="fas fa-trash-alt me-1"></i>Delete All
                    </button>
                    @endif
                </div>
            </div>

            <!-- Search and Filter Toggle -->
            <div class="d-flex justify-content-end mb-3">
                <button class="btn btn-outline-dark btn-sm rounded-pill" type="button" data-bs-toggle="collapse" data-bs-target="#filterCollapse" style="font-size: 12px;">
                    <i class="fas fa-filter me-1"></i>Filters
                </button>
            </div>

            <div class="collapse {{ request()->has('search') || request()->has('status') ? 'show' : '' }} mb-3" id="filterCollapse">
                <div class="card border" style="box-shadow: 0 0.125rem 0.25rem rgba(0,0,0,0.075);">
                    <div class="card-body bg-white p-3">
                        <form action="{{ route('admin.orders.index') }}" method="GET" class="row g-2" id="filterForm">
                            <div class="col-12 col-md-3 d-flex flex-column">
                                <label class="form-label fw-bold text-dark text-uppercase mb-1" style="font-size: 11px;">Search</label>
                                <div class="input-group input-group-sm flex-grow-1">
                                    <span class="input-group-text bg-white border-end-0" style="font-size: 12px;"><i class="fas fa-search text-muted"></i></span>
                                    <input type="text" name="search" id="search" class="form-control border-start-0" placeholder="Order code, Name, Phone..." value="{{ request('search') }}" style="font-size: 12px;">
                                </div>
                            </div>
                            <div class="col-6 col-md-2 d-flex flex-column">
                                <label class="form-label fw-bold text-dark text-uppercase mb-1" style="font-size: 11px;">Approval</label>
                                <select name="status" class="form-select form-select-sm flex-grow-1" style="font-size: 12px;">
                                    <option value="">All</option>
                                    <option value="requested" {{ request('status') === 'requested' ? 'selected' : '' }}>Pending</option>
                                    <option value="approved" {{ request('status') === 'approved' ? 'selected' : '' }}>Approved</option>
                                    <option value="cancelled" {{ request('status') === 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                                </select>
                            </div>
                            <div class="col-6 col-md-2 d-flex flex-column">
                                <label class="form-label fw-bold text-dark text-uppercase mb-1" style="font-size: 11px;">Payment</label>
                                <select name="payment_status" class="form-select form-select-sm flex-grow-1" style="font-size: 12px;">
                                    <option value="">All</option>
                                    <option value="paid" {{ request('payment_status') === 'paid' ? 'selected' : '' }}>Paid</option>
                                    <option value="unpaid" {{ request('payment_status') === 'unpaid' ? 'selected' : '' }}>Unpaid</option>
                                    <option value="pending" {{ request('payment_status') === 'pending' ? 'selected' : '' }}>Pending</option>
                                </select>
                            </div>
                            <div class="col-6 col-md-3 d-flex flex-column">
                                <label class="form-label fw-bold text-dark text-uppercase mb-1" style="font-size: 11px;">Date Range</label>
                                <div class="input-group input-group-sm flex-grow-1">
                                    <input type="date" name="date_from" class="form-control" value="{{ request('date_from') }}" style="font-size: 12px;">
                                    <span class="input-group-text bg-white" style="font-size: 12px;">-</span>
                                    <input type="date" name="date_to" class="form-control" value="{{ request('date_to') }}" style="font-size: 12px;">
                                </div>
                            </div>
                            <div class="col-6 col-md-2 d-flex align-items-end">
                                <button type="submit" class="btn btn-dark btn-sm w-100" style="font-size: 12px;">Apply</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
            
            <!-- Stats Cards -->
            <div class="row g-2 mb-3">
                <div class="col-6 col-md-3">
                    <div class="card border-0 h-100" style="background-color: #f8f9fa;">
                        <div class="card-body p-3">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <div class="text-muted small fw-bold text-uppercase" style="font-size: 11px;">Total Orders</div>
                                    <div class="h4 mb-0 fw-bold text-dark mt-1">{{ $stats['total'] ?? 0 }}</div>
                                </div>
                                <div class="rounded-circle bg-white d-flex align-items-center justify-content-center" style="width: 40px; height: 40px; box-shadow: 0 2px 4px rgba(0,0,0,0.05);">
                                    <i class="fas fa-shopping-bag text-primary"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-6 col-md-3">
                    <div class="card border-0 h-100" style="background-color: #f8f9fa;">
                        <div class="card-body p-3">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <div class="text-muted small fw-bold text-uppercase" style="font-size: 11px;">Pending</div>
                                    <div class="h4 mb-0 fw-bold text-warning mt-1">{{ $stats['pending'] ?? 0 }}</div>
                                </div>
                                <div class="rounded-circle bg-white d-flex align-items-center justify-content-center" style="width: 40px; height: 40px; box-shadow: 0 2px 4px rgba(0,0,0,0.05);">
                                    <i class="fas fa-clock text-warning"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-6 col-md-3">
                    <div class="card border-0 h-100" style="background-color: #f8f9fa;">
                        <div class="card-body p-3">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <div class="text-muted small fw-bold text-uppercase" style="font-size: 11px;">Approved</div>
                                    <div class="h4 mb-0 fw-bold text-success mt-1">{{ $stats['approved'] ?? 0 }}</div>
                                </div>
                                <div class="rounded-circle bg-white d-flex align-items-center justify-content-center" style="width: 40px; height: 40px; box-shadow: 0 2px 4px rgba(0,0,0,0.05);">
                                    <i class="fas fa-check-circle text-success"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-6 col-md-3">
                    <div class="card border-0 h-100" style="background-color: #f8f9fa;">
                        <div class="card-body p-3">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <div class="text-muted small fw-bold text-uppercase" style="font-size: 11px;">Paid Orders</div>
                                    <div class="h4 mb-0 fw-bold text-info mt-1">{{ $stats['paid'] ?? 0 }}</div>
                                </div>
                                <div class="rounded-circle bg-white d-flex align-items-center justify-content-center" style="width: 40px; height: 40px; box-shadow: 0 2px 4px rgba(0,0,0,0.05);">
                                    <i class="fas fa-money-bill-wave text-info"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Orders Table -->
    <div class="card border-0" style="box-shadow: 0 0.125rem 0.25rem rgba(0,0,0,0.075);">
        <div class="card-header bg-white border-bottom py-2">
            <h6 class="mb-0 fw-bold text-dark" style="font-size: 14px;">
                <i class="fas fa-list me-2"></i>Order List
            </h6>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="bg-white border-bottom">
                         <tr>
                            <th class="py-2 px-3 align-middle" style="font-size: 12px;">Order Code</th>
                            <th class="py-2 px-3 align-middle" style="font-size: 12px;">Customer</th>
                            <th class="py-2 px-3 align-middle" style="font-size: 12px;">Amount</th>
                            <th class="py-2 px-3 align-middle" style="font-size: 12px;">Status</th>
                            <th class="py-2 px-3 align-middle" style="font-size: 12px;">Payment</th>
                            <th class="py-2 px-3 align-middle" style="font-size: 12px;">Date</th>
                            <th class="py-2 px-3 align-middle" style="font-size: 12px;">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($orders as $order)
                             <tr class="border-bottom" style="{{ $order->user && $order->user->email === 'guest@chibobrand.com' ? 'background-color: #fff3cd;' : '' }}">
                                <td class="py-2 px-3 align-middle">
                                    <a href="{{ route('admin.orders.show', $order->order_code) }}" class="fw-bold text-dark text-decoration-none" style="font-size: 12px;">
                                        {{ $order->order_code }}
                                    </a>
                                    @if($order->user && $order->user->email === 'guest@chibobrand.com')
                                        <span class="badge bg-warning text-dark ms-1" style="font-size: 10px;">GUEST</span>
                                    @endif
                                </td>
                                <td class="py-2 px-3 align-middle">
                                    <div class="d-flex flex-column">
                                        <span class="fw-bold text-dark" style="font-size: 12px;">{{ $order->user->name ?? 'Guest' }}</span>
                                        <small class="text-muted" style="font-size: 11px;">{{ $order->user->phone ?? '-' }}</small>
                                    </div>
                                </td>
                                <td class="py-2 px-3 align-middle fw-bold text-success" style="font-size: 12px;">
                                    {{ number_format($order->total_amount) }}
                                </td>
                                <td class="py-2 px-3 align-middle">
                                    @php
                                        $approvalColor = match($order->approval_status) {
                                            'approved' => 'success',
                                            'cancelled' => 'danger',
                                            default => 'warning'
                                        };
                                    @endphp
                                    <span class="badge bg-{{ $approvalColor }} text-white" style="font-size: 11px; font-weight: 500;">
                                        {{ ucfirst($order->approval_status) }}
                                    </span>
                                </td>
                                <td class="py-2 px-3 align-middle">
                                     @php
                                        $payColor = match($order->payment_status) {
                                            'paid' => 'success',
                                            'unpaid' => 'danger',
                                            default => 'warning'
                                        };
                                    @endphp
                                    <span class="badge border text-{{ $payColor }} bg-{{ $payColor }} bg-opacity-10" style="font-size: 11px;">
                                        {{ ucfirst($order->payment_status) }}
                                    </span>
                                </td>
                                <td class="py-2 px-3 align-middle">
                                    <div class="text-dark" style="font-size: 12px;">{{ $order->created_at->format('M j, Y') }}</div>
                                    <small class="text-muted" style="font-size: 10px;">{{ $order->created_at->format('H:i') }}</small>
                                </td>
                                <td class="py-2 px-3 align-middle">
                                    <div class="d-flex justify-content-start gap-1 flex-wrap">
                                        <!-- Actions -->
                                        <a href="{{ route('admin.orders.show', $order->order_code) }}" class="btn btn-sm bg-white border text-primary" title="View Details" style="width: 28px; height: 28px; padding: 0; display: inline-flex; align-items: center; justify-content: center;">
                                            <i class="fas fa-eye" style="font-size: 12px;"></i>
                                        </a>

                                        {{-- Direct Print Actions --}}
                                        <button type="button" onclick="printDirect('{{ route('admin.finance.invoices.proforma', $order->order_code) }}')" class="btn btn-sm bg-white border text-info" title="Print Proforma" style="width: 28px; height: 28px; padding: 0; display: inline-flex; align-items: center; justify-content: center;">
                                            <i class="fas fa-file-invoice" style="font-size: 12px;"></i>
                                        </button>
                                        
                                        @if($order->payment_status === 'paid' || $order->amount_paid > 0)
                                        <button type="button" onclick="printDirect('{{ route('admin.finance.invoices.sales', $order->order_code) }}')" class="btn btn-sm bg-white border text-success" title="Print Sales Invoice" style="width: 28px; height: 28px; padding: 0; display: inline-flex; align-items: center; justify-content: center;">
                                            <i class="fas fa-receipt" style="font-size: 12px;"></i>
                                        </button>
                                        @endif
                                                                                 @if($order->approval_status === 'requested')
                                            <!-- Approve -->
                                            <form method="POST" action="{{ route('admin.orders.approve', $order->order_code) }}" class="d-inline" id="approveForm{{ $order->id }}">
                                                @csrf
                                                <button type="button" class="btn btn-sm bg-white border text-success" title="Approve" onclick="modernConfirm('Approve order?', () => document.getElementById('approveForm{{ $order->id }}').submit())" style="width: 28px; height: 28px; padding: 0; display: inline-flex; align-items: center; justify-content: center;">
                                                    <i class="fas fa-check" style="font-size: 12px;"></i>
                                                </button>
                                            </form>
                                            <!-- Cancel -->
                                              <form method="POST" action="{{ route('admin.orders.cancel', $order->order_code) }}" class="d-inline" id="cancelForm{{ $order->id }}">
                                                @csrf
                                                <button type="button" class="btn btn-sm bg-white border text-danger" title="Cancel" onclick="modernConfirm('Cancel order?', () => document.getElementById('cancelForm{{ $order->id }}').submit())" style="width: 28px; height: 28px; padding: 0; display: inline-flex; align-items: center; justify-content: center;">
                                                    <i class="fas fa-times" style="font-size: 12px;"></i>
                                                </button>
                                            </form>
                                        @endif

                                        @if($order->approval_status === 'requested' || $order->type === 'proforma')
                                            <a href="{{ route('admin.orders.edit', $order->order_code) }}" class="btn btn-sm bg-white border text-warning" title="Edit" style="width: 28px; height: 28px; padding: 0; display: inline-flex; align-items: center; justify-content: center;">
                                                <i class="fas fa-edit" style="font-size: 12px;"></i>
                                            </a>
                                        @endif

                                        @if($order->type === 'proforma')
                                            <!-- Convert to Invoice -->
                                            <form method="POST" action="{{ route('admin.orders.convert', $order->order_code) }}" class="d-inline" id="convertForm{{ $order->id }}">
                                                @csrf
                                                <button type="button" class="btn btn-sm bg-white border text-primary" title="Convert to Invoice" onclick="modernConfirm('Convert this Proforma to a Sales Invoice?', () => document.getElementById('convertForm{{ $order->id }}').submit())" style="width: 28px; height: 28px; padding: 0; display: inline-flex; align-items: center; justify-content: center;">
                                                    <i class="fas fa-file-import" style="font-size: 12px;"></i>
                                                </button>
                                            </form>
                                        @endif

                                        <!-- Delete -->
                                        <form method="POST" action="{{ route('admin.orders.destroy', $order->order_code) }}" class="d-inline" id="deleteForm{{ $order->id }}">
                                            @csrf
                                            @method('DELETE')
                                            <button type="button" class="btn btn-sm bg-white border text-danger" title="Delete" onclick="modernConfirm('Are you sure you want to delete this order? This cannot be undone.', () => document.getElementById('deleteForm{{ $order->id }}').submit())" style="width: 28px; height: 28px; padding: 0; display: inline-flex; align-items: center; justify-content: center;">
                                                <i class="fas fa-trash" style="font-size: 12px;"></i>
                                            </button>
                                        </form>

                                         <a href="https://wa.me/255655392319?text={{ urlencode($order->generateWhatsAppMessage()) }}" target="_blank" class="btn btn-sm bg-white border text-success" title="WhatsApp" style="width: 28px; height: 28px; padding: 0; display: inline-flex; align-items: center; justify-content: center;">
                                            <i class="fab fa-whatsapp" style="font-size: 12px;"></i>
                                        </a>
                                        
                                        @if($templates->count() > 0 && ($order->user && $order->user->phone))
                                            <div class="btn-group" role="group">
                                                <button type="button" class="btn btn-sm bg-white border text-info dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false" title="Send" style="width: 28px; height: 28px; padding: 0; display: inline-flex; align-items: center; justify-content: center;">
                                                    <i class="fas fa-paper-plane" style="font-size: 11px;"></i>
                                                </button>
                                                <ul class="dropdown-menu dropdown-menu-end shadow border-0" style="font-size: 0.8rem; min-width: 180px;">
                                                    <li class="dropdown-header fw-bold text-uppercase x-small">Send Template</li>
                                                    @foreach($templates as $template)
                                                        <li>
                                                            <form action="{{ route('admin.message-templates.send') }}" method="POST">
                                                                @csrf
                                                                <input type="hidden" name="template_id" value="{{ $template->id }}">
                                                                <input type="hidden" name="customer_id" value="{{ $order->user->customer_id ?? \App\Models\Customer::where('phone', $order->user->phone)->first()?->id }}">
                                                                <button type="submit" class="dropdown-item py-2" data-no-global-handler>
                                                                    <i class="fas fa-comment-alt me-2 text-info opacity-50"></i>{{ $template->title }}
                                                                </button>
                                                            </form>
                                                        </li>
                                                    @endforeach
                                                </ul>
                                            </div>
                                        @endif
                                    </div>
                                </td>
                             </tr> 
                        @empty
                            <tr><td colspan="7" class="text-center text-muted py-4">No orders found</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            
            @if($orders->hasPages())
                <div class="d-flex justify-content-between align-items-center mt-3 pt-3 border-top bg-light px-3 py-2">
                    <div class="text-muted small fw-medium">
                        <i class="fas fa-info-circle me-1"></i>
                        Showing {{ $orders->firstItem() }} to {{ $orders->lastItem() }} of {{ $orders->total() }} orders
                    </div>
                    <div class="pagination-wrapper">
                        {{ $orders->appends(request()->query())->links('pagination::bootstrap-4') }}
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Add animation to table rows
    const tableRows = document.querySelectorAll('tbody tr');
    tableRows.forEach((row, index) => {
        row.style.opacity = '0';
        row.style.transform = 'translateX(-20px)';
        
        setTimeout(() => {
            row.style.transition = 'all 0.3s ease';
            row.style.opacity = '1';
            row.style.transform = 'translateX(0)';
            
            // Remove transform after animation to prevent stacking context issues with dropdowns
            setTimeout(() => {
                row.style.transform = '';
            }, 350);
        }, index * 30);
    });

    // Add hover effects to action buttons
    const actionButtons = document.querySelectorAll('.btn-group .btn');
    actionButtons.forEach(button => {
        button.addEventListener('mouseenter', function() {
            this.style.transform = 'scale(1.1)';
        });
        
        button.addEventListener('mouseleave', function() {
            this.style.transform = 'scale(1)';
        });
    });

    // Real-time search
    const searchInput = document.getElementById('search');
    let searchTimeout;
    searchInput.addEventListener('input', function() {
        clearTimeout(searchTimeout);
        searchTimeout = setTimeout(() => {
            document.getElementById('filterForm').submit();
        }, 500);
    });
});

// Export to Excel function
function exportToExcel() {
    const table = document.querySelector('table');
    const rows = table.querySelectorAll('tr');
    let csv = [];
    
    rows.forEach(row => {
        const cols = row.querySelectorAll('td, th');
        let csvRow = [];
        cols.forEach((col, index) => {
            // Skip actions column
            if (index !== cols.length - 1) {
                csvRow.push('"' + col.innerText.replace(/"/g, '""') + '"');
            }
        });
        csv.push(csvRow.join(','));
    });
    
    const csvContent = csv.join('\n');
    const blob = new Blob([csvContent], { type: 'text/csv' });
    const url = window.URL.createObjectURL(blob);
    const a = document.createElement('a');
    a.href = url;
    a.download = 'orders_' + new Date().toISOString().split('T')[0] + '.csv';
    a.click();
    window.URL.revokeObjectURL(url);
}
</script>

@if(auth()->user()->hasPermission('delete_all_orders'))
{{-- Delete All Orders Confirmation Modal --}}
<div class="modal fade" id="deleteAllOrdersModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden">
            <div class="modal-header bg-danger text-white border-0 py-3 px-4">
                <h6 class="modal-title fw-bold mb-0"><i class="fas fa-exclamation-triangle me-2"></i>Delete All Orders</h6>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body px-4 py-4 text-center">
                <div class="mb-3">
                    <div style="width:60px;height:60px;border-radius:50%;background:#fee2e2;display:flex;align-items:center;justify-content:center;margin:0 auto 12px;">
                        <i class="fas fa-trash-alt text-danger" style="font-size:24px;"></i>
                    </div>
                    <h6 class="fw-bold text-dark mb-1">Are you absolutely sure?</h6>
                    <p class="text-muted small mb-0">This will permanently delete <strong>all online orders</strong>, their items, and associated payment records. This action <strong>cannot be undone</strong>.</p>
                </div>
            </div>
            <div class="modal-footer border-0 px-4 pb-4 pt-0 d-flex gap-2 justify-content-center">
                <button type="button" class="btn btn-light rounded-pill px-4 fw-bold" data-bs-dismiss="modal">Cancel</button>
                <form action="{{ route('admin.orders.destroy-all') }}" method="POST" class="d-inline">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger rounded-pill px-4 fw-bold">
                        <i class="fas fa-trash-alt me-1"></i>Yes, Delete All
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
@endif

@endsection