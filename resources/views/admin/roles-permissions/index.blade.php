@extends('layouts.admin')

@section('page-title', 'Roles & Permissions')

@push('styles')
<style>
    /* ── Page Layout ── */
    .rp-table-wrap {
        overflow-x: auto;
        border-radius: 12px;
        box-shadow: 0 2px 12px rgba(0,0,0,.06);
        background: #fff;
        border: 1px solid #e9ecef;
    }

    /* ── Matrix Table ── */
    .rp-table {
        width: 100%;
        border-collapse: separate;
        border-spacing: 0;
        min-width: 900px;
        font-size: 13px;
    }

    /* Role Header Cells */
    .rp-table thead th {
        background: #fff;
        padding: 0;
        border-bottom: 2px solid #e9ecef;
        vertical-align: top;
        white-space: nowrap;
    }

    .rp-table thead th:first-child {
        position: sticky;
        left: 0;
        z-index: 10;
        background: #fff;
        min-width: 200px;
        border-right: 2px solid #e9ecef;
    }

    .role-th-inner {
        padding: 1rem 0.75rem 0.75rem;
        display: flex;
        flex-direction: column;
        align-items: center;
        gap: 0.35rem;
        min-width: 100px;
    }

    .role-icon-wrap {
        width: 36px;
        height: 36px;
        border-radius: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1rem;
    }

    .role-th-name {
        font-size: 0.72rem;
        font-weight: 700;
        color: #1e293b;
        text-align: center;
        line-height: 1.2;
    }

    .role-th-count {
        font-size: 0.65rem;
        color: #94a3b8;
        font-weight: 500;
    }

    .btn-save-col {
        font-size: 0.7rem;
        padding: 0.25rem 0.75rem;
        border-radius: 20px;
        font-weight: 600;
        white-space: nowrap;
    }

    /* Permission Label Column (sticky) */
    .perm-label-cell {
        position: sticky;
        left: 0;
        background: #fff;
        z-index: 5;
        border-right: 2px solid #e9ecef;
        padding: 0.6rem 1rem;
        white-space: nowrap;
    }

    /* Category separator rows */
    .cat-row td {
        background: #f8fafc;
        padding: 0.4rem 1rem;
        font-size: 0.65rem;
        font-weight: 800;
        text-transform: uppercase;
        letter-spacing: 0.07em;
        color: #64748b;
        border-top: 1px solid #e9ecef;
        border-bottom: 1px solid #e9ecef;
    }

    .cat-row td:first-child {
        position: sticky;
        left: 0;
        z-index: 5;
        background: #f8fafc;
        border-right: 2px solid #e9ecef;
    }

    /* Permission rows */
    .perm-row-tr:hover .perm-label-cell,
    .perm-row-tr:hover td {
        background: #f8faff;
    }

    .perm-row-tr td {
        border-bottom: 1px solid #f1f5f9;
        vertical-align: middle;
        text-align: center;
        padding: 0.55rem 0.5rem;
    }

    .perm-label {
        display: flex;
        align-items: center;
        gap: 0.6rem;
    }

    .perm-label-icon {
        width: 26px;
        height: 26px;
        border-radius: 7px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 0.7rem;
        flex-shrink: 0;
    }

    .perm-label-text {
        font-size: 0.8rem;
        font-weight: 500;
        color: #334155;
        line-height: 1.2;
    }

    /* Toggle Switch */
    .sw {
        position: relative;
        display: inline-block;
        width: 34px;
        height: 18px;
    }

    .sw input { opacity: 0; width: 0; height: 0; }

    .sw-track {
        position: absolute;
        inset: 0;
        background: #e2e8f0;
        border-radius: 18px;
        cursor: pointer;
        transition: .25s;
    }

    .sw-track:before {
        content: '';
        position: absolute;
        width: 12px;
        height: 12px;
        left: 3px;
        top: 3px;
        background: #fff;
        border-radius: 50%;
        transition: .25s;
        box-shadow: 0 1px 2px rgba(0,0,0,.15);
    }

    .sw input:checked + .sw-track { background: #0d6efd; }
    .sw input:checked + .sw-track:before { transform: translateX(16px); }
    .sw input:disabled + .sw-track { opacity: .55; cursor: default; }

    /* Locked badge for super_admin */
    .locked-badge {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 22px;
        height: 22px;
        background: #fef2f2;
        border-radius: 6px;
        color: #dc2626;
        font-size: 0.65rem;
    }

    /* Role colour helpers */
    .ri-danger    { background: #fef2f2; color: #dc2626; }
    .ri-primary   { background: #eef2ff; color: #4338ca; }
    .ri-success   { background: #ecfdf5; color: #059669; }
    .ri-warning   { background: #fffbeb; color: #d97706; }
    .ri-pink      { background: #fff1f2; color: #e11d48; }
    .ri-teal      { background: #f0fdfa; color: #0d9488; }
    .ri-cyan      { background: #ecfeff; color: #0891b2; }
    .ri-purple    { background: #f5f3ff; color: #7c3aed; }
    .ri-orange    { background: #fff7ed; color: #ea580c; }
    .ri-dark      { background: #f1f5f9; color: #334155; }

    /* Permission icon colour helpers */
    .pi-blue   { background: #eff6ff; color: #2563eb; }
    .pi-green  { background: #f0fdf4; color: #16a34a; }
    .pi-amber  { background: #fffbeb; color: #d97706; }
    .pi-red    { background: #fff1f2; color: #e11d48; }
    .pi-purple { background: #f5f3ff; color: #7c3aed; }
    .pi-teal   { background: #f0fdfa; color: #0d9488; }
    .pi-slate  { background: #f1f5f9; color: #475569; }

    /* Save feedback */
    .save-ok  { color: #059669; font-size: 0.7rem; font-weight: 600; display: none; }
    .save-err { color: #dc2626; font-size: 0.7rem; font-weight: 600; display: none; }

    /* Responsive hint */
    .scroll-hint { font-size: 0.75rem; color: #94a3b8; }

    /* Summary bar */
    .role-summary-bar {
        display: flex;
        gap: 0.5rem;
        flex-wrap: wrap;
        margin-bottom: 1.25rem;
    }
    .rsb-item {
        display: inline-flex;
        align-items: center;
        gap: 0.4rem;
        padding: 0.35rem 0.75rem;
        border-radius: 8px;
        font-size: 0.75rem;
        font-weight: 600;
        border: 1px solid #e9ecef;
        background: #fff;
    }
    .rsb-dot {
        width: 8px;
        height: 8px;
        border-radius: 50%;
    }
</style>
@endpush

@section('content')
@php
    /* ── Permission catalogue ── */
    $permCatalogue = [
        'Administration' => [
            'icon'  => 'fa-shield-halved',
            'color' => 'pi-blue',
            'items' => [
                'manage_users'       => ['label' => 'Manage Users',    'icon' => 'fa-users',       'color' => 'pi-blue'],
                'manage_roles'       => ['label' => 'Manage Roles',    'icon' => 'fa-key',         'color' => 'pi-blue'],
                'manage_settings'    => ['label' => 'System Settings', 'icon' => 'fa-cog',         'color' => 'pi-slate'],
                'reset_passwords'    => ['label' => 'Reset Passwords', 'icon' => 'fa-lock-open',   'color' => 'pi-amber'],
                'view_audit_logs'    => ['label' => 'Audit Logs',      'icon' => 'fa-history',     'color' => 'pi-slate'],
                'manage_departments' => ['label' => 'Departments',     'icon' => 'fa-building',    'color' => 'pi-slate'],
            ],
        ],
        'Sales & Operations' => [
            'icon'  => 'fa-cart-shopping',
            'color' => 'pi-green',
            'items' => [
                'manage_orders'         => ['label' => 'Online Orders',        'icon' => 'fa-shopping-cart',  'color' => 'pi-green'],
                'delete_all_orders'     => ['label' => 'Delete All Orders',   'icon' => 'fa-trash-alt',      'color' => 'pi-red'],
                'manage_pos'            => ['label' => 'POS Terminal',        'icon' => 'fa-cash-register',  'color' => 'pi-green'],
                'manage_customers'      => ['label' => 'Customers',         'icon' => 'fa-user-friends',   'color' => 'pi-green'],
                'manage_leads'          => ['label' => 'Leads & Follow Up', 'icon' => 'fa-funnel-dollar',  'color' => 'pi-teal'],
                'manage_design_tasks'   => ['label' => 'Design Tasks',      'icon' => 'fa-paint-brush',    'color' => 'pi-purple'],
                'view_contact_messages' => ['label' => 'Contact Messages',  'icon' => 'fa-envelope',       'color' => 'pi-teal'],
            ],
        ],
        'Inventory & Products' => [
            'icon'  => 'fa-boxes-stacked',
            'color' => 'pi-amber',
            'items' => [
                'manage_products'    => ['label' => 'Products & Categories', 'icon' => 'fa-box',      'color' => 'pi-amber'],
                'manage_inventory'   => ['label' => 'Inventory & Stock',     'icon' => 'fa-warehouse','color' => 'pi-amber'],
                'manage_hero_slides' => ['label' => 'Hero Slides & Ads',     'icon' => 'fa-images',   'color' => 'pi-purple'],
            ],
        ],
        'Finance & Reporting' => [
            'icon'  => 'fa-wallet',
            'color' => 'pi-red',
            'items' => [
                'manage_finance'     => ['label' => 'Finance Module',   'icon' => 'fa-wallet',    'color' => 'pi-red'],
                'manage_reports'     => ['label' => 'Reports & Analytics','icon' => 'fa-chart-bar','color' => 'pi-red'],
                'manage_performance' => ['label' => 'Performance KPIs', 'icon' => 'fa-star',      'color' => 'pi-amber'],
            ],
        ],
        'HR Module' => [
            'icon'  => 'fa-user-tie',
            'color' => 'pi-teal',
            'items' => [
                'manage_hr' => ['label' => 'HR (Employees, Attendance, Leaves, KPIs)', 'icon' => 'fa-user-tie', 'color' => 'pi-teal'],
            ],
        ],
        'Marketing Module' => [
            'icon'  => 'fa-bullhorn',
            'color' => 'pi-red',
            'items' => [
                'manage_marketing' => ['label' => 'Marketing (Campaigns, Ads, Events, Reports)', 'icon' => 'fa-bullhorn', 'color' => 'pi-red'],
            ],
        ],
        'Logistics' => [
            'icon'  => 'fa-shipping-fast',
            'color' => 'pi-blue',
            'items' => [
                'manage_delivery'   => ['label' => 'Delivery Management',  'icon' => 'fa-shipping-fast', 'color' => 'pi-blue'],
                'manage_gatekeeper' => ['label' => 'Gatekeeper System',    'icon' => 'fa-door-open',     'color' => 'pi-slate'],
            ],
        ],
    ];

    /* ── Role display config ── */
    $roleConfig = [
        'super_admin'       => ['icon' => 'fa-shield-alt',         'color' => 'ri-danger',  'label' => 'Super Admin'],
        'admin'             => ['icon' => 'fa-user-shield',         'color' => 'ri-primary', 'label' => 'Admin'],
        'manager'           => ['icon' => 'fa-user-edit',           'color' => 'ri-success', 'label' => 'Manager'],
        'accountant'        => ['icon' => 'fa-file-invoice-dollar', 'color' => 'ri-warning', 'label' => 'Accountant'],
        'marketing_manager' => ['icon' => 'fa-bullhorn',            'color' => 'ri-pink',    'label' => 'Mkt. Manager'],
        'hr_officer'        => ['icon' => 'fa-user-tie',            'color' => 'ri-teal',    'label' => 'HR Officer'],
        'receptionist'      => ['icon' => 'fa-headset',             'color' => 'ri-cyan',    'label' => 'Receptionist'],
        'saler'             => ['icon' => 'fa-shopping-cart',       'color' => 'ri-purple',  'label' => 'Sales'],
        'operator'          => ['icon' => 'fa-tools',               'color' => 'ri-orange',  'label' => 'Operator'],
        'designer'          => ['icon' => 'fa-pen-nib',             'color' => 'ri-purple',  'label' => 'Designer'],
        'delivery'          => ['icon' => 'fa-shipping-fast',       'color' => 'ri-cyan',    'label' => 'Delivery'],
        'gatekeeper'        => ['icon' => 'fa-door-open',           'color' => 'ri-dark',    'label' => 'Gatekeeper'],
    ];
@endphp

<div class="container-fluid py-3">

    {{-- Page Header --}}
    <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-3">
        <div>
            <h4 class="fw-bold text-dark mb-1">Roles &amp; Permissions</h4>
            <p class="text-muted small mb-0">Configure what each role can access across the system</p>
        </div>
        <a href="{{ route('admin.admins.index') }}" class="btn btn-outline-secondary btn-sm px-4">
            <i class="fas fa-users-cog me-2"></i>Manage Users
        </a>
    </div>

    @if(session('success'))
    <div class="alert alert-success border-0 shadow-sm rounded-3 mb-3 d-flex align-items-center py-2" role="alert">
        <i class="fas fa-check-circle me-2 text-success"></i>
        <div class="small">{{ session('success') }}</div>
        <button type="button" class="btn-close btn-close-sm ms-auto" data-bs-dismiss="alert"></button>
    </div>
    @endif

    {{-- Role summary chips --}}
    <div class="role-summary-bar">
        @foreach($rolesPermissions as $rk => $rd)
            @php $cfg = $roleConfig[$rk] ?? ['icon' => 'fa-user-tag', 'color' => 'ri-dark', 'label' => $rd['name']]; @endphp
            <span class="rsb-item">
                <span class="rsb-dot" style="background: currentColor; opacity:.5;"></span>
                <i class="fas {{ $cfg['icon'] }} fa-xs"></i>
                {{ $rd['name'] }}
                <span class="badge bg-light text-secondary rounded-pill" style="font-size:0.65rem;">{{ $roleCounts[$rk] ?? 0 }}</span>
            </span>
        @endforeach
    </div>

    {{-- Scroll hint on mobile --}}
    <p class="scroll-hint d-md-none mb-2"><i class="fas fa-arrows-left-right me-1"></i>Scroll horizontally to see all roles</p>

    {{-- Matrix Table --}}
    <div class="rp-table-wrap">
        <table class="rp-table">
            <thead>
                <tr>
                    {{-- Permission column header --}}
                    <th>
                        <div class="role-th-inner align-items-start">
                            <span class="text-muted small fw-bold" style="font-size:0.7rem;">PERMISSION</span>
                        </div>
                    </th>

                    {{-- One column per role --}}
                    @foreach($rolesPermissions as $roleKey => $roleData)
                    @php $cfg = $roleConfig[$roleKey] ?? ['icon' => 'fa-user-tag', 'color' => 'ri-dark', 'label' => $roleData['name']]; @endphp
                    <th>
                        <div class="role-th-inner">
                            <div class="role-icon-wrap {{ $cfg['color'] }}">
                                <i class="fas {{ $cfg['icon'] }}"></i>
                            </div>
                            <div class="role-th-name">{{ $roleData['name'] }}</div>
                            <div class="role-th-count">{{ $roleCounts[$roleKey] ?? 0 }} user{{ ($roleCounts[$roleKey] ?? 0) != 1 ? 's' : '' }}</div>
                            @if($roleKey !== 'super_admin')
                                <button type="button"
                                    class="btn btn-primary btn-save-col"
                                    onclick="saveRole('{{ $roleKey }}')"
                                    id="save-btn-{{ $roleKey }}">
                                    Save
                                </button>
                                <span class="save-ok"  id="ok-{{ $roleKey }}"><i class="fas fa-check me-1"></i>Saved</span>
                                <span class="save-err" id="err-{{ $roleKey }}">Failed</span>
                            @else
                                <span class="locked-badge" title="Locked — root access"><i class="fas fa-lock"></i></span>
                            @endif
                        </div>
                    </th>
                    @endforeach
                </tr>
            </thead>

            <tbody>
                @foreach($permCatalogue as $catName => $catData)

                {{-- Category separator --}}
                <tr class="cat-row">
                    <td colspan="{{ count($rolesPermissions) + 1 }}">
                        <i class="fas {{ $catData['icon'] }} me-2"></i>{{ $catName }}
                    </td>
                </tr>

                {{-- Permission rows --}}
                @foreach($catData['items'] as $permKey => $permMeta)
                <tr class="perm-row-tr">
                    <td class="perm-label-cell">
                        <div class="perm-label">
                            <span class="perm-label-icon {{ $permMeta['color'] }}">
                                <i class="fas {{ $permMeta['icon'] }}"></i>
                            </span>
                            <span class="perm-label-text">{{ $permMeta['label'] }}</span>
                        </div>
                    </td>

                    @foreach($rolesPermissions as $roleKey => $roleData)
                    @php $hasIt = $roleData['permissions'][$permKey] ?? false; @endphp
                    <td>
                        @if($roleKey === 'super_admin')
                            {{-- Super admin: always on, locked --}}
                            <label class="sw">
                                <input type="checkbox" checked disabled>
                                <span class="sw-track"></span>
                            </label>
                        @else
                            <label class="sw">
                                <input type="checkbox"
                                    class="perm-toggle"
                                    data-role="{{ $roleKey }}"
                                    data-perm="{{ $permKey }}"
                                    {{ $hasIt ? 'checked' : '' }}>
                                <span class="sw-track"></span>
                            </label>
                        @endif
                    </td>
                    @endforeach
                </tr>
                @endforeach

                @endforeach
            </tbody>
        </table>
    </div>

    <p class="text-muted mt-3" style="font-size:0.75rem;">
        <i class="fas fa-info-circle me-1"></i>
        Changes take effect immediately after saving. Super Admin permissions are always locked to full access.
    </p>
</div>

<script>
function saveRole(roleKey) {
    const btn  = document.getElementById('save-btn-' + roleKey);
    const okEl = document.getElementById('ok-' + roleKey);
    const erEl = document.getElementById('err-' + roleKey);

    // Collect checked permissions for this role
    const permissions = {};
    document.querySelectorAll(`.perm-toggle[data-role="${roleKey}"]`).forEach(cb => {
        if (cb.checked) permissions[cb.dataset.perm] = '1';
    });

    btn.disabled = true;
    btn.textContent = '…';
    okEl.style.display = 'none';
    erEl.style.display = 'none';

    const formData = new FormData();
    formData.append('_method', 'PUT');
    formData.append('_token', '{{ csrf_token() }}');
    Object.entries(permissions).forEach(([k, v]) => formData.append(`permissions[${k}]`, v));

    fetch(`{{ url('admin/roles-permissions') }}/${roleKey}`, {
        method: 'POST',
        body: formData,
        headers: { 'X-Requested-With': 'XMLHttpRequest' }
    })
    .then(r => {
        btn.disabled = false;
        btn.textContent = 'Save';
        if (r.ok || r.redirected) {
            okEl.style.display = 'inline-flex';
            setTimeout(() => { okEl.style.display = 'none'; }, 3000);
        } else {
            erEl.style.display = 'inline-flex';
        }
    })
    .catch(() => {
        btn.disabled = false;
        btn.textContent = 'Save';
        erEl.style.display = 'inline-flex';
    });
}
</script>
@endsection
