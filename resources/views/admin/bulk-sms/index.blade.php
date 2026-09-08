@extends('layouts.admin')

@section('page-title', 'Bulk SMS & Auto Messaging')

@push('styles')
<style>
/* ── RESET & BASE ─────────────────────────────────────────────────── */
.bsms-page { background:#f1f5f9; min-height:calc(100vh - 64px); }

/* ── TOP HEADER BAR ──────────────────────────────────────────────── */
.bsms-topbar {
    background: linear-gradient(135deg, #eff6ff 0%, #dbeafe 100%);
    border-bottom: 1px solid #bfdbfe;
    padding: 20px 12px;
    margin-bottom: 0;
    display: flex;
    align-items: center;
    justify-content: space-between;
    flex-wrap: wrap;
    gap: 16px;
}
.bsms-topbar-title { color:#1e3a8a; font-size:1.25rem; font-weight:800; letter-spacing:-.01em; }
.bsms-topbar-sub { color:#3b82f6; font-size:.82rem; margin-top:2px; }
.bsms-kpi-strip { display:flex; gap:12px; flex-wrap:wrap; }
.bsms-kpi {
    background: #fff;
    border: 1px solid #bfdbfe;
    border-radius: 10px;
    padding: 8px 16px;
    text-align:center;
    min-width: 90px;
    box-shadow: 0 1px 4px rgba(59,130,246,.08);
}
.bsms-kpi-label { color:#6b7280; font-size:.72rem; font-weight:600; text-transform:uppercase; letter-spacing:.06em; }
.bsms-kpi-value { color:#1e3a8a; font-size:1.3rem; font-weight:900; line-height:1.1; }
.bsms-kpi-value.red { color:#dc2626; }
.bsms-kpi-value.grn { color:#15803d; }
.bsms-kpi-balance {
    border-color: #fcd34d;
    background: #fffbeb;
    min-width: 110px;
    cursor: pointer;
    position: relative;
}
.bsms-kpi-balance:hover { background: #fef3c7; }

/* ── TAB NAV ─────────────────────────────────────────────────────── */
.bsms-tabnav {
    background:#fff;
    border-bottom: 1px solid #e2e8f0;
    padding: 0 12px;
    display: flex;
    gap: 0;
}
.bsms-tablink {
    padding: 14px 22px;
    font-size:.87rem;
    font-weight:700;
    color:#64748b;
    border:none;
    background:none;
    border-bottom: 3px solid transparent;
    cursor:pointer;
    transition: all .15s;
    display:flex;
    align-items:center;
    gap:7px;
    white-space:nowrap;
}
.bsms-tablink:hover { color:#0f172a; }
.bsms-tablink.active { color:#dc2626; border-bottom-color:#dc2626; }
.bsms-tablink .tab-icon { font-size:.9rem; }

/* ── TAB CONTENT ─────────────────────────────────────────────────── */
.bsms-tab-content { padding: 24px 12px; display:none; }
.bsms-tab-content.active { display:block; }

/* ── CARDS ───────────────────────────────────────────────────────── */
.bsms-card {
    background:#fff;
    border: 1px solid #e2e8f0;
    border-radius: 14px;
    box-shadow: 0 1px 4px rgba(0,0,0,.04);
}
.bsms-card-head {
    padding: 16px 20px;
    border-bottom: 1px solid #f1f5f9;
    display:flex;
    align-items:center;
    justify-content: space-between;
    gap:10px;
}
.bsms-card-head h6 {
    font-size:.9rem;
    font-weight:800;
    color:#0f172a;
    margin:0;
    display:flex;
    align-items:center;
    gap:8px;
}
.bsms-card-body { padding: 20px; }

/* ── STEP BADGE ──────────────────────────────────────────────────── */
.step-num {
    width: 24px; height: 24px;
    background: #dc2626;
    color:#fff;
    border-radius:50%;
    font-size:.75rem;
    font-weight:800;
    display:inline-flex;
    align-items:center;
    justify-content:center;
    flex-shrink:0;
}

/* ── SELECTION PILLS ─────────────────────────────────────────────── */
.sel-grid { display:grid; grid-template-columns: repeat(3, 1fr); gap:8px; margin-bottom:16px; }
.sel-btn {
    padding: 10px 6px;
    border: 1.5px solid #e2e8f0;
    border-radius:10px;
    background:#fafafa;
    cursor:pointer;
    text-align:center;
    transition: all .15s;
    font-size:.8rem;
    font-weight:700;
    color:#64748b;
}
.sel-btn:hover { border-color:#dc2626; color:#dc2626; background:#fff7f7; }
.sel-btn.active { border-color:#dc2626; background:#fff1f2; color:#dc2626; }
.sel-btn i { display:block; font-size:1.1rem; margin-bottom:4px; }
.sel-btn.grn { border-color:#e2e8f0; }
.sel-btn.grn.active { border-color:#16a34a; background:#f0fdf4; color:#16a34a; }
.sel-btn.blu { border-color:#e2e8f0; }
.sel-btn.blu.active { border-color:#0ea5e9; background:#f0f9ff; color:#0ea5e9; }

/* ── SUB FILTER PANEL ─────────────────────────────────────────────── */
.sub-filter-panel {
    border-radius:10px;
    padding:14px;
    margin-bottom:14px;
    display:none;
}
.sub-filter-panel.show { display:block; }
.sub-filter-panel.red-tint { background:#fff7f7; border:1px solid #fecaca; }
.sub-filter-panel.grn-tint { background:#f0fdf4; border:1px solid #bbf7d0; }
.sub-filter-panel.blu-tint { background:#f0f9ff; border:1px solid #bae6fd; }

/* ── LIVE COUNT BOX ──────────────────────────────────────────────── */
.live-count-box {
    background: linear-gradient(135deg, #fff1f2 0%, #fff7f7 100%);
    border: 1.5px solid #fecaca;
    border-radius: 12px;
    padding: 16px;
    display:flex;
    align-items:center;
    justify-content:space-between;
}
.live-count-num { font-size:2rem; font-weight:900; color:#dc2626; line-height:1; }
.live-count-label { font-size:.75rem; color:#64748b; font-weight:600; text-transform:uppercase; letter-spacing:.05em; }

/* ── SMS CHAR METER ──────────────────────────────────────────────── */
.char-meter { background:#f8fafc; border:1px solid #e2e8f0; border-radius:10px; padding:12px 14px; }
.char-row { display:flex; justify-content:space-between; font-size:.8rem; color:#64748b; margin-bottom:4px; }
.char-row:last-child { margin-bottom:0; border-top:1px solid #e2e8f0; padding-top:8px; margin-top:4px; font-weight:700; color:#0f172a; }
.char-bar-wrap { height:4px; background:#e2e8f0; border-radius:4px; margin:6px 0; overflow:hidden; }
.char-bar { height:100%; background:#dc2626; border-radius:4px; transition:width .2s; }

/* ── PHONE PREVIEW ───────────────────────────────────────────────── */
.phone-shell {
    width: 220px;
    margin: 0 auto;
    background: #1e293b;
    border-radius: 30px;
    padding: 12px 10px;
    box-shadow: 0 20px 40px rgba(0,0,0,.25);
    position:relative;
}
.phone-notch {
    width: 70px; height: 18px;
    background: #0f172a;
    border-radius: 0 0 12px 12px;
    margin: 0 auto 8px;
}
.phone-screen {
    background: #f1f5f9;
    border-radius: 20px;
    min-height: 300px;
    overflow:hidden;
}
.phone-screen-head {
    background: #dc2626;
    color:#fff;
    padding: 8px 12px;
    font-size:.72rem;
    font-weight:700;
    display:flex;
    align-items:center;
    gap:6px;
}
.phone-screen-head i { font-size:.65rem; }
.sms-bubble {
    background: #fff;
    border: 1px solid #e2e8f0;
    border-radius: 12px 12px 12px 0;
    margin: 12px 8px;
    padding: 10px 12px;
    font-size:.72rem;
    color:#0f172a;
    line-height:1.5;
    min-height:80px;
    word-break:break-word;
    white-space:pre-wrap;
}
.phone-time { text-align:right; font-size:.62rem; color:#94a3b8; margin: 4px 16px 8px; }

/* ── TEMPLATE CHIPS ──────────────────────────────────────────────── */
.tpl-chips { display:flex; flex-wrap:wrap; gap:6px; margin-bottom:12px; }
.tpl-chip {
    padding: 5px 12px;
    background: #f1f5f9;
    border: 1px solid #e2e8f0;
    border-radius: 20px;
    font-size:.75rem;
    font-weight:600;
    color:#475569;
    cursor:pointer;
    transition: all .15s;
}
.tpl-chip:hover { background:#fff1f2; border-color:#fecaca; color:#dc2626; }

/* ── AUTO SMS CARDS ──────────────────────────────────────────────── */
.auto-trigger-card {
    background:#fff;
    border: 1px solid #e2e8f0;
    border-radius: 14px;
    overflow:hidden;
    transition: box-shadow .2s;
}
.auto-trigger-card:hover { box-shadow: 0 4px 20px rgba(0,0,0,.06); }
.atc-head {
    padding: 14px 18px;
    display:flex;
    align-items:center;
    gap:12px;
    border-bottom: 1px solid #f1f5f9;
}
.atc-icon {
    width:40px; height:40px;
    border-radius:10px;
    display:flex; align-items:center; justify-content:center;
    font-size:.95rem;
    flex-shrink:0;
}
.atc-title { font-size:.88rem; font-weight:800; color:#0f172a; }
.atc-desc { font-size:.75rem; color:#64748b; }
.atc-toggle-wrap { margin-left:auto; display:flex; align-items:center; gap:8px; }
.atc-badge { font-size:.68rem; font-weight:700; padding:3px 9px; border-radius:20px; }
.atc-badge.on { background:#dcfce7; color:#15803d; }
.atc-badge.off { background:#f1f5f9; color:#64748b; }
.atc-body { padding: 16px 18px; }
.atc-textarea {
    font-size:.8rem;
    border: 1px solid #e2e8f0;
    border-radius:8px;
    width:100%;
    padding:10px;
    resize:vertical;
    min-height:68px;
    font-family:inherit;
    color:#0f172a;
    transition: border-color .15s;
}
.atc-textarea:focus { outline:none; border-color:#dc2626; }
.var-hint { font-size:.7rem; color:#94a3b8; margin-top:5px; }
.var-tag {
    display:inline-block;
    background:#e0f2fe;
    color:#0369a1;
    padding:1px 7px;
    border-radius:4px;
    font-size:.67rem;
    font-weight:600;
    margin:1px 2px;
    cursor:pointer;
    user-select:none;
}
.var-tag:hover { background:#bae6fd; }

/* ── FORM SWITCH (custom) ────────────────────────────────────────── */
.bsms-switch { position:relative; display:inline-block; width:44px; height:24px; }
.bsms-switch input { opacity:0; width:0; height:0; }
.bsms-slider {
    position:absolute; cursor:pointer; inset:0;
    background:#cbd5e1; border-radius:34px;
    transition:.2s;
}
.bsms-slider:before {
    content:''; position:absolute;
    width:18px; height:18px; left:3px; bottom:3px;
    background:#fff; border-radius:50%; transition:.2s;
}
.bsms-switch input:checked + .bsms-slider { background:#dc2626; }
.bsms-switch input:checked + .bsms-slider:before { transform:translateX(20px); }

/* ── STAT CARDS ──────────────────────────────────────────────────── */
.sms-stat-card {
    background:#fff;
    border: 1px solid #e2e8f0;
    border-radius:12px;
    padding:18px 20px;
    display:flex;
    align-items:center;
    gap:14px;
}
.sms-stat-icon {
    width:46px; height:46px;
    border-radius:11px;
    display:flex; align-items:center; justify-content:center;
    font-size:1.1rem;
    flex-shrink:0;
}
.sms-stat-label { font-size:.75rem; font-weight:600; color:#64748b; text-transform:uppercase; letter-spacing:.05em; }
.sms-stat-value { font-size:1.6rem; font-weight:900; color:#0f172a; line-height:1.1; }

/* ── HISTORY TABLE ───────────────────────────────────────────────── */
.hist-table { width:100%; border-collapse:collapse; font-size:.82rem; }
.hist-table th { background:#f8fafc; color:#64748b; font-weight:700; font-size:.72rem; text-transform:uppercase; letter-spacing:.05em; padding:10px 14px; border-bottom:1px solid #e2e8f0; white-space:nowrap; }
.hist-table td { padding:11px 14px; border-bottom:1px solid #f1f5f9; color:#334155; vertical-align:middle; }
.hist-table tbody tr:hover td { background:#fafafa; }
.hist-table .msg-preview { max-width:240px; overflow:hidden; text-overflow:ellipsis; white-space:nowrap; color:#64748b; }

/* ── SCHEDULE TOGGLE ─────────────────────────────────────────────── */
.schedule-panel { background:#f0f9ff; border:1px solid #bae6fd; border-radius:10px; padding:14px; margin-top:12px; display:none; }
.schedule-panel.show { display:block; }

/* ── BUTTONS ─────────────────────────────────────────────────────── */
.btn-sms-primary {
    background: linear-gradient(135deg, #dc2626 0%, #b91c1c 100%);
    color:#fff; border:none; border-radius:10px;
    padding:11px 22px; font-weight:700; font-size:.87rem;
    cursor:pointer; transition: all .15s;
    display:inline-flex; align-items:center; gap:7px;
    box-shadow: 0 2px 8px rgba(220,38,38,.25);
}
.btn-sms-primary:hover { background: linear-gradient(135deg, #b91c1c 0%, #991b1b 100%); transform:translateY(-1px); }
.btn-sms-primary:disabled { opacity:.6; cursor:not-allowed; transform:none; }
.btn-sms-secondary {
    background:#fff; color:#334155;
    border:1.5px solid #e2e8f0; border-radius:10px;
    padding:10px 20px; font-weight:700; font-size:.87rem;
    cursor:pointer; transition: all .15s;
    display:inline-flex; align-items:center; gap:7px;
}
.btn-sms-secondary:hover { border-color:#94a3b8; background:#f8fafc; }
.btn-sms-save-auto {
    background:#f0fdf4; color:#15803d;
    border:1px solid #bbf7d0; border-radius:7px;
    padding:6px 14px; font-weight:700; font-size:.75rem;
    cursor:pointer; transition: all .15s;
}
.btn-sms-save-auto:hover { background:#dcfce7; }
.btn-sms-test {
    background:#f0f9ff; color:#0369a1;
    border:1px solid #bae6fd; border-radius:7px;
    padding:6px 14px; font-weight:700; font-size:.75rem;
    cursor:pointer; transition: all .15s;
}
.btn-sms-test:hover { background:#e0f2fe; }

/* ── MISC ────────────────────────────────────────────────────────── */
.section-eyebrow { font-size:.72rem; font-weight:700; text-transform:uppercase; letter-spacing:.08em; color:#94a3b8; margin-bottom:6px; }
.empty-state { text-align:center; padding:40px 20px; color:#94a3b8; }
.empty-state i { font-size:2.5rem; display:block; margin-bottom:10px; }
.toast-success, .toast-error {
    position:fixed; bottom:24px; right:24px;
    padding:12px 20px; border-radius:10px; font-size:.85rem; font-weight:700;
    z-index:9999; box-shadow:0 8px 24px rgba(0,0,0,.15); display:none;
    max-width:320px;
}
.toast-success { background:#15803d; color:#fff; }
.toast-error { background:#b91c1c; color:#fff; }
</style>
@endpush

@section('content')
<div class="bsms-page">

    {{-- ── TOP HEADER BAR ── --}}
    <div class="bsms-topbar">
        <div>
            <div class="bsms-topbar-title">
                <i class="fas fa-sms" style="color:#3b82f6;margin-right:8px;"></i>Bulk SMS & Auto Messaging
            </div>
            <div class="bsms-topbar-sub">Compose campaigns, manage auto-triggers and track delivery in one place</div>
        </div>
        <div class="bsms-kpi-strip">
            <div class="bsms-kpi">
                <div class="bsms-kpi-label">Today</div>
                <div class="bsms-kpi-value">{{ number_format($stats['today']) }}</div>
            </div>
            <div class="bsms-kpi">
                <div class="bsms-kpi-label">This Month</div>
                <div class="bsms-kpi-value">{{ number_format($stats['this_month']) }}</div>
            </div>
            <div class="bsms-kpi">
                <div class="bsms-kpi-label">Total Sent</div>
                <div class="bsms-kpi-value red">{{ number_format($stats['total_system']) }}</div>
            </div>
            <div class="bsms-kpi">
                <div class="bsms-kpi-label">Campaigns</div>
                <div class="bsms-kpi-value grn">{{ number_format($stats['total_campaigns']) }}</div>
            </div>
            <div class="bsms-kpi bsms-kpi-balance" id="balanceKpi" title="Beem Africa SMS credits — click to refresh">
                <div class="bsms-kpi-label">
                    <i class="fas fa-envelope" style="font-size:10px;margin-right:3px;"></i>SMS Credits
                    <button onclick="loadSmsBalance()" id="balanceRefreshBtn" style="background:none;border:none;padding:0;margin-left:4px;cursor:pointer;color:inherit;font-size:10px;line-height:1;" title="Refresh">
                        <i class="fas fa-sync-alt" id="balanceRefreshIcon"></i>
                    </button>
                </div>
                <div class="bsms-kpi-value" id="balanceValue" style="color:#f59e0b;">
                    <i class="fas fa-spinner fa-spin" style="font-size:12px;"></i>
                </div>
                <div style="font-size:10px;opacity:.7;margin-top:1px;" id="balanceCurrency">Beem Africa</div>
            </div>
        </div>
    </div>

    {{-- ── TAB NAVIGATION ── --}}
    <div class="bsms-tabnav">
        <button class="bsms-tablink active" data-tab="compose">
            <i class="fas fa-paper-plane tab-icon"></i>Compose & Send
        </button>
        <button class="bsms-tablink" data-tab="autosms">
            <i class="fas fa-robot tab-icon"></i>Auto SMS
        </button>
        <button class="bsms-tablink" data-tab="stats">
            <i class="fas fa-chart-bar tab-icon"></i>Statistics
        </button>
        <button class="bsms-tablink" data-tab="history">
            <i class="fas fa-history tab-icon"></i>History
            @if($recentCampaigns->total() > 0)
                <span style="background:#dc2626;color:#fff;border-radius:20px;padding:1px 7px;font-size:.65rem;">{{ $recentCampaigns->total() }}</span>
            @endif
        </button>
    </div>

    {{-- ═══════════════════════════════════════════════════════════ --}}
    {{-- TAB 1: COMPOSE & SEND                                       --}}
    {{-- ═══════════════════════════════════════════════════════════ --}}
    <div class="bsms-tab-content active" id="tab-compose">
        <form id="smsComposeForm">
            @csrf
            <div class="row g-3">

                {{-- ── STEP 1: AUDIENCE ── --}}
                <div class="col-xl-4 col-lg-5">
                    <div class="bsms-card h-100">
                        <div class="bsms-card-head">
                            <h6><span class="step-num">1</span> Target Audience</h6>
                            <span id="recipientBadge" style="background:#fef2f2;color:#dc2626;border:1px solid #fecaca;border-radius:20px;padding:3px 12px;font-size:.75rem;font-weight:700;">0 Recipients</span>
                        </div>
                        <div class="bsms-card-body">

                            <div class="section-eyebrow">Selection Method</div>
                            <div class="sel-grid" id="selGrid">
                                <div class="sel-btn active" data-val="all">
                                    <i class="fas fa-globe"></i>All Customers
                                </div>
                                <div class="sel-btn" data-val="category">
                                    <i class="fas fa-layer-group"></i>By Category
                                </div>
                                <div class="sel-btn" data-val="filters">
                                    <i class="fas fa-filter"></i>Advanced Filter
                                </div>
                                <div class="sel-btn" data-val="individual">
                                    <i class="fas fa-user-check"></i>Individual Pick
                                </div>
                                <div class="sel-btn grn" data-val="completed_customers">
                                    <i class="fas fa-check-circle"></i>Completed Orders
                                </div>
                                <div class="sel-btn blu" data-val="imported_leads">
                                    <i class="fas fa-user-plus"></i>Imported Leads
                                </div>
                            </div>
                            <input type="hidden" name="selection_type" id="selectionTypeInput" value="all">

                            {{-- Category --}}
                            <div class="sub-filter-panel red-tint" id="pnl_category">
                                <label class="form-label fw-bold small mb-1">Customer Category</label>
                                <select class="form-select form-select-sm" name="category" id="categorySelect">
                                    <option value="">— All Categories —</option>
                                    <option value="wholesale">Wholesale</option>
                                    <option value="retail">Retail</option>
                                    @foreach($businessTypes as $bt)
                                        <option value="{{ $bt }}">{{ ucfirst($bt) }}</option>
                                    @endforeach
                                </select>
                            </div>

                            {{-- Advanced Filters --}}
                            <div class="sub-filter-panel red-tint" id="pnl_filters">
                                <div class="row g-2">
                                    <div class="col-6">
                                        <label class="form-label fw-bold small mb-1">Buyer Activity</label>
                                        <select class="form-select form-select-sm" name="buyer_status" id="buyerStatusSelect">
                                            <option value="">All Levels</option>
                                            <option value="recent">Recent Buyers (30d)</option>
                                            <option value="inactive">Inactive (>30d)</option>
                                        </select>
                                    </div>
                                    <div class="col-6">
                                        <label class="form-label fw-bold small mb-1">Region</label>
                                        <select class="form-select form-select-sm" name="region_id" id="regionSelect">
                                            <option value="">All Regions</option>
                                            @foreach($regions as $reg)
                                                <option value="{{ $reg->id }}">{{ $reg->region_name ?? $reg->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-6">
                                        <label class="form-label fw-bold small mb-1">Sales Rep</label>
                                        <select class="form-select form-select-sm" name="sales_rep_id" id="salesRepSelect">
                                            <option value="">All Reps</option>
                                            @foreach($salesReps as $rep)
                                                <option value="{{ $rep->id }}">{{ $rep->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-6">
                                        <label class="form-label fw-bold small mb-1">Registered From</label>
                                        <input type="date" class="form-control form-control-sm" name="date_from" id="dateFromInput">
                                    </div>
                                </div>
                            </div>

                            {{-- Individual --}}
                            <div class="sub-filter-panel red-tint" id="pnl_individual">
                                <label class="form-label fw-bold small mb-1">Search Customers</label>
                                <input type="text" class="form-control form-control-sm mb-2" id="customerSearchInput" placeholder="Name, phone, company...">
                                <div style="max-height:160px;overflow-y:auto;border:1px solid #fecaca;border-radius:8px;background:#fff;padding:6px;" id="customerChecklist">
                                    <div class="text-muted text-center py-2 small">Type to search...</div>
                                </div>
                            </div>

                            {{-- Completed Customers --}}
                            <div class="sub-filter-panel grn-tint" id="pnl_completed_customers">
                                <label class="form-label fw-bold small text-success mb-1"><i class="fas fa-calendar me-1"></i>Order Completion Date</label>
                                <div class="btn-group btn-group-sm w-100 mb-2">
                                    <input type="radio" class="btn-check" name="order_period" id="op_today" value="today" checked>
                                    <label class="btn btn-outline-success btn-sm" for="op_today">Today</label>
                                    <input type="radio" class="btn-check" name="order_period" id="op_week" value="week">
                                    <label class="btn btn-outline-success btn-sm" for="op_week">This Week</label>
                                    <input type="radio" class="btn-check" name="order_period" id="op_custom" value="custom">
                                    <label class="btn btn-outline-success btn-sm" for="op_custom">Custom</label>
                                </div>
                                <div id="orderCustomDates" class="row g-2" style="display:none!important;">
                                    <div class="col-6"><input type="date" class="form-control form-control-sm" name="order_date_from" id="orderDateFrom"></div>
                                    <div class="col-6"><input type="date" class="form-control form-control-sm" name="order_date_to" id="orderDateTo"></div>
                                </div>
                            </div>

                            {{-- Leads --}}
                            <div class="sub-filter-panel blu-tint" id="pnl_imported_leads">
                                <label class="form-label fw-bold small text-info mb-1"><i class="fas fa-calendar me-1"></i>Lead Import Date</label>
                                <div class="btn-group btn-group-sm w-100 mb-2">
                                    <input type="radio" class="btn-check" name="lead_period" id="lp_today" value="today" checked>
                                    <label class="btn btn-outline-info btn-sm" for="lp_today">Today</label>
                                    <input type="radio" class="btn-check" name="lead_period" id="lp_week" value="week">
                                    <label class="btn btn-outline-info btn-sm" for="lp_week">This Week</label>
                                    <input type="radio" class="btn-check" name="lead_period" id="lp_custom" value="custom">
                                    <label class="btn btn-outline-info btn-sm" for="lp_custom">Custom</label>
                                </div>
                                <div id="leadCustomDates" class="row g-2" style="display:none!important;">
                                    <div class="col-6"><input type="date" class="form-control form-control-sm" name="lead_date_from" id="leadDateFrom"></div>
                                    <div class="col-6"><input type="date" class="form-control form-control-sm" name="lead_date_to" id="leadDateTo"></div>
                                </div>
                            </div>

                            {{-- Live Count --}}
                            <div class="live-count-box mt-2">
                                <div>
                                    <div class="live-count-label">Target Recipients</div>
                                    <div class="live-count-num" id="liveCountNum">—</div>
                                </div>
                                <div style="background:#dc2626;width:42px;height:42px;border-radius:50%;display:flex;align-items:center;justify-content:center;">
                                    <i class="fas fa-users" style="color:#fff;font-size:1rem;"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- ── STEP 2: MESSAGE ── --}}
                <div class="col-xl-4 col-lg-7">
                    <div class="bsms-card h-100">
                        <div class="bsms-card-head">
                            <h6><span class="step-num">2</span> Compose Message</h6>
                        </div>
                        <div class="bsms-card-body" style="display:flex;flex-direction:column;gap:14px;">

                            <div>
                                <label class="form-label fw-bold small mb-1">Campaign Title <span class="text-muted fw-normal">(optional)</span></label>
                                <input type="text" class="form-control form-control-sm" name="title" id="campaignTitle" placeholder="e.g. July Promo Blast">
                            </div>

                            {{-- Template Picker --}}
                            @if($templates->count())
                            <div>
                                <div class="section-eyebrow">Quick Templates</div>
                                <div class="tpl-chips" id="tplChips">
                                    @foreach($templates->take(8) as $tpl)
                                    <div class="tpl-chip" data-content="{{ $tpl->content }}" title="{{ $tpl->content }}">
                                        {{ Str::limit($tpl->title, 22) }}
                                    </div>
                                    @endforeach
                                </div>
                            </div>
                            @endif

                            <div style="flex:1;">
                                <label class="form-label fw-bold small mb-1">
                                    SMS Message <span class="text-danger">*</span>
                                </label>
                                <textarea class="form-control" name="message" id="smsBody" rows="7"
                                    placeholder="Type your SMS message here...&#10;&#10;Use variables: {name}, {amount}, {date}, {order_id}"
                                    required style="resize:vertical;font-size:.85rem;"></textarea>
                                <div class="var-hint mt-1">
                                    Variables:
                                    <span class="var-tag" onclick="insertVar('{name}')">name</span>
                                    <span class="var-tag" onclick="insertVar('{task_code}')">task_code</span>
                                    <span class="var-tag" onclick="insertVar('{task_title}')">task_title</span>
                                    <span class="var-tag" onclick="insertVar('{amount}')">amount</span>
                                    <span class="var-tag" onclick="insertVar('{balance}')">balance</span>
                                    <span class="var-tag" onclick="insertVar('{deadline}')">deadline</span>
                                </div>
                            </div>

                            <div class="char-meter">
                                <div class="char-row">
                                    <span>Characters</span>
                                    <span><span id="charCount">0</span> / 160</span>
                                </div>
                                <div class="char-bar-wrap">
                                    <div class="char-bar" id="charBar" style="width:0%"></div>
                                </div>
                                <div class="char-row">
                                    <span>SMS Parts</span>
                                    <span id="smsParts">1 part</span>
                                </div>
                                <div class="char-row">
                                    <span>Units Per Recipient</span>
                                    <span id="unitsPerRec">1</span>
                                </div>
                                <div class="char-row" style="border-top:1px solid #e2e8f0;padding-top:8px;margin-top:4px;font-weight:700;">
                                    <span>Total Estimated Units</span>
                                    <span id="totalUnits" style="color:#dc2626;">0</span>
                                </div>
                            </div>

                            {{-- Schedule --}}
                            <div>
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" id="scheduleToggle">
                                    <label class="form-check-label fw-semibold small" for="scheduleToggle">Schedule for later</label>
                                </div>
                                <div class="schedule-panel" id="schedulePnl">
                                    <div class="row g-2">
                                        <div class="col-6">
                                            <label class="form-label small fw-bold mb-1">Date</label>
                                            <input type="date" class="form-control form-control-sm" name="schedule_date" id="scheduleDate" min="{{ date('Y-m-d') }}">
                                        </div>
                                        <div class="col-6">
                                            <label class="form-label small fw-bold mb-1">Time</label>
                                            <input type="time" class="form-control form-control-sm" name="schedule_time" id="scheduleTime">
                                        </div>
                                    </div>
                                    <div class="mt-2" style="font-size:.72rem;color:#0369a1;"><i class="fas fa-info-circle me-1"></i>Requires queue worker running on server.</div>
                                </div>
                            </div>

                            <div style="display:flex;gap:10px;margin-top:auto;">
                                <button type="button" class="btn-sms-secondary flex-fill" id="btnPreview">
                                    <i class="fas fa-eye"></i>Preview
                                </button>
                                <button type="button" class="btn-sms-primary flex-fill" id="btnSend">
                                    <i class="fas fa-paper-plane"></i>Send Now
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- ── STEP 3: PHONE PREVIEW ── --}}
                <div class="col-xl-4 col-lg-12">
                    <div class="bsms-card" style="position:sticky;top:80px;">
                        <div class="bsms-card-head">
                            <h6><span class="step-num">3</span> Live Preview</h6>
                        </div>
                        <div class="bsms-card-body" style="display:flex;flex-direction:column;align-items:center;gap:16px;">
                            <div class="phone-shell">
                                <div class="phone-notch"></div>
                                <div class="phone-screen">
                                    <div class="phone-screen-head">
                                        <i class="fas fa-sms"></i> CHIBOBRAND &nbsp;|&nbsp; SMS
                                    </div>
                                    <div class="sms-bubble" id="phoneBubble" style="color:#94a3b8;font-style:italic;">Your message preview will appear here...</div>
                                    <div class="phone-time" id="phoneTime">Now</div>
                                </div>
                            </div>

                            <div style="width:100%;background:#f8fafc;border:1px solid #e2e8f0;border-radius:10px;padding:14px;">
                                <div class="section-eyebrow mb-2">Campaign Summary</div>
                                <div style="display:flex;justify-content:space-between;font-size:.8rem;padding:5px 0;border-bottom:1px solid #f1f5f9;">
                                    <span style="color:#64748b;">Recipients</span>
                                    <span style="font-weight:700;" id="summaryRecipients">—</span>
                                </div>
                                <div style="display:flex;justify-content:space-between;font-size:.8rem;padding:5px 0;border-bottom:1px solid #f1f5f9;">
                                    <span style="color:#64748b;">SMS Parts</span>
                                    <span style="font-weight:700;" id="summaryParts">1</span>
                                </div>
                                <div style="display:flex;justify-content:space-between;font-size:.8rem;padding:5px 0;border-bottom:1px solid #f1f5f9;">
                                    <span style="color:#64748b;">Total Units</span>
                                    <span style="font-weight:700;color:#dc2626;" id="summaryUnits">0</span>
                                </div>
                                <div style="display:flex;justify-content:space-between;font-size:.8rem;padding:6px 0;font-weight:700;">
                                    <span style="color:#64748b;">Sender ID</span>
                                    <span>CHIBOBRAND</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

            </div>{{-- end row --}}
        </form>
    </div>{{-- end compose tab --}}


    {{-- ═══════════════════════════════════════════════════════════ --}}
    {{-- TAB 2: AUTO SMS                                             --}}
    {{-- ═══════════════════════════════════════════════════════════ --}}
    <div class="bsms-tab-content" id="tab-autosms">

        <div style="background:#fffbeb;border:1px solid #fef08a;border-radius:12px;padding:16px 20px;margin-bottom:20px;display:flex;gap:14px;align-items:flex-start;">
            <i class="fas fa-robot" style="color:#ca8a04;font-size:1.4rem;margin-top:2px;"></i>
            <div>
                <div style="font-weight:800;color:#78350f;font-size:.9rem;">Automatic SMS Triggers — Design Task Events</div>
                <div style="font-size:.8rem;color:#92400e;margin-top:3px;line-height:1.5;">
                    These messages are sent automatically when specific design task events occur (task received, task ready for pickup, payment, delivery, etc.).
                    Toggle each trigger on or off and customise the template. Changes save immediately.
                    <br><strong>Available variables:</strong>
                    <span class="var-tag">{name}</span>
                    <span class="var-tag">{task_code}</span>
                    <span class="var-tag">{task_title}</span>
                    <span class="var-tag">{amount}</span>
                    <span class="var-tag">{paid}</span>
                    <span class="var-tag">{balance}</span>
                    <span class="var-tag">{pickup_code}</span>
                    <span class="var-tag">{deadline}</span>
                    <span class="var-tag">{seller_contact}</span>
                </div>
            </div>
        </div>

        <div class="row g-3" id="autoSmsGrid">

            @php
            $triggers = [
                'welcome'          => ['icon'=>'fas fa-handshake',          'color'=>'#7c3aed','bg'=>'#f5f3ff',
                                       'title'=>'Welcome New Customer',
                                       'desc' =>'Sent when a new customer is added to the system.'],
                'task_received'    => ['icon'=>'fas fa-inbox',               'color'=>'#0369a1','bg'=>'#f0f9ff',
                                       'title'=>'Design Task Received (Receipt)',
                                       'desc' =>'Sent immediately when a design task is created and payment is recorded. Includes task code, amount paid, balance and deadline.'],
                'task_ready'       => ['icon'=>'fas fa-check-double',        'color'=>'#15803d','bg'=>'#f0fdf4',
                                       'title'=>'Design Task Ready for Pickup',
                                       'desc' =>'Sent when a task reaches "Super Completed" status — design is printed & packed. Includes pickup code and remaining balance.'],
                'task_completed'   => ['icon'=>'fas fa-check-circle',        'color'=>'#166534','bg'=>'#dcfce7',
                                       'title'=>'Design Task Completed',
                                       'desc' =>'Sent when a task status is marked as Completed (design work done, pending print/pickup).'],
                'payment_received' => ['icon'=>'fas fa-money-bill-wave',     'color'=>'#065f46','bg'=>'#ecfdf5',
                                       'title'=>'Payment Received',
                                       'desc' =>'Sent when a payment is recorded against a design task. Shows amount received and remaining balance.'],
                'delivery_assigned'=> ['icon'=>'fas fa-truck',               'color'=>'#c2410c','bg'=>'#fff7ed',
                                       'title'=>'Delivery Assigned',
                                       'desc' =>'Sent when a delivery person is assigned to deliver a completed design task to the customer.'],
                'lead_reminder'    => ['icon'=>'fas fa-bell',                'color'=>'#b45309','bg'=>'#fffbeb',
                                       'title'=>'Lead Follow-Up Reminder',
                                       'desc' =>'Sent automatically on a lead\'s scheduled follow-up date (via SendLeadReminders cron job).'],
                'overdue_payment'  => ['icon'=>'fas fa-exclamation-triangle','color'=>'#b91c1c','bg'=>'#fef2f2',
                                       'title'=>'Overdue Balance Reminder',
                                       'desc' =>'Sent when a design task has an outstanding balance past the due date (via SendOverdueReminders cron job).'],
                'task_cancelled'   => ['icon'=>'fas fa-times-circle',        'color'=>'#64748b','bg'=>'#f8fafc',
                                       'title'=>'Design Task Cancelled',
                                       'desc' =>'Sent when a design task is cancelled by staff or the customer.'],
            ];
            @endphp

            @foreach($triggers as $key => $t)
            @php $cfg = $autoSettings[$key] ?? ['enabled'=>false,'template'=>'']; @endphp
            <div class="col-md-6">
                <div class="auto-trigger-card">
                    <div class="atc-head">
                        <div class="atc-icon" style="background:{{ $t['bg'] }};color:{{ $t['color'] }};">
                            <i class="{{ $t['icon'] }}"></i>
                        </div>
                        <div style="flex:1;min-width:0;">
                            <div class="atc-title">{{ $t['title'] }}</div>
                            <div class="atc-desc">{{ $t['desc'] }}</div>
                        </div>
                        <div class="atc-toggle-wrap">
                            <span class="atc-badge {{ $cfg['enabled'] ? 'on' : 'off' }}" id="badge_{{ $key }}">{{ $cfg['enabled'] ? 'ON' : 'OFF' }}</span>
                            <label class="bsms-switch">
                                <input type="checkbox" class="auto-toggle" data-trigger="{{ $key }}" {{ $cfg['enabled'] ? 'checked' : '' }}>
                                <span class="bsms-slider"></span>
                            </label>
                        </div>
                    </div>
                    <div class="atc-body">
                        <label class="form-label fw-bold" style="font-size:.75rem;margin-bottom:5px;">Message Template</label>
                        <textarea class="atc-textarea" id="tpl_{{ $key }}" data-trigger="{{ $key }}" rows="3">{{ $cfg['template'] }}</textarea>
                        <div class="var-hint">
                            Insert:
                            <span class="var-tag" onclick="insertAutoVar('{{ $key }}','{name}')">name</span>
                            <span class="var-tag" onclick="insertAutoVar('{{ $key }}','{task_code}')">task_code</span>
                            <span class="var-tag" onclick="insertAutoVar('{{ $key }}','{task_title}')">task_title</span>
                            <span class="var-tag" onclick="insertAutoVar('{{ $key }}','{amount}')">amount</span>
                            <span class="var-tag" onclick="insertAutoVar('{{ $key }}','{paid}')">paid</span>
                            <span class="var-tag" onclick="insertAutoVar('{{ $key }}','{balance}')">balance</span>
                            <span class="var-tag" onclick="insertAutoVar('{{ $key }}','{pickup_code}')">pickup_code</span>
                            <span class="var-tag" onclick="insertAutoVar('{{ $key }}','{deadline}')">deadline</span>
                            <span class="var-tag" onclick="insertAutoVar('{{ $key }}','{seller_contact}')">seller_contact</span>
                        </div>
                        <div style="margin-top:10px;display:flex;gap:8px;">
                            <button type="button" class="btn-sms-save-auto" onclick="saveSingleTrigger('{{ $key }}')">
                                <i class="fas fa-save me-1"></i>Save
                            </button>
                            <button type="button" class="btn-sms-test" onclick="testTrigger('{{ $key }}','{{ addslashes($t['title']) }}')">
                                <i class="fas fa-flask me-1"></i>Test Send
                            </button>
                        </div>
                    </div>
                </div>
            </div>
            @endforeach

        </div>

        <div style="text-align:right;margin-top:20px;">
            <button type="button" class="btn-sms-primary" onclick="saveAllAutoSettings()">
                <i class="fas fa-save"></i>Save All Auto SMS Settings
            </button>
        </div>
    </div>{{-- end autosms tab --}}


    {{-- ═══════════════════════════════════════════════════════════ --}}
    {{-- TAB 3: STATISTICS                                           --}}
    {{-- ═══════════════════════════════════════════════════════════ --}}
    <div class="bsms-tab-content" id="tab-stats">

        <div class="row g-3 mb-4">
            <div class="col-md-4 col-xl-2">
                <div class="sms-stat-card">
                    <div class="sms-stat-icon" style="background:#fef2f2;color:#dc2626;"><i class="fas fa-sun"></i></div>
                    <div>
                        <div class="sms-stat-label">Today</div>
                        <div class="sms-stat-value">{{ number_format($stats['today']) }}</div>
                    </div>
                </div>
            </div>
            <div class="col-md-4 col-xl-2">
                <div class="sms-stat-card">
                    <div class="sms-stat-icon" style="background:#fef9c3;color:#ca8a04;"><i class="fas fa-calendar-week"></i></div>
                    <div>
                        <div class="sms-stat-label">This Week</div>
                        <div class="sms-stat-value">{{ number_format($stats['this_week']) }}</div>
                    </div>
                </div>
            </div>
            <div class="col-md-4 col-xl-2">
                <div class="sms-stat-card">
                    <div class="sms-stat-icon" style="background:#f0f9ff;color:#0369a1;"><i class="fas fa-calendar-alt"></i></div>
                    <div>
                        <div class="sms-stat-label">This Month</div>
                        <div class="sms-stat-value">{{ number_format($stats['this_month']) }}</div>
                    </div>
                </div>
            </div>
            <div class="col-md-4 col-xl-2">
                <div class="sms-stat-card">
                    <div class="sms-stat-icon" style="background:#f0fdf4;color:#15803d;"><i class="fas fa-calendar"></i></div>
                    <div>
                        <div class="sms-stat-label">This Year</div>
                        <div class="sms-stat-value">{{ number_format($stats['this_year']) }}</div>
                    </div>
                </div>
            </div>
            <div class="col-md-4 col-xl-2">
                <div class="sms-stat-card">
                    <div class="sms-stat-icon" style="background:#fdf4ff;color:#7c3aed;"><i class="fas fa-paper-plane"></i></div>
                    <div>
                        <div class="sms-stat-label">Total Sent</div>
                        <div class="sms-stat-value" style="color:#dc2626;">{{ number_format($stats['total_system']) }}</div>
                    </div>
                </div>
            </div>
            <div class="col-md-4 col-xl-2">
                <div class="sms-stat-card">
                    <div class="sms-stat-icon" style="background:#fff7ed;color:#c2410c;"><i class="fas fa-bullhorn"></i></div>
                    <div>
                        <div class="sms-stat-label">Campaigns</div>
                        <div class="sms-stat-value">{{ number_format($stats['total_campaigns']) }}</div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row g-3">
            <div class="col-lg-8">
                <div class="bsms-card">
                    <div class="bsms-card-head">
                        <h6><i class="fas fa-chart-area" style="color:#dc2626;"></i>Monthly SMS Volume (Last 6 Months)</h6>
                    </div>
                    <div class="bsms-card-body">
                        <div style="height:260px;position:relative;">
                            <canvas id="chartMonthly"></canvas>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-4">
                <div class="bsms-card">
                    <div class="bsms-card-head">
                        <h6><i class="fas fa-chart-pie" style="color:#64748b;"></i>Delivery Status</h6>
                    </div>
                    <div class="bsms-card-body">
                        <div style="height:220px;position:relative;">
                            <canvas id="chartStatus"></canvas>
                        </div>
                        <div style="display:flex;justify-content:center;gap:16px;margin-top:12px;flex-wrap:wrap;">
                            <div style="font-size:.75rem;display:flex;align-items:center;gap:5px;"><span style="width:10px;height:10px;background:#16a34a;border-radius:50%;display:inline-block;"></span>Delivered <strong>{{ number_format($stats['status_completed']) }}</strong></div>
                            <div style="font-size:.75rem;display:flex;align-items:center;gap:5px;"><span style="width:10px;height:10px;background:#f59e0b;border-radius:50%;display:inline-block;"></span>Pending <strong>{{ number_format($stats['status_pending']) }}</strong></div>
                            <div style="font-size:.75rem;display:flex;align-items:center;gap:5px;"><span style="width:10px;height:10px;background:#ef4444;border-radius:50%;display:inline-block;"></span>Failed <strong>{{ number_format($stats['status_failed']) }}</strong></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>{{-- end stats tab --}}


    {{-- ═══════════════════════════════════════════════════════════ --}}
    {{-- TAB 4: HISTORY                                              --}}
    {{-- ═══════════════════════════════════════════════════════════ --}}
    <div class="bsms-tab-content" id="tab-history">
        <div class="bsms-card">
            <div class="bsms-card-head">
                <h6><i class="fas fa-history" style="color:#dc2626;"></i>SMS Campaign History</h6>
                <span style="font-size:.78rem;color:#64748b;">{{ $recentCampaigns->total() }} total campaigns</span>
            </div>
            <div style="overflow-x:auto;">
                <table class="hist-table">
                    <thead>
                        <tr>
                            <th>Date & Time</th>
                            <th>Campaign Title</th>
                            <th>Sent By</th>
                            <th>Message</th>
                            <th style="text-align:center;">Recipients</th>
                            <th style="text-align:center;">Units</th>
                            <th style="text-align:center;">Sent</th>
                            <th style="text-align:center;">Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($recentCampaigns as $campaign)
                        <tr>
                            <td style="white-space:nowrap;font-weight:600;color:#0f172a;">{{ $campaign->created_at->format('d M Y') }}<br><span style="font-size:.72rem;color:#94a3b8;">{{ $campaign->created_at->format('H:i') }}</span></td>
                            <td style="font-weight:700;color:#0f172a;">
                                @if(str_starts_with($campaign->title, 'Auto:'))
                                    <span style="background:#ede9fe;color:#6d28d9;font-size:.65rem;font-weight:800;padding:2px 7px;border-radius:20px;margin-right:5px;vertical-align:middle;letter-spacing:.03em;">AUTO</span>
                                @endif
                                {{ $campaign->title }}
                            </td>
                            <td style="font-size:.78rem;color:#64748b;">{{ $campaign->sender->name ?? '⚙ System' }}</td>
                            <td class="msg-preview" title="{{ $campaign->message }}">{{ $campaign->message }}</td>
                            <td style="text-align:center;">
                                <span style="background:#f1f5f9;color:#475569;padding:2px 10px;border-radius:20px;font-size:.75rem;font-weight:700;">{{ number_format($campaign->total_recipients) }}</span>
                            </td>
                            <td style="text-align:center;">
                                <span style="background:#e0f2fe;color:#0369a1;padding:2px 10px;border-radius:20px;font-size:.75rem;font-weight:700;">{{ number_format($campaign->total_sms_units) }}</span>
                            </td>
                            <td style="text-align:center;">
                                <span style="font-size:.8rem;font-weight:700;color:#15803d;">{{ number_format($campaign->total_sent ?? 0) }}</span>
                            </td>
                            <td style="text-align:center;">
                                @if($campaign->status === 'completed')
                                    <span style="background:#dcfce7;color:#15803d;padding:3px 10px;border-radius:20px;font-size:.72rem;font-weight:700;white-space:nowrap;"><i class="fas fa-check-circle me-1"></i>Done</span>
                                @elseif($campaign->status === 'processing')
                                    <span style="background:#fef9c3;color:#92400e;padding:3px 10px;border-radius:20px;font-size:.72rem;font-weight:700;white-space:nowrap;"><i class="fas fa-spinner fa-spin me-1"></i>Processing</span>
                                @else
                                    <span style="background:#fee2e2;color:#991b1b;padding:3px 10px;border-radius:20px;font-size:.72rem;font-weight:700;white-space:nowrap;"><i class="fas fa-exclamation me-1"></i>Failed</span>
                                @endif
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="8">
                                <div class="empty-state">
                                    <i class="fas fa-inbox"></i>
                                    No SMS campaigns yet. Send your first campaign from the Compose & Send tab!
                                </div>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if($recentCampaigns->hasPages())
            <div style="padding:14px 20px;border-top:1px solid #f1f5f9;">
                {{ $recentCampaigns->links() }}
            </div>
            @endif
        </div>
    </div>{{-- end history tab --}}

</div>{{-- end bsms-page --}}

{{-- ── PREVIEW MODAL ── --}}
<div class="modal fade" id="previewModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content" style="border-radius:16px;border:none;overflow:hidden;">
            <div class="modal-header" style="background:linear-gradient(135deg,#0f172a,#1e293b);color:#fff;border:none;">
                <h5 class="modal-title fw-bold" style="font-size:.95rem;"><i class="fas fa-eye me-2"></i>Campaign Preview</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-4">
                <div class="row g-3 mb-4">
                    <div class="col-4 text-center" style="background:#fef2f2;border:1px solid #fecaca;border-radius:10px;padding:14px 10px;">
                        <div style="font-size:.7rem;color:#94a3b8;font-weight:600;text-transform:uppercase;">Recipients</div>
                        <div style="font-size:1.6rem;font-weight:900;color:#dc2626;" id="mdlRecipients">0</div>
                    </div>
                    <div class="col-4 text-center" style="background:#f0f9ff;border:1px solid #bae6fd;border-radius:10px;padding:14px 10px;">
                        <div style="font-size:.7rem;color:#94a3b8;font-weight:600;text-transform:uppercase;">SMS Parts</div>
                        <div style="font-size:1.6rem;font-weight:900;color:#0369a1;" id="mdlParts">1</div>
                    </div>
                    <div class="col-4 text-center" style="background:#f0fdf4;border:1px solid #bbf7d0;border-radius:10px;padding:14px 10px;">
                        <div style="font-size:.7rem;color:#94a3b8;font-weight:600;text-transform:uppercase;">Total Units</div>
                        <div style="font-size:1.6rem;font-weight:900;color:#15803d;" id="mdlUnits">0</div>
                    </div>
                </div>
                <div style="background:#f8fafc;border:1px solid #e2e8f0;border-radius:10px;padding:14px;">
                    <div style="font-size:.72rem;font-weight:700;text-transform:uppercase;color:#94a3b8;margin-bottom:8px;">Message Content</div>
                    <div id="mdlMessage" style="font-size:.85rem;color:#0f172a;line-height:1.6;white-space:pre-wrap;"></div>
                </div>
            </div>
            <div class="modal-footer" style="border-top:1px solid #f1f5f9;background:#fafafa;">
                <button type="button" class="btn-sms-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn-sms-primary" id="btnConfirmSend">
                    <i class="fas fa-paper-plane"></i>Confirm & Send
                </button>
            </div>
        </div>
    </div>
</div>

{{-- ── SUCCESS MODAL ── --}}
<div class="modal fade" id="successModal" data-bs-backdrop="static" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content" style="border-radius:20px;border:none;padding:10px;">
            <div class="modal-body text-center p-4">
                <div style="width:70px;height:70px;background:#15803d;border-radius:50%;display:inline-flex;align-items:center;justify-content:center;margin-bottom:16px;">
                    <i class="fas fa-check" style="color:#fff;font-size:1.8rem;"></i>
                </div>
                <h4 style="font-weight:900;color:#0f172a;margin-bottom:4px;">Campaign Sent!</h4>
                <p style="color:#64748b;font-size:.85rem;margin-bottom:20px;">Your bulk SMS has been dispatched successfully.</p>
                <div style="background:#f8fafc;border:1px solid #e2e8f0;border-radius:12px;padding:16px;text-align:left;margin-bottom:20px;">
                    <div style="display:flex;justify-content:space-between;font-size:.82rem;padding:5px 0;border-bottom:1px solid #f1f5f9;">
                        <span style="color:#64748b;">Recipients</span>
                        <span style="font-weight:700;" id="sucRecipients">0</span>
                    </div>
                    <div style="display:flex;justify-content:space-between;font-size:.82rem;padding:5px 0;border-bottom:1px solid #f1f5f9;">
                        <span style="color:#64748b;">SMS Sent</span>
                        <span style="font-weight:700;color:#15803d;" id="sucSent">0</span>
                    </div>
                    <div style="display:flex;justify-content:space-between;font-size:.82rem;padding:6px 0;">
                        <span style="color:#64748b;">Date & Time</span>
                        <span style="font-weight:700;" id="sucDate">—</span>
                    </div>
                </div>
                <button type="button" class="btn-sms-primary w-100" data-bs-dismiss="modal" onclick="location.reload();">
                    Done
                </button>
            </div>
        </div>
    </div>
</div>

{{-- ── TEST SMS MODAL ── --}}
<div class="modal fade" id="testSmsModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-sm">
        <div class="modal-content" style="border-radius:14px;border:none;">
            <div class="modal-header" style="border:none;padding:16px 20px 8px;">
                <h6 class="modal-title fw-bold" style="font-size:.9rem;"><i class="fas fa-flask me-2 text-info"></i>Test Auto SMS</h6>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-4 pt-2">
                <p style="font-size:.8rem;color:#64748b;" id="testSmsTitle"></p>
                <label class="form-label fw-bold small mb-1">Send test to phone number:</label>
                <input type="tel" class="form-control form-control-sm" id="testPhoneInput" placeholder="+255 7XX XXX XXX"
                    oninput="document.getElementById('testSmsError').style.display='none'">
                <div style="font-size:.72rem;color:#94a3b8;margin-top:4px;">Include country code (e.g. +255 for Tanzania)</div>
                <div id="testSmsError" style="display:none;margin-top:10px;padding:10px 12px;background:#fef2f2;border:1px solid #fca5a5;border-radius:8px;font-size:.78rem;color:#b91c1c;line-height:1.4;">
                    <i class="fas fa-exclamation-circle me-1"></i><span></span>
                </div>
            </div>
            <div class="modal-footer" style="border:none;padding:0 20px 16px;">
                <button type="button" class="btn-sms-secondary flex-fill" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn-sms-primary flex-fill" id="btnSendTest">
                    <i class="fas fa-paper-plane"></i>Send Test
                </button>
            </div>
        </div>
    </div>
</div>

{{-- ── TOASTS ── --}}
<div class="toast-success" id="toastSuccess"><i class="fas fa-check-circle me-2"></i><span id="toastSuccessMsg">Saved!</span></div>
<div class="toast-error" id="toastError"><i class="fas fa-exclamation-circle me-2"></i><span id="toastErrorMsg">Error occurred.</span></div>

@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
    // ─────────────────────────────────────────────────────────────
    // SMS BALANCE
    // ─────────────────────────────────────────────────────────────
    window.loadSmsBalance = function() {
        const valEl  = document.getElementById('balanceValue');
        const curEl  = document.getElementById('balanceCurrency');
        const icon   = document.getElementById('balanceRefreshIcon');
        const kpi    = document.getElementById('balanceKpi');

        valEl.innerHTML = '<i class="fas fa-spinner fa-spin" style="font-size:12px;"></i>';
        curEl.textContent = '';
        if (icon) { icon.classList.add('fa-spin'); }

        fetch("{{ route('admin.bulk-sms.balance') }}")
            .then(r => r.json())
            .then(data => {
                if (data.success) {
                    const bal = parseInt(data.balance) || 0;
                    valEl.textContent = bal.toLocaleString() + ' SMS';
                    curEl.textContent = 'Beem Africa';

                    // Color-code: green ≥ 100, amber < 100, red ≤ 0
                    valEl.style.color = bal > 100 ? '#22c55e' : (bal > 0 ? '#f59e0b' : '#ef4444');
                    kpi.title = 'Beem Africa: ' + bal.toLocaleString() + ' SMS credits remaining — click to refresh';

                    // Show warning banner if credits are critically low
                    if (bal <= 0) {
                        showLowBalanceBanner('Your Beem Africa SMS credits are <strong>0</strong>. Top up at <a href="https://apisms.beem.africa" target="_blank" style="color:#fff;text-decoration:underline;">apisms.beem.africa</a> to send SMS.');
                    } else if (bal < 50) {
                        showLowBalanceBanner('Low SMS credits: <strong>' + bal.toLocaleString() + ' credits</strong> remaining. Top up at <a href="https://apisms.beem.africa" target="_blank" style="color:#fff;text-decoration:underline;">apisms.beem.africa</a>.');
                    } else {
                        hideLowBalanceBanner();
                    }
                } else {
                    valEl.innerHTML = '<i class="fas fa-exclamation-triangle" style="color:#ef4444;"></i>';
                    curEl.textContent = data.message || 'Error';
                    valEl.style.color = '#ef4444';
                    kpi.title = 'Balance check failed: ' + (data.message || 'Unknown error');
                }
            })
            .catch(() => {
                valEl.innerHTML = '<i class="fas fa-times-circle" style="color:#ef4444;"></i>';
                curEl.textContent = 'Failed';
                valEl.style.color = '#ef4444';
            })
            .finally(() => {
                if (icon) { icon.classList.remove('fa-spin'); }
            });
    };

    function showLowBalanceBanner(html) {
        let banner = document.getElementById('lowBalanceBanner');
        if (!banner) {
            banner = document.createElement('div');
            banner.id = 'lowBalanceBanner';
            banner.style.cssText = 'background:#b91c1c;color:#fff;padding:10px 18px;border-radius:8px;margin-bottom:12px;font-size:13px;display:flex;align-items:center;gap:10px;';
            banner.innerHTML = '<i class="fas fa-exclamation-circle" style="font-size:16px;flex-shrink:0;"></i><span class="low-balance-msg"></span><button onclick="window.hideLowBalanceBanner()" style="margin-left:auto;background:none;border:none;color:#fff;font-size:16px;cursor:pointer;padding:0;">×</button>';
            const container = document.querySelector('.bsms-wrapper') || document.querySelector('.container-fluid') || document.body;
            container.insertBefore(banner, container.firstChild);
        }
        banner.querySelector('.low-balance-msg').innerHTML = html;
        banner.style.display = 'flex';
    }

    window.hideLowBalanceBanner = function() {
        const b = document.getElementById('lowBalanceBanner');
        if (b) b.style.display = 'none';
    };

    // Auto-load balance on page ready
    setTimeout(loadSmsBalance, 300);

document.addEventListener('DOMContentLoaded', function() {

    // ─────────────────────────────────────────────────────────────
    // TAB SWITCHING
    // ─────────────────────────────────────────────────────────────
    document.querySelectorAll('.bsms-tablink').forEach(btn => {
        btn.addEventListener('click', function() {
            document.querySelectorAll('.bsms-tablink').forEach(b => b.classList.remove('active'));
            document.querySelectorAll('.bsms-tab-content').forEach(t => t.classList.remove('active'));
            this.classList.add('active');
            document.getElementById('tab-' + this.dataset.tab).classList.add('active');
        });
    });

    // ─────────────────────────────────────────────────────────────
    // COMPOSE: SELECTION BUTTONS
    // ─────────────────────────────────────────────────────────────
    let currentRecipients = 0;

    document.querySelectorAll('.sel-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            document.querySelectorAll('.sel-btn').forEach(b => b.classList.remove('active'));
            this.classList.add('active');
            const val = this.dataset.val;
            document.getElementById('selectionTypeInput').value = val;

            document.querySelectorAll('.sub-filter-panel').forEach(p => p.classList.remove('show'));
            const pnl = document.getElementById('pnl_' + val);
            if (pnl) pnl.classList.add('show');

            if (val === 'individual') fetchIndividualCustomers();
            refreshCount();
        });
    });

    // Sub-filter changes
    ['categorySelect','buyerStatusSelect','regionSelect','salesRepSelect','dateFromInput'].forEach(id => {
        const el = document.getElementById(id);
        if (el) el.addEventListener('change', refreshCount);
    });

    document.querySelectorAll('input[name="order_period"]').forEach(r => {
        r.addEventListener('change', function() {
            const show = this.value === 'custom';
            document.getElementById('orderCustomDates').style.setProperty('display', show ? 'flex' : 'none', 'important');
            refreshCount();
        });
    });

    document.querySelectorAll('input[name="lead_period"]').forEach(r => {
        r.addEventListener('change', function() {
            const show = this.value === 'custom';
            document.getElementById('leadCustomDates').style.setProperty('display', show ? 'flex' : 'none', 'important');
            refreshCount();
        });
    });

    ['orderDateFrom','orderDateTo','leadDateFrom','leadDateTo'].forEach(id => {
        const el = document.getElementById(id);
        if (el) el.addEventListener('change', refreshCount);
    });

    // ─────────────────────────────────────────────────────────────
    // INDIVIDUAL SEARCH
    // ─────────────────────────────────────────────────────────────
    let searchTimer;
    const searchInput = document.getElementById('customerSearchInput');
    if (searchInput) {
        searchInput.addEventListener('input', function() {
            clearTimeout(searchTimer);
            searchTimer = setTimeout(fetchIndividualCustomers, 300);
        });
    }

    function fetchIndividualCustomers() {
        const q = searchInput ? searchInput.value : '';
        fetch("{{ route('admin.bulk-sms.customers') }}?selection_type=all&search=" + encodeURIComponent(q))
            .then(r => r.json()).then(data => {
                const list = document.getElementById('customerChecklist');
                if (data.success && data.customers.length) {
                    list.innerHTML = data.customers.map(c => `
                        <div class="form-check py-1 border-bottom" style="border-color:#f1f5f9!important;">
                            <input class="form-check-input" type="checkbox" name="selected_ids[]" value="${c.id}" id="cst_${c.id}" onchange="refreshCount()">
                            <label class="form-check-label" for="cst_${c.id}" style="font-size:.78rem;">
                                <strong>${c.name}</strong> — ${c.phone || 'no phone'} ${c.company_name ? '('+c.company_name+')' : ''}
                            </label>
                        </div>`).join('');
                } else {
                    list.innerHTML = '<div style="text-align:center;padding:12px;color:#94a3b8;font-size:.8rem;">No customers found.</div>';
                }
            });
    }

    // ─────────────────────────────────────────────────────────────
    // RECIPIENT COUNT
    // ─────────────────────────────────────────────────────────────
    let countTimer;
    function refreshCount() {
        document.getElementById('liveCountNum').textContent = '…';
        clearTimeout(countTimer);
        countTimer = setTimeout(doCount, 400);
    }

    function doCount() {
        const form = document.getElementById('smsComposeForm');
        const params = new URLSearchParams(new FormData(form));
        fetch("{{ route('admin.bulk-sms.customers') }}?" + params.toString())
            .then(r => r.json()).then(data => {
                if (data.success) {
                    currentRecipients = data.total_count;
                    document.getElementById('liveCountNum').textContent = data.total_count.toLocaleString();
                    document.getElementById('recipientBadge').textContent = data.total_count.toLocaleString() + ' Recipients';
                    document.getElementById('summaryRecipients').textContent = data.total_count.toLocaleString();
                    recalcUnits();
                }
            }).catch(() => { document.getElementById('liveCountNum').textContent = '—'; });
    }

    // ─────────────────────────────────────────────────────────────
    // CHAR COUNTER + PHONE PREVIEW
    // ─────────────────────────────────────────────────────────────
    const smsBody = document.getElementById('smsBody');
    smsBody.addEventListener('input', function() {
        const len = this.value.length;
        const parts = Math.max(1, Math.ceil(len / 160));
        const pct = Math.min(100, Math.round((len % 160 || 160) / 160 * 100));

        document.getElementById('charCount').textContent = len;
        document.getElementById('charBar').style.width = pct + '%';
        document.getElementById('charBar').style.background = parts > 1 ? '#f59e0b' : '#dc2626';
        document.getElementById('smsParts').textContent = parts + (parts > 1 ? ' parts' : ' part');
        document.getElementById('unitsPerRec').textContent = parts;
        document.getElementById('summaryParts').textContent = parts;

        // Phone preview
        const preview = this.value.trim() || null;
        const bubble = document.getElementById('phoneBubble');
        if (preview) {
            bubble.textContent = preview;
            bubble.style.color = '#0f172a';
            bubble.style.fontStyle = 'normal';
        } else {
            bubble.textContent = 'Your message preview will appear here...';
            bubble.style.color = '#94a3b8';
            bubble.style.fontStyle = 'italic';
        }
        document.getElementById('phoneTime').textContent = new Date().toLocaleTimeString([], {hour:'2-digit',minute:'2-digit'});

        recalcUnits();
    });

    function recalcUnits() {
        const len = smsBody.value.length;
        const parts = Math.max(1, Math.ceil(len / 160));
        const total = currentRecipients * parts;
        document.getElementById('totalUnits').textContent = total.toLocaleString();
        document.getElementById('summaryUnits').textContent = total.toLocaleString();
    }

    // ─────────────────────────────────────────────────────────────
    // TEMPLATE CHIPS
    // ─────────────────────────────────────────────────────────────
    document.querySelectorAll('.tpl-chip').forEach(chip => {
        chip.addEventListener('click', function() {
            smsBody.value = this.dataset.content;
            smsBody.dispatchEvent(new Event('input'));
        });
    });

    // ─────────────────────────────────────────────────────────────
    // INSERT VARIABLE INTO COMPOSE
    // ─────────────────────────────────────────────────────────────
    window.insertVar = function(v) {
        const ta = smsBody;
        const start = ta.selectionStart, end = ta.selectionEnd;
        const text = ta.value;
        ta.value = text.slice(0, start) + v + text.slice(end);
        ta.selectionStart = ta.selectionEnd = start + v.length;
        ta.focus();
        ta.dispatchEvent(new Event('input'));
    };

    // ─────────────────────────────────────────────────────────────
    // SCHEDULE TOGGLE
    // ─────────────────────────────────────────────────────────────
    document.getElementById('scheduleToggle').addEventListener('change', function() {
        const pnl = document.getElementById('schedulePnl');
        if (this.checked) pnl.classList.add('show');
        else pnl.classList.remove('show');
    });

    // ─────────────────────────────────────────────────────────────
    // PREVIEW BUTTON
    // ─────────────────────────────────────────────────────────────
    const previewModal = new bootstrap.Modal(document.getElementById('previewModal'));
    const successModal = new bootstrap.Modal(document.getElementById('successModal'));

    document.getElementById('btnPreview').addEventListener('click', function() {
        const msg = smsBody.value.trim();
        if (!msg) { showToast('error', 'Please write a message first.'); return; }
        const parts = Math.max(1, Math.ceil(msg.length / 160));
        document.getElementById('mdlRecipients').textContent = currentRecipients.toLocaleString();
        document.getElementById('mdlParts').textContent = parts;
        document.getElementById('mdlUnits').textContent = (currentRecipients * parts).toLocaleString();
        document.getElementById('mdlMessage').textContent = msg;
        previewModal.show();
    });

    document.getElementById('btnConfirmSend').addEventListener('click', function() {
        previewModal.hide();
        executeSend();
    });

    document.getElementById('btnSend').addEventListener('click', executeSend);

    // ─────────────────────────────────────────────────────────────
    // SEND
    // ─────────────────────────────────────────────────────────────
    function executeSend() {
        const msg = smsBody.value.trim();
        if (!msg) { showToast('error', 'Please enter an SMS message.'); return; }
        if (currentRecipients === 0) { showToast('error', 'No recipients selected.'); return; }

        if (!confirm('Send bulk SMS to ' + currentRecipients.toLocaleString() + ' recipient(s)?')) return;

        const btn = document.getElementById('btnSend');
        btn.disabled = true;
        btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Sending...';

        const form = document.getElementById('smsComposeForm');
        const formData = new FormData(form);

        fetch("{{ route('admin.bulk-sms.send') }}", {
            method: 'POST',
            body: formData,
            headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' }
        })
        .then(r => r.json())
        .then(data => {
            btn.disabled = false;
            btn.innerHTML = '<i class="fas fa-paper-plane"></i> Send Now';
            if (data.success) {
                document.getElementById('sucRecipients').textContent = (data.total_recipients || 0).toLocaleString();
                document.getElementById('sucSent').textContent = (data.total_sms_sent || 0).toLocaleString();
                document.getElementById('sucDate').textContent = data.date || new Date().toLocaleString();
                successModal.show();
            } else {
                showToast('error', data.message || 'Send failed. Check server logs.');
            }
        })
        .catch(() => {
            btn.disabled = false;
            btn.innerHTML = '<i class="fas fa-paper-plane"></i> Send Now';
            showToast('error', 'Network error. Please try again.');
        });
    }

    // ─────────────────────────────────────────────────────────────
    // AUTO SMS TOGGLES
    // ─────────────────────────────────────────────────────────────
    document.querySelectorAll('.auto-toggle').forEach(toggle => {
        toggle.addEventListener('change', function() {
            const key = this.dataset.trigger;
            const badge = document.getElementById('badge_' + key);
            if (this.checked) {
                badge.textContent = 'ON';
                badge.className = 'atc-badge on';
            } else {
                badge.textContent = 'OFF';
                badge.className = 'atc-badge off';
            }
        });
    });

    window.insertAutoVar = function(trigger, v) {
        const ta = document.getElementById('tpl_' + trigger);
        if (!ta) return;
        const s = ta.selectionStart, e = ta.selectionEnd;
        ta.value = ta.value.slice(0, s) + v + ta.value.slice(e);
        ta.selectionStart = ta.selectionEnd = s + v.length;
        ta.focus();
    };

    window.saveSingleTrigger = function(trigger) {
        const enabled = document.querySelector('.auto-toggle[data-trigger="' + trigger + '"]').checked;
        const template = document.getElementById('tpl_' + trigger).value;
        const settings = {};
        settings[trigger] = { enabled, template };
        saveAutoSettingsData(settings, 'Saved!');
    };

    window.saveAllAutoSettings = function() {
        const settings = {};
        document.querySelectorAll('.auto-toggle').forEach(toggle => {
            const k = toggle.dataset.trigger;
            settings[k] = {
                enabled: toggle.checked,
                template: (document.getElementById('tpl_' + k) || {}).value || ''
            };
        });
        saveAutoSettingsData(settings, 'All auto SMS settings saved!');
    };

    function saveAutoSettingsData(settings, successMsg) {
        fetch("{{ route('admin.bulk-sms.auto-settings.save') }}", {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify({ settings })
        })
        .then(r => r.json())
        .then(data => {
            if (data.success) showToast('success', successMsg);
            else showToast('error', data.message || 'Save failed.');
        })
        .catch(() => showToast('error', 'Network error saving settings.'));
    }

    // ─────────────────────────────────────────────────────────────
    // TEST SMS
    // ─────────────────────────────────────────────────────────────
    let currentTestTrigger = null;
    const testModal = new bootstrap.Modal(document.getElementById('testSmsModal'));

    window.testTrigger = function(trigger, title) {
        currentTestTrigger = trigger;
        document.getElementById('testSmsTitle').textContent = 'Testing: ' + title;
        document.getElementById('testPhoneInput').value = '';
        const errBox = document.getElementById('testSmsError');
        if (errBox) errBox.style.display = 'none';
        testModal.show();
    };

    document.getElementById('btnSendTest').addEventListener('click', function() {
        const phone = document.getElementById('testPhoneInput').value.trim();
        if (!phone) { showToast('error', 'Please enter a phone number.'); return; }
        const template = (document.getElementById('tpl_' + currentTestTrigger) || {}).value || 'Test SMS from Chibobrand.';

        // Fill placeholders with sample values for the test
        const now = new Date();
        const msg = template
            .replace(/{name}/g, 'Test Customer')
            .replace(/{task_code}/g, 'TSK-TEST-001')
            .replace(/{task_title}/g, 'Sample Design Task')
            .replace(/{amount}/g, '150,000')
            .replace(/{paid}/g, '100,000')
            .replace(/{balance}/g, '50,000')
            .replace(/{pickup_code}/g, 'PICKUP123')
            .replace(/{deadline}/g, now.toLocaleDateString('en-GB'))
            .replace(/{seller_contact}/g, '0753883382');

        this.disabled = true;
        this.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';

        fetch("{{ route('admin.bulk-sms.test-send') }}", {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
            body: JSON.stringify({ phone: phone, message: msg })
        })
        .then(r => r.json())
        .then(data => {
            this.disabled = false;
            this.innerHTML = '<i class="fas fa-paper-plane"></i> Send Test';
            if (data.success) {
                testModal.hide();
                showToast('success', 'Test SMS sent successfully to ' + phone);
                // Refresh balance after sending
                setTimeout(loadSmsBalance, 1000);
            } else {
                // Show the real error from Beem Africa inside the modal
                const errBox = document.getElementById('testSmsError');
                if (errBox) {
                    errBox.querySelector('span').textContent = data.message || 'Send failed. Check API credentials and account balance.';
                    errBox.style.display = 'block';
                } else {
                    showToast('error', data.message || 'Test SMS failed. Check Beem Africa balance.');
                }
            }
        })
        .catch(() => {
            this.disabled = false;
            this.innerHTML = '<i class="fas fa-paper-plane"></i> Send Test';
            showToast('error', 'Network error. Please try again.');
        });
    });

    // ─────────────────────────────────────────────────────────────
    // CHARTS
    // ─────────────────────────────────────────────────────────────
    const monthlyCtx = document.getElementById('chartMonthly');
    if (monthlyCtx) {
        new Chart(monthlyCtx, {
            type: 'line',
            data: {
                labels: {!! json_encode($stats['monthly_labels'] ?? []) !!},
                datasets: [{
                    label: 'SMS Sent',
                    data: {!! json_encode($stats['monthly_data'] ?? []) !!},
                    borderColor: '#dc2626',
                    backgroundColor: 'rgba(220,38,38,.07)',
                    borderWidth: 2.5,
                    fill: true,
                    tension: 0.4,
                    pointBackgroundColor: '#dc2626',
                    pointRadius: 5,
                    pointHoverRadius: 7,
                }]
            },
            options: {
                responsive: true, maintainAspectRatio: false,
                plugins: { legend: { display: false }, tooltip: { callbacks: { label: ctx => ' ' + ctx.raw.toLocaleString() + ' SMS' } } },
                scales: {
                    y: { beginAtZero: true, grid: { color: '#f1f5f9' }, ticks: { precision:0 } },
                    x: { grid: { display: false } }
                }
            }
        });
    }

    const statusCtx = document.getElementById('chartStatus');
    if (statusCtx) {
        const c = {{ $stats['status_completed'] ?? 0 }};
        const p = {{ $stats['status_pending'] ?? 0 }};
        const f = {{ $stats['status_failed'] ?? 0 }};
        new Chart(statusCtx, {
            type: 'doughnut',
            data: {
                labels: ['Delivered','Pending','Failed'],
                datasets: [{ data: [c, p, f], backgroundColor: ['#16a34a','#f59e0b','#ef4444'], borderWidth: 2, borderColor: '#fff' }]
            },
            options: {
                responsive: true, maintainAspectRatio: false,
                cutout: '72%',
                plugins: { legend: { display: false }, tooltip: { callbacks: { label: ctx => ' ' + ctx.label + ': ' + ctx.raw.toLocaleString() } } }
            }
        });
    }

    // ─────────────────────────────────────────────────────────────
    // TOAST HELPER
    // ─────────────────────────────────────────────────────────────
    function showToast(type, msg) {
        const el = document.getElementById(type === 'success' ? 'toastSuccess' : 'toastError');
        const msgEl = document.getElementById(type === 'success' ? 'toastSuccessMsg' : 'toastErrorMsg');
        msgEl.textContent = msg;
        el.style.display = 'block';
        setTimeout(() => el.style.display = 'none', 3500);
    }

    // ─────────────────────────────────────────────────────────────
    // INIT
    // ─────────────────────────────────────────────────────────────
    refreshCount();
});
</script>
@endpush
