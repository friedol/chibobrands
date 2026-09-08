@extends('layouts.admin')

@section('title', 'Zoho Comparison')

@push('styles')
<style>
    .zoho-header { background: #f8fafc; border: 1px solid #e2e8f0; color: #1e293b; border-radius: 14px; padding: 22px 26px; margin-bottom: 26px; }
    .upload-zone { border: 2px dashed #cbd5e1; border-radius: 12px; padding: 40px 20px; text-align: center; background: #f8fafc; transition: border-color .2s; cursor: pointer; }
    .upload-zone:hover, .upload-zone.drag { border-color: #dc2626; background: #fff5f5; }
    .upload-zone i { font-size: 2.5rem; color: #94a3b8; margin-bottom: 12px; }
    .compare-table th { font-size: .72rem; text-transform: uppercase; letter-spacing: .3px; padding: 10px 14px; font-weight: 700; color: #64748b; background: #f8fafc; border: none; }
    .compare-table td { font-size: .82rem; padding: 9px 14px; border-color: #f1f5f9; vertical-align: middle; }
    .diff-pos { color: #16a34a; font-weight: 700; }
    .diff-neg { color: #dc2626; font-weight: 700; }
    .sum-box { border: 1.5px solid #e2e8f0; border-radius: 10px; padding: 14px 18px; text-align: center; }
    .tab-section { border: 1px solid #e2e8f0; border-radius: 12px; overflow: hidden; box-shadow: 0 2px 12px rgba(0,0,0,.04); margin-bottom: 20px; background: #ffffff; }
    .tab-section .card-header { background: #ffffff; color: #1e293b; border-bottom: 1px solid #e2e8f0; padding: 11px 18px; font-weight: 700; font-size: .82rem; text-transform: uppercase; letter-spacing: .4px; }
</style>
@endpush

@section('content')
<div class="container-fluid py-4">

    {{-- Header --}}
    <div class="zoho-header d-flex justify-content-between align-items-center">
        <div>
            <h5 class="fw-bold mb-1" style="color: #1e293b;"><i class="fas fa-code-branch me-2" style="color:#f87171;"></i>Zoho vs. System Comparison</h5>
            <p class="mb-0 text-muted" style="font-size:.82rem;">Upload a Zoho CSV export to compare daily totals with our system. No data is modified — read-only analysis.</p>
        </div>
        <div>
            <a href="{{ route('admin.finance.verification-dashboard') }}" class="btn btn-outline-secondary btn-sm">
                <i class="fas fa-shield-alt me-1"></i> Verification Dashboard
            </a>
        </div>
    </div>

    {{-- Upload Form --}}
    <div class="card border-0 shadow-sm rounded-3 mb-4">
        <div class="card-body p-4">
            <h6 class="fw-bold mb-3">Upload Zoho Export</h6>
            <form action="{{ route('admin.finance.zoho-compare') }}" method="POST" enctype="multipart/form-data" id="zohoForm">
                @csrf
                <div class="row g-3 align-items-end">
                    <div class="col-md-4">
                        <label class="form-label fw-semibold small">Zoho CSV File <span class="text-danger">*</span></label>
                        <div class="upload-zone" id="uploadZone" onclick="document.getElementById('zohoFile').click()">
                            <i class="fas fa-file-csv d-block mb-2"></i>
                            <div class="fw-semibold" id="fileLabel">Click or drag a CSV file here</div>
                            <div class="text-muted small mt-1">Supported: .csv — Columns: Date, Amount, Reference, Customer</div>
                        </div>
                        <input type="file" name="zoho_file" id="zohoFile" class="d-none" accept=".csv,.txt">
                        @error('zoho_file')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-3">
                        <label class="form-label fw-semibold small">System Date Range From</label>
                        <input type="date" name="date_from" class="form-control" value="{{ $dateFrom }}">
                        @error('date_from')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-3">
                        <label class="form-label fw-semibold small">System Date Range To</label>
                        <input type="date" name="date_to" class="form-control" value="{{ $dateTo }}">
                        @error('date_to')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-2">
                        <button type="submit" class="btn btn-danger w-100 fw-bold" id="compareBtn">
                            <i class="fas fa-balance-scale me-1"></i> Compare
                        </button>
                    </div>
                </div>
            </form>

            <div class="mt-3 p-3 rounded" style="background:#fff5f5;border:1px solid #fecaca;font-size:.8rem;">
                <strong><i class="fas fa-info-circle text-danger me-1"></i>Expected CSV Format:</strong>
                Date, Amount, Reference, Account/Customer<br>
                <code style="font-size:.72rem;">2024-01-15,500000,INV-001,John Doe</code>
            </div>
        </div>
    </div>

    {{-- Results --}}
    @if($result)

    {{-- Summary Row --}}
    <div class="row g-3 mb-4">
        @php $s = $result['summary']; @endphp
        @foreach([
            ['Total Days','total_days','dark'],
            ['Matched','matched_days','success'],
            ['Discrepancies','discrepancy_days','danger'],
            ['Only in Zoho','only_zoho_days','warning'],
            ['Only in System','only_system_days','info'],
        ] as [$label, $key, $color])
        <div class="col">
            <div class="sum-box">
                <div class="fw-bold fs-4 text-{{ $color }}">{{ $s[$key] }}</div>
                <div class="text-muted small">{{ $label }}</div>
            </div>
        </div>
        @endforeach
    </div>

    <div class="row g-3 mb-4">
        <div class="col-md-4">
            <div class="sum-box">
                <div class="text-muted small mb-1">System Total (Period)</div>
                <div class="fw-bold fs-5 text-dark">TZS {{ number_format($s['total_system']) }}</div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="sum-box">
                <div class="text-muted small mb-1">Zoho Total</div>
                <div class="fw-bold fs-5 text-primary">TZS {{ number_format($s['total_zoho']) }}</div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="sum-box">
                <div class="text-muted small mb-1">Net Difference</div>
                <div class="fw-bold fs-5 {{ abs($s['net_difference']) < 1 ? 'text-success' : ($s['net_difference'] > 0 ? 'text-danger' : 'text-warning') }}">
                    {{ $s['net_difference'] >= 0 ? '+' : '' }}TZS {{ number_format($s['net_difference']) }}
                </div>
            </div>
        </div>
    </div>

    {{-- Discrepancies Table --}}
    @if($result['discrepancies']->isNotEmpty())
    <div class="tab-section card">
        <div class="card-header d-flex justify-content-between">
            <span><i class="fas fa-exclamation-triangle me-2" style="color:#f87171;"></i>Discrepancies ({{ $result['discrepancies']->count() }} days)</span>
        </div>
        <div class="table-responsive">
            <table class="table mb-0 compare-table">
                <thead><tr><th>Date</th><th>System Total</th><th>Zoho Total</th><th>Difference</th></tr></thead>
                <tbody>
                    @foreach($result['discrepancies'] as $d)
                    <tr style="background:#fff5f5;">
                        <td class="fw-bold">{{ $d['date'] }}</td>
                        <td>TZS {{ number_format($d['system_total']) }}</td>
                        <td>TZS {{ number_format($d['zoho_total']) }}</td>
                        <td class="{{ $d['difference'] > 0 ? 'diff-pos' : 'diff-neg' }}">
                            {{ $d['difference'] >= 0 ? '+' : '' }}TZS {{ number_format($d['difference']) }}
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    @endif

    {{-- Matched Table --}}
    @if($result['matched']->isNotEmpty())
    <div class="tab-section card">
        <div class="card-header"><i class="fas fa-check-circle me-2 text-success"></i>Matched Days ({{ $result['matched']->count() }})</div>
        <div class="table-responsive">
            <table class="table mb-0 compare-table">
                <thead><tr><th>Date</th><th>System Total</th><th>Zoho Total</th><th>Status</th></tr></thead>
                <tbody>
                    @foreach($result['matched'] as $m)
                    <tr>
                        <td>{{ $m['date'] }}</td>
                        <td>TZS {{ number_format($m['system_total']) }}</td>
                        <td>TZS {{ number_format($m['zoho_total']) }}</td>
                        <td><span class="badge bg-success">✓ Match</span></td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    @endif

    {{-- Only in Zoho / Only in System --}}
    <div class="row g-3">
        @if($result['only_in_zoho']->isNotEmpty())
        <div class="col-md-6">
            <div class="tab-section card">
                <div class="card-header"><i class="fas fa-cloud me-2 text-warning"></i>Only in Zoho ({{ $result['only_in_zoho']->count() }})</div>
                <div class="table-responsive">
                    <table class="table mb-0 compare-table">
                        <thead><tr><th>Date</th><th>Zoho Total</th></tr></thead>
                        <tbody>
                            @foreach($result['only_in_zoho'] as $r)
                            <tr><td>{{ $r['date'] }}</td><td class="text-warning fw-bold">TZS {{ number_format($r['zoho_total']) }}</td></tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        @endif
        @if($result['only_in_system']->isNotEmpty())
        <div class="col-md-6">
            <div class="tab-section card">
                <div class="card-header"><i class="fas fa-server me-2 text-info"></i>Only in System ({{ $result['only_in_system']->count() }})</div>
                <div class="table-responsive">
                    <table class="table mb-0 compare-table">
                        <thead><tr><th>Date</th><th>System Total</th></tr></thead>
                        <tbody>
                            @foreach($result['only_in_system'] as $r)
                            <tr><td>{{ $r['date'] }}</td><td class="text-info fw-bold">TZS {{ number_format($r['system_total']) }}</td></tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        @endif
    </div>

    @endif {{-- end $result --}}

</div>
@endsection

@push('scripts')
<script>
    const fileInput = document.getElementById('zohoFile');
    const label = document.getElementById('fileLabel');
    const zone = document.getElementById('uploadZone');

    fileInput.addEventListener('change', function() {
        label.textContent = this.files[0] ? this.files[0].name : 'Click or drag a CSV file here';
    });

    zone.addEventListener('dragover', e => { e.preventDefault(); zone.classList.add('drag'); });
    zone.addEventListener('dragleave', () => zone.classList.remove('drag'));
    zone.addEventListener('drop', e => {
        e.preventDefault();
        zone.classList.remove('drag');
        fileInput.files = e.dataTransfer.files;
        label.textContent = fileInput.files[0]?.name || 'Click or drag a CSV file here';
    });

    document.getElementById('zohoForm').addEventListener('submit', function() {
        const btn = document.getElementById('compareBtn');
        btn.disabled = true;
        btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Comparing…';
    });
</script>
@endpush
