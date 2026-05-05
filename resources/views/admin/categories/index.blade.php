@extends('layouts.admin')

@section('title', 'Categories Management')

@section('content')
<div class="container-fluid">
    <!-- Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <div>
                    <h2 class="mb-1">Categories</h2>
                    <p class="text-muted">Manage product categories</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Categories Table -->
    <div class="card">
        <div class="card-header">
            <h5 class="mb-0 fw-bold"><i class="fas fa-tags me-2"></i>All Categories</h5>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead>
                        <tr>
                            <th class="py-3 px-4">Image</th>
                            <th class="py-3 px-4">Name</th>
                            <th class="py-3 px-4">Description</th>
                            <th class="py-3 px-4">Products</th>
                            <th class="py-3 px-4">Status</th>
                            <th class="py-3 px-4">Created</th>
                            <th class="py-3 px-4">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($categories as $category)
                            <tr>
                                <td class="py-3 px-4">
                                    @if($category->image_path)
                                        <img src="{{ asset('storage/' . $category->image_path) }}" 
                                             alt="{{ $category->name }}" 
                                             class="rounded"
                                             style="width: 60px; height: 60px; object-fit: cover;">
                                    @else
                                        <div class="rounded d-flex align-items-center justify-content-center bg-light"
                                             style="width: 60px; height: 60px;">
                                            <i class="fas fa-image text-muted"></i>
                                        </div>
                                    @endif
                                </td>
                                <td class="py-3 px-4">
                                    <div class="fw-bold">{{ $category->name }}</div>
                                    <small class="text-muted">Slug: {{ $category->slug }}</small>
                                </td>
                                <td class="py-3 px-4">
                                    {{ Str::limit($category->description ?? 'No description', 100) }}
                                </td>
                                <td class="py-3 px-4">
                                    <span class="badge bg-info">{{ $category->products_count ?? 0 }}</span>
                                </td>
                                <td class="py-3 px-4">
                                    @if($category->is_active)
                                        <span class="badge bg-success">Active</span>
                                    @else
                                        <span class="badge bg-danger">Inactive</span>
                                    @endif
                                </td>
                                <td class="py-3 px-4">
                                    <div class="small">{{ $category->created_at->format('M d, Y') }}</div>
                                    <div class="text-muted" style="font-size: 0.75rem;">{{ $category->created_at->diffForHumans() }}</div>
                                </td>
                                <td class="py-3 px-4">
                                    <div class="d-flex gap-2">
                                        <a href="{{ route('admin.categories.show', $category) }}" 
                                           class="btn btn-sm btn-outline-primary" 
                                           title="View">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <a href="{{ route('admin.categories.edit', $category) }}" 
                                           class="btn btn-sm btn-outline-secondary" 
                                           title="Edit">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <form action="{{ route('admin.categories.destroy', $category) }}" 
                                              method="POST" 
                                              style="display: inline-block;"
                                              onsubmit="return confirm('Are you sure you want to delete {{ $category->name }}?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-outline-danger" title="Delete">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center py-5">
                                    <div class="py-4">
                                        <i class="fas fa-folder-open text-muted" style="font-size: 3rem;"></i>
                                        <p class="mt-3 text-muted">No categories found</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            
            @if($categories->hasPages())
                <div class="d-flex justify-content-between align-items-center mt-3 pt-3 border-top px-3 py-2">
                    <div class="text-muted small fw-medium">
                        <i class="fas fa-info-circle me-1"></i>
                        Showing {{ $categories->firstItem() }} to {{ $categories->lastItem() }} of {{ $categories->total() }} categories
                    </div>
                    <div class="pagination-wrapper">
                        {{ $categories->appends(request()->query())->links('pagination::bootstrap-4') }}
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
