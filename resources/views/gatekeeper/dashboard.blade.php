@extends('layouts.admin')

@section('title', 'Gatekeeper Dashboard')

@section('content')
<div class="container-fluid p-3">
    <!-- Welcome Header & Filters -->
    <div class="d-flex flex-column flex-md-row align-items-md-center justify-content-between mb-4 gap-3">
        <!-- Left: Title -->
        <div>
            <h2 class="fw-bold mb-0 text-dark">
                <span data-i18n="welcome">Welcome</span>, {{ Auth::user()->name ?? 'Gatekeeper' }}
            </h2>
            <p class="text-muted small mb-0" data-i18n="dashboard_subtitle">Monitor and record facility product movements</p>
        </div>
        
        <!-- Right: Settings Toolbar -->
        <div class="d-flex align-items-center gap-2">
            <!-- Language -->
            <button id="langToggle" class="btn btn-white border shadow-sm btn-sm rounded-pill px-3 fs-5" title="Switch Language" style="height: 38px; min-width: 50px;">
                    <span id="langLabel">🇹🇿</span>
            </button>

            <!-- Period Filter -->
            <form method="GET" action="{{ route('gatekeeper.dashboard') }}" id="periodForm" class="m-0 d-flex align-items-center gap-2">
                <select name="period" id="periodSelect" class="form-select form-select-sm rounded-pill px-3 border-0 shadow-sm text-uppercase fw-bold x-small" style="height: 38px; min-width: 120px; cursor: pointer; background-color: #fff;">
                    <option value="today" {{ ($period ?? '') == 'today' ? 'selected' : '' }} data-i18n="period_today">Today</option>
                    <option value="yesterday" {{ ($period ?? '') == 'yesterday' ? 'selected' : '' }} data-i18n="period_yesterday">Yesterday</option>
                    <option value="week" {{ ($period ?? '') == 'week' ? 'selected' : '' }} data-i18n="period_week">Week</option>
                    <option value="month" {{ ($period ?? '') == 'month' || !isset($period) ? 'selected' : '' }} data-i18n="period_month">Month</option>
                    <option value="6_months" {{ ($period ?? '') == '6_months' ? 'selected' : '' }} data-i18n="period_6months">Last 6 Months</option>
                    <option value="year" {{ ($period ?? '') == 'year' ? 'selected' : '' }} data-i18n="period_year">This Year</option>
                    <option value="2_years" {{ ($period ?? '') == '2_years' ? 'selected' : '' }} data-i18n="period_2years">Last 2 Years</option>
                    <option value="custom" {{ ($period ?? '') == 'custom' ? 'selected' : '' }} data-i18n="period_custom">Custom Range</option>
                    <option value="all" {{ ($period ?? '') == 'all' ? 'selected' : '' }} data-i18n="period_all">All</option>
                </select>

                <div id="customDateRange" class="d-flex align-items-center gap-1 {{ ($period ?? '') == 'custom' ? '' : 'd-none' }}">
                    <input type="date" name="start_date" class="form-control form-control-sm rounded-pill border-0 shadow-sm x-small" value="{{ request('start_date') }}" style="height: 38px; width: 130px;">
                    <span class="x-small text-muted">to</span>
                    <input type="date" name="end_date" class="form-control form-control-sm rounded-pill border-0 shadow-sm x-small" value="{{ request('end_date') }}" style="height: 38px; width: 130px;">
                    <button type="submit" class="btn btn-primary btn-sm rounded-circle shadow-sm" style="width: 38px; height: 38px;"><i class="fas fa-check"></i></button>
                </div>
            </form>

                <!-- Refresh -->
            <button class="btn btn-white border shadow-sm rounded-circle" style="width: 38px; height: 38px;" onclick="window.location.reload()" title="Refresh">
                <i class="fas fa-sync-alt text-muted small"></i>
            </button>
        </div>
    </div>

