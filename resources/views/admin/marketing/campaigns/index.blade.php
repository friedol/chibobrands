@extends('layouts.admin')

@section('title', 'Campaigns')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="mb-0">Campaigns</h2>
        <a href="{{ route('admin.marketing.campaigns.create') }}" class="btn btn-primary">
            <i class="fas fa-plus me-2"></i> New Campaign
        </a>
    </div>
    <div class="card shadow-sm">
        <div class="card-body">
            <p class="text-muted text-center py-5">No campaigns running.</p>
        </div>
    </div>
</div>
@endsection
