@extends('layouts.admin')
@section('title', 'Sales Performance Report')

@section('content')
<div class="container-fluid py-3">

    {{-- ── Header ── --}}
    <div class="row mb-3 align-items-center">
        <div class="col-lg-7">
            <h4 class="fw-bold mb-0"><i class="fas fa-chart-line text-primary me-2"></i>Sales Performance Report</h4>
            <p class="text-muted small mb-0">
                Period:
                <strong>{{ ucfirst($period) }}</strong>
                — {{ $from->format('d M Y') }} to {{ $to->format('d M Y') }}
                @if($sellerId && $sellers->isNotEmpty())
                    — Seller: <strong>{{ $sellers->firstWhere('id', $sellerId)?->name ?? 'All' }}</strong>
                @endif
            </p>
        </div>
        <div class="col-lg-5 text-lg-end mt-2 mt-lg-0 d-flex flex-wrap justify-content-lg-end gap-2">
            <a href="{{ route('admin.reports.sales.print', request()->all()) }}" target="_blank"
               class="btn btn-dark btn-sm px-3"><i class="fas fa-print me-1"></i>Print</a>
            <a href="{{ route('admin.reports.sales.pdf', request()->all()) }}"
               class="btn btn-danger btn-sm px-3"><i class="fas fa-file-pdf me-1"></i>PDF</a>
            <a href="{{ route('admin.reports.sales.excel', request()->all()) }}"
               class="btn btn-success btn-sm px-3"><i class="fas fa-file-excel me-1"></i>Excel</a>
        </div>
    </div>

    {{-- ── Filters ── --}}
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body bg-light p-3">
            <form action="{{ route('admin.reports.sales') }}" method="GET" class="row g-2">
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
                @if(!$allSeller && $sellers->isNotEmpty())
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
                <div class="col-auto d-flex align-items-end">
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

    {{-- ── KPI Cards ── --}}
    <div class="row g-3 mb-4">
        @php
            $cards = [
                ['label'=>'Total Leads', 'value'=>$data['totalLeads'], 'icon'=>'fas fa-users', 'color'=>'primary'],
                ['label'=>'Converted (Won)', 'value'=>$data['convertedLeads'], 'icon'=>'fas fa-check-circle', 'color'=>'success'],
                ['label'=>'Pending Leads', 'value'=>$data['pendingLeads'], 'icon'=>'fas fa-hourglass-half', 'color'=>'warning'],
                ['label'=>'Follow-Ups Done', 'value'=>$data['followUpsDone'], 'icon'=>'fas fa-phone-alt', 'color'=>'info'],
                ['label'=>'Paid Clients', 'value'=>$data['paidTasks'], 'icon'=>'fas fa-money-check-alt', 'color'=>'success'],
                ['label'=>'Unpaid Clients', 'value'=>$data['unpaidTasks'], 'icon'=>'fas fa-file-invoice', 'color'=>'danger'],
                ['label'=>'New Customers', 'value'=>$data['newCustomerCount'], 'icon'=>'fas fa-user-plus', 'color'=>'primary'],
                ['label'=>'Repeated Customers', 'value'=>$data['repCustomerCount'], 'icon'=>'fas fa-redo', 'color'=>'info'],
            ];
        @endphp
        @foreach($cards as $c)
        <div class="col-6 col-md-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body d-flex align-items-center py-3">
                    <div class="rounded-circle bg-{{ $c['color'] }} bg-opacity-10 p-3 me-3">
                        <i class="{{ $c['icon'] }} text-{{ $c['color'] }}"></i>
                    </div>
                    <div>
                        <div class="fs-4 fw-bold">{{ number_format($c['value']) }}</div>
                        <div class="x-small text-muted fw-semibold">{{ $c['label'] }}</div>
                    </div>
                </div>
            </div>
        </div>
        @endforeach
    </div>

    {{-- ── Revenue summary ── --}}
    <div class="row g-3 mb-4">
        <div class="col-md-4">
            <div class="card border-0 shadow-sm h-100 border-top border-4 border-success">
                <div class="card-body text-center py-4">
                    <i class="fas fa-coins fa-2x text-success mb-2"></i>
                    <div class="fs-3 fw-bold text-success">TZS {{ number_format($data['totalRevenue']) }}</div>
                    <div class="text-muted small">Total Revenue Collected</div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-0 shadow-sm h-100 border-top border-4 border-primary">
                <div class="card-body text-center py-4">
                    <i class="fas fa-user-plus fa-2x text-primary mb-2"></i>
                    <div class="fs-3 fw-bold text-primary">TZS {{ number_format($data['newRevenue']) }}</div>
                    <div class="text-muted small">New Customer Revenue</div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-0 shadow-sm h-100 border-top border-4 border-info">
                <div class="card-body text-center py-4">
                    <i class="fas fa-redo fa-2x text-info mb-2"></i>
                    <div class="fs-3 fw-bold text-info">TZS {{ number_format($data['repRevenue']) }}</div>
                    <div class="text-muted small">Repeated Customer Revenue</div>
                </div>
            </div>
        </div>
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
                            $srcLabels = [
                                'promo' => 'Promo', 'instagram' => 'Instagram',
                                'follow_up' => 'Follow-Up', 'referral' => 'Referral',
                                'walk_in' => 'Walk-In', 'whatsapp' => 'WhatsApp', 'other' => 'Other'
                            ];
                            $srcIcons = [
                                'promo' => 'fas fa-bullhorn text-warning',
                                'instagram' => 'fab fa-instagram text-danger',
                                'follow_up' => 'fas fa-phone text-primary',
                                'referral' => 'fas fa-user-friends text-info',
                                'walk_in' => 'fas fa-walking text-success',
                                'whatsapp' => 'fab fa-whatsapp text-success',
                                'other' => 'fas fa-tag text-secondary',
                            ];
                        @endphp
                        <tr>
                            <td>
                                <i class="{{ $srcIcons[$src] ?? 'fas fa-tag text-secondary' }} me-2"></i>
                                <strong>{{ $srcLabels[$src] ?? ucfirst($src) }}</strong>
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
