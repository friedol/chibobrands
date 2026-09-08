@extends('layouts.admin')

@section('title', 'New Campaign')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="mb-0">Launch New Campaign</h2>
        <a href="{{ route('admin.marketing.campaigns.index') }}" class="btn btn-outline-secondary">
            <i class="fas fa-arrow-left me-2"></i> Back
        </a>
    </div>

    <div class="card shadow-sm border-0" style="border-radius: 12px;">
        <div class="card-body p-4">
            <form action="{{ route('admin.marketing.campaigns.store') }}" method="POST">
                @csrf
                <div class="row g-4">
                    <div class="col-md-8">
                        <label for="title" class="form-label fw-bold">Campaign Title <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="title" name="title" required placeholder="e.g. Summer Blowout Sale">
                    </div>

                    <div class="col-md-4">
                        <label for="stage" class="form-label fw-bold">Funnel Stage</label>
                        <select class="form-select" id="stage" name="stage">
                            <option value="awareness">Awareness</option>
                            <option value="consideration">Consideration</option>
                            <option value="conversion">Conversion</option>
                            <option value="retention">Retention</option>
                        </select>
                    </div>

                    <div class="col-md-6">
                        <label for="budget" class="form-label fw-bold">Allocated Budget ($)</label>
                        <input type="number" step="0.01" class="form-control" id="budget" name="budget" placeholder="0.00">
                    </div>

                    <div class="col-md-6">
                        <label for="target_market" class="form-label fw-bold">Target Market</label>
                        <input type="text" class="form-control" id="target_market" name="target_market" placeholder="Demographic / Location">
                    </div>

                    <div class="col-md-6">
                        <label for="start_date" class="form-label fw-bold">Start Date</label>
                        <input type="date" class="form-control" id="start_date" name="start_date">
                    </div>

                    <div class="col-md-6">
                        <label for="end_date" class="form-label fw-bold">End Date</label>
                        <input type="date" class="form-control" id="end_date" name="end_date">
                    </div>
                    
                    <div class="col-md-6">
                        <label for="expected_reach" class="form-label fw-bold">Expected Reach (People)</label>
                        <input type="number" class="form-control" id="expected_reach" name="expected_reach">
                    </div>

                    <div class="col-md-6">
                        <label for="status" class="form-label fw-bold">Initial Status</label>
                        <select class="form-select" id="status" name="status">
                            <option value="draft">Draft</option>
                            <option value="scheduled">Scheduled</option>
                            <option value="active">Active</option>
                        </select>
                    </div>

                    <div class="col-12">
                        <label for="description" class="form-label fw-bold">Campaign Description</label>
                        <textarea class="form-control" id="description" name="description" rows="3"></textarea>
                    </div>

                    <div class="col-12 mt-4 text-end">
                        <button type="submit" class="btn btn-primary px-4 py-2" style="border-radius: 8px;">
                            <i class="fas fa-rocket me-2"></i> Launch Campaign
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