@push('styles')
<style>
    .transition-all { transition: all 0.3s ease; }
    .icon-circle {
        width: 32px;
        height: 32px;
        border-radius: 8px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 0.8rem;
    }
    .card-metric .h2 { font-size: 1.6rem; margin-bottom: 0; }
    .x-small { font-size: 0.75rem; }
    .hover-lift { transition: transform 0.2s ease-in-out, box-shadow 0.2s ease; }
    .hover-lift:hover { transform: translateY(-5px); box-shadow: 0 .5rem 1rem rgba(0,0,0,.15)!important; }
    
    @media (max-width: 768px) {
        .h3 { font-size: 1.1rem !important; }
        .card-body { padding: 0.75rem !important; }
        .icon-circle { width: 28px; height: 28px; font-size: 11px; }
        .x-small { font-size: 9px; }
    }
</style>
@endpush

    <!-- Quick Stats Metric Cards -->
    <div class="row g-2 g-md-3 mb-4">
        <!-- Total Movements -->
        <div class="col-6 col-lg-3">
            <div class="card shadow-sm h-100 border-0 border-start border-4 border-primary hover-lift">
                <div class="card-body p-3">
                    <div class="d-flex align-items-center mb-2">
                        <div class="icon-circle bg-primary bg-opacity-10 text-primary me-2">
                            <i class="fas fa-cubes"></i>
                        </div>
                        <span class="text-uppercase x-small fw-bold text-muted" data-i18n="metric_total">Total movements</span>
                    </div>
                    <div class="h3 mb-0 fw-bold text-dark">{{ number_format($stats['total_movements']) }}</div>
                    <div class="mt-2 x-small text-muted">
                        <i class="fas fa-history me-1"></i> Recorded movements
                    </div>
                </div>
            </div>
        </div>

        <!-- Including (My Entries) -->
        <div class="col-6 col-lg-3">
            <div class="card shadow-sm h-100 border-0 border-start border-4 border-info hover-lift">
                <div class="card-body p-3">
                    <div class="d-flex align-items-center mb-2">
                        <div class="icon-circle bg-info bg-opacity-10 text-info me-2">
                            <i class="fas fa-user-tag text-info"></i>
                        </div>
                        <span class="text-uppercase x-small fw-bold text-muted" data-i18n="metric_my_entries">My Entries</span>
                    </div>
                    <div class="h3 mb-0 fw-bold text-dark">{{ number_format($stats['my_entries_count']) }}</div>
                    <div class="mt-2 x-small text-info">
                        <i class="fas fa-check-circle me-1"></i> Added by me
                    </div>
                </div>
            </div>
        </div>

        <!-- Incoming -->
        <div class="col-6 col-lg-3">
            <div class="card shadow-sm h-100 border-0 border-start border-4 border-success hover-lift">
                <div class="card-body p-3">
                    <div class="d-flex align-items-center mb-2">
                        <div class="icon-circle bg-success bg-opacity-10 text-success me-2">
                            <i class="fas fa-arrow-down"></i>
                        </div>
                        <span class="text-uppercase x-small fw-bold text-muted" data-i18n="metric_in">Incoming</span>
                    </div>
                    <div class="h3 mb-0 fw-bold text-success">{{ number_format($stats['total_in']) }}</div>
                    <a href="{{ route('gatekeeper.movements.create', ['type' => 'in']) }}" class="btn btn-sm btn-success w-100 rounded-pill fw-bold x-small shadow-sm mt-2">
                        <i class="fas fa-plus me-1"></i> <span data-i18n="btn_in">RECORD IN</span>
                    </a>
                </div>
            </div>
        </div>

        <!-- Outgoing -->
        <div class="col-6 col-lg-3">
            <div class="card shadow-sm h-100 border-0 border-start border-4 border-warning hover-lift">
                <div class="card-body p-3">
                    <div class="d-flex align-items-center mb-2">
                        <div class="icon-circle bg-warning bg-opacity-10 text-warning me-2">
                            <i class="fas fa-arrow-up"></i>
                        </div>
                        <span class="text-uppercase x-small fw-bold text-muted" data-i18n="metric_out">Outgoing</span>
                    </div>
                    <div class="h3 mb-0 fw-bold text-warning">{{ number_format($stats['total_out']) }}</div>
                    <a href="{{ route('gatekeeper.deliver') }}" class="btn btn-sm btn-warning text-dark w-100 rounded-pill fw-bold x-small shadow-sm mt-2">
                        <i class="fas fa-box-open me-1"></i> <span>MARK DELIVERY</span>
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Analytics Charts Row -->
    <div class="row g-3 mb-4">
        <!-- Trend Chart -->
        <div class="col-12 col-md-8">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white py-3 border-0 d-flex justify-content-between align-items-center">
                    <h6 class="m-0 fw-bold text-dark"><i class="fas fa-chart-line me-2"></i><span data-i18n="chart_trends">Movement Trends</span></h6>
                    <!-- Chart Type Toggle -->
                    <div class="bg-light rounded-pill p-1 d-inline-flex">
                        <button type="button" class="btn btn-sm btn-white rounded-pill shadow-sm px-3 fw-bold text-primary active-chart-type transition-all" id="btnChartLine" onclick="toggleChartType('line')">
                            <i class="fas fa-wave-square me-1"></i> Line
                        </button>
                        <button type="button" class="btn btn-sm btn-transparent rounded-pill px-3 fw-bold text-muted transition-all" id="btnChartBar" onclick="toggleChartType('bar')">
                            <i class="fas fa-chart-bar me-1"></i> Bar
                        </button>
                    </div>
                </div>
                <div class="card-body">
                    <div style="height: 300px;">
                        <canvas id="trendChart"></canvas>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Distribution Chart -->
        <div class="col-12 col-md-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white py-3 border-0">
                    <h6 class="m-0 fw-bold text-dark"><i class="fas fa-chart-pie me-2"></i><span data-i18n="chart_ratio">In/Out Ratio</span></h6>
                </div>
                <div class="card-body">
                    <div style="height: 250px;">
                        <canvas id="distributionChart"></canvas>
                    </div>
                    <div class="mt-4">
                        <div class="d-flex justify-content-between small mb-2 p-2 rounded bg-success-subtle">
                            <span class="fw-bold text-success"><i class="fas fa-arrow-down me-1"></i> <span data-i18n="metric_in">Incoming</span></span>
                            <span class="fw-bold text-dark">{{ number_format($stats['total_in']) }}</span>
                        </div>
                        <div class="d-flex justify-content-between small p-2 rounded bg-warning-subtle">
                            <span class="fw-bold text-warning-emphasis"><i class="fas fa-arrow-up me-1"></i> <span data-i18n="metric_out">Outgoing</span></span>
                            <span class="fw-bold text-dark">{{ number_format($stats['total_out']) }}</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Ready for Pickup (Design Tasks) -->
    @if($readyForPickupTasks->count() > 0)
    <div class="row g-3 mb-4 animate-in">
        <div class="col-12">
            <div class="card border-0 shadow-sm border-start border-4 border-info">
                <div class="card-header bg-white py-3 border-0 d-flex justify-content-between align-items-center">
                    <h6 class="m-0 fw-bold text-dark">
                        <i class="fas fa-box-open me-2 text-info"></i>
                        <span data-i18n="ready_pickup">Ready for Pickup / Delivery</span>
                    </h6>
                    <span class="badge bg-info text-white rounded-pill px-3">{{ $readyForPickupTasks->count() }} Items</span>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="bg-light">
                                <tr>
                                    <th class="ps-4 border-0 text-muted text-uppercase x-small">Ref #</th>
                                    <th class="border-0 text-muted text-uppercase x-small">Customer</th>
                                    <th class="border-0 text-muted text-uppercase x-small">Item Details</th>
                                    <th class="border-0 text-muted text-uppercase x-small">Status</th>
                                    <th class="text-end pe-4 border-0 text-muted text-uppercase x-small">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($readyForPickupTasks as $task)
                                <tr class="transition-all">
                                    <td class="ps-4 fw-bold small text-primary">#{{ $task->task_code ?? $task->id }}</td>
                                    <td>
                                        <div class="fw-bold text-dark small">{{ $task->customer->name }}</div>
                                        <div class="text-muted x-small"><i class="fas fa-phone-alt me-1"></i>{{ $task->customer->phone }}</div>
                                    </td>
                                    <td>
                                        <div class="small fw-medium text-dark text-truncate" style="max-width: 250px;">{{ $task->title }}</div>
                                        @if($task->receptionist)
                                        <div class="text-muted x-small">By: {{ $task->receptionist->name }}</div>
                                        @endif
                                    </td>
                                    <td>
                                        <span class="badge bg-info-subtle text-info border border-info-subtle rounded-pill x-small">
                                            {{ ucfirst(str_replace('_', ' ', $task->delivery_status)) }}
                                        </span>
                                    </td>
                                    <td class="text-end pe-4">
                                        <div class="d-flex justify-content-end gap-1">
                                            <a href="{{ route('admin.design-tasks.show', $task->id) }}" class="btn btn-sm btn-outline-primary rounded-pill x-small">
                                                <i class="fas fa-eye me-1"></i> View / Comment
                                            </a>
                                            <a href="{{ route('gatekeeper.movements.create', [
                                                'type' => 'out',
                                                'product_name' => $task->title,
                                                'quantity' => $task->qty ?? 1,
                                                'recipient_name' => $task->customer->name,
                                                'recipient_identifier' => $task->customer->phone,
                                                'purpose' => 'Delivery',
                                                'authorization_reference' => $task->task_code ?? $task->id
                                            ]) }}" class="btn btn-sm btn-warning text-dark rounded-pill x-small">
                                                <i class="fas fa-sign-out-alt me-1"></i> Record Out
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endif

    <!-- Detailed Sections -->
    <div class="row g-3">
        <!-- Recent Activity Feed -->
        <div class="col-12 col-lg-8">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white py-3 border-0 d-flex justify-content-between align-items-center">
                    <h6 class="m-0 fw-bold text-dark">
                        <i class="fas fa-history me-2"></i>
                        <span data-i18n="activity_log">Activity Log</span> 
                        ({{ ucfirst($period) }})
                    </h6>
                    <a href="{{ route('gatekeeper.movements.index') }}" class="btn btn-sm btn-link text-decoration-none" data-i18n="view_all">View All</a>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="bg-light">
                                <tr>
                                    <th class="ps-4 border-0 text-muted text-uppercase x-small" data-i18n="th_type">Type</th>
                                    <th class="border-0 text-muted text-uppercase x-small" data-i18n="th_desc">Description</th>
                                    <th class="border-0 text-muted text-uppercase x-small" data-i18n="th_handler">Handler</th>
                                    <th class="text-end pe-4 border-0 text-muted text-uppercase x-small" data-i18n="th_time">Time</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($recentMovements as $movement)
                                <tr>
                                    <td class="ps-4">
                                        @if($movement->type === 'in')
                                            <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill x-small">
                                                <i class="fas fa-arrow-down me-1"></i> <span data-i18n="btn_in">IN</span>
                                            </span>
                                        @else
                                            <span class="badge bg-warning-subtle text-warning border border-warning-subtle rounded-pill x-small">
                                                <i class="fas fa-arrow-up me-1"></i> <span data-i18n="btn_out">OUT</span>
                                            </span>
                                        @endif
                                    </td>
                                    <td>
                                        <div class="fw-bold text-dark small text-truncate" style="max-width: 200px;">{{ $movement->product_name }}</div>
                                        <div class="text-muted x-small">
                                            <span data-i18n="txt_qty">Qty</span>: {{ $movement->quantity }} • 
                                            {{ $movement->type === 'in' ? Str::limit($movement->source_name, 12) : Str::limit($movement->recipient_name, 12) }}
                                        </div>
                                    </td>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <div class="avatar-circle bg-secondary bg-opacity-10 text-secondary me-2 d-flex align-items-center justify-content-center rounded-circle" style="width: 24px; height: 24px;">
                                                <i class="fas fa-user x-small"></i>
                                            </div>
                                            <span class="small fw-medium text-dark">{{ Str::limit($movement->handler_name, 15) }}</span>
                                        </div>
                                    </td>
                                    <td class="text-end pe-4">
                                        <div class="small text-dark">{{ $movement->movement_date->format('M d') }}</div>
                                        <span class="text-muted x-small">{{ $movement->movement_date->format('H:i') }}</span>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="4" class="text-center py-4 text-muted small" data-i18n="no_movements">No movements found for this period.</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- My Activity -->
        <div class="col-12 col-lg-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white py-3 border-0">
                    <h6 class="m-0 fw-bold text-dark"><i class="fas fa-user-edit me-2"></i><span data-i18n="my_logs">My Recent Logs</span></h6>
                </div>
                <div class="card-body p-0">
                    <div class="list-group list-group-flush">
                        @forelse($myRecentActivity as $activity)
                        <a href="{{ route('gatekeeper.movements.show', $activity) }}" class="list-group-item list-group-item-action border-0 py-3">
                            <div class="d-flex w-100 justify-content-between mb-1">
                                <small class="fw-bold {{ $activity->type == 'in' ? 'text-success' : 'text-warning' }}">
                                    <span data-i18n="{{ $activity->type == 'in' ? 'incoming' : 'outgoing' }}">
                                    {{ $activity->type == 'in' ? 'INCOMING' : 'OUTGOING' }}
                                    </span>
                                </small>
                                <small class="text-muted">{{ $activity->created_at->diffForHumans() }}</small>
                            </div>
                            <p class="mb-1 small fw-medium text-dark text-truncate">{{ $activity->product_name }}</p>
                            <small class="text-muted"><span data-i18n="txt_qty">Qty</span>: {{ $activity->quantity }}</small>
                        </a>
                        @empty
                        <div class="text-center py-4 text-muted small" data-i18n="no_logs">You haven't recorded any movements yet.</div>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>



