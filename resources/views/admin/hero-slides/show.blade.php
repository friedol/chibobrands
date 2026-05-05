@extends('layouts.admin')

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
                <a href="{{ route('admin.hero-slides.index') }}" class="text-decoration-none">
                    <i class="fas fa-images me-1"></i>Hero Slides
                </a>
            </li>
            <li class="breadcrumb-item active" aria-current="page">
                <i class="fas fa-eye me-1"></i>View Slide
            </li>
        </ol>
    </nav>

    <!-- Page Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0 text-gray-800">Hero Slide Details</h1>
            
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('admin.hero-slides.edit', $heroSlide) }}" class="btn btn-warning">
                <i class="fas fa-edit me-2"></i>Edit Slide
            </a>
            <a href="{{ route('admin.hero-slides.index') }}" class="btn btn-outline-secondary">
                <i class="fas fa-arrow-left me-2"></i>Back to Slides
            </a>
        </div>
    </div>

    <!-- Slide Details -->
    <div class="row">
        <div class="col-lg-8">
            <div class="card shadow">
                <div class="card-header py-2">
                    <h6 class="mb-0 text-white">Slide Information</h6>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label fw-semibold">Title</label>
                                <p class="form-control-plaintext">{{ $heroSlide->title }}</p>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label fw-semibold">Sort Order</label>
                                <p class="form-control-plaintext">{{ $heroSlide->sort_order }}</p>
                            </div>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold">Subtitle</label>
                        <p class="form-control-plaintext">{{ $heroSlide->subtitle ?: 'No subtitle' }}</p>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold">Background Image</label>
                        @if($heroSlide->image_path)
                            <div class="mt-2">
                                <img src="{{ asset('storage/' . $heroSlide->image_path) }}" 
                                     alt="{{ $heroSlide->title }}" 
                                     class="img-fluid rounded" 
                                     style="max-height: 300px;">
                            </div>
                        @else
                            <p class="form-control-plaintext text-muted">No image uploaded</p>
                        @endif
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label fw-semibold">Button Text</label>
                                <p class="form-control-plaintext">{{ $heroSlide->button_text ?: 'No button' }}</p>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label fw-semibold">Button URL</label>
                                <p class="form-control-plaintext">
                                    @if($heroSlide->button_url)
                                        <a href="{{ $heroSlide->button_url }}" target="_blank" class="text-decoration-none">
                                            {{ $heroSlide->button_url }}
                                            <i class="fas fa-external-link-alt ms-1"></i>
                                        </a>
                                    @else
                                        No URL
                                    @endif
                                </p>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label fw-semibold">Button Color</label>
                                <div class="d-flex align-items-center gap-2">
                                    <div style="width: 30px; height: 30px; background-color: {{ $heroSlide->button_color }}; border-radius: 4px; border: 1px solid #ddd;"></div>
                                    <span class="form-control-plaintext">{{ $heroSlide->button_color }}</span>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label fw-semibold">Status</label>
                                <p class="form-control-plaintext">
                                    @if($heroSlide->is_active)
                                        <span class="badge bg-success">Active</span>
                                    @else
                                        <span class="badge bg-secondary">Inactive</span>
                                    @endif
                                </p>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label fw-semibold">Created At</label>
                                <p class="form-control-plaintext">{{ $heroSlide->created_at->format('M d, Y H:i') }}</p>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label fw-semibold">Updated At</label>
                                <p class="form-control-plaintext">{{ $heroSlide->updated_at->format('M d, Y H:i') }}</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-lg-4">
            <div class="card shadow">
                <div class="card-header py-2">
                    <h6 class="mb-0 text-white">Live Preview</h6>
                </div>
                <div class="card-body">
                    <div class="hero-preview" 
                         @if($heroSlide->image_path)
                             style="background-image: url('{{ asset('storage/' . $heroSlide->image_path) }}'); background-size: cover; background-position: center;"
                         @endif>
                        <div class="hero-preview-content">
                            <h3>{{ $heroSlide->title }}</h3>
                            <p>{{ $heroSlide->subtitle ?: 'Slide subtitle will appear here' }}</p>
                            @if($heroSlide->button_text)
                                <button class="btn" style="background-color: {{ $heroSlide->button_color }};">{{ $heroSlide->button_text }}</button>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Actions Card -->
            <div class="card shadow mt-3">
                <div class="card-header py-2">
                    <h6 class="mb-0 text-white">Actions</h6>
                </div>
                <div class="card-body">
                    <div class="d-grid gap-2">
                        <a href="{{ route('admin.hero-slides.edit', $heroSlide) }}" class="btn btn-warning">
                            <i class="fas fa-edit me-2"></i>Edit Slide
                        </a>
                        <form action="{{ route('admin.hero-slides.destroy', $heroSlide) }}" 
                              method="POST" 
                              onsubmit="return confirm('Are you sure you want to delete this slide?')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-danger w-100">
                                <i class="fas fa-trash me-2"></i>Delete Slide
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@push('styles')
<style>
    .hero-preview {
        background: linear-gradient(135deg, #0a0a0a 0%, #1a0000 50%, #000000 100%);
        min-height: 200px;
        border-radius: 8px;
        position: relative;
        overflow: hidden;
        display: flex;
        align-items: center;
        justify-content: center;
    }
    
    .hero-preview-content {
        text-align: center;
        color: white;
        z-index: 2;
        position: relative;
    }
    
    .hero-preview h3 {
        font-size: 1.5rem;
        font-weight: 700;
        margin-bottom: 0.5rem;
        text-shadow: 0 0 30px rgba(255,0,0,0.3);
    }
    
    .hero-preview p {
        font-size: 1rem;
        opacity: 0.85;
        margin-bottom: 1rem;
    }
    
    .hero-preview .btn {
        background: #ff0000;
        border: none;
        color: white;
        padding: 10px 20px;
        border-radius: 25px;
        font-weight: 600;
    }
</style>
@endpush
@endsection
