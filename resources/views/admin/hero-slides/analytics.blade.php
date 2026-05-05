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
                <i class="fas fa-chart-bar me-1"></i>Ad Analytics
            </li>
        </ol>
    </nav>

    <!-- Page Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0 text-gray-800">Ad Analytics</h1>
          
        </div>
        <a href="{{ route('admin.hero-slides.index') }}" class="btn btn-outline-secondary">
            <i class="fas fa-arrow-left me-2"></i>Back to Slides
        </a>
    </div>

    <!-- Analytics Overview Cards -->
    <div class="row mb-4">
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-primary shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">Total Ads</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $analytics['total_ads'] }}</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-ad fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-success shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">Active Ads</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $analytics['active_ads'] }}</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-check-circle fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-info shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-info text-uppercase mb-1">Total Clicks</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ number_format($analytics['total_clicks']) }}</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-mouse-pointer fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-warning shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">Total Impressions</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ number_format($analytics['total_impressions']) }}</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-eye fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Charts Row -->
    <div class="row mb-4">
        <div class="col-lg-6">
            <div class="card shadow">
                <div class="card-header bg-white py-2">
                    <h6 class="mb-0">Ads by Type</h6>
                </div>
                <div class="card-body">
                    @if($analytics['ads_by_type']->count() > 0)
                        <canvas id="adTypeChart" width="400" height="200"></canvas>
                    @else
                        <p class="text-muted text-center">No ad type data available</p>
                    @endif
                </div>
            </div>
        </div>
        
        <div class="col-lg-6">
            <div class="card shadow">
                <div class="card-header bg-white py-2">
                    <h6 class="mb-0">Ads by Target Audience</h6>
                </div>
                <div class="card-body">
                    @if($analytics['ads_by_audience']->count() > 0)
                        <canvas id="audienceChart" width="400" height="200"></canvas>
                    @else
                        <p class="text-muted text-center">No audience data available</p>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Detailed Ad Performance Table -->
    <div class="card shadow">
        <div class="card-header bg-white py-2">
            <h6 class="mb-0">Ad Performance Details</h6>
        </div>
        <div class="card-body p-0">
            @if($ads->count() > 0)
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="bg-light">
                            <tr>
                                <th>Ad Title</th>
                                <th>Type</th>
                                <th>Position</th>
                                <th>Target Audience</th>
                                <th>Status</th>
                                <th>Clicks</th>
                                <th>Impressions</th>
                                <th>CTR</th>
                                <th>Budget</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($ads as $ad)
                                <tr>
                                    <td>
                                        <div class="fw-semibold">{{ $ad->title }}</div>
                                        @if($ad->subtitle)
                                            <small class="text-muted">{{ Str::limit($ad->subtitle, 50) }}</small>
                                        @endif
                                    </td>
                                    <td>
                                        <span class="badge bg-warning">{{ ucfirst($ad->ad_type) }}</span>
                                    </td>
                                    <td>
                                        @if($ad->ad_position)
                                            <span class="badge bg-info">{{ ucfirst($ad->ad_position) }}</span>
                                        @else
                                            <span class="text-muted">-</span>
                                        @endif
                                    </td>
                                    <td>
                                        <span class="badge bg-secondary">{{ ucfirst(str_replace('_', ' ', $ad->ad_target_audience)) }}</span>
                                    </td>
                                    <td>
                                        @if($ad->isCurrentlyActive())
                                            <span class="badge bg-success">Active</span>
                                        @else
                                            <span class="badge bg-secondary">Inactive</span>
                                        @endif
                                    </td>
                                    <td>
                                        <span class="fw-bold text-primary">{{ number_format($ad->ad_click_count) }}</span>
                                    </td>
                                    <td>
                                        <span class="fw-bold text-info">{{ number_format($ad->ad_impression_count) }}</span>
                                    </td>
                                    <td>
                                        @if($ad->ad_impression_count > 0)
                                            <span class="fw-bold text-success">
                                                {{ number_format(($ad->ad_click_count / $ad->ad_impression_count) * 100, 2) }}%
                                            </span>
                                        @else
                                            <span class="text-muted">0%</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($ad->ad_budget)
                                            <span class="fw-bold">{{ number_format($ad->ad_budget, 2) }} TZS</span>
                                        @else
                                            <span class="text-muted">-</span>
                                        @endif
                                    </td>
                                    <td>
                                        <div class="btn-group btn-group-sm" role="group">
                                            <a href="{{ route('admin.hero-slides.edit', $ad) }}" 
                                               class="btn btn-outline-warning" 
                                               title="Edit">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <a href="{{ route('admin.hero-slides.show', $ad) }}" 
                                               class="btn btn-outline-primary" 
                                               title="View">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div class="text-center py-5">
                    <i class="fas fa-chart-bar fa-3x text-muted mb-3"></i>
                    <h5 class="text-muted">No Ads Found</h5>
                    <p class="text-muted">Create your first advertisement to see analytics.</p>
                    <a href="{{ route('admin.hero-slides.create', ['type' => 'ad']) }}" class="btn btn-warning">
                        <i class="fas fa-plus me-2"></i>Create First Ad
                    </a>
                </div>
            @endif
        </div>
    </div>
</div>

@push('styles')
<style>
    .border-left-primary {
        border-left: 0.25rem solid #4e73df !important;
    }
    .border-left-success {
        border-left: 0.25rem solid #1cc88a !important;
    }
    .border-left-info {
        border-left: 0.25rem solid #36b9cc !important;
    }
    .border-left-warning {
        border-left: 0.25rem solid #f6c23e !important;
    }
    
    .text-xs {
        font-size: 0.7rem;
    }
    
    .font-weight-bold {
        font-weight: 700 !important;
    }
    
    .text-uppercase {
        text-transform: uppercase !important;
    }
</style>
@endpush

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
$(document).ready(function() {
    // Ad Type Chart
    @if($analytics['ads_by_type']->count() > 0)
    const adTypeCtx = document.getElementById('adTypeChart').getContext('2d');
    new Chart(adTypeCtx, {
        type: 'doughnut',
        data: {
            labels: {!! json_encode($analytics['ads_by_type']->keys()->map(function($key) { return ucfirst($key); })->toArray()) !!},
            datasets: [{
                data: {!! json_encode($analytics['ads_by_type']->values()->toArray()) !!},
                backgroundColor: [
                    '#FF6384',
                    '#36A2EB',
                    '#FFCE56',
                    '#4BC0C0'
                ]
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false
        }
    });
    @endif

    // Audience Chart
    @if($analytics['ads_by_audience']->count() > 0)
    const audienceCtx = document.getElementById('audienceChart').getContext('2d');
    new Chart(audienceCtx, {
        type: 'bar',
        data: {
            labels: {!! json_encode($analytics['ads_by_audience']->keys()->map(function($key) { return ucfirst(str_replace('_', ' ', $key)); })->toArray()) !!},
            datasets: [{
                label: 'Number of Ads',
                data: {!! json_encode($analytics['ads_by_audience']->values()->toArray()) !!},
                backgroundColor: '#36A2EB'
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                y: {
                    beginAtZero: true
                }
            }
        }
    });
    @endif
});
</script>
@endpush
@endsection







