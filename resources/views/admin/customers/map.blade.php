@extends('layouts.admin')

@section('title', 'Customer Map - CHIBO BRAND Admin')
@section('description', 'Customer distribution across Tanzania')

@push('styles')
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin=""/>
<style>
    #customerMapPage {
        background: #f8fafc;
        border-radius: 12px;
        box-shadow: inset 0 2px 4px rgba(0,0,0,0.06);
    }
    .leaflet-popup-content-wrapper {
        border-radius: 12px;
        box-shadow: 0 10px 25px rgba(0,0,0,0.15);
    }
    .leaflet-popup-content {
        font-family: 'Nunito Sans', sans-serif;
        font-size: 13px;
    }
    .x-small { font-size: 11px !important; }
    .ls-1 { letter-spacing: 0.5px; }
    body { font-size: 13px !important; }
    .badge { font-weight: 600; padding: 0.5em 0.8em; font-size: 11px !important; }
    .form-control, .form-select, .btn, label { font-size: 13px !important; }
    h4, .h4 { font-size: 14px !important; }
    .text-muted.small { font-size: 12px !important; }
    .map-point-card {
        transition: transform .15s ease, box-shadow .15s ease;
    }
    .map-point-card:hover {
        transform: translateY(-1px);
        box-shadow: 0 8px 18px rgba(15,23,42,.08);
    }
</style>
@endpush

