@extends('layouts.admin')

@section('title', 'Customer Sources Management')

@section('content')
<div class="container-fluid py-3">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="h4 fw-bold mb-1"><i class="fas fa-share-alt text-danger me-2"></i>Customer Source Management</h2>
            <p class="text-muted small mb-0">Manage predefined marketing acquisition channels for Lead and Customer registration.</p>
        </div>
        <button class="btn btn-danger btn-sm rounded-pill px-3" data-bs-toggle="modal" data-bs-target="#addSourceModal">
            <i class="fas fa-plus me-1.5"></i> Add New Source
        </button>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show rounded-3 small" role="alert">
            <i class="fas fa-check-circle me-1.5"></i> {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="bg-light">
                        <tr>
                            <th class="ps-4" style="width: 50px;">#</th>
                            <th>Source Name</th>
                            <th class="text-center">Sort Order</th>
                            <th class="text-center">Status</th>
                            <th class="text-end pe-4">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($sources as $index => $source)
                            <tr>
                                <td class="ps-4 fw-bold text-muted">{{ $index + 1 }}</td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <div class="rounded-circle bg-danger bg-opacity-10 text-danger p-2 me-2 d-flex align-items-center justify-content-center" style="width:32px; height:32px;">
                                            <i class="fas fa-tag fa-xs"></i>
                                        </div>
                                        <span class="fw-bold text-dark">{{ $source->name }}</span>
                                    </div>
                                </td>
                                <td class="text-center fw-semibold">{{ $source->sort_order }}</td>
                                <td class="text-center">
                                    @if($source->is_active)
                                        <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill px-2.5 py-1">Active</span>
                                    @else
                                        <span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle rounded-pill px-2.5 py-1">Disabled</span>
                                    @endif
                                </td>
                                <td class="text-end pe-4">
                                    <div class="d-flex justify-content-end gap-1">
                                        <form action="{{ route('admin.settings.customer-sources.toggle', $source) }}" method="POST" class="d-inline">
                                            @csrf
                                            <button type="submit" class="btn btn-sm btn-outline-secondary rounded-circle" title="Toggle Status" style="width:30px; height:30px; padding:0;">
                                                <i class="fas {{ $source->is_active ? 'fa-eye-slash' : 'fa-eye' }} fa-xs"></i>
                                            </button>
                                        </form>
                                        <form action="{{ route('admin.settings.customer-sources.destroy', $source) }}" method="POST" class="d-inline" onsubmit="return confirm('Are you sure you want to delete this customer source?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-outline-danger rounded-circle" title="Delete" style="width:30px; height:30px; padding:0;">
                                                <i class="fas fa-trash fa-xs"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center py-4 text-muted">No customer sources found.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Add Source Modal -->
<div class="modal fade" id="addSourceModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content rounded-4 border-0 shadow">
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title fw-bold"><i class="fas fa-plus text-danger me-2"></i>Add Customer Source</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('admin.settings.customer-sources.store') }}" method="POST">
                @csrf
                <div class="modal-body py-3">
                    <div class="mb-3">
                        <label class="form-label fw-semibold small">Source Name <span class="text-danger">*</span></label>
                        <input type="text" name="name" class="form-control rounded-3" placeholder="e.g. TikTok, WhatsApp, Referral" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold small">Sort Order</label>
                        <input type="number" name="sort_order" class="form-control rounded-3" value="0">
                    </div>
                </div>
                <div class="modal-footer border-0 pt-0">
                    <button type="button" class="btn btn-light btn-sm rounded-pill px-3" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-danger btn-sm rounded-pill px-4">Save Source</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
