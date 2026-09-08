@extends('layouts.admin')

@section('title', 'Marketing Dashboard')

@section('content')
<div class="container-fluid">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="mb-0 fw-bold">Marketing Dashboard</h2>
        <div>
            <a href="{{ route('admin.marketing.campaigns.create') }}" class="btn btn-primary rounded-pill px-4">
                <i class="fas fa-plus me-2"></i> New Campaign
            </a>
        </div>
    </div>

    <!-- Stats Cards (Admin Dashboard Style) -->
    <div class="row g-2 g-md-3 mb-4">
        <!-- Active Campaigns -->
        <div class="col-6 col-md-4 col-lg-2">
            <a href="{{ route('admin.marketing.campaigns.index') }}" class="text-decoration-none">
                <div class="card shadow-sm h-100 border-0 border-start border-4 border-primary hover-lift">
                    <div class="card-body p-3">
                        <div class="d-flex align-items-center mb-2">
                            <div class="icon-circle bg-primary bg-opacity-10 text-primary me-2" style="width:32px;height:32px;border-radius:50%;display:flex;align-items:center;justify-content:center;">
                                <i class="fas fa-flag"></i>
                            </div>
                            <span class="text-uppercase x-small fw-bold text-muted" style="font-size:0.7rem;">Active Campaigns</span>
                        </div>
                        <div class="h3 mb-0 fw-bold text-dark">{{ $stats['active_campaigns'] ?? 0 }}</div>
                        <div class="x-small text-muted mt-2" style="font-size:0.75rem;">Currently running</div>
                    </div>
                </div>
            </a>
        </div>
        
        <!-- Running Ads -->
        <div class="col-6 col-md-4 col-lg-2">
            <a href="{{ route('admin.marketing.ads.index') }}" class="text-decoration-none">
                <div class="card shadow-sm h-100 border-0 border-start border-4 border-success hover-lift">
                    <div class="card-body p-3">
                        <div class="d-flex align-items-center mb-2">
                            <div class="icon-circle bg-success bg-opacity-10 text-success me-2" style="width:32px;height:32px;border-radius:50%;display:flex;align-items:center;justify-content:center;">
                                <i class="fas fa-ad"></i>
                            </div>
                            <span class="text-uppercase x-small fw-bold text-muted" style="font-size:0.7rem;">Running Ads</span>
                        </div>
                        <div class="h3 mb-0 fw-bold text-success">{{ $stats['running_ads'] ?? 0 }}</div>
                        <div class="x-small text-muted mt-2" style="font-size:0.75rem;">Active ad trackers</div>
                    </div>
                </div>
            </a>
        </div>

        <!-- Total Reach -->
        <div class="col-6 col-md-4 col-lg-2">
            <div class="card shadow-sm h-100 border-0 border-start border-4 border-info hover-lift">
                <div class="card-body p-3">
                    <div class="d-flex align-items-center mb-2">
                        <div class="icon-circle bg-info bg-opacity-10 text-info me-2" style="width:32px;height:32px;border-radius:50%;display:flex;align-items:center;justify-content:center;">
                            <i class="fas fa-users"></i>
                        </div>
                        <span class="text-uppercase x-small fw-bold text-muted" style="font-size:0.7rem;">Total Reach</span>
                    </div>
                    <div class="h3 mb-0 fw-bold text-info">{{ number_format($stats['total_reach'] ?? 0) }}</div>
                    <div class="x-small text-muted mt-2" style="font-size:0.75rem;">Unique impressions</div>
                </div>
            </div>
        </div>

        <!-- Total Conversions -->
        <div class="col-6 col-md-4 col-lg-2">
            <div class="card shadow-sm h-100 border-0 border-start border-4 border-warning hover-lift">
                <div class="card-body p-3">
                    <div class="d-flex align-items-center mb-2">
                        <div class="icon-circle bg-warning bg-opacity-10 text-warning me-2" style="width:32px;height:32px;border-radius:50%;display:flex;align-items:center;justify-content:center;">
                            <i class="fas fa-bullseye"></i>
                        </div>
                        <span class="text-uppercase x-small fw-bold text-muted" style="font-size:0.7rem;">Conversions</span>
                    </div>
                    <div class="h3 mb-0 fw-bold text-warning">{{ number_format($stats['total_conversions'] ?? 0) }}</div>
                    <div class="x-small text-muted mt-2" style="font-size:0.75rem;">Leads generated</div>
                </div>
            </div>
        </div>

        <!-- Marketing Spend -->
        <div class="col-6 col-md-4 col-lg-2">
            <div class="card shadow-sm h-100 border-0 border-start border-4 border-danger hover-lift">
                <div class="card-body p-3">
                    <div class="d-flex align-items-center mb-2">
                        <div class="icon-circle bg-danger bg-opacity-10 text-danger me-2" style="width:32px;height:32px;border-radius:50%;display:flex;align-items:center;justify-content:center;">
                            <i class="fas fa-money-bill-wave"></i>
                        </div>
                        <span class="text-uppercase x-small fw-bold text-muted" style="font-size:0.7rem;">Total Spend</span>
                    </div>
                    <div class="h4 mb-0 fw-bold text-danger">{{ number_format($stats['marketing_spend'] ?? 0) }}</div>
                    <div class="x-small text-muted mt-2" style="font-size:0.75rem;">TZS allocated</div>
                </div>
            </div>
        </div>

        <!-- CPC -->
        <div class="col-6 col-md-4 col-lg-2">
            <div class="card shadow-sm h-100 border-0 border-start border-4 hover-lift" style="border-left-color: #6f42c1!important;">
                <div class="card-body p-3">
                    <div class="d-flex align-items-center mb-2">
                        <div class="icon-circle me-2" style="background-color: rgba(111, 66, 193, 0.1); color: #6f42c1; width:32px;height:32px;border-radius:50%;display:flex;align-items:center;justify-content:center;">
                            <i class="fas fa-hand-holding-usd"></i>
                        </div>
                        <span class="text-uppercase x-small fw-bold text-muted" style="font-size:0.7rem;">Cost / Conv.</span>
                    </div>
                    <div class="h4 mb-0 fw-bold" style="color: #6f42c1;">TZS {{ $stats['roi_percentage'] ?? 0 }}</div>
                    <div class="x-small text-muted mt-2" style="font-size:0.75rem;">Avg cost per lead</div>
                </div>
            </div>
        </div>
    </div>

    <!-- MAIN GRAPHS ROW -->
    <div class="row g-3 mb-4">
        <!-- Line/Bar Chart Toggle -->
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white py-3 border-0 d-flex justify-content-between align-items-center">
                    <h6 class="m-0 fw-bold text-dark"><i class="fas fa-chart-area me-2 text-primary"></i>Campaign Reach vs Conversions</h6>
                    <div class="btn-group btn-group-sm shadow-sm" role="group">
                        <button type="button" class="btn btn-outline-dark active" id="btnMarketingLine" onclick="toggleMarketingChart('line')">
                            <i class="fas fa-chart-line"></i> Line
                        </button>
                        <button type="button" class="btn btn-outline-dark" id="btnMarketingBar" onclick="toggleMarketingChart('bar')">
                            <i class="fas fa-chart-bar"></i> Bar
                        </button>
                    </div>
                </div>
                <div class="card-body">
                    <div style="height: 300px;">
                        <canvas id="marketingTrendChart"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <!-- Pie Chart -->
        <div class="col-lg-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white py-3 border-0">
                    <h6 class="m-0 fw-bold text-dark"><i class="fas fa-chart-pie me-2 text-warning"></i>Ad Platform Distribution</h6>
                </div>
                <div class="card-body d-flex justify-content-center align-items-center">
                    <div style="height: 250px; width: 100%;">
                        <canvas id="platformPieChart"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Upcoming Events Table Row -->
    <div class="row g-3">
        <div class="col-md-8">
            <div class="card border-0 shadow-sm mb-4 h-100">
                <div class="card-header bg-white py-3 border-0 d-flex justify-content-between align-items-center">
                    <h6 class="m-0 fw-bold text-dark"><i class="fas fa-calendar-alt me-2 text-info"></i>Upcoming Marketing Events</h6>
                    <a href="{{ route('admin.marketing.calendar.index') }}" class="btn btn-sm btn-link text-primary p-0 text-decoration-none">View Full Calendar</a>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="bg-light text-muted x-small text-uppercase">
                                <tr>
                                    <th class="ps-3 py-2">Event Title</th>
                                    <th>Start Date</th>
                                    <th>Platform/Type</th>
                                    <th class="text-center pe-3">Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($upcomingEvents as $event)
                                <tr>
                                    <td class="ps-3 fw-bold small">{{ $event->title }}</td>
                                    <td class="small">{{ $event->start_datetime->format('d M, Y h:i A') }}</td>
                                    <td class="small">{{ $event->platform ?? 'General' }}</td>
                                    <td class="text-center pe-3">
                                        <span class="badge rounded-pill bg-info text-dark x-small">{{ ucfirst($event->status) }}</span>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="4" class="text-center py-4 text-muted">
                                        <i class="fas fa-calendar-times mb-2 opacity-25" style="font-size:2rem;"></i>
                                        <p class="small mb-0">No upcoming events found.</p>
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Quick Actions Sidebar -->
        <div class="col-md-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white py-3 border-0">
                    <h6 class="m-0 fw-bold text-dark"><i class="fas fa-bolt me-2 text-warning"></i>Quick Actions</h6>
                </div>
                <div class="card-body">
                    <div class="d-grid gap-3">
                        <a href="{{ route('admin.marketing.product-penetration.create') }}" class="btn btn-light border text-start p-3 hover-lift d-flex align-items-center">
                            <div class="icon-circle bg-primary bg-opacity-10 text-primary me-3" style="width:40px;height:40px;border-radius:50%;display:flex;align-items:center;justify-content:center;">
                                <i class="fas fa-bullseye"></i>
                            </div>
                            <div>
                                <h6 class="mb-0 fw-bold text-dark">Product Penetration</h6>
                                <small class="text-muted" style="font-size:0.7rem;">Plan market reach strategy</small>
                            </div>
                        </a>
                        
                        <a href="{{ route('admin.marketing.theme-events.create') }}" class="btn btn-light border text-start p-3 hover-lift d-flex align-items-center">
                            <div class="icon-circle bg-success bg-opacity-10 text-success me-3" style="width:40px;height:40px;border-radius:50%;display:flex;align-items:center;justify-content:center;">
                                <i class="fas fa-calendar-star"></i>
                            </div>
                            <div>
                                <h6 class="mb-0 fw-bold text-dark">New Theme Event</h6>
                                <small class="text-muted" style="font-size:0.7rem;">Schedule a themed promo</small>
                            </div>
                        </a>

                        <a href="{{ route('admin.marketing.reports.index') }}" class="btn btn-light border text-start p-3 hover-lift d-flex align-items-center">
                            <div class="icon-circle bg-info bg-opacity-10 text-info me-3" style="width:40px;height:40px;border-radius:50%;display:flex;align-items:center;justify-content:center;">
                                <i class="fas fa-file-contract"></i>
                            </div>
                            <div>
                                <h6 class="mb-0 fw-bold text-dark">Generate Reports</h6>
                                <small class="text-muted" style="font-size:0.7rem;">Analyze campaign performance</small>
                            </div>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    .hover-lift {
        transition: transform 0.2s ease, box-shadow 0.2s ease;
    }
    .hover-lift:hover {
        transform: translateY(-3px);
        box-shadow: 0 .5rem 1rem rgba(0,0,0,.15)!important;
    }
