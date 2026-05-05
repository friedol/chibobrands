@extends('layouts.admin')

@section('title', 'Contact Messages')

@section('content')
<div class="container-fluid">
    <!-- Breadcrumb -->
    <nav aria-label="breadcrumb" class="mb-3">
        <ol class="breadcrumb">
            <li class="breadcrumb-item">
                <a href="{{ route('admin.dashboard') }}" class="text-decoration-none">
                    <i class="fas fa-home me-1"></i>Dashboard
                </a>
            </li>
            <li class="breadcrumb-item active" aria-current="page">
                <i class="fas fa-envelope me-1"></i>Contact Messages
            </li>
        </ol>
    </nav>

    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0">Contact Messages</h1>
        <div class="d-flex gap-2">
            <span class="badge bg-danger fs-6">{{ $newCount }} New</span>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <!-- Filters -->
    <div class="card mb-4">
        <div class="card-header py-2">
            <h6 class="card-title mb-0 text-white">
                <i class="fas fa-filter me-2"></i>Filters
            </h6>
        </div>
        <div class="card-body">
            <form method="GET" action="{{ route('admin.contact-messages.index') }}" class="row g-3">
                <div class="col-md-4">
                    <label class="form-label">Status</label>
                    <select name="status" class="form-select">
                        <option value="">All Status</option>
                        <option value="new" {{ request('status') == 'new' ? 'selected' : '' }}>New</option>
                        <option value="read" {{ request('status') == 'read' ? 'selected' : '' }}>Read</option>
                        <option value="replied" {{ request('status') == 'replied' ? 'selected' : '' }}>Replied</option>
                    </select>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Search</label>
                    <input type="text" name="search" class="form-control" placeholder="Search by name, email, subject..." value="{{ request('search') }}">
                </div>
                <div class="col-md-2 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="fas fa-search"></i> Filter
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Messages Table -->
    <div class="card">
        <div class="card-header py-2">
            <h6 class="card-title mb-0 text-white">
                <i class="fas fa-table me-2"></i>Messages Table
            </h6>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Status</th>
                            <th>Date</th>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Phone</th>
                            <th>Subject</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($messages as $message)
                            <tr class="{{ $message->status === 'new' ? 'table-warning' : '' }}">
                                <td>
                                    @if($message->status === 'new')
                                        <span class="badge bg-danger">New</span>
                                    @elseif($message->status === 'read')
                                        <span class="badge bg-info">Read</span>
                                    @else
                                        <span class="badge bg-success">Replied</span>
                                    @endif
                                </td>
                                <td>{{ $message->created_at->format('M d, Y H:i') }}</td>
                                <td>{{ $message->name }}</td>
                                <td>{{ $message->email }}</td>
                                <td>{{ $message->phone ?? 'N/A' }}</td>
                                <td>{{ Str::limit($message->subject, 40) }}</td>
                                <td>
                                    <div class="btn-group btn-group-sm">
                                        <a href="{{ route('admin.contact-messages.show', $message->id) }}" class="btn btn-primary" title="View">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <form action="{{ route('admin.contact-messages.destroy', $message->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Are you sure you want to delete this message?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-danger" title="Delete">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center py-4">
                                    <i class="fas fa-inbox fa-3x text-muted mb-3"></i>
                                    <p class="text-muted">No messages found</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div class="mt-3">
                {{ $messages->links() }}
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
/* Breadcrumb styles */
.breadcrumb {
    background: transparent;
    padding: 0.5rem 0;
    margin-bottom: 0;
    font-size: 0.875rem;
}

.breadcrumb-item {
    display: flex;
    align-items: center;
}

.breadcrumb-item + .breadcrumb-item::before {
    content: "›";
    color: #6c757d;
    font-size: 1.2rem;
    font-weight: 600;
    margin: 0 0.5rem;
}

.breadcrumb-item a {
    color: #6c757d;
    text-decoration: none;
    transition: color 0.2s ease;
}

.breadcrumb-item a:hover {
    color: #0d6efd;
}

.breadcrumb-item.active {
    color: #495057;
    font-weight: 500;
}

.breadcrumb-item i {
    font-size: 0.75rem;
}

/* Card header height reduction */
.card-header {
    padding: 0.5rem 1rem;
    min-height: 2.5rem;
}

.card-header h6 {
    font-size: 0.95rem;
    font-weight: 600;
    line-height: 1.2;
}

/* Font size reductions */
h1.h3 {
    font-size: 1.5rem;
}

.form-label {
    font-size: 0.875rem;
    font-weight: 500;
}

.form-control, .form-select {
    font-size: 0.875rem;
}

.btn {
    font-size: 0.875rem;
}

.badge {
    font-size: 0.75rem;
}

.table th {
    font-size: 0.875rem;
    font-weight: 600;
}

.table td {
    font-size: 0.85rem;
}

.btn-group-sm .btn {
    font-size: 0.75rem;
    padding: 0.25rem 0.5rem;
}

.text-muted {
    font-size: 0.8rem;
}

.fa-3x {
    font-size: 2rem !important;
}

/* Remove table header rounded corners */
table, thead, th {
    border-radius: 0 !important;
}

/* Responsive improvements for card headers */
@media (max-width: 768px) {
    .card-header {
        padding: 0.25rem 0.5rem;
        min-height: 2rem;
    }
    
    .card-header h6 {
        font-size: 0.85rem;
    }
    
    h1.h3 {
        font-size: 1.25rem;
    }
    
    .form-label {
        font-size: 0.8rem;
    }
    
    .form-control, .form-select {
        font-size: 0.8rem;
    }
    
    .btn {
        font-size: 0.8rem;
    }
    
    .table th {
        font-size: 0.8rem;
    }
    
    .table td {
        font-size: 0.75rem;
    }
}
</style>
@endpush
