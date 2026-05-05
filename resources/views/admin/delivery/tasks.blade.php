@extends('layouts.admin')

@section('title', ($viewType === 'all delivery updates' ? 'Delivery Updates' : ucfirst($viewType) . ' Deliveries') . ' - CHIBO BRAND')

@section('content')
<div class="container-fluid py-4 px-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="mb-1 text-dark fw-bold">{{ $viewType === 'all delivery updates' ? 'Delivery Updates' : ucfirst($viewType) . ' Deliveries' }}</h2>
            <p class="text-muted mb-0">Manage your {{ $viewType === 'all delivery updates' ? 'delivery updates' : $viewType . ' delivery tasks' }}.</p>
        </div>
        <div class="d-flex gap-2">
            @if(Auth::user()->role === 'delivery')
                <button type="button" class="btn btn-primary shadow-sm px-4 d-flex align-items-center gap-2" data-bs-toggle="modal" data-bs-target="#pullTaskModal">
                    <i class="fas fa-hand-holding me-1"></i> <span class="d-none d-sm-inline">Pull Task</span>
                </button>
            @endif
            <button type="button" class="btn btn-dark shadow-sm px-4 d-flex align-items-center gap-2" onclick="printDirect('{{ route('admin.delivery.print-filtered', ['view_type' => $viewType]) }}')">
                <i class="fas fa-print"></i> <span class="d-none d-sm-inline">Print Filtered</span>
            </button>
        </div>
    </div>

    <!-- Pull Task Modal -->
    <div class="modal fade" id="pullTaskModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg">
                <div class="modal-header bg-primary text-white border-0">
                    <h5 class="modal-title fw-bold"><i class="fas fa-search me-2"></i>Search & Claim Tasks</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" id="closePullModal"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="mb-4">
                        <label class="form-label small fw-bold">Search Task (Code or Customer Name)</label>
                        <div class="input-group">
                            <span class="input-group-text bg-light border-0"><i class="fas fa-search"></i></span>
                            <input type="text" id="taskSearchInput" class="form-control border-light-subtle bg-light" placeholder="e.g. DT-1234 or Juma...">
                        </div>
                        <div class="form-text x-small">Type at least 3 characters to search for ready-to-deliver tasks.</div>
                    </div>

                    <div id="searchResults" class="list-group list-group-flush border rounded-3 overflow-hidden d-none">
                        <!-- Search results will be injected here -->
                    </div>
                    
                    <div id="searchPlaceholder" class="text-center py-5 text-muted">
                        <i class="fas fa-box-open fa-3x mb-3 opacity-25"></i>
                        <p class="small mb-0">Search for tasks that are "Super Completed" and ready for delivery.</p>
                    </div>

                    <div id="searchLoading" class="text-center py-5 d-none">
                        <div class="spinner-border text-primary" role="status">
                            <span class="visually-hidden">Loading...</span>
                        </div>
                        <p class="small mt-2 text-muted">Searching available tasks...</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="bg-light">
                        <tr>
                            <th class="ps-4 py-3 border-bottom-0 text-secondary small fw-bold text-uppercase">Task ID</th>
                            <th class="py-3 border-bottom-0 text-secondary small fw-bold text-uppercase">Customer Info</th>
                            <th class="py-3 border-bottom-0 text-secondary small fw-bold text-uppercase">Task Details</th>
                            <th class="py-3 border-bottom-0 text-secondary small fw-bold text-uppercase">Status</th>
                            <th class="py-3 border-bottom-0 text-secondary small fw-bold text-uppercase text-end pe-4">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($tasks as $task)
                            <tr>
                                <td class="ps-4">
                                    <span class="fw-bold">#{{ $task->id }}</span>
                                    <div class="small text-muted">{{ $task->created_at->format('M d, H:i') }}</div>
                                </td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <div class="avatar-initial rounded bg-soft-primary text-primary fw-bold me-3" style="width: 32px; height: 32px; display: flex; align-items: center; justify-content: center;">
                                            {{ substr($task->customer_name ?? '?', 0, 1) }}
                                        </div>
                                        <div>
                                            <div class="fw-bold text-dark">{{ $task->customer_name }}</div>
                                            <div class="small text-muted">{{ $task->customer?->phone ?? 'No Phone' }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <div class="fw-medium text-dark">{{ Str::limit($task->description, 40) }}</div>
                                    @if($task->receptionist)
                                    <div class="small text-muted">Assigned by: {{ $task->receptionist->name }}</div>
                                    @endif
                                </td>
                                <td>
                                    @if($task->delivery_status === 'assigned')
                                        <span class="badge bg-warning-subtle text-warning border border-warning-subtle">
                                            <i class="fas fa-shipping-fast me-1"></i> Assigned
                                        </span>
                                    @elseif($task->delivery_status === 'picked_up')
                                        <span class="badge bg-info-subtle text-info border border-info-subtle">
                                            <i class="fas fa-box-open me-1"></i> Picked Up
                                        </span>
                                    @elseif($task->delivery_status === 'delivered')
                                        <span class="badge bg-success-subtle text-success border border-success-subtle">
                                            <i class="fas fa-check-circle me-1"></i> Delivered
                                        </span>
                                    @elseif($task->delivery_status === 'failed')
                                        <span class="badge bg-danger-subtle text-danger border border-danger-subtle">
                                            <i class="fas fa-times-circle me-1"></i> Failed
                                        </span>
                                    @else
                                        <span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle">
                                            {{ ucfirst($task->delivery_status ?? 'Pending') }}
                                        </span>
                                    @endif
                                </td>
                                <td class="text-end pe-4">
                                    <div class="d-flex justify-content-end gap-2">
                                        <a href="{{ route('admin.design-tasks.show', $task->id) }}" class="btn btn-sm btn-outline-primary shadow-sm" title="View Details">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        @if(in_array(Auth::user()->role, ['gatekeeper', 'delivery']))
                                            <a href="{{ route('gatekeeper.movements.create', [
                                                'type' => 'out',
                                                'product_name' => $task->title,
                                                'quantity' => $task->qty ?? 1,
                                                'recipient_name' => $task->customer?->name ?? $task->customer_name,
                                                'recipient_identifier' => $task->customer?->phone,
                                                'purpose' => 'Delivery',
                                                'authorization_reference' => $task->task_code ?? $task->id
                                            ]) }}" class="btn btn-sm btn-warning text-dark shadow-sm" title="Record Outgoing">
                                                <i class="fas fa-sign-out-alt"></i>
                                            </a>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center py-5">
                                    <div class="empty-state">
                                        <div class="mb-3">
                                            <i class="fas fa-truck text-muted" style="font-size: 3rem; opacity: 0.2;"></i>
                                        </div>
                                        <h6 class="text-muted fw-bold">No {{ $viewType === 'all delivery updates' ? 'delivery updates' : $viewType . ' deliveries' }} found</h6>
                                        <p class="text-muted small mb-0">Check back later or view your dashboard.</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            
            @if($tasks->hasPages())
            <div class="px-4 py-3 border-top">
                {{ $tasks->links() }}
            </div>
            @endif
        </div>
    </div>
