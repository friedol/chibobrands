@extends('layouts.admin')

@section('title', 'Operator Performance Reporting & System Verification')

@push('styles')
    <style>
        :root {
            --op-red-primary: #dc2626;
            --op-red-dark: #991b1b;
            --op-bg-subtle: #fef2f2;
            --op-card-border: #e2e8f0;
        }

        .operator-report-container {
            background-color: #f8fafc;
            min-height: calc(100vh - 100px);
            padding-bottom: 2rem;
        }

        .header-badge-pill {
            background-color: rgba(220, 38, 38, 0.1);
            color: #dc2626 !important;
            padding: 0.35rem 0.95rem;
            border-radius: 50px;
            font-size: 0.8rem;
            font-weight: 600;
            display: inline-flex;
            align-items: center;
        }

        .header-stat-box {
            background-color: rgba(0, 0, 0, 0.18);
            border: 1px solid rgba(255, 255, 255, 0.3);
            padding: 0.65rem 1.35rem;
            border-radius: 12px;
            color: #ffffff !important;
            text-align: right;
        }

        /* Cards & Container */
        .op-card {
            border-radius: 14px;
            border: 1px solid var(--op-card-border);
            background: #ffffff;
            box-shadow: 0 4px 14px rgba(0, 0, 0, 0.03);
            transition: all 0.25s cubic-bezier(0.4, 0, 0.2, 1);
        }

        .op-card:hover {
            box-shadow: 0 8px 24px rgba(0, 0, 0, 0.06);
        }

        /* Single-Row Scrollable Stat Cards Bar for Operators Report */
        .op-stats-row-scroll {
            display: flex;
            flex-wrap: nowrap;
            overflow-x: auto;
            gap: 10px;
            padding-bottom: 8px;
            -webkit-overflow-scrolling: touch;
            scrollbar-width: thin;
        }

        .op-stats-row-scroll::-webkit-scrollbar {
            height: 4px;
        }

        .op-stats-row-scroll::-webkit-scrollbar-thumb {
            background: #cbd5e1;
            border-radius: 4px;
        }

        /* Stat Cards Styling */
        .stat-card-custom {
            flex: 1 1 0;
            min-width: 145px;
            border-radius: 10px;
            background: #ffffff;
            border: 1px solid #e2e8f0;
            padding: 0.85rem 1rem;
            position: relative;
            overflow: hidden;
            box-shadow: 0 2px 6px rgba(0, 0, 0, 0.03);
            transition: transform 0.2s ease, box-shadow 0.2s ease;
        }

        .stat-card-custom:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 16px rgba(0, 0, 0, 0.06);
        }

        .stat-icon-wrapper {
            width: 34px;
            height: 34px;
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 0.95rem;
            flex-shrink: 0;
        }

        .avatar-circle {
            height: 36px;
            width: 36px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 13px;
            font-weight: 700;
            box-shadow: 0 2px 6px rgba(0, 0, 0, 0.08);
        }

        .system-verification-banner {
            background: linear-gradient(135deg, #fef2f2 0%, #fff1f2 100%);
            border: 1px solid #fecaca;
            border-radius: 14px;
            padding: 1.25rem 1.5rem;
            box-shadow: 0 4px 12px rgba(220, 38, 38, 0.04);
        }

        .progress-micro {
            height: 6px;
            border-radius: 10px;
            background-color: #e2e8f0;
            overflow: hidden;
        }

        @media print {
            @page {
                size: A4;
                margin: 8mm;
            }

            body {
                background: white !important;
                font-size: 10pt !important;
                margin: 0 !important;
                padding: 0 !important;
                color: #000 !important;
            }

            .no-print {
                display: none !important;
            }

            .main-content {
                margin-left: 0 !important;
                padding: 0 !important;
                width: 100% !important;
            }

            .container-fluid {
                width: 100% !important;
                padding: 0 !important;
            }
        }
    </style>
@endpush

@section('content')
    <div class="operator-report-container container-fluid py-4">

        {{-- ── PAGE HEADER ──────────────────────────────────────────── --}}
        <div class="row mb-4">
            <div class="col-12">
                <div class="d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-3">
                    <div>
                        <h2 class="fw-bold mb-0" style="font-size: clamp(1.1rem, 4vw, 1.5rem); color:#1e293b;">
                            <i class="fas fa-user-gear me-2 text-danger" style="font-size:1.1rem;"></i>Operator Reports
                        </h2>
                    </div>
                    <div class="d-flex align-items-center gap-2 flex-wrap">
                        <x-report-export-menu
                            :print-url="route('admin.reports.operators.print', request()->all())"
                            :pdf-url="route('admin.reports.operators.pdf', request()->all())"
                            :excel-url="route('admin.reports.operators.export', request()->all())"
                            label="Export"
                        />
                    </div>
                </div>
            </div>
        </div>

        <!-- Filters Section -->
        <div class="op-card mb-4 no-print">
            <div class="card-header bg-white py-3 border-0">
                <h6 class="fw-bold mb-0 text-dark"><i class="fas fa-sliders text-danger me-2"></i>Report Filters & Audit
                    Parameters</h6>
            </div>
            <div class="card-body bg-light bg-opacity-50 p-3.5 border-top">
                <form action="{{ route('admin.reports.operators') }}" method="GET" class="row g-3">
                    <div class="col-6 col-md-2">
                        <label class="form-label fw-semibold text-secondary small text-uppercase mb-1">From Date</label>
                        <input type="date" class="form-control form-control-sm" name="date_from" value="{{ $dateFrom }}">
                    </div>
                    <div class="col-6 col-md-2">
                        <label class="form-label fw-semibold text-secondary small text-uppercase mb-1">To Date</label>
                        <input type="date" class="form-control form-control-sm" name="date_to" value="{{ $dateTo }}">
                    </div>
                    <div class="col-6 col-md-3">
                        <label class="form-label fw-semibold text-secondary small text-uppercase mb-1">Operator</label>
                        <select class="form-select form-select-sm" name="operator_id">
                            <option value="">All Operators</option>
                            @foreach($operators as $op)
                                <option value="{{ $op->id }}" {{ $operatorId == $op->id ? 'selected' : '' }}>
                                    {{ $op->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-6 col-md-2">
                        <label class="form-label fw-semibold text-secondary small text-uppercase mb-1">Branch /
                            Department</label>
                        <select class="form-select form-select-sm" name="department_id">
                            <option value="">All Branches</option>
                            @foreach($departments as $dept)
                                <option value="{{ $dept->id }}" {{ $departmentId == $dept->id ? 'selected' : '' }}>
                                    {{ $dept->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-6 col-md-2">
                        <label class="form-label fw-semibold text-secondary small text-uppercase mb-1">Task Status</label>
                        <select class="form-select form-select-sm" name="status">
                            <option value="all" {{ $status === 'all' ? 'selected' : '' }}>All Statuses</option>
                            <option value="completed" {{ $status === 'completed' ? 'selected' : '' }}>Completed (Verified)
                            </option>
                            <option value="pending" {{ $status === 'pending' ? 'selected' : '' }}>Pending / In Progress
                            </option>
                            <option value="overdue" {{ $status === 'overdue' ? 'selected' : '' }}>Overdue Tasks</option>
                            <option value="printing" {{ $status === 'printing' ? 'selected' : '' }}>Printing</option>
                            <option value="printed" {{ $status === 'printed' ? 'selected' : '' }}>Printed</option>
                            <option value="delivered" {{ $status === 'delivered' ? 'selected' : '' }}>Delivered</option>
                        </select>
                    </div>
                    <div class="col-6 col-md-1 d-flex align-items-end">
                        <button type="submit" class="btn btn-danger btn-sm w-100 py-1.5 fw-bold shadow-sm"
                            style="background: linear-gradient(135deg, #dc2626 0%, #b91c1c 100%); border: none;">
                            <i class="fas fa-filter me-1"></i> Apply
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- 5 Performance Metric Summary Cards (Single Row Fit) -->
        <div class="op-stats-row-scroll mb-4">
            <div class="stat-card-custom">
                <div class="d-flex align-items-center mb-1.5">
                    <div class="stat-icon-wrapper bg-primary-subtle text-primary me-2.5">
                        <i class="fas fa-tasks"></i>
                    </div>
                    <div>
                        <div class="text-secondary x-small fw-bold text-uppercase">Assigned</div>
                        <h4 class="fw-extrabold text-dark mb-0 mt-0.5" style="font-size: 1.1rem;">
                            {{ number_format($summary['total_assigned']) }}</h4>
                    </div>
                </div>
                <div class="progress-micro mt-1.5">
                    <div class="progress-bar bg-primary" style="width: 100%;"></div>
                </div>
                <span class="x-small text-muted mt-1 d-block">System Tasks</span>
            </div>

            <div class="stat-card-custom">
                <div class="d-flex align-items-center mb-1.5">
                    <div class="stat-icon-wrapper bg-success-subtle text-success me-2.5">
                        <i class="fas fa-check-circle"></i>
                    </div>
                    <div>
                        <div class="text-secondary x-small fw-bold text-uppercase">Completed</div>
                        <h4 class="fw-extrabold text-success mb-0 mt-0.5" style="font-size: 1.1rem;">
                            {{ number_format($summary['total_completed']) }}</h4>
                    </div>
                </div>
                <div class="progress-micro mt-1.5">
                    <div class="progress-bar bg-success" style="width: {{ min(100, $summary['completion_rate']) }}%;"></div>
                </div>
                <span class="x-small fw-bold text-success mt-1 d-block"><i
                        class="fas fa-arrow-up me-1"></i>{{ $summary['completion_rate'] }}%</span>
            </div>

            <div class="stat-card-custom">
                <div class="d-flex align-items-center mb-1.5">
                    <div class="stat-icon-wrapper bg-warning-subtle text-warning me-2.5">
                        <i class="fas fa-hourglass-half"></i>
                    </div>
                    <div>
                        <div class="text-secondary x-small fw-bold text-uppercase">Pending</div>
                        <h4 class="fw-extrabold text-dark mb-0 mt-0.5" style="font-size: 1.1rem;">
                            {{ number_format($summary['total_pending']) }}</h4>
                    </div>
                </div>
                <div class="progress-micro mt-1.5">
                    <div class="progress-bar bg-warning" style="width: {{ min(100, $summary['pending_rate']) }}%;"></div>
                </div>
                <span class="x-small fw-bold text-warning mt-1 d-block">{{ $summary['pending_rate'] }}%</span>
            </div>

            <div class="stat-card-custom">
                <div class="d-flex align-items-center mb-1.5">
                    <div class="stat-icon-wrapper bg-danger-subtle text-danger me-2.5">
                        <i class="fas fa-exclamation-triangle"></i>
                    </div>
                    <div>
                        <div class="text-secondary x-small fw-bold text-uppercase">Overdue</div>
                        <h4 class="fw-extrabold text-danger mb-0 mt-0.5" style="font-size: 1.1rem;">
                            {{ number_format($summary['total_overdue']) }}</h4>
                    </div>
                </div>
                <div class="progress-micro mt-1.5">
                    <div class="progress-bar bg-danger"
                        style="width: {{ $summary['total_assigned'] > 0 ? min(100, round(($summary['total_overdue'] / $summary['total_assigned']) * 100, 1)) : 0 }}%;">
                    </div>
                </div>
                <span class="x-small text-danger fw-bold mt-1 d-block"><i class="fas fa-bell me-1"></i>Follow-up</span>
            </div>

            <div class="stat-card-custom">
                <div class="d-flex align-items-center mb-1.5">
                    <div class="stat-icon-wrapper bg-info-subtle text-info me-2.5">
                        <i class="fas fa-wallet"></i>
                    </div>
                    <div>
                        <div class="text-secondary x-small fw-bold text-uppercase">Revenue</div>
                        <h5 class="fw-extrabold text-dark mb-0 mt-0.5" style="font-size: 0.95rem;">TZS
                            {{ number_format($summary['total_revenue']) }}</h5>
                    </div>
                </div>
                <div class="progress-micro mt-1.5">
                    <div class="progress-bar bg-info" style="width: 100%;"></div>
                </div>
                <span class="x-small text-muted mt-1 d-block">Output</span>
            </div>
        </div>

        <!-- Operator Efficiency & Verification Rankings Table -->
        <div class="op-card mb-4">
            <div class="card-header bg-white py-3 border-0 d-flex justify-content-between align-items-center">
                <h6 class="m-0 fw-bold text-dark"><i class="fas fa-trophy text-warning me-2"></i>Operator Performance
                    Summary & Efficiency Rankings</h6>
                <span
                    class="badge bg-danger-subtle text-danger border border-danger-subtle px-3 py-1.5 rounded-pill small fw-bold">
                    <i class="fas fa-check-circle me-1"></i>Database Verified Data
                </span>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="bg-light text-secondary x-small text-uppercase">
                            <tr>
                                <th class="ps-4 py-3 border-0">Operator Name</th>
                                <th class="text-center border-0">Assigned Tasks</th>
                                <th class="text-center border-0">Completed</th>
                                <th class="text-center border-0">Pending</th>
                                <th class="text-center border-0">Overdue</th>
                                <th class="text-center border-0">Completion Rate</th>
                                <th class="text-end pe-4 border-0">Revenue Managed</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($tasksByOperator as $item)
                                <tr>
                                    <td class="ps-4">
                                        <div class="d-flex align-items-center py-1">
                                            <div class="avatar-circle bg-danger text-white me-3">
                                                {{ strtoupper(substr($item['operator']->name ?? 'U', 0, 1)) }}
                                            </div>
                                            <div>
                                                <div class="fw-bold text-dark fs-7">
                                                    {{ $item['operator']->name ?? 'Unassigned' }}
                                                </div>
                                                <div class="x-small text-muted">
                                                    {{ ucfirst($item['operator']->role ?? 'Staff') }}
                                                </div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="text-center small fw-semibold text-dark">{{ number_format($item['assigned']) }}
                                    </td>
                                    <td class="text-center small">
                                        <span
                                            class="badge bg-success-subtle text-success border border-success-subtle px-3 py-1.5 rounded-pill fw-bold">
                                            {{ number_format($item['completed']) }}
                                        </span>
                                    </td>
                                    <td class="text-center small fw-semibold text-warning">{{ number_format($item['pending']) }}
                                    </td>
                                    <td class="text-center small fw-semibold text-danger">{{ number_format($item['overdue']) }}
                                    </td>
                                    <td class="text-center">
                                        <div class="d-inline-block text-center" style="min-width: 110px;">
                                            <span
                                                class="fw-bold {{ $item['efficiency'] >= 80 ? 'text-success' : ($item['efficiency'] >= 50 ? 'text-warning' : 'text-danger') }} small">
                                                {{ number_format($item['efficiency'], 1) }}%
                                            </span>
                                            <div class="progress-micro mt-1">
                                                <div class="progress-bar {{ $item['efficiency'] >= 80 ? 'bg-success' : ($item['efficiency'] >= 50 ? 'bg-warning' : 'bg-danger') }}"
                                                    style="width: {{ min(100, $item['efficiency']) }}%;"></div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="text-end pe-4 small fw-bold text-dark">TZS {{ number_format($item['revenue']) }}
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="text-center py-5 text-muted">
                                        <i class="fas fa-inbox fs-1 d-block mb-2 text-secondary opacity-50"></i>
                                        No operator performance activity recorded for this period.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Detailed Task Breakdown Table (Verification Audit) -->
        <div class="op-card mb-4">
            <div class="card-header bg-white py-3 border-0 d-flex justify-content-between align-items-center">
                <h6 class="m-0 fw-bold text-dark"><i class="fas fa-list-check text-danger me-2"></i>Detailed System Task
                    Verification List</h6>
                <span
                    class="badge bg-secondary-subtle text-secondary border px-3 py-1.5 rounded-pill small fw-semibold">{{ number_format($tasks->total()) }}
                    Total Tasks</span>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0 small">
                        <thead class="bg-dark text-white">
                            <tr>
                                <th class="ps-4 py-3">Task Code / Title</th>
                                <th>Customer</th>
                                <th>Operator</th>
                                <th>Branch / Department</th>
                                <th>Assigned Date</th>
                                <th>Completion / Deadline</th>
                                <th class="pe-4 text-end">Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($tasks as $task)
                                @php
                                    $isCompleted = in_array($task->status, [
                                        \App\Models\DesignTask::STATUS_COMPLETED,
                                        \App\Models\DesignTask::STATUS_CONFIRMED,
                                        \App\Models\DesignTask::STATUS_PRINTED,
                                        \App\Models\DesignTask::STATUS_SUPER_COMPLETED,
                                        \App\Models\DesignTask::STATUS_DELIVERED
                                    ]);
                                    $isOverdue = $task->deadline && $task->deadline->lt(now()) && !$isCompleted && $task->status !== \App\Models\DesignTask::STATUS_CANCELLED;
                                @endphp
                                <tr>
                                    <td class="ps-4">
                                        <div class="fw-bold text-dark fs-7">{{ $task->title }}</div>
                                        <code class="x-small text-muted">{{ $task->task_code ?? "TASK-{$task->id}" }}</code>
                                    </td>
                                    <td>
                                        <div class="fw-semibold text-dark">{{ $task->customer->name ?? 'N/A' }}</div>
                                        <div class="x-small text-muted">{{ $task->customer->company_name ?? '' }}</div>
                                    </td>
                                    <td>
                                        <span
                                            class="badge bg-light text-dark border px-2.5 py-1">{{ $task->operator->name ?? 'Unassigned' }}</span>
                                    </td>
                                    <td class="text-secondary">{{ $task->department->name ?? 'General' }}</td>
                                    <td class="text-secondary">{{ $task->created_at->format('d M Y, H:i') }}</td>
                                    <td>
                                        @if($isCompleted && $task->completed_at)
                                            <span class="text-success fw-semibold"><i class="fas fa-check-circle me-1"></i>Completed
                                                {{ $task->completed_at->format('d M Y') }}</span>
                                        @elseif($task->deadline)
                                            <span class="{{ $isOverdue ? 'text-danger fw-bold' : 'text-muted' }}">
                                                {{ $task->deadline->format('d M Y, H:i') }}
                                                @if($isOverdue) <span class="badge bg-danger ms-1">Overdue</span> @endif
                                            </span>
                                        @else
                                            <span class="text-muted">—</span>
                                        @endif
                                    </td>
                                    <td class="pe-4 text-end">
                                        @if($isCompleted)
                                            <span
                                                class="badge bg-success-subtle text-success border border-success-subtle px-3 py-1.5 rounded-pill fw-semibold">
                                                <i class="fas fa-check-double me-1"></i>Completed
                                            </span>
                                        @elseif($task->status === 'printing')
                                            <span
                                                class="badge bg-warning-subtle text-warning border border-warning-subtle px-3 py-1.5 rounded-pill fw-semibold">
                                                <i class="fas fa-print me-1"></i>Printing
                                            </span>
                                        @elseif($task->status === 'in_progress')
                                            <span
                                                class="badge bg-info-subtle text-info border border-info-subtle px-3 py-1.5 rounded-pill fw-semibold">
                                                <i class="fas fa-spinner fa-spin me-1"></i>In Progress
                                            </span>
                                        @elseif($task->status === 'cancelled')
                                            <span
                                                class="badge bg-secondary-subtle text-secondary px-3 py-1.5 rounded-pill fw-semibold">
                                                Cancelled
                                            </span>
                                        @else
                                            <span
                                                class="badge bg-warning-subtle text-dark border px-3 py-1.5 rounded-pill fw-semibold">
                                                {{ ucfirst(str_replace('_', ' ', $task->status)) }}
                                            </span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="text-center py-5 text-muted">
                                        <i class="fas fa-inbox fs-1 d-block mb-2 text-secondary opacity-50"></i>
                                        No detailed task records found for the selected criteria.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                @if($tasks->hasPages())
                    <div class="px-4 py-3 border-top bg-white no-print">
                        {{ $tasks->links('pagination::bootstrap-5') }}
                    </div>
                @endif
            </div>
        </div>

        <!-- Production Trend & Status Distribution Charts -->
        <div class="row g-4 mb-4">
            <div class="col-lg-8">
                <div class="op-card p-4 h-100">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <div>
                            <h6 class="fw-bold text-dark mb-0"><i class="fas fa-chart-line text-danger me-2"></i>Daily Task
                                Dispatch & Completion Trend</h6>
                            <p class="text-muted x-small mb-0">Production volume trend over selected date range.</p>
                        </div>
                        <span
                            class="badge bg-danger-subtle text-danger border border-danger-subtle px-3 py-1 rounded-pill x-small fw-bold">Date
                            Range Trend</span>
                    </div>
                    <div style="position: relative; height: 260px; width: 100%;">
                        <canvas id="operatorTrendChart"></canvas>
                    </div>
                </div>
            </div>
            <div class="col-lg-4">
                <div class="op-card p-4 h-100">
                    <div class="mb-3">
                        <h6 class="fw-bold text-dark mb-0"><i class="fas fa-chart-pie text-danger me-2"></i>Operator Task
                            Status Breakdown</h6>
                        <p class="text-muted x-small mb-0">Task delivery and status distribution.</p>
                    </div>
                    <div style="position: relative; height: 240px; width: 100%;"
                        class="d-flex align-items-center justify-content-center">
                        <canvas id="operatorStatusChart"></canvas>
                    </div>
                </div>
            </div>
        </div>

    </div>

    @push('scripts')
        <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                const trendDataRaw = @json($tasksByDate);
                const trendLabels = trendDataRaw.map(d => d.date);
                const totalTrend = trendDataRaw.map(d => d.assigned);
                const completedTrend = trendDataRaw.map(d => d.completed);

                const trendCanvas = document.getElementById('operatorTrendChart');
                if (trendCanvas) {
                    new Chart(trendCanvas.getContext('2d'), {
                        type: 'line',
                        data: {
                            labels: trendLabels,
                            datasets: [{
                                label: 'Tasks Assigned',
                                data: totalTrend,
                                borderColor: '#dc2626',
                                backgroundColor: 'rgba(220, 38, 38, 0.05)',
                                borderWidth: 2.5,
                                fill: true,
                                tension: 0.35
                            }, {
                                label: 'Tasks Completed',
                                data: completedTrend,
                                borderColor: '#10b981',
                                borderWidth: 2.5,
                                tension: 0.35
                            }]
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            plugins: { legend: { position: 'top' } },
                            scales: {
                                x: { grid: { display: false } },
                                y: { ticks: { stepSize: 1 }, beginAtZero: true }
                            }
                        }
                    });
                }

                const statusCanvas = document.getElementById('operatorStatusChart');
                if (statusCanvas) {
                    new Chart(statusCanvas.getContext('2d'), {
                        type: 'doughnut',
                        data: {
                            labels: ['Completed', 'Pending', 'Overdue'],
                            datasets: [{
                                data: [
                                            {{ $summary['total_completed'] }},
                                            {{ $summary['total_pending'] }},
                                    {{ $summary['total_overdue'] }}
                                ],
                                backgroundColor: ['#10b981', '#f59e0b', '#ef4444'],
                                borderWidth: 2,
                                borderColor: '#ffffff'
                            }]
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            plugins: {
                                legend: { position: 'bottom' }
                            },
                            cutout: '70%'
                        }
                    });
                }
            });
        </script>
    @endpush
@endsection