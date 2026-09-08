@extends('layouts.admin')

@section('title', 'New Ad Tracker')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="mb-0">Create Ad Tracker</h2>
        <a href="{{ route('admin.marketing.ads.index') }}" class="btn btn-outline-secondary">
            <i class="fas fa-arrow-left me-2"></i> Back
        </a>
    </div>

    <div class="card shadow-sm border-0" style="border-radius: 12px;">
        <div class="card-body p-4">
            <form action="{{ route('admin.marketing.ads.store') }}" method="POST">
                @csrf
                <div class="row g-4">
                    <div class="col-md-6">
                        <label for="ad_name" class="form-label fw-bold">Ad Name <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="ad_name" name="ad_name" required placeholder="e.g. Retargeting FB Video Ad">
                    </div>

                    <div class="col-md-6">
                        <label for="campaign_id" class="form-label fw-bold">Parent Campaign</label>
                        <select class="form-select" id="campaign_id" name="campaign_id">
                            <option value="">No Campaign</option>
                            <!-- Campaign Options -->
                        </select>
                    </div>

                    <div class="col-md-6">
                        <label for="platform" class="form-label fw-bold">Platform</label>
                        <select class="form-select" id="platform" name="platform">
                            <option value="facebook">Facebook Ads</option>
                            <option value="instagram">Instagram Ads</option>
                            <option value="tiktok">TikTok Ads</option>
                            <option value="google">Google Ads</option>
                            <option value="other">Other</option>
                        </select>
                    </div>

                    <div class="col-md-6">
                        <label for="content_type" class="form-label fw-bold">Content Type</label>
                        <select class="form-select" id="content_type" name="content_type">
                            <option value="image">Image / Poster</option>
                            <option value="video">Video</option>
                            <option value="carousel">Carousel</option>
                            <option value="text">Text Only</option>
                        </select>
                    </div>

                    <div class="col-md-6">
                        <label for="budget" class="form-label fw-bold">Daily / Lifetime Budget ($)</label>
                        <input type="number" step="0.01" class="form-control" id="budget" name="budget">
                    </div>
                    
                    <div class="col-md-6">
                        <label for="target_audience" class="form-label fw-bold">Target Audience Details</label>
                        <input type="text" class="form-control" id="target_audience" name="target_audience">
                    </div>

                    <div class="col-md-6">
                        <label for="start_date" class="form-label fw-bold">Start Date</label>
                        <input type="datetime-local" class="form-control" id="start_date" name="start_date">
                    </div>

                    <div class="col-md-6">
                        <label for="end_date" class="form-label fw-bold">End Date (Optional)</label>
                        <input type="datetime-local" class="form-control" id="end_date" name="end_date">
                    </div>

                    <div class="col-12 mt-4 text-end">
                        <button type="submit" class="btn btn-primary px-4 py-2" style="border-radius: 8px;">
                            <i class="fas fa-save me-2"></i> Save Ad Tracker
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
