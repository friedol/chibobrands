@extends('layouts.admin')

@section('page-title', 'My Profile')

@section('content')
<div class="container-fluid">
    <h2 class="mb-4">My Profile</h2>

    <div class="row">
        <!-- Profile Card -->
        <div class="col-lg-4 mb-4">
            <div class="card text-center">
                <div class="card-body">
                    <div class="profile-image mb-3">
                        @if(auth()->user()->profile_image)
                            <img src="{{ asset('storage/' . auth()->user()->profile_image) }}" alt="{{ auth()->user()->name }}" id="preview">
                        @else
                            <div class="placeholder" id="placeholder">{{ substr(auth()->user()->name, 0, 1) }}</div>
                        @endif
                    </div>
                    <h4>{{ auth()->user()->name }}</h4>
                    <p class="text-muted">{{ auth()->user()->email }}</p>
                    <span class="badge bg-primary">{{ ucfirst(auth()->user()->role ?? 'Admin') }}</span>
                </div>
            </div>
        </div>

        <!-- Forms -->
        <div class="col-lg-8">
            <div class="card mb-3">
                <div class="card-header"><h5>Personal Information</h5></div>
                <div class="card-body">
                    <form method="POST" action="{{ route('admin.profile.update') }}" enctype="multipart/form-data" data-no-preloader>
                        @csrf
                        @method('PUT')
                        
                        <div class="mb-3">
                            <label class="form-label">Profile Image</label>
                            <input type="file" class="form-control" name="profile_image" accept="image/*">
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Name</label>
                                <input type="text" class="form-control" name="name" value="{{ auth()->user()->name }}" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Email</label>
                                <input type="email" class="form-control" name="email" value="{{ auth()->user()->email }}" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">WhatsApp Number</label>
                                <input type="text" class="form-control" name="phone" value="{{ auth()->user()->phone }}" placeholder="e.g. +255 655 392 319">
                                <small class="text-muted">Include country code, e.g. +255...</small>
                            </div>
                        </div>

                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-danger" data-no-global-handler>
                                <i class="fas fa-save me-2"></i>Save Changes
                            </button>
                            <a href="{{ route('admin.dashboard') }}" class="btn btn-outline-secondary">Cancel</a>
                        </div>
                    </form>
                </div>
            </div>

            <div class="card">
                <div class="card-header"><h5>Change Password</h5></div>
                <div class="card-body">
                    <form method="POST" action="{{ route('admin.profile.password') }}" data-no-preloader>
                        @csrf
                        @method('PUT')
                        
                        <div class="mb-3">
                            <label class="form-label">Current Password</label>
                            <input type="password" class="form-control" name="current_password" required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">New Password</label>
                            <input type="password" class="form-control" name="password" required minlength="8">
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Confirm Password</label>
                            <input type="password" class="form-control" name="password_confirmation" required>
                        </div>

                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-danger" data-no-global-handler>
                                <i class="fas fa-key me-2"></i>Update Password
                            </button>
                            <a href="{{ route('admin.dashboard') }}" class="btn btn-outline-secondary">Cancel</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.profile-image { width: 120px; height: 120px; border-radius: 50%; overflow: hidden; margin: 0 auto; border: 4px solid #f8f9fa; }
.profile-image img { width: 100%; height: 100%; object-fit: cover; }
.placeholder { width: 100%; height: 100%; background: linear-gradient(135deg, #FF0000, #cc0000); color: white; display: flex; align-items: center; justify-content: center; font-size: 3rem; font-weight: 700; }
.card { border-radius: 12px; box-shadow: 0 2px 8px rgba(0,0,0,0.05); }

/* Standardized 12/14px font sizes */
.container-fluid { font-size: 14px; }
.card-header { padding: 0.75rem 1rem; }
.card-header h5 { font-size: 14px !important; font-weight: 700; margin: 0; }
.card-body { padding: 1rem; }
h2.mb-4 { font-size: 1.25rem; }
.form-label { font-size: 13px; font-weight: 600; }
.form-control { font-size: 13px; padding: 0.45rem 0.75rem; }
.btn { font-size: 13px; padding: 0.5rem 1rem; }
.text-muted { font-size: 12px !important; }
.badge { font-size: 11px !important; }
h4 { font-size: 1.1rem; }
</style>
@endsection
