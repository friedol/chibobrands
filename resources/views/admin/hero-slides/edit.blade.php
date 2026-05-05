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
                <i class="fas fa-edit me-1"></i>Edit Slide
            </li>
        </ol>
    </nav>

    <!-- Page Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0 text-gray-800">
                @if($isAd)
                    Edit Advertisement
                @else
                    Edit Hero Slide
                @endif
            </h1>
            <p class="text-muted mb-0">
                @if($isAd)
                    Update advertisement settings and content
                @else
                    Update slide content and settings
                @endif
            </p>
        </div>
        <a href="{{ route('admin.hero-slides.index') }}" class="btn btn-outline-secondary">
            <i class="fas fa-arrow-left me-2"></i>Back to Slides
        </a>
    </div>

    <!-- Edit Form -->
    <div class="row">
        <div class="col-lg-8">
            <div class="card shadow">
                <div class="card-header py-2">
                    <h6 class="mb-0 text-white">Slide Information</h6>
                </div>
                <div class="card-body">
                    <form action="{{ route('admin.hero-slides.update', $heroSlide) }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        @method('PUT')
                        
                        <div class="row">
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label for="title" class="form-label">Title</label>
                                    <input type="text" 
                                           class="form-control @error('title') is-invalid @enderror" 
                                           id="title" 
                                           name="title" 
                                           value="{{ old('title', $heroSlide->title) }}" 
                                           >
                                    @error('title')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label for="page_type" class="form-label">Page Type <span class="text-danger">*</span></label>
                                    <select class="form-select @error('page_type') is-invalid @enderror" 
                                            id="page_type" 
                                            name="page_type" 
                                            required>
                                        <option value="">Select Page Type</option>
                                        <option value="all" {{ old('page_type', $heroSlide->page_type) == 'all' ? 'selected' : '' }}>All Pages</option>
                                        <option value="homepage" {{ old('page_type', $heroSlide->page_type) == 'homepage' ? 'selected' : '' }}>Homepage Only</option>
                                        <option value="services" {{ old('page_type', $heroSlide->page_type) == 'services' ? 'selected' : '' }}>Services Page Only</option>
                                        <option value="products" {{ old('page_type', $heroSlide->page_type) == 'products' ? 'selected' : '' }}>Products Pages Only</option>
                                        <option value="wholesale_services" {{ old('page_type', $heroSlide->page_type) == 'wholesale_services' ? 'selected' : '' }}>Wholesale Services Only</option>
                                        <option value="wholesale_homepage" {{ old('page_type', $heroSlide->page_type) == 'wholesale_homepage' ? 'selected' : '' }}>Wholesale Homepage Only</option>
                                    </select>
                                    @error('page_type')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label for="sort_order" class="form-label">Sort Order</label>
                                    <input type="number" 
                                           class="form-control @error('sort_order') is-invalid @enderror" 
                                           id="sort_order" 
                                           name="sort_order" 
                                           value="{{ old('sort_order', $heroSlide->sort_order) }}" 
                                           min="0">
                                    @error('sort_order')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="subtitle" class="form-label">Subtitle</label>
                            <textarea class="form-control @error('subtitle') is-invalid @enderror" 
                                      id="subtitle" 
                                      name="subtitle" 
                                      rows="3">{{ old('subtitle', $heroSlide->subtitle) }}</textarea>
                            @error('subtitle')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="image" class="form-label">Background Media (Image/Video/GIF)</label>
                            @if($heroSlide->image_path)
                                <div class="mb-2">
                                    @php
                                        $extension = strtolower(pathinfo($heroSlide->image_path, PATHINFO_EXTENSION));
                                        $isVideo = in_array($extension, ['mp4', 'avi', 'mov', 'wmv', 'flv', 'webm', 'mkv', '3gp', 'mpg', 'mpeg']);
                                    @endphp
                                    
                                    @if($isVideo)
                                        <video class="img-thumbnail" style="max-width: 200px; height: auto;" controls>
                                            <source src="{{ asset('storage/' . $heroSlide->image_path) }}" type="video/{{ $extension }}">
                                            Your browser does not support the video tag.
                                        </video>
                                    @else
                                        <img src="{{ asset('storage/' . $heroSlide->image_path) }}" 
                                             alt="Current media" 
                                             class="img-thumbnail" 
                                             style="max-width: 200px;">
                                    @endif
                                    <div class="form-text">Current media</div>
                                </div>
                            @endif
                            <input type="file" 
                                   class="form-control @error('image') is-invalid @enderror" 
                                   id="image" 
                                   name="image" 
                                   accept="image/*,video/*,.gif">
                            <div class="form-text">
                                <i class="fas fa-info-circle me-1"></i>
                                Supported formats: Images (JPEG, PNG, JPG, GIF, WEBP, SVG) and Videos (MP4, AVI, MOV, WMV, FLV, WEBM, MKV, 3GP, MPG, MPEG). 
                                Maximum file size: 10MB (10240 KB)
                            </div>
                          
                            @error('image')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="button_text" class="form-label">Button Text</label>
                                    <input type="text" 
                                           class="form-control @error('button_text') is-invalid @enderror" 
                                           id="button_text" 
                                           name="button_text" 
                                           value="{{ old('button_text', $heroSlide->button_text) }}">
                                    @error('button_text')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="button_url" class="form-label">Button URL</label>
                                    <input type="url" 
                                           class="form-control @error('button_url') is-invalid @enderror" 
                                           id="button_url" 
                                           name="button_url" 
                                           value="{{ old('button_url', $heroSlide->button_url) }}">
                                    @error('button_url')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="button_color" class="form-label">Button Color</label>
                            <div class="input-group">
                                <input type="color" 
                                       class="form-control form-control-color @error('button_color') is-invalid @enderror" 
                                       id="button_color" 
                                       name="button_color" 
                                       value="{{ old('button_color', $heroSlide->button_color) }}">
                                <input type="text" 
                                       class="form-control" 
                                       id="button_color_text" 
                                       value="{{ old('button_color', $heroSlide->button_color) }}" 
                                       readonly>
                            </div>
                            @error('button_color')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Ad-specific fields -->
                        @if($isAd)
                            <div class="card mb-4">
                                <div class="card-header bg-warning text-dark">
                                    <h6 class="mb-0"><i class="fas fa-ad me-2"></i>Advertisement Settings</h6>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label for="ad_type" class="form-label">Ad Type <span class="text-danger">*</span></label>
                                                <select class="form-select @error('ad_type') is-invalid @enderror" 
                                                        id="ad_type" 
                                                        name="ad_type" 
                                                        required>
                                                    <option value="">Select Ad Type</option>
                                                    <option value="banner" {{ old('ad_type', $heroSlide->ad_type) == 'banner' ? 'selected' : '' }}>Banner</option>
                                                    <option value="popup" {{ old('ad_type', $heroSlide->ad_type) == 'popup' ? 'selected' : '' }}>Popup</option>
                                                    <option value="sidebar" {{ old('ad_type', $heroSlide->ad_type) == 'sidebar' ? 'selected' : '' }}>Sidebar</option>
                                                    <option value="inline" {{ old('ad_type', $heroSlide->ad_type) == 'inline' ? 'selected' : '' }}>Inline</option>
                                                </select>
                                                @error('ad_type')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label for="ad_position" class="form-label">Ad Position</label>
                                                <select class="form-select @error('ad_position') is-invalid @enderror" 
                                                        id="ad_position" 
                                                        name="ad_position">
                                                    <option value="">Select Position</option>
                                                    <option value="top" {{ old('ad_position', $heroSlide->ad_position) == 'top' ? 'selected' : '' }}>Top</option>
                                                    <option value="bottom" {{ old('ad_position', $heroSlide->ad_position) == 'bottom' ? 'selected' : '' }}>Bottom</option>
                                                    <option value="left" {{ old('ad_position', $heroSlide->ad_position) == 'left' ? 'selected' : '' }}>Left</option>
                                                    <option value="right" {{ old('ad_position', $heroSlide->ad_position) == 'right' ? 'selected' : '' }}>Right</option>
                                                    <option value="center" {{ old('ad_position', $heroSlide->ad_position) == 'center' ? 'selected' : '' }}>Center</option>
                                                </select>
                                                @error('ad_position')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label for="ad_duration" class="form-label">Auto-close Duration (seconds)</label>
                                                <input type="number" 
                                                       class="form-control @error('ad_duration') is-invalid @enderror" 
                                                       id="ad_duration" 
                                                       name="ad_duration" 
                                                       value="{{ old('ad_duration', $heroSlide->ad_duration) }}" 
                                                       min="1">
                                                @error('ad_duration')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label for="ad_target_audience" class="form-label">Target Audience</label>
                                                <select class="form-select @error('ad_target_audience') is-invalid @enderror" 
                                                        id="ad_target_audience" 
                                                        name="ad_target_audience">
                                                    <option value="all" {{ old('ad_target_audience', $heroSlide->ad_target_audience) == 'all' ? 'selected' : '' }}>All Users</option>
                                                    <option value="retail" {{ old('ad_target_audience', $heroSlide->ad_target_audience) == 'retail' ? 'selected' : '' }}>Retail Customers</option>
                                                    <option value="wholesale" {{ old('ad_target_audience', $heroSlide->ad_target_audience) == 'wholesale' ? 'selected' : '' }}>Wholesale Customers</option>
                                                    <option value="new_customers" {{ old('ad_target_audience', $heroSlide->ad_target_audience) == 'new_customers' ? 'selected' : '' }}>New Customers</option>
                                                    <option value="returning_customers" {{ old('ad_target_audience', $heroSlide->ad_target_audience) == 'returning_customers' ? 'selected' : '' }}>Returning Customers</option>
                                                </select>
                                                @error('ad_target_audience')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label for="ad_start_date" class="form-label">Start Date</label>
                                                <input type="date" 
                                                       class="form-control @error('ad_start_date') is-invalid @enderror" 
                                                       id="ad_start_date" 
                                                       name="ad_start_date" 
                                                       value="{{ old('ad_start_date', $heroSlide->ad_start_date?->format('Y-m-d')) }}">
                                                @error('ad_start_date')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label for="ad_end_date" class="form-label">End Date</label>
                                                <input type="date" 
                                                       class="form-control @error('ad_end_date') is-invalid @enderror" 
                                                       id="ad_end_date" 
                                                       name="ad_end_date" 
                                                       value="{{ old('ad_end_date', $heroSlide->ad_end_date?->format('Y-m-d')) }}">
                                                @error('ad_end_date')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <div class="row">
                                        <div class="col-md-4">
                                            <div class="mb-3">
                                                <label for="ad_budget" class="form-label">Budget (TZS)</label>
                                                <input type="number" 
                                                       class="form-control @error('ad_budget') is-invalid @enderror" 
                                                       id="ad_budget" 
                                                       name="ad_budget" 
                                                       value="{{ old('ad_budget', $heroSlide->ad_budget) }}" 
                                                       min="0" 
                                                       step="0.01">
                                                @error('ad_budget')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="mb-3">
                                                <label for="ad_cost_per_click" class="form-label">Cost Per Click (TZS)</label>
                                                <input type="number" 
                                                       class="form-control @error('ad_cost_per_click') is-invalid @enderror" 
                                                       id="ad_cost_per_click" 
                                                       name="ad_cost_per_click" 
                                                       value="{{ old('ad_cost_per_click', $heroSlide->ad_cost_per_click) }}" 
                                                       min="0" 
                                                       step="0.01">
                                                @error('ad_cost_per_click')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="mb-3">
                                                <label for="ad_cost_per_impression" class="form-label">Cost Per Impression (TZS)</label>
                                                <input type="number" 
                                                       class="form-control @error('ad_cost_per_impression') is-invalid @enderror" 
                                                       id="ad_cost_per_impression" 
                                                       name="ad_cost_per_impression" 
                                                       value="{{ old('ad_cost_per_impression', $heroSlide->ad_cost_per_impression) }}" 
                                                       min="0" 
                                                       step="0.01">
                                                @error('ad_cost_per_impression')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <div class="mb-3">
                                        <div class="form-check">
                                            <input class="form-check-input" 
                                                   type="checkbox" 
                                                   id="ad_closable" 
                                                   name="ad_closable" 
                                                   value="1" 
                                                   {{ old('ad_closable', $heroSlide->ad_closable) ? 'checked' : '' }}>
                                            <label class="form-check-label" for="ad_closable">
                                                Users can close this ad
                                            </label>
                                        </div>
                                    </div>
                                    
                                    <!-- Ad Statistics -->
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label class="form-label">Ad Statistics</label>
                                                <div class="card bg-light">
                                                    <div class="card-body py-2">
                                                        <div class="row text-center">
                                                            <div class="col-4">
                                                                <div class="fw-bold text-primary">{{ $heroSlide->ad_click_count }}</div>
                                                                <small class="text-muted">Clicks</small>
                                                            </div>
                                                            <div class="col-4">
                                                                <div class="fw-bold text-info">{{ $heroSlide->ad_impression_count }}</div>
                                                                <small class="text-muted">Views</small>
                                                            </div>
                                                            <div class="col-4">
                                                                <div class="fw-bold text-success">
                                                                    @if($heroSlide->ad_impression_count > 0)
                                                                        {{ number_format(($heroSlide->ad_click_count / $heroSlide->ad_impression_count) * 100, 2) }}%
                                                                    @else
                                                                        0%
                                                                    @endif
                                                                </div>
                                                                <small class="text-muted">CTR</small>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endif

                        <div class="mb-3">
                            <div class="form-check">
                                <input class="form-check-input" 
                                       type="checkbox" 
                                       id="is_active" 
                                       name="is_active" 
                                       value="1" 
                                       {{ old('is_active', $heroSlide->is_active) ? 'checked' : '' }}>
                                <label class="form-check-label" for="is_active">
                                    Active (show this {{ $isAd ? 'ad' : 'slide' }})
                                </label>
                            </div>
                        </div>
                        
                        <!-- Hidden field to indicate if this is an ad -->
                        <input type="hidden" name="is_ad" value="{{ $isAd ? '1' : '0' }}">

                        <div class="d-flex gap-2">
                            <button type="submit" class="btn {{ $isAd ? 'btn-warning' : 'btn-danger' }}" data-no-global-handler>
                                <i class="fas fa-save me-2"></i>Update {{ $isAd ? 'Ad' : 'Slide' }}
                            </button>
                            <a href="{{ route('admin.hero-slides.index') }}" class="btn btn-outline-secondary">
                                Cancel
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        
        <div class="col-lg-4">
            <div class="card shadow">
                <div class="card-header py-2">
                    <h6 class="mb-0 text-white">Preview</h6>
                </div>
                <div class="card-body">
                    <div id="slide-preview" class="hero-preview" 
                         @if($heroSlide->image_path)
                             style="background-image: url('{{ asset('storage/' . $heroSlide->image_path) }}'); background-size: cover; background-position: center;"
                         @endif>
                        <div class="hero-preview-content">
                            <h3 id="preview-title">{{ $heroSlide->title }}</h3>
                            <p id="preview-subtitle">{{ $heroSlide->subtitle ?: 'Slide subtitle will appear here' }}</p>
                            @if($heroSlide->button_text)
                                <button id="preview-button" class="btn" style="background-color: {{ $heroSlide->button_color }};">{{ $heroSlide->button_text }}</button>
                            @else
                                <button id="preview-button" class="btn" style="display: none;">Button Text</button>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@push('styles')