@section('content')
<div class="container-fluid px-4 py-4">
    <!-- Header -->
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-4 gap-3">
        <div>
            <h4 class="fw-bold mb-2 text-dark">Customer Distribution Map</h4>
            <p class="text-muted small mb-0">Visualizing customer geography and density across regions</p>
        </div>
        <div class="d-flex gap-2">
            <a href="#mapFilters" class="btn btn-sm btn-outline-secondary rounded-2 fw-bold px-3 d-flex align-items-center gap-1">
                <i class="fas fa-sliders-h"></i>Map Filters
            </a>
            <a href="{{ route('admin.customers.create') }}" class="btn btn-sm btn-danger rounded-2 fw-bold px-3 d-flex align-items-center gap-1">
                <i class="fas fa-plus"></i>Add Customer
            </a>
            <a href="{{ route('admin.customers.index') }}" class="btn btn-primary d-flex align-items-center gap-2 shadow-sm">
                <i class="fas fa-list"></i>
                <span>Customer List</span>
            </a>
        </div>
    </div>

    @if(session('success'))
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        {{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    @endif

    @if(session('error'))
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        {{ session('error') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    @endif

    <div class="card border-0 shadow-sm mb-4" style="border-radius:10px; border:1px solid #e2e8f0;">
        <div class="card-body p-3">
            <h6 class="fw-bold mb-3 text-dark"><i class="fas fa-map-pin text-danger me-2"></i>Location Configuration</h6>
            <div class="row g-3">
                <div class="col-12 col-lg-6">
                    <div class="border rounded-3 p-3 h-100 bg-light">
                        <h6 class="fw-bold small text-uppercase text-muted mb-2">Add Region</h6>
                        <form action="{{ route('admin.customers.map.regions.store') }}" method="POST" class="row g-2" data-no-global-handler>
                            @csrf
                            <div class="col-12">
                                <label class="form-label fw-semibold small mb-1">Region Name <span class="text-danger">*</span></label>
                                <input type="text" name="region_name" class="form-control form-control-sm" placeholder="e.g. Arusha" required>
                            </div>
                            <div class="col-6">
                                <label class="form-label fw-semibold small mb-1">Latitude (Optional)</label>
                                <input type="text" name="latitude" class="form-control form-control-sm" placeholder="-3.3869">
                            </div>
                            <div class="col-6">
                                <label class="form-label fw-semibold small mb-1">Longitude (Optional)</label>
                                <input type="text" name="longitude" class="form-control form-control-sm" placeholder="36.6830">
                            </div>
                            <div class="col-12">
                                <button type="submit" class="btn btn-danger btn-sm fw-bold px-3">
                                    <i class="fas fa-plus me-1"></i>Save Region
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
                <div class="col-12 col-lg-6">
                    <div class="border rounded-3 p-3 h-100 bg-light">
                        <h6 class="fw-bold small text-uppercase text-muted mb-2">Add District To Region</h6>
                        <form action="{{ route('admin.customers.map.districts.store') }}" method="POST" class="row g-2" data-no-global-handler>
                            @csrf
                            <div class="col-12">
                                <label class="form-label fw-semibold small mb-1">Region <span class="text-danger">*</span></label>
                                <select name="region_id" class="form-select form-select-sm" required>
                                    <option value="">Select Region</option>
                                    @foreach($regions as $region)
                                        <option value="{{ $region->id }}" {{ (string)request('region_id') === (string)$region->id ? 'selected' : '' }}>{{ $region->region_name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-12">
                                <label class="form-label fw-semibold small mb-1">District Name <span class="text-danger">*</span></label>
                                <input type="text" name="district_name" class="form-control form-control-sm" placeholder="e.g. Arusha City" required>
                            </div>
                            <div class="col-12">
                                <button type="submit" class="btn btn-primary btn-sm fw-bold px-3">
                                    <i class="fas fa-plus me-1"></i>Save District
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- ── Map Filters (Always Visible) ── --}}
    <div class="mb-4" id="mapFilters">
        <div class="card filter-card border-0 shadow-sm" style="border-radius:10px; border:1px solid #e2e8f0; background:#f8fafc;">
            <div class="card-body p-3">
                <form action="{{ route('admin.customers.map') }}" method="GET" class="row g-2" data-no-global-handler>
                    <div class="col-12 col-md-3">
                        <label class="form-label fw-bold x-small text-uppercase mb-1" style="font-size:10px; color:#64748b;">Search Customers</label>
                        <div class="input-group input-group-sm">
                            <span class="input-group-text bg-white border-end-0"><i class="fas fa-search text-muted"></i></span>
                            <input type="text" name="search" class="form-control border-start-0"
                                   placeholder="Name, phone, email, company…" value="{{ request('search') }}">
                        </div>
                    </div>
                    <div class="col-6 col-md-2">
                        <label class="form-label fw-bold x-small text-uppercase mb-1" style="font-size:10px; color:#64748b;">Status / Segment</label>
                        <select name="status" class="form-select form-select-sm">
                            <option value="all">All Stages</option>
                            <option value="verified"   {{ request('status') == 'verified'   ? 'selected' : '' }}>Verified</option>
                            <option value="unverified" {{ request('status') == 'unverified' ? 'selected' : '' }}>Unverified</option>
                            <option value="wholesale"  {{ request('status') == 'wholesale'  ? 'selected' : '' }}>Wholesale Partner</option>
                            <option value="active"     {{ request('status') == 'active'     ? 'selected' : '' }}>Active Status</option>
                            <option value="new"        {{ request('status') == 'new'        ? 'selected' : '' }}>New Customer</option>
                            <option value="repeated"   {{ request('status') == 'repeated'   ? 'selected' : '' }}>Repeat Customer</option>
                        </select>
                    </div>
                    <div class="col-6 col-md-2">
                        <label class="form-label fw-bold x-small text-uppercase mb-1" style="font-size:10px; color:#64748b;">Joined Period</label>
                        <select name="period" class="form-select form-select-sm">
                            <option value="all"   {{ request('period') == 'all'   ? 'selected' : '' }}>All Time</option>
                            <option value="today" {{ request('period') == 'today' ? 'selected' : '' }}>Today</option>
                            <option value="week"  {{ request('period') == 'week'  ? 'selected' : '' }}>This Week</option>
                            <option value="month" {{ request('period') == 'month' ? 'selected' : '' }}>This Month</option>
                            <option value="year"  {{ request('period') == 'year'  ? 'selected' : '' }}>This Year</option>
                        </select>
                    </div>
                    <div class="col-6 col-md-2">
                        <label class="form-label fw-bold x-small text-uppercase mb-1" style="font-size:10px; color:#64748b;">Region (Optional)</label>
                        <select name="region_id" id="mapRegionSelect" class="form-select form-select-sm">
                            <option value="all">All Regions</option>
                            @foreach($regions as $region)
                                <option value="{{ $region->id }}" {{ request('region_id') == $region->id ? 'selected' : '' }}>{{ $region->region_name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-6 col-md-2">
                        <label class="form-label fw-bold x-small text-uppercase mb-1" style="font-size:10px; color:#64748b;">District (Optional)</label>
                        <select name="district_id" id="mapDistrictSelect" class="form-select form-select-sm">
                            <option value="all">All Districts</option>
                            @foreach($districts as $district)
                                <option value="{{ $district->id }}" {{ request('district_id') == $district->id ? 'selected' : '' }}>{{ $district->district_name }}</option>
                            @endforeach
                        </select>
                    </div>
                    @if(in_array(auth()->user()->role, ['admin','super_admin','accountant']))
                    <div class="col-12 col-md-3">
                        <label class="form-label fw-bold x-small text-uppercase mb-1" style="font-size:10px; color:#64748b;">Salesperson (Brought By)</label>
                        <select name="saler_id" class="form-select form-select-sm">
                            <option value="all">All Salespeople</option>
                            @foreach($salers as $s)
                                <option value="{{ $s->id }}" {{ request('saler_id') == $s->id ? 'selected' : '' }}>{{ $s->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    @endif
                    <div class="col-12 col-md-auto d-flex align-items-end gap-2 ms-md-auto">
                        <button type="submit" class="btn btn-primary btn-sm px-4 fw-bold rounded-2 flex-fill flex-md-grow-0">Apply</button>
                        <a href="{{ route('admin.customers.map') }}" class="btn btn-outline-secondary btn-sm px-3 fw-bold rounded-2 flex-fill flex-md-grow-0">Reset</a>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Main Map Layout -->
    <div class="row">
        <div class="col-12">
            <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
                <div class="card-header bg-white py-3 d-flex align-items-center justify-content-between border-bottom">
                    <h6 class="mb-0 fw-bold text-dark">
                        <i class="fas fa-map-marked-alt text-primary me-2"></i>Map View
                    </h6>
                    <span class="badge bg-primary-subtle text-primary rounded-pill px-3">{{ count($customerPoints) }} Pins Active</span>
                </div>
                <div class="card-body p-0 position-relative">
                    <div class="row g-0">
                        <div class="col-lg-9">
                            <div id="customerMapPage" style="height: 680px; z-index: 1;"></div>
                        </div>
                        <div class="col-lg-3 border-start bg-light bg-opacity-50">
                            <div class="p-3 h-100 d-flex flex-column" style="max-height: 680px;">
                                <h6 class="fw-bold text-muted small text-uppercase mb-3">Customer Location Pins</h6>
                                <div class="overflow-auto flex-grow-1">
                                    @forelse($customerPoints as $point)
                                        <button type="button" class="w-100 text-start border rounded-3 bg-white p-2 mb-2 map-point-card" data-lat="{{ $point['latitude'] }}" data-lng="{{ $point['longitude'] }}">
                                            <div class="d-flex align-items-start justify-content-between gap-2">
                                                <div>
                                                    <div class="fw-bold small text-dark">{{ $point['name'] }}</div>
                                                    <div class="x-small text-muted">{{ $point['company_name'] ?: 'No company name' }}</div>
                                                    <div class="x-small text-muted mt-1">
                                                        @if($point['region']){{ $point['region'] }}@endif
                                                        @if($point['district']) · {{ $point['district'] }}@endif
                                                        @if(!empty($point['count'])) · {{ number_format($point['count']) }} customers @endif
                                                    </div>
                                                </div>
                                                <span class="badge bg-primary-subtle text-primary rounded-pill">#{{ $loop->iteration }}</span>
                                            </div>
                                        </button>
                                    @empty
                                        <div class="text-center text-muted py-5 small">No customer records with latitude and longitude yet.</div>
                                    @endforelse
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const mapContainer = document.getElementById('customerMapPage');
        if (mapContainer) {
            const mapCenter = @json($mapCenter);
            const map = L.map('customerMapPage').setView(mapCenter, 6);

            // Voyager style tile layer
            L.tileLayer('https://{s}.basemaps.cartocdn.com/rastertiles/voyager/{z}/{x}/{y}{r}.png', {
                attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors &copy; <a href="https://carto.com/attributions">CARTO</a>',
                subdomains: 'abcd',
                maxZoom: 20
            }).addTo(map);

            const customerPoints = @json($customerPoints);
            const markers = [];

            customerPoints.forEach(function(point) {
                const marker = L.marker([point.latitude, point.longitude]).addTo(map);
                markers.push({ marker, point });

                marker.bindPopup(
                    '<div class="p-1" style="min-width: 220px;">' +
                        '<h6 class="fw-bold mb-1 text-dark">' + point.name + '</h6>' +
                        '<div class="small text-muted mb-1">' + (point.company_name || 'No company name') + '</div>' +
                        '<div class="small mb-1"><strong>Region:</strong> ' + (point.region || 'Not set') + '</div>' +
                        '<div class="small mb-1"><strong>District:</strong> ' + (point.district || 'Optional') + '</div>' +
                        (point.count ? '<div class="small mb-1"><strong>Customers:</strong> ' + point.count + '</div>' : '') +
                        '<div class="small mb-1"><strong>Lat/Lng:</strong> ' + point.latitude + ', ' + point.longitude + '</div>' +
                        '<div class="small text-muted">' + (point.address || '') + '</div>' +
                    '</div>'
                );
            });

            document.querySelectorAll('.map-point-card').forEach(function(card) {
                card.addEventListener('click', function() {
                    const lat = parseFloat(this.dataset.lat);
                    const lng = parseFloat(this.dataset.lng);
                    map.setView([lat, lng], 12);
                });
            });

            if (markers.length > 0) {
                const group = L.featureGroup(markers.map(item => item.marker));
                map.fitBounds(group.getBounds().pad(0.18));
            }
        }

        const regionSelect = document.getElementById('mapRegionSelect');
        const districtSelect = document.getElementById('mapDistrictSelect');

        if (regionSelect && districtSelect) {
            regionSelect.addEventListener('change', function() {
                const regionId = this.value;
                districtSelect.innerHTML = '<option value="all">All Districts</option>';

                if (!regionId || regionId === 'all') {
                    return;
                }

                fetch(`/regions/${regionId}/districts`)
                    .then(response => response.json())
                    .then(data => {
                        data.forEach(function(district) {
                            const option = document.createElement('option');
                            option.value = district.id;
                            option.textContent = district.district_name;
                            districtSelect.appendChild(option);
                        });
                    })
                    .catch(error => console.error('Error loading districts:', error));
            });
        }
    });
</script>
@endpush
