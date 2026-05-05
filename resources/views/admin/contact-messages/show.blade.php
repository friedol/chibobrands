@extends('layouts.admin')

@section('title', 'View Message')

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
            <li class="breadcrumb-item">
                <a href="{{ route('admin.contact-messages.index') }}" class="text-decoration-none">
                    <i class="fas fa-envelope me-1"></i>Contact Messages
                </a>
            </li>
            <li class="breadcrumb-item active" aria-current="page">
                <i class="fas fa-eye me-1"></i>View Message
            </li>
        </ol>
    </nav>

    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0">Contact Message Details</h1>
        <a href="{{ route('admin.contact-messages.index') }}" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> Back to Messages
        </a>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="fas fa-check-circle"></i> {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="fas fa-exclamation-circle"></i> {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="row">
        <!-- Message Details -->
        <div class="col-lg-8">
            <div class="card mb-4">
                <div class="card-header py-2">
                    <h6 class="card-title mb-0 text-white">
                        <i class="fas fa-envelope me-2"></i>Message Information
                    </h6>
                </div>
                <div class="card-body">
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <strong>Name:</strong><br>
                            {{ $message->name }}
                        </div>
                        <div class="col-md-6">
                            <strong>Email:</strong><br>
                            <a href="mailto:{{ $message->email }}">{{ $message->email }}</a>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <strong>Phone:</strong><br>
                            {{ $message->phone ?? 'N/A' }}
                        </div>
                        <div class="col-md-6">
                            <strong>Status:</strong><br>
                            @if($message->status === 'new')
                                <span class="badge bg-danger">New</span>
                            @elseif($message->status === 'read')
                                <span class="badge bg-info">Read</span>
                            @else
                                <span class="badge bg-success">Replied</span>
                            @endif
                        </div>
                    </div>

                    <div class="mb-3">
                        <strong>Subject:</strong><br>
                        {{ $message->subject }}
                    </div>

                    <div class="mb-3">
                        <strong>Message:</strong><br>
                        <div class="p-3 bg-light rounded">
                            {{ $message->message }}
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <small class="text-muted">
                                <i class="fas fa-calendar"></i> Received: {{ $message->created_at->format('M d, Y H:i A') }}
                            </small>
                        </div>
                        @if($message->replied_at)
                            <div class="col-md-6">
                                <small class="text-muted">
                                    <i class="fas fa-reply"></i> Replied: {{ $message->replied_at->format('M d, Y H:i A') }}
                                </small>
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Admin Reply Section -->
            @if($message->admin_reply)
                <div class="card mb-4">
                    <div class="card-header py-2">
                        <h6 class="card-title mb-0 text-white">
                            <i class="fas fa-reply me-2"></i>Your Reply
                        </h6>
                    </div>
                    <div class="card-body">
                        <div class="p-3 bg-light rounded">
                            {{ $message->admin_reply }}
                        </div>
                        @if($message->repliedBy)
                            <small class="text-muted mt-2 d-block">
                                Replied by: {{ $message->repliedBy->name }}
                            </small>
                        @endif
                    </div>
                </div>
            @else
                <!-- Send Email Reply (Queue-Based) -->
                <div class="card mb-3">
                    <div class="card-header py-2">
                        <h6 class="card-title mb-0 text-white">
                            <i class="fas fa-envelope me-2"></i>Send Email Reply to Customer
                        </h6>
                    </div>
                    <div class="card-body">
                        <form action="{{ route('admin.contact-messages.send-email', $message->id) }}" method="POST" id="emailReplyForm">
                            @csrf
                            
                            <div class="mb-3">
                                <label for="email_subject" class="form-label">Email Subject *</label>
                                <input type="text" name="email_subject" id="email_subject" class="form-control @error('email_subject') is-invalid @enderror" value="{{ old('email_subject', 'Re: ' . $message->subject) }}" required>
                                @error('email_subject')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="mb-3">
                                <label for="email_reply" class="form-label">Your Reply Message *</label>
                                <textarea name="email_reply" id="email_reply" class="form-control @error('email_reply') is-invalid @enderror" rows="8" required placeholder="Write your reply message here...">{{ old('email_reply') }}</textarea>
                                @error('email_reply')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <small class="text-muted">
                                    <i class="fas fa-info-circle"></i> Email will be sent to <strong>{{ $message->email }}</strong>
                                </small>
                            </div>

                            <div class="alert alert-info mb-3">
                                <i class="fas fa-paper-plane"></i> <strong>How it works:</strong><br>
                                • Your reply is saved immediately in the database<br>
                                • Email is sent in the background (won't freeze the page)<br>
                                • Customer receives a professional formatted email<br>
                                • Check logs if email doesn't arrive
                            </div>

                            <button type="submit" class="btn btn-primary btn-lg w-100" id="sendEmailBtn">
                                <i class="fas fa-paper-plane me-2"></i> Send Email Reply
                            </button>
                        </form>
                    </div>
                </div>

                <script>
                document.getElementById('emailReplyForm').addEventListener('submit', function(e) {
                    const btn = document.getElementById('sendEmailBtn');
                    btn.disabled = true;
                    btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Sending...';
                });
                </script>
            @endif
        </div>

        <!-- Quick Actions -->
        <div class="col-lg-4">
            <div class="card">
                <div class="card-header py-2">
                    <h6 class="card-title mb-0 text-white">
                        <i class="fas fa-tasks me-2"></i>Quick Actions
                    </h6>
                </div>
                <div class="card-body">
                    <div class="d-grid gap-2">
                        <a href="mailto:{{ $message->email }}" class="btn btn-primary">
                            <i class="fas fa-envelope"></i> Send Email
                        </a>
                        @if($message->phone)
                            <a href="tel:{{ $message->phone }}" class="btn btn-success">
                                <i class="fas fa-phone"></i> Call {{ $message->phone }}
                            </a>
                            <a href="https://wa.me/{{ preg_replace('/[^0-9]/', '', $message->phone) }}" target="_blank" class="btn btn-success">
                                <i class="fab fa-whatsapp"></i> WhatsApp
                            </a>
                        @endif
                        <form action="{{ route('admin.contact-messages.destroy', $message->id) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete this message?');">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-danger w-100">
                                <i class="fas fa-trash"></i> Delete Message
                            </button>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Message Info -->
            <div class="card mt-3">
                <div class="card-header py-2">
                    <h6 class="card-title mb-0 text-white">
                        <i class="fas fa-info-circle me-2"></i>Message Info
                    </h6>
                </div>
                <div class="card-body">
                    <p class="mb-2"><strong>ID:</strong> #{{ $message->id }}</p>
                    <p class="mb-2"><strong>Received:</strong><br>{{ $message->created_at->diffForHumans() }}</p>
                    @if($message->status === 'replied' && $message->replied_at)
                        <p class="mb-0"><strong>Replied:</strong><br>{{ $message->replied_at->diffForHumans() }}</p>
                    @endif
                </div>
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

.text-muted {
    font-size: 0.8rem;
}

small {
    font-size: 0.75rem;
}

.alert {
    font-size: 0.85rem;
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
}
</style>
@endpush
