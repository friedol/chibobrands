@extends('layouts.admin')

@section('title', 'Designer Performance Report')

@push('styles')
<style>
/* ── Stat cards — same as design task index ── */
.dash-stat-card-compact {
    background: #ffffff;
    border: 1px solid #e2e8f0;
    border-radius: 10px;
    padding: 9px 11px;
    box-shadow: 0 1px 3px rgba(0,0,0,.04);
    transition: all .2s ease;
    height: 100%;
    display: flex;
    flex-direction: column;
    justify-content: space-between;
    text-decoration: none;
    color: inherit;
    cursor: pointer;
}
.dash-stat-card-compact:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(0,0,0,.08);
    color: inherit;
}
.dsc-icon {
    width: 30px; height: 30px; border-radius: 8px;
    display: flex; align-items: center; justify-content: center;
    font-size: 12px; flex-shrink: 0;
}
.dsc-val  { font-size: 1.05rem; font-weight: 700; line-height: 1.25; margin-top: 4px; }
.dsc-lbl  { font-size: 11px; font-weight: 600; color: #64748b; margin-top: 1px; }
.dsc-sub  { font-size: 10px; font-weight: 500; color: #94a3b8; }

/* ── Designer rows clickable ── */
.designer-row { cursor: pointer; transition: background .15s; }
.designer-row:hover td { background: #f0f7ff !important; }
.designer-row td:first-child { position: relative; }
.rate-bar { height: 5px; border-radius: 3px; background: #e9ecef; overflow: hidden; }
.rate-bar-fill { height: 100%; border-radius: 3px; transition: width .4s; }

/* ── Clickable task rows ── */
.task-row { cursor: pointer; transition: background .12s; }
.task-row:hover td { background: #f8fafc !important; }

/* ── Section header ── */
.section-card-header {
    border-bottom: none;
    display: flex; align-items: center; justify-content: space-between;
}

.avatar-sm {
    width: 30px; height: 30px; border-radius: 50%;
    display: flex; align-items: center; justify-content: center;
    font-size: 11px; font-weight: 700; flex-shrink: 0;
}

@media (max-width: 768px) {
    .dtr-stat .val { font-size: 1.3rem; }
    .dtr-stat .icon-wrap { width: 36px; height: 36px; font-size: 1rem; }
}

@media print {
    @page { size: A4; margin: 10mm; }
    body { background:#fff !important; }
    .sidebar, .top-navbar, .no-print, .filter-section, .btn, .breadcrumb { display:none !important; }
    .main-content { margin:0 !important; padding:0 !important; }
    .container-fluid { padding:0 !important; }
    .card { box-shadow:none !important; border:1px solid #ccc !important; break-inside:avoid; }
    canvas { max-height:180px !important; }
    .print-only { display:block !important; }
}
.print-only { display:none; }
</style>
@endpush

@section('content')
<div class="container-fluid px-3 py-3">

    {{-- Print header --}}
    <div class="print-only mb-4" style="border-bottom:2px solid #1d4ed8;padding-bottom:12px;text-align:center;">
        @include('partials.logo-print')
        <div style="font-size:22px;font-weight:900;">CHIBOBRAND CO. LTD</div>
        <div style="font-size:16px;color:#333;margin-top:4px;">Designer Performance Report</div>
        <div style="font-size:11px;color:#666;margin-top:4px;">
            Generated: {{ now()->format('M d, Y H:i') }} &nbsp;|&nbsp;
            Period: {{ \Carbon\Carbon::parse($dateFrom)->format('M d, Y') }} – {{ \Carbon\Carbon::parse($dateTo)->format('M d, Y') }}
        </div>
    </div>

    {{-- ── Page header ── --}}
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-2 no-print">
        <div>
            <h2 class="mb-0 fw-bold">Designer Performance</h2>
            <p class="text-muted small mb-0">Productivity and fulfillment analytics</p>
        </div>
        <div class="d-flex gap-2 flex-wrap">
            <x-report-export-menu
                :print-url="route('admin.design-tasks.reports.print', request()->only('date_from','date_to','designer_id'))"
                :pdf-url="route('admin.reports.design-tasks.export', array_merge(request()->all(), ['type' => 'pdf']))"
                :excel-url="route('admin.reports.design-tasks.export', array_merge(request()->all(), ['type' => 'excel']))"
                label="Export"
            />
        </div>
    </div>

    {{-- ── Filters ── --}}
    <div class="card border-0 shadow-sm mb-4 no-print" style="border-radius:12px;">
        <div class="card-body p-3">
            <form action="{{ route('admin.reports.design-tasks') }}" method="GET" class="row g-2 align-items-end" data-no-global-handler>
                <div class="col-6 col-md-3">
                    <label class="form-label fw-bold x-small text-uppercase mb-1" style="color:#64748b;">From</label>
                    <input type="date" class="form-control form-control-sm" name="date_from" value="{{ $dateFrom }}">
                </div>
                <div class="col-6 col-md-3">
                    <label class="form-label fw-bold x-small text-uppercase mb-1" style="color:#64748b;">To</label>
                    <input type="date" class="form-control form-control-sm" name="date_to" value="{{ $dateTo }}">
                </div>
                <div class="col-12 col-md-4">
                    <label class="form-label fw-bold x-small text-uppercase mb-1" style="color:#64748b;">Designer</label>
                    <select class="form-select form-select-sm" name="designer_id">
                        <option value="">All Designers</option>
                        @foreach($designers as $d)
                            <option value="{{ $d->id }}" {{ $designerId == $d->id ? 'selected' : '' }}>{{ $d->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-12 col-md-2 d-flex gap-1">
                    <button type="submit" data-no-global-handler class="btn btn-primary btn-sm fw-bold flex-grow-1">
                        <i class="fas fa-sync-alt me-1"></i>Filter
                    </button>
                    @if(request()->anyFilled(['date_from','date_to','designer_id']))
                        <a href="{{ route('admin.reports.design-tasks') }}" class="btn btn-outline-secondary btn-sm">✕</a>
                    @endif
                </div>
            </form>
        </div>
    </div>

    {{-- ── Stat cards — same style as design task index ── --}}
    <div class="row g-2 mb-4">
        {{-- Total --}}
        <div class="col-6 col-md-4 col-lg-2">
            <a href="{{ route('admin.reports.design-tasks', request()->except('page')) }}" class="dash-stat-card-compact">
                <div class="d-flex justify-content-between align-items-center">
                    <div class="dsc-icon bg-primary-subtle text-primary"><i class="fas fa-tasks"></i></div>
                    <span class="dsc-sub">All</span>
                </div>
                <div class="dsc-val text-primary">{{ number_format($summary['total_tasks']) }}</div>
                <div class="dsc-lbl">Total Tasks</div>
            </a>
        </div>
        {{-- Pending --}}
        <div class="col-6 col-md-4 col-lg-2">
            <a href="#" class="dash-stat-card-compact">
                <div class="d-flex justify-content-between align-items-center">
                    <div class="dsc-icon bg-warning-subtle text-warning"><i class="fas fa-clock"></i></div>
                    <span class="dsc-sub">Queue</span>
                </div>
                <div class="dsc-val text-warning">{{ number_format($summary['pending']) }}</div>
                <div class="dsc-lbl">Pending</div>
            </a>
        </div>
        {{-- In Progress --}}
        <div class="col-6 col-md-4 col-lg-2">
            <a href="#" class="dash-stat-card-compact">
                <div class="d-flex justify-content-between align-items-center">
                    <div class="dsc-icon bg-info-subtle text-info"><i class="fas fa-spinner"></i></div>
                    <span class="dsc-sub">Active</span>
                </div>
                <div class="dsc-val text-info">{{ number_format($summary['in_progress']) }}</div>
                <div class="dsc-lbl">In Progress</div>
            </a>
        </div>
        {{-- Completed --}}
        <div class="col-6 col-md-4 col-lg-2">
            <a href="#" class="dash-stat-card-compact">
                <div class="d-flex justify-content-between align-items-center">
                    <div class="dsc-icon bg-success-subtle text-success"><i class="fas fa-check-double"></i></div>
                    <span class="dsc-sub">Done</span>
                </div>
                <div class="dsc-val text-success">{{ number_format($summary['completed']) }}</div>
                <div class="dsc-lbl">Completed</div>
            </a>
        </div>
        {{-- Rejected --}}
        <div class="col-6 col-md-4 col-lg-2">
            <a href="#" class="dash-stat-card-compact">
                <div class="d-flex justify-content-between align-items-center">
                    <div class="dsc-icon bg-danger-subtle text-danger"><i class="fas fa-times-circle"></i></div>
                    <span class="dsc-sub">Void</span>
                </div>
                <div class="dsc-val text-danger">{{ number_format($summary['rejected']) }}</div>
                <div class="dsc-lbl">Rejected</div>
            </a>
        </div>
        {{-- Completion Rate --}}
        <div class="col-6 col-md-4 col-lg-2">
            <div class="dash-stat-card-compact" style="cursor:default;">
                <div class="d-flex justify-content-between align-items-center">
                    <div class="dsc-icon" style="background:rgba(139,92,246,.12);color:#7c3aed;"><i class="fas fa-chart-pie"></i></div>
                    <span class="dsc-sub">Rate</span>
                </div>
                <div class="dsc-val" style="color:#7c3aed;">{{ $summary['completion_rate'] }}%</div>
                <div class="dsc-lbl">Completion Rate</div>
            </div>
        </div>
    </div>

    {{-- ── Charts row ── --}}
    <div class="row g-3 mb-4 no-print">
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white py-3 border-0">
                    <h6 class="m-0 fw-bold text-primary"><i class="fas fa-chart-line me-2"></i>Productivity Trend</h6>
                </div>
                <div class="card-body pt-0">
                    <canvas id="designerTrendChart" style="max-height:240px;"></canvas>
                </div>
            </div>
        </div>
        <div class="col-lg-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white py-3 border-0">
                    <h6 class="m-0 fw-bold text-primary"><i class="fas fa-chart-pie me-2"></i>Status Breakdown</h6>
                </div>
                <div class="card-body d-flex align-items-center justify-content-center">
                    <canvas id="designerPieChart" style="max-height:220px;"></canvas>
                </div>
            </div>
        </div>
    </div>

    {{-- ── Designer Rankings — click row to open report ── --}}
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-header bg-white py-3 border-0 section-card-header">
            <h6 class="m-0 fw-bold text-primary"><i class="fas fa-medal me-2"></i>Designer Rankings</h6>
            <span class="badge bg-primary bg-opacity-10 text-primary x-small">Click row to view designer report</span>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead>
                        <tr class="bg-light">
                            <th class="ps-3 x-small fw-bold">Designer</th>
                            <th class="text-center x-small fw-bold">Total</th>
                            <th class="text-center x-small fw-bold">In Progress</th>
                            <th class="text-center x-small fw-bold">Completed</th>
                            <th class="x-small fw-bold" style="min-width:120px;">Fulfillment</th>
                            <th class="text-end pe-3 x-small fw-bold">Rate</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($tasksByDesigner as $item)
                        @php
                            $rate = $item['total'] > 0 ? round(($item['completed'] / $item['total']) * 100, 1) : 0;
                            $rateColor = $rate >= 80 ? '#16a34a' : ($rate >= 50 ? '#f59e0b' : '#dc2626');
                            $printUrl = route('admin.design-tasks.reports.print', array_merge(
                                request()->only('date_from','date_to'),
                                ['designer_id' => $item['designer']?->id]
                            ));
                        @endphp
                        <tr class="designer-row" onclick="window.open('{{ $printUrl }}', '_blank')" title="Open {{ $item['designer']?->name }} Performance Report">
                            <td class="ps-3">
                                <div class="d-flex align-items-center gap-2">
                                    <div class="avatar-sm bg-primary text-white">
                                        {{ substr($item['designer']?->name ?? 'D', 0, 1) }}
                                    </div>
                                    <div>
                                        <div class="small fw-bold">{{ $item['designer']?->name ?? 'Unknown' }}</div>
                                    </div>
                                    <i class="fas fa-external-link-alt ms-auto text-muted no-print" style="font-size:.6rem;opacity:.5;"></i>
                                </div>
                            </td>
                            <td class="text-center small fw-bold">{{ $item['total'] }}</td>
                            <td class="text-center small text-warning fw-bold">{{ $item['in_progress'] }}</td>
                            <td class="text-center small text-success fw-bold">{{ $item['completed'] }}</td>
                            <td>
                                <div class="rate-bar">
                                    <div class="rate-bar-fill" style="width:{{ $rate }}%;background:{{ $rateColor }};"></div>
                                </div>
                            </td>
                            <td class="text-end pe-3 small fw-bold" style="color:{{ $rateColor }};">{{ $rate }}%</td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="text-center py-4 text-muted small">No tasks found for this period.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- ── Task list — click row to open task ── --}}
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-header bg-white py-3 border-0 section-card-header">
            <h6 class="m-0 fw-bold text-primary"><i class="fas fa-list me-2"></i>Recent Design Tasks</h6>
            <span class="badge bg-secondary bg-opacity-10 text-secondary x-small">Click row to open task</span>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead>
                        <tr class="bg-light">
                            <th class="ps-3 x-small fw-bold">Task</th>
                            <th class="x-small fw-bold">Customer</th>
                            <th class="x-small fw-bold">Designer</th>
                            <th class="text-center x-small fw-bold">Status</th>
                            <th class="text-end pe-3 x-small fw-bold" style="white-space:nowrap;">Created</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($tasks->take(60) as $task)
                        @php
                            $statusColors = [
                                'pending'        => ['bg'=>'#fef3c7','text'=>'#92400e'],
                                'in_progress'    => ['bg'=>'#dbeafe','text'=>'#1e40af'],
                                'in_review'      => ['bg'=>'#e0e7ff','text'=>'#3730a3'],
                                'printing'       => ['bg'=>'#e0f2fe','text'=>'#0369a1'],
                                'printed'        => ['bg'=>'#d1fae5','text'=>'#065f46'],
                                'completed'      => ['bg'=>'#d1fae5','text'=>'#065f46'],
                                'confirmed'      => ['bg'=>'#d1fae5','text'=>'#065f46'],
                                'super_completed'=> ['bg'=>'#d1fae5','text'=>'#065f46'],
                                'delivered'      => ['bg'=>'#bbf7d0','text'=>'#14532d'],
                                'rejected'       => ['bg'=>'#fee2e2','text'=>'#991b1b'],
                                'cancelled'      => ['bg'=>'#f3f4f6','text'=>'#6b7280'],
                            ];
                            $sc = $statusColors[$task->status] ?? ['bg'=>'#f3f4f6','text'=>'#374151'];
                            $taskUrl = route('admin.design-tasks.show', $task);
                        @endphp
                        <tr class="task-row" onclick="window.location='{{ $taskUrl }}'">
                            <td class="ps-3">
                                <div class="small fw-bold">{{ $task->title }}</div>
                                <div class="x-small text-muted">{{ $task->task_code }}</div>
                            </td>
                            <td class="small">{{ $task->customer?->name ?? 'N/A' }}</td>
                            <td class="small">{{ $task->designer?->name ?? '—' }}</td>
                            <td class="text-center">
                                <span class="badge rounded-pill"
                                      style="background:{{ $sc['bg'] }};color:{{ $sc['text'] }};font-size:.65rem;font-weight:700;padding:3px 10px;">
                                    {{ ucfirst(str_replace('_',' ',$task->status)) }}
                                </span>
                            </td>
                            <td class="text-end pe-3 small text-muted" style="white-space:nowrap;">{{ $task->created_at->format('M d, Y') }}</td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="5" class="text-center py-4 text-muted small">No tasks found for this period.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if($tasks->count() > 60)
            <div class="px-3 py-2 text-muted x-small text-end border-top">
                Showing 60 of {{ $tasks->count() }} tasks. Use the print view to see all.
            </div>
            @endif
        </div>
    </div>

</div>

@push('scripts')
<script>
const trendData = @json($tasksByDate);
const labels    = trendData.map(d => d.date);

new Chart(document.getElementById('designerTrendChart'), {
    type: 'line',
    data: {
        labels,
        datasets: [
            { label: 'Total', data: trendData.map(d => d.total),     borderColor: '#6366f1', backgroundColor: 'rgba(99,102,241,.06)', fill: true, tension: .35, pointRadius: 2 },
            { label: 'Completed', data: trendData.map(d => d.completed), borderColor: '#10b981', borderWidth: 2, tension: .35, pointRadius: 2 },
            { label: 'In Progress', data: trendData.map(d => d.in_progress), borderColor: '#f59e0b', borderDash: [4,3], tension: .35, pointRadius: 2 },
        ]
    },
    options: {
        maintainAspectRatio: false,
        plugins: { legend: { position: 'top', labels: { boxWidth: 10, font: { size: 10 } } } },
        scales: {
            x: { grid: { display: false }, ticks: { font: { size: 9 }, maxTicksLimit: 12 } },
            y: { ticks: { font: { size: 9 }, stepSize: 1 }, beginAtZero: true }
        }
    }
});

new Chart(document.getElementById('designerPieChart'), {
    type: 'doughnut',
    data: {
        labels: ['Pending', 'In Progress', 'Completed', 'Rejected'],
        datasets: [{
            data: [{{ $summary['pending'] }}, {{ $summary['in_progress'] }}, {{ $summary['completed'] }}, {{ $summary['rejected'] }}],
            backgroundColor: ['#fef3c7','#dbeafe','#d1fae5','#fee2e2'],
            borderColor:     ['#f59e0b','#3b82f6','#10b981','#ef4444'],
            borderWidth: 2,
        }]
    },
    options: {
        maintainAspectRatio: false,
        plugins: {
            legend: { position: 'bottom', labels: { boxWidth: 10, font: { size: 10 } } }
        },
        cutout: '65%'
    }
});
</script>
@endpush
@endsection
