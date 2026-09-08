@extends('layouts.admin')

@section('title', 'New Theme Event')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="mb-0">Create Theme Event</h2>
        <a href="{{ route('admin.marketing.theme-events.index') }}" class="btn btn-outline-secondary">
            <i class="fas fa-arrow-left me-2"></i> Back
        </a>
    </div>

    <div class="card shadow-sm border-0" style="border-radius: 12px;">
        <div class="card-body p-4">
            <form action="{{ route('admin.marketing.theme-events.store') }}" method="POST">
                @csrf
                <div class="row g-4">
                    <div class="col-md-12">
                        <label for="title" class="form-label fw-bold">Event Title <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="title" name="title" required placeholder="e.g. Back to School Promo">
                    </div>

                    <div class="col-md-6">
                        <label for="product_id" class="form-label fw-bold">Linked Product</label>
                        <select class="form-select" id="product_id" name="product_id">
                            <option value="">None (General Event)</option>
                            <!-- Options -->
                        </select>
                    </div>

                    <div class="col-md-6">
                        <label for="target_audience" class="form-label fw-bold">Target Audience</label>
                        <input type="text" class="form-control" id="target_audience" name="target_audience">
                    </div>

                    <div class="col-md-6">
                        <label for="start_date" class="form-label fw-bold">Start Date & Time</label>
                        <input type="datetime-local" class="form-control" id="start_date" name="start_date">
                    </div>

                    <div class="col-md-6">
                        <label for="end_date" class="form-label fw-bold">End Date & Time</label>
                        <input type="datetime-local" class="form-control" id="end_date" name="end_date">
                    </div>

                    <div class="col-12">
                        <label for="description" class="form-label fw-bold">Description</label>
                        <textarea class="form-control" id="description" name="description" rows="3"></textarea>
                    </div>

                    <div class="col-12">
                        <label for="objective" class="form-label fw-bold">Objective</label>
                        <textarea class="form-control" id="objective" name="objective" rows="2" placeholder="What is the goal of this event?"></textarea>
                    </div>

                    <div class="col-12 mt-4 text-end">
                        <button type="submit" class="btn btn-primary px-4 py-2" style="border-radius: 8px;">
                            <i class="fas fa-save me-2"></i> Save Event
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
