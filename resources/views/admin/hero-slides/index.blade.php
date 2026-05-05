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
            <li class="breadcrumb-item active" aria-current="page">
                <i class="fas fa-images me-1"></i>Hero Slides
            </li>
        </ol>
    </nav>

    <!-- Page Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0 text-gray-800">Hero Slides & Ads</h1>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('admin.hero-slides.create') }}" class="btn btn-danger">
                <i class="fas fa-plus me-2"></i>Add New Slide
            </a>
            <a href="{{ route('admin.hero-slides.create', ['type' => 'ad']) }}" class="btn btn-warning">
                <i class="fas fa-ad me-2"></i>Add New Ad
            </a>
            <a href="{{ route('admin.hero-slides.analytics') }}" class="btn btn-info">
                <i class="fas fa-chart-bar me-2"></i>Analytics
            </a>
        </div>
    </div>

    <!-- Filters -->
    <!--<div class="card shadow mb-4">-->
    <!--    <div class="card-body">-->
    <!--        <form method="GET" class="row g-3">-->
    <!--            <div class="col-md-3">-->
    <!--                <label for="type" class="form-label">Type</label>-->
    <!--                <select name="type" id="type" class="form-select">-->
    <!--                    <option value="">All</option>-->
    <!--                    <option value="slides" {{ request('type') === 'slides' ? 'selected' : '' }}>Slides Only</option>-->
    <!--                    <option value="ads" {{ request('type') === 'ads' ? 'selected' : '' }}>Ads Only</option>-->
    <!--                </select>-->
    <!--            </div>-->
    <!--            <div class="col-md-3">-->
    <!--                <label for="page_type" class="form-label">Page Type</label>-->
    <!--                <select name="page_type" id="page_type" class="form-select">-->
    <!--                    <option value="">All Pages</option>-->
    <!--                    <option value="all" {{ request('page_type') === 'all' ? 'selected' : '' }}>All Pages</option>-->
    <!--                    <option value="homepage" {{ request('page_type') === 'homepage' ? 'selected' : '' }}>Homepage</option>-->
    <!--                    <option value="services" {{ request('page_type') === 'services' ? 'selected' : '' }}>Services</option>-->
    <!--                    <option value="products" {{ request('page_type') === 'products' ? 'selected' : '' }}>Products</option>-->
    <!--                    <option value="wholesale_services" {{ request('page_type') === 'wholesale_services' ? 'selected' : '' }}>Wholesale Services</option>-->
    <!--                    <option value="wholesale_homepage" {{ request('page_type') === 'wholesale_homepage' ? 'selected' : '' }}>Wholesale Homepage</option>-->
    <!--                </select>-->
    <!--            </div>-->
    <!--            <div class="col-md-3">-->
    <!--                <label for="ad_type" class="form-label">Ad Type</label>-->
    <!--                <select name="ad_type" id="ad_type" class="form-select">-->
    <!--                    <option value="">All Ad Types</option>-->
    <!--                    <option value="banner" {{ request('ad_type') === 'banner' ? 'selected' : '' }}>Banner</option>-->
    <!--                    <option value="popup" {{ request('ad_type') === 'popup' ? 'selected' : '' }}>Popup</option>-->
    <!--                    <option value="sidebar" {{ request('ad_type') === 'sidebar' ? 'selected' : '' }}>Sidebar</option>-->
    <!--                    <option value="inline" {{ request('ad_type') === 'inline' ? 'selected' : '' }}>Inline</option>-->
    <!--                </select>-->
    <!--            </div>-->
    <!--            <div class="col-md-3">-->
    <!--                <label class="form-label">&nbsp;</label>-->
    <!--                <div class="d-flex gap-2">-->
    <!--                    <button type="submit" class="btn btn-primary">-->
    <!--                        <i class="fas fa-filter me-1"></i>Filter-->
    <!--                    </button>-->
    <!--                    <a href="{{ route('admin.hero-slides.index') }}" class="btn btn-outline-secondary">-->
    <!--                        <i class="fas fa-times me-1"></i>Clear-->
    <!--                    </a>-->
    <!--                </div>-->
    <!--            </div>-->
    <!--        </form>-->
    <!--    </div>-->
    <!--</div>-->

    <!-- Slides List -->
    <div class="card shadow">
        <div class="card-header py-2">
            <h6 class="mb-0 text-white">All Slides ({{ $slides->count() }})</h6>
        </div>
        <div class="card-body p-0" style="overflow-x: auto;">
            @if($slides->count() > 0)
                <div class="table-responsive">
                    <table class="table table-hover mb-0" style="border-radius: 0;">
                        <thead class="bg-light" style="border-radius: 0;">
                            <tr>
                                <th width="60">Preview</th>
                                <th>Title</th>
                                <th>Subtitle</th>
                                <th width="100">Type</th>
                                <th width="120">Page Type</th>
                                <th width="80">Sort Order</th>
                                <th width="80">Status</th>
                                <th width="120">Ad Stats</th>
                                <th width="150">Actions</th>
                            </tr>
                        </thead>
                        <tbody id="sortable-slides">
                            @foreach($slides as $index => $slide)
                                @php
                                    $previousSlide = $index > 0 ? $slides[$index - 1] : null;
                                    
                                    // Show main type header when switching between slides and ads
                                    $showMainTypeHeader = !$previousSlide || $previousSlide->is_ad !== $slide->is_ad;
                                    
                                    // Show page type header within same main type
                                    $showPageTypeHeader = !$previousSlide || 
                                        ($previousSlide->is_ad === $slide->is_ad && $previousSlide->page_type !== $slide->page_type);
                                @endphp
                                
                                @if($showMainTypeHeader)
                                    <tr class="bg-primary text-white group-header-main">
                                        <td colspan="9" class="py-3">
                                            @php
                                                $mainTypeCount = $slides->where('is_ad', $slide->is_ad)->count();
                                            @endphp
                                            <h6 class="mb-0">
                                                <i class="fas fa-{{ $slide->is_ad ? 'ad' : 'image' }} me-2"></i>
                                                {{ $slide->is_ad ? 'ADVERTISEMENTS' : 'HERO SLIDES' }}
                                                <span class="badge bg-light text-primary ms-2">{{ $mainTypeCount }} total</span>
                                            </h6>
                                        </td>
                                    </tr>
                                @endif
                                
                                @if($showPageTypeHeader)
                                    <tr class="bg-light group-header-sub">
                                        <td colspan="9" class="py-2">
                                            @php
                                                $pageTypeLabels = [
                                                    'all' => 'All Pages',
                                                    'homepage' => 'Homepage',
                                                    'services' => 'Services',
                                                    'products' => 'Products',
                                                    'wholesale_services' => 'Wholesale Services',
                                                    'wholesale_homepage' => 'Wholesale Homepage'
                                                ];
                                                $pageTypeLabel = $pageTypeLabels[$slide->page_type] ?? $slide->page_type;
                                                $pageGroupCount = $slides->where('is_ad', $slide->is_ad)->where('page_type', $slide->page_type)->count();
                                            @endphp
                                            <strong class="text-secondary">
                                                <i class="fas fa-{{ $slide->page_type === 'all' ? 'globe' : 'file' }} me-2"></i>
                                                {{ $pageTypeLabel }}
                                            </strong>
                                            <span class="badge bg-info ms-2">{{ $pageGroupCount }} {{ $slide->is_ad ? 'ad' : 'slide' }}(s)</span>
                                        </td>
                                    </tr>
                                @endif
                                
                                <tr data-id="{{ $slide->id }}" class="slide-row" data-is-ad="{{ $slide->is_ad ? '1' : '0' }}" data-page-type="{{ $slide->page_type }}">
                                    <td>
                                        @if($slide->image_path)
                                            @php
                                                $extension = strtolower(pathinfo($slide->image_path, PATHINFO_EXTENSION));
                                                $isVideo = in_array($extension, ['mp4', 'avi', 'mov', 'wmv', 'flv', 'webm', 'mkv', '3gp', 'mpg', 'mpeg']);
                                            @endphp
                                            
                                            @if($isVideo)
                                                <video class="img-thumbnail" 
                                                       style="width: 50px; height: 50px; object-fit: cover;" 
                                                       muted>
                                                    <source src="{{ asset('storage/' . $slide->image_path) }}" type="video/{{ $extension }}">
                                                </video>
                                            @else
                                                <img src="{{ asset('storage/' . $slide->image_path) }}" 
                                                     alt="{{ $slide->title }}" 
                                                     class="img-thumbnail" 
                                                     style="width: 50px; height: 50px; object-fit: cover;">
                                            @endif
                                        @else
                                            <div class="bg-light d-flex align-items-center justify-content-center" 
                                                 style="width: 50px; height: 50px;">
                                                <i class="fas fa-image text-muted"></i>
                                            </div>
                                        @endif
                                    </td>
                                    <td>
                                        <div class="fw-semibold">{{ $slide->title }}</div>
                                        @if($slide->button_text)
                                            <small class="text-muted">
                                                <i class="fas fa-link me-1"></i>{{ $slide->button_text }}
                                            </small>
                                        @endif
                                    </td>
                                    <td>
                                        <div class="text-muted" style="max-width: 200px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">
                                            {{ $slide->subtitle }}
                                        </div>
                                    </td>
                                    <td>
                                        @if($slide->is_ad)
                                            <span class="badge bg-warning">
                                                <i class="fas fa-ad me-1"></i>Ad
                                            </span>
                                            @if($slide->ad_type)
                                                <br><small class="text-muted">{{ ucfirst($slide->ad_type) }}</small>
                                            @endif
                                        @else
                                            <span class="badge bg-primary">
                                                <i class="fas fa-image me-1"></i>Slide
                                            </span>
                                        @endif
                                    </td>
                                    <td>
                                        @php
                                            $pageTypeLabels = [
                                                'all' => 'All Pages',
                                                'homepage' => 'Homepage',
                                                'services' => 'Services',
                                                'products' => 'Products',
                                                'wholesale_services' => 'Wholesale Services',
                                                'wholesale_homepage' => 'Wholesale Homepage'
                                            ];
                                            $pageTypeLabel = $pageTypeLabels[$slide->page_type] ?? $slide->page_type;
                                        @endphp
                                        <span class="badge bg-info">{{ $pageTypeLabel }}</span>
                                    </td>
                                    <td>
                                        <input type="number" 
                                               class="form-control form-control-sm sort-order-input" 
                                               value="{{ $slide->sort_order }}" 
                                               min="0" 
                                               data-id="{{ $slide->id }}"
                                               style="width: 60px;">
                                    </td>
                                    <td>
                                        @if($slide->is_active)
                                            <span class="badge bg-success">Active</span>
                                        @else
                                            <span class="badge bg-secondary">Inactive</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($slide->is_ad)
                                            <div class="small">
                                                <div><strong>Clicks:</strong> {{ $slide->ad_click_count }}</div>
                                                <div><strong>Views:</strong> {{ $slide->ad_impression_count }}</div>
                                                @if($slide->ad_impression_count > 0)
                                                    <div><strong>CTR:</strong> {{ number_format(($slide->ad_click_count / $slide->ad_impression_count) * 100, 2) }}%</div>
                                                @endif
                                            </div>
                                        @else
                                            <span class="text-muted">-</span>
                                        @endif
                                    </td>
                                    <td>
                                        <div class="btn-group btn-group-sm" role="group">
                                            <a href="{{ route('admin.hero-slides.show', $slide) }}" 
                                               class="btn btn-outline-primary" 
                                               title="View">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            <a href="{{ route('admin.hero-slides.edit', $slide) }}" 
                                               class="btn btn-outline-warning" 
                                               title="Edit">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <form action="{{ route('admin.hero-slides.destroy', $slide) }}" 
                                                  method="POST" 
                                                  class="d-inline"
                                                  id="deleteForm{{ $slide->id }}">
                                                @csrf
                                                @method('DELETE')
                                                <button type="button" 
                                                        class="btn btn-outline-danger" 
                                                        title="Delete"
                                                        onclick="modernConfirm('Are you sure you want to delete this {{ $slide->is_ad ? 'ad' : 'slide' }}? This action cannot be undone.', () => document.getElementById('deleteForm{{ $slide->id }}').submit(), { title: 'Delete {{ ucfirst($slide->is_ad ? 'Ad' : 'Slide') }}', type: 'danger', icon: 'fa-trash', confirmText: 'Delete' })">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                
                <!-- Save Sort Order Button -->
                <div class="card-footer bg-light">
                    <button type="button" class="btn btn-success" id="save-sort-order">
                        <i class="fas fa-save me-2"></i>Save Sort Order
                    </button>
                    <small class="text-muted ms-3">Drag rows to reorder or use the sort order inputs</small>
                </div>
            @else
                <div class="text-center py-5">
                    <i class="fas fa-images fa-3x text-muted mb-3"></i>
                    <h5 class="text-muted">No Hero Slides Found</h5>
                    <p class="text-muted">Create your first hero slide to get started.</p>
                    <a href="{{ route('admin.hero-slides.create') }}" class="btn btn-danger">
                        <i class="fas fa-plus me-2"></i>Add First Slide
                    </a>
                </div>
            @endif
        </div>
    </div>
