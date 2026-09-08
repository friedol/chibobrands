@extends('layouts.admin')
@section('title', 'Leads Report')

@section('content')
<div class="container-fluid py-3">

    {{-- ── Header ── --}}
    <div class="row mb-3 align-items-center">
        <div class="col-lg-7">
            <h4 class="fw-bold mb-0"><i class="fas fa-users text-primary me-2"></i>Leads Report</h4>
            <p class="text-muted small mb-0">
                Period: <strong>{{ ucfirst($period) }}</strong>
                — {{ $from->format('d M Y') }} to {{ $to->format('d M Y') }}
                @if($sellerId && $sellers->isNotEmpty())
                    &nbsp;&middot;&nbsp; Seller: <strong>{{ $sellers->firstWhere('id', $sellerId)?->name ?? 'All' }}</strong>
                @else
                    &nbsp;&middot;&nbsp; <span class="text-muted">All Sellers</span>
                @endif
            </p>
        </div>
        <div class="col-lg-5 text-lg-end mt-2 mt-lg-0 d-flex flex-wrap justify-content-lg-end gap-2">
            <x-report-export-menu
                :print-url="route('admin.reports.sales.print', request()->all())"
                :pdf-url="route('admin.reports.sales.pdf', request()->all())"
                :excel-url="route('admin.reports.sales.excel', request()->all())"
                label="Export"
            />
        </div>
    </div>

    {{-- ── Filters ── --}}
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body bg-light p-3">
            <form action="{{ route('admin.reports.sales') }}" method="GET" class="row g-2 align-items-end">
                <div class="col-6 col-md-2">
                    <label class="form-label fw-bold x-small text-uppercase mb-1">Period</label>
                    <select name="period" class="form-select form-select-sm" onchange="toggleCustom(this)">
                        @foreach(['today'=>'Today','yesterday'=>'Yesterday','week'=>'This Week','month'=>'This Month','year'=>'This Year','custom'=>'Custom Range'] as $val => $label)
                            <option value="{{ $val }}" {{ $period === $val ? 'selected' : '' }}>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-6 col-md-2 custom-range" style="{{ $period === 'custom' ? '' : 'display:none' }}">
                    <label class="form-label fw-bold x-small text-uppercase mb-1">From</label>
                    <input type="date" name="date_from" class="form-control form-control-sm" value="{{ request('date_from', $from->format('Y-m-d')) }}">
                </div>
                <div class="col-6 col-md-2 custom-range" style="{{ $period === 'custom' ? '' : 'display:none' }}">
                    <label class="form-label fw-bold x-small text-uppercase mb-1">To</label>
                    <input type="date" name="date_to" class="form-control form-control-sm" value="{{ request('date_to', $to->format('Y-m-d')) }}">
                </div>
                @if($sellers->isNotEmpty())
                <div class="col-6 col-md-3">
                    <label class="form-label fw-bold x-small text-uppercase mb-1">Seller</label>
                    <select name="saler_id" class="form-select form-select-sm">
                        <option value="">All Sellers</option>
                        @foreach($sellers as $s)
                            <option value="{{ $s->id }}" {{ $sellerId == $s->id ? 'selected' : '' }}>{{ $s->name }}</option>
                        @endforeach
                    </select>
                </div>
                @endif
                <div class="col-auto">
                    <button type="submit" class="btn btn-primary btn-sm px-3">Apply</button>
                </div>
            </form>
        </div>
    </div>

    {{-- ── Target vs Actual ── --}}
    @if($target)
    <div class="card border-0 shadow-sm mb-4 border-top border-4 border-primary">
        <div class="card-body">
            <div class="row align-items-center">
                <div class="col-md-4">
                    <div class="fw-bold text-uppercase small text-muted mb-1">Monthly Target</div>
                    <div class="fs-4 fw-bold">TZS {{ number_format($target->target_amount) }}</div>
                </div>
                <div class="col-md-4">
                    <div class="fw-bold text-uppercase small text-muted mb-1">Achieved</div>
                    <div class="fs-4 fw-bold text-success">TZS {{ number_format($data['totalRevenue']) }}</div>
                </div>
                <div class="col-md-4">
                    @php
                        $pct = $target->target_amount > 0
                            ? min(100, round(($data['totalRevenue'] / $target->target_amount) * 100, 1))
                            : 0;
                        $pctColor = $pct >= 100 ? 'success' : ($pct >= 70 ? 'warning' : 'danger');
                    @endphp
                    <div class="fw-bold text-uppercase small text-muted mb-1">Performance</div>
                    <div class="progress" style="height:14px">
                        <div class="progress-bar bg-{{ $pctColor }}" style="width:{{ $pct }}%">{{ $pct }}%</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endif

    {{-- ── KPI Cards (12 cards, 6 per row) ── --}}
    <div class="row g-2 g-md-3 mb-4">
        @php
            $cards = [
                ['label'=>'Total Leads',          'value'=>number_format($data['totalLeads']),         'icon'=>'fas fa-users',           'color'=>'primary', 'sub'=>'Volume'],
                ['label'=>'Converted (Won)',       'value'=>number_format($data['convertedLeads']),     'icon'=>'fas fa-check-circle',    'color'=>'success', 'sub'=>'Won'],
                ['label'=>'Pending Leads',         'value'=>number_format($data['pendingLeads']),       'icon'=>'fas fa-hourglass-half',  'color'=>'warning', 'sub'=>'In Progress'],
                ['label'=>'Not Interested',        'value'=>number_format($data['notInterested']),      'icon'=>'fas fa-ban',             'color'=>'secondary','sub'=>'Lost'],
                ['label'=>'Follow-Ups Done',       'value'=>number_format($data['followUpsDone']),      'icon'=>'fas fa-phone-alt',       'color'=>'info',    'sub'=>'Contacted'],
                ['label'=>'Paid Clients',          'value'=>number_format($data['paidTasks']),          'icon'=>'fas fa-money-check-alt', 'color'=>'success', 'sub'=>'Cleared'],
                ['label'=>'Unpaid Clients',        'value'=>number_format($data['unpaidTasks']),        'icon'=>'fas fa-file-invoice',    'color'=>'danger',  'sub'=>'Balance Due'],
                ['label'=>'New Customers',         'value'=>number_format($data['newCustomerCount']),   'icon'=>'fas fa-user-plus',       'color'=>'primary', 'sub'=>'First-time'],
                ['label'=>'Repeated Customers',    'value'=>number_format($data['repCustomerCount']),   'icon'=>'fas fa-redo',            'color'=>'info',    'sub'=>'Returning'],
                ['label'=>'Total Revenue',         'value'=>'TZS '.number_format($data['totalRevenue']),'icon'=>'fas fa-coins',          'color'=>'success', 'sub'=>'Grand Total'],
                ['label'=>'New Customer Revenue',  'value'=>'TZS '.number_format($data['newRevenue']),  'icon'=>'fas fa-user-plus',      'color'=>'primary', 'sub'=>'New Clients'],
                ['label'=>'Returning Revenue',     'value'=>'TZS '.number_format($data['repRevenue']),  'icon'=>'fas fa-redo',           'color'=>'info',    'sub'=>'Returning'],
            ];
        @endphp
        @foreach($cards as $c)
        <div class="col-12 col-sm-6 col-md-2">
            <div class="cust-stat-card">
                <div class="d-flex justify-content-between align-items-center">
                    <div class="cust-stat-icon bg-{{ $c['color'] }}-subtle text-{{ $c['color'] }}">
                        <i class="{{ $c['icon'] }}"></i>
                    </div>
                    <span class="cust-stat-sub text-{{ $c['color'] }} fw-semibold">{{ $c['sub'] }}</span>
                </div>
                <div class="cust-stat-val text-{{ $c['color'] === 'primary' ? 'dark' : $c['color'] }}" style="font-size:1.1rem;">{{ $c['value'] }}</div>
                <div class="cust-stat-lbl">{{ $c['label'] }}</div>
            </div>
        </div>
        @endforeach
    </div>

    {{-- ── Lead Source Breakdown ── --}}
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-header bg-white border-bottom py-2 fw-bold">
            <i class="fas fa-funnel-dollar text-primary me-2"></i>Lead Source Breakdown
        </div>
        <div class="card-body p-0">
            @if(empty($data['sourceStats']))
                <p class="text-muted text-center py-4">No lead source data for this period.</p>
            @else
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0 small">
                    <thead class="table-light">
                        <tr>
                            <th>Lead Source</th>
                            <th class="text-center">Total Leads</th>
                            <th class="text-center">Paid (Won)</th>
                            <th class="text-center">Unpaid (Pending)</th>
                            <th class="text-end">Revenue (TZS)</th>
                            <th class="text-center">Conversion %</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($data['sourceStats'] as $src => $stat)
                        @php
                            $convRate = $stat['total'] > 0 ? round(($stat['paid'] / $stat['total']) * 100, 1) : 0;
                            $srcLower = strtolower($src);
                            $srcIconMap = [
                                'instagram'       => 'fab fa-instagram text-danger',
                                'tiktok'          => 'fab fa-tiktok text-dark',
                                'whatsapp'        => 'fab fa-whatsapp text-success',
                                'facebook'        => 'fab fa-facebook text-primary',
                                'linkedin'        => 'fab fa-linkedin text-info',
                                'follow up'       => 'fas fa-phone text-primary',
                                'inside programs' => 'fas fa-layer-group text-warning',
                                'repeated customer' => 'fas fa-redo text-info',
                                'bulk sms'        => 'fas fa-sms text-purple',
                                'referral'        => 'fas fa-user-friends text-info',
                                'walk-in'         => 'fas fa-walking text-success',
                                'website'         => 'fas fa-globe text-secondary',
                                'google search'   => 'fab fa-google text-warning',
                                'exhibition'      => 'fas fa-store text-purple',
                                'livaro'          => 'fas fa-tag text-primary',
                                'other'           => 'fas fa-tag text-secondary',
                            ];
                            $icon = $srcIconMap[$srcLower] ?? 'fas fa-tag text-secondary';
                            $hasSub = (!empty($stat['campaigns']) && count($stat['campaigns']) > 0)
                                   || (!empty($stat['programs'])  && count($stat['programs'])  > 0);
                            $rowId = 'src-' . \Illuminate\Support\Str::slug($src);
                        @endphp
                        <tr class="{{ $hasSub ? 'cursor-pointer' : '' }}" {{ $hasSub ? 'onclick=document.getElementById(\'' . $rowId . '\').classList.toggle(\'d-none\')' : '' }}>
                            <td>
                                <i class="{{ $icon }} me-2"></i>
                                <strong>{{ $src }}</strong>
                                @if($hasSub) <i class="fas fa-chevron-down ms-1 small text-muted"></i> @endif
                            </td>
                            <td class="text-center fw-bold">{{ $stat['total'] }}</td>
                            <td class="text-center text-success fw-semibold">{{ $stat['paid'] }}</td>
                            <td class="text-center text-danger fw-semibold">{{ $stat['unpaid'] }}</td>
                            <td class="text-end fw-bold">{{ number_format($stat['revenue']) }}</td>
                            <td class="text-center">
                                <div class="d-flex align-items-center gap-1">
                                    <div class="progress flex-grow-1" style="height:8px">
                                        <div class="progress-bar bg-{{ $convRate >= 60 ? 'success' : ($convRate >= 30 ? 'warning' : 'danger') }}"
                                             style="width:{{ $convRate }}%"></div>
                                    </div>
                                    <small>{{ $convRate }}%</small>
                                </div>
                            </td>
                        </tr>
                        @if($hasSub)
                        <tr id="{{ $rowId }}" class="d-none">
                            <td colspan="6" class="p-0">
                                <div class="px-4 py-2 bg-light border-bottom">
                                    @if(!empty($stat['campaigns']))
                                        <div class="small fw-semibold text-muted mb-1">Campaigns:</div>
                                        <ul class="list-unstyled mb-0 small">
                                            @foreach($stat['campaigns'] as $c)
                                                <li><i class="fas fa-flag text-danger me-1"></i>{{ $c['name'] }} — <strong>{{ $c['total'] }}</strong> leads</li>
                                            @endforeach
                                        </ul>
                                    @endif
                                    @if(!empty($stat['programs']))
                                        <div class="small fw-semibold text-muted mb-1 {{ !empty($stat['campaigns']) ? 'mt-2' : '' }}">Programs:</div>
                                        <ul class="list-unstyled mb-0 small">
                                            @foreach($stat['programs'] as $p)
                                                <li><i class="fas fa-layer-group text-warning me-1"></i>{{ $p['name'] }} — <strong>{{ $p['total'] }}</strong> leads</li>
                                            @endforeach
                                        </ul>
                                    @endif
                                </div>
                            </td>
                        </tr>
                        @endif
                        @endforeach
                    </tbody>
                    <tfoot class="table-light fw-bold">
                        <tr>
                            <td>TOTAL</td>
                            <td class="text-center">{{ array_sum(array_column($data['sourceStats'], 'total')) }}</td>
                            <td class="text-center text-success">{{ array_sum(array_column($data['sourceStats'], 'paid')) }}</td>
                            <td class="text-center text-danger">{{ array_sum(array_column($data['sourceStats'], 'unpaid')) }}</td>
                            <td class="text-end">{{ number_format(array_sum(array_column($data['sourceStats'], 'revenue'))) }}</td>
                            <td></td>
                        </tr>
                    </tfoot>
                </table>
            </div>
            @endif
        </div>
    </div>

    {{-- ── Seller Ranking (Admin only) ── --}}
    @if(!empty($ranking))
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-header bg-white border-bottom py-2 fw-bold">
            <i class="fas fa-trophy text-warning me-2"></i>Seller Rankings
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0 small">
                    <thead class="table-light">
                        <tr>
                            <th>#</th>
                            <th>Seller</th>
                            <th class="text-end">Revenue (TZS)</th>
                            <th class="text-center">Leads</th>
                            <th class="text-center">Converted</th>
                            <th class="text-center">Conversion Rate</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($ranking as $rank => $seller)
                        <tr>
                            <td>
                                @if($rank === 0) <i class="fas fa-trophy text-warning fa-lg"></i>
                                @elseif($rank === 1) <i class="fas fa-medal text-secondary fa-lg"></i>
                                @elseif($rank === 2) <i class="fas fa-medal text-danger fa-lg"></i>
                                @else <span class="text-muted fw-bold">{{ $rank + 1 }}</span>
                                @endif
                            </td>
                            <td class="fw-semibold">{{ $seller['name'] }}</td>
                            <td class="text-end fw-bold text-success">{{ number_format($seller['revenue']) }}</td>
                            <td class="text-center">{{ $seller['leads'] }}</td>
                            <td class="text-center text-success">{{ $seller['converted'] }}</td>
                            <td class="text-center">
                                <span class="badge bg-{{ $seller['rate'] >= 60 ? 'success' : ($seller['rate'] >= 30 ? 'warning' : 'danger') }}">
                                    {{ $seller['rate'] }}%
                                </span>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    @endif

    {{-- ── Leads Registered ── --}}
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-header bg-white border-bottom py-3 d-flex justify-content-between align-items-center">
            <h6 class="m-0 fw-bold text-dark">
                <i class="fas fa-users text-primary me-2"></i>Leads Registered
            </h6>
            <span class="badge bg-primary bg-opacity-10 text-primary fw-bold px-3 py-2 rounded-pill small">
                {{ count($data['registeredLeadsList']) }} Registered
            </span>
        </div>
        <div class="card-body p-0">
            @if($data['registeredLeadsList']->isEmpty())
                <p class="text-muted text-center py-5 mb-0">
                    <i class="fas fa-user-slash fa-2x mb-2 text-muted opacity-50 d-block"></i>
                    No leads registered during this period.
                </p>
            @else
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0 small">
                    <thead class="table-light">
                        <tr>
                            <th class="ps-4">Date Registered</th>
                            <th>Lead Name</th>
                            <th>Phone</th>
                            <th>Source</th>
                            <th>Interest Level</th>
                            <th>Status</th>
                            @if($allSeller)
                            <th>Seller</th>
                            @endif
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($data['registeredLeadsList'] as $lead)
                        <tr>
                            <td class="ps-4 text-muted">
                                {{ $lead->created_at->format('d M Y, h:i A') }}
                            </td>
                            <td><strong class="text-dark">{{ $lead->customer_name }}</strong></td>
                            <td>{{ $lead->phone }}</td>
                            <td>
                                <span class="badge bg-secondary bg-opacity-10 text-secondary rounded-pill px-2.5 py-1">
                                    {{ ucfirst(str_replace('_', ' ', $lead->source)) }}
                                </span>
                            </td>
                            <td>
                                @php
                                    $interestColors = ['hot' => 'danger', 'warm' => 'warning', 'cold' => 'info'];
                                    $color = $interestColors[strtolower($lead->interest_level)] ?? 'secondary';
                                @endphp
                                <span class="badge bg-{{ $color }} bg-opacity-10 text-{{ $color }} rounded-pill px-2.5 py-1">
                                    {{ ucfirst($lead->interest_level ?: 'unknown') }}
                                </span>
                            </td>
                            <td>
                                @php
                                    $statusColors = ['converted' => 'success', 'pending' => 'warning', 'not_interested' => 'danger'];
                                    $sColor = $statusColors[strtolower($lead->status)] ?? 'secondary';
                                    $sLabel = $lead->status === 'converted' ? 'Won' : ($lead->status === 'not_interested' ? 'Lost' : ucfirst($lead->status));
                                @endphp
                                <span class="badge bg-{{ $sColor }} bg-opacity-10 text-{{ $sColor }} rounded-pill px-2.5 py-1">
                                    {{ $sLabel }}
                                </span>
                            </td>
                            @if($allSeller)
                            <td>
                                <span class="badge bg-dark bg-opacity-10 text-dark px-2.5 py-1.5 rounded-pill fw-semibold">{{ $lead->seller?->name ?? '—' }}</span>
                            </td>
                            @endif
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @endif
        </div>
    </div>

    {{-- ── Paid Clients & Tasks ── --}}
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-header bg-white border-bottom py-3 d-flex justify-content-between align-items-center">
            <h6 class="m-0 fw-bold text-dark">
                <i class="fas fa-money-check-alt text-success me-2"></i>Paid Clients & Tasks
            </h6>
            <span class="badge bg-success bg-opacity-10 text-success fw-bold px-3 py-2 rounded-pill small">
                {{ count($data['paidClientsList']) }} Paid Tasks
            </span>
        </div>
        <div class="card-body p-0">
            @if($data['paidClientsList']->isEmpty())
                <p class="text-muted text-center py-5 mb-0">
                    <i class="fas fa-file-invoice-dollar fa-2x mb-2 text-muted opacity-50 d-block"></i>
                    No paid tasks recorded for this period.
                </p>
            @else
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0 small">
                    <thead class="table-light">
                        <tr>
                            <th class="ps-4">Task Date</th>
                            <th>Customer Name</th>
                            <th>Task Code</th>
                            <th>Department</th>
                            <th>Design Details</th>
                            <th class="text-end">Price</th>
                            @if($allSeller)
                            <th>Seller</th>
                            @endif
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($data['paidClientsList'] as $task)
                        <tr>
                            <td class="ps-4 text-muted">
                                {{ $task->created_at->format('d M Y, h:i A') }}
                            </td>
                            <td><strong class="text-dark">{{ $task->customer?->name ?? 'Guest' }}</strong></td>
                            <td><code class="text-primary fw-bold">{{ $task->task_code }}</code></td>
                            <td>
                                <span class="badge bg-info bg-opacity-10 text-info rounded-pill px-2.5 py-1">
                                    {{ $task->department?->name ?? 'POS' }}
                                </span>
                            </td>
                            <td>
                                <div class="text-dark fw-medium">{{ $task->title }}</div>
                                <div class="x-small text-muted">{{ $task->size }} | {{ $task->quantity }} pcs</div>
                            </td>
                            <td class="text-end fw-bold text-success pe-4">
                                TZS {{ number_format($task->price) }}
                            </td>
                            @if($allSeller)
                            <td>
                                <span class="badge bg-dark bg-opacity-10 text-dark px-2.5 py-1.5 rounded-pill fw-semibold">{{ $task->saler?->name ?? '—' }}</span>
                            </td>
                            @endif
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @endif
        </div>
    </div>

    {{-- ── Activities Done & Comments ── --}}
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-header bg-white border-bottom py-3 d-flex justify-content-between align-items-center">
            <h6 class="m-0 fw-bold text-dark">
                <i class="fas fa-history text-info me-2"></i>Activities Done & Comments
            </h6>
            <span class="badge bg-info bg-opacity-10 text-info fw-bold px-3 py-2 rounded-pill small">
                {{ count($data['activities']) }} Total Actions
            </span>
        </div>
        <div class="card-body p-0">
            @if(empty($data['activities']))
                <p class="text-muted text-center py-5 mb-0">
                    <i class="fas fa-clipboard-list fa-2x mb-2 text-muted opacity-50 d-block"></i>
                    No follow-up activity found for this period.
                </p>
            @else
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0 small">
                    <thead class="table-light">
                        <tr>
                            <th class="ps-4">Date & Time</th>
                            <th>Activity Type</th>
                            <th>Contact / Client</th>
                            <th>Phone</th>
                            <th>Channel</th>
                            <th>Comments / Notes</th>
                            @if($allSeller)
                            <th>Seller</th>
                            @endif
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($data['activities'] as $act)
                        <tr>
                            <td class="ps-4 text-muted">
                                {{ \Carbon\Carbon::parse($act['date'])->format('d M Y, h:i A') }}
                            </td>
                            <td>
                                @if($act['type'] === 'Lead Follow-Up')
                                    <span class="badge bg-primary bg-opacity-10 text-primary px-3 py-1.5 rounded-pill fw-semibold">
                                        <i class="fas fa-user-tag me-1"></i>{{ $act['type'] }}
                                    </span>
                                @else
                                    <span class="badge bg-success bg-opacity-10 text-success px-3 py-1.5 rounded-pill fw-semibold">
                                        <i class="fas fa-user-check me-1"></i>{{ $act['type'] }}
                                    </span>
                                @endif
                            </td>
                            <td>
                                <strong class="text-dark">{{ $act['contact_name'] }}</strong>
                            </td>
                            <td>
                                <a href="tel:{{ $act['phone'] }}" class="text-decoration-none text-muted">
                                    <i class="fas fa-phone-alt me-1 text-muted opacity-50"></i>{{ $act['phone'] }}
                                </a>
                            </td>
                            <td>
                                @if($act['channel'] === 'WhatsApp')
                                    <span class="badge bg-success bg-opacity-10 text-success px-2.5 py-1.5 rounded-pill"><i class="fab fa-whatsapp me-1"></i>WhatsApp</span>
                                @elseif($act['channel'] === 'Phone Call')
                                    <span class="badge bg-primary bg-opacity-10 text-primary px-2.5 py-1.5 rounded-pill"><i class="fas fa-phone me-1"></i>Phone Call</span>
                                @elseif($act['channel'] === 'Email')
                                    <span class="badge bg-warning bg-opacity-10 text-warning px-2.5 py-1.5 rounded-pill"><i class="fas fa-envelope me-1"></i>Email</span>
                                @elseif($act['channel'] === 'In Person')
                                    <span class="badge bg-info bg-opacity-10 text-info px-2.5 py-1.5 rounded-pill"><i class="fas fa-users me-1"></i>In Person</span>
                                @else
                                    <span class="badge bg-secondary bg-opacity-10 text-secondary px-2.5 py-1.5 rounded-pill">{{ $act['channel'] }}</span>
                                @endif
                            </td>
                            <td class="text-wrap" style="max-width: 300px;">
                                <div class="text-dark fw-medium">{{ $act['notes'] ?: '—' }}</div>
                            </td>
                            @if($allSeller)
                            <td>
                                <span class="badge bg-dark bg-opacity-10 text-dark px-2.5 py-1.5 rounded-pill fw-semibold">{{ $act['seller_name'] }}</span>
                            </td>
                            @endif
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @endif
        </div>
    </div>

</div>
@endsection

@push('scripts')
<script>
function toggleCustom(sel) {
    document.querySelectorAll('.custom-range').forEach(el => {
        el.style.display = sel.value === 'custom' ? '' : 'none';
    });
}
</script>
@endpush
