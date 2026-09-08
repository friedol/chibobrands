@extends('layouts.admin')

@section('title', 'Reports & Analytics')

@push('styles')
<style>
    .report-group { margin-bottom: 28px; }
    .report-group-title {
        font-size: 10px;
        font-weight: 800;
        text-transform: uppercase;
        letter-spacing: .7px;
        color: #94a3b8;
        margin-bottom: 6px;
        padding-left: 2px;
    }
    .report-list { border: 1px solid #e2e8f0; border-radius: 10px; overflow: hidden; }
    .report-list .col-12 { border-bottom: 1px solid #f1f5f9; }
    .report-list .col-12:nth-last-child(1),
    .report-list .col-12:nth-last-child(2):nth-child(odd) { border-bottom: none; }
    .report-item {
        display: flex;
        align-items: center;
        gap: 14px;
        padding: 12px 16px;
        text-decoration: none;
        color: #0f172a;
        background: #fff;
        height: 100%;
        transition: background .13s;
    }
    .report-item:hover { background: #f8fafc; color: #0f172a; }
    .ri-icon {
        width: 34px; height: 34px;
        border-radius: 8px;
        display: flex; align-items: center; justify-content: center;
        font-size: 14px;
        flex-shrink: 0;
    }
    .ri-name { font-size: 13px; font-weight: 700; }
    .ri-desc { font-size: 11px; color: #64748b; }
    .ri-arrow { margin-left: auto; color: #cbd5e1; font-size: 11px; flex-shrink: 0; }
    .report-item:hover .ri-arrow { color: #475569; }
</style>
@endpush

@section('content')
<div class="container-fluid py-3">

    <div class="mb-4">
        <h4 class="fw-bold mb-0"><i class="fas fa-chart-bar me-2 text-danger"></i>Reports &amp; Analytics</h4>
        <p class="text-muted mb-0" style="font-size:12px;">All reports in one place</p>
    </div>

    @php
    $groups = [
        [
            'title' => 'Sales & Tasks',
            'items' => [
                ['icon'=>'fa-chart-pie',   'color'=>'#3b82f6', 'bg'=>'rgba(59,130,246,.1)',  'name'=>'Design Task Reports',  'desc'=>'Task volumes, status breakdown and period analysis.',             'route'=>route('admin.design-tasks.reports')],
                ['icon'=>'fa-users',       'color'=>'#10b981', 'bg'=>'rgba(16,185,129,.1)',  'name'=>'Leads Report',         'desc'=>'Lead KPIs, conversions, follow-ups and source breakdown.',        'route'=>route('admin.reports.sales')],
                ['icon'=>'fa-list-alt',    'color'=>'#f59e0b', 'bg'=>'rgba(245,158,11,.1)',  'name'=>'Leads Summary',        'desc'=>'Full leads list with status filters — print, PDF and Excel.',    'route'=>route('admin.leads.index')],
                ['icon'=>'fa-building',    'color'=>'#0ea5e9', 'bg'=>'rgba(14,165,233,.1)',  'name'=>'Department Sales',     'desc'=>'Sales breakdown by department and team.',                         'route'=>route('admin.reports.department_sales')],
                ['icon'=>'fa-user-tie',    'color'=>'#10b981', 'bg'=>'rgba(16,185,129,.1)',  'name'=>'Saler Performance',    'desc'=>'Seller output, conversion rates and trends.',                     'route'=>route('admin.saler-performance.index')],
                ['icon'=>'fa-paint-brush', 'color'=>'#06b6d4', 'bg'=>'rgba(6,182,212,.1)',   'name'=>'Designer Performance', 'desc'=>'Designer productivity, workload and quality.',                    'route'=>route('admin.reports.design-tasks')],
                ['icon'=>'fa-cogs',        'color'=>'#475569', 'bg'=>'rgba(71,85,105,.1)',   'name'=>'Operator Performance', 'desc'=>'Operator throughput and completion patterns.',                    'route'=>route('admin.reports.operators')],
                ['icon'=>'fa-user-clock',  'color'=>'#dc2626', 'bg'=>'rgba(220,38,38,.1)',   'name'=>'Saler Activity',       'desc'=>'Seller lead activity, conversions and follow-up history per period.', 'route'=>route('admin.saler.sales-report')],
            ],
        ],
        [
            'title' => 'Finance',
            'items' => [
                ['icon'=>'fa-coins',        'color'=>'#f59e0b', 'bg'=>'rgba(245,158,11,.1)', 'name'=>'Financial Reports',       'desc'=>'Revenue, expenses and profit & loss summaries.',             'route'=>route('admin.finance.reports')],
                ['icon'=>'fa-calendar-day', 'color'=>'#ef4444', 'bg'=>'rgba(239,68,68,.1)',  'name'=>'Daily Finance Summary',   'desc'=>'Daily cash movement and transaction overview.',              'route'=>route('admin.finance.daily-report')],
            ],
        ],
        [
            'title' => 'Customers & Marketing',
            'items' => [
                ['icon'=>'fa-users',  'color'=>'#8b5cf6', 'bg'=>'rgba(139,92,246,.1)', 'name'=>'Customer Reports', 'desc'=>'Growth, retention and customer source analytics.', 'route'=>route('admin.reports.customers')],
                ['icon'=>'fa-images', 'color'=>'#64748b', 'bg'=>'rgba(100,116,139,.1)','name'=>'Ads / Hero Slides','desc'=>'Creative and hero-slide performance analytics.',   'route'=>route('admin.hero-slides.analytics')],
            ],
        ],
        [
            'title' => 'Operations',
            'items' => [
                ['icon'=>'fa-door-open',    'color'=>'#3b82f6', 'bg'=>'rgba(59,130,246,.1)', 'name'=>'Gatekeeper Performance', 'desc'=>'Checkpoint throughput and delivery validation.', 'route'=>route('admin.gatekeeper-performance.index')],
                ['icon'=>'fa-truck-loading','color'=>'#f59e0b', 'bg'=>'rgba(245,158,11,.1)', 'name'=>'Delivery Performance',   'desc'=>'Delivery speed, completion and exceptions.',    'route'=>route('admin.delivery-performance.index')],
            ],
        ],
    ];
    @endphp

    @foreach($groups as $group)
    <div class="report-group">
        <div class="report-group-title">{{ $group['title'] }}</div>
        <div class="report-list row g-0">
            @foreach($group['items'] as $i => $item)
            <div class="col-12 col-md-6" style="{{ ($i % 2 === 0 && $i + 1 < count($group['items'])) ? 'border-right:1px solid #f1f5f9;' : '' }}">
                <a href="{{ $item['route'] }}" class="report-item">
                    <div class="ri-icon" style="background:{{ $item['bg'] }};color:{{ $item['color'] }};">
                        <i class="fas {{ $item['icon'] }}"></i>
                    </div>
                    <div>
                        <div class="ri-name">{{ $item['name'] }}</div>
                        <div class="ri-desc">{{ $item['desc'] }}</div>
                    </div>
                    <i class="fas fa-chevron-right ri-arrow"></i>
                </a>
            </div>
            @endforeach
        </div>
    </div>
    @endforeach

</div>
@endsection