</div>

@push('styles')
<style>
    .slide-row {
        cursor: move;
    }
    
    .slide-row:hover {
        background-color: #f8f9fa;
    }
    
    .sort-order-input {
        text-align: center;
    }
    
    .ui-sortable-helper {
        background-color: #fff;
        box-shadow: 0 4px 8px rgba(0,0,0,0.1);
    }
    
    .table thead th {
        border-radius: 0 !important;
    }
    
    .table thead th:first-child {
        border-top-left-radius: 0 !important;
    }
    
    .table thead th:last-child {
        border-top-right-radius: 0 !important;
    }
    
    .table {
        border-radius: 0 !important;
    }
    
    .table thead {
        border-radius: 0 !important;
    }
    
    /* Group header styles */
    .group-header-main {
        background-color: #0d6efd !important;
        border-top: 3px solid #084298;
        border-bottom: 2px solid #084298;
    }
    
    .group-header-main h6 {
        font-size: 1rem;
        font-weight: 700;
        letter-spacing: 0.5px;
    }
    
    .group-header-sub {
        background-color: #f8f9fa !important;
        border-top: 1px solid #dee2e6;
        border-bottom: 1px solid #dee2e6;
    }
    
    .group-header-sub strong {
        font-size: 0.9rem;
        font-weight: 600;
    }
    
    .group-header .badge {
        font-size: 0.75rem;
    }
    
    /* Slide rows in groups */
    .slide-row {
        border-left: 3px solid transparent;
    }
    
    .slide-row[data-is-ad="1"] {
        border-left-color: #ffc107;
    }
    
    .slide-row[data-is-ad="0"] {
        border-left-color: #0d6efd;
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

    /* Mobile refinements */
    @media (max-width: 576px) {
        /* Page title and header buttons */
        .h3, h3 { font-size: 1rem; }
        .btn { font-size: 0.8rem; padding: 0.4rem 0.6rem; border-radius: 8px; }
        .btn .fa, .btn .fas, .btn .far { font-size: 0.9em; }
        .btn-group .btn { padding: 0.35rem 0.5rem; }

        /* Breadcrumb and badges */
        .breadcrumb { font-size: 0.8rem; }
        .badge { font-size: 0.7rem; padding: 0.25rem 0.5rem; }

        /* Card paddings */
        .card-body { padding: 1rem; }
        .card-header { padding: 0.75rem 1rem; }
        .card-footer { padding: 0.75rem 1rem; }

        /* Table */
        .table { font-size: 0.85rem; }
        .table thead th { padding: 0.5rem; }
        .table tbody td { padding: 0.5rem; }
        .img-thumbnail { width: 40px !important; height: 40px !important; }

        /* Inputs */
        .form-select, .form-control { font-size: 0.85rem; padding: 0.375rem 0.5rem; }
        .sort-order-input { width: 52px !important; }
    }
</style>
@endpush

@push('scripts')
<script src="https://code.jquery.com/ui/1.13.2/jquery-ui.min.js"></script>
<script>
$(document).ready(function() {
    // Make all slides sortable
    $("#sortable-slides").sortable({
        helper: "clone",
        opacity: 0.8,
        items: ".slide-row",
        tolerance: "pointer",
        cursor: "move",
        update: function(event, ui) {
            updateSortOrderInputs();
        }
    });
    
    // Update sort order inputs when dragging
    function updateSortOrderInputs() {
        $("#sortable-slides .slide-row").each(function(index) {
            $(this).find('.sort-order-input').val(index);
        });
    }
    
    // Save sort order
    $('#save-sort-order').click(function() {
        const slides = [];
        $('#sortable-slides .slide-row').each(function() {
            const id = $(this).data('id');
            const sortOrder = $(this).find('.sort-order-input').val();
            slides.push({ id: id, sort_order: parseInt(sortOrder) });
        });
        
        $.ajax({
            url: '{{ route("admin.hero-slides.update-sort-order") }}',
            method: 'POST',
            data: {
                slides: slides,
                _token: '{{ csrf_token() }}'
            },
            success: function(response) {
                showSuccessToast('Sort order updated successfully!');
            },
            error: function(xhr) {
                showErrorToast('Failed to update sort order');
            }
        });
    });
    
    // Auto-save on sort order input change
    $('.sort-order-input').on('change', function() {
        const slides = [];
        $('#sortable-slides .slide-row').each(function() {
            const id = $(this).data('id');
            const sortOrder = $(this).find('.sort-order-input').val();
            slides.push({ id: id, sort_order: parseInt(sortOrder) });
        });
        
        $.ajax({
            url: '{{ route("admin.hero-slides.update-sort-order") }}',
            method: 'POST',
            data: {
                slides: slides,
                _token: '{{ csrf_token() }}'
            },
            success: function(response) {
                // Silent update
            },
            error: function(xhr) {
                showErrorToast('Failed to update sort order');
            }
        });
    });
});
</script>
@endpush
@endsection
