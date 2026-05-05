@extends('layouts.admin')

@section('title', 'Design Task Types')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h1 class="h5 mb-0 text-dark fw-bold" style="font-family: 'Nunito Sans', sans-serif;">Manage Task</h1>
        <button type="button" class="btn btn-dark btn-sm px-3 rounded-pill shadow-none" data-bs-toggle="modal" data-bs-target="#createTypeModal" style="font-size: 13px;">
            <i class="fas fa-plus me-1"></i>New Type
        </button>
    </div>

    @if(session('success'))
        <div class="alert alert-light border shadow-sm alert-dismissible fade show py-2" role="alert" style="font-size: 13px;">
            <i class="fas fa-check-circle text-success me-2"></i>{{ session('success') }}
            <button type="button" class="btn-close btn-sm" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <div class="card border-0 shadow-sm rounded-3 overflow-hidden">
        <div class="card-header bg-white py-2 border-bottom border-light">
            <h6 class="m-0 fw-bold text-dark" style="font-size: 14px;">Management List</h6>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0" id="dataTable" width="100%" cellspacing="0">
                    <thead class="bg-light text-dark">
                        <tr style="font-size: 12px; letter-spacing: 0.5px; text-transform: uppercase;">
                            <th class="py-2 ps-4 border-0 fw-bold">Type Name</th>
                            <th class="py-2 border-0 fw-bold">Rate</th>
                            <th class="py-2 border-0 fw-bold">Description</th>
                            <th class="py-2 pe-4 border-0 text-end fw-bold">Actions</th>
                        </tr>
                    </thead>
                    <tbody style="font-size: 13px;">
                        @foreach($types as $type)
                            <tr>
                                <td class="ps-4 fw-medium text-dark py-2">{{ $type->name }}</td>
                                <td class="text-dark fw-bold py-2">{{ number_format($type->price) }} <span class="text-muted fw-normal" style="font-size: 11px;">TZS</span></td>
                                <td class="text-muted py-2">{{ Str::limit($type->description, 50) ?: 'N/A' }}</td>
                                <td class="pe-4 text-end py-2">
                                    <button class="btn btn-sm btn-outline-dark rounded-circle me-1 p-0 d-inline-flex align-items-center justify-content-center" 
                                            onclick="openEditModal({{ $type->id }}, '{{ addslashes($type->name) }}', '{{ $type->price }}', '{{ addslashes($type->description ?? '') }}')"
                                            title="Edit"
                                            style="width: 24px; height: 24px;">
                                        <i class="fas fa-pen" style="font-size: 10px;"></i>
                                    </button>
                                    <form action="{{ route('admin.design-task-types.destroy', $type->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Delete this task type permanently?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-outline-danger rounded-circle p-0 d-inline-flex align-items-center justify-content-center" 
                                                title="Delete"
                                                style="width: 24px; height: 24px;">
                                            <i class="fas fa-trash" style="font-size: 10px;"></i>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            @if($types->isEmpty())
                <div class="text-center py-5">
                    <div class="mb-3 text-muted opacity-25">
                        <i class="fas fa-folder-open fa-3x"></i>
                    </div>
                    <p class="text-muted mb-0">No task types defined yet.</p>
                </div>
            @endif
        </div>
    </div>
</div>

<!-- Create Modal -->
<div class="modal fade" id="createTypeModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg rounded-4">
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title fw-bold text-dark ps-2">Create New Type</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ route('admin.design-task-types.store') }}" method="POST">
                @csrf
                <div class="modal-body pt-4">
                    <div class="mb-3">
                        <label class="form-label text-muted small fw-bold text-uppercase">Type Name</label>
                        <input type="text" name="name" class="form-control form-control-lg bg-light border-0" required placeholder="e.g. Logo Design">
                    </div>
                    <div class="mb-3">
                        <label class="form-label text-muted small fw-bold text-uppercase">Rate (TZS)</label>
                        <input type="number" name="price" class="form-control form-control-lg bg-light border-0" required min="0" step="0.01" placeholder="0">
                    </div>
                    <div class="mb-3">
                        <label class="form-label text-muted small fw-bold text-uppercase">Description</label>
                        <textarea name="description" class="form-control bg-light border-0" rows="3" placeholder="Description..."></textarea>
                    </div>
                </div>
                <div class="modal-footer border-0 pt-0 pb-4 pe-4">
                    <button type="button" class="btn btn-link text-muted text-decoration-none" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-dark px-4 rounded-pill" data-no-global-handler>Create</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit Modal -->
<div class="modal fade" id="editTypeModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg rounded-4">
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title fw-bold text-dark ps-2">Edit Type</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="editTypeForm" method="POST">
                @csrf
                @method('PUT')
                <div class="modal-body pt-4">
                    <div class="mb-3">
                        <label class="form-label text-muted small fw-bold text-uppercase">Type Name</label>
                        <input type="text" id="edit_name" name="name" class="form-control form-control-lg bg-light border-0" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label text-muted small fw-bold text-uppercase">Rate (TZS)</label>
                        <input type="number" id="edit_price" name="price" class="form-control form-control-lg bg-light border-0" required min="0" step="0.01">
                    </div>
                    <div class="mb-3">
                        <label class="form-label text-muted small fw-bold text-uppercase">Description</label>
                        <textarea id="edit_description" name="description" class="form-control bg-light border-0" rows="3"></textarea>
                    </div>
                </div>
                <div class="modal-footer border-0 pt-0 pb-4 pe-4">
                    <button type="button" class="btn btn-link text-muted text-decoration-none" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-dark px-4 rounded-pill" data-no-global-handler>Update Changes</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    function openEditModal(id, name, price, description) {
        document.getElementById('edit_name').value = name;
        document.getElementById('edit_price').value = price;
        document.getElementById('edit_description').value = description;
        
        // Update form action
        document.getElementById('editTypeForm').action = `/admin/design-task-types/${id}`;
        
        // Open modal
        new bootstrap.Modal(document.getElementById('editTypeModal')).show();
    }
</script>
@endsection
