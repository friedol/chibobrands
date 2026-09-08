@extends('layouts.admin')

@section('title', 'Proforma Invoices')

@section('content')
<div class="container-fluid">
    <!-- Modern Header & Actions -->
    <div class="row mb-3 align-items-end">
        <div class="col-lg-6 mb-2 mb-lg-0">
            <h4 class="fw-bold mb-0">Proforma Invoices</h4>
            <p class="text-muted small mb-0">Manage and track all issued proforma invoices</p>
        </div>
        <div class="col-lg-6 text-lg-end">
            <div class="d-flex flex-wrap justify-content-lg-end gap-2">
                <button class="btn btn-outline-primary btn-sm px-3 fw-bold" type="button" data-bs-toggle="collapse" data-bs-target="#filterCollapse">
                    <i class="fas fa-filter me-1"></i> Filter
                    @if(request()->anyFilled(['search', 'department_id']))
                        <span class="badge bg-primary ms-1">Active</span>
                    @endif
                </button>
                
                <a href="{{ route('admin.pos.index', ['type' => 'proforma']) }}" class="btn btn-primary btn-sm px-3 fw-bold shadow-sm">
                    <i class="fas fa-plus-circle me-1"></i> New Proforma
                </a>
            </div>
        </div>
    </div>

    <!-- Collapsable Filters -->
    <div class="collapse {{ request()->anyFilled(['search', 'department_id']) ? 'show' : '' }} mb-4" id="filterCollapse">
        <div class="card border-0 shadow-sm border-top border-4 border-primary">
            <div class="card-body bg-light p-3">
                <form action="{{ route('admin.finance.proforma.index') }}" method="GET" class="row g-2">
                    <div class="col-12 col-md-4">
                        <label class="form-label fw-bold x-small text-uppercase mb-1">Search</label>
                        <div class="input-group input-group-sm">
                            <span class="input-group-text bg-white border-end-0"><i class="fas fa-search text-muted"></i></span>
                            <input type="text" name="search" class="form-control border-start-0" placeholder="Order # or Customer name..." value="{{ request('search') }}">
                        </div>
                    </div>
                    
                    <div class="col-6 col-md-3">
                        <label class="form-label fw-bold x-small text-uppercase mb-1">Department</label>
                        <select name="department_id" class="form-select form-select-sm">
                            <option value="all">All Departments</option>
                            @foreach($departments as $dept)
                                <option value="{{ $dept->id }}" {{ request('department_id') == $dept->id ? 'selected' : '' }}>{{ $dept->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-auto d-none d-md-flex align-items-end ms-auto">
                        <div class="btn-group shadow-sm">
                            <button type="submit" class="btn btn-primary btn-sm px-3 fw-bold">APPLY</button>
                            <a href="{{ route('admin.finance.proforma.index') }}" class="btn btn-dark btn-sm px-3 fw-bold">RESET</a>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="bg-light">
                        <tr>
                            <th class="ps-4">Order Code</th>
                            <th>Date</th>
                            <th>Customer</th>
                            <th>Department</th>
                            <th>Total Amount</th>
                            <th>Issued By</th>
                            <th class="text-end pe-4">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($proformas as $proforma)
                        <tr>
                            <td class="ps-4 fw-bold">#{{ $proforma->order_code }}</td>
                            <td>{{ $proforma->created_at->format('M d, Y') }}</td>
                            <td>
                                <div class="fw-bold">{{ $proforma->user->name ?? 'N/A' }}</div>
                                <div class="small text-muted">{{ $proforma->user->phone ?? '' }}</div>
                            </td>
                            <td>{{ $proforma->department->name ?? 'N/A' }}</td>
                            <td class="fw-bold text-primary">TZS {{ number_format($proforma->total_amount) }}</td>
                            <td>
                                {{ $proforma->saler->name ?? 'N/A' }}
                                @if($proforma->approval_status === 'approved')
                                    <span class="badge bg-success ms-1" style="font-size:9px;">Exported</span>
                                @endif
                            </td>
                            <td class="text-end pe-4">
                                <div class="d-flex justify-content-end gap-1">
                                    <button type="button" onclick="printDirect('{{ route('admin.finance.invoices.proforma', $proforma->order_code) }}')" class="btn btn-sm btn-light border text-dark px-2" title="Print Proforma">
                                        <i class="fas fa-print"></i>
                                    </button>
                                    <button type="button" onclick="openEdit({{ $proforma->id }})" class="btn btn-sm btn-light border text-warning px-2" title="Edit Proforma">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <button type="button" onclick="convertToTask({{ $proforma->id }}, '{{ $proforma->order_code }}')" class="btn btn-sm btn-light border text-success px-2" title="Export to Tasks" {{ $proforma->approval_status === 'approved' ? 'disabled' : '' }}>
                                        <i class="fas fa-tasks"></i>
                                    </button>
                                    <button type="button" onclick="viewDetails({{ $proforma->id }})" class="btn btn-sm btn-light border text-primary px-2" title="View Details">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="7" class="text-center py-5 text-muted">No proforma invoices found.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="p-4">
                {{ $proformas->links() }}
            </div>
        </div>
    </div>
</div>

@push('styles')
<style>
    .container-fluid { font-size: 13px; }
    h4 { font-size: 1.25rem !important; font-weight: 700; }
    .table th { font-size: 11px; text-transform: uppercase; letter-spacing: 0.8px; font-weight: 700; color: #666; }
    .table td { font-size: 13px; }
    .btn { font-weight: 600; letter-spacing: 0.2px; }
    .form-control, .form-select { font-size: 13px !important; border-radius: 6px; }
    .x-small { font-size: 10px !important; }
    .card { border-radius: 10px; }
</style>
@endpush

{{-- View Details Modal --}}
<div class="modal fade" id="viewProformaModal" tabindex="-1" aria-labelledby="viewProformaModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header border-0 pb-0" style="background:linear-gradient(135deg,#6366f1 0%,#4f46e5 100%);">
                <div>
                    <h6 class="modal-title fw-bold text-white mb-0" id="viewProformaModalLabel">
                        <i class="fas fa-file-invoice me-2"></i>Proforma Invoice — <span id="viewOrderCode"></span>
                    </h6>
                    <div class="small text-white opacity-75 mt-1" id="viewOrderDate"></div>
                </div>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-0">
                {{-- Loading --}}
                <div id="viewLoadingState" class="text-center py-5">
                    <div class="spinner-border text-primary" role="status"></div>
                    <p class="small text-muted mt-2 mb-0">Loading…</p>
                </div>
                {{-- Content --}}
                <div id="viewContent" class="d-none">
                    {{-- Customer + Meta row --}}
                    <div class="row g-0 border-bottom">
                        <div class="col-md-6 p-3 border-end">
                            <p class="fw-bold small text-uppercase text-muted mb-2">Customer</p>
                            <p class="fw-bold mb-0" id="viewCustomerName"></p>
                            <p class="small text-muted mb-0" id="viewCustomerPhone"></p>
                            <p class="small text-muted mb-0" id="viewCustomerEmail"></p>
                        </div>
                        <div class="col-md-6 p-3">
                            <div class="row g-2">
                                <div class="col-6">
                                    <p class="fw-bold small text-uppercase text-muted mb-1">Department</p>
                                    <p class="small mb-0 fw-bold" id="viewDept"></p>
                                </div>
                                <div class="col-6">
                                    <p class="fw-bold small text-uppercase text-muted mb-1">Issued By</p>
                                    <p class="small mb-0 fw-bold" id="viewSaler"></p>
                                </div>
                                <div class="col-6">
                                    <p class="fw-bold small text-uppercase text-muted mb-1">Status</p>
                                    <p class="small mb-0" id="viewStatus"></p>
                                </div>
                            </div>
                        </div>
                    </div>
                    {{-- Items table --}}
                    <div class="p-3">
                        <p class="fw-bold small text-uppercase text-muted mb-2">Line Items</p>
                        <div class="table-responsive">
                            <table class="table table-sm table-hover align-middle mb-0">
                                <thead class="bg-light">
                                    <tr>
                                        <th style="font-size:11px;">Item</th>
                                        <th style="font-size:11px; width:70px;" class="text-center">Qty</th>
                                        <th style="font-size:11px; width:130px;" class="text-end">Unit Price</th>
                                        <th style="font-size:11px; width:130px;" class="text-end">Subtotal</th>
                                    </tr>
                                </thead>
                                <tbody id="viewItemsBody"></tbody>
                                <tfoot>
                                    <tr>
                                        <td colspan="3" class="text-end fw-bold small pt-3">Total Amount</td>
                                        <td class="text-end fw-bold text-primary pt-3" style="font-size:15px;" id="viewTotal"></td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                        <div id="viewNotesBlock" class="mt-3 p-2 bg-light rounded d-none">
                            <p class="fw-bold small text-uppercase text-muted mb-1">Notes</p>
                            <p class="small mb-0" id="viewNotes"></p>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer border-0 pt-0">
                <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Close</button>
                <button type="button" class="btn btn-dark btn-sm fw-bold" id="viewPrintBtn">
                    <i class="fas fa-print me-1"></i> Print Invoice
                </button>
            </div>
        </div>
    </div>
</div>

{{-- Edit Proforma Modal --}}
<div class="modal fade" id="editProformaModal" tabindex="-1" aria-labelledby="editProformaModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header border-0 pb-0">
                <h6 class="modal-title fw-bold" id="editProformaModalLabel"><i class="fas fa-edit me-2 text-warning"></i>Edit Proforma Invoice — <span id="editOrderCode"></span></h6>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div id="editLoadingState" class="text-center py-4 d-none">
                    <div class="spinner-border text-primary" role="status"></div>
                    <p class="small text-muted mt-2">Loading…</p>
                </div>
                <form id="editProformaForm">
                    <input type="hidden" id="editOrderId">
                    <div class="mb-3">
                        <label class="form-label fw-bold small text-uppercase">Notes</label>
                        <textarea id="editNotes" name="notes" class="form-control form-control-sm" rows="2" placeholder="Optional notes…"></textarea>
                    </div>
                    <div class="mb-2">
                        <label class="form-label fw-bold small text-uppercase">Line Items</label>
                        <div class="table-responsive">
                            <table class="table table-sm table-bordered align-middle mb-0" id="editItemsTable">
                                <thead class="bg-light">
                                    <tr>
                                        <th style="font-size:11px;">Item</th>
                                        <th style="font-size:11px; width:90px;">Qty</th>
                                        <th style="font-size:11px; width:130px;">Unit Price</th>
                                        <th style="font-size:11px; width:130px;">Subtotal</th>
                                    </tr>
                                </thead>
                                <tbody id="editItemsBody"></tbody>
                                <tfoot>
                                    <tr>
                                        <td colspan="3" class="text-end fw-bold small">Total</td>
                                        <td class="fw-bold small" id="editTotalDisplay">TZS 0</td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer border-0 pt-0">
                <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-warning btn-sm fw-bold px-4" id="saveProformaBtn" onclick="saveProforma()">
                    <i class="fas fa-save me-1"></i> Save Changes
                </button>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
const CSRF = document.querySelector('meta[name="csrf-token"]')?.content || '';
const PROFORMA_BASE = '{{ url("admin/finance/proforma") }}';

function viewDetails(id) {
    const modal = new bootstrap.Modal(document.getElementById('viewProformaModal'));
    document.getElementById('viewLoadingState').classList.remove('d-none');
    document.getElementById('viewContent').classList.add('d-none');
    document.getElementById('viewOrderCode').textContent = '';
    document.getElementById('viewPrintBtn').onclick = null;
    modal.show();

    fetch(`${PROFORMA_BASE}/${id}/details`, {
        headers: { 'X-Requested-With': 'XMLHttpRequest' }
    })
    .then(r => r.json())
    .then(d => {
        document.getElementById('viewLoadingState').classList.add('d-none');
        document.getElementById('viewContent').classList.remove('d-none');

        document.getElementById('viewOrderCode').textContent = '#' + d.order_code;
        document.getElementById('viewOrderDate').textContent = d.date;
        document.getElementById('viewCustomerName').textContent = d.customer.name;
        document.getElementById('viewCustomerPhone').textContent = d.customer.phone || '';
        document.getElementById('viewCustomerEmail').textContent = d.customer.email || '';
        document.getElementById('viewDept').textContent = d.department;
        document.getElementById('viewSaler').textContent = d.saler;

        const statusMap = { approved: ['Exported / Accepted', 'success'], requested: ['Pending', 'warning'], cancelled: ['Cancelled', 'danger'] };
        const [label, color] = statusMap[d.status] || [d.status, 'secondary'];
        document.getElementById('viewStatus').innerHTML = `<span class="badge bg-${color}">${label}</span>`;

        const tbody = document.getElementById('viewItemsBody');
        tbody.innerHTML = '';
        d.items.forEach(item => {
            tbody.insertAdjacentHTML('beforeend', `
                <tr>
                    <td class="small fw-bold">${item.product_name}</td>
                    <td class="small text-center">${item.quantity}</td>
                    <td class="small text-end">${formatNum(item.unit_price)}</td>
                    <td class="small text-end fw-bold">${formatNum(item.subtotal)}</td>
                </tr>
            `);
        });
        document.getElementById('viewTotal').textContent = formatNum(d.total_amount);

        const notesBlock = document.getElementById('viewNotesBlock');
        if (d.notes) {
            notesBlock.classList.remove('d-none');
            document.getElementById('viewNotes').textContent = d.notes;
        } else {
            notesBlock.classList.add('d-none');
        }

        document.getElementById('viewPrintBtn').onclick = () => printDirect(d.print_url);
    })
    .catch(() => {
        document.getElementById('viewLoadingState').classList.add('d-none');
        Swal.fire('Error', 'Could not load details.', 'error');
    });
}

function printDirect(url) {
    Swal.fire({
        title: 'Print Proforma Invoice?',
        text: 'Do you want to open and print this proforma invoice?',
        icon: 'question',
        showCancelButton: true,
        confirmButtonText: '<i class="fas fa-print me-1"></i> Print',
        cancelButtonText: 'Cancel',
        confirmButtonColor: '#2563eb',
        cancelButtonColor: '#6c757d',
    }).then((result) => {
        if (result.isConfirmed) {
            window.open(url + '?print=true', '_blank');
        }
    });
}

function formatNum(n) {
    return 'TZS ' + Number(n).toLocaleString('en-US', {minimumFractionDigits: 0, maximumFractionDigits: 0});
}

function recalcEditTotal() {
    let total = 0;
    document.querySelectorAll('#editItemsBody tr').forEach(row => {
        const qty = parseFloat(row.querySelector('.edit-qty').value) || 0;
        const price = parseFloat(row.querySelector('.edit-price').value) || 0;
        const sub = qty * price;
        row.querySelector('.edit-sub').textContent = formatNum(sub);
        total += sub;
    });
    document.getElementById('editTotalDisplay').textContent = formatNum(total);
}

function openEdit(id) {
    const modal = new bootstrap.Modal(document.getElementById('editProformaModal'));
    document.getElementById('editLoadingState').classList.remove('d-none');
    document.getElementById('editProformaForm').style.opacity = '0.3';
    document.getElementById('editOrderCode').textContent = '';
    modal.show();

    fetch(`${PROFORMA_BASE}/${id}/edit-data`, {
        headers: { 'X-Requested-With': 'XMLHttpRequest' }
    })
    .then(r => r.json())
    .then(data => {
        document.getElementById('editLoadingState').classList.add('d-none');
        document.getElementById('editProformaForm').style.opacity = '1';
        document.getElementById('editOrderId').value = data.id;
        document.getElementById('editOrderCode').textContent = '#' + data.order_code;
        document.getElementById('editNotes').value = data.notes || '';

        const tbody = document.getElementById('editItemsBody');
        tbody.innerHTML = '';
        data.items.forEach(item => {
            tbody.insertAdjacentHTML('beforeend', `
                <tr data-item-id="${item.id}">
                    <td class="small fw-bold">${item.product_name}</td>
                    <td><input type="number" class="form-control form-control-sm edit-qty" value="${item.quantity}" min="1" oninput="recalcEditTotal()"></td>
                    <td><input type="number" class="form-control form-control-sm edit-price" value="${item.unit_price}" min="0" step="0.01" oninput="recalcEditTotal()"></td>
                    <td class="edit-sub small fw-bold">${formatNum(item.subtotal)}</td>
                </tr>
            `);
        });
        recalcEditTotal();
    })
    .catch(() => {
        document.getElementById('editLoadingState').classList.add('d-none');
        document.getElementById('editProformaForm').style.opacity = '1';
        Swal.fire('Error', 'Could not load proforma data.', 'error');
    });
}

function saveProforma() {
    const id = document.getElementById('editOrderId').value;
    const notes = document.getElementById('editNotes').value;
    const items = [];

    document.querySelectorAll('#editItemsBody tr').forEach(row => {
        items.push({
            id: row.dataset.itemId,
            quantity: parseInt(row.querySelector('.edit-qty').value),
            unit_price: parseFloat(row.querySelector('.edit-price').value),
        });
    });

    const btn = document.getElementById('saveProformaBtn');
    btn.disabled = true;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i> Saving…';

    fetch(`${PROFORMA_BASE}/${id}/update`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': CSRF,
        },
        body: JSON.stringify({ notes, items }),
    })
    .then(r => r.json())
    .then(data => {
        btn.disabled = false;
        btn.innerHTML = '<i class="fas fa-save me-1"></i> Save Changes';
        if (data.success) {
            bootstrap.Modal.getInstance(document.getElementById('editProformaModal')).hide();
            Swal.fire({ title: 'Saved!', text: data.message, icon: 'success', timer: 2000, showConfirmButton: false });
            setTimeout(() => location.reload(), 2100);
        } else {
            Swal.fire('Error', data.message || 'Could not save.', 'error');
        }
    })
    .catch(() => {
        btn.disabled = false;
        btn.innerHTML = '<i class="fas fa-save me-1"></i> Save Changes';
        Swal.fire('Error', 'Request failed.', 'error');
    });
}

function convertToTask(id, orderCode) {
    Swal.fire({
        title: 'Export to Design Tasks?',
        html: `Proforma <strong>#${orderCode}</strong> will be converted into design task(s) visible in the task board.<br><br>This action marks the proforma as accepted.`,
        icon: 'question',
        showCancelButton: true,
        confirmButtonText: '<i class="fas fa-tasks me-1"></i> Yes, Export to Tasks',
        cancelButtonText: 'Cancel',
        confirmButtonColor: '#16a34a',
        cancelButtonColor: '#6c757d',
    }).then(result => {
        if (!result.isConfirmed) return;

        Swal.fire({ title: 'Creating tasks…', allowOutsideClick: false, didOpen: () => Swal.showLoading() });

        fetch(`${PROFORMA_BASE}/${id}/convert-to-tasks`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF },
            body: JSON.stringify({}),
        })
        .then(r => r.json())
        .then(data => {
            if (data.success) {
                Swal.fire({
                    title: 'Done!',
                    html: `<strong>${data.tasks_count}</strong> design task(s) created.<br><a href="${data.tasks_url}" class="btn btn-sm btn-primary mt-2"><i class="fas fa-tasks me-1"></i>View Tasks</a>`,
                    icon: 'success',
                    confirmButtonText: 'OK',
                }).then(() => location.reload());
            } else {
                Swal.fire('Error', data.message || 'Conversion failed.', 'error');
            }
        })
        .catch(() => Swal.fire('Error', 'Request failed.', 'error'));
    });
}
</script>
@endpush
@endsection