</div>

<style>
    /* Mobile Responsiveness & Font Size Reductions */
    @media (max-width: 768px) {
        h2.fw-bold { font-size: 1.25rem !important; }
        .text-muted.mb-0 { font-size: 0.75rem !important; }
        
        .table thead th { font-size: 0.65rem !important; padding: 0.75rem 0.5rem !important; }
        .table tbody td { padding: 0.75rem 0.5rem !important; }
        
        .table .fw-bold { font-size: 0.8rem !important; }
        .table .small { font-size: 0.7rem !important; }
        .table .fw-medium { font-size: 0.75rem !important; }
        
        .badge { font-size: 0.65rem !important; padding: 0.35em 0.5em !important; }
        .btn-sm { padding: 0.25rem 0.5rem !important; font-size: 0.7rem !important; }
        
        .avatar-initial { width: 28px !important; height: 28px !important; font-size: 0.75rem !important; margin-right: 0.5rem !important; }
        .table td { white-space: normal !important; overflow-wrap: break-word !important; }
    }
</style>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.getElementById('taskSearchInput');
    const resultsContainer = document.getElementById('searchResults');
    const placeholder = document.getElementById('searchPlaceholder');
    const loading = document.getElementById('searchLoading');
    let timeout = null;

    if (searchInput) {
        searchInput.addEventListener('input', function() {
            clearTimeout(timeout);
            const query = this.value.trim();

            if (query.length < 2) {
                resultsContainer.classList.add('d-none');
                placeholder.classList.remove('d-none');
                loading.classList.add('d-none');
                return;
            }

            placeholder.classList.add('d-none');
            loading.classList.remove('d-none');
            resultsContainer.classList.add('d-none');

            timeout = setTimeout(() => {
                fetch(`{{ route('admin.delivery.search') }}?query=${encodeURIComponent(query)}`)
                    .then(response => response.json())
                    .then(data => {
                        loading.classList.add('d-none');
                        resultsContainer.innerHTML = '';
                        
                        if (data.length > 0) {
                            data.forEach(task => {
                                const item = document.createElement('div');
                                item.className = 'list-group-item list-group-item-action d-flex justify-content-between align-items-center py-3';
                                item.innerHTML = `
                                    <div>
                                        <div class="fw-bold text-primary mb-1">${task.task_code || 'N/A'} - ${task.title}</div>
                                        <div class="small text-muted">
                                            <i class="fas fa-user me-1"></i>${task.customer_name}
                                        </div>
                                    </div>
                                    <form action="{{ url('admin/delivery/pull') }}/${task.id}" method="POST">
                                        @csrf
                                        <button type="submit" class="btn btn-sm btn-success rounded-pill px-3 fw-bold shadow-sm">
                                            Pull Task
                                        </button>
                                    </form>
                                `;
                                resultsContainer.appendChild(item);
                            });
                            resultsContainer.classList.remove('d-none');
                        } else {
                            resultsContainer.innerHTML = '<div class="list-group-item py-4 text-center text-muted small">No available tasks found matching your search.</div>';
                            resultsContainer.classList.remove('d-none');
                        }
                    })
                    .catch(err => {
                        console.error(err);
                        loading.classList.add('d-none');
                    });
            }, 300);
        });
    }
});
</script>
@endpush
@endsection
