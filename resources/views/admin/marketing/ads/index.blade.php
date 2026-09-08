@extends('layouts.admin')

@section('title', 'Ads Tracking')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="mb-0">Ads Tracking</h2>
        <a href="{{ route('admin.marketing.ads.create') }}" class="btn btn-primary">
            <i class="fas fa-plus me-2"></i> New Ad Tracker
        </a>
    </div>
    <div class="card shadow-sm">
        <div class="card-body">
            <p class="text-muted text-center py-5">No ad tracking data available.</p>
        </div>
    </div>
</div>
@endsection
