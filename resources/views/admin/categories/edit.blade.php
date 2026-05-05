@extends('layouts.admin')

@section('title', 'Edit Category - CHIBO BRAND Admin')
@section('description', 'Edit category information')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h2 class="h3 mb-1">Edit Category</h2>
        <p class="text-muted mb-0">Update category information</p>
    </div>
    <div>
        <a href="{{ route('admin.categories.index') }}" class="btn btn-outline-secondary">
            <i class="fas fa-arrow-left me-2"></i>Back to Categories
        </a>
    </div>
</div>

@if(session('success'))
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif

@if(session('error'))
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <i class="fas fa-exclamation-triangle me-2"></i>{{ session('error') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif

<form method="POST" action="{{ route('admin.categories.update', $category) }}" enctype="multipart/form-data">
    @csrf
    @method('PUT')
    
    <div class="row">
        <!-- Main Content -->
        <div class="col-lg-8">
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="card-title mb-0">Category Information</h5>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label for="name" class="form-label fw-bold">
                            Category Name <span class="text-danger">*</span>
                        </label>
                        <input type="text" 
                               class="form-control @error('name') is-invalid @enderror" 
                               id="name" 
                               name="name" 
                               value="{{ old('name', $category->name) }}" 
                               placeholder="Enter category name" 
                               required>
                        @error('name')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    
                    <div class="mb-3">
                        <label for="description" class="form-label fw-bold">Description</label>
                        <textarea class="form-control @error('description') is-invalid @enderror" 
                                  id="description" 
                                  name="description" 
                                  rows="4"
                                  placeholder="Enter category description (optional)">{{ old('description', $category->description) }}</textarea>
                        @error('description')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="mb-3">
                        <label for="image" class="form-label fw-bold">Category Image</label>
                        @if($category->image_path)
                            <div class="mb-2">
                                <img src="{{ asset('storage/'.$category->image_path) }}" alt="{{ $category->name }}" style="height:60px;border-radius:6px;object-fit:cover;">
                            </div>
                        @endif
                        <input type="file" name="image" id="image" class="form-control @error('image') is-invalid @enderror" accept="image/*">
                        @error('image')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <small class="text-muted">Upload JPG/PNG/WebP up to 2MB.</small>
                    </div>
                    
                    <div class="form-check form-switch">
                        <input class="form-check-input" 
                               type="checkbox" 
                               role="switch"
                               id="is_active" 
                               name="is_active" 
                               value="1" 
                               {{ old('is_active', $category->is_active) ? 'checked' : '' }}>
                        <label class="form-check-label fw-bold" for="is_active">
                            Active Status
                        </label>
                        <small class="form-text text-muted d-block">Enable this category to be visible on the website</small>
                    </div>
                </div>
            </div>
        </div>

        <!-- Sidebar -->
        <div class="col-lg-4">
            <!-- Category Info -->
            <div class="card mb-3">
                <div class="card-header">
                    <h6 class="card-title mb-0">Category Details</h6>
                </div>
                <div class="card-body">
                    <p class="mb-2"><strong>ID:</strong> {{ $category->id }}</p>
                    <p class="mb-2"><strong>Created:</strong> {{ $category->created_at->format('M d, Y') }}</p>
                    <p class="mb-0"><strong>Updated:</strong> {{ $category->updated_at->format('M d, Y') }}</p>
                </div>
            </div>

            <!-- Actions -->
            <div class="card">
                <div class="card-body">
                    <div class="d-grid gap-2">
                        <button type="submit" class="btn btn-primary" data-no-global-handler>
                            <i class="fas fa-save me-2"></i>Update Category
                        </button>
                        <a href="{{ route('admin.categories.index') }}" class="btn btn-outline-secondary">
                            <i class="fas fa-times me-2"></i>Cancel
                        </a>
                        <hr>
                        <button type="button" 
                                class="btn btn-outline-danger" 
                                onclick="modernConfirm('Are you sure you want to delete this category? This action cannot be undone.', () => document.getElementById('delete-form').submit(), { title: 'Delete Category', type: 'danger', icon: 'fa-trash', confirmText: 'Delete' })">
                            <i class="fas fa-trash me-2"></i>Delete Category
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</form>

<!-- Delete Form -->
<form id="delete-form" 
      action="{{ route('admin.categories.destroy', $category) }}" 
      method="POST" 
      class="d-none">
    @csrf
    @method('DELETE')
</form>
@endsection