<style>
    .hero-preview {
        background: #6c757d;
        min-height: 200px;
        border-radius: 8px;
        position: relative;
        overflow: hidden;
        display: flex;
        align-items: center;
        justify-content: center;
        border: 1px solid #495057;
    }
    
    .hero-preview-content {
        text-align: center;
        color: #333333;
        z-index: 2;
        position: relative;
    }
    
    .hero-preview h3 {
        font-size: 1.5rem;
        font-weight: 700;
        margin-bottom: 0.5rem;
        text-shadow: 0 2px 10px rgba(0,0,0,0.1);
    }
    
    .hero-preview p {
        font-size: 1rem;
        color: #666666;
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
</style>
@endpush

@push('scripts')
<script>
$(document).ready(function() {
    // Update preview when inputs change
    function updatePreview() {
        const title = $('#title').val() || 'Slide Title';
        const subtitle = $('#subtitle').val() || 'Slide subtitle will appear here';
        const buttonText = $('#button_text').val();
        const buttonColor = $('#button_color').val();
        
        $('#preview-title').text(title);
        $('#preview-subtitle').text(subtitle);
        
        if (buttonText) {
            $('#preview-button').text(buttonText).show();
            $('#preview-button').css('background-color', buttonColor);
        } else {
            $('#preview-button').hide();
        }
    }
    
    // Bind input events
    $('#title, #subtitle, #button_text, #button_color').on('input change', updatePreview);
    
    // Sync color picker with text input
    $('#button_color').on('change', function() {
        $('#button_color_text').val($(this).val());
    });
    
    $('#button_color_text').on('input', function() {
        $('#button_color').val($(this).val());
        updatePreview();
    });
    
    // Media preview
    $('#image').on('change', function(e) {
        const file = e.target.files[0];
        if (file) {
            const reader = new FileReader();
            reader.onload = function(e) {
                const fileName = file.name.toLowerCase();
                const isVideo = fileName.match(/\.(mp4|avi|mov|wmv|flv|webm|mkv|3gp|mpg|mpeg)$/);
                
                if (isVideo) {
                    // Handle video preview
                    const videoHtml = `
                        <video style="position: absolute; top: 0; left: 0; width: 100%; height: 100%; object-fit: cover; z-index: 0;" autoplay muted loop>
                            <source src="${e.target.result}" type="video/${fileName.split('.').pop()}">
                        </video>
                    `;
                    $('#slide-preview').html(videoHtml + $('#slide-preview').html());
                } else {
                    // Handle image preview
                    $('#slide-preview').css('background-image', 'url(' + e.target.result + ')');
                    $('#slide-preview').css('background-size', 'cover');
                    $('#slide-preview').css('background-position', 'center');
                }
            };
            reader.readAsDataURL(file);
        }
    });
    
    // Initial preview update
    updatePreview();
});
</script>
@endpush
@endsection