</style>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    let marketingChartInstance;

    document.addEventListener('DOMContentLoaded', function() {
        // Data from Controller
        const chartData = {!! json_encode($chartData ?? []) !!};
        
        // 1. Initialize Trend Chart (Line by default)
        initMarketingChart('line', chartData);

        // 2. Initialize Platform Distribution Pie Chart
        initPlatformPieChart(chartData);
    });

    function initMarketingChart(type, data) {
        const ctx = document.getElementById('marketingTrendChart');
        if (!ctx) return;
        
        if (marketingChartInstance) {
            marketingChartInstance.destroy();
        }

        marketingChartInstance = new Chart(ctx, {
            type: type,
            data: {
                labels: data.months,
                datasets: [
                    {
                        label: 'Campaign Reach',
                        data: data.campaign_reach,
                        backgroundColor: type === 'bar' ? 'rgba(54, 162, 235, 0.7)' : 'rgba(54, 162, 235, 0.2)',
                        borderColor: 'rgba(54, 162, 235, 1)',
                        borderWidth: 2,
                        tension: 0.4,
                        fill: type === 'line',
                        yAxisID: 'y'
                    },
                    {
                        label: 'Conversions',
                        data: data.conversions,
                        backgroundColor: type === 'bar' ? 'rgba(255, 193, 7, 0.7)' : 'rgba(255, 193, 7, 0.2)',
                        borderColor: 'rgba(255, 193, 7, 1)',
                        borderWidth: 2,
                        tension: 0.4,
                        fill: type === 'line',
                        yAxisID: 'y1'
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                interaction: {
                    mode: 'index',
                    intersect: false,
                },
                plugins: {
                    legend: {
                        position: 'top',
                    }
                },
                scales: {
                    y: {
                        type: 'linear',
                        display: true,
                        position: 'left',
                        title: { display: true, text: 'Reach (People)' }
                    },
                    y1: {
                        type: 'linear',
                        display: true,
                        position: 'right',
                        grid: { drawOnChartArea: false },
                        title: { display: true, text: 'Conversions' }
                    }
                }
            }
        });
    }

    function toggleMarketingChart(type) {
        // Toggle active button states
        document.getElementById('btnMarketingLine').classList.toggle('active', type === 'line');
        document.getElementById('btnMarketingBar').classList.toggle('active', type === 'bar');
        
        // Re-init chart
        const chartData = {!! json_encode($chartData ?? []) !!};
        initMarketingChart(type, chartData);
    }

    function initPlatformPieChart(data) {
        const ctx = document.getElementById('platformPieChart');
        if (!ctx) return;

        new Chart(ctx, {
            type: 'doughnut',
            data: {
                labels: data.platforms,
                datasets: [{
                    data: data.platform_shares,
                    backgroundColor: [
                        '#1877F2', // Facebook
                        '#E1306C', // Instagram
                        '#EA4335', // Google
                        '#000000'  // TikTok
                    ],
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'right',
                        labels: {
                            boxWidth: 12,
                            padding: 15
                        }
                    }
                },
                cutout: '70%'
            }
        });
    }
</script>
@endpush
