@extends('layouts.admin')
@section('title', 'Inside Programs')

@section('content')
<div class="container-fluid py-3">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="fw-bold mb-0"><i class="fas fa-layer-group text-primary me-2"></i>Inside Programs</h4>
            <p class="text-muted small mb-0">Manage internal programs used as lead sources in the Sales Report.</p>
        </div>
        <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#addProgramModal">
            <i class="fas fa-plus me-1"></i> Add Program
        </button>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="card shadow-sm border-0">
        <div class="card-body p-0">
            <table class="table table-hover mb-0 align-middle">
                <thead class="table-light">
                    <tr>
                        <th style="width:50px">#</th>
                        <th>Program Name</th>
                        <th>Description</th>
                        <th class="text-center" style="width:100px">Order</th>
                        <th class="text-center" style="width:100px">Status</th>
                        <th class="text-end" style="width:120px">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($programs as $program)
                    <tr>
                        <td class="text-muted small">{{ $program->id }}</td>
                        <td class="fw-semibold">{{ $program->name }}</td>
                        <td class="text-muted small">{{ $program->description ?: '—' }}</td>
                        <td class="text-center">{{ $program->sort_order }}</td>
                        <td class="text-center">
                            @if($program->is_active)
                                <span class="badge bg-success">Active</span>
                            @else
                                <span class="badge bg-secondary">Inactive</span>
                            @endif
                        </td>
                        <td class="text-end">
                            <button class="btn btn-sm btn-outline-secondary me-1"
                                onclick="editProgram({{ $program->id }}, '{{ addslashes($program->name) }}', '{{ addslashes($program->description ?? '') }}', {{ $program->sort_order }}, {{ $program->is_active ? 'true' : 'false' }})">
                                <i class="fas fa-edit"></i>
                            </button>
                            <form action="{{ route('admin.sales.programs.destroy', $program) }}" method="POST" class="d-inline"
                                onsubmit="return confirm('Delete {{ addslashes($program->name) }}? This will unlink any leads from this program.')">
                                @csrf @method('DELETE')
                                <button class="btn btn-sm btn-outline-danger"><i class="fas fa-trash"></i></button>
                            </form>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="text-center text-muted py-4">No programs yet. Add one to get started.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($programs->hasPages())
        <div class="card-footer bg-white">
            {{ $programs->links() }}
        </div>
        @endif
    </div>
</div>

{{-- Add Program Modal --}}
<div class="modal fade" id="addProgramModal" tabindex="-1">
    <div class="modal-dialog">
        <form action="{{ route('admin.sales.programs.store') }}" method="POST">
            @csrf
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Add Program</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body row g-3">
                    <div class="col-12">
                        <label class="form-label fw-semibold">Program Name <span class="text-danger">*</span></label>
                        <input type="text" name="name" class="form-control" required placeholder="e.g. Ramadan Campaign">
                    </div>
                    <div class="col-12">
                        <label class="form-label fw-semibold">Description</label>
                        <textarea name="description" class="form-control" rows="2" placeholder="Optional description"></textarea>
                    </div>
                    <div class="col-6">
                        <label class="form-label fw-semibold">Sort Order</label>
                        <input type="number" name="sort_order" class="form-control" value="0" min="0">
                    </div>
                    <div class="col-6 d-flex align-items-end">
                        <div class="form-check form-switch mb-2">
                            <input class="form-check-input" type="checkbox" name="is_active" value="1" checked id="addActive">
                            <label class="form-check-label" for="addActive">Active</label>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-sm btn-primary">Add Program</button>
                </div>
            </div>
        </form>
    </div>
</div>

{{-- Edit Program Modal --}}
<div class="modal fade" id="editProgramModal" tabindex="-1">
    <div class="modal-dialog">
        <form id="editProgramForm" method="POST">
            @csrf @method('PUT')
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Edit Program</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body row g-3">
                    <div class="col-12">
                        <label class="form-label fw-semibold">Program Name <span class="text-danger">*</span></label>
                        <input type="text" name="name" id="editName" class="form-control" required>
                    </div>
                    <div class="col-12">
                        <label class="form-label fw-semibold">Description</label>
                        <textarea name="description" id="editDesc" class="form-control" rows="2"></textarea>
                    </div>
                    <div class="col-6">
                        <label class="form-label fw-semibold">Sort Order</label>
                        <input type="number" name="sort_order" id="editOrder" class="form-control" min="0">
                    </div>
                    <div class="col-6 d-flex align-items-end">
                        <div class="form-check form-switch mb-2">
                            <input class="form-check-input" type="checkbox" name="is_active" value="1" id="editActive">
                            <label class="form-check-label" for="editActive">Active</label>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-sm btn-primary">Save Changes</button>
                </div>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
function editProgram(id, name, desc, order, active) {
    document.getElementById('editProgramForm').action = '/admin/sales/programs/' + id;
    document.getElementById('editName').value = name;
    document.getElementById('editDesc').value = desc;
    document.getElementById('editOrder').value = order;
    document.getElementById('editActive').checked = active;
    new bootstrap.Modal(document.getElementById('editProgramModal')).show();
}
</script>
@endpush
@endsection
