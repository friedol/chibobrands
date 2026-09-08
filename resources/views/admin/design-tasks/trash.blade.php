@extends('layouts.admin')

@section('page-title', 'Design Tasks Trash Bin')

@section('content')
<div class="container-fluid py-4">

    <!-- Hero Header -->
    <div class="card border-0 shadow-sm rounded-4 mb-4 bg-danger text-white overflow-hidden" style="background: linear-gradient(135deg, #dc2626 0%, #991b1b 100%);">
        <div class="card-body p-4 d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3">
            <div>
                <div class="d-flex align-items-center mb-2">
                    <span class="badge bg-white bg-opacity-20 text-white px-3 py-1 rounded-pill small me-2">
                        <i class="fas fa-trash-alt me-1"></i> Trash & Recycling Manager
                    </span>
                    <span class="text-white-50 small"><i class="fas fa-history me-1"></i> Soft-Deleted System Records</span>
                </div>
                <h2 class="fw-bold text-white mb-1"><i class="fas fa-trash-can me-2"></i>Design Tasks Trash Bin</h2>
                <p class="text-white-50 mb-0 small">Manage soft-deleted tasks. Recycle and restore tasks back to active workflow, or permanently purge them.</p>
            </div>
            <div class="d-flex gap-2">
                <a href="{{ route('admin.design-tasks.index') }}" class="btn btn-light btn-sm px-3.5 py-2 fw-bold text-dark shadow-sm">
                    <i class="fas fa-arrow-left me-1.5"></i> Back to Tasks
                </a>
                @if($tasks->total() > 0 && in_array(Auth::user()->role, ['admin', 'super_admin']))
                <form action="{{ route('admin.design-tasks.empty-trash') }}" method="POST" onsubmit="return confirm('WARNING: Are you sure you want to permanently delete ALL trashed tasks? This action CANNOT be undone!');">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-dark btn-sm px-3.5 py-2 fw-bold shadow-sm text-warning">
                        <i class="fas fa-dumpster-fire me-1.5 text-warning"></i> Empty Trash Bin
                    </button>
                </form>
                @endif
            </div>
        </div>
    </div>

    <!-- Alert Messages -->
    @if(session('success'))
    <div class="alert alert-success alert-dismissible fade show border-0 shadow-sm rounded-3 mb-4" role="alert">
        <i class="fas fa-check-circle me-2 fs-5"></i>{{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    @endif

    @if(session('error'))
    <div class="alert alert-danger alert-dismissible fade show border-0 shadow-sm rounded-3 mb-4" role="alert">
        <i class="fas fa-exclamation-triangle me-2 fs-5"></i>{{ session('error') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    @endif

    <!-- Trashed Tasks Table Card -->
    <div class="card border-0 shadow-sm rounded-4">
        <div class="card-header bg-white py-3 border-0 d-flex justify-content-between align-items-center">
            <h6 class="m-0 fw-bold text-dark"><i class="fas fa-list text-danger me-2"></i>Trashed Design Tasks List</h6>
            <span class="badge bg-danger-subtle text-danger border border-danger-subtle px-3 py-1 rounded-pill fw-bold">
                {{ number_format($tasks->total()) }} Tasks in Trash
            </span>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="bg-light text-secondary x-small text-uppercase">
                        <tr>
                            <th class="ps-4 py-3">Task Code & Title</th>
                            <th>Customer</th>
                            <th>Department</th>
                            <th>Deleted On</th>
                            <th>Amount (TZS)</th>
                            <th class="text-end pe-4">Recycling Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($tasks as $task)
                        <tr>
                            <td class="ps-4">
                                <div class="fw-bold text-dark fs-7">{{ $task->title }}</div>
                                <code class="x-small text-muted">{{ $task->task_code ?? "TASK-{$task->id}" }}</code>
                            </td>
                            <td>
                                <div class="fw-semibold text-dark">{{ $task->customer->name ?? 'N/A' }}</div>
                                <div class="x-small text-muted">{{ $task->customer->company_name ?? '' }}</div>
                            </td>
                            <td>
                                <span class="badge bg-light text-dark border">{{ $task->department->name ?? 'General' }}</span>
                            </td>
                            <td class="text-secondary">
                                <i class="fas fa-clock text-danger me-1"></i>
                                {{ $task->deleted_at ? $task->deleted_at->format('d M Y, H:i') : 'N/A' }}
                            </td>
                            <td class="fw-bold text-dark">
                                TZS {{ number_format($task->price ?? 0) }}
                            </td>
                            <td class="text-end pe-4">
                                <div class="d-flex justify-content-end gap-2">
                                    <!-- Restore / Recycle Form -->
                                    <form action="{{ route('admin.design-tasks.restore', $task->id) }}" method="POST">
                                        @csrf
                                        <button type="submit" class="btn btn-outline-success btn-sm px-3 fw-bold rounded-pill shadow-sm" title="Recycle & Restore Task">
                                            <i class="fas fa-undo me-1"></i> Restore
                                        </button>
                                    </form>

                                    <!-- Permanent Delete Form -->
                                    @if(in_array(Auth::user()->role, ['admin', 'super_admin']))
                                    <form action="{{ route('admin.design-tasks.force-delete', $task->id) }}" method="POST" onsubmit="return confirm('Are you sure you want to PERMANENTLY delete task &quot;{{ addslashes($task->title) }}&quot;? This cannot be restored!');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-outline-danger btn-sm px-3 fw-bold rounded-pill shadow-sm" title="Delete Permanently">
                                            <i class="fas fa-trash-alt me-1"></i> Delete Permanently
                                        </button>
                                    </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="text-center py-5 text-muted">
                                <i class="fas fa-trash-can fs-1 d-block mb-3 text-secondary opacity-50"></i>
                                <h6 class="fw-bold text-dark">Trash Bin is Empty</h6>
                                <p class="x-small text-muted mb-0">No soft-deleted design tasks found.</p>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if($tasks->hasPages())
            <div class="px-4 py-3 border-top bg-white">
                {{ $tasks->links('pagination::bootstrap-5') }}
            </div>
            @endif
        </div>
    </div>

</div>
@endsection