@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    
    // --- Translations Configuration ---
    const translations = {
        en: {
            dashboard_title: "Gatekeeper Overview",
            dashboard_subtitle: "Monitor and record facility product movements",
            period_today: "Today",
            period_yesterday: "Yesterday",
            period_week: "This Week",
            period_month: "This Month",
            period_6months: "Last 6 Months",
            period_year: "This Year",
            period_2years: "Last 2 Years",
            period_custom: "Custom Range",
            period_all: "All Time",
            btn_in: "IN",
            btn_out: "OUT",
            metric_total: "Total Movements",
            metric_my_entries: "My Entries",
            metric_desc: "Recorded in this period",
            metric_in: "Incoming",
            metric_receipts: "Receipts",
            metric_out: "Outgoing",
            metric_dispatches: "Dispatches",
            chart_trends: "Movement Trends",
            chart_ratio: "In/Out Ratio",
            activity_log: "Activity Log",
            ready_pickup: "Ready for Pickup",
            view_all: "View All",
            th_type: "Type",
            th_desc: "Description",
            th_handler: "Handler",
            th_time: "Time",
            txt_qty: "Qty",
            no_movements: "No movements found for this period.",
            my_logs: "My Recent Logs",
            no_logs: "You haven't recorded any movements yet.",
            incoming: "INCOMING",
            outgoing: "OUTGOING"
        },
        sw: {
            dashboard_title: "Dashibodi ya Mlinzi",
            dashboard_subtitle: "Fuatilia na rekodi mizigo inayoingia na kutoka",
            period_today: "Leo",
            period_yesterday: "Jana",
            period_week: "Wiki Hii",
            period_month: "Mwezi Huu",
            period_6months: "Miezi 6 Iliyopita",
            period_year: "Mwaka Huu",
            period_2years: "Miaka 2 Iliyopita",
            period_custom: "Muda Maalum",
            period_all: "Muda Wote",
            btn_in: "NDANI",
            btn_out: "NJE",
            metric_total: "Jumla ya Matukio",
            metric_my_entries: "Ingizo Zangu",
            metric_desc: "Imerekodiwa katika kipindi hiki",
            metric_in: "Yaliyoingia",
            metric_receipts: "Mapokezi",
            metric_out: "Yaliyotoka",
            metric_dispatches: "Usafirishaji",
            chart_trends: "Mwenendo wa Matukio",
            chart_ratio: "Uwiano wa Ndani/Nje",
            ready_pickup: "Bidhaa Tayari Kuchukuliwa",
            activity_log: "Kumbukumbu",
            view_all: "Ona Zote",
            th_type: "Aina",
            th_desc: "Maelezo",
            th_handler: "Mhusika",
            th_time: "Muda",
            txt_qty: "Idadi",
            no_movements: "Hakuna matukio yaliyopatikana.",
            my_logs: "Rekodi Zangu",
            no_logs: "Hujarekodi tukio lolote bado.",
            incoming: "INAYOINGIA",
            outgoing: "INAYOTOKA"
        }
    };

    let currentLang = 'en';

    function setLanguage(lang) {
        currentLang = lang;
        document.querySelectorAll('[data-i18n]').forEach(el => {
            const key = el.getAttribute('data-i18n');
            if (translations[lang][key]) {
                el.innerText = translations[lang][key];
            }
        });
        
        // Update Toggle Button Text
        const toggleLabel = document.getElementById('langLabel');
        if (toggleLabel) {
            toggleLabel.innerText = lang === 'en' ? '🇹🇿' : '🇬🇧';
        }

        // Save preference
        localStorage.setItem('gatekeeper_lang', lang);
    }

    // Period Selection Handling
    const periodSelect = document.getElementById('periodSelect');
    const customDateRange = document.getElementById('customDateRange');
    
    if (periodSelect) {
        periodSelect.addEventListener('change', function() {
            if (this.value === 'custom') {
                customDateRange.classList.remove('d-none');
            } else {
                customDateRange.classList.add('d-none');
                this.form.submit();
            }
        });
    }

    // Initialize Language
    const langToggle = document.getElementById('langToggle');
    const savedLang = localStorage.getItem('gatekeeper_lang');
    if (savedLang) {
        setLanguage(savedLang);
    }
    if (langToggle) {
        langToggle.addEventListener('click', function() {
            const newLang = currentLang === 'en' ? 'sw' : 'en';
            setLanguage(newLang);
        });
    }


    // --- Charts ---
    // Trend Chart Configuration
    let trendChartInstance = null;
    const trendCtx = document.getElementById('trendChart').getContext('2d');
    
    // Data from Controller
    const rawLabels = @json($trendChart['labels']);
    const rawDatasets = @json($trendChart['datasets']);

    // Helper: Create Gradient
    function createGradient(ctx, colorStart, colorEnd) {
        const gradient = ctx.createLinearGradient(0, 0, 0, 300);
        gradient.addColorStop(0, colorStart);
        gradient.addColorStop(1, colorEnd);
        return gradient;
    }

    window.toggleChartType = function(type) {
        // Toggle Buttons UI
        const btnLine = document.getElementById('btnChartLine');
        const btnBar = document.getElementById('btnChartBar');
        
        if (type === 'line') {
            btnLine.classList.add('btn-white', 'shadow-sm', 'text-primary');
            btnLine.classList.remove('btn-transparent', 'text-muted');
            btnBar.classList.add('btn-transparent', 'text-muted');
            btnBar.classList.remove('btn-white', 'shadow-sm', 'text-primary');
        } else {
            btnBar.classList.add('btn-white', 'shadow-sm', 'text-primary');
            btnBar.classList.remove('btn-transparent', 'text-muted');
            btnLine.classList.add('btn-transparent', 'text-muted');
            btnLine.classList.remove('btn-white', 'shadow-sm', 'text-primary');
        }

        // Destroy existing chart
        if (trendChartInstance) {
            trendChartInstance.destroy();
        }

        // Prepare Datasets with Modern Styling
        const modernDatasets = rawDatasets.map(ds => {
             const borderColor = ds.borderColor; // Keep original distinct color
             
             // Create dynamic gradients based on the series color for Line specific styling
             const bgColor = type === 'line' 
                ? createGradient(trendCtx, borderColor.replace('1)', '0.4').replace(')', ', 0.4)'), borderColor.replace('1)', '0.01').replace(')', ', 0.01)')) 
                : createGradient(trendCtx, borderColor.replace('1)', '0.8').replace(')', ', 0.8)'), borderColor.replace('1)', '0.4').replace(')', ', 0.4)'));

             // Note: Controller Colors are Hex (#1cc88a), need simple RGB conversion or basic mapping if sticking to specific colors is needed
             // But controller actually sends hex. We need rgba for transparency manipulation.
             // Let's hardcode the known colors for now to ensure gradients work nicely.
             
             let colorBase;
             if(ds.label === 'Incoming') colorBase = '28, 200, 138'; // Green
             else colorBase = '246, 194, 62'; // Yellow/Orange
             
             const bgGradient = createGradient(trendCtx, `rgba(${colorBase}, ${type === 'line' ? 0.3 : 0.8})`, `rgba(${colorBase}, 0.05)`);

            return {
                ...ds,
                backgroundColor: bgGradient,
                borderColor: `rgb(${colorBase})`,
                borderWidth: 2,
                borderRadius: type === 'bar' ? 4 : 0,
                tension: 0.4, // Smooth curve
                fill: type === 'line', // Fill area for line
                pointRadius: type === 'line' ? 3 : 0,
                pointHoverRadius: 6,
                pointBackgroundColor: '#fff',
                pointBorderWidth: 2,
                barPercentage: 0.6,
                categoryPercentage: 0.8
            };
        });

        // Create Chart
        trendChartInstance = new Chart(trendCtx, {
            type: type,
            data: {
                labels: rawLabels,
                datasets: modernDatasets
            },
            options: {
                maintainAspectRatio: false,
                plugins: {
                    legend: { 
                        position: 'top',
                        labels: {
                            usePointStyle: true,
                            boxWidth: 8,
                            font: { family: "'Inter', sans-serif", size: 12 }
                        }
                    },
                    tooltip: { 
                        mode: 'index', 
                        intersect: false,
                        backgroundColor: 'rgba(255, 255, 255, 0.9)',
                        titleColor: '#000',
                        bodyColor: '#555',
                        borderColor: '#eee',
                        borderWidth: 1,
                        padding: 10,
                        displayColors: true,
                        boxPadding: 4,
                        callbacks: {
                            labelColor: function(context) {
                                return {
                                    borderColor: context.dataset.borderColor,
                                    backgroundColor: context.dataset.borderColor
                                };
                            }
                        }
                    }
                },
                scales: {
                    y: { 
                        beginAtZero: true, 
                        grid: { borderDash: [4, 4], color: '#f0f0f0' },
                        ticks: { font: { size: 11 }, padding: 8 }
                    },
                    x: { 
                        grid: { display: false },
                        ticks: { font: { size: 11 }, padding: 8 }
                    }
                },
                interaction: {
                    mode: 'nearest',
                    axis: 'x',
                    intersect: false
                },
                animation: {
                    duration: 750,
                    easing: 'easeOutQuart'
                }
            }
        });
    };

    // Initialize default
    toggleChartType('line');

    // Distribution Chart
    const distCtx = document.getElementById('distributionChart').getContext('2d');
    new Chart(distCtx, {
        type: 'doughnut',
        data: {
            labels: @json($distributionChart['labels']),
            datasets: [{
                data: @json($distributionChart['data']),
                backgroundColor: @json($distributionChart['colors']),
                borderWidth: 0,
                hoverOffset: 4
            }]
        },
        options: {
            maintainAspectRatio: false,
            plugins: {
                legend: { display: false }
            },
            cutout: '70%'
        }
    });

    // Auto-refresh Dashboard every 30 seconds
    setTimeout(function() {
        window.location.reload();
    }, 30000); // 30 seconds
});
</script>
@endpush
@endsection
